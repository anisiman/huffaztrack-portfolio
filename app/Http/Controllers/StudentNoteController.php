<?php

namespace App\Http\Controllers;

use App\Models\Student;

class StudentNoteController extends Controller
{
    public function index()
    {
        $student = Student::where('user_id', auth()->id())->first();

        if (! $student) {
            $student = Student::where('email', auth()->user()->email)->whereNull('user_id')->first();
            if ($student) {
                $student->update(['user_id' => auth()->id()]);
            }
        }

        if (! $student) {
            return view('student.notes', ['notes' => collect()]);
        }

        $notes = $student->notes()
            ->with('lecturer')
            ->where('is_private', false)
            ->get();

        return view('student.notes', compact('notes'));
    }
}
