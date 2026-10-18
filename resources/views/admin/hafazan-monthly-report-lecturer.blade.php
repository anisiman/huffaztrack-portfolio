@extends('layouts/admin')

@section('title', $lecturerName . ' — Monthly Hafazan')

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

/* ── Month info banner ────────────────────── */
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
    cursor:pointer; user-select:none;
    transition:box-shadow .15s, border-color .15s, background .15s, transform .1s;
}
.wr-kpi:hover:not(.wr-kpi--active) { box-shadow:0 12px 28px rgba(15,23,42,.1); transform:translateY(-1px); }
.wr-kpi__label { font-size:0.78rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em; }
.wr-kpi__value { font-size:1.75rem; font-weight:800; letter-spacing:-0.04em; }
.wr-kpi__note  { font-size:0.78rem; color:#94a3b8; }
.wr-kpi--green .wr-kpi__value { color:#16a34a; }
.wr-kpi--blue  .wr-kpi__value { color:#2563eb; }
.wr-kpi--red   .wr-kpi__value { color:#dc2626; }
.wr-kpi--gray  .wr-kpi__value { color:#64748b; }
.wr-kpi--green.wr-kpi--active { border-color:#16a34a; background:#f0fdf4; box-shadow:0 0 0 4px rgba(22,163,74,.12),0 8px 22px rgba(15,23,42,.06); }
.wr-kpi--blue.wr-kpi--active  { border-color:#2563eb; background:#eff6ff; box-shadow:0 0 0 4px rgba(37,99,235,.12),0 8px 22px rgba(15,23,42,.06); }
.wr-kpi--red.wr-kpi--active   { border-color:#dc2626; background:#fef2f2; box-shadow:0 0 0 4px rgba(220,38,38,.12),0 8px 22px rgba(15,23,42,.06); }
.wr-kpi--gray.wr-kpi--active  { border-color:#64748b; background:#f1f5f9; box-shadow:0 0 0 4px rgba(100,116,139,.12),0 8px 22px rgba(15,23,42,.06); }

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
.wr-table { width:100%; border-collapse:collapse; min-width:580px; }
.wr-table th {
    padding:0.75rem 1rem; font-size:0.72rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.05em; color:#94a3b8;
    background:#fcfdff; border-bottom:1px solid #eef2f7; white-space:nowrap; text-align:left;
}
.wr-table td { padding:0.85rem 1rem; font-size:0.875rem; color:#475569; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.wr-table tbody tr:last-child td { border-bottom:0; }
.wr-table tbody tr:hover { background:#fafbff; }
.wr-table__name  { font-weight:600; color:#1e293b; }
.wr-table__sub   { display:block; font-size:0.75rem; color:#94a3b8; margin-top:1px; }
.wr-table__num   { font-weight:700; color:#334155; }
.wr-table__empty { text-align:center; color:#94a3b8; padding:2.5rem 1rem; font-size:0.88rem; }

/* ── Status badges ────────────────────────── */
.wr-badge {
    display:inline-flex; align-items:center;
    font-size:0.72rem; font-weight:700;
    padding:3px 10px; border-radius:20px; white-space:nowrap;
}
.wr-badge--green  { background:#dcfce7; color:#15803d; }
.wr-badge--blue   { background:#dbeafe; color:#1d4ed8; }
.wr-badge--yellow { background:#fef9c3; color:#854d0e; }
.wr-badge--orange { background:#ffedd5; color:#c2410c; }
.wr-badge--red    { background:#fee2e2; color:#dc2626; }
.wr-badge--gray   { background:#f1f5f9; color:#64748b; }

/* ── Legend ───────────────────────────────── */
.wr-legend {
    display:flex; flex-wrap:wrap; gap:0.6rem; align-items:center;
    padding:0.8rem 1.4rem; border-top:1px solid #f1f5f9;
    font-size:0.75rem; color:#64748b;
}
.wr-legend__label { font-weight:600; color:#94a3b8; }
</style>
@endpush

@section('content')
<div class="wr-page">

    {{-- ── Back ── --}}
    @include('partials.back-button', ['href' => route('admin.reports.hafazan.monthly'), 'label' => 'Monthly Hafazan Report'])

    {{-- ── Header ── --}}
    <div class="wr-header">
        <h1 class="wr-title">{{ $lecturerName }}</h1>
        @isset($lecturerPhone)<p class="wr-subtitle">{{ $lecturerPhone }}</p>@endisset
        @if ($rows->isNotEmpty())
        <p class="wr-subtitle">
            {{ $rows->pluck('batch_name')->filter(fn ($b) => $b !== '—')->unique()->sort()->implode(', ') }}
            &nbsp;&mdash;&nbsp; {{ $monthStart->format('F Y') }}
        </p>
        @endif
    </div>

    {{-- ── Filters ── --}}
    <form class="wr-filters" method="GET" action="{{ route('admin.reports.hafazan.monthly.lecturer', $lecturerId) }}">
        <div class="wr-filter-group">
            <span class="wr-filter-label">Month</span>
            <select class="wr-filter-select" name="month" onchange="this.form.submit()">
                @foreach ($months as $m)
                    <option value="{{ $m['value'] }}" {{ $monthStr === $m['value'] ? 'selected' : '' }}>
                        {{ $m['label'] }}
                    </option>
                @endforeach
            </select>
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
        </div>
    </form>

    {{-- ── Month info banner ── --}}
    <div class="wr-info">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
        </svg>
        <span>
            Hafazan summary for <strong>{{ $monthStart->format('F Y') }}</strong>
            &nbsp;&mdash;&nbsp; {{ $monthStart->format('d M') }} to {{ $monthEnd->format('d M Y') }}
        </span>
    </div>

    {{-- ── KPI cards (clickable — same as lecturer monthly) ── --}}
    <div class="wr-kpis" id="wr-kpis">
        <div class="wr-kpi wr-kpi--green" data-filter="Completed" title="Click to filter by Completed">
            <p class="wr-kpi__label">Completed</p>
            <p class="wr-kpi__value">{{ $completedCount }}</p>
            <p class="wr-kpi__note">Reached expected muqarrar</p>
        </div>
        <div class="wr-kpi wr-kpi--blue" data-filter="On Track" title="Click to filter by On Track">
            <p class="wr-kpi__label">On Track</p>
            <p class="wr-kpi__value">{{ $onTrackCount }}</p>
            <p class="wr-kpi__note">Following muqarrar</p>
        </div>
        <div class="wr-kpi wr-kpi--red" data-filter="Behind" title="Click to filter by Behind">
            <p class="wr-kpi__label">Behind</p>
            <p class="wr-kpi__value">{{ $behindCount }}</p>
            <p class="wr-kpi__note">Behind muqarrar</p>
        </div>
        <div class="wr-kpi wr-kpi--gray" data-filter="Not Recorded" title="Click to filter by Not Recorded">
            <p class="wr-kpi__label">Not Recorded</p>
            <p class="wr-kpi__value">{{ $notRecordedCount }}</p>
            <p class="wr-kpi__note">No tasmi' this month</p>
        </div>
    </div>

    {{-- ── Student progress table ── --}}
    <div class="wr-card">
        <div class="wr-card__head">
            <div>
                <h2 class="wr-card__title">Student Progress — {{ $monthStart->format('F Y') }}</h2>
                <p class="wr-card__subtitle">Latest hafazan page and pages completed during the month.</p>
            </div>
            <span class="wr-card__chip" id="kpi-count">{{ $rows->count() }} students</span>
        </div>

        <div class="wr-table-wrap">
            <table class="wr-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Student Name</th>
                        <th>Halaqah</th>
                        <th>Latest Page</th>
                        <th>Pages Tasmi'</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="wr-tbody">
                    @forelse ($rows as $i => $row)
                    <tr data-status="{{ $row->status }}">
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <span class="wr-table__name">{{ $row->student_name }}</span>
                            <span class="wr-table__sub">{{ $row->student_id }}</span>
                        </td>
                        <td style="font-size:0.82rem;color:#64748b;">{{ $row->batch_name }}</td>
                        <td class="wr-table__num">{{ $row->latest_page !== null ? $row->latest_page : '—' }}</td>
                        <td class="wr-table__num">{{ $row->pages_this_month !== null ? $row->pages_this_month : '—' }}</td>
                        <td><span class="wr-badge wr-badge--{{ $row->color }}">{{ $row->status }}</span></td>
                    </tr>
                    @empty
                    <tr id="wr-server-empty">
                        <td colspan="6" class="wr-table__empty">No student data found for the selected month.</td>
                    </tr>
                    @endforelse
                    <tr id="wr-filter-empty" style="display:none;">
                        <td colspan="6" class="wr-table__empty">No students match this filter. Click the card again to clear.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="wr-legend">
            <span class="wr-legend__label">Status key:</span>
            <span class="wr-badge wr-badge--green">Completed</span>
            <span class="wr-badge wr-badge--blue">On Track</span>
            <span class="wr-badge wr-badge--yellow">Behind 1–5 pg</span>
            <span class="wr-badge wr-badge--orange">Behind 6–10 pg</span>
            <span class="wr-badge wr-badge--red">Behind 11+ pg</span>
            <span class="wr-badge wr-badge--gray">Not Recorded</span>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
(function () {
    const kpis        = document.querySelectorAll('#wr-kpis .wr-kpi[data-filter]');
    const chip        = document.getElementById('kpi-count');
    const filterEmpty = document.getElementById('wr-filter-empty');
    let   active      = null;

    function applyFilter(filter) {
        const rows = document.querySelectorAll('#wr-tbody tr[data-status]');
        let visible = 0;
        rows.forEach(function (row) {
            const match = !filter || row.dataset.status === filter;
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        chip.textContent = visible + ' student' + (visible !== 1 ? 's' : '');
        if (filterEmpty) filterEmpty.style.display = (visible === 0 && rows.length > 0) ? '' : 'none';
    }

    kpis.forEach(function (kpi) {
        kpi.addEventListener('click', function () {
            const filter = kpi.dataset.filter;
            if (active === filter) {
                active = null;
                kpis.forEach(function (k) { k.classList.remove('wr-kpi--active'); });
            } else {
                active = filter;
                kpis.forEach(function (k) { k.classList.remove('wr-kpi--active'); });
                kpi.classList.add('wr-kpi--active');
            }
            applyFilter(active);
        });
    });
}());
</script>
@endsection
