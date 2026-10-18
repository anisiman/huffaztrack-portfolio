<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\Attendance;
use App\Models\AttendanceAppeal;
use App\Models\Student;

class StudentAttendanceController extends Controller
{
    public function index()
    {
        $student = Student::where('user_id', auth()->id())->first();

        // Fallback: match by email and auto-link
        if (! $student) {
            $student = Student::where('email', auth()->user()->email)->whereNull('user_id')->first();
            if ($student) {
                $student->update(['user_id' => auth()->id()]);
            }
        }

        if (! $student) {
            return view('student.attendance', ['student' => null]);
        }

        // Summary totals are scoped to the current academic semester so a
        // student's stats don't carry over TH/H counts from past semesters.
        AcademicPeriod::recalculateAll();
        $activePeriod = AcademicPeriod::current();

        $summaryQuery = fn () => Attendance::where('student_id', $student->student_id)
            ->when($activePeriod, fn ($q) => $q->whereBetween('date', [$activePeriod->start_date, $activePeriod->end_date]));

        $totalAttendance   = $summaryQuery()->count();
        $presentCount      = $summaryQuery()->where('status', 'present')->count();
        $absentCount       = $summaryQuery()->where('status', 'absent')->count();
        $excusedCount      = $summaryQuery()->where('status', 'absent_with_reason')->count();
        $attendancePercent = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100) : 0;

        $attendanceHistory = Attendance::where('student_id', $student->student_id)
            ->orderByDesc('date')
            ->paginate(15);

        // Load appeals keyed by attendance_id for quick lookup in the view
        $attendanceIds = $attendanceHistory->pluck('attendance_id');
        $appeals = AttendanceAppeal::whereIn('attendance_id', $attendanceIds)
            ->get()
            ->keyBy('attendance_id');

        return view('student.attendance', compact(
            'student',
            'totalAttendance',
            'presentCount',
            'absentCount',
            'excusedCount',
            'attendancePercent',
            'attendanceHistory',
            'appeals'
        ));
    }
}
