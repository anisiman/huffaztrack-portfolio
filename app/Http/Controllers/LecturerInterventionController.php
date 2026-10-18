<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Lecturer;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LecturerInterventionController extends Controller
{
    public function show(string $studentId): View
    {
        $lecturerId = Lecturer::where('user_id', Auth::id())->value('lecturer_id');

        $student = Student::with(['batch', 'lecturer', 'results.semester'])
            ->where('lecturer_id', $lecturerId)
            ->findOrFail($studentId);

        $results = $student->results->sortBy(fn ($result) => $result->semester?->semester_id ?? 0)->values();
        $latestResult = $results->last();
        $previousResult = $results->count() > 1 ? $results->get($results->count() - 2) : null;

        $currentCgpa = $latestResult?->cgpa;
        $riskLevel = $currentCgpa === null ? 'Unknown'
            : ($currentCgpa < 2.0 ? 'Rasib' : ($currentCgpa < 2.5 ? 'Maqbul' : 'On Track'));

        $trend = null;
        if ($previousResult?->cgpa !== null && $currentCgpa !== null) {
            $difference = $currentCgpa - $previousResult->cgpa;
            if ($difference <= -0.05) {
                $trend = 'declining';
            } elseif ($difference >= 0.05) {
                $trend = 'improving';
            } else {
                $trend = 'stable';
            }
        }

        $attendanceWindowStart = Carbon::today()->subDays(30);
        $attendanceRows = Attendance::where('student_id', $student->student_id)
            ->whereBetween('date', [$attendanceWindowStart->toDateString(), Carbon::today()->toDateString()])
            ->get();

        $attendancePresent = $attendanceRows->where('status', 'present')->count();
        $attendanceAbsent = $attendanceRows->where('status', 'absent')->count();
        $attendanceExcused = $attendanceRows->where('status', 'absent_with_reason')->count();
        $attendanceTotal = $attendanceRows->count();
        $attendanceRate = $attendanceTotal > 0
            ? round($attendancePresent / $attendanceTotal * 100)
            : null;

        $reasons = [];

        if ($currentCgpa === null) {
            $reasons[] = 'No cumulative GPA record is available for this student yet.';
        } else {
            if ($currentCgpa < 2.0) {
                $reasons[] = 'Current CGPA is below 2.00, placing the student in the Rasib category.';
            } elseif ($currentCgpa < 2.5) {
                $reasons[] = 'Current CGPA is below 2.50, placing the student in the Maqbul category.';
            }
        }

        if ($trend === 'declining') {
            $reasons[] = 'CGPA trend is declining compared with the previous semester.';
        } elseif ($trend === 'stable' && $currentCgpa !== null && $currentCgpa < 2.5) {
            $reasons[] = 'CGPA is low and has not shown clear improvement this semester.';
        }

        if ($attendanceTotal === 0) {
            $reasons[] = 'No attendance records exist for the last 30 days; verify that attendance is being submitted.';
        } else {
            if ($attendanceRate < 80) {
                $reasons[] = "Attendance rate over the last 30 days is {$attendanceRate}%, which is below the expected threshold.";
            }
            if ($attendanceAbsent >= 3) {
                $reasons[] = "The student has missed {$attendanceAbsent} sessions in the last 30 days.";
            }
        }

        if ($student->results->count() > 3 && $currentCgpa !== null) {
            $averageLastThree = $results->slice(max(0, $results->count() - 3))->avg('cgpa');
            if ($averageLastThree !== null && $currentCgpa < $averageLastThree - 0.05) {
                $reasons[] = 'Recent CGPA performance is lower than the average of the last three semesters.';
            }
        }

        $recommendations = [
            'Schedule a dedicated consultation session to review the student’s current academic and attendance challenges.',
            'Add focused revision sessions for weak areas and agree on a short-term study plan.',
            'Monitor attendance closely and follow up on every missed session within 24 hours.',
            'Record intervention notes, progress checkpoints, and agreed next steps in the student file.',
            'Escalate to admin if there is no measurable improvement in the next two weeks.',
        ];

        return view('lecturer.intervention', compact(
            'student',
            'latestResult',
            'currentCgpa',
            'riskLevel',
            'reasons',
            'recommendations',
            'attendanceRate',
            'attendancePresent',
            'attendanceAbsent',
            'attendanceExcused',
            'attendanceTotal',
            'trend'
        ));
    }
}
