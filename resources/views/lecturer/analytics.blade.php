@extends('layouts/lecturer')

@section('title', 'Analytics')

@push('styles')
<style>
    /* ── Page ──────────────────────────────────────────────── */
    .analytics-page {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    .analytics-page__header {
        display: flex;
        flex-direction: row;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .analytics-page__header-left {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }
    .analytics-page__title {
        margin: 0;
        font-size: 2.25rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: #0f172a;
    }
    .analytics-page__subtitle {
        margin: 0;
        font-size: 0.9rem;
        color: #94a3b8;
    }

    /* ── Tab Navigation ────────────────────────────────────── */
    .an-tabs {
        display:flex; gap:0.25rem; flex-wrap:wrap;
        background:#f8fafc; border:1px solid #e2e8f0;
        border-radius:1rem; padding:0.35rem;
    }
    .an-tab {
        display:flex; align-items:center; gap:0.45rem;
        padding:0.5rem 1.1rem; border-radius:0.75rem;
        font-size:0.82rem; font-weight:700; cursor:pointer;
        border:none; background:transparent; color:#64748b;
        transition:background 0.15s, color 0.15s, box-shadow 0.15s;
        white-space:nowrap;
    }
    .an-tab:hover { background:#fff; color:#334155; }
    .an-tab.active {
        background:#fff; color:#7c3aed;
        box-shadow:0 2px 8px rgba(124,58,237,0.15);
    }
    .an-tab-dot {
        width:6px; height:6px; border-radius:50%; flex-shrink:0;
    }

    /* ── Section heading ───────────────────────────────────── */
    .analytics-section-title {
        margin: 0 0 1rem;
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .analytics-section-title::before {
        content: '';
        display: inline-block;
        width: 4px;
        height: 1.2rem;
        border-radius: 2px;
        background: #6d28d9;
        flex-shrink: 0;
    }

    /* ── Card ──────────────────────────────────────────────── */
    .ldb-card {
        padding: 1.25rem 1.4rem;
        border: 1px solid #e2e8f0;
        border-radius: 1.25rem;
        background: #fff;
        box-shadow: 0 6px 24px rgba(15,23,42,.05);
    }
    .ldb-card__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: .85rem;
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

    /* ── Panel Cards (admin-style) ─────────────────────────── */
    .panel-card {
        background:#fff; border:1px solid #e2e8f0;
        border-radius:1.25rem;
        box-shadow:0 4px 18px rgba(15,23,42,.05);
    }
    .pc-top { border-top:3px solid #7c3aed; }

    /* ── Two-column grid ───────────────────────────────────── */
    .analytics-grid-2 {
        display: grid;
        gap: 1.25rem;
        grid-template-columns: 1fr;
    }
    @media (min-width: 1024px) {
        .analytics-grid-2 { grid-template-columns: 1fr 1fr; }
    }

    /* ── Chart canvas wrapper ──────────────────────────────── */
    .chart-wrap {
        position: relative;
        height: 260px;
    }

    /* ── Table ─────────────────────────────────────────────── */
    .analytics-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .85rem;
    }
    .analytics-table th {
        text-align: left;
        padding: .55rem .75rem;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #94a3b8;
        border-bottom: 1px solid #e2e8f0;
    }
    .analytics-table td {
        padding: .65rem .75rem;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
    }
    .analytics-table tr:last-child td { border-bottom: none; }
    .analytics-table tr:hover td { background: #f8fafc; }

    /* ── Status / warning badges ───────────────────────────── */
    .badge {
        display: inline-block;
        font-size: .7rem;
        font-weight: 700;
        padding: .18rem .6rem;
        border-radius: 20px;
        white-space: nowrap;
    }
    .badge--ahead   { background: #dcfce7; color: #166534; }
    .badge--ontrack { background: #dbeafe; color: #1d4ed8; }
    .badge--behind  { background: #ffedd5; color: #9a3412; }
    .badge--none    { background: #f1f5f9; color: #64748b; }
    .badge--wl1     { background: #fef3c7; color: #92400e; }
    .badge--wl2     { background: #ffedd5; color: #9a3412; }
    .badge--wl3     { background: #fee2e2; color: #991b1b; }

    /* ── Warning summary stats ─────────────────────────────── */
    .wl-stat-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: .75rem;
        margin-bottom: 1.25rem;
    }
    @media (min-width: 640px) {
        .wl-stat-grid { grid-template-columns: repeat(4, 1fr); }
    }
    .wl-stat {
        padding: 1rem;
        border-radius: 1rem;
        text-align: center;
    }
    .wl-stat--ok   { background: #f0fdf4; border: 1px solid #bbf7d0; }
    .wl-stat--wl1  { background: #fffbeb; border: 1px solid #fde68a; }
    .wl-stat--wl2  { background: #fff7ed; border: 1px solid #fed7aa; }
    .wl-stat--term { background: #fef2f2; border: 1px solid #fecaca; }
    .wl-stat__label {
        margin: 0;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #64748b;
    }
    .wl-stat__value {
        margin: .35rem 0 0;
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: -0.03em;
    }
    .wl-stat--ok   .wl-stat__value { color: #16a34a; }
    .wl-stat--wl1  .wl-stat__value { color: #d97706; }
    .wl-stat--wl2  .wl-stat__value { color: #ea580c; }
    .wl-stat--term .wl-stat__value { color: #dc2626; }

    /* ── Empty state ───────────────────────────────────────── */
    .ldb-empty {
        text-align: center;
        padding: 1.5rem;
        color: #94a3b8;
        font-size: .85rem;
    }

    /* ── Custom scrollbar ──────────────────────────────────── */
    .custom-scrollbar::-webkit-scrollbar { width:6px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:999px; }
    .custom-scrollbar::-webkit-scrollbar-track { background:transparent; }

    /* ── Hafazan 3-section analytics ──────────────────── */
    .haf-kpis { display:grid; grid-template-columns:repeat(4,1fr); gap:.875rem; margin-bottom:1rem; }
    @media(max-width:768px){ .haf-kpis { grid-template-columns:repeat(2,1fr); } }
    .haf-kpi { border-radius:1rem; padding:1rem 1.1rem; }
    .haf-kpi__label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; margin:0 0 .3rem; }
    .haf-kpi__val   { font-size:2rem; font-weight:800; margin:0 0 .25rem; letter-spacing:-.03em; }
    .haf-kpi__note  { font-size:.75rem; color:#94a3b8; margin:0; }
    .haf-kpi--green { background:#f0fdf4; border:1px solid #bbf7d0; }
    .haf-kpi--green .haf-kpi__label { color:#15803d; }
    .haf-kpi--green .haf-kpi__val   { color:#16a34a; }
    .haf-kpi--blue  { background:#eff6ff; border:1px solid #bfdbfe; }
    .haf-kpi--blue  .haf-kpi__label { color:#1d4ed8; }
    .haf-kpi--blue  .haf-kpi__val   { color:#2563eb; }
    .haf-kpi--red   { background:#fef2f2; border:1px solid #fecaca; }
    .haf-kpi--red   .haf-kpi__label { color:#dc2626; }
    .haf-kpi--red   .haf-kpi__val   { color:#dc2626; }
    .haf-kpi--slate { background:#f1f5f9; border:1px solid #e2e8f0; }
    .haf-kpi--slate .haf-kpi__label { color:#64748b; }
    .haf-kpi--slate .haf-kpi__val   { color:#64748b; }
    .haf-bar { height:10px; background:#f1f5f9; border-radius:99px; overflow:hidden; display:flex; margin-bottom:.5rem; }
    .haf-bar__seg { height:100%; }
    .haf-bar__seg--green { background:#16a34a; }
    .haf-bar__seg--blue  { background:#2563eb; }
    .haf-bar__seg--red   { background:#dc2626; }
    .haf-bar__seg--slate { background:#94a3b8; }
    .haf-bar-legend { display:flex; flex-wrap:wrap; gap:.75rem; font-size:.78rem; color:#64748b; }
    .haf-bar-legend__dot { width:8px;height:8px;border-radius:50%;display:inline-block;flex-shrink:0;vertical-align:middle;margin-right:.2rem; }
    .haf-sec-title { display:flex;align-items:center;gap:.5rem;font-size:1rem;font-weight:700;color:#1e293b;margin:0; }
    .haf-sec-title::before { content:'';display:inline-block;width:4px;height:1rem;border-radius:2px;background:#7c3aed;flex-shrink:0; }
    .haf-filter-input { min-height:2rem;padding:0 .75rem;border:1px solid #e2e8f0;border-radius:.65rem;background:#f8fafc;color:#334155;font-size:.82rem;cursor:pointer;outline:none; }
    .haf-filter-input:focus { border-color:#7c3aed;background:#fff; }

    /* Enhanced weekly chips */
    .haf-wk-chip { background:#fff; border:1px solid #e2e8f0; border-radius:1rem; padding:1.1rem 1.25rem; position:relative; overflow:hidden; }
    .haf-wk-chip::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; }
    .haf-wk-chip--green::before { background:#16a34a; }
    .haf-wk-chip--blue::before  { background:#2563eb; }
    .haf-wk-chip--red::before   { background:#dc2626; }
    .haf-wk-chip--slate::before { background:#94a3b8; }
    .haf-wk-chip__num   { font-size:2.25rem; font-weight:800; letter-spacing:-.04em; line-height:1.1; margin:0; }
    .haf-wk-chip--green .haf-wk-chip__num { color:#16a34a; }
    .haf-wk-chip--blue  .haf-wk-chip__num { color:#2563eb; }
    .haf-wk-chip--red   .haf-wk-chip__num { color:#dc2626; }
    .haf-wk-chip--slate .haf-wk-chip__num { color:#64748b; }
    .haf-wk-chip__lbl   { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:#94a3b8; margin:0 0 .35rem; }
    .haf-wk-chip__pct   { font-size:.82rem; font-weight:700; margin:.3rem 0 0; }
    .haf-wk-chip--green .haf-wk-chip__pct { color:#15803d; }
    .haf-wk-chip--blue  .haf-wk-chip__pct { color:#1d4ed8; }
    .haf-wk-chip--red   .haf-wk-chip__pct { color:#b91c1c; }
    .haf-wk-chip--slate .haf-wk-chip__pct { color:#64748b; }
    /* Thick segmented bar */
    .haf-thick-bar { height:22px; background:#f1f5f9; border-radius:99px; overflow:hidden; display:flex; }
    .haf-thick-bar__seg { height:100%; transition:width .4s ease; }
    .haf-thick-bar__seg--green { background:linear-gradient(90deg,#16a34a,#22c55e); }
    .haf-thick-bar__seg--blue  { background:linear-gradient(90deg,#2563eb,#60a5fa); }
    .haf-thick-bar__seg--red   { background:linear-gradient(90deg,#dc2626,#f87171); }
    .haf-thick-bar__seg--slate { background:#cbd5e1; }
    /* Daily + Monthly 2-col grid */
    .haf-daily-grid { display:grid; grid-template-columns:220px 1fr; gap:2rem; align-items:center; padding:.5rem 0; }
    .att-daily-grid { display:grid; grid-template-columns:minmax(210px, 260px) minmax(220px, 1fr); gap:1.75rem; align-items:center; padding:.25rem 0 .5rem; }
    .att-daily-chart { position:relative; width:100%; max-width:260px; aspect-ratio:1; justify-self:center; }
    .att-summary { display:grid; gap:.65rem; max-width:360px; width:100%; }
    .att-summary__row { display:grid; grid-template-columns:auto 1fr auto; align-items:center; gap:.65rem; padding:.72rem .85rem; border:1px solid #e2e8f0; border-radius:.85rem; background:#f8fafc; }
    .att-summary__dot { width:10px; height:10px; border-radius:999px; display:inline-block; }
    .att-summary__label { color:#475569; font-size:.86rem; font-weight:700; }
    .att-summary__value { color:#1e293b; font-size:1.05rem; font-weight:800; }
    @media(max-width:640px){
        .haf-daily-grid { grid-template-columns:1fr; }
        .haf-kpis { grid-template-columns:repeat(2,1fr); }
    }

    /* Attendance 2-column split layout (donut left, stat cards right) */
    .att-split { display:grid; grid-template-columns:35% 1fr; gap:1.5rem; align-items:center; padding:.25rem 0; }
    @media(max-width:600px) { .att-split { grid-template-columns:1fr; } }
    .att-split__donut { position:relative; width:100%; max-width:190px; aspect-ratio:1; justify-self:center; }
    .att-split__stats { display:grid; grid-template-columns:1fr 1fr; gap:.6rem; }
    .att-stat { border-radius:.9rem; padding:.8rem 1rem; }
    .att-stat--green { background:#f0fdf4; border:1px solid #bbf7d0; }
    .att-stat--red   { background:#fef2f2; border:1px solid #fecaca; }
    .att-stat--amber { background:#fffbeb; border:1px solid #fde68a; }
    .att-stat--slate { background:#f8fafc; border:1px solid #e2e8f0; }
    .att-stat__label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; margin:0 0 .25rem; }
    .att-stat--green .att-stat__label { color:#15803d; }
    .att-stat--red   .att-stat__label { color:#dc2626; }
    .att-stat--amber .att-stat__label { color:#b45309; }
    .att-stat--slate .att-stat__label { color:#64748b; }
    .att-stat__value { font-size:1.75rem; font-weight:800; letter-spacing:-.03em; margin:0; line-height:1; }
    .att-stat--green .att-stat__value { color:#16a34a; }
    .att-stat--red   .att-stat__value { color:#dc2626; }
    .att-stat--amber .att-stat__value { color:#d97706; }
    .att-stat--slate .att-stat__value { color:#64748b; }

    /* Attendance middle row: weekly + monthly 50/50 */
    .att-mid-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-top:1rem; }
    @media(max-width:900px) { .att-mid-grid { grid-template-columns:1fr; } }
    .att-mid-grid .ldb-card { padding:1rem 1.2rem; }
    .att-mid-grid .ldb-card__header { margin-bottom:.65rem; }

    /* Monthly attendance KPI grid */
    .att-month-kpis { display:grid; grid-template-columns:repeat(3,1fr); gap:.55rem; margin-bottom:1rem; }
    .att-month-kpi { border-radius:.8rem; padding:.65rem .8rem; }
    .att-month-kpi--green { background:#f0fdf4; border:1px solid #bbf7d0; }
    .att-month-kpi--red   { background:#fef2f2; border:1px solid #fecaca; }
    .att-month-kpi--amber { background:#fffbeb; border:1px solid #fde68a; }
    .att-month-kpi--slate { background:#f8fafc; border:1px solid #e2e8f0; }
    .att-month-kpi__label { font-size:.64rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin:0 0 .15rem; }
    .att-month-kpi--green .att-month-kpi__label { color:#15803d; }
    .att-month-kpi--red   .att-month-kpi__label { color:#dc2626; }
    .att-month-kpi--amber .att-month-kpi__label { color:#b45309; }
    .att-month-kpi--slate .att-month-kpi__label { color:#64748b; }
    .att-month-kpi__value { font-size:1.35rem; font-weight:800; letter-spacing:-.03em; margin:0 0 .1rem; line-height:1; }
    .att-month-kpi--green .att-month-kpi__value { color:#16a34a; }
    .att-month-kpi--red   .att-month-kpi__value { color:#dc2626; }
    .att-month-kpi--amber .att-month-kpi__value { color:#d97706; }
    .att-month-kpi--slate .att-month-kpi__value { color:#64748b; }
    .att-month-kpi__sub { font-size:.69rem; color:#94a3b8; margin:0; font-weight:500; line-height:1.3; }
</style>
@endpush

@section('content')
<div class="analytics-page">

    {{-- ── Back ── --}}
    @include('partials.back-button', ['href' => route('lecturer.dashboard'), 'label' => 'Dashboard'])

    {{-- ── Header ── --}}
    <header class="analytics-page__header">
        <div class="analytics-page__header-left">
            <h1 class="analytics-page__title">Analytics</h1>
            <p class="analytics-page__subtitle">
                {{ $lecturer?->lecturer_name ?? 'Lecturer' }} &mdash; {{ $headerContext }} &mdash; {{ $totalStudents }} students
            </p>
        </div>
        {{-- Date filter --}}
        <div class="flex items-center gap-2 bg-white border border-slate-100 rounded-xl px-3 py-2 shadow-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" stroke-linecap="round"/>
                <path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round"/>
            </svg>
            <input type="date"
                   id="analyticDatePicker"
                   value="{{ $selectedDate->format('Y-m-d') }}"
                   max="{{ $today->format('Y-m-d') }}"
                   onchange="setDate(this.value)"
                   class="text-sm font-semibold text-slate-700 bg-transparent border-none outline-none cursor-pointer" />
        </div>
    </header>

    {{-- ── Tab Navigation ── --}}
    <div class="an-tabs">
        <button class="an-tab" id="tab-btn-cgpa" onclick="showTab('cgpa')">
            <span class="an-tab-dot" style="background:#7c3aed;"></span>
            Academic (CGPA)
        </button>
        <button class="an-tab" id="tab-btn-hafazan" onclick="showTab('hafazan')">
            <span class="an-tab-dot" style="background:#8b5cf6;"></span>
            Hafazan
        </button>
        <button class="an-tab" id="tab-btn-attendance" onclick="showTab('attendance')">
            <span class="an-tab-dot" style="background:#3b82f6;"></span>
            Attendance
        </button>
    </div>

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- TAB 1 — ACADEMIC (CGPA)                               --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div id="tab-cgpa" class="space-y-6">

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="panel-card p-5">
                <p class="text-sm text-slate-400 mb-2">Average CGPA</p>
                <h2 class="text-4xl font-black text-violet-700">{{ number_format($averageCgpa ?? 0, 2) }}</h2>
            </div>
            <div class="panel-card p-5">
                <p class="text-sm text-slate-400 mb-2">Highest CGPA</p>
                <h2 class="text-4xl font-black text-emerald-600">{{ number_format($highestCgpa ?? 0, 2) }}</h2>
            </div>
            <div class="panel-card p-5">
                <p class="text-sm text-slate-400 mb-2">At-Risk Students</p>
                <h2 class="text-4xl font-black text-red-500">{{ $atRiskCount }}</h2>
            </div>
        </div>

        {{-- CGPA Trend --}}
        <div class="panel-card p-5">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-slate-800">CGPA Trend by Semester</h2>
                <p class="text-sm text-slate-400 mt-1">Average cumulative GPA progression</p>
            </div>
            <div style="position:relative; height:320px;">
                <canvas id="cgpaTrendChart"></canvas>
            </div>
        </div>

        {{-- CGPA Distribution --}}
        <div class="panel-card p-5">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-slate-800">CGPA Distribution</h2>
                <p class="text-sm text-slate-400 mt-1">Students CGPA breakdown</p>
            </div>
            <div style="position:relative; height:320px;">
                <canvas id="cgpaDistributionChart"></canvas>
            </div>
        </div>

        {{-- Top + At-Risk CGPA Students --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            <div class="panel-card p-5">
                <div class="flex items-start justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Students Leaderboard</h2>
                        <p class="text-sm text-slate-400 mt-1">Grading based on CGPA</p>
                    </div>
                </div>
                <div class="space-y-3 max-h-[420px] overflow-y-auto pr-1 custom-scrollbar">
                    @forelse ($topStudents as $student)
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-violet-50 border border-violet-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center font-black">★</div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700">{{ $student->student_name }}</p>
                                    <p class="text-xs text-slate-400">{{ $student->batch_name }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-black text-violet-600">{{ number_format($student->cgpa, 2) }}</p>
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">CGPA</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12"><p class="text-sm text-slate-400">No students found.</p></div>
                    @endforelse
                </div>
            </div>

            <div class="panel-card p-5">
                <div class="flex items-start justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">At-Risk Students</h2>
                        <p class="text-sm text-slate-400 mt-1">Students with CGPA below 2.50</p>
                    </div>
                </div>
                <div class="space-y-3 max-h-[420px] overflow-y-auto pr-1 custom-scrollbar">
                    @forelse ($atRiskStudents as $student)
                        <a href="{{ route('lecturer.analytics.intervention', $student->student_id) }}" class="block rounded-2xl bg-red-50 border border-red-100 p-3 transition hover:bg-red-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center font-black">!</div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-700">{{ $student->student_name }}</p>
                                        <p class="text-xs text-slate-400">{{ $student->batch_name }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-black text-red-600">{{ number_format($student->cgpa, 2) }}</p>
                                    <p class="text-[10px] uppercase tracking-wider text-slate-400">CGPA</p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-12"><p class="text-sm text-slate-400">No at-risk students detected.</p></div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>{{-- /tab-cgpa --}}

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- TAB 2 — HAFAZAN ANALYTICS                             --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div id="tab-hafazan" class="hidden">

    {{-- ── 1. Daily Hafazan Analytics ──────────────────────────── --}}
    <article class="ldb-card">
        <div class="ldb-card__header">
            <div>
                <p class="haf-sec-title">Daily Hafazan Analytics</p>
                <p class="ldb-card__subtitle">
                    {{ $hafDailyDate->format('l, d F Y') }}
                    @if ($hafDailyMuq)
                        &mdash; Week {{ $hafDailyMuq->week }},
                        Pg {{ $hafDailyMuq->start_page }}&rarr;{{ $hafDailyMuq->end_page }}
                        @if ($hafDailyChk > 0)
                            &mdash; Today&rsquo;s target: <strong>Pg {{ $hafDailyChk }}</strong>
                        @endif
                    @endif
                </p>
            </div>
        </div>

        @php $dTotal = array_sum($hafDailyCounts); @endphp

        @if ($dTotal > 0)
        <div class="haf-daily-grid">
            {{-- Donut --}}
            <div style="position:relative;height:220px;">
                <canvas id="hafDailyDonut"></canvas>
            </div>
            {{-- Stat list --}}
            <div style="display:flex;flex-direction:column;justify-content:center;gap:0;">
                @php
                    $dailyRows = [
                        ['label'=>'Completed',    'count'=>$hafDailyCounts['Completed'],    'color'=>'#16a34a'],
                        ['label'=>'On Track',     'count'=>$hafDailyCounts['On Track'],     'color'=>'#2563eb'],
                        ['label'=>'Behind',       'count'=>$hafDailyCounts['Behind'],       'color'=>'#dc2626'],
                        ['label'=>'Not Recorded', 'count'=>$hafDailyCounts['Not Recorded'], 'color'=>'#94a3b8'],
                    ];
                @endphp
                @foreach ($dailyRows as $row)
                <div style="display:flex;align-items:center;gap:.7rem;padding:.65rem 0;{{ !$loop->last ? 'border-bottom:1px solid #f1f5f9;' : '' }}">
                    <span style="width:10px;height:10px;border-radius:50%;background:{{ $row['color'] }};flex-shrink:0;display:inline-block;"></span>
                    <span style="flex:1;font-size:.875rem;font-weight:600;color:#475569;">{{ $row['label'] }}</span>
                    <span style="font-size:1.4rem;font-weight:800;color:{{ $row['color'] }};min-width:2rem;text-align:right;">{{ $row['count'] }}</span>
                    <span style="font-size:.78rem;color:#94a3b8;min-width:2.75rem;text-align:right;">{{ round($row['count']/$dTotal*100) }}%</span>
                </div>
                @endforeach
            </div>
        </div>
        @else
            <p style="color:#94a3b8;font-size:.875rem;text-align:center;padding:2.5rem 0;">No data available for this date.</p>
        @endif
    </article>

    {{-- ── 2. Weekly Hafazan Analytics ──────────────────────────── --}}
    <article class="ldb-card" style="margin-top:1rem;">
        <div class="ldb-card__header">
            <div>
                <p class="haf-sec-title">Weekly Hafazan Analytics</p>
                <p class="ldb-card__subtitle">
                    {{ $hafWeeklyLabel }}
                    @if ($hafWeeklyChk > 0)
                        &mdash; Target: <strong>Pg {{ $hafWeeklyChk }}</strong>
                    @endif
                </p>
            </div>
        </div>

        @php $wTotal = array_sum($hafWeeklyCounts); @endphp

        {{-- Enhanced chips --}}
        <div class="haf-kpis">
            @php
                $wkChips = [
                    ['key'=>'Completed',    'label'=>'Completed',    'cls'=>'green', 'note'=>'Reached weekly muqarrar'],
                    ['key'=>'On Track',     'label'=>'On Track',     'cls'=>'blue',  'note'=>'Following this week\'s muqarrar'],
                    ['key'=>'Behind',       'label'=>'Behind',       'cls'=>'red',   'note'=>'Behind this week\'s muqarrar'],
                    ['key'=>'Not Recorded', 'label'=>'Not Recorded', 'cls'=>'slate', 'note'=>'No tasmi\' this week'],
                ];
            @endphp
            @foreach ($wkChips as $chip)
            @php $cnt = $hafWeeklyCounts[$chip['key']]; $pct = $wTotal > 0 ? round($cnt/$wTotal*100) : 0; @endphp
            <div class="haf-wk-chip haf-wk-chip--{{ $chip['cls'] }}">
                <p class="haf-wk-chip__lbl">{{ $chip['label'] }}</p>
                <p class="haf-wk-chip__num">{{ $cnt }}</p>
                <p class="haf-wk-chip__pct">{{ $pct }}%</p>
            </div>
            @endforeach
        </div>

        {{-- Thick segmented bar --}}
        @if ($wTotal > 0)
        <div style="margin-top:.25rem;">
            <div class="haf-thick-bar">
                @if ($hafWeeklyCounts['Completed']    > 0)<div class="haf-thick-bar__seg haf-thick-bar__seg--green" style="width:{{ round($hafWeeklyCounts['Completed']/$wTotal*100) }}%"></div>@endif
                @if ($hafWeeklyCounts['On Track']     > 0)<div class="haf-thick-bar__seg haf-thick-bar__seg--blue"  style="width:{{ round($hafWeeklyCounts['On Track']/$wTotal*100) }}%"></div>@endif
                @if ($hafWeeklyCounts['Behind']       > 0)<div class="haf-thick-bar__seg haf-thick-bar__seg--red"   style="width:{{ round($hafWeeklyCounts['Behind']/$wTotal*100) }}%"></div>@endif
                @if ($hafWeeklyCounts['Not Recorded'] > 0)<div class="haf-thick-bar__seg haf-thick-bar__seg--slate" style="width:{{ round($hafWeeklyCounts['Not Recorded']/$wTotal*100) }}%"></div>@endif
            </div>
            <div class="haf-bar-legend" style="margin-top:.6rem;">
                <span><span class="haf-bar-legend__dot" style="background:#16a34a;"></span>Completed {{ round($hafWeeklyCounts['Completed']/$wTotal*100) }}%</span>
                <span><span class="haf-bar-legend__dot" style="background:#2563eb;"></span>On Track {{ round($hafWeeklyCounts['On Track']/$wTotal*100) }}%</span>
                <span><span class="haf-bar-legend__dot" style="background:#dc2626;"></span>Behind {{ round($hafWeeklyCounts['Behind']/$wTotal*100) }}%</span>
                <span><span class="haf-bar-legend__dot" style="background:#94a3b8;"></span>Not Recorded {{ round($hafWeeklyCounts['Not Recorded']/$wTotal*100) }}%</span>
            </div>
        </div>
        @else
            <p style="color:#94a3b8;font-size:.85rem;text-align:center;padding:.5rem 0;">No data available for the selected week.</p>
        @endif
    </article>

    {{-- ── 3. Monthly Hafazan Analytics ─────────────────────────── --}}
    <article class="ldb-card" style="margin-top:1rem;">
        <div class="ldb-card__header">
            <div>
                <p class="haf-sec-title">Monthly Hafazan Analytics</p>
                <p class="ldb-card__subtitle">
                    Hafazan summary for <strong>{{ $hafMonthlyStart->format('F Y') }}</strong>
                    &mdash; {{ $hafMonthlyStart->format('d M') }} to {{ $hafMonthlyStart->copy()->endOfMonth()->format('d M Y') }}
                </p>
            </div>
        </div>

        @php
            $mTotal      = array_sum($hafMonthlyCounts);
            $mHealthyPct = $mTotal > 0 ? round(($hafMonthlyCounts['Completed'] + $hafMonthlyCounts['On Track']) / $mTotal * 100) : 0;
        @endphp

        @if ($mTotal > 0)
        <div class="haf-daily-grid">
            {{-- Donut --}}
            <div style="position:relative;height:220px;">
                <canvas id="hafMonthlyDonut"></canvas>
            </div>
            {{-- Stat list (legend) --}}
            <div style="display:flex;flex-direction:column;justify-content:center;gap:0;">
                @php
                    $mRows = [
                        ['label'=>'Completed',    'count'=>$hafMonthlyCounts['Completed'],    'color'=>'#16a34a'],
                        ['label'=>'On Track',     'count'=>$hafMonthlyCounts['On Track'],     'color'=>'#2563eb'],
                        ['label'=>'Behind',       'count'=>$hafMonthlyCounts['Behind'],       'color'=>'#dc2626'],
                        ['label'=>'Not Recorded', 'count'=>$hafMonthlyCounts['Not Recorded'], 'color'=>'#94a3b8'],
                    ];
                @endphp
                @foreach ($mRows as $row)
                @php $pct = round($row['count'] / $mTotal * 100); @endphp
                <div style="display:flex;align-items:center;gap:.7rem;padding:.65rem 0;{{ !$loop->last ? 'border-bottom:1px solid #f1f5f9;' : '' }}">
                    <span style="width:10px;height:10px;border-radius:50%;background:{{ $row['color'] }};flex-shrink:0;display:inline-block;"></span>
                    <span style="flex:1;font-size:.875rem;font-weight:600;color:#475569;">{{ $row['label'] }}</span>
                    <span style="font-size:1.4rem;font-weight:800;color:{{ $row['color'] }};min-width:2rem;text-align:right;">{{ $row['count'] }}</span>
                    <span style="font-size:.78rem;color:#94a3b8;min-width:2.75rem;text-align:right;">{{ $pct }}%</span>
                </div>
                @endforeach
            </div>
        </div>
        @else
            <p style="color:#94a3b8;font-size:.875rem;text-align:center;padding:2.5rem 0;">No data available for this month.</p>
        @endif
    </article>

        {{-- ── Top Performers & Students Needing Attention ──────────── --}}
    <div class="analytics-grid-2" style="margin-top:1rem;">

        {{-- Top Performers --}}
        <article class="ldb-card">
            <div class="ldb-card__header">
                <div>
                    <p class="ldb-card__title">Student with highest hafazan progress for selected week</p>
                    <p class="ldb-card__subtitle">
                        {{ $hafWeeklyLabel }} &mdash; highest page reached
                        @if($muqarrarEndPage > 0)
                            (target: Pg {{ $muqarrarEndPage }})
                        @endif
                    </p>
                </div>
                @if($topPerformers->isNotEmpty())
                    <span class="ldb-card__chip">{{ $topPerformers->count() }} students</span>
                @endif
            </div>

            @if ($topPerformers->isEmpty())
                <div class="ldb-empty">No hafazan records for this week yet.</div>
            @else
                <table class="analytics-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Halaqah</th>
                            <th>Page Reached</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topPerformers as $i => $s)
                        <tr>
                            <td style="color:#94a3b8;font-size:.8rem;font-weight:700;">{{ $i + 1 }}</td>
                            <td style="font-weight:600;color:#1e293b;">{{ $s->name }}</td>
                            <td style="color:#64748b;">{{ $s->batch }}</td>
                            <td>
                                <span style="font-weight:800;color:#7c3aed;">Pg {{ $s->page }}</span>
                                @if(($s->target ?? 0) > 0)
                                    <span style="font-size:.75rem;color:#94a3b8;"> / {{ $s->target }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($s->status === 'Completed')
                                    <span class="badge badge--ahead">Completed</span>
                                @elseif ($s->status === 'On Track')
                                    <span class="badge badge--ontrack">On Track</span>
                                @else
                                    <span class="badge badge--behind">Behind</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </article>

        {{-- Students Needing Attention --}}
        <article class="ldb-card">
            <div class="ldb-card__header">
                <div>
                    <p class="ldb-card__title">Students behind muqarrar or with no hafazan record for selected week</p>
                    <p class="ldb-card__subtitle">Behind or no record &mdash; {{ $hafWeeklyLabel }}</p>
                </div>
                @if ($needsAttention->isNotEmpty())
                    <span class="ldb-card__chip" style="background:#ffedd5;color:#9a3412;">
                        {{ $needsAttention->count() }} students
                    </span>
                @endif
            </div>

            @if ($needsAttention->isEmpty())
                <div class="ldb-empty">All students are on track or ahead &mdash; great work!</div>
            @else
                <table class="analytics-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Halaqah</th>
                            <th>Page</th>
                            <th>Gap</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($needsAttention as $s)
                        <tr>
                            <td style="font-weight:600;color:#1e293b;">{{ $s->name }}</td>
                            <td style="color:#64748b;">{{ $s->batch }}</td>
                            <td>{{ $s->page > 0 ? 'Pg ' . $s->page : '—' }}</td>
                            <td style="font-weight:700;">
                                @if ($s->status === 'Not Recorded')
                                    <span style="color:#94a3b8;">—</span>
                                @else
                                    <span style="color:#dc2626;">{{ $s->gap }} pg behind</span>
                                @endif
                            </td>
                            <td>
                                @if ($s->status === 'Behind')
                                    <span class="badge badge--behind">Behind</span>
                                @else
                                    <span class="badge badge--none">Not Recorded</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </article>

    </div>
</div>{{-- /tab-hafazan --}}

    {{-- ══════════════════════════════════════════════════════ --}}
    {{-- TAB 3 — ATTENDANCE ANALYTICS                          --}}
    {{-- ══════════════════════════════════════════════════════ --}}
    <div id="tab-attendance" class="hidden">

    {{-- ── 1. Daily Attendance Analytics ──────────────────────── --}}
    <article class="ldb-card">
        <div class="ldb-card__header">
            <div>
                <p class="haf-sec-title">Daily Attendance Analytics</p>
                <p class="ldb-card__subtitle">{{ $attDailyDate->format('l, d F Y') }}</p>
            </div>
        </div>

        @if ($totalStudents > 0)
        <div class="att-split">
            <div class="att-split__donut">
                <canvas id="attDailyDonut"></canvas>
            </div>
            <div class="att-split__stats">
                <div class="att-stat att-stat--green">
                    <p class="att-stat__label">Present</p>
                    <p class="att-stat__value">{{ $attDailyPresent }}</p>
                </div>
                <div class="att-stat att-stat--red">
                    <p class="att-stat__label">TH</p>
                    <p class="att-stat__value">{{ $attDailyAbsent }}</p>
                </div>
                <div class="att-stat att-stat--amber">
                    <p class="att-stat__label">THS</p>
                    <p class="att-stat__value">{{ $attDailyExcused }}</p>
                </div>
                <div class="att-stat att-stat--slate">
                    <p class="att-stat__label">No Record</p>
                    <p class="att-stat__value">{{ $attDailyNoRecord }}</p>
                </div>
            </div>
        </div>
        @else
            <p style="color:#94a3b8;font-size:.875rem;text-align:center;padding:2rem 0;">No students found.</p>
        @endif
    </article>

    {{-- ── 1b. Subject Attendance (Madah) — compact summary, same day.
         Separate from Tasmik above: Section roster, not this lecturer's
         Halaqah. Always rendered — shows an empty state (matching the
         Monthly/Frequent Absentees cards below) rather than disappearing,
         so "I don't teach any Madah Sections" is visible, not ambiguous. ── --}}
    <article class="ldb-card">
        <div class="ldb-card__header">
            <div>
                <p class="haf-sec-title">Subject Attendance (Madah)</p>
                <p class="ldb-card__subtitle">{{ $attDailyDate->format('l, d F Y') }}</p>
            </div>
            @if ($hasMadahSections)
                <a href="{{ route('lecturer.subjectAttendance') }}" style="font-size:.75rem;font-weight:700;color:#7c3aed;text-decoration:none;">View Sections →</a>
            @endif
        </div>

        @if ($hasMadahSections)
        <div class="att-split__stats" style="grid-template-columns:repeat(3,1fr);">
            <div class="att-stat att-stat--green">
                <p class="att-stat__label">Present</p>
                <p class="att-stat__value">{{ $subjAttPresent }}</p>
            </div>
            <div class="att-stat att-stat--red">
                <p class="att-stat__label">Absent</p>
                <p class="att-stat__value">{{ $subjAttAbsent }}</p>
            </div>
            <div class="att-stat att-stat--slate">
                <p class="att-stat__label">Rate</p>
                <p class="att-stat__value">{{ $subjAttRate !== null ? $subjAttRate . '%' : '—' }}</p>
            </div>
        </div>
        @else
            <p style="color:#94a3b8;font-size:.875rem;text-align:center;padding:2rem 0;">This lecturer does not teach any Madah Sections this semester.</p>
        @endif
    </article>

    {{-- ── 2+3. Weekly + Monthly (50/50) ──────────────────────────── --}}
    <div class="att-mid-grid">

    <article class="ldb-card">
        <div class="ldb-card__header">
            <div>
                <p class="haf-sec-title">Weekly Attendance Overview</p>
                <p class="ldb-card__subtitle">Attendance pattern throughout the selected week &mdash; {{ $attWeekLabel }}</p>
            </div>
        </div>
        <div class="chart-wrap" style="height:240px;">
            <canvas id="attWeeklyChart"></canvas>
        </div>
    </article>

    {{-- ── 3. Monthly Attendance Analytics ────────────────────────── --}}
    <article class="ldb-card">
        <div class="ldb-card__header">
            <div>
                <p class="haf-sec-title">Monthly Attendance Analytics</p>
                <p class="ldb-card__subtitle">Attendance overview for <strong>{{ $attMonthStart->format('F Y') }}</strong></p>
            </div>
        </div>

        @php $attMonthSessionsHas = $attMonthSessionsTotal > 0; @endphp

        @if ($attMonthSessionsHas)
        {{-- 3 KPI Cards --}}
        <div class="att-month-kpis">
            <div class="att-month-kpi att-month-kpi--green">
                <p class="att-month-kpi__label">Present Rate</p>
                <p class="att-month-kpi__value">{{ $attMonthPresentRate }}%</p>
                <p class="att-month-kpi__sub">{{ $attMonthPresent }} / {{ $attMonthSessionsTotal }} sessions</p>
            </div>
            <div class="att-month-kpi att-month-kpi--red">
                <p class="att-month-kpi__label">TH Rate</p>
                <p class="att-month-kpi__value">{{ $attMonthAbsentRate }}%</p>
                <p class="att-month-kpi__sub">{{ $attMonthAbsent }} unexcused absence{{ $attMonthAbsent === 1 ? '' : 's' }}</p>
            </div>
            <div class="att-month-kpi att-month-kpi--amber">
                <p class="att-month-kpi__label">THS Rate</p>
                <p class="att-month-kpi__value">{{ $attMonthExcusedRate }}%</p>
                <p class="att-month-kpi__sub">{{ $attMonthExcused }} excused absence{{ $attMonthExcused === 1 ? '' : 's' }}</p>
            </div>
        </div>

        {{-- Weekly Breakdown Chart --}}
        @if (!empty($attMonthWeekLabels))
        <div>
            <p style="font-size:.67rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.07em;margin:0 0 .6rem;">Weekly Breakdown</p>
            <div style="position:relative;height:175px;">
                <canvas id="attMonthWeekChart"></canvas>
            </div>
        </div>
        @endif

        @else
            <p style="color:#94a3b8;font-size:.875rem;text-align:center;padding:2rem 0;">No data available for this month.</p>
        @endif
    </article>

    </div>{{-- /att-mid-grid --}}

    {{-- ── 4. Frequent Absentees ───────────────────────────────── --}}
    <article class="ldb-card" style="margin-top:1rem;">
        <div class="ldb-card__header">
            <div>
                <p class="ldb-card__title">Frequent Absentees</p>
                <p class="ldb-card__subtitle">Monthly TH accumulation — {{ $wlMonthStart->format('F Y') }}</p>
            </div>
            @if ($frequentAbsentees->isNotEmpty())
                <span class="ldb-card__chip" style="background:#ffedd5;color:#9a3412;">
                    {{ $frequentAbsentees->count() }} student{{ $frequentAbsentees->count() === 1 ? '' : 's' }}
                </span>
            @endif
        </div>

        @if ($frequentAbsentees->isEmpty())
            <div class="ldb-empty">No frequent absentees this month.</div>
        @else
            <div style="overflow-x:auto;">
                <table class="analytics-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Halaqah</th>
                            <th>Total TH</th>
                            <th>Warning Level</th>
                            <th>Att. Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($frequentAbsentees as $s)
                        <tr>
                            <td style="font-weight:600;color:#1e293b;">{{ $s->name }}</td>
                            <td style="color:#64748b;">{{ $s->batch }}</td>
                            <td><span style="font-weight:700;color:#dc2626;">{{ $s->absences }}</span></td>
                            <td>
                                @if ($s->wl >= 3)
                                    <span class="badge badge--wl3">WL3</span>
                                @elseif ($s->wl === 2)
                                    <span class="badge badge--wl2">WL 2</span>
                                @elseif ($s->wl === 1)
                                    <span class="badge badge--wl1">WL 1</span>
                                @else
                                    <span class="badge badge--none">—</span>
                                @endif
                            </td>
                            <td>
                                <span style="font-weight:700;color:{{ $s->att_rate >= 80 ? '#16a34a' : ($s->att_rate >= 60 ? '#ea580c' : '#dc2626') }};">
                                    {{ $s->att_rate }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </article>

    </div>{{-- /tab-attendance --}}


</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
// ── Tab switching ──────────────────────────────────────────
function showTab(name) {
    const tabs = ['cgpa', 'hafazan', 'attendance'];
    // Guard: ignore stale session values referencing removed tabs
    if (!tabs.includes(name)) name = 'cgpa';
    tabs.forEach(t => {
        document.getElementById('tab-' + t).classList.toggle('hidden', t !== name);
        document.getElementById('tab-btn-' + t).classList.toggle('active', t === name);
    });
    sessionStorage.setItem('lec_analytics_tab', name);
    if (name === 'hafazan')    { requestAnimationFrame(() => { initHafDailyDonut(); initHafMonthlyDonut(); }); }
    if (name === 'attendance') { requestAnimationFrame(() => initAttDailyDonut()); }
}

// Restore last active tab
(function () {
    const saved = sessionStorage.getItem('lec_analytics_tab') || 'cgpa';
    showTab(saved);
})();

// ── Chart data ─────────────────────────────────────────────
const ATT_WEEK_LABELS  = @json($attWeekLabels);
const ATT_WEEK_PRESENT = @json($attWeekPresent);
const ATT_WEEK_ABSENT  = @json($attWeekAbsent);
const ATT_WEEK_EXCUSED = @json($attWeekExcused);

const CGPA_TREND_LABELS = @json($cgpaTrend->pluck('semester_name'));
const CGPA_TREND_DATA   = @json($cgpaTrend->pluck('average_cgpa'));

const CGPA_DIST_DATA = [
    {{ $cgpaDistribution['mumtaz'] }},
    {{ $cgpaDistribution['jayyid_jiddan'] }},
    {{ $cgpaDistribution['jayyid'] }},
    {{ $cgpaDistribution['maqbul'] }},
    {{ $cgpaDistribution['rasib'] }},
];

Chart.defaults.font.family = "'Inter', 'ui-sans-serif', system-ui, sans-serif";
Chart.defaults.font.size   = 12;

// ── 1. CGPA Trend line chart ───────────────────────────────
new Chart(document.getElementById('cgpaTrendChart'), {
    type: 'line',
    data: {
        labels: CGPA_TREND_LABELS,
        datasets: [{
            label: 'Average CGPA',
            data: CGPA_TREND_DATA,
            borderColor: '#7c3aed',
            backgroundColor: 'rgba(124,58,237,0.1)',
            fill: true,
            tension: 0.35,
            pointBackgroundColor: '#7c3aed',
            pointRadius: 5,
            pointHoverRadius: 7,
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            datalabels: {
                anchor: 'top', align: 'top',
                color: '#7c3aed', font: { weight: 'bold', size: 11 },
                formatter: v => v ? Number(v).toFixed(2) : '',
            },
        },
        scales: {
            x: { grid: { display: false } },
            y: { beginAtZero: false, min: 0, max: 4, ticks: { stepSize: 0.5 } },
        },
    },
    plugins: [ChartDataLabels],
});

// ── 2. CGPA Distribution bar chart ────────────────────────
    const cgpaDistEl = document.getElementById('cgpaDistributionChart');
    if (cgpaDistEl) {
        const distData = [
            parseInt("{{ $cgpaDistribution['mumtaz'] ?? 0 }}") || 0,
            parseInt("{{ $cgpaDistribution['jayyid_jiddan'] ?? 0 }}") || 0,
            parseInt("{{ $cgpaDistribution['jayyid'] ?? 0 }}") || 0,
            parseInt("{{ $cgpaDistribution['maqbul'] ?? 0 }}") || 0,
            parseInt("{{ $cgpaDistribution['rasib'] ?? 0 }}") || 0
        ];
        new Chart(cgpaDistEl, {
            type: 'bar',
            data: {
                labels: ['Mumtaz / Cemerlang', 'Jayyid Jiddan / Kepujian Atas', 'Jayyid / Kepujian', 'Maqbul / Lulus', 'Rasib / Gagal'],
                datasets: [{
                    label: 'Jumlah Pelajar',
                    data: distData,
                    backgroundColor: ['#B8E6B8','#AFC6FF','#FFE199','#FFF2CC','#E6B8C1'],
                    borderRadius: 8, borderSkipped: false, maxBarThickness: 40
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        anchor: 'end', align: 'top', color: '#475569',
                        font: { weight: 'bold', size: 11 },
                        formatter: v => v > 0 ? v : ''
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 11 } } },
                    y: { beginAtZero: true, ticks: { precision: 0, color: '#94a3b8' }, grid: { color: 'rgba(148,163,184,0.12)' } }
                }
            }
        });
    }

// ── 3. Weekly attendance stacked bar ──────────────────────
const attWeeklyEl = document.getElementById('attWeeklyChart');
if (attWeeklyEl) {
    new Chart(attWeeklyEl, {
        type: 'bar',
        data: {
            labels: ATT_WEEK_LABELS,
            datasets: [
                { label: 'Present', data: ATT_WEEK_PRESENT, backgroundColor: '#16a34a', borderRadius: 4, borderSkipped: false, maxBarThickness: 42 },
                { label: 'TH',      data: ATT_WEEK_ABSENT,  backgroundColor: '#dc2626', borderRadius: 4, borderSkipped: false, maxBarThickness: 42 },
                { label: 'THS',     data: ATT_WEEK_EXCUSED, backgroundColor: '#f59e0b', borderRadius: 4, borderSkipped: false, maxBarThickness: 42 },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' }, tooltip: { mode: 'index' } },
            scales: {
                x: { stacked: true, grid: { display: false } },
                y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } },
            },
        },
    });
}

// ── 4. Attendance daily donut (lazy-initialised when tab shown) ──
const ATT_DAILY_DATA   = [{{ $attDailyPresent }}, {{ $attDailyAbsent }}, {{ $attDailyExcused }}, {{ $attDailyNoRecord }}];
const ATT_DAILY_TOTAL  = {{ $totalStudents }};
const ATT_DAILY_LABELS = ['Present', 'TH', 'THS', 'No Record'];
const ATT_DAILY_COLORS = ['#16a34a', '#dc2626', '#f59e0b', '#94a3b8'];
let   attDailyChartInit = false;

function initAttDailyDonut() {
    if (attDailyChartInit) return;
    const el = document.getElementById('attDailyDonut');
    if (!el || ATT_DAILY_TOTAL === 0) return;
    attDailyChartInit = true;

    const presentPct = ATT_DAILY_TOTAL > 0 ? Math.round(ATT_DAILY_DATA[0] / ATT_DAILY_TOTAL * 100) : 0;

    const centerPlugin = {
        id: 'attDailyCenterText',
        beforeDraw(chart) {
            const { ctx, chartArea } = chart;
            if (!chartArea) return;
            const cx = (chartArea.left + chartArea.right) / 2;
            const cy = (chartArea.top  + chartArea.bottom) / 2;
            ctx.save();
            ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            ctx.font = 'bold 30px Inter, ui-sans-serif, sans-serif';
            ctx.fillStyle = '#1e293b';
            ctx.fillText(presentPct + '%', cx, cy - 12);
            ctx.font = '600 11px Inter, ui-sans-serif, sans-serif';
            ctx.fillStyle = '#64748b';
            ctx.fillText('Present', cx, cy + 10);
            ctx.restore();
        },
    };

    new Chart(el, {
        type: 'doughnut',
        data: {
            labels: ATT_DAILY_LABELS,
            datasets: [{ data: ATT_DAILY_DATA, backgroundColor: ATT_DAILY_COLORS, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }],
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '66%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const total = ctx.dataset.data.reduce((a,b)=>a+b,0);
                            const p = total > 0 ? Math.round(ctx.parsed/total*100) : 0;
                            return ` ${ctx.label}: ${ctx.parsed} student${ctx.parsed===1?'':'s'} (${p}%)`;
                        },
                    },
                },
            },
        },
        plugins: [centerPlugin],
    });
}

// ── Monthly weekly breakdown bar chart ───────────────────
const ATT_MONTH_WEEK_LABELS = @json($attMonthWeekLabels);
const ATT_MONTH_WEEK_RATES  = @json($attMonthWeekRates);

const attMonthWeekEl = document.getElementById('attMonthWeekChart');
if (attMonthWeekEl && ATT_MONTH_WEEK_LABELS.length > 0) {
    new Chart(attMonthWeekEl, {
        type: 'bar',
        data: {
            labels: ATT_MONTH_WEEK_LABELS,
            datasets: [{
                label: 'Attendance Rate',
                data: ATT_MONTH_WEEK_RATES,
                backgroundColor: ATT_MONTH_WEEK_RATES.map(r => r >= 80 ? '#16a34a' : r >= 60 ? '#f59e0b' : '#dc2626'),
                borderRadius: 8,
                borderSkipped: false,
                maxBarThickness: 52,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                datalabels: {
                    anchor: 'end', align: 'top',
                    color: '#475569', font: { weight: '700', size: 11 },
                    formatter: v => v + '%',
                },
                tooltip: { callbacks: { label: ctx => ` Attendance: ${ctx.parsed.y}%` } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#64748b' } },
                y: {
                    beginAtZero: true, max: 100,
                    ticks: { callback: v => v + '%', color: '#94a3b8', stepSize: 25 },
                    grid: { color: 'rgba(148,163,184,.1)' },
                },
            },
        },
        plugins: [ChartDataLabels],
    });
}

// ── Hafazan daily donut (lazy-initialised when tab shown) ──
const HAF_DAILY_DATA    = @json(array_values($hafDailyCounts));
const HAF_DAILY_TOTAL   = {{ array_sum($hafDailyCounts) }};
const HAF_DAILY_LABELS  = ['Completed', 'On Track', 'Behind', 'Not Recorded'];
const HAF_DONUT_COLORS  = ['#16a34a', '#2563eb', '#dc2626', '#94a3b8'];
let   hafDailyChartInit = false;

function initHafDailyDonut() {
    if (hafDailyChartInit) return;
    const el = document.getElementById('hafDailyDonut');
    if (!el || HAF_DAILY_TOTAL === 0) return;
    hafDailyChartInit = true;

    const dominantIdx   = HAF_DAILY_DATA.indexOf(Math.max(...HAF_DAILY_DATA));
    const dominantLabel = HAF_DAILY_LABELS[dominantIdx];
    const dominantPct   = HAF_DAILY_TOTAL > 0 ? Math.round(HAF_DAILY_DATA[dominantIdx] / HAF_DAILY_TOTAL * 100) : 0;

    const centerTextPlugin = {
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
            datasets: [{ data: HAF_DAILY_DATA, backgroundColor: HAF_DONUT_COLORS, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }],
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '66%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const total = ctx.dataset.data.reduce((a,b)=>a+b,0);
                            const pct2  = total > 0 ? Math.round(ctx.parsed/total*100) : 0;
                            return ` ${ctx.label}: ${ctx.parsed} student${ctx.parsed===1?'':'s'} (${pct2}%)`;
                        },
                    },
                },
            },
        },
        plugins: [centerTextPlugin],
    });
}

// ── Hafazan monthly donut (lazy-initialised when tab shown) ──
const HAF_MONTHLY_DATA  = @json(array_values($hafMonthlyCounts));
const HAF_MONTHLY_TOTAL = {{ array_sum($hafMonthlyCounts) }};
let   hafMonthlyChartInit = false;

function initHafMonthlyDonut() {
    if (hafMonthlyChartInit) return;
    const el = document.getElementById('hafMonthlyDonut');
    if (!el || HAF_MONTHLY_TOTAL === 0) return;
    hafMonthlyChartInit = true;

    const mDominantIdx   = HAF_MONTHLY_DATA.indexOf(Math.max(...HAF_MONTHLY_DATA));
    const mDominantLabel = HAF_DAILY_LABELS[mDominantIdx];
    const mDominantPct   = HAF_MONTHLY_TOTAL > 0 ? Math.round(HAF_MONTHLY_DATA[mDominantIdx] / HAF_MONTHLY_TOTAL * 100) : 0;

    const centerPlugin = {
        id: 'hafMonthlyCenterText',
        beforeDraw(chart) {
            const { ctx, chartArea } = chart;
            if (!chartArea) return;
            const cx = (chartArea.left + chartArea.right) / 2;
            const cy = (chartArea.top  + chartArea.bottom) / 2;
            ctx.save();
            ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            ctx.font = 'bold 28px Inter, ui-sans-serif, sans-serif';
            ctx.fillStyle = '#1e293b';
            ctx.fillText(mDominantPct + '%', cx, cy - 10);
            ctx.font = '600 10px Inter, ui-sans-serif, sans-serif';
            ctx.fillStyle = '#64748b';
            ctx.fillText(mDominantLabel, cx, cy + 10);
            ctx.restore();
        },
    };

    new Chart(el, {
        type: 'doughnut',
        data: {
            labels: HAF_DAILY_LABELS,
            datasets: [{ data: HAF_MONTHLY_DATA, backgroundColor: HAF_DONUT_COLORS, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }],
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '66%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const total = ctx.dataset.data.reduce((a,b)=>a+b,0);
                            const p = total > 0 ? Math.round(ctx.parsed/total*100) : 0;
                            return ` ${ctx.label}: ${ctx.parsed} student${ctx.parsed===1?'':'s'} (${p}%)`;
                        },
                    },
                },
            },
        },
        plugins: [centerPlugin],
    });
}

function setDate(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('date', value);
    window.location.href = url.toString();
}
</script>
@endsection
