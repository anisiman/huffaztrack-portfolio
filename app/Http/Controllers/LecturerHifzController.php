<?php

namespace App\Http\Controllers;

use App\Models\KhatamRecord;
use App\Models\Lecturer;
use App\Models\Muqarrar;
use App\Services\NotificationService;
use App\Models\Student;
use App\Models\Tasmi;
use App\Models\TasmiProgress;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LecturerHifzController extends Controller
{
    public function index(Request $request): View
    {
        $selectedDate    = $request->filled('date')
            ? Carbon::parse($request->query('date'))->startOfDay()
            : Carbon::today();
        $search          = trim((string) $request->query('search', ''));
        $selectedHalaqah = trim((string) $request->query('halaqah', ''));
        $selectedStatus  = trim((string) $request->query('status', ''));

        $lecturerId = Lecturer::where('user_id', Auth::id())->value('lecturer_id');

        // Load all students for this lecturer (with their batch)
        $students = Student::query()
            ->operational()
            ->with('batch')
            ->when(
                $lecturerId,
                fn ($q) => $q->where('lecturer_id', $lecturerId),
                fn ($q) => $q->whereRaw('1 = 0')
            )
            ->when($search !== '', fn ($q) => $q->where(function ($q2) use ($search) {
                $q2->where('student_name', 'like', "%{$search}%")
                   ->orWhere('student_id',   'like', "%{$search}%");
            }))
            ->when($selectedHalaqah !== '', fn ($q) => $q->whereHas('batch', fn ($b) => $b->where('batch_name', $selectedHalaqah)))
            ->orderBy('student_name')
            ->get();

        // Collect distinct batch IDs for this class
        $batchIds = $students->pluck('batch_id')->filter()->unique()->values();

        // Load muqarrars for these batches (batch-specific first, semester-wide as fallback)
        $allMuqarrars = Muqarrar::whereNotNull('week_start_date')
            ->where(fn ($q) => $q->whereIn('batch_id', $batchIds)->orWhereNull('batch_id'))
            ->orderBy('week_start_date')
            ->get();

        // ─── Helpers ──────────────────────────────────────────────────────────

        // Find the muqarrar for a student's batch on the selected date.
        // Tries batch-specific first; falls back to null-batch (semester-wide) records.
        $getMuqarrarForBatch = function (?int $batchId) use ($allMuqarrars, $selectedDate): ?Muqarrar {
            $subset = $batchId
                ? $allMuqarrars->where('batch_id', $batchId)
                : collect();

            if ($subset->isEmpty()) {
                $subset = $allMuqarrars->whereNull('batch_id');
            }

            // Return the most recently started muqarrar whose week_start_date <= selected date.
            // This ensures a new week takes effect immediately on its start date rather than
            // waiting for the previous week's 7-day window to expire.
            $match = $subset
                ->filter(fn ($m) => Carbon::parse($m->week_start_date)->startOfDay()->lte($selectedDate))
                ->sortByDesc('week_start_date')
                ->first();

            // Fallback: if selected date is before all muqarrars, show the earliest one.
            return $match ?? $subset->sortBy('week_start_date')->first();
        };

        // Compute the daily checkpoint for a muqarrar on the selected date.
        $getCheckpoint = function (?Muqarrar $muqarrar) use ($selectedDate): int {
            if (! $muqarrar) return 0;

            $totalDays = 5;
            if ($muqarrar->week_start_date) {
                $ws       = Carbon::parse($muqarrar->week_start_date)->startOfDay();
                $sel      = Carbon::parse($selectedDate->format('Y-m-d'))->startOfDay();
                $diffDays = (int) $ws->diffInDays($sel, false);
                $dayOfWeek = max(1, min($totalDays, $diffDays + 1));
            } else {
                $dayOfWeek = max(1, min($totalDays, $selectedDate->dayOfWeekIso));
            }

            $weekly = (int) $muqarrar->end_page - (int) $muqarrar->start_page;
            return (int) min(
                (int) $muqarrar->end_page,
                (int) round((int) $muqarrar->start_page + ($weekly / $totalDays) * $dayOfWeek)
            );
        };

        // ─── Build per-batch context for the banner ────────────────────────────

        $totalLearningDays = 5;

        $batchContexts = $batchIds->map(function ($batchId) use (
            $students, $getMuqarrarForBatch, $getCheckpoint, $selectedDate, $totalLearningDays
        ): object {
            $batchName = $students->firstWhere('batch_id', $batchId)?->batch?->batch_name ?? (string) $batchId;
            $muqarrar  = $getMuqarrarForBatch($batchId);
            $checkpoint = $getCheckpoint($muqarrar);

            $dayOfWeek       = 0;
            $dailyRangeStart = 0;
            $dailyRangeEnd   = 0;

            if ($muqarrar) {
                if ($muqarrar->week_start_date) {
                    $ws       = Carbon::parse($muqarrar->week_start_date)->startOfDay();
                    $sel      = Carbon::parse($selectedDate->format('Y-m-d'))->startOfDay();
                    $diffDays = (int) $ws->diffInDays($sel, false);
                    $dayOfWeek = max(1, min($totalLearningDays, $diffDays + 1));
                } else {
                    $dayOfWeek = max(1, min($totalLearningDays, $selectedDate->dayOfWeekIso));
                }

                $weekly      = (int) $muqarrar->end_page - (int) $muqarrar->start_page;
                $dailyInc    = $weekly / $totalLearningDays;
                $prevCheckpoint  = $dayOfWeek > 1
                    ? (int) round((int) $muqarrar->start_page + $dailyInc * ($dayOfWeek - 1))
                    : (int) $muqarrar->start_page;
                $dailyRangeStart = $prevCheckpoint + 1;
                $dailyRangeEnd   = $checkpoint;
            }

            return (object) [
                'batch_name'        => $batchName,
                'muqarrar'          => $muqarrar,
                'checkpoint'        => $checkpoint,
                'dayOfWeek'         => $dayOfWeek,
                'totalLearningDays' => $totalLearningDays,
                'dailyRangeStart'   => $dailyRangeStart,
                'dailyRangeEnd'     => $dailyRangeEnd,
            ];
        })->values();

        // ─── Tasmi records ─────────────────────────────────────────────────────

        $studentIds = $students->pluck('student_id');

        // Scoped by student only (not tasmi.lecturer_id): after a halaqah is
        // transferred, the new lecturer must see records the previous one made.
        $tasmiToday = Tasmi::with('progress')
            ->whereIn('student_id', $studentIds)
            ->whereDate('tasmi_date', $selectedDate)
            ->get()
            ->keyBy('student_id');

        $prevTasmi = Tasmi::with('progress')
            ->whereIn('student_id', $studentIds)
            ->whereDate('tasmi_date', '<', $selectedDate)
            ->orderBy('tasmi_date', 'desc')
            ->get()
            ->unique('student_id')
            ->keyBy('student_id');

        $khatamCounts = KhatamRecord::countsAsOf($studentIds, $selectedDate);

        // ─── Build roster ──────────────────────────────────────────────────────

        $roster = $students->map(function (Student $student) use (
            $tasmiToday, $prevTasmi, $getMuqarrarForBatch, $getCheckpoint, $khatamCounts
        ) {
            $khatamCount = $khatamCounts->get($student->student_id, 0);

            // Each student's muqarrar is determined by their own batch_id
            $studentMuqarrar   = $getMuqarrarForBatch($student->batch_id);
            $studentCheckpoint = $getCheckpoint($studentMuqarrar);
            $studentEndPage    = (int) ($studentMuqarrar?->end_page   ?? 0);
            $studentStartPage  = (int) ($studentMuqarrar?->start_page ?? 0);

            $tasmi       = $tasmiToday->get($student->student_id);
            $pageReached = (int) ($tasmi?->progress?->page_reached ?? 0);
            $hasRecord   = $tasmi !== null && $pageReached > 0;

            $prevRecord  = $prevTasmi->get($student->student_id);
            $prevPage    = (int) ($prevRecord?->progress?->page_reached ?? 0);

            $displayPage = $hasRecord ? $pageReached : $prevPage;

            if (! $hasRecord) {
                $status = 'Not Recorded';
            } elseif ($khatamCount > 0) {
                // Already khatam — Muqarrar fulfilled; current page is revision, not compared.
                $status = 'Completed';
            } elseif ($studentCheckpoint > 0 && $studentCheckpoint === $studentEndPage && $pageReached >= $studentEndPage) {
                // Final checkpoint reached — student completed the weekly target
                $status = 'Completed';
            } elseif ($studentCheckpoint > 0 && $pageReached > $studentCheckpoint) {
                $status = 'Ahead';
            } elseif ($studentCheckpoint > 0 && $pageReached === $studentCheckpoint) {
                $status = 'On-track';
            } else {
                $status = 'Behind';
            }

            $remaining = ($studentEndPage > 0 && $hasRecord && $khatamCount === 0) ? max(0, $studentEndPage - $pageReached) : null;

            return (object) [
                'student_id'   => $student->student_id,
                'student_name' => $student->student_name,
                'batch_name'   => $student->batch?->batch_name ?? '-',
                'tasmi_id'     => $tasmi?->tasmi_id,
                'page_reached' => $displayPage > 0 ? $displayPage : '',
                'prev_page'    => $prevPage,
                'remaining'    => $remaining,
                'start_page'   => $studentStartPage,
                'end_page'     => $studentEndPage,
                'checkpoint'   => $studentCheckpoint,
                'muqarrar_id'  => $studentMuqarrar?->muqarrar_id,
                'has_record'   => $hasRecord,
                'status'       => $status,
                'khatam_count' => $khatamCount,
            ];
        });

        // Sort by batch name first, then student name, so groups appear together
        $roster = $roster->sortBy([
            ['batch_name', 'asc'],
            ['student_name', 'asc'],
        ])->values();

        // Apply status filter after sorting — supports a comma-separated list (e.g. the
        // "Ahead / Completed" summary card links with "Ahead,Completed") as well as the
        // single-value options in the status dropdown.
        $statusFilterValues = $selectedStatus !== '' ? explode(',', $selectedStatus) : [];
        $records = $selectedStatus !== ''
            ? $roster->filter(fn ($r) => in_array($r->status, $statusFilterValues, true))->values()
            : $roster;

        // Summary counts (unfiltered)
        $completedCount   = $roster->whereIn('status', ['Ahead', 'Completed'])->count();
        $onTrackCount     = $roster->where('status', 'On-track')->count();
        $behindCount      = $roster->where('status', 'Behind')->count();
        $notRecordedCount = $roster->where('status', 'Not Recorded')->count();

        $halaqahOptions = Student::query()
            ->operational()
            ->with('batch')
            ->when(
                $lecturerId,
                fn ($q) => $q->where('lecturer_id', $lecturerId),
                fn ($q) => $q->whereRaw('1 = 0')
            )
            ->get()
            ->map(fn (Student $student) => $student->batch?->batch_name)
            ->filter()
            ->sort()
            ->unique()
            ->values();

        return view('lecturer.hifz', compact(
            'records',
            'batchContexts',
            'halaqahOptions',
            'search',
            'selectedDate',
            'selectedHalaqah',
            'selectedStatus',
            'lecturerId',
            'completedCount',
            'onTrackCount',
            'behindCount',
            'notRecordedCount',
        ));
    }

    public function saveAll(Request $request): RedirectResponse
    {
        $lecturerId = Lecturer::where('user_id', Auth::id())->value('lecturer_id');
        $date       = $request->input('date', Carbon::today()->format('Y-m-d'));
        $rows       = $request->input('rows', []);

        DB::transaction(function () use ($rows, $lecturerId, $date) {
            foreach ($rows as $studentId => $data) {
                $isActiveAssignedStudent = Student::query()
                    ->operational()
                    ->where('student_id', $studentId)
                    ->where('lecturer_id', $lecturerId)
                    ->exists();

                if (! $isActiveAssignedStudent) {
                    continue;
                }

                $pageReached = (int) ($data['page_reached'] ?? 0);
                $prevPage    = (int) ($data['prev_page']    ?? 0);
                $muqarrarId  = ($data['muqarrar_id'] ?? null) ?: null;

                if ($pageReached <= 0) {
                    continue;
                }

                // One tasmi per student per day, whoever recorded it — so editing
                // a day the previous lecturer already recorded updates it in place.
                $tasmi = Tasmi::where('student_id', $studentId)
                    ->whereDate('tasmi_date', $date)
                    ->first();

                // Skip pre-filled unchanged page only when the lecturer did not explicitly edit the field.
                // The dirty flag is set to 1 by the client-side JS when the user types in the input.
                $isDirty = ! empty($data['dirty']);
                if (! $isDirty && ! $tasmi && $prevPage > 0 && $pageReached === $prevPage) {
                    continue;
                }

                if ($tasmi) {
                    if ($tasmi->progress) {
                        $tasmi->progress->update(['page_reached' => $pageReached]);
                    } else {
                        TasmiProgress::create(['tasmi_id' => $tasmi->tasmi_id, 'page_reached' => $pageReached]);
                    }
                } else {
                    $tasmi = Tasmi::create([
                        'student_id'  => $studentId,
                        'lecturer_id' => $lecturerId,
                        'muqarrar_id' => $muqarrarId,
                        'tasmi_date'  => $date,
                    ]);
                    TasmiProgress::create(['tasmi_id' => $tasmi->tasmi_id, 'page_reached' => $pageReached]);
                }

                // Notify on weekly completion
                $muqarrar = $muqarrarId ? Muqarrar::find($muqarrarId) : null;
                if ($muqarrar && (int) $muqarrar->end_page > 0 && $pageReached >= (int) $muqarrar->end_page) {
                    NotificationService::notifyStudent($studentId, 'hafazan', 'Weekly Target Completed', "Congratulations! You have completed this week's hafazan target (page {$muqarrar->end_page}).", '/student/hafazan');
                    $student = Student::find($studentId);
                    $lecturerUserId = Lecturer::where('lecturer_id', $lecturerId)->value('user_id');
                    if ($lecturerUserId && $student) {
                        NotificationService::send($lecturerUserId, 'hafazan', 'Student Completed Muqarrar', "{$student->student_name} has completed this week's hafazan target.", '/lecturer/hifz');
                    }
                }
            }
        });

        return redirect()
            ->route('lecturer.hifz', ['date' => $date])
            ->with('success', 'Records saved successfully.');
    }

    public function studentHistory(Request $request): \Illuminate\Http\JsonResponse
    {
        $studentId         = $request->query('student_id', '');
        $lecturerId        = Lecturer::where('user_id', Auth::id())->value('lecturer_id');
        $totalLearningDays = 5;

        $isActiveAssignedStudent = Student::query()
            ->operational()
            ->where('student_id', $studentId)
            ->when(
                $lecturerId,
                fn ($q) => $q->where('lecturer_id', $lecturerId),
                fn ($q) => $q->whereRaw('1 = 0')
            )
            ->exists();

        if (! $isActiveAssignedStudent) {
            return response()->json([]);
        }

        $tasmis = Tasmi::with(['progress', 'muqarrar'])
            ->where('student_id', $studentId)
            ->orderBy('tasmi_date', 'desc')
            ->get();

        $khatamDates = KhatamRecord::where('student_id', $studentId)->pluck('khatam_date');

        $history = $tasmis->map(function ($t) use ($totalLearningDays, $khatamDates) {
            $muq       = $t->muqarrar;
            $tasmiDate = $t->tasmi_date ? Carbon::parse($t->tasmi_date)->startOfDay() : null;

            $histCheckpoint = 0;
            if ($muq && $muq->week_start_date && $tasmiDate) {
                $ws       = Carbon::parse($muq->week_start_date)->startOfDay();
                $sel      = Carbon::parse($tasmiDate->format('Y-m-d'))->startOfDay();
                $diffDays = (int) $ws->diffInDays($sel, false);
                $rawDay   = max(1, min($totalLearningDays, $diffDays + 1));
                $weekly   = (int) $muq->end_page - (int) $muq->start_page;
                $histCheckpoint = (int) min(
                    (int) $muq->end_page,
                    (int) round((int) $muq->start_page + ($weekly / $totalLearningDays) * $rawDay)
                );
            }

            $pageReached = (int) ($t->progress?->page_reached ?? 0);
            $endPage     = (int) ($muq?->end_page ?? 0);
            $isKhatam    = $tasmiDate && $khatamDates->contains(fn ($d) => $d->lte($tasmiDate));

            if ($pageReached <= 0) {
                $status = 'Not Recorded';
            } elseif ($isKhatam) {
                $status = 'Completed';
            } elseif ($histCheckpoint > 0 && $histCheckpoint === $endPage && $pageReached >= $endPage) {
                $status = 'Completed';
            } elseif ($histCheckpoint > 0 && $pageReached > $histCheckpoint) {
                $status = 'Ahead';
            } elseif ($histCheckpoint > 0 && $pageReached === $histCheckpoint) {
                $status = 'On-track';
            } else {
                $status = 'Behind';
            }

            return [
                'date'       => $tasmiDate ? $tasmiDate->format('d M Y') : '-',
                'reached'    => $pageReached > 0 ? $pageReached : '-',
                'checkpoint' => $histCheckpoint ?: '-',
                'remaining'  => ($endPage > 0 && $pageReached > 0 && ! $isKhatam) ? max(0, $endPage - $pageReached) : null,
                'status'     => $status,
            ];
        })->values();

        return response()->json($history);
    }
}
