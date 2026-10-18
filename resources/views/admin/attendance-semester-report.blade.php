@extends('layouts/admin')

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
.wr-kpi__value-row { display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap; }
.wr-kpi__value { font-size:1.75rem; font-weight:800; letter-spacing:-0.04em; }
.wr-kpi__note  { font-size:0.78rem; color:#94a3b8; }
.wr-kpi__badge {
    display:inline-flex; align-items:center;
    font-size:0.72rem; font-weight:700;
    padding:0.18rem 0.6rem; border-radius:20px; white-space:nowrap;
}
.wr-kpi__badge--green  { background:#dcfce7; color:#15803d; }
.wr-kpi__badge--red    { background:#fee2e2; color:#b91c1c; }
.wr-kpi__badge--blue   { background:#dbeafe; color:#1d4ed8; }
.wr-kpi__badge--purple { background:#ede9fe; color:#6d28d9; }
.wr-kpi--green  .wr-kpi__value { color:#16a34a; }
.wr-kpi--red    .wr-kpi__value { color:#dc2626; }
.wr-kpi--blue   .wr-kpi__value { color:#2563eb; }
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
.wr-table { width:100%; border-collapse:collapse; min-width:820px; }
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

.wr-batch-header td {
    background:#f8fafc !important; border-top:1px solid #e2e8f0 !important;
    border-bottom:1px solid #e2e8f0 !important; padding:.45rem 1rem !important;
    font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#b45309;
}
.wr-batch-header:hover td { background:#f8fafc !important; }
.wr-batch-header__count { font-weight:400; text-transform:none; letter-spacing:0; color:#c2872f; margin-left:.35rem; }

.wr-lecturer-header td {
    background:#fdfdfe !important; border-bottom:1px solid #f1f5f9 !important;
    padding:.4rem 1rem .4rem 1.75rem !important;
    font-size:.72rem; font-weight:700; color:#64748b;
}
.wr-lecturer-header:hover td { background:#fdfdfe !important; }
.wr-lecturer-header__count { font-weight:400; color:#94a3b8; margin-left:.35rem; }

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

.warn-badge {
    display:inline-flex; align-items:center;
    font-size:0.7rem; font-weight:700;
    padding:2px 8px; border-radius:20px;
}
.warn-0 { background:#f1f5f9; color:#64748b; }
.warn-1 { background:#fef3c7; color:#92400e; }
.warn-2 { background:#fee2e2; color:#b91c1c; }
</style>
@endpush

@section('content')
<div class="wr-page">

    @include('partials.back-button', ['href' => route('admin.reports', ['section' => 'attendance']), 'label' => 'Attendance Reports'])

    <div class="wr-header">
        <h1 class="wr-title">Semester Attendance Report</h1>
        <p class="wr-subtitle">Full-semester attendance breakdown per student — all batches &amp; lecturers.</p>
    </div>

    <form class="wr-filters" method="GET" action="{{ route('admin.reports.attendance.semester') }}">
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
            <a href="{{ route('admin.reports.attendance.semester.pdf', ['academic_period_id' => $selectedPeriodId, 'halaqah' => $filterHalaqah]) }}"
               class="wr-btn wr-btn--ghost" style="gap:0.35rem;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:.85rem;height:.85rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                PDF
            </a>
            <a href="{{ route('admin.reports.attendance.semester') }}" class="wr-btn wr-btn--ghost">Clear</a>
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
            &nbsp;&mdash;&nbsp; {{ $totalStudents }} students {{ $filterHalaqah !== '' ? 'in '.$filterHalaqah : 'across all batches' }}
        </span>
    </div>

    <div class="wr-kpis">
        <div class="wr-kpi wr-kpi--green">
            <p class="wr-kpi__label">Overall Present Rate</p>
            <div class="wr-kpi__value-row">
                <span class="wr-kpi__value">{{ $avgAttendanceRate }}%</span>
                <span class="wr-kpi__badge wr-kpi__badge--green">{{ $totalStudents }} Students</span>
            </div>
            <p class="wr-kpi__note">Present sessions this semester</p>
        </div>
        <div class="wr-kpi wr-kpi--red">
            <p class="wr-kpi__label">TH Rate (Absent)</p>
            <div class="wr-kpi__value-row">
                <span class="wr-kpi__value">{{ $avgAbsentRate }}%</span>
                <span class="wr-kpi__badge wr-kpi__badge--red">{{ $thStudentCount }} {{ Str::plural('Student', $thStudentCount) }}</span>
            </div>
            <p class="wr-kpi__note">Had TH this semester</p>
        </div>
        <div class="wr-kpi wr-kpi--blue">
            <p class="wr-kpi__label">THS Rate (Excused)</p>
            <div class="wr-kpi__value-row">
                <span class="wr-kpi__value">{{ $avgExcusedRate }}%</span>
                <span class="wr-kpi__badge wr-kpi__badge--blue">{{ $thsStudentCount }} {{ Str::plural('Student', $thsStudentCount) }}</span>
            </div>
            <p class="wr-kpi__note">Had approved excuse</p>
        </div>
        <div class="wr-kpi wr-kpi--purple">
            <p class="wr-kpi__label">Perfect Attendance</p>
            <div class="wr-kpi__value-row">
                <span class="wr-kpi__value">{{ $perfectCount }}</span>
                <span class="wr-kpi__badge wr-kpi__badge--purple">of {{ $totalStudents }}</span>
            </div>
            <p class="wr-kpi__note">Attended all sessions</p>
        </div>
    </div>

    <div class="wr-card">
        <div class="wr-card__head">
            <div>
                <h2 class="wr-card__title">Student Attendance — {{ $semesterLabel }}</h2>
                <p class="wr-card__subtitle">Total present, TH, THS, attendance rate, and warning level per student.</p>
            </div>
            <span class="wr-card__chip">{{ $totalStudents }} students</span>
        </div>

        <div class="wr-table-wrap">
            @if ($rows->isEmpty())
                <p class="wr-table__empty">No students found.</p>
            @else
            @php
                $grouped = $rows->groupBy('batch_name')->sortKeys();
                $rowNum  = 0;
            @endphp
            <table class="wr-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Student Name</th>
                        <th style="text-align:center;color:#16a34a;">Present</th>
                        <th style="text-align:center;color:#dc2626;">TH (Absent)</th>
                        <th style="text-align:center;color:#2563eb;">THS (Excused)</th>
                        <th>Attendance Rate</th>
                        <th style="text-align:center;">Warning</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($grouped as $batchName => $batchRows)
                    <tr class="wr-batch-header">
                        <td colspan="7">
                            {{ $batchName ?: 'Unassigned' }}
                            <span class="wr-batch-header__count">({{ $batchRows->count() }} {{ Str::plural('student', $batchRows->count()) }})</span>
                        </td>
                    </tr>
                    @php $byLecturer = $batchRows->groupBy('lecturer_name')->sortKeys(); @endphp
                    @foreach ($byLecturer as $lecturerName => $lecturerRows)
                    <tr class="wr-lecturer-header">
                        <td colspan="7">
                            {{ $lecturerName ?: 'Unassigned' }}
                            <span class="wr-lecturer-header__count">({{ $lecturerRows->count() }} {{ Str::plural('student', $lecturerRows->count()) }})</span>
                        </td>
                    </tr>
                    @foreach ($lecturerRows->sortBy('student_name') as $row)
                    @php
                        $rowNum++;
                        $rc = $row->attendance_rate <= 0 ? 'none' : ($row->attendance_rate >= 80 ? 'high' : ($row->attendance_rate >= 60 ? 'mid' : 'low'));
                    @endphp
                    <tr>
                        <td style="color:#94a3b8;font-size:.8rem;font-weight:700;">{{ $rowNum }}</td>
                        <td class="wr-table__name">{{ $row->student_name }}</td>
                        <td style="text-align:center;font-weight:700;color:#16a34a;">{{ $row->total_present }}</td>
                        <td style="text-align:center;font-weight:700;color:{{ $row->total_absent > 0 ? '#dc2626' : '#94a3b8' }};">{{ $row->total_absent }}</td>
                        <td style="text-align:center;font-weight:700;color:{{ $row->total_excused > 0 ? '#2563eb' : '#94a3b8' }};">{{ $row->total_excused }}</td>
                        <td>
                            @if ($row->total_sessions > 0)
                            <div class="rate-bar-wrap">
                                <div class="rate-bar">
                                    <div class="rate-bar__fill rate-bar__fill--{{ $rc }}" style="width:{{ $row->attendance_rate }}%;"></div>
                                </div>
                                <span class="rate-label rate-label--{{ $rc }}">{{ $row->attendance_rate }}%</span>
                            </div>
                            @else
                                <span class="rate-label rate-label--none">No records</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <span class="warn-badge warn-{{ min(2, $row->warning_level) }}">
                                {{ $row->warning_level > 0 ? 'Level ' . $row->warning_level : 'None' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

</div>
@endsection
