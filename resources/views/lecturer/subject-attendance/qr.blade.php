@extends('layouts/lecturer')

@section('title', 'Subject Attendance Session')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/lecturer-attendance.css') }}">
    <style>
        .att-hist-status { display:inline-flex;align-items:center;font-size:11.5px;font-weight:600;padding:3px 9px;border-radius:20px;white-space:nowrap; }
        .att-hist-status--present       { background:#dcfce7;color:#15803d; }
        .att-hist-status--absent        { background:#fee2e2;color:#dc2626; }
        .att-hist-status--not_recorded  { background:#f1f5f9;color:#64748b; }
        .att-qr-chip { display:inline-flex;align-items:center;font-size:10px;font-weight:700;letter-spacing:.4px;padding:1px 6px;border-radius:20px;background:#ede9fe;color:#6d28d9;margin-right:4px;vertical-align:middle; }
        .att-pencil-btn { display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:7px;border:1.5px solid #e2e8f0;background:#fff;cursor:pointer;color:#64748b;transition:all .15s; }
        .att-pencil-btn:hover { background:#f1f5f9;border-color:#94a3b8;color:#1e293b; }
        .sam-editing { display:none;gap:4px;align-items:center;justify-content:center; }
        .att-save-btn { display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:7px;border:1.5px solid #bbf7d0;background:#f0fdf4;cursor:pointer;color:#16a34a;transition:all .15s; }
        .att-save-btn:hover { background:#dcfce7;border-color:#4ade80; }
        .att-cancel-btn { display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:7px;border:1.5px solid #fca5a5;background:#fff;cursor:pointer;color:#dc2626;transition:all .15s; }
        .att-cancel-btn:hover { background:#fef2f2; }
        .sam-row--editing { background:#fefce8; }
    </style>
@endpush

@section('content')
@php
    $sessionIsExpired = $session->isExpired();
    $sessionIsPaused  = $session->isManuallyPaused();
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
        <h1 class="attendance-page__title">{{ $session->subject?->subjectT_name }} — {{ $session->scope_label }}</h1>
    </div>

    {{-- ── EXPIRED STATE ── --}}
    @if ($sessionIsExpired)
    <section class="qr-expired-state">
        <div class="qr-expired-state__icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>
        <h2 class="qr-expired-state__title">Session Ended</h2>
        <p class="qr-expired-state__subtitle">Attendance has been finalized — students who did not scan were automatically marked <strong>Absent</strong>.</p>
        <p class="qr-expired-state__meta">
            Session was valid from {{ $validFrom->format('g:i A') }} until {{ $validUntil->format('g:i A') }}.
        </p>
        @include('partials.back-button', ['href' => route('lecturer.subjectAttendance'), 'label' => 'Back to Subject Attendance'])
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

            <form method="POST" action="{{ route('lecturer.subjectAttendance.resume', $session->session_id) }}" style="margin:0;">
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

            <form method="POST" action="{{ route('lecturer.subjectAttendance.end', $session->session_id) }}" style="margin:0;">
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
                    <a href="{{ route('lecturer.subjectAttendance.show', $session->session_id) }}"
                       class="attendance-chip" style="text-decoration:none;">Refresh</a>
                @endif
                @include('partials.back-button', ['href' => route('lecturer.subjectAttendance'), 'label' => 'Back to list'])
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
                    @forelse ($scannedRows as $index => $row)
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

    {{-- ── MANUAL ATTENDANCE (full Section roster, editable — QR fallback) ── --}}
    <section class="attendance-panel attendance-panel--qr" style="margin-top:1.5rem;">
        <div class="attendance-panel__header">
            <div>
                <h2 class="attendance-panel__title">
                    Manual Attendance
                    <span class="attendance-panel__title-note">Enrolled students in {{ $session->scope_label }}</span>
                </h2>
                <p class="attendance-panel__subtitle">
                    Use this if a student could not scan the QR code. Click the pencil to set a student
                    <strong>Present</strong> or <strong>Absent</strong>.
                    @if ($sessionIsExpired)
                        Non-scanned students were auto-marked <strong>Absent</strong> when the session ended.
                    @endif
                </p>
            </div>
            <div class="attendance-panel__tools">
                <span class="attendance-chip attendance-chip--filled">{{ $rosterRows->count() }} students</span>
            </div>
        </div>

        <div style="margin-bottom:0.75rem;">
            <input type="text" id="samSearch" oninput="samFilter(this.value)"
                   placeholder="Search by name or ID…"
                   style="width:100%;max-width:320px;padding:8px 12px;border-radius:8px;border:1px solid #e2e8f0;font-size:13px;color:#1e293b;outline:none;">
        </div>

        <form method="POST" action="{{ route('lecturer.subjectAttendance.manualUpdate', $session->session_id) }}" id="samForm">
            @csrf
            <div class="attendance-table-wrap">
                <table class="attendance-table" style="min-width:640px;">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Student</th>
                            <th>Status</th>
                            <th>Check-in</th>
                            <th style="width:48px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rosterRows as $i => $row)
                        @php
                            $statusLabel = match ($row->status) {
                                'present' => 'Present',
                                'absent'  => 'Absent',
                                default   => 'Not Recorded',
                            };
                        @endphp
                        <tr class="sam-row"
                            data-name="{{ strtolower($row->student_name ?? '') }}"
                            data-sid="{{ strtolower($row->student_id) }}"
                            data-orig="{{ $row->status }}">
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <span style="font-weight:600;color:#1e293b;">{{ $row->student_name }}</span>
                                <span style="display:block;font-size:11px;color:#94a3b8;">{{ $row->student_id }}@if($row->nric) &middot; IC: {{ $row->nric }}@endif</span>
                            </td>
                            <td>
                                <div class="sam-display">
                                    <span class="att-hist-status att-hist-status--{{ $row->status }}">{{ $statusLabel }}</span>
                                </div>
                                <div class="sam-input" style="display:none;">
                                    <select name="attendance[{{ $row->student_id }}]" disabled
                                            style="padding:5px 10px;border-radius:8px;border:1px solid #e2e8f0;font-size:13px;width:100%;cursor:pointer;">
                                        <option value="not_recorded" {{ $row->status === 'not_recorded' ? 'selected' : '' }}>Not Recorded</option>
                                        <option value="present"      {{ $row->status === 'present'      ? 'selected' : '' }}>Present</option>
                                        <option value="absent"       {{ $row->status === 'absent'       ? 'selected' : '' }}>Absent</option>
                                    </select>
                                </div>
                            </td>
                            <td style="white-space:nowrap;">
                                @if ($row->via_qr)
                                    <span class="att-qr-chip">QR</span>
                                    <span style="font-size:12px;color:#1e293b;">{{ optional($row->scanned_at)->format('g:i A') }}</span>
                                @else
                                    <span style="color:#cbd5e1;">—</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <button type="button" class="att-pencil-btn" onclick="samEdit(this)" title="Edit attendance">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" style="width:14px;height:14px;">
                                        <path d="M13.488 2.513a1.75 1.75 0 0 0-2.475 0L6.75 6.774a2.75 2.75 0 0 0-.596.892l-.848 2.047a.75.75 0 0 0 .98.98l2.047-.848a2.75 2.75 0 0 0 .892-.596l4.263-4.261a1.75 1.75 0 0 0 0-2.475Z"/>
                                        <path d="M4.75 3.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h6.5c.69 0 1.25-.56 1.25-1.25V8.75a.75.75 0 0 1 1.5 0v2.5A2.75 2.75 0 0 1 11.25 14h-6.5A2.75 2.75 0 0 1 2 11.25v-6.5A2.75 2.75 0 0 1 4.75 2h2.5a.75.75 0 0 1 0 1.5h-2.5Z"/>
                                    </svg>
                                </button>
                                <div class="sam-editing">
                                    <button type="submit" class="att-save-btn" title="Save">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" style="width:14px;height:14px;">
                                            <path fill-rule="evenodd" d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                    <button type="button" class="att-cancel-btn" onclick="samCancel(this)" title="Cancel">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" style="width:14px;height:14px;">
                                            <path d="M5.28 4.22a.75.75 0 0 0-1.06 1.06L6.94 8l-2.72 2.72a.75.75 0 1 0 1.06 1.06L8 9.06l2.72 2.72a.75.75 0 1 0 1.06-1.06L9.06 8l2.72-2.72a.75.75 0 0 0-1.06-1.06L8 6.94 5.28 4.22Z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="attendance-table__empty">No students are enrolled in this section.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    </section>
</div>
@endsection

@section('scripts')
<script>
function samEdit(btn) {
    const row = btn.closest('tr');
    row.querySelector('.sam-display').style.display = 'none';
    row.querySelector('.sam-input').style.display = '';
    row.querySelector('select[name^="attendance["]').disabled = false;
    btn.style.display = 'none';
    row.querySelector('.sam-editing').style.display = 'flex';
    row.classList.add('sam-row--editing');
}

function samCancel(btn) {
    const row = btn.closest('tr');
    const select = row.querySelector('select[name^="attendance["]');
    select.value = row.dataset.orig || 'not_recorded';
    select.disabled = true;
    row.querySelector('.sam-display').style.display = '';
    row.querySelector('.sam-input').style.display = 'none';
    row.querySelector('.sam-editing').style.display = 'none';
    row.querySelector('.att-pencil-btn').style.display = '';
    row.classList.remove('sam-row--editing');
}

document.getElementById('samForm')?.addEventListener('submit', function (e) {
    const clearing = [...this.querySelectorAll('select[name^="attendance["]:not([disabled])')]
        .filter(s => s.value === 'not_recorded').length;
    if (clearing > 0 && !confirm(clearing + ' student(s) will be reset to Not Recorded.\n\nSave anyway?')) {
        e.preventDefault();
    }
});

function samFilter(query) {
    const q = query.trim().toLowerCase();
    document.querySelectorAll('.sam-row').forEach(row => {
        const match = !q || (row.dataset.name || '').includes(q) || (row.dataset.sid || '').includes(q);
        row.style.display = match ? '' : 'none';
    });
}
</script>
@endsection
