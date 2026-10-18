<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\Lecturer;
use App\Models\SubjectAttendanceRecord;
use App\Models\SubjectAttendanceSession;
use App\Models\SubjectT;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSubjectAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $academicPeriods = AcademicPeriod::orderByDesc('start_date')->get();
        $activePeriod = AcademicPeriod::current();

        $selectedPeriodId = $request->query('academic_period_id') ?: $activePeriod?->id;
        $period = $selectedPeriodId ? $academicPeriods->firstWhere('id', (int) $selectedPeriodId) : null;

        $subjects = SubjectT::orderBy('subjectT_code')->get();
        $lecturers = Lecturer::orderBy('lecturer_name')->get();

        $selectedDate = $request->filled('date') ? Carbon::parse($request->query('date'))->startOfDay() : null;
        $filterSubjectId = $request->query('subjectT_id', '');
        $filterLecturerId = $request->query('lecturer_id', '');
        $filterStatus = $request->query('status', '');

        $rows = collect();
        $sessionRows = collect();
        $presentCount = 0;
        $absentCount = 0;
        $attendanceRate = null;

        if ($period) {
            $records = SubjectAttendanceRecord::where('academic_period_id', $period->id)
                ->when($filterSubjectId !== '', fn ($q) => $q->where('subjectT_id', $filterSubjectId))
                ->when($filterLecturerId !== '', fn ($q) => $q->where('lecturer_id', $filterLecturerId))
                ->when($selectedDate, fn ($q) => $q->whereDate('date', $selectedDate))
                ->with(['student', 'subject', 'batch', 'lecturer', 'session.section'])
                ->orderByDesc('date')
                ->get();

            $presentCount = $records->where('status', 'present')->count();
            $absentCount = $records->where('status', 'absent')->count();
            $total = $presentCount + $absentCount;
            $attendanceRate = $total > 0 ? round(($presentCount / $total) * 100) : null;

            // Grouped-by-session overview (default view) — mirrors Tasmik Attendance's
            // grouped-by-lecturer-and-batch table, one row per subject-class session.
            $sessionRows = $records
                ->groupBy('subject_attendance_session_id')
                ->map(function ($group) {
                    $first   = $group->first();
                    $total   = $group->count();
                    $present = $group->where('status', 'present')->count();
                    $absent  = $group->where('status', 'absent')->count();

                    return (object) [
                        'session_id'      => $first->subject_attendance_session_id,
                        'date'            => $first->date,
                        'subject_name'    => $first->subject?->subjectT_name ?? '—',
                        // Roster scope of the SESSION (Section/Halaqah/legacy Batch), not a
                        // per-record batch — a session's roster is no longer guaranteed to
                        // be one batch, so this can't be read off any single record anymore.
                        'roster_label'    => $first->session?->scope_label ?? ($first->batch?->batch_name ?? '—'),
                        'lecturer_name'   => $first->lecturer?->lecturer_name ?? '—',
                        'total_students'  => $total,
                        'present_count'   => $present,
                        'absent_count'    => $absent,
                        'attendance_rate' => $total > 0 ? round($present / $total * 100) : 0,
                    ];
                })
                ->sortByDesc(fn ($s) => $s->date)
                ->values();

            $rows = $records
                ->when($filterStatus !== '', fn ($r) => $r->where('status', $filterStatus))
                ->map(fn (SubjectAttendanceRecord $r) => (object) [
                    'session_id' => $r->subject_attendance_session_id,
                    'student_id' => $r->student?->student_id ?? $r->student_id,
                    'student_name' => $r->student?->student_name ?? 'Unknown',
                    'subject_name' => $r->subject?->subjectT_name ?? '—',
                    // Per-record: each student's own batch, always accurate regardless of
                    // whether the session's roster spans multiple batches.
                    'batch_name' => $r->batch?->batch_name ?? '—',
                    'section_name' => $r->section?->section_name,
                    'lecturer_name' => $r->lecturer?->lecturer_name ?? '—',
                    'status' => $r->status,
                    'date' => $r->date,
                    'scanned_at' => $r->scanned_at,
                ])
                ->values();
        }

        return view('admin.subject-attendance.index', compact(
            'academicPeriods', 'period', 'subjects', 'lecturers',
            'selectedDate', 'filterSubjectId', 'filterLecturerId', 'filterStatus',
            'rows', 'sessionRows', 'presentCount', 'absentCount', 'attendanceRate'
        ));
    }

    /** Full-page drill-down for one subject-class session — replaces having no detail view at all. */
    public function details(Request $request, int $id): View
    {
        $session = SubjectAttendanceSession::with(['subject', 'batch', 'section', 'lecturer', 'academicPeriod'])
            ->findOrFail($id);

        $rows = SubjectAttendanceRecord::where('subject_attendance_session_id', $id)
            ->with('student')
            ->get()
            ->map(fn (SubjectAttendanceRecord $r) => (object) [
                'student_id'   => $r->student?->student_id ?? $r->student_id,
                'student_name' => $r->student?->student_name ?? 'Unknown',
                'status'       => $r->status,
                'scanned_at'   => $r->scanned_at,
            ])
            ->sortBy('student_name')
            ->values();

        // Reconstructs the list page's previous filters for the "Back" link.
        $backParams = array_filter([
            'academic_period_id' => $request->query('academic_period_id'),
            'subjectT_id'        => $request->query('subjectT_id'),
            'lecturer_id'        => $request->query('lecturer_id'),
            'date'               => $request->query('date'),
            'status'             => $request->query('list_status'),
        ], fn ($v) => $v !== null && $v !== '');

        return view('admin.subject-attendance.details', compact('session', 'rows', 'backParams'));
    }
}
