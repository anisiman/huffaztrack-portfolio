@extends('layouts/lecturer')

@section('title', 'Subject Attendance Report')

@push('styles')
<style>
.wr-page     { display:flex; flex-direction:column; gap:1.5rem; }
.wr-header   { display:flex; flex-direction:column; gap:0.25rem; }
.wr-title    { margin:0; font-size:2.25rem; font-weight:700; letter-spacing:-0.02em; color:#1e293b; }
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
    font-size:0.875rem; cursor:pointer; outline:none; min-width:160px;
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

.wr-kpis { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; }
@media(max-width:860px){ .wr-kpis { grid-template-columns:repeat(1,1fr); } }
.wr-kpi {
    background:#fff; border:2px solid #e2e8f0;
    border-radius:1.1rem; padding:1.1rem 1.25rem;
    box-shadow:0 8px 22px rgba(15,23,42,.05);
    display:flex; flex-direction:column; gap:0.35rem;
}
.wr-kpi__label { font-size:0.78rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em; }
.wr-kpi__value { font-size:1.75rem; font-weight:800; letter-spacing:-0.04em; }
.wr-kpi--green  .wr-kpi__value { color:#16a34a; }
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
.wr-card__title { margin:0; font-size:1.05rem; font-weight:700; color:#1e293b; }
.wr-table-wrap { overflow-x:auto; }
.wr-table { width:100%; border-collapse:collapse; min-width:760px; }
.wr-table th {
    padding:0.75rem 1rem; font-size:0.72rem; font-weight:700;
    text-transform:uppercase; letter-spacing:.05em; color:#94a3b8;
    background:#fcfdff; border-bottom:1px solid #eef2f7; white-space:nowrap; text-align:left;
}
.wr-table td { padding:0.85rem 1rem; font-size:0.875rem; color:#475569; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.wr-table tbody tr:last-child td { border-bottom:0; }
.wr-table tbody tr:hover { background:#fafbff; }
.wr-table__name  { font-weight:600; color:#1e293b; }
.wr-table__empty { text-align:center; color:#94a3b8; padding:2.5rem 1rem; font-size:0.88rem; }
</style>
@endpush

@section('content')
<div class="wr-page">

    @include('partials.back-button', ['href' => route('lecturer.reports'), 'label' => 'Reports'])

    <div class="wr-header">
        <h1 class="wr-title">Subject Attendance Report</h1>
        <p class="wr-subtitle">Present/Absent counts and attendance percentage for your own subjects only.</p>
    </div>

    <form class="wr-toolbar" method="GET" action="{{ route('lecturer.reports.subjectAttendance') }}">
        <div class="wr-filter-group">
            <span class="wr-filter-label">Academic Semester</span>
            <select class="wr-field-input" name="academic_period_id" onchange="this.form.submit()">
                @foreach ($academicPeriods as $p)
                    <option value="{{ $p->id }}" {{ $period && $period->id === $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="wr-filter-group">
            <span class="wr-filter-label">Subject</span>
            <select class="wr-field-input" name="subjectT_id" onchange="this.form.submit()">
                <option value="">All My Subjects</option>
                @foreach ($subjects as $s)
                    <option value="{{ $s->subjectT_id }}" {{ (string) $filterSubjectId === (string) $s->subjectT_id ? 'selected' : '' }}>{{ $s->subjectT_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="wr-filter-group">
            <span class="wr-filter-label">Batch</span>
            <select class="wr-field-input" name="batch_id" onchange="this.form.submit()">
                <option value="">All Batches</option>
                @foreach ($batches as $b)
                    <option value="{{ $b->batch_id }}" {{ (string) $filterBatchId === (string) $b->batch_id ? 'selected' : '' }}>{{ $b->batch_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="wr-filter-group">
            <span class="wr-filter-label">From</span>
            <input class="wr-field-input" type="date" name="date_from" value="{{ $dateFrom?->format('Y-m-d') }}" onchange="this.form.submit()">
        </div>
        <div class="wr-filter-group">
            <span class="wr-filter-label">To</span>
            <input class="wr-field-input" type="date" name="date_to" value="{{ $dateTo?->format('Y-m-d') }}" onchange="this.form.submit()">
        </div>
        <div style="display:flex;gap:0.5rem;padding-top:1.3rem;">
            <a href="{{ route('lecturer.reports.subjectAttendance') }}" class="wr-btn wr-btn--ghost">Reset</a>
            <a href="{{ route('lecturer.reports.subjectAttendance.pdf', request()->query()) }}" class="wr-btn wr-btn--primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:.85rem;height:.85rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                PDF
            </a>
        </div>
    </form>

    <div class="wr-kpis">
        <div class="wr-kpi wr-kpi--green">
            <p class="wr-kpi__label">Total Present</p>
            <p class="wr-kpi__value">{{ $totalPresent }}</p>
        </div>
        <div class="wr-kpi wr-kpi--red">
            <p class="wr-kpi__label">Total Absent</p>
            <p class="wr-kpi__value">{{ $totalAbsent }}</p>
        </div>
        <div class="wr-kpi wr-kpi--purple">
            <p class="wr-kpi__label">Overall Rate</p>
            <p class="wr-kpi__value">{{ $overallRate !== null ? $overallRate . '%' : '—' }}</p>
        </div>
    </div>

    <div class="wr-card">
        <div class="wr-card__head">
            <h2 class="wr-card__title">{{ $rows->count() }} student-subject row(s){{ $period ? ' — ' . $period->name : '' }}</h2>
        </div>
        <div class="wr-table-wrap">
            @if ($rows->isEmpty())
                <p class="wr-table__empty">No records match these filters.</p>
            @else
            <table class="wr-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>Subject</th>
                        <th>Section</th>
                        <th>Batch</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Attendance %</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td class="wr-table__name">{{ $row->student_name }}</td>
                            <td>{{ $row->student_id }}</td>
                            <td>{{ $row->subject_name }}</td>
                            <td>{{ $row->section_name ?? '—' }}</td>
                            <td>{{ $row->batch_name }}</td>
                            <td style="font-weight:700;color:#16a34a;">{{ $row->present }}</td>
                            <td style="font-weight:700;color:{{ $row->absent > 0 ? '#dc2626' : '#94a3b8' }};">{{ $row->absent }}</td>
                            <td>
                                @if ($row->percentage !== null)
                                    <span style="font-weight:700;color:{{ $row->percentage >= 80 ? '#16a34a' : ($row->percentage >= 60 ? '#d97706' : '#dc2626') }};">{{ $row->percentage }}%</span>
                                @else
                                    <span style="color:#94a3b8;">—</span>
                                @endif
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
