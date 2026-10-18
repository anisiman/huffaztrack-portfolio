@extends('layouts.admin')

@section('title', 'Academic Semester Management')

@push('styles')
<style>

    .users-page {
        padding: 2.25rem 2rem;
        max-width: 1600px;
        margin: 0 auto;
    }

    .users-page__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .users-page__title {
        font-size: 2.25rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.25;
        margin-bottom: 0.125rem;
    }

    .users-page__breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-size: 0.95rem;
        color: #94a3b8;
        font-weight: 500;
    }

    .users-page__breadcrumb-accent {
        color: #7c3aed;
        font-weight: 700;
    }

    .users-button {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border-radius: 14px;
        padding: 0.9rem 1.3rem;
        font-weight: 600;
        font-size: 0.95rem;
        transition: 0.2s;
    }

    .users-button--primary {
        background: #7c3aed;
        color: white;
        box-shadow: 0 10px 25px rgba(124,58,237,0.25);
    }

    .users-button--primary:hover {
        background: #6d28d9;
    }

    .period-page {
        padding: 1.5rem;
        max-width: 1600px;
        margin: 0 auto;
    }

    .period-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #eef2f7;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .period-table {
        width: 100%;
        border-collapse: collapse;
    }

    .period-table thead {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .period-table thead th {
        padding: 18px 20px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        color: #64748b;
        text-transform: uppercase;
        text-align: left;
    }

    .period-table tbody td {
        padding: 18px 20px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.92rem;
        color: #334155;
        vertical-align: middle;
    }

    .period-table tbody tr:hover {
        background: #fafafa;
    }

    .period-chip {
        background: #ede9fe;
        color: #7c3aed;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-badge-period {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 12px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
    }

    .status-badge-period--active {
        background: #dcfce7;
        color: #16a34a;
    }

    .status-badge-period--upcoming {
        background: #e0f2fe;
        color: #0284c7;
    }

    .status-badge-period--completed {
        background: #f1f5f9;
        color: #64748b;
    }

    .status-lock-tag {
        font-size: 0.65rem;
        color: #94a3b8;
        font-weight: 600;
        margin-left: 4px;
    }

    .action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        transition: 0.2s;
    }

    .action-btn:hover {
        background: #f8fafc;
    }

    .btn-delete {
        color: #ef4444;
    }

    .btn-edit {
        color: #64748b;
    }

    .empty-state {
        padding: 3rem;
        text-align: center;
        color: #94a3b8;
    }
</style>
@endpush

@section('content')

<div class="period-page">

    @if (session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-semibold shadow-sm flex items-center gap-2">
            <span>✅</span> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm font-semibold shadow-sm flex items-center gap-2">
            <span>⚠️</span> {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm font-semibold shadow-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- HEADER --}}
    <div class="users-page__header">
        <div>
            <h1 class="users-page__title">Academic Semester Management</h1>
            <div class="users-page__breadcrumb">
                <span class="users-page__breadcrumb-accent">System</span>
                <span>&gt;</span>
                <span>Academic Semesters</span>
            </div>
        </div>

        <button onclick="openCreatePeriodModal()" class="users-button users-button--primary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor" style="width:18px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            <span>New Academic Semester</span>
        </button>
    </div>

    {{-- TABLE CARD --}}
    <div class="period-card" style="margin-top: 2rem;">
        <table class="period-table">
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Name</th>
                    <th>Academic Year</th>
                    <th style="text-align:center;">Semester No.</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Halaqah Assignments</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($periods as $index => $period)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <span class="period-chip">{{ $period->name }}</span>
                        </td>
                        <td>{{ $period->academic_year }}</td>
                        <td style="text-align:center;">{{ $period->period_number }}</td>
                        <td>{{ $period->start_date->format('d M Y') }}</td>
                        <td>{{ $period->end_date->format('d M Y') }}</td>
                        <td style="text-align:center;">
                            @php
                                $statusClass = match ($period->status) {
                                    'active' => 'status-badge-period--active',
                                    'upcoming' => 'status-badge-period--upcoming',
                                    default => 'status-badge-period--completed',
                                };
                            @endphp
                            <span class="status-badge-period {{ $statusClass }}">
                                {{ $period->status }}
                            </span>
                            @if ($period->status_locked)
                                <span class="status-lock-tag" title="Manually set — not auto-updated from dates">🔒 manual</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            {{ $period->halaqah_assignments_count }}
                        </td>
                        <td>
                            <div style="display:flex; justify-content:center; gap:10px; flex-wrap: wrap;">
                                @if ($period->halaqah_assignments_count === 0)
                                    <form action="{{ route('admin.academic-periods.seedFromLive', $period->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Copy all current live lecturer-student links into \'{{ addslashes($period->name) }}\'? Use this once, when adopting Academic Semesters after assignments already exist.');">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-1.5 bg-violet-50 hover:bg-violet-600 text-violet-700 hover:text-white text-xs font-bold rounded-lg transition border border-violet-100"
                                            title="Copy current live lecturer-student links into this semester">
                                            Seed from Live
                                        </button>
                                    </form>
                                @endif

                                <button onclick="openBatchLevelsModal(
                                        {{ $period->id }},
                                        '{{ addslashes($period->name) }}'
                                    )" class="action-btn"
                                    style="color:#4338ca; border-color:#c7d2fe;"
                                    title="Manage which Batch is at which Semester Level">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                                        <path d="M8 2v4M16 2v4M3 10h18"></path>
                                        <path d="M8 15h3M8 18h5"></path>
                                    </svg>
                                </button>

                                @php
                                    [$wl1, $wl2, $wl3] = \App\Models\WarningThreshold::effectiveFor($period);
                                @endphp
                                <button onclick="openThresholdsModal(
                                        {{ $period->id }},
                                        '{{ addslashes($period->name) }}',
                                        {{ $wl1 }}, {{ $wl2 }}, {{ $wl3 }}
                                    )" class="action-btn"
                                    style="color:#b45309; border-color:#fde68a;"
                                    title="Edit Warning Letter thresholds">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                                        <path d="M12 15.75h.007v.008H12v-.008Z"></path>
                                    </svg>
                                </button>

                                <button onclick="openEditPeriodModal(
                                        {{ $period->id }},
                                        '{{ addslashes($period->name) }}',
                                        '{{ $period->academic_year }}',
                                        {{ $period->period_number }},
                                        '{{ $period->start_date->format('Y-m-d') }}',
                                        '{{ $period->end_date->format('Y-m-d') }}',
                                        {{ $period->status_locked ? 'true' : 'false' }},
                                        '{{ $period->status }}',
                                        '{{ addslashes($period->notes ?? '') }}'
                                    )" class="action-btn btn-edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14 a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>

                                <form action="{{ route('admin.academic-periods.destroy', $period->id) }}" method="POST"
                                    onsubmit="return confirm('Delete this academic semester?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn btn-delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6 m3 0V4a2 2 0 0 1 2-2h4 a2 2 0 0 1 2 2v2">
                                            </path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">No academic semesters found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('admin.academic-periods.partials.create-modal')
@include('admin.academic-periods.partials.edit-modal')
@include('admin.academic-periods.partials.thresholds-modal')
@include('admin.academic-periods.partials.batch-levels-modal')

@push('scripts')
<script>

    const batchLevelsByPeriod = @json($batchLevelsByPeriod);

    function openBatchLevelsModal(id, name) {
        const form = document.getElementById('batchLevelsForm');
        form.action = `/admin/academic-periods/${id}/batch-levels`;

        document.getElementById('batch_levels_semester_name').textContent = name;

        const mapping = batchLevelsByPeriod[id] || {};
        document.querySelectorAll('#batchLevelsForm select[data-batch-id]').forEach(function (select) {
            const batchId = select.dataset.batchId;
            select.value = mapping[batchId] ?? '';
        });

        document.getElementById('batchLevelsModal').classList.remove('hidden');
        document.getElementById('batchLevelsModal').classList.add('flex');
    }

    function closeBatchLevelsModal() {
        document.getElementById('batchLevelsModal').classList.add('hidden');
        document.getElementById('batchLevelsModal').classList.remove('flex');
    }

    function openCreatePeriodModal() {
        document.getElementById('createPeriodModal').classList.remove('hidden');
        document.getElementById('createPeriodModal').classList.add('flex');
    }

    function closeCreatePeriodModal() {
        document.getElementById('createPeriodModal').classList.add('hidden');
        document.getElementById('createPeriodModal').classList.remove('flex');
    }

    function toggleStatusOverride(prefix) {
        const checkbox = document.getElementById(prefix + '_override_status');
        const wrap = document.getElementById(prefix + '_status_wrap');
        wrap.style.display = checkbox.checked ? 'block' : 'none';
    }

    function openEditPeriodModal(id, name, academicYear, periodNumber, startDate, endDate, statusLocked, status, notes) {
        const form = document.getElementById('editPeriodForm');
        form.action = `/admin/academic-periods/${id}`;

        document.getElementById('edit_name').value = name;
        document.getElementById('edit_academic_year').value = academicYear;
        document.getElementById('edit_period_number').value = periodNumber;
        document.getElementById('edit_start_date').value = startDate;
        document.getElementById('edit_end_date').value = endDate;
        document.getElementById('edit_notes').value = notes;

        document.getElementById('edit_override_status').checked = statusLocked;
        document.getElementById('edit_status').value = status;
        toggleStatusOverride('edit');

        document.getElementById('editPeriodModal').classList.remove('hidden');
        document.getElementById('editPeriodModal').classList.add('flex');
    }

    function closeEditPeriodModal() {
        document.getElementById('editPeriodModal').classList.add('hidden');
        document.getElementById('editPeriodModal').classList.remove('flex');
    }

    function openThresholdsModal(id, name, w1, w2, w3) {
        const form = document.getElementById('thresholdsForm');
        form.action = `/admin/academic-periods/${id}/thresholds`;

        document.getElementById('thresholds_semester_name').textContent = name;
        document.getElementById('threshold_1').value = w1;
        document.getElementById('threshold_2').value = w2;
        document.getElementById('threshold_3').value = w3;

        document.getElementById('thresholdsModal').classList.remove('hidden');
        document.getElementById('thresholdsModal').classList.add('flex');
    }

    function closeThresholdsModal() {
        document.getElementById('thresholdsModal').classList.add('hidden');
        document.getElementById('thresholdsModal').classList.remove('flex');
    }

</script>
@endpush
@endsection
