<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceQrSession;
use App\Models\Student;
use App\Models\SubjectAttendanceRecord;
use App\Models\SubjectAttendanceSession;
use Illuminate\Support\Collection;

/**
 * Marks every roster student who did not scan as Absent once a QR session's
 * window has closed. Runs from the scheduler (attendance:finalize-expired) so
 * the outcome no longer depends on the lecturer reopening the attendance page;
 * the controllers still call it on page load as a fallback. Idempotent —
 * existing records (scanned, manual, excused) are never overwritten.
 */
class AttendanceFinalizer
{
    public function __construct(private WarningLetterService $warningLetters)
    {
    }

    public function finalizeTasmik(AttendanceQrSession $session): void
    {
        $date = $session->start_time->copy()->setTimezone(config('app.timezone'))->toDateString();

        $students = Student::query()
            ->operational()
            ->where('lecturer_id', $session->lecturer_id)
            ->get();

        foreach ($students as $student) {
            $record = Attendance::firstOrCreate(
                ['student_id' => $student->getKey(), 'date' => $date],
                ['status' => 'absent']
            );

            if ($record->wasRecentlyCreated) {
                $this->warningLetters->recalculate($student->getKey());
                if ($student->user_id) {
                    NotificationService::send(
                        $student->user_id,
                        'attendance',
                        'Marked Absent',
                        'You did not scan the attendance QR before the session ended and have been marked absent. Contact your lecturer if this is incorrect.',
                        '/student/attendance'
                    );
                }
            }
        }

        $session->forceFill(['finalized_at' => now()])->save();
    }

    public function finalizeSubject(SubjectAttendanceSession $session): void
    {
        foreach ($this->subjectRoster($session) as $student) {
            SubjectAttendanceRecord::firstOrCreate(
                [
                    'subject_attendance_session_id' => $session->id,
                    'student_id' => $student->getKey(),
                ],
                [
                    'academic_period_id' => $session->academic_period_id,
                    'subjectT_id' => $session->subjectT_id,
                    'batch_id' => $student->batch_id,
                    'subject_section_id' => $session->subject_section_id,
                    'lecturer_id' => $session->lecturer_id,
                    'status' => 'absent',
                    'date' => $session->start_time->toDateString(),
                ]
            );
        }

        $session->forceFill(['finalized_at' => now()])->save();
    }

    /**
     * Students expected at a Subject Attendance session:
     * Section roster when sectioned; Halaqah students for Hafazan;
     * whole batch for legacy Madah sessions that predate Sections.
     */
    public function subjectRoster(SubjectAttendanceSession $session): Collection
    {
        if ($session->subject_section_id) {
            return SubjectOwnershipResolver::studentsInSection($session->section);
        }

        if ($session->subject?->isHafazan()) {
            return Student::where('lecturer_id', $session->lecturer_id)->active()->orderBy('student_name')->get();
        }

        return Student::where('batch_id', $session->batch_id)->active()->orderBy('student_name')->get();
    }
}
