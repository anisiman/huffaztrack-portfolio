@extends('layouts.admin')

@section('title', 'Attendance')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-attendance.css') }}">
@endpush

@section('content')
<div class="admin-attendance-page">

    <div>
        <h1 class="admin-attendance-page__title">Attendance</h1>
        <p class="admin-attendance-page__subtitle">Daily attendance monitoring grouped by lecturer and batch.</p>
    </div>

    @if (session('success'))
        <div style="background:#dcfce7;color:#15803d;padding:10px 16px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:1rem;">
            {{ session('success') }}
        </div>
    @endif


    {{-- ── SUMMARY CARDS ── --}}
    <section class="admin-attendance-summary">
        <div class="admin-attendance-summary__heading">
            <div>
                <h2 class="admin-attendance-summary__title">Today's Snapshot</h2>
                <p class="admin-attendance-summary__subtitle">{{ $selectedDate->format('d M Y') }} &mdash; {{ $totalStudents }} operational students</p>
            </div>
            <span class="admin-attendance-summary__badge">
                {{ $notRecordedCount }} not yet recorded
            </span>
        </div>

        <div class="admin-attendance-summary__grid">
            @foreach ($summaryCards as $card)
                @php
                    $cardUrl = route('admin.attendance', array_filter([
                        'date'   => $selectedDate->format('Y-m-d'),
                        'batch'  => $selectedBatch,
                        'search' => $search,
                        'status' => $card['status'],
                    ], fn ($v) => $v !== null && $v !== ''));
                    $cardActive = $selectedStatus === ($card['status'] ?? '');
                @endphp
                <a href="{{ $cardUrl }}" class="admin-attendance-card{{ $cardActive ? ' admin-attendance-card--active' : '' }}" style="text-decoration:none;display:block;cursor:pointer;">
                    <div class="admin-attendance-card__icon admin-attendance-card__icon--{{ $card['theme'] }}">
                        @if ($card['icon'] === 'check')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 2.25 2.25L15 9.75" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        @elseif ($card['icon'] === 'cross')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m15 9-6 6m0-6 6 6" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        @elseif ($card['icon'] === 'medical')
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5v6.75A2.25 2.25 0 0 1 17.25 19.5H6.75A2.25 2.25 0 0 1 4.5 17.25V10.5m15 0V6.75A2.25 2.25 0 0 0 17.25 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v3.75m15 0h-15" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v4.5m2.25-2.25h-4.5" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                        @endif
                    </div>
                    <p class="admin-attendance-card__label">{{ $card['label'] }}</p>
                    <p class="admin-attendance-card__value">{{ $card['value'] }}</p>
                    <p class="admin-attendance-card__note">{{ $card['note'] }}</p>
                </a>
            @endforeach
        </div>
    </section>

      {{-- ── FILTERS ── --}}
    <form class="admin-attendance-toolbar" method="GET" action="{{ route('admin.attendance') }}">
        <label class="admin-attendance-search">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search lecturer, student, ID, IC number, batch">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.75 17.25a7.5 7.5 0 0 0 9.9-.6Z" />
            </svg>
        </label>

        <label class="admin-attendance-tool">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 8.25h16.5M4.5 6.75h15A2.25 2.25 0 0 1 21.75 9v9.75A2.25 2.25 0 0 1 19.5 21h-15a2.25 2.25 0 0 1-2.25-2.25V9A2.25 2.25 0 0 1 4.5 6.75Z" />
            </svg>
            <input type="date" name="date" value="{{ $selectedDate->format('Y-m-d') }}"
                   max="{{ now()->format('Y-m-d') }}"
                   onchange="this.form.submit()">
        </label>

        <label class="admin-attendance-tool admin-attendance-tool--select">
            <select name="batch" onchange="this.form.submit()">
                <option value="">All Batches</option>
                @foreach ($batchOptions as $batch)
                    <option value="{{ $batch }}" {{ $selectedBatch === $batch ? 'selected' : '' }}>{{ $batch }}</option>
                @endforeach
            </select>
        </label>

        <label class="admin-attendance-tool admin-attendance-tool--select">
            <select name="status" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="present"            {{ $selectedStatus === 'present'            ? 'selected' : '' }}>Present</option>
                <option value="absent"             {{ $selectedStatus === 'absent'             ? 'selected' : '' }}>Absent (TH)</option>
                <option value="absent_with_reason" {{ $selectedStatus === 'absent_with_reason' ? 'selected' : '' }}>Excused (THS)</option>
                <option value="not_scanned"        {{ $selectedStatus === 'not_scanned'        ? 'selected' : '' }}>Not Recorded</option>
            </select>
        </label>

        <a class="admin-attendance-button admin-attendance-button--ghost" href="{{ route('admin.attendance') }}">Clear</a>
        <button class="admin-attendance-button" type="submit">Apply</button>
    </form>

    @php
        $statusTitles = [
            'present'            => 'Present Students',
            'absent'             => 'Absent (TH) Students',
            'absent_with_reason' => 'Excused (THS) Students',
        ];
    @endphp

    @if ($selectedStatus === '')
        {{-- ── LECTURER GROUP TABLE ── --}}
        <section class="admin-attendance-panel">
            <div class="admin-attendance-panel__header">
                <div>
                    <h2 class="admin-attendance-panel__title">Attendance Overview</h2>
                    <p class="admin-attendance-panel__subtitle">Grouped by lecturer &amp; batch &mdash; {{ $selectedDate->format('d M Y') }}.</p>
                </div>
                <div class="admin-attendance-panel__meta">
                    <span class="admin-attendance-panel__chip">{{ $lecturerRows->count() }} lecturer groups</span>
                </div>
            </div>

            <div class="admin-attendance-table-wrap">
                <table class="admin-attendance-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Lecturer</th>
                            <th>Batch</th>
                            <th style="text-align:center;">Total</th>
                            <th style="text-align:center;">Present</th>
                            <th style="text-align:center;">Absent (TH)</th>
                            <th style="text-align:center;">Excused (THS)</th>
                            <th style="min-width:110px;">Rate</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lecturerRows as $index => $row)
                            @php
                                $detailUrl = route('admin.attendance.details', [
                                    'lecturer_id' => $row->lecturer_id,
                                    'batch_id'    => $row->batch_id,
                                    'date'        => $selectedDate->format('Y-m-d'),
                                    'list_batch'  => $selectedBatch,
                                    'list_status' => $selectedStatus,
                                    'list_search' => $search,
                                ]);
                            @endphp
                            <tr class="admin-attendance-row" onclick="window.location='{{ $detailUrl }}'" style="cursor:pointer;">
                                <td class="admin-attendance-table__strong">{{ $index + 1 }}</td>
                                <td><div style="font-weight:600;color:#0f172a;">{{ $row->lecturer_name }}</div></td>
                                <td>
                                    <span style="background:#ede9fe;color:#6d28d9;border-radius:6px;padding:2px 10px;font-size:12px;font-weight:600;">
                                        {{ $row->batch }}
                                    </span>
                                </td>
                                <td style="text-align:center;font-weight:600;color:#0f172a;">{{ $row->total_students }}</td>
                                <td style="text-align:center;"><span style="color:#059669;font-weight:700;">{{ $row->present_count }}</span></td>
                                <td style="text-align:center;"><span style="color:#dc2626;font-weight:700;">{{ $row->absent_count }}</span></td>
                                <td style="text-align:center;"><span style="color:#0369a1;font-weight:700;">{{ $row->excused_count }}</span></td>
                                <td>
                                    @php
                                        $rate = $row->attendance_rate;
                                        $rateColor = $rate >= 80 ? '#10b981' : ($rate >= 60 ? '#f59e0b' : '#ef4444');
                                    @endphp
                                    <div class="admin-attendance-progress-bar">
                                        <div class="admin-attendance-progress-bar__fill"
                                             style="width:{{ $rate }}%;background:{{ $rateColor }};"></div>
                                    </div>
                                    <div style="font-size:11px;color:#94a3b8;margin-top:2px;">{{ $rate }}%</div>
                                </td>
                                <td>
                                    <a href="{{ $detailUrl }}" class="admin-attendance-detail-button" style="text-decoration:none;display:inline-block;" onclick="event.stopPropagation();">View Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="admin-attendance-table__strong">No records found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @else
        {{-- ── FLAT STUDENT LIST (filtered by status card) ── --}}
        <section class="admin-attendance-panel">
            <div class="admin-attendance-panel__header">
                <div>
                    <h2 class="admin-attendance-panel__title">{{ $statusTitles[$selectedStatus] ?? 'Students' }}</h2>
                    <p class="admin-attendance-panel__subtitle">{{ $selectedDate->format('d M Y') }} &mdash; each student's lecturer is shown alongside their name.</p>
                </div>
                <div class="admin-attendance-panel__meta">
                    <span class="admin-attendance-panel__chip">{{ $tableRows->count() }} students</span>
                    <a href="{{ route('admin.attendance', array_filter(['date' => $selectedDate->format('Y-m-d'), 'batch' => $selectedBatch, 'search' => $search])) }}"
                       class="admin-attendance-button admin-attendance-button--ghost" style="margin-left:.5rem;">Back to Overview</a>
                </div>
            </div>

            <div class="admin-attendance-table-wrap">
                <table class="admin-attendance-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Student Name</th>
                            <th>Student ID</th>
                            <th>Lecturer</th>
                            <th>Batch</th>
                            <th>Check-in</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tableRows as $index => $s)
                            @php
                                $detailUrl = route('admin.attendance.details', [
                                    'lecturer_id' => $s->lecturer_id,
                                    'batch_id'    => $s->batch_id,
                                    'date'        => $selectedDate->format('Y-m-d'),
                                    'list_batch'  => $selectedBatch,
                                    'list_status' => $selectedStatus,
                                    'list_search' => $search,
                                ]);
                            @endphp
                            <tr class="admin-attendance-row" onclick="window.location='{{ $detailUrl }}'" style="cursor:pointer;">
                                <td class="admin-attendance-table__strong">{{ $index + 1 }}</td>
                                <td><div style="font-weight:600;color:#0f172a;">{{ $s->student_name }}</div></td>
                                <td>{{ $s->student_id }}</td>
                                <td>{{ $s->lecturer_name }}</td>
                                <td>
                                    <span style="background:#ede9fe;color:#6d28d9;border-radius:6px;padding:2px 10px;font-size:12px;font-weight:600;">
                                        {{ $s->batch }}
                                    </span>
                                </td>
                                <td>{{ $s->check_in }}</td>
                                <td><span class="att-status att-status--{{ $s->status }}">{{ $s->status_label }}</span></td>
                                <td>
                                    <a href="{{ $detailUrl }}" class="admin-attendance-detail-button" style="text-decoration:none;display:inline-block;" onclick="event.stopPropagation();">View Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="admin-attendance-table__strong">No students found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif

</div>
@endsection
