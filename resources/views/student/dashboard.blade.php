@extends('layouts.student')

@section('title', 'Dashboard')

@section('content')

{{-- ── Not Linked Warning ── --}}
@if (!$student)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-8 text-center max-w-md mx-auto mt-10">
        <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-amber-500">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
        </div>
        <p class="text-amber-800 font-bold text-base">Account not linked</p>
        <p class="text-amber-600 text-sm mt-1">Please contact your administrator to link your account to a student record.</p>
    </div>

@else

{{-- ── Hero Banner ── --}}
<div class="relative overflow-hidden rounded-2xl mb-6 p-6 lg:p-8"
     style="background: linear-gradient(135deg, #3b0764 0%, #6d28d9 55%, #7c3aed 100%);">
    {{-- Decorative blobs --}}
    <div class="absolute -top-10 -right-10 w-48 h-48 rounded-full opacity-10" style="background: radial-gradient(circle, white, transparent);"></div>
    <div class="absolute bottom-0 right-24 w-28 h-28 rounded-full opacity-10" style="background: radial-gradient(circle, white, transparent);"></div>
    <div class="absolute top-4 right-56 w-14 h-14 rounded-full opacity-10" style="background: radial-gradient(circle, white, transparent);"></div>

    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            @php
                $hour = now()->hour;
                $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
            @endphp
            <p class="text-purple-200 text-sm font-medium mb-1">{{ $greeting }} 👋</p>
            <h1 class="text-4xl font-bold text-white">{{ Auth::user()->name }}</h1>
            <p class="text-purple-200 text-sm mt-1">Here's your progress overview for today.</p>
        </div>
        <div class="flex gap-3 flex-wrap">
            <a href="{{ route('student.attendance') }}"
               class="text-xs font-semibold px-4 py-2.5 bg-white/20 hover:bg-white/30 text-white rounded-xl transition border border-white/20 backdrop-blur-sm">
                My Attendance
            </a>
            <a href="{{ route('student.hafazan') }}"
               class="text-xs font-semibold px-4 py-2.5 bg-white text-purple-900 rounded-xl transition hover:bg-purple-50 shadow-md">
                My Hafazan
            </a>
        </div>
    </div>
</div>

{{-- ── Summary Cards ── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">

    {{-- Attendance Card --}}
    <div class="relative overflow-hidden rounded-2xl p-6 text-white shadow-lg"
         style="background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%);">
        <div class="absolute -bottom-4 -right-4 w-28 h-28 rounded-full opacity-10" style="background: radial-gradient(circle, white, transparent);"></div>
        <div class="flex items-start justify-between mb-4">
            <div>
                <p class="text-purple-200 text-xs font-bold uppercase tracking-widest mb-1">Attendance Rate</p>
                <p class="text-4xl font-black">{{ $attendancePercent }}<span class="text-xl font-semibold text-purple-200">%</span></p>
                <p class="text-purple-200 text-xs mt-1">{{ $presentCount }} of {{ $totalAttendance }} sessions attended</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-6 h-6 text-white">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
        </div>
        {{-- Progress bar --}}
        <div class="w-full bg-white/20 rounded-full h-2 mb-3">
            <div class="bg-white h-2 rounded-full transition-all" style="width: {{ $attendancePercent }}%"></div>
        </div>
        <a href="{{ route('student.attendance') }}" class="text-xs font-semibold text-purple-200 hover:text-white transition">
            View full history →
        </a>
    </div>

    {{-- Hafazan Card --}}
    <div class="relative overflow-hidden rounded-2xl p-6 text-white shadow-lg"
         style="background: linear-gradient(135deg, #065f46 0%, #059669 100%);">
        <div class="absolute -bottom-4 -right-4 w-28 h-28 rounded-full opacity-10" style="background: radial-gradient(circle, white, transparent);"></div>
        <div class="flex items-start justify-between mb-4">
            <div>
                <p class="text-emerald-200 text-xs font-bold uppercase tracking-widest mb-1">Hafazan Progress</p>
                @if($latestTasmi && $latestTasmi->progress)
                    <p class="text-4xl font-black">{{ $latestTasmi->progress->page_reached }}<span class="text-xl font-semibold text-emerald-200"> / 604</span></p>
                    <p class="text-emerald-200 text-xs mt-1">pages memorized</p>
                    @if($quranPosition)
                        <p class="text-white/80 text-xs mt-1.5 font-semibold">
                            Juz {{ $quranPosition['juz'] }} &middot; {{ $quranPosition['surah_name'] }} ({{ $quranPosition['surah_number'] }})
                        </p>
                    @endif
                @else
                    <p class="text-4xl font-black text-emerald-300">—</p>
                    <p class="text-emerald-200 text-xs mt-1">No sessions recorded yet</p>
                @endif
            </div>
            <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-6 h-6 text-white">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                </svg>
            </div>
        </div>
        {{-- Quran page progress bar --}}
        @if($latestTasmi && $latestTasmi->progress)
            @php $hafazanPct = round(($latestTasmi->progress->page_reached / 604) * 100); @endphp
            <div class="w-full bg-white/20 rounded-full h-2 mb-3">
                <div class="bg-white h-2 rounded-full transition-all" style="width: {{ $hafazanPct }}%"></div>
            </div>
            <p class="text-xs text-emerald-200">{{ $hafazanPct }}% of the Quran completed</p>
        @else
            <div class="w-full bg-white/20 rounded-full h-2 mb-3">
                <div class="bg-white h-2 rounded-full" style="width: 0%"></div>
            </div>
            <p class="text-xs text-emerald-200">{{ $totalSessions }} session{{ $totalSessions !== 1 ? 's' : '' }} total</p>
        @endif
        <div class="mt-2">
            <a href="{{ route('student.hafazan') }}" class="text-xs font-semibold text-emerald-200 hover:text-white transition">
                View full history →
            </a>
        </div>
    </div>

</div>

{{-- ── Recent Activity ── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

    {{-- Recent Attendance --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-violet-500"></div>
                <h2 class="text-sm font-bold text-slate-700">Recent Attendance</h2>
            </div>
            <a href="{{ route('student.attendance') }}" class="text-xs text-violet-500 hover:text-violet-700 font-semibold">See all</a>
        </div>

        @if($recentAttendance->isEmpty())
            <div class="py-10 text-center">
                <p class="text-sm text-slate-400">No attendance records yet.</p>
            </div>
        @else
            <div class="divide-y divide-slate-50">
                @foreach($recentAttendance as $record)
                    @php
                        $status = strtolower($record->status ?? '');
                        $badge = match($status) {
                            'present'            => ['bg-green-50 text-green-700 border border-green-100', 'Present'],
                            'absent'             => ['bg-red-50 text-red-600 border border-red-100', 'Absent'],
                            'absent_with_reason' => ['bg-amber-50 text-amber-700 border border-amber-100', 'Excused'],
                            default              => ['bg-slate-100 text-slate-500', ucfirst($record->status ?? 'Unknown')],
                        };
                        $dot = match($status) {
                            'present'            => 'bg-green-400',
                            'absent'             => 'bg-red-400',
                            'absent_with_reason' => 'bg-amber-400',
                            default              => 'bg-slate-300',
                        };
                    @endphp
                    <div class="flex items-center justify-between px-6 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full {{ $dot }}"></div>
                            <p class="text-sm font-medium text-slate-700">
                                {{ $record->date ? \Carbon\Carbon::parse($record->date)->format('d M Y') : '—' }}
                            </p>
                        </div>
                        <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $badge[0] }}">
                            {{ $badge[1] }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Latest Hafazan --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                <h2 class="text-sm font-bold text-slate-700">Latest Hafazan</h2>
            </div>
            <a href="{{ route('student.hafazan') }}" class="text-xs text-emerald-500 hover:text-emerald-700 font-semibold">See all</a>
        </div>

        @if($recentHafazan->isEmpty())
            <div class="py-10 text-center">
                <p class="text-sm text-slate-400">No hafazan sessions recorded yet.</p>
            </div>
        @else
            <div class="divide-y divide-slate-50">
                @foreach($recentHafazan as $session)
                    <div class="flex items-center justify-between px-6 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                            <p class="text-sm font-medium text-slate-700">
                                {{ $session->tasmi_date ? \Carbon\Carbon::parse($session->tasmi_date)->format('d M Y') : '—' }}
                            </p>
                        </div>
                        @if($session->progress)
                            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                                Page {{ $session->progress->page_reached }}
                            </span>
                        @else
                            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-100 text-slate-400">
                                Pending
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

@endif

@endsection
