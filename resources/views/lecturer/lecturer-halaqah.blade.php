{{-- resources/views/admin/lecturer-halaqah.blade.php --}}
@extends('layouts.admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">
@endpush

@section('content')
    <div class="w-full px-6 py-10 bg-slate-50 min-h-screen"
         x-data="{
            studentSearch: '', assignSearch: '', selected: [],
            transferOpen: {{ $errors->has('to_lecturer_id') || $errors->has('student_ids') ? 'true' : 'false' }},
            transferTo: @js((string) old('to_lecturer_id', '')),
            allStudentIds: @js($lecturer->students->pluck('student_id')->map(fn ($id) => (string) $id)->values()),
            transferIds: @js($lecturer->students->pluck('student_id')->map(fn ($id) => (string) $id)->values()),
         }">

        {{-- Toast Notification --}}
        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-semibold shadow-sm flex items-center gap-2">
                <span>✅</span> {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm font-semibold shadow-sm">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Header --}}
        <div class="border-b border-gray-200 pb-6 mb-8">
            <a href="{{ route('admin.lecturers', array_filter(['period' => $selectedPeriodId])) }}"
               class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-500 hover:text-violet-700 transition mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                Back to Halaqah List
            </a>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-violet-600 text-white rounded-xl flex items-center justify-center font-black text-xl shadow-md shadow-violet-100 overflow-hidden flex-shrink-0">
                        @if ($lecturer->user?->profile_picture)
                            <img src="{{ asset('storage/' . $lecturer->user->profile_picture) }}"
                                 alt="{{ $lecturer->lecturer_name }}"
                                 class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($lecturer->lecturer_name, 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <h1 class="users-page__title">{{ $lecturer->lecturer_name }}</h1>
                        <div class="users-page__breadcrumb">
                            <span>Main</span>
                            <span>&gt;</span>
                            <a href="{{ route('admin.lecturers', array_filter(['period' => $selectedPeriodId])) }}" class="hover:underline">Halaqah (Lecturer)</a>
                            <span>&gt;</span>
                            <span class="users-page__breadcrumb-accent">{{ $lecturer->lecturer_name }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    @if ($periods->isNotEmpty())
                        <select onchange="window.location.href = this.value"
                            class="px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 focus:outline-none focus:border-violet-500">
                            <option value="{{ route('admin.lecturers.show', $lecturer->lecturer_id) }}" {{ ! $selectedPeriodId ? 'selected' : '' }}>
                                {{ $currentPeriod ? 'Current: ' . $currentPeriod->name : 'Live (no active semester)' }}
                            </option>
                            @foreach ($periods as $p)
                                <option value="{{ route('admin.lecturers.show', ['lecturer_id' => $lecturer->lecturer_id, 'period' => $p->id]) }}"
                                    {{ $selectedPeriodId === $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} ({{ ucfirst($p->status) }})
                                </option>
                            @endforeach
                        </select>
                    @endif

                    @if (! $isHistorical && $lecturer->students->isNotEmpty() && $otherLecturers->isNotEmpty())
                        <button type="button" @click="transferOpen = true; transferIds = [...allStudentIds]"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold rounded-xl shadow-md shadow-violet-100 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                            </svg>
                            Transfer Halaqah
                        </button>
                    @endif

                    @unless ($isHistorical)
                        <form action="{{ route('admin.lecturers.destroy', $lecturer->lecturer_id) }}"
                            method="POST"
                            onsubmit="return confirm('Are you sure you want to completely remove this lecturer? All assigned students will be set back to unassigned status.');">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="return" value="halaqah">
                            <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-red-50 text-red-500 border border-red-100 text-sm font-bold rounded-xl transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete Lecturer
                            </button>
                        </form>
                    @endunless
                </div>
            </div>
        </div>

        @if ($isHistorical)
            <div class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm font-semibold shadow-sm flex items-center gap-2">
                <span>🔒</span>
                Viewing historical Halaqah data for <span class="font-black">{{ $viewingPeriod->name }}</span> — read only. Switch to the current semester to make changes.
            </div>
        @endif

        <div class="grid grid-cols-1 {{ $isHistorical ? '' : 'xl:grid-cols-5' }} gap-6 items-start">

            {{-- Students in this halaqah --}}
            <div class="{{ $isHistorical ? '' : 'xl:col-span-3' }} bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-5 bg-slate-50/60 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <h2 class="text-base font-extrabold text-gray-800">Students in this Halaqah</h2>
                        <span class="text-xs font-black bg-violet-50 text-violet-700 px-3 py-1 rounded-lg border border-violet-100">
                            {{ $lecturer->students->count() }}
                        </span>
                    </div>
                    @if ($lecturer->students->isNotEmpty())
                        <input type="search" x-model="studentSearch" placeholder="Search name or ID"
                            class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-100 transition" />
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50/30 text-[10px] text-gray-400 font-black uppercase tracking-wider border-b border-gray-50">
                            <tr>
                                <th class="px-6 py-4">Student Name</th>
                                <th class="px-6 py-4">Student ID</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                @unless ($isHistorical)
                                    <th class="px-6 py-4 text-right">Actions</th>
                                @endunless
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($lecturer->students as $student)
                                <tr class="hover:bg-slate-50/40 transition"
                                    x-show="studentSearch === '' || $el.dataset.studentName.includes(studentSearch.toLowerCase()) || $el.dataset.studentId.includes(studentSearch.toLowerCase())"
                                    data-student-name="{{ strtolower($student->student_name) }}"
                                    data-student-id="{{ strtolower($student->student_id) }}">
                                    <td class="px-6 py-4 font-bold text-gray-700">{{ $student->student_name }}</td>
                                    <td class="px-6 py-4 font-mono text-xs font-bold text-gray-500">{{ $student->student_id }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-block px-2.5 py-1 text-[10px] font-black rounded-md tracking-wider uppercase bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            {{ $student->status }}
                                        </span>
                                    </td>
                                    @unless ($isHistorical)
                                        <td class="px-6 py-4 text-right">
                                            <form action="{{ route('admin.lecturers.removeStudent', $student->student_id) }}"
                                                method="POST"
                                                onsubmit="return confirm('Are you sure you want to remove this student from this advisor?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-bold text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                                                    Remove
                                                </button>
                                            </form>
                                        </td>
                                    @endunless
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-xs text-gray-400 italic bg-slate-50/20">
                                        No students recorded for this lecturer{{ $isHistorical ? ' in this semester.' : ' yet. Use the panel to assign students.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Assign panel: only students not yet in any halaqah --}}
            @unless ($isHistorical)
                <div class="xl:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden xl:sticky xl:top-6">
                    <div class="px-6 py-5 bg-slate-50/60 border-b border-gray-100">
                        <h2 class="text-base font-extrabold text-gray-800">Assign Students</h2>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Only active students not yet in any halaqah are listed ({{ $unassignedStudents->count() }}).
                        </p>
                    </div>

                    @if ($unassignedStudents->isEmpty())
                        <div class="p-12 text-center text-xs text-gray-400 italic">
                            🎉 All active students have been assigned to a halaqah.
                        </div>
                    @else
                        <form action="{{ route('admin.lecturers.assignStudent') }}" method="POST">
                            @csrf
                            <input type="hidden" name="lecturer_id" value="{{ $lecturer->lecturer_id }}">

                            <div class="px-6 py-4 border-b border-gray-100">
                                <input type="search" x-model="assignSearch" placeholder="Search by student name or ID"
                                    class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-100 transition" />
                            </div>

                            <div class="max-h-[28rem] overflow-y-auto divide-y divide-gray-100">
                                @foreach ($unassignedStudents as $unassigned)
                                    <label class="flex items-center gap-3 px-6 py-3 hover:bg-slate-50/60 cursor-pointer transition"
                                        x-show="assignSearch === '' || $el.dataset.studentName.includes(assignSearch.toLowerCase()) || $el.dataset.studentId.includes(assignSearch.toLowerCase())"
                                        data-student-name="{{ strtolower($unassigned->student_name) }}"
                                        data-student-id="{{ strtolower($unassigned->student_id) }}">
                                        <input type="checkbox" name="student_ids[]" value="{{ $unassigned->student_id }}" x-model="selected"
                                            class="w-4 h-4 rounded border-gray-300 text-violet-600 focus:ring-violet-500">
                                        <div class="min-w-0">
                                            <div class="font-bold text-sm text-gray-700 truncate">{{ $unassigned->student_name }}</div>
                                            <div class="text-xs font-mono font-semibold text-gray-400 mt-0.5">{{ $unassigned->student_id }}</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <div class="px-6 py-4 bg-slate-50 border-t border-gray-100 flex items-center justify-between gap-3">
                                <span class="text-xs font-semibold text-gray-500">
                                    <span x-text="selected.length"></span> selected
                                </span>
                                <div class="flex items-center gap-2">
                                    <button type="button" x-show="selected.length" @click="selected = []"
                                        class="px-3 py-2 bg-white border border-gray-200 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-50 transition">
                                        Clear
                                    </button>
                                    <button type="submit" :disabled="! selected.length"
                                        class="px-4 py-2 bg-violet-600 text-white text-xs font-black rounded-xl hover:bg-violet-700 transition shadow-md shadow-violet-100 disabled:opacity-40 disabled:cursor-not-allowed">
                                        Assign to {{ \Illuminate\Support\Str::limit($lecturer->lecturer_name, 20) }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            @endunless
        </div>

        {{-- MODAL: Transfer Halaqah to another lecturer --}}
        @if (! $isHistorical && $lecturer->students->isNotEmpty() && $otherLecturers->isNotEmpty())
            <div x-show="transferOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak
                 @keydown.escape.window="transferOpen = false">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="transferOpen = false"></div>
                <div class="flex min-h-full items-end justify-center p-4 sm:items-center">
                    <div class="relative w-full sm:max-w-xl overflow-hidden rounded-2xl bg-white text-left shadow-2xl border border-gray-100">
                        <form action="{{ route('admin.lecturers.transfer', $lecturer->lecturer_id) }}" method="POST"
                              @submit="if (! confirm('Transfer ' + transferIds.length + ' student(s) to ' + ($refs.toSelect.selectedOptions[0]?.dataset.name || 'the selected lecturer') + '?')) $event.preventDefault()">
                            @csrf

                            <div class="px-6 py-5 bg-slate-50 border-b border-gray-100 flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-base font-black text-gray-800">Transfer Halaqah</h3>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        Move students from <span class="font-bold text-violet-600">{{ $lecturer->lecturer_name }}</span> to another lecturer.
                                    </p>
                                </div>
                                <button type="button" @click="transferOpen = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">×</button>
                            </div>

                            <div class="p-6 space-y-5">
                                @if ($errors->has('to_lecturer_id') || $errors->has('student_ids'))
                                    <div class="p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs font-semibold">
                                        {{ $errors->first('to_lecturer_id') ?: $errors->first('student_ids') }}
                                    </div>
                                @endif

                                <div>
                                    <label for="to_lecturer_id" class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Transfer to</label>
                                    <select id="to_lecturer_id" name="to_lecturer_id" x-model="transferTo" x-ref="toSelect" required
                                        class="w-full px-4 py-3 bg-slate-50 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700 focus:outline-none focus:border-violet-500 focus:bg-white transition">
                                        <option value="">Select a lecturer…</option>
                                        @foreach ($otherLecturers as $other)
                                            <option value="{{ $other->lecturer_id }}" data-name="{{ $other->lecturer_name }}">
                                                {{ $other->lecturer_name }} — {{ $other->students_count }} {{ $other->students_count === 1 ? 'student' : 'students' }}{{ $other->user && ! $other->user->is_active ? ' (inactive account)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-black text-gray-400 uppercase tracking-wider">
                                            Students (<span x-text="transferIds.length"></span>/{{ $lecturer->students->count() }})
                                        </span>
                                        <button type="button" class="text-xs font-bold text-violet-600 hover:text-violet-800"
                                            @click="transferIds = transferIds.length === allStudentIds.length ? [] : [...allStudentIds]"
                                            x-text="transferIds.length === allStudentIds.length ? 'Deselect all' : 'Select all'"></button>
                                    </div>
                                    <div class="max-h-60 overflow-y-auto border border-gray-100 rounded-xl divide-y divide-gray-100">
                                        @foreach ($lecturer->students as $student)
                                            <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 cursor-pointer">
                                                <input type="checkbox" name="student_ids[]" value="{{ $student->student_id }}" x-model="transferIds"
                                                    class="w-4 h-4 rounded border-gray-300 text-violet-600 focus:ring-violet-500">
                                                <span class="text-sm font-bold text-gray-700 truncate">{{ $student->student_name }}</span>
                                                <span class="ml-auto text-xs font-mono text-gray-400">{{ $student->student_id }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <p class="text-xs text-gray-500 bg-violet-50/60 border border-violet-100 rounded-xl p-3 leading-relaxed">
                                    Past hafazan, attendance and notes stay as they are and will be visible to the new lecturer.
                                    Pending excuse letters for these students move to the new lecturer for review.
                                </p>
                            </div>

                            <div class="px-6 py-4 bg-slate-50 border-t border-gray-100 flex items-center justify-end gap-3">
                                <button type="button" @click="transferOpen = false"
                                    class="px-4 py-2 bg-white border border-gray-200 text-gray-600 text-xs font-bold rounded-xl hover:bg-gray-50 transition">
                                    Cancel
                                </button>
                                <button type="submit" :disabled="! transferTo || ! transferIds.length"
                                    class="px-4 py-2 bg-violet-600 text-white text-xs font-black rounded-xl hover:bg-violet-700 transition shadow-md shadow-violet-100 disabled:opacity-40 disabled:cursor-not-allowed">
                                    Transfer <span x-text="transferIds.length"></span> student(s)
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
