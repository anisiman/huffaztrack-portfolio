@extends('layouts/lecturer')

@section('title', 'Daily Hafazan Report')

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
    font-size:0.875rem; cursor:pointer; outline:none;
    min-width:160px;
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
.wr-kpi--blue   .wr-kpi__value { color:#2563eb; }
.wr-kpi--red    .wr-kpi__value { color:#dc2626; }
.wr-kpi--gray   .wr-kpi__value { color:#64748b; }

.wr-info {
    display:flex; align-items:center; gap:0.75rem;
    background:#f5f3ff; border:1px solid #ddd6fe;
    border-radius:0.9rem; padding:0.8rem 1.1rem;
    font-size:0.875rem; color:#5b21b6;
}
.wr-info svg { width:1.1rem; height:1.1rem; flex-shrink:0; }

/* Insight summary card */
.wr-insight {
    background:#fffbeb; border:1px solid #fde68a;
    border-radius:0.9rem; padding:0.9rem 1.1rem;
    font-size:0.875rem; color:#78350f; line-height:1.7;
}
.wr-insight__title {
    font-size:0.8rem; font-weight:700; color:#92400e;
    text-transform:uppercase; letter-spacing:.05em;
    margin-bottom:0.35rem;
}
.wr-insight__line { margin:0; }

.wr-card {
    background:#fff; border:1px solid #e2e8f0;
    border-radius:1.25rem; overflow:hidden;
    box-shadow:0 14px 32px rgba(15,23,42,.06);
}
.wr-card__head {
    display:flex; align-items:center; justify-content:space-between;
    padding:1.1rem 1.4rem 0.9rem;
    border-bottom:1px solid #f1f5f9;
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
.wr-table { width:100%; border-collapse:collapse; min-width:700px; }
.wr-table th {
    padding:0.75rem 1rem;
    font-size:0.72rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.05em; color:#94a3b8; background:#fcfdff;
    border-bottom:1px solid #eef2f7; white-space:nowrap; text-align:left;
}
.wr-table td {
    padding:0.85rem 1rem;
    font-size:0.875rem; color:#475569;
    border-bottom:1px solid #f1f5f9; vertical-align:middle;
}
.wr-table tbody tr:last-child td { border-bottom:0; }
.wr-table tbody tr:hover { background:#fafbff; }
.wr-table__name  { font-weight:600; color:#1e293b; }
.wr-table__sub   { display:block; font-size:0.75rem; color:#94a3b8; margin-top:1px; }
.wr-table__num   { font-weight:700; color:#334155; }
.wr-table__empty { text-align:center; color:#94a3b8; padding:2.5rem 1rem; font-size:0.88rem; }

.wr-badge {
    display:inline-flex; align-items:center;
    font-size:0.72rem; font-weight:700;
    padding:3px 10px; border-radius:20px; white-space:nowrap;
}
.wr-badge--completed { background:#dcfce7; color:#15803d; }
.wr-badge--ontrack   { background:#dbeafe; color:#1d4ed8; }
.wr-badge--behind    { background:#fee2e2; color:#dc2626; }
.wr-badge--none      { background:#f1f5f9; color:#64748b; }

.gauge-wrap { display:flex; align-items:center; gap:0.5rem; min-width:110px; }
.gauge-bar  { flex:1; height:6px; background:#f1f5f9; border-radius:99px; overflow:hidden; }
.gauge-fill { height:100%; border-radius:99px; }
.gauge-pct  { font-size:0.78rem; font-weight:700; min-width:2.5rem; text-align:right; }
</style>
@endpush

@section('content')
<div class="wr-page">

    {{-- ── Back ── --}}
    @include('partials.back-button', ['href' => route('lecturer.reports'), 'label' => 'Reports'])

    {{-- ── Header ── --}}
    <div class="wr-header">
        <h1 class="wr-title">Daily Hafazan Report</h1>
        <p class="wr-subtitle">Student progress versus today's muqarrar — who is on track, behind, or not yet recorded.</p>
    </div>

    {{-- ── Toolbar ── --}}
    <form class="wr-toolbar" method="GET" action="{{ route('lecturer.reports.daily') }}">
        <div class="wr-filter-group">
            <span class="wr-filter-label">Date</span>
            <input class="wr-field-input" type="date" name="date"
                value="{{ $date->format('Y-m-d') }}"
                max="{{ $today->format('Y-m-d') }}"
                onchange="this.form.submit()">
        </div>
        <div style="display:flex;gap:0.5rem;padding-top:1.3rem;">
            <button type="submit" class="wr-btn wr-btn--primary">Apply</button>
            <a href="{{ route('lecturer.reports.daily.pdf', ['date' => $date->format('Y-m-d')]) }}"
               class="wr-btn wr-btn--ghost" style="gap:0.35rem;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:.85rem;height:.85rem;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                PDF
            </a>
        </div>
    </form>

    {{-- ── Date + Muqarrar banner ── --}}
    <div class="wr-info">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
        </svg>
        <span>
            <strong>{{ $date->format('l, d F Y') }}</strong>
            @if($date->isSameDay($today))
                &nbsp;<span style="background:#ede9fe;color:#6d28d9;font-size:0.75rem;font-weight:700;padding:2px 9px;border-radius:20px;">Today</span>
            @endif
            @if($muqarrar)
                &nbsp;&mdash;&nbsp;
                Week {{ $muqarrar->week }}
                &nbsp;&bull;&nbsp; Muqarrar: Pg {{ $muqarrar->start_page }} &rarr; {{ $muqarrar->end_page }}
                @if($checkpoint > 0)
                    &nbsp;&bull;&nbsp; Today's Muqarrar: <strong>Pg {{ $checkpoint }}</strong>
                @endif
            @endif
        </span>
    </div>

    {{-- ── KPIs ── --}}
    <div class="wr-kpis">
        <div class="wr-kpi wr-kpi--green">
            <p class="wr-kpi__label">Completed</p>
            <p class="wr-kpi__value">{{ $completedCount }}</p>
            <p class="wr-kpi__note">Reached end page (Pg {{ $endPage ?: '—' }})</p>
        </div>
        <div class="wr-kpi wr-kpi--blue">
            <p class="wr-kpi__label">On Track</p>
            <p class="wr-kpi__value">{{ $onTrackCount }}</p>
            <p class="wr-kpi__note">At or ahead of muqarrar</p>
        </div>
        <div class="wr-kpi wr-kpi--red">
            <p class="wr-kpi__label">Behind</p>
            <p class="wr-kpi__value">{{ $behindCount }}</p>
            <p class="wr-kpi__note">Below today's muqarrar</p>
        </div>
        <div class="wr-kpi wr-kpi--gray">
            <p class="wr-kpi__label">Not Recorded</p>
            <p class="wr-kpi__value">{{ $notRecordedCount }}</p>
            <p class="wr-kpi__note">No page recorded this week</p>
        </div>
    </div>

    {{-- ── Insight summary ── --}}
    @php
        $goodCount = $completedCount + $onTrackCount;
        $goodPct   = $total > 0 ? round($goodCount / $total * 100) : 0;
    @endphp
    <div class="wr-insight">
        <div class="wr-insight__title">Class Insight &mdash; {{ $date->format('d M Y') }}</div>
        <p class="wr-insight__line">
            <strong>{{ $goodCount }} of {{ $total }} students ({{ $goodPct }}%)</strong> are on track or have completed the week's target.
            @if($behindCount > 0)
                &nbsp;<strong>{{ $behindCount }} {{ $behindCount === 1 ? 'student is' : 'students are' }} behind</strong> today's muqarrar
                @if($checkpoint > 0)(Pg {{ $checkpoint }})@endif — consider following up with them.
            @endif
        </p>
        @if($notRecordedCount > 0)
        <p class="wr-insight__line">
            <strong>{{ $notRecordedCount }} {{ $notRecordedCount === 1 ? 'student has' : 'students have' }}</strong> no hafazan record this week yet.
        </p>
        @endif
    </div>

    {{-- ── Table ── --}}
    <div class="wr-card">
        <div class="wr-card__head">
            <div>
                <h2 class="wr-card__title">Student Progress — {{ $date->format('d M Y') }}</h2>
                <p class="wr-card__subtitle">Latest page reached this week vs. today's muqarrar.</p>
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
                        <th>Latest Page</th>
                        <th>Muqarrar</th>
                        <th>Gap</th>
                        <th>Status</th>
                        <th>Weekly Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $i => $row)
                    @php
                        $badgeClass  = match($row->status) {
                            'Completed'    => 'wr-badge--completed',
                            'On Track'     => 'wr-badge--ontrack',
                            'Behind'       => 'wr-badge--behind',
                            default        => 'wr-badge--none',
                        };
                        $gaugeColor  = match($row->status) {
                            'Completed' => '#16a34a',
                            'On Track'  => '#2563eb',
                            'Behind'    => '#ef4444',
                            default     => '#cbd5e1',
                        };
                        if ($row->gap === null) {
                            $gapLabel = '—';
                            $gapColor = '#94a3b8';
                        } elseif ($row->gap <= 0) {
                            $ahead    = abs($row->gap);
                            $gapLabel = $ahead > 0 ? $ahead . ' pg ahead' : 'On target';
                            $gapColor = '#16a34a';
                        } else {
                            $gapLabel = $row->gap . ' pg behind';
                            $gapColor = '#dc2626';
                        }
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <span class="wr-table__name">{{ $row->student_name }}</span>
                            <span class="wr-table__sub">{{ $row->student_id }}</span>
                        </td>
                        <td>{{ $row->batch_name }}</td>
                        <td>
                            @if($row->latest_page !== null)
                                <span class="wr-table__num">Pg {{ $row->latest_page }}</span>
                            @else
                                <span style="color:#94a3b8;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($row->checkpoint !== null)
                                <span class="wr-table__num">Pg {{ $row->checkpoint }}</span>
                            @else
                                <span style="color:#94a3b8;">—</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size:0.8rem;font-weight:700;color:{{ $gapColor }};">{{ $gapLabel }}</span>
                        </td>
                        <td>
                            <span class="wr-badge {{ $badgeClass }}">{{ $row->status }}</span>
                        </td>
                        <td>
                            @if($row->has_record)
                            <div class="gauge-wrap">
                                <div class="gauge-bar">
                                    <div class="gauge-fill" style="width:{{ $row->gauge_pct }}%;background:{{ $gaugeColor }};"></div>
                                </div>
                                <span class="gauge-pct" style="color:{{ $gaugeColor }};">{{ $row->gauge_pct }}%</span>
                            </div>
                            @else
                                <span style="color:#94a3b8;font-size:0.8rem;">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="wr-table__empty">No students assigned to your account.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
