@extends('layouts.admin')

@section('title', 'Attendance Details')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-attendance.css') }}">
@endpush

@section('content')
<div class="admin-attendance-page">

    <div>
        <a href="{{ route('admin.attendance', $backParams) }}" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:#7c3aed;text-decoration:none;margin-bottom:10px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Back to Attendance
        </a>
        <h1 class="admin-attendance-page__title">{{ $lecturerName }}</h1>
        <p class="admin-attendance-page__subtitle">
            Batch: <span style="background:#ede9fe;color:#6d28d9;border-radius:6px;padding:2px 10px;font-size:12px;font-weight:600;">{{ $batchLabel }}</span>
            &mdash; {{ $selectedDate->format('d M Y') }}
        </p>
    </div>

    @if (session('success'))
        <div style="background:#dcfce7;color:#15803d;padding:10px 16px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:1rem;">
            {{ session('success') }}
        </div>
    @endif

    <section class="admin-attendance-panel">
        <div class="admin-attendance-table-wrap">
            <table class="admin-attendance-table admin-attendance-table--details">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>IC Number</th>
                        <th>Check-in</th>
                        <th>Status</th>
                        <th>Warning</th>
                        <th>Reason / Remarks</th>
                        <th style="width:70px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($studentRows as $s)
                        <tr class="admin-att-row"
                            data-sid="{{ $s->student_id }}"
                            data-status="{{ $s->status }}"
                            data-reason="{{ $s->reason_type }}"
                            data-remarks="{{ $s->remarks }}">
                            <td>{{ $s->student_name }}</td>
                            <td>{{ $s->student_id }}</td>
                            <td>{{ $s->ic_number }}</td>
                            <td>{{ $s->check_in }}</td>
                            <td>
                                <span class="admin-att-display">
                                    <span class="att-status att-status--{{ $s->status }}">{{ $s->status_label }}</span>
                                </span>
                                <select class="admin-att-input admin-att-status-select" style="display:none;padding:4px 6px;border-radius:6px;border:1px solid #e2e8f0;font-size:12px;">
                                    <option value="present" @selected($s->status === 'present')>Present</option>
                                    <option value="absent" @selected($s->status === 'absent')>Absent (TH)</option>
                                    <option value="absent_with_reason" @selected($s->status === 'absent_with_reason')>Excused (THS)</option>
                                </select>
                            </td>
                            <td>
                                <span class="wl-badge wl-badge--{{ ['green','amber','orange','red'][$s->warning_level] ?? 'green' }}">{{ $s->warning_label }}</span>
                            </td>
                            <td style="font-size:12px;color:#64748b;">
                                <span class="admin-att-display">
                                    @if ($s->reason_type)
                                        <span style="font-weight:600;">{{ ['sick_mc'=>'Sick / MC','official_program'=>'Official Program','family_emergency'=>'Family Emergency','personal_reason'=>'Personal Reason','other'=>'Other'][$s->reason_type] ?? $s->reason_type }}</span>
                                    @endif
                                    @if ($s->remarks)
                                        <br><span style="color:#94a3b8;">{{ $s->remarks }}</span>
                                    @endif
                                    @if (! $s->reason_type && ! $s->remarks)
                                        &mdash;
                                    @endif
                                </span>
                                <div class="admin-att-input" style="display:none;">
                                    <select class="admin-att-reason-select" style="width:100%;margin-bottom:4px;padding:4px 6px;border-radius:6px;border:1px solid #e2e8f0;font-size:12px;">
                                        <option value="" @selected(! $s->reason_type)>&mdash; Select reason &mdash;</option>
                                        <option value="sick_mc" @selected($s->reason_type === 'sick_mc')>Sick / MC</option>
                                        <option value="official_program" @selected($s->reason_type === 'official_program')>Official Program</option>
                                        <option value="family_emergency" @selected($s->reason_type === 'family_emergency')>Family Emergency</option>
                                        <option value="personal_reason" @selected($s->reason_type === 'personal_reason')>Personal Reason</option>
                                        <option value="other" @selected($s->reason_type === 'other')>Other</option>
                                    </select>
                                    <input type="text" class="admin-att-remarks-input" value="{{ $s->remarks }}" placeholder="Remarks…" style="width:100%;padding:4px 6px;border-radius:6px;border:1px solid #e2e8f0;font-size:12px;">
                                </div>
                            </td>
                            <td style="text-align:center;white-space:nowrap;">
                                <button type="button" class="admin-att-edit-btn" title="Edit attendance">Edit</button>
                                <span class="admin-att-save-cancel" style="display:none;">
                                    <button type="button" class="admin-att-save-btn" title="Save">Save</button>
                                    <button type="button" class="admin-att-cancel-btn" title="Cancel">Cancel</button>
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

</div>

<style>
.admin-att-edit-btn, .admin-att-save-btn, .admin-att-cancel-btn {
    font-size:11px;font-weight:600;padding:3px 9px;border-radius:6px;cursor:pointer;
}
.admin-att-edit-btn { border:1.5px solid #e2e8f0;background:#fff;color:#64748b; }
.admin-att-edit-btn:hover { background:#f1f5f9;border-color:#94a3b8;color:#1e293b; }
.admin-att-save-btn { border:1.5px solid #bbf7d0;background:#f0fdf4;color:#16a34a;margin-right:4px; }
.admin-att-save-btn:hover { background:#dcfce7;border-color:#4ade80; }
.admin-att-cancel-btn { border:1.5px solid #fca5a5;background:#fff;color:#dc2626; }
.admin-att-cancel-btn:hover { background:#fef2f2; }
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const selectedDateStr = @json($selectedDate->format('Y-m-d'));
    const csrfToken = @json(csrf_token());
    const storeUrl = @json(route('admin.attendance.store'));
    const body = document.querySelector('.admin-attendance-table--details tbody');

    const toggleRowEdit = (tr, editing) => {
        tr.querySelectorAll('.admin-att-display').forEach(el => el.style.display = editing ? 'none' : '');
        tr.querySelectorAll('.admin-att-input').forEach(el => el.style.display = editing ? '' : 'none');
        tr.querySelector('.admin-att-edit-btn').style.display = editing ? 'none' : '';
        tr.querySelector('.admin-att-save-cancel').style.display = editing ? 'inline-flex' : 'none';
    };

    body.addEventListener('click', (e) => {
        const tr = e.target.closest('tr.admin-att-row');
        if (! tr) return;

        if (e.target.classList.contains('admin-att-edit-btn')) {
            toggleRowEdit(tr, true);
            return;
        }

        if (e.target.classList.contains('admin-att-cancel-btn')) {
            tr.querySelector('.admin-att-status-select').value = tr.dataset.status;
            tr.querySelector('.admin-att-reason-select').value = tr.dataset.reason;
            tr.querySelector('.admin-att-remarks-input').value = tr.dataset.remarks;
            toggleRowEdit(tr, false);
            return;
        }

        if (e.target.classList.contains('admin-att-save-btn')) {
            const btn        = e.target;
            const status     = tr.querySelector('.admin-att-status-select').value;
            const isExcused  = status === 'absent_with_reason';
            const reasonType = isExcused ? tr.querySelector('.admin-att-reason-select').value : '';
            const remarks    = isExcused ? tr.querySelector('.admin-att-remarks-input').value : '';

            btn.disabled = true;
            fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new URLSearchParams({
                    student_id: tr.dataset.sid,
                    date: selectedDateStr,
                    status: status,
                    reason_type: reasonType,
                    remarks: remarks,
                }),
            }).then(res => {
                if (! res.ok) throw new Error('Save failed');
                location.reload();
            }).catch(() => {
                alert('Failed to save attendance. Please try again.');
                btn.disabled = false;
            });
        }
    });
});
</script>
@endpush
