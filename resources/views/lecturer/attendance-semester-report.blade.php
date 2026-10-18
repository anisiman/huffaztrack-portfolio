@extends('layouts/lecturer')

@section('title', 'Semester Attendance Report')

@push('styles')
<style>
.wr-page    { display:flex; flex-direction:column; gap:1.5rem; }
.wr-header  { display:flex; flex-direction:column; gap:0.25rem; }
.wr-title   { margin:0; font-size:2.25rem; font-weight:700; letter-spacing:-0.02em; color:#1e293b; }
.wr-subtitle{ margin:0; font-size:0.92rem; color:#94a3b8; }

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
    font-size:0.85rem; cursor:pointer; outline:none; min-width:220px;
}
.wr-filter-select:focus { border-color:#d97706; background:#fff; }
.wr-filter-actions { display:flex; gap:0.5rem; align-items:flex-end; padding-top:1.3rem; }
.wr-btn {
    display:inline-flex; align-items:center; justify-content:center; gap:0.4rem;
    min-height:2.2rem; padding:0 1rem;
    border:0; border-radius:0.65rem;
    font-size:0.85rem; font-weight:700; cursor:pointer; text-decoration:none;
}
.wr-btn--primary { background:linear-gradient(90deg,#b45309,#d97706); color:#fff; box-shadow:0 8px 20px rgba(217,119,6,.2); }
.wr-btn--ghost   { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }

.wr-info {
    display:flex; align-items:center; gap:0.75rem;
    background:#fffbeb; border:1px solid #fde68a;
    border-radius:0.9rem; padding:0.8rem 1.1rem;
    font-size:0.875rem; color:#92400e;
}
.wr-info svg { width:1.1rem; height:1.1rem; flex-shrink:0; }

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
.wr-kpi--green  .wr-kpi__value { color:#16a34a; }
.wr-kpi--amber  .wr-kpi__value { color:#d97706; }
.wr-kpi--red    .wr-kpi__value { color:#dc2626; }
.wr-kpi--purple .wr-kpi__value { color:#7c3aed; }

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
    background:#fffbeb; color:#b45309;
    font-size:0.78rem; font-weight:700;
    padding:0.25rem 0.75rem; border-radius:20px;
}

.wr-table-wrap { overflow-x:auto; }
.wr-table { width:100%; border-collapse:collapse; min-width:740px; }
.wr-table th {
    padding:0.75rem 1rem; font-size:0.72rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.05em; color:#94a3b8;
    background:#fcfdff; border-bottom:1px solid #eef2f7; white-space:nowrap; text-align:left;
}
.wr-table td { padding:0.85rem 1rem; font-size:0.875rem; color:#475569; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.wr-table tbody tr:last-child td { border-bottom:0; }
.wr-table tbody tr:hover { background:#fafbff; }
.wr-table__name { font-weight:600; color:#1e293b; }
.wr-table__num  { font-weight:700; color:#334155; }
.wr-table__empty { text-align:center; color:#94a3b8; padding:2.5rem 1rem; font-size:0.88rem; }

.wr-rate { display:flex; align-items:center; gap:0.6rem; }
.wr-rate__bar { flex:1; height:6px; background:#f1f5f9; border-radius:99px; overflow:hidden; min-width:60px; }
.wr-rate__fill { height:100%; border-radius:99px; }
.wr-rate__fill--high   { background:linear-gradient(90deg,#16a34a,#4ade80); }
.wr-rate__fill--medium { background:linear-gradient(90deg,#d97706,#fbbf24); }
.wr-rate__fill--low    { background:linear-gradient(90deg,#dc2626,#f87171); }
.wr-rate__pct { font-size:0.82rem; font-weight:700; color:#334155; white-space:nowrap; min-width:2.8rem; text-align:right; }

.badge-warn {
    display:inline-flex; align-items:center;
    font-size:0.72rem; font-weight:700;
    padding:3px 10px; border-radius:20px; white-space:nowrap;
}
.warn-none { background:#f1f5f9; color:#64748b; }
.warn-1    { background:#fef3c7; color:#92400e; }
.warn-2    { background:#fee2e2; color:#991b1b; }
</style>
@endpush

@section('content')
<div class="wr-page">

    @include('partials.back-button', ['href' => route('lecturer.reports'), 'label' => 'Reports'])

    <div class="wr-header">
        <h1 class="wr-title">Semester Attendance Report</h1>
        <p class="wr-subtitle">Attendance summary for your students — {{ $lecturerName }}@isset($lecturerPhone) &middot; {{ $lecturerPhone }}@endisset</p>
    </div>

    <form class="wr-filters" method="GET" action="{{ route('lecturer.reports.attendance.semester') }}">
        <div class="wr-filter-group">
            <span class="wr-filter-label">Academic Semester</span>
            <select class="wr-filter-select" name="academic_period_id" onchange="this.form.submit()">
                @foreach ($periodOptions as $p)
                    <option value="{{ $p['value'] }}" {{ $selectedPeriodId === $p['value'] ? 'selected' : '' }}>
                        {{ $p['label'] }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="wr-filter-actions">
            <button type="submit" class="wr-btn wr-btn--primary">Apply</button>
            <a href="{{ route('lecturer.reports.attendance.semester.pdf', ['academic_period_id' => $selectedPeriodId]) }}"
               class="wr-btn wr-btn--ghost" style="gap:0.35rem;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:.85rem;height:.85rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                PDF
            </a>
            <a href="{{ route('lecturer.reports.attendance.semester') }}" class="wr-btn wr-btn--ghost">Clear</a>
        </div>
    </form>

    <div class="wr-info">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
        </svg>
        <span>
            <strong>{{ $semesterLabel }}</strong>
            @if ($semStart && $semEnd)
                &nbsp;&mdash;&nbsp; {{ $semStart->format('d M Y') }} to {{ $semEnd->format('d M Y') }}
            @endif
            &nbsp;&mdash;&nbsp; Total sessions: <strong>{{ $totalSessions }}</strong>
        </span>
    </div>

    <div class="wr-kpis">
        <div class="wr-kpi wr-kpi--green">
            <p class="wr-kpi__label">Avg Present Rate</p>
            <p class="wr-kpi__value">{{ $avgAttendanceRate }}%</p>
            <p class="wr-kpi__note">Class-wide average</p>
        </div>
        <div class="wr-kpi wr-kpi--amber">
            <p class="wr-kpi__label">Avg TH Rate</p>
            <p class="wr-kpi__value">{{ $avgAbsentRate }}%</p>
            <p class="wr-kpi__note">Unexcused absence average</p>
        </div>
        <div class="wr-kpi wr-kpi--red">
            <p class="wr-kpi__label">Warning Level 2</p>
            <p class="wr-kpi__value">{{ $warn2Count }}</p>
            <p class="wr-kpi__note">Students at high risk</p>
        </div>
        <div class="wr-kpi wr-kpi--purple">
            <p class="wr-kpi__label">Perfect Attendance</p>
            <p class="wr-kpi__value">{{ $perfectCount }}</p>
            <p class="wr-kpi__note">100% present this semester</p>
        </div>
    </div>

    <div class="wr-card">
        <div class="wr-card__head">
            <div>
                <h2 class="wr-card__title">Student Attendance — {{ $semesterLabel }}</h2>
                <p class="wr-card__subtitle">Present, TH (absent), THS (excused), attendance rate, and warning level per student.</p>
            </div>
            <span class="wr-card__chip">{{ $rows->count() }} students</span>
        </div>

        <div class="wr-table-wrap">
            @if ($rows->isEmpty())
                <p class="wr-table__empty">No students found.</p>
            @else
            <table class="wr-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Student Name</th>
                        <th>Batch</th>
                        <th style="text-align:center;">Present</th>
                        <th style="text-align:center;">TH</th>
                        <th style="text-align:center;">THS</th>
                        <th>Attendance Rate</th>
                        <th style="text-align:center;">Warning</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $i => $row)
                    @php
                        $rate = $row->attendance_rate;
                        $fillClass = $rate >= 80 ? 'wr-rate__fill--high' : ($rate >= 60 ? 'wr-rate__fill--medium' : 'wr-rate__fill--low');
                        $warnClass = $row->warning_level === 0 ? 'warn-none' : ($row->warning_level === 1 ? 'warn-1' : 'warn-2');
                        $warnLabel = $row->warning_level === 0 ? 'None' : 'Level ' . $row->warning_level;
                    @endphp
                    <tr>
                        <td style="color:#94a3b8;font-size:.8rem;font-weight:700;">{{ $i + 1 }}</td>
                        <td class="wr-table__name">{{ $row->student_name }}</td>
                        <td style="font-size:0.82rem;color:#64748b;">{{ $row->batch_name }}</td>
                        <td style="text-align:center;" class="wr-table__num" style="color:#16a34a;">{{ $row->total_present }}</td>
                        <td style="text-align:center;" class="wr-table__num" style="color:#d97706;">{{ $row->total_absent }}</td>
                        <td style="text-align:center;" class="wr-table__num" style="color:#64748b;">{{ $row->total_excused }}</td>
                        <td style="min-width:130px;">
                            <div class="wr-rate">
                                <div class="wr-rate__bar">
                                    <div class="wr-rate__fill {{ $fillClass }}" style="width:{{ $rate }}%"></div>
                                </div>
                                <span class="wr-rate__pct">{{ $rate }}%</span>
                            </div>
                        </td>
                        <td style="text-align:center;">
                            <span class="badge-warn {{ $warnClass }}">{{ $warnLabel }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

</div>
@endsection
