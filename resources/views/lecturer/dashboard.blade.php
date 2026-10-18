@extends('layouts/lecturer')

@section('title', 'Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
    <style>
        /* ── Ambient background ─────────────────────────── */
        .ldb-ambient {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background:
                radial-gradient(ellipse 70% 55% at 5% 0%,   rgba(124,58,237,0.07) 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 95% 95%, rgba(67,56,202,0.05) 0%, transparent 55%),
                radial-gradient(ellipse 40% 30% at 65% 15%, rgba(139,92,246,0.04) 0%, transparent 50%);
        }

        /* ── Page ────────────────────────────────────────── */
        .lecturer-dashboard {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        .lecturer-dashboard__header {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .lecturer-dashboard__header-left {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }
        .lecturer-dashboard__title {
            margin: 0;
            font-size: 2.25rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #0f172a;
        }
        .lecturer-dashboard__subtitle {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 500;
            color: #94a3b8;
        }

        /* ── KPI grid (4 cards) ─────────────────────────── */
        .lecturer-dashboard__grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(2, 1fr);
        }
        @media (min-width: 768px) {
            .lecturer-dashboard__grid { grid-template-columns: repeat(3, 1fr); }
        }

        /* ── Card icon colours ──────────────────────────── */
        .dashboard-card--students  .dashboard-card__icon { background:#ede9fe; color:#6d28d9; }
        .dashboard-card--attendance .dashboard-card__icon { background:#dcfce7; color:#059669; }
        .dashboard-card--progress   .dashboard-card__icon { background:#ffedd5; color:#ea580c; }
        .dashboard-card--warning    .dashboard-card__icon { background:#fee2e2; color:#dc2626; }

        /* ── Highlight banner ───────────────────────────── */
        .lecturer-highlight {
            display: grid;
            gap: 1rem;
            padding: 1.5rem;
            border: 1px solid transparent;
            border-radius: 1.5rem;
            background: linear-gradient(135deg, #4c1d95 0%, #1e1b4b 50%, #0f172a 100%);
            color: #fff;
            box-shadow: 0 18px 40px rgba(76,29,149,.22), 0 4px 12px rgba(15,23,42,.10);
        }
        .lecturer-highlight__eyebrow {
            margin: 0;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: #cbd5e1;
        }
        .lecturer-highlight__title {
            margin: .35rem 0 0;
            font-size: 1.2rem;
            font-weight: 700;
            line-height: 1.3;
        }
        .lecturer-highlight__stats {
            display: grid;
            gap: .75rem;
            grid-template-columns: repeat(2, 1fr);
        }
        @media (min-width: 768px) {
            .lecturer-highlight__stats { grid-template-columns: repeat(4, 1fr); }
        }
        .lecturer-highlight__stat {
            padding: 1rem;
            border-radius: 1rem;
            background: rgba(255,255,255,.09);
        }
        .lecturer-highlight__stat-label {
            margin: 0;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #94a3b8;
        }
        .lecturer-highlight__stat-value {
            margin: .4rem 0 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }
        .lecturer-highlight__stat-value--warning { color: #fca5a5; }

        /* ── Monitor card ───────────────────────────────── */
        .ldb-card {
            padding: 1.4rem 1.5rem;
            border: 1px solid #e2e8f0;
            border-top: 3px solid #7c3aed;
            border-radius: 1.4rem;
            background: linear-gradient(180deg, #faf5ff 0%, #ffffff 40%);
            box-shadow: 0 10px 30px rgba(124,58,237,.08);
        }
        .ldb-card__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .ldb-card__title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
            color: #1e293b;
        }
        .ldb-card__subtitle {
            margin: .2rem 0 0;
            font-size: .8rem;
            color: #94a3b8;
        }
        .ldb-card__chip {
            flex-shrink: 0;
            background: #f5f3ff;
            color: #6d28d9;
            font-size: .75rem;
            font-weight: 700;
            padding: .25rem .75rem;
            border-radius: 20px;
            white-space: nowrap;
        }

        /* ── Warning list ───────────────────────────────── */
        .ldb-monitor-list {
            display: flex;
            flex-direction: column;
            gap: .6rem;
        }
        .ldb-monitor-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .75rem 1rem;
            border: 1px solid #f1f5f9;
            border-radius: .9rem;
            background: #f8fafc;
        }
        .ldb-monitor-item:hover { background: #f5f3ff; border-color: #ddd6fe; }
        .ldb-monitor-name {
            margin: 0;
            font-size: .9rem;
            font-weight: 700;
            color: #1e293b;
        }
        .ldb-monitor-meta {
            margin: .15rem 0 0;
            font-size: .76rem;
            color: #94a3b8;
        }
        .ldb-wl-badge {
            flex-shrink: 0;
            font-size: .7rem;
            font-weight: 700;
            padding: .2rem .65rem;
            border-radius: 20px;
            white-space: nowrap;
        }
        .ldb-wl-badge--1 { background:#fef3c7; color:#92400e; }
        .ldb-wl-badge--2 { background:#ffedd5; color:#9a3412; }
        .ldb-wl-badge--3 { background:#fee2e2; color:#991b1b; }
        .ldb-empty {
            text-align: center;
            padding: 1.5rem;
            color: #94a3b8;
            font-size: .85rem;
        }

        /* ── Academic section ───────────────────────────── */
        .ac-section-heading {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0 0 1rem;
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
        }
        .ac-section-heading::before {
            content: '';
            display: inline-block;
            width: 4px;
            height: 1.2rem;
            border-radius: 2px;
            background: #6d28d9;
            flex-shrink: 0;
        }
        .ac-kpi-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: repeat(1, 1fr);
        }
        @media (min-width: 640px)  { .ac-kpi-grid { grid-template-columns: repeat(3, 1fr); } }
        .ac-kpi {
            padding: 1.25rem 1.4rem;
            border: 1px solid #e2e8f0;
            border-radius: 1.1rem;
            background: #fff;
            box-shadow: 0 4px 16px rgba(15,23,42,.05);
        }
        .ac-kpi__label { margin: 0 0 .4rem; font-size: .72rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .06em; }
        .ac-kpi__value { margin: 0 0 .25rem; font-size: 2rem; font-weight: 800; letter-spacing: -0.04em; }
        .ac-kpi--violet {
            background: linear-gradient(135deg, #faf5ff, #fff);
            border-left: 4px solid #7c3aed;
        }
        .ac-kpi--violet .ac-kpi__value { color: #7c3aed; }
        .ac-kpi--green {
            background: linear-gradient(135deg, #f0fdf4, #fff);
            border-left: 4px solid #16a34a;
        }
        .ac-kpi--green  .ac-kpi__value { color: #16a34a; }
        .ac-kpi--red {
            background: linear-gradient(135deg, #fff5f5, #fff);
            border-left: 4px solid #dc2626;
        }
        .ac-kpi--red    .ac-kpi__value { color: #dc2626; }

        .ac-charts-grid {
            display: grid;
            gap: 1.25rem;
            grid-template-columns: 1fr;
        }
        @media (min-width: 1024px) { .ac-charts-grid { grid-template-columns: 1fr 1fr; } }

        .ac-student-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .75rem 1rem;
            border-radius: 1rem;
            margin-bottom: .5rem;
        }
        .ac-student-row:last-child { margin-bottom: 0; }
        .ac-student-avatar {
            width: 2.5rem; height: 2.5rem;
            border-radius: .75rem;
            display: flex; align-items: center; justify-content: center;
            font-size: .85rem; font-weight: 900; flex-shrink: 0;
        }
        .ac-student-name  { margin: 0; font-size: .9rem; font-weight: 700; color: #1e293b; }
        .ac-student-batch { margin: .15rem 0 0; font-size: .75rem; color: #94a3b8; }
        .ac-cgpa-value    { font-size: 1.15rem; font-weight: 900; letter-spacing: -0.02em; }
        .ac-cgpa-label    { font-size: .65rem; text-transform: uppercase; letter-spacing: .08em; color: #94a3b8; text-align: right; }

        /* ── Today's Tasks strip ────────────────────────── */
        .ldb-tasks {
            padding: 1.1rem 1.4rem;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #7c3aed;
            border-radius: 1.2rem;
            background: linear-gradient(135deg, #faf5ff 0%, #fff 60%);
            box-shadow: 0 4px 16px rgba(124,58,237,.07);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .ldb-tasks__title {
            margin: 0;
            font-size: .9rem;
            font-weight: 700;
            color: #1e293b;
        }
        .ldb-tasks__hint {
            margin: .2rem 0 0;
            font-size: .76rem;
            color: #94a3b8;
        }
        .ldb-tasks__actions {
            display: flex;
            gap: .6rem;
            flex-wrap: wrap;
        }
        .ldb-tasks__btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .45rem 1rem;
            border-radius: .7rem;
            font-size: .82rem;
            font-weight: 700;
            text-decoration: none;
            transition: opacity .15s;
        }
        .ldb-tasks__btn:hover { opacity: .85; }
        .ldb-tasks__btn--primary  { background: #7c3aed; color: #fff; }
        .ldb-tasks__btn--secondary { background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }
    </style>
@endpush

@section('content')
<div class="ldb-ambient"></div>
<div class="relative z-10">
<div class="lecturer-dashboard">

    {{-- ── Header ── --}}
    <header class="lecturer-dashboard__header">
        <div class="lecturer-dashboard__header-left">
            <h1 class="lecturer-dashboard__title">Dashboard</h1>
            <p class="lecturer-dashboard__subtitle">
                {{ $lecturer?->lecturer_name ?? 'Lecturer' }} &mdash; {{ $today->format('d/m/Y') }}
            </p>
        </div>
        {{-- Date filter --}}
        <div class="flex items-center gap-2 bg-white border border-slate-100 rounded-2xl px-4 py-2 shadow-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" stroke-linecap="round"/>
                <path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round"/>
            </svg>
            <input type="date"
                   id="dashboardDatePicker"
                   value="{{ $selectedDate->format('Y-m-d') }}"
                   max="{{ $today->format('Y-m-d') }}"
                   onchange="setDate(this.value)"
                   class="text-base font-semibold text-slate-700 bg-transparent border-none outline-none cursor-pointer" />
        </div>
    </header>

    {{-- ── Today's Tasks ── --}}
    <section class="ldb-tasks" aria-label="Today's tasks">
        <div>
            <p class="ldb-tasks__title">Today's Tasks</p>
            <p class="ldb-tasks__hint">Start by taking attendance or recording today's hafazan progress.</p>
        </div>
        <div class="ldb-tasks__actions">
            <a href="{{ route('lecturer.attendance') }}" class="ldb-tasks__btn ldb-tasks__btn--primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
                Take Attendance
            </a>
            <a href="{{ route('lecturer.hifz') }}" class="ldb-tasks__btn ldb-tasks__btn--secondary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                </svg>
                Record Hafazan
            </a>
        </div>
    </section>

    {{-- ── KPI Cards ── --}}
    <section class="lecturer-dashboard__grid" aria-label="KPI metrics">

        @php
            $hafazanOnTrackUrl = route('lecturer.hifz', ['status' => 'Ahead,On-track,Completed', 'date' => $selectedDate->format('Y-m-d')]);
            $hafazanBehindUrl  = route('lecturer.hifz', ['status' => 'Behind,Not Recorded', 'date' => $selectedDate->format('Y-m-d')]);
        @endphp

        {{-- Assigned Students --}}
        <article class="dashboard-card dashboard-card--students">
            <span class="card-glow" aria-hidden="true"></span>
            <a href="{{ route('lecturer.students') }}" class="dashboard-card__link">
                <div class="dashboard-card__icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.742-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.331 0-4.512-.666-6.358-1.816a6.75 6.75 0 0 1-.037-.666v-.031m12.395-3.199a5.25 5.25 0 0 0-10.395 0m10.395 0a5.25 5.25 0 0 1 0 0ZM15 7.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM7.5 10.5a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                </div>
                <p class="dashboard-card__label">Total Students</p>
                <p class="dashboard-card__value">{{ $totalStudents }}</p>
            </a>
            <div class="dashboard-card__details">
                <a href="{{ $hafazanOnTrackUrl }}" class="dashboard-card__detail-link">Hafazan On Track: {{ $onTrackCount }} students</a>
                <a href="{{ $hafazanBehindUrl }}" class="dashboard-card__detail-link">Hafazan Behind: {{ $behindCount }} students</a>
            </div>
        </article>

        {{-- Attendance Rate --}}
        <a href="{{ route('lecturer.attendance', ['date' => $selectedDate->format('Y-m-d')]) }}" class="dashboard-card dashboard-card--attendance">
            <span class="card-glow" aria-hidden="true"></span>
            <div class="dashboard-card__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
            </div>
            <p class="dashboard-card__label">Attendance Rate</p>
            <div class="dashboard-card__headline">
                <p class="dashboard-card__value">{{ $attendanceRate }}%</p>
                <span class="dashboard-card__badge dashboard-card__badge--positive">{{ $periodLabel }}</span>
            </div>
            @php
                $attCaption = $presentToday . ' present records (' . $selectedDate->format('d M Y') . ')';
            @endphp
            <p class="dashboard-card__caption">{{ $attCaption }}</p>
        </a>

        {{-- Students Meeting Muqarrar --}}
        <a href="{{ $hafazanOnTrackUrl }}" class="dashboard-card dashboard-card--progress">
            <span class="card-glow" aria-hidden="true"></span>
            <div class="dashboard-card__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 2.25 2.25L15 9.75m6 2.25a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <p class="dashboard-card__label">Students Meeting Muqarrar</p>
            <div class="dashboard-card__headline">
                <p class="dashboard-card__value">{{ $onTrackCount }} / {{ $totalStudents }}</p>
                <span class="dashboard-card__badge dashboard-card__badge--positive">Reached Muqarrar</span>
            </div>
            <p class="dashboard-card__caption" style="margin-top:.75rem;font-size:.82rem;color:#94a3b8;">
                Met target until {{ $weekLabel }}
            </p>
        </a>

    </section>

    {{-- ═══════════════════════════
         ACADEMIC ANALYTICS (CGPA) 
        ═════════════════════════════--}}
    <section aria-label="Academic analytics">

        <h2 class="ac-section-heading">Academic Overview (CGPA)</h2>

        {{-- KPI row --}}
        <div class="ac-kpi-grid" style="margin-bottom:1.25rem;">
            <div class="ac-kpi ac-kpi--violet">
                <p class="ac-kpi__label">Average CGPA</p>
                <p class="ac-kpi__value">{{ number_format($averageCgpa ?? 0, 2) }}</p>
                <p style="margin:0;font-size:.75rem;color:#94a3b8;">Across your students</p>
            </div>
            <div class="ac-kpi ac-kpi--green">
                <p class="ac-kpi__label">Highest CGPA</p>
                <p class="ac-kpi__value">{{ number_format($highestCgpa ?? 0, 2) }}</p>
                <p style="margin:0;font-size:.75rem;color:#94a3b8;">Best performing student</p>
            </div>
            <div class="ac-kpi ac-kpi--red">
                <p class="ac-kpi__label">At-Risk Students</p>
                <p class="ac-kpi__value">{{ $atRiskCount }}</p>
                <p style="margin:0;font-size:.75rem;color:#94a3b8;">CGPA below 2.50</p>
            </div>
        </div>

        {{-- At-risk + Top students row --}}
        <div class="ac-charts-grid">

            {{-- Top Students Leaderboard --}}
            <article class="ldb-card">
                <div class="ldb-card__header">
                    <div>
                        <p class="ldb-card__title">Top Students Leaderboard</p>
                        <p class="ldb-card__subtitle">Highest latest CGPA</p>
                    </div>
                    <a href="{{ route('lecturer.analytics') }}" class="ldb-card__chip" style="text-decoration:none;">See full →</a>
                </div>
                @forelse ($topStudents as $index => $student)
                    <div class="ac-student-row" style="background:#f5f3ff;border:1px solid #ddd6fe;">
                        <div style="display:flex;align-items:center;gap:.75rem;">
                            <div class="ac-student-avatar" style="background:#ede9fe;color:#6d28d9;">#{{ $index + 1 }}</div>
                            <div>
                                <p class="ac-student-name">{{ $student->student_name }}</p>
                                <p class="ac-student-batch">{{ $student->batch_name }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="ac-cgpa-value" style="color:#7c3aed;">{{ number_format($student->cgpa, 2) }}</p>
                            <p class="ac-cgpa-label">CGPA</p>
                        </div>
                    </div>
                @empty
                    <div class="ldb-empty">No result data available.</div>
                @endforelse
            </article>

            {{-- At-Risk Students --}}
            <article class="ldb-card">
                <div class="ldb-card__header">
                    <div>
                        <p class="ldb-card__title">At-Risk Students</p>
                        <p class="ldb-card__subtitle">CGPA below 2.50</p>
                    </div>
                    @if($atRiskCount > 0)
                        <span class="ldb-card__chip" style="background:#fee2e2;color:#dc2626;">{{ $atRiskCount }} at risk</span>
                    @endif
                </div>
                @forelse ($atRiskStudents as $student)
                    <div class="ac-student-row" style="background:#fff7f7;border:1px solid #fecaca;">
                        <div style="display:flex;align-items:center;gap:.75rem;">
                            <div class="ac-student-avatar" style="background:#fee2e2;color:#dc2626;">!</div>
                            <div>
                                <p class="ac-student-name">{{ $student->student_name }}</p>
                                <p class="ac-student-batch">{{ $student->batch_name }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="ac-cgpa-value" style="color:#dc2626;">{{ number_format($student->cgpa, 2) }}</p>
                            <p class="ac-cgpa-label">CGPA</p>
                        </div>
                    </div>
                @empty
                    <div class="ldb-empty">No at-risk students detected.</div>
                @endforelse
            </article>

        </div>
    </section>

</div>
</div>
@endsection

@section('scripts')
<script>
function setDate(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('date', value);
    window.location.href = url.toString();
}
</script>
@endsection
