@extends('layouts/lecturer')

@section('title', 'Attendance Daily Report')

@push('styles')
<style>
.wr-page     { display:flex; flex-direction:column; gap:1.5rem; }
.wr-header   { display:flex; flex-direction:column; gap:0.25rem; }
.wr-title    { margin:0; font-size:2.25rem; font-weight:700; letter-spacing:-0.02em; color:#0f172a; }
.wr-subtitle { margin:0; font-size:0.92rem; color:#94a3b8; }

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

.wr-kpis { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; }
@media(max-width:860px){ .wr-kpis { grid-template-columns:repeat(2,1fr); } }
.wr-kpi {
    background:#fff; border:1px solid #e2e8f0;
    border-radius:1.1rem; padding:1.1rem 1.25rem;
    box-shadow:0 8px 22px rgba(15,23,42,.05);
    display:flex; flex-direction:column; gap:0.35rem;
}
.wr-kpi__label { font-size:0.78rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em; }
.wr-kpi__value { font-size:1.75rem; font-weight:800; letter-spacing:-0.04em; }
.wr-kpi__note  { font-size:0.78rem; color:#94a3b8; }
.wr-kpi--green  .wr-kpi__value { color:#16a34a; }
.wr-kpi--red    .wr-kpi__value { color:#dc2626; }
.wr-kpi--blue   .wr-kpi__value { color:#2563eb; }
.wr-kpi--purple .wr-kpi__value { color:#7c3aed; }

.wr-info {
    display:flex; align-items:center; gap:0.75rem;
    background:#f5f3ff; border:1px solid #ddd6fe;
    border-radius:0.9rem; padding:0.8rem 1.1rem;
    font-size:0.875rem; color:#5b21b6;
}
.wr-info svg { width:1.1rem; height:1.1rem; flex-shrink:0; }

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
.wr-table { width:100%; border-collapse:collapse; min-width:580px; }
.wr-table th {
    padding:0.75rem 1rem; font-size:0.72rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.05em; color:#94a3b8;
    background:#fcfdff; border-bottom:1px solid #eef2f7; text-align:left;
}
.wr-table td { padding:0.85rem 1rem; font-size:0.875rem; color:#475569; border-bottom:1px solid #f1f5f9; }
.wr-table tbody tr:last-child td { border-bottom:0; }
.wr-table tbody tr:hover { background:#fafbff; }
.wr-table__name  { font-weight:600; color:#1e293b; }
.wr-table__sub   { display:block; font-size:0.75rem; color:#94a3b8; margin-top:1px; }
.wr-table__empty { text-align:center; color:#94a3b8; padding:2.5rem 1rem; font-size:0.88rem; }

/* Attendance status badges */
.att-badge {
    display:inline-flex; align-items:center;
    font-size:0.75rem; font-weight:700;
    padding:0.2rem 0.65rem; border-radius:99px;
}
.att-badge--present  { background:#dcfce7; color:#15803d; }
.att-badge--absent   { background:#fee2e2; color:#dc2626; }
.att-badge--excused  { background:#dbeafe; color:#1d4ed8; }
.att-badge--none     { background:#f1f5f9; color:#94a3b8; }
</style>
@endpush

@section('content')
<div class="wr-page">

    {{-- Back --}}
    @include('partials.back-button', ['href' => route('lecturer.reports'), 'label' => 'Reports'])

    {{-- Header --}}
    <div class="wr-header">
        <h1 class="wr-title">Attendance Daily Report</h1>
        <p class="wr-subtitle">Attendance status for each student on a selected date.</p>
    </div>

    {{-- Toolbar --}}
    <form class="wr-toolbar" method="GET" action="{{ route('lecturer.reports.attendance.daily') }}">
        <div class="wr-filter-group">
            <span class="wr-filter-label">Date</span>
            <input class="wr-field-input" type="date" name="date"
                value="{{ $date->format('Y-m-d') }}"
                max="{{ $today->format('Y-m-d') }}"
                onchange="this.form.submit()">
        </div>
        <div style="display:flex;gap:0.5rem;padding-top:1.3rem;">
            <button type="submit" class="wr-btn wr-btn--primary">Apply</button>
            <a href="{{ route('lecturer.reports.attendance.daily.pdf', ['date' => $date->format('Y-m-d')]) }}"
               class="wr-btn wr-btn--ghost" style="gap:0.35rem;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:.85rem;height:.85rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                PDF
            </a>
        </div>
    </form>

    {{-- Date banner --}}
    <div class="wr-info">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
        </svg>
        <span>Attendance for <strong>{{ $date->format('l, d F Y') }}</strong></span>
    </div>

    {{-- KPI summary --}}
    <div class="wr-kpis">
        <div class="wr-kpi wr-kpi--green">
            <p class="wr-kpi__label">Present</p>
            <p class="wr-kpi__value">{{ $presentCount }}</p>
            <p class="wr-kpi__note">Students present</p>
        </div>
        <div class="wr-kpi wr-kpi--red">
            <p class="wr-kpi__label">Absent (TH)</p>
            <p class="wr-kpi__value">{{ $absentCount }}</p>
            <p class="wr-kpi__note">Unexcused absences</p>
        </div>
        <div class="wr-kpi wr-kpi--blue">
            <p class="wr-kpi__label">Excused (THS)</p>
            <p class="wr-kpi__value">{{ $excusedCount }}</p>
            <p class="wr-kpi__note">Absent with reason</p>
        </div>
        <div class="wr-kpi wr-kpi--purple">
            <p class="wr-kpi__label">Attendance Rate</p>
            <p class="wr-kpi__value">{{ $attendanceRate !== null ? $attendanceRate . '%' : '—' }}</p>
            <p class="wr-kpi__note">Based on recorded only</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="wr-card">
        <div class="wr-card__head">
            <div>
                <h2 class="wr-card__title">Student Attendance — {{ $date->format('d M Y') }}</h2>
                <p class="wr-card__subtitle">Status and remarks for each assigned student.</p>
            </div>
            <span class="wr-card__chip">{{ $rows->count() }} students</span>
        </div>

        <div class="wr-table-wrap">
            <table class="wr-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Student Name</th>
                        <th>Halaqah</th>
                        <th>Status</th>
                        <th>Reason</th>
                        <th>Remarks</th>
                        <th>Document</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $i => $row)
                    @php
                        $badgeClass = match($row->status) {
                            'present'            => 'att-badge--present',
                            'absent'             => 'att-badge--absent',
                            'absent_with_reason' => 'att-badge--excused',
                            default              => 'att-badge--none',
                        };
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <span class="wr-table__name">{{ $row->student_name }}</span>
                            <span class="wr-table__sub">{{ $row->student_id }}</span>
                        </td>
                        <td>{{ $row->batch_name }}</td>
                        <td><span class="att-badge {{ $badgeClass }}">{{ $row->status_label }}</span></td>
                        <td style="font-size:0.82rem;">{{ $row->reason_type ?? '—' }}</td>
                        <td style="font-size:0.82rem;color:#64748b;">{{ $row->remarks ?? '—' }}</td>
                        <td>
                            @if ($row->supporting_document ?? null)
                                <a href="{{ route('documents.attendance', $row->attendance_id) }}"
                                   target="_blank"
                                   style="font-size:0.78rem;color:#0369a1;text-decoration:none;font-weight:600;">
                                    View
                                </a>
                            @else
                                <span style="color:#cbd5e1;">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="wr-table__empty">No students assigned to your account.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
