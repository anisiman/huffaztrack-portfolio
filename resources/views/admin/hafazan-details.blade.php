@extends('layouts.admin')

@section('title', 'Hafazan Details')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/hifz-record.css') }}">
    <style>
        .badge { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 600; }
        .badge--completed { background: #d1fae5; color: #059669; }
        .badge--on-track  { background: #dbeafe; color: #2563eb; }
        .badge--behind    { background: #fee2e2; color: #dc2626; }
        .badge--upcoming  { background: #f1f5f9; color: #64748b; }
    </style>
@endpush

@section('content')
<div class="hifz-page">
    <div>
        <a href="{{ route('admin.hafazan', $backParams) }}" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:#7c3aed;text-decoration:none;margin-bottom:10px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Back to Hafazan
        </a>
        <h1 class="hifz-page__title">{{ $lecturerName }}</h1>
        <p class="hifz-page__subtitle">
            Batch: <span style="background:#ede9fe;color:#6d28d9;border-radius:6px;padding:2px 10px;font-size:12px;font-weight:600;">{{ $batchName }}</span>
            &mdash; {{ $selectedDate->format('d M Y') }}
        </p>
    </div>

    @php
        $ahead  = $students->where('status', 'Ahead')->count();
        $onTrack = $students->where('status', 'On-track')->count();
        $behind = $students->where('status', 'Behind')->count();
        $notRec = $students->where('status', 'Not Recorded')->count();
    @endphp
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <span style="background:#d1fae5;color:#059669;padding:3px 12px;border-radius:99px;font-size:12px;font-weight:600;">Ahead: {{ $ahead }}</span>
        <span style="background:#dbeafe;color:#2563eb;padding:3px 12px;border-radius:99px;font-size:12px;font-weight:600;">On-track: {{ $onTrack }}</span>
        <span style="background:#fee2e2;color:#dc2626;padding:3px 12px;border-radius:99px;font-size:12px;font-weight:600;">Behind: {{ $behind }}</span>
        <span style="background:#f1f5f9;color:#64748b;padding:3px 12px;border-radius:99px;font-size:12px;font-weight:600;">Not Recorded: {{ $notRec }}</span>
    </div>

    <section class="hifz-table-panel">
        <div class="hifz-table-panel__header">
            <div>
                <h2 class="hifz-table-panel__title">Student Details</h2>
                <p class="hifz-table-panel__subtitle">{{ $students->count() }} student{{ $students->count() !== 1 ? 's' : '' }}</p>
            </div>
        </div>
        <div class="hifz-table-wrap">
            <table class="hifz-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Latest Page</th>
                        <th>Remaining</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $gdBadge = [
                            'Ahead'        => 'badge--completed',
                            'On-track'     => 'badge--on-track',
                            'Behind'       => 'badge--behind',
                            'Not Recorded' => 'badge--upcoming',
                        ];
                    @endphp
                    @forelse ($students as $s)
                        <tr>
                            <td>
                                <div style="font-weight:600;color:#0f172a;">{{ $s['student_name'] }}@if (! empty($s['khatam_count'])) <span style="display:inline-block;margin-left:6px;padding:1px 7px;border-radius:999px;background:#fef3c7;color:#b45309;font-size:0.68rem;font-weight:700;white-space:nowrap;" title="Muqarrar already fulfilled">🏆 Khatam x{{ $s['khatam_count'] }}</span>@endif</div>
                                <div style="font-size:11px;color:#94a3b8;">{{ $s['student_id'] }}</div>
                            </td>
                            <td>{{ $s['latest_page'] ? 'Pg ' . $s['latest_page'] : '—' }}</td>
                            <td style="color:#64748b;">@if (! empty($s['khatam_count']) && $s['latest_page'])<span style="color:#059669;font-weight:600;">Passed Muqarrar (Khatam)</span>@else{{ $s['remaining'] !== null ? $s['remaining'] . ' pages left' : '—' }}@endif</td>
                            <td><span class="badge {{ $gdBadge[$s['status']] ?? 'badge--upcoming' }}">{{ $s['status'] }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center;color:#94a3b8;padding:40px;">No students found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
