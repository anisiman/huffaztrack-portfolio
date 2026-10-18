@extends('layouts/lecturer')

@section('title', 'My Students')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">
    <style>
        .users-page { padding: 0; max-width: none; margin: 0; }

        .status-badge {
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-active { background: #dcfce7; color: #16a34a; }
        .status-dropped { background: #fee2e2; color: #dc2626; }
        .status-completed { background: #e0f2fe; color: #0284c7; }

        .gender-tag {
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 6px;
            margin-left: 4px;
            font-weight: 700;
        }
        .gender-tag--m { background: #e0f2fe; color: #0369a1; }
        .gender-tag--f { background: #fce7f3; color: #be185d; }

        .search-row { margin-bottom: 0; max-width: 450px; }

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
            padding: 18px 20px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.92rem;
            color: #334155;
            vertical-align: middle;
        }
        .users-table tbody tr:hover { background: #fafafa; }
        .users-table tbody tr:last-child td { border-bottom: none; }

        /* KHATAM CELL */
        .tamat-display { display: flex; align-items: center; gap: 6px; }
        .khatam-count-badge {
            background: #ede9fe;
            color: #7c3aed;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 999px;
        }
        .khatam-panel {
            margin-top: 8px;
            padding: 10px;
            background: #f8fafc;
            border: 1px solid #eef2f7;
            border-radius: 10px;
            min-width: 220px;
        }
        .khatam-list { list-style: none; margin: 0 0 8px; padding: 0; display: flex; flex-direction: column; gap: 4px; }
        .khatam-list li { display: flex; align-items: center; gap: 6px; font-size: 0.8rem; }
        .khatam-list-date { font-weight: 600; color: #334155; }
        .khatam-note {
            color: #94a3b8;
            font-style: italic;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .khatam-delete-btn {
            background: none;
            border: none;
            color: #dc2626;
            cursor: pointer;
            font-size: 1rem;
            line-height: 1;
            padding: 0 2px;
        }
        .khatam-notes-input {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 4px 6px;
            font-size: 0.8rem;
            color: #334155;
            width: 110px;
        }
        .tamat-date-value { font-size: 0.85rem; color: #334155; }
        .tamat-date-value--empty { color: #94a3b8; font-style: italic; }
        .tamat-inline-form { display: flex; align-items: center; gap: 6px; }
        .tamat-date-input {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 4px 6px;
            font-size: 0.8rem;
            color: #334155;
        }
        .tamat-edit-btn, .tamat-save-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .tamat-edit-btn:hover { background: #f1f5f9; }
        .tamat-save-btn { color: #16a34a; }
    </style>
@endpush

@section('content')
    <div class="users-page">

        {{-- Header --}}
        <div class="users-page__header">
            <div>
                <h1 class="users-page__title">My Students</h1>
                <p class="text-sm text-slate-400 mt-0.5">Students under your supervision</p>
            </div>
        </div>

        <script>
            function toggleKhatamPanel(index) {
                const panel = document.getElementById('khatam-panel-' + index);
                panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
            }
        </script>

        @if(session('success'))
            <div class="rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="users-table-card">

            {{-- Search --}}
            <form method="GET" action="{{ route('lecturer.students') }}"
                style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; display:flex; gap:12px; align-items:center;">
                <div class="search-row" style="margin-bottom:0;">
                    <div class="users-search">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, ID, or email…">
                    </div>
                </div>
                <button type="submit" class="users-button users-button--primary">Search</button>
                @if($search)
                    <a href="{{ route('lecturer.students') }}" class="users-button users-button--secondary">Clear</a>
                @endif
                <span class="text-sm text-slate-400" style="margin-left:auto;">
                    {{ $students->count() }} {{ Str::plural('student', $students->count()) }}
                    @if($search) matching &ldquo;{{ $search }}&rdquo; @endif
                </span>
            </form>

            {{-- TABLE --}}
            <div class="users-table-wrap">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th style="width: 70px;">NO</th>
                            <th>STUDENT</th>
                            <th>STUDENT ID</th>
                            <th>BATCH</th>
                            <th>STATUS</th>
                            <th>KHATAM</th>
                            <th style="text-align: center;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $index => $student)
                            <tr>
                                <td class="users-table__muted">{{ $index + 1 }}</td>

                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="font-weight: 400; color: #1e293b;">{{ $student->student_name }}</span>
                                        @if(strtoupper($student->gender) === 'LELAKI')
                                            <span class="gender-tag gender-tag--m">M</span>
                                        @elseif(strtoupper($student->gender) === 'PEREMPUAN')
                                            <span class="gender-tag gender-tag--f">F</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="users-table__muted">{{ $student->student_id }}</td>

                                <td>
                                    <span style="background:#f1f5f9;padding:4px 10px;border-radius:6px;font-size:0.75rem;color:#475569;">
                                        {{ trim($student->batch->batch_name ?? 'N/A') }}
                                    </span>
                                </td>

                                <td>
                                    @php
                                        $statusClass = match ($student->status) {
                                            'AKTIF' => 'status-active',
                                            'TAMAT PENSIJILAN' => 'status-completed',
                                            'GAGAL DAN DIBERHENTIKAN', 'TARIK DIRI' => 'status-dropped',
                                            default => 'bg-gray-200 text-gray-800',
                                        };
                                    @endphp
                                    <span class="status-badge {{ $statusClass }}">{{ $student->status }}</span>
                                </td>

                                {{-- KHATAM --}}
                                <td>
                                    <div class="khatam-cell">
                                        <div class="tamat-display">
                                            @if ($student->khatams->isNotEmpty())
                                                <span class="khatam-count-badge">{{ $student->khatams->count() }}×</span>
                                                <span class="tamat-date-value">
                                                    Latest: {{ $student->khatams->first()->khatam_date->format('d M Y') }}
                                                </span>
                                            @else
                                                <span class="tamat-date-value tamat-date-value--empty">Not Recorded</span>
                                            @endif

                                            <button type="button" class="tamat-edit-btn" title="Manage Khatam records"
                                                onclick="toggleKhatamPanel({{ $index }})">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                    viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <div class="khatam-panel" id="khatam-panel-{{ $index }}" style="display:none;">
                                            @if ($student->khatams->isNotEmpty())
                                                <ul class="khatam-list">
                                                    @foreach ($student->khatams as $khatam)
                                                        <li>
                                                            <span class="khatam-list-date">{{ $khatam->khatam_date->format('d M Y') }}</span>
                                                            @if ($khatam->notes)
                                                                <span class="khatam-note">{{ $khatam->notes }}</span>
                                                            @else
                                                                <span class="khatam-note"></span>
                                                            @endif
                                                            <form action="{{ route('lecturer.students.khatam.destroy', [$student->student_id, $khatam->id]) }}"
                                                                method="POST" onsubmit="return confirm('Remove this Khatam record?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="khatam-delete-btn" title="Remove">&times;</button>
                                                            </form>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif

                                            <form action="{{ route('lecturer.students.khatam.store', $student->student_id) }}"
                                                method="POST" class="tamat-inline-form">
                                                @csrf
                                                <input type="date" name="khatam_date" required class="tamat-date-input">
                                                <input type="text" name="notes" placeholder="Notes (optional)"
                                                    class="khatam-notes-input" maxlength="255">
                                                <button type="submit" class="tamat-save-btn" title="Add Khatam date">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>

                                {{-- ACTIONS --}}
                                <td>
                                    <div style="display: flex; align-items: center; justify-content: center;">
                                        <a href="{{ route('lecturer.students.show', $student->student_id) }}" title="View">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem; color: #94a3b8;">
                                    No students found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
