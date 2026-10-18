<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\BatchSemesterLevel;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentSubjectSection;
use App\Models\SubjectMark;
use App\Models\SubjectSection;
use App\Models\SubjectT;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSubjectController extends Controller
{
    public function index(Request $request): View
    {
        $academicPeriods = AcademicPeriod::orderByDesc('start_date')->get();
        $activePeriod = AcademicPeriod::current();

        $selectedPeriodId = $request->query('academic_period_id') ?: $activePeriod?->id;
        $period = $selectedPeriodId ? $academicPeriods->firstWhere('id', (int) $selectedPeriodId) : null;

        $subjects = SubjectT::with('semester')
            ->orderBy('semester_id')
            ->orderBy('subjectT_code')
            ->get();

        $eligibleCounts = collect();
        $marksEnteredCounts = collect();

        // Madah ownership summary: sections + distinct assigned lecturers this
        // period. Hafazan subjects have no equivalent — they're shown as
        // "Managed via Halaqah" in the view instead of reading this at all.
        $sectionSummaries = collect();

        if ($period) {
            // Batches mapped to each Semester Level for this period, so we can
            // count how many active students are currently eligible per subject.
            $batchIdsByLevel = BatchSemesterLevel::where('academic_period_id', $period->id)
                ->get()
                ->groupBy('semester_id')
                ->map(fn ($rows) => $rows->pluck('batch_id'));

            foreach ($subjects as $subject) {
                $batchIds = $batchIdsByLevel->get($subject->semester_id, collect());
                $eligibleCounts[$subject->subjectT_id] = $batchIds->isEmpty()
                    ? 0
                    : Student::whereIn('batch_id', $batchIds)->active()->count();
            }

            // toBase() avoids Eloquent model hydration here — SubjectMark's
            // getTotalAttribute() accessor would otherwise shadow this raw
            // aggregate's "total" alias and silently return null for every row.
            $marksEnteredCounts = SubjectMark::where('academic_period_id', $period->id)
                ->where(fn ($q) => $q->whereNotNull('carry_mark_1')->orWhereNotNull('carry_mark_2'))
                ->selectRaw('subjectT_id, COUNT(*) as total')
                ->groupBy('subjectT_id')
                ->toBase()
                ->pluck('total', 'subjectT_id');

            $sectionSummaries = SubjectSection::where('academic_period_id', $period->id)
                ->get()
                ->groupBy('subjectT_id')
                ->map(fn ($sections) => [
                    'sections' => $sections->count(),
                    'lecturers' => $sections->pluck('lecturer_id')->filter()->unique()->count(),
                ]);
        }

        $semesters = Semester::orderBy('semester_id')->get();

        return view('admin.subjects.index', compact(
            'academicPeriods', 'period', 'subjects', 'semesters',
            'eligibleCounts', 'marksEnteredCounts', 'sectionSummaries'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSubject($request);

        SubjectT::create($validated);

        return redirect()
            ->route('admin.subjects', $request->only('academic_period_id'))
            ->with('success', "Subject \"{$validated['subjectT_name']}\" created successfully.");
    }

    public function update(Request $request, SubjectT $subject): RedirectResponse
    {
        $validated = $this->validateSubject($request);

        if ($validated['type'] !== $subject->type && $subject->subjectSections()->exists()) {
            return redirect()
                ->route('admin.subjects', $request->only('academic_period_id'))
                ->with('error', "Cannot change the type of \"{$subject->subjectT_name}\" — it already has Sections. Remove them first.");
        }

        $subject->update($validated);

        return redirect()
            ->route('admin.subjects', $request->only('academic_period_id'))
            ->with('success', "Subject \"{$subject->subjectT_name}\" updated successfully.");
    }

    /**
     * Delete a subject. Blocked once it has any Section, enrollment, marks,
     * or attendance history — deleting would cascade-wipe that history, and
     * silently losing it would defeat the point of tracking it.
     */
    public function destroy(Request $request, SubjectT $subject): RedirectResponse
    {
        $hasHistory = $subject->subjectSections()->exists()
            || $subject->studentSubjects()->exists()
            || $subject->subjectMarks()->exists()
            || $subject->subjectAttendanceSessions()->exists();

        if ($hasHistory) {
            return redirect()
                ->route('admin.subjects', $request->only('academic_period_id'))
                ->with('error', "Cannot delete \"{$subject->subjectT_name}\" — it has sections, enrollments, marks, or attendance history. Remove those first.");
        }

        $name = $subject->subjectT_name;
        $subject->delete();

        return redirect()
            ->route('admin.subjects', $request->only('academic_period_id'))
            ->with('success', "Subject \"{$name}\" deleted successfully.");
    }

    /** @return array<string, mixed> */
    private function validateSubject(Request $request): array
    {
        $validated = $request->validate([
            'subjectT_name' => 'required|string|max:255',
            'subjectT_code' => 'nullable|string|max:50',
            'type' => 'required|in:' . SubjectT::TYPE_HAFAZAN . ',' . SubjectT::TYPE_MADAH,
            'credit_hours' => 'nullable|integer|min:0',
            'semester_id' => 'required|exists:semester,semester_id',
        ]);

        return $validated;
    }

    public function marks(Request $request, SubjectT $subject): View
    {
        $academicPeriods = AcademicPeriod::orderByDesc('start_date')->get();
        $activePeriod = AcademicPeriod::current();

        $selectedPeriodId = $request->query('academic_period_id') ?: $activePeriod?->id;
        $period = $selectedPeriodId ? $academicPeriods->firstWhere('id', (int) $selectedPeriodId) : null;

        $students = collect();

        if ($period) {
            $batchIds = BatchSemesterLevel::where('academic_period_id', $period->id)
                ->where('semester_id', $subject->semester_id)
                ->pluck('batch_id');

            $marksByStudent = SubjectMark::where('academic_period_id', $period->id)
                ->where('subjectT_id', $subject->subjectT_id)
                ->get()
                ->keyBy('student_id');

            // For Madah subjects, "responsible" is whichever Section (and its
            // lecturer) the student enrolled in — not a single subject-wide
            // lecturer. For Hafazan, it's simply their live Halaqah lecturer.
            $sectionEnrollmentsByStudent = $subject->isMadah()
                ? StudentSubjectSection::where('academic_period_id', $period->id)
                    ->where('subjectT_id', $subject->subjectT_id)
                    ->with('section.lecturer')
                    ->get()
                    ->keyBy('student_id')
                : collect();

            $students = Student::whereIn('batch_id', $batchIds)
                ->active()
                ->with(['batch', 'lecturer'])
                ->orderBy('student_name')
                ->get()
                ->map(function (Student $student) use ($marksByStudent, $sectionEnrollmentsByStudent, $subject) {
                    $mark = $marksByStudent->get($student->student_id);

                    if ($subject->isHafazan()) {
                        $responsible = $student->lecturer ? $student->lecturer->lecturer_name . ' (Halaqah)' : null;
                    } else {
                        $section = $sectionEnrollmentsByStudent->get($student->student_id)?->section;
                        $responsible = $section
                            ? 'Section ' . $section->section_name . ($section->lecturer ? ' — ' . $section->lecturer->lecturer_name : '')
                            : null;
                    }

                    return [
                        'student' => $student,
                        'responsible' => $responsible ?? 'Not enrolled',
                        'carry_mark_1' => $mark?->carry_mark_1,
                        'carry_mark_2' => $mark?->carry_mark_2,
                        'total' => $mark?->total,
                    ];
                });
        }

        return view('admin.subjects.marks', compact('subject', 'academicPeriods', 'period', 'students'));
    }
}
