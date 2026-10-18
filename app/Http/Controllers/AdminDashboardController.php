<?php

namespace App\Http\Controllers;

use App\Models\KhatamRecord;
use App\Models\Student;
use App\Models\User;
use App\Models\Attendance;
use App\Models\College;
use App\Models\Batch;
use App\Models\Result;
use App\Models\Muqarrar;
use App\Models\SubjectAttendanceRecord;
use App\Models\WarningLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Teammate: delegates to private method (cleaner structure, supports analytics separation)
        return $this->generateDashboardData($request);
    }

    private function generateDashboardData(Request $request)
    {
        $currentBatch = $request->get('batch', 'Overall');

        // BASE QUERY — uses STATUS_ACTIVE constant from Student model (teammate)
        $baseQuery = Student::where('status', Student::STATUS_ACTIVE);

        if ($currentBatch !== 'Overall') {
            $baseQuery->whereHas('batch', function ($q) use ($currentBatch) {
                $q->where('batch_name', $currentBatch);
            });
        }

        $students       = $baseQuery->get();
        $totalStudents  = $students->count();
        $maleStudents   = $students->where('gender', Student::GENDER_MALE)->count();
        $femaleStudents = $students->where('gender', Student::GENDER_FEMALE)->count();

        // Derive college membership via student -> program -> college relationship
        // Normalize whitespace/newlines so CSV-imported names match expected labels
        $collegeCounts = $students->map(fn($s) => optional(optional($s->program)->college)->college_name)
            ->filter()
            ->map(fn($name) => preg_replace('/\s+/', ' ', trim(str_replace(["\r\n", "\n", "\r"], ' ', $name))))
            ->countBy();

        $engineeringStudents = (int) ($collegeCounts->get('FACULTY OF ENGINEERING (FOE)') ?? 0);
        $computingStudents   = (int) ($collegeCounts->get('FACULTY OF COMPUTING (FOC)') ?? 0);
        $businessStudents    = (int) ($collegeCounts->get('FACULTY OF BUSINESS (FOB)') ?? 0);
        $totalLecturers = User::where('role', 'lecturer')->count();

        // Date filter — single day selected via date picker, default to today
        $today        = Carbon::today();
        $selectedDate = $request->get('date', $today->toDateString());
        $dateFrom     = Carbon::parse($selectedDate)->startOfDay();
        $dateTo       = Carbon::parse($selectedDate)->endOfDay();
        $periodLabel  = $dateFrom->format('d M Y');

        $studentIds = $students->pluck('student_id');

        // Attendance for selected date
        $totalAttRecords  = Attendance::whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->whereIn('student_id', $studentIds)->count();
        $presentCount     = Attendance::whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->where('status', 'present')->whereIn('student_id', $studentIds)->count();
        $attendanceRate   = $totalAttRecords > 0 ? round($presentCount / $totalAttRecords * 100) : 0;
        $absentTodayCount = Attendance::whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->where('status', 'absent')->whereIn('student_id', $studentIds)->count();

        // Batch distribution — filter tabs mirror Batch Management's "is_active" flag,
        // so the tab list always matches what Batch Management shows as active.
        $batchDistribution = Batch::active()->orderedByName()->pluck('batch_name', 'batch_name');

        $totalActiveBatches = $batchDistribution->count();

        // Active Batches card — mirrors Batch Management's "is_active" flag directly
        // (not "has current students"), so the two pages always agree on which
        // batches are shown as active.
        $activeBatchesList = Batch::where('is_active', true)
            ->withCount(['students as active_students_count' => function ($query) {
                $query->where('status', Student::STATUS_ACTIVE);
            }])
            ->orderByRaw('CAST(SUBSTRING(batch_name, 2) AS UNSIGNED) ASC')
            ->pluck('active_students_count', 'batch_name');

        $activeBatchesCount = $activeBatchesList->count();

        // ── Operational At-Risk ──────────────────────────────────────────────

        // Compute a per-batch checkpoint so T13 and T14 (different page ranges)
        // are each compared against their own muqarrar schedule.
        $opCheckpointByBatch = [];
        $allBatchMuqs = Muqarrar::whereNotNull('week_start_date')
            ->whereNotNull('batch_id')
            ->orderBy('week_start_date')
            ->get()
            ->groupBy('batch_id');

        foreach ($allBatchMuqs as $batchId => $weeks) {
            $activeMuq = null;
            foreach ($weeks as $m) {
                $ws = Carbon::parse($m->week_start_date)->startOfDay();
                if ($today->between($ws, $ws->copy()->addDays(6)->endOfDay())) {
                    $activeMuq = $m;
                    break;
                }
            }
            $activeMuq = $activeMuq ?? $weeks->last();
            if (!$activeMuq) continue;

            // Always use end_page — stable reference regardless of day within the week,
            // prevents the proportional checkpoint from making everyone look On Track
            // at the start of a week or Far Behind at the end.
            $opCheckpointByBatch[$batchId] = (int)$activeMuq->end_page;
        }
        // Scalar fallback used by legacy view variables
        $opCheckpoint = !empty($opCheckpointByBatch) ? max($opCheckpointByBatch) : null;

        // Latest hafazan page per operational student — include batch_id for per-batch gap
        $opBestPages = DB::table('tasmi as t')
            ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
            ->join('student as s', 't.student_id', '=', 's.student_id')
            ->join('batch as b', 's.batch_id', '=', 'b.batch_id')
            ->where('s.status', Student::STATUS_ACTIVE)
            ->whereNotNull('s.lecturer_id')
            ->when($currentBatch !== 'Overall', fn ($q) => $q->where('b.batch_name', $currentBatch))
            ->select('t.student_id', 's.batch_id', DB::raw('MAX(tp.page_reached) as max_page'))
            ->groupBy('t.student_id', 's.batch_id')
            ->get()
            ->keyBy('student_id');

        // Students behind muqarrar checkpoint, split by severity (per-batch)
        $criticalHafazanIds  = collect();
        $needsAttentionCount = 0;
        foreach ($opBestPages as $sid => $row) {
            $checkpoint = $opCheckpointByBatch[$row->batch_id] ?? null;
            if ($checkpoint === null) continue;
            $gap = $checkpoint - (int)$row->max_page;
            if ($gap > 5)       $criticalHafazanIds->push($sid);
            elseif ($gap >= 1)  $needsAttentionCount++;
        }
        $criticalHafazanCount  = $criticalHafazanIds->count();
        $behindMuqarrarCount   = $criticalHafazanCount + $needsAttentionCount;

        $criticalHafazanStudents = collect();
        if ($criticalHafazanIds->isNotEmpty()) {
            $criticalHafazanStudents = Student::whereIn('student.student_id', $criticalHafazanIds->all())
                ->join('batch', 'student.batch_id', '=', 'batch.batch_id')
                ->select('student.student_name', 'student.student_id', 'batch.batch_name', 'student.batch_id', 'student.lecturer_id')
                ->get()
                ->map(function ($s) use ($opBestPages, $opCheckpointByBatch) {
                    $checkpoint       = $opCheckpointByBatch[$s->batch_id] ?? 0;
                    $s->current_page  = (int)($opBestPages[$s->student_id]->max_page ?? 0);
                    $s->target_page   = $checkpoint;
                    $s->gap           = $checkpoint - $s->current_page;
                    return $s;
                })
                ->sortByDesc('gap')
                ->take(5)
                ->values();
        }

        // Warning letters: active, highest level first (batch-filter-aware)
        $warningStudentIds = WarningLetter::where('warning_letter.status', 'active')
            ->join('student', 'warning_letter.student_id', '=', 'student.student_id')
            ->join('batch', 'student.batch_id', '=', 'batch.batch_id')
            ->when($currentBatch !== 'Overall', fn ($q) => $q->where('batch.batch_name', $currentBatch))
            ->distinct()->pluck('warning_letter.student_id');
        $warningStudentCount = $warningStudentIds->count();

        $warningLetterData = WarningLetter::where('warning_letter.status', 'active')
            ->join('student', 'warning_letter.student_id', '=', 'student.student_id')
            ->join('batch', 'student.batch_id', '=', 'batch.batch_id')
            ->when($currentBatch !== 'Overall', fn ($q) => $q->where('batch.batch_name', $currentBatch))
            ->select(
                'warning_letter.student_id',
                'student.student_name',
                'batch.batch_name',
                'student.lecturer_id',
                DB::raw('MAX(warning_letter.warning_level) as max_level'),
                DB::raw('MAX(warning_letter.attendance_count) as max_attendance_count')
            )
            ->groupBy('warning_letter.student_id', 'student.student_name', 'batch.batch_name', 'student.lecturer_id')
            ->orderByDesc('max_level')
            ->orderByDesc('max_attendance_count')
            ->limit(5)
            ->get();

        // Operational at-risk count: deduplicated union of critical hafazan + warning letters
        $atRiskOperationalCount = $criticalHafazanIds->merge($warningStudentIds)->unique()->count();

        // ── Teammate: Latest result subquery ─────────────────────────────────
        $latestResults = Result::select('result.*')
            ->join(DB::raw('
                (
                    SELECT student_id, MAX(result_id) as latest_result_id
                    FROM result
                    GROUP BY student_id
                ) latest_result
            '), function ($join) {
                $join->on('result.student_id', '=', 'latest_result.student_id')
                    ->on('result.result_id', '=', 'latest_result.latest_result_id');
            });

        // Average CGPA per batch (teammate)
        $cgpaPerBatch = Student::query()
            ->leftJoinSub($latestResults, 'latest_results', fn ($join) =>
                $join->on(DB::raw("REPLACE(student.student_id, ' ', '')"), '=', 'latest_results.student_id'))
            ->join('batch', 'student.batch_id', '=', 'batch.batch_id')
            ->where('student.status', 'AKTIF')
            ->when($currentBatch !== 'Overall', fn ($q) =>
                $q->whereHas('batch', fn ($b) => $b->where('batch_name', $currentBatch)))
            ->groupBy('batch.batch_name')
            ->orderBy('batch.batch_name')
            ->select('batch.batch_name', DB::raw('ROUND(AVG(latest_results.cgpa), 2) as average_cgpa'))
            ->get();

        // CGPA distribution (teammate)
        $cgpaDistribution = [
            'mumtaz'        => 0,
            'jayyid_jiddan' => 0,
            'jayyid'        => 0,
            'maqbul'        => 0,
            'rasib'         => 0,
        ];

        $latestCgpas = Student::query()
            ->leftJoinSub($latestResults, 'latest_results', fn ($join) =>
                $join->on(DB::raw("REPLACE(student.student_id, ' ', '')"), '=', 'latest_results.student_id'))
            ->where('student.status', 'AKTIF')
            ->when($currentBatch !== 'Overall', fn ($q) =>
                $q->whereHas('batch', fn ($b) => $b->where('batch_name', $currentBatch)))
            ->whereNotNull('latest_results.cgpa')
            ->pluck('latest_results.cgpa');

        foreach ($latestCgpas as $cgpa) {
            if ($cgpa >= 3.70)     $cgpaDistribution['mumtaz']++;
            elseif ($cgpa >= 3.00) $cgpaDistribution['jayyid_jiddan']++;
            elseif ($cgpa >= 2.50) $cgpaDistribution['jayyid']++;
            elseif ($cgpa >= 2.00) $cgpaDistribution['maqbul']++;
            else                   $cgpaDistribution['rasib']++;
        }

        // At-risk students by CGPA (teammate)
        $atRiskStudents = Student::query()
            ->joinSub($latestResults, 'latest_results', fn ($join) =>
                $join->on(DB::raw("REPLACE(student.student_id, ' ', '')"), '=', 'latest_results.student_id'))
            ->join('batch', 'student.batch_id', '=', 'batch.batch_id')
            ->where('student.status', 'AKTIF')
            ->when($currentBatch !== 'Overall', fn ($q) =>
                $q->whereHas('batch', fn ($b) => $b->where('batch_name', $currentBatch)))
            ->where('latest_results.cgpa', '<', 2.50)
            ->orderBy('latest_results.cgpa')
            ->limit(5)
            ->select('student.student_name', 'student.student_id', 'batch.batch_name', 'latest_results.cgpa')
            ->get();

        // Top students by CGPA (teammate)
        $topStudents = Student::query()
            ->joinSub($latestResults, 'latest_results', fn ($join) =>
                $join->on(DB::raw("REPLACE(student.student_id, ' ', '')"), '=', 'latest_results.student_id'))
            ->join('batch', 'student.batch_id', '=', 'batch.batch_id')
            ->where('student.status', 'AKTIF')
            ->when($currentBatch !== 'Overall', fn ($q) =>
                $q->whereHas('batch', fn ($b) => $b->where('batch_name', $currentBatch)))
            ->where('latest_results.cgpa', '>=', 3.50)
            ->orderByDesc('latest_results.cgpa')
            ->limit(5)
            ->select('student.student_name', 'student.student_id', 'batch.batch_name', 'latest_results.cgpa')
            ->get();

        return view('admin.dashboard', compact(
            'selectedDate',
            'periodLabel',
            'totalStudents',
            'totalLecturers',
            'maleStudents',
            'femaleStudents',
            'engineeringStudents',
            'computingStudents',
            'businessStudents',
            'presentCount',
            'attendanceRate',
            'absentTodayCount',
            'totalActiveBatches',
            'batchDistribution',
            'activeBatchesList',
            'activeBatchesCount',
            'currentBatch',
            'atRiskOperationalCount',   // deduplicated union: critical hafazan + warning letters
            'criticalHafazanCount',     // High Risk: gap > 5 pages
            'needsAttentionCount',      // Needs Attention: gap 1–5 pages
            'behindMuqarrarCount',      // total students behind muqarrar (any gap > 0)
            'warningStudentCount',      // actual total students with active warning letters
            'criticalHafazanStudents',  // top 5 critical hafazan for attention section
            'warningLetterData',        // top 5 active warning letters for attention section
            'cgpaPerBatch',             // teammate — CGPA per batch
            'cgpaDistribution',         // teammate — CGPA grade breakdown
            'atRiskStudents',           // teammate — students with CGPA < 2.50
            'topStudents'               // teammate — top students by CGPA
        ));
    }

    public function topPerformers(Request $request)
    {
        return $this->cgpaLeaderboard($request, 'top');
    }

    public function atRiskList(Request $request)
    {
        return $this->cgpaLeaderboard($request, 'at_risk');
    }

    private function cgpaLeaderboard(Request $request, string $mode)
    {
        $currentBatch = $request->get('batch', 'Overall');
        $search = $request->get('search');

        $latestResults = Result::select('result.*')
            ->join(DB::raw('
                (
                    SELECT student_id, MAX(result_id) as latest_result_id
                    FROM result
                    GROUP BY student_id
                ) latest_result
            '), function ($join) {
                $join->on('result.student_id', '=', 'latest_result.student_id')
                    ->on('result.result_id', '=', 'latest_result.latest_result_id');
            });

        $query = Student::query()
            ->joinSub($latestResults, 'latest_results', fn ($join) =>
                $join->on(DB::raw("REPLACE(student.student_id, ' ', '')"), '=', 'latest_results.student_id'))
            ->join('batch', 'student.batch_id', '=', 'batch.batch_id')
            ->where('student.status', Student::STATUS_ACTIVE)
            ->when($currentBatch !== 'Overall', fn ($q) =>
                $q->whereHas('batch', fn ($b) => $b->where('batch_name', $currentBatch)))
            ->when($search, fn ($q) =>
                $q->where(fn ($sq) => $sq->where('student.student_name', 'like', "%{$search}%")
                    ->orWhere('student.student_id', 'like', "%{$search}%")))
            ->select('student.student_name', 'student.student_id', 'batch.batch_name', 'latest_results.cgpa');

        if ($mode === 'at_risk') {
            $query->where('latest_results.cgpa', '<', 2.50)->orderBy('latest_results.cgpa');
        } else {
            $query->where('latest_results.cgpa', '>=', 3.50)->orderByDesc('latest_results.cgpa');
        }

        $students = $query->paginate(20)->withQueryString();
        $batches = Batch::active()->orderedByName()->get();

        return view('admin.cgpa-leaderboard', compact('students', 'batches', 'currentBatch', 'search', 'mode'));
    }

    public function analytics(Request $request)
    {
        $currentBatch = $request->get('batch', 'Overall');
        $today        = Carbon::today();
        $selectedDate = $request->get('date', $today->toDateString());
        $selectedDay  = Carbon::parse($selectedDate)->startOfDay();
        // Manual Mon-start calc: avoids Carbon 3.x locale edge case on Sundays
        $weekStart    = $selectedDay->copy()->subDays(($selectedDay->dayOfWeek + 6) % 7)->startOfDay();
        $weekEnd      = $selectedDay->copy()->endOfDay();
        $monthStart   = $selectedDay->copy()->startOfMonth()->startOfDay();
        $dateFrom     = $weekStart->copy();
        $dateTo       = $weekEnd->copy();
        $periodLabel  = $selectedDay->format('d M Y');

        $students = Student::query()
            ->where('status', 'AKTIF')
            ->when($currentBatch !== 'Overall', function ($query) use ($currentBatch) {
                $query->whereHas('batch', function ($q) use ($currentBatch) {
                    $q->where('batch_name', $currentBatch);
                });
            });

        $latestResults = Result::select('result.*')
            ->join(DB::raw('
                (
                    SELECT student_id, MAX(result_id) as latest_result_id
                    FROM result
                    GROUP BY student_id
                ) latest_result
            '), function ($join) {
                $join->on('result.student_id', '=', 'latest_result.student_id')
                    ->on('result.result_id', '=', 'latest_result.latest_result_id');
            });

        $averageCgpa = Student::query()
            ->joinSub($latestResults, 'latest_results', fn ($join) =>
                $join->on(DB::raw("REPLACE(student.student_id, ' ', '')"), '=', 'latest_results.student_id'))
            ->where('student.status', 'AKTIF')
            ->when($currentBatch !== 'Overall', fn ($q) =>
                $q->whereHas('batch', fn ($b) => $b->where('batch_name', $currentBatch)))
            ->avg('latest_results.cgpa');

        $highestCgpa = Student::query()
            ->joinSub($latestResults, 'latest_results', fn ($join) =>
                $join->on(DB::raw("REPLACE(student.student_id, ' ', '')"), '=', 'latest_results.student_id'))
            ->where('student.status', 'AKTIF')
            ->when($currentBatch !== 'Overall', fn ($q) =>
                $q->whereHas('batch', fn ($b) => $b->where('batch_name', $currentBatch)))
            ->max('latest_results.cgpa');

        $atRiskCount = Student::query()
            ->joinSub($latestResults, 'latest_results', fn ($join) =>
                $join->on(DB::raw("REPLACE(student.student_id, ' ', '')"), '=', 'latest_results.student_id'))
            ->where('student.status', 'AKTIF')
            ->where('latest_results.cgpa', '<', 2.50)
            ->when($currentBatch !== 'Overall', fn ($q) =>
                $q->whereHas('batch', fn ($b) => $b->where('batch_name', $currentBatch)))
            ->count();

        $cgpaTrend = Result::query()
            ->join('semester', 'result.semester_id', '=', 'semester.semester_id')
            ->join('student', 'result.student_id', '=', 'student.student_id')
            ->where('student.status', 'AKTIF')
            ->when($currentBatch !== 'Overall', fn ($q) =>
                $q->whereHas('student.batch', fn ($b) => $b->where('batch_name', $currentBatch)))
            ->groupBy('semester.semester_id', 'semester.semester_name')
            ->orderBy('semester.semester_id')
            ->select('semester.semester_name', DB::raw('ROUND(AVG(result.cgpa), 2) as average_cgpa'))
            ->get();

        $cgpaDistribution = [
            'mumtaz'       => 0,
            'jayyid_jiddan'=> 0,
            'jayyid'       => 0,
            'maqbul'       => 0,
            'rasib'        => 0,
        ];

        $latestCgpas = Student::query()
            ->leftJoinSub($latestResults, 'latest_results', fn ($join) =>
                $join->on(DB::raw("REPLACE(student.student_id, ' ', '')"), '=', 'latest_results.student_id'))
            ->where('student.status', 'AKTIF')
            ->when($currentBatch !== 'Overall', fn ($q) =>
                $q->whereHas('batch', fn ($b) => $b->where('batch_name', $currentBatch)))
            ->whereNotNull('latest_results.cgpa')
            ->pluck('latest_results.cgpa');

        foreach ($latestCgpas as $cgpa) {
            if ($cgpa >= 3.70)      $cgpaDistribution['mumtaz']++;
            elseif ($cgpa >= 3.00)  $cgpaDistribution['jayyid_jiddan']++;
            elseif ($cgpa >= 2.50)  $cgpaDistribution['jayyid']++;
            elseif ($cgpa >= 2.00)  $cgpaDistribution['maqbul']++;
            else                    $cgpaDistribution['rasib']++;
        }

        // Filter tabs mirror Batch Management's "is_active" flag (see generateDashboardData()).
        $batchDistribution = Batch::active()->orderedByName()->pluck('batch_name', 'batch_name');

        $topStudents = Student::query()
            ->joinSub($latestResults, 'latest_results', fn ($join) =>
                $join->on(DB::raw("REPLACE(student.student_id, ' ', '')"), '=', 'latest_results.student_id'))
            ->join('batch', 'student.batch_id', '=', 'batch.batch_id')
            ->where('student.status', 'AKTIF')
            ->when($currentBatch !== 'Overall', fn ($q) =>
                $q->where('batch.batch_name', $currentBatch))
            ->orderByDesc('latest_results.cgpa')
            ->limit(10)
            ->select('student.student_name', 'student.student_id', 'batch.batch_name', 'latest_results.cgpa')
            ->get();

        $atRiskStudents = Student::query()
            ->joinSub($latestResults, 'latest_results', fn ($join) =>
                $join->on(DB::raw("REPLACE(student.student_id, ' ', '')"), '=', 'latest_results.student_id'))
            ->join('batch', 'student.batch_id', '=', 'batch.batch_id')
            ->where('student.status', 'AKTIF')
            ->where('latest_results.cgpa', '<', 2.50)
            ->when($currentBatch !== 'Overall', fn ($q) =>
                $q->where('batch.batch_name', $currentBatch))
            ->orderBy('latest_results.cgpa')
            ->select('student.student_name', 'student.student_id', 'batch.batch_name', 'latest_results.cgpa')
            ->get();

        // ── Hafazan Analytics ─────────────────────────────────────────────────

        // Build per-batch checkpoint for the period end date so T13 and T14
        // are each evaluated against their own muqarrar schedule.
        $hafCheckpointByBatch = [];
        $hafAllBatchMuqs = Muqarrar::whereNotNull('week_start_date')
            ->whereNotNull('batch_id')
            ->orderBy('week_start_date')
            ->get()
            ->groupBy('batch_id');

        foreach ($hafAllBatchMuqs as $batchId => $weeks) {
            $hafMuq = null;
            foreach ($weeks as $m) {
                $ws = Carbon::parse($m->week_start_date)->startOfDay();
                if ($dateTo->between($ws, $ws->copy()->addDays(6)->endOfDay())) {
                    $hafMuq = $m;
                    break;
                }
            }
            $hafMuq = $hafMuq ?? $weeks->last();
            if (!$hafMuq) continue;

    // Always use end_page — stable reference consistent with generateDashboardData
            $hafCheckpointByBatch[$batchId] = (int)$hafMuq->end_page;
        }
        // Scalar fallback for legacy view usage
        $hafCheckpoint = !empty($hafCheckpointByBatch) ? max($hafCheckpointByBatch) : null;
        $hafMuqarrar   = null; // retained for view compatibility; per-batch logic uses $hafCheckpointByBatch

        // Operational students + best hafazan page in the selected period (batch-filter-aware)
        $hafStudentIds = Student::operational()
            ->when($currentBatch !== 'Overall', fn ($q) =>
                $q->whereHas('batch', fn ($b) => $b->where('batch_name', $currentBatch)))
            ->pluck('student_id');

        // Include batch_id so we can use the correct per-batch checkpoint
        $hafBestPages = DB::table('tasmi as t')
            ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
            ->join('student as s', 't.student_id', '=', 's.student_id')
            ->whereIn('t.student_id', $hafStudentIds)
            ->whereBetween('t.tasmi_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->select('t.student_id', 's.batch_id', DB::raw('MAX(tp.page_reached) as max_page'))
            ->groupBy('t.student_id', 's.batch_id')
            ->get()->keyBy('student_id');

        // Khatam students have already fulfilled the Muqarrar — never Behind.
        $hafKhatam = KhatamRecord::countsAsOf($hafStudentIds, $dateTo);

        $hafHealthy = 0; $hafAttention = 0; $hafCritical = 0; $hafNoRecord = 0;
        foreach ($hafStudentIds as $sid) {
            if (!$hafBestPages->has($sid)) {
                $hafNoRecord++;
            } elseif ($hafKhatam->has($sid)) {
                $hafHealthy++;
            } else {
                $row        = $hafBestPages[$sid];
                $checkpoint = $hafCheckpointByBatch[$row->batch_id] ?? null;
                $gap        = $checkpoint !== null ? ($checkpoint - (int)$row->max_page) : 0;
                if ($gap <= 0)      $hafHealthy++;
                elseif ($gap <= 5)  $hafAttention++;
                else                $hafCritical++;
            }
        }

        // Page-range distribution
        $hafBuckets = ['0–50' => 0, '51–100' => 0, '101–200' => 0, '201–300' => 0, '300+' => 0];
        foreach ($hafBestPages as $row) {
            $p = (int)$row->max_page;
            if ($p <= 50)       $hafBuckets['0–50']++;
            elseif ($p <= 100)  $hafBuckets['51–100']++;
            elseif ($p <= 200)  $hafBuckets['101–200']++;
            elseif ($p <= 300)  $hafBuckets['201–300']++;
            else                $hafBuckets['300+']++;
        }

        // Top hafazan performers in the selected period
        $topHafazan = DB::table('tasmi as t')
            ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
            ->join('student as s', 't.student_id', '=', 's.student_id')
            ->join('batch as b', 's.batch_id', '=', 'b.batch_id')
            ->where('s.status', Student::STATUS_ACTIVE)
            ->whereNotNull('s.lecturer_id')
            ->when($currentBatch !== 'Overall', fn ($q) => $q->where('b.batch_name', $currentBatch))
            ->whereBetween('t.tasmi_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->select('s.student_name', 'b.batch_name', DB::raw('MAX(tp.page_reached) as max_page'))
            ->groupBy('t.student_id', 's.student_name', 'b.batch_name')
            ->orderByDesc('max_page')
            ->limit(10)
            ->get();

        // ── Hafazan: Structured Daily / Weekly / Monthly counts ───────────────

        // Daily — selected date only
        $hafDailyPages = DB::table('tasmi as t')
            ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
            ->join('student as s', 't.student_id', '=', 's.student_id')
            ->whereIn('t.student_id', $hafStudentIds)
            ->whereDate('t.tasmi_date', $selectedDay->toDateString())
            ->select('t.student_id', 's.batch_id', DB::raw('MAX(tp.page_reached) as max_page'))
            ->groupBy('t.student_id', 's.batch_id')
            ->get()->keyBy('student_id');

        $hafKhatamOnDay = KhatamRecord::countsAsOf($hafStudentIds, $selectedDay);

        $hafDailyCounts = ['Completed' => 0, 'On Track' => 0, 'Behind' => 0, 'Not Recorded' => 0];
        foreach ($hafStudentIds as $sid) {
            if (!$hafDailyPages->has($sid)) {
                $hafDailyCounts['Not Recorded']++;
            } elseif ($hafKhatamOnDay->has($sid)) {
                $hafDailyCounts['Completed']++;
            } else {
                $row = $hafDailyPages[$sid];
                $cp  = $hafCheckpointByBatch[$row->batch_id] ?? null;
                if ($cp === null) { $hafDailyCounts['Not Recorded']++; continue; }
                $gap = $cp - (int) $row->max_page;
                if ($gap <= 0)     $hafDailyCounts['Completed']++;
                elseif ($gap <= 5) $hafDailyCounts['On Track']++;
                else               $hafDailyCounts['Behind']++;
            }
        }

        // Weekly — Mon-to-selected-day (reuses existing bucket vars, maps to cleaner labels)
        $hafWeeklyCounts = [
            'Completed'    => $hafHealthy,
            'On Track'     => $hafAttention,
            'Behind'       => $hafCritical,
            'Not Recorded' => $hafNoRecord,
        ];
        $hafWeekLabel = $weekStart->format('d M') . ' – ' . $selectedDay->format('d M Y');

        // Monthly — month-start to selected day
        $hafMonthlyPages = DB::table('tasmi as t')
            ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
            ->join('student as s', 't.student_id', '=', 's.student_id')
            ->whereIn('t.student_id', $hafStudentIds)
            ->whereBetween('t.tasmi_date', [$monthStart->toDateString(), $selectedDay->toDateString()])
            ->select('t.student_id', 's.batch_id', DB::raw('MAX(tp.page_reached) as max_page'))
            ->groupBy('t.student_id', 's.batch_id')
            ->get()->keyBy('student_id');

        $hafMonthlyCounts = ['Completed' => 0, 'On Track' => 0, 'Behind' => 0, 'Not Recorded' => 0];
        foreach ($hafStudentIds as $sid) {
            if (!$hafMonthlyPages->has($sid)) {
                $hafMonthlyCounts['Not Recorded']++;
            } elseif ($hafKhatamOnDay->has($sid)) {
                $hafMonthlyCounts['Completed']++;
            } else {
                $row = $hafMonthlyPages[$sid];
                $cp  = $hafCheckpointByBatch[$row->batch_id] ?? null;
                if ($cp === null) { $hafMonthlyCounts['Not Recorded']++; continue; }
                $gap = $cp - (int) $row->max_page;
                if ($gap <= 0)     $hafMonthlyCounts['Completed']++;
                elseif ($gap <= 5) $hafMonthlyCounts['On Track']++;
                else               $hafMonthlyCounts['Behind']++;
            }
        }
        $hafMonthLabel = $monthStart->format('F Y');

        // ── Attendance Analytics ───────────────────────────────────────────────

        $attStudentIds = Student::where('status', Student::STATUS_ACTIVE)
            ->when($currentBatch !== 'Overall', fn ($q) =>
                $q->whereHas('batch', fn ($b) => $b->where('batch_name', $currentBatch)))
            ->pluck('student_id');

        $attTotal = $attStudentIds->count();

        $attTotalRecords = Attendance::whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->whereIn('student_id', $attStudentIds)->count();
        $attPresentToday = Attendance::whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->where('status', 'present')->whereIn('student_id', $attStudentIds)->count();
        $attAbsentToday  = Attendance::whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->where('status', 'absent')->whereIn('student_id', $attStudentIds)->count();
        $attMedicalToday = Attendance::whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->where('status', 'medical')->whereIn('student_id', $attStudentIds)->count();
        $attRateToday    = $attTotalRecords > 0 ? round($attPresentToday / $attTotalRecords * 100) : 0;

        // Attendance trend for selected period
        $trendDayCount = max(0, (int)$dateFrom->diffInDays($dateTo));
        $trendDays     = collect(range(0, $trendDayCount))->map(fn ($i) => $dateFrom->copy()->addDays($i));

        $trendData = Attendance::whereIn('student_id', $attStudentIds)
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->select('date',
                DB::raw("SUM(CASE WHEN status='present' THEN 1 ELSE 0 END) as present_count"),
                DB::raw("SUM(CASE WHEN status='absent'  THEN 1 ELSE 0 END) as absent_count"),
                DB::raw("SUM(CASE WHEN status IN ('medical','absent_with_reason') THEN 1 ELSE 0 END) as medical_count"))
            ->groupBy('date')
            ->get()->keyBy(fn ($r) => Carbon::parse($r->date)->toDateString());

        $attTrendLabels   = $trendDays->map(fn ($d) => $d->format('d M'))->all();
        $attTrendPresent  = $trendDays->map(fn ($d) => (int)optional($trendData->get($d->toDateString()))->present_count)->all();
        $attTrendAbsent   = $trendDays->map(fn ($d) => (int)optional($trendData->get($d->toDateString()))->absent_count)->all();
        $attTrendMedical  = $trendDays->map(fn ($d) => (int)optional($trendData->get($d->toDateString()))->medical_count)->all();

        // Per-batch attendance for selected period
        $daysInPeriod = max(1, $dateFrom->diffInDays($dateTo) + 1);

        // Raw present-record count (used only to compute the rate %)
        $presentByBatch = Attendance::whereBetween('attendance.date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->where('attendance.status', 'present')
            ->join('student', 'attendance.student_id', '=', 'student.student_id')
            ->join('batch', 'student.batch_id', '=', 'batch.batch_id')
            ->where('student.status', Student::STATUS_ACTIVE)
            ->when($currentBatch !== 'Overall', fn ($q) => $q->where('batch.batch_name', $currentBatch))
            ->select('batch.batch_name', DB::raw('COUNT(*) as present_count'))
            ->groupBy('batch.batch_name')
            ->get()->keyBy('batch_name');

        // Distinct students who showed up at least once — drives the student-level summary
        $attendedByBatch = Attendance::whereBetween('attendance.date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->where('attendance.status', 'present')
            ->join('student', 'attendance.student_id', '=', 'student.student_id')
            ->join('batch', 'student.batch_id', '=', 'batch.batch_id')
            ->where('student.status', Student::STATUS_ACTIVE)
            ->when($currentBatch !== 'Overall', fn ($q) => $q->where('batch.batch_name', $currentBatch))
            ->select('batch.batch_name', DB::raw('COUNT(DISTINCT attendance.student_id) as attended_count'))
            ->groupBy('batch.batch_name')
            ->get()->keyBy('batch_name');

        $totalByBatch = Student::where('status', Student::STATUS_ACTIVE)
            ->join('batch', 'student.batch_id', '=', 'batch.batch_id')
            ->when($currentBatch !== 'Overall', fn ($q) => $q->where('batch.batch_name', $currentBatch))
            ->select('batch.batch_name', DB::raw('COUNT(*) as total'))
            ->groupBy('batch.batch_name')
            ->get();

        $attPerBatch = $totalByBatch->map(function ($row) use ($presentByBatch, $attendedByBatch, $daysInPeriod) {
            $present             = $presentByBatch->get($row->batch_name)?->present_count ?? 0;
            $attended            = (int)($attendedByBatch->get($row->batch_name)?->attended_count ?? 0);
            $row->attended       = $attended;
            $row->need_attention = max(0, $row->total - $attended);
            $denominator         = $row->total * $daysInPeriod;
            $row->rate           = $denominator > 0 ? round($present / $denominator * 100) : 0;
            return $row;
        });

        // ── Student Risk Analytics ─────────────────────────────────────────────

        // Warning letters: count by level + full list
        $warnLevelCounts = WarningLetter::where('warning_letter.status', 'active')
            ->join('student', 'warning_letter.student_id', '=', 'student.student_id')
            ->join('batch', 'student.batch_id', '=', 'batch.batch_id')
            ->when($currentBatch !== 'Overall', fn ($q) => $q->where('batch.batch_name', $currentBatch))
            ->select('warning_letter.warning_level', DB::raw('COUNT(DISTINCT warning_letter.student_id) as cnt'))
            ->groupBy('warning_letter.warning_level')
            ->get()->keyBy('warning_level');

        $warn1Count = (int)($warnLevelCounts->get(1)?->cnt ?? 0);
        $warn2Count = (int)($warnLevelCounts->get(2)?->cnt ?? 0);
        $warn3Count = (int)($warnLevelCounts->get(3)?->cnt ?? 0);

        $riskWarnStudents = WarningLetter::where('warning_letter.status', 'active')
            ->join('student', 'warning_letter.student_id', '=', 'student.student_id')
            ->join('batch', 'student.batch_id', '=', 'batch.batch_id')
            ->when($currentBatch !== 'Overall', fn ($q) => $q->where('batch.batch_name', $currentBatch))
            ->select(
                'warning_letter.student_id',
                'student.student_name',
                'batch.batch_name',
                DB::raw('MAX(warning_letter.warning_level) as max_level')
            )
            ->groupBy('warning_letter.student_id', 'student.student_name', 'batch.batch_name')
            ->orderByDesc('max_level')
            ->get();

        // Critical hafazan students (gap > 5 pages) — per-batch checkpoint
        $critItems = [];
        foreach ($hafStudentIds as $sid) {
            if (!$hafBestPages->has($sid)) continue;
            $row        = $hafBestPages[$sid];
            $checkpoint = $hafCheckpointByBatch[$row->batch_id] ?? null;
            if ($checkpoint === null) continue;
            $gap = $checkpoint - (int)$row->max_page;
            if ($gap > 5) $critItems[] = ['student_id' => $sid, 'gap' => $gap];
        }
        $riskCriticalHaf = collect();
        if (!empty($critItems)) {
            $critIds        = array_column($critItems, 'student_id');
            $critStudentMap = Student::whereIn('student.student_id', $critIds)
                ->join('batch', 'student.batch_id', '=', 'batch.batch_id')
                ->select('student.student_id', 'student.student_name', 'batch.batch_name')
                ->get()->keyBy('student_id');
            $riskCriticalHaf = collect($critItems)->map(function ($item) use ($critStudentMap) {
                $s = $critStudentMap->get($item['student_id']);
                return (object)[
                    'student_name' => $s?->student_name ?? 'Unknown',
                    'batch_name'   => $s?->batch_name ?? '—',
                    'gap'          => $item['gap'],
                ];
            })->sortByDesc('gap')->values();
        }

        // ── Students Behind Checkpoint (hafazan tab attention list) ────────
        // Each student is compared against their own batch's checkpoint, not a global max.
        $hafBehindStudents = collect();
        $behindItems = [];
        foreach ($hafStudentIds as $sid) {
            if (!$hafBestPages->has($sid)) continue;
            $row        = $hafBestPages[$sid];
            $checkpoint = $hafCheckpointByBatch[$row->batch_id] ?? null;
            if ($checkpoint === null) continue;
            $gap = $checkpoint - (int)$row->max_page;
            if ($gap > 0) {
                $behindItems[] = ['student_id' => $sid, 'gap' => $gap];
            }
        }
        if (!empty($behindItems)) {
            $behindIds = array_column($behindItems, 'student_id');
            $behindMap = Student::whereIn('student.student_id', $behindIds)
                ->join('batch', 'student.batch_id', '=', 'batch.batch_id')
                ->select('student.student_id', 'student.student_name', 'batch.batch_name')
                ->get()->keyBy('student_id');
            $hafBehindStudents = collect($behindItems)->map(function ($item) use ($behindMap) {
                $s = $behindMap->get($item['student_id']);
                return (object)[
                    'student_name' => $s?->student_name ?? 'Unknown',
                    'batch_name'   => $s?->batch_name ?? '—',
                    'gap'          => $item['gap'],
                ];
            })->sortByDesc('gap')->take(8)->values();
        }

        // ── Hafazan Batch Comparison — all currently active batches, period-filtered ──
        // "Active batch" = any batch that has at least one AKTIF student.
        // Date filter is applied so the comparison reflects the selected period.
        $batchDateFrom = $dateFrom->toDateString();
        $batchDateTo   = $dateTo->toDateString();
        $hafBatchComparison = DB::table('student as s')
            ->join('batch as b', 's.batch_id', '=', 'b.batch_id')
            ->leftJoin(DB::raw("(SELECT t2.student_id, MAX(tp2.page_reached) as max_page
                                  FROM tasmi t2
                                  JOIN tasmi_progress tp2 ON t2.tasmi_id = tp2.tasmi_id
                                  WHERE t2.tasmi_date BETWEEN '{$batchDateFrom}' AND '{$batchDateTo}'
                                  GROUP BY t2.student_id) as bpages"),
                's.student_id', '=', 'bpages.student_id')
            ->where('s.status', Student::STATUS_ACTIVE)
            ->select(
                'b.batch_name',
                DB::raw('ROUND(AVG(COALESCE(bpages.max_page, 0)), 0) as avg_page'),
                DB::raw('COUNT(s.student_id) as total'),
                DB::raw('SUM(CASE WHEN bpages.max_page IS NULL THEN 1 ELSE 0 END) as no_record_count')
            )
            ->groupBy('b.batch_name')
            ->orderByDesc('avg_page')
            ->get();

        $bestBatch    = $hafBatchComparison->first();
        $weakestBatch = $hafBatchComparison->count() > 1 ? $hafBatchComparison->last() : null;

        // ── Weekly Improvement: avg page_reached this week vs last week ──────
        $thisWeekAvgPage = DB::table('tasmi as t')
            ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
            ->join('student as s', 't.student_id', '=', 's.student_id')
            ->join('batch as b', 's.batch_id', '=', 'b.batch_id')
            ->where('s.status', Student::STATUS_ACTIVE)
            ->when($currentBatch !== 'Overall', fn ($q) => $q->where('b.batch_name', $currentBatch))
            ->whereNotNull('t.tasmi_date')
            ->where('t.tasmi_date', '>=', $today->copy()->subDays(6)->toDateString())
            ->avg('tp.page_reached');

        $lastWeekAvgPage = DB::table('tasmi as t')
            ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
            ->join('student as s', 't.student_id', '=', 's.student_id')
            ->join('batch as b', 's.batch_id', '=', 'b.batch_id')
            ->where('s.status', Student::STATUS_ACTIVE)
            ->when($currentBatch !== 'Overall', fn ($q) => $q->where('b.batch_name', $currentBatch))
            ->whereNotNull('t.tasmi_date')
            ->whereBetween('t.tasmi_date', [
                $today->copy()->subDays(13)->toDateString(),
                $today->copy()->subDays(7)->toDateString(),
            ])
            ->avg('tp.page_reached');

        $weekImprovement = ($thisWeekAvgPage !== null && $lastWeekAvgPage !== null)
            ? round((float)$thisWeekAvgPage - (float)$lastWeekAvgPage, 1)
            : null;

        // Frequent absentees for selected period
        $minAbsences = 1;
        $riskAbsentees = DB::table('attendance as a')
            ->join('student as s', 'a.student_id', '=', 's.student_id')
            ->join('batch as b', 's.batch_id', '=', 'b.batch_id')
            ->where('a.status', 'absent')
            ->whereBetween('a.date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->whereIn('a.student_id', $attStudentIds)
            ->select('a.student_id', 's.student_name', 'b.batch_name',
                DB::raw('COUNT(*) as absence_count'))
            ->groupBy('a.student_id', 's.student_name', 'b.batch_name')
            ->having('absence_count', '>=', $minAbsences)
            ->orderByDesc('absence_count')
            ->get();

        // ── Attendance Analytics: Daily / Weekly / Monthly sections ───────────
        // These always reflect today / this-week / this-month, independent of
        // the period filter, and respect the batch filter via $attStudentIds.

        // 1. Daily KPIs (today)
        $attDailyPresent  = Attendance::whereIn('student_id', $attStudentIds)->whereDate('date', $selectedDay)->where('status', 'present')->count();
        $attDailyAbsent   = Attendance::whereIn('student_id', $attStudentIds)->whereDate('date', $selectedDay)->where('status', 'absent')->count();
        $attDailyExcused  = Attendance::whereIn('student_id', $attStudentIds)->whereDate('date', $selectedDay)->whereIn('status', ['medical', 'absent_with_reason'])->count();
        $attDailyNoRecord = max(0, $attTotal - ($attDailyPresent + $attDailyAbsent + $attDailyExcused));

        // Subject Attendance (Madah) — system-wide daily summary, same batch
        // filter as Tasmik above. Kept separate rather than blended into the
        // Tasmik figures: Section rosters aren't the same students as a
        // Halaqah/batch roster, so combining them into one rate would be
        // misleading. Always computed (not conditionally skipped) — the view
        // shows an explicit "no records" state rather than hiding the card.
        $subjAttPresent = SubjectAttendanceRecord::whereIn('student_id', $attStudentIds)->whereDate('date', $selectedDay)->where('status', 'present')->count();
        $subjAttAbsent  = SubjectAttendanceRecord::whereIn('student_id', $attStudentIds)->whereDate('date', $selectedDay)->where('status', 'absent')->count();
        $subjAttTotal   = $subjAttPresent + $subjAttAbsent;
        $subjAttRate    = $subjAttTotal > 0 ? round($subjAttPresent / $subjAttTotal * 100) : null;

        // 2. Weekly KPIs (Mon–Sun of the current week)
        $adminWeekStart      = $weekStart->copy();
        $adminWeekEnd        = $adminWeekStart->copy()->addDays(6)->endOfDay();
        $adminWeekLabel      = $adminWeekStart->format('d M') . ' – ' . $adminWeekEnd->format('d M Y');
        $adminWeekDays       = collect(range(0, 6))->map(fn ($d) => $adminWeekStart->copy()->addDays($d));
        $rawAdminWeekAtt     = Attendance::whereIn('student_id', $attStudentIds)
            ->whereBetween('date', [$adminWeekStart->toDateString(), $adminWeekEnd->toDateString()])
            ->get()->groupBy(fn ($a) => Carbon::parse($a->date)->format('Y-m-d'));
        $attAdminWeekLabels  = $adminWeekDays->map(fn ($d) => $d->format('D, d M'))->all();
        $attAdminWeekPresent = $adminWeekDays->map(fn ($d) => $rawAdminWeekAtt->get($d->format('Y-m-d'), collect())->where('status', 'present')->count())->all();
        $attAdminWeekAbsent  = $adminWeekDays->map(fn ($d) => $rawAdminWeekAtt->get($d->format('Y-m-d'), collect())->where('status', 'absent')->count())->all();
        $attAdminWeekExcused = $adminWeekDays->map(fn ($d) => $rawAdminWeekAtt->get($d->format('Y-m-d'), collect())->whereIn('status', ['medical', 'absent_with_reason'])->count())->all();
        $attAdminWeekSessions = array_sum($attAdminWeekPresent) + array_sum($attAdminWeekAbsent) + array_sum($attAdminWeekExcused);
        $attAdminWeekRate     = $attAdminWeekSessions > 0 ? round(array_sum($attAdminWeekPresent) / $attAdminWeekSessions * 100) : null;

        // 3. Monthly KPIs (this month)
        $adminMonthStart      = $monthStart->copy();
        $adminMonthEffEnd     = $selectedDay->copy();
        $adminMonthLabel      = $adminMonthStart->format('F Y');
        $rawAdminMonthAtt     = Attendance::whereIn('student_id', $attStudentIds)
            ->whereBetween('date', [$adminMonthStart->toDateString(), $adminMonthEffEnd->toDateString()])
            ->get();
        $attAdminMonthPresent   = $rawAdminMonthAtt->where('status', 'present')->count();
        $attAdminMonthAbsent    = $rawAdminMonthAtt->where('status', 'absent')->count();
        $attAdminMonthExcused   = $rawAdminMonthAtt->whereIn('status', ['medical', 'absent_with_reason'])->count();
        $attAdminMonthDateCnt   = $rawAdminMonthAtt->groupBy(fn ($a) => Carbon::parse($a->date)->format('Y-m-d'))->keys()->count();
        $attAdminMonthSessions  = max(0, $attAdminMonthDateCnt * $attTotal);
        $attAdminMonthPresentRate = $attAdminMonthSessions > 0 ? round($attAdminMonthPresent / $attAdminMonthSessions * 100) : 0;
        $attAdminMonthAbsentRate  = $attAdminMonthSessions > 0 ? round($attAdminMonthAbsent  / $attAdminMonthSessions * 100) : 0;
        $attAdminMonthExcusedRate = $attAdminMonthSessions > 0 ? round($attAdminMonthExcused / $attAdminMonthSessions * 100) : 0;

        // Monthly weekly breakdown chart
        $attAdminMonthWeekLabels = [];
        $attAdminMonthWeekRates  = [];
        $mwAdminStart = $adminMonthStart->copy()->startOfWeek(Carbon::MONDAY);
        $mwAdminCtr   = 1;
        while ($mwAdminStart->copy()->startOfDay()->lte($adminMonthEffEnd->copy()->startOfDay())) {
            $mwAdminEnd = $mwAdminStart->copy()->addDays(6)->endOfDay();
            $clampS = $mwAdminStart->lt($adminMonthStart) ? $adminMonthStart->copy()->startOfDay() : $mwAdminStart->copy()->startOfDay();
            $clampE = $mwAdminEnd->gt($adminMonthEffEnd) ? $adminMonthEffEnd->copy()->endOfDay() : $mwAdminEnd->copy();
            $wkAtt  = $rawAdminMonthAtt->filter(function ($a) use ($clampS, $clampE) {
                $d = Carbon::parse($a->date)->startOfDay();
                return $d->gte($clampS) && $d->lte($clampE);
            });
            $wkDates = $wkAtt->groupBy(fn ($a) => Carbon::parse($a->date)->format('Y-m-d'))->keys()->count();
            if ($wkDates > 0) {
                $wkSlots = $wkDates * $attTotal;
                $attAdminMonthWeekLabels[] = 'Wk ' . $mwAdminCtr;
                $attAdminMonthWeekRates[]  = $wkSlots > 0 ? round($wkAtt->where('status', 'present')->count() / $wkSlots * 100) : 0;
            }
            $mwAdminStart->addWeek();
            $mwAdminCtr++;
        }

        // 4. Frequent Absentees for attendance section (current month, ≥3 TH)
        $attFreqRaw = DB::table('attendance as a')
            ->join('student as s', 'a.student_id', '=', 's.student_id')
            ->join('batch as b', 's.batch_id', '=', 'b.batch_id')
            ->where('s.status', Student::STATUS_ACTIVE)
            ->whereIn('a.student_id', $attStudentIds)
            ->whereBetween('a.date', [$adminMonthStart->toDateString(), $adminMonthEffEnd->toDateString()])
            ->select(
                'a.student_id', 's.student_name', 'b.batch_name',
                DB::raw('SUM(a.status = "absent") as th_count'),
                DB::raw('SUM(a.status = "present") as present_count'),
                DB::raw('COUNT(*) as total_count')
            )
            ->groupBy('a.student_id', 's.student_name', 'b.batch_name')
            ->having('th_count', '>=', 3)
            ->orderByDesc('th_count')
            ->get();

        $attFreqWlMap = $attFreqRaw->isNotEmpty()
            ? WarningLetter::where('status', 'active')
                ->whereIn('student_id', $attFreqRaw->pluck('student_id'))
                ->select('student_id', DB::raw('MAX(warning_level) as max_wl'))
                ->groupBy('student_id')
                ->pluck('max_wl', 'student_id')
            : collect();

        $attFrequentAbsentees = $attFreqRaw->map(function ($row) use ($attFreqWlMap) {
            return (object) [
                'student_name' => $row->student_name,
                'batch_name'   => $row->batch_name,
                'absences'     => (int) $row->th_count,
                'wl'           => (int) ($attFreqWlMap->get($row->student_id) ?? 0),
                'att_rate'     => (int) $row->total_count > 0 ? (int) round($row->present_count / $row->total_count * 100) : 0,
            ];
        });

        return view('admin.analytics', compact(
            'selectedDate',
            'periodLabel',
            // ── Teammate (CGPA) — untouched ──────────────────────
            'currentBatch',
            'batchDistribution',
            'averageCgpa',
            'highestCgpa',
            'atRiskCount',
            'cgpaTrend',
            'cgpaDistribution',
            'topStudents',
            'atRiskStudents',
            // ── Hafazan Analytics ─────────────────────────────────
            'hafMuqarrar',
            'hafCheckpoint',
            'hafHealthy',
            'hafAttention',
            'hafCritical',
            'hafNoRecord',
            'hafBuckets',
            'topHafazan',
            'hafBehindStudents',
            'hafBatchComparison',
            'bestBatch',
            'weakestBatch',
            'weekImprovement',
            'hafDailyCounts', 'hafWeeklyCounts', 'hafWeekLabel',
            'hafMonthlyCounts', 'hafMonthLabel',
            // ── Attendance Analytics ──────────────────────────────
            'attTotal',
            'attRateToday',
            'attPresentToday',
            'attAbsentToday',
            'attMedicalToday',
            'attTrendLabels',
            'attTrendPresent',
            'attTrendAbsent',
            'attTrendMedical',
            'attPerBatch',
            // ── Attendance: Daily / Weekly / Monthly sections ─────
            'attDailyPresent', 'attDailyAbsent', 'attDailyExcused', 'attDailyNoRecord',
            // ── Subject Attendance (Madah) — daily summary ────────
            'subjAttPresent', 'subjAttAbsent', 'subjAttRate',
            'adminWeekLabel', 'attAdminWeekLabels', 'attAdminWeekPresent', 'attAdminWeekAbsent', 'attAdminWeekExcused', 'attAdminWeekRate',
            'adminMonthLabel', 'adminMonthStart',
            'attAdminMonthPresent', 'attAdminMonthAbsent', 'attAdminMonthExcused',
            'attAdminMonthSessions', 'attAdminMonthPresentRate', 'attAdminMonthAbsentRate', 'attAdminMonthExcusedRate',
            'attAdminMonthWeekLabels', 'attAdminMonthWeekRates',
            'attFrequentAbsentees',
            // ── Student Risk ──────────────────────────────────────
            'warn1Count',
            'warn2Count',
            'warn3Count',
            'riskWarnStudents',
            'riskCriticalHaf',
            'riskAbsentees'
        ));
    }

    /** Returns [$dateFrom, $dateTo] Carbon instances for the given period key. */
    private function getDateRange(string $period, Carbon $today): array
    {
        return match ($period) {
            'this_week'  => [$today->copy()->startOfWeek(Carbon::MONDAY), $today->copy()],
            'last_week'  => [$today->copy()->subWeek()->startOfWeek(Carbon::MONDAY),
                             $today->copy()->subWeek()->endOfWeek(Carbon::SUNDAY)],
            'this_month' => [$today->copy()->startOfMonth(), $today->copy()],
            'last_month' => [$today->copy()->subMonth()->startOfMonth(),
                             $today->copy()->subMonth()->endOfMonth()],
            default      => [$today->copy(), $today->copy()], // 'today'
        };
    }

    /** Returns a human-readable label for the selected period. */
    private function buildPeriodLabel(string $period, Carbon $from, Carbon $to): string
    {
        return match ($period) {
            'this_week'  => 'This Week (' . $from->format('d M') . '–' . $to->format('d M') . ')',
            'last_week'  => 'Last Week (' . $from->format('d M') . '–' . $to->format('d M') . ')',
            'this_month' => 'This Month (' . $from->format('M Y') . ')',
            'last_month' => 'Last Month (' . $from->format('M Y') . ')',
            default      => 'Today (' . $from->format('d M Y') . ')',
        };
    }
}
