<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\SubjectMark;
use App\Models\SubjectT;
use App\Services\SubjectOwnershipResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LecturerSubjectController extends Controller
{
    public function index(): View
    {
        $lecturer = Lecturer::where('user_id', Auth::id())->first();
        $period = AcademicPeriod::current();

        $assignments = collect();

        if ($lecturer && $period) {
            $hafazanRows = SubjectOwnershipResolver::hafazanSubjectsFor($lecturer, $period)
                ->map(fn ($row) => ['subject' => $row['subject'], 'student_count' => $row['students']->count()]);

            $madahRows = SubjectOwnershipResolver::madahSectionsOwnedBy($lecturer, $period)
                ->groupBy('subjectT_id')
                ->map(fn ($sections) => [
                    'subject' => $sections->first()->subject,
                    'student_count' => $sections->sum('enrollments_count'),
                ])
                ->values();

            $assignments = $hafazanRows->merge($madahRows)
                ->sortBy(fn ($row) => $row['subject']->subjectT_code)
                ->values();
        }

        return view('lecturer.subjects.index', compact('lecturer', 'period', 'assignments'));
    }

    public function show(SubjectT $subject): View|RedirectResponse
    {
        $lecturer = Lecturer::where('user_id', Auth::id())->first();
        $period = AcademicPeriod::current();

        if (! $lecturer || ! $period) {
            return redirect()->route('lecturer.subjects')->with('error', 'No active academic semester is currently available.');
        }

        if (! SubjectOwnershipResolver::isAssignedToSubject($lecturer, $subject, $period)) {
            return redirect()->route('lecturer.subjects')->with('error', 'You are not assigned to teach this subject this semester.');
        }

        $marksByStudent = SubjectMark::where('academic_period_id', $period->id)
            ->where('subjectT_id', $subject->subjectT_id)
            ->get()
            ->keyBy('student_id');

        $students = SubjectOwnershipResolver::rosterForSubject($lecturer, $subject, $period)
            ->map(fn (Student $student) => [
                'student' => $student,
                'carry_mark_1' => $marksByStudent->get($student->student_id)?->carry_mark_1,
                'carry_mark_2' => $marksByStudent->get($student->student_id)?->carry_mark_2,
            ]);

        return view('lecturer.subjects.show', compact('subject', 'period', 'students'));
    }

    public function saveMarks(Request $request, SubjectT $subject): RedirectResponse
    {
        $lecturer = Lecturer::where('user_id', Auth::id())->first();
        $period = AcademicPeriod::current();

        if (! $lecturer || ! $period) {
            return redirect()->route('lecturer.subjects')->with('error', 'No active academic semester is currently available.');
        }

        if (! SubjectOwnershipResolver::isAssignedToSubject($lecturer, $subject, $period)) {
            return redirect()->route('lecturer.subjects')->with('error', 'You are not assigned to teach this subject this semester.');
        }

        $eligibleStudentIds = SubjectOwnershipResolver::rosterForSubject($lecturer, $subject, $period)
            ->pluck('student_id')
            ->flip();

        $rows = $request->input('rows', []);

        DB::transaction(function () use ($rows, $eligibleStudentIds, $lecturer, $period, $subject) {
            foreach ($rows as $studentId => $data) {
                if (! isset($eligibleStudentIds[$studentId])) {
                    continue;
                }

                $carryMark1 = $data['carry_mark_1'] ?? null;
                $carryMark2 = $data['carry_mark_2'] ?? null;

                $carryMark1 = $carryMark1 === '' ? null : $carryMark1;
                $carryMark2 = $carryMark2 === '' ? null : $carryMark2;

                if ($carryMark1 === null && $carryMark2 === null) {
                    continue;
                }

                SubjectMark::updateOrCreate(
                    [
                        'academic_period_id' => $period->id,
                        'subjectT_id' => $subject->subjectT_id,
                        'student_id' => $studentId,
                    ],
                    [
                        'lecturer_id' => $lecturer->lecturer_id,
                        'carry_mark_1' => $carryMark1 !== null ? max(0, min(100, (float) $carryMark1)) : null,
                        'carry_mark_2' => $carryMark2 !== null ? max(0, min(100, (float) $carryMark2)) : null,
                    ]
                );
            }
        });

        return redirect()->route('lecturer.subjects.show', $subject->subjectT_id)
            ->with('success', "Marks saved for \"{$subject->subjectT_name}\".");
    }
}
