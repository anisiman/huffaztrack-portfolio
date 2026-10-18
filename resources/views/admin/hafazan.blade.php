@extends('layouts.admin')

@section('title', 'Hafazan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/hifz-record.css') }}">
    <style>
        .hifz-tabs { display: flex; gap: 4px; margin-bottom: 24px; border-bottom: 1px solid #e2e8f0; }
        .hifz-tab { padding: 10px 20px; font-size: 13px; font-weight: 500; color: #94a3b8; border: none; background: none; cursor: pointer; border-bottom: 2px solid transparent; transition: color .2s, border-color .2s; text-decoration: none; }
        .hifz-tab.active { color: #7c3aed; border-bottom-color: #7c3aed; }
        .hifz-tab:hover:not(.active) { color: #64748b; }

        .hifz-stat { transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease; }
        .hifz-stat:hover { border-color: #c4b5fd; box-shadow: 0 4px 14px rgba(124,58,237,.14); transform: translateY(-1px); }
        .hifz-stat--active { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,.15); }

        .badge { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 600; }
        .badge--completed { background: #d1fae5; color: #059669; }
        .badge--on-track  { background: #dbeafe; color: #2563eb; }
        .badge--behind    { background: #fee2e2; color: #dc2626; }
        .badge--upcoming  { background: #f1f5f9; color: #64748b; }
        .badge--progress  { background: #ede9fe; color: #7c3aed; }

        .progress-bar { height: 5px; background: #e2e8f0; border-radius: 99px; overflow: hidden; margin-top: 4px; }
        .progress-bar__fill { height: 100%; background: #7c3aed; border-radius: 99px; }

        .btn-detail { background: #ede9fe; color: #7c3aed; border: 1px solid #c4b5fd; border-radius: 7px; padding: 4px 12px; font-size: 12px; cursor: pointer; }
        .btn-detail:hover { background: #ddd6fe; }

        /* Modal */
        .hifz-modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; align-items: center; justify-content: center; }
        .hifz-modal-backdrop.open { display: flex; }
        .hifz-modal { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 28px; width: 680px; max-width: 96vw; max-height: 80vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(15,23,42,0.15); }
        .hifz-modal__title { font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .hifz-modal__sub   { font-size: 13px; color: #94a3b8; margin-bottom: 20px; }
        .hifz-modal__close { float: right; background: none; border: none; color: #94a3b8; font-size: 22px; cursor: pointer; line-height: 1; }
        .hifz-modal__close:hover { color: #0f172a; }
        .hifz-modal__section { margin-bottom: 20px; }
        .hifz-modal__section-title { font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8; margin-bottom: 10px; }

        .muq-form { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
        .muq-form__grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
        .muq-form label { font-size: 11px; color: #64748b; display: block; margin-bottom: 5px; }
        .muq-form input, .muq-form select { width: 100%; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; color: #0f172a; font-size: 13px; outline: none; }
        .muq-form input:focus, .muq-form select:focus { border-color: #7c3aed; }
        .muq-hint { font-size: 11px; margin-top: 5px; min-height: 15px; line-height: 1.4; color: #94a3b8; }
        .muq-hint--auto { color: #7c3aed; }
        .muq-hint--warn { color: #f59e0b; }
        .muq-hint--ok   { color: #10b981; }
        .muq-suggest-bar { display: none; margin-top: 14px; padding: 10px 14px; background: #ede9fe; border: 1px solid #c4b5fd; border-radius: 8px; font-size: 12px; color: #6d28d9; }

        /* Muqarrar batch group header row */
        .muq-group-header td { background: #f8fafc; border-top: 2px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; padding: 7px 16px; }
        .muq-group-header td:first-child { border-left: 3px solid #7c3aed; }
        .muq-group-label { font-size: 12px; font-weight: 700; color: #3730a3; text-transform: uppercase; letter-spacing: 0.6px; }
        .muq-group-count { font-size: 11px; color: #94a3b8; margin-left: 8px; font-weight: 400; }
        .muq-group-badge { display: inline-flex; align-items: center; gap: 5px; background: #ede9fe; color: #6d28d9; border-radius: 6px; padding: 2px 10px; font-size: 11px; font-weight: 600; }
    </style>
@endpush

@section('content')
<div class="hifz-page">
    <div>
        <h1 class="hifz-page__title">Hafazan Record</h1>
        <p class="hifz-page__subtitle">Overview of all student hafazan progress and targets</p>
    </div>

    @if (session('success'))
        <div class="hifz-notice hifz-notice--success" style="margin-bottom: 16px;">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="hifz-notice hifz-notice--error" style="margin-bottom: 16px;">{{ session('error') }}</div>
    @endif

    {{-- Tabs --}}
    <div class="hifz-tabs">
        <a href="{{ route('admin.hafazan', array_merge(request()->query(), ['tab' => 'progress'])) }}"
           class="hifz-tab {{ $tab === 'progress' ? 'active' : '' }}">Student Progress</a>
        <a href="{{ route('admin.hafazan', array_merge(request()->query(), ['tab' => 'muqarrar'])) }}"
           class="hifz-tab {{ $tab === 'muqarrar' ? 'active' : '' }}">Muqarrar Targets</a>
    </div>

    {{-- ══════════ PROGRESS TAB ══════════ --}}
    @if ($tab === 'progress')

        {{-- Today's Hafazan Snapshot --}}
        <section class="hifz-snapshot">
            <div class="hifz-snapshot__heading">
                <div>
                    <h2 class="hifz-snapshot__title">Today's Hafazan Snapshot</h2>
                    <p class="hifz-snapshot__subtitle">{{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }} &mdash; {{ $totalStudentCount }} students tracked</p>
                </div>
                <span class="hifz-snapshot__badge">{{ $notRecordedCount }} not recorded</span>
            </div>
            @php
                $hafazanCardBaseParams = array_filter([
                    'tab'         => 'progress',
                    'date'        => $selectedDate,
                    'lecturer_id' => $filterLecturer,
                    'halaqah'     => $filterHalaqah,
                    'search'      => $search,
                ], fn ($v) => $v !== '' && $v !== null);
                $hafazanStatusCards = [
                    ['label' => 'Total Students', 'value' => $totalStudentCount, 'theme' => 'purple', 'status' => null,           'icon' => 'people'],
                    ['label' => 'Ahead',          'value' => $aheadCount,        'theme' => 'green',  'status' => 'Ahead',        'icon' => 'up'],
                    ['label' => 'On-track',       'value' => $onTrackCount,      'theme' => 'blue',   'status' => 'On-track',     'icon' => 'check'],
                    ['label' => 'Behind',         'value' => $behindCount,       'theme' => 'red',    'status' => 'Behind',       'icon' => 'warn'],
                    ['label' => 'Not Recorded',   'value' => $notRecordedCount,  'theme' => 'slate',  'status' => 'Not Recorded', 'icon' => 'doc'],
                ];
            @endphp
            <div class="hifz-snapshot__grid">
                @foreach ($hafazanStatusCards as $card)
                    @php
                        $cardUrl = route('admin.hafazan', array_merge($hafazanCardBaseParams, array_filter(['status' => $card['status']], fn ($v) => $v !== null)));
                        $cardActive = $filterStatus === ($card['status'] ?? '');
                    @endphp
                    <a href="{{ $cardUrl }}" class="hifz-stat{{ $cardActive ? ' hifz-stat--active' : '' }}" style="text-decoration:none;display:block;cursor:pointer;">
                        <div class="hifz-stat__icon hifz-stat__icon--{{ $card['theme'] }}">
                            @if ($card['icon'] === 'people')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                </svg>
                            @elseif ($card['icon'] === 'up')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18" />
                                </svg>
                            @elseif ($card['icon'] === 'check')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 2.25 2.25L15 9.75" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            @elseif ($card['icon'] === 'warn')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                </svg>
                            @endif
                        </div>
                        <p class="hifz-stat__label">{{ $card['label'] }}</p>
                        <p class="hifz-stat__value{{ $card['theme'] !== 'purple' ? ' hifz-stat__value--' . $card['theme'] : '' }}">{{ $card['value'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Muqarrar context bar --}}
        @if ($batchMuqarrars->isNotEmpty())
        <div style="background:#f5f3ff;border:1px solid #ddd6fe;border-radius:14px;padding:12px 16px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                <span style="font-size:10.5px;font-weight:700;color:#7c3aed;text-transform:uppercase;letter-spacing:0.7px;">Week Target</span>
                <span style="font-size:11px;color:#94a3b8;">{{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</span>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                @foreach ($batchMuqarrars as $entry)
                    @php $m = $entry['muqarrar']; @endphp
                    <div style="background:#fff;border:1px solid #ddd6fe;border-radius:10px;padding:10px 14px;min-width:130px;">
                        <div style="margin-bottom:6px;">
                            <span style="display:inline-flex;align-items:center;background:#ede9fe;color:#6d28d9;border-radius:6px;padding:2px 10px;font-size:11px;font-weight:700;">{{ implode(', ', $entry['batch_names']) }}</span>
                        </div>
                        <div style="font-size:11.5px;color:#64748b;">Week {{ $m->week }}&ensp;&middot;&ensp;Pg {{ $m->start_page }}–{{ $m->end_page }}</div>
                        <div style="font-size:13px;font-weight:700;color:#5b21b6;margin-top:5px;">Today: Pg {{ $entry['checkpoint'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.hafazan') }}" class="hifz-toolbar">
            <input type="hidden" name="tab" value="progress">

            <input type="date" name="date" value="{{ $selectedDate }}" max="{{ now()->format('Y-m-d') }}"
                   onchange="this.form.submit()"
                   style="height:36px;padding:0 10px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;color:#334155;background:#f8fafc;outline:none;cursor:pointer;">

            <label class="hifz-tool hifz-tool--select">
                <select name="lecturer_id" onchange="this.form.submit()">
                    <option value="">All Lecturers</option>
                    @foreach ($lecturers as $lect)
                        <option value="{{ $lect->lecturer_id }}" {{ $filterLecturer == $lect->lecturer_id ? 'selected' : '' }}>
                            {{ $lect->lecturer_name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="hifz-tool hifz-tool--select">
                <select name="halaqah" onchange="this.form.submit()">
                    <option value="">All Batch</option>
                    @foreach ($halaqahOptions as $h)
                        <option value="{{ $h }}" {{ $filterHalaqah === $h ? 'selected' : '' }}>{{ $h }}</option>
                    @endforeach
                </select>
            </label>

            <label class="hifz-tool hifz-tool--select">
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="Ahead"        {{ $filterStatus === 'Ahead'        ? 'selected' : '' }}>Ahead</option>
                    <option value="On-track"     {{ $filterStatus === 'On-track'     ? 'selected' : '' }}>On-track</option>
                    <option value="Behind"       {{ $filterStatus === 'Behind'       ? 'selected' : '' }}>Behind Only</option>
                    <option value="Not Recorded" {{ $filterStatus === 'Not Recorded' ? 'selected' : '' }}>Not Recorded</option>
                </select>
            </label>

            <label class="hifz-search" style="max-width:200px;">
                <input type="text" name="search" value="{{ $search }}" placeholder="Student, lecturer, or batch…">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.75 17.25a7.5 7.5 0 0 0 9.9-.6Z" />
                </svg>
            </label>

            <button type="submit" class="hifz-button">Filter</button>
            @if ($search || $filterLecturer || $filterStatus || $filterHalaqah || $selectedDate !== now()->format('Y-m-d'))
                <a href="{{ route('admin.hafazan', ['tab' => 'progress']) }}" class="hifz-button" style="background:#f1f5f9;color:#64748b;box-shadow:none;">Clear</a>
            @endif
        </form>

        @php
            $hafazanDetailBaseParams = [
                'date'          => $selectedDate,
                'list_lecturer' => $filterLecturer,
                'list_halaqah'  => $filterHalaqah,
                'list_status'   => $filterStatus,
                'list_search'   => $search,
            ];
        @endphp

        @if ($filterStatus === '')
            {{-- Batch-grouped table --}}
            <section class="hifz-table-panel">
                <div class="hifz-table-panel__header">
                    <div>
                        <h2 class="hifz-table-panel__title">Progress Overview</h2>
                        <p class="hifz-table-panel__subtitle">Grouped by lecturer &amp; batch &mdash; {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</p>
                    </div>
                    <span class="hifz-table-panel__chip">{{ $halaqahGroups->count() }} {{ $halaqahGroups->count() === 1 ? 'group' : 'groups' }}</span>
                </div>
                <div class="hifz-table-wrap">
                    <table class="hifz-table">
                        <thead>
                            <tr>
                                <th>Lecturer</th>
                                <th>Batch</th>
                                <th style="text-align:center;">Students</th>
                                <th style="text-align:center;">Ahead</th>
                                <th style="text-align:center;">On-track</th>
                                <th style="text-align:center;">Behind</th>
                                <th style="text-align:center;">Not Recorded</th>
                                <th style="min-width:110px;">Students Meeting Target</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($halaqahGroups as $group)
                                @php
                                    $pct      = $group['progress_pct'];
                                    $barColor = $pct >= 80 ? '#10b981' : ($pct >= 60 ? '#f59e0b' : '#ef4444');
                                    $detailUrl = route('admin.hafazan.details', array_merge($hafazanDetailBaseParams, [
                                        'lecturer_id' => $group['lecturer_id'],
                                        'batch_id'    => $group['batch_id'],
                                    ]));
                                @endphp
                                <tr>
                                    <td>
                                        <div style="font-weight:600;color:#0f172a;">{{ $group['lecturer_name'] }}</div>
                                    </td>
                                    <td>
                                        <span style="background:#ede9fe;color:#6d28d9;border-radius:6px;padding:2px 10px;font-size:12px;font-weight:600;">
                                            {{ $group['batch_name'] }}
                                        </span>
                                    </td>
                                    <td style="text-align:center;font-weight:600;color:#0f172a;">{{ $group['total'] }}</td>
                                    <td style="text-align:center;">
                                        @if ($group['ahead'] > 0)
                                            <span style="color:#059669;font-weight:700;">{{ $group['ahead'] }}</span>
                                        @else
                                            <span style="color:#cbd5e1;">0</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;">
                                        @if ($group['on_track'] > 0)
                                            <span style="color:#2563eb;font-weight:700;">{{ $group['on_track'] }}</span>
                                        @else
                                            <span style="color:#cbd5e1;">0</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;">
                                        @if ($group['behind'] > 0)
                                            <span style="color:#ef4444;font-weight:700;">{{ $group['behind'] }}</span>
                                        @else
                                            <span style="color:#cbd5e1;">0</span>
                                        @endif
                                    </td>
                                    <td style="text-align:center;">
                                        @if ($group['not_recorded'] > 0)
                                            <span style="color:#94a3b8;font-weight:600;">{{ $group['not_recorded'] }}</span>
                                        @else
                                            <span style="color:#cbd5e1;">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <div class="progress-bar">
                                                <div class="progress-bar__fill" style="width:{{ $pct }}%;background:{{ $barColor }};"></div>
                                            </div>
                                            <div style="font-size:11px;color:#94a3b8;margin-top:2px;">{{ $pct }}%</div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ $detailUrl }}" class="btn-detail" style="text-decoration:none;display:inline-block;">View Details</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" style="text-align:center;padding:40px;color:#94a3b8;">
                                        No batch groups found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @else
            {{-- Flat student list (filtered by status card) ── each row shows its lecturer directly --}}
            @php
                $flatStatusTitles = [
                    'Ahead'        => 'Ahead Students',
                    'On-track'     => 'On-track Students',
                    'Behind'       => 'Behind Students',
                    'Not Recorded' => 'Not Recorded Students',
                ];
            @endphp
            <section class="hifz-table-panel">
                <div class="hifz-table-panel__header">
                    <div>
                        <h2 class="hifz-table-panel__title">{{ $flatStatusTitles[$filterStatus] ?? 'Students' }}</h2>
                        <p class="hifz-table-panel__subtitle">{{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }} &mdash; each student's lecturer is shown alongside their name.</p>
                    </div>
                    <div style="display:flex;align-items:center;gap:.5rem;">
                        <span class="hifz-table-panel__chip">{{ $flatStudents->count() }} students</span>
                        <a href="{{ route('admin.hafazan', array_filter(['tab' => 'progress', 'date' => $selectedDate, 'lecturer_id' => $filterLecturer, 'halaqah' => $filterHalaqah, 'search' => $search])) }}"
                           class="hifz-button" style="background:#f1f5f9;color:#64748b;box-shadow:none;">Back to Overview</a>
                    </div>
                </div>
                <div class="hifz-table-wrap">
                    <table class="hifz-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Lecturer</th>
                                <th>Batch</th>
                                <th>Latest Page</th>
                                <th>Remaining</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $flatBadge = [
                                    'Ahead'        => 'badge--completed',
                                    'On-track'     => 'badge--on-track',
                                    'Behind'       => 'badge--behind',
                                    'Not Recorded' => 'badge--upcoming',
                                ];
                            @endphp
                            @forelse ($flatStudents as $s)
                                @php
                                    $detailUrl = route('admin.hafazan.details', array_merge($hafazanDetailBaseParams, [
                                        'lecturer_id' => $s['lecturer_id'],
                                        'batch_id'    => $s['batch_id'],
                                    ]));
                                @endphp
                                <tr>
                                    <td>
                                        <div style="font-weight:600;color:#0f172a;">{{ $s['student_name'] }}@if (! empty($s['khatam_count'])) <span style="display:inline-block;margin-left:6px;padding:1px 7px;border-radius:999px;background:#fef3c7;color:#b45309;font-size:0.68rem;font-weight:700;white-space:nowrap;" title="Muqarrar already fulfilled">🏆 Khatam x{{ $s['khatam_count'] }}</span>@endif</div>
                                        <div style="font-size:11px;color:#94a3b8;">{{ $s['student_id'] }}</div>
                                    </td>
                                    <td>{{ $s['lecturer_name'] }}</td>
                                    <td>
                                        <span style="background:#ede9fe;color:#6d28d9;border-radius:6px;padding:2px 10px;font-size:12px;font-weight:600;">
                                            {{ $s['batch_name'] }}
                                        </span>
                                    </td>
                                    <td>{{ $s['latest_page'] ? 'Pg ' . $s['latest_page'] : '—' }}</td>
                                    <td style="color:#64748b;">@if (! empty($s['khatam_count']) && $s['latest_page'])<span style="color:#059669;font-weight:600;">Passed Muqarrar (Khatam)</span>@else{{ $s['remaining'] !== null ? $s['remaining'] . ' pages left' : '—' }}@endif</td>
                                    <td><span class="badge {{ $flatBadge[$s['status']] ?? 'badge--upcoming' }}">{{ $s['status'] }}</span></td>
                                    <td>
                                        <a href="{{ $detailUrl }}" class="btn-detail" style="text-decoration:none;display:inline-block;">View Details</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align:center;padding:40px;color:#94a3b8;">
                                        No students found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

    @endif

    {{-- ══════════ MUQARRAR TAB ══════════ --}}
    @if ($tab === 'muqarrar')

        {{-- Add form --}}
        <div class="muq-form">
            <h3 style="font-size: 14px; font-weight: 600; color: #0f172a; margin-bottom: 4px;">Add Muqarrar Week</h3>
            <p style="font-size: 12px; color: #94a3b8; margin-bottom: 14px;">Select a batch and week — the system will suggest page ranges based on previous weeks.</p>

            @if (! $activeAcademicPeriod)
                <div class="hifz-notice hifz-notice--error" style="margin-bottom: 14px;">
                    No active academic semester is currently available. Please activate an academic semester in Academic Semester Management before adding Muqarrar targets.
                </div>
            @endif

            <form method="POST" action="{{ route('admin.hafazan.muqarrar.store') }}">
                @csrf
                <div class="muq-form__grid" style="grid-template-columns: repeat(3, 1fr);">
                    <div>
                        <label>Academic Semester</label>
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;font-size:13px;color:#0f172a;font-weight:600;">
                            {{ $activeAcademicPeriod->name ?? '— None active —' }}
                        </div>
                        <p class="muq-hint">Automatically selected from the active academic semester.</p>
                    </div>
                    <div>
                        <label>Batch</label>
                        <select id="mq-batch" name="batch_id" required onchange="muqSuggest()">
                            <option value="">Select batch</option>
                            @foreach ($batches as $batch)
                                <option value="{{ $batch->batch_id }}">{{ $batch->batch_name }}</option>
                            @endforeach
                        </select>
                        <p class="muq-hint">Which batch this muqarrar target applies to.</p>
                    </div>
                    <div>
                        <label>Week</label>
                        <input id="mq-week" type="number" name="week" min="1" required placeholder="e.g. 1" oninput="muqSuggest()">
                        <p class="muq-hint" id="mq-week-hint"></p>
                    </div>
                    <div>
                        <label>Week Start Date <span style="color:#94a3b8;">(Monday)</span></label>
                        <input id="mq-week-start" type="date" name="week_start_date" onchange="muqSuggest()">
                        <p class="muq-hint">Sets which calendar week this muqarrar covers.</p>
                    </div>
                    <div>
                        <label>Start Page</label>
                        <input id="mq-start" type="number" name="start_page" min="0" required placeholder="e.g. 481" oninput="muqStartChanged()">
                        <p class="muq-hint" id="mq-start-hint"></p>
                    </div>
                    <div>
                        <label>End Page</label>
                        <input id="mq-end" type="number" name="end_page" min="1" required placeholder="e.g. 491" oninput="muqEndChanged()">
                        <p class="muq-hint" id="mq-end-hint"></p>
                    </div>
                </div>

                <div class="muq-suggest-bar" id="mq-suggest-bar">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:13px;height:13px;display:inline;vertical-align:middle;margin-right:4px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                    </svg>
                    <span id="mq-suggest-text">Suggested based on previous week muqarrar. You may adjust if needed.</span>
                </div>

                <div style="margin-top: 14px;">
                    <button type="submit" class="hifz-button" {{ $activeAcademicPeriod ? '' : 'disabled' }}>Add Week</button>
                </div>
            </form>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.hafazan') }}" class="hifz-toolbar">
            <input type="hidden" name="tab" value="muqarrar">
            <label class="hifz-tool hifz-tool--select">
                <select name="academic_period_id" onchange="this.form.submit()">
                    <option value="">All Academic Semesters</option>
                    @foreach ($academicPeriods as $period)
                        <option value="{{ $period->id }}" {{ $filterAcademicPeriod == $period->id ? 'selected' : '' }}>
                            {{ $period->name }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label class="hifz-tool hifz-tool--select">
                <select name="week" onchange="this.form.submit()">
                    <option value="">All Weeks</option>
                    @foreach ($muqarrarWeeks as $w)
                        <option value="{{ $w }}" {{ $filterWeek == $w ? 'selected' : '' }}>Week {{ $w }}</option>
                    @endforeach
                </select>
            </label>
            <label class="hifz-tool hifz-tool--select">
                <select name="batch_id" onchange="this.form.submit()">
                    <option value="">All Batches</option>
                    @foreach ($batches as $batch)
                        <option value="{{ $batch->batch_id }}" {{ $filterBatch == $batch->batch_id ? 'selected' : '' }}>
                            {{ $batch->batch_name }}
                        </option>
                    @endforeach
                </select>
            </label>
            
            <button type="submit" class="hifz-button">Filter</button>
        </form>

        {{-- Table --}}
        <section class="hifz-table-panel">
            <div class="hifz-table-panel__header">
                <div>
                    <h2 class="hifz-table-panel__title">Muqarrar Weeks</h2>
                    <p class="hifz-table-panel__subtitle">{{ $muqarrars->count() }} week{{ $muqarrars->count() !== 1 ? 's' : '' }} found</p>
                </div>
            </div>
            <div class="hifz-table-wrap">
                <table class="hifz-table">
                    <thead>
                        <tr>
                            <th>Week</th>
                            <th>Academic Semester</th>
                            <th>Batch</th>
                            <th>Week Start</th>
                            <th>Pages</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // toBase() converts Eloquent Collection → Support Collection so merge()
                            // treats values as plain items instead of calling getKey() on them.
                            $byBatch    = $muqarrars->groupBy(fn ($m) => $m->batch_id ?? '__all__')->toBase();
                            $allFirst   = $byBatch->has('__all__') ? collect(['__all__' => $byBatch['__all__']]) : collect();
                            $restSorted = $byBatch->except('__all__')->sortBy(fn ($rows) => $rows->first()->batch?->batch_name ?? '');
                            $grouped    = $allFirst->merge($restSorted);
                        @endphp

                        @if ($grouped->isEmpty())
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">
                                    No muqarrar weeks found.
                                </td>
                            </tr>
                        @else
                            @foreach ($grouped as $batchKey => $batchRows)
                                @php
                                    $batchLabel = $batchKey === '__all__'
                                        ? 'All Batches — Semester-wide'
                                        : ($batchRows->first()->batch?->batch_name ?? 'Unknown Batch');
                                @endphp
                                <tr class="muq-group-header">
                                    <td colspan="6">
                                        <span class="muq-group-badge">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:11px;height:11px;">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                            </svg>
                                            {{ $batchLabel }}
                                        </span>
                                        <span class="muq-group-count">{{ $batchRows->count() }} week{{ $batchRows->count() !== 1 ? 's' : '' }}</span>
                                    </td>
                                </tr>
                                @foreach ($batchRows as $m)
                                    @php
                                        $badgeClass = match($m->muq_status) {
                                            'Completed'       => 'badge--completed',
                                            'In Progress'     => 'badge--progress',
                                            'Behind Schedule' => 'badge--behind',
                                            default           => 'badge--upcoming',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="hifz-table__strong">Week {{ $m->week }}</td>
                                        <td style="color: #64748b;">
                                            {{ $m->academicPeriod?->name ?? ($m->semester?->semester_name ?? 'Semester ' . $m->semester_id) . ' (legacy)' }}
                                        </td>
                                        <td style="color: #64748b;">{{ $m->batch?->batch_name ?? '—' }}</td>
                                        <td style="color: #64748b;">
                                            {{ $m->week_start_date ? \Carbon\Carbon::parse($m->week_start_date)->format('d M Y') : '—' }}
                                        </td>
                                        <td style="color: #64748b;">{{ $m->start_page }} – {{ $m->end_page }}</td>
                                        <td>
                                            <div class="hifz-actions">
                                                {{-- Edit --}}
                                                <button type="button" class="hifz-action" title="Edit"
                                                        onclick="openEditMuq({{ $m->muqarrar_id }}, '{{ addslashes($m->academicPeriod?->name ?? ($m->semester?->semester_name ?? 'Semester ' . $m->semester_id) . ' (legacy)') }}', {{ $m->batch_id ?? 'null' }}, {{ $m->week }}, '{{ $m->week_start_date ?? '' }}', {{ $m->start_page }}, {{ $m->end_page }})"
                                                        style="color:#7c3aed; border-color:#c4b5fd;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                                    </svg>
                                                </button>
                                                {{-- Delete --}}
                                                <form method="POST" action="{{ route('admin.hafazan.muqarrar.destroy', $m->muqarrar_id) }}"
                                                      onsubmit="return confirmDelete({{ $m->week }}, {{ $m->total_students }})"
                                                      style="display:inline;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="hifz-action hifz-action--delete" title="Delete">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.245-1.327L4.772 5.79m14.456 0A48.108 48.108 0 0 0 15.75 5.25m3.478.54a48.11 48.11 0 0 1-7.956 0m7.956 0L18.75 4.5A2.25 2.25 0 0 0 16.5 2.25h-3A2.25 2.25 0 0 0 11.25 4.5l-.278 1.29" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </section>

    @endif
</div>

{{-- Edit Muqarrar Modal --}}
<div class="hifz-modal-backdrop" id="editMuqModal">
    <div class="hifz-modal" style="width: 560px;">
        <button class="hifz-modal__close" onclick="closeEditMuq()">&times;</button>
        <h2 class="hifz-modal__title">Edit Muqarrar</h2>
        <p class="hifz-modal__sub" id="editMuqSub" style="margin-bottom: 20px;"></p>

        <form id="editMuqForm" method="POST">
            @csrf
            @method('PUT')
            <div class="muq-form__grid" style="grid-template-columns: repeat(3, 1fr);">
                <div>
                    <label style="font-size:11px;color:#64748b;display:block;margin-bottom:5px;">Academic Semester</label>
                    <div id="edit-mq-sem-display"
                         style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;font-size:13px;color:#0f172a;font-weight:600;"></div>
                    <p class="muq-hint">Fixed at creation — not editable here.</p>
                </div>
                <div>
                    <label style="font-size:11px;color:#64748b;display:block;margin-bottom:5px;">Batch</label>
                    <select id="edit-mq-batch" name="batch_id" required
                            style="width:100%;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;font-size:13px;outline:none;">
                        <option value="">Select batch</option>
                        @foreach ($batches as $batch)
                            <option value="{{ $batch->batch_id }}">{{ $batch->batch_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:11px;color:#64748b;display:block;margin-bottom:5px;">Week</label>
                    <input id="edit-mq-week" type="number" name="week" min="1" required
                           style="width:100%;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;font-size:13px;outline:none;">
                </div>
                <div>
                    <label style="font-size:11px;color:#64748b;display:block;margin-bottom:5px;">Week Start Date</label>
                    <input id="edit-mq-week-start" type="date" name="week_start_date"
                           style="width:100%;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;font-size:13px;outline:none;">
                </div>
                <div>
                    <label style="font-size:11px;color:#64748b;display:block;margin-bottom:5px;">Start Page</label>
                    <input id="edit-mq-start" type="number" name="start_page" min="0" required
                           style="width:100%;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;font-size:13px;outline:none;">
                </div>
                <div>
                    <label style="font-size:11px;color:#64748b;display:block;margin-bottom:5px;">End Page</label>
                    <input id="edit-mq-end" type="number" name="end_page" min="1" required
                           style="width:100%;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;font-size:13px;outline:none;"
                           oninput="editMuqPreview()">
                    <p id="edit-mq-end-hint" class="muq-hint" style="margin-top:5px;"></p>
                </div>
            </div>
            <div style="margin-top: 18px; display: flex; gap: 8px;">
                <button type="submit" class="hifz-button">Save Changes</button>
                <button type="button" onclick="closeEditMuq()"
                        class="hifz-button" style="background:#f1f5f9;color:#64748b;box-shadow:none;">Cancel</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// ── Muqarrar edit modal ─────────────────────────────────────────────────────
const muqUpdateBase = '{{ rtrim(route("admin.hafazan.muqarrar.update", ["id" => "__ID__"]), "") }}';

function openEditMuq(id, semesterLabel, batchId, week, weekStartDate, startPage, endPage) {
    document.getElementById('editMuqSub').textContent       = 'Week ' + week + ' · Pg ' + startPage + ' → ' + endPage;
    document.getElementById('edit-mq-sem-display').textContent = semesterLabel;
    document.getElementById('edit-mq-batch').value       = batchId || '';
    document.getElementById('edit-mq-week').value        = week;
    document.getElementById('edit-mq-week-start').value  = weekStartDate || '';
    document.getElementById('edit-mq-start').value       = startPage;
    document.getElementById('edit-mq-end').value         = endPage;
    document.getElementById('editMuqForm').action        = muqUpdateBase.replace('__ID__', id);
    editMuqPreview();
    document.getElementById('editMuqModal').classList.add('open');
}

function closeEditMuq() {
    document.getElementById('editMuqModal').classList.remove('open');
}

function editMuqPreview() {
    const start = parseInt(document.getElementById('edit-mq-start').value) || 0;
    const end   = parseInt(document.getElementById('edit-mq-end').value)   || 0;
    const hint  = document.getElementById('edit-mq-end-hint');
    if (end > start) {
        hint.textContent = 'Weekly target: ' + (end - start) + ' pages.';
        hint.className   = 'muq-hint muq-hint--ok';
    } else if (end > 0 && end <= start) {
        hint.textContent = '⚠ End page must be greater than start page.';
        hint.className   = 'muq-hint muq-hint--warn';
    } else {
        hint.textContent = '';
    }
}

document.getElementById('editMuqModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditMuq();
});

function confirmDelete(week, studentCount) {
    let msg = 'Delete Week ' + week + '?';
    if (studentCount > 0) {
        msg += '\n\nThis will also permanently delete ' + studentCount + ' student tasmi record(s) for this week.';
    }
    return confirm(msg);
}

// ── Muqarrar add-form auto-suggest ──────────────────────────────────────────
const muqData = @json($muqarrarsForJs);
const MQ_DEFAULT_PAGES = 10;

function muqSuggest() {
    const batchId = document.getElementById('mq-batch').value;
    const week  = parseInt(document.getElementById('mq-week').value) || 0;

    const startEl   = document.getElementById('mq-start');
    const endEl     = document.getElementById('mq-end');
    const weekHint  = document.getElementById('mq-week-hint');
    const startHint = document.getElementById('mq-start-hint');
    const endHint   = document.getElementById('mq-end-hint');
    const sugBar    = document.getElementById('mq-suggest-bar');
    const sugText   = document.getElementById('mq-suggest-text');

    [weekHint, startHint, endHint].forEach(el => { el.textContent = ''; el.className = 'muq-hint'; });
    sugBar.style.display = 'none';

    if (!batchId || week < 1) return;

    const batchData = muqData[batchId] || {};
    const existing  = batchData[week];
    const prevWeek  = batchData[week - 1];

    if (existing) {
        weekHint.textContent = '⚠ Week ' + week + ' already exists for this batch — submitting will create a duplicate.';
        weekHint.className   = 'muq-hint muq-hint--warn';
    }

    if (week === 1) {
        weekHint.textContent = weekHint.textContent || 'First week for this batch — enter the starting page manually.';
        weekHint.className   = 'muq-hint';
        return;
    }

    if (!prevWeek) {
        weekHint.textContent = weekHint.textContent || 'Week ' + (week - 1) + ' data not found — cannot auto-fill Week ' + week + ' pages. Enter manually.';
        weekHint.className   = 'muq-hint';
        return;
    }

    const weekStartDate = document.getElementById('mq-week-start').value;
    if (!weekStartDate) {
        weekHint.textContent = weekHint.textContent || 'Select a week start date to auto-fill pages.';
        weekHint.className   = 'muq-hint';
        return;
    }

    const prevRange = prevWeek.end_page - prevWeek.start_page;
    const sugStart  = prevWeek.end_page + 1;
    const sugEnd    = sugStart + prevRange;

    startEl.value = sugStart;
    endEl.value   = sugEnd;

    startHint.textContent = 'Auto-filled: Week ' + (week - 1) + ' ended at Pg ' + prevWeek.end_page + '.';
    startHint.className   = 'muq-hint muq-hint--auto';

    endHint.textContent = 'Weekly target: ' + (prevRange + 1) + ' pages (same as Week ' + (week - 1) + ').';
    endHint.className   = 'muq-hint muq-hint--auto';

    sugBar.style.display = 'block';
    sugText.textContent  = 'Suggested from Week ' + (week - 1) + ' muqarrar (Pg ' + prevWeek.start_page + ' → ' + prevWeek.end_page + '). You may adjust if needed.';
}

function muqStartChanged() {
    const batchId  = document.getElementById('mq-batch').value;
    const week     = parseInt(document.getElementById('mq-week').value) || 0;
    const start    = parseInt(document.getElementById('mq-start').value) || 0;
    const endEl    = document.getElementById('mq-end');
    const startHint = document.getElementById('mq-start-hint');
    const endHint   = document.getElementById('mq-end-hint');

    startHint.textContent = '';
    startHint.className   = 'muq-hint';

    if (start <= 0) { endHint.textContent = ''; return; }

    const batchData = muqData[batchId] || {};
    const prevWeek  = batchData[week - 1];
    const range    = prevWeek ? (prevWeek.end_page - prevWeek.start_page) : MQ_DEFAULT_PAGES;

    endEl.value = start + range;
    endHint.textContent = 'Auto-calculated: ' + (range + 1) + ' pages from start' + (prevWeek ? ' (same as Week ' + (week - 1) + ')' : ' (default)') + '.';
    endHint.className   = 'muq-hint muq-hint--auto';
}

function muqEndChanged() {
    const start = parseInt(document.getElementById('mq-start').value) || 0;
    const end   = parseInt(document.getElementById('mq-end').value)   || 0;
    const hint  = document.getElementById('mq-end-hint');

    if (end > 0 && start > 0 && end > start) {
        hint.textContent = 'Weekly target: ' + (end - start) + ' pages.';
        hint.className   = 'muq-hint muq-hint--ok';
    } else if (end > 0 && start > 0 && end <= start) {
        hint.textContent = '⚠ End page must be greater than start page.';
        hint.className   = 'muq-hint muq-hint--warn';
    } else {
        hint.textContent = '';
        hint.className   = 'muq-hint';
    }
}
</script>
@endpush
@endsection
