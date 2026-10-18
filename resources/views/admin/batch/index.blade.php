@extends('layouts.admin')

@section('title', 'Batch Management')

@push('styles')
<style>

    /* PAGE */
    .users-page {
        padding: 2.25rem 2rem;
        max-width: 1600px;
        margin: 0 auto;
    }

    /* HEADER */
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

    /* BUTTON */
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
    .batch-page {
        padding: 1.5rem;
        max-width: 1600px;
        margin: 0 auto;
    }

    .batch-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #eef2f7;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .batch-table {
        width: 100%;
        border-collapse: collapse;
    }

    .batch-table thead {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .batch-table thead th {
        padding: 18px 20px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        color: #64748b;
        text-transform: uppercase;
        text-align: left;
    }

    .batch-table tbody td {
        padding: 18px 20px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.92rem;
        color: #334155;
        vertical-align: middle;
    }

    .batch-table tbody tr:hover {
        background: #fafafa;
    }

    .batch-chip {
        background: #ede9fe;
        color: #7c3aed;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
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

<div class="batch-page">

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

    {{-- HEADER --}}
    <div class="users-page__header">
        <div>
            <h1 class="users-page__title">Batch Management</h1>
            <div class="users-page__breadcrumb">
                <span class="users-page__breadcrumb-accent">
                    System
                </span>

                <span>&gt;</span>
                <span>Batch Management</span>
            </div>
        </div>

        {{-- ADD BUTTON --}}
        <button onclick="openCreateBatchModal()"
                class="users-button users-button--primary">
            <svg xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
                style="width:18px;">

                <path stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            <span>New Batch</span>
        </button>
    </div>

    {{-- TABLE CARD --}}
    <div class="batch-card" style="margin-top: 2rem;">
        <table class="batch-table">
            <thead>
                <tr>
                    <th style="width: 80px;" >No</th>
                    <th>Batch</th>
                    <th>Intake</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Total Students</th>
                    <th style="text-align:center;">Active</th>
                    <th style="text-align:center;">Completed</th>
                    <th style="text-align:center;">Dropped/Failed</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $index => $batch)
                    <tr>
                        {{-- NO --}}
                        <td>
                            {{ $index + 1 }}
                        </td>
                        {{-- BATCH NAME --}}
                        <td>
                            <span class="batch-chip">
                                {{ $batch->batch_name }}
                            </span>
                        </td>
                        {{-- INTAKE --}}
                        <td>
                            {{ $batch->intake }}
                        </td>
                        {{-- STATUS --}}
                        <td style="text-align:center;">
                            @if ($batch->is_active)
                                <span style="display:inline-flex; align-items:center; justify-content:center; padding:4px 12px; border-radius:999px; background:#dcfce7; color:#16a34a; font-weight:700; font-size:0.75rem; text-transform:uppercase;">
                                    Active
                                </span>
                            @else
                                <span style="display:inline-flex; align-items:center; justify-content:center; padding:4px 12px; border-radius:999px; background:#f1f5f9; color:#64748b; font-weight:700; font-size:0.75rem; text-transform:uppercase;">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        {{-- TOTAL --}}
                        <td style="text-align:center;">
                            {{ $batch->students_count }}
                        </td>
                        {{-- ACTIVE --}}
                        <td style="text-align:center;">
                            <span style="display:inline-flex; align-items:center; justify-content:center; min-width:42px; padding:6px 12px; border-radius:999px; background:#dcfce7; color:#16a34a; font-weight:700; font-size:0.85rem;">
                                {{ $batch->active_students_count }}
                            </span>
                        </td>
                        {{-- COMPLETED --}}
                        <td style="text-align:center;">
                            <span style="display:inline-flex; align-items:center; justify-content:center; min-width:42px; padding:6px 12px; border-radius:999px; background:#e0e7ff; color:#4c1d95; font-weight:700; font-size:0.85rem;">
                                {{ $batch->completed_students_count }}
                            </span>
                        </td>
                        {{-- DROPPED --}}
                        <td style="text-align:center;">
                            <span style="display:inline-flex; align-items:center; justify-content:center; min-width:42px; padding:6px 12px; border-radius:999px; background:#fee2e2; color:#b91c1c; font-weight:700; font-size:0.85rem;">
                                {{ $batch->dropped_students_count }}
                            </span>
                        </td>
                        {{-- ACTIONS --}}
                        <td>

                            <div style="
                                display:flex;
                                justify-content:center;
                                gap:10px;
                            ">
                                {{-- EDIT --}}
                                <button onclick="openEditBatchModal('{{ $batch->batch_id }}', '{{ $batch->batch_name }}', '{{ $batch->intake }}', {{ $batch->is_active ? 'true' : 'false' }}, {{ (int) $batch->active_students_count }})"
                                        class="action-btn btn-edit">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        width="18"
                                        height="18"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round">

                                        <path d="M11 4H4a2 2 0 0 0-2 2v14 a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>

                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>

                                {{-- DELETE --}}
                                <form action="{{ route('admin.batches.destroy', $batch->batch_id) }}" method="POST" onsubmit="return confirm('Delete this batch?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn btn-delete">

                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            width="18"
                                            height="18"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round">

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
                        <td colspan="8">
                            <div class="empty-state">
                                No batches found.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@include('admin.batch.partials.create-modal')
@include('admin.batch.partials.edit-modal')
@push('scripts')
<script>

    function openCreateBatchModal() {
        document.getElementById('createBatchModal')
            .classList.remove('hidden');

        document.getElementById('createBatchModal')
            .classList.add('flex');
    }

    function closeCreateBatchModal() {
        document.getElementById('createBatchModal')
            .classList.add('hidden');

        document.getElementById('createBatchModal')
            .classList.remove('flex');
    }

    let editBatchOriginalActive = true;

    function openEditBatchModal(id, name, intake, isActive, activeStudentCount) {

        const form = document.getElementById('editBatchForm');
        form.action = `/admin/batches/${id}`;
        form.dataset.activeStudentCount = activeStudentCount;

        document.getElementById('edit_batch_name')
            .value = name;

        document.getElementById('edit_intake')
            .value = intake;

        document.getElementById('edit_is_active')
            .checked = isActive;

        editBatchOriginalActive = isActive;

        document.getElementById('editBatchModal')
            .classList.remove('hidden');

        document.getElementById('editBatchModal')
            .classList.add('flex');
    }

    function closeEditBatchModal() {

        document.getElementById('editBatchModal')
            .classList.add('hidden');

        document.getElementById('editBatchModal')
            .classList.remove('flex');
    }

    document.getElementById('editBatchForm').addEventListener('submit', function(e) {
        const checkbox = document.getElementById('edit_is_active');
        const activeStudentCount = parseInt(this.dataset.activeStudentCount || '0', 10);

        // Only warn on the exact transition that triggers the mass status change:
        // batch was active, admin is now unchecking it, and it actually has active students.
        if (editBatchOriginalActive && !checkbox.checked && activeStudentCount > 0) {
            const confirmed = confirm(
                `This batch currently has ${activeStudentCount} active student(s). ` +
                `Marking it inactive will change all of them to "Tamat Pensijilan" status. Continue?`
            );

            if (!confirmed) {
                e.preventDefault();
            }
        }
    });

</script>
@endpush
@endsection
