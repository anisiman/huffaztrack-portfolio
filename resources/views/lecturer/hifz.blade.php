@extends('layouts/lecturer')

@section('title', 'Daily Tasmi Workspace')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/hifz-record.css') }}">
    <style>
        .ws-header { display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
        .ws-title { font-size: 2.25rem; font-weight: 700; letter-spacing: -0.02em; color: #0f172a; margin: 0; }
        .ws-subtitle { font-size: 0.875rem; color: #94a3b8; margin: 2px 0 0; }

        .ws-date-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 99px; font-size: 0.82rem; font-weight: 600; color: #475569; }
        .ws-date-pill input[type="date"] { border: 0; background: transparent; font: inherit; color: inherit; cursor: pointer; outline: none; }

        .ws-day-nav { display: flex; align-items: center; gap: 4px; }
        .ws-day-nav__btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border-radius: 8px; font-size: 0.82rem; font-weight: 600; color: #475569; background: #f1f5f9; border: 1px solid #e2e8f0; text-decoration: none; transition: background .15s, color .15s; white-space: nowrap; }
        .ws-day-nav__btn:hover { background: #e2e8f0; color: #0f172a; }
        .ws-day-nav__btn--disabled { color: #cbd5e1; background: #f8fafc; border-color: #f1f5f9; pointer-events: none; cursor: default; }

        /* Muqarrar context banner */
        .ws-context { display: flex; flex-wrap: wrap; align-items: stretch; gap: 12px; margin-bottom: 24px; }
        .ws-context__chip { display: flex; flex-direction: column; gap: 6px; padding: 16px 22px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; }
        .ws-context__chip--key { padding: 20px 30px; background: #fff; border-color: #e2e8f0; box-shadow: 0 2px 6px rgba(15,23,42,.08); }
        .ws-context__chip--checkpoint { background: #faf5ff; border-color: #ddd6fe; }
        .ws-context__label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: #94a3b8; }
        .ws-context__val { font-size: 1.2rem; font-weight: 700; color: #0f172a; line-height: 1.15; }
        .ws-context__chip--key .ws-context__val { font-size: 2.1rem; font-weight: 800; }
        .ws-context__val--purple { color: #7c3aed; }
        .ws-context__divider { display: none; }

        /* Progress gauge */
        .ws-gauge { min-width: 130px; }
        .ws-gauge__top { display: flex; justify-content: space-between; font-size: 10px; color: #94a3b8; margin-bottom: 4px; }
        .ws-gauge__pct { font-weight: 700; color: #0f172a; }
        .ws-gauge__track { height: 8px; background: #f1f5f9; border-radius: 99px; overflow: hidden; }
        .ws-gauge__fill { height: 100%; border-radius: 99px; transition: width .3s ease, background-color .25s ease; }
        .ws-gauge__bottom { text-align: right; font-size: 10px; color: #94a3b8; margin-top: 3px; }

        .ws-summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 24px; }
        .ws-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px 22px;
            box-shadow: 0 2px 6px rgba(15,23,42,.06);
            display: block; text-decoration: none; cursor: pointer;
            transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
        }
        .ws-card:hover { border-color: #c4b5fd; box-shadow: 0 4px 14px rgba(124,58,237,.14); transform: translateY(-1px); }
        .ws-card--active { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,.15); }
        .ws-card__label { font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1.4px; margin-bottom: 6px; }
        .ws-card__value { font-size: 2.2rem; font-weight: 800; color: #0f172a; }
        .ws-card__sub { font-size: 12px; font-weight: 500; color: #94a3b8; margin-top: 4px; }
        .ws-card--green .ws-card__value   { color: #10b981; }
        .ws-card--blue .ws-card__value    { color: #2563eb; }
        .ws-card--red .ws-card__value     { color: #ef4444; }
        .ws-card--slate .ws-card__value   { color: #64748b; }

        .ws-filters { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 16px; }
        .ws-filter-input { display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.85rem; color: #475569; min-width: 140px; }
        .ws-filter-input input, .ws-filter-input select { border: 0; background: transparent; font: inherit; color: inherit; outline: none; width: 100%; }
        .ws-filter-input svg { width: 15px; height: 15px; flex-shrink: 0; color: #94a3b8; }
        .ws-btn { padding: 7px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; border: 0; }
        .ws-btn--primary { background: #7c3aed; color: #fff; }
        .ws-btn--primary:hover { background: #6d28d9; }
        .ws-btn--save { background: #227942; color: #fff; padding: 9px 24px; font-size: 0.9rem; border-radius: 10px; }
        .ws-btn--save:hover { background: #1e293b; }
        .ws-btn--outline { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }
        .ws-btn--hist { background: #ede9fe; color: #7c3aed; border: 1px solid #c4b5fd; font-size: 12px; padding: 4px 10px; border-radius: 7px; }
        .ws-btn--hist:hover { background: #ddd6fe; }

        .ws-panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; box-shadow: 0 2px 8px rgba(15,23,42,.04); }
        .ws-panel__head { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; border-bottom: 1px solid #f1f5f9; }
        .ws-panel__title { font-size: 0.95rem; font-weight: 700; color: #0f172a; }
        .ws-panel__meta  { font-size: 0.8rem; color: #94a3b8; }

        .ws-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        .ws-table thead th { padding: 10px 16px; text-align: left; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #94a3b8; background: #f8fafc; border-bottom: 1px solid #f1f5f9; }
        .ws-table tbody td { padding: 11px 16px; border-bottom: 1px solid #f8fafc; vertical-align: middle; }
        .ws-table tbody tr:last-child td { border-bottom: 0; }
        .ws-table tbody tr:hover { background: #fafbff; }

        .ws-student-name { font-weight: 600; color: #0f172a; }
        .ws-student-id   { font-size: 11px; color: #94a3b8; }

        .ws-badge { display: inline-block; padding: 3px 9px; border-radius: 99px; font-size: 11px; font-weight: 600; }
        .ws-badge--completed   { background: #d1fae5; color: #059669; }
        .ws-badge--ontrack     { background: #dbeafe; color: #2563eb; }
        .ws-badge--behind      { background: #fee2e2; color: #dc2626; }
        .ws-badge--notrecorded { background: #f1f5f9; color: #94a3b8; }

        .ws-page-input { width: 80px; padding: 5px 8px; border: 1px solid #e2e8f0; border-radius: 7px; font-size: 0.875rem; color: #0f172a; text-align: center; outline: none; transition: border-color .15s; }
        .ws-page-input:focus { border-color: #7c3aed; box-shadow: 0 0 0 2px rgba(124,58,237,.1); }
        .ws-page-input.is-completed { border-color: #059669; background: #f0fdf4; }
        .ws-page-input.is-ontrack   { border-color: #2563eb; background: #eff6ff; }
        .ws-page-input.is-behind    { border-color: #ef4444; background: #fef2f2; }

        .ws-remaining { font-size: 0.82rem; color: #64748b; }
        .ws-remaining--khatam { color: #059669; font-weight: 600; }

        .ws-khatam-badge { display: inline-flex; align-items: center; gap: 3px; margin-left: 6px; padding: 1px 7px; border-radius: 999px; background: #fef3c7; color: #b45309; font-size: 0.68rem; font-weight: 700; vertical-align: middle; white-space: nowrap; }

        .ws-footer { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; border-top: 1px solid #f1f5f9; }
        .ws-footer__count { font-size: 0.82rem; color: #94a3b8; }

        /* Batch group separator row */
        .ws-batch-sep td { padding: 0; border-bottom: none !important; background: transparent; }
        .ws-batch-header {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 16px 8px;
            border-top: 2px solid #e2e8f0;
            background: #f8fafc;
        }
        .ws-batch-header--first { border-top: none; }
        .ws-batch-title {
            font-size: 0.78rem; font-weight: 800; color: #0f172a;
            text-transform: uppercase; letter-spacing: 0.07em;
        }
        .ws-batch-week {
            font-size: 0.75rem; color: #7c3aed; font-weight: 600;
            background: #ede9fe; padding: 2px 8px; border-radius: 99px;
        }
        .ws-batch-pages {
            font-size: 0.75rem; color: #64748b; font-weight: 500;
        }
        .ws-batch-count {
            margin-left: auto; font-size: 0.75rem; color: #94a3b8; font-weight: 500;
        }

        /* History modal */
        .ws-modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 999; align-items: center; justify-content: center; }
        .ws-modal-backdrop.open { display: flex; }
        .ws-modal { background: #fff; border-radius: 16px; padding: 28px; width: 560px; max-width: 96vw; max-height: 80vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(15,23,42,.15); }
        .ws-modal__head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .ws-modal__title { font-size: 18px; font-weight: 700; color: #0f172a; margin: 0; }
        .ws-modal__close { background: none; border: none; color: #94a3b8; font-size: 22px; cursor: pointer; }
        .ws-modal__close:hover { color: #0f172a; }
        .ws-modal__section-title { font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8; margin-bottom: 10px; }

        /* Surah / Juz pill badges */
        .ws-quran-info { display: flex; gap: 5px; align-items: center; flex-wrap: wrap; margin-top: 6px; }
        .ws-context__surah {
            display: inline-block;
            background: #ede9fe; color: #6d28d9;
            font-size: 0.82rem; font-weight: 700;
            padding: 3px 10px; border-radius: 99px; line-height: 1.5;
        }
        .ws-context__chip--checkpoint .ws-context__surah { background: #ede9fe; color: #6d28d9; }
        .ws-context__juz {
            display: inline-block;
            background: #dbeafe; color: #1d4ed8;
            font-size: 0.78rem; font-weight: 600;
            padding: 3px 10px; border-radius: 99px; line-height: 1.5;
        }
    </style>
@endpush

@section('content')
<div class="hifz-page">

    {{-- Header --}}
    <div class="ws-header">
        <div>
            <h1 class="ws-title">Daily Tasmi&rsquo;</h1>
            <p class="ws-subtitle">{{ $selectedDate->format('l, d F Y') }}</p>
        </div>
        @php
            $prevDate    = $selectedDate->copy()->subDay();
            $nextDate    = $selectedDate->copy()->addDay();
            $disableNext = $nextDate->startOfDay()->gt(\Carbon\Carbon::today());
            $navParams   = request()->except('date');
        @endphp
        <div class="ws-day-nav">
            {{-- Previous Day --}}
            <a href="{{ route('lecturer.hifz', array_merge($navParams, ['date' => $prevDate->format('Y-m-d')])) }}"
               class="ws-day-nav__btn" title="Previous Day">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;">
                    <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/>
                </svg>
                Prev
            </a>

            {{-- Calendar date picker --}}
            <form method="GET" action="{{ route('lecturer.hifz') }}" style="display:contents">
                @foreach($navParams as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <label class="ws-date-pill" style="border-radius:8px; cursor:pointer;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:14px;height:14px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 8.25h16.5M4.5 6.75h15A2.25 2.25 0 0 1 21.75 9v9.75A2.25 2.25 0 0 1 19.5 21h-15a2.25 2.25 0 0 1-2.25-2.25V9A2.25 2.25 0 0 1 4.5 6.75Z" />
                    </svg>
                    <input type="date" name="date" value="{{ $selectedDate->format('Y-m-d') }}" onchange="this.form.submit()">
                </label>
            </form>

            {{-- Next Day --}}
            @if ($disableNext)
                <span class="ws-day-nav__btn ws-day-nav__btn--disabled" title="Cannot navigate to a future date">
                    Next
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;">
                        <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                    </svg>
                </span>
            @else
                <a href="{{ route('lecturer.hifz', array_merge($navParams, ['date' => $nextDate->format('Y-m-d')])) }}"
                   class="ws-day-nav__btn" title="Next Day">
                    Next
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:14px;height:14px;">
                        <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                    </svg>
                </a>
            @endif
        </div>
    </div>

    {{-- Muqarrar context banner — one row per batch --}}
    @if ($batchContexts->isNotEmpty())
        @foreach ($batchContexts as $ctx)
        <div class="ws-context" style="{{ $batchContexts->count() > 1 ? 'margin-bottom:8px;' : '' }}">
            @if ($batchContexts->count() > 1)
            <div class="ws-context__chip ws-context__chip--key">
                <span class="ws-context__label">Batch</span>
                <span class="ws-context__val" style="font-size:1.3rem;">{{ $ctx->batch_name }}</span>
            </div>
            @endif
            @if ($ctx->muqarrar)
            <div class="ws-context__chip ws-context__chip--key">
                <span class="ws-context__label">Week</span>
                <span class="ws-context__val">{{ $ctx->muqarrar->week }}</span>
            </div>
            <div class="ws-context__chip ws-context__chip--key">
                <span class="ws-context__label">Muqarrar</span>
                <span class="ws-context__val">Pg {{ $ctx->muqarrar->start_page }} &rarr; {{ $ctx->muqarrar->end_page }}</span>
                @php $qi = \App\Helpers\QuranHelper::getInfo((int) $ctx->muqarrar->end_page); @endphp
                <div class="ws-quran-info">
                    <span class="ws-context__surah" dir="rtl" lang="ar">{{ $qi['surah'] }}</span>
                    <span class="ws-context__juz">Juz {{ $qi['juz'] }}</span>
                </div>
            </div>
            <div class="ws-context__chip ws-context__chip--key ws-context__chip--checkpoint">
                <span class="ws-context__label">Today&rsquo;s Muqarrar</span>
                <span class="ws-context__val ws-context__val--purple">Pg {{ $ctx->checkpoint }}</span>
                @php $qi = \App\Helpers\QuranHelper::getInfo((int) $ctx->checkpoint); @endphp
                <div class="ws-quran-info">
                    <span class="ws-context__surah" dir="rtl" lang="ar">{{ $qi['surah'] }}</span>
                    <span class="ws-context__juz">Juz {{ $qi['juz'] }}</span>
                </div>
            </div>
            <div class="ws-context__chip">
                <span class="ws-context__label">Today&rsquo;s Tasmi&rsquo;</span>
                <span class="ws-context__val">Pg {{ $ctx->dailyRangeStart }} &ndash; {{ $ctx->dailyRangeEnd }}</span>
                @php $qi = \App\Helpers\QuranHelper::getInfo((int) $ctx->dailyRangeEnd); @endphp
                <div class="ws-quran-info">
                    <span class="ws-context__surah" dir="rtl" lang="ar">{{ $qi['surah'] }}</span>
                    <span class="ws-context__juz">Juz {{ $qi['juz'] }}</span>
                </div>
            </div>
            @if ($ctx->dayOfWeek > 0)
            <div class="ws-context__chip">
                <span class="ws-context__label">Day</span>
                <span class="ws-context__val">{{ $ctx->dayOfWeek }}/{{ $ctx->totalLearningDays }}</span>
            </div>
            @endif
            @else
            <span style="color:#94a3b8;font-size:0.875rem;">No muqarrar configured for {{ $ctx->batch_name }}.</span>
            @endif
        </div>
        @endforeach
    @else
    <div class="ws-context" style="color: #94a3b8; padding: 14px 18px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;">No active muqarrar configured.</div>
    @endif

    {{-- Notices --}}
    @if (session('success'))
        <div class="hifz-notice hifz-notice--success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="hifz-notice hifz-notice--error">{{ $errors->first() }}</div>
    @endif

    {{-- Summary Cards — click one to filter the roster below --}}
    @php
        $wsCardBaseParams = array_filter([
            'date'    => $selectedDate->format('Y-m-d'),
            'search'  => $search,
            'halaqah' => $selectedHalaqah,
        ], fn ($v) => $v !== '' && $v !== null);
        $wsCards = [
            ['label' => 'Ahead / Completed', 'value' => $completedCount,   'sub' => "Exceeded today's muqarrar",  'theme' => 'green', 'status' => 'Ahead,Completed'],
            ['label' => 'On-track',          'value' => $onTrackCount,     'sub' => "Following today's muqarrar", 'theme' => 'blue',  'status' => 'On-track'],
            ['label' => 'Behind',            'value' => $behindCount,      'sub' => "Behind today's muqarrar",    'theme' => 'red',   'status' => 'Behind'],
            ['label' => 'Not Recorded',      'value' => $notRecordedCount, 'sub' => 'no entry today',             'theme' => 'slate', 'status' => 'Not Recorded'],
        ];
    @endphp
    <div class="ws-summary">
        @foreach ($wsCards as $card)
            @php $cardActive = $selectedStatus === $card['status']; @endphp
            <a href="{{ route('lecturer.hifz', array_merge($wsCardBaseParams, ['status' => $cardActive ? '' : $card['status']])) }}"
               class="ws-card ws-card--{{ $card['theme'] }}{{ $cardActive ? ' ws-card--active' : '' }}">
                <div class="ws-card__label">{{ $card['label'] }}</div>
                <div class="ws-card__value">{{ $card['value'] }}</div>
                <div class="ws-card__sub">{{ $card['sub'] }}</div>
            </a>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('lecturer.hifz') }}" class="ws-filters" id="hifzFilterForm">
        <input type="hidden" name="date" value="{{ $selectedDate->format('Y-m-d') }}">

        <label class="ws-filter-input" style="min-width:200px;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.75 17.25a7.5 7.5 0 0 0 9.9-.6Z" />
            </svg>
            <input type="text" name="search" id="hifzSearchInput" value="{{ $search }}" placeholder="Search student…">
        </label>

        <label class="ws-filter-input">
            <select name="halaqah" onchange="this.form.submit()">
                <option value="">All Batch</option>
                @foreach ($halaqahOptions as $h)
                    <option value="{{ $h }}" {{ $selectedHalaqah === $h ? 'selected' : '' }}>{{ $h }}</option>
                @endforeach
            </select>
        </label>

        <label class="ws-filter-input">
            <select name="status" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="Completed"    {{ $selectedStatus === 'Completed'    ? 'selected' : '' }}>Completed</option>
                <option value="Ahead"        {{ $selectedStatus === 'Ahead'        ? 'selected' : '' }}>Ahead</option>
                <option value="On-track"     {{ $selectedStatus === 'On-track'     ? 'selected' : '' }}>On-track</option>
                <option value="Behind"       {{ $selectedStatus === 'Behind'       ? 'selected' : '' }}>Behind</option>
                <option value="Not Recorded" {{ $selectedStatus === 'Not Recorded' ? 'selected' : '' }}>Not Recorded</option>
            </select>
        </label>

        @if ($search || $selectedHalaqah || $selectedStatus)
            <a href="{{ route('lecturer.hifz', ['date' => $selectedDate->format('Y-m-d')]) }}" class="ws-btn ws-btn--outline">Clear</a>
        @endif
    </form>

    {{-- Workspace Table --}}
    <form method="POST" action="{{ route('lecturer.hifz.saveAll') }}">
        @csrf
        <input type="hidden" name="date" value="{{ $selectedDate->format('Y-m-d') }}">

        <div class="ws-panel">
            <div class="ws-panel__head">
                <span class="ws-panel__title">Student Roster &mdash; {{ $selectedDate->format('d M Y') }}</span>
                <span class="ws-panel__meta">{{ $records->count() }} student{{ $records->count() === 1 ? '' : 's' }}</span>
            </div>

            <div style="overflow-x:auto;">
                <table class="ws-table">
                    <thead>
                        <tr>
                            <th style="width:42px;">No.</th>
                            <th>Student</th>
                            <th>Batch</th>
                            <th>Latest Page</th>
                            <th>Remaining</th>
                            <th>Week Progress</th>
                            <th>Status</th>
                            <th>History</th>
                        </tr>
                    </thead>
                    @php
                        // Group students by batch, preserving sorted order
                        $grouped        = $records->groupBy('batch_name');
                        $batchContextMap = $batchContexts->keyBy('batch_name');
                        $rowNum          = 0;
                    @endphp
                    <tbody>
                        @if ($records->isEmpty())
                            <tr>
                                <td colspan="8" style="text-align:center; padding:40px; color:#94a3b8;">
                                    No students found.
                                </td>
                            </tr>
                        @else
                        @foreach ($grouped as $batchName => $batchRows)
                            @php
                                $ctx         = $batchContextMap->get($batchName);
                                $isFirstGroup = $loop->first;
                            @endphp

                            {{-- Batch separator header --}}
                            <tr class="ws-batch-sep">
                                <td colspan="8">
                                    <div class="ws-batch-header {{ $isFirstGroup ? 'ws-batch-header--first' : '' }}">
                                        <span class="ws-batch-title">{{ $batchName }}</span>
                                        @if ($ctx && $ctx->muqarrar)
                                            <span class="ws-batch-week">Week {{ $ctx->muqarrar->week }}</span>
                                            <span class="ws-batch-pages">
                                                Pg {{ $ctx->muqarrar->start_page }} &rarr; {{ $ctx->muqarrar->end_page }}
                                                &nbsp;&middot;&nbsp; Sem. {{ $ctx->muqarrar->semester_id }}
                                            </span>
                                        @endif
                                        <span class="ws-batch-count">
                                            {{ $batchRows->count() }} student{{ $batchRows->count() === 1 ? '' : 's' }}
                                        </span>
                                    </div>
                                </td>
                            </tr>

                            {{-- Student rows for this batch --}}
                            @foreach ($batchRows as $row)
                                @php
                                    $rowNum++;
                                    $badgeClass = match($row->status) {
                                        'Ahead', 'Completed' => 'ws-badge--completed',
                                        'On-track'           => 'ws-badge--ontrack',
                                        'Behind'             => 'ws-badge--behind',
                                        default              => 'ws-badge--notrecorded',
                                    };
                                    $inputClass = match($row->status) {
                                        'Ahead', 'Completed' => 'is-completed',
                                        'On-track'           => 'is-ontrack',
                                        'Behind'             => 'is-behind',
                                        default              => '',
                                    };
                                    $muqStart   = (int) $row->start_page;
                                    $muqEnd     = (int) $row->end_page;
                                    $sPage      = (int) ($row->page_reached ?? 0);
                                    $isKhatam   = $row->khatam_count > 0;
                                    $gaugeDenom = $muqEnd - $muqStart;
                                    $gaugePct   = ($isKhatam && $row->has_record) ? 100 : (($gaugeDenom > 0 && $sPage > $muqStart)
                                        ? min(100, max(0, (int) round(($sPage - $muqStart) / $gaugeDenom * 100)))
                                        : 0);
                                    $gaugeColor = match($row->status) {
                                        'Ahead', 'Completed' => '#10b981',
                                        'On-track'  => '#2563eb',
                                        'Behind'    => '#ef4444',
                                        default     => '#cbd5e1',
                                    };
                                @endphp
                                <input type="hidden" name="rows[{{ $row->student_id }}][muqarrar_id]" value="{{ $row->muqarrar_id }}">
                                <input type="hidden" name="rows[{{ $row->student_id }}][prev_page]"    value="{{ $row->prev_page }}">
                                <input type="hidden" name="rows[{{ $row->student_id }}][dirty]"        value="0" id="dirty-{{ $row->student_id }}">
                                <tr>
                                    <td style="color:#94a3b8; font-size:12px;">{{ $rowNum }}</td>
                                    <td>
                                        <div class="ws-student-name">
                                            {{ $row->student_name }}
                                            @if ($isKhatam)
                                                <span class="ws-khatam-badge" title="Khatam {{ $row->khatam_count }} time(s) — Muqarrar already fulfilled">🏆 Khatam x{{ $row->khatam_count }}</span>
                                            @endif
                                        </div>
                                        <div class="ws-student-id">{{ $row->student_id }}</div>
                                    </td>
                                    <td style="color:#64748b;">{{ $row->batch_name }}</td>
                                    <td>
                                        <input
                                            type="number"
                                            name="rows[{{ $row->student_id }}][page_reached]"
                                            value="{{ $row->page_reached }}"
                                            min="0"
                                            placeholder="—"
                                            class="ws-page-input {{ $inputClass }}"
                                            data-checkpoint="{{ $row->checkpoint }}"
                                            data-start-page="{{ $row->start_page }}"
                                            data-end-page="{{ $row->end_page }}"
                                            data-student-id="{{ $row->student_id }}"
                                            data-prev-page="{{ $row->prev_page }}"
                                            data-has-record="{{ $row->has_record ? '1' : '0' }}"
                                            data-khatam="{{ $isKhatam ? '1' : '0' }}"
                                            oninput="updateStatus(this)"
                                        >
                                    </td>
                                    <td>
                                        <span class="ws-remaining {{ $isKhatam && $row->has_record ? 'ws-remaining--khatam' : '' }}" id="remaining-{{ $row->student_id }}">
                                            @if ($isKhatam && $row->has_record)
                                                Passed Muqarrar (Khatam)
                                            @elseif ($row->has_record && $row->remaining !== null)
                                                {{ $row->remaining === 0 ? '0 pages left' : $row->remaining . ' pages left' }}
                                            @else
                                                &mdash;
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        <div class="ws-gauge">
                                            <div class="ws-gauge__top">
                                                <span>Pg {{ $muqStart }}</span>
                                                <span class="ws-gauge__pct" id="gauge-pct-{{ $row->student_id }}">{{ $gaugePct }}%</span>
                                            </div>
                                            <div class="ws-gauge__track">
                                                <div class="ws-gauge__fill"
                                                     id="gauge-{{ $row->student_id }}"
                                                     style="width:{{ $gaugePct }}%; background-color:{{ $gaugeColor }};"></div>
                                            </div>
                                            <div class="ws-gauge__bottom">Pg {{ $muqEnd }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="ws-badge {{ $badgeClass }}" id="status-{{ $row->student_id }}">
                                            {{ $row->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="ws-btn ws-btn--hist js-hist-btn"
                                                data-student-id="{{ $row->student_id }}"
                                                data-student-name="{{ $row->student_name }}">
                                            History
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="ws-footer">
                <span class="ws-footer__count">
                    {{ $completedCount }} ahead &nbsp;&middot;&nbsp;
                    {{ $onTrackCount }} on-track &nbsp;&middot;&nbsp;
                    {{ $behindCount }} behind &nbsp;&middot;&nbsp;
                    {{ $notRecordedCount }} not recorded
                </span>
                <button type="submit" class="ws-btn ws-btn--save">Save All</button>
            </div>
        </div>
    </form>

</div>

{{-- History Modal --}}
<div class="ws-modal-backdrop" id="histModal">
    <div class="ws-modal">
        <div class="ws-modal__head">
            <h2 class="ws-modal__title" id="histModalName"></h2>
            <button class="ws-modal__close" onclick="closeHistModal()">&times;</button>
        </div>
        <div class="ws-modal__section-title">Daily Tasmi&rsquo; History</div>
        <table class="ws-table" style="font-size:12px;">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Latest Page</th>
                    <th>Daily Muqarrar</th>
                    <th>Remaining</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="histModalBody"></tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<script>
const historyUrl = '{{ route('lecturer.hifz.studentHistory') }}';

const gaugeColors = {
    completed:   '#10b981',
    khatam:      '#10b981',
    ontrack:     '#2563eb',
    behind:      '#ef4444',
    notrecorded: '#cbd5e1',
};

// Gauge uses per-student start/end from data attributes — no global page variables needed
function updateGauge(studentId, val, statusKey) {
    const fill  = document.getElementById('gauge-' + studentId);
    const pctEl = document.getElementById('gauge-pct-' + studentId);
    if (!fill) return;
    const input     = document.querySelector('[data-student-id="' + studentId + '"]');
    const startPage = input ? (parseInt(input.dataset.startPage) || 0) : 0;
    const endPage   = input ? (parseInt(input.dataset.endPage)   || 0) : 0;
    const denom = endPage - startPage;
    let pct = 0;
    if (statusKey === 'khatam') {
        pct = 100;
    } else if (denom > 0 && val > startPage) {
        pct = Math.min(100, Math.max(0, Math.round((val - startPage) / denom * 100)));
    }
    fill.style.width = pct + '%';
    fill.style.backgroundColor = gaugeColors[statusKey] || gaugeColors.notrecorded;
    if (pctEl) pctEl.textContent = pct + '%';
}

function updateStatus(input) {
    // Mark this field as explicitly edited by the user so the server-side
    // skip guard (same-page-as-yesterday) is bypassed on save.
    input.dataset.dirty = '1';
    const dirtyEl = document.getElementById('dirty-' + input.dataset.studentId);
    if (dirtyEl) dirtyEl.value = '1';

    const val        = parseInt(input.value) || 0;
    const checkpoint = parseInt(input.dataset.checkpoint) || 0;
    const endPage    = parseInt(input.dataset.endPage)    || 0;
    const studentId  = input.dataset.studentId;
    const badge      = document.getElementById('status-' + studentId);
    const remaining  = document.getElementById('remaining-' + studentId);

    input.classList.remove('is-completed', 'is-ontrack', 'is-behind');
    badge.classList.remove('ws-badge--completed', 'ws-badge--ontrack', 'ws-badge--behind', 'ws-badge--notrecorded');
    remaining.classList.remove('ws-remaining--khatam');

    let statusKey;
    if (val <= 0) {
        badge.textContent = 'Not Recorded';
        badge.classList.add('ws-badge--notrecorded');
        remaining.textContent = '—';
        statusKey = 'notrecorded';
    } else if (input.dataset.khatam === '1') {
        // Already khatam — Muqarrar fulfilled; page is revision, never compared to checkpoint
        badge.textContent = 'Completed';
        badge.classList.add('ws-badge--completed');
        input.classList.add('is-completed');
        remaining.textContent = 'Passed Muqarrar (Khatam)';
        remaining.classList.add('ws-remaining--khatam');
        statusKey = 'khatam';
    } else if (checkpoint > 0 && checkpoint === endPage && val >= endPage) {
        // Final checkpoint: student has completed the weekly target
        badge.textContent = 'Completed';
        badge.classList.add('ws-badge--completed');
        input.classList.add('is-completed');
        remaining.textContent = '0 pages left';
        statusKey = 'completed';
    } else if (checkpoint > 0 && val > checkpoint) {
        badge.textContent = 'Ahead';
        badge.classList.add('ws-badge--completed');
        input.classList.add('is-completed');
        remaining.textContent = endPage > 0 ? Math.max(0, endPage - val) + ' pages left' : '—';
        statusKey = 'completed';
    } else if (checkpoint > 0 && val === checkpoint) {
        badge.textContent = 'On-track';
        badge.classList.add('ws-badge--ontrack');
        input.classList.add('is-ontrack');
        remaining.textContent = endPage > 0 ? Math.max(0, endPage - val) + ' pages left' : '—';
        statusKey = 'ontrack';
    } else {
        badge.textContent = 'Behind';
        badge.classList.add('ws-badge--behind');
        input.classList.add('is-behind');
        remaining.textContent = endPage > 0 ? Math.max(0, endPage - val) + ' pages left' : '—';
        statusKey = 'behind';
    }

    updateGauge(studentId, val, statusKey);
}

const statusColors = {
    'Completed':    { bg: '#d1fae5', color: '#059669' },
    'Ahead':        { bg: '#d1fae5', color: '#059669' },
    'On-track':     { bg: '#dbeafe', color: '#2563eb' },
    'Behind':       { bg: '#fee2e2', color: '#dc2626' },
    'Not Recorded': { bg: '#f1f5f9', color: '#94a3b8' },
};

function renderHistRows(rows, body) {
    body.innerHTML = rows.length
        ? rows.map(h => {
            const sc    = statusColors[h.status] || statusColors['Not Recorded'];
            const badge = `<span style="display:inline-block;padding:2px 8px;border-radius:99px;font-size:10px;font-weight:600;background:${sc.bg};color:${sc.color}">${h.status}</span>`;
            const remaining = h.remaining === null ? '—'
                : h.remaining === 0 ? '0 pages left'
                : h.remaining + ' pages left';
            return `<tr>
                <td>${h.date}</td>
                <td>${h.reached !== '-' ? 'Pg ' + h.reached : '—'}</td>
                <td>${h.checkpoint !== '-' ? 'Pg ' + h.checkpoint : '—'}</td>
                <td style="color:#64748b;font-size:11px;">${remaining}</td>
                <td>${badge}</td>
              </tr>`;
          }).join('')
        : '<tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:20px">No history yet.</td></tr>';
}

async function openHistModal(studentId, studentName) {
    const body = document.getElementById('histModalBody');
    document.getElementById('histModalName').textContent = studentName;
    body.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:20px">Loading…</td></tr>';
    document.getElementById('histModal').classList.add('open');

    try {
        const resp = await fetch(historyUrl + '?student_id=' + encodeURIComponent(studentId));
        if (!resp.ok) throw new Error('Network error');
        const rows = await resp.json();
        renderHistRows(rows, body);
    } catch (e) {
        body.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#ef4444;padding:20px">Failed to load history.</td></tr>';
    }
}

function closeHistModal() {
    document.getElementById('histModal').classList.remove('open');
}

document.getElementById('histModal').addEventListener('click', function(e) {
    if (e.target === this) closeHistModal();
});

document.addEventListener('click', function(e) {
    const btn = e.target.closest('.js-hist-btn');
    if (btn) openHistModal(btn.dataset.studentId, btn.dataset.studentName);
});

// Search auto-submit with debounce so the page doesn't reload on every keystroke
(function () {
    const searchInput = document.getElementById('hifzSearchInput');
    const filterForm  = document.getElementById('hifzFilterForm');
    if (!searchInput || !filterForm) return;
    let debounceTimer;
    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => filterForm.submit(), 500);
    });
}());
</script>
@endsection
