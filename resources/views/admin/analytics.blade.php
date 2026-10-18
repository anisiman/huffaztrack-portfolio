@extends('layouts.admin')

@section('title', 'Analytics')

@push('styles')
<style>
/* ══════════════════════════════════════════
   Layout
══════════════════════════════════════════ */
.an-page { display:flex; flex-direction:column; gap:1.75rem; }

/* ══════════════════════════════════════════
   Tab Navigation
══════════════════════════════════════════ */
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
.an-tab svg { width:15px; height:15px; flex-shrink:0; }
.an-tab-dot {
    width:6px; height:6px; border-radius:50%; flex-shrink:0;
}

/* ══════════════════════════════════════════
   Batch Filter
══════════════════════════════════════════ */
.filter-tab {
    padding:5px 14px; border-radius:8px; font-size:13px; font-weight:500;
    border:none; cursor:pointer; background:transparent; color:#64748b;
    transition:background 0.15s, color 0.15s;
}
.filter-tab:hover { background:#f1f5f9; }
.filter-tab.active {
    background:#7c3aed; color:#fff;
    box-shadow:0 3px 10px rgba(124,58,237,0.3);
}

/* ══════════════════════════════════════════
   KPI Cards
══════════════════════════════════════════ */
.an-kpi {
    background:#fff; border:1px solid #e2e8f0;
    border-radius:1.1rem; padding:1.25rem 1.4rem;
    box-shadow:0 4px 16px rgba(15,23,42,.05);
}
.an-kpi__label { margin:0 0 0.4rem; font-size:0.72rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.06em; }
.an-kpi__value { margin:0 0 0.25rem; font-size:2rem; font-weight:800; letter-spacing:-0.04em; }
.an-kpi__note  { margin:0; font-size:0.75rem; color:#94a3b8; }
.an-kpi--violet .an-kpi__value { color:#7c3aed; }
.an-kpi--green  .an-kpi__value { color:#16a34a; }
.an-kpi--yellow .an-kpi__value { color:#d97706; }
.an-kpi--red    .an-kpi__value { color:#dc2626; }
.an-kpi--gray   .an-kpi__value { color:#64748b; }
.an-kpi--blue   .an-kpi__value { color:#2563eb; }
.an-kpi--emerald .an-kpi__value { color:#059669; }

/* ══════════════════════════════════════════
   Panel Cards
══════════════════════════════════════════ */
.panel-card {
    background:#fff; border:1px solid #e2e8f0;
    border-radius:1.25rem;
    box-shadow:0 4px 18px rgba(15,23,42,.05);
}
.pc-top { border-top:3px solid #7c3aed; }
.pc-amber { border-top:3px solid #f59e0b; }
.pc-red   { border-top:3px solid #ef4444; }
.pc-blue  { border-top:3px solid #3b82f6; }

.panel-card__head {
    display:flex; align-items:center; justify-content:space-between;
    padding:1.1rem 1.4rem; border-bottom:1px solid #f1f5f9;
}
.panel-card__title { margin:0; font-size:0.95rem; font-weight:700; color:#1e293b; }
.panel-card__sub   { margin:0.2rem 0 0; font-size:0.75rem; color:#94a3b8; }
.panel-card__chip {
    font-size:0.72rem; font-weight:700;
    padding:0.22rem 0.65rem; border-radius:20px;
    background:#f5f3ff; color:#6d28d9;
}

/* ══════════════════════════════════════════
   Progress bars (attendance per batch)
══════════════════════════════════════════ */
.an-bar-track { flex:1; height:8px; background:#f1f5f9; border-radius:99px; overflow:hidden; }
.an-bar-fill  { height:100%; border-radius:99px; transition:width 1s cubic-bezier(0.22,1,0.36,1); width:0; }

/* ══════════════════════════════════════════
   Risk list rows
══════════════════════════════════════════ */
.risk-row {
    display:flex; align-items:center; gap:10px;
    padding:0.65rem 0.9rem; border-radius:10px;
    margin-bottom:4px;
}
.risk-row:last-child { margin-bottom:0; }
.risk-badge {
    display:inline-flex; align-items:center;
    font-size:11px; font-weight:700;
    padding:2px 8px; border-radius:20px; white-space:nowrap; flex-shrink:0;
}

/* ══════════════════════════════════════════
   Scrollable lists
══════════════════════════════════════════ */
.an-scroll { max-height:380px; overflow-y:auto; padding-right:2px; }
.an-scroll::-webkit-scrollbar { width:4px; }
.an-scroll::-webkit-scrollbar-thumb { background:#e2e8f0; border-radius:4px; }

/* ── Lecturer-style hafazan components ───────────────────── */
.ldb-card { padding:1.25rem 1.4rem; border:1px solid #e2e8f0; border-radius:1.25rem; background:#fff; box-shadow:0 6px 24px rgba(15,23,42,.05); }
.ldb-card__header { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom:.85rem; }
.ldb-card__title    { margin:0; font-size:1.05rem; font-weight:700; color:#1e293b; }
.ldb-card__subtitle { margin:.2rem 0 0; font-size:.8rem; color:#94a3b8; }
.ldb-card__chip { flex-shrink:0; background:#f5f3ff; color:#6d28d9; font-size:.75rem; font-weight:700; padding:.25rem .75rem; border-radius:20px; white-space:nowrap; }
.haf-sec-title { display:flex; align-items:center; gap:.5rem; font-size:1rem; font-weight:700; color:#1e293b; margin:0; }
.haf-sec-title::before { content:''; display:inline-block; width:4px; height:1rem; border-radius:2px; background:#7c3aed; flex-shrink:0; }
.haf-filter-input { min-height:2rem; padding:0 .75rem; border:1px solid #e2e8f0; border-radius:.65rem; background:#f8fafc; color:#334155; font-size:.82rem; cursor:pointer; outline:none; }
.haf-filter-input:focus { border-color:#7c3aed; background:#fff; }
/* KPI chips */
.haf-kpis { display:grid; grid-template-columns:repeat(4,1fr); gap:.875rem; margin-bottom:1rem; }
@media(max-width:768px){ .haf-kpis { grid-template-columns:repeat(2,1fr); } }
.haf-wk-chip { background:#fff; border:1px solid #e2e8f0; border-radius:1rem; padding:1.1rem 1.25rem; position:relative; overflow:hidden; }
.haf-wk-chip::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; }
.haf-wk-chip--green::before { background:#16a34a; }
.haf-wk-chip--blue::before  { background:#2563eb; }
.haf-wk-chip--amber::before { background:#d97706; }
.haf-wk-chip--red::before   { background:#dc2626; }
.haf-wk-chip--slate::before { background:#94a3b8; }
.haf-wk-chip__num  { font-size:2.25rem; font-weight:800; letter-spacing:-.04em; line-height:1.1; margin:0; }
.haf-wk-chip--green .haf-wk-chip__num { color:#16a34a; }
.haf-wk-chip--blue  .haf-wk-chip__num { color:#2563eb; }
.haf-wk-chip--amber .haf-wk-chip__num { color:#d97706; }
.haf-wk-chip--red   .haf-wk-chip__num { color:#dc2626; }
.haf-wk-chip--slate .haf-wk-chip__num { color:#64748b; }
.haf-wk-chip__lbl  { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:#94a3b8; margin:0 0 .35rem; }
.haf-wk-chip__pct  { font-size:.82rem; font-weight:700; margin:.3rem 0 0; }
.haf-wk-chip--green .haf-wk-chip__pct { color:#15803d; }
.haf-wk-chip--blue  .haf-wk-chip__pct { color:#1d4ed8; }
.haf-wk-chip--amber .haf-wk-chip__pct { color:#b45309; }
.haf-wk-chip--red   .haf-wk-chip__pct { color:#b91c1c; }
.haf-wk-chip--slate .haf-wk-chip__pct { color:#64748b; }
/* Thick bar */
.haf-thick-bar { height:22px; background:#f1f5f9; border-radius:99px; overflow:hidden; display:flex; }
.haf-thick-bar__seg { height:100%; transition:width .4s ease; }
.haf-thick-bar__seg--green { background:linear-gradient(90deg,#16a34a,#22c55e); }
.haf-thick-bar__seg--blue  { background:linear-gradient(90deg,#2563eb,#60a5fa); }
.haf-thick-bar__seg--amber { background:linear-gradient(90deg,#d97706,#fbbf24); }
.haf-thick-bar__seg--red   { background:linear-gradient(90deg,#dc2626,#f87171); }
.haf-thick-bar__seg--slate { background:#cbd5e1; }
/* Bar legend */
.haf-bar-legend { display:flex; flex-wrap:wrap; gap:.75rem; font-size:.78rem; color:#64748b; }
.haf-bar-legend__dot { width:8px;height:8px;border-radius:50%;display:inline-block;flex-shrink:0;vertical-align:middle;margin-right:.2rem; }
/* Daily grid: donut left, stat list right */
.haf-daily-grid { display:grid; grid-template-columns:220px 1fr; gap:2rem; align-items:center; padding:.5rem 0; }
@media(max-width:640px){ .haf-daily-grid { grid-template-columns:1fr; } }
/* 2-column grid */
.analytics-grid-2 { display:grid; gap:1.25rem; grid-template-columns:1fr; }
@media(min-width:1024px){ .analytics-grid-2 { grid-template-columns:1fr 1fr; } }
/* Table */
.analytics-table { width:100%; border-collapse:collapse; font-size:.85rem; }
.analytics-table th { text-align:left; padding:.55rem .75rem; font-size:.72rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:#94a3b8; border-bottom:1px solid #e2e8f0; }
.analytics-table td { padding:.65rem .75rem; color:#334155; border-bottom:1px solid #f1f5f9; }
.analytics-table tr:last-child td { border-bottom:none; }
.analytics-table tr:hover td { background:#f8fafc; }
/* Status badges */
.haf-badge { display:inline-block; font-size:.7rem; font-weight:700; padding:.18rem .6rem; border-radius:20px; white-space:nowrap; }
.haf-badge--green { background:#dcfce7; color:#166534; }
.haf-badge--amber { background:#fef3c7; color:#92400e; }
.haf-badge--red   { background:#fee2e2; color:#991b1b; }
.haf-badge--none  { background:#f1f5f9; color:#64748b; }
/* Batch card */
.haf-batch-card { border-radius:.9rem; padding:1rem 1.1rem; }
.haf-batch-card--best { background:#f0fdf4; border:1px solid #bbf7d0; }
.haf-batch-card--mid  { background:#f8fafc; border:1px solid #e2e8f0; }
.haf-batch-card--low  { background:#fef2f2; border:1px solid #fecaca; }

/* ══════════════════════════════════════════
   Custom scrollbar (teammate)
══════════════════════════════════════════ */
.custom-scrollbar::-webkit-scrollbar { width:6px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:999px; }
.custom-scrollbar::-webkit-scrollbar-track { background:transparent; }

/* ══════════════════════════════════════════
   Attendance Analytics — Lecturer-style
══════════════════════════════════════════ */
/* Split layout: donut left, stat 2×2 grid right */
.att-split { display:grid; grid-template-columns:35% 1fr; gap:1.5rem; align-items:center; padding:.25rem 0; }
@media(max-width:600px) { .att-split { grid-template-columns:1fr; } }
.att-split__donut { position:relative; width:100%; max-width:190px; aspect-ratio:1; justify-self:center; }
.att-split__stats { display:grid; grid-template-columns:1fr 1fr; gap:.65rem; }

/* Individual stat cards */
.att-stat { border-radius:.9rem; padding:.85rem 1rem; }
.att-stat--green { background:#f0fdf4; border:1px solid #bbf7d0; }
.att-stat--red   { background:#fef2f2; border:1px solid #fecaca; }
.att-stat--amber { background:#fffbeb; border:1px solid #fde68a; }
.att-stat--slate { background:#f8fafc; border:1px solid #e2e8f0; }
.att-stat__label { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; margin:0 0 .25rem; }
.att-stat--green .att-stat__label { color:#15803d; }
.att-stat--red   .att-stat__label { color:#dc2626; }
.att-stat--amber .att-stat__label { color:#b45309; }
.att-stat--slate .att-stat__label { color:#64748b; }
.att-stat__value { font-size:1.9rem; font-weight:800; letter-spacing:-.03em; margin:0; line-height:1; }
.att-stat--green .att-stat__value { color:#16a34a; }
.att-stat--red   .att-stat__value { color:#dc2626; }
.att-stat--amber .att-stat__value { color:#d97706; }
.att-stat--slate .att-stat__value { color:#64748b; }

/* Weekly + Monthly side-by-side */
.att-mid-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-top:1rem; }
@media(max-width:900px) { .att-mid-grid { grid-template-columns:1fr; } }
.att-mid-grid .ldb-card { padding:1rem 1.2rem; }
.att-mid-grid .ldb-card__header { margin-bottom:.65rem; }

/* Monthly KPI chips (3-col) */
.att-month-kpis { display:grid; grid-template-columns:repeat(3,1fr); gap:.55rem; margin-bottom:1rem; }
.att-month-kpi  { border-radius:.8rem; padding:.65rem .85rem; }
.att-month-kpi--green { background:#f0fdf4; border:1px solid #bbf7d0; }
.att-month-kpi--red   { background:#fef2f2; border:1px solid #fecaca; }
.att-month-kpi--amber { background:#fffbeb; border:1px solid #fde68a; }
.att-month-kpi__label { font-size:.64rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin:0 0 .15rem; }
.att-month-kpi--green .att-month-kpi__label { color:#15803d; }
.att-month-kpi--red   .att-month-kpi__label { color:#dc2626; }
.att-month-kpi--amber .att-month-kpi__label { color:#b45309; }
.att-month-kpi__value { font-size:1.4rem; font-weight:800; letter-spacing:-.03em; margin:0 0 .1rem; line-height:1; }
.att-month-kpi--green .att-month-kpi__value { color:#16a34a; }
.att-month-kpi--red   .att-month-kpi__value { color:#dc2626; }
.att-month-kpi--amber .att-month-kpi__value { color:#d97706; }
.att-month-kpi__sub { font-size:.69rem; color:#94a3b8; margin:0; font-weight:500; line-height:1.3; }

/* Warning level badges (for frequent absentees table) */
.badge { display:inline-block; font-size:.7rem; font-weight:700; padding:.18rem .6rem; border-radius:20px; white-space:nowrap; }
.badge--none { background:#f1f5f9; color:#64748b; }
.badge--wl1  { background:#fef3c7; color:#92400e; }
.badge--wl2  { background:#ffedd5; color:#9a3412; }
.badge--wl3  { background:#fee2e2; color:#991b1b; }
</style>
@endpush

@section('content')
@php
    $currentBatch = request('batch', 'Overall');
    $hafBucketMax = max(array_merge(array_values($hafBuckets), [1]));
    $topHafMax    = max((int)($topHafazan->first()?->max_page ?? 0), 1);
@endphp

<div class="an-page">

    {{-- ══ HEADER ══ --}}
    @include('partials.back-button', ['href' => route('admin.dashboard'), 'label' => 'Dashboard'])

    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest mb-1" style="color:#7c3aed;">HuffazTrack &mdash; Admin</p>
            <h1 class="text-4xl font-bold text-slate-800 leading-tight">Analytics</h1>
            <p class="text-sm text-slate-400 mt-0.5">Deep insights across academic, hafazan, and attendance.</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            {{-- Batch filter --}}
            <div class="flex items-center gap-1 bg-white border border-slate-100 rounded-xl p-1 shadow-sm">
                <button class="filter-tab {{ $currentBatch === 'Overall' ? 'active' : '' }}" onclick="setFilter('Overall')">Overall</button>
                @foreach ($batchDistribution as $bKey => $count)
                    <button class="filter-tab {{ $currentBatch === $bKey ? 'active' : '' }}" onclick="setFilter('{{ $bKey }}')">{{ $bKey }}</button>
                @endforeach
            </div>
            {{-- Date filter --}}
            <div class="flex items-center gap-2 bg-white border border-slate-100 rounded-xl px-3 py-2 shadow-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" stroke-linecap="round"/>
                    <path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round"/>
                </svg>
                <input type="date"
                       id="analyticsDatePicker"
                       value="{{ $selectedDate }}"
                       max="{{ \Carbon\Carbon::today()->toDateString() }}"
                       onchange="setDate(this.value)"
                       class="text-sm font-semibold text-slate-700 bg-transparent border-none outline-none cursor-pointer" />
            </div>
        </div>
    </div>

    {{-- ══ TABS ══ --}}
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

    {{-- ═══════════════════════════════════════════════
         TAB 1 — CGPA (teammate, bit-for-bit preserved)
    ═══════════════════════════════════════════════ --}}
    <div id="tab-cgpa" class="space-y-6" style="flex-direction:column;">

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
                        <h2 class="text-lg font-bold text-slate-800">Top Performing Students</h2>
                        <p class="text-sm text-slate-400 mt-1">Top 10 students with highest CGPA</p>
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
                        <div class="text-center py-12"><p class="text-sm text-slate-400">No top students found.</p></div>
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
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-red-50 border border-red-100">
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
                    @empty
                        <div class="text-center py-12"><p class="text-sm text-slate-400">No at-risk students detected.</p></div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>{{-- /tab-cgpa --}}


    {{-- ═══════════════════════════════════════════════
         TAB 2 — Hafazan Analytics (lecturer style)
    ═══════════════════════════════════════════════ --}}
    <div id="tab-hafazan" class="hidden" style="display:none;flex-direction:column;gap:1.25rem;">

    @php
        $hafDailyTotal   = array_sum($hafDailyCounts);
        $hafWeeklyTotal  = array_sum($hafWeeklyCounts);
        $hafMonthlyTotal = array_sum($hafMonthlyCounts);
    @endphp

    {{-- ── 1. Daily Hafazan Analytics ──────────────────────────── --}}
    <article class="ldb-card">
        <div class="ldb-card__header">
            <div>
                <p class="haf-sec-title">Daily Hafazan Analytics</p>
                <p class="ldb-card__subtitle">
                    {{ \Carbon\Carbon::parse($selectedDate)->format('l, d F Y') }}
                    @if ($hafCheckpoint)
                        &mdash; Week target: <strong>Pg {{ $hafCheckpoint }}</strong>
                    @endif
                    &mdash; All batches &amp; lecturers
                </p>
            </div>
            <span class="ldb-card__chip">{{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</span>
        </div>

        @if ($hafDailyTotal > 0)
        <div class="haf-daily-grid">
            <div style="position:relative;height:220px;">
                <canvas id="hafAdminDailyDonut"></canvas>
            </div>
            <div style="display:flex;flex-direction:column;justify-content:center;gap:0;">
                @php
                    $dAdminRows = [
                        ['label'=>'Completed',    'count'=>$hafDailyCounts['Completed'],    'color'=>'#16a34a'],
                        ['label'=>'On Track',     'count'=>$hafDailyCounts['On Track'],     'color'=>'#2563eb'],
                        ['label'=>'Behind',       'count'=>$hafDailyCounts['Behind'],       'color'=>'#dc2626'],
                        ['label'=>'Not Recorded', 'count'=>$hafDailyCounts['Not Recorded'], 'color'=>'#94a3b8'],
                    ];
                @endphp
                @foreach ($dAdminRows as $row)
                @php $pct = $hafDailyTotal > 0 ? round($row['count']/$hafDailyTotal*100) : 0; @endphp
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
            <p style="color:#94a3b8;font-size:.875rem;text-align:center;padding:2.5rem 0;">No hafazan data for the selected date.</p>
        @endif
    </article>

    {{-- ── 2. Weekly Hafazan Analytics ──────────────────────────── --}}
    <article class="ldb-card">
        <div class="ldb-card__header">
            <div>
                <p class="haf-sec-title">Weekly Hafazan Analytics</p>
                <p class="ldb-card__subtitle">
                    {{ $hafWeekLabel }}
                    @if ($hafCheckpoint)
                        &mdash; Week target: <strong>Pg {{ $hafCheckpoint }}</strong>
                    @endif
                    &mdash; All batches &amp; lecturers
                </p>
            </div>
            <a href="{{ route('admin.reports.hafazan.weekly') }}"
               style="flex-shrink:0;font-size:.78rem;font-weight:700;color:#7c3aed;text-decoration:none;background:#f5f3ff;border:1px solid #ddd6fe;padding:.25rem .75rem;border-radius:.5rem;">
                Full Report →
            </a>
        </div>

        <div class="haf-kpis">
            @php
                $wkAdminChips = [
                    ['key'=>'Completed',    'label'=>'Completed',    'cls'=>'green'],
                    ['key'=>'On Track',     'label'=>'On Track',     'cls'=>'blue'],
                    ['key'=>'Behind',       'label'=>'Behind',       'cls'=>'red'],
                    ['key'=>'Not Recorded', 'label'=>'Not Recorded', 'cls'=>'slate'],
                ];
            @endphp
            @foreach ($wkAdminChips as $chip)
            @php $cnt = $hafWeeklyCounts[$chip['key']]; $pct = $hafWeeklyTotal > 0 ? round($cnt/$hafWeeklyTotal*100) : 0; @endphp
            <div class="haf-wk-chip haf-wk-chip--{{ $chip['cls'] }}">
                <p class="haf-wk-chip__lbl">{{ $chip['label'] }}</p>
                <p class="haf-wk-chip__num">{{ $cnt }}</p>
                <p class="haf-wk-chip__pct">{{ $pct }}%</p>
            </div>
            @endforeach
        </div>

        @if ($hafWeeklyTotal > 0)
        <div>
            <div class="haf-thick-bar">
                @if ($hafWeeklyCounts['Completed']    > 0)<div class="haf-thick-bar__seg haf-thick-bar__seg--green" style="width:{{ round($hafWeeklyCounts['Completed']/$hafWeeklyTotal*100) }}%"></div>@endif
                @if ($hafWeeklyCounts['On Track']     > 0)<div class="haf-thick-bar__seg haf-thick-bar__seg--blue"  style="width:{{ round($hafWeeklyCounts['On Track']/$hafWeeklyTotal*100) }}%"></div>@endif
                @if ($hafWeeklyCounts['Behind']       > 0)<div class="haf-thick-bar__seg haf-thick-bar__seg--red"   style="width:{{ round($hafWeeklyCounts['Behind']/$hafWeeklyTotal*100) }}%"></div>@endif
                @if ($hafWeeklyCounts['Not Recorded'] > 0)<div class="haf-thick-bar__seg haf-thick-bar__seg--slate" style="width:{{ round($hafWeeklyCounts['Not Recorded']/$hafWeeklyTotal*100) }}%"></div>@endif
            </div>
            <div class="haf-bar-legend" style="margin-top:.6rem;">
                <span><span class="haf-bar-legend__dot" style="background:#16a34a;"></span>Completed {{ round($hafWeeklyCounts['Completed']/$hafWeeklyTotal*100) }}%</span>
                <span><span class="haf-bar-legend__dot" style="background:#2563eb;"></span>On Track {{ round($hafWeeklyCounts['On Track']/$hafWeeklyTotal*100) }}%</span>
                <span><span class="haf-bar-legend__dot" style="background:#dc2626;"></span>Behind {{ round($hafWeeklyCounts['Behind']/$hafWeeklyTotal*100) }}%</span>
                <span><span class="haf-bar-legend__dot" style="background:#94a3b8;"></span>Not Recorded {{ round($hafWeeklyCounts['Not Recorded']/$hafWeeklyTotal*100) }}%</span>
            </div>
        </div>
        @else
            <p style="color:#94a3b8;font-size:.85rem;text-align:center;padding:.5rem 0;">No hafazan data for the selected week.</p>
        @endif
    </article>

    {{-- ── 3. Monthly Hafazan Analytics ─────────────────────────── --}}
    <article class="ldb-card">
        <div class="ldb-card__header">
            <div>
                <p class="haf-sec-title">Monthly Hafazan Analytics</p>
                <p class="ldb-card__subtitle">
                    Hafazan summary for <strong>{{ $hafMonthLabel }}</strong>
                    &mdash; month-to-date &mdash; All batches &amp; lecturers
                </p>
            </div>
            <span class="ldb-card__chip">{{ $hafMonthLabel }}</span>
        </div>

        @if ($hafMonthlyTotal > 0)
        <div class="haf-daily-grid">
            <div style="position:relative;height:220px;">
                <canvas id="hafAdminMonthlyDonut"></canvas>
            </div>
            <div style="display:flex;flex-direction:column;justify-content:center;gap:0;">
                @php
                    $mAdminRows = [
                        ['label'=>'Completed',    'count'=>$hafMonthlyCounts['Completed'],    'color'=>'#16a34a'],
                        ['label'=>'On Track',     'count'=>$hafMonthlyCounts['On Track'],     'color'=>'#2563eb'],
                        ['label'=>'Behind',       'count'=>$hafMonthlyCounts['Behind'],       'color'=>'#dc2626'],
                        ['label'=>'Not Recorded', 'count'=>$hafMonthlyCounts['Not Recorded'], 'color'=>'#94a3b8'],
                    ];
                @endphp
                @foreach ($mAdminRows as $row)
                @php $pct = $hafMonthlyTotal > 0 ? round($row['count']/$hafMonthlyTotal*100) : 0; @endphp
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
            <p style="color:#94a3b8;font-size:.875rem;text-align:center;padding:2.5rem 0;">No hafazan data for this month.</p>
        @endif
    </article>

    {{-- ── 4. Top Performers + Needing Attention ── --}}
    <div class="analytics-grid-2">

        {{-- Top Performers --}}
        <article class="ldb-card">
            <div class="ldb-card__header">
                <div>
                    <p class="ldb-card__title">Top Performers</p>
                    <p class="ldb-card__subtitle">Highest pages reached &mdash; {{ $periodLabel }}</p>
                </div>
                @if ($topHafazan->isNotEmpty())
                    <span class="ldb-card__chip">{{ $topHafazan->count() }} students</span>
                @endif
            </div>
            @if ($topHafazan->isEmpty())
                <p style="color:#94a3b8;font-size:.875rem;text-align:center;padding:1.5rem 0;">No records for this period.</p>
            @else
            @php $topHafMax = max((int)($topHafazan->first()?->max_page ?? 0), 1); @endphp
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Batch</th>
                        <th>Page Reached</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($topHafazan as $i => $s)
                    <tr>
                        <td style="color:#94a3b8;font-size:.8rem;font-weight:700;">{{ $i + 1 }}</td>
                        <td style="font-weight:600;color:#1e293b;">{{ $s->student_name }}</td>
                        <td style="color:#64748b;font-size:.82rem;">{{ $s->batch_name }}</td>
                        <td>
                            <span style="font-weight:800;color:#7c3aed;">Pg {{ number_format($s->max_page) }}</span>
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
                    <p class="ldb-card__title">Students Needing Attention</p>
                    <p class="ldb-card__subtitle">Behind or no record &mdash; {{ $periodLabel }}</p>
                </div>
                @if ($hafBehindStudents->isNotEmpty())
                    <span class="ldb-card__chip" style="background:#ffedd5;color:#9a3412;">{{ $hafBehindStudents->count() }} students</span>
                @endif
            </div>
            @if ($hafBehindStudents->isEmpty())
                <p style="color:#94a3b8;font-size:.875rem;text-align:center;padding:1.5rem 0;">All students are on track — great work!</p>
            @else
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Batch</th>
                        <th>Gap</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($hafBehindStudents as $s)
                    @php
                        $badgeCls = $s->gap > 5 ? 'haf-badge--red' : 'haf-badge--amber';
                        $badgeLbl = $s->gap > 5 ? 'Critical' : 'Attention';
                    @endphp
                    <tr>
                        <td style="font-weight:600;color:#1e293b;">{{ $s->student_name }}</td>
                        <td style="color:#64748b;font-size:.82rem;">{{ $s->batch_name }}</td>
                        <td style="font-weight:700;color:#dc2626;">{{ $s->gap }} pg behind</td>
                        <td><span class="haf-badge {{ $badgeCls }}">{{ $badgeLbl }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </article>

    </div>

    </div>{{-- /tab-hafazan --}}


    {{-- ═══════════════════════════════════════════════
         TAB 3 — Attendance Analytics (lecturer style)
    ═══════════════════════════════════════════════ --}}
    <div id="tab-attendance" class="hidden" style="display:none;flex-direction:column;gap:1.25rem;">

    {{-- ── 1. Daily Attendance Analytics ─────────────────────────── --}}
    <article class="ldb-card">
        <div class="ldb-card__header">
            <div>
                <p class="haf-sec-title">Daily Attendance Analytics</p>
                <p class="ldb-card__subtitle">
                    {{ \Carbon\Carbon::parse($selectedDate)->format('l, d F Y') }} &mdash; All batches &amp; lecturers
                </p>
            </div>
            <a href="{{ route('admin.reports.attendance.daily') }}"
               style="flex-shrink:0;font-size:.78rem;font-weight:700;color:#7c3aed;text-decoration:none;background:#f5f3ff;border:1px solid #ddd6fe;padding:.25rem .75rem;border-radius:.5rem;">
                Full Report →
            </a>
        </div>

        @if ($attTotal > 0)
        <div class="att-split">
            {{-- Donut --}}
            <div class="att-split__donut">
                <canvas id="attAdminDailyDonut"></canvas>
            </div>
            {{-- 2×2 stat cards --}}
            <div class="att-split__stats">
                <div class="att-stat att-stat--green">
                    <p class="att-stat__label">Present</p>
                    <p class="att-stat__value">{{ $attDailyPresent }}</p>
                </div>
                <div class="att-stat att-stat--red">
                    <p class="att-stat__label">TH (Absent)</p>
                    <p class="att-stat__value">{{ $attDailyAbsent }}</p>
                </div>
                <div class="att-stat att-stat--amber">
                    <p class="att-stat__label">THS (Excused)</p>
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

    {{-- ── 1b. Subject Attendance (Madah) — system-wide daily summary.
         Separate from Tasmik above: Section rosters, not batch/Halaqah
         rosters. Always shown — an empty state instead of hiding, matching
         the rest of this page's cards. ── --}}
    <article class="ldb-card">
        <div class="ldb-card__header">
            <div>
                <p class="haf-sec-title">Subject Attendance (Madah)</p>
                <p class="ldb-card__subtitle">
                    {{ \Carbon\Carbon::parse($selectedDate)->format('l, d F Y') }} &mdash; all Madah Sections
                </p>
            </div>
            <a href="{{ route('admin.subjectAttendance') }}"
               style="flex-shrink:0;font-size:.78rem;font-weight:700;color:#7c3aed;text-decoration:none;background:#f5f3ff;border:1px solid #ddd6fe;padding:.25rem .75rem;border-radius:.5rem;">
                Full Report →
            </a>
        </div>

        @if (($subjAttPresent + $subjAttAbsent) > 0)
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
            <p style="color:#94a3b8;font-size:.875rem;text-align:center;padding:2rem 0;">No Subject Attendance records for this date.</p>
        @endif
    </article>

    {{-- ── 2+3. Weekly + Monthly side-by-side ─────────────────────── --}}
    <div class="att-mid-grid">

    {{-- Weekly Overview --}}
    <article class="ldb-card">
        <div class="ldb-card__header">
            <div>
                <p class="haf-sec-title">Weekly Attendance Overview</p>
                <p class="ldb-card__subtitle">{{ $adminWeekLabel }} &mdash; All batches</p>
            </div>
            <div style="display:flex;align-items:center;gap:.5rem;flex-shrink:0;">
                @if ($attAdminWeekRate !== null)
                    <span class="ldb-card__chip">{{ $attAdminWeekRate }}% present</span>
                @endif
                <a href="{{ route('admin.reports.attendance.weekly') }}"
                   style="flex-shrink:0;font-size:.78rem;font-weight:700;color:#7c3aed;text-decoration:none;background:#f5f3ff;border:1px solid #ddd6fe;padding:.25rem .75rem;border-radius:.5rem;">
                    Full Report →
                </a>
            </div>
        </div>
        <div style="position:relative;height:240px;">
            <canvas id="attAdminWeeklyChart"></canvas>
        </div>
        <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-top:.75rem;font-size:.78rem;color:#64748b;">
            <span><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#16a34a;margin-right:.3rem;vertical-align:middle;"></span>Present</span>
            <span><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#dc2626;margin-right:.3rem;vertical-align:middle;"></span>TH (Absent)</span>
            <span><span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#f59e0b;margin-right:.3rem;vertical-align:middle;"></span>THS (Excused)</span>
        </div>
    </article>

    {{-- Monthly Overview --}}
    <article class="ldb-card">
        <div class="ldb-card__header">
            <div>
                <p class="haf-sec-title">Monthly Attendance Analytics</p>
                <p class="ldb-card__subtitle">{{ $adminMonthLabel }} &mdash; All batches</p>
            </div>
            <a href="{{ route('admin.reports.attendance.monthly') }}"
               style="flex-shrink:0;font-size:.78rem;font-weight:700;color:#7c3aed;text-decoration:none;background:#f5f3ff;border:1px solid #ddd6fe;padding:.25rem .75rem;border-radius:.5rem;">
                Full Report →
            </a>
        </div>

        @if ($attAdminMonthSessions > 0)
        <div class="att-month-kpis">
            <div class="att-month-kpi att-month-kpi--green">
                <p class="att-month-kpi__label">Present Rate</p>
                <p class="att-month-kpi__value">{{ $attAdminMonthPresentRate }}%</p>
                <p class="att-month-kpi__sub">{{ $attAdminMonthPresent }} sessions</p>
            </div>
            <div class="att-month-kpi att-month-kpi--red">
                <p class="att-month-kpi__label">TH Rate</p>
                <p class="att-month-kpi__value">{{ $attAdminMonthAbsentRate }}%</p>
                <p class="att-month-kpi__sub">{{ $attAdminMonthAbsent }} absent</p>
            </div>
            <div class="att-month-kpi att-month-kpi--amber">
                <p class="att-month-kpi__label">THS Rate</p>
                <p class="att-month-kpi__value">{{ $attAdminMonthExcusedRate }}%</p>
                <p class="att-month-kpi__sub">{{ $attAdminMonthExcused }} excused</p>
            </div>
        </div>

        @if (!empty($attAdminMonthWeekLabels))
        <div>
            <p style="font-size:.67rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.07em;margin:0 0 .6rem;">Weekly Breakdown</p>
            <div style="position:relative;height:175px;">
                <canvas id="attAdminMonthWeekChart"></canvas>
            </div>
        </div>
        @endif
        @else
            <p style="color:#94a3b8;font-size:.875rem;text-align:center;padding:2rem 0;">No data available for {{ $adminMonthLabel }}.</p>
        @endif
    </article>

    </div>{{-- /att-mid-grid --}}

    {{-- ── 4. Frequent Absentees ───────────────────────────────────── --}}
    <article class="ldb-card">
        <div class="ldb-card__header">
            <div>
                <p class="ldb-card__title">Frequent Absentees</p>
                <p class="ldb-card__subtitle">
                    Monthly TH accumulation &mdash; {{ $adminMonthStart->format('F Y') }} &mdash; All batches &amp; lecturers
                </p>
            </div>
            @if ($attFrequentAbsentees->isNotEmpty())
                <span class="ldb-card__chip" style="background:#ffedd5;color:#9a3412;">
                    {{ $attFrequentAbsentees->count() }} student{{ $attFrequentAbsentees->count() === 1 ? '' : 's' }}
                </span>
            @endif
        </div>

        @if ($attFrequentAbsentees->isEmpty())
            <p style="color:#94a3b8;font-size:.875rem;text-align:center;padding:1.5rem 0;">No frequent absentees this month.</p>
        @else
        <div style="overflow-x:auto;">
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Batch</th>
                        <th>Total TH</th>
                        <th>Warning Level</th>
                        <th>Att. Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attFrequentAbsentees as $s)
                    <tr>
                        <td style="font-weight:600;color:#1e293b;">{{ $s->student_name }}</td>
                        <td style="color:#64748b;font-size:.82rem;">{{ $s->batch_name }}</td>
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

</div>{{-- /an-page --}}
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
Chart.register(ChartDataLabels);

/* ──────────────────────────────────────────
   Tab System
────────────────────────────────────────── */
const TAB_IDS = ['cgpa', 'hafazan', 'attendance'];

function showTab(name) {
    TAB_IDS.forEach(id => {
        const el = document.getElementById('tab-' + id);
        if (el) { el.classList.add('hidden'); el.style.display = ''; }
        document.getElementById('tab-btn-' + id)?.classList.remove('active');
    });
    const active = document.getElementById('tab-' + name);
    if (active) { active.classList.remove('hidden'); active.style.display = 'flex'; }
    document.getElementById('tab-btn-' + name)?.classList.add('active');

    if (name === 'hafazan') {
        requestAnimationFrame(() => { initHafAdminDailyDonut(); initHafAdminMonthlyDonut(); });
    }
    if (name === 'attendance') {
        requestAnimationFrame(() => { initAttAdminDailyDonut(); });
    }

    try { sessionStorage.setItem('adminAnalyticsTab', name); } catch(e) {}
}

// Restore last tab (or default to cgpa)
(function() {
    let active = 'cgpa';
    try { active = sessionStorage.getItem('adminAnalyticsTab') || 'cgpa'; } catch(e) {}
    if (!TAB_IDS.includes(active)) active = 'cgpa';
    showTab(active);
})();

/* ──────────────────────────────────────────
   Batch filter
────────────────────────────────────────── */
function setFilter(batch) {
    const url = new URL(window.location.href);
    if (batch === 'Overall') url.searchParams.delete('batch');
    else url.searchParams.set('batch', batch);
    window.location.href = url.toString();
}

function setDate(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('date', value);
    url.searchParams.delete('period');
    window.location.href = url.toString();
}

/* ── Hafazan admin donuts (daily + monthly, lazy-init when tab shown) ── */
const HAF_ADMIN_LABELS = ['Completed', 'On Track', 'Behind', 'Not Recorded'];
const HAF_ADMIN_COLORS = ['#16a34a', '#2563eb', '#dc2626', '#94a3b8'];

const HAF_ADMIN_DAILY_DATA  = [{{ $hafDailyCounts['Completed'] }}, {{ $hafDailyCounts['On Track'] }}, {{ $hafDailyCounts['Behind'] }}, {{ $hafDailyCounts['Not Recorded'] }}];
const HAF_ADMIN_DAILY_TOTAL = {{ array_sum($hafDailyCounts) }};
let   hafAdminDailyChartInit = false;

function initHafAdminDailyDonut() {
    if (hafAdminDailyChartInit) return;
    const el = document.getElementById('hafAdminDailyDonut');
    if (!el || HAF_ADMIN_DAILY_TOTAL === 0) return;
    hafAdminDailyChartInit = true;

    const domIdx = HAF_ADMIN_DAILY_DATA.indexOf(Math.max(...HAF_ADMIN_DAILY_DATA));
    const domPct = Math.round(HAF_ADMIN_DAILY_DATA[domIdx] / HAF_ADMIN_DAILY_TOTAL * 100);

    new Chart(el, {
        type: 'doughnut',
        data: { labels: HAF_ADMIN_LABELS, datasets: [{ data: HAF_ADMIN_DAILY_DATA, backgroundColor: HAF_ADMIN_COLORS, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }] },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '66%',
            plugins: {
                legend: { display: false },
                datalabels: { display: false },
                tooltip: { callbacks: { label: ctx => { const t = ctx.dataset.data.reduce((a,b)=>a+b,0); return ` ${ctx.label}: ${ctx.parsed} students (${t>0?Math.round(ctx.parsed/t*100):0}%)`; } } },
            },
        },
        plugins: [{
            id: 'hafAdminDailyCenter',
            beforeDraw(chart) {
                const { ctx, chartArea } = chart; if (!chartArea) return;
                const cx = (chartArea.left+chartArea.right)/2, cy = (chartArea.top+chartArea.bottom)/2;
                ctx.save(); ctx.textAlign='center'; ctx.textBaseline='middle';
                ctx.font='bold 30px Inter,sans-serif'; ctx.fillStyle='#1e293b';
                ctx.fillText(domPct+'%', cx, cy-12);
                ctx.font='600 11px Inter,sans-serif'; ctx.fillStyle='#64748b';
                ctx.fillText(HAF_ADMIN_LABELS[domIdx], cx, cy+10);
                ctx.restore();
            },
        }],
    });
}

const HAF_ADMIN_MONTHLY_DATA  = [{{ $hafMonthlyCounts['Completed'] }}, {{ $hafMonthlyCounts['On Track'] }}, {{ $hafMonthlyCounts['Behind'] }}, {{ $hafMonthlyCounts['Not Recorded'] }}];
const HAF_ADMIN_MONTHLY_TOTAL = {{ array_sum($hafMonthlyCounts) }};
let   hafAdminMonthlyChartInit = false;

function initHafAdminMonthlyDonut() {
    if (hafAdminMonthlyChartInit) return;
    const el = document.getElementById('hafAdminMonthlyDonut');
    if (!el || HAF_ADMIN_MONTHLY_TOTAL === 0) return;
    hafAdminMonthlyChartInit = true;

    const domIdx = HAF_ADMIN_MONTHLY_DATA.indexOf(Math.max(...HAF_ADMIN_MONTHLY_DATA));
    const domPct = Math.round(HAF_ADMIN_MONTHLY_DATA[domIdx] / HAF_ADMIN_MONTHLY_TOTAL * 100);

    new Chart(el, {
        type: 'doughnut',
        data: { labels: HAF_ADMIN_LABELS, datasets: [{ data: HAF_ADMIN_MONTHLY_DATA, backgroundColor: HAF_ADMIN_COLORS, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }] },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '66%',
            plugins: {
                legend: { display: false },
                datalabels: { display: false },
                tooltip: { callbacks: { label: ctx => { const t = ctx.dataset.data.reduce((a,b)=>a+b,0); return ` ${ctx.label}: ${ctx.parsed} students (${t>0?Math.round(ctx.parsed/t*100):0}%)`; } } },
            },
        },
        plugins: [{
            id: 'hafAdminMonthlyCenter',
            beforeDraw(chart) {
                const { ctx, chartArea } = chart; if (!chartArea) return;
                const cx = (chartArea.left+chartArea.right)/2, cy = (chartArea.top+chartArea.bottom)/2;
                ctx.save(); ctx.textAlign='center'; ctx.textBaseline='middle';
                ctx.font='bold 28px Inter,sans-serif'; ctx.fillStyle='#1e293b';
                ctx.fillText(domPct+'%', cx, cy-12);
                ctx.font='600 11px Inter,sans-serif'; ctx.fillStyle='#64748b';
                ctx.fillText(HAF_ADMIN_LABELS[domIdx], cx, cy+10);
                ctx.restore();
            },
        }],
    });
}

/* ── Attendance daily donut (lazy-init when tab shown) ── */
const ATT_ADMIN_DAILY_DATA   = [{{ $attDailyPresent }}, {{ $attDailyAbsent }}, {{ $attDailyExcused }}, {{ $attDailyNoRecord }}];
const ATT_ADMIN_DAILY_TOTAL  = {{ $attTotal }};
const ATT_ADMIN_DAILY_LABELS = ['Present', 'TH', 'THS', 'No Record'];
const ATT_ADMIN_DAILY_COLORS = ['#16a34a', '#dc2626', '#f59e0b', '#94a3b8'];
let   attAdminDailyChartInit = false;

function initAttAdminDailyDonut() {
    if (attAdminDailyChartInit) return;
    const el = document.getElementById('attAdminDailyDonut');
    if (!el || ATT_ADMIN_DAILY_TOTAL === 0) return;
    attAdminDailyChartInit = true;

    const presentPct = ATT_ADMIN_DAILY_TOTAL > 0 ? Math.round(ATT_ADMIN_DAILY_DATA[0] / ATT_ADMIN_DAILY_TOTAL * 100) : 0;

    const centerPlugin = {
        id: 'attAdminDailyCenter',
        beforeDraw(chart) {
            const { ctx, chartArea } = chart;
            if (!chartArea) return;
            const cx = (chartArea.left + chartArea.right) / 2;
            const cy = (chartArea.top  + chartArea.bottom) / 2;
            ctx.save();
            ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            ctx.font = 'bold 28px Inter, ui-sans-serif, sans-serif';
            ctx.fillStyle = '#1e293b';
            ctx.fillText(presentPct + '%', cx, cy - 10);
            ctx.font = '600 11px Inter, ui-sans-serif, sans-serif';
            ctx.fillStyle = '#64748b';
            ctx.fillText('Present', cx, cy + 10);
            ctx.restore();
        },
    };

    new Chart(el, {
        type: 'doughnut',
        data: {
            labels: ATT_ADMIN_DAILY_LABELS,
            datasets: [{ data: ATT_ADMIN_DAILY_DATA, backgroundColor: ATT_ADMIN_DAILY_COLORS, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }],
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '66%',
            plugins: {
                legend: { display: false },
                datalabels: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const p = total > 0 ? Math.round(ctx.parsed / total * 100) : 0;
                            return ` ${ctx.label}: ${ctx.parsed} students (${p}%)`;
                        },
                    },
                },
            },
        },
        plugins: [centerPlugin],
    });
}

/* ──────────────────────────────────────────
   Charts (init after DOM ready)
────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {

    /* ── 1. CGPA Trend (teammate) ── */
    const cgpaTrendEl = document.getElementById('cgpaTrendChart');
    if (cgpaTrendEl) {
        const ctx = cgpaTrendEl.getContext('2d');
        let gradient = '#7c3aed';
        if (ctx) {
            gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(124,58,237,0.25)');
            gradient.addColorStop(1, 'rgba(124,58,237,0)');
        }
        const trendLabels = {!! json_encode($cgpaTrend->pluck('semester_name') ?? []) !!};
        const trendData   = {!! json_encode($cgpaTrend->pluck('average_cgpa') ?? []) !!};

        new Chart(cgpaTrendEl, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Average CGPA',
                    data: trendData.map(v => parseFloat(v) || 0),
                    borderColor: '#7c3aed',
                    backgroundColor: gradient,
                    fill: true, tension: 0.4, borderWidth: 3,
                    pointRadius: 4, pointBackgroundColor: '#7c3aed'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        align: 'top', anchor: 'end', color: '#475569',
                        font: { weight: 'bold', size: 11 },
                        formatter: v => { const n = parseFloat(v); return isNaN(n) ? '0.00' : n.toFixed(2); }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94a3b8' } },
                    y: { beginAtZero: true, max: 4, ticks: { stepSize: 0.5, color: '#94a3b8' }, grid: { color: 'rgba(148,163,184,0.12)' } }
                }
            }
        });
    }

    /* ── 2. CGPA Distribution (teammate) ── */
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

    /* ── 3. Attendance: weekly stacked bar (current week) ── */
    const attAdminWeeklyEl = document.getElementById('attAdminWeeklyChart');
    if (attAdminWeeklyEl) {
        new Chart(attAdminWeeklyEl, {
            type: 'bar',
            data: {
                labels: @json($attAdminWeekLabels),
                datasets: [
                    { label: 'Present', data: @json($attAdminWeekPresent), backgroundColor: '#16a34a', borderRadius: 4, borderSkipped: false, maxBarThickness: 42 },
                    { label: 'TH',      data: @json($attAdminWeekAbsent),  backgroundColor: '#dc2626', borderRadius: 4, borderSkipped: false, maxBarThickness: 42 },
                    { label: 'THS',     data: @json($attAdminWeekExcused), backgroundColor: '#f59e0b', borderRadius: 4, borderSkipped: false, maxBarThickness: 42 },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: { display: false },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: { stacked: true, grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10 } } },
                    y: { stacked: true, beginAtZero: true, ticks: { precision: 0, color: '#94a3b8' }, grid: { color: 'rgba(148,163,184,.1)' } },
                },
            },
        });
    }

    /* ── 4. Attendance: monthly weekly-breakdown bar ── */
    const attAdminMonthWeekEl = document.getElementById('attAdminMonthWeekChart');
    if (attAdminMonthWeekEl) {
        const mwRates = @json($attAdminMonthWeekRates);
        new Chart(attAdminMonthWeekEl, {
            type: 'bar',
            data: {
                labels: @json($attAdminMonthWeekLabels),
                datasets: [{
                    label: 'Attendance Rate',
                    data: mwRates,
                    backgroundColor: mwRates.map(r => r >= 80 ? '#16a34a' : r >= 60 ? '#f59e0b' : '#dc2626'),
                    borderRadius: 8, borderSkipped: false, maxBarThickness: 52,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
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
});
</script>
@endpush
