@extends('layouts/lecturer')

@section('title', 'QR Code Attendance')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/lecturer-attendance.css') }}">
@endpush

@section('content')
@php
    $sessionIsExpired = $qrSession->isExpired();
    $sessionIsPaused  = $qrSession->isManuallyPaused();
    $sessionIsActive  = $qrSession->isActive();
@endphp

<div class="attendance-page attendance-page--qr">
    @if (session('success'))
        <div class="attendance-alert attendance-alert--success">{{ session('success') }}</div>
    @endif
    @if (session('info'))
        <div class="attendance-alert attendance-alert--info">{{ session('info') }}</div>
    @endif
    @if (session('error'))
        <div class="attendance-alert attendance-alert--error">{{ session('error') }}</div>
    @endif

    <div>
        <h1 class="attendance-page__title">Attendance Session Active</h1>
    </div>

    {{-- ── EXPIRED STATE ── --}}
    @if ($sessionIsExpired)
    <section class="qr-expired-state">
        <div class="qr-expired-state__icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>
        <h2 class="qr-expired-state__title">QR Session Expired</h2>
        <p class="qr-expired-state__subtitle">Attendance has been finalized — students who did not scan were automatically marked <strong>Absent (TH)</strong>.</p>
        <p class="qr-expired-state__meta">
            Session was valid from {{ $validFrom->format('g:i A') }} until {{ $validUntil->format('g:i A') }}.
            Use the pencil icon in the attendance table to correct individual records.
        </p>
        @include('partials.back-button', ['href' => route('lecturer.attendance'), 'label' => 'Back to Attendance'])
    </section>

    {{-- ── PAUSED STATE ── --}}
    @elseif ($sessionIsPaused)
    <section class="attendance-qr-stage">
        <div class="qr-paused-state">
            <div class="qr-paused-state__icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9v6m-4.5 0V9M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <p class="qr-paused-state__label">QR session temporarily closed by lecturer.</p>
        </div>

        <div class="attendance-qr-stage__actions">
            <div class="attendance-qr-stage__window">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Students can scan until {{ $validUntil->format('g:i A') }}. Anyone who has not scanned by then is marked <strong>Absent</strong> automatically.</span>
            </div>

            <form method="POST" action="{{ route('lecturer.attendance.qr.resume', $sessionId) }}" style="margin:0;">
                @csrf
                <button type="submit" class="attendance-button attendance-button--success">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" />
                    </svg>
                    Resume QR
                </button>
            </form>
        </div>
    </section>

    {{-- ── ACTIVE STATE ── --}}
    @else
    <section class="attendance-qr-stage">
        <div class="attendance-qr-stage__code">
            {!! $qrCodeSvg !!}
        </div>

        <div class="attendance-qr-stage__actions">
            <div class="attendance-qr-stage__window">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Students can scan until {{ $validUntil->format('g:i A') }}. Anyone who has not scanned by then is marked <strong>Absent</strong> automatically.</span>
            </div>

            <form method="POST" action="{{ route('lecturer.attendance.qr.end', $sessionId) }}" style="margin:0;">
                @csrf
                <button type="submit" class="attendance-button attendance-button--warning">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:1rem;height:1rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9v6m-4.5 0V9M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    Pause QR
                </button>
            </form>
        </div>
    </section>
    @endif

    {{-- ── LIVE ATTENDANCE TABLE ── --}}
    <section class="attendance-panel attendance-panel--qr">
        <div class="attendance-panel__header">
            <div>
                <h2 class="attendance-panel__title">
                    Live Attendance
                    <span class="attendance-panel__title-note">Students who have scanned</span>
                </h2>
                <p class="attendance-panel__subtitle">
                    {{ $presentCount }} of {{ $totalStudents }} students scanned.
                </p>
            </div>

            <div class="attendance-panel__tools">
                @if (! $sessionIsExpired)
                    <a href="{{ route('lecturer.attendance.qr.show', $sessionId) }}"
                       class="attendance-chip" style="text-decoration:none;">Refresh</a>
                @endif
                @include('partials.back-button', ['href' => route('lecturer.attendance'), 'label' => 'Back to list'])
            </div>
        </div>

        <div class="attendance-table-wrap">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Time scanned</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendanceRows as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->student_id }}</td>
                            <td>{{ $row->student_name }}</td>
                            <td>{{ optional($row->scanned_at)->format('g:i:s A') ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="attendance-table__empty">No students have scanned this QR code yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
