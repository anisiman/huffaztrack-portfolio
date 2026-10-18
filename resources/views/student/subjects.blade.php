@extends('layouts.student')

@section('title', 'My Subjects')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800">My Subjects</h1>
        <p class="text-sm text-slate-500 mt-1">
            @if ($period)
                Subjects for {{ $period->name }}, determined automatically from your batch's semester level.
            @else
                No active Academic Semester.
            @endif
        </p>
    </div>

    @if ($subjects->isEmpty())
        <div class="p-8 bg-white border border-slate-100 rounded-2xl text-center text-slate-400 text-sm">
            No subjects found for this semester yet. This is set by the admin once your batch's semester level is assigned.
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3 text-left font-bold text-slate-500 uppercase text-[11px] tracking-wider">Subject</th>
                        <th class="px-5 py-3 text-left font-bold text-slate-500 uppercase text-[11px] tracking-wider">Lecturer</th>
                        <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Via</th>
                        <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Carry Marks 1</th>
                        <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Carry Marks 2</th>
                        <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Total Carry Marks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subjects as $row)
                        <tr class="border-b border-slate-50">
                            <td class="px-5 py-3">
                                <p class="font-semibold text-slate-800">{{ $row['subject']->subjectT_name }}</p>
                                <p class="text-xs text-slate-400">{{ $row['subject']->subjectT_code }} &middot; {{ $row['subject']->credit_hours }} credit hours</p>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $row['lecturer']->lecturer_name ?? '—' }}</td>
                            <td class="px-5 py-3 text-center">
                                @if ($row['subject']->isHafazan())
                                    <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-[11px] font-semibold">Halaqah</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-violet-50 text-violet-700 text-[11px] font-semibold">Section</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center text-slate-700">{{ $row['carry_mark_1'] ?? '—' }}</td>
                            <td class="px-5 py-3 text-center text-slate-700">{{ $row['carry_mark_2'] ?? '—' }}</td>
                            <td class="px-5 py-3 text-center font-bold text-violet-700">{{ $row['total'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
