@extends('layouts.lecturer')

@section('title', 'Subject Attendance')

@section('content')
<div class="max-w-5xl mx-auto">

    @if (session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-semibold shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('info'))
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl text-sm font-semibold shadow-sm">
            {{ session('info') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm font-semibold shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800">Subject Attendance</h1>
        <p class="text-sm text-slate-500 mt-1">
            @if ($period)
                My Sections for {{ $period->name }} — create a session and generate a QR code.
            @else
                No active Academic Semester.
            @endif
        </p>
        <p class="text-xs text-slate-400 mt-1">
            Untuk kehadiran Hifz Syafawi &amp; Hifz Tahriri, sila gunakan
            <a href="{{ route('lecturer.attendance') }}" class="text-violet-600 hover:text-violet-800 font-semibold">Tasmik Attendance</a>.
        </p>
    </div>

    {{-- Today's sessions --}}
    @if ($todaySessions->isNotEmpty())
    <div class="mb-8">
        <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-3">Today's Sessions</h2>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Subject</th>
                        <th class="px-5 py-3 text-left text-[11px] font-bold text-slate-400 uppercase tracking-wider">Roster</th>
                        <th class="px-5 py-3 text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider">Scanned</th>
                        <th class="px-5 py-3 text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach ($todaySessions as $s)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-5 py-3 font-semibold text-slate-700">{{ $s->subject?->subjectT_name }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $s->scope_label }}</td>
                            <td class="px-5 py-3 text-center text-slate-600">{{ $s->records_count }}</td>
                            <td class="px-5 py-3 text-center">
                                @if ($s->isExpired())
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[11px] font-semibold">Ended</span>
                                @elseif ($s->isManuallyPaused())
                                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[11px] font-semibold">Paused</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-semibold">Active</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('lecturer.subjectAttendance.show', $s->session_id) }}"
                                   class="text-xs font-bold text-violet-600 hover:text-violet-800">View →</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- My Sections — one card per Madah Section I own. Hafazan is intentionally
         excluded (see LecturerSubjectAttendanceController docblock). --}}
    <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-3">My Sections</h2>
    @if ($rows->isEmpty())
        <div class="p-8 bg-white border border-slate-100 rounded-2xl text-center text-slate-400 text-sm">
            You have no assigned Sections this semester.
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($rows as $row)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <p class="text-xs font-bold text-violet-600 uppercase tracking-wider mb-1">
                        {{ $row['subject']->semester?->semester_name ?? 'Semester Level ' . $row['subject']->semester_id }}
                    </p>
                    <h3 class="text-lg font-bold text-slate-800">{{ $row['subject']->subjectT_name }}</h3>
                    <p class="text-xs text-slate-400 mb-3">
                        {{ $row['subject']->subjectT_code }} &middot; Section {{ $row['section']->section_name }}
                    </p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">{{ $row['student_count'] }} student(s)</span>
                        <form method="POST" action="{{ route('lecturer.subjectAttendance.createSession') }}">
                            @csrf
                            <input type="hidden" name="subjectT_id" value="{{ $row['subject']->subjectT_id }}">
                            <input type="hidden" name="subject_section_id" value="{{ $row['section']->id }}">
                            <button type="submit"
                                class="px-4 py-2 bg-violet-600 text-white rounded-xl text-xs font-bold hover:bg-violet-700 transition">
                                Create Session
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
