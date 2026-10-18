@extends('layouts.lecturer')

@section('title', 'Intervention — ' . $student->student_name)

@section('content')
<div class="space-y-6">

    <a href="{{ route('lecturer.analytics') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 transition hover:text-violet-700">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
        </svg>
        Back to Analytics
    </a>

    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-[1.875rem] font-bold tracking-tight text-slate-800">{{ $student->student_name }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $student->student_id }} · {{ $student->batch?->batch_name ?? 'Unknown batch' }}</p>
                @if($student->lecturer)
                    <p class="mt-2 text-sm text-slate-500">Lecturer: {{ $student->lecturer->lecturer_name }}</p>
                @endif
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl bg-slate-50 p-4 text-center">
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Latest CGPA</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">{{ $latestResult?->cgpa ? number_format($latestResult->cgpa, 2) : 'N/A' }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4 text-center">
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Risk Level</p>
                    <p class="mt-2 text-2xl font-bold {{ $riskLevel === 'Rasib' ? 'text-red-600' : 'text-amber-600' }}">
                        {{ $riskLevel }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4 text-center">
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Trend</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">{{ $trend ? ucfirst($trend) : 'Review' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-800">CGPA Trend</h2>
                <p class="text-sm text-slate-500">Track this student’s CGPA across recorded semesters.</p>
            </div>
        </div>

        @if($student->results->isNotEmpty())
            <div class="h-72">
                <canvas id="interventionCgpaTrendChart"></canvas>
            </div>
        @else
            <p class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">No CGPA trend information is available.</p>
        @endif
    </div>

    <div class="grid gap-5 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-slate-800">Risk Reasons</h2>
            <p class="text-sm text-slate-500 mb-5">These are the main factors contributing to the student’s current at-risk profile.</p>

            @if(count($reasons) > 0)
                <ul class="space-y-3">
                    @foreach($reasons as $reason)
                        <li class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">{{ $reason }}</li>
                    @endforeach
                </ul>
            @else
                <p class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">No specific risk reasons could be derived from the current data.</p>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-slate-800">Recommended Actions</h2>
            <p class="text-sm text-slate-500 mb-5">Use these practical steps to support the student and reduce risk.</p>

            <ul class="space-y-3">
                @foreach($recommendations as $recommendation)
                    <li class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">{{ $recommendation }}</li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-bold text-slate-800">Attendance Summary</h2>
        @if($attendanceTotal > 0)
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl bg-slate-50 p-4 text-center">
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Present</p>
                    <p class="mt-2 text-lg font-bold text-slate-900">{{ $attendancePresent }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4 text-center">
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Absent</p>
                    <p class="mt-2 text-lg font-bold text-slate-900">{{ $attendanceAbsent }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4 text-center">
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Rate</p>
                    <p class="mt-2 text-lg font-bold text-slate-900">{{ $attendanceRate }}%</p>
                </div>
            </div>
        @else
            <p class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">No attendance data is available for the last 30 days.</p>
        @endif
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-bold text-slate-800">Intervention Notes</h2>
        <p class="text-sm text-slate-500">Record your observations, consultation outcomes, and agreed next steps in the central student file or notes system.</p>
        <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
            • Review the student’s attendance and CGPA before every follow-up meeting.
            <br>• Confirm the next checkpoint date and expected improvements.
            <br>• Keep the student’s parent/guardian informed if required by policy.
        </div>
    </div>

    @php
        $trendResults = $student->results->sortBy(fn($r) => $r->semester?->semester_id ?? 0)
            ->values()
            ->map(function($result, $key) {
                return [
                    'label' => $result->semester?->semester_name ?? 'Sem ' . ($key + 1),
                    'value' => $result->cgpa !== null ? (float) $result->cgpa : null,
                ];
            })->toArray();
    @endphp

    @section('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('interventionCgpaTrendChart');
                if (!ctx) return;

                const trendResults = @json($trendResults);

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: trendResults.map(item => item.label),
                        datasets: [{
                            label: 'CGPA',
                            data: trendResults.map(item => item.value),
                            borderColor: '#db2777',
                            backgroundColor: 'rgba(219, 39, 119, 0.16)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 4,
                            pointBackgroundColor: '#db2777',
                            borderWidth: 2,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 4,
                                ticks: { stepSize: 0.5 },
                            },
                        },
                        plugins: {
                            legend: { display: false },
                        },
                    },
                });
            });
        </script>
    @endsection
</div>
@endsection
