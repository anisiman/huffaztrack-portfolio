@extends('layouts.student')

@section('title', 'Lecturer Notes')

@section('content')

{{-- Page Header --}}
<div class="mb-6">
    <h1 class="text-4xl font-bold text-black">Lecturer Notes</h1>
    <p class="text-sm text-slate-400 mt-1">Notes and remarks your lecturer has written about your progress.</p>
</div>

@if($notes->isEmpty())
    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-white py-20 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mb-4 h-12 w-12 text-slate-300">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
        </svg>
        <p class="text-base font-semibold text-slate-400">No notes yet</p>
        <p class="mt-1 text-sm text-slate-400">Your lecturer hasn't written any notes for you yet.</p>
    </div>
@else
    <div class="space-y-4">
        @foreach($notes as $note)
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $note->body }}</p>
                <div class="mt-3 flex items-center gap-2 text-xs text-slate-400">
                    <span class="font-semibold text-slate-500">{{ $note->lecturer?->lecturer_name ?? 'Your lecturer' }}</span>
                    <span>&middot;</span>
                    <span>{{ $note->created_at->format('d M Y, g:i A') }}</span>
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection
