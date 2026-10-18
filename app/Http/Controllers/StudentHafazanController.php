<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Tasmi;

class StudentHafazanController extends Controller
{
    public function index()
    {
        // Find the student record linked to the logged-in user
        $student = Student::where('user_id', auth()->id())->first();

        // Fallback: match by email and auto-link (fixes existing unlinked accounts)
        if (!$student) {
            $student = Student::where('email', auth()->user()->email)->whereNull('user_id')->first();
            if ($student) {
                $student->update(['user_id' => auth()->id()]);
            }
        }

        if (!$student) {
            return view('student.hafazan', ['student' => null]);
        }

        // Total hafazan sessions recorded
        $totalSessions = Tasmi::where('student_id', $student->student_id)->count();

        // Most recent session — used for the "current progress" card
        $latestTasmi = Tasmi::where('student_id', $student->student_id)
                             ->with('progress')
                             ->latest('tasmi_date')
                             ->first();

        // Full hafazan history, newest first, paginated
        $hafazanHistory = Tasmi::where('student_id', $student->student_id)
                                ->with('progress')
                                ->orderByDesc('tasmi_date')
                                ->paginate(15);

        return view('student.hafazan', compact(
            'student',
            'totalSessions',
            'latestTasmi',
            'hafazanHistory'
        ));
    }
}
