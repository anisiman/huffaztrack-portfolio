@extends('layouts.admin')

@section('title', $subject->subjectT_name . ' — Sections')

@section('content')
<div class="max-w-6xl mx-auto" x-data="{ showCreate: false }">

    <a href="{{ route('admin.sections', ['academic_period_id' => $period->id]) }}"
        class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-violet-600 mb-4">
        &larr; Back to Subject Sections
    </a>

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

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">{{ $subject->subjectT_name }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $subject->subjectT_code }} &middot; {{ $period->name }}</p>
        </div>
        <button type="button" @click="showCreate = true"
            class="px-4 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
            + Add Section
        </button>
    </div>

    {{-- Sections --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-8">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3 text-left font-bold text-slate-500 uppercase text-[11px] tracking-wider">Section</th>
                    <th class="px-5 py-3 text-left font-bold text-slate-500 uppercase text-[11px] tracking-wider">Lecturer</th>
                    <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Capacity</th>
                    <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Enrolled</th>
                    <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Remaining</th>
                    <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sections as $section)
                    <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                        <form action="{{ route('admin.sections.update', ['subject' => $subject->subjectT_id, 'section' => $section->id]) }}" method="POST" id="section-form-{{ $section->id }}">
                            @csrf
                            @method('PUT')
                        </form>
                        <td class="px-5 py-3">
                            <input type="text" name="section_name" value="{{ $section->section_name }}" required form="section-form-{{ $section->id }}"
                                class="w-28 rounded-lg border border-slate-200 px-2.5 py-1.5 text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-violet-500 focus:outline-none">
                        </td>
                        <td class="px-5 py-3">
                            <select name="lecturer_id" form="section-form-{{ $section->id }}"
                                class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-violet-500 focus:outline-none">
                                <option value="">— Unassigned —</option>
                                @foreach ($lecturers as $lecturer)
                                    <option value="{{ $lecturer->lecturer_id }}" {{ $section->lecturer_id == $lecturer->lecturer_id ? 'selected' : '' }}>
                                        {{ $lecturer->lecturer_name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <input type="number" min="1" name="capacity" value="{{ $section->capacity }}" required form="section-form-{{ $section->id }}"
                                class="w-16 text-center rounded-lg border border-slate-200 px-2 py-1.5 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                        </td>
                        <td class="px-5 py-3 text-center text-slate-600">{{ $section->enrollments_count }}</td>
                        <td class="px-5 py-3 text-center font-semibold {{ $section->capacity - $section->enrollments_count <= 0 ? 'text-red-500' : 'text-emerald-600' }}">
                            {{ max(0, $section->capacity - $section->enrollments_count) }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button type="submit" form="section-form-{{ $section->id }}" class="px-2.5 py-1.5 bg-violet-50 hover:bg-violet-600 text-violet-700 hover:text-white text-xs font-bold rounded-lg transition border border-violet-100">
                                    Save
                                </button>
                                <form action="{{ route('admin.sections.destroy', ['subject' => $subject->subjectT_id, 'section' => $section->id]) }}" method="POST"
                                    onsubmit="return confirm('Delete Section {{ $section->section_name }}? Enrolled students will be unenrolled.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition text-xs font-semibold">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-400">No sections yet for this subject/semester.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Manually enroll a student --}}
    @if ($eligibleStudents->isNotEmpty() && $sections->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 mb-8">
            <h2 class="text-sm font-bold text-slate-800 mb-3">Manually Enroll a Student</h2>
            <form action="{{ route('admin.sections.enroll', $subject->subjectT_id) }}" method="POST" class="flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-slate-500 uppercase">Student</label>
                    <select name="student_id" required class="mt-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none bg-white">
                        @foreach ($eligibleStudents as $student)
                            <option value="{{ $student->student_id }}">{{ $student->student_name }} ({{ $student->student_id }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500 uppercase">Section</label>
                    <select name="subject_section_id" required class="mt-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none bg-white">
                        @foreach ($sections as $section)
                            <option value="{{ $section->id }}" {{ $section->enrollments_count >= $section->capacity ? 'disabled' : '' }}>
                                {{ $section->section_name }} ({{ $section->enrollments_count }}/{{ $section->capacity }}){{ $section->enrollments_count >= $section->capacity ? ' — Full' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                    Enroll
                </button>
            </form>
        </div>
    @endif

    {{-- Enrolled roster --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-200 bg-slate-50">
            <h2 class="text-sm font-bold text-slate-800">Enrolled Students ({{ $enrollments->count() }})</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3 text-left font-bold text-slate-500 uppercase text-[11px] tracking-wider">Student</th>
                    <th class="px-5 py-3 text-left font-bold text-slate-500 uppercase text-[11px] tracking-wider">Section</th>
                    <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($enrollments as $enrollment)
                    <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                        <td class="px-5 py-3">
                            <p class="font-semibold text-slate-800">{{ $enrollment->student?->student_name ?? '—' }}</p>
                            <p class="text-xs text-slate-400">{{ $enrollment->student_id }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <form action="{{ route('admin.sections.enrollments.move', ['subject' => $subject->subjectT_id, 'enrollment' => $enrollment->id]) }}" method="POST"
                                class="flex items-center gap-2">
                                @csrf
                                @method('PUT')
                                <select name="subject_section_id" onchange="this.form.submit()"
                                    class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs focus:ring-2 focus:ring-violet-500 focus:outline-none">
                                    @foreach ($sections as $section)
                                        <option value="{{ $section->id }}"
                                            {{ $enrollment->subject_section_id == $section->id ? 'selected' : '' }}
                                            {{ $enrollment->subject_section_id != $section->id && $section->enrollments_count >= $section->capacity ? 'disabled' : '' }}>
                                            {{ $section->section_name }} ({{ $section->enrollments_count }}/{{ $section->capacity }})
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <form action="{{ route('admin.sections.enrollments.destroy', ['subject' => $subject->subjectT_id, 'enrollment' => $enrollment->id]) }}" method="POST"
                                onsubmit="return confirm('Remove this enrollment?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2.5 py-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition text-xs font-semibold">
                                    Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-5 py-10 text-center text-slate-400">No students enrolled yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Create Section modal --}}
    <div x-show="showCreate" x-cloak style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4">
        <div @click.outside="showCreate = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Add Section</h2>
            <form action="{{ route('admin.sections.store', $subject->subjectT_id) }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="academic_period_id" value="{{ $period->id }}">
                <div>
                    <label class="text-xs font-semibold text-slate-500 uppercase">Section Name</label>
                    <input type="text" name="section_name" required placeholder="e.g. Section A"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500 uppercase">Lecturer</label>
                    <select name="lecturer_id" class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none bg-white">
                        <option value="">— Unassigned —</option>
                        @foreach ($lecturers as $lecturer)
                            <option value="{{ $lecturer->lecturer_id }}">{{ $lecturer->lecturer_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500 uppercase">Capacity</label>
                    <input type="number" min="1" name="capacity" required
                        class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showCreate = false" class="px-4 py-2 text-sm font-semibold text-slate-500 hover:text-slate-700">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold rounded-xl">Create Section</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
