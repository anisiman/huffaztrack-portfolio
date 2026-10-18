@extends('layouts/lecturer')

@section('title', 'Lecturer Attendance')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/lecturer-attendance.css') }}">
@endpush

@section('content')
<div class="attendance-page">
    <div>
        <h1 class="attendance-page__title">Attendance</h1>
        <p class="attendance-page__subtitle">Generate a QR code for student self check-in, or record attendance manually in the table below.</p>
    </div>

    @if (session('success'))
        <div class="attendance-alert attendance-alert--success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('info'))
        <div class="attendance-alert attendance-alert--info">
            {{ session('info') }}
        </div>
    @endif

    @if (session('error'))
        <div class="attendance-alert attendance-alert--error">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="attendance-alert attendance-alert--error">
            @foreach ($errors->unique() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- ── PENDING APPEALS BANNER ── --}}
    @if (isset($pendingAppealsCount) && $pendingAppealsCount > 0)
    <a href="{{ route('lecturer.attendance.appeals') }}" style="text-decoration:none;display:block;margin-bottom:1rem;">
        <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:#92400e;background:#fffbeb;border:1.5px solid #fde68a;padding:10px 16px;border-radius:10px;font-weight:500;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" style="width:15px;height:15px;flex-shrink:0;color:#d97706;">
                <path fill-rule="evenodd" d="M6.701 2.25c.577-1 2.02-1 2.598 0l5.196 9a1.5 1.5 0 0 1-1.299 2.25H2.804a1.5 1.5 0 0 1-1.3-2.25l5.197-9ZM8 4a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3A.75.75 0 0 1 8 4Zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
            </svg>
            <span>
                <strong>{{ $pendingAppealsCount }} pending excuse {{ Str::plural('letter', $pendingAppealsCount) }}</strong>
                from your students &mdash; click to review.
            </span>
        </div>
    </a>
    @endif

    {{-- ── DATE FILTER ── --}}
    <div class="attendance-toolbar">
        <form method="GET" action="{{ route('lecturer.attendance') }}" style="display:contents;">
            <div class="attendance-tool attendance-tool--static">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75" />
                </svg>
                <input type="date" name="date"
                       value="{{ $selectedDate->format('Y-m-d') }}"
                       max="{{ $today->format('Y-m-d') }}"   /* prevent future dates */
                       onchange="this.form.submit()" // auto-submit after date selection
                       style="border:none;background:transparent;font-size:13px;color:#1e293b;font-weight:500;cursor:pointer;outline:none;min-width:130px;">
            </div>

            @if (! $isToday)
                <a href="{{ route('lecturer.attendance') }}"
                   class="attendance-button attendance-button--secondary"
                   style="text-decoration:none;">
                    Today
                </a>
            @endif
        </form>

        @if ($isToday)
            @if ($currentSession && $currentSession->isExpired())
                <span class="att-completed-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" style="width:13px;height:13px;flex-shrink:0;">
                        <path fill-rule="evenodd" d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd"/>
                    </svg>
                    Attendance Completed
                </span>
                <span class="att-completed-hint">QR session ended &mdash; use the pencil icon to correct records if needed.</span>
            @elseif ($currentSession && $currentSession->isManuallyPaused())
                <a href="{{ route('lecturer.attendance.qr.show', ['sessionId' => $currentSessionId]) }}"
                   class="attendance-button attendance-button--secondary" style="text-decoration:none;">Open QR</a>
                <form method="POST" action="{{ route('lecturer.attendance.qr.resume', $currentSessionId) }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="attendance-button attendance-button--success">Resume QR</button>
                </form>
                <span class="qr-validity-badge qr-validity-badge--paused">
                    Paused &middot; Until {{ $currentSession->end_time->copy()->setTimezone('Asia/Kuala_Lumpur')->format('g:i A') }}
                </span>
            @elseif ($currentSession && $currentSession->isActive())
                <a href="{{ route('lecturer.attendance.qr.show', ['sessionId' => $currentSessionId]) }}"
                   class="attendance-button attendance-button--primary" style="text-decoration:none;">Open QR</a>
                <form method="POST" action="{{ route('lecturer.attendance.qr.end', $currentSessionId) }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="attendance-button attendance-button--warning">Pause QR</button>
                </form>
                <span class="qr-validity-badge">
                    Until {{ $currentSession->end_time->copy()->setTimezone('Asia/Kuala_Lumpur')->format('g:i A') }}
                </span>
            @else
                <form method="POST" action="{{ route('lecturer.attendance.qr') }}" id="qrGenerateForm" style="margin:0;">
                    @csrf
                    <input type="hidden" name="duration_minutes" id="qrDurationInput" value="120">
                    <button type="button" class="attendance-button attendance-button--primary"
                            onclick="document.getElementById('qrDurationModal').style.display='flex'">Generate QR Code</button>
                </form>
            @endif
        @endif
    </div>

    {{-- ── QR SESSION CARD (today only) ── --}}
    @if ($isToday)
    <div class="attendance-session-card">
        <div>
            <p class="attendance-session-card__label">Current Session ID</p>
            <h2 class="attendance-session-card__value">{{ $currentSessionId ?: 'No active QR session yet' }}</h2>
            <p class="attendance-session-card__note">
                @if (! $currentSessionId)
                    Generate a QR code to start recording attendance for a new session.
                @elseif ($currentSession?->isExpired())
                    QR attendance for today has already been completed. Use the pencil icon to correct individual records if changes are needed.
                @elseif ($currentSession?->isManuallyPaused())
                    QR session temporarily closed. Students cannot scan until you resume it.
                @else
                    Students who scan this QR code will be recorded under this session.
                @endif
            </p>
        </div>
        @if ($currentSession)
        <div>
            @if ($currentSession->isExpired())
                <span class="qr-status-badge qr-status-badge--expired">Completed</span>
            @elseif ($currentSession->isManuallyPaused())
                <span class="qr-status-badge qr-status-badge--paused">Paused</span>
            @else
                <span class="qr-status-badge qr-status-badge--active">Active</span>
            @endif
        </div>
        @endif
    </div>
    @endif

    {{-- ── STATS (date-based) — click a card to filter the table below ── --}}
    <div class="attendance-stats">
        <button type="button" class="attendance-stat attendance-stat--green" onclick="filterByStatus('present', this)">
            <div class="attendance-stat__icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div>
                <p class="attendance-stat__label">Present</p>
                <h2 class="attendance-stat__value">{{ $presentCount }} Students</h2>
            </div>
        </button>

        <button type="button" class="attendance-stat attendance-stat--red" onclick="filterByStatus('absent', this)">
            <div class="attendance-stat__icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div>
                <p class="attendance-stat__label">Absent (TH)</p>
                <h2 class="attendance-stat__value">{{ $absentCount }} Students</h2>
            </div>
        </button>

        <button type="button" class="attendance-stat attendance-stat--purple" onclick="filterByStatus('absent_with_reason', this)">
            <div class="attendance-stat__icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5v6.75A2.25 2.25 0 0 1 17.25 19.5H6.75A2.25 2.25 0 0 1 4.5 17.25V10.5m15 0V6.75A2.25 2.25 0 0 0 17.25 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v3.75m15 0h-15" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v4.5m2.25-2.25h-4.5" />
                </svg>
            </div>
            <div>
                <p class="attendance-stat__label">Excused (THS)</p>
                <h2 class="attendance-stat__value">{{ $excusedCount }} Students</h2>
            </div>
        </button>

        <button type="button" class="attendance-stat attendance-stat--slate" onclick="filterByStatus('not_recorded', this)">
            <div class="attendance-stat__icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div>
                <p class="attendance-stat__label">Not Recorded</p>
                <h2 class="attendance-stat__value">{{ $notRecordedCount }} Students</h2>
            </div>
        </button>

        <button type="button" class="attendance-stat attendance-stat--yellow" onclick="filterByStatus('', this)">
            <div class="attendance-stat__icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
            </div>
            <div>
                <p class="attendance-stat__label">Attendance Rate</p>
                <h2 class="attendance-stat__value">{{ $attendanceRate }}%</h2>
            </div>
        </button>
    </div>

    {{-- ── UNIFIED ATTENDANCE TABLE ── --}}
    <section class="attendance-panel" style="margin-top:1.5rem;">
        <div class="attendance-panel__header" style="margin-bottom:1rem;">
            <div>
                <h2 class="attendance-panel__title">
                    Attendance &mdash; {{ $selectedDate->format('d M Y') }}{{ $isToday ? ' (Today)' : '' }}
                </h2>
                <p class="attendance-panel__subtitle">
                    All students for this date. Only <strong>Absent (TH)</strong> affects warning letters.
                    Use the pencil icon to edit any record.
                </p>
            </div>
            <div style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">
                <span class="attendance-chip attendance-chip--filled">{{ $totalStudents }} students</span>
                @if ($isToday && $currentSessionId)
                    <span class="attendance-chip attendance-chip--filled">{{ $attendanceRows->count() }} via QR</span>
                    <a href="{{ route('lecturer.attendance', ['session_id' => $currentSessionId]) }}"
                       class="attendance-chip">Refresh</a>
                @endif
            </div>
        </div>

        <div style="margin-bottom:0.75rem;">
            <input type="text" id="attStudentSearch" oninput="filterAttendanceRows(this.value)"
                   placeholder="Search by name, Tahfiz ID, or IC number…"
                   style="width:100%;max-width:360px;padding:8px 12px;border-radius:8px;border:1px solid #e2e8f0;font-size:13px;color:#1e293b;outline:none;">
        </div>

        {{-- Editable table wrapped in save form — works for any date, not just today --}}
        @if ($currentSession)
            @if ($currentSession->isExpired())
            <p style="font-size:13px;color:#15803d;background:#f0fdf4;border-left:3px solid #4ade80;padding:9px 14px;border-radius:0 8px 8px 0;margin-bottom:1rem;line-height:1.55;">
                <strong>Attendance finalized.</strong> Non-scanned students were automatically marked <strong>Absent (TH)</strong>. Click the pencil icon to correct individual records if needed.
            </p>
            @else
            <p style="font-size:13px;color:#0369a1;background:#f0f9ff;border-left:3px solid #38bdf8;padding:9px 14px;border-radius:0 8px 8px 0;margin-bottom:1rem;line-height:1.55;">
                <strong>QR session is active.</strong> Students who scan are marked Present. When the session expires, non-scanned students will be automatically marked <strong>Absent (TH)</strong>.
            </p>
            @endif
        @endif
        <form method="POST" action="{{ route('lecturer.attendance.store') }}" id="attendanceForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="date" value="{{ $selectedDate->format('Y-m-d') }}">
            <div class="attendance-table-wrap">
                <table class="attendance-table" style="min-width:860px;">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Student Name</th>
                            <th title="Warning letters issued based on unexcused absences. No Warning = good standing. WL1/WL2/WL3 = 1st/2nd/3rd warning letter issued.">Warning Level</th>
                            <th>Status</th>
                            <th style="min-width:140px;">Reason Type</th>
                            <th style="min-width:160px;">Remarks</th>
                            <th style="min-width:160px;">Supporting Document</th>
                            <th>Check-in</th>
                            <th style="width:48px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $i => $student)
                        @php
                            $att           = $dateAttendance->get($student->student_id);
                            $currentStatus = $att ? strtolower($att->status) : 'not_recorded';
                            $currentReason = $att?->reason_type ?? '';
                            $currentRemark = $att?->remarks ?? '';
                            $wl            = (int) ($student->warning_level ?? 0);
                            $sid           = $student->student_id;
                            $scannedViaQr  = $att && filled($att->session_id) && $att->scanned_at !== null;
                            $scannedTime   = optional($att?->scanned_at?->copy()->setTimezone('Asia/Kuala_Lumpur'))->format('g:i A');
                            $statusLabel   = match ($currentStatus) {
                                'present'            => 'Present',
                                'absent'             => 'Absent (TH)',
                                'absent_with_reason' => 'Excused (THS)',
                                default              => 'Not Recorded',
                            };
                            $reasonDisplay = $currentReason ? ucwords(str_replace('_', ' ', $currentReason)) : '';
                        @endphp
                        <tr class="manual-att-row" data-sid="{{ $sid }}"
                            data-name="{{ strtolower($student->student_name ?? '') }}"
                            data-nric="{{ strtolower($student->nric ?? '') }}"
                            data-orig-status="{{ $currentStatus }}"
                            data-orig-reason="{{ $currentReason }}"
                            data-orig-remarks="{{ $currentRemark }}">
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <span style="font-weight:600;color:#1e293b;">{{ $student->student_name }}</span>
                                <span style="display:block;font-size:11px;color:#94a3b8;">{{ $student->student_id }}@if($student->nric) &middot; IC: {{ $student->nric }}@endif</span>
                            </td>
                            <td>
                                @if ($wl === 0)
                                    <span class="wl-badge wl-badge--green">No Warning</span>
                                @elseif ($wl === 1)
                                    <span class="wl-badge wl-badge--amber">WL1</span>
                                @elseif ($wl === 2)
                                    <span class="wl-badge wl-badge--orange">WL2</span>
                                @else
                                    <span class="wl-badge wl-badge--red">WL3</span>
                                @endif
                            </td>

                            {{-- Status cell --}}
                            <td>
                                <div class="att-display">
                                    <span class="att-hist-status att-hist-status--{{ $currentStatus }}">{{ $statusLabel }}</span>
                                </div>
                                <div class="att-input" style="display:none;">
                                    <select name="attendance[{{ $sid }}]"
                                            class="att-select{{ $currentStatus === 'not_recorded' ? ' att-select--unset' : '' }}"
                                            data-student="{{ $sid }}"
                                            onchange="toggleReasonFields(this)"
                                            disabled
                                            style="padding:5px 10px;border-radius:8px;border:1px solid #e2e8f0;font-size:13px;width:100%;cursor:pointer;">
                                        <option value="not_recorded"       {{ $currentStatus === 'not_recorded'       ? 'selected' : '' }}>Not Recorded</option>
                                        <option value="present"            {{ $currentStatus === 'present'            ? 'selected' : '' }}>Present</option>
                                        <option value="absent"             {{ $currentStatus === 'absent'             ? 'selected' : '' }}>Absent (TH)</option>
                                        <option value="absent_with_reason" {{ $currentStatus === 'absent_with_reason' ? 'selected' : '' }}>Excused (THS)</option>
                                    </select>
                                </div>
                            </td>

                            {{-- Reason type cell --}}
                            <td>
                                <div class="att-display">
                                    <span style="font-size:12px;color:#64748b;">{{ $reasonDisplay ?: '—' }}</span>
                                </div>
                                <div class="att-input" style="display:none;">
                                    <select name="reason_type[{{ $sid }}]"
                                            class="reason-field reason-field--{{ $sid }}"
                                            disabled
                                            style="padding:5px 10px;border-radius:8px;border:1px solid #e2e8f0;font-size:13px;width:100%;">
                                        <option value="">— Select —</option>
                                        <option value="sick_mc"           {{ $currentReason === 'sick_mc'           ? 'selected' : '' }}>Sick / MC</option>
                                        <option value="official_program"  {{ $currentReason === 'official_program'  ? 'selected' : '' }}>Official Program</option>
                                        <option value="family_emergency"  {{ $currentReason === 'family_emergency'  ? 'selected' : '' }}>Family Emergency</option>
                                        <option value="personal_reason"   {{ $currentReason === 'personal_reason'   ? 'selected' : '' }}>Personal Reason</option>
                                        <option value="other"             {{ $currentReason === 'other'             ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                            </td>

                            {{-- Remarks cell --}}
                            <td>
                                <div class="att-display">
                                    <span style="font-size:12px;color:#64748b;">{{ $currentRemark ?: '—' }}</span>
                                </div>
                                <div class="att-input" style="display:none;">
                                    <textarea name="remarks[{{ $sid }}]"
                                              class="reason-field reason-field--{{ $sid }}"
                                              rows="1"
                                              placeholder="Short explanation…"
                                              disabled
                                              style="padding:5px 10px;border-radius:8px;border:1px solid #e2e8f0;font-size:13px;width:100%;resize:none;">{{ $currentRemark }}</textarea>
                                </div>
                            </td>

                            {{-- Supporting document cell --}}
                            <td>
                                <div class="att-display">
                                    @if ($att?->supporting_document)
                                        <a href="{{ route('documents.attendance', $att->attendance_id) }}" target="_blank" class="support-doc-link">View</a>
                                    @else
                                        <span style="color:#cbd5e1;">—</span>
                                    @endif
                                </div>
                                <div class="att-input" style="display:none;">
                                    <div class="support-doc-wrap support-doc-wrap--{{ $sid }}"
                                         style="{{ $currentStatus !== 'absent_with_reason' ? 'display:none;' : '' }}">
                                        @if ($att?->supporting_document)
                                            <a href="{{ route('documents.attendance', $att->attendance_id) }}"
                                               target="_blank"
                                               class="support-doc-link">View existing</a>
                                        @endif
                                        <input type="file"
                                               name="document[{{ $sid }}]"
                                               accept=".pdf,.jpg,.jpeg,.png"
                                               disabled
                                               style="font-size:12px;display:block;margin-top:4px;max-width:160px;">
                                        <span class="support-doc-hint">PDF, JPG, PNG. Optional.</span>
                                    </div>
                                </div>
                            </td>

                            {{-- Check-in: always shows QR badge if scanned (audit trail) --}}
                            <td style="white-space:nowrap;">
                                @if ($scannedViaQr)
                                    <span class="att-qr-chip">QR</span>
                                    <span style="font-size:12px;color:#1e293b;">{{ $scannedTime ?: '—' }}</span>
                                @else
                                    <span style="color:#cbd5e1;">—</span>
                                @endif
                            </td>

                            {{-- Action column --}}
                            <td style="text-align:center;">
                                {{-- Default state: pencil --}}
                                <button type="button" class="att-pencil-btn" onclick="startEditRow(this)" title="Edit attendance">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" style="width:14px;height:14px;">
                                        <path d="M13.488 2.513a1.75 1.75 0 0 0-2.475 0L6.75 6.774a2.75 2.75 0 0 0-.596.892l-.848 2.047a.75.75 0 0 0 .98.98l2.047-.848a2.75 2.75 0 0 0 .892-.596l4.263-4.261a1.75 1.75 0 0 0 0-2.475Z"/>
                                        <path d="M4.75 3.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h6.5c.69 0 1.25-.56 1.25-1.25V8.75a.75.75 0 0 1 1.5 0v2.5A2.75 2.75 0 0 1 11.25 14h-6.5A2.75 2.75 0 0 1 2 11.25v-6.5A2.75 2.75 0 0 1 4.75 2h2.5a.75.75 0 0 1 0 1.5h-2.5Z"/>
                                    </svg>
                                </button>
                                {{-- Edit state: save + cancel --}}
                                <div class="att-editing-actions" style="display:none;">
                                    <button type="submit" class="att-save-btn" title="Save changes">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" style="width:14px;height:14px;">
                                            <path fill-rule="evenodd" d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                    <button type="button" class="att-cancel-btn" onclick="cancelEditRow(this)" title="Cancel">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" style="width:14px;height:14px;">
                                            <path d="M5.28 4.22a.75.75 0 0 0-1.06 1.06L6.94 8l-2.72 2.72a.75.75 0 1 0 1.06 1.06L8 9.06l2.72 2.72a.75.75 0 1 0 1.06-1.06L9.06 8l2.72-2.72a.75.75 0 0 0-1.06-1.06L8 6.94 5.28 4.22Z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="attendance-table__empty">No students assigned to your account.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </form>
    </section>
</div>

{{-- ── QR Duration Modal ── --}}
<div id="qrDurationModal"
     style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,.5);align-items:center;justify-content:center;"
     onclick="if(event.target===this)this.style.display='none'">
    <div style="background:#fff;border-radius:16px;padding:28px 32px;width:100%;max-width:400px;box-shadow:0 20px 60px rgba(0,0,0,.18);margin:0 16px;">

        <h3 style="font-size:1.05rem;font-weight:700;color:#0f172a;margin:0 0 4px;">Generate Attendance QR</h3>
        <p style="font-size:13px;color:#64748b;margin:0 0 22px;">Choose how long the QR code stays active for student scanning.</p>

        <p style="font-size:11.5px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.5px;margin:0 0 10px;">QR Validity Duration</p>

        <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:16px;">
            <label style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;border:1.5px solid #e2e8f0;cursor:pointer;font-size:14px;color:#1e293b;">
                <input type="radio" name="qrDuration" value="30" onchange="qrDurationChanged(this)" style="accent-color:#0f172a;flex-shrink:0;">
                30 Minutes
            </label>
            <label style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;border:1.5px solid #e2e8f0;cursor:pointer;font-size:14px;color:#1e293b;">
                <input type="radio" name="qrDuration" value="60" onchange="qrDurationChanged(this)" style="accent-color:#0f172a;flex-shrink:0;">
                1 Hour
            </label>
            <label id="qrDur120Label" style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;border:1.5px solid #0f172a;background:#f8fafc;cursor:pointer;font-size:14px;color:#1e293b;">
                <input type="radio" name="qrDuration" value="120" checked onchange="qrDurationChanged(this)" style="accent-color:#0f172a;flex-shrink:0;">
                2 Hours
                <span style="font-size:11px;font-weight:600;background:#0f172a;color:#fff;padding:1px 8px;border-radius:20px;margin-left:auto;white-space:nowrap;">Default</span>
            </label>
            <label style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;border:1.5px solid #e2e8f0;cursor:pointer;font-size:14px;color:#1e293b;">
                <input type="radio" name="qrDuration" value="custom" onchange="qrDurationChanged(this)" style="accent-color:#0f172a;flex-shrink:0;">
                Custom Duration
            </label>
        </div>

        <div id="qrCustomWrap" style="display:none;margin-bottom:16px;">
            <div style="display:flex;align-items:center;gap:8px;">
                <input type="number" id="qrCustomMinutes" min="5" max="480" placeholder="e.g. 45"
                       style="width:96px;padding:8px 12px;border-radius:8px;border:1.5px solid #e2e8f0;font-size:14px;color:#1e293b;outline:none;">
                <span style="font-size:13px;color:#475569;">minutes</span>
            </div>
            <p id="qrCustomError" style="font-size:12px;color:#ef4444;margin:5px 0 0;display:none;">Please enter a value between 5 and 480 minutes.</p>
            <p style="font-size:11.5px;color:#94a3b8;margin:4px 0 0;">Enter between 5 – 480 minutes.</p>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;">
            <button type="button"
                    onclick="document.getElementById('qrDurationModal').style.display='none'"
                    style="padding:9px 18px;border-radius:10px;border:1.5px solid #e2e8f0;background:#fff;font-size:14px;color:#475569;cursor:pointer;font-weight:500;">
                Cancel
            </button>
            <button type="button" onclick="submitQrForm()" class="attendance-button attendance-button--primary" style="font-size:14px;">
                Generate QR
            </button>
        </div>

    </div>
</div>

<style>
.wl-badge {
    display:inline-flex;align-items:center;
    font-size:11px;font-weight:700;
    padding:2px 9px;border-radius:20px;
    white-space:nowrap;
}
.wl-badge--green  { background:#dcfce7;color:#15803d; }
.wl-badge--amber  { background:#fef3c7;color:#b45309; }
.wl-badge--orange { background:#ffedd5;color:#c2410c; }
.wl-badge--red    { background:#fee2e2;color:#dc2626; }

.att-hist-status {
    display:inline-flex;align-items:center;
    font-size:11.5px;font-weight:600;
    padding:3px 9px;border-radius:20px;white-space:nowrap;
}
.att-hist-status--present            { background:#dcfce7;color:#15803d; }
.att-hist-status--absent             { background:#fee2e2;color:#dc2626; }
.att-hist-status--absent_with_reason { background:#e0f2fe;color:#0369a1; }
.att-hist-status--not_recorded       { background:#f1f5f9;color:#64748b; }

.att-qr-chip {
    display:inline-flex;align-items:center;
    font-size:10px;font-weight:700;letter-spacing:.4px;
    padding:1px 6px;border-radius:20px;
    background:#ede9fe;color:#6d28d9;
    margin-right:4px;vertical-align:middle;
}

/* Attendance completed toolbar badge */
.att-completed-badge {
    display:inline-flex;align-items:center;gap:5px;
    font-size:13px;font-weight:600;color:#15803d;
    background:#f0fdf4;border:1.5px solid #bbf7d0;
    padding:6px 14px;border-radius:8px;
    cursor:default;white-space:nowrap;
}
.att-completed-hint {
    font-size:12px;color:#64748b;
}

/* Default pencil button */
.att-pencil-btn {
    display:inline-flex;align-items:center;justify-content:center;
    width:28px;height:28px;border-radius:7px;
    border:1.5px solid #e2e8f0;background:#fff;
    cursor:pointer;color:#64748b;transition:all .15s;
}
.att-pencil-btn:hover { background:#f1f5f9;border-color:#94a3b8;color:#1e293b; }

/* Editing action group */
.att-editing-actions {
    display:none;gap:4px;align-items:center;justify-content:center;
}
.att-save-btn {
    display:inline-flex;align-items:center;justify-content:center;
    width:28px;height:28px;border-radius:7px;
    border:1.5px solid #bbf7d0;background:#f0fdf4;
    cursor:pointer;color:#16a34a;transition:all .15s;
}
.att-save-btn:hover { background:#dcfce7;border-color:#4ade80; }
.att-cancel-btn {
    display:inline-flex;align-items:center;justify-content:center;
    width:28px;height:28px;border-radius:7px;
    border:1.5px solid #fca5a5;background:#fff;
    cursor:pointer;color:#dc2626;transition:all .15s;
}
.att-cancel-btn:hover { background:#fef2f2; }

/* Row in editing state */
.manual-att-row--editing { background:#fefce8; }
.manual-att-row--editing:hover { background:#fef9c3 !important; }

.reason-field:disabled { opacity:0.4;cursor:not-allowed; }

.support-doc-link {
    font-size:12px;color:#0369a1;text-decoration:none;
}
.support-doc-link:hover { text-decoration:underline; }
.support-doc-hint {
    display:block;font-size:11px;color:#94a3b8;margin-top:2px;
}
</style>

@endsection

@section('scripts')
<script>
function startEditRow(btn) {
    const row = btn.closest('tr');
    row.querySelectorAll('.att-display').forEach(el => el.style.display = 'none');
    row.querySelectorAll('.att-input').forEach(el => { el.style.display = ''; });
    row.querySelectorAll('select[name], textarea[name], input[type="file"]').forEach(el => el.disabled = false);
    const statusSelect = row.querySelector('select[name^="attendance["]');
    if (statusSelect) toggleReasonFields(statusSelect);
    btn.style.display = 'none';
    const editActions = row.querySelector('.att-editing-actions');
    if (editActions) editActions.style.display = 'flex';
    row.classList.add('manual-att-row--editing');
}

function cancelEditRow(btn) {
    const row = btn.closest('tr');

    // Restore original values
    const statusSelect = row.querySelector('select[name^="attendance["]');
    if (statusSelect) statusSelect.value = row.dataset.origStatus || 'not_recorded';
    const reasonSelect = row.querySelector('select[name^="reason_type["]');
    if (reasonSelect) reasonSelect.value = row.dataset.origReason || '';
    const remarksArea = row.querySelector('textarea[name^="remarks["]');
    if (remarksArea) remarksArea.value = row.dataset.origRemarks || '';

    // Disable all form fields
    row.querySelectorAll('select[name], textarea[name], input[type="file"]').forEach(el => el.disabled = true);

    // Return to finalized state
    row.querySelectorAll('.att-display').forEach(el => { el.style.display = ''; });
    row.querySelectorAll('.att-input').forEach(el => el.style.display = 'none');
    btn.closest('.att-editing-actions').style.display = 'none';
    row.querySelector('.att-pencil-btn').style.display = '';
    row.classList.remove('manual-att-row--editing');
}

function toggleReasonFields(select) {
    select.classList.toggle('att-select--unset', select.value === 'not_recorded');
    const isExcused = select.value === 'absent_with_reason';
    const row = select.closest('tr');
    row.querySelectorAll('.reason-field').forEach(el => {
        el.disabled = !isExcused;
        el.style.opacity = isExcused ? '1' : '0.4';
    });
    row.querySelectorAll('[class*="support-doc-wrap"]').forEach(el => {
        el.style.display = isExcused ? '' : 'none';
    });
}

document.getElementById('attendanceForm')?.addEventListener('submit', function (e) {
    const notRecorded = [...this.querySelectorAll('select[name^="attendance["]:not([disabled])')]
        .filter(s => s.value === 'not_recorded').length;
    if (notRecorded > 0) {
        const ok = confirm(
            notRecorded + ' student(s) will have their attendance cleared (Not Recorded).\n\nSave anyway?'
        );
        if (!ok) e.preventDefault();
    }
});

function qrDurationChanged(radio) {
    document.querySelectorAll('input[name="qrDuration"]').forEach(function (r) {
        var lbl = r.closest('label');
        lbl.style.borderColor = r.checked ? '#0f172a' : '#e2e8f0';
        lbl.style.background  = r.checked ? '#f8fafc'  : '';
    });
    var customWrap = document.getElementById('qrCustomWrap');
    if (customWrap) customWrap.style.display = radio.value === 'custom' ? '' : 'none';
    if (radio.value !== 'custom') {
        var err = document.getElementById('qrCustomError');
        if (err) err.style.display = 'none';
    }
}

function submitQrForm() {
    var selected = document.querySelector('input[name="qrDuration"]:checked');
    if (!selected) return;

    var minutes;
    if (selected.value === 'custom') {
        var customInput = document.getElementById('qrCustomMinutes');
        var errorEl     = document.getElementById('qrCustomError');
        minutes = parseInt(customInput.value, 10);
        if (!minutes || minutes < 5 || minutes > 480) {
            customInput.style.borderColor = '#ef4444';
            if (errorEl) errorEl.style.display = '';
            customInput.focus();
            return;
        }
        customInput.style.borderColor = '#e2e8f0';
        if (errorEl) errorEl.style.display = 'none';
    } else {
        minutes = parseInt(selected.value, 10);
    }

    document.getElementById('qrDurationInput').value = minutes;
    document.getElementById('qrGenerateForm').submit();
}

let attSearchQuery  = '';
let attStatusFilter = '';

function applyAttendanceFilters() {
    document.querySelectorAll('.manual-att-row').forEach(row => {
        const name = row.dataset.name || '';
        const sid  = (row.dataset.sid || '').toLowerCase();
        const nric = row.dataset.nric || '';
        const matchesSearch = !attSearchQuery || name.includes(attSearchQuery) || sid.includes(attSearchQuery) || nric.includes(attSearchQuery);
        const matchesStatus = !attStatusFilter || row.dataset.origStatus === attStatusFilter;
        row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
    });
}

function filterAttendanceRows(query) {
    attSearchQuery = query.trim().toLowerCase();
    applyAttendanceFilters();
}

function filterByStatus(status, btn) {
    attStatusFilter = (attStatusFilter === status && status !== '') ? '' : status;
    document.querySelectorAll('.attendance-stat').forEach(el => el.classList.remove('attendance-stat--active'));
    if (attStatusFilter) btn.classList.add('attendance-stat--active');
    applyAttendanceFilters();
}
</script>
@endsection
