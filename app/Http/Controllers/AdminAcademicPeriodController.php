<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\Batch;
use App\Models\BatchSemesterLevel;
use App\Models\HalaqahAssignment;
use App\Models\Semester;
use App\Models\Student;
use App\Models\WarningThreshold;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminAcademicPeriodController extends Controller
{
    /**
     * Display the academic period listing, refreshing auto-computed statuses first.
     */
    public function index(): View
    {
        AcademicPeriod::recalculateAll();

        $periods = AcademicPeriod::withCount('halaqahAssignments')->with('warningThreshold')->orderByDesc('start_date')->get();

        $batches = Batch::where('is_active', true)
            ->orderByRaw('CAST(SUBSTRING(batch_name, 2) AS UNSIGNED) ASC')
            ->get();
        $semesterLevels = Semester::orderBy('semester_id')->get();

        // Existing batch-level mappings, keyed by period then by batch, for the modal's JS to prefill.
        $batchLevelsByPeriod = BatchSemesterLevel::all()
            ->groupBy('academic_period_id')
            ->map(fn ($rows) => $rows->keyBy('batch_id')->map->semester_id);

        return view('admin.academic-periods.index', compact(
            'periods', 'batches', 'semesterLevels', 'batchLevelsByPeriod'
        ));
    }

    /**
     * Store a new academic period, then auto-clone the previous period's
     * Halaqah assignments into it so the new period doesn't start empty.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:academic_periods,name',
            'academic_year' => 'required|string|max:20',
            'period_number' => [
                'required', 'integer', 'min:1', 'max:3',
                function ($attr, $value, $fail) use ($request) {
                    $exists = AcademicPeriod::where('academic_year', $request->input('academic_year'))
                        ->where('period_number', $value)
                        ->exists();
                    if ($exists) {
                        $fail('This academic year already has a semester with that number.');
                    }
                },
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:upcoming,active,completed',
        ]);

        $clonedCount = 0;

        $period = DB::transaction(function () use ($validated, $request, &$clonedCount) {
            $period = AcademicPeriod::create([
                'name' => $validated['name'],
                'academic_year' => $validated['academic_year'],
                'period_number' => $validated['period_number'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'upcoming',
                'status_locked' => false,
            ]);

            $this->applyStatus($period, $request, $validated);

            $clonedCount = $this->cloneHalaqahFromPrevious($period);

            return $period;
        });

        $message = "Academic semester \"{$period->name}\" created successfully.";
        if ($clonedCount > 0) {
            $message .= " {$clonedCount} Halaqah assignment(s) copied from the previous semester.";
        }

        return redirect()
            ->route('admin.academic-periods.index')
            ->with('success', $message);
    }

    /**
     * Copy the immediately-preceding period's Halaqah assignments into the
     * new period, so admins start from the prior roster and only edit what
     * changed. The previous period's own rows are never touched. If there's
     * no previous period with recorded assignments (e.g. this is the first
     * period ever created, adopted after live lecturer_id links already
     * existed), fall back to seeding from current live data instead of
     * leaving the new period empty.
     */
    private function cloneHalaqahFromPrevious(AcademicPeriod $period): int
    {
        $previous = $period->previous();
        $rows = $previous
            ? HalaqahAssignment::where('academic_period_id', $previous->id)->get()
            : collect();

        if ($rows->isEmpty()) {
            return $this->seedHalaqahFromLive($period);
        }

        $now = now();
        $inserts = $rows->map(fn ($row) => [
            'academic_period_id' => $period->id,
            'student_id' => $row->student_id,
            'lecturer_id' => $row->lecturer_id,
            'student_name_snapshot' => $row->student_name_snapshot,
            'lecturer_name_snapshot' => $row->lecturer_name_snapshot,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        HalaqahAssignment::insert($inserts);

        return count($inserts);
    }

    /**
     * Seed a period's Halaqah assignments from whatever lecturer_id links
     * currently exist live on Student — used both as the clone fallback
     * above and as the manual "seed from live" action below, for adopting
     * this feature into a system that already has real assignments.
     */
    private function seedHalaqahFromLive(AcademicPeriod $period): int
    {
        $students = Student::whereNotNull('lecturer_id')->with('lecturer')->get();
        if ($students->isEmpty()) {
            return 0;
        }

        $now = now();
        $inserts = $students->map(fn ($student) => [
            'academic_period_id' => $period->id,
            'student_id' => $student->student_id,
            'lecturer_id' => $student->lecturer_id,
            'student_name_snapshot' => $student->student_name,
            'lecturer_name_snapshot' => $student->lecturer->lecturer_name ?? '',
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        HalaqahAssignment::insert($inserts);

        return count($inserts);
    }

    /**
     * Manually snapshot current live lecturer-student links into a period —
     * for adopting this feature after real assignments already exist.
     * Blocked once the period has any assignments, to avoid silently
     * clobbering history with whatever is live right now.
     */
    public function seedFromLive(AcademicPeriod $academicPeriod): RedirectResponse
    {
        if (HalaqahAssignment::where('academic_period_id', $academicPeriod->id)->exists()) {
            return redirect()
                ->route('admin.academic-periods.index')
                ->with('error', 'This semester already has Halaqah assignments recorded — seeding again would risk overwriting history.');
        }

        $count = $this->seedHalaqahFromLive($academicPeriod);

        $message = $count > 0
            ? "{$count} current lecturer-student link(s) copied into \"{$academicPeriod->name}\"."
            : 'No live lecturer-student links were found to copy.';

        return redirect()->route('admin.academic-periods.index')->with('success', $message);
    }

    /**
     * Update an academic period's details and/or status override.
     */
    public function update(Request $request, AcademicPeriod $academicPeriod): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:academic_periods,name,' . $academicPeriod->id,
            'academic_year' => 'required|string|max:20',
            'period_number' => [
                'required', 'integer', 'min:1', 'max:3',
                function ($attr, $value, $fail) use ($request, $academicPeriod) {
                    $exists = AcademicPeriod::where('academic_year', $request->input('academic_year'))
                        ->where('period_number', $value)
                        ->where('id', '!=', $academicPeriod->id)
                        ->exists();
                    if ($exists) {
                        $fail('This academic year already has a semester with that number.');
                    }
                },
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:upcoming,active,completed',
        ]);

        $academicPeriod->update([
            'name' => $validated['name'],
            'academic_year' => $validated['academic_year'],
            'period_number' => $validated['period_number'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->applyStatus($academicPeriod, $request, $validated);

        return redirect()
            ->route('admin.academic-periods.index')
            ->with('success', "Academic semester \"{$academicPeriod->name}\" updated successfully.");
    }

    /**
     * Delete an academic period. Blocked once it has Halaqah history —
     * the FK is set to cascade-delete for data-hygiene reasons, but silently
     * losing a semester's assignment history on delete would defeat the
     * point of tracking it in the first place.
     */
    public function destroy(AcademicPeriod $academicPeriod): RedirectResponse
    {
        if (HalaqahAssignment::where('academic_period_id', $academicPeriod->id)->exists()) {
            return redirect()
                ->route('admin.academic-periods.index')
                ->with('error', 'Cannot delete a semester that has Halaqah assignments recorded. Remove its assignments first if you really want to delete it.');
        }

        $academicPeriod->delete();

        return redirect()
            ->route('admin.academic-periods.index')
            ->with('success', 'Academic semester deleted successfully.');
    }

    /**
     * Create or update a period's Warning Letter thresholds. Independent per
     * period — saving one semester's config never touches another's row.
     */
    public function updateThresholds(Request $request, AcademicPeriod $academicPeriod): RedirectResponse
    {
        $validated = $request->validate([
            'warning_1_threshold' => 'required|integer|min:1',
            'warning_2_threshold' => 'required|integer|gt:warning_1_threshold',
            'warning_3_threshold' => 'required|integer|gt:warning_2_threshold',
        ]);

        WarningThreshold::updateOrCreate(
            ['academic_period_id' => $academicPeriod->id],
            $validated
        );

        return redirect()
            ->route('admin.academic-periods.index')
            ->with('success', "Warning Letter thresholds for \"{$academicPeriod->name}\" updated successfully.");
    }

    /**
     * Set/update/clear which Semester Level each Batch is at for this
     * Academic Semester. This mapping is the source of truth that determines
     * which subjects a batch's students take (see Student::subjectsForPeriod).
     */
    public function updateBatchLevels(Request $request, AcademicPeriod $academicPeriod): RedirectResponse
    {
        $validated = $request->validate([
            'levels' => 'nullable|array',
            'levels.*.batch_id' => 'required|exists:batch,batch_id',
            'levels.*.semester_id' => 'nullable|exists:semester,semester_id',
        ]);

        DB::transaction(function () use ($validated, $academicPeriod) {
            foreach ($validated['levels'] ?? [] as $row) {
                if (empty($row['semester_id'])) {
                    BatchSemesterLevel::where('academic_period_id', $academicPeriod->id)
                        ->where('batch_id', $row['batch_id'])
                        ->delete();
                    continue;
                }

                BatchSemesterLevel::updateOrCreate(
                    ['academic_period_id' => $academicPeriod->id, 'batch_id' => $row['batch_id']],
                    ['semester_id' => $row['semester_id']]
                );
            }
        });

        return redirect()
            ->route('admin.academic-periods.index')
            ->with('success', "Batch levels for \"{$academicPeriod->name}\" updated successfully.");
    }

    /**
     * Apply the requested status: a manual override (locked) if the admin
     * checked "override status", otherwise unlock and let dates drive it.
     */
    private function applyStatus(AcademicPeriod $period, Request $request, array $validated): void
    {
        if ($request->boolean('override_status') && ! empty($validated['status'])) {
            if ($validated['status'] === 'active') {
                $period->activate();
            } else {
                $period->update(['status' => $validated['status'], 'status_locked' => true]);
            }
        } else {
            $period->update(['status_locked' => false]);
            AcademicPeriod::recalculateAll();
        }
    }
}
