@extends('layouts.admin')

@section('title', $subject->subjectT_name . ' — Marks')

@section('content')
<div class="max-w-5xl mx-auto">

    <a href="{{ route('admin.subjects', ['academic_period_id' => $period?->id]) }}"
        class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-violet-600 mb-4">
        &larr; Back to Subject Management
    </a>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">{{ $subject->subjectT_name }}</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $subject->subjectT_code }} &middot; {{ $subject->credit_hours }} credit hours</p>
        </div>

        <form method="GET" class="flex items-center gap-2">
            <label class="text-xs font-semibold text-slate-500 uppercase">Semester</label>
            <select name="academic_period_id" onchange="this.form.submit()"
                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none bg-white">
                @foreach ($academicPeriods as $ap)
                    <option value="{{ $ap->id }}" {{ $period && $period->id === $ap->id ? 'selected' : '' }}>
                        {{ $ap->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if (! $period)
        <div class="p-6 bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl text-sm font-semibold">
            No Academic Semester selected.
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3 text-left font-bold text-slate-500 uppercase text-[11px] tracking-wider">Student</th>
                        <th class="px-5 py-3 text-left font-bold text-slate-500 uppercase text-[11px] tracking-wider">Batch</th>
                        <th class="px-5 py-3 text-left font-bold text-slate-500 uppercase text-[11px] tracking-wider">Responsible</th>
                        <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Carry Marks 1</th>
                        <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Carry Marks 2</th>
                        <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Total Carry Marks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $row)
                        <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                            <td class="px-5 py-3">
                                <p class="font-semibold text-slate-800">{{ $row['student']->student_name }}</p>
                                <p class="text-xs text-slate-400">{{ $row['student']->student_id }}</p>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $row['student']->batch?->batch_name ?? '—' }}</td>
                            <td class="px-5 py-3 text-slate-500 text-xs">{{ $row['responsible'] }}</td>
                            <td class="px-5 py-3 text-center text-slate-700">{{ $row['carry_mark_1'] ?? '—' }}</td>
                            <td class="px-5 py-3 text-center text-slate-700">{{ $row['carry_mark_2'] ?? '—' }}</td>
                            <td class="px-5 py-3 text-center font-bold text-violet-700">{{ $row['total'] ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-slate-400">
                                No students are currently mapped to this subject's Semester Level for this Academic Semester.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
