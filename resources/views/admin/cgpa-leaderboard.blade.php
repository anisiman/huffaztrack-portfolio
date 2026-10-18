@extends('layouts.admin')

@section('title', $mode === 'at_risk' ? 'At-Risk Students' : 'Top Students Leaderboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">
    <style>
        .users-page { padding: 0; max-width: none; margin: 0; }

        .rank-badge {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 13px;
            flex-shrink: 0;
        }

        .users-table-card {
            margin-top: 0;
            background: #fff;
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }
        .users-table-wrap { overflow-x: auto; }
        .users-table { width: 100%; border-collapse: collapse; }
        .users-table thead { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        .users-table thead th {
            padding: 18px 20px;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #64748b;
            text-transform: uppercase;
            text-align: left;
        }
        .users-table tbody td {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.92rem;
            color: #334155;
            vertical-align: middle;
        }
        .users-table tbody tr:hover { background: #fafafa; }
        .users-table tbody tr:last-child td { border-bottom: none; }
    </style>
@endpush

@section('content')
    <div class="users-page">

        {{-- Header --}}
        <div class="users-page__header">
            <div>
                <h1 class="users-page__title">{{ $mode === 'at_risk' ? 'At-Risk Students' : 'Top Students Leaderboard' }}</h1>
                <p class="text-sm text-slate-400 mt-0.5">
                    {{ $mode === 'at_risk' ? 'Active students with latest CGPA below 2.50.' : 'Active students with latest CGPA of 3.50 and above.' }}
                </p>
                <div class="users-page__breadcrumb">
                    <span>Dashboard</span>
                    <span>&gt;</span>
                    <span class="users-page__breadcrumb-accent">{{ $mode === 'at_risk' ? 'At-Risk Students' : 'Top Performers' }}</span>
                </div>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="users-button users-button--secondary">
                &larr; Back to Dashboard
            </a>
        </div>

        <div class="users-table-card">

            {{-- Filter --}}
            <form method="GET" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
                <div class="users-search" style="max-width:320px;">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or ID...">
                </div>

                <select name="batch" class="users-filter--select" style="max-width:220px;">
                    <option value="Overall" {{ $currentBatch === 'Overall' ? 'selected' : '' }}>All Batches</option>
                    @foreach($batches as $b)
                        <option value="{{ $b->batch_name }}" {{ $currentBatch === $b->batch_name ? 'selected' : '' }}>
                            {{ trim($b->batch_name) }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="users-button users-button--primary">Filter</button>
                @if($search || $currentBatch !== 'Overall')
                    <a href="{{ url()->current() }}" class="users-button users-button--secondary">Reset</a>
                @endif

                <span class="text-sm text-slate-400" style="margin-left:auto;">
                    {{ $students->total() }} {{ Str::plural('student', $students->total()) }}
                </span>
            </form>

            {{-- TABLE --}}
            <div class="users-table-wrap">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th style="width: 70px;">{{ $mode === 'at_risk' ? '' : 'RANK' }}</th>
                            <th>STUDENT</th>
                            <th>STUDENT ID</th>
                            <th>BATCH</th>
                            <th style="text-align: right;">CGPA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $index => $student)
                            <tr>
                                <td>
                                    @if($mode === 'at_risk')
                                        <div class="rank-badge" style="background:#fee2e2; color:#dc2626;">!</div>
                                    @else
                                        <div class="rank-badge" style="background:#ede9fe; color:#7c3aed;">#{{ $students->firstItem() + $index }}</div>
                                    @endif
                                </td>
                                <td style="font-weight:500; color:#1e293b;">{{ $student->student_name }}</td>
                                <td class="users-table__muted">{{ $student->student_id }}</td>
                                <td>
                                    <span style="background:#f1f5f9;padding:4px 10px;border-radius:6px;font-size:0.75rem;color:#475569;">
                                        {{ trim($student->batch_name) }}
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <span style="font-weight:800; font-size:1rem; color: {{ $mode === 'at_risk' ? '#dc2626' : '#7c3aed' }};">
                                        {{ number_format($student->cgpa, 2) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 2rem; color: #94a3b8;">
                                    {{ $mode === 'at_risk' ? 'No at-risk students found.' : 'No students found.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div style="padding: 1.25rem 1.5rem; border-top: 1px solid #f1f5f9; background: #fff;">
                    {{ $students->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
