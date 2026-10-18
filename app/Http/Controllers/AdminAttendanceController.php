<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Batch;
use App\Models\Student;
use App\Services\WarningLetterService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $selectedDate    = $this->parseDate($request);
        $selectedDateStr = $selectedDate->toDateString();
        $selectedBatch   = $this->cleanBatchName($request->input('batch', ''));
        $selectedStatus  = (string) ($request->input('status') ?? '');
        $search          = trim((string) $request->input('search', ''));

        // Step 1: load all operational students (no date constraint here)
        $students = Student::query()
            ->operational()
            ->with(['batch', 'lecturer'])
            ->when($selectedBatch !== '', fn ($q) => $q->whereHas('batch', fn ($b) => $b
                ->where('batch_name', $selectedBatch)
                ->orWhere('batch_name', 'Batch - ' . $selectedBatch)))
            ->when($search !== '', fn ($q) => $q->where(fn ($s) => $s
                ->where('student_name', 'like', "%{$search}%")
                ->orWhere('student_id',   'like', "%{$search}%")
                ->orWhere('nric',         'like', "%{$search}%")
                ->orWhereHas('batch',    fn ($b) => $b->where('batch_name',    'like', "%{$search}%"))
                ->orWhereHas('lecturer', fn ($l) => $l->where('lecturer_name', 'like', "%{$search}%"))))
            ->orderBy('lecturer_id')
            ->orderBy('batch_id')
            ->orderBy('student_name')
            ->get();

        $studentRows = $this->buildStudentRows($students, $selectedDateStr);

        // Summary stats — always computed from ALL students regardless of status filter
        $presentCount     = $studentRows->where('status', 'present')->count();
        $absentCount      = $studentRows->where('status', 'absent')->count();
        $excusedCount     = $studentRows->where('status', 'absent_with_reason')->count();
        $notRecordedCount = $studentRows->where('status', 'not_scanned')->count();
        $totalStudents    = $studentRows->count();
        $attendanceRate   = $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100) : 0;

        // Status filter only narrows the table rows — cards stay unaffected
        $tableRows = $selectedStatus !== ''
            ? $studentRows->where('status', $selectedStatus)->values()
            : $studentRows;

        $lecturerRows = $tableRows
            ->groupBy(fn ($r) => $r->lecturer_id . '|' . ($r->batch_id ?: 'unassigned'))
            ->map(function ($rows) {
                $first            = $rows->first();
                $total            = $rows->count();
                $presentCount     = $rows->where('status', 'present')->count();
                $absentCount      = $rows->where('status', 'absent')->count();
                $excusedCount     = $rows->where('status', 'absent_with_reason')->count();
                $notRecordedCount = $rows->where('status', 'not_scanned')->count();
                $attendanceRate   = $total > 0 ? round(($presentCount / $total) * 100) : 0;

                return (object) [
                    'lecturer_id'       => $first->lecturer_id,
                    'lecturer_name'     => $first->lecturer_name,
                    'batch_id'          => $first->batch_id,
                    'batch'             => $first->batch,
                    'total_students'    => $total,
                    'present_count'     => $presentCount,
                    'absent_count'      => $absentCount,
                    'excused_count'     => $excusedCount,
                    'not_scanned_count' => $notRecordedCount,
                    'attendance_rate'   => $attendanceRate,
                    'students'          => $rows->values(),
                ];
            })

            //->sortBy(fn ($row) => $row->batch)--}} //sort batch
            ->values();

        // Mirrors Batch Management's "is_active" flag, so the filter always matches
        // what Batch Management shows as active.
        $batchOptions = Batch::active()
            ->orderedByName()
            ->pluck('batch_name')
            ->map(fn ($name) => $this->cleanBatchName($name))
            ->filter()
            ->values();

        $dateLabel = $selectedDate->isToday() ? 'today' : 'on ' . $selectedDate->format('d M Y');
        $summaryCards = [
            ['label' => 'Present',        'value' => $presentCount   . ' Students', 'note' => 'Attended ' . $dateLabel,  'theme' => 'green', 'icon' => 'check',   'status' => 'present'],
            ['label' => 'Absent (TH)',    'value' => $absentCount    . ' Students', 'note' => 'Unexcused — counts as WL', 'theme' => 'red',   'icon' => 'cross',   'status' => 'absent'],
            ['label' => 'Excused (THS)',  'value' => $excusedCount   . ' Students', 'note' => 'Absent with reason',       'theme' => 'blue',  'icon' => 'medical', 'status' => 'absent_with_reason'],
            ['label' => 'Attendance Rate','value' => $attendanceRate . '%',         'note' => 'Present ÷ total',          'theme' => 'amber', 'icon' => 'chart',   'status' => null],
        ];

        return view('admin.attendance', compact(
            'selectedDate',
            'selectedBatch',
            'selectedStatus',
            'search',
            'summaryCards',
            'lecturerRows',
            'tableRows',
            'batchOptions',
            'notRecordedCount',
            'totalStudents',
        ));
    }

    /** Full-page lecturer/batch attendance detail (replaces the old modal). */
    public function details(Request $request): View
    {
        $selectedDate    = $this->parseDate($request);
        $selectedDateStr = $selectedDate->toDateString();

        $lecturerId = trim((string) $request->input('lecturer_id', ''));
        $batchId    = $request->input('batch_id');
        $batchId    = ($batchId === '' || $batchId === null) ? null : $batchId;

        $students = Student::query()
            ->operational()
            ->with(['batch', 'lecturer'])
            ->where('lecturer_id', $lecturerId)
            ->when(
                $batchId,
                fn ($q) => $q->where('batch_id', $batchId),
                fn ($q) => $q->whereNull('batch_id')
            )
            ->orderBy('student_name')
            ->get();

        if ($students->isEmpty()) {
            abort(404);
        }

        $studentRows = $this->buildStudentRows($students, $selectedDateStr);

        // Params to reconstruct the list page's previous filters for the back link
        $backParams = array_filter([
            'date'   => $selectedDateStr,
            'batch'  => $request->input('list_batch', ''),
            'status' => $request->input('list_status', ''),
            'search' => $request->input('list_search', ''),
        ], fn ($value) => $value !== '' && $value !== null);

        return view('admin.attendance-details', [
            'selectedDate' => $selectedDate,
            'lecturerName' => $studentRows->first()->lecturer_name,
            'batchLabel'   => $studentRows->first()->batch,
            'studentRows'  => $studentRows,
            'backParams'   => $backParams,
        ]);
    }

    /** Admin manual attendance override for any student. */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'student_id' => 'required|exists:student,student_id',
            'date'       => 'required|date',
            'status'     => 'required|in:present,absent,absent_with_reason',
            'reason_type'=> 'nullable|string|max:100',
            'remarks'    => 'nullable|string|max:500',
        ]);

        $isExcused  = $request->status === 'absent_with_reason';
        $reasonType = $isExcused ? $request->reason_type : null;
        $remarks    = $isExcused ? $request->remarks      : null;

        Attendance::updateOrCreate(
            ['student_id' => $request->student_id, 'date' => $request->date],
            ['status' => $request->status, 'reason_type' => $reasonType, 'remarks' => $remarks]
        );

        app(WarningLetterService::class)->recalculate($request->student_id);

        return redirect()->back()->with('success', 'Attendance updated for student.');
    }

    private function cleanBatchName(?string $batchName): string
    {
        if (! $batchName) {
            return '';
        }

        return trim(preg_replace('/^Batch\s*-\s*/i', '', $batchName));
    }

    /** Parses the date input — accepts both YYYY-MM-DD (HTML date picker) and DD/MM/YYYY. */
    private function parseDate(Request $request): Carbon
    {
        $rawDate = trim($request->input('date', ''));

        if ($rawDate !== '' && preg_match('#^\d{2}/\d{2}/\d{4}$#', $rawDate)) {
            return Carbon::createFromFormat('d/m/Y', $rawDate)->startOfDay();
        }

        if ($rawDate !== '') {
            return Carbon::parse($rawDate)->startOfDay();
        }

        return Carbon::today()->startOfDay();
    }

    /** Builds per-student attendance rows (with warning/status labels) for a given date. */
    private function buildStudentRows($students, string $selectedDateStr)
    {
        $attendanceMap = Attendance::whereIn('student_id', $students->pluck('student_id'))
            ->where('date', $selectedDateStr)
            ->get()
            ->keyBy('student_id');

        return $students->map(function (Student $student) use ($attendanceMap) {
            $attendance = $attendanceMap->get($student->student_id);
            $rawStatus  = strtolower($attendance?->status ?? '');

            $status = match ($rawStatus) {
                'present'            => 'present',
                'absent'             => 'absent',
                'absent_with_reason' => 'absent_with_reason',
                default              => 'not_scanned',
            };

            $statusLabel = match ($status) {
                'present'            => 'Present',
                'absent'             => 'Absent (TH)',
                'absent_with_reason' => 'Excused (THS)',
                default              => 'Not Recorded',
            };

            return (object) [
                'student_id'    => $student->student_id ?: '-',
                'ic_number'     => $student->nric ?: '-',
                'student_name'  => $student->student_name ?? 'Unknown',
                'lecturer_id'   => $student->lecturer_id,
                'lecturer_name' => $student->lecturer?->lecturer_name ?? '-',
                'batch_id'      => $student->batch_id,
                'batch'         => $this->cleanBatchName($student->batch?->batch_name) ?: '-',
                'check_in'      => optional($attendance?->scanned_at)->format('h:i A') ?: '-',
                'status'        => $status,
                'status_label'  => $statusLabel,
                'reason_type'   => $attendance?->reason_type,
                'remarks'       => $attendance?->remarks,
                'warning_level' => (int) ($student->warning_level ?? 0),
                'warning_label' => $student->warningLabel(),
                'warning_color' => $student->warningColor(),
            ];
        });
    }
}
