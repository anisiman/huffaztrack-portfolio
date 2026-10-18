<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\KhatamRecord;
use App\Models\Lecturer;
use App\Models\Muqarrar;
use App\Models\Result;
use App\Models\Student;
use App\Models\Tasmi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LecturerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $lecturerId = Lecturer::where('user_id', Auth::id())->value('lecturer_id');
        $lecturer   = Lecturer::find($lecturerId);
        $today        = Carbon::today();
        $selectedDate = Carbon::parse($request->get('date', $today->toDateString()))->startOfDay();
        if ($selectedDate->isAfter($today)) {
            $selectedDate = $today->copy();
        }
        $dateFrom    = $selectedDate->copy()->startOfWeek(Carbon::MONDAY);
        $dateTo      = $selectedDate->copy();
        $periodLabel = $selectedDate->format('d M Y');

        // ── Students ──────────────────────────────────────────────────────────
        $students      = Student::with('batch')->operational()->where('lecturer_id', $lecturerId)->get();
        $totalStudents = $students->count();
        $studentIds    = $students->pluck('student_id');

        // ── Warning students ──────────────────────────────────────────────────
        $warningStudents = $students
            ->filter(fn ($s) => (int) ($s->warning_level ?? 0) > 0)
            ->sortByDesc('warning_level')
            ->take(5)
            ->values();
        $warningCount = $warningStudents->count();

        // ── Attendance for selected date only ─────────────────────────────────
        $periodRows = $studentIds->isNotEmpty()
            ? Attendance::whereIn('student_id', $studentIds)
                ->whereDate('date', $selectedDate->toDateString())
                ->get()
            : collect();

        $totalPeriodRecords = $periodRows->count();
        $presentToday       = $periodRows->filter(fn ($a) => strtolower($a->status) === 'present')->count();
        $attendanceRate     = $totalPeriodRecords > 0 ? round($presentToday / $totalPeriodRecords * 100) : 0;

        // ── Hafazan progress — ceiling = end of selected period so the card
        //    reflects "Last Month" / "Last Week" rather than always showing today
        [$onTrackCount, $behindCount, $weekLabel] = $this->hafazanStatus($studentIds, $today, $dateTo);
        $onTrackRate = $totalStudents > 0 ? round(($onTrackCount / $totalStudents) * 100) : 0;

        // ── Academic / CGPA data ──────────────────────────────────────────────
        $latestResults = Result::select('result.*')
            ->join(DB::raw('(
                SELECT student_id, MAX(result_id) as latest_result_id
                FROM result
                GROUP BY student_id
            ) as lr'), function ($join) {
                $join->on('result.student_id', '=', 'lr.student_id')
                     ->on('result.result_id', '=', 'lr.latest_result_id');
            });

        $cgpaBase = fn () => Student::query()
            ->joinSub($latestResults, 'latest_results', fn ($j) =>
                $j->on('student.student_id', '=', 'latest_results.student_id'))
            ->where('student.status', 'AKTIF')
            ->where('student.lecturer_id', $lecturerId);

        $averageCgpa = $cgpaBase()->avg('latest_results.cgpa');
        $highestCgpa = $cgpaBase()->max('latest_results.cgpa');
        $atRiskCount = $cgpaBase()->where('latest_results.cgpa', '<', 2.50)->count();

        $atRiskStudents = Student::query()
            ->joinSub($latestResults, 'latest_results', fn ($j) =>
                $j->on('student.student_id', '=', 'latest_results.student_id'))
            ->join('batch', 'student.batch_id', '=', 'batch.batch_id')
            ->where('student.status', 'AKTIF')
            ->where('student.lecturer_id', $lecturerId)
            ->where('latest_results.cgpa', '<', 2.50)
            ->orderBy('latest_results.cgpa')
            ->limit(5)
            ->select('student.student_name', 'student.student_id', 'batch.batch_name', 'latest_results.cgpa')
            ->get();

        $topStudents = Student::query()
            ->joinSub($latestResults, 'latest_results', fn ($j) =>
                $j->on('student.student_id', '=', 'latest_results.student_id'))
            ->join('batch', 'student.batch_id', '=', 'batch.batch_id')
            ->where('student.status', 'AKTIF')
            ->where('student.lecturer_id', $lecturerId)
            ->orderByDesc('latest_results.cgpa')
            ->limit(5)
            ->select('student.student_name', 'student.student_id', 'batch.batch_name', 'latest_results.cgpa')
            ->get();

        return view('lecturer.dashboard', compact(
            'selectedDate',
            'periodLabel',
            'dateFrom',
            'dateTo',
            // ── Original (attendance / hafazan / warning) ──────────────────
            'lecturer',
            'totalStudents',
            'warningCount',
            'warningStudents',
            'presentToday',
            'attendanceRate',
            'onTrackCount',
            'onTrackRate',
            'behindCount',
            'weekLabel',
            'today',
            // ── Academic / CGPA (new section) ─────────────────────────────
            'averageCgpa',
            'highestCgpa',
            'atRiskCount',
            'atRiskStudents',
            'topStudents'
        ));
    }

    /**
     * Compute On Track / Behind counts using the identical logic as LecturerHifzController
     * (Daily Tasmi' Workspace) so both views always show consistent numbers.
     *
     * Dashboard "On Track" = Ahead + On-track (workspace statuses).
     * Dashboard "Behind"   = Behind + Not Recorded.
     *
     * @return array{0: int, 1: int, 2: string}  [onTrackCount, behindCount, weekLabel]
     */
    private function hafazanStatus($studentIds, Carbon $today, ?Carbon $periodEnd = null): array
    {
        if ($studentIds->isEmpty()) {
            return [0, 0, 'N/A'];
        }

        // $periodEnd is the last day of the selected period (e.g., last day of last month).
        // Fall back to today so "Today" / "This Week" still work correctly.
        $ceiling = $periodEnd ?? $today;

        // Load students with batch_id so each can be matched to its muqarrar
        $studentsMap = Student::whereIn('student_id', $studentIds)
            ->get(['student_id', 'batch_id'])
            ->keyBy('student_id');

        $batchIds = $studentsMap->pluck('batch_id')->filter()->unique();

        // Batch-specific muqarrars first; semester-wide (null batch_id) as fallback
        $allMuqarrars = Muqarrar::whereNotNull('week_start_date')
            ->where(fn ($q) => $q->whereIn('batch_id', $batchIds)->orWhereNull('batch_id'))
            ->orderBy('week_start_date')
            ->get();

        // Use the most recent tasmi date at or before the period ceiling.
        // This prevents weekends/holidays wiping the counts and correctly
        // reflects past periods (Last Week, Last Month, etc.).
        $latestDate    = Tasmi::whereIn('student_id', $studentIds)
            ->whereDate('tasmi_date', '<=', $ceiling->toDateString())
            ->max('tasmi_date');
        $effectiveDate = $latestDate ? Carbon::parse($latestDate)->startOfDay() : $ceiling;

        // Find the muqarrar covering the effective date for a given batch (mirrors LecturerHifzController)
        $getMuqarrarForBatch = function ($batchId) use ($allMuqarrars, $effectiveDate): ?Muqarrar {
            $subset = $batchId ? $allMuqarrars->where('batch_id', $batchId) : collect();
            if ($subset->isEmpty()) {
                $subset = $allMuqarrars->whereNull('batch_id');
            }
            foreach ($subset->sortBy('week_start_date') as $m) {
                $ws = Carbon::parse($m->week_start_date)->startOfDay();
                $we = $ws->copy()->addDays(6)->endOfDay();
                if ($effectiveDate->between($ws, $we)) {
                    return $m;
                }
            }
            return $subset->sortBy('week_start_date')->last();
        };

        // Daily checkpoint for a muqarrar on the effective date (mirrors LecturerHifzController)
        $getCheckpoint = function (?Muqarrar $muqarrar) use ($effectiveDate): int {
            if (! $muqarrar) return 0;
            $totalDays = 5;
            if ($muqarrar->week_start_date) {
                $ws        = Carbon::parse($muqarrar->week_start_date)->startOfDay();
                $sel       = Carbon::parse($effectiveDate->format('Y-m-d'))->startOfDay();
                $diffDays  = (int) $ws->diffInDays($sel, false);
                $dayOfWeek = max(1, min($totalDays, $diffDays + 1));
            } else {
                $dayOfWeek = max(1, min($totalDays, $effectiveDate->dayOfWeekIso));
            }
            $weekly = (int) $muqarrar->end_page - (int) $muqarrar->start_page;
            return (int) min(
                (int) $muqarrar->end_page,
                (int) round((int) $muqarrar->start_page + ($weekly / $totalDays) * $dayOfWeek)
            );
        };

        // Today's tasmi keyed by student_id (same query as LecturerHifzController)
        $tasmiToday = Tasmi::with('progress')
            ->whereIn('student_id', $studentIds)
            ->whereDate('tasmi_date', $effectiveDate)
            ->get()
            ->keyBy('student_id');

        $khatamCounts = KhatamRecord::countsAsOf($studentIds, $effectiveDate);

        $onTrackCount = 0;
        $behindCount  = 0;

        foreach ($studentIds as $sid) {
            $student    = $studentsMap->get($sid);
            $muqarrar   = $getMuqarrarForBatch($student?->batch_id);
            $checkpoint = $getCheckpoint($muqarrar);
            $endPage    = (int) ($muqarrar?->end_page ?? 0);

            $tasmi       = $tasmiToday->get($sid);
            $pageReached = (int) ($tasmi?->progress?->page_reached ?? 0);
            $hasRecord   = $tasmi !== null && $pageReached > 0;

            if (! $hasRecord) {
                $behindCount++;                          // Not Recorded → Behind
            } elseif ($khatamCounts->has($sid)) {
                $onTrackCount++;                         // Khatam — Muqarrar already fulfilled
            } elseif ($checkpoint > 0 && $pageReached >= $checkpoint) {
                $onTrackCount++;                         // Ahead or On-track
            } else {
                $behindCount++;                          // Behind
            }
        }

        // Week label from the muqarrar covering the effective date
        $refMuqarrar = null;
        foreach ($allMuqarrars as $m) {
            if (! $m->week_start_date) continue;
            $ws = Carbon::parse($m->week_start_date)->startOfDay();
            $we = $ws->copy()->addDays(6)->endOfDay();
            if ($effectiveDate->between($ws, $we)) {
                $refMuqarrar = $m;
                break;
            }
        }
        $refMuqarrar = $refMuqarrar ?? $allMuqarrars->last();
        $weekLabel   = $refMuqarrar ? 'Week ' . $refMuqarrar->week : 'N/A';

        return [$onTrackCount, $behindCount, $weekLabel];
    }
}
