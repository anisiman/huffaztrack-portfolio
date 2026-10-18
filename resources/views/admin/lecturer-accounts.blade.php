{{-- resources/views/admin/lecturer-accounts.blade.php --}}
@extends('layouts.admin')

@section('title', 'Lecturer Accounts')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">
    <style>
        .users-page { padding: 1.5rem; max-width: 1600px; margin: 0 auto; }

        .cell-user { display: flex; align-items: center; gap: 10px; }
        .cell-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700; flex-shrink: 0;
        }
        .cell-name { font-weight: 600; font-size: 13.5px; color: #1e293b; line-height: 1.2; }
        .cell-sub  { font-size: 11.5px; color: #94a3b8; margin-top: 1px; }

        .cell-actions { display: flex; align-items: center; gap: 4px; justify-content: flex-end; }
        .act-btn {
            width: 30px; height: 30px; border-radius: 8px; border: 1px solid #e2e8f0;
            background: transparent; display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: #64748b; transition: all 0.13s; position: relative;
        }
        .act-btn svg { width: 14px; height: 14px; }
        .act-btn:hover { border-color: #cbd5e1; }
        .act-btn.a-edit:hover { background: #f5f3ff; color: #7c3aed; border-color: #c4b5fd; }
        .act-btn.a-del:hover  { background: #fef2f2; color: #ef4444; border-color: #fca5a5; }
        .act-btn::after {
            content: attr(data-tip); position: absolute; bottom: calc(100% + 6px);
            left: 50%; transform: translateX(-50%); background: #1e293b; color: #fff;
            font-size: 11px; padding: 3px 8px; border-radius: 5px; white-space: nowrap;
            pointer-events: none; opacity: 0; transition: opacity 0.15s;
        }
        .act-btn:hover::after { opacity: 1; }
    </style>
@endpush

@section('content')
    <div class="w-full px-6 py-10 bg-slate-50 min-h-screen">

        {{-- Toast Notification --}}
        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-semibold shadow-sm flex items-center gap-2">
                <span>✅</span> {{ session('success') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-6 mb-8 gap-4">
            <div>
                <h1 class="users-page__title">Lecturer Accounts</h1>
                <p class="text-sm text-slate-400 mt-0.5">Manage lecturer login credentials and account status.</p>
                <div class="users-page__breadcrumb">
                    <span>User Management</span>
                    <span>&gt;</span>
                    <span class="users-page__breadcrumb-accent">Lecturer Accounts</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" id="openAddLecturerModal" class="users-button users-button--primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3M15 6.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0" />
                    </svg>
                    <span>Add Account</span>
                </button>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="users-stats users-stats--three">
            <article class="users-stat-card users-stat-card--violet">
                <div class="users-stat-card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.742-.479 3 3 0 0 0-4.682-2.72m.94 3.198-.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.204-.576-5.962-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a5.971 5.971 0 0 0-.94 3.197m0 0a9.093 9.093 0 0 1-3.743-.479 3 3 0 0 1 4.682-2.72m.001.031A5.994 5.994 0 0 1 12 12.75a5.994 5.994 0 0 1 5.058 2.772M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                </div>
                <p class="users-stat-card__label">Total Lecturer Accounts</p>
                <p class="users-stat-card__value">{{ $totalLecturers }} Lecturers</p>
            </article>

            <article class="users-stat-card users-stat-card--emerald">
                <div class="users-stat-card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.552-.645-6.452-1.766v-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
                <p class="users-stat-card__label">Active Accounts</p>
                <p class="users-stat-card__value">{{ $permanentLecturers }} Lecturers</p>
            </article>

            <article class="users-stat-card users-stat-card--amber">
                <div class="users-stat-card__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.742-.479 3 3 0 0 0-4.682-2.72m.94 3.198-.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.204-.576-5.962-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a5.971 5.971 0 0 0-.94 3.197m0 0a9.093 9.093 0 0 1-3.743-.479 3 3 0 0 1 4.682-2.72m.001.031A5.994 5.994 0 0 1 12 12.75a5.994 5.994 0 0 1 5.058 2.772M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                </div>
                <p class="users-stat-card__label">Inactive Accounts</p>
                <p class="users-stat-card__value">{{ $partTimeLecturers }} Lecturers</p>
            </article>
        </div>

        {{-- Account Management Table --}}
        <section class="users-panel" style="margin-top: 2rem;">
            <div class="users-panel__header">
                <h2 class="users-panel__title">Account Management</h2>

                <form method="GET" action="{{ route('admin.lecturerAccounts') }}" class="users-panel__tools">
                    <label class="users-search">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search lecturer..">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m0 0A7.65 7.65 0 1 0 5.825 5.825a7.65 7.65 0 0 0 10.825 10.825Z" />
                        </svg>
                    </label>

                    <select name="status" class="users-filter users-filter--select users-filter--caret">
                        <option value="">Account Status</option>
                        <option value="Active" {{ ($status ?? '') == 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ ($status ?? '') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    <button type="submit" class="users-button users-button--secondary">Apply</button>
                </form>
            </div>

            <div class="users-table-wrap">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Date Created</th>
                            <th>Account Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lecturers as $lecturer)
                            @php
                                $avPalette = [
                                    ['#f5f3ff','#6d28d9'],['#dcfce7','#059669'],
                                    ['#fef3c7','#d97706'],['#dbeafe','#1d4ed8'],
                                    ['#fce7f3','#be185d'],['#e0f2fe','#0369a1'],
                                ];
                                [$avBg, $avCol] = $avPalette[ord($lecturer->name[0]) % count($avPalette)];
                                $initials = collect(explode(' ', $lecturer->name))->filter()->take(2)->map(fn($w) => strtoupper($w[0]))->implode('');
                            @endphp
                            <tr>
                                <td class="users-table__muted" style="font-weight:600;">{{ $lecturer->display_no }}</td>
                                <td>
                                    <div class="cell-user">
                                        <div class="cell-avatar" style="background:{{ $avBg }};color:{{ $avCol }};overflow:hidden;">
                                    @if($lecturer->profile_picture)
                                        <img src="{{ asset('storage/' . $lecturer->profile_picture) }}" alt="{{ $lecturer->name }}" class="w-full h-full object-cover">
                                    @else
                                        {{ $initials }}
                                    @endif
                                </div>
                                        <div>
                                            <div class="cell-name">{{ $lecturer->name }}</div>
                                            <div class="cell-sub">{{ $lecturer->display_username }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="users-table__muted">{{ $lecturer->email }}</td>
                                <td class="users-table__muted">{{ $lecturer->created_at->format('d M Y') }}</td>
                                <td>
                                    <span class="users-status {{ $lecturer->display_status === 'Active' ? 'users-status--active' : 'users-status--inactive' }}">
                                        {{ $lecturer->display_status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="cell-actions">
                                        <button type="button"
                                            class="act-btn a-edit js-edit-trigger"
                                            data-tip="Edit account"
                                            data-url="{{ route('admin.lecturers.update', $lecturer) }}"
                                            data-profile="{{ $lecturer->lecturerProfile?->lecturer_id ?? '' }}"
                                            data-email="{{ $lecturer->email }}"
                                            data-active="{{ $lecturer->is_active ? '1' : '0' }}"
                                            data-id="{{ $lecturer->id }}"
                                            data-name="{{ $lecturer->name }}"
                                            data-student-count="{{ $lecturer->lecturerProfile?->students_count ?? 0 }}"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931ZM19.5 7.125 16.875 4.5" />
                                            </svg>
                                        </button>

                                        @if ($lecturer->lecturerProfile)
                                        <form action="{{ route('admin.lecturers.destroy', $lecturer->lecturerProfile->lecturer_id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                class="act-btn a-del js-delete-trigger"
                                                data-tip="Delete account"
                                                data-delete-name="{{ $lecturer->name }}"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                        @else
                                        <form action="{{ route('admin.lecturers.destroyAccount', $lecturer->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                class="act-btn a-del js-delete-trigger"
                                                data-tip="Delete account"
                                                data-delete-name="{{ $lecturer->name }}"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="users-table__empty">No lecturer accounts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3">
                {{ $lecturers->appends(request()->query())->links() }}
            </div>
        </section>

    </div>

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Add Lecturer Account                                                 --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
<div id="addLecturerModal" class="users-modal" hidden>
    <div class="users-modal__backdrop"></div>
    <div class="users-modal__dialog users-modal__dialog--wide" role="dialog" aria-modal="true" aria-labelledby="addLecturerModalTitle">
        <h2 id="addLecturerModalTitle" class="users-modal__title">Add Lecturer Account</h2>

        @if ($errors->any() && old('_form') === 'create')
            <div class="users-modal__alert">
                <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.lecturers.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_form" value="create">
            <div class="users-modal__body">
                <div class="users-modal__field">
                    <label class="users-modal__label" for="add_profile">Lecturer Profile</label>
                    <select id="add_profile" name="lecturer_profile_id" class="users-modal__select" required>
                        <option value="">— select profile —</option>
                        @foreach ($lecturerProfiles as $profile)
                            <option value="{{ $profile->lecturer_id }}"
                                {{ old('lecturer_profile_id') == $profile->lecturer_id ? 'selected' : '' }}
                                {{ $profile->user_id ? 'disabled' : '' }}>
                                {{ $profile->lecturer_name }}{{ $profile->user_id ? ' (already linked)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="users-modal__field">
                    <label class="users-modal__label" for="add_email">Email</label>
                    <input id="add_email" type="email" name="email" class="users-modal__input"
                        value="{{ old('email') }}" required autocomplete="off">
                </div>
                <div class="users-modal__field">
                    <label class="users-modal__label" for="add_status">Status</label>
                    <select id="add_status" name="is_active" class="users-modal__select" required>
                        <option value="1" {{ old('is_active', '1') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="users-modal__field">
                    <label class="users-modal__label" for="add_password">Password</label>
                    <input id="add_password" type="password" name="password" class="users-modal__input"
                        required minlength="8" autocomplete="new-password">
                </div>
                <div class="users-modal__field">
                    <label class="users-modal__label" for="add_password_confirmation">Confirm Password</label>
                    <input id="add_password_confirmation" type="password" name="password_confirmation"
                        class="users-modal__input" required minlength="8" autocomplete="new-password">
                </div>
                <div class="users-modal__field">
                    <label class="users-modal__label" for="add_profile_picture">Profile Picture</label>
                    <input id="add_profile_picture" type="file" name="profile_picture" class="users-modal__input" accept="image/jpeg,image/png,image/jpg,image/webp">
                </div>
            </div>
            <div class="users-modal__actions">
                <button type="button" class="users-button users-button--secondary" id="addLecturerCancel">Cancel</button>
                <button type="submit" class="users-button users-button--primary">Create Account</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Edit Lecturer Account                                                --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
<div id="editLecturerModal" class="users-modal" hidden>
    <div class="users-modal__backdrop"></div>
    <div class="users-modal__dialog users-modal__dialog--wide" role="dialog" aria-modal="true" aria-labelledby="editLecturerModalTitle">
        <h2 id="editLecturerModalTitle" class="users-modal__title">Edit Lecturer Account</h2>

        @if ($errors->any() && old('_form') === 'edit')
            <div class="users-modal__alert">
                <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form id="editLecturerForm" method="POST" action="" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="_form" value="edit">
            <input type="hidden" id="edit_id_field" name="edit_id" value="">
            <div class="users-modal__body">
                <div class="users-modal__field">
                    <label class="users-modal__label" for="edit_profile">Lecturer Profile</label>
                    <select id="edit_profile" name="lecturer_profile_id" class="users-modal__select" required>
                        <option value="">— select profile —</option>
                        @foreach ($lecturerProfiles as $profile)
                            <option value="{{ $profile->lecturer_id }}"
                                {{ old('lecturer_profile_id') == $profile->lecturer_id ? 'selected' : '' }}>
                                {{ $profile->lecturer_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="users-modal__field">
                    <label class="users-modal__label" for="edit_email">Email</label>
                    <input id="edit_email" type="email" name="email" class="users-modal__input"
                        value="{{ old('email') }}" required autocomplete="off">
                </div>
                <div class="users-modal__field">
                    <label class="users-modal__label" for="edit_status">Status</label>
                    <select id="edit_status" name="is_active" class="users-modal__select" required>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="users-modal__field">
                    <label class="users-modal__label" for="edit_password">
                        New Password <span class="users-modal__hint">(leave blank to keep current)</span>
                    </label>
                    <input id="edit_password" type="password" name="password" class="users-modal__input"
                        minlength="8" autocomplete="new-password">
                </div>
                <div class="users-modal__field">
                    <label class="users-modal__label" for="edit_password_confirmation">Confirm New Password</label>
                    <input id="edit_password_confirmation" type="password" name="password_confirmation"
                        class="users-modal__input" minlength="8" autocomplete="new-password">
                </div>
                <div class="users-modal__field">
                    <label class="users-modal__label" for="edit_profile_picture">Profile Picture</label>
                    <input id="edit_profile_picture" type="file" name="profile_picture" class="users-modal__input" accept="image/jpeg,image/png,image/jpg,image/webp">
                </div>
                <div class="users-modal__field">
                    <label class="users-modal__label"><input type="checkbox" name="remove_avatar" value="1"> Remove existing avatar</label>
                </div>
            </div>
            <div class="users-modal__actions">
                <button type="button" class="users-button users-button--secondary" id="editLecturerCancel">Cancel</button>
                <button type="submit" class="users-button users-button--primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Delete Confirmation                                                  --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
<div id="deleteLecturerModal" class="users-modal" hidden>
    <div class="users-modal__backdrop"></div>
    <div class="users-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="deleteLecturerModalTitle">
        <h2 id="deleteLecturerModalTitle" class="users-modal__title">Delete Lecturer</h2>
        <p class="users-modal__text">Are you sure you want to delete this lecturer? Assigned students will be released.</p>
        <p id="deleteLecturerModalName" class="users-modal__name"></p>
        <div class="users-modal__actions">
            <button type="button" class="users-button users-button--secondary" id="deleteLecturerCancel">Cancel</button>
            <button type="button" class="users-button users-button--primary" id="deleteLecturerConfirm">OK</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function openModal(modal) { modal.hidden = false; }
    function closeModal(modal) { modal.hidden = true; }

    function trapClose(modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal || e.target.classList.contains('users-modal__backdrop')) {
                closeModal(modal);
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.users-modal:not([hidden])').forEach(closeModal);
        }
    });

    // ── Delete modal ────────────────────────────────────────────────────────
    (function () {
        const modal      = document.getElementById('deleteLecturerModal');
        const modalName  = document.getElementById('deleteLecturerModalName');
        const cancelBtn  = document.getElementById('deleteLecturerCancel');
        const confirmBtn = document.getElementById('deleteLecturerConfirm');
        const triggers   = document.querySelectorAll('.js-delete-trigger');
        if (!modal) return;
        let activeForm = null;

        triggers.forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                activeForm = trigger.closest('form');
                modalName.textContent = trigger.dataset.deleteName ? 'Lecturer: ' + trigger.dataset.deleteName : '';
                openModal(modal);
            });
        });

        cancelBtn.addEventListener('click', function () { closeModal(modal); activeForm = null; });
        confirmBtn.addEventListener('click', function () { if (activeForm) activeForm.submit(); });
        trapClose(modal);
    }());

    // ── Add Account modal ───────────────────────────────────────────────────
    (function () {
        const modal     = document.getElementById('addLecturerModal');
        const openBtn   = document.getElementById('openAddLecturerModal');
        const cancelBtn = document.getElementById('addLecturerCancel');
        if (!modal) return;

        openBtn.addEventListener('click', function () { openModal(modal); });
        cancelBtn.addEventListener('click', function () { closeModal(modal); });
        trapClose(modal);

        if ({{ $errors->any() && old('_form') === 'create' ? 'true' : 'false' }}) {
            openModal(modal);
        }
    }());

    // ── Edit Account modal ─────────────────────────────────────────────────
    (function () {
        const modal     = document.getElementById('editLecturerModal');
        const form      = document.getElementById('editLecturerForm');
        const cancelBtn = document.getElementById('editLecturerCancel');
        const triggers  = document.querySelectorAll('.js-edit-trigger');
        if (!modal) return;

        const profileSelect = document.getElementById('edit_profile');
        const emailInput    = document.getElementById('edit_email');
        const statusSelect  = document.getElementById('edit_status');
        const editIdField   = document.getElementById('edit_id_field');

        let currentWasActive   = false;
        let currentStudentCount = 0;

        triggers.forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                form.action       = trigger.dataset.url;
                editIdField.value = trigger.dataset.id;

                currentWasActive    = trigger.dataset.active === '1';
                currentStudentCount = parseInt(trigger.dataset.studentCount || '0', 10);

                @if (!($errors->any() && old('_form') === 'edit'))
                profileSelect.value = trigger.dataset.profile;
                emailInput.value    = trigger.dataset.email;
                statusSelect.value  = trigger.dataset.active;
                @endif

                const fileInput = form.querySelector('input[name="profile_picture"]');
                const removeCheckbox = form.querySelector('input[name="remove_avatar"]');
                if (fileInput) fileInput.value = '';
                if (removeCheckbox) removeCheckbox.checked = false;

                openModal(modal);
            });
        });

        form.addEventListener('submit', function (e) {
            const nowInactive = statusSelect.value === '0';
            if (currentWasActive && nowInactive && currentStudentCount > 0) {
                const plural = currentStudentCount === 1 ? 'student' : 'students';
                const ok = confirm(
                    'Deactivating this lecturer will remove them and release ' +
                    currentStudentCount + ' assigned ' + plural + ' from their halaqah. Continue?'
                );
                if (!ok) {
                    e.preventDefault();
                }
            }
        });

        cancelBtn.addEventListener('click', function () { closeModal(modal); });
        trapClose(modal);

        @if ($errors->any() && old('_form') === 'edit')
        (function () {
            const editId = {{ (int) old('edit_id', 0) }};
            if (editId) {
                const trigger = document.querySelector('.js-edit-trigger[data-id="{{ (int) old("edit_id", 0) }}"]');
                if (trigger) form.action = trigger.dataset.url;
            }
            openModal(modal);
        }());
        @endif
    }());
});
</script>
@endpush
