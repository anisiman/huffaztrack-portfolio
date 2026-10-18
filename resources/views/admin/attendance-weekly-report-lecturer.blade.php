@extends('layouts/admin')

@section('title', $lecturerName . ' — Weekly Attendance')

@push('styles')
<style>
.wr-page    { display:flex; flex-direction:column; gap:1.5rem; }
.wr-header  { display:flex; flex-direction:column; gap:0.25rem; }
.wr-title   { margin:0; font-size:2.25rem; font-weight:700; letter-spacing:-0.02em; color:#1e293b; }
.wr-subtitle { margin:0; font-size:0.92rem; color:#94a3b8; }

.wr-breadcrumb { display:flex; align-items:center; gap:0.5rem; font-size:0.82rem; color:#94a3b8; }
.wr-breadcrumb a { color:#7c3aed; text-decoration:none; font-weight:600; }
.wr-breadcrumb a:hover { text-decoration:underline; }

.wr-toolbar {
    display:flex; flex-wrap:wrap; gap:0.65rem; align-items:flex-end;
    background:#fff; border:1px solid #e2e8f0;
    border-radius:1.1rem; padding:0.9rem 1.1rem;
    box-shadow:0 6px 18px rgba(15,23,42,.04);
}
.wr-filter-group { display:flex; flex-direction:column; gap:0.3rem; }
.wr-filter-label { font-size:0.72rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em; }
.wr-field-input {
    min-height:2.2rem; padding:0 0.85rem;
    border:1px solid #e2e8f0; border-radius:0.65rem;
    background:#f8fafc; color:#334155;
    font-size:0.875rem; cursor:pointer; outline:none; min-width:180px;
}
.wr-field-input:focus { border-color:#7c3aed; background:#fff; }
.wr-btn {
    display:inline-flex; align-items:center; justify-content:center; gap:0.4rem;
    min-height:2.2rem; padding:0 1rem;
    border:0; border-radius:0.65rem;
    font-size:0.85rem; font-weight:700; cursor:pointer; text-decoration:none;
}
.wr-btn--primary { background:linear-gradient(90deg,#5b21b6,#7c3aed); color:#fff; box-shadow:0 8px 20px rgba(124,58,237,.2); }
.wr-btn--ghost   { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }

/* ── KPI cards ── */
.wr-kpis { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; }
@media(max-width:860px){ .wr-kpis { grid-template-columns:repeat(2,1fr); } }
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
.wr-kpi--green  .wr-kpi__value { color:#16a34a; }
.wr-kpi--red    .wr-kpi__value { color:#dc2626; }
.wr-kpi--blue   .wr-kpi__value { color:#2563eb; }
.wr-kpi--purple .wr-kpi__value { color:#7c3aed; }
.wr-kpi__value-row { display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; }
.wr-kpi__badge { display:inline-flex; align-items:center; font-size:0.72rem; font-weight:700; padding:0.18rem 0.6rem; border-radius:20px; white-space:nowrap; line-height:1.4; }
.wr-kpi__badge--green  { background:#dcfce7; color:#15803d; }
.wr-kpi__badge--red    { background:#fee2e2; color:#b91c1c; }
.wr-kpi__badge--blue   { background:#dbeafe; color:#1d4ed8; }
.wr-kpi__badge--purple { background:#ede9fe; color:#6d28d9; }
.wr-kpi--green.wr-kpi--active  { border-color:#16a34a; background:#f0fdf4; box-shadow:0 0 0 4px rgba(22,163,74,.12); }
.wr-kpi--red.wr-kpi--active    { border-color:#dc2626; background:#fef2f2; box-shadow:0 0 0 4px rgba(220,38,38,.12); }
.wr-kpi--blue.wr-kpi--active   { border-color:#2563eb; background:#eff6ff; box-shadow:0 0 0 4px rgba(37,99,235,.12); }
.wr-kpi--purple.wr-kpi--active { border-color:#7c3aed; background:#f5f3ff; box-shadow:0 0 0 4px rgba(124,58,237,.12); }

/* ── Info banner ── */
.wr-info {
    display:flex; align-items:center; gap:0.75rem;
    background:#f5f3ff; border:1px solid #ddd6fe;
    border-radius:0.9rem; padding:0.8rem 1.1rem;
    font-size:0.875rem; color:#5b21b6;
}
.wr-info svg { width:1.1rem; height:1.1rem; flex-shrink:0; }

/* ── Inline quick filters ── */
.wr-inline-filters {
    display:flex; flex-wrap:wrap; gap:0.55rem; align-items:center;
    background:#fff; border:1px solid #e2e8f0;
    border-radius:1.1rem; padding:0.75rem 1.1rem;
    box-shadow:0 4px 14px rgba(15,23,42,.03);
}
.wr-inline-label { font-size:0.72rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em; margin-right:0.15rem; white-space:nowrap; }
.wr-pill-group { display:flex; gap:0.4rem; flex-wrap:wrap; }
.wr-pill {
    display:inline-flex; align-items:center; gap:0.3rem;
    padding:0.22rem 0.75rem; border-radius:20px;
    font-size:0.78rem; font-weight:700; cursor:pointer; user-select:none;
    border:1.5px solid #e2e8f0; background:#f8fafc; color:#64748b;
    transition:background .12s, border-color .12s, color .12s;
}
.wr-pill:hover:not(.wr-pill--active) { border-color:#c4b5fd; color:#7c3aed; background:#f5f3ff; }
.wr-pill--active { border-color:#7c3aed; background:#ede9fe; color:#5b21b6; }
.wr-inline-sep { width:1px; height:1.5rem; background:#e2e8f0; margin:0 0.3rem; align-self:center; }
.wr-wl-select {
    min-height:2rem; padding:0 0.7rem;
    border:1.5px solid #e2e8f0; border-radius:0.65rem;
    background:#f8fafc; color:#334155;
    font-size:0.8rem; cursor:pointer; outline:none;
}
.wr-wl-select:focus { border-color:#7c3aed; background:#fff; }

/* ── Table card ── */
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
.wr-table-wrap { overflow-x:auto; }
.wr-table { width:100%; border-collapse:collapse; min-width:720px; }
.wr-table th {
    padding:0.75rem 1rem; font-size:0.72rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.05em; color:#94a3b8;
    background:#fcfdff; border-bottom:1px solid #eef2f7; text-align:left; white-space:nowrap;
}
.wr-table td { padding:0.85rem 1rem; font-size:0.875rem; color:#475569; border-bottom:1px solid #f1f5f9; }
.wr-table tbody tr:last-child td { border-bottom:0; }
.wr-table tbody tr:hover { background:#fafbff; }
.wr-table__name  { font-weight:600; color:#1e293b; }
.wr-table__sub   { display:block; font-size:0.75rem; color:#94a3b8; margin-top:1px; }
.wr-table__num   { font-weight:700; color:#334155; }
.wr-table__empty { text-align:center; color:#94a3b8; padding:2.5rem 1rem; font-size:0.88rem; }

/* ── Rate bar ── */
.rate-bar-wrap { display:flex; align-items:center; gap:0.6rem; }
.rate-bar { flex:1; height:6px; border-radius:99px; background:#f1f5f9; overflow:hidden; min-width:50px; }
.rate-bar__fill { height:100%; border-radius:99px; }
.rate-bar__fill--high { background:#16a34a; }
.rate-bar__fill--mid  { background:#f59e0b; }
.rate-bar__fill--low  { background:#dc2626; }
.rate-label { font-size:0.8rem; font-weight:700; min-width:2.8rem; text-align:right; }
.rate-label--high { color:#16a34a; }
.rate-label--mid  { color:#f59e0b; }
.rate-label--low  { color:#dc2626; }
.rate-label--none { color:#94a3b8; font-weight:400; }

/* ── Warning level badges ── */
.wl-badge { display:inline-flex; align-items:center; font-size:0.72rem; font-weight:700; padding:3px 9px; border-radius:20px; white-space:nowrap; }
.wl-badge--none { background:#f1f5f9; color:#64748b; }
.wl-badge--wl1  { background:#fef9c3; color:#854d0e; }
.wl-badge--wl2  { background:#ffedd5; color:#c2410c; }
.wl-badge--wl3  { background:#fee2e2; color:#dc2626; }

/* ── View Details button + Modal ── */
.att-details-btn {
    display:inline-flex; align-items:center; gap:0.3rem;
    padding:0.22rem 0.65rem;
    background:#f5f3ff; color:#6d28d9;
    border:1px solid #ddd6fe; border-radius:0.45rem;
    font-size:0.75rem; font-weight:600; cursor:pointer;
    transition:background .15s; white-space:nowrap;
}
.att-details-btn:hover { background:#ede9fe; }
.att-modal-overlay { position:fixed; inset:0; z-index:9999; background:rgba(15,23,42,.45); display:flex; align-items:center; justify-content:center; padding:1rem; }
.att-modal { background:#fff; border-radius:1.1rem; width:100%; max-width:700px; max-height:85vh; display:flex; flex-direction:column; box-shadow:0 20px 50px rgba(15,23,42,.22); }
.att-modal__head { display:flex; align-items:flex-start; justify-content:space-between; padding:1.1rem 1.3rem 0.85rem; border-bottom:1px solid #f1f5f9; flex-shrink:0; }
.att-modal__title { margin:0; font-size:1rem; font-weight:700; color:#1e293b; }
.att-modal__sub   { margin:.2rem 0 0; font-size:0.78rem; color:#94a3b8; }
.att-modal__close { background:none; border:none; cursor:pointer; font-size:1.4rem; line-height:1; color:#94a3b8; padding:0; flex-shrink:0; }
.att-modal__close:hover { color:#475569; }
.att-modal__body { overflow-y:auto; padding:1rem 1.3rem 1.3rem; }
.att-modal__empty { text-align:center; color:#94a3b8; font-size:0.875rem; padding:1.5rem 0; }
.att-modal__table { width:100%; border-collapse:collapse; min-width:460px; }
.att-modal__table th { padding:0.5rem 0.75rem; font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#94a3b8; background:#fcfdff; border-bottom:1px solid #eef2f7; text-align:left; white-space:nowrap; }
.att-modal__table td { padding:0.65rem 0.75rem; font-size:0.845rem; color:#475569; border-bottom:1px solid #f8fafc; vertical-align:top; }
.att-modal__table tbody tr:last-child td { border-bottom:0; }
.att-badge { display:inline-flex; align-items:center; font-size:0.72rem; font-weight:700; padding:0.18rem 0.6rem; border-radius:99px; white-space:nowrap; }
.att-badge--absent  { background:#fee2e2; color:#dc2626; }
.att-badge--excused { background:#dbeafe; color:#1d4ed8; }
.att-modal__doc-link { font-size:0.78rem; color:#0369a1; font-weight:600; text-decoration:none; }
.att-modal__doc-link:hover { text-decoration:underline; }
</style>
@endpush

@section('content')
<div class="wr-page">

    {{-- Back --}}
    @include('partials.back-button', ['href' => route('admin.reports.attendance.weekly').'?week_start='.$weekStart->format('Y-m-d'), 'label' => 'Attendance Weekly Report'])

    {{-- Header --}}
    <div class="wr-header">
        <h1 class="wr-title">{{ $lecturerName }}</h1>
        @isset($lecturerPhone)<p class="wr-subtitle">{{ $lecturerPhone }}</p>@endisset
        @if ($rows->isNotEmpty())
        <p class="wr-subtitle">
            {{ $rows->pluck('batch_name')->filter(fn ($b) => $b !== '—')->unique()->sort()->implode(', ') }}
            &nbsp;&mdash;&nbsp; {{ $weekLabel }}
        </p>
        @endif
    </div>

    {{-- Toolbar --}}
    <form class="wr-toolbar" method="GET" action="{{ route('admin.reports.attendance.weekly.lecturer', $lecturerId) }}">
        <div class="wr-filter-group">
            <span class="wr-filter-label">Any date in the week</span>
            <input class="wr-field-input" type="date" name="week_start"
                value="{{ $weekStart->format('Y-m-d') }}"
                max="{{ $today->format('Y-m-d') }}"
                onchange="this.form.submit()">
        </div>
        <div style="display:flex;gap:0.5rem;padding-top:1.3rem;">
            <button type="submit" class="wr-btn wr-btn--primary">Apply</button>
        </div>
    </form>

    {{-- Week banner --}}
    <div class="wr-info">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
        </svg>
        <span><strong>{{ $weekLabel }}</strong></span>
    </div>

    {{-- KPI cards (mirrors lecturer weekly attendance) -- }}
    <div class="wr-kpis" id="wr-kpis">
        <div class="wr-kpi wr-kpi--green" data-kpi="all" title="Click to show all students">
            <p class="wr-kpi__label">Overall Present Rate</p>
            <div class="wr-kpi__value-row">
                <span class="wr-kpi__value">{{ $overallPresentRate !== null ? $overallPresentRate . '%' : '—' }}</span>
                <span class="wr-kpi__badge wr-kpi__badge--green">{{ $totalStudents }} Students</span>
            </div>
            <p class="wr-kpi__note">Students present this week</p>
        </div>
        <div class="wr-kpi wr-kpi--red" data-kpi="th" title="Click to filter students with TH">
            <p class="wr-kpi__label">TH Rate (Absent)</p>
            <div class="wr-kpi__value-row">
                <span class="wr-kpi__value">{{ $thRate !== null ? $thRate . '%' : '—' }}</span>
                <span class="wr-kpi__badge wr-kpi__badge--red">{{ $thStudentCount }} {{ Str::plural('Student', $thStudentCount) }}</span>
            </div>
            <p class="wr-kpi__note">Had TH this week</p>
        </div>
        <div class="wr-kpi wr-kpi--blue" data-kpi="ths" title="Click to filter students with THS">
            <p class="wr-kpi__label">THS Rate (Excused)</p>
            <div class="wr-kpi__value-row">
                <span class="wr-kpi__value">{{ $thsRate !== null ? $thsRate . '%' : '—' }}</span>
                <span class="wr-kpi__badge wr-kpi__badge--blue">{{ $thsStudentCount }} {{ Str::plural('Student', $thsStudentCount) }}</span>
            </div>
            <p class="wr-kpi__note">Had approved excuse this week</p>
        </div>
        <div class="wr-kpi wr-kpi--purple" data-kpi="perfect" title="Click to filter students with perfect attendance">
            <p class="wr-kpi__label">Perfect Attendance</p>
            <div class="wr-kpi__value-row">
                <span class="wr-kpi__value">{{ $perfectCount }}</span>
                <span class="wr-kpi__badge wr-kpi__badge--purple">of {{ $totalStudents }}</span>
            </div>
            <p class="wr-kpi__note">Attended all sessions this week</p>
        </div>
    </div>

    {{-- Quick filters --}}
    <div class="wr-inline-filters">
        <span class="wr-inline-label">Filter:</span>
        <div class="wr-pill-group" id="att-pills">
            <span class="wr-pill wr-pill--active" data-att="all">All Students</span>
            <span class="wr-pill" data-att="th">TH Only</span>
            <span class="wr-pill" data-att="ths">THS Only</span>
            <span class="wr-pill" data-att="perfect">Perfect Attendance</span>
        </div>
        <div class="wr-inline-sep"></div>
        <span class="wr-inline-label">Warning:</span>
        <select class="wr-wl-select" id="wl-select">
            <option value="">All Levels</option>
            <option value="0">No Warning</option>
            <option value="1">WL1</option>
            <option value="2">WL2</option>
            <option value="3">WL3</option>
        </select>
    </div>

    {{-- Student table --}}
    <div class="wr-card" id="wr-table-card">
        <div class="wr-card__head">
            <div>
                <h2 class="wr-card__title">Weekly Attendance — {{ $weekLabel }}</h2>
                <p class="wr-card__subtitle">Attendance rate for each student during this week.</p>
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
                        <th>Warning Level</th>
                        <th>Present</th>
                        <th>Absent (TH)</th>
                        <th>Excused (THS)</th>
                        <th>Weekly Rate</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="wr-tbody">
                    @forelse ($rows as $i => $row)
                    @php
                        $rc      = $row->rate === null ? 'none' : ($row->rate >= 80 ? 'high' : ($row->rate >= 60 ? 'mid' : 'low'));
                        $wlLabel = match($row->warning_level) { 1 => 'WL1', 2 => 'WL2', 3 => 'WL3', default => 'No Warning' };
                        $wlClass = match($row->warning_level) { 1 => 'wl-badge--wl1', 2 => 'wl-badge--wl2', 3 => 'wl-badge--wl3', default => 'wl-badge--none' };
                    @endphp
                    <tr
                        data-wl="{{ $row->warning_level }}"
                        data-has-th="{{ $row->has_th ? '1' : '0' }}"
                        data-has-ths="{{ $row->has_ths ? '1' : '0' }}"
                        data-is-perfect="{{ $row->is_perfect ? '1' : '0' }}"
                    >
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <span class="wr-table__name">{{ $row->student_name }}</span>
                            <span class="wr-table__sub">{{ $row->student_id }}</span>
                        </td>
                        <td>{{ $row->batch_name }}</td>
                        <td><span class="wl-badge {{ $wlClass }}">{{ $wlLabel }}</span></td>
                        <td>
                            <span class="wr-table__num" style="color:#16a34a;">{{ $row->present }}</span>
                            <span style="color:#94a3b8;font-size:0.8rem;font-weight:500;"> / {{ $row->total_days }}</span>
                        </td>
                        <td><span class="wr-table__num" style="color:#dc2626;">{{ $row->absent }}</span></td>
                        <td><span class="wr-table__num" style="color:#2563eb;">{{ $row->excused }}</span></td>
                        <td>
                            @if ($row->rate !== null)
                            <div class="rate-bar-wrap">
                                <div class="rate-bar">
                                    <div class="rate-bar__fill rate-bar__fill--{{ $rc }}" style="width:{{ $row->rate }}%;"></div>
                                </div>
                                <span class="rate-label rate-label--{{ $rc }}">{{ $row->rate }}%</span>
                            </div>
                            @else
                                <span class="rate-label rate-label--none">—</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="att-details-btn"
                                    onclick="openAttDetails('{{ $row->student_id }}', '{{ addslashes($row->student_name) }}')">
                                View Details
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr id="wr-server-empty">
                        <td colspan="9" class="wr-table__empty">No students found for this lecturer.</td>
                    </tr>
                    @endforelse
                    <tr id="wr-filter-empty" style="display:none;">
                        <td colspan="9" class="wr-table__empty">No students match this filter. Try a different filter.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Attendance History Modal --}}
<div id="attDetailsModal" class="att-modal-overlay" style="display:none;"
     onclick="if(event.target===this)closeAttDetails()">
    <div class="att-modal">
        <div class="att-modal__head">
            <div>
                <h3 class="att-modal__title" id="attModalTitle"></h3>
                <p class="att-modal__sub" id="attModalSub"></p>
            </div>
            <button type="button" class="att-modal__close" onclick="closeAttDetails()" aria-label="Close">&times;</button>
        </div>
        <div class="att-modal__body" id="attModalBody"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const __attDetails  = @json($attendanceDetails);
const __periodLabel = '{{ $weekLabel }}';

function openAttDetails(studentId, studentName) {
    document.getElementById('attModalTitle').textContent = studentName;
    document.getElementById('attModalSub').textContent   = __periodLabel;
    const records = __attDetails[studentId] || [];
    const body    = document.getElementById('attModalBody');
    if (!records.length) {
        body.innerHTML = '<p class="att-modal__empty">No TH / THS records found for this period.</p>';
    } else {
        const rows = records.map(r => {
            const docCell  = r.document_url
                ? `<a href="${r.document_url}" target="_blank" class="att-modal__doc-link">View</a>`
                : '<span style="color:#cbd5e1;">—</span>';
            const badgeCls = r.status === 'absent' ? 'att-badge--absent' : 'att-badge--excused';
            return `<tr>
                <td style="white-space:nowrap;">${r.date}</td>
                <td><span class="att-badge ${badgeCls}">${r.label}</span></td>
                <td>${r.reason  ?? '<span style="color:#cbd5e1;">—</span>'}</td>
                <td style="font-size:0.8rem;color:#64748b;">${r.remarks ?? '<span style="color:#cbd5e1;">—</span>'}</td>
                <td>${docCell}</td>
            </tr>`;
        }).join('');
        body.innerHTML = `<div style="overflow-x:auto;"><table class="att-modal__table">
            <thead><tr><th>Date</th><th>Status</th><th>Reason</th><th>Remarks</th><th>Document</th></tr></thead>
            <tbody>${rows}</tbody></table></div>`;
    }
    document.getElementById('attDetailsModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeAttDetails() {
    document.getElementById('attDetailsModal').style.display = 'none';
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAttDetails(); });

/* ── KPI card + pill + WL filter (identical to lecturer weekly) ── */
(function () {
    const kpis      = document.querySelectorAll('#wr-kpis .wr-kpi[data-kpi]');
    const pills     = document.querySelectorAll('#att-pills .wr-pill[data-att]');
    const wlSelect  = document.getElementById('wl-select');
    const chip      = document.getElementById('kpi-count');
    const filterRow = document.getElementById('wr-filter-empty');
    let activeAtt = 'all';
    let activeWL  = '';

    function applyFilter() {
        const rows = document.querySelectorAll('#wr-tbody tr[data-wl]');
        let visible = 0;
        rows.forEach(function (row) {
            let showAtt = true;
            if      (activeAtt === 'th')      showAtt = row.dataset.hasTh     === '1';
            else if (activeAtt === 'ths')     showAtt = row.dataset.hasThs    === '1';
            else if (activeAtt === 'perfect') showAtt = row.dataset.isPerfect === '1';
            const showWL = activeWL === '' || row.dataset.wl === activeWL;
            const show = showAtt && showWL;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        chip.textContent = visible + ' student' + (visible !== 1 ? 's' : '');
        if (filterRow) filterRow.style.display = (visible === 0 && rows.length > 0) ? '' : 'none';
    }

    function setAttFilter(value) {
        activeAtt = value;
        pills.forEach(function (p) { p.classList.toggle('wr-pill--active', p.dataset.att === value); });
        kpis.forEach(function (k) { k.classList.toggle('wr-kpi--active', k.dataset.kpi === value); });
        applyFilter();
        if (value !== 'all') document.getElementById('wr-table-card')?.scrollIntoView({ behavior:'smooth', block:'start' });
    }

    kpis.forEach(function (kpi) {
        kpi.addEventListener('click', function () {
            const next = kpi.dataset.kpi === activeAtt && kpi.dataset.kpi !== 'all' ? 'all' : kpi.dataset.kpi;
            setAttFilter(next);
        });
    });
    pills.forEach(function (pill) { pill.addEventListener('click', function () { setAttFilter(pill.dataset.att); }); });
    wlSelect.addEventListener('change', function () { activeWL = wlSelect.value; applyFilter(); });
}());
</script>
@endpush
