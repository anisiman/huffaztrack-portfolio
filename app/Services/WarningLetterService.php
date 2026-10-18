<?php

namespace App\Services;

use App\Models\AcademicPeriod;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\WarningLetter;
use App\Models\WarningThreshold;
use App\Services\NotificationService;

class WarningLetterService
{
    /** Used only when no academic semester is active/configured. */
    private const LEGACY_THRESHOLDS = [3, 6, 9];

    /**
     * Recalculate warning level for a student based on their unexcused absences
     * (TH) within the currently active academic semester. Only 'absent' status
     * counts — 'absent_with_reason' (THS) is excluded.
     *
     * Warning 3 terminates the student permanently: once status is 'inactive',
     * this method is a no-op — it never re-evaluates or reactivates a
     * terminated student, even across a semester boundary.
     */
    public function recalculate(string $studentId): void
    {
        $student = Student::find($studentId);
        if (! $student) {
            return;
        }

        if ($student->status === 'inactive') {
            return;
        }

        AcademicPeriod::recalculateAll();
        $activePeriod = AcademicPeriod::current();

        $absentCount = $this->absentCountFor($studentId, $activePeriod);
        $thresholds  = $activePeriod ? WarningThreshold::effectiveFor($activePeriod) : self::LEGACY_THRESHOLDS;

        $newLevel = $this->levelFor($absentCount, $thresholds);
        $oldLevel = (int) ($student->warning_level ?? 0);

        // Update student warning_level; terminate permanently if the top threshold is reached.
        $student->warning_level = $newLevel;
        if ($newLevel >= 3) {
            $student->status = 'inactive';
        }
        $student->save();

        // Create a warning letter record only when the level increases
        if ($newLevel > $oldLevel && $newLevel >= 1) {
            $triggeringAttendance = Attendance::where('student_id', $studentId)
                ->where('status', 'absent')
                ->orderByDesc('date')
                ->first();

            if ($triggeringAttendance) {
                WarningLetter::create([
                    'student_id'       => $studentId,
                    'attendance_id'    => $triggeringAttendance->attendance_id,
                    'attendance_count' => $absentCount,
                    'warning_level'    => $newLevel,
                    'letter_date'      => now()->toDateString(),
                    'status'           => 'active',
                ]);
            }

            $levelLabel = $newLevel >= 3 ? 'Warning Letter 3' : "Level {$newLevel}";

            NotificationService::notifyStudent($studentId, 'warning', "Warning Letter Issued ({$levelLabel})", "You have received a Warning Letter {$levelLabel} due to unexcused absences. Please contact your lecturer.", '/student/attendance');
            NotificationService::notifyLecturerByStudentId($studentId, 'warning', "Student Warning {$levelLabel}", "{$student->student_name} has reached Warning {$levelLabel} ({$absentCount} unexcused absences).", '/lecturer/attendance');
            NotificationService::notifyAdmins('warning', "Student Warning Alert", "{$student->student_name} has reached Warning {$levelLabel}.", '/admin/masterlist/' . $studentId);
        }
    }

    /**
     * Unexcused-absence (TH) count for a student, scoped to the given academic
     * period's date range — or lifetime when no period is given.
     */
    public function absentCountFor(string $studentId, ?AcademicPeriod $period): int
    {
        $query = Attendance::where('student_id', $studentId)->where('status', 'absent');

        if ($period) {
            $query->whereBetween('date', [$period->start_date, $period->end_date]);
        }

        return $query->count();
    }

    /** @param array{0:int,1:int,2:int} $thresholds [warning1, warning2, warning3] */
    private function levelFor(int $count, array $thresholds): int
    {
        return match (true) {
            $count >= $thresholds[2] => 3,
            $count >= $thresholds[1] => 2,
            $count >= $thresholds[0] => 1,
            default                  => 0,
        };
    }
}
