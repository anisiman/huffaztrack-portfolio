@extends('layouts.student')

@section('title', 'Subject Attendance')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800">Subject Attendance</h1>
        <p class="text-sm text-slate-500 mt-1">Your attendance history and percentage per subject.</p>
    </div>

    @if ($bySubject->isEmpty())
        <div class="p-8 bg-white border border-slate-100 rounded-2xl text-center text-slate-400 text-sm">
            No subject attendance recorded yet.
        </div>
    @else
        <div class="space-y-4">
            @foreach ($bySubject as $row)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="p-5 flex items-center justify-between cursor-pointer" onclick="this.nextElementSibling.classList.toggle('hidden')">
                        <div>
                            <p class="font-bold text-slate-800">{{ $row['subject']?->subjectT_name ?? 'Unknown subject' }}</p>
                            <p class="text-xs text-slate-400">{{ $row['subject']?->subjectT_code }}</p>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="text-center">
                                <p class="text-xs text-slate-400">Present</p>
                                <p class="font-bold text-emerald-600">{{ $row['present'] }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-slate-400">Absent</p>
                                <p class="font-bold text-red-600">{{ $row['absent'] }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-slate-400">Rate</p>
                                <p class="font-bold text-violet-700">{{ $row['percentage'] !== null ? $row['percentage'] . '%' : '—' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="hidden border-t border-slate-100">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-5 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Date</th>
                                    <th class="px-5 py-2 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach ($row['records'] as $record)
                                    <tr>
                                        <td class="px-5 py-2 text-slate-600">{{ $record->date->format('d M Y') }}</td>
                                        <td class="px-5 py-2">
                                            @if ($record->status === 'present')
                                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-semibold">Present</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-[11px] font-semibold">Absent</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
