@extends('layouts/admin')

@section('title', 'Subject Attendance Details')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-attendance.css') }}">
@endpush

@section('content')
<div class="admin-attendance-page">

    <div>
        <a href="{{ route('admin.subjectAttendance', $backParams ?? []) }}" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:#7c3aed;text-decoration:none;margin-bottom:10px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Back to Subject Attendance
        </a>
        <h1 class="admin-attendance-page__title">{{ $session->subject?->subjectT_name ?? 'Subject' }}</h1>
        <p class="admin-attendance-page__subtitle">
            {{ $session->lecturer?->lecturer_name ?? '—' }} &middot;
            <span style="background:#ede9fe;color:#6d28d9;border-radius:6px;padding:2px 10px;font-size:12px;font-weight:600;">{{ $session->scope_label }}</span>
            &mdash; {{ $session->start_time?->format('d M Y') }}
        </p>
    </div>

    <section class="admin-attendance-panel">
        <div class="admin-attendance-panel__header">
            <div>
                <h2 class="admin-attendance-panel__title">Student Details</h2>
                <p class="admin-attendance-panel__subtitle">Attendance for this subject-class session.</p>
            </div>
            <div class="admin-attendance-panel__meta">
                <span class="admin-attendance-panel__chip">{{ $rows->count() }} {{ $rows->count() === 1 ? 'student' : 'students' }}</span>
            </div>
        </div>

        <div class="admin-attendance-table-wrap">
            <table class="admin-attendance-table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>Status</th>
                        <th>Check-in</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td><div style="font-weight:600;color:#0f172a;">{{ $row->student_name }}</div></td>
                            <td>{{ $row->student_id }}</td>
                            <td><span class="att-status att-status--{{ $row->status }}">{{ ucfirst($row->status) }}</span></td>
                            <td>{{ optional($row->scanned_at)->format('h:i A') ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="admin-attendance-table__strong">No students found for this session.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>
@endsection
