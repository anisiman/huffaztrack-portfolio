<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\Batch;
use App\Models\KhatamRecord;
use App\Models\Lecturer;
use App\Models\Muqarrar;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Tasmi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminHafazanController extends Controller
{
    public function index(Request $request)
    {
        $tab            = $request->query('tab', 'progress');
        $search         = trim($request->query('search', ''));
        $filterLecturer = $request->query('lecturer_id', '');
        $filterStatus   = $request->query('status', '');
        $filterAcademicPeriod = $request->query('academic_period_id', '');
        $filterWeek     = $request->query('week', '');
        $filterBatch    = $request->query('batch_id', '');
        $filterHalaqah  = trim($request->query('halaqah', ''));
        $selectedDate   = $request->query('date', Carbon::today()->format('Y-m-d'));

        $lecturers      = Lecturer::orderBy('lecturer_name')->get();
        // Legacy student-level (1-7) list — only used to label old Muqarrar rows that
        // predate academic_period_id. The Add/Edit forms no longer let admins pick this.
        $semesters      = Semester::orderBy('semester_id')->get();
        $academicPeriods      = AcademicPeriod::orderByDesc('start_date')->get();
        $activeAcademicPeriod = AcademicPeriod::current();
        // Mirrors Batch Management's "is_active" flag, so every batch dropdown/filter
        // here always matches what Batch Management shows as active.
        $batches        = Batch::active()->orderedByName()->get();
        $muqarrarWeeks  = Muqarrar::select('week')->distinct()->orderBy('week')->pluck('week');
        $halaqahOptions = collect();

        // ── Student Progress Tab ───────────────────────────
        $halaqahGroups     = collect();
        $flatStudents      = collect();
        $totalStudentCount = 0;
        $aheadCount        = 0;
        $onTrackCount      = 0;
        $behindCount       = 0;
        $notRecordedCount  = 0;
        $todayCheckpoint   = 0;
        $currentMuqarrar   = null;
        $batchMuqarrars    = collect();

        if ($tab === 'progress') {
            $halaqahOptions = Batch::active()->orderedByName()->pluck('batch_name');

            $selDateObj = Carbon::parse($selectedDate)->startOfDay();

            // Load students first — we need their batch IDs to load the right muqarrars
            $allStudents = Student::with(['batch', 'lecturer'])
                ->operational()
                ->when($filterLecturer, fn($q) => $q->where('lecturer_id', $filterLecturer))
                ->when($filterHalaqah,  fn($q) => $q->whereHas('batch', fn($b) => $b->where('batch_name', $filterHalaqah)))
                ->when($search !== '',  fn($q) => $q->where(function ($q) use ($search) {
                    $q->where('student_name', 'like', "%{$search}%")
                      ->orWhere('student_id', 'like', "%{$search}%")
                      ->orWhereHas('lecturer', fn($lq) => $lq->where('lecturer_name', 'like', "%{$search}%"))
                      ->orWhereHas('batch',   fn($bq) => $bq->where('batch_name',    'like', "%{$search}%"));
                }))
                ->orderBy('student_name')
                ->get();

            $studentBatchIds = $allStudents->pluck('batch_id')->filter()->unique()->values();

            // Load muqarrars: batch-specific records for these batches + semester-wide (null batch_id) as fallback
            $allMuqarrars = Muqarrar::whereNotNull('week_start_date')
                ->where(fn ($q) => $q->whereIn('batch_id', $studentBatchIds)->orWhereNull('batch_id'))
                ->orderBy('week_start_date')
                ->get();

            // ── Same helpers as LecturerHifzController ──────────────────────────
            [$getMuqarrarForBatch, $getCheckpoint] = $this->muqarrarLookupHelpers($allMuqarrars, $selDateObj);

            // Build per-batch muqarrar display data for the info bar
            $muqarrarBatchMap = [];
            foreach ($studentBatchIds as $batchId) {
                $muq = $getMuqarrarForBatch($batchId);
                if (! $muq) continue;
                $batchName = $batches->firstWhere('batch_id', $batchId)?->batch_name ?? "Batch {$batchId}";
                $mid = $muq->muqarrar_id;
                if (! isset($muqarrarBatchMap[$mid])) {
                    $muqarrarBatchMap[$mid] = [
                        'muqarrar'    => $muq,
                        'batch_names' => [],
                        'checkpoint'  => $getCheckpoint($muq),
                    ];
                }
                $muqarrarBatchMap[$mid]['batch_names'][] = $batchName;
            }
            foreach ($muqarrarBatchMap as &$entry) {
                sort($entry['batch_names']);
            }
            unset($entry);
            $batchMuqarrars = collect(array_values($muqarrarBatchMap))
                ->sortBy(fn($e) => $e['batch_names'][0] ?? '')
                ->values();

            $currentMuqarrar = $batchMuqarrars->first()['muqarrar'] ?? null;
            $todayCheckpoint = $batchMuqarrars->first()['checkpoint'] ?? 0;

            $studentIds = $allStudents->pluck('student_id');

            // Tasmi records submitted on the selected date
            $dateRecords = Tasmi::with(['progress'])
                ->whereIn('student_id', $studentIds)
                ->whereDate('tasmi_date', $selectedDate)
                ->get()
                ->keyBy('student_id');

            // Classify each student — same status logic as LecturerHifzController
            $khatamCounts = KhatamRecord::countsAsOf($studentIds, $selectedDate);
            $classified   = $allStudents->map(fn ($student) => $this->classifyStudent($student, $dateRecords, $getMuqarrarForBatch, $getCheckpoint, $khatamCounts));

            // KPI counts (reflect current filter set)
            $totalStudentCount = $classified->count();
            $aheadCount        = $classified->where('status', 'Ahead')->count();
            $onTrackCount      = $classified->where('status', 'On-track')->count();
            $behindCount       = $classified->where('status', 'Behind')->count();
            $notRecordedCount  = $classified->where('status', 'Not Recorded')->count();

            // Group by (lecturer, batch) for the default overview table
            $halaqahGroups = $classified
                ->groupBy(fn($s) => ($s['lecturer_id'] ?: '_') . '|' . ($s['batch_id'] ?: '_'))
                ->map(function ($students, $key) {
                    $first   = $students->first();
                    $total   = $students->count();
                    $ahead   = $students->where('status', 'Ahead')->count();
                    $onTrack = $students->where('status', 'On-track')->count();
                    $behind  = $students->where('status', 'Behind')->count();
                    $notRec  = $students->where('status', 'Not Recorded')->count();
                    $pct     = $total > 0 ? round(($ahead + $onTrack) / $total * 100) : 0;

                    return [
                        'key'           => $key,
                        'lecturer_id'   => $first['lecturer_id'],
                        'lecturer_name' => $first['lecturer_name'],
                        'batch_id'      => $first['batch_id'],
                        'batch_name'    => $first['batch_name'],
                        'total'         => $total,
                        'ahead'         => $ahead,
                        'on_track'      => $onTrack,
                        'behind'        => $behind,
                        'not_recorded'  => $notRec,
                        'progress_pct'  => $pct,
                    ];
                })
                ->sortBy([
                    ['batch_name',    'desc'],  // T14 before T13
                    ['lecturer_name', 'asc'], //alphabetical within each batch
                ])
                ->values();

            // Flat, per-student list — shown instead of the grouped table when a
            // status card/filter is active, so each student's lecturer is visible directly.
            $flatStudents = $filterStatus !== ''
                ? $classified->where('status', $filterStatus)->values()
                : collect();
        }

        // ── Muqarrar Targets Tab ───────────────────────────
        $muqarrars           = collect();
        $muqarrarsBySemester = collect();
        // Keyed by batch (the field admin now picks), not semester_id, so the
        // "suggest from previous week" feature suggests the previous week for
        // the SAME batch rather than the old student-level grouping.
        $muqarrarsForJs = Muqarrar::select('batch_id', 'week', 'start_page', 'end_page')
            ->orderBy('batch_id')->orderBy('week')
            ->get()
            ->groupBy(fn ($m) => $m->batch_id ?? '__all__')
            ->map(fn($grp) => $grp->keyBy('week')->map(fn($m) => [
                'start_page' => (int) $m->start_page,
                'end_page'   => (int) $m->end_page,
            ]));

        if ($tab === 'muqarrar') {
            $muqarrars = Muqarrar::with(['semester', 'batch', 'academicPeriod'])
                ->when($filterAcademicPeriod, fn($q) => $q->where('academic_period_id', $filterAcademicPeriod))
                ->when($filterWeek,     fn($q) => $q->where('week', $filterWeek))
                ->when($filterBatch,    fn($q) => $q->where('batch_id', $filterBatch))
                ->orderBy('batch_id')->orderBy('week')
                ->get();

            $muqarrarIds = $muqarrars->pluck('muqarrar_id')->filter();

            $tasmiStats = DB::table('tasmi as t')
                ->join('student as s', 't.student_id', '=', 's.student_id')
                ->select('t.muqarrar_id', DB::raw('COUNT(DISTINCT t.student_id) as total'))
                ->whereIn('t.muqarrar_id', $muqarrarIds)
                ->where('s.status', Student::STATUS_ACTIVE)
                ->whereNotNull('s.lecturer_id')
                ->groupBy('t.muqarrar_id')
                ->get()->keyBy('muqarrar_id');

            $completedStats = $muqarrarIds->isNotEmpty()
                ? DB::table('tasmi as t')
                    ->join('tasmi_progress as tp', 't.tasmi_id', '=', 'tp.tasmi_id')
                    ->join('muqarrar as m', 't.muqarrar_id', '=', 'm.muqarrar_id')
                    ->join('student as s', 't.student_id', '=', 's.student_id')
                    ->whereIn('t.muqarrar_id', $muqarrarIds)
                    ->where('s.status', Student::STATUS_ACTIVE)
                    ->whereNotNull('s.lecturer_id')
                    ->whereRaw('tp.page_reached >= m.end_page')
                    ->select('t.muqarrar_id', 't.student_id')
                    ->distinct()
                    ->get()
                    ->groupBy('muqarrar_id')
                    ->map(fn($grp) => (object) ['completed' => $grp->count()])
                : collect();

            $muqarrars = $muqarrars->map(function ($m) use ($tasmiStats, $completedStats) {
                $total     = (int) ($tasmiStats[$m->muqarrar_id]->total     ?? 0);
                $completed = (int) ($completedStats[$m->muqarrar_id]->completed ?? 0);
                $pct       = $total > 0 ? min(100, round($completed / $total * 100)) : 0;

                if ($total === 0)     $status = 'Upcoming';
                elseif ($pct >= 100) $status = 'Completed';
                elseif ($pct >= 70)  $status = 'In Progress';
                else                  $status = 'Behind Schedule';

                $m->total_students     = $total;
                $m->completed_students = $completed;
                $m->completion_pct     = $pct;
                $m->muq_status         = $status;
                return $m;
            })
            ->when($filterStatus !== '', fn($c) => $c->filter(fn($m) => $m->muq_status === $filterStatus))
            ->values();

            $muqarrarsBySemester = $muqarrars->groupBy('semester_id');
        }

        return view('admin.hafazan', compact(
            'tab', 'search', 'filterLecturer', 'filterStatus', 'filterAcademicPeriod', 'filterWeek', 'filterBatch',
            'filterHalaqah', 'selectedDate',
            'halaqahGroups', 'flatStudents', 'halaqahOptions',
            'muqarrars', 'muqarrarsBySemester', 'muqarrarsForJs',
            'lecturers', 'semesters', 'academicPeriods', 'activeAcademicPeriod', 'batches', 'muqarrarWeeks',
            'totalStudentCount', 'aheadCount', 'onTrackCount', 'behindCount', 'notRecordedCount',
            'todayCheckpoint', 'currentMuqarrar', 'batchMuqarrars'
        ));
    }

    /** Full-page halaqah (lecturer/batch) hafazan drill-down — replaces the old modal. */
    public function details(Request $request)
    {
        $lecturerId   = trim((string) $request->query('lecturer_id', ''));
        $batchId      = $request->query('batch_id', '');
        $selectedDate = $request->query('date', Carbon::today()->format('Y-m-d'));
        $selDateObj   = Carbon::parse($selectedDate)->startOfDay();

        $students = Student::with(['batch', 'lecturer'])
            ->operational()
            ->where('lecturer_id', $lecturerId)
            ->when(
                $batchId !== '',
                fn ($q) => $q->where('batch_id', $batchId),
                fn ($q) => $q->whereNull('batch_id')
            )
            ->orderBy('student_name')
            ->get();

        if ($students->isEmpty()) {
            abort(404);
        }

        $studentBatchIds = $students->pluck('batch_id')->filter()->unique()->values();
        $allMuqarrars = Muqarrar::whereNotNull('week_start_date')
            ->where(fn ($q) => $q->whereIn('batch_id', $studentBatchIds)->orWhereNull('batch_id'))
            ->orderBy('week_start_date')
            ->get();

        [$getMuqarrarForBatch, $getCheckpoint] = $this->muqarrarLookupHelpers($allMuqarrars, $selDateObj);

        $dateRecords = Tasmi::with(['progress'])
            ->whereIn('student_id', $students->pluck('student_id'))
            ->whereDate('tasmi_date', $selectedDate)
            ->get()
            ->keyBy('student_id');

        $khatamCounts = KhatamRecord::countsAsOf($students->pluck('student_id'), $selectedDate);
        $classified   = $students->map(fn ($student) => $this->classifyStudent($student, $dateRecords, $getMuqarrarForBatch, $getCheckpoint, $khatamCounts));

        // Sort: Behind first, then Not Recorded, then On-track, Ahead last — matches the old modal
        $statusOrder = ['Behind' => 0, 'Not Recorded' => 1, 'On-track' => 2, 'Ahead' => 3];
        $classified = $classified->sortBy(fn ($s) => $statusOrder[$s['status']] ?? 9)->values();

        $backParams = array_filter([
            'date'    => $selectedDate,
            'lecturer_id' => $request->query('list_lecturer', ''),
            'halaqah'     => $request->query('list_halaqah', ''),
            'status'      => $request->query('list_status', ''),
            'search'      => $request->query('list_search', ''),
            'tab'         => 'progress',
        ], fn ($v) => $v !== '' && $v !== null);

        return view('admin.hafazan-details', [
            'selectedDate' => $selDateObj,
            'lecturerName' => $classified->first()['lecturer_name'],
            'batchName'    => $classified->first()['batch_name'],
            'students'     => $classified,
            'backParams'   => $backParams,
        ]);
    }

    public function storeMuqarrar(Request $request)
    {
        $activePeriod = AcademicPeriod::current();

        if (! $activePeriod) {
            return back()->withInput()->with(
                'error',
                'No active academic semester is currently available. Please activate an academic semester before adding Muqarrar targets.'
            );
        }

        $data = $request->validate([
            'batch_id'        => 'required|exists:batch,batch_id',
            'week'            => 'required|integer|min:1',
            'week_start_date' => 'nullable|date',
            'start_page'      => 'required|integer|min:0',
            'end_page'        => 'required|integer|min:1',
        ]);

        $data['academic_period_id'] = $activePeriod->id;

        Muqarrar::create($data);
        return redirect()->route('admin.hafazan', ['tab' => 'muqarrar'])
            ->with('success', 'Muqarrar Week ' . $data['week'] . ' created successfully.');
    }

    public function updateMuqarrar(Request $request, $id)
    {
        $muqarrar = Muqarrar::findOrFail($id);
        // Which academic semester (or legacy level) a record belongs to is fixed at
        // creation and never edited here — only its batch/week/page details can change.
        $data = $request->validate([
            'batch_id'        => 'required|exists:batch,batch_id',
            'week'            => 'required|integer|min:1',
            'week_start_date' => 'nullable|date',
            'start_page'      => 'required|integer|min:0',
            'end_page'        => 'required|integer|min:1',
        ]);
        $muqarrar->update($data);
        return redirect()->route('admin.hafazan', ['tab' => 'muqarrar'])
            ->with('success', 'Muqarrar Week ' . $data['week'] . ' updated successfully.');
    }

    public function destroyMuqarrar($id)
    {
        $muqarrar = Muqarrar::findOrFail($id);
        $week = $muqarrar->week;

        DB::transaction(function () use ($muqarrar) {
            $tasmiIds = DB::table('tasmi')
                ->where('muqarrar_id', $muqarrar->muqarrar_id)
                ->pluck('tasmi_id');

            if ($tasmiIds->isNotEmpty()) {
                DB::table('tasmi_progress')->whereIn('tasmi_id', $tasmiIds)->delete();
                DB::table('tasmi')->where('muqarrar_id', $muqarrar->muqarrar_id)->delete();
            }

            $muqarrar->delete();
        });

        return redirect()->route('admin.hafazan', ['tab' => 'muqarrar'])
            ->with('success', 'Muqarrar Week ' . $week . ' and all related records deleted.');
    }

    /**
     * Builds the two muqarrar-lookup closures shared by index()/details():
     * one resolves the applicable Muqarrar for a batch on the selected date,
     * the other computes that muqarrar's daily checkpoint page.
     */
    private function muqarrarLookupHelpers($allMuqarrars, Carbon $selDateObj): array
    {
        $getMuqarrarForBatch = function (?int $batchId) use ($allMuqarrars, $selDateObj): ?Muqarrar {
            $subset = $batchId
                ? $allMuqarrars->where('batch_id', $batchId)
                : collect();

            if ($subset->isEmpty()) {
                $subset = $allMuqarrars->whereNull('batch_id');
            }

            foreach ($subset->sortBy('week_start_date') as $m) {
                $ws = Carbon::parse($m->week_start_date)->startOfDay();
                $we = $ws->copy()->addDays(6)->endOfDay();
                if ($selDateObj->between($ws, $we)) {
                    return $m;
                }
            }

            return $subset->sortBy('week_start_date')->last();
        };

        $getCheckpoint = function (?Muqarrar $muqarrar) use ($selDateObj): int {
            if (! $muqarrar) return 0;

            $totalDays = 5;
            if ($muqarrar->week_start_date) {
                $ws        = Carbon::parse($muqarrar->week_start_date)->startOfDay();
                $sel       = Carbon::parse($selDateObj->format('Y-m-d'))->startOfDay();
                $diffDays  = (int) $ws->diffInDays($sel, false);
                $dayOfWeek = max(1, min($totalDays, $diffDays + 1));
            } else {
                $dayOfWeek = max(1, min($totalDays, $selDateObj->dayOfWeekIso));
            }

            $weekly = (int) $muqarrar->end_page - (int) $muqarrar->start_page;
            return (int) min(
                (int) $muqarrar->end_page,
                (int) round((int) $muqarrar->start_page + ($weekly / $totalDays) * $dayOfWeek)
            );
        };

        return [$getMuqarrarForBatch, $getCheckpoint];
    }

    /**
     * Classifies one student's hafazan status (Ahead/On-track/Behind/Not Recorded) for the selected date.
     * Khatam students count as Ahead (Muqarrar fulfilled) whatever page they are revising.
     */
    private function classifyStudent(Student $student, $dateRecords, callable $getMuqarrarForBatch, callable $getCheckpoint, $khatamCounts): array
    {
        $studentMuqarrar   = $getMuqarrarForBatch($student->batch_id);
        $studentCheckpoint = $getCheckpoint($studentMuqarrar);
        $endPage           = (int) ($studentMuqarrar?->end_page ?? 0);
        $khatamCount       = $khatamCounts->get($student->student_id, 0);

        $record    = $dateRecords->get($student->student_id);
        $page      = (int) ($record?->progress?->page_reached ?? 0);
        $remaining = ($endPage > 0 && $page > 0 && $khatamCount === 0) ? max(0, $endPage - $page) : null;

        if (! $record || $page === 0) {
            $status = 'Not Recorded';
        } elseif ($khatamCount > 0) {
            $status = 'Ahead';
        } elseif ($endPage > 0 && $page >= $endPage) {
            $status = 'Ahead';
        } elseif ($studentCheckpoint > 0 && $page >= $studentCheckpoint) {
            $status = 'On-track';
        } else {
            $status = 'Behind';
        }

        return [
            'student_id'    => $student->student_id,
            'student_name'  => $student->student_name ?? 'Unknown',
            'lecturer_id'   => (string) ($student->lecturer_id ?? ''),
            'lecturer_name' => $student->lecturer?->lecturer_name ?? '—',
            'batch_id'      => (string) ($student->batch_id ?? ''),
            'batch_name'    => $student->batch?->batch_name ?? '—',
            'latest_page'   => $page ?: null,
            'remaining'     => $remaining,
            'status'        => $status,
            'khatam_count'  => $khatamCount,
        ];
    }
}
