@extends('layouts.student')

@section('title', 'Section Enrollment')

@section('content')
<div class="max-w-4xl mx-auto">

    @if (session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-semibold shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm font-semibold shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800">Section Enrollment</h1>
        <p class="text-sm text-slate-500 mt-1">
            @if ($period)
                Choose your section for each subject this semester.
                @if (! $period->section_enrollment_open)
                    <span class="font-semibold text-red-500">Enrollment is currently closed — contact your administrator to change a section.</span>
                @endif
            @else
                No active Academic Semester.
            @endif
        </p>
    </div>

    @if ($subjects->isEmpty())
        <div class="p-8 bg-white border border-slate-100 rounded-2xl text-center text-slate-400 text-sm">
            No subjects found for this semester yet.
        </div>
    @else
        <div class="space-y-6">
            @foreach ($subjects as $row)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <h3 class="text-lg font-bold text-slate-800 mb-1">{{ $row['subject']->subjectT_name }}</h3>
                    <p class="text-xs text-slate-400 mb-4">{{ $row['subject']->subjectT_code }}</p>

                    @if ($row['sections']->isEmpty())
                        <p class="text-sm text-slate-400">No sections have been set up for this subject yet.</p>
                    @else
                        <div class="grid gap-3 md:grid-cols-2">
                            @foreach ($row['sections'] as $section)
                                @php
                                    $isMine = $row['my_section_id'] == $section->id;
                                    $isFull = $section->enrollments_count >= $section->capacity;
                                @endphp
                                <div class="border rounded-xl p-4 {{ $isMine ? 'border-violet-400 bg-violet-50/50' : 'border-slate-200' }}">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="font-bold text-slate-800">Section {{ $section->section_name }}</p>
                                        @if ($isMine)
                                            <span class="px-2 py-0.5 rounded-full bg-violet-600 text-white text-[10px] font-bold uppercase">Selected</span>
                                        @elseif ($isFull)
                                            <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-600 text-[10px] font-bold uppercase">Full</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-500 mb-1">Lecturer: {{ $section->lecturer?->lecturer_name ?? '—' }}</p>
                                    <p class="text-xs text-slate-500 mb-3">{{ $section->enrollments_count }} / {{ $section->capacity }} enrolled</p>

                                    <form action="{{ route('student.sectionEnrollment.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="subject_section_id" value="{{ $section->id }}">
                                        <button type="submit"
                                            @disabled($isMine || (! $period->section_enrollment_open) || ($isFull && ! $isMine))
                                            class="w-full py-2 rounded-lg text-xs font-bold uppercase tracking-wide transition
                                                {{ $isMine ? 'bg-violet-100 text-violet-400 cursor-not-allowed' : ($isFull || ! $period->section_enrollment_open ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-violet-600 text-white hover:bg-violet-700') }}">
                                            {{ $isMine ? 'Current Section' : ($isFull ? 'Full' : 'Choose This Section') }}
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
