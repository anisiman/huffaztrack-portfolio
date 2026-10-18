@extends('layouts/lecturer')

@section('title', 'Attendance Appeals')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/lecturer-attendance.css') }}">
@endpush

@section('content')
<div class="attendance-page">

    @include('partials.back-button', ['href' => route('lecturer.attendance'), 'label' => 'Attendance'])

    <div style="margin-bottom:1.5rem;">
        <h1 class="attendance-page__title">Attendance Appeals</h1>
        <p class="attendance-page__subtitle">Review excuse letters submitted by your students.</p>
    </div>

    @if (session('success'))
        <div class="attendance-alert attendance-alert--success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="attendance-alert attendance-alert--error">{{ session('error') }}</div>
    @endif

    {{-- ── PENDING APPEALS ── --}}
    <section class="attendance-panel" style="margin-bottom:2rem;">
        <div class="attendance-panel__header">
            <div>
                <h2 class="attendance-panel__title">
                    Pending Review
                    @if ($pending->count() > 0)
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:#ef4444;color:#fff;font-size:11px;font-weight:700;margin-left:6px;">{{ $pending->count() }}</span>
                    @endif
                </h2>
                <p class="attendance-panel__subtitle">Approve or reject each request. Approved appeals automatically update attendance to Excused (THS).</p>
            </div>
        </div>

        @if ($pending->isEmpty())
            <div style="padding:2.5rem;text-align:center;color:#94a3b8;font-size:14px;">
                No pending appeals. All caught up!
            </div>
        @else
            <div class="attendance-table-wrap">
                <table class="attendance-table" style="min-width:720px;">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Date</th>
                            <th>Reason</th>
                            <th>Remarks</th>
                            <th>Document</th>
                            <th>Submitted</th>
                            <th style="width:160px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pending as $appeal)
                        <tr>
                            <td>
                                <span style="font-weight:600;color:#1e293b;display:block;">{{ $appeal->student?->student_name ?? '—' }}</span>
                                <span style="font-size:11px;color:#94a3b8;">{{ $appeal->student_id }}</span>
                            </td>
                            <td style="white-space:nowrap;font-size:13px;">
                                {{ $appeal->date->format('d M Y') }}<br>
                                <span style="font-size:11px;color:#94a3b8;">{{ $appeal->date->format('D') }}</span>
                            </td>
                            <td style="font-size:13px;color:#475569;">
                                {{ $appeal->reason_type ? ucwords(str_replace('_', ' ', $appeal->reason_type)) : '—' }}
                            </td>
                            <td style="font-size:13px;color:#475569;max-width:180px;">
                                {{ $appeal->remarks ?: '—' }}
                            </td>
                            <td>
                                <a href="{{ route('documents.appeal', $appeal->id) }}" target="_blank"
                                   style="font-size:12px;color:#0369a1;text-decoration:none;font-weight:600;">
                                    View Document ↗
                                </a>
                            </td>
                            <td style="font-size:12px;color:#94a3b8;white-space:nowrap;">
                                {{ $appeal->created_at->setTimezone('Asia/Kuala_Lumpur')->format('d M, g:i A') }}
                            </td>
                            <td>
                                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                    {{-- Approve --}}
                                    <form method="POST" action="{{ route('lecturer.attendance.appeals.approve', $appeal->id) }}" style="margin:0;">
                                        @csrf
                                        <button type="submit" class="attendance-button attendance-button--success"
                                                style="font-size:12px;padding:5px 12px;"
                                                onclick="return confirm('Approve this excuse letter? Attendance will be updated to Excused (THS).')">
                                            ✓ Approve
                                        </button>
                                    </form>
                                    {{-- Reject --}}
                                    <button type="button"
                                            class="attendance-button attendance-button--danger"
                                            style="font-size:12px;padding:5px 12px;"
                                            onclick="openRejectModal({{ $appeal->id }}, '{{ addslashes($appeal->student?->student_name ?? '') }}', '{{ $appeal->date->format('d M Y') }}')">
                                        ✕ Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- ── HISTORY ── --}}
    <section class="attendance-panel">
        <div class="attendance-panel__header">
            <div>
                <h2 class="attendance-panel__title">Decision History</h2>
                <p class="attendance-panel__subtitle">Last 50 decided appeals.</p>
            </div>
        </div>

        @if ($history->isEmpty())
            <div style="padding:2rem;text-align:center;color:#94a3b8;font-size:14px;">No history yet.</div>
        @else
            <div class="attendance-table-wrap">
                <table class="attendance-table" style="min-width:680px;">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Date</th>
                            <th>Reason</th>
                            <th>Document</th>
                            <th>Decision</th>
                            <th>Decided</th>
                            <th>Rejection Remarks</th>
                            <th style="width:110px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $appeal)
                        <tr>
                            <td>
                                <span style="font-weight:600;color:#1e293b;display:block;">{{ $appeal->student?->student_name ?? '—' }}</span>
                                <span style="font-size:11px;color:#94a3b8;">{{ $appeal->student_id }}</span>
                            </td>
                            <td style="font-size:13px;white-space:nowrap;">{{ $appeal->date->format('d M Y') }}</td>
                            <td style="font-size:13px;color:#475569;">
                                {{ $appeal->reason_type ? ucwords(str_replace('_', ' ', $appeal->reason_type)) : '—' }}
                            </td>
                            <td>
                                <a href="{{ route('documents.appeal', $appeal->id) }}" target="_blank"
                                   style="font-size:12px;color:#0369a1;text-decoration:none;">View ↗</a>
                            </td>
                            <td>
                                @if ($appeal->isApproved())
                                    <span class="att-hist-status att-hist-status--present">Approved</span>
                                @else
                                    <span class="att-hist-status att-hist-status--absent">Rejected</span>
                                @endif
                            </td>
                            <td style="font-size:12px;color:#94a3b8;white-space:nowrap;">
                                {{ $appeal->decided_at?->setTimezone('Asia/Kuala_Lumpur')->format('d M, g:i A') ?? '—' }}
                            </td>
                            <td style="font-size:13px;color:#64748b;">{{ $appeal->rejection_remarks ?: '—' }}</td>
                            <td>
                                {{-- Only the lecturer who decided can undo; others see who it was. --}}
                                @if ((string) $appeal->lecturer_id !== (string) $lecturerId)
                                    <span style="font-size:11px;color:#94a3b8;">By {{ $appeal->lecturer?->lecturer_name ?? 'previous lecturer' }}</span>
                                @else
                                @php
                                    $undoMsg = 'Undo this decision? The appeal goes back to Pending Review'
                                        . ($appeal->isApproved()
                                            ? ", and this student's attendance for " . $appeal->date->format('d M Y') . ' reverts to Absent (TH).'
                                            : '.');
                                @endphp
                                <form method="POST" action="{{ route('lecturer.attendance.appeals.undo', $appeal->id) }}" style="margin:0;">
                                    @csrf
                                    <button type="submit" class="attendance-button attendance-button--secondary"
                                            style="font-size:12px;padding:5px 12px;"
                                            onclick="return confirm(@js($undoMsg))">
                                        &#8617; Undo
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>

{{-- ── Reject Modal ── --}}
<div id="rejectModal"
     style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,.5);align-items:center;justify-content:center;"
     onclick="if(event.target===this) closeRejectModal()">
    <div style="background:#fff;border-radius:16px;padding:28px 32px;width:100%;max-width:420px;margin:0 16px;box-shadow:0 20px 60px rgba(0,0,0,.18);">
        <h3 style="font-size:1rem;font-weight:700;color:#0f172a;margin:0 0 4px;">Reject Appeal</h3>
        <p id="rejectModalDesc" style="font-size:13px;color:#64748b;margin:0 0 18px;"></p>

        <form id="rejectForm" method="POST">
            @csrf
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:6px;text-transform:uppercase;letter-spacing:.4px;">
                    Rejection Remarks <span style="font-weight:400;color:#94a3b8;">(optional)</span>
                </label>
                <textarea name="rejection_remarks" rows="3" placeholder="Reason for rejection…"
                    style="width:100%;padding:9px 12px;border-radius:10px;border:1.5px solid #e2e8f0;font-size:13px;color:#1e293b;resize:none;outline:none;box-sizing:border-box;"></textarea>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" onclick="closeRejectModal()"
                    style="padding:9px 18px;border-radius:10px;border:1.5px solid #e2e8f0;background:#fff;font-size:13px;color:#475569;cursor:pointer;font-weight:500;">
                    Cancel
                </button>
                <button type="submit" class="attendance-button attendance-button--danger" style="font-size:13px;">
                    Confirm Reject
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.att-hist-status {
    display:inline-flex;align-items:center;
    font-size:11.5px;font-weight:600;
    padding:3px 9px;border-radius:20px;white-space:nowrap;
}
.att-hist-status--present { background:#dcfce7;color:#15803d; }
.att-hist-status--absent  { background:#fee2e2;color:#dc2626; }
</style>

@endsection

@section('scripts')
<script>
function openRejectModal(appealId, studentName, dateLabel) {
    document.getElementById('rejectModalDesc').textContent =
        'Rejecting excuse letter from ' + studentName + ' (' + dateLabel + ').';
    document.getElementById('rejectForm').action = '/lecturer/attendance/appeals/' + appealId + '/reject';
    document.getElementById('rejectModal').style.display = 'flex';
}
function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}
</script>
@endsection
