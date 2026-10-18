{{-- resources/views/admin/lecturers.blade.php --}}
@extends('layouts.admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">
    <style>
        .users-page { padding: 1.5rem; max-width: 1600px; margin: 0 auto; }
    </style>
@endpush

@section('content')
    <div class="w-full px-6 py-10 bg-slate-50 min-h-screen"
         x-data="{ addLecturerModalOpen: false, lecturerSearch: '' }">

        {{-- Toast Notification --}}
        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-semibold shadow-sm flex items-center gap-2">
                <span>✅</span> {{ session('success') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-6 mb-8 gap-4">
            <div>
                <h1 class="users-page__title">Halaqah Allocation</h1>
                <p class="text-sm text-gray-500 mt-1">Manage lecturer profiles and student assignments.</p>
                <div class="users-page__breadcrumb">
                    <span>Main</span>
                    <span>&gt;</span>
                    <span class="users-page__breadcrumb-accent">Halaqah (Lecturer)</span>
                </div>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                @if ($periods->isNotEmpty())
                    <select onchange="window.location.href = this.value"
                        class="px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 focus:outline-none focus:border-violet-500">
                        <option value="{{ route('admin.lecturers') }}" {{ ! $selectedPeriodId ? 'selected' : '' }}>
                            {{ $currentPeriod ? 'Current: ' . $currentPeriod->name : 'Live (no active semester)' }}
                        </option>
                        @foreach ($periods as $p)
                            <option value="{{ route('admin.lecturers', ['period' => $p->id]) }}"
                                {{ $selectedPeriodId === $p->id ? 'selected' : '' }}>
                                {{ $p->name }} ({{ ucfirst($p->status) }})
                            </option>
                        @endforeach
                    </select>
                @endif

                @unless ($isHistorical)
                    <button type="button" @click="addLecturerModalOpen = true"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold rounded-xl shadow-md shadow-violet-100 transition whitespace-nowrap">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Add Lecturer
                    </button>
                @endunless
            </div>
        </div>

        @if ($isHistorical)
            <div class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm font-semibold shadow-sm flex items-center gap-2">
                <span>🔒</span>
                Viewing historical Halaqah data for <span class="font-black">{{ $viewingPeriod->name }}</span> — read only. Switch to the current semester to make changes.
            </div>
        @endif

        {{-- Halaqah list: one card per lecturer, each opens its own halaqah page --}}
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-800">Halaqah List</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Click a lecturer to view and manage their students.</p>
                </div>
                <div class="flex items-center gap-3">
                    @unless ($isHistorical)
                        <span class="text-xs font-black bg-amber-50 text-amber-700 px-3 py-1.5 rounded-lg border border-amber-100">
                            Unassigned active students: {{ $unassignedCount }}
                        </span>
                    @endunless
                    @if ($lecturerProfiles->isNotEmpty())
                        <input type="search" x-model="lecturerSearch" placeholder="Search lecturer"
                            class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-100 transition" />
                    @endif
                </div>
            </div>

            @if ($lecturerProfiles->isEmpty())
                <div class="bg-white rounded-2xl border border-dashed border-gray-200 p-12 text-center">
                    <span class="text-3xl">📭</span>
                    <h4 class="text-sm font-bold text-gray-700 mt-2">No Lecturers Found</h4>
                    <p class="text-xs text-gray-400 mt-1">Click the "Add Lecturer" button above to register your first record.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach ($lecturerProfiles as $lecturer)
                        <a href="{{ route('admin.lecturers.show', array_filter(['lecturer_id' => $lecturer->lecturer_id, 'period' => $selectedPeriodId])) }}"
                           x-show="lecturerSearch === '' || $el.dataset.name.includes(lecturerSearch.toLowerCase())"
                           data-name="{{ strtolower($lecturer->lecturer_name) }}"
                           class="group bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-4 hover:border-violet-200 hover:shadow-md transition">
                            <div class="w-12 h-12 bg-violet-600 text-white rounded-xl flex items-center justify-center font-black text-lg shadow-md shadow-violet-100 overflow-hidden flex-shrink-0">
                                @if ($lecturer->user?->profile_picture)
                                    <img src="{{ asset('storage/' . $lecturer->user->profile_picture) }}"
                                         alt="{{ $lecturer->lecturer_name }}"
                                         class="w-full h-full object-cover">
                                @else
                                    {{ strtoupper(substr($lecturer->lecturer_name, 0, 1)) }}
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-sm font-extrabold text-gray-800 truncate group-hover:text-violet-700 transition">{{ $lecturer->lecturer_name }}</h3>
                                <p class="text-xs font-semibold text-gray-400 mt-1">
                                    {{ $lecturer->students->count() }} {{ $lecturer->students->count() === 1 ? 'student' : 'students' }}
                                </p>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-300 group-hover:text-violet-600 transition flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- MODAL: Add Lecturer Profile --}}
        <div x-show="addLecturerModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="addLecturerModalOpen = false"></div>
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100">

                    <div class="px-6 py-5 bg-slate-50 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-black text-gray-800">Register New Lecturer</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Creates a distinct profile entity key inside system tracking.</p>
                        </div>
                        <button type="button" @click="addLecturerModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">×</button>
                    </div>

                    <form action="{{ route('admin.lecturers.storeProfile') }}" method="POST">
                        @csrf
                        <div class="p-6 space-y-4">
                            <div>
                                <label for="lecturer_name" class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Lecturer Full Name</label>
                                <input type="text"
                                       name="lecturer_name"
                                       id="lecturer_name"
                                       required
                                       placeholder="e.g. DR. DEMO LECTURER"
                                       class="w-full px-4 py-3 bg-slate-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 placeholder-gray-400 focus:outline-none focus:border-violet-500 focus:bg-white transition" />
                            </div>
                        </div>

                        <div class="px-6 py-4 bg-slate-50 border-t border-gray-100 flex items-center justify-end gap-3">
                            <button type="button" @click="addLecturerModalOpen = false" class="px-4 py-2 bg-white border border-gray-200 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-50 transition">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-violet-600 text-white text-xs font-black rounded-xl hover:bg-violet-700 transition shadow-md shadow-violet-100">
                                Save Lecturer
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>


    </div>
@endsection
