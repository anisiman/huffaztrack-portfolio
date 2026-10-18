@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
    <style>
        /* ══════════════════════════════════════════
           Background Decoration
        ══════════════════════════════════════════ */
        .db-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background:
                radial-gradient(ellipse 70% 55% at 5% 0%, rgba(124, 58, 237, 0.09) 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 95% 95%, rgba(67, 56, 202, 0.07) 0%, transparent 55%),
                radial-gradient(ellipse 40% 30% at 65% 15%, rgba(139, 92, 246, 0.05) 0%, transparent 50%);
        }

        /* ══════════════════════════════════════════
           Stat Cards
        ══════════════════════════════════════════ */
        .stat-card {
            border-radius: 20px;
            padding: 22px;
            display: flex;
            flex-direction: column;
            height: 100%;
            position: relative;
            animation: dbFadeUp 0.55s ease both;
            transition: transform 0.25s, box-shadow 0.25s;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        /* ── Circling light — rotating glow ring around the card border on hover ── */
        @property --card-angle {
            syntax: '<angle>';
            inherits: false;
            initial-value: 0deg;
        }

        .card-glow {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 1;
            border-radius: inherit;
            padding: 2px;
            pointer-events: none;
            opacity: 0;
            background: conic-gradient(from var(--card-angle, 0deg),
                transparent 0deg,
                transparent 270deg,
                rgba(196, 181, 253, 0.9) 320deg,
                #fff 345deg,
                rgba(196, 181, 253, 0.9) 360deg);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            transition: opacity 0.3s ease;
        }

        .stat-card:hover .card-glow {
            opacity: 1;
            animation: card-glow-spin 2.2s linear infinite;
        }

        @keyframes card-glow-spin {
            to {
                --card-angle: 360deg;
            }
        }

        .sc-featured {
            background: linear-gradient(135deg, #7c3aed 0%, #4338ca 100%);
            box-shadow: 0 8px 32px rgba(124, 58, 237, 0.28), 0 2px 6px rgba(0, 0, 0, 0.08);
            border: none;
            position: relative;
            overflow: hidden;
        }

        .sc-featured::before {
            content: '';
            position: absolute;
            top: -70px;
            right: -70px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.07);
            pointer-events: none;
        }

        .sc-featured::after {
            content: '';
            position: absolute;
            bottom: -90px;
            left: -40px;
            width: 190px;
            height: 190px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
            pointer-events: none;
        }

        .sc-featured:hover {
            box-shadow: 0 14px 44px rgba(124, 58, 237, 0.36), 0 3px 8px rgba(0, 0, 0, 0.10);
        }

        .sc-white {
            background: #fff;
            border: 1px solid rgba(226, 232, 240, 0.7);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 8px 24px rgba(124, 58, 237, 0.05);
        }

        .sc-white:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.07), 0 16px 40px rgba(124, 58, 237, 0.10);
        }

        .sc-green {
            border-top: 4px solid #10b981;
        }

        .sc-red {
            border-top: 4px solid #ef4444;
        }

        .sc-blue {
            border-top: 4px solid #3b82f6;
        }

        /* ══════════════════════════════════════════
           Icons
        ══════════════════════════════════════════ */
        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon svg {
            width: 20px;
            height: 20px;
        }

        .icon-glass {
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(8px);
        }

        /* ══════════════════════════════════════════
           Filter tabs & Badges
        ══════════════════════════════════════════ */
        .filter-tab {
            padding: 5px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            background: transparent;
            color: #64748b;
            transition: background 0.15s, color 0.15s;
        }

        .filter-tab:hover {
            background: #f1f5f9;
        }

        .filter-tab.active {
            background: #7c3aed;
            color: #fff;
            box-shadow: 0 3px 10px rgba(124, 58, 237, 0.35);
        }

        .gender-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11.5px;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 20px;
        }

        /* ══════════════════════════════════════════
           Panel Cards
        ══════════════════════════════════════════ */
        .panel-card {
            background: #fff;
            border: 1px solid rgba(226, 232, 240, 0.7);
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 8px 24px rgba(124, 58, 237, 0.05);
            transition: transform 0.25s, box-shadow 0.25s;
            animation: dbFadeUp 0.55s ease both;
        }

        .panel-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06), 0 16px 40px rgba(124, 58, 237, 0.09);
        }

        .pc-violet {
            border-top: 4px solid #7c3aed;
        }

        .pc-amber {
            border-top: 4px solid #f59e0b;
        }

        /* ══════════════════════════════════════════
           Progress Bar (stat cards)
        ══════════════════════════════════════════ */
        .mini-bar-track {
            width: 100%;
            height: 6px;
            background: #f1f5f9;
            border-radius: 6px;
            overflow: hidden;
        }

        .mini-bar-fill {
            height: 100%;
            border-radius: 6px;
        }

        /* ══════════════════════════════════════════
           Attention Section Items
        ══════════════════════════════════════════ */
        .attn-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 4px;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s;
            text-decoration: none;
            cursor: pointer;
        }

        .attn-row:last-child {
            border-bottom: none;
        }

        .attn-row:hover {
            background: #f8fafc;
        }

        .attn-row__dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .attn-row__main {
            flex: 1;
            min-width: 0;
        }

        .attn-row__name {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .attn-row__meta {
            display: block;
            font-size: 11.5px;
            color: #94a3b8;
            margin-top: 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .attn-badge {
            display: inline-flex;
            align-items: center;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .attn-col-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #94a3b8;
            margin-bottom: 10px;
        }

        /* ══════════════════════════════════════════
           Animations
        ══════════════════════════════════════════ */
        @keyframes dbFadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .d1 {
            animation-delay: 0.04s;
        }

        .d2 {
            animation-delay: 0.10s;
        }

        .d3 {
            animation-delay: 0.16s;
        }

        .d4 {
            animation-delay: 0.22s;
        }

        .d5 {
            animation-delay: 0.30s;
        }

        .d6 {
            animation-delay: 0.36s;
        }

        .d7 {
            animation-delay: 0.42s;
        }

        .d8 {
            animation-delay: 0.48s;
        }
    </style>
@endpush

@section('content')
    @php
        $currentBatch = request('batch', 'Overall');
        $todayLabel = \Carbon\Carbon::now()->format('l, d F Y');
        $currentMonth = \Carbon\Carbon::now()->format('M Y');
    @endphp

    {{-- Ambient background --}}
    <div class="db-bg"></div>

    <div class="relative z-10 p-6 md:p-8 space-y-6">

        {{-- ══ PAGE HEADER ══ --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest mb-1" style="color:#7c3aed; letter-spacing:0.14em;">
                    HuffazTrack &mdash; Admin
                </p>
                <h1 class="text-4xl font-bold text-slate-800 leading-tight">Dashboard Overview</h1>
                <p class="text-sm text-slate-400 mt-0.5">{{ $todayLabel }}</p>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                {{-- Batch filter --}}
                <div class="flex items-center gap-1 bg-white border border-slate-100 rounded-xl p-1 shadow-sm">
                    <button class="filter-tab {{ $currentBatch === 'Overall' ? 'active' : '' }}"
                        onclick="setFilter('Overall')">Overall</button>
                    @foreach ($batchDistribution as $bKey => $count)
                        <button class="filter-tab {{ $currentBatch === $bKey ? 'active' : '' }}"
                            onclick="setFilter('{{ $bKey }}')">{{ $bKey }}</button>
                    @endforeach
                </div>

                {{-- Date filter --}}
                <div class="flex items-center gap-2 bg-white border border-slate-100 rounded-xl px-3 py-2 shadow-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" stroke-linecap="round" />
                        <path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round" />
                    </svg>
                    <input type="date" id="dashboardDatePicker" value="{{ $selectedDate }}"
                        max="{{ \Carbon\Carbon::today()->toDateString() }}" onchange="setDate(this.value)"
                        class="text-sm font-semibold text-slate-700 bg-transparent border-none outline-none cursor-pointer" />
                </div>
            </div>
        </div>

        {{-- ══ KPI CARDS ══ --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

            {{-- 1. Total Students --}}
            <a href="{{ route('admin.masterlist') }}" class="stat-card sc-featured d1" style="text-decoration:none;">
                <span class="card-glow" aria-hidden="true"></span>
                <div class="relative z-10 flex items-start justify-between mb-5">
                    <div class="stat-icon icon-glass">
                        <svg viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.92)" stroke-width="1.8">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke-linecap="round" />
                        </svg>
                    </div>
                    <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full"
                        style="background:rgba(255,255,255,0.17);color:rgba(255,255,255,0.92);">
                        {{ $totalActiveBatches }} Batches
                    </span>
                </div>
                <div class="relative z-10">
                    <p class="text-4xl font-extrabold text-white leading-none mb-1 counter"
                        data-target="{{ $totalStudents }}">{{ $totalStudents }}</p>
                    <p class="text-sm font-medium mb-5" style="color:rgba(255,255,255,0.68);">Total Active Students</p>
                    <div class="flex gap-2 flex-wrap">
                        <span class="gender-tag" style="background:rgba(255,255,255,0.16);color:rgba(255,255,255,0.92);">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" />
                            </svg>
                            {{ $maleStudents }} Male
                        </span>
                        <span class="gender-tag" style="background:rgba(255,255,255,0.11);color:rgba(255,255,255,0.92);">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" />
                            </svg>
                            {{ $femaleStudents }} Female
                        </span>
                    </div>
                    <div class="mt-2">
                        <span class="gender-tag" style="background:rgba(255,255,255,0.11);color:rgba(255,255,255,0.92);">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" />
                            </svg>
                            {{ $engineeringStudents }} FOE
                        </span>
                        <span class="gender-tag" style="background:rgba(255,255,255,0.11);color:rgba(255,255,255,0.92);">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" />
                            </svg>
                            {{ $computingStudents }} FOC
                        </span>
                        <span class="gender-tag" style="background:rgba(255,255,255,0.11);color:rgba(255,255,255,0.92);">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="8" r="4" />
                                <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" />
                            </svg>
                            {{ $businessStudents }} FOB
                        </span>
                    </div>
                </div>
            </a>

            {{-- 2. Attendance Today --}}
            <a href="{{ route('admin.attendance', ['date' => $selectedDate]) }}" class="stat-card sc-white sc-green d2" style="text-decoration:none;">
                <span class="card-glow" aria-hidden="true"></span>
                <div class="flex items-start justify-between mb-5">
                    <div class="stat-icon" style="background:#dcfce7;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2">
                            <path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full"
                        style="background:#dcfce7;color:#15803d;">{{ $periodLabel }}</span>
                </div>
                <p class="text-4xl font-extrabold text-slate-800 leading-none mb-1">
                    <span class="counter" data-target="{{ $attendanceRate }}">{{ $attendanceRate }}</span><span
                        class="text-xl font-semibold text-slate-400">%</span>
                </p>
                <p class="text-sm text-slate-500 mb-4">Attendance Rate</p>
                <div class="mt-auto">
                    <div class="flex justify-between text-xs mb-1.5">
                        <span class="text-slate-400">Present today</span>
                        <span class="font-semibold text-slate-600">{{ $presentCount }} / {{ $totalStudents }}</span>
                    </div>
                    <div class="mini-bar-track">
                        <div class="mini-bar-fill"
                            style="width:{{ $attendanceRate }}%; background:linear-gradient(90deg,#10b981,#34d399);">
                        </div>
                    </div>
                </div>
            </a>

            {{-- 3. Students Behind Muqarrar --}}
            <a href="{{ route('admin.hafazan', ['tab' => 'progress', 'date' => $selectedDate, 'status' => 'Behind']) }}" class="stat-card sc-white sc-red d3" style="text-decoration:none;">
                <span class="card-glow" aria-hidden="true"></span>
                <div class="flex items-start justify-between mb-5">
                    <div class="stat-icon" style="background:#fee2e2;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="1.8">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                    <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full"
                        style="background:#fee2e2;color:#b91c1c;">{{ $periodLabel }}</span>
                </div>
                <p class="text-4xl font-extrabold text-slate-800 leading-none mb-1">
                    <span class="counter" data-target="{{ $behindMuqarrarCount }}">{{ $behindMuqarrarCount }}</span>
                </p>
                <p class="text-sm text-slate-500 mb-4">Students Behind Muqarrar</p>
                <div class="mt-auto space-y-1.5">
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-400">Needs Attention (1–5 pg)</span>
                        <span class="font-semibold text-amber-600">{{ $needsAttentionCount }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-400">High Risk (&gt;5 pg)</span>
                        <span class="font-semibold text-red-600">{{ $criticalHafazanCount }}</span>
                    </div>
                </div>
            </a>

            {{-- 4. Active Batches --}}
            <a href="{{ route('admin.batches.index') }}" class="stat-card sc-white sc-blue d4"
                style="text-decoration:none;">
                <span class="card-glow" aria-hidden="true"></span>
                <div class="flex items-start justify-between mb-5">
                    <div class="stat-icon" style="background:#dbeafe;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="1.8">
                            <polygon points="12,2 22,8.5 22,15.5 12,22 2,15.5 2,8.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <line x1="12" y1="22" x2="12" y2="15.5" stroke-linecap="round" />
                            <polyline points="22,8.5 12,15.5 2,8.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
                <p class="text-4xl font-extrabold text-slate-800 leading-none mb-1 counter"
                    data-target="{{ $activeBatchesCount }}">{{ $activeBatchesCount }}</p>
                <p class="text-sm text-slate-500 mb-4">Active Batches</p>
                <div class="space-y-2 mt-auto">
                    @foreach ($activeBatchesList as $batch => $count)
                        <div class="flex items-center justify-between text-xs">
                            <span class="flex items-center gap-1.5 text-slate-500 font-medium">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-400 flex-shrink-0"></span>
                                {{ $batch }}
                            </span>
                            <span class="font-bold text-slate-700">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </a>

        </div>

        {{-- ══ STUDENTS REQUIRING ATTENTION ══ --}}
        <div class="panel-card pc-violet p-5 d5">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                <div>
                    <h2 class="text-base font-bold text-slate-800">Students Requiring Attention</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Critical hafazan &bull; Active warning letters &bull; Absent
                        today</p>
                </div>
                @if ($atRiskOperationalCount > 0)
                    <span class="text-xs font-bold px-3 py-1.5 rounded-full flex-shrink-0"
                        style="background:#fee2e2;color:#dc2626;">
                        {{ $atRiskOperationalCount }} requiring attention
                    </span>
                @else
                    <span class="text-xs font-bold px-3 py-1.5 rounded-full flex-shrink-0"
                        style="background:#dcfce7;color:#15803d;">
                        All clear
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                {{-- Critical Hafazan --}}
                <div>
                    <div class="attn-col-label">
                        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:#dc2626;"></span>
                        Critical Hafazan
                        <span class="ml-auto font-bold text-red-500">{{ $criticalHafazanCount }}</span>
                    </div>
                    @if ($criticalHafazanCount > 0)
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-[11px] text-slate-400">
                                Showing {{ $criticalHafazanStudents->count() }} of {{ $criticalHafazanCount }} students
                            </p>
                            <a href="{{ route('admin.reports.hafazan.weekly') }}"
                                class="text-[11px] font-semibold text-violet-600 hover:underline flex-shrink-0">
                                View All →
                            </a>
                        </div>
                    @endif
                    @forelse ($criticalHafazanStudents as $s)
                        <a href="{{ $s->lecturer_id ? route('admin.reports.hafazan.lecturer', ['id' => $s->lecturer_id, 'student' => $s->student_id]) : route('admin.masterlist.show', $s->student_id) }}" class="attn-row">
                            <span class="attn-row__dot" style="background:#dc2626;"></span>
                            <div class="attn-row__main">
                                <span class="attn-row__name">{{ $s->student_name }}</span>
                                <span class="attn-row__meta">{{ $s->batch_name }} &middot; Pg {{ $s->current_page }} → {{ $s->target_page }}</span>
                            </div>
                            <span class="attn-badge" style="background:#fee2e2;color:#dc2626;">
                                {{ $s->gap }}pg behind
                            </span>
                        </a>
                    @empty
                        <div class="flex items-center gap-2 p-3 rounded-xl"
                            style="background:#f8fafc;border:1px solid #f1f5f9;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8"
                                stroke-width="2">
                                <path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round" />
                                <circle cx="12" cy="12" r="10" />
                            </svg>
                            <p class="text-xs text-slate-400">No critical students this week.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Warning Letters --}}
                <div>
                    <div class="attn-col-label">
                        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:#f59e0b;"></span>
                        Active Warning Letters
                        <span class="ml-auto font-bold text-amber-500">{{ $warningStudentCount }}</span>
                    </div>
                    @if ($warningStudentCount > 0)
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-[11px] text-slate-400">
                                Showing {{ $warningLetterData->count() }} of {{ $warningStudentCount }} students
                            </p>
                            <a href="{{ route('admin.reports.attendance.monthly') }}"
                                class="text-[11px] font-semibold text-violet-600 hover:underline flex-shrink-0">
                                View All →
                            </a>
                        </div>
                    @endif
                    @forelse ($warningLetterData as $w)
                        <a href="{{ $w->lecturer_id ? route('admin.reports.attendance.monthly.lecturer', ['id' => $w->lecturer_id, 'student' => $w->student_id]) : route('admin.masterlist.show', $w->student_id) }}" class="attn-row">
                            <span class="attn-row__dot" style="background:{{ $w->max_level >= 3 ? '#dc2626' : '#f59e0b' }};"></span>
                            <div class="attn-row__main">
                                <span class="attn-row__name">{{ $w->student_name }}</span>
                                <span class="attn-row__meta">
                                    {{ $w->batch_name }}
                                    @if ($w->max_attendance_count)
                                        &middot; {{ $w->max_attendance_count }} unexcused
                                    @endif
                                </span>
                            </div>
                            <span class="attn-badge"
                                style="background:{{ $w->max_level >= 3 ? '#fee2e2' : '#fef3c7' }};
                               color:{{ $w->max_level >= 3 ? '#dc2626' : '#b45309' }};">
                                WL{{ $w->max_level }}
                            </span>
                        </a>
                    @empty
                        <div class="flex items-center gap-2 p-3 rounded-xl"
                            style="background:#f8fafc;border:1px solid #f1f5f9;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8"
                                stroke-width="2">
                                <path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round" />
                                <circle cx="12" cy="12" r="10" />
                            </svg>
                            <p class="text-xs text-slate-400">No active warning letters.</p>
                        </div>
                    @endforelse
                </div>

            </div>

            {{-- Absent Today Footer --}}
            <div class="flex flex-wrap items-center justify-between gap-3 mt-5 pt-4 border-t border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:#f97316;"></span>
                    <span class="text-sm text-slate-500">Absent ({{ $periodLabel }}):</span>
                    <span class="text-sm font-bold text-slate-700">{{ $absentTodayCount }}</span>
                    <span class="text-xs text-slate-400">{{ $absentTodayCount === 1 ? 'student' : 'students' }}</span>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('admin.reports.attendance.daily', ['date' => $selectedDate]) }}"
                        class="text-xs font-semibold text-violet-600 hover:underline">
                        View Attendance →
                    </a>
                    <a href="{{ route('admin.reports.hafazan.weekly') }}" class="text-xs font-semibold text-violet-600 hover:underline">
                        View Hafazan →
                    </a>
                </div>
            </div>
        </div>

        {{-- ══ ACADEMIC ANALYTICS (teammate) ══ --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

            {{-- Average CGPA Per Batch --}}
            <div class="panel-card p-5 d6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Average CGPA Per Batch</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Active batches comparison</p>
                    </div>
                </div>
                <div style="position:relative; height:320px;">
                    <canvas id="cgpaBatchChart"></canvas>
                </div>
            </div>

            {{-- CGPA Distribution --}}
            <div class="panel-card p-5 d7">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-800">CGPA Distribution</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Overall tahfiz classification</p>
                    </div>
                </div>
                <div class="flex items-center justify-center w-full">
                    <div class="relative" style="width:320px; height:320px;">
                        <div class="relative" style="width:320px; height:270px;">
                            <canvas id="cgpaDistributionChart"></canvas>

                            <div
                                class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none"
                                style="transform: translateY(-16px);">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                Total
                            </p>

                            <p class="text-3xl font-extrabold text-slate-700 leading-tight">
                                {{ array_sum($cgpaDistribution) }}
                            </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ══ SECOND ANALYTICS ROW (teammate) ══ --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

            {{-- At-Risk Students (CGPA) --}}
            <div class="panel-card p-5 d8">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-base font-bold text-slate-800">At-Risk Students</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Students with CGPA below 2.50</p>
                    </div>
                    <a href="{{ route('admin.dashboard.atRisk', ['batch' => $currentBatch]) }}"
                        class="text-[11px] font-semibold text-violet-600 hover:underline flex-shrink-0">
                        See All &rarr;
                    </a>
                </div>
                <div class="space-y-3">
                    @forelse ($atRiskStudents as $index => $student)
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-red-50 border border-red-100">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center font-black">
                                    !</div>
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
                        <div class="text-center py-10">
                            <p class="text-sm text-slate-400">No at-risk students detected.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Top CGPA Students --}}
            <div class="panel-card p-5 d8">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Top Students Leaderboard</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Students with CGPA 3.50 and above</p>
                    </div>
                    <a href="{{ route('admin.dashboard.topPerformers', ['batch' => $currentBatch]) }}"
                        class="text-[11px] font-semibold text-violet-600 hover:underline flex-shrink-0">
                        See All &rarr;
                    </a>
                </div>
                <div class="space-y-3">
                    @foreach ($topStudents as $index => $student)
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-xl bg-violet-100 text-violet-700 flex items-center justify-center font-black">
                                    #{{ $index + 1 }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700">{{ $student->student_name }}</p>
                                    <p class="text-xs text-slate-400">{{ $student->batch_name }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-black text-violet-700">{{ number_format($student->cgpa, 2) }}</p>
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">CGPA</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* ── Count-up animation ─────────────────────────────── */
            function countUp(el, target, ms) {
                const start = performance.now();;
                (function tick(now) {
                    const t = Math.min((now - start) / ms, 1);
                    const v = Math.round((1 - Math.pow(1 - t, 4)) * target);
                    el.textContent = v.toLocaleString();
                    if (t < 1) requestAnimationFrame(tick);
                })(start);
            }

            document.querySelectorAll('.counter[data-target]').forEach(el => {
                countUp(el, parseFloat(el.dataset.target), 1300);
            });

            if (typeof Chart === 'undefined') return;

            /* ── CGPA Charts (teammate) ─────────────────────────── */
            Chart.register(ChartDataLabels);

            const cgpaBatchEl = document.getElementById('cgpaBatchChart');
            if (cgpaBatchEl) {
                const batchLabels = {!! json_encode($cgpaPerBatch->pluck('batch_name') ?? []) !!};
                const batchData = {!! json_encode($cgpaPerBatch->pluck('average_cgpa') ?? []) !!};

                new Chart(cgpaBatchEl, {
                    type: 'bar',
                    data: {
                        labels: batchLabels,
                        datasets: [{
                            label: 'Average CGPA',
                            data: batchData.map(val => parseFloat(val) || 0),
                            backgroundColor: '#7c3aed',
                            borderRadius: 12,
                            borderSkipped: false,
                            maxBarThickness: 50
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#94a3b8'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                max: 4,
                                ticks: {
                                    stepSize: 0.5,
                                    color: '#94a3b8'
                                },
                                grid: {
                                    color: 'rgba(148,163,184,0.12)'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                color: '#475569',
                                font: {
                                    weight: 'bold',
                                    size: 11
                                },
                                formatter: value => {
                                    const num = parseFloat(value);
                                    return isNaN(num) ? '0.00' : num.toFixed(2);
                                }
                            }
                        }
                    }
                });
            }

            const cgpaDistributionEl = document.getElementById('cgpaDistributionChart');
            if (cgpaDistributionEl) {
                const distributionData = [
                    parseInt("{{ $cgpaDistribution['mumtaz'] ?? 0 }}") || 0,
                    parseInt("{{ $cgpaDistribution['jayyid_jiddan'] ?? 0 }}") || 0,
                    parseInt("{{ $cgpaDistribution['jayyid'] ?? 0 }}") || 0,
                    parseInt("{{ $cgpaDistribution['maqbul'] ?? 0 }}") || 0,
                    parseInt("{{ $cgpaDistribution['rasib'] ?? 0 }}") || 0
                ];

                new Chart(cgpaDistributionEl, {
                    type: 'doughnut',
                    data: {
                        labels: ['Mumtaz', 'Jayyid Jiddan', 'Jayyid', 'Maqbul', 'Rasib'],
                        datasets: [{
                            data: distributionData,
                            backgroundColor: ['#B8E6B8', '#AFC6FF', '#FFE199', '#FFF2CC',
                                '#E6B8C1'
                            ],
                            borderWidth: 0,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            datalabels: {
                                color: '#475569',
                                font: {
                                    weight: 'bold',
                                    size: 12
                                },
                                formatter: (value, context) => {
                                    if (value <= 0) return '';
                                    const total = context.chart.data.datasets[0].data.reduce((sum,
                                        val) => sum + val, 0);
                                    const pct = total > 0 ? (value / total) * 100 : 0;
                                    return `${pct.toFixed(1)}%`;
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: context => {
                                        const classifications = [
                                            'Mumtaz / Cemerlang',
                                            'Jayyid Jiddan / Kepujian Atas',
                                            'Jayyid / Kepujian',
                                            'Maqbul / Lulus',
                                            'Rasib / Gagal'
                                        ];
                                        const total = context.dataset.data.reduce((sum, value) => sum +
                                            value, 0);
                                        const value = Number(context.raw) || 0;
                                        const pct = total > 0 ? (value / total) * 100 : 0;
                                        return `${classifications[context.dataIndex]}: ${value} (${pct.toFixed(1)}%)`;
                                    }
                                }
                            },
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 12,
                                    color: '#64748b',
                                    boxWidth: 10
                                }
                            }
                        }
                    }
                });
            }
        });

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
    </script>
@endpush
