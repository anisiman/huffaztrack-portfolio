<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BatchController extends Controller
{
    /**
     * Display batch listing.
     */
    public function index(): View
    {
        $batches = Batch::withCount([
            'students',

            'students as active_students_count' => function ($query) {
                $query->where('status', 'AKTIF');
            },

            'students as completed_students_count' => function ($query) {
                $query->where('status', 'TAMAT PENSIJILAN');
            },

            'students as dropped_students_count' => function ($query) {
                $query->whereIn('status', [
                    'GAGAL DAN DIBERHENTIKAN',
                    'TARIK DIRI'
                ]);
            }
        ])
        ->orderByRaw('CAST(SUBSTRING(batch_name, 2) AS UNSIGNED) ASC')
        ->get();

        return view('admin.batch.index', compact('batches'));
    }

    /**
     * Store new batch.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'batch_name' => 'required|string|max:50|unique:batch,batch_name',
            'intake' => 'required|string|max:50',
        ]);

        Batch::create($validated);

        return redirect()
            ->route('admin.batches.index')
            ->with('success', 'Batch created successfully.');
    }

    /**
     * Update batch. Toggling "is active" off/on also auto-completes/restores
     * that batch's students, since only active batches take subjects and
     * carry ongoing progress — see the deactivate/reactivate logic below.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $batch = Batch::findOrFail($id);

        $validated = $request->validate([
            'batch_name' => 'required|string|max:50|unique:batch,batch_name,' . $id . ',batch_id',
            'intake' => 'required|string|max:50',
        ]);

        $wasActive = $batch->is_active;
        $isActive  = $request->boolean('is_active');
        $affectedCount = 0;

        DB::transaction(function () use ($batch, $validated, $wasActive, $isActive, &$affectedCount) {
            $batch->update(array_merge($validated, ['is_active' => $isActive]));

            if ($wasActive && ! $isActive) {
                // Deactivating: auto-complete currently active students, remembering
                // which ones so a later reactivation only restores those, not
                // students who completed/dropped for unrelated reasons.
                $activeStudentIds = Student::where('batch_id', $batch->batch_id)
                    ->where('status', Student::STATUS_ACTIVE)
                    ->pluck('student_id');

                if ($activeStudentIds->isNotEmpty()) {
                    Student::whereIn('student_id', $activeStudentIds)
                        ->update(['status' => Student::STATUS_COMPLETED]);

                    $batch->update(['auto_completed_students' => $activeStudentIds->values()->all()]);
                    $affectedCount = $activeStudentIds->count();
                }
            } elseif (! $wasActive && $isActive) {
                // Reactivating: restore only the students this batch auto-completed,
                // and only if their status hasn't been changed manually since.
                $autoCompletedIds = collect($batch->auto_completed_students ?? []);

                if ($autoCompletedIds->isNotEmpty()) {
                    $affectedCount = Student::whereIn('student_id', $autoCompletedIds)
                        ->where('status', Student::STATUS_COMPLETED)
                        ->update(['status' => Student::STATUS_ACTIVE]);
                }

                $batch->update(['auto_completed_students' => null]);
            }
        });

        $message = 'Batch updated successfully.';
        if ($wasActive && ! $isActive && $affectedCount > 0) {
            $message = "Batch marked inactive. {$affectedCount} active student(s) moved to \"Tamat Pensijilan\" status.";
        } elseif (! $wasActive && $isActive && $affectedCount > 0) {
            $message = "Batch marked active. {$affectedCount} student(s) restored to \"Aktif\" status.";
        }

        return redirect()
            ->route('admin.batches.index')
            ->with('success', $message);
    }

    /**
     * Delete batch.
     */
    public function destroy($id): RedirectResponse
    {
        $batch = Batch::withCount('students')->findOrFail($id);

        /* PREVENT DELETE IF STUDENTS EXIST */
        if ($batch->students_count > 0) {

            return redirect()
                ->route('admin.batches.index')
                ->with('error', 'Cannot delete batch with registered students.');
        }

        $batch->delete();

        return redirect()
            ->route('admin.batches.index')
            ->with('success', 'Batch deleted successfully.');
    }
}