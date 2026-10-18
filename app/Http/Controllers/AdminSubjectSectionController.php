<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\BatchSemesterLevel;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\StudentSubjectSection;
use App\Models\SubjectSection;
use App\Models\SubjectT;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminSubjectSectionController extends Controller
{
    /** Overview: every subject for the selected period, with section/enrollment totals. */
    public function overview(Request $request): View
    {
        $academicPeriods = AcademicPeriod::orderByDesc('start_date')->get();
        $activePeriod = AcademicPeriod::current();

        $selectedPeriodId = $request->query('academic_period_id') ?: $activePeriod?->id;
        $period = $selectedPeriodId ? $academicPeriods->firstWhere('id', (int) $selectedPeriodId) : null;

        // Hafazan subjects don't use sections — they're taught via the existing
        // Halaqah lecturer-student assignment (see Student::lecturer / HalaqahAssignment).
        $subjects = SubjectT::with('semester')
            ->where('type', SubjectT::TYPE_MADAH)
            ->orderBy('semester_id')
            ->orderBy('subjectT_code')
            ->get();

        $sectionStats = collect();

        if ($period) {
            $sections = SubjectSection::where('academic_period_id', $period->id)
                ->withCount('enrollments')
                ->get()
                ->groupBy('subjectT_id');

            foreach ($subjects as $subject) {
                $subjectSections = $sections->get($subject->subjectT_id, collect());

                $sectionStats[$subject->subjectT_id] = [
                    'sections' => $subjectSections->count(),
                    'capacity' => $subjectSections->sum('capacity'),
                    'enrolled' => $subjectSections->sum('enrollments_count'),
                ];
            }
        }

        return view('admin.sections.overview', compact(
            'academicPeriods', 'period', 'subjects', 'sectionStats'
        ));
    }

    public function toggleEnrollment(Request $request, AcademicPeriod $academicPeriod): RedirectResponse
    {
        $academicPeriod->update([
            'section_enrollment_open' => ! $academicPeriod->section_enrollment_open,
        ]);

        $state = $academicPeriod->section_enrollment_open ? 'opened' : 'closed';

        return redirect()
            ->route('admin.sections', ['academic_period_id' => $academicPeriod->id])
            ->with('success', "Section enrollment for \"{$academicPeriod->name}\" is now {$state}.");
    }

    /** Manage sections + the enrollment roster for one subject, within a period. */
    public function manage(Request $request, SubjectT $subject): View|RedirectResponse
    {
        if ($subject->isHafazan()) {
            return redirect()
                ->route('admin.sections')
                ->with('error', "\"{$subject->subjectT_name}\" is a Hafazan subject — it uses the existing Halaqah lecturer-student assignment instead of Sections.");
        }

        $academicPeriods = AcademicPeriod::orderByDesc('start_date')->get();
        $activePeriod = AcademicPeriod::current();

        $selectedPeriodId = $request->query('academic_period_id') ?: $activePeriod?->id;
        $period = $selectedPeriodId ? $academicPeriods->firstWhere('id', (int) $selectedPeriodId) : null;

        if (! $period) {
            return redirect()->route('admin.sections')->with('error', 'No Academic Semester selected or active.');
        }

        $lecturers = Lecturer::orderBy('lecturer_name')->get();

        $sections = SubjectSection::where('subjectT_id', $subject->subjectT_id)
            ->where('academic_period_id', $period->id)
            ->withCount('enrollments')
            ->with('lecturer')
            ->orderBy('section_name')
            ->get();

        $enrollments = StudentSubjectSection::where('subjectT_id', $subject->subjectT_id)
            ->where('academic_period_id', $period->id)
            ->with(['student', 'section'])
            ->get()
            ->sortBy(fn ($e) => $e->student?->student_name);

        $eligibleStudents = $this->eligibleStudents($subject, $period)
            ->whereNotIn('student_id', $enrollments->pluck('student_id'))
            ->values();

        return view('admin.sections.manage', compact(
            'subject', 'academicPeriods', 'period', 'lecturers', 'sections', 'enrollments', 'eligibleStudents'
        ));
    }

    public function store(Request $request, SubjectT $subject): RedirectResponse
    {
        abort_if($subject->isHafazan(), 422, 'Hafazan subjects use Halaqah assignments, not Sections.');

        $validated = $request->validate([
            'academic_period_id' => 'required|exists:academic_periods,id',
            'section_name' => 'required|string|max:50',
            'lecturer_id' => 'nullable|exists:lecturer,lecturer_id',
            'capacity' => 'required|integer|min:1',
        ]);

        SubjectSection::create([
            'subjectT_id' => $subject->subjectT_id,
            'academic_period_id' => $validated['academic_period_id'],
            'section_name' => $validated['section_name'],
            'lecturer_id' => $validated['lecturer_id'] ?? null,
            'capacity' => $validated['capacity'],
        ]);

        return redirect()
            ->route('admin.sections.manage', ['subject' => $subject->subjectT_id, 'academic_period_id' => $validated['academic_period_id']])
            ->with('success', "Section \"{$validated['section_name']}\" created.");
    }

    public function update(Request $request, SubjectT $subject, SubjectSection $section): RedirectResponse
    {
        $validated = $request->validate([
            'section_name' => 'required|string|max:50',
            'lecturer_id' => 'nullable|exists:lecturer,lecturer_id',
            'capacity' => 'required|integer|min:1',
        ]);

        $section->update($validated);

        return redirect()
            ->route('admin.sections.manage', ['subject' => $subject->subjectT_id, 'academic_period_id' => $section->academic_period_id])
            ->with('success', "Section \"{$section->section_name}\" updated.");
    }

    public function destroy(SubjectT $subject, SubjectSection $section): RedirectResponse
    {
        $periodId = $section->academic_period_id;
        $name = $section->section_name;
        $section->delete();

        return redirect()
            ->route('admin.sections.manage', ['subject' => $subject->subjectT_id, 'academic_period_id' => $periodId])
            ->with('success', "Section \"{$name}\" deleted.");
    }

    public function enrollStudent(Request $request, SubjectT $subject): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:student,student_id',
            'subject_section_id' => 'required|exists:subject_sections,id',
        ]);

        $section = SubjectSection::findOrFail($validated['subject_section_id']);

        return DB::transaction(function () use ($validated, $subject, $section) {
            $section = SubjectSection::whereKey($section->id)->lockForUpdate()->firstOrFail();

            if ($section->isFull()) {
                return redirect()
                    ->route('admin.sections.manage', ['subject' => $subject->subjectT_id, 'academic_period_id' => $section->academic_period_id])
                    ->with('error', "Section \"{$section->section_name}\" is already full.");
            }

            StudentSubjectSection::updateOrCreate(
                [
                    'student_id' => $validated['student_id'],
                    'subjectT_id' => $subject->subjectT_id,
                    'academic_period_id' => $section->academic_period_id,
                ],
                ['subject_section_id' => $section->id]
            );

            return redirect()
                ->route('admin.sections.manage', ['subject' => $subject->subjectT_id, 'academic_period_id' => $section->academic_period_id])
                ->with('success', "Student enrolled into Section \"{$section->section_name}\".");
        });
    }

    public function moveStudent(Request $request, SubjectT $subject, StudentSubjectSection $enrollment): RedirectResponse
    {
        $validated = $request->validate([
            'subject_section_id' => 'required|exists:subject_sections,id',
        ]);

        $target = SubjectSection::findOrFail($validated['subject_section_id']);

        if ($target->subjectT_id != $subject->subjectT_id || $target->academic_period_id != $enrollment->academic_period_id) {
            abort(422, 'Target section does not belong to this subject/semester.');
        }

        return DB::transaction(function () use ($target, $enrollment, $subject) {
            $target = SubjectSection::whereKey($target->id)->lockForUpdate()->firstOrFail();

            if ($target->id != $enrollment->subject_section_id && $target->isFull()) {
                return redirect()
                    ->route('admin.sections.manage', ['subject' => $subject->subjectT_id, 'academic_period_id' => $enrollment->academic_period_id])
                    ->with('error', "Section \"{$target->section_name}\" is already full.");
            }

            $enrollment->update(['subject_section_id' => $target->id]);

            return redirect()
                ->route('admin.sections.manage', ['subject' => $subject->subjectT_id, 'academic_period_id' => $enrollment->academic_period_id])
                ->with('success', "Student moved to Section \"{$target->section_name}\".");
        });
    }

    public function removeStudent(SubjectT $subject, StudentSubjectSection $enrollment): RedirectResponse
    {
        $periodId = $enrollment->academic_period_id;
        $enrollment->delete();

        return redirect()
            ->route('admin.sections.manage', ['subject' => $subject->subjectT_id, 'academic_period_id' => $periodId])
            ->with('success', 'Enrollment removed.');
    }

    /** Students who take this subject this period, per Student::subjectsForPeriod's batch/semester-level logic. */
    private function eligibleStudents(SubjectT $subject, AcademicPeriod $period): \Illuminate\Support\Collection
    {
        $batchIds = BatchSemesterLevel::where('academic_period_id', $period->id)
            ->where('semester_id', $subject->semester_id)
            ->pluck('batch_id');

        return Student::whereIn('batch_id', $batchIds)
            ->active()
            ->orderBy('student_name')
            ->get();
    }
}
