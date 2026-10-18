@extends('layouts/admin')

@section('title', 'Subject Attendance')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-attendance.css') }}">
@endpush

@section('content')
<div class="admin-attendance-page">

    <div>
        <h1 class="admin-attendance-page__title">Subject Attendance</h1>
        <p class="admin-attendance-page__subtitle">
            View attendance records for all subject classes. Hifz attendance is tracked separately
            under Tasmik Attendance.
        </p>
    </div>

    @if (session('success'))
        <div style="margin-bottom:1rem;padding:1rem;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;border-radius:0.875rem;font-size:0.875rem;font-weight:600;">
            {{ session('success') }}
        </div>
    @endif

    {{-- ── SUMMARY CARDS ── --}}
    <section class="admin-attendance-summary">
        <div class="admin-attendance-summary__heading">
            <div>
                <h2 class="admin-attendance-summary__title">Attendance Snapshot</h2>
                <p class="admin-attendance-summary__subtitle">
                    {{ $period?->name ?? 'No active Academic Semester' }}
                    @if ($selectedDate) &mdash; {{ $selectedDate->format('d M Y') }} @endif
                </p>
            </div>
            @if ($period)
                <span class="admin-attendance-summary__badge">{{ $period->name }}</span>
            @endif
        </div>

        @php
            $kpiBaseParams = array_filter([
                'academic_period_id' => $period?->id,
                'subjectT_id'        => $filterSubjectId,
                'lecturer_id'        => $filterLecturerId,
                'date'               => $selectedDate?->format('Y-m-d'),
            ], fn ($v) => $v !== null && $v !== '');
        @endphp
        <div class="admin-attendance-summary__grid">
            <a href="{{ route('admin.subjectAttendance', array_merge($kpiBaseParams, ['status' => 'present'])) }}"
               class="admin-attendance-card{{ $filterStatus === 'present' ? ' admin-attendance-card--active' : '' }}" style="text-decoration:none;display:block;cursor:pointer;">
                <div class="admin-attendance-card__icon admin-attendance-card__icon--green">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 2.25 2.25L15 9.75" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <p class="admin-attendance-card__label">Present</p>
                <p class="admin-attendance-card__value">{{ $presentCount }}</p>
            </a>

            <a href="{{ route('admin.subjectAttendance', array_merge($kpiBaseParams, ['status' => 'absent'])) }}"
               class="admin-attendance-card{{ $filterStatus === 'absent' ? ' admin-attendance-card--active' : '' }}" style="text-decoration:none;display:block;cursor:pointer;">
                <div class="admin-attendance-card__icon admin-attendance-card__icon--red">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15 9-6 6m0-6 6 6" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <p class="admin-attendance-card__label">Absent</p>
                <p class="admin-attendance-card__value">{{ $absentCount }}</p>
            </a>

            <a href="{{ route('admin.subjectAttendance', $kpiBaseParams) }}"
               class="admin-attendance-card{{ $filterStatus === '' ? ' admin-attendance-card--active' : '' }}" style="text-decoration:none;display:block;cursor:pointer;">
                <div class="admin-attendance-card__icon admin-attendance-card__icon--amber">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </div>
                <p class="admin-attendance-card__label">Attendance Rate</p>
                <p class="admin-attendance-card__value">{{ $attendanceRate !== null ? $attendanceRate . '%' : '—' }}</p>
            </a>
        </div>
    </section>

    {{-- ── FILTERS ── --}}
    <form class="admin-attendance-toolbar" method="GET" action="{{ route('admin.subjectAttendance') }}">
        <label class="admin-attendance-tool admin-attendance-tool--select">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
            </svg>
            <select name="academic_period_id" onchange="this.form.submit()">
                @foreach ($academicPeriods as $p)
                    <option value="{{ $p->id }}" {{ $period && $period->id === $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </label>

        <label class="admin-attendance-tool admin-attendance-tool--select">
            <select name="subjectT_id" onchange="this.form.submit()">
                <option value="">All Subjects</option>
                @foreach ($subjects as $s)
                    <option value="{{ $s->subjectT_id }}" {{ (string) $filterSubjectId === (string) $s->subjectT_id ? 'selected' : '' }}>{{ $s->subjectT_name }}</option>
                @endforeach
            </select>
        </label>

        <label class="admin-attendance-tool admin-attendance-tool--select">
            <select name="lecturer_id" onchange="this.form.submit()">
                <option value="">All Lecturers</option>
                @foreach ($lecturers as $l)
                    <option value="{{ $l->lecturer_id }}" {{ (string) $filterLecturerId === (string) $l->lecturer_id ? 'selected' : '' }}>{{ $l->lecturer_name }}</option>
                @endforeach
            </select>
        </label>

        <label class="admin-attendance-tool">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 8.25h16.5M4.5 6.75h15A2.25 2.25 0 0 1 21.75 9v9.75A2.25 2.25 0 0 1 19.5 21h-15a2.25 2.25 0 0 1-2.25-2.25V9A2.25 2.25 0 0 1 4.5 6.75Z" />
            </svg>
            <input type="date" name="date" value="{{ $selectedDate?->format('Y-m-d') }}" onchange="this.form.submit()">
        </label>

        <a class="admin-attendance-button admin-attendance-button--ghost" href="{{ route('admin.subjectAttendance') }}">Reset</a>
        <a class="admin-attendance-button" href="{{ route('admin.reports.subjectAttendance') }}">Full Report →</a>
    </form>

    @php
        $detailBaseParams = array_filter([
            'academic_period_id' => $period?->id,
            'subjectT_id'        => $filterSubjectId,
            'lecturer_id'        => $filterLecturerId,
            'date'               => $selectedDate?->format('Y-m-d'),
            'list_status'        => $filterStatus,
        ], fn ($v) => $v !== null && $v !== '');
    @endphp

    @if ($filterStatus === '')
        {{-- ── SESSION GROUP TABLE ── --}}
        <section class="admin-attendance-panel">
            <div class="admin-attendance-panel__header">
                <div>
                    <h2 class="admin-attendance-panel__title">Attendance Overview</h2>
                    <p class="admin-attendance-panel__subtitle">Grouped by subject &amp; roster{{ $period ? ' — ' . $period->name : '' }}.</p>
                </div>
                <div class="admin-attendance-panel__meta">
                    <span class="admin-attendance-panel__chip">{{ $sessionRows->count() }} {{ $sessionRows->count() === 1 ? 'session' : 'sessions' }}</span>
                </div>
            </div>

            <div class="admin-attendance-table-wrap">
                <table class="admin-attendance-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Subject</th>
                            <th>Roster</th>
                            <th>Lecturer</th>
                            <th style="text-align:center;">Total</th>
                            <th style="text-align:center;">Present</th>
                            <th style="text-align:center;">Absent</th>
                            <th style="min-width:110px;">Rate</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sessionRows as $index => $row)
                            @php
                                $detailUrl = route('admin.subjectAttendance.details', array_merge($detailBaseParams, ['id' => $row->session_id]));
                                $rate      = $row->attendance_rate;
                                $rateColor = $rate >= 80 ? '#10b981' : ($rate >= 60 ? '#f59e0b' : '#ef4444');
                            @endphp
                            <tr class="admin-attendance-row" onclick="window.location='{{ $detailUrl }}'" style="cursor:pointer;">
                                <td class="admin-attendance-table__strong">{{ $index + 1 }}</td>
                                <td><div style="font-weight:600;color:#0f172a;">{{ $row->subject_name }}</div></td>
                                <td>
                                    <span style="background:#ede9fe;color:#6d28d9;border-radius:6px;padding:2px 10px;font-size:12px;font-weight:600;">
                                        {{ $row->roster_label }}
                                    </span>
                                </td>
                                <td>{{ $row->lecturer_name }}</td>
                                <td style="text-align:center;font-weight:600;color:#0f172a;">{{ $row->total_students }}</td>
                                <td style="text-align:center;"><span style="color:#059669;font-weight:700;">{{ $row->present_count }}</span></td>
                                <td style="text-align:center;"><span style="color:#dc2626;font-weight:700;">{{ $row->absent_count }}</span></td>
                                <td>
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
                                <td colspan="9" class="admin-attendance-table__strong">No records found{{ $period ? ' for this semester' : ' — no active Academic Semester' }}.</td>
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
                    <h2 class="admin-attendance-panel__title">{{ $filterStatus === 'present' ? 'Present Students' : 'Absent Students' }}</h2>
                    <p class="admin-attendance-panel__subtitle">Each student's lecturer is shown alongside their name.</p>
                </div>
                <div class="admin-attendance-panel__meta">
                    <span class="admin-attendance-panel__chip">{{ $rows->count() }} students</span>
                    <a href="{{ route('admin.subjectAttendance', $kpiBaseParams) }}"
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
                            <th>Subject</th>
                            <th>Batch</th>
                            <th>Lecturer</th>
                            <th>Date</th>
                            <th>Check-in</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $index => $row)
                            @php
                                $detailUrl = route('admin.subjectAttendance.details', array_merge($detailBaseParams, ['id' => $row->session_id]));
                            @endphp
                            <tr class="admin-attendance-row" onclick="window.location='{{ $detailUrl }}'" style="cursor:pointer;">
                                <td class="admin-attendance-table__strong">{{ $index + 1 }}</td>
                                <td><div style="font-weight:600;color:#0f172a;">{{ $row->student_name }}</div></td>
                                <td>{{ $row->student_id }}</td>
                                <td>{{ $row->subject_name }}</td>
                                <td>
                                    <span style="background:#ede9fe;color:#6d28d9;border-radius:6px;padding:2px 10px;font-size:12px;font-weight:600;">
                                        {{ $row->batch_name }}
                                    </span>
                                </td>
                                <td>{{ $row->lecturer_name }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($row->date)->format('d M Y') }}</td>
                                <td>{{ optional($row->scanned_at)->format('h:i A') ?: '-' }}</td>
                                <td><span class="att-status att-status--{{ $row->status }}">{{ ucfirst($row->status) }}</span></td>
                                <td>
                                    <a href="{{ $detailUrl }}" class="admin-attendance-detail-button" style="text-decoration:none;display:inline-block;" onclick="event.stopPropagation();">View Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="admin-attendance-table__strong">No students found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif

</div>
@endsection
