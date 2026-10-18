@extends('layouts.lecturer')

@section('title', 'Subjects')

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
        <h1 class="text-2xl font-bold text-slate-800">My Subjects</h1>
        <p class="text-sm text-slate-500 mt-1">
            @if ($period)
                Subjects you teach for {{ $period->name }}.
            @else
                No active Academic Semester.
            @endif
        </p>
    </div>

    @if ($assignments->isEmpty())
        <div class="p-8 bg-white border border-slate-100 rounded-2xl text-center text-slate-400 text-sm">
            You have not been assigned to teach any subjects this semester.
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($assignments as $row)
                <a href="{{ route('lecturer.subjects.show', $row['subject']->subjectT_id) }}"
                    class="block bg-white rounded-2xl border border-slate-100 shadow-sm p-6 hover:border-violet-300 hover:shadow-md transition">
                    <div class="flex items-center gap-2 mb-1">
                        <p class="text-xs font-bold text-violet-600 uppercase tracking-wider">
                            {{ $row['subject']->semester?->semester_name ?? 'Semester Level ' . $row['subject']->semester_id }}
                        </p>
                        @if ($row['subject']->isHafazan())
                            <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-[10px] font-bold uppercase">Halaqah</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full bg-violet-50 text-violet-700 text-[10px] font-bold uppercase">Section</span>
                        @endif
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">{{ $row['subject']->subjectT_name }}</h3>
                    <p class="text-xs text-slate-400 mb-3">{{ $row['subject']->subjectT_code }} &middot; {{ $row['subject']->credit_hours }} credit hours</p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">{{ $row['student_count'] }} student(s)</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
