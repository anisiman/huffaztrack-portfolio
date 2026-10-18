<?php

namespace App\Http\Controllers;

use App\Models\AcademicPeriod;
use App\Models\Batch;
use App\Models\Lecturer;
use App\Models\SubjectAttendanceRecord;
use App\Models\SubjectT;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SubjectAttendanceReportController extends Controller
{
    public function adminIndex(Request $request): View
    {
        $data = $this->buildReportData($request, null);

        return view('admin.subject-attendance-report', $data);
    }

    public function adminPdf(Request $request)
    {
        $data = $this->buildReportData($request, null);
        $data['generatedAt'] = now()->format('d M Y, h:i A');

        return Pdf::loadView('pdf.subject-attendance-report', $data)
            ->setPaper('a4', 'landscape')
            ->download('subject-attendance-report-' . now()->format('Ymd-His') . '.pdf');
    }

    public function lecturerIndex(Request $request): View
    {
        $lecturerId = Lecturer::where('user_id', Auth::id())->value('lecturer_id');
        $data = $this->buildReportData($request, $lecturerId);

        return view('lecturer.subject-attendance-report', $data);
    }

    public function lecturerPdf(Request $request)
    {
        $lecturerId = Lecturer::where('user_id', Auth::id())->value('lecturer_id');
        $data = $this->buildReportData($request, $lecturerId);
        $data['generatedAt'] = now()->format('d M Y, h:i A');
        $data['lecturerName'] = Lecturer::where('lecturer_id', $lecturerId)->value('lecturer_name') ?? 'Lecturer';

        return Pdf::loadView('pdf.subject-attendance-report', $data)
            ->setPaper('a4', 'landscape')
            ->download('my-subject-attendance-report-' . now()->format('Ymd-His') . '.pdf');
    }

    /**
     * Shared data builder for admin/lecturer, screen/PDF. When $lockLecturerId is
     * given (lecturer routes), every result is hard-scoped to that lecturer — this
     * is what a lecturer can never see or export another lecturer's rows.
     */
    private function buildReportData(Request $request, ?string $lockLecturerId): array
    {
        $academicPeriods = AcademicPeriod::orderByDesc('start_date')->get();
        $activePeriod = AcademicPeriod::current();

        $selectedPeriodId = $request->query('academic_period_id') ?: $activePeriod?->id;
        $period = $selectedPeriodId ? $academicPeriods->firstWhere('id', (int) $selectedPeriodId) : null;

        $filterSubjectId = $request->query('subjectT_id', '');
        $filterBatchId = $request->query('batch_id', '');
        $filterLecturerId = $lockLecturerId ?: $request->query('lecturer_id', '');
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->query('date_from'))->startOfDay() : null;
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->query('date_to'))->endOfDay() : null;

        $subjects = SubjectT::orderBy('subjectT_code')->get();
        $batches = Batch::active()->orderedByName()->get();
        $lecturers = $lockLecturerId ? collect() : Lecturer::orderBy('lecturer_name')->get();

        $rows = collect();
        $totalPresent = 0;
        $totalAbsent = 0;
        $overallRate = null;

        if ($period) {
            $records = SubjectAttendanceRecord::where('academic_period_id', $period->id)
                ->when($filterSubjectId !== '', fn ($q) => $q->where('subjectT_id', $filterSubjectId))
                ->when($filterBatchId !== '', fn ($q) => $q->where('batch_id', $filterBatchId))
                ->when($filterLecturerId !== '', fn ($q) => $q->where('lecturer_id', $filterLecturerId))
                ->when($dateFrom, fn ($q) => $q->where('date', '>=', $dateFrom->toDateString()))
                ->when($dateTo, fn ($q) => $q->where('date', '<=', $dateTo->toDateString()))
                ->with(['student', 'subject', 'batch', 'section'])
                ->get();

            $rows = $records
                ->groupBy(fn (SubjectAttendanceRecord $r) => $r->student_id . '|' . $r->subjectT_id)
                ->map(function ($group) {
                    $first = $group->first();
                    $present = $group->where('status', 'present')->count();
                    $absent = $group->where('status', 'absent')->count();
                    $total = $present + $absent;

                    return (object) [
                        'student_name' => $first->student?->student_name ?? 'Unknown',
                        'student_id' => $first->student?->student_id ?? $first->student_id,
                        'subject_name' => $first->subject?->subjectT_name ?? '—',
                        'batch_name' => $first->batch?->batch_name ?? '—',
                        // Only meaningful for Madah rows recorded under the new
                        // Section-scoped model; null for Hafazan and legacy rows.
                        'section_name' => $first->section?->section_name,
                        'present' => $present,
                        'absent' => $absent,
                        'percentage' => $total > 0 ? round(($present / $total) * 100) : null,
                    ];
                })
                ->sortBy([
                    fn ($r) => $r->batch_name,
                    fn ($r) => $r->student_name,
                ])
                ->values();

            $totalPresent = $records->where('status', 'present')->count();
            $totalAbsent = $records->where('status', 'absent')->count();
            $totalAll = $totalPresent + $totalAbsent;
            $overallRate = $totalAll > 0 ? round(($totalPresent / $totalAll) * 100) : null;
        }

        return compact(
            'academicPeriods', 'period', 'subjects', 'batches', 'lecturers',
            'filterSubjectId', 'filterBatchId', 'filterLecturerId', 'dateFrom', 'dateTo',
            'rows', 'totalPresent', 'totalAbsent', 'overallRate', 'lockLecturerId'
        );
    }
}
