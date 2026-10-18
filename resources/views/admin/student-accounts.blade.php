@extends('layouts.admin')

@section('title', 'Student Accounts')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">
    <style>
        .users-page { display: flex; flex-direction: column; gap: 1.5rem; }

        .cell-user { display: flex; align-items: center; gap: 10px; }
        .cell-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700; flex-shrink: 0;
        }
        .cell-name  { font-weight: 600; font-size: 13.5px; color: #1e293b; line-height: 1.2; }
        .cell-sub   { font-size: 11.5px; color: #94a3b8; margin-top: 1px; }

        .cell-actions { display: flex; align-items: center; gap: 4px; justify-content: flex-end; }
        .act-btn {
            width: 30px; height: 30px; border-radius: 8px; border: 1px solid #e2e8f0;
            background: transparent; display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: #64748b; transition: all 0.13s; position: relative;
            text-decoration: none;
        }
        .act-btn svg { width: 14px; height: 14px; }
        .act-btn:hover { border-color: #cbd5e1; }
        .act-btn.a-edit:hover  { background: #f5f3ff; color: #7c3aed; border-color: #c4b5fd; }
        .act-btn.a-reset:hover { background: #fffbeb; color: #d97706; border-color: #fde68a; }
        .act-btn.a-del:hover   { background: #fef2f2; color: #ef4444; border-color: #fca5a5; }
        .act-btn::after {
            content: attr(data-tip); position: absolute; bottom: calc(100% + 6px);
            left: 50%; transform: translateX(-50%); background: #1e293b; color: #fff;
            font-size: 11px; font-family: 'Segoe UI', system-ui, sans-serif;
            padding: 3px 8px; border-radius: 5px; white-space: nowrap;
            pointer-events: none; opacity: 0; transition: opacity 0.15s;
        }
        .act-btn:hover::after { opacity: 1; }

        .m-backdrop {
            display: none; position: fixed; inset: 0; z-index: 200;
            background: rgba(15,23,42,0.45); backdrop-filter: blur(3px);
            align-items: center; justify-content: center; padding: 1.5rem;
        }
        .m-backdrop.is-open { display: flex; }
        .m-box {
            background: #fff; border-radius: 20px; width: 100%;
            box-shadow: 0 24px 60px rgba(15,23,42,0.18);
            animation: mIn 0.18s ease; overflow: hidden;
        }
        @keyframes mIn { from { opacity:0; transform:scale(0.96) translateY(8px); } to { opacity:1; transform:none; } }
        .m-head {
            display: flex; align-items: flex-start; justify-content: space-between;
            padding: 22px 24px 16px; border-bottom: 1px solid #f1f5f9;
        }
        .m-title { font-size: 17px; font-weight: 700; color: #0f172a; }
        .m-desc  { font-size: 12.5px; color: #94a3b8; margin-top: 3px; }
        .m-close {
            width: 30px; height: 30px; border-radius: 8px; border: 1px solid #e2e8f0;
            background: transparent; display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: #64748b; flex-shrink: 0; transition: all 0.13s;
        }
        .m-close:hover { background: #f1f5f9; }
        .m-close svg   { width: 14px; height: 14px; }
        .m-body  { padding: 20px 24px; }
        .m-foot  { display: flex; justify-content: flex-end; gap: 8px; padding: 14px 24px 20px; border-top: 1px solid #f1f5f9; }

        .fm-grid   { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
        .fm-full   { grid-column: 1 / -1; }
        .fm-group  { display: flex; flex-direction: column; gap: 5px; }
        .fm-label  { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.07em; }
        .fm-input, .fm-select {
            height: 40px; padding: 0 12px; border: 1px solid #e2e8f0;
            border-radius: 10px; background: #f8fafc; font-size: 13.5px;
            color: #0f172a; font-family: 'Segoe UI', system-ui, sans-serif;
            outline: none; transition: all 0.13s; width: 100%;
        }
        .fm-input:focus, .fm-select:focus {
            border-color: #7c3aed; background: #fff;
            box-shadow: 0 0 0 3px rgba(124,58,237,0.1);
        }
        .fm-input::placeholder { color: #94a3b8; }
        .fm-input:disabled, .fm-input[readonly] { background: #f1f5f9; color: #64748b; cursor: default; }
        .fm-select {
            appearance: none; cursor: pointer; padding-right: 32px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='11' height='11' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 10px center; background-color: #f8fafc;
        }
        .fm-divider  { height: 1px; background: #f1f5f9; margin: 16px 0; }
        .fm-section  { font-size: 10.5px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.09em; margin-bottom: 12px; }
        .fm-hint     { font-size: 12px; color: #94a3b8; margin-top: 2px; }

        .pw-wrap { position: relative; }
        .pw-wrap .fm-input { padding-right: 38px; }
        .pw-eye {
            position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
            cursor: pointer; color: #94a3b8; background: none; border: none;
            padding: 0; display: flex; align-items: center;
        }
        .pw-eye svg { width: 16px; height: 16px; }
        .pw-eye:hover { color: #64748b; }

        .reset-info {
            background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px;
            padding: 12px 14px; display: flex; gap: 10px; margin-bottom: 16px;
        }
        .reset-info svg { width: 18px; height: 18px; color: #d97706; flex-shrink: 0; margin-top: 1px; }
        .reset-info p   { font-size: 12.5px; color: #92400e; line-height: 1.55; }
        .reset-for {
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
            padding: 10px 14px; font-size: 13px; color: #64748b; margin-bottom: 16px;
        }
        .reset-for strong { color: #0f172a; }

        .del-icon-wrap {
            width: 58px; height: 58px; border-radius: 50%; background: #fef2f2;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px; color: #ef4444;
        }
        .del-icon-wrap svg { width: 28px; height: 28px; }

        .m-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            height: 40px; padding: 0 18px; border-radius: 10px;
            font-size: 13.5px; font-weight: 600; cursor: pointer;
            border: 1px solid transparent; font-family: 'Segoe UI', system-ui, sans-serif;
            transition: all 0.13s; white-space: nowrap; text-decoration: none;
        }
        .m-btn svg { width: 15px; height: 15px; }
        .m-btn-ghost   { background: #fff; color: #64748b; border-color: #e2e8f0; }
        .m-btn-ghost:hover { background: #f8fafc; border-color: #cbd5e1; }
        .m-btn-primary { background: linear-gradient(90deg,#6d28d9,#7c3aed); color: #fff; }
        .m-btn-primary:hover { background: linear-gradient(90deg,#5b21b6,#6d28d9); }
        .m-btn-amber   { background: #d97706; color: #fff; border-color: #d97706; }
        .m-btn-amber:hover { background: #b45309; }
        .m-btn-danger  { background: #ef4444; color: #fff; border-color: #ef4444; }
        .m-btn-danger:hover { background: #dc2626; }
    </style>
@endpush

@section('content')
<div class="w-full px-6 py-10 bg-slate-50 min-h-screen users-page">

    {{-- Page header --}}
    <div class="users-page__header">
        <div>
            <h1 class="users-page__title">Student Accounts</h1>
            <div class="users-page__breadcrumb">
                <span class="users-page__breadcrumb-accent">User Management</span>
                <span>&gt;</span>
                <span>Student Accounts</span>
            </div>
        </div>
        <div class="users-page__actions">
            <button type="button" class="users-button users-button--primary" onclick="openModal('addModal')">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3M15 6.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0" />
                </svg>
                <span>Add Student Account</span>
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="users-alert users-alert--success">{{ session('success') }}</div>
    @endif

    {{-- Stat Cards --}}
    <div class="users-stats users-stats--four">
        <article class="users-stat-card users-stat-card--violet">
            <div class="users-stat-card__icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.742-.479 3 3 0 0 0-4.682-2.72m.94 3.198-.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.204-.576-5.962-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a5.971 5.971 0 0 0-.94 3.197m0 0a9.093 9.093 0 0 1-3.743-.479 3 3 0 0 1 4.682-2.72m.001.031A5.994 5.994 0 0 1 12 12.75a5.994 5.994 0 0 1 5.058 2.772M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>
            </div>
            <p class="users-stat-card__label">Total Students Accounts</p>
            <p class="users-stat-card__value">{{ $totalStudents }} Students</p>
        </article>

        <article class="users-stat-card users-stat-card--emerald">
            <div class="users-stat-card__icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.552-.645-6.452-1.766v-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </div>
            <p class="users-stat-card__label">Active Accounts</p>
            <p class="users-stat-card__value">{{ $activeStudents }} Students</p>
        </article>

        <article class="users-stat-card users-stat-card--amber">
            <div class="users-stat-card__icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M22 10.5h-6m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                </svg>
            </div>
            <p class="users-stat-card__label">Inactive Accounts</p>
            <p class="users-stat-card__value">{{ $inactiveStudents }} Students</p>
        </article>

        <article class="users-stat-card users-stat-card--blue">
            <div class="users-stat-card__icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
            </div>
            <p class="users-stat-card__label">New This Month</p>
            <p class="users-stat-card__value">{{ $newThisMonth }} Students</p>
        </article>
    </div>

    {{-- Panel: search/filter header + table --}}
    <section class="users-panel">
        <div class="users-panel__header">
            <h2 class="users-panel__title">Student Accounts</h2>
            <form method="GET" action="{{ route('admin.students') }}" id="filterForm">
                <div class="users-panel__tools">
                    <label class="users-search">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.65 7.65 0 1 0 5.825 5.825a7.65 7.65 0 0 0 10.825 10.825Z" />
                        </svg>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or email…">
                    </label>
                    <select name="status" class="users-filter users-filter--select" onchange="document.getElementById('filterForm').submit()">
                        <option value="">Account Status</option>
                        <option value="Active"   {{ $status === 'Active'   ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ $status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <button type="submit" class="users-button users-button--secondary">Apply</button>
                    <a href="{{ route('admin.students') }}" class="users-button users-button--secondary">Reset</a>
                </div>
            </form>
        </div>

        <div class="users-table-wrap">
            <table class="users-table" style="min-width:1100px;">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Lecturer</th>
                        <th>Date Created</th>
                        <th>Account Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        @php
                            $avPalette = [
                                ['#f5f3ff','#6d28d9'],['#dcfce7','#059669'],
                                ['#fef3c7','#d97706'],['#dbeafe','#1d4ed8'],
                                ['#fce7f3','#be185d'],['#e0f2fe','#0369a1'],
                            ];
                            [$avBg, $avCol] = $avPalette[ord($student->name[0]) % count($avPalette)];
                            $initials = collect(explode(' ', $student->name))->filter()->take(2)->map(fn($w) => strtoupper($w[0]))->implode('');
                        @endphp
                        <tr>
                            <td class="users-table__muted" style="font-weight:600;">{{ $student->display_no }}</td>
                            <td style="color:#6d28d9;font-size:12.5px;font-weight:600;font-family:monospace;">{{ $student->student?->student_id ?? '—' }}</td>
                            <td>
                                <div class="cell-user">
                                    <div class="cell-avatar" style="background:{{ $avBg }};color:{{ $avCol }};overflow:hidden;">
                                @if($student->profile_picture)
                                    <img src="{{ asset('storage/' . $student->profile_picture) }}" alt="{{ $student->name }}" class="w-full h-full object-cover">
                                @else
                                    {{ $initials }}
                                @endif
                            </div>
                                    <div>
                                        <div class="cell-name">{{ $student->name }}</div>
                                        <div class="cell-sub">{{ $student->display_username }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $student->email }}</td>
                            <td>{{ $student->student?->lecturer?->lecturer_name ?? '—' }}</td>
                            <td>{{ $student->created_at->format('d M Y') }}</td>
                            <td>
                                <span class="users-status {{ $student->display_status === 'Active' ? 'users-status--active' : 'users-status--inactive' }}">
                                    {{ $student->display_status }}
                                </span>
                            </td>
                            <td>
                                <div class="cell-actions">
                                    {{-- Edit --}}
                                    <button type="button" class="act-btn a-edit js-edit"
                                        data-tip="Edit account"
                                        data-name="{{ $student->name }}"
                                        data-email="{{ $student->email }}"
                                        data-active="{{ $student->is_active ? '1' : '0' }}"
                                        data-username="{{ $student->display_username }}"
                                        data-url="{{ route('admin.students.update', $student) }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                        </svg>
                                    </button>

                                    {{-- Reset password --}}
                                    <button type="button" class="act-btn a-reset js-reset"
                                        data-tip="Reset password"
                                        data-name="{{ $student->name }}"
                                        data-url="{{ route('admin.students.reset-password', $student) }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                                        </svg>
                                    </button>

                                    {{-- Delete --}}
                                    <form method="POST" action="{{ route('admin.students.destroy', $student) }}" class="js-del-form">
                                        @csrf @method('DELETE')
                                        <button type="button" class="act-btn a-del js-del"
                                            data-tip="Delete account"
                                            data-name="{{ $student->name }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0 1 15.916 21.75H8.084a2.25 2.25 0 0 1-2.245-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0V4.875c0-1.036-.84-1.875-1.875-1.875h-3.75C8.09 3 7.25 3.84 7.25 4.875v.518m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="users-table__empty">No student accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $students->appends(request()->query())->links() }}</div>
    </section>
</div>

{{-- ══════════════════════════════════════════════════ --}}
{{-- ADD MODAL                                         --}}
{{-- ══════════════════════════════════════════════════ --}}
<div id="addModal" class="m-backdrop">
    <div class="m-box" style="max-width:520px;">
        <div class="m-head">
            <div>
                <div class="m-title">Add Student Account</div>
                <div class="m-desc">Create a new student account with login credentials.</div>
            </div>
            <button type="button" class="m-close js-modal-close" data-modal="addModal">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="m-body">
                @if ($errors->any() && old('student_id') !== null)
                    <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:10px 14px;margin-bottom:14px;font-size:12.5px;color:#b91c1c;">
                        <ul style="margin:0;padding-left:16px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="fm-grid">
                    <div class="fm-group fm-full">
                        <label class="fm-label">Student ID</label>
                        <input type="text" name="student_id" class="fm-input" placeholder="e.g. DEMO000001" value="{{ old('student_id') }}" required>
                        <span class="fm-hint">Must match a student in the masterlist who doesn't have an account yet.</span>
                    </div>
                    <div class="fm-group">
                        <label class="fm-label">Account Status</label>
                        <select name="is_active" class="fm-select" required>
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="fm-group">
                        <label class="fm-label">Email Address</label>
                        <input type="email" name="email" class="fm-input" placeholder="email@example.com" value="{{ old('email') }}" required>
                    </div>
                    <div class="fm-group fm-full">
                        <label class="fm-label">Profile Picture</label>
                        <input type="file" name="profile_picture" accept="image/jpeg,image/png,image/jpg,image/webp" class="fm-input">
                    </div>
                </div>

                <div class="fm-divider"></div>
                <div class="fm-section">Login Credentials</div>

                <div class="fm-grid" style="margin-bottom:10px;">
                    <div class="fm-group">
                        <label class="fm-label">Password</label>
                        <div class="pw-wrap">
                            <input type="password" name="password" id="a-pw" class="fm-input" placeholder="Enter password" autocomplete="new-password" required>
                            <button type="button" class="pw-eye" onclick="togglePw('a-pw', this)">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.01 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.01-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="fm-group">
                        <label class="fm-label">Confirm Password</label>
                        <div class="pw-wrap">
                            <input type="password" name="password_confirmation" id="a-pw2" class="fm-input" placeholder="Repeat password" required>
                            <button type="button" class="pw-eye" onclick="togglePw('a-pw2', this)">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.01 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.01-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <p class="fm-hint">Password must be at least 8 characters.</p>
            </div>
            <div class="m-foot">
                <button type="button" class="m-btn m-btn-ghost js-modal-close" data-modal="addModal">Cancel</button>
                <button type="submit" class="m-btn m-btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Add Student
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════ --}}
{{-- EDIT MODAL                                        --}}
{{-- ══════════════════════════════════════════════════ --}}
<div id="editModal" class="m-backdrop">
    <div class="m-box" style="max-width:520px;">
        <div class="m-head">
            <div>
                <div class="m-title">Edit Account</div>
                <div class="m-desc">Update this student's account details.</div>
            </div>
            <button type="button" class="m-close js-modal-close" data-modal="editModal">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="editForm" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="m-body">
                <div class="fm-grid">
                    <div class="fm-group">
                        <label class="fm-label">Full Name</label>
                        <input type="text" name="name" id="e-name" class="fm-input" placeholder="Full name" required>
                    </div>
                    <div class="fm-group">
                        <label class="fm-label">Account Status</label>
                        <select name="is_active" id="e-active" class="fm-select" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="fm-group fm-full">
                        <label class="fm-label">Email Address</label>
                        <input type="email" name="email" id="e-email" class="fm-input" placeholder="email@example.com" required>
                    </div>
                    <div class="fm-group fm-full">
                        <label class="fm-label">Profile Picture</label>
                        <input type="file" name="profile_picture" accept="image/jpeg,image/png,image/jpg,image/webp" class="fm-input">
                    </div>
                    <div class="fm-group fm-full">
                        <label class="fm-label"><input type="checkbox" name="remove_avatar" value="1"> Remove existing avatar</label>
                    </div>
                </div>

                <div class="fm-divider"></div>
                <div class="fm-section">Login Credentials</div>

                <div class="fm-grid" style="margin-bottom:10px;">
                    <div class="fm-group fm-full">
                        <label class="fm-label">Username</label>
                        <input type="text" id="e-username" class="fm-input" readonly>
                    </div>
                    <div class="fm-group">
                        <label class="fm-label">New Password <span style="font-weight:400;color:#94a3b8;text-transform:none;font-size:11px;">(optional)</span></label>
                        <div class="pw-wrap">
                            <input type="password" name="password" id="e-pw" class="fm-input" placeholder="Leave blank to keep current" autocomplete="new-password">
                            <button type="button" class="pw-eye" onclick="togglePw('e-pw', this)">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.01 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.01-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="fm-group">
                        <label class="fm-label">Confirm Password</label>
                        <div class="pw-wrap">
                            <input type="password" name="password_confirmation" id="e-pw2" class="fm-input" placeholder="Repeat password">
                            <button type="button" class="pw-eye" onclick="togglePw('e-pw2', this)">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.01 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.01-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <p class="fm-hint">Password must be at least 8 characters. Leave blank to keep the current password.</p>
            </div>
            <div class="m-foot">
                <button type="button" class="m-btn m-btn-ghost js-modal-close" data-modal="editModal">Cancel</button>
                <button type="submit" class="m-btn m-btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════ --}}
{{-- RESET PASSWORD MODAL                              --}}
{{-- ══════════════════════════════════════════════════ --}}
<div id="resetModal" class="m-backdrop">
    <div class="m-box" style="max-width:440px;">
        <div class="m-head">
            <div>
                <div class="m-title">Reset Password</div>
                <div class="m-desc">Set a new temporary password for this account.</div>
            </div>
            <button type="button" class="m-close js-modal-close" data-modal="resetModal">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="resetForm" method="POST">
            @csrf
            <div class="m-body">
                <div class="reset-info">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                    </svg>
                    <p>The student will need to use this new password on their next login. Make sure to notify them.</p>
                </div>
                <div class="reset-for">
                    Resetting password for: <strong id="r-name-display">—</strong>
                </div>
                <div class="fm-group" style="margin-bottom:10px;">
                    <label class="fm-label">New Temporary Password</label>
                    <div class="pw-wrap">
                        <input type="password" name="password" id="r-pw" class="fm-input" placeholder="Enter new password" autocomplete="new-password" required>
                        <button type="button" class="pw-eye" onclick="togglePw('r-pw', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.01 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.01-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        </button>
                    </div>
                </div>
                <div class="fm-group">
                    <label class="fm-label">Confirm Password</label>
                    <div class="pw-wrap">
                        <input type="password" name="password_confirmation" id="r-pw2" class="fm-input" placeholder="Repeat password" required>
                        <button type="button" class="pw-eye" onclick="togglePw('r-pw2', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.01 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.01-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="m-foot">
                <button type="button" class="m-btn m-btn-ghost js-modal-close" data-modal="resetModal">Cancel</button>
                <button type="submit" class="m-btn m-btn-amber">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z"/></svg>
                    Reset Password
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════ --}}
{{-- DELETE MODAL                                      --}}
{{-- ══════════════════════════════════════════════════ --}}
<div id="deleteModal" class="m-backdrop">
    <div class="m-box" style="max-width:400px;">
        <div style="display:flex;justify-content:flex-end;padding:18px 18px 0;">
            <button type="button" class="m-close js-modal-close" data-modal="deleteModal">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div style="padding:0 28px 10px;text-align:center;">
            <div class="del-icon-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M22 10.5h-6m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/>
                </svg>
            </div>
            <h3 style="font-size:18px;font-weight:700;color:#0f172a;margin-bottom:10px;">Delete Account?</h3>
            <p style="font-size:13px;color:#64748b;line-height:1.65;">
                You are about to permanently delete the account for<br>
                <strong id="d-name-display" style="color:#0f172a;"></strong>.<br>
                This action cannot be undone and will revoke their access immediately.
            </p>
        </div>
        <div style="display:flex;justify-content:center;gap:10px;padding:16px 24px 24px;">
            <form id="deleteForm" method="POST">
                @csrf @method('DELETE')
                <div style="display:flex;gap:10px;">
                    <button type="button" class="m-btn m-btn-ghost js-modal-close" data-modal="deleteModal" style="min-width:110px;">Cancel</button>
                    <button type="submit" class="m-btn m-btn-danger" style="min-width:130px;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0 1 15.916 21.75H8.084a2.25 2.25 0 0 1-2.245-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0V4.875c0-1.036-.84-1.875-1.875-1.875h-3.75C8.09 3 7.25 3.84 7.25 4.875v.518m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                        Yes, Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
@if ($errors->any() && old('student_id') !== null)
openModal('addModal');
@endif

function openModal(id)  { document.getElementById(id).classList.add('is-open'); }
function closeModal(id) { document.getElementById(id).classList.remove('is-open'); }

document.querySelectorAll('.js-modal-close').forEach(btn => {
    btn.addEventListener('click', () => closeModal(btn.dataset.modal));
});
document.querySelectorAll('.m-backdrop').forEach(el => {
    el.addEventListener('click', e => { if (e.target === el) closeModal(el.id); });
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.m-backdrop.is-open').forEach(el => closeModal(el.id));
});

document.querySelectorAll('.js-edit').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('e-name').value     = this.dataset.name;
        document.getElementById('e-email').value    = this.dataset.email;
        document.getElementById('e-active').value   = this.dataset.active;
        document.getElementById('e-username').value = this.dataset.username;
        document.getElementById('e-pw').value       = '';
        document.getElementById('e-pw2').value      = '';
        document.getElementById('editForm').action  = this.dataset.url;
        const fileInput = document.querySelector('#editForm input[name="profile_picture"]');
        const removeCheckbox = document.querySelector('#editForm input[name="remove_avatar"]');
        if (fileInput) fileInput.value = '';
        if (removeCheckbox) removeCheckbox.checked = false;
        openModal('editModal');
    });
});

document.querySelectorAll('.js-reset').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('r-name-display').textContent = this.dataset.name;
        document.getElementById('r-pw').value  = '';
        document.getElementById('r-pw2').value = '';
        document.getElementById('resetForm').action = this.dataset.url;
        openModal('resetModal');
    });
});

document.querySelectorAll('.js-del').forEach(btn => {
    btn.addEventListener('click', function () {
        const form = this.closest('.js-del-form');
        document.getElementById('d-name-display').textContent = this.dataset.name;
        document.getElementById('deleteForm').action = form.action;
        openModal('deleteModal');
    });
});

function togglePw(inputId, btn) {
    const input = document.getElementById(inputId);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    btn.innerHTML = isHidden
        ? `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>`
        : `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.01 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.01-9.964-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>`;
}
</script>
@endpush
