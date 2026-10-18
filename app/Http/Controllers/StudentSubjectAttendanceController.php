<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\SubjectAttendanceRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentSubjectAttendanceController extends Controller
{
    public function index(): View
    {
        $student = Auth::user()->student;
        $period = AcademicPeriod::current();

        $bySubject = collect();

        if ($student) {
            $records = SubjectAttendanceRecord::where('student_id', $student->student_id)
                ->with(['subject', 'session'])
                ->orderByDesc('date')
                ->get();

            $bySubject = $records
                ->groupBy('subjectT_id')
                ->map(function ($rows) {
                    $present = $rows->where('status', 'present')->count();
                    $absent = $rows->where('status', 'absent')->count();
                    $total = $present + $absent;

                    return [
                        'subject' => $rows->first()->subject,
                        'present' => $present,
                        'absent' => $absent,
                        'percentage' => $total > 0 ? round(($present / $total) * 100) : null,
                        'records' => $rows->sortByDesc('date')->values(),
                    ];
                })
                ->sortBy(fn ($row) => $row['subject']?->subjectT_code)
                ->values();
        }

        return view('student.subject-attendance', compact('student', 'period', 'bySubject'));
    }
}
