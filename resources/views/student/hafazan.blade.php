@extends('layouts.student')

@section('title', 'My Hafazan')

@section('content')

{{-- Page Header --}}
<div class="mb-6">
    <h1 class="text-4xl font-bold text-black">My Hafazan</h1>
    <p class="text-sm text-slate-400 mt-1">Track your Quran memorisation progress and session history.</p>
</div>

{{-- ── Not Linked Warning ── --}}
@if (!$student)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-8 text-center max-w-md mx-auto mt-10">
        <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-amber-500">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
        </div>
        <p class="text-amber-800 font-bold text-base">Account not linked</p>
        <p class="text-amber-600 text-sm mt-1">Please contact your administrator to link your account.</p>
    </div>

@else

{{-- ── Quran Progress Banner ── --}}
<div class="relative overflow-hidden rounded-2xl mb-6 p-6"
     style="background: linear-gradient(135deg, #064e3b 0%, #059669 55%, #10b981 100%);">
    <div class="absolute -top-8 -right-8 w-44 h-44 rounded-full opacity-10" style="background: radial-gradient(circle, white, transparent);"></div>
    <div class="absolute bottom-0 right-20 w-24 h-24 rounded-full opacity-10" style="background: radial-gradient(circle, white, transparent);"></div>

    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-emerald-200 text-xs font-bold uppercase tracking-widest mb-1">Quran Memorisation</p>
            @if($latestTasmi && $latestTasmi->progress)
                @php $pageReached = $latestTasmi->progress->page_reached; $pct = round(($pageReached / 604) * 100); @endphp
                <p class="text-5xl font-black text-white">{{ $pageReached }}<span class="text-2xl font-semibold text-emerald-200"> / 604</span></p>
                @php $qi = \App\Helpers\QuranHelper::getInfo($pageReached); @endphp
                <div class="flex gap-2 mt-2 mb-1 flex-wrap items-center">
                    <span class="inline-block bg-white/20 text-white text-sm font-bold px-3 py-1 rounded-full" dir="rtl" lang="ar">{{ $qi['surah'] }}</span>
                    <span class="inline-block bg-white/15 text-emerald-100 text-xs font-semibold px-3 py-1 rounded-full">Juz {{ $qi['juz'] }}</span>
                </div>
                <p class="text-emerald-200 text-sm mt-1">pages memorized · {{ $pct }}% of the Quran</p>
            @else
                <p class="text-4xl font-black text-emerald-200">No sessions yet</p>
                <p class="text-emerald-300 text-sm mt-1">Your progress will appear here once recorded.</p>
                @php $pct = 0; @endphp
            @endif
        </div>
        <div class="w-full sm:w-56">
            <div class="flex justify-between text-xs text-emerald-200 mb-1.5">
                <span>Progress</span>
                <span>{{ $pct }}%</span>
            </div>
            <div class="w-full bg-white/20 rounded-full h-3">
                <div class="bg-white h-3 rounded-full transition-all" style="width: {{ $pct }}%"></div>
            </div>
            @if($latestTasmi)
                <p class="text-emerald-200 text-xs mt-2">Last session: {{ \Carbon\Carbon::parse($latestTasmi->tasmi_date)->format('d M Y') }}</p>
            @endif
        </div>
    </div>
</div>

{{-- ── Summary Cards ── --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

    {{-- Total Sessions --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-xl bg-violet-100 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-violet-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/>
                </svg>
            </div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Sessions</p>
        </div>
        <p class="text-3xl font-black text-slate-700">{{ $totalSessions }}</p>
        <p class="text-xs text-slate-400 mt-1">hafazan sessions recorded</p>
    </div>

    {{-- Current Page --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-emerald-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                </svg>
            </div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Current Page</p>
        </div>
        <p class="text-3xl font-black text-emerald-600">
            @if($latestTasmi && $latestTasmi->progress)
                {{ $latestTasmi->progress->page_reached }}
            @else
                <span class="text-slate-300">—</span>
            @endif
        </p>
        @if($latestTasmi && $latestTasmi->progress)
            @php $qi = \App\Helpers\QuranHelper::getInfo($latestTasmi->progress->page_reached); @endphp
            <div class="flex gap-2 mt-2 flex-wrap items-center">
                <span class="inline-block bg-purple-100 text-purple-700 text-xs font-bold px-3 py-1 rounded-full" dir="rtl" lang="ar">{{ $qi['surah'] }}</span>
                <span class="inline-block bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full">Juz {{ $qi['juz'] }}</span>
            </div>
        @endif
        <p class="text-xs text-slate-400 mt-1">out of 604 pages</p>
    </div>

    {{-- Last Session --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-xl bg-sky-100 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-sky-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                </svg>
            </div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Last Session</p>
        </div>
        <p class="text-lg font-black text-slate-700 leading-tight">
            @if($latestTasmi)
                {{ \Carbon\Carbon::parse($latestTasmi->tasmi_date)->format('d M Y') }}
            @else
                <span class="text-slate-300 text-2xl">—</span>
            @endif
        </p>
        <p class="text-xs text-slate-400 mt-1">most recent hafazan date</p>
    </div>

</div>

{{-- ── Session History Table ── --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

    <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
        <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
        <h2 class="text-sm font-bold text-slate-700">Session History</h2>
    </div>

    @if($hafazanHistory->isEmpty())
        <div class="py-16 text-center">
            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-slate-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                </svg>
            </div>
            <p class="text-slate-500 font-semibold text-sm">No hafazan sessions recorded yet.</p>
        </div>

    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider w-14">No.</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Page Reached</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($hafazanHistory as $index => $session)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4 text-xs text-slate-400 font-semibold">
                                {{ $hafazanHistory->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-2 h-2 rounded-full flex-shrink-0 {{ $session->progress ? 'bg-emerald-400' : 'bg-slate-300' }}"></div>
                                    <span class="text-sm font-medium text-slate-700">
                                        {{ $session->tasmi_date
                                            ? \Carbon\Carbon::parse($session->tasmi_date)->format('d M Y, D')
                                            : '—' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($session->progress)
                                    @php $qi = \App\Helpers\QuranHelper::getInfo($session->progress->page_reached); @endphp
                                    <span class="text-sm font-black text-emerald-600">
                                        Page {{ $session->progress->page_reached }}
                                    </span>
                                    <div class="flex gap-1.5 mt-1.5 flex-wrap items-center">
                                        <span class="inline-block bg-purple-100 text-purple-700 text-xs font-bold px-2.5 py-0.5 rounded-full" dir="rtl" lang="ar">{{ $qi['surah'] }}</span>
                                        <span class="inline-block bg-blue-100 text-blue-700 text-xs font-semibold px-2.5 py-0.5 rounded-full">Juz {{ $qi['juz'] }}</span>
                                    </div>
                                @else
                                    <span class="text-sm text-slate-400">Not recorded</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($session->progress)
                                    <span class="text-xs font-semibold px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        Recorded
                                    </span>
                                @else
                                    <span class="text-xs font-semibold px-3 py-1.5 rounded-full bg-slate-100 text-slate-400 border border-slate-200">
                                        Pending
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100">
            {{ $hafazanHistory->links() }}
        </div>
    @endif

</div>

@endif

@endsection
