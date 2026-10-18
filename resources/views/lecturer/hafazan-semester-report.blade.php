@extends('layouts/lecturer')

@section('title', 'Semester Hafazan Report')

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
.wr-kpi--gray   .wr-kpi__value { color:#64748b; }
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

.wr-progress { display:flex; align-items:center; gap:0.6rem; }
.wr-progress__bar { flex:1; height:6px; background:#f1f5f9; border-radius:99px; overflow:hidden; min-width:60px; }
.wr-progress__fill { height:100%; border-radius:99px; background:linear-gradient(90deg,#d97706,#b45309); }
.wr-progress__pct { font-size:0.82rem; font-weight:700; color:#334155; white-space:nowrap; min-width:2.5rem; text-align:right; }

.badge-status {
    display:inline-flex; align-items:center;
    font-size:0.72rem; font-weight:700;
    padding:3px 10px; border-radius:20px; white-space:nowrap;
}
.badge-completed    { background:#dcfce7; color:#15803d; }
.badge-in-progress  { background:#fef3c7; color:#92400e; }
.badge-not-recorded { background:#f1f5f9; color:#64748b; }
</style>
@endpush

@section('content')
<div class="wr-page">

    @include('partials.back-button', ['href' => route('lecturer.reports'), 'label' => 'Reports'])

    <div class="wr-header">
        <h1 class="wr-title">Semester Hafazan Report</h1>
        <p class="wr-subtitle">Cumulative hafazan progress for your students — {{ $lecturerName }}@isset($lecturerPhone) &middot; {{ $lecturerPhone }}@endisset</p>
    </div>

    <form class="wr-filters" method="GET" action="{{ route('lecturer.reports.hafazan.semester') }}">
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
            <a href="{{ route('lecturer.reports.hafazan.semester.pdf', ['academic_period_id' => $selectedPeriodId]) }}"
               class="wr-btn wr-btn--ghost" style="gap:0.35rem;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:.85rem;height:.85rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                PDF
            </a>
            <a href="{{ route('lecturer.reports.hafazan.semester') }}" class="wr-btn wr-btn--ghost">Clear</a>
        </div>
    </form>

    <div class="wr-info">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
        </svg>
        <span>
            <strong>{{ $semesterLabel }}</strong>
            @if ($semStart && $semEnd)
                &nbsp;&mdash;&nbsp; {{ $semStart->format('d M Y') }} to {{ $semEnd->format('d M Y') }}
            @endif
            @if ($semesterEndPage > 0)
                &nbsp;&mdash;&nbsp; Target page: <strong>{{ $semesterEndPage }}</strong>
            @endif
        </span>
    </div>

    <div class="wr-kpis">
        <div class="wr-kpi wr-kpi--green">
            <p class="wr-kpi__label">Completed</p>
            <p class="wr-kpi__value">{{ $completedCount }}</p>
            <p class="wr-kpi__note">Reached semester target</p>
        </div>
        <div class="wr-kpi wr-kpi--amber">
            <p class="wr-kpi__label">In Progress</p>
            <p class="wr-kpi__value">{{ $inProgressCount }}</p>
            <p class="wr-kpi__note">Active but not yet completed</p>
        </div>
        <div class="wr-kpi wr-kpi--gray">
            <p class="wr-kpi__label">Not Recorded</p>
            <p class="wr-kpi__value">{{ $notRecordedCount }}</p>
            <p class="wr-kpi__note">No tasmi' this semester</p>
        </div>
        <div class="wr-kpi wr-kpi--purple">
            <p class="wr-kpi__label">Total Students</p>
            <p class="wr-kpi__value">{{ $rows->count() }}</p>
            <p class="wr-kpi__note">Under your class</p>
        </div>
    </div>

    <div class="wr-card">
        <div class="wr-card__head">
            <div>
                <h2 class="wr-card__title">Student Hafazan — {{ $semesterLabel }}</h2>
                <p class="wr-card__subtitle">Latest page reached, sessions attended, weekly average gain, and overall progress this semester.</p>
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
                        <th style="text-align:center;">Latest Page</th>
                        <th style="text-align:center;">Sessions</th>
                        <th style="text-align:center;">Wkly Avg</th>
                        <th>Overall Progress</th>
                        <th style="text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $i => $row)
                    <tr>
                        <td style="color:#94a3b8;font-size:.8rem;font-weight:700;">{{ $i + 1 }}</td>
                        <td class="wr-table__name">{{ $row->student_name }}</td>
                        <td style="font-size:0.82rem;color:#64748b;">{{ $row->batch_name }}</td>
                        <td style="text-align:center;" class="wr-table__num">
                            {{ $row->latest_page !== null ? $row->latest_page : '—' }}
                            @if ($semesterEndPage > 0)
                                <span style="font-size:0.75rem;color:#94a3b8;font-weight:400;">/ {{ $semesterEndPage }}</span>
                            @endif
                        </td>
                        <td style="text-align:center;" class="wr-table__num">{{ $row->total_sessions ?: '—' }}</td>
                        <td style="text-align:center;font-weight:700;color:#475569;">
                            {{ $row->weekly_avg !== null ? $row->weekly_avg . ' pg/wk' : '—' }}
                        </td>
                        <td style="min-width:120px;">
                            @if ($row->progress_pct > 0)
                            <div class="wr-progress">
                                <div class="wr-progress__bar">
                                    <div class="wr-progress__fill" style="width:{{ $row->progress_pct }}%"></div>
                                </div>
                                <span class="wr-progress__pct">{{ $row->progress_pct }}%</span>
                            </div>
                            @else
                                <span style="color:#94a3b8;font-size:0.82rem;">—</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <span class="badge-status {{ $row->status === 'Completed' ? 'badge-completed' : ($row->status === 'In Progress' ? 'badge-in-progress' : 'badge-not-recorded') }}">
                                {{ $row->status }}
                            </span>
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
