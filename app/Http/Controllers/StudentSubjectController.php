<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\StudentSubjectSection;
use App\Models\SubjectMark;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentSubjectController extends Controller
{
    public function index(): View
    {
        $student = Auth::user()->student;
        $period = AcademicPeriod::current();

        $subjects = collect();

        if ($student && $period) {
            $subjectList = $student->subjectsForPeriod($period);

            $marksBySubject = SubjectMark::where('academic_period_id', $period->id)
                ->where('student_id', $student->student_id)
                ->get()
                ->keyBy('subjectT_id');

            // Madah: whichever Section the student enrolled in carries the lecturer.
            // Hafazan: their live Halaqah lecturer (Student::lecturer) carries it —
            // fetched once via the relation below rather than per-subject.
            $sectionEnrollmentsBySubject = StudentSubjectSection::where('student_id', $student->student_id)
                ->where('academic_period_id', $period->id)
                ->with('section.lecturer')
                ->get()
                ->keyBy('subjectT_id');

            $halaqahLecturer = $student->lecturer;

            $subjects = $subjectList->map(function ($subject) use ($marksBySubject, $sectionEnrollmentsBySubject, $halaqahLecturer) {
                $mark = $marksBySubject->get($subject->subjectT_id);

                $lecturer = $subject->isHafazan()
                    ? $halaqahLecturer
                    : $sectionEnrollmentsBySubject->get($subject->subjectT_id)?->section?->lecturer;

                return [
                    'subject' => $subject,
                    'lecturer' => $lecturer,
                    'carry_mark_1' => $mark?->carry_mark_1,
                    'carry_mark_2' => $mark?->carry_mark_2,
                    'total' => $mark?->total,
                ];
            });
        }

        return view('student.subjects', compact('student', 'period', 'subjects'));
    }
}
