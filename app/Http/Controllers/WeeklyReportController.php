<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\Attendance;
use App\Models\Batch;
use App\Models\KhatamRecord;
use App\Models\Lecturer;
use App\Models\Muqarrar;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WeeklyReportController extends Controller
{
    // ─── Hub ──────────────────────────────────────────────────────────────────

    public function lecturerHub(Request $request): View
    {
        $lecturerId = Lecturer::where('user_id', Auth::id())->value('lecturer_id');

        // Show weeks for this lecturer's students' batches
        $batchIds = Student::operational()
            ->where('lecturer_id', $lecturerId)
            ->pluck('batch_id')
            ->filter()
            ->unique();

        $allWeeks = Muqarrar::whereNotNull('week_start_date')
            ->where(fn ($q) => $q->whereIn('batch_id', $batchIds)->orWhereNull('batch_id'))
            ->orderBy('week_start_date')
            ->get();

        $today          = Carbon::today();
        $months         = $this->buildMonthList($today);
        $periodOptions   = $this->buildAttendancePeriodOptions();
        $currentPeriodId = AcademicPeriod::current()?->id;
        $currentPeriodId = $currentPeriodId ? (string) $currentPeriodId : null;

        return view('lecturer.reports-hub', compact('allWeeks', 'months', 'today', 'periodOptions', 'currentPeriodId'));
    }

    // ─── Admin Hafazan Hub ────────────────────────────────────────────────────

    public function adminHafazanHub(Request $request): View
    {
        $today          = Carbon::today();
        $allWeeks       = Muqarrar::whereNotNull('week_start_date')->orderBy('week_start_date')->get();
        $months         = $this->buildMonthList($today);
        $periodOptions   = $this->buildAttendancePeriodOptions();
        $currentPeriodId = AcademicPeriod::current()?->id;
        $currentPeriodId = $currentPeriodId ? (string) $currentPeriodId : null;
        $section         = $request->query('section', '');
        $halaqahOptions  = Batch::active()->orderedByName()->pluck('batch_name');
        $currentBatch    = $request->query('batch', 'Overall');
        if ($currentBatch !== 'Overall' && ! $halaqahOptions->contains($currentBatch)) {
            $currentBatch = 'Overall';
        }
        return view('admin.hafazan-hub', compact('allWeeks', 'months', 'today', 'periodOptions', 'currentPeriodId', 'section', 'halaqahOptions', 'currentBatch'));
    }

    public function adminDailyIndex(Request $request): View
    {
        $today         = Carbon::today();
        $date          = Carbon::parse($request->input('date', $today->toDateString()))->startOfDay();
        $months        = $this->buildMonthList($today);
        $filterHalaqah = trim($request->input('halaqah', ''));

        $students   = Student::with(['batch', 'lecturer'])
            ->operational()
            ->when($filterHalaqah, fn ($q) => $q->whereHas('batch', fn ($b) => $b->where('batch_name', $filterHalaqah)))
            ->orderBy('student_name')
            ->get();
        $studentIds = $students->pluck('student_id');

        // Best page per student up to (and including) the selected date — matches
        // lecturer daily logic (cumulative, not just that single day).
        $pageMap = collect();
        if ($studentIds->isNotEmpty()) {
            $pageMap = DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '<=', $date->format('Y-m-d'))
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as best'))
                ->groupBy('t.student_id')
                ->get()
                ->keyBy('student_id');
        }

        // Per-batch muqarrar covering the selected date.
        $batchIds = $students->pluck('batch_id')->unique()->filter()->values()->all();
        $allMuqarrars = collect();
        if (! empty($batchIds)) {
            $allMuqarrars = Muqarrar::whereNotNull('week_start_date')
                ->where('week_start_date', '<=', $date->toDateString())
                ->whereIn('batch_id', $batchIds)
                ->orderBy('week_start_date', 'desc')
                ->get();
        }

        $batchMuqMap = [];
        foreach ($allMuqarrars->groupBy('batch_id') as $batchId => $rows) {
            $muq = $rows->first(function ($m) use ($date) {
                $ws = Carbon::parse($m->week_start_date)->startOfDay();
                return $date->between($ws, $ws->copy()->addDays(6)->endOfDay());
            }) ?? $rows->first();
            $batchMuqMap[$batchId] = $muq;
        }

        // Proportional intra-week checkpoint — same formula as LecturerAnalyticsController.
        $chkptFn = function ($muq, Carbon $d): int {
            if (! $muq) return 0;
            $ws = Carbon::parse($muq->week_start_date)->startOfDay();
            $we = $ws->copy()->addDays(6)->endOfDay();
            if ($d->isAfter($we)) return (int) $muq->end_page;
            $diff = (int) $ws->diffInDays($d->copy()->startOfDay(), false);
            $day  = max(1, min(5, $diff + 1));
            $w    = (int) $muq->end_page - (int) $muq->start_page;
            return (int) min((int) $muq->end_page, (int) round((int) $muq->start_page + ($w / 5) * $day));
        };

        // System-wide status counts — same classification as lecturer daily section.
        // Khatam students have already fulfilled the Muqarrar — recorded = Completed.
        $khatam = KhatamRecord::countsAsOf($studentIds, $date);

        $hafDailyCounts = ['Completed' => 0, 'On Track' => 0, 'Behind' => 0, 'Not Recorded' => 0];
        foreach ($students as $s) {
            $muq  = $batchMuqMap[$s->batch_id] ?? null;
            $end  = (int) ($muq?->end_page ?? 0);
            $chk  = $chkptFn($muq, $date);
            $page = (int) ($pageMap->get($s->student_id)?->best ?? 0);
            if ($page <= 0)                     $hafDailyCounts['Not Recorded']++;
            elseif ($khatam->has($s->student_id)) $hafDailyCounts['Completed']++;
            elseif ($end > 0 && $page >= $end)  $hafDailyCounts['Completed']++;
            elseif ($chk > 0 && $page >= $chk)  $hafDailyCounts['On Track']++;
            else                                $hafDailyCounts['Behind']++;
        }

        // Primary muqarrar for header context (batch with most students).
        $primaryBatchId = $students->groupBy('batch_id')->map->count()->sortDesc()->keys()->first();
        $hafDailyMuq    = $batchMuqMap[$primaryBatchId] ?? null;
        $hafDailyChk    = $chkptFn($hafDailyMuq, $date);

        // Per-lecturer summaries with full status breakdown, sorted by batch then name.
        $lecturerSummaries = $students
            ->groupBy('lecturer_id')
            ->map(function ($group) use ($pageMap, $batchMuqMap, $chkptFn, $date, $khatam) {
                $total   = $group->count();
                $batches = $group->map(fn ($s) => $s->batch?->batch_name)->filter()->unique()->sort()->values();
                $cnt     = ['Completed' => 0, 'On Track' => 0, 'Behind' => 0, 'Not Recorded' => 0];
                foreach ($group as $s) {
                    $muq  = $batchMuqMap[$s->batch_id] ?? null;
                    $end  = (int) ($muq?->end_page ?? 0);
                    $chk  = $chkptFn($muq, $date);
                    $page = (int) ($pageMap->get($s->student_id)?->best ?? 0);
                    if ($page <= 0)                    $cnt['Not Recorded']++;
                    elseif ($khatam->has($s->student_id)) $cnt['Completed']++;
                    elseif ($end > 0 && $page >= $end) $cnt['Completed']++;
                    elseif ($chk > 0 && $page >= $chk) $cnt['On Track']++;
                    else                               $cnt['Behind']++;
                }
                $recorded = $total - $cnt['Not Recorded'];
                return (object) [
                    'lecturer_id'   => $group->first()->lecturer_id,
                    'lecturer_name' => $group->first()->lecturer?->lecturer_name ?? '—',
                    'batch_name'    => $batches->implode(', ') ?: '—',
                    'total'         => $total,
                    'completed'     => $cnt['Completed'],
                    'on_track'      => $cnt['On Track'],
                    'behind'        => $cnt['Behind'],
                    'not_recorded'  => $cnt['Not Recorded'],
                    'recorded'      => $recorded,
                    'coverage_pct'  => $total > 0 ? round($recorded / $total * 100) : 0,
                ];
            })
            ->values()
            ->sortBy('lecturer_name')
            ->values();

        $halaqahOptions = Batch::active()->orderedByName()->pluck('batch_name');

        return view('admin.hafazan-daily-report', compact(
            'date', 'today', 'months',
            'lecturerSummaries',
            'hafDailyCounts', 'hafDailyMuq', 'hafDailyChk',
            'filterHalaqah', 'halaqahOptions'
        ));
    }

    public function adminHafazanDailyLecturerDetail(Request $request, int $id): View
    {
        $lecturer = Lecturer::find($id);
        if (! $lecturer) abort(404);

        $today = Carbon::today();
        $date  = Carbon::parse($request->input('date', $today->toDateString()))->startOfDay();

        $students   = Student::with(['batch'])->operational()->where('lecturer_id', $id)->orderBy('student_name')->get();
        $studentIds = $students->pluck('student_id');

        // Per-batch muqarrar — same approach as adminDailyIndex.
        $batchIds = $students->pluck('batch_id')->unique()->filter()->values()->all();
        $allMuqarrars = collect();
        if (! empty($batchIds)) {
            $allMuqarrars = Muqarrar::whereNotNull('week_start_date')
                ->where('week_start_date', '<=', $date->toDateString())
                ->whereIn('batch_id', $batchIds)
                ->orderBy('week_start_date', 'desc')
                ->get();
        }

        $batchMuqMap = [];
        foreach ($allMuqarrars->groupBy('batch_id') as $batchId => $muqRows) {
            $muq = $muqRows->first(function ($m) use ($date) {
                $ws = Carbon::parse($m->week_start_date)->startOfDay();
                return $date->between($ws, $ws->copy()->addDays(6)->endOfDay());
            }) ?? $muqRows->first();
            $batchMuqMap[$batchId] = $muq;
        }

        $chkptFn = function ($muq, Carbon $d): int {
            if (! $muq) return 0;
            $ws = Carbon::parse($muq->week_start_date)->startOfDay();
            $we = $ws->copy()->addDays(6)->endOfDay();
            if ($d->isAfter($we)) return (int) $muq->end_page;
            $diff = (int) $ws->diffInDays($d->copy()->startOfDay(), false);
            $day  = max(1, min(5, $diff + 1));
            $w    = (int) $muq->end_page - (int) $muq->start_page;
            return (int) min((int) $muq->end_page, (int) round((int) $muq->start_page + ($w / 5) * $day));
        };

        $pageMap = collect();
        if ($studentIds->isNotEmpty()) {
            $pageMap = DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '<=', $date->format('Y-m-d'))
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as best'))
                ->groupBy('t.student_id')->get()->keyBy('student_id');
        }

        $primaryBatchId = $students->groupBy('batch_id')->map->count()->sortDesc()->keys()->first();
        $muqarrar       = $batchMuqMap[$primaryBatchId] ?? null;
        $checkpoint     = $chkptFn($muqarrar, $date);
        $endPage        = (int) ($muqarrar?->end_page ?? 0);

        $khatam = KhatamRecord::countsAsOf($studentIds, $date);

        $rows = $students->map(function ($s) use ($batchMuqMap, $chkptFn, $date, $pageMap, $khatam) {
            $muq  = $batchMuqMap[$s->batch_id] ?? null;
            $end  = (int) ($muq?->end_page ?? 0);
            $chk  = $chkptFn($muq, $date);
            $page = (int) ($pageMap->get($s->student_id)?->best ?? 0);
            $isKhatam = $khatam->has($s->student_id);

            if ($page <= 0) {
                $status = 'Not Recorded'; $gap = null;
            } elseif ($isKhatam) {
                $status = 'Completed'; $gap = 0;
            } elseif ($end > 0 && $page >= $end) {
                $status = 'Completed'; $gap = $chk > 0 ? $chk - $page : 0;
            } elseif ($chk > 0 && $page >= $chk) {
                $status = 'On Track'; $gap = $chk - $page;
            } else {
                $status = 'Behind'; $gap = $chk > 0 ? $chk - $page : null;
            }

            return (object) [
                'student_id'  => $s->student_id,
                'student_name'=> $s->student_name,
                'batch_name'  => $s->batch?->batch_name ?? '—',
                'latest_page' => $page > 0 ? $page : null,
                'checkpoint'  => $chk > 0 ? $chk : null,
                'gap'         => $page > 0 && $chk > 0 && ! $isKhatam ? $chk - $page : null,
                'status'      => $status,
                'has_record'  => $page > 0,
                'gauge_pct'   => $isKhatam && $page > 0 ? 100 : ($end > 0 && $page > 0 ? min(100, (int) round($page / $end * 100)) : 0),
                'khatam_count'=> $khatam->get($s->student_id, 0),
            ];
        });

        return view('admin.hafazan-daily-report-lecturer', [
            'rows'             => $rows,
            'date'             => $date,
            'today'            => $today,
            'muqarrar'         => $muqarrar,
            'checkpoint'       => $checkpoint,
            'endPage'          => $endPage,
            'lecturerName'     => $lecturer->lecturer_name,
            'lecturerPhone'    => $lecturer->phone ?? null,
            'lecturerId'       => $id,
            'completedCount'   => $rows->where('status', 'Completed')->count(),
            'onTrackCount'     => $rows->where('status', 'On Track')->count(),
            'behindCount'      => $rows->where('status', 'Behind')->count(),
            'notRecordedCount' => $rows->where('status', 'Not Recorded')->count(),
            'total'            => $rows->count(),
        ]);
    }

    public function adminMonthlyIndex(Request $request): View
    {
        $today         = Carbon::today();
        $monthStr      = $request->input('month', $today->format('Y-m'));
        $filterHalaqah = trim($request->input('halaqah', ''));
        $monthStart    = Carbon::parse($monthStr . '-01')->startOfMonth();
        $monthEnd      = $monthStart->copy()->endOfMonth();
        $effectiveDate = $today->isAfter($monthEnd) ? $monthEnd->copy() : $today->copy();
        $months        = $this->buildMonthList($today);

        $students = Student::with(['batch', 'lecturer'])
            ->operational()
            ->when($filterHalaqah, fn ($q) => $q->whereHas('batch', fn ($b) => $b->where('batch_name', $filterHalaqah)))
            ->orderBy('student_name')
            ->get();
        $studentIds = $students->pluck('student_id');

        // Per-batch muqarrar covering effectiveDate — same approach as daily admin report.
        $batchIds = $students->pluck('batch_id')->unique()->filter()->values()->all();
        $allMuqarrars = collect();
        if (! empty($batchIds)) {
            $allMuqarrars = Muqarrar::whereNotNull('week_start_date')
                ->where('week_start_date', '<=', $effectiveDate->toDateString())
                ->whereIn('batch_id', $batchIds)
                ->orderBy('week_start_date', 'desc')
                ->get();
        }

        $batchMuqMap = [];
        foreach ($allMuqarrars->groupBy('batch_id') as $batchId => $rows) {
            $muq = $rows->first(function ($m) use ($effectiveDate) {
                $ws = Carbon::parse($m->week_start_date)->startOfDay();
                return $effectiveDate->between($ws, $ws->copy()->addDays(6)->endOfDay());
            }) ?? $rows->first();
            $batchMuqMap[$batchId] = $muq;
        }

        // Checkpoint formula — identical to buildHafazanMonthlyData.
        $chkptFn = function ($muq, Carbon $d): int {
            if (! $muq) return 0;
            $ws = Carbon::parse($muq->week_start_date)->startOfDay();
            $we = $ws->copy()->addDays(6)->endOfDay();
            if ($d->isAfter($we)) return (int) $muq->end_page;
            $diff = (int) $ws->diffInDays($d->copy()->startOfDay(), false);
            $day  = max(1, min(5, $diff + 1));
            $w    = (int) $muq->end_page - (int) $muq->start_page;
            return (int) min((int) $muq->end_page, (int) round((int) $muq->start_page + ($w / 5) * $day));
        };

        // Latest page per student up to effectiveDate.
        $latestPageMap = collect();
        if ($studentIds->isNotEmpty()) {
            $latestPageMap = DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '<=', $effectiveDate->toDateString())
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as best_page'))
                ->groupBy('t.student_id')
                ->get()
                ->keyBy('student_id');
        }

        // Best page within this month — determines has_record.
        $bestInMonthMap = collect();
        if ($studentIds->isNotEmpty()) {
            $bestInMonthMap = DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '>=', $monthStart->toDateString())
                ->whereDate('t.tasmi_date', '<=', $effectiveDate->toDateString())
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as best_page'))
                ->groupBy('t.student_id')
                ->get()
                ->keyBy('student_id');
        }

        // Classify each student — exact same logic as buildHafazanMonthlyData.
        $khatam = KhatamRecord::countsAsOf($studentIds, $effectiveDate);

        $classifyStudent = function ($s) use ($batchMuqMap, $chkptFn, $effectiveDate, $latestPageMap, $bestInMonthMap, $khatam): string {
            $muq       = $batchMuqMap[$s->batch_id] ?? null;
            $endPage   = (int) ($muq?->end_page ?? 0);
            $chk       = $chkptFn($muq, $effectiveDate);
            $hasRecord = $bestInMonthMap->has($s->student_id);
            $rawPage   = $latestPageMap->get($s->student_id)?->best_page ?? null;
            $latest    = $rawPage !== null ? (int) $rawPage : null;

            if (! $hasRecord)                                        return 'Not Recorded';
            if ($khatam->has($s->student_id))                        return 'Completed';
            if ($endPage > 0 && $latest !== null && $latest >= $endPage) return 'Completed';
            $gap = ($chk > 0 && $latest !== null) ? ($chk - $latest) : 0;
            return $gap <= 0 ? 'On Track' : 'Behind';
        };

        // System-wide KPI counts.
        $completedCount   = 0; $onTrackCount = 0; $behindCount = 0; $notRecordedCount = 0;
        foreach ($students as $s) {
            match ($classifyStudent($s)) {
                'Completed'    => $completedCount++,
                'On Track'     => $onTrackCount++,
                'Behind'       => $behindCount++,
                default        => $notRecordedCount++,
            };
        }

        // Primary muqarrar for banner context (batch with most students).
        $primaryBatchId  = $students->groupBy('batch_id')->map->count()->sortDesc()->keys()->first();
        $primaryMuqarrar = $batchMuqMap[$primaryBatchId] ?? null;

        // Per-lecturer summaries with full status breakdown.
        $lecturerSummaries = $students
            ->groupBy('lecturer_id')
            ->map(function ($group) use ($classifyStudent) {
                $total   = $group->count();
                $batches = $group->map(fn ($s) => $s->batch?->batch_name)->filter()->unique()->sort()->values();
                $cnt     = ['Completed' => 0, 'On Track' => 0, 'Behind' => 0, 'Not Recorded' => 0];
                foreach ($group as $s) {
                    $cnt[$classifyStudent($s)]++;
                }
                return (object) [
                    'lecturer_id'   => $group->first()->lecturer_id,
                    'lecturer_name' => $group->first()->lecturer?->lecturer_name ?? '—',
                    'batch_name'    => $batches->implode(', ') ?: '—',
                    'total'         => $total,
                    'completed'     => $cnt['Completed'],
                    'on_track'      => $cnt['On Track'],
                    'behind'        => $cnt['Behind'],
                    'not_recorded'  => $cnt['Not Recorded'],
                    'progress_pct'  => $total > 0 ? round(($cnt['Completed'] + $cnt['On Track']) / $total * 100) : 0,
                ];
            })
            ->values()
            ->sortBy('lecturer_name')
            ->values();

        $halaqahOptions = Batch::active()->orderedByName()->pluck('batch_name');

        return view('admin.hafazan-monthly-report', compact(
            'monthStr', 'monthStart', 'monthEnd', 'today', 'months',
            'filterHalaqah', 'halaqahOptions', 'primaryMuqarrar',
            'completedCount', 'onTrackCount', 'behindCount', 'notRecordedCount',
            'lecturerSummaries'
        ));
    }

    public function adminHafazanDailyPdf(Request $request)
    {
        $today         = Carbon::today();
        $date          = Carbon::parse($request->input('date', $today->toDateString()))->startOfDay();
        $filterHalaqah = trim($request->input('halaqah', ''));

        $students   = Student::with(['batch', 'lecturer'])
            ->operational()
            ->when($filterHalaqah, fn ($q) => $q->whereHas('batch', fn ($b) => $b->where('batch_name', $filterHalaqah)))
            ->orderBy('student_name')
            ->get();
        $studentIds = $students->pluck('student_id');

        $pageMap = collect();
        if ($studentIds->isNotEmpty()) {
            $pageMap = DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '<=', $date->format('Y-m-d'))
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as best'))
                ->groupBy('t.student_id')
                ->get()
                ->keyBy('student_id');
        }

        $batchIds = $students->pluck('batch_id')->unique()->filter()->values()->all();
        $allMuqarrars = collect();
        if (! empty($batchIds)) {
            $allMuqarrars = Muqarrar::whereNotNull('week_start_date')
                ->where('week_start_date', '<=', $date->toDateString())
                ->whereIn('batch_id', $batchIds)
                ->orderBy('week_start_date', 'desc')
                ->get();
        }

        $batchMuqMap = [];
        foreach ($allMuqarrars->groupBy('batch_id') as $batchId => $rows) {
            $muq = $rows->first(function ($m) use ($date) {
                $ws = Carbon::parse($m->week_start_date)->startOfDay();
                return $date->between($ws, $ws->copy()->addDays(6)->endOfDay());
            }) ?? $rows->first();
            $batchMuqMap[$batchId] = $muq;
        }

        $chkptFn = function ($muq, Carbon $d): int {
            if (! $muq) return 0;
            $ws = Carbon::parse($muq->week_start_date)->startOfDay();
            $we = $ws->copy()->addDays(6)->endOfDay();
            if ($d->isAfter($we)) return (int) $muq->end_page;
            $diff = (int) $ws->diffInDays($d->copy()->startOfDay(), false);
            $day  = max(1, min(5, $diff + 1));
            $w    = (int) $muq->end_page - (int) $muq->start_page;
            return (int) min((int) $muq->end_page, (int) round((int) $muq->start_page + ($w / 5) * $day));
        };

        // Khatam students have already fulfilled the Muqarrar — recorded = Completed.
        $khatam = KhatamRecord::countsAsOf($studentIds, $date);

        $hafDailyCounts = ['Completed' => 0, 'On Track' => 0, 'Behind' => 0, 'Not Recorded' => 0];
        foreach ($students as $s) {
            $muq  = $batchMuqMap[$s->batch_id] ?? null;
            $end  = (int) ($muq?->end_page ?? 0);
            $chk  = $chkptFn($muq, $date);
            $page = (int) ($pageMap->get($s->student_id)?->best ?? 0);
            if ($page <= 0)                    $hafDailyCounts['Not Recorded']++;
            elseif ($khatam->has($s->student_id)) $hafDailyCounts['Completed']++;
            elseif ($end > 0 && $page >= $end) $hafDailyCounts['Completed']++;
            elseif ($chk > 0 && $page >= $chk) $hafDailyCounts['On Track']++;
            else                               $hafDailyCounts['Behind']++;
        }

        $primaryBatchId = $students->groupBy('batch_id')->map->count()->sortDesc()->keys()->first();
        $hafDailyMuq    = $batchMuqMap[$primaryBatchId] ?? null;
        $hafDailyChk    = $chkptFn($hafDailyMuq, $date);

        $lecturerSummaries = $students
            ->groupBy('lecturer_id')
            ->map(function ($group) use ($pageMap, $batchMuqMap, $chkptFn, $date, $khatam) {
                $total   = $group->count();
                $batches = $group->map(fn ($s) => $s->batch?->batch_name)->filter()->unique()->sort()->values();
                $cnt     = ['Completed' => 0, 'On Track' => 0, 'Behind' => 0, 'Not Recorded' => 0];
                foreach ($group as $s) {
                    $muq  = $batchMuqMap[$s->batch_id] ?? null;
                    $end  = (int) ($muq?->end_page ?? 0);
                    $chk  = $chkptFn($muq, $date);
                    $page = (int) ($pageMap->get($s->student_id)?->best ?? 0);
                    if ($page <= 0)                    $cnt['Not Recorded']++;
                    elseif ($khatam->has($s->student_id)) $cnt['Completed']++;
                    elseif ($end > 0 && $page >= $end) $cnt['Completed']++;
                    elseif ($chk > 0 && $page >= $chk) $cnt['On Track']++;
                    else                               $cnt['Behind']++;
                }
                $recorded = $total - $cnt['Not Recorded'];
                return (object) [
                    'lecturer_name' => $group->first()->lecturer?->lecturer_name ?? '—',
                    'batch_name'    => $batches->implode(', ') ?: '—',
                    'total'         => $total,
                    'completed'     => $cnt['Completed'],
                    'on_track'      => $cnt['On Track'],
                    'behind'        => $cnt['Behind'],
                    'not_recorded'  => $cnt['Not Recorded'],
                    'coverage_pct'  => $total > 0 ? round($recorded / $total * 100) : 0,
                ];
            })
            ->values()
            ->sortBy(function ($row) {
                preg_match('/(\d+)/', $row->batch_name, $m);
                $num = isset($m[1]) ? (int) $m[1] : 9999;
                return sprintf('%05d_%s', $num, strtolower($row->lecturer_name));
            })
            ->values();

        $data                = compact('date', 'today', 'hafDailyCounts', 'hafDailyMuq', 'hafDailyChk', 'lecturerSummaries');
        $data['generatedAt'] = Carbon::now()->format('d M Y, h:i A');
        $pdf = Pdf::loadView('pdf.admin-hafazan-daily-report', $data)->setPaper('a4', 'landscape');
        return $pdf->download('admin-hafazan-daily-' . $date->format('Y-m-d') . '.pdf');
    }

    public function adminHafazanMonthlyPdf(Request $request)
    {
        $today         = Carbon::today();
        $monthStr      = $request->input('month', $today->format('Y-m'));
        $monthStart    = Carbon::parse($monthStr . '-01')->startOfMonth();
        $monthEnd      = $monthStart->copy()->endOfMonth();
        $effectiveDate = $today->isAfter($monthEnd) ? $monthEnd->copy() : $today->copy();

        $students   = Student::with(['batch', 'lecturer'])->operational()->orderBy('student_name')->get();
        $studentIds = $students->pluck('student_id');

        $batchIds = $students->pluck('batch_id')->unique()->filter()->values()->all();
        $allMuqarrars = collect();
        if (! empty($batchIds)) {
            $allMuqarrars = Muqarrar::whereNotNull('week_start_date')
                ->where('week_start_date', '<=', $effectiveDate->toDateString())
                ->whereIn('batch_id', $batchIds)
                ->orderBy('week_start_date', 'desc')
                ->get();
        }

        $batchMuqMap = [];
        foreach ($allMuqarrars->groupBy('batch_id') as $batchId => $rows) {
            $muq = $rows->first(function ($m) use ($effectiveDate) {
                $ws = Carbon::parse($m->week_start_date)->startOfDay();
                return $effectiveDate->between($ws, $ws->copy()->addDays(6)->endOfDay());
            }) ?? $rows->first();
            $batchMuqMap[$batchId] = $muq;
        }

        $chkptFn = function ($muq, Carbon $d): int {
            if (! $muq) return 0;
            $ws = Carbon::parse($muq->week_start_date)->startOfDay();
            $we = $ws->copy()->addDays(6)->endOfDay();
            if ($d->isAfter($we)) return (int) $muq->end_page;
            $diff = (int) $ws->diffInDays($d->copy()->startOfDay(), false);
            $day  = max(1, min(5, $diff + 1));
            $w    = (int) $muq->end_page - (int) $muq->start_page;
            return (int) min((int) $muq->end_page, (int) round((int) $muq->start_page + ($w / 5) * $day));
        };

        $latestPageMap = collect();
        if ($studentIds->isNotEmpty()) {
            $latestPageMap = DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '<=', $effectiveDate->toDateString())
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as best_page'))
                ->groupBy('t.student_id')
                ->get()
                ->keyBy('student_id');
        }

        $bestInMonthMap = collect();
        if ($studentIds->isNotEmpty()) {
            $bestInMonthMap = DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '>=', $monthStart->toDateString())
                ->whereDate('t.tasmi_date', '<=', $effectiveDate->toDateString())
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as best_page'))
                ->groupBy('t.student_id')
                ->get()
                ->keyBy('student_id');
        }

        $khatam = KhatamRecord::countsAsOf($studentIds, $effectiveDate);

        $classifyStudent = function ($s) use ($batchMuqMap, $chkptFn, $effectiveDate, $latestPageMap, $bestInMonthMap, $khatam): string {
            $muq       = $batchMuqMap[$s->batch_id] ?? null;
            $endPage   = (int) ($muq?->end_page ?? 0);
            $chk       = $chkptFn($muq, $effectiveDate);
            $hasRecord = $bestInMonthMap->has($s->student_id);
            $rawPage   = $latestPageMap->get($s->student_id)?->best_page ?? null;
            $latest    = $rawPage !== null ? (int) $rawPage : null;
            if (! $hasRecord)                                             return 'Not Recorded';
            if ($khatam->has($s->student_id))                             return 'Completed';
            if ($endPage > 0 && $latest !== null && $latest >= $endPage) return 'Completed';
            $gap = ($chk > 0 && $latest !== null) ? ($chk - $latest) : 0;
            return $gap <= 0 ? 'On Track' : 'Behind';
        };

        $completedCount = 0; $onTrackCount = 0; $behindCount = 0; $notRecordedCount = 0;
        foreach ($students as $s) {
            match ($classifyStudent($s)) {
                'Completed' => $completedCount++,
                'On Track'  => $onTrackCount++,
                'Behind'    => $behindCount++,
                default     => $notRecordedCount++,
            };
        }

        $lecturerSummaries = $students
            ->groupBy('lecturer_id')
            ->map(function ($group) use ($classifyStudent) {
                $total   = $group->count();
                $batches = $group->map(fn ($s) => $s->batch?->batch_name)->filter()->unique()->sort()->values();
                $cnt     = ['Completed' => 0, 'On Track' => 0, 'Behind' => 0, 'Not Recorded' => 0];
                foreach ($group as $s) { $cnt[$classifyStudent($s)]++; }
                return (object) [
                    'lecturer_name' => $group->first()->lecturer?->lecturer_name ?? '—',
                    'batch_name'    => $batches->implode(', ') ?: '—',
                    'total'         => $total,
                    'completed'     => $cnt['Completed'],
                    'on_track'      => $cnt['On Track'],
                    'behind'        => $cnt['Behind'],
                    'not_recorded'  => $cnt['Not Recorded'],
                    'progress_pct'  => $total > 0 ? round(($cnt['Completed'] + $cnt['On Track']) / $total * 100) : 0,
                ];
            })
            ->values()
            ->sortBy(function ($row) {
                preg_match('/(\d+)/', $row->batch_name, $m);
                $num = isset($m[1]) ? (int) $m[1] : 9999;
                return sprintf('%05d_%s', $num, strtolower($row->lecturer_name));
            })
            ->values();

        $data                = compact('monthStr', 'monthStart', 'monthEnd', 'completedCount', 'onTrackCount', 'behindCount', 'notRecordedCount', 'lecturerSummaries');
        $data['generatedAt'] = Carbon::now()->format('d M Y, h:i A');
        $pdf = Pdf::loadView('pdf.admin-hafazan-monthly-report', $data)->setPaper('a4', 'landscape');
        return $pdf->download('admin-hafazan-monthly-' . $monthStr . '.pdf');
    }

    public function adminHafazanMonthlyLecturerDetail(Request $request, int $id): View
    {
        $lecturer = Lecturer::find($id);
        if (! $lecturer) abort(404);

        // Reuse lecturer monthly logic exactly — same as buildHafazanMonthlyData
        // but scoped to a specific lecturer (admin context, no Auth dependency).
        $today         = Carbon::today();
        $monthStr      = $request->input('month', $today->format('Y-m'));
        $filterHalaqah = trim($request->input('halaqah', ''));
        $monthStart    = Carbon::parse($monthStr . '-01')->startOfMonth();
        $monthEnd      = $monthStart->copy()->endOfMonth();
        $effectiveDate = $today->isAfter($monthEnd) ? $monthEnd->copy() : $today->copy();

        $students = Student::with('batch')
            ->operational()
            ->where('lecturer_id', $id)
            ->when($filterHalaqah, fn ($q) => $q->whereHas('batch', fn ($b) => $b->where('batch_name', $filterHalaqah)))
            ->orderBy('student_name')
            ->get();
        $studentIds = $students->pluck('student_id');

        // Match the summary: every student's status must use their own batch's
        // Muqarrar, rather than one arbitrary Muqarrar for the selected date.
        $batchIds = $students->pluck('batch_id')->unique()->filter()->values()->all();
        $allMuqarrars = collect();
        if (! empty($batchIds)) {
            $allMuqarrars = Muqarrar::whereNotNull('week_start_date')
                ->where('week_start_date', '<=', $effectiveDate->toDateString())
                ->whereIn('batch_id', $batchIds)
                ->orderBy('week_start_date', 'desc')
                ->get();
        }

        $batchMuqMap = [];
        foreach ($allMuqarrars->groupBy('batch_id') as $batchId => $muqarrars) {
            $batchMuqMap[$batchId] = $muqarrars->first(function ($muqarrar) use ($effectiveDate) {
                $weekStart = Carbon::parse($muqarrar->week_start_date)->startOfDay();

                return $effectiveDate->between($weekStart, $weekStart->copy()->addDays(6)->endOfDay());
            }) ?? $muqarrars->first();
        }

        $checkpointFor = function ($muqarrar, Carbon $date): int {
            if (! $muqarrar) {
                return 0;
            }

            $weekStart = Carbon::parse($muqarrar->week_start_date)->startOfDay();
            $weekEnd   = $weekStart->copy()->addDays(6)->endOfDay();
            if ($date->isAfter($weekEnd)) {
                return (int) $muqarrar->end_page;
            }

            $day    = max(1, min(5, (int) $weekStart->diffInDays($date->copy()->startOfDay(), false) + 1));
            $weekly = (int) $muqarrar->end_page - (int) $muqarrar->start_page;

            return (int) min(
                (int) $muqarrar->end_page,
                (int) round((int) $muqarrar->start_page + ($weekly / 5) * $day)
            );
        };

        $latestPageMap = collect();
        if ($studentIds->isNotEmpty()) {
            $latestPageMap = DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '<=', $effectiveDate->toDateString())
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as best_page'))
                ->groupBy('t.student_id')->get()->keyBy('student_id');
        }

        $bestInMonthMap = collect();
        if ($studentIds->isNotEmpty()) {
            $bestInMonthMap = DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '>=', $monthStart->toDateString())
                ->whereDate('t.tasmi_date', '<=', $effectiveDate->toDateString())
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as best_page'))
                ->groupBy('t.student_id')->get()->keyBy('student_id');
        }

        $pageBeforeMonthMap = collect();
        if ($studentIds->isNotEmpty()) {
            $pageBeforeMonthMap = DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '<', $monthStart->toDateString())
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as page_before'))
                ->groupBy('t.student_id')->get()->keyBy('student_id');
        }

        $khatam = KhatamRecord::countsAsOf($studentIds, $effectiveDate);

        $rows = $students->map(function (Student $s) use (
            $latestPageMap, $bestInMonthMap, $pageBeforeMonthMap, $batchMuqMap, $checkpointFor, $effectiveDate, $khatam
        ) {
            $muqarrar  = $batchMuqMap[$s->batch_id] ?? null;
            $endPage   = (int) ($muqarrar?->end_page ?? 0);
            $checkpoint = $checkpointFor($muqarrar, $effectiveDate);
            $hasRecord  = $bestInMonthMap->has($s->student_id);
            $rawPage    = $latestPageMap->get($s->student_id)?->best_page ?? null;
            $latestPage = $rawPage !== null ? (int) $rawPage : null;
            $pagesThisMonth = null;
            if ($hasRecord) {
                $bestInMonth    = (int) $bestInMonthMap->get($s->student_id)->best_page;
                $pageBefore     = $pageBeforeMonthMap->has($s->student_id)
                    ? (int) $pageBeforeMonthMap->get($s->student_id)->page_before : 0;
                $pagesThisMonth = max(0, $bestInMonth - $pageBefore);
            }
            if (! $hasRecord) {
                $status = 'Not Recorded'; $color = 'gray';
            } elseif ($khatam->has($s->student_id)) {
                $status = 'Completed'; $color = 'green';
            } elseif ($endPage > 0 && $latestPage >= $endPage) {
                $status = 'Completed'; $color = 'green';
            } else {
                $gap = ($checkpoint > 0 && $latestPage !== null) ? ($checkpoint - $latestPage) : 0;
                if      ($gap <= 0)  { $status = 'On Track'; $color = 'blue'; }
                elseif  ($gap <= 5)  { $status = 'Behind';   $color = 'yellow'; }
                elseif  ($gap <= 10) { $status = 'Behind';   $color = 'orange'; }
                else                 { $status = 'Behind';   $color = 'red'; }
            }
            return (object) [
                'student_id'       => $s->student_id,
                'student_name'     => $s->student_name,
                'batch_name'       => $s->batch?->batch_name ?? '—',
                'latest_page'      => $latestPage,
                'pages_this_month' => $pagesThisMonth,
                'has_record'       => $hasRecord,
                'status'           => $status,
                'color'            => $color,
            ];
        });

        $halaqahOptions = Student::query()->operational()->with('batch')
            ->where('lecturer_id', $id)->get()
            ->map(fn ($s) => $s->batch?->batch_name)->filter()->sort()->unique()->values();

        $months = $this->buildMonthList($today);

        return view('admin.hafazan-monthly-report-lecturer', [
            'rows'             => $rows,
            'monthStart'       => $monthStart,
            'monthEnd'         => $monthEnd,
            'monthStr'         => $monthStr,
            'today'            => $today,
            'months'           => $months,
            'filterHalaqah'    => $filterHalaqah,
            'halaqahOptions'   => $halaqahOptions,
            'completedCount'   => $rows->where('status', 'Completed')->count(),
            'onTrackCount'     => $rows->where('status', 'On Track')->count(),
            'behindCount'      => $rows->where('status', 'Behind')->count(),
            'notRecordedCount' => $rows->where('status', 'Not Recorded')->count(),
            'lecturerName'     => $lecturer->lecturer_name,
            'lecturerPhone'    => $lecturer->phone ?? null,
            'lecturerId'       => $id,
        ]);
    }

    // ─── Admin Attendance ─────────────────────────────────────────────────────

    public function adminAttendanceDaily(Request $request): View
    {
        $data = $this->buildAdminAttendanceDailyData($request);
        $rows = $data['rows'];

        // Per-lecturer summaries for the overview table.
        $lecturerSummaries = $rows
            ->groupBy('lecturer_id')
            ->map(function ($group) {
                $total   = $group->count();
                $present = $group->filter(fn ($r) => $r->status === 'present')->count();
                $absent  = $group->filter(fn ($r) => $r->status === 'absent')->count();
                $excused = $group->filter(fn ($r) => $r->status === 'absent_with_reason')->count();
                $noRec   = $total - $present - $absent - $excused;
                $rec     = $present + $absent + $excused;
                $batches = $group->pluck('batch_name')->filter(fn ($b) => $b !== '—')->unique()->sort()->values();
                return (object) [
                    'lecturer_id'   => $group->first()->lecturer_id,
                    'lecturer_name' => $group->first()->lecturer_name,
                    'batch_name'    => $batches->implode(', ') ?: '—',
                    'total'         => $total,
                    'present'       => $present,
                    'absent'        => $absent,
                    'excused'       => $excused,
                    'no_record'     => $noRec,
                    'att_rate'      => $rec > 0 ? round($present / $rec * 100) : null,
                ];
            })
            ->values()
            ->sortBy('lecturer_name')
            ->values();

        $data['lecturerSummaries'] = $lecturerSummaries;
        return view('admin.attendance-daily-report', $data);
    }

    public function adminAttendanceDailyLecturerDetail(Request $request, int $id): View
    {
        $lecturer = Lecturer::find($id);
        if (! $lecturer) abort(404);

        $data = $this->buildAdminAttendanceDailyData($request);
        $rows = $data['rows']->filter(fn ($r) => $r->lecturer_id === $id)->values();

        $presentCount = $rows->filter(fn ($r) => $r->status === 'present')->count();
        $absentCount  = $rows->filter(fn ($r) => $r->status === 'absent')->count();
        $excusedCount = $rows->filter(fn ($r) => $r->status === 'absent_with_reason')->count();
        $recorded     = $presentCount + $absentCount + $excusedCount;

        return view('admin.attendance-daily-report-lecturer', [
            'rows'           => $rows,
            'date'           => $data['date'],
            'today'          => $data['today'],
            'lecturerName'   => $lecturer->lecturer_name,
            'lecturerPhone'  => $lecturer->phone ?? null,
            'lecturerId'     => $id,
            'presentCount'   => $presentCount,
            'absentCount'    => $absentCount,
            'excusedCount'   => $excusedCount,
            'attendanceRate' => $recorded > 0 ? round($presentCount / $recorded * 100) : null,
        ]);
    }

    public function adminAttendanceDailyPdf(Request $request)
    {
        $data                = $this->buildAdminAttendanceDailyData($request);
        $data['generatedAt'] = Carbon::now()->format('d M Y, h:i A');
        $pdf = Pdf::loadView('pdf.attendance-daily-report', $data)->setPaper('a4');
        return $pdf->download('admin-attendance-daily-' . $data['date']->format('Y-m-d') . '.pdf');
    }

    public function adminAttendanceWeekly(Request $request): View
    {
        $data = $this->buildAdminAttendanceWeeklyData($request);
        $rows = $data['rows'];

        // Per-lecturer summaries for the overview table.
        $lecturerSummaries = $rows
            ->groupBy('lecturer_id')
            ->map(function ($group) {
                $present = $group->sum('present');
                $absent  = $group->sum('absent');
                $excused = $group->sum('excused');
                $rec     = $present + $absent + $excused;
                $batches = $group->pluck('batch_name')->filter(fn ($b) => $b !== '—')->unique()->sort()->values();
                return (object) [
                    'lecturer_id'   => $group->first()->lecturer_id,
                    'lecturer_name' => $group->first()->lecturer_name,
                    'batch_name'    => $batches->implode(', ') ?: '—',
                    'total'         => $group->count(),
                    'present'       => $present,
                    'absent'        => $absent,
                    'excused'       => $excused,
                    'weekly_rate'   => $rec > 0 ? round($present / $rec * 100) : null,
                ];
            })
            ->values()
            ->sortBy('lecturer_name')
            ->values();

        $data['lecturerSummaries'] = $lecturerSummaries;
        return view('admin.attendance-weekly-report', $data);
    }

    public function adminAttendanceWeeklyLecturerDetail(Request $request, int $id): View
    {
        $lecturer = Lecturer::find($id);
        if (! $lecturer) abort(404);

        $data = $this->buildAdminAttendanceWeeklyData($request);
        $rows = $data['rows']->filter(fn ($r) => $r->lecturer_id === $id)->values();

        $totalStudents      = $rows->count();
        $totalPresent       = $rows->sum('present');
        $totalAbsent        = $rows->sum('absent');
        $totalExcused       = $rows->sum('excused');
        $totalSessions      = $totalPresent + $totalAbsent + $totalExcused;
        $overallPresentRate = $totalSessions > 0 ? round($totalPresent / $totalSessions * 100) : null;
        $thRate             = $totalSessions > 0 ? round($totalAbsent  / $totalSessions * 100) : null;
        $thsRate            = $totalSessions > 0 ? round($totalExcused / $totalSessions * 100) : null;
        $perfectCount       = $rows->filter(fn ($r) => $r->is_perfect)->count();
        $thStudentCount     = $rows->filter(fn ($r) => $r->has_th)->count();
        $thsStudentCount    = $rows->filter(fn ($r) => $r->has_ths)->count();

        // Filter attendanceDetails to this lecturer's students only.
        $lecturerStudentIds = $rows->pluck('student_id')->flip()->all();
        $attDetails = array_intersect_key($data['attendanceDetails'], $lecturerStudentIds);

        $weekLabel = $data['weekStart']->format('d M') . ' – ' . $data['weekEnd']->format('d M Y');

        return view('admin.attendance-weekly-report-lecturer', [
            'rows'               => $rows,
            'weekStart'          => $data['weekStart'],
            'weekEnd'            => $data['weekEnd'],
            'weekLabel'          => $weekLabel,
            'today'              => $data['today'],
            'lecturerName'       => $lecturer->lecturer_name,
            'lecturerPhone'      => $lecturer->phone ?? null,
            'lecturerId'         => $id,
            'totalStudents'      => $totalStudents,
            'overallPresentRate' => $overallPresentRate,
            'thRate'             => $thRate,
            'thsRate'            => $thsRate,
            'perfectCount'       => $perfectCount,
            'thStudentCount'     => $thStudentCount,
            'thsStudentCount'    => $thsStudentCount,
            'attendanceDetails'  => $attDetails,
        ]);
    }

    public function adminAttendanceWeeklyPdf(Request $request)
    {
        $data                = $this->buildAdminAttendanceWeeklyData($request);
        $data['generatedAt'] = Carbon::now()->format('d M Y, h:i A');
        $pdf = Pdf::loadView('pdf.attendance-weekly-report', $data)->setPaper('a4', 'landscape');
        return $pdf->download('admin-attendance-weekly-' . $data['weekStart']->format('Y-m-d') . '.pdf');
    }

    public function adminAttendanceMonthly(Request $request): View
    {
        $data = $this->buildAdminAttendanceMonthlyData($request);
        $rows = $data['rows'];

        $totalStudents      = $rows->count();
        $totalPresent       = $rows->sum('present');
        $totalAbsent        = $rows->sum('absent');
        $totalExcused       = $rows->sum('excused');
        $totalSessions      = $totalPresent + $totalAbsent + $totalExcused;
        $overallPresentRate = $totalSessions > 0 ? round($totalPresent / $totalSessions * 100) : null;
        $thRate             = $totalSessions > 0 ? round($totalAbsent  / $totalSessions * 100) : null;
        $thsRate            = $totalSessions > 0 ? round($totalExcused / $totalSessions * 100) : null;
        $perfectCount       = $rows->filter(fn ($r) => $r->is_perfect)->count();
        $thStudentCount     = $rows->filter(fn ($r) => $r->has_th)->count();
        $thsStudentCount    = $rows->filter(fn ($r) => $r->has_ths)->count();

        // Per-lecturer summaries for the overview table.
        $lecturerSummaries = $rows
            ->groupBy('lecturer_id')
            ->map(function ($group) {
                $present = $group->sum('present');
                $absent  = $group->sum('absent');
                $excused = $group->sum('excused');
                $rec     = $present + $absent + $excused;
                $batches = $group->pluck('batch_name')->filter(fn ($b) => $b !== '—')->unique()->sort()->values();
                return (object) [
                    'lecturer_id'    => $group->first()->lecturer_id,
                    'lecturer_name'  => $group->first()->lecturer_name,
                    'batch_name'     => $batches->implode(', ') ?: '—',
                    'total'          => $group->count(),
                    'present'        => $present,
                    'absent'         => $absent,
                    'excused'        => $excused,
                    'monthly_rate'   => $rec > 0 ? round($present / $rec * 100) : null,
                ];
            })
            ->values()
            ->sortBy('lecturer_name')
            ->values();

        return view('admin.attendance-monthly-report', array_merge($data, [
            'lecturerSummaries'  => $lecturerSummaries,
            'totalStudents'      => $totalStudents,
            'overallPresentRate' => $overallPresentRate,
            'thRate'             => $thRate,
            'thsRate'            => $thsRate,
            'perfectCount'       => $perfectCount,
            'thStudentCount'     => $thStudentCount,
            'thsStudentCount'    => $thsStudentCount,
        ]));
    }

    public function adminAttendanceMonthlyLecturerDetail(Request $request, int $id): View
    {
        $lecturer = Lecturer::find($id);
        if (! $lecturer) abort(404);

        $data = $this->buildAdminAttendanceMonthlyData($request);
        $rows = $data['rows']->filter(fn ($r) => $r->lecturer_id === $id)->values();

        $totalStudents      = $rows->count();
        $totalPresent       = $rows->sum('present');
        $totalAbsent        = $rows->sum('absent');
        $totalExcused       = $rows->sum('excused');
        $totalSessions      = $totalPresent + $totalAbsent + $totalExcused;
        $overallPresentRate = $totalSessions > 0 ? round($totalPresent / $totalSessions * 100) : null;
        $thRate             = $totalSessions > 0 ? round($totalAbsent  / $totalSessions * 100) : null;
        $thsRate            = $totalSessions > 0 ? round($totalExcused / $totalSessions * 100) : null;
        $perfectCount       = $rows->filter(fn ($r) => $r->is_perfect)->count();
        $thStudentCount     = $rows->filter(fn ($r) => $r->has_th)->count();
        $thsStudentCount    = $rows->filter(fn ($r) => $r->has_ths)->count();

        $lecturerStudentIds = $rows->pluck('student_id')->flip()->all();
        $attDetails = array_intersect_key($data['attendanceDetails'], $lecturerStudentIds);

        $halaqahOptions = $rows->pluck('batch_name')->filter(fn ($b) => $b !== '—')->unique()->sort()->values();

        return view('admin.attendance-monthly-report-lecturer', [
            'rows'               => $rows,
            'monthStart'         => $data['monthStart'],
            'monthEnd'           => $data['monthEnd'],
            'monthStr'           => $data['monthStr'],
            'months'             => $data['months'],
            'today'              => $data['today'],
            'lecturerName'       => $lecturer->lecturer_name,
            'lecturerPhone'      => $lecturer->phone ?? null,
            'lecturerId'         => $id,
            'totalStudents'      => $totalStudents,
            'overallPresentRate' => $overallPresentRate,
            'thRate'             => $thRate,
            'thsRate'            => $thsRate,
            'perfectCount'       => $perfectCount,
            'thStudentCount'     => $thStudentCount,
            'thsStudentCount'    => $thsStudentCount,
            'attendanceDetails'  => $attDetails,
            'filterHalaqah'      => '',
            'halaqahOptions'     => $halaqahOptions,
        ]);
    }

    public function adminAttendanceMonthlyPdf(Request $request)
    {
        $data                = $this->buildAdminAttendanceMonthlyData($request);
        $data['generatedAt'] = Carbon::now()->format('d M Y, h:i A');
        $pdf = Pdf::loadView('pdf.attendance-report', $data)->setPaper('a4');
        return $pdf->download('admin-attendance-monthly-' . $data['monthStr'] . '.pdf');
    }

    /**
     * Daily attendance register (Malay-language, one column per day of the
     * month) for the admin monthly attendance report — a second PDF format
     * kept alongside the existing summary PDF, same month/batch scope.
     */
    public function adminAttendanceMonthlyDailyRegisterPdf(Request $request)
    {
        // DomPDF keeps every table cell of the document in memory until it
        // finishes (~0.6 MB per student for a full month), so the register is
        // rendered one batch at a time. "All batches" becomes a ZIP of one PDF
        // per batch — which also matches the form's single Guru Tasmi' signature.
        // The raised limits are headroom for one unusually large batch.
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $monthStr      = $request->input('month', Carbon::today()->format('Y-m'));
        $monthStart    = Carbon::parse($monthStr . '-01')->startOfMonth();
        $monthEnd      = $monthStart->copy()->endOfMonth();
        $filterHalaqah = trim($request->input('halaqah', ''));
        $generatedAt   = Carbon::now()->format('d M Y, h:i A');

        $students = Student::with('batch:batch_id,batch_name')
            ->operational()
            ->when($filterHalaqah, fn ($q) => $q->whereHas('batch', fn ($b) => $b->where('batch_name', $filterHalaqah)))
            ->orderBy('student_name')
            ->get(['student_id', 'student_name', 'batch_id']);

        $attendances = Attendance::whereIn('student_id', $students->pluck('student_id'))
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get(['student_id', 'date', 'status'])
            ->groupBy('student_id');

        $byBatch = $students->groupBy(fn ($s) => $s->batch?->batch_name ?? 'Tiada Batch')->sortKeys();

        $render = fn (string $batchLabel, $batchStudents) => Pdf::loadView(
            'pdf.attendance-monthly-daily-register',
            $this->buildDailyRegisterData($batchStudents, $attendances, $monthStart, $monthEnd) + [
                'filterHalaqah' => $batchLabel,
                'generatedAt'   => $generatedAt,
            ]
        )->setPaper('a4', 'landscape')->output();

        if ($filterHalaqah !== '' || $byBatch->count() <= 1) {
            $label = $filterHalaqah !== '' ? $filterHalaqah : ($byBatch->keys()->first() ?? '');
            return response($render($label, $students), 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="rekod-kehadiran-harian-' . $monthStr . '.pdf"',
            ]);
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'rkh');
        $zip     = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::OVERWRITE);
        foreach ($byBatch as $batchName => $batchStudents) {
            $safeName = preg_replace('/[^A-Za-z0-9_-]+/', '-', $batchName);
            $zip->addFromString("rekod-kehadiran-harian-{$monthStr}-{$safeName}.pdf", $render($batchName, $batchStudents));
            gc_collect_cycles();
        }
        $zip->close();

        return response()->download($zipPath, "rekod-kehadiran-harian-{$monthStr}-semua-batch.zip", [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend();
    }

    /**
     * Per-day register symbols and daily totals for one set of students.
     * A student can have several attendance sessions on the same day; the
     * worst status wins (X over S over /) so an absence is never hidden.
     */
    private function buildDailyRegisterData($students, $attendances, Carbon $monthStart, Carbon $monthEnd): array
    {
        $daysInMonth = (int) $monthEnd->day;
        $rank        = ['/' => 1, 'S' => 2, 'X' => 3];

        $rows = $students->values()->map(function ($s) use ($attendances, $daysInMonth, $rank) {
            $days = array_fill(1, $daysInMonth, '');
            foreach ($attendances->get($s->student_id, []) as $a) {
                $symbol = match (strtolower($a->status)) {
                    'present'            => '/',
                    'absent'             => 'X',
                    'absent_with_reason' => 'S',
                    default              => '',
                };
                $d = $a->date->day;
                if ($symbol !== '' && ($rank[$symbol] > ($rank[$days[$d]] ?? 0))) {
                    $days[$d] = $symbol;
                }
            }
            return (object) [
                'student_name' => $s->student_name,
                'days'         => $days,
            ];
        });

        $dailyHadir    = array_fill(1, $daysInMonth, 0);
        $dailyTakHadir = array_fill(1, $daysInMonth, 0);
        foreach ($rows as $r) {
            foreach ($r->days as $d => $symbol) {
                if ($symbol === '/') {
                    $dailyHadir[$d]++;
                } elseif ($symbol === 'X' || $symbol === 'S') {
                    $dailyTakHadir[$d]++;
                }
            }
        }

        return [
            'rows'          => $rows,
            'daysInMonth'   => $daysInMonth,
            'dailyHadir'    => $dailyHadir,
            'dailyTakHadir' => $dailyTakHadir,
            'totalStudents' => $rows->count(),
            'monthStart'    => $monthStart,
            'monthEnd'      => $monthEnd,
        ];
    }

    // ─── Weekly ───────────────────────────────────────────────────────────────

    public function lecturerIndex(Request $request): View
    {
        $lecturerId = Lecturer::where('user_id', Auth::id())->value('lecturer_id');
        $data       = $this->buildWeeklyData($request, lecturerId: $lecturerId, isAdmin: false);
        return view('lecturer.hafazan-weekly-report', $data);
    }

    public function lecturerWeeklyPdf(Request $request)
    {
        $lecturerId   = Lecturer::where('user_id', Auth::id())->value('lecturer_id');
        $data         = $this->buildWeeklyData($request, lecturerId: $lecturerId, isAdmin: false);
        $lecturer = Lecturer::find($lecturerId);
        $data['lecturerName']  = $lecturer?->lecturer_name ?? 'N/A';
        $data['lecturerPhone'] = $lecturer?->phone ?? null;
        $data['generatedAt']   = Carbon::now()->format('d M Y, h:i A');

        $pdf = Pdf::loadView('pdf.weekly-report', $data)->setPaper('a4', 'landscape');
        return $pdf->download('weekly-hafazan-report-' . ($data['muqarrar']?->week ?? 'na') . '.pdf');
    }

    public function adminIndex(Request $request): View
    {
        $data    = $this->buildWeeklyData($request, lecturerId: null, isAdmin: true);
        $allRows = $data['allRows'];

        $lecturerSummaries = $allRows
            ->groupBy('lecturer_id')
            ->map(function ($rows) {
                $total       = $rows->count();
                $completed   = $rows->where('status', 'Completed')->count();
                $onTrack     = $rows->where('status', 'On Track')->count();
                $behind      = $rows->where('status', 'Behind')->count();
                $notRecorded = $rows->where('status', 'Not Recorded')->count();
                $batches     = $rows->pluck('batch_name')->filter(fn ($b) => $b !== '—')->unique()->sort()->values();

                return (object) [
                    'lecturer_id'   => $rows->first()->lecturer_id,
                    'lecturer_name' => $rows->first()->lecturer_name,
                    'batch_name'    => $batches->implode(', ') ?: '—',
                    'total'         => $total,
                    'completed'     => $completed,
                    'on_track'      => $onTrack,
                    'behind'        => $behind,
                    'not_recorded'  => $notRecorded,
                    'progress_pct'  => $total > 0 ? round(($completed + $onTrack) / $total * 100) : 0,
                ];
            })
            ->values()
            ->sortBy('lecturer_name')
            ->values();

        $data['lecturerSummaries'] = $lecturerSummaries;

        return view('admin.weekly-report', $data);
    }

    public function adminHafazanWeeklyPdf(Request $request)
    {
        $data    = $this->buildWeeklyData($request, lecturerId: null, isAdmin: true);
        $allRows = $data['allRows'];

        $lecturerSummaries = $allRows
            ->groupBy('lecturer_id')
            ->map(function ($rows) {
                $total       = $rows->count();
                $completed   = $rows->where('status', 'Completed')->count();
                $onTrack     = $rows->where('status', 'On Track')->count();
                $behind      = $rows->where('status', 'Behind')->count();
                $notRecorded = $rows->where('status', 'Not Recorded')->count();
                $batches     = $rows->pluck('batch_name')->filter(fn ($b) => $b !== '—')->unique()->sort()->values();

                return (object) [
                    'lecturer_id'   => $rows->first()->lecturer_id,
                    'lecturer_name' => $rows->first()->lecturer_name,
                    'batch_name'    => $batches->implode(', ') ?: '—',
                    'total'         => $total,
                    'completed'     => $completed,
                    'on_track'      => $onTrack,
                    'behind'        => $behind,
                    'not_recorded'  => $notRecorded,
                    'progress_pct'  => $total > 0 ? round(($completed + $onTrack) / $total * 100) : 0,
                ];
            })
            ->values()
            ->sortBy('lecturer_name')
            ->values();

        $data['lecturerSummaries'] = $lecturerSummaries;
        $data['generatedAt']       = Carbon::now()->format('d M Y, h:i A');

        $pdf = Pdf::loadView('pdf.admin-hafazan-weekly-report', $data)->setPaper('a4', 'landscape');
        return $pdf->download('admin-hafazan-weekly-week' . ($data['muqarrar']?->week ?? 'na') . '.pdf');
    }

    public function adminLecturerDetail(Request $request, int $lecturerId): View
    {
        $lecturer = Lecturer::find($lecturerId);
        if (! $lecturer) abort(404);

        $data = $this->buildWeeklyData($request, lecturerId: (string) $lecturerId, isAdmin: false);

        $weekStart = $data['muqarrar']?->week_start_date
            ? Carbon::parse($data['muqarrar']->week_start_date)->startOfDay()
            : null;
        $weekEnd = $weekStart?->copy()->addDays(6)->endOfDay();

        $allStudentIds = $data['allRows']->pluck('student_id');
        $lastUpdateMap = collect();
        if ($allStudentIds->isNotEmpty() && $weekStart && $weekEnd) {
            $lastUpdateMap = DB::table('tasmi')
                ->whereIn('student_id', $allStudentIds)
                ->whereDate('tasmi_date', '>=', $weekStart->format('Y-m-d'))
                ->whereDate('tasmi_date', '<=', $weekEnd->format('Y-m-d'))
                ->select('student_id', DB::raw('MAX(tasmi_date) as last_date'))
                ->groupBy('student_id')
                ->get()
                ->keyBy('student_id');
        }

        $data['rows'] = $data['allRows']->map(function ($r) use ($lastUpdateMap) {
            $raw = $lastUpdateMap->get($r->student_id)?->last_date;
            $r->last_update = $raw ? Carbon::parse($raw)->format('d M Y') : null;
            return $r;
        })->sortBy(function ($r) {
            if ($r->has_record && $r->gap > 5)  return 0;
            if ($r->has_record && $r->gap >= 1) return 1;
            if (! $r->has_record)               return 2;
            return 3;
        })->values();

        $data['lecturerName']  = $lecturer->lecturer_name;
        $data['lecturerPhone'] = $lecturer->phone ?? null;
        $data['lecturerId']    = $lecturerId;
        $data['weekStart']     = $weekStart;
        $data['weekEnd']       = $weekEnd;
        $data['totalStudents'] = $data['allRows']->count();
        // completedCount, onTrackCount, behindCount, notRecordedCount already in $data from buildWeeklyData()

        return view('admin.weekly-report-lecturer', $data);
    }

    // ─── Daily ────────────────────────────────────────────────────────────────

    public function lecturerDaily(Request $request): View
    {
        $data = $this->buildDailyData($request);
        return view('lecturer.daily-report', $data);
    }

    public function lecturerDailyPdf(Request $request)
    {
        $data                = $this->buildDailyData($request);
        $data['generatedAt'] = Carbon::now()->format('d M Y, h:i A');

        $pdf = Pdf::loadView('pdf.daily-report', $data)->setPaper('a4');
        return $pdf->download('daily-hafazan-report-' . $data['date']->format('Y-m-d') . '.pdf');
    }

    // ─── Attendance ───────────────────────────────────────────────────────────

    public function lecturerAttendance(Request $request): View
    {
        $data = $this->buildAttendanceData($request);
        return view('lecturer.attendance-report', $data);
    }

    public function lecturerAttendancePdf(Request $request)
    {
        $data                = $this->buildAttendanceData($request);
        $data['generatedAt'] = Carbon::now()->format('d M Y, h:i A');

        $pdf = Pdf::loadView('pdf.attendance-report', $data)->setPaper('a4');
        return $pdf->download('attendance-report-' . $data['monthStr'] . '.pdf');
    }

    // ─── Private data builders ────────────────────────────────────────────────

    private function buildDailyData(Request $request): array
    {
        $lecturerId = Lecturer::where('user_id', Auth::id())->value('lecturer_id');
        $lecturer   = Lecturer::find($lecturerId);
        $today      = Carbon::today();
        $date       = Carbon::parse($request->input('date', $today->toDateString()))->startOfDay();

        $students   = Student::with('batch')
            ->operational()
            ->where('lecturer_id', $lecturerId)
            ->orderBy('student_name')
            ->get();
        $studentIds = $students->pluck('student_id');

        // Load muqarrars for all batches in this class
        $batchIds     = $students->pluck('batch_id')->filter()->unique();
        $allMuqarrars = Muqarrar::whereNotNull('week_start_date')
            ->where(fn ($q) => $q->whereIn('batch_id', $batchIds)->orWhereNull('batch_id'))
            ->orderBy('week_start_date')
            ->get();

        // Find the muqarrar for a batch on a given date
        $getMuqarrarForBatch = function (?int $batchId) use ($allMuqarrars, $date): ?Muqarrar {
            $subset = $batchId ? $allMuqarrars->where('batch_id', $batchId) : collect();
            if ($subset->isEmpty()) $subset = $allMuqarrars->whereNull('batch_id');
            foreach ($subset->sortBy('week_start_date') as $m) {
                $ws = Carbon::parse($m->week_start_date)->startOfDay();
                if ($date->between($ws, $ws->copy()->addDays(6)->endOfDay())) return $m;
            }
            return $subset->sortBy('week_start_date')->last();
        };

        // Compute checkpoint for a muqarrar on the given date
        $getCheckpoint = function (?Muqarrar $muqarrar) use ($date): int {
            if (! $muqarrar) return 0;
            $totalDays = 5;
            if ($muqarrar->week_start_date) {
                $ws   = Carbon::parse($muqarrar->week_start_date)->startOfDay();
                $diff = (int) $ws->diffInDays(Carbon::parse($date->format('Y-m-d'))->startOfDay(), false);
                $day  = max(1, min($totalDays, $diff + 1));
            } else {
                $day = max(1, min($totalDays, $date->dayOfWeekIso));
            }
            $weekly = (int) $muqarrar->end_page - (int) $muqarrar->start_page;
            return (int) min(
                (int) $muqarrar->end_page,
                (int) round((int) $muqarrar->start_page + ($weekly / $totalDays) * $day)
            );
        };

        // Fetch each student's latest page up to the selected date
        $bestPageMap = collect();
        if ($studentIds->isNotEmpty()) {
            $bestPageMap = DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '<=', $date->format('Y-m-d'))
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as best_page'))
                ->groupBy('t.student_id')
                ->get()
                ->keyBy('student_id');
        }

        $khatam = KhatamRecord::countsAsOf($studentIds, $date);

        $rows = $students->map(function (Student $s) use ($bestPageMap, $getMuqarrarForBatch, $getCheckpoint, $khatam) {
            $isKhatam = $khatam->has($s->student_id);
            // Each student's checkpoint comes from their own batch's muqarrar
            $muqarrar        = $getMuqarrarForBatch($s->batch_id);
            $checkpoint      = $getCheckpoint($muqarrar);
            $startPage       = (int) ($muqarrar?->start_page ?? 0);
            $endPage         = (int) ($muqarrar?->end_page   ?? 0);

            $rawPage    = $bestPageMap->get($s->student_id)?->best_page ?? null;
            $hasRecord  = $rawPage !== null;
            $latestPage = $hasRecord ? (int) $rawPage : null;

            $gap    = null;
            $status = 'Not Recorded';

            if ($hasRecord && $isKhatam) {
                $gap    = 0;
                $status = 'Completed';
            } elseif ($hasRecord) {
                $gap = $checkpoint > 0 ? ($checkpoint - $latestPage) : 0;

                if ($endPage > 0 && $latestPage >= $endPage) {
                    $status = 'Completed';
                } elseif ($gap <= 0) {
                    $status = 'On Track';
                } else {
                    $status = 'Behind';
                }
            }

            $gaugeDenom = $endPage - $startPage;
            $gaugePct   = ($hasRecord && $isKhatam) ? 100 : (($hasRecord && $gaugeDenom > 0 && $latestPage > $startPage)
                ? min(100, max(0, (int) round(($latestPage - $startPage) / $gaugeDenom * 100)))
                : 0);

            return (object) [
                'student_id'   => $s->student_id,
                'student_name' => $s->student_name,
                'batch_name'   => $s->batch?->batch_name ?? '—',
                'latest_page'  => $latestPage,
                'checkpoint'   => $checkpoint > 0 ? $checkpoint : null,
                'gap'          => $gap,
                'status'       => $status,
                'gauge_pct'    => $gaugePct,
                'has_record'   => $hasRecord,
            ];
        });

        // For the report header, use the first batch's muqarrar as the display context
        $firstMuqarrar = $batchIds->isNotEmpty() ? $getMuqarrarForBatch($batchIds->first()) : null;
        $weekStart     = $firstMuqarrar?->week_start_date
            ? Carbon::parse($firstMuqarrar->week_start_date)->startOfDay()
            : null;
        $weekEnd       = $weekStart?->copy()->addDays(6)->endOfDay();

        // Sidebar week list: all muqarrars for these batches
        $allWeeks = $allMuqarrars->sortBy('week_start_date')->values();

        return [
            'rows'             => $rows,
            'date'             => $date,
            'today'            => $today,
            'muqarrar'         => $firstMuqarrar,
            'checkpoint'       => $firstMuqarrar ? $getCheckpoint($firstMuqarrar) : 0,
            'startPage'        => (int) ($firstMuqarrar?->start_page ?? 0),
            'endPage'          => (int) ($firstMuqarrar?->end_page   ?? 0),
            'weekStart'        => $weekStart,
            'weekEnd'          => $weekEnd,
            'allWeeks'         => $allWeeks,
            'months'           => $this->buildMonthList($today),
            'completedCount'   => $rows->where('status', 'Completed')->count(),
            'onTrackCount'     => $rows->where('status', 'On Track')->count(),
            'behindCount'      => $rows->where('status', 'Behind')->count(),
            'notRecordedCount' => $rows->where('status', 'Not Recorded')->count(),
            'total'            => $rows->count(),
            'lecturerName'     => $lecturer?->lecturer_name ?? 'N/A',
            'lecturerPhone'    => $lecturer?->phone ?? null,
        ];
    }

    private function buildAttendanceData(Request $request): array
    {
        $lecturerId    = Lecturer::where('user_id', Auth::id())->value('lecturer_id');
        $lecturer      = Lecturer::find($lecturerId);
        $today         = Carbon::today();
        $monthStr      = $request->input('month', $today->format('Y-m'));
        $filterHalaqah = trim($request->input('halaqah', ''));
        $monthStart    = Carbon::parse($monthStr . '-01')->startOfMonth();
        $monthEnd      = $monthStart->copy()->endOfMonth();

        $students = Student::with('batch')
            ->operational()
            ->where('lecturer_id', $lecturerId)
            ->when($filterHalaqah, fn ($q) => $q->whereHas('batch', fn ($b) => $b->where('batch_name', $filterHalaqah)))
            ->orderBy('student_name')
            ->get();
        $studentIds = $students->pluck('student_id');

        $attendances = Attendance::whereIn('student_id', $studentIds)
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get()
            ->groupBy('student_id');

        $rows = $students->map(function (Student $s) use ($attendances) {
            $recs    = $attendances->get($s->student_id, collect());
            $present = $recs->filter(fn ($a) => strtolower($a->status) === 'present')->count();
            $absent  = $recs->filter(fn ($a) => strtolower($a->status) === 'absent')->count();
            $excused = $recs->filter(fn ($a) => strtolower($a->status) === 'absent_with_reason')->count();
            $total   = $present + $absent + $excused;
            $rate    = $total > 0 ? round(($present / $total) * 100) : null;

            return (object) [
                'student_id'    => $s->student_id,
                'student_name'  => $s->student_name,
                'batch_name'    => $s->batch?->batch_name ?? '—',
                'warning_level' => (int) ($s->warning_level ?? 0),
                'present'       => $present,
                'absent'        => $absent,
                'excused'       => $excused,
                'total_days'    => $total,
                'rate'          => $rate,
                'has_th'        => $absent > 0,
                'has_ths'       => $excused > 0,
                'is_perfect'    => $total > 0 && $rate !== null && $rate >= 100,
            ];
        });

        $totalStudents      = $rows->count();
        $totalSessions      = $rows->sum('total_days');
        $totalPresent       = $rows->sum('present');
        $totalAbsent        = $rows->sum('absent');
        $totalExcused       = $rows->sum('excused');
        $overallPresentRate = $totalSessions > 0 ? round(($totalPresent / $totalSessions) * 100) : null;
        $thRate             = $totalSessions > 0 ? round(($totalAbsent  / $totalSessions) * 100) : null;
        $thsRate            = $totalSessions > 0 ? round(($totalExcused / $totalSessions) * 100) : null;
        $perfectCount       = $rows->filter(fn ($r) => $r->is_perfect)->count();
        $thStudentCount     = $rows->filter(fn ($r) => $r->has_th)->count();
        $thsStudentCount    = $rows->filter(fn ($r) => $r->has_ths)->count();

        $halaqahOptions = Student::query()
            ->operational()
            ->with('batch')
            ->where('lecturer_id', $lecturerId)
            ->get()
            ->map(fn ($s) => $s->batch?->batch_name)
            ->filter()->sort()->unique()->values();

        return [
            'rows'               => $rows,
            'monthStart'         => $monthStart,
            'monthEnd'           => $monthEnd,
            'monthStr'           => $monthStr,
            'filterHalaqah'      => $filterHalaqah,
            'halaqahOptions'     => $halaqahOptions,
            'months'             => $this->buildMonthList($today),
            'today'              => $today,
            'totalStudents'      => $totalStudents,
            'totalPresent'       => $totalPresent,
            'totalAbsent'        => $totalAbsent,
            'totalExcused'       => $totalExcused,
            'overallPresentRate' => $overallPresentRate,
            'thRate'             => $thRate,
            'thsRate'            => $thsRate,
            'perfectCount'       => $perfectCount,
            'thStudentCount'     => $thStudentCount,
            'thsStudentCount'    => $thsStudentCount,
            'lecturerName'       => $lecturer?->lecturer_name ?? 'N/A',
            'lecturerPhone'      => $lecturer?->phone ?? null,
            'attendanceDetails'  => $this->buildAttendanceDetails($attendances),
        ];
    }

    private function buildWeeklyData(Request $request, ?string $lecturerId, bool $isAdmin): array
    {
        $today          = Carbon::today();
        $filterStatus   = $request->input('status', '');
        $filterHalaqah  = trim($request->input('halaqah', ''));
        $filterLecturer = $isAdmin ? $request->input('lecturer_id', '') : $lecturerId;

        $students = Student::with(['batch', 'lecturer'])
            ->operational()
            ->when(! $isAdmin && $lecturerId, fn ($q) => $q->where('lecturer_id', $lecturerId))
            ->when($isAdmin && $filterLecturer, fn ($q) => $q->where('lecturer_id', $filterLecturer))
            ->when($filterHalaqah, fn ($q) => $q->whereHas('batch', fn ($b) => $b->where('batch_name', $filterHalaqah)))
            ->orderBy('student_name')
            ->get();

        $studentIds = $students->pluck('student_id');
        $batchIds   = $students->pluck('batch_id')->filter()->unique();

        // Load muqarrars for the week selector dropdown (batch-specific + semester-wide)
        $allMuqarrars = Muqarrar::whereNotNull('week_start_date')
            ->where(fn ($q) => $q->whereIn('batch_id', $batchIds)->orWhereNull('batch_id'))
            ->orderBy('week_start_date')
            ->get();

        // Compute checkpoint — identical formula to LecturerDashboardController::hafazanStatus
        $getCheckpoint = function (?Muqarrar $muqarrar) use ($today): int {
            if (! $muqarrar) return 0;
            $totalDays = 5;
            $weekStart = $muqarrar->week_start_date
                ? Carbon::parse($muqarrar->week_start_date)->startOfDay()
                : null;
            $weekEnd = $weekStart?->copy()->addDays(6)->endOfDay();

            if ($weekEnd && $today->isAfter($weekEnd)) {
                return (int) $muqarrar->end_page;
            }

            if ($weekStart) {
                $diff = (int) $weekStart->diffInDays(Carbon::parse($today->format('Y-m-d'))->startOfDay(), false);
                $day  = max(1, min($totalDays, $diff + 1));
            } else {
                $day = max(1, min($totalDays, $today->dayOfWeekIso));
            }

            $weekly = (int) $muqarrar->end_page - (int) $muqarrar->start_page;
            return (int) min(
                (int) $muqarrar->end_page,
                (int) round((int) $muqarrar->start_page + ($weekly / $totalDays) * $day)
            );
        };

        // Select the active muqarrar using the same logic as LecturerDashboardController.
        // When a muqarrar_id is in the request, honour it directly.
        // When auto-detecting the current week, query ONLY the latest semester so we never
        // accidentally fall back to a wrong-semester entry (the old batch-filtered collection
        // spans all semesters and its last() fallback returns the wrong week).
        $selectedMuqarrarId = $request->input('muqarrar_id');

        if ($selectedMuqarrarId) {
            $muqarrar = $allMuqarrars->firstWhere('muqarrar_id', $selectedMuqarrarId)
                ?? Muqarrar::find($selectedMuqarrarId);
        } else {
            // Prefer the active academic semester's muqarrars (new records set
            // academic_period_id, not semester_id); fall back to the legacy
            // level-based lookup for data created before that column existed.
            $activePeriod  = AcademicPeriod::current();
            $semesterWeeks = $activePeriod
                ? Muqarrar::where('academic_period_id', $activePeriod->id)->orderBy('week_start_date')->get()
                : collect();

            if ($semesterWeeks->isEmpty()) {
                $semesterId    = Muqarrar::orderBy('semester_id', 'desc')->value('semester_id');
                $semesterWeeks = $semesterId
                    ? Muqarrar::where('semester_id', $semesterId)->orderBy('week_start_date')->get()
                    : collect();
            }

            $muqarrar = null;
            foreach ($semesterWeeks as $m) {
                if (! $m->week_start_date) continue;
                $ws = Carbon::parse($m->week_start_date)->startOfDay();
                if ($today->between($ws, $ws->copy()->addDays(6)->endOfDay())) {
                    $muqarrar = $m;
                    break;
                }
            }
            $muqarrar = $muqarrar ?? $semesterWeeks->last();
        }

        $weekStart = $muqarrar?->week_start_date
            ? Carbon::parse($muqarrar->week_start_date)->startOfDay()
            : null;
        $weekEnd   = $weekStart?->copy()->addDays(6)->endOfDay();
        $weekLabel = $muqarrar
            ? ('Week ' . $muqarrar->week . ($weekStart ? ' (' . $weekStart->format('d M') . ' – ' . $weekEnd->format('d M Y') . ')' : ''))
            : 'No data';

        // Best page per student within the selected week's date range
        $bestPageMap = collect();
        if ($studentIds->isNotEmpty() && $weekStart && $weekEnd) {
            $bestPageMap = DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '>=', $weekStart->format('Y-m-d'))
                ->whereDate('t.tasmi_date', '<=', $weekEnd->format('Y-m-d'))
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as best_page'))
                ->groupBy('t.student_id')
                ->get()
                ->keyBy('student_id');
        }

        // Max page reached before this week — used to compute pages covered this week
        $pageBeforeWeekMap = collect();
        if ($studentIds->isNotEmpty() && $weekStart) {
            $pageBeforeWeekMap = DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '<', $weekStart->format('Y-m-d'))
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as page_before'))
                ->groupBy('t.student_id')
                ->get()
                ->keyBy('student_id');
        }

        // Pre-compute a single checkpoint shared by all students — same as the dashboard.
        $sharedCheckpoint = $getCheckpoint($muqarrar);
        $sharedEndPage    = (int) ($muqarrar?->end_page ?? 0);
        $sharedStartPage  = (int) ($muqarrar?->start_page ?? 1);

        // Khatam as of the week's end (or today, for the current week).
        $khatamAsOf = ($weekEnd && $weekEnd->lt($today)) ? $weekEnd : $today;
        $khatam     = KhatamRecord::countsAsOf($studentIds, $khatamAsOf);

        $allRows = $students->map(function (Student $student) use (
            $bestPageMap, $pageBeforeWeekMap, $sharedCheckpoint, $sharedEndPage, $sharedStartPage, $khatam
        ) {
            $checkpoint = $sharedCheckpoint;
            $endPage    = $sharedEndPage;
            $isKhatam   = $khatam->has($student->student_id);

            $latestPage = (int) ($bestPageMap->get($student->student_id)?->best_page ?? 0);
            $hasRecord  = $latestPage > 0;
            $gap        = ($hasRecord && $checkpoint > 0 && ! $isKhatam) ? ($checkpoint - $latestPage) : 0;
            $remaining  = $isKhatam ? null : ($endPage > 0
                ? ($hasRecord ? max(0, $endPage - $latestPage) : $endPage)
                : null);

            $pagesThisWeek = null;
            if ($hasRecord) {
                $pageBefore    = $pageBeforeWeekMap->has($student->student_id)
                    ? (int) $pageBeforeWeekMap->get($student->student_id)->page_before
                    : max(0, $sharedStartPage - 1);
                $pagesThisWeek = max(0, $latestPage - $pageBefore);
            }

            if (! $hasRecord) {
                $status = 'Not Recorded'; $color = 'gray';
            } elseif ($isKhatam || ($endPage > 0 && $latestPage >= $endPage)) {
                $status = 'Completed'; $color = 'green';
            } elseif ($gap <= 0) {
                $status = 'On Track'; $color = 'blue';
            } elseif ($gap <= 5) {
                $status = 'Behind'; $color = 'yellow';
            } elseif ($gap <= 10) {
                $status = 'Behind'; $color = 'orange';
            } else {
                $status = 'Behind'; $color = 'red';
            }

            return (object) [
                'student_id'     => $student->student_id,
                'student_name'   => $student->student_name,
                'batch_name'     => $student->batch?->batch_name ?? '—',
                'lecturer_id'    => $student->lecturer_id,
                'lecturer_name'  => $student->lecturer?->lecturer_name ?? '—',
                'target_page'    => $checkpoint > 0 ? $checkpoint : '—',
                'latest_page'    => $hasRecord ? $latestPage : null,
                'remaining'      => $remaining,
                'gap'            => $gap,
                'has_record'     => $hasRecord,
                'status'         => $status,
                'color'          => $color,
                'pages_this_week'=> $pagesThisWeek,
                'khatam_count'   => $khatam->get($student->student_id, 0),
            ];
        });

        $completedCount   = $allRows->where('status', 'Completed')->count();
        $onTrackCount     = $allRows->where('status', 'On Track')->count();
        $behindCount      = $allRows->where('status', 'Behind')->count();
        $notRecordedCount = $allRows->where('status', 'Not Recorded')->count();

        // $rows always equals $allRows so KPIs and the table use the same dataset.
        // Halaqah and week filters still narrow $students (and therefore $allRows) upstream.
        $rows = $allRows;

        // Admin's global (no lecturer filter) view mirrors Batch Management's "is_active"
        // flag directly; a lecturer-scoped view (admin viewing one lecturer, or a lecturer
        // viewing their own class) is legitimately narrower — only their own batches.
        $halaqahOptions = ($isAdmin && ! $filterLecturer)
            ? Batch::active()->orderedByName()->pluck('batch_name')
            : Student::query()
                ->operational()
                ->with('batch')
                ->when(! $isAdmin && $lecturerId, fn ($q) => $q->where('lecturer_id', $lecturerId))
                ->when($isAdmin && $filterLecturer, fn ($q) => $q->where('lecturer_id', $filterLecturer))
                ->get()
                ->map(fn ($s) => $s->batch?->batch_name)
                ->filter()->sort()->unique()->values();

        $lecturers = $isAdmin ? Lecturer::orderBy('lecturer_name')->get() : collect();

        // Week selector shows all muqarrars for relevant batches
        $allWeeks = $allMuqarrars->sortBy('week_start_date')->values();

        return [
            'rows'             => $rows,
            'allRows'          => $allRows,
            'allWeeks'         => $allWeeks,
            'muqarrar'         => $muqarrar,
            'checkpoint'       => $muqarrar ? $getCheckpoint($muqarrar) : 0,
            'endPage'          => (int) ($muqarrar?->end_page ?? 0),
            'weekLabel'        => $weekLabel,
            'filterStatus'     => $filterStatus,
            'filterHalaqah'    => $filterHalaqah,
            'filterLecturer'   => $isAdmin ? ($filterLecturer ?? '') : null,
            'halaqahOptions'   => $halaqahOptions,
            'lecturers'        => $lecturers,
            'onTrackCount'     => $onTrackCount,
            'behindCount'      => $behindCount,
            'completedCount'   => $completedCount,
            'notRecordedCount' => $notRecordedCount,
            'isAdmin'          => $isAdmin,
        ];
    }

    private function buildAdminAttendanceDailyData(Request $request): array
    {
        $today         = Carbon::today();
        $date          = Carbon::parse($request->input('date', $today->toDateString()))->startOfDay();
        $filterHalaqah = trim($request->input('halaqah', ''));

        $students   = Student::with(['batch', 'lecturer'])
            ->operational()
            ->when($filterHalaqah, fn ($q) => $q->whereHas('batch', fn ($b) => $b->where('batch_name', $filterHalaqah)))
            ->orderBy('student_name')
            ->get();
        $studentIds = $students->pluck('student_id');

        $attMap = Attendance::whereIn('student_id', $studentIds)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('student_id');

        $rows = $students->map(function (Student $s) use ($attMap) {
            $att         = $attMap->get($s->student_id);
            $status      = $att ? strtolower($att->status) : null;
            $reasonLabel = $att?->reason_type
                ? ucwords(str_replace('_', ' ', $att->reason_type))
                : null;

            return (object) [
                'student_id'          => $s->student_id,
                'student_name'        => $s->student_name,
                'batch_name'          => $s->batch?->batch_name ?? '—',
                'lecturer_id'         => $s->lecturer_id,
                'lecturer_name'       => $s->lecturer?->lecturer_name ?? '—',
                'status'              => $status,
                'status_label'        => match($status) {
                    'present'            => 'Present',
                    'absent'             => 'Absent (TH)',
                    'absent_with_reason' => 'Excused (THS)',
                    default              => 'Not Recorded',
                },
                'reason_type'         => $reasonLabel,
                'remarks'             => $att?->remarks,
                'attendance_id'       => $att?->attendance_id,
                'supporting_document' => $att?->supporting_document,
            ];
        });

        $presentCount = $rows->filter(fn ($r) => $r->status === 'present')->count();
        $absentCount  = $rows->filter(fn ($r) => $r->status === 'absent')->count();
        $excusedCount = $rows->filter(fn ($r) => $r->status === 'absent_with_reason')->count();
        $recorded     = $presentCount + $absentCount + $excusedCount;

        return [
            'rows'           => $rows,
            'date'           => $date,
            'today'          => $today,
            'presentCount'   => $presentCount,
            'absentCount'    => $absentCount,
            'excusedCount'   => $excusedCount,
            'attendanceRate' => $recorded > 0 ? round(($presentCount / $recorded) * 100) : null,
            'lecturerName'   => 'System-wide (All Batches)',
            'months'         => $this->buildMonthList($today),
            'filterHalaqah'  => $filterHalaqah,
            'halaqahOptions' => Batch::active()->orderedByName()->pluck('batch_name'),
        ];
    }

    private function buildAdminAttendanceWeeklyData(Request $request): array
    {
        $today         = Carbon::today();
        $inputDate     = $request->input('week_start', $today->toDateString());
        $weekStart     = Carbon::parse($inputDate)->startOfWeek(Carbon::MONDAY)->startOfDay();
        $weekEnd       = $weekStart->copy()->addDays(6)->endOfDay();
        $filterHalaqah = trim($request->input('halaqah', ''));

        $students   = Student::with(['batch', 'lecturer'])
            ->operational()
            ->when($filterHalaqah, fn ($q) => $q->whereHas('batch', fn ($b) => $b->where('batch_name', $filterHalaqah)))
            ->orderBy('student_name')
            ->get();
        $studentIds = $students->pluck('student_id');

        $attendances = Attendance::whereIn('student_id', $studentIds)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->groupBy('student_id');

        $rows = $students->map(function (Student $s) use ($attendances) {
            $recs    = $attendances->get($s->student_id, collect());
            $present = $recs->filter(fn ($a) => strtolower($a->status) === 'present')->count();
            $absent  = $recs->filter(fn ($a) => strtolower($a->status) === 'absent')->count();
            $excused = $recs->filter(fn ($a) => strtolower($a->status) === 'absent_with_reason')->count();
            $total   = $present + $absent + $excused;

            $rate = $total > 0 ? round(($present / $total) * 100) : null;
            return (object) [
                'student_id'    => $s->student_id,
                'student_name'  => $s->student_name,
                'batch_name'    => $s->batch?->batch_name ?? '—',
                'lecturer_id'   => $s->lecturer_id,
                'lecturer_name' => $s->lecturer?->lecturer_name ?? '—',
                'warning_level' => (int) ($s->warning_level ?? 0),
                'present'       => $present,
                'absent'        => $absent,
                'excused'       => $excused,
                'total_days'    => $total,
                'rate'          => $rate,
                'has_th'        => $absent > 0,
                'has_ths'       => $excused > 0,
                'is_perfect'    => $total > 0 && $rate !== null && $rate >= 100,
            ];
        });

        return [
            'rows'              => $rows,
            'weekStart'         => $weekStart,
            'weekEnd'           => $weekEnd,
            'today'             => $today,
            'totalPresent'      => $rows->sum('present'),
            'totalAbsent'       => $rows->filter(fn ($r) => $r->has_th)->count(),
            'totalExcused'      => $rows->filter(fn ($r) => $r->has_ths)->count(),
            'lecturerName'      => 'System-wide (All Batches)',
            'months'            => $this->buildMonthList($today),
            'attendanceDetails' => $this->buildAttendanceDetails($attendances),
            'filterHalaqah'     => $filterHalaqah,
            'halaqahOptions'    => Batch::active()->orderedByName()->pluck('batch_name'),
        ];
    }

    private function buildAdminAttendanceMonthlyData(Request $request): array
    {
        $today         = Carbon::today();
        $monthStr      = $request->input('month', $today->format('Y-m'));
        $monthStart    = Carbon::parse($monthStr . '-01')->startOfMonth();
        $monthEnd      = $monthStart->copy()->endOfMonth();
        $filterHalaqah = trim($request->input('halaqah', ''));

        $students   = Student::with(['batch', 'lecturer'])
            ->operational()
            ->when($filterHalaqah, fn ($q) => $q->whereHas('batch', fn ($b) => $b->where('batch_name', $filterHalaqah)))
            ->orderBy('student_name')
            ->get();
        $studentIds = $students->pluck('student_id');

        $attendances = Attendance::whereIn('student_id', $studentIds)
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get()
            ->groupBy('student_id');

        $rows = $students->map(function (Student $s) use ($attendances) {
            $recs    = $attendances->get($s->student_id, collect());
            $present = $recs->filter(fn ($a) => strtolower($a->status) === 'present')->count();
            $absent  = $recs->filter(fn ($a) => strtolower($a->status) === 'absent')->count();
            $excused = $recs->filter(fn ($a) => strtolower($a->status) === 'absent_with_reason')->count();
            $total   = $present + $absent + $excused;

            $rate = $total > 0 ? round(($present / $total) * 100) : null;
            return (object) [
                'student_id'    => $s->student_id,
                'student_name'  => $s->student_name,
                'batch_name'    => $s->batch?->batch_name ?? '—',
                'lecturer_id'   => $s->lecturer_id,
                'lecturer_name' => $s->lecturer?->lecturer_name ?? '—',
                'warning_level' => (int) ($s->warning_level ?? 0),
                'present'       => $present,
                'absent'        => $absent,
                'excused'       => $excused,
                'total_days'    => $total,
                'rate'          => $rate,
                'has_th'        => $absent > 0,
                'has_ths'       => $excused > 0,
                'is_perfect'    => $total > 0 && $rate !== null && $rate >= 100,
            ];
        });

        return [
            'rows'              => $rows,
            'monthStart'        => $monthStart,
            'monthEnd'          => $monthEnd,
            'monthStr'          => $monthStr,
            'today'             => $today,
            'months'            => $this->buildMonthList($today),
            'lecturerName'      => 'System-wide (All Batches)',
            'attendanceDetails' => $this->buildAttendanceDetails($attendances),
            'filterHalaqah'     => $filterHalaqah,
            'halaqahOptions'    => Batch::active()->orderedByName()->pluck('batch_name'),
        ];
    }

    /**
     * Build a per-student map of TH/THS records for use in the "View Details" modal.
     * Only absent/excused entries are included — present rows are already summarised
     * in the aggregate table and don't need further drill-down.
     */
    private function buildAttendanceDetails(\Illuminate\Support\Collection $grouped): array
    {
        $details = [];
        foreach ($grouped as $sid => $recs) {
            $details[$sid] = $recs
                ->filter(fn ($a) => strtolower($a->status) !== 'present')
                ->sortBy('date')
                ->map(fn ($a) => [
                    'date'     => Carbon::parse($a->date)->format('d M Y'),
                    'status'   => strtolower($a->status),
                    'label'    => match (strtolower($a->status)) {
                        'absent'             => 'Absent (TH)',
                        'absent_with_reason' => 'Excused (THS)',
                        default              => ucfirst($a->status),
                    },
                    'reason'   => $a->reason_type
                        ? ucwords(str_replace('_', ' ', $a->reason_type))
                        : null,
                    'remarks'  => $a->remarks ?: null,
                    'document_url' => $a->supporting_document
                        ? route('documents.attendance', $a->attendance_id)
                        : null,
                ])
                ->values()
                ->toArray();
        }
        return $details;
    }

    private function buildMonthList(Carbon $today): \Illuminate\Support\Collection
    {
        $months = collect();
        for ($i = 0; $i < 12; $i++) {
            $m = $today->copy()->subMonths($i)->startOfMonth();
            $months->push(['value' => $m->format('Y-m'), 'label' => $m->format('F Y')]);
        }
        return $months;
    }

    private function buildHafazanSemesterData(Request $request, ?string $lecturerId, bool $isAdmin): array
    {
        $today         = Carbon::today();
        $filterHalaqah = trim($request->input('halaqah', ''));

        AcademicPeriod::recalculateAll();
        $period = $request->filled('academic_period_id')
            ? AcademicPeriod::find($request->input('academic_period_id'))
            : AcademicPeriod::current();

        if ($period) {
            $semStart      = Carbon::parse($period->start_date)->startOfDay();
            $semEnd        = Carbon::parse($period->end_date)->endOfDay();
            $effectiveEnd  = $today->isAfter($semEnd) ? $semEnd->copy() : $today->copy();
            $semesterLabel = $period->name;
        } else {
            // No academic semester exists yet — render an empty, zeroed report rather than
            // erroring, since $semStart/$semEnd have nothing meaningful to be.
            $semStart      = null;
            $semEnd        = null;
            $effectiveEnd  = $today->copy();
            $semesterLabel = 'No academic semester configured';
        }

        $students = Student::with(['batch', 'lecturer'])
            ->operational()
            ->when($lecturerId, fn ($q) => $q->where('lecturer_id', $lecturerId))
            ->when($filterHalaqah, fn ($q) => $q->whereHas('batch', fn ($b) => $b->where('batch_name', $filterHalaqah)))
            ->orderBy('student_name')
            ->get();
        $studentIds = $students->pluck('student_id');

        $latestPageMap = collect();
        if ($studentIds->isNotEmpty()) {
            $latestPageMap = DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '<=', $effectiveEnd->toDateString())
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as best_page'))
                ->groupBy('t.student_id')->get()->keyBy('student_id');
        }

        $bestInSemMap = collect();
        if ($period && $studentIds->isNotEmpty()) {
            $bestInSemMap = DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '>=', $semStart->toDateString())
                ->whereDate('t.tasmi_date', '<=', $effectiveEnd->toDateString())
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as best_page'))
                ->groupBy('t.student_id')->get()->keyBy('student_id');
        }

        $pageBeforeSemMap = collect();
        if ($period && $studentIds->isNotEmpty()) {
            $pageBeforeSemMap = DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '<', $semStart->toDateString())
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as page_before'))
                ->groupBy('t.student_id')->get()->keyBy('student_id');
        }

        $sessionsMap = collect();
        if ($period && $studentIds->isNotEmpty()) {
            $sessionsMap = DB::table('tasmi')
                ->whereIn('student_id', $studentIds)
                ->whereDate('tasmi_date', '>=', $semStart->toDateString())
                ->whereDate('tasmi_date', '<=', $effectiveEnd->toDateString())
                ->select('student_id', DB::raw('COUNT(*) as session_count'))
                ->groupBy('student_id')->get()->keyBy('student_id');
        }

        // Use the highest end_page muqarrar within the semester as the target
        $lastMuqarrar    = $period
            ? Muqarrar::whereNotNull('week_start_date')
                ->whereDate('week_start_date', '>=', $semStart->toDateString())
                ->whereDate('week_start_date', '<=', $semEnd->toDateString())
                ->orderBy('end_page', 'desc')->first()
            : null;
        $semesterEndPage = (int) ($lastMuqarrar?->end_page ?? 0);
        $weeksElapsed    = $period ? max(1, (int) ceil($semStart->diffInDays($effectiveEnd) / 7)) : 1;

        $khatam = KhatamRecord::countsAsOf($studentIds, $effectiveEnd);

        $rows = $students->map(function (Student $s) use (
            $latestPageMap, $bestInSemMap, $pageBeforeSemMap, $sessionsMap, $semesterEndPage, $weeksElapsed, $khatam
        ) {
            $isKhatam   = $khatam->has($s->student_id);
            $hasRecord  = $bestInSemMap->has($s->student_id);
            $rawPage    = $latestPageMap->get($s->student_id)?->best_page ?? null;
            $latestPage = $rawPage !== null ? (int) $rawPage : null;
            $sessions   = (int) ($sessionsMap->get($s->student_id)?->session_count ?? 0);

            $pagesGained = null;
            $weeklyAvg   = null;
            if ($hasRecord && $latestPage !== null) {
                $pageBefore  = $pageBeforeSemMap->has($s->student_id)
                    ? (int) $pageBeforeSemMap->get($s->student_id)->page_before : 0;
                $pagesGained = max(0, (int) $bestInSemMap->get($s->student_id)->best_page - $pageBefore);
                $weeklyAvg   = $weeksElapsed > 0 ? round($pagesGained / $weeksElapsed, 1) : null;
            }

            $progressPct = ($isKhatam && $hasRecord) ? 100 : (($semesterEndPage > 0 && $latestPage !== null)
                ? min(100, (int) round($latestPage / $semesterEndPage * 100)) : 0);

            if (! $hasRecord) {
                $status = 'Not Recorded';
            } elseif ($isKhatam) {
                $status = 'Completed';
            } elseif ($semesterEndPage > 0 && $latestPage !== null && $latestPage >= $semesterEndPage) {
                $status = 'Completed';
            } else {
                $status = 'In Progress';
            }

            return (object) [
                'student_id'     => $s->student_id,
                'student_name'   => $s->student_name,
                'batch_name'     => $s->batch?->batch_name ?? '—',
                'lecturer_id'    => $s->lecturer_id,
                'lecturer_name'  => $s->lecturer?->lecturer_name ?? '—',
                'latest_page'    => $latestPage,
                'total_sessions' => $sessions,
                'pages_gained'   => $pagesGained,
                'weekly_avg'     => $weeklyAvg,
                'progress_pct'   => $progressPct,
                'has_record'     => $hasRecord,
                'status'         => $status,
            ];
        });

        return [
            'rows'             => $rows,
            'selectedPeriodId' => $period?->id ? (string) $period->id : null,
            'semesterLabel'    => $semesterLabel,
            'semStart'         => $semStart,
            'semEnd'           => $semEnd,
            'periodOptions'    => $this->buildAttendancePeriodOptions(),
            'semesterEndPage'  => $semesterEndPage,
            'completedCount'   => $rows->where('status', 'Completed')->count(),
            'inProgressCount'  => $rows->where('status', 'In Progress')->count(),
            'notRecordedCount' => $rows->where('status', 'Not Recorded')->count(),
            'today'            => $today,
            'filterHalaqah'    => $filterHalaqah,
            'halaqahOptions'   => Batch::active()->orderedByName()->pluck('batch_name'),
        ];
    }

    /** Academic periods for the attendance-semester selector, newest first. */
    private function buildAttendancePeriodOptions()
    {
        return AcademicPeriod::orderByDesc('start_date')->get()
            ->map(fn (AcademicPeriod $p) => ['value' => (string) $p->id, 'label' => $p->name]);
    }

    private function buildAttendanceSemesterData(Request $request, ?string $lecturerId, bool $isAdmin): array
    {
        $today         = Carbon::today();
        $filterHalaqah = trim($request->input('halaqah', ''));

        AcademicPeriod::recalculateAll();
        $period = $request->filled('academic_period_id')
            ? AcademicPeriod::find($request->input('academic_period_id'))
            : AcademicPeriod::current();

        $students = Student::with(['batch', 'lecturer'])
            ->operational()
            ->when($lecturerId, fn ($q) => $q->where('lecturer_id', $lecturerId))
            ->when($filterHalaqah, fn ($q) => $q->whereHas('batch', fn ($b) => $b->where('batch_name', $filterHalaqah)))
            ->orderBy('student_name')
            ->get();
        $studentIds = $students->pluck('student_id');

        if ($period) {
            $semStart      = Carbon::parse($period->start_date)->startOfDay();
            $semEnd        = Carbon::parse($period->end_date)->endOfDay();
            $effectiveEnd  = $today->isAfter($semEnd) ? $semEnd->copy() : $today->copy();
            $semesterLabel = $period->name;

            $attendances = Attendance::whereIn('student_id', $studentIds)
                ->whereBetween('date', [$semStart->toDateString(), $effectiveEnd->toDateString()])
                ->get()
                ->groupBy('student_id');
        } else {
            // No academic semester exists yet — render an empty, zeroed report rather than
            // erroring, since $semStart/$semEnd have nothing meaningful to be.
            $semStart      = null;
            $semEnd        = null;
            $semesterLabel = 'No academic semester configured';
            $attendances   = collect();
        }

        $rows = $students->map(function (Student $s) use ($attendances) {
            $recs    = $attendances->get($s->student_id, collect());
            $present = $recs->filter(fn ($a) => strtolower($a->status) === 'present')->count();
            $absent  = $recs->filter(fn ($a) => strtolower($a->status) === 'absent')->count();
            $excused = $recs->filter(fn ($a) => strtolower($a->status) === 'absent_with_reason')->count();
            $total   = $present + $absent + $excused;
            $rate    = $total > 0 ? round(($present / $total) * 100) : null;

            return (object) [
                'student_id'      => $s->student_id,
                'student_name'    => $s->student_name,
                'batch_name'      => $s->batch?->batch_name ?? '—',
                'lecturer_id'     => $s->lecturer_id,
                'lecturer_name'   => $s->lecturer?->lecturer_name ?? '—',
                'warning_level'   => (int) ($s->warning_level ?? 0),
                'total_present'   => $present,
                'total_absent'    => $absent,
                'total_excused'   => $excused,
                'total_sessions'  => $total,
                'attendance_rate' => $rate ?? 0,
                'has_th'          => $absent > 0,
                'has_ths'         => $excused > 0,
                'is_perfect'      => $total > 0 && $rate !== null && $rate >= 100,
            ];
        });

        $totalSessions = $rows->sum('total_sessions');
        $totalPresent  = $rows->sum('total_present');
        $totalAbsent   = $rows->sum('total_absent');
        $totalExcused  = $rows->sum('total_excused');

        return [
            'rows'               => $rows,
            'selectedPeriodId'   => $period?->id ? (string) $period->id : null,
            'semesterLabel'      => $semesterLabel,
            'semStart'           => $semStart,
            'semEnd'             => $semEnd,
            'periodOptions'      => $this->buildAttendancePeriodOptions(),
            'totalSessions'      => $totalSessions,
            'totalStudents'      => $rows->count(),
            'totalPresent'       => $totalPresent,
            'totalAbsent'        => $totalAbsent,
            'totalExcused'       => $totalExcused,
            'avgAttendanceRate'  => $totalSessions > 0 ? round($totalPresent / $totalSessions * 100) : 0,
            'avgAbsentRate'      => $totalSessions > 0 ? round($totalAbsent  / $totalSessions * 100) : 0,
            'avgExcusedRate'     => $totalSessions > 0 ? round($totalExcused / $totalSessions * 100) : 0,
            'perfectCount'       => $rows->filter(fn ($r) => $r->is_perfect)->count(),
            'warn2Count'         => $rows->filter(fn ($r) => $r->warning_level >= 2)->count(),
            'thStudentCount'     => $rows->filter(fn ($r) => $r->has_th)->count(),
            'thsStudentCount'    => $rows->filter(fn ($r) => $r->has_ths)->count(),
            'today'              => $today,
            'filterHalaqah'      => $filterHalaqah,
            'halaqahOptions'     => Batch::active()->orderedByName()->pluck('batch_name'),
        ];
    }

    // ─── Admin Semester Hafazan ───────────────────────────────────────────────

    public function adminHafazanSemester(Request $request): View
    {
        $data = $this->buildHafazanSemesterData($request, null, true);
        $data['lecturerSummaries'] = $this->summarizeHafazanSemesterByLecturer($data['rows']);
        return view('admin.hafazan-semester-report', $data);
    }

    public function adminHafazanSemesterPdf(Request $request)
    {
        $data                = $this->buildHafazanSemesterData($request, null, true);
        $data['generatedAt'] = Carbon::now()->format('d M Y, h:i A');
        $pdf = Pdf::loadView('pdf.admin-hafazan-semester-report', $data)->setPaper('a4', 'landscape');
        return $pdf->download('admin-hafazan-semester-' . ($data['selectedPeriodId'] ?? 'none') . '.pdf');
    }

    public function adminHafazanSemesterLecturerDetail(Request $request, int $id): View
    {
        $lecturer = Lecturer::find($id);
        if (! $lecturer) abort(404);

        $data = $this->buildHafazanSemesterData($request, (string) $id, true);
        $data['lecturerName']  = $lecturer->lecturer_name;
        $data['lecturerPhone'] = $lecturer->phone ?? null;
        $data['lecturerId']    = $id;

        return view('admin.hafazan-semester-report-lecturer', $data);
    }

    /** Collapse per-student semester rows into per-lecturer summary counts for the breakdown table. */
    private function summarizeHafazanSemesterByLecturer($rows)
    {
        return $rows
            ->groupBy('lecturer_id')
            ->map(function ($group) {
                $total      = $group->count();
                $batches    = $group->map(fn ($r) => $r->batch_name)->filter(fn ($b) => $b !== '—')->unique()->sort()->values();
                $completed  = $group->where('status', 'Completed')->count();
                $inProgress = $group->where('status', 'In Progress')->count();
                $notRec     = $group->where('status', 'Not Recorded')->count();

                return (object) [
                    'lecturer_id'   => $group->first()->lecturer_id,
                    'lecturer_name' => $group->first()->lecturer_name,
                    'batch_name'    => $batches->implode(', ') ?: '—',
                    'total'         => $total,
                    'completed'     => $completed,
                    'in_progress'   => $inProgress,
                    'not_recorded'  => $notRec,
                    'progress_pct'  => $total > 0 ? (int) round($group->avg('progress_pct')) : 0,
                ];
            })
            ->values()
            ->sortBy('lecturer_name')
            ->values();
    }

    // ─── Admin Semester Attendance ────────────────────────────────────────────

    public function adminAttendanceSemester(Request $request): View
    {
        $data = $this->buildAttendanceSemesterData($request, null, true);
        return view('admin.attendance-semester-report', $data);
    }

    public function adminAttendanceSemesterPdf(Request $request)
    {
        $data                = $this->buildAttendanceSemesterData($request, null, true);
        $data['generatedAt'] = Carbon::now()->format('d M Y, h:i A');
        $pdf = Pdf::loadView('pdf.admin-attendance-semester-report', $data)->setPaper('a4', 'landscape');
        return $pdf->download('admin-attendance-semester-' . ($data['selectedPeriodId'] ?? 'none') . '.pdf');
    }

    // ─── Lecturer Semester Hafazan ────────────────────────────────────────────

    public function lecturerHafazanSemester(Request $request): View
    {
        $lecturerId = Lecturer::where('user_id', Auth::id())->value('lecturer_id');
        $data = $this->buildHafazanSemesterData($request, $lecturerId, false);
        $lecturer = Lecturer::find($lecturerId);
        $data['lecturerName']  = $lecturer?->lecturer_name ?? 'N/A';
        $data['lecturerPhone'] = $lecturer?->phone ?? null;
        return view('lecturer.hafazan-semester-report', $data);
    }

    public function lecturerHafazanSemesterPdf(Request $request)
    {
        $lecturerId = Lecturer::where('user_id', Auth::id())->value('lecturer_id');
        $data = $this->buildHafazanSemesterData($request, $lecturerId, false);
        $lecturer = Lecturer::find($lecturerId);
        $data['lecturerName']  = $lecturer?->lecturer_name ?? 'N/A';
        $data['lecturerPhone'] = $lecturer?->phone ?? null;
        $data['generatedAt']  = Carbon::now()->format('d M Y, h:i A');
        $pdf = Pdf::loadView('pdf.hafazan-semester-report', $data)->setPaper('a4', 'landscape');
        return $pdf->download('hafazan-semester-' . ($data['selectedPeriodId'] ?? 'none') . '.pdf');
    }

    // ─── Lecturer Semester Attendance ─────────────────────────────────────────

    public function lecturerAttendanceSemester(Request $request): View
    {
        $lecturerId = Lecturer::where('user_id', Auth::id())->value('lecturer_id');
        $data = $this->buildAttendanceSemesterData($request, $lecturerId, false);
        $lecturer = Lecturer::find($lecturerId);
        $data['lecturerName']  = $lecturer?->lecturer_name ?? 'N/A';
        $data['lecturerPhone'] = $lecturer?->phone ?? null;
        return view('lecturer.attendance-semester-report', $data);
    }

    public function lecturerAttendanceSemesterPdf(Request $request)
    {
        $lecturerId = Lecturer::where('user_id', Auth::id())->value('lecturer_id');
        $data = $this->buildAttendanceSemesterData($request, $lecturerId, false);
        $lecturer = Lecturer::find($lecturerId);
        $data['lecturerName']  = $lecturer?->lecturer_name ?? 'N/A';
        $data['lecturerPhone'] = $lecturer?->phone ?? null;
        $data['generatedAt']  = Carbon::now()->format('d M Y, h:i A');
        $pdf = Pdf::loadView('pdf.attendance-semester-report', $data)->setPaper('a4', 'landscape');
        return $pdf->download('attendance-semester-' . ($data['selectedPeriodId'] ?? 'none') . '.pdf');
    }

    // ─── Attendance Daily ─────────────────────────────────────────────────────

    public function lecturerAttendanceDaily(Request $request): View
    {
        $data = $this->buildAttendanceDailyData($request);
        return view('lecturer.attendance-daily-report', $data);
    }

    public function lecturerAttendanceDailyPdf(Request $request)
    {
        $data                = $this->buildAttendanceDailyData($request);
        $data['generatedAt'] = Carbon::now()->format('d M Y, h:i A');
        $pdf = Pdf::loadView('pdf.attendance-daily-report', $data)->setPaper('a4');
        return $pdf->download('attendance-daily-' . $data['date']->format('Y-m-d') . '.pdf');
    }

    private function buildAttendanceDailyData(Request $request): array
    {
        $lecturerId = Lecturer::where('user_id', Auth::id())->value('lecturer_id');
        $lecturer   = Lecturer::find($lecturerId);
        $today      = Carbon::today();
        $date       = Carbon::parse($request->input('date', $today->toDateString()))->startOfDay();

        $students   = Student::with('batch')
            ->operational()
            ->where('lecturer_id', $lecturerId)
            ->orderBy('student_name')
            ->get();
        $studentIds = $students->pluck('student_id');

        // Get attendance records for the selected date
        $attMap = Attendance::whereIn('student_id', $studentIds)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('student_id');

        $rows = $students->map(function (Student $s) use ($attMap) {
            $att    = $attMap->get($s->student_id);
            $status = $att ? strtolower($att->status) : null;

            // Turn the reason_type snake_case into a readable label
            $reasonLabel = $att?->reason_type
                ? ucwords(str_replace('_', ' ', $att->reason_type))
                : null;

            return (object) [
                'student_id'          => $s->student_id,
                'student_name'        => $s->student_name,
                'batch_name'          => $s->batch?->batch_name ?? '—',
                'status'              => $status,
                'status_label'        => match($status) {
                    'present'            => 'Present',
                    'absent'             => 'Absent (TH)',
                    'absent_with_reason' => 'Excused (THS)',
                    default              => 'Not Recorded',
                },
                'reason_type'         => $reasonLabel,
                'remarks'             => $att?->remarks,
                'attendance_id'       => $att?->attendance_id,
                'supporting_document' => $att?->supporting_document,
            ];
        });

        $presentCount = $rows->filter(fn ($r) => $r->status === 'present')->count();
        $absentCount  = $rows->filter(fn ($r) => $r->status === 'absent')->count();
        $excusedCount = $rows->filter(fn ($r) => $r->status === 'absent_with_reason')->count();
        $recorded     = $presentCount + $absentCount + $excusedCount;

        return [
            'rows'           => $rows,
            'date'           => $date,
            'today'          => $today,
            'presentCount'   => $presentCount,
            'absentCount'    => $absentCount,
            'excusedCount'   => $excusedCount,
            'attendanceRate' => $recorded > 0 ? round(($presentCount / $recorded) * 100) : null,
            'lecturerName'   => $lecturer?->lecturer_name ?? 'N/A',
            'lecturerPhone'  => $lecturer?->phone ?? null,
            'months'         => $this->buildMonthList($today),
        ];
    }

    // ─── Attendance Weekly ────────────────────────────────────────────────────

    public function lecturerAttendanceWeekly(Request $request): View
    {
        $data = $this->buildAttendanceWeeklyData($request);
        return view('lecturer.attendance-weekly-report', $data);
    }

    public function lecturerAttendanceWeeklyPdf(Request $request)
    {
        $data                = $this->buildAttendanceWeeklyData($request);
        $data['generatedAt'] = Carbon::now()->format('d M Y, h:i A');
        $pdf = Pdf::loadView('pdf.attendance-weekly-report', $data)->setPaper('a4', 'landscape');
        return $pdf->download('attendance-weekly-' . $data['weekStart']->format('Y-m-d') . '.pdf');
    }

    private function buildAttendanceWeeklyData(Request $request): array
    {
        $lecturerId    = Lecturer::where('user_id', Auth::id())->value('lecturer_id');
        $lecturer      = Lecturer::find($lecturerId);
        $today         = Carbon::today();
        $filterHalaqah = trim($request->input('halaqah', ''));

        $students = Student::with('batch')
            ->operational()
            ->where('lecturer_id', $lecturerId)
            ->when($filterHalaqah, fn ($q) => $q->whereHas('batch', fn ($b) => $b->where('batch_name', $filterHalaqah)))
            ->orderBy('student_name')
            ->get();
        $studentIds = $students->pluck('student_id');
        $batchIds   = $students->pluck('batch_id')->filter()->unique();

        // Muqarrar-based week selector (same pattern as buildWeeklyData)
        $allMuqarrars = Muqarrar::whereNotNull('week_start_date')
            ->where(fn ($q) => $q->whereIn('batch_id', $batchIds)->orWhereNull('batch_id'))
            ->orderBy('week_start_date')
            ->get();

        $selectedMuqarrarId = $request->input('muqarrar_id');

        if ($selectedMuqarrarId) {
            $muqarrar = $allMuqarrars->firstWhere('muqarrar_id', $selectedMuqarrarId)
                ?? Muqarrar::find($selectedMuqarrarId);
        } else {
            // Prefer the active academic semester's muqarrars (new records set
            // academic_period_id, not semester_id); fall back to the legacy
            // level-based lookup for data created before that column existed.
            $activePeriod  = AcademicPeriod::current();
            $semesterWeeks = $activePeriod
                ? Muqarrar::where('academic_period_id', $activePeriod->id)->orderBy('week_start_date')->get()
                : collect();

            if ($semesterWeeks->isEmpty()) {
                $semesterId    = Muqarrar::orderBy('semester_id', 'desc')->value('semester_id');
                $semesterWeeks = $semesterId
                    ? Muqarrar::where('semester_id', $semesterId)->orderBy('week_start_date')->get()
                    : collect();
            }

            $muqarrar = null;
            foreach ($semesterWeeks as $m) {
                if (! $m->week_start_date) continue;
                $ws = Carbon::parse($m->week_start_date)->startOfDay();
                if ($today->between($ws, $ws->copy()->addDays(6)->endOfDay())) {
                    $muqarrar = $m;
                    break;
                }
            }
            $muqarrar = $muqarrar ?? $semesterWeeks->last();
        }

        $weekStart = $muqarrar?->week_start_date
            ? Carbon::parse($muqarrar->week_start_date)->startOfDay()
            : $today->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $weekEnd   = $weekStart->copy()->addDays(6)->endOfDay();
        $weekLabel = $muqarrar
            ? ('Week ' . $muqarrar->week . ' (' . $weekStart->format('d M') . ' – ' . $weekEnd->format('d M Y') . ')')
            : ($weekStart->format('d M') . ' – ' . $weekEnd->format('d M Y'));

        $attendances = Attendance::whereIn('student_id', $studentIds)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->groupBy('student_id');

        $rows = $students->map(function (Student $s) use ($attendances) {
            $recs    = $attendances->get($s->student_id, collect());
            $present = $recs->filter(fn ($a) => strtolower($a->status) === 'present')->count();
            $absent  = $recs->filter(fn ($a) => strtolower($a->status) === 'absent')->count();
            $excused = $recs->filter(fn ($a) => strtolower($a->status) === 'absent_with_reason')->count();
            $total   = $present + $absent + $excused;
            $rate    = $total > 0 ? round(($present / $total) * 100) : null;

            return (object) [
                'student_id'    => $s->student_id,
                'student_name'  => $s->student_name,
                'batch_name'    => $s->batch?->batch_name ?? '—',
                'warning_level' => (int) ($s->warning_level ?? 0),
                'present'       => $present,
                'absent'        => $absent,
                'excused'       => $excused,
                'total_days'    => $total,
                'rate'          => $rate,
                'has_th'        => $absent > 0,
                'has_ths'       => $excused > 0,
                'is_perfect'    => $total > 0 && $rate !== null && $rate >= 100,
            ];
        });

        $totalStudents      = $rows->count();
        $totalSessions      = $rows->sum('total_days');
        $totalPresent       = $rows->sum('present');
        $totalAbsent        = $rows->sum('absent');
        $totalExcused       = $rows->sum('excused');
        $overallPresentRate = $totalSessions > 0 ? round(($totalPresent / $totalSessions) * 100) : null;
        $thRate             = $totalSessions > 0 ? round(($totalAbsent  / $totalSessions) * 100) : null;
        $thsRate            = $totalSessions > 0 ? round(($totalExcused / $totalSessions) * 100) : null;
        $perfectCount       = $rows->filter(fn ($r) => $r->is_perfect)->count();
        $thStudentCount     = $rows->filter(fn ($r) => $r->has_th)->count();
        $thsStudentCount    = $rows->filter(fn ($r) => $r->has_ths)->count();

        $halaqahOptions = Student::query()
            ->operational()
            ->with('batch')
            ->where('lecturer_id', $lecturerId)
            ->get()
            ->map(fn ($s) => $s->batch?->batch_name)
            ->filter()->sort()->unique()->values();

        $allWeeks = $allMuqarrars->sortBy('week_start_date')->values();

        return [
            'rows'               => $rows,
            'weekStart'          => $weekStart,
            'weekEnd'            => $weekEnd,
            'today'              => $today,
            'muqarrar'           => $muqarrar,
            'allWeeks'           => $allWeeks,
            'weekLabel'          => $weekLabel,
            'filterHalaqah'      => $filterHalaqah,
            'halaqahOptions'     => $halaqahOptions,
            'totalPresent'       => $totalPresent,
            'totalAbsent'        => $totalAbsent,
            'totalExcused'       => $totalExcused,
            'totalStudents'      => $totalStudents,
            'overallPresentRate' => $overallPresentRate,
            'thRate'             => $thRate,
            'thsRate'            => $thsRate,
            'perfectCount'       => $perfectCount,
            'thStudentCount'     => $thStudentCount,
            'thsStudentCount'    => $thsStudentCount,
            'lecturerName'       => $lecturer?->lecturer_name ?? 'N/A',
            'lecturerPhone'      => $lecturer?->phone ?? null,
            'months'             => $this->buildMonthList($today),
            'attendanceDetails'  => $this->buildAttendanceDetails($attendances),
        ];
    }

    // ─── Hafazan Monthly ──────────────────────────────────────────────────────

    public function lecturerHafazanMonthly(Request $request): View
    {
        $data = $this->buildHafazanMonthlyData($request);
        return view('lecturer.hafazan-monthly-report', $data);
    }

    public function lecturerHafazanMonthlyPdf(Request $request)
    {
        $data                = $this->buildHafazanMonthlyData($request);
        $data['generatedAt'] = Carbon::now()->format('d M Y, h:i A');
        $pdf = Pdf::loadView('pdf.hafazan-monthly-report', $data)->setPaper('a4');
        return $pdf->download('hafazan-monthly-' . $data['monthStr'] . '.pdf');
    }

    private function buildHafazanMonthlyData(Request $request): array
    {
        $lecturerId    = Lecturer::where('user_id', Auth::id())->value('lecturer_id');
        $lecturer      = Lecturer::find($lecturerId);
        $today         = Carbon::today();
        $monthStr      = $request->input('month', $today->format('Y-m'));
        $filterHalaqah = trim($request->input('halaqah', ''));
        $monthStart    = Carbon::parse($monthStr . '-01')->startOfMonth();
        $monthEnd      = $monthStart->copy()->endOfMonth();
        // For current month use today; for past months snap to last day of month
        $effectiveDate = $today->isAfter($monthEnd) ? $monthEnd->copy() : $today->copy();

        $students = Student::with('batch')
            ->operational()
            ->where('lecturer_id', $lecturerId)
            ->when($filterHalaqah, fn ($q) => $q->whereHas('batch', fn ($b) => $b->where('batch_name', $filterHalaqah)))
            ->orderBy('student_name')
            ->get();
        $studentIds = $students->pluck('student_id');

        // Find the weekly muqarrar active at effectiveDate.
        // Search ALL muqarrars across all semesters — the "latest semester" fallback
        // is wrong for past months because it returns a future week with a much higher
        // end_page, inflating the checkpoint and marking everyone as Behind.
        $allWeeks = Muqarrar::whereNotNull('week_start_date')
            ->orderBy('week_start_date')
            ->get();

        // 1. Prefer a week whose date range contains effectiveDate exactly.
        $muqarrar = null;
        foreach ($allWeeks as $m) {
            $ws = Carbon::parse($m->week_start_date)->startOfDay();
            if ($effectiveDate->between($ws, $ws->copy()->addDays(6)->endOfDay())) {
                $muqarrar = $m;
                break;
            }
        }

        // 2. If no week covers effectiveDate (e.g. semester break), use the most
        //    recent week that started at or before effectiveDate.
        if (! $muqarrar) {
            foreach ($allWeeks->sortByDesc('week_start_date') as $m) {
                $ws = Carbon::parse($m->week_start_date)->startOfDay();
                if ($ws->lte($effectiveDate)) {
                    $muqarrar = $m;
                    break;
                }
            }
        }

        // Checkpoint at effectiveDate — identical formula to weekly report
        $endPage    = $muqarrar ? (int) $muqarrar->end_page : 0;
        $checkpoint = 0;
        if ($muqarrar) {
            $ws = $muqarrar->week_start_date ? Carbon::parse($muqarrar->week_start_date)->startOfDay() : null;
            $we = $ws?->copy()->addDays(6)->endOfDay();

            if ($we && $effectiveDate->isAfter($we)) {
                $checkpoint = $endPage;
            } elseif ($ws) {
                $diff       = (int) $ws->diffInDays(Carbon::parse($effectiveDate->format('Y-m-d'))->startOfDay(), false);
                $day        = max(1, min(5, $diff + 1));
                $weekly     = $endPage - (int) $muqarrar->start_page;
                $checkpoint = (int) min($endPage, (int) round((int) $muqarrar->start_page + ($weekly / 5) * $day));
            } else {
                $checkpoint = $endPage;
            }
        }

        // Latest page per student — all history up to effectiveDate
        $latestPageMap = collect();
        if ($studentIds->isNotEmpty()) {
            $latestPageMap = DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '<=', $effectiveDate->toDateString())
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as best_page'))
                ->groupBy('t.student_id')
                ->get()
                ->keyBy('student_id');
        }

        // Best page recorded within this month — also used to determine has_record
        $bestInMonthMap = collect();
        if ($studentIds->isNotEmpty()) {
            $bestInMonthMap = DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '>=', $monthStart->toDateString())
                ->whereDate('t.tasmi_date', '<=', $effectiveDate->toDateString())
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as best_page'))
                ->groupBy('t.student_id')
                ->get()
                ->keyBy('student_id');
        }

        // Page before month start — denominator for pages_this_month
        $pageBeforeMonthMap = collect();
        if ($studentIds->isNotEmpty()) {
            $pageBeforeMonthMap = DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '<', $monthStart->toDateString())
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as page_before'))
                ->groupBy('t.student_id')
                ->get()
                ->keyBy('student_id');
        }

        $khatam = KhatamRecord::countsAsOf($studentIds, $effectiveDate);

        $rows = $students->map(function (Student $s) use (
            $latestPageMap, $bestInMonthMap, $pageBeforeMonthMap, $checkpoint, $endPage, $khatam
        ) {
            $hasRecord  = $bestInMonthMap->has($s->student_id);
            $rawPage    = $latestPageMap->get($s->student_id)?->best_page ?? null;
            $latestPage = $rawPage !== null ? (int) $rawPage : null;

            $pagesThisMonth = null;
            if ($hasRecord) {
                $bestInMonth    = (int) $bestInMonthMap->get($s->student_id)->best_page;
                $pageBefore     = $pageBeforeMonthMap->has($s->student_id)
                    ? (int) $pageBeforeMonthMap->get($s->student_id)->page_before
                    : 0;
                $pagesThisMonth = max(0, $bestInMonth - $pageBefore);
            }

            if (! $hasRecord) {
                $status = 'Not Recorded'; $color = 'gray';
            } elseif ($khatam->has($s->student_id)) {
                $status = 'Completed'; $color = 'green';
            } elseif ($endPage > 0 && $latestPage >= $endPage) {
                $status = 'Completed'; $color = 'green';
            } else {
                $gap = ($checkpoint > 0 && $latestPage !== null) ? ($checkpoint - $latestPage) : 0;
                if ($gap <= 0) {
                    $status = 'On Track'; $color = 'blue';
                } elseif ($gap <= 5) {
                    $status = 'Behind'; $color = 'yellow';
                } elseif ($gap <= 10) {
                    $status = 'Behind'; $color = 'orange';
                } else {
                    $status = 'Behind'; $color = 'red';
                }
            }

            return (object) [
                'student_id'       => $s->student_id,
                'student_name'     => $s->student_name,
                'batch_name'       => $s->batch?->batch_name ?? '—',
                'latest_page'      => $latestPage,
                'pages_this_month' => $pagesThisMonth,
                'has_record'       => $hasRecord,
                'status'           => $status,
                'color'            => $color,
            ];
        });

        $halaqahOptions = Student::query()
            ->operational()
            ->with('batch')
            ->where('lecturer_id', $lecturerId)
            ->get()
            ->map(fn ($s) => $s->batch?->batch_name)
            ->filter()->sort()->unique()->values();

        $total    = $rows->count();
        $recorded = $rows->where('has_record', true)->count();

        return [
            'rows'             => $rows,
            'monthStart'       => $monthStart,
            'monthEnd'         => $monthEnd,
            'monthStr'         => $monthStr,
            'today'            => $today,
            'months'           => $this->buildMonthList($today),
            'filterHalaqah'    => $filterHalaqah,
            'halaqahOptions'   => $halaqahOptions,
            'completedCount'   => $rows->where('status', 'Completed')->count(),
            'onTrackCount'     => $rows->where('status', 'On Track')->count(),
            'behindCount'      => $rows->where('status', 'Behind')->count(),
            'notRecordedCount' => $rows->where('status', 'Not Recorded')->count(),
            'lecturerName'     => $lecturer?->lecturer_name ?? 'N/A',
            'lecturerPhone'    => $lecturer?->phone ?? null,
            'total'            => $total,
            'recorded'         => $recorded,
        ];
    }
}
