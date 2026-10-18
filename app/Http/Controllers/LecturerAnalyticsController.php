<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\Attendance;
use App\Models\KhatamRecord;
use App\Models\Lecturer;
use App\Models\Muqarrar;
use App\Models\Result;
use App\Models\Student;
use App\Models\SubjectAttendanceRecord;
use App\Services\SubjectOwnershipResolver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LecturerAnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $lecturerId  = Lecturer::where('user_id', Auth::id())->value('lecturer_id');
        $lecturer    = Lecturer::find($lecturerId);
        $today        = Carbon::today();
        $selectedDate = Carbon::parse($request->get('date', $today->toDateString()))->startOfDay();
        if ($selectedDate->isAfter($today)) {
            $selectedDate = $today->copy();
        }
        $dateFrom    = $selectedDate->copy()->startOfWeek(Carbon::MONDAY);
        $dateTo      = $selectedDate->copy();

        // Load all active students for this lecturer
        $students      = Student::with('batch')->operational()->where('lecturer_id', $lecturerId)->get();
        $studentIds    = $students->pluck('student_id');
        $totalStudents = $students->count();

        // ── 1. ATTENDANCE ANALYTICS ───────────────────────────────────────────

        // Daily Attendance
        $attDailyDate = Carbon::parse($request->input('att_date', $selectedDate->toDateString()))->startOfDay();
        if ($attDailyDate->isAfter($today)) {
            $attDailyDate = $today->copy();
        }

        $rawDailyAtt = $studentIds->isNotEmpty()
            ? Attendance::whereIn('student_id', $studentIds)
                ->whereDate('date', $attDailyDate->format('Y-m-d'))
                ->get()
            : collect();

        $attDailyPresent  = $rawDailyAtt->where('status', 'present')->count();
        $attDailyAbsent   = $rawDailyAtt->where('status', 'absent')->count();
        $attDailyExcused  = $rawDailyAtt->where('status', 'absent_with_reason')->count();
        $attDailyNoRecord = max(0, $totalStudents - ($attDailyPresent + $attDailyAbsent + $attDailyExcused));

        // Weekly Attendance
        $attWeekStart = $selectedDate->copy()->startOfWeek(Carbon::MONDAY);
        $attWeekEnd   = $attWeekStart->copy()->addDays(6)->endOfDay();
        $attWeekDays  = collect(range(0, 6))->map(fn ($d) => $attWeekStart->copy()->addDays($d));
        $attWeekLabel = $attWeekStart->format('d M') . ' – ' . $attWeekEnd->format('d M Y');

        $rawWeekAtt = $studentIds->isNotEmpty()
            ? Attendance::whereIn('student_id', $studentIds)
                ->whereBetween('date', [$attWeekStart->toDateString(), $attWeekEnd->toDateString()])
                ->get()
                ->groupBy(fn ($a) => Carbon::parse($a->date)->format('Y-m-d'))
            : collect();

        $attWeekLabels  = $attWeekDays->map(fn ($d) => $d->format('D, d M'))->all();
        $attWeekPresent = $attWeekDays->map(fn ($d) =>
            $rawWeekAtt->get($d->format('Y-m-d'), collect())->where('status', 'present')->count()
        )->all();
        $attWeekAbsent  = $attWeekDays->map(fn ($d) =>
            $rawWeekAtt->get($d->format('Y-m-d'), collect())->where('status', 'absent')->count()
        )->all();
        $attWeekExcused = $attWeekDays->map(fn ($d) =>
            $rawWeekAtt->get($d->format('Y-m-d'), collect())->where('status', 'absent_with_reason')->count()
        )->all();

        // Monthly Attendance
        $attMonthStr    = $selectedDate->format('Y-m');
        $attMonthStart  = Carbon::parse($attMonthStr . '-01')->startOfMonth();
        $attMonthEnd    = $attMonthStart->copy()->endOfMonth();
        $attMonthEffEnd = $today->isAfter($attMonthEnd) ? $attMonthEnd->copy() : $today->copy();

        $rawMonthAtt = $studentIds->isNotEmpty()
            ? Attendance::whereIn('student_id', $studentIds)
                ->whereBetween('date', [$attMonthStart->toDateString(), $attMonthEffEnd->toDateString()])
                ->get()
            : collect();

        $attMonthPresent   = $rawMonthAtt->where('status', 'present')->count();
        $attMonthAbsent    = $rawMonthAtt->where('status', 'absent')->count();
        $attMonthExcused   = $rawMonthAtt->where('status', 'absent_with_reason')->count();
        $attMonthDateCount = $rawMonthAtt->groupBy(fn ($a) => Carbon::parse($a->date)->format('Y-m-d'))->keys()->count();
        $attMonthNoRecord  = max(0, ($attMonthDateCount * $totalStudents) - ($attMonthPresent + $attMonthAbsent + $attMonthExcused));


        // Monthly KPI rates
        $attMonthSessionsTotal = max(0, $attMonthDateCount * $totalStudents);
        $attMonthPresentRate   = $attMonthSessionsTotal > 0 ? round($attMonthPresent / $attMonthSessionsTotal * 100) : 0;
        $attMonthAbsentRate    = $attMonthSessionsTotal > 0 ? round($attMonthAbsent  / $attMonthSessionsTotal * 100) : 0;
        $attMonthExcusedRate   = $attMonthSessionsTotal > 0 ? round($attMonthExcused / $attMonthSessionsTotal * 100) : 0;

        // Monthly weekly attendance breakdown (weeks within the selected month)
        $attMonthWeekLabels = [];
        $attMonthWeekRates  = [];
        $mwStart    = $attMonthStart->copy()->startOfWeek(Carbon::MONDAY);
        $mwCounter  = 1;
        while ($mwStart->copy()->startOfDay()->lte($attMonthEffEnd->copy()->startOfDay())) {
            $mwEnd   = $mwStart->copy()->addDays(6)->endOfDay();
            $clampS  = $mwStart->lt($attMonthStart) ? $attMonthStart->copy()->startOfDay() : $mwStart->copy()->startOfDay();
            $clampE  = $mwEnd->gt($attMonthEffEnd)  ? $attMonthEffEnd->copy()->endOfDay()  : $mwEnd->copy();
            $wkAtt   = $rawMonthAtt->filter(function ($a) use ($clampS, $clampE) {
                $d = Carbon::parse($a->date)->startOfDay();
                return $d->gte($clampS) && $d->lte($clampE);
            });
            $wkDates = $wkAtt->groupBy(fn ($a) => Carbon::parse($a->date)->format('Y-m-d'))->keys()->count();
            if ($wkDates > 0) {
                $wkPresent              = $wkAtt->where('status', 'present')->count();
                $wkSlots                = $wkDates * $totalStudents;
                $attMonthWeekLabels[]   = 'Wk ' . $mwCounter;
                $attMonthWeekRates[]    = $wkSlots > 0 ? round($wkPresent / $wkSlots * 100) : 0;
            }
            $mwStart->addWeek();
            $mwCounter++;
        }

        // Subject Attendance (Madah) — compact daily summary, separate from the
        // Tasmik numbers above since the roster isn't the same set of students
        // (Section enrollment, not this lecturer's Halaqah). Only shown at all
        // when the lecturer actually teaches at least one Section this period —
        // Hafazan subjects have no Subject Attendance sessions; see Tasmik above.
        $currentPeriod = AcademicPeriod::current();
        $hasMadahSections = $currentPeriod
            && SubjectOwnershipResolver::madahSectionsOwnedBy($lecturer, $currentPeriod)->isNotEmpty();

        $subjAttPresent = 0;
        $subjAttAbsent = 0;
        $subjAttRate = null;

        if ($hasMadahSections) {
            $subjAttRecords = SubjectAttendanceRecord::where('lecturer_id', $lecturerId)
                ->whereDate('date', $attDailyDate->format('Y-m-d'))
                ->get();

            $subjAttPresent = $subjAttRecords->where('status', 'present')->count();
            $subjAttAbsent = $subjAttRecords->where('status', 'absent')->count();
            $subjAttTotal = $subjAttPresent + $subjAttAbsent;
            $subjAttRate = $subjAttTotal > 0 ? round($subjAttPresent / $subjAttTotal * 100) : null;
        }

        // Warning-level students — follows selected month
        $wlMonthStart = $attMonthStart->copy();

        $wlAttMap = collect();
        if ($studentIds->isNotEmpty()) {
            $wlAttMap = DB::table('attendance')
                ->whereIn('student_id', $studentIds)
                ->whereBetween('date', [$wlMonthStart->toDateString(), $attMonthEffEnd->toDateString()])
                ->select(
                    'student_id',
                    DB::raw('SUM(status = "absent") as th_count'),
                    DB::raw('SUM(status = "present") as present_count'),
                    DB::raw('COUNT(*) as total_count')
                )
                ->groupBy('student_id')
                ->get()
                ->keyBy('student_id');
        }

        // WL thresholds mirror WarningLetterService::levelFor() exactly.
        $wlLevelFor = fn (int $count): int => match (true) {
            $count >= 9 => 3,
            $count >= 6 => 2,
            $count >= 3 => 1,
            default     => 0,
        };

        $frequentAbsentees = $students
            ->filter(fn ($s) => (int) ($wlAttMap->get($s->student_id)?->th_count ?? 0) >= 3)
            ->sortByDesc(fn ($s) => (int) ($wlAttMap->get($s->student_id)?->th_count ?? 0))
            ->map(function ($s) use ($wlAttMap, $wlLevelFor) {
                $row          = $wlAttMap->get($s->student_id);
                $thCount      = (int) ($row?->th_count ?? 0);
                $presentCount = (int) ($row?->present_count ?? 0);
                $totalCount   = (int) ($row?->total_count ?? 0);

                // Use the student's actual system-generated warning level.
                // If not yet set (edge case), derive it from monthly TH using
                // the same thresholds as WarningLetterService.
                $warningLevel = (int) ($s->warning_level ?? 0);
                if ($warningLevel === 0) {
                    $warningLevel = $wlLevelFor($thCount);
                }

                return (object) [
                    'name'     => $s->student_name,
                    'id'       => $s->student_id,
                    'batch'    => $s->batch?->batch_name ?? '-',
                    'absences' => $thCount,
                    'wl'       => $warningLevel,
                    'att_rate' => $totalCount > 0 ? (int) round($presentCount / $totalCount * 100) : 0,
                ];
            })
            ->values();

        // ── 2. HAFAZAN ANALYTICS ──────────────────────────────────────────────

        // Build per-batch checkpoint map so students in different batches are
        // evaluated against their own batch's muqarrar, not a random other batch's.
        $batchIds = $students->pluck('batch_id')->unique()->filter()->values()->all();

        $batchCheckpointMap = $this->weeklyCheckpointMap($dateTo, $batchIds);

        // Pick the primary batch (most students) for header context display.
        $primaryBatchId = $students->groupBy('batch_id')->map->count()->sortDesc()->keys()->first();
        $primaryEntry   = $batchCheckpointMap[$primaryBatchId]
            ?? (count($batchCheckpointMap) ? array_values($batchCheckpointMap)[0] : null);
        $muqarrar       = $primaryEntry['muqarrar'] ?? null;

        $wkLabel       = $muqarrar ? ' — Week ' . $muqarrar->week : '';
        $headerContext = $selectedDate->format('d F Y') . $wkLabel;

        // Best page per student in the selected period
        $bestPageMap = collect();
        if ($studentIds->isNotEmpty()) {
            $bestPageMap = DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereBetween('t.tasmi_date', [$dateFrom->toDateString(), $dateTo->toDateString()])
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as best'))
                ->groupBy('t.student_id')
                ->get()
                ->keyBy('student_id');
        }

        // Khatam students have already fulfilled the Muqarrar — never Behind.
        $khatamCounts = KhatamRecord::countsAsOf($studentIds, $dateTo);

        // Classify each student using their own batch's proportional checkpoint.
        $hafDist = ['Ahead' => 0, 'On Track' => 0, 'Behind' => 0, 'Not Recorded' => 0];

        $studentRows = $students->map(function ($s) use ($bestPageMap, $batchCheckpointMap, $khatamCounts, &$hafDist) {
            $entry      = $batchCheckpointMap[$s->batch_id] ?? null;
            $batchChkpt = $entry ? (int) $entry['checkpoint'] : 0;
            $page       = (int) ($bestPageMap->get($s->student_id)?->best ?? 0);

            if ($page > 0 && $khatamCounts->has($s->student_id)) {
                $status = 'Ahead';
            } elseif ($batchChkpt > 0 && $page > 0) {
                $status = $page > $batchChkpt ? 'Ahead'
                    : ($page === $batchChkpt ? 'On Track' : 'Behind');
            } else {
                $status = 'Not Recorded';
            }
            $hafDist[$status]++;

            return (object) [
                'name'   => $s->student_name,
                'id'     => $s->student_id,
                'batch'  => $s->batch?->batch_name ?? '-',
                'page'   => $page,
                'status' => $status,
                'gap'    => ($batchChkpt > 0 && $status !== 'Ahead') ? max(0, $batchChkpt - $page) : 0,
                'target' => $batchChkpt,   // proportional checkpoint, same as classification
                'wl'     => (int) ($s->warning_level ?? 0),
            ];
        })->sortByDesc('page')->values();

        $onTrackCount    = $hafDist['Ahead'] + $hafDist['On Track'];
        $onTrackPct      = $totalStudents > 0 ? round($onTrackCount / $totalStudents * 100) : 0;
        // Show the proportional checkpoint as the displayed target (not the week's raw end_page).
        $muqarrarEndPage = $primaryEntry ? (int) $primaryEntry['checkpoint'] : 0;

        // Top performers: students with a recorded page this week, highest page first
        $topPerformers = $studentRows->where('page', '>', 0)->take(5)->values();

        // Students needing attention: Behind (sorted lowest page first) then Not Recorded
        $needsAttention = $studentRows->filter(fn ($s) => $s->status === 'Behind')->sortBy('page')
            ->merge($studentRows->filter(fn ($s) => $s->status === 'Not Recorded'))
            ->take(8)->values();

        // ── NEW: HAFAZAN 3-SECTION ANALYTICS ─────────────────────────────────────────

        $hafAllMuqarrars = Muqarrar::whereNotNull('week_start_date')
            ->where(fn ($q) => $q->whereIn('batch_id', $batchIds)->orWhereNull('batch_id'))
            ->orderBy('week_start_date')
            ->get();
        $getMuqOn = function (Carbon $d) use ($hafAllMuqarrars): ?Muqarrar {
            foreach ($hafAllMuqarrars->sortBy('week_start_date') as $m) {
                $ws = Carbon::parse($m->week_start_date)->startOfDay();
                if ($d->between($ws, $ws->copy()->addDays(6)->endOfDay())) return $m;
            }
            return $hafAllMuqarrars->sortBy('week_start_date')->last();
        };

        $hafChkptFn = function (?Muqarrar $m, Carbon $d): int {
            if (! $m) return 0;
            $ws = Carbon::parse($m->week_start_date)->startOfDay();
            $we = $ws->copy()->addDays(6)->endOfDay();
            if ($d->isAfter($we)) return (int) $m->end_page;
            $diff = (int) $ws->diffInDays($d->copy()->startOfDay(), false);
            $day  = max(1, min(5, $diff + 1));
            $w    = (int) $m->end_page - (int) $m->start_page;
            return (int) min((int) $m->end_page, (int) round((int) $m->start_page + ($w / 5) * $day));
        };

        $classifyFn = function ($pageMap, int $endPage, int $chk, $khatam) use ($studentIds): array {
            $c = ['Completed' => 0, 'On Track' => 0, 'Behind' => 0, 'Not Recorded' => 0];
            foreach ($studentIds as $sid) {
                $p = (int) ($pageMap->get($sid)?->best ?? 0);
                if ($p <= 0)                           $c['Not Recorded']++;
                elseif ($khatam->has($sid))            $c['Completed']++;
                elseif ($endPage > 0 && $p >= $endPage) $c['Completed']++;
                elseif ($chk > 0 && $p >= $chk)        $c['On Track']++;
                else                                   $c['Behind']++;
            }
            return $c;
        };

        // ── Daily ─────────────────────────────────────────────────────────────────
        $hafDailyDate = $selectedDate->copy()->startOfDay();
        $hafDailyMuq  = $getMuqOn($hafDailyDate);
        $hafDailyEnd  = (int) ($hafDailyMuq?->end_page ?? 0);
        $hafDailyChk  = $hafChkptFn($hafDailyMuq, $hafDailyDate);

        $hafDailyPageMap = $studentIds->isNotEmpty()
            ? DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '<=', $hafDailyDate->format('Y-m-d'))
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as best'))
                ->groupBy('t.student_id')->get()->keyBy('student_id')
            : collect();

        $hafDailyCounts = $classifyFn($hafDailyPageMap, $hafDailyEnd, $hafDailyChk, KhatamRecord::countsAsOf($studentIds, $hafDailyDate));

        // ── Weekly ────────────────────────────────────────────────────────────────
        $semId    = Muqarrar::orderBy('semester_id', 'desc')->value('semester_id');
        $semWks   = $semId ? Muqarrar::where('semester_id', $semId)->orderBy('week_start_date')->get() : collect();
        $hafWeeklyMuq = null;
        foreach ($semWks as $m) {
            if (! $m->week_start_date) continue;
            $ws = Carbon::parse($m->week_start_date)->startOfDay();
            if ($selectedDate->between($ws, $ws->copy()->addDays(6)->endOfDay())) {
                $hafWeeklyMuq = $m; break;
            }
        }
        $hafWeeklyMuq = $hafWeeklyMuq ?? $semWks->last();

        $hafWeeklyWS    = $hafWeeklyMuq?->week_start_date ? Carbon::parse($hafWeeklyMuq->week_start_date)->startOfDay() : null;
        $hafWeeklyWE    = $hafWeeklyWS?->copy()->addDays(6)->endOfDay();
        $hafWeeklyEnd   = (int) ($hafWeeklyMuq?->end_page ?? 0);
        $hafWeeklyRef   = ($hafWeeklyWE && $selectedDate->isAfter($hafWeeklyWE)) ? $hafWeeklyWE : $selectedDate;
        $hafWeeklyChk   = $hafChkptFn($hafWeeklyMuq, $hafWeeklyRef);
        $hafWeeklyLabel = $hafWeeklyMuq
            ? 'Week ' . $hafWeeklyMuq->week
              . ($hafWeeklyWS ? ' (' . $hafWeeklyWS->format('d M') . ' – ' . $hafWeeklyWE->format('d M Y') . ')' : '')
            : 'No data';

        $hafWeeklyPageMap = ($studentIds->isNotEmpty() && $hafWeeklyWS && $hafWeeklyWE)
            ? DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '>=', $hafWeeklyWS->format('Y-m-d'))
                ->whereDate('t.tasmi_date', '<=', $hafWeeklyWE->format('Y-m-d'))
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as best'))
                ->groupBy('t.student_id')->get()->keyBy('student_id')
            : collect();

        // Weekly counts — per-batch: each student measured against their own muqarrar
        $hafWeeklyCounts = ['Completed' => 0, 'On Track' => 0, 'Behind' => 0, 'Not Recorded' => 0];
        foreach ($studentIds as $sid) {
            $student  = $students->firstWhere('student_id', $sid);
            $entry    = $batchCheckpointMap[$student?->batch_id] ?? null;
            $bEndPage = $entry ? (int) ($entry['muqarrar']->end_page ?? 0) : $hafWeeklyEnd;
            $bChkpt   = $entry ? (int) $entry['checkpoint']              : $hafWeeklyChk;
            $p        = (int) ($hafWeeklyPageMap->get($sid)?->best ?? 0);
            if ($p <= 0)                               $hafWeeklyCounts['Not Recorded']++;
            elseif ($khatamCounts->has($sid))          $hafWeeklyCounts['Completed']++;
            elseif ($bEndPage > 0 && $p >= $bEndPage)  $hafWeeklyCounts['Completed']++;
            elseif ($bChkpt  > 0  && $p >= $bChkpt)    $hafWeeklyCounts['On Track']++;
            else                                        $hafWeeklyCounts['Behind']++;
        }

        // ── Monthly ───────────────────────────────────────────────────────────────
        $hafMonthlyStr   = $selectedDate->format('Y-m');
        $hafMonthlyStart = Carbon::parse($hafMonthlyStr . '-01')->startOfMonth();
        $hafMonthlyEndDt = $hafMonthlyStart->copy()->endOfMonth();
        $hafEffDate      = $today->isAfter($hafMonthlyEndDt) ? $hafMonthlyEndDt->copy() : $today->copy();

        $latestInMonth = $studentIds->isNotEmpty()
            ? DB::table('tasmi')
                ->whereIn('student_id', $studentIds)
                ->whereBetween('tasmi_date', [$hafMonthlyStart->toDateString(), $hafEffDate->toDateString()])
                ->max('tasmi_date')
            : null;
        $hafMonthChkRef = $latestInMonth ? Carbon::parse($latestInMonth)->startOfDay() : $hafEffDate;

        $hafMonthlyMuq = $getMuqOn($hafMonthChkRef);
        if (! $hafMonthlyMuq) {
            $hafMonthlyMuq = $hafAllMuqarrars
                ->filter(fn ($m) => Carbon::parse($m->week_start_date)->startOfDay()->lte($hafMonthChkRef))
                ->sortByDesc('week_start_date')->first();
        }
        $hafMonthlyMuqEnd = (int) ($hafMonthlyMuq?->end_page ?? 0);
        $hafMonthChk      = $hafChkptFn($hafMonthlyMuq, $hafMonthChkRef);

        $hafMonthlyPageMap = $studentIds->isNotEmpty()
            ? DB::table('tasmi as t')
                ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                ->whereIn('t.student_id', $studentIds)
                ->whereDate('t.tasmi_date', '>=', $hafMonthlyStart->toDateString())
                ->whereDate('t.tasmi_date', '<=', $hafEffDate->toDateString())
                ->select('t.student_id', DB::raw('MAX(tp.page_reached) as best'))
                ->groupBy('t.student_id')->get()->keyBy('student_id')
            : collect();

        $hafMonthlyCounts = $classifyFn($hafMonthlyPageMap, $hafMonthlyMuqEnd, $hafMonthChk, KhatamRecord::countsAsOf($studentIds, $hafEffDate));

        // Top performers — batch-aware: each student compared to their own muqarrar
        $topPerformers = $students->map(function ($s) use ($hafWeeklyPageMap, $batchCheckpointMap, $hafWeeklyEnd, $hafWeeklyChk, $khatamCounts) {
            $entry    = $batchCheckpointMap[$s->batch_id] ?? null;
            $bEndPage = $entry ? (int) ($entry['muqarrar']->end_page ?? 0) : $hafWeeklyEnd;
            $bChkpt   = $entry ? (int) $entry['checkpoint']              : $hafWeeklyChk;
            $page     = (int) ($hafWeeklyPageMap->get($s->student_id)?->best ?? 0);
            $isKhatam = $khatamCounts->has($s->student_id);
            $status   = $page <= 0 ? 'Not Recorded'
                : ($isKhatam || ($bEndPage > 0 && $page >= $bEndPage) ? 'Completed'
                : ($bChkpt  > 0 && $page >= $bChkpt   ? 'On Track' : 'Behind'));
            return (object) [
                'name'   => $s->student_name,
                'batch'  => $s->batch?->batch_name ?? '—',
                'page'   => $page,
                'status' => $status,
                'gap'    => ($page > 0 && ! $isKhatam) ? max(0, $bChkpt - $page) : 0,
                'target' => $bChkpt,
            ];
        })->filter(fn ($s) => $s->page > 0)->sortByDesc('page')->take(5)->values();

        // Students needing attention — batch-aware; gap = 0 when no record (page = 0)
        $needsAttention = $students->map(function ($s) use ($hafWeeklyPageMap, $batchCheckpointMap, $hafWeeklyChk, $khatamCounts) {
            $entry  = $batchCheckpointMap[$s->batch_id] ?? null;
            $bChkpt = $entry ? (int) $entry['checkpoint'] : $hafWeeklyChk;
            $page   = (int) ($hafWeeklyPageMap->get($s->student_id)?->best ?? 0);
            if ($page <= 0)                            $status = 'Not Recorded';
            elseif ($khatamCounts->has($s->student_id)) return null;
            elseif ($bChkpt > 0 && $page < $bChkpt)   $status = 'Behind';
            else                                       return null;
            return (object) [
                'name'   => $s->student_name,
                'batch'  => $s->batch?->batch_name ?? '—',
                'page'   => $page,
                'status' => $status,
                'gap'    => $page > 0 ? max(0, $bChkpt - $page) : 0,
            ];
        })->filter()->sortBy('page')->take(8)->values();

        $muqarrarEndPage = $hafWeeklyChk;

        // ── 3. Academic / CGPA ───────────────────────────────────────────────
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

        $cgpaTrend = Result::query()
            ->join('semester', 'result.semester_id', '=', 'semester.semester_id')
            ->join('student', 'result.student_id', '=', 'student.student_id')
            ->where('student.status', 'AKTIF')
            ->where('student.lecturer_id', $lecturerId)
            ->groupBy('semester.semester_id', 'semester.semester_name')
            ->orderBy('semester.semester_id')
            ->select('semester.semester_name', DB::raw('ROUND(AVG(result.cgpa), 2) as average_cgpa'))
            ->get();

        $cgpaDistribution = ['mumtaz' => 0, 'jayyid_jiddan' => 0, 'jayyid' => 0, 'maqbul' => 0, 'rasib' => 0];
        foreach ($cgpaBase()->pluck('latest_results.cgpa') as $cgpa) {
            if ($cgpa >= 3.70)     $cgpaDistribution['mumtaz']++;
            elseif ($cgpa >= 3.00) $cgpaDistribution['jayyid_jiddan']++;
            elseif ($cgpa >= 2.50) $cgpaDistribution['jayyid']++;
            elseif ($cgpa >= 2.00) $cgpaDistribution['maqbul']++;
            else                   $cgpaDistribution['rasib']++;
        }

        $topStudents = Student::query()
            ->joinSub($latestResults, 'latest_results', fn ($j) =>
                $j->on('student.student_id', '=', 'latest_results.student_id'))
            ->join('batch', 'student.batch_id', '=', 'batch.batch_id')
            ->where('student.status', 'AKTIF')
            ->where('student.lecturer_id', $lecturerId)
            ->orderByDesc('latest_results.cgpa')
            ->limit(10)
            ->select('student.student_name', 'student.student_id', 'batch.batch_name', 'latest_results.cgpa')
            ->get();

        $atRiskStudents = Student::query()
            ->joinSub($latestResults, 'latest_results', fn ($j) =>
                $j->on('student.student_id', '=', 'latest_results.student_id'))
            ->join('batch', 'student.batch_id', '=', 'batch.batch_id')
            ->where('student.status', 'AKTIF')
            ->where('student.lecturer_id', $lecturerId)
            ->where('latest_results.cgpa', '<', 2.50)
            ->orderBy('latest_results.cgpa')
            ->select('student.student_name', 'student.student_id', 'batch.batch_name', 'latest_results.cgpa')
            ->get();

        return view('lecturer.analytics', compact(
            'selectedDate',
            'lecturer',
            'today',
            'totalStudents',
            'headerContext',
            // Attendance
            'attDailyDate',
            'attDailyPresent',
            'attDailyAbsent',
            'attDailyExcused',
            'attDailyNoRecord',
            'attWeekLabels',
            'attWeekPresent',
            'attWeekAbsent',
            'attWeekExcused',
            'attWeekLabel',
            'attWeekStart',
            'attMonthStr',
            'attMonthStart',
            'attMonthPresent',
            'attMonthAbsent',
            'attMonthExcused',
            'attMonthNoRecord',
            'attMonthSessionsTotal',
            'attMonthPresentRate',
            'attMonthAbsentRate',
            'attMonthExcusedRate',
            'attMonthWeekLabels',
            'attMonthWeekRates',
            'wlMonthStart',
            'frequentAbsentees',
            // Subject Attendance (Madah) — compact daily summary
            'hasMadahSections',
            'subjAttPresent',
            'subjAttAbsent',
            'subjAttRate',
            // Hafazan — shared
            'hafDist',
            'onTrackCount',
            'onTrackPct',
            'muqarrarEndPage',
            'topPerformers',
            'needsAttention',
            // Hafazan — daily section
            'hafDailyDate',
            'hafDailyCounts',
            'hafDailyMuq',
            'hafDailyChk',
            // Hafazan — weekly section
            'hafWeeklyMuq',
            'hafWeeklyLabel',
            'hafWeeklyChk',
            'hafWeeklyCounts',
            // Hafazan — monthly section
            'hafMonthlyStr',
            'hafMonthlyStart',
            'hafMonthlyCounts',
            // Academic / CGPA
            'averageCgpa',
            'highestCgpa',
            'atRiskCount',
            'cgpaTrend',
            'cgpaDistribution',
            'topStudents',
            'atRiskStudents'
        ));
    }

    /**
     * Weekly / daily checkpoint per batch: proportional intra-week target for
     * the given reference date.  Returns batch_id => ['muqarrar', 'checkpoint'].
     */
    private function weeklyCheckpointMap(Carbon $referenceDate, array $batchIds): array
    {
        $query = Muqarrar::whereNotNull('week_start_date')
            ->where('week_start_date', '<=', $referenceDate->toDateString());

        if (! empty($batchIds)) {
            $query->whereIn('batch_id', $batchIds);
        }

        $allRows = $query->orderBy('week_start_date', 'desc')->get();

        $map = [];
        foreach ($allRows->groupBy('batch_id') as $batchId => $rows) {
            // Prefer the week that contains the reference date, else use the most recent.
            $muqarrar = $rows->first(function ($m) use ($referenceDate) {
                $ws = Carbon::parse($m->week_start_date)->startOfDay();
                return $referenceDate->between($ws, $ws->copy()->addDays(6)->endOfDay());
            }) ?? $rows->first();

            if (! $muqarrar) {
                continue;
            }

            $ws          = Carbon::parse($muqarrar->week_start_date)->startOfDay();
            $weeklyPages = (int) $muqarrar->end_page - (int) $muqarrar->start_page;
            $diffDays    = (int) $ws->diffInDays($referenceDate->copy()->startOfDay(), false);
            $dayOfWeek   = max(1, min(5, $diffDays + 1));
            $checkpoint  = (int) min(
                (int) $muqarrar->end_page,
                round((int) $muqarrar->start_page + ($weeklyPages / 5) * $dayOfWeek)
            );

            $map[$batchId] = ['muqarrar' => $muqarrar, 'checkpoint' => $checkpoint];
        }

        return $map;
    }

}
