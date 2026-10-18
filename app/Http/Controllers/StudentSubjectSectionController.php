<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\StudentSubjectSection;
use App\Models\SubjectSection;
use App\Models\SubjectT;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentSubjectSectionController extends Controller
{
    public function index(): View
    {
        $student = Auth::user()->student;
        $period = AcademicPeriod::current();

        $subjects = collect();
        $myEnrollments = collect();

        if ($student && $period) {
            // Hafazan subjects aren't section-based — they follow the student's
            // existing Halaqah lecturer assignment, so they're excluded here.
            $subjectList = $student->subjectsForPeriod($period)->where('type', SubjectT::TYPE_MADAH);

            $sectionsBySubject = SubjectSection::where('academic_period_id', $period->id)
                ->whereIn('subjectT_id', $subjectList->pluck('subjectT_id'))
                ->withCount('enrollments')
                ->with('lecturer')
                ->orderBy('section_name')
                ->get()
                ->groupBy('subjectT_id');

            $myEnrollments = StudentSubjectSection::where('student_id', $student->student_id)
                ->where('academic_period_id', $period->id)
                ->get()
                ->keyBy('subjectT_id');

            $subjects = $subjectList->map(fn ($subject) => [
                'subject' => $subject,
                'sections' => $sectionsBySubject->get($subject->subjectT_id, collect()),
                'my_section_id' => $myEnrollments->get($subject->subjectT_id)?->subject_section_id,
            ]);
        }

        return view('student.section-enrollment', compact('student', 'period', 'subjects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $student = Auth::user()->student;
        $period = AcademicPeriod::current();

        if (! $student || ! $period) {
            return redirect()->route('student.sectionEnrollment')->with('error', 'No active Academic Semester is currently available.');
        }

        if (! $period->section_enrollment_open) {
            return redirect()->route('student.sectionEnrollment')->with('error', 'Section enrollment is currently closed. Contact your administrator to change your section.');
        }

        $validated = $request->validate([
            'subject_section_id' => 'required|exists:subject_sections,id',
        ]);

        $section = SubjectSection::findOrFail($validated['subject_section_id']);

        $eligibleSubjectIds = $student->subjectsForPeriod($period)
            ->where('type', SubjectT::TYPE_MADAH)
            ->pluck('subjectT_id');

        if ($section->academic_period_id != $period->id || ! $eligibleSubjectIds->contains($section->subjectT_id)) {
            abort(422, 'This section is not available to you this semester.');
        }

        return DB::transaction(function () use ($section, $student, $period) {
            $section = SubjectSection::whereKey($section->id)->lockForUpdate()->firstOrFail();

            $existing = StudentSubjectSection::where('student_id', $student->student_id)
                ->where('subjectT_id', $section->subjectT_id)
                ->where('academic_period_id', $period->id)
                ->first();

            if ((! $existing || $existing->subject_section_id != $section->id) && $section->isFull()) {
                return redirect()->route('student.sectionEnrollment')->with('error', "Section \"{$section->section_name}\" is already full.");
            }

            StudentSubjectSection::updateOrCreate(
                [
                    'student_id' => $student->student_id,
                    'subjectT_id' => $section->subjectT_id,
                    'academic_period_id' => $period->id,
                ],
                ['subject_section_id' => $section->id]
            );

            return redirect()->route('student.sectionEnrollment')->with('success', "You are enrolled in Section \"{$section->section_name}\".");
        });
    }
}
