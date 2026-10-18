@extends('layouts.lecturer')

@section('title', 'My Sections')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800">My Sections</h1>
        <p class="text-sm text-slate-500 mt-1">
            @if ($period)
                Subject sections you teach for {{ $period->name }}.
            @else
                No active Academic Semester.
            @endif
        </p>
    </div>

    @if ($sections->isEmpty())
        <div class="p-8 bg-white border border-slate-100 rounded-2xl text-center text-slate-400 text-sm">
            You have not been assigned to teach any sections this semester.
        </div>
    @else
        <div class="space-y-6">
            @foreach ($sections as $section)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-violet-600 uppercase tracking-wider mb-1">
                                {{ $section->subject?->semester?->semester_name ?? 'Semester Level ' . $section->subject?->semester_id }}
                            </p>
                            <h3 class="text-lg font-bold text-slate-800">
                                {{ $section->subject?->subjectT_name }} &middot; Section {{ $section->section_name }}
                            </h3>
                        </div>
                        <span class="px-3 py-1.5 rounded-full bg-violet-50 text-violet-700 text-xs font-bold">
                            {{ $section->enrollments->count() }} / {{ $section->capacity }}
                        </span>
                    </div>

                    @if ($section->enrollments->isEmpty())
                        <p class="p-5 text-sm text-slate-400">No students enrolled yet.</p>
                    @else
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-5 py-2.5 text-left font-bold text-slate-500 uppercase text-[11px] tracking-wider">Student ID</th>
                                    <th class="px-5 py-2.5 text-left font-bold text-slate-500 uppercase text-[11px] tracking-wider">Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($section->enrollments->sortBy(fn ($e) => $e->student?->student_name) as $enrollment)
                                    <tr class="border-b border-slate-50">
                                        <td class="px-5 py-2.5 text-slate-500">{{ $enrollment->student_id }}</td>
                                        <td class="px-5 py-2.5 font-semibold text-slate-800">{{ $enrollment->student?->student_name ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
