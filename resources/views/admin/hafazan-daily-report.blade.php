@extends('layouts/admin')

@section('title', 'Daily Hafazan Report')

@push('styles')
<style>
/* ── Page ─────────────────────────────────── */
.wr-page    { display:flex; flex-direction:column; gap:1.5rem; }
.wr-header  { display:flex; flex-direction:column; gap:0.25rem; }
.wr-title   { margin:0; font-size:2.25rem; font-weight:700; letter-spacing:-0.02em; color:#1e293b; }
.wr-subtitle{ margin:0; font-size:0.92rem; color:#94a3b8; }

.wr-breadcrumb { display:flex; align-items:center; gap:0.5rem; font-size:0.82rem; color:#94a3b8; }
.wr-breadcrumb a { color:#7c3aed; text-decoration:none; font-weight:600; }
.wr-breadcrumb a:hover { text-decoration:underline; }

/* ── Filter bar ───────────────────────────── */
.wr-filters {
    display:flex; flex-wrap:wrap; gap:0.65rem; align-items:center;
    background:#fff; border:1px solid #e2e8f0;
    border-radius:1.1rem; padding:0.9rem 1.1rem;
    box-shadow:0 6px 18px rgba(15,23,42,.04);
}
.wr-filter-group  { display:flex; flex-direction:column; gap:0.3rem; }
.wr-filter-label  { font-size:0.72rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em; }
.wr-filter-select {
    min-height:2.2rem; padding:0 0.8rem;
    border:1px solid #e2e8f0; border-radius:0.65rem;
    background:#f8fafc; color:#334155;
    font-size:0.85rem; cursor:pointer; outline:none; min-width:140px;
}
.wr-filter-select:focus { border-color:#7c3aed; background:#fff; }
.wr-filter-actions { display:flex; gap:0.5rem; align-items:flex-end; padding-top:1.3rem; }
.wr-btn {
    display:inline-flex; align-items:center; justify-content:center; gap:0.4rem;
    min-height:2.2rem; padding:0 1rem;
    border:0; border-radius:0.65rem;
    font-size:0.85rem; font-weight:700; cursor:pointer; text-decoration:none;
}
.wr-btn--primary { background:linear-gradient(90deg,#5b21b6,#7c3aed); color:#fff; box-shadow:0 8px 20px rgba(124,58,237,.2); }
.wr-btn--ghost   { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
.wr-btn--detail  { background:#f5f3ff; color:#6d28d9; border:1px solid #ddd6fe; font-size:0.78rem; padding:0 0.75rem; min-height:1.9rem; white-space:nowrap; }

/* ── Date info banner ─────────────────────── */
.wr-info {
    display:flex; align-items:center; gap:0.75rem;
    background:#f5f3ff; border:1px solid #ddd6fe;
    border-radius:0.9rem; padding:0.8rem 1.1rem;
    font-size:0.875rem; color:#5b21b6;
}
.wr-info svg   { width:1.1rem; height:1.1rem; flex-shrink:0; }
.wr-info strong{ font-weight:700; }

/* ── KPI cards ────────────────────────────── */
.wr-kpis { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; }
@media(max-width:860px){ .wr-kpis { grid-template-columns:repeat(2,1fr); } }
@media(max-width:520px){ .wr-kpis { grid-template-columns:1fr; } }

.wr-kpi {
    background:#fff; border:2px solid #e2e8f0;
    border-radius:1.1rem; padding:1.1rem 1.25rem;
    box-shadow:0 8px 22px rgba(15,23,42,.05);
    display:flex; flex-direction:column; gap:0.35rem;
}
.wr-kpi__label { font-size:0.78rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em; }
.wr-kpi__value { font-size:1.75rem; font-weight:800; letter-spacing:-0.04em; }
.wr-kpi__note  { font-size:0.78rem; color:#94a3b8; }
.wr-kpi--green .wr-kpi__value { color:#16a34a; }
.wr-kpi--blue  .wr-kpi__value { color:#2563eb; }
.wr-kpi--red   .wr-kpi__value { color:#dc2626; }
.wr-kpi--gray  .wr-kpi__value { color:#64748b; }

/* ── Table card ───────────────────────────── */
.wr-card {
    background:#fff; border:1px solid #e2e8f0;
    border-radius:1.25rem; overflow:hidden;
    box-shadow:0 14px 32px rgba(15,23,42,.06);
}
.wr-card__head {
    display:flex; align-items:center; justify-content:space-between;
    padding:1.1rem 1.4rem 0.9rem; border-bottom:1px solid #f1f5f9;
}
.wr-card__title    { margin:0; font-size:1.05rem; font-weight:700; color:#1e293b; }
.wr-card__subtitle { margin:.25rem 0 0; font-size:0.82rem; color:#94a3b8; }
.wr-card__chip {
    display:inline-flex; align-items:center;
    background:#f5f3ff; color:#6d28d9;
    font-size:0.78rem; font-weight:700;
    padding:0.25rem 0.75rem; border-radius:20px;
}

/* ── Table ────────────────────────────────── */
.wr-table-wrap { overflow-x:auto; }
.wr-table { width:100%; border-collapse:collapse; min-width:860px; }
.wr-table th {
    padding:0.75rem 1rem; font-size:0.72rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.05em; color:#94a3b8;
    background:#fcfdff; border-bottom:1px solid #eef2f7; white-space:nowrap; text-align:left;
}
.wr-table td { padding:0.85rem 1rem; font-size:0.875rem; color:#475569; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.wr-table tbody tr:last-child td { border-bottom:0; }
.wr-table tbody tr:hover { background:#fafbff; }
.wr-table__name  { font-weight:600; color:#1e293b; }
.wr-table__num   { font-weight:700; color:#334155; }
.wr-table__empty { text-align:center; color:#94a3b8; padding:2.5rem 1rem; font-size:0.88rem; }

/* ── Batch group header row ───────────────── */
.wr-batch-header td {
    background:#f8fafc !important;
    border-top:1px solid #e2e8f0 !important;
    border-bottom:1px solid #e2e8f0 !important;
    padding:.45rem 1rem !important;
    font-size:.7rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.08em; color:#7c3aed;
}
.wr-batch-header:hover td { background:#f8fafc !important; }

/* ── Status count colours ─────────────────── */
.wr-count--green { font-weight:700; color:#16a34a; }
.wr-count--blue  { font-weight:700; color:#2563eb; }
.wr-count--red   { font-weight:700; color:#dc2626; }
.wr-count--gray  { font-weight:700; color:#94a3b8; }

/* ── Progress bar ─────────────────────────── */
.wr-progress { display:flex; align-items:center; gap:0.6rem; }
.wr-progress__bar { flex:1; height:6px; background:#f1f5f9; border-radius:99px; overflow:hidden; min-width:60px; }
.wr-progress__fill { height:100%; border-radius:99px; background:linear-gradient(90deg,#7c3aed,#5b21b6); }
.wr-progress__pct { font-size:0.82rem; font-weight:700; color:#334155; white-space:nowrap; min-width:2.5rem; text-align:right; }

/* ── Legend ───────────────────────────────── */
.wr-legend {
    display:flex; flex-wrap:wrap; gap:0.6rem; align-items:center;
    padding:0.8rem 1.4rem; border-top:1px solid #f1f5f9;
    font-size:0.75rem; color:#64748b;
}
.wr-legend__label { font-weight:600; color:#94a3b8; }
.wr-badge {
    display:inline-flex; align-items:center;
    font-size:0.72rem; font-weight:700;
    padding:3px 10px; border-radius:20px; white-space:nowrap;
}
.wr-badge--green { background:#dcfce7; color:#15803d; }
.wr-badge--blue  { background:#dbeafe; color:#1d4ed8; }
.wr-badge--red   { background:#fee2e2; color:#dc2626; }
.wr-badge--gray  { background:#f1f5f9; color:#64748b; }
</style>
@endpush

@section('content')
<div class="wr-page">

    {{-- ── Back ── --}}
    @include('partials.back-button', ['href' => route('admin.reports', ['section' => 'hafazan']), 'label' => 'Hafazan Reports'])

    {{-- ── Header ── --}}
    <div class="wr-header">
        <h1 class="wr-title">Daily Hafazan Report</h1>
        <p class="wr-subtitle">Student progress versus the selected date's muqarrar — all batches &amp; lecturers.</p>
    </div>

    {{-- ── Filters ── --}}
    <form class="wr-filters" method="GET" action="{{ route('admin.reports.hafazan.daily') }}">
        <div class="wr-filter-group">
            <span class="wr-filter-label">Date</span>
            <input class="wr-filter-select" type="date" name="date"
                value="{{ $date->format('Y-m-d') }}"
                max="{{ $today->format('Y-m-d') }}"
                onchange="this.form.submit()">
        </div>
        <div class="wr-filter-group">
            <span class="wr-filter-label">Batch</span>
            <select class="wr-filter-select" name="halaqah" onchange="this.form.submit()">
                <option value="">All Batches</option>
                @foreach ($halaqahOptions as $h)
                    <option value="{{ $h }}" {{ $filterHalaqah === $h ? 'selected' : '' }}>{{ $h }}</option>
                @endforeach
            </select>
        </div>
        <div class="wr-filter-actions">
            <button type="submit" class="wr-btn wr-btn--primary">Apply</button>
            @if ($date->format('Y-m-d') !== $today->format('Y-m-d') || $filterHalaqah !== '')
                <a href="{{ route('admin.reports.hafazan.daily') }}" class="wr-btn wr-btn--ghost">Clear</a>
            @endif
            <a href="{{ route('admin.reports.hafazan.daily.pdf', ['date' => $date->format('Y-m-d'), 'halaqah' => $filterHalaqah]) }}"
               class="wr-btn wr-btn--ghost" style="gap:0.35rem;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:.85rem;height:.85rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                PDF
            </a>
        </div>
    </form>

    {{-- ── Date / Muqarrar info banner ── --}}
    <div class="wr-info">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
        </svg>
        <span>
            Hafazan summary for <strong>{{ $date->format('d F Y') }}</strong>
            @if ($hafDailyMuq)
                &nbsp;&mdash;&nbsp; Week {{ $hafDailyMuq->week }},
                Pg {{ $hafDailyMuq->start_page }}&rarr;{{ $hafDailyMuq->end_page }}
                @if ($hafDailyChk > 0)
                    &nbsp;&mdash;&nbsp; Today&rsquo;s target: <strong>Pg {{ $hafDailyChk }}</strong>
                @endif
            @else
                &nbsp;&mdash;&nbsp; No muqarrar set for this date
            @endif
            &nbsp;&mdash;&nbsp; All batches &amp; lecturers
        </span>
    </div>

    {{-- ── KPI cards ── --}}
    <div class="wr-kpis">
        <div class="wr-kpi wr-kpi--green">
            <p class="wr-kpi__label">Completed</p>
            <p class="wr-kpi__value">{{ $hafDailyCounts['Completed'] }}</p>
            <p class="wr-kpi__note">Reached end page</p>
        </div>
        <div class="wr-kpi wr-kpi--blue">
            <p class="wr-kpi__label">On Track</p>
            <p class="wr-kpi__value">{{ $hafDailyCounts['On Track'] }}</p>
            <p class="wr-kpi__note">At today's muqarrar</p>
        </div>
        <div class="wr-kpi wr-kpi--red">
            <p class="wr-kpi__label">Behind</p>
            <p class="wr-kpi__value">{{ $hafDailyCounts['Behind'] }}</p>
            <p class="wr-kpi__note">Below daily target</p>
        </div>
        <div class="wr-kpi wr-kpi--gray">
            <p class="wr-kpi__label">Not Recorded</p>
            <p class="wr-kpi__value">{{ $hafDailyCounts['Not Recorded'] }}</p>
            <p class="wr-kpi__note">No tasmi' record</p>
        </div>
    </div>

    {{-- ── Lecturer breakdown table ── --}}
    <div class="wr-card">
        <div class="wr-card__head">
            <div>
                <h2 class="wr-card__title">Lecturer Breakdown — {{ $date->format('d F Y') }}</h2>
                <p class="wr-card__subtitle">Hafazan progress per lecturer, sorted by batch.</p>
            </div>
            <span class="wr-card__chip">{{ $lecturerSummaries->count() }} lecturers</span>
        </div>

        <div class="wr-table-wrap">
            @if ($lecturerSummaries->isEmpty())
                <p class="wr-table__empty">No data for this date.</p>
            @else
            @php
                $sorted = $lecturerSummaries->sortBy(function ($row) {
                    $first = trim(explode(',', $row->batch_name)[0]);
                    preg_match('/(\d+)/', $first, $m);
                    $num = isset($m[1]) ? (int) $m[1] : 9999;
                    return sprintf('%05d_%s', $num, strtolower($row->lecturer_name));
                })->values();
                $currentBatch = null;
                $rowNum = 0;
            @endphp
            <table class="wr-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Lecturer</th>
                        <th>Batch</th>
                        <th style="text-align:center;">Total Students</th>
                        <th style="color:#16a34a;">Completed</th>
                        <th style="color:#2563eb;">On Track</th>
                        <th style="color:#dc2626;">Behind</th>
                        <th style="color:#94a3b8; text-align:center;">Not Recorded</th>
                        <th>Progress</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sorted as $row)
                    @php
                        $firstBatch = trim(explode(',', $row->batch_name)[0]);
                        $batchLabel = ($firstBatch !== '' && $firstBatch !== '—') ? $firstBatch : 'Unassigned';
                    @endphp
                    @if ($batchLabel !== $currentBatch)
                    @php $currentBatch = $batchLabel; @endphp
                    <tr class="wr-batch-header">
                        <td colspan="10">{{ $batchLabel }}</td>
                    </tr>
                    @endif
                    @php $rowNum++; $pct = $row->coverage_pct; @endphp
                    <tr>
                        <td style="color:#94a3b8;font-size:.8rem;font-weight:700;">{{ $rowNum }}</td>
                        <td class="wr-table__name">{{ $row->lecturer_name }}</td>
                        <td style="font-size:0.82rem;color:#64748b;">{{ $row->batch_name }}</td>
                        <td style="text-align:center;" class="wr-table__num">{{ $row->total }}</td>
                        <td><span class="wr-count--green">{{ $row->completed }}</span></td>
                        <td><span class="wr-count--blue">{{ $row->on_track }}</span></td>
                        <td><span class="{{ $row->behind > 0 ? 'wr-count--red' : 'wr-count--gray' }}">{{ $row->behind }}</span></td>
                        <td style="text-align:center;"><span class="wr-count--gray">{{ $row->not_recorded }}</span></td>
                        <td>
                            <div class="wr-progress">
                                <div class="wr-progress__bar">
                                    <div class="wr-progress__fill" style="width:{{ $pct }}%"></div>
                                </div>
                                <span class="wr-progress__pct">{{ $pct }}%</span>
                            </div>
                        </td>
                        <td>
                            @if ($row->lecturer_id)
                            <a href="{{ route('admin.reports.hafazan.daily.lecturer', $row->lecturer_id) }}?date={{ $date->format('Y-m-d') }}"
                               class="wr-btn wr-btn--detail">
                                View Details
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

        <div class="wr-legend">
            <span class="wr-legend__label">Status key:</span>
            <span class="wr-badge wr-badge--green">Completed</span>
            <span class="wr-badge wr-badge--blue">On Track</span>
            <span class="wr-badge wr-badge--red">Behind</span>
            <span class="wr-badge wr-badge--gray">Not Recorded</span>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const HAF_DAILY_DATA   = @json(array_values($hafDailyCounts));
const HAF_DAILY_TOTAL  = {{ array_sum($hafDailyCounts) }};
const HAF_DAILY_LABELS = ['Completed', 'On Track', 'Behind', 'Not Recorded'];
const HAF_DONUT_COLORS = ['#16a34a', '#2563eb', '#dc2626', '#94a3b8'];

(function initHafDailyDonut() {
    const el = document.getElementById('hafDailyDonut');
    if (!el || HAF_DAILY_TOTAL === 0) return;

    const dominantIdx   = HAF_DAILY_DATA.indexOf(Math.max(...HAF_DAILY_DATA));
    const dominantLabel = HAF_DAILY_LABELS[dominantIdx];
    const dominantPct   = Math.round(HAF_DAILY_DATA[dominantIdx] / HAF_DAILY_TOTAL * 100);

    const centerPlugin = {
        id: 'hafDailyCenterText',
        beforeDraw(chart) {
            const { ctx, chartArea } = chart;
            if (!chartArea) return;
            const cx = (chartArea.left + chartArea.right) / 2;
            const cy = (chartArea.top  + chartArea.bottom) / 2;
            ctx.save();
            ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            ctx.font = 'bold 30px Inter, ui-sans-serif, sans-serif';
            ctx.fillStyle = '#1e293b';
            ctx.fillText(dominantPct + '%', cx, cy - 12);
            ctx.font = '600 11px Inter, ui-sans-serif, sans-serif';
            ctx.fillStyle = '#64748b';
            ctx.fillText(dominantLabel, cx, cy + 10);
            ctx.restore();
        },
    };

    new Chart(el, {
        type: 'doughnut',
        data: {
            labels: HAF_DAILY_LABELS,
            datasets: [{
                data: HAF_DAILY_DATA,
                backgroundColor: HAF_DONUT_COLORS,
                borderWidth: 2, borderColor: '#fff', hoverOffset: 6,
            }],
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '66%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const p = total > 0 ? Math.round(ctx.parsed / total * 100) : 0;
                            return ` ${ctx.label}: ${ctx.parsed} student${ctx.parsed === 1 ? '' : 's'} (${p}%)`;
                        },
                    },
                },
            },
        },
        plugins: [centerPlugin],
    });
})();
</script>
@endsection
