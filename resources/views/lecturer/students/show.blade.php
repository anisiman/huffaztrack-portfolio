@extends('layouts/lecturer')

@section('title', $student->student_name)

@section('content')
<div class="space-y-6">

    {{-- Back --}}
    @include('partials.back-button', ['href' => route('lecturer.students'), 'label' => 'Students'])

    {{-- Profile header card --}}
    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">

            {{-- Avatar --}}
            @if($student->user?->profile_picture)
                <div class="h-20 w-20 overflow-hidden rounded-full bg-slate-100">
                    <img src="{{ asset('storage/' . $student->user->profile_picture) }}" alt="{{ $student->student_name }}" class="h-full w-full object-cover">
                </div>
            @else
                <div class="flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-full bg-violet-100 text-3xl font-bold text-violet-700">
                    {{ strtoupper(substr($student->student_name, 0, 1)) }}
                </div>
            @endif

            {{-- Name & ID --}}
            <div class="flex-1">
                <h1 class="text-4xl font-bold tracking-tight text-black" style="letter-spacing:-0.02em;">{{ $student->student_name }}</h1>
                <p class="mt-0.5 text-sm text-slate-400">{{ $student->student_id }}</p>

                <div class="mt-3 flex flex-wrap gap-2">
                    {{-- Status --}}
                    <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                        Active
                    </span>

                    {{-- Gender --}}
                    @if(strtoupper($student->gender) === 'LELAKI')
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">Male</span>
                    @elseif(strtoupper($student->gender) === 'PEREMPUAN')
                        <span class="rounded-full bg-pink-50 px-3 py-1 text-xs font-semibold text-pink-600">Female</span>
                    @endif

                    {{-- Program level --}}
                    @if($student->program)
                        <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">
                            {{ $student->program->program_level ?? $student->program->program_name }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Detail sections --}}
    <div class="grid gap-5 lg:grid-cols-2">

        {{-- Personal Information --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-widest text-slate-400">Personal Information</h2>
            <dl class="space-y-4">

                <div class="flex flex-col gap-0.5">
                    <dt class="text-xs font-semibold text-slate-400">Full Name</dt>
                    <dd class="text-sm font-medium text-slate-800">{{ $student->student_name ?: '—' }}</dd>
                </div>

                <div class="flex flex-col gap-0.5">
                    <dt class="text-xs font-semibold text-slate-400">Student ID</dt>
                    <dd class="text-sm font-medium text-slate-800">{{ $student->student_id ?: '—' }}</dd>
                </div>

                <div class="flex flex-col gap-0.5">
                    <dt class="text-xs font-semibold text-slate-400">NRIC</dt>
                    <dd class="text-sm font-medium text-slate-800">{{ $student->nric ?: '—' }}</dd>
                </div>

                <div class="flex flex-col gap-0.5">
                    <dt class="text-xs font-semibold text-slate-400">Gender</dt>
                    <dd class="text-sm font-medium text-slate-800">
                        @if(strtoupper($student->gender) === 'LELAKI') Male
                        @elseif(strtoupper($student->gender) === 'PEREMPUAN') Female
                        @else {{ $student->gender ?: '—' }}
                        @endif
                    </dd>
                </div>

                <div class="flex flex-col gap-0.5">
                    <dt class="text-xs font-semibold text-slate-400">Race</dt>
                    <dd class="text-sm font-medium text-slate-800">{{ $student->race ?: '—' }}</dd>
                </div>

            </dl>
        </div>

        {{-- Contact Information --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-widest text-slate-400">Contact Information</h2>
            <dl class="space-y-4">

                <div class="flex flex-col gap-0.5">
                    <dt class="text-xs font-semibold text-slate-400">Email</dt>
                    <dd class="text-sm font-medium text-slate-800">{{ $student->email ?: '—' }}</dd>
                </div>

                <div class="flex flex-col gap-0.5">
                    <dt class="text-xs font-semibold text-slate-400">Phone</dt>
                    <dd class="text-sm font-medium text-slate-800">{{ $student->phone ?: '—' }}</dd>
                </div>

                <div class="flex flex-col gap-0.5">
                    <dt class="text-xs font-semibold text-slate-400">Address</dt>
                    <dd class="text-sm font-medium text-slate-800 leading-relaxed">
                        @php
                            $parts = array_filter([
                                $student->address,
                                $student->postcode && $student->town ? $student->postcode . ' ' . $student->town : ($student->town ?: $student->postcode),
                                $student->state,
                            ]);
                        @endphp
                        {{ $parts ? implode(', ', $parts) : '—' }}
                    </dd>
                </div>

            </dl>
        </div>

        {{-- Academic Information --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-widest text-slate-400">Academic Information</h2>
            <dl class="space-y-4">

                <div class="flex flex-col gap-0.5">
                    <dt class="text-xs font-semibold text-slate-400">Program</dt>
                    <dd class="text-sm font-medium text-slate-800">{{ $student->program?->program_name ?: '—' }}</dd>
                </div>

                <div class="flex flex-col gap-0.5">
                    <dt class="text-xs font-semibold text-slate-400">Level</dt>
                    <dd class="text-sm font-medium text-slate-800">{{ $student->program?->program_level ?: '—' }}</dd>
                </div>

                <div class="flex flex-col gap-0.5">
                    <dt class="text-xs font-semibold text-slate-400">Batch / Intake</dt>
                    <dd class="text-sm font-medium text-slate-800">
                        {{ $student->batch?->batch_name ?: '—' }}
                        @if($student->batch?->intake)
                            <span class="text-slate-400">({{ $student->batch->intake }})</span>
                        @endif
                    </dd>
                </div>

                <div class="flex flex-col gap-0.5">
                    <dt class="text-xs font-semibold text-slate-400">Secondary School</dt>
                    <dd class="text-sm font-medium text-slate-800">{{ $student->secondary_school ?: '—' }}</dd>
                </div>

                @if($student->sponsorship)
                <div class="flex flex-col gap-0.5">
                    <dt class="text-xs font-semibold text-slate-400">Sponsorship</dt>
                    <dd class="text-sm font-medium text-slate-800">{{ $student->sponsorship->sponsorship_name ?? '—' }}</dd>
                </div>
                @endif

            </dl>
        </div>

        
        

        @if($student->asasi_diploma_id || $student->ijazah_id || $student->remarks)
        <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-widest text-slate-400">Additional Details</h2>
            <dl class="space-y-4">

                @if($student->asasi_diploma_id)
                <div class="flex flex-col gap-0.5">
                    <dt class="text-xs font-semibold text-slate-400">Asasi / Diploma ID</dt>
                    <dd class="text-sm font-medium text-slate-800">{{ $student->asasi_diploma_id }}</dd>
                </div>
                @endif

                @if($student->ijazah_id)
                <div class="flex flex-col gap-0.5">
                    <dt class="text-xs font-semibold text-slate-400">Ijazah ID</dt>
                    <dd class="text-sm font-medium text-slate-800">{{ $student->ijazah_id }}</dd>
                </div>
                @endif

                @if($student->remarks)
                <div class="flex flex-col gap-0.5">
                    <dt class="text-xs font-semibold text-slate-400">Remarks</dt>
                    <dd class="text-sm font-medium text-slate-800">{{ $student->remarks }}</dd>
                </div>
                @endif

            </dl>
        </div>
        @endif

        {{-- Lecturer Notes --}}
        <div class="lg:col-span-2 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-widest text-slate-400">Lecturer Notes</h2>

            @if(session('success'))
                <div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-2.5 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="space-y-3 mb-5">
                @forelse($student->notes as $note)
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4" id="note-{{ $note->id }}">
                        <div id="note-view-{{ $note->id }}" class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-slate-700 whitespace-pre-line note-body-text">{{ $note->body }}</p>
                                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-400">
                                    <span>{{ $note->lecturer?->lecturer_name ?? 'You' }}</span>
                                    <span>&middot;</span>
                                    <span>{{ $note->created_at->format('d M Y, g:i A') }}</span>
                                    @if($note->is_private)
                                        <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-600">Private (not visible to student)</span>
                                    @endif
                                </div>
                            </div>

                            @if((string) $note->lecturer_id === (string) $lecturerId)
                                <div class="flex flex-shrink-0 items-center gap-1">
                                    <button type="button" class="rounded-lg p-1.5 text-slate-400 hover:bg-white hover:text-violet-600" title="Edit"
                                        onclick="document.getElementById('note-view-{{ $note->id }}').classList.add('hidden'); document.getElementById('note-edit-{{ $note->id }}').classList.remove('hidden');">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>
                                    <form action="{{ route('lecturer.students.notes.destroy', [$student->student_id, $note->id]) }}" method="POST" onsubmit="return confirm('Delete this note?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg p-1.5 text-slate-400 hover:bg-white hover:text-red-600" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        @if((string) $note->lecturer_id === (string) $lecturerId)
                            <form id="note-edit-{{ $note->id }}" action="{{ route('lecturer.students.notes.update', [$student->student_id, $note->id]) }}" method="POST" class="hidden mt-3 space-y-2">
                                @csrf
                                @method('PUT')
                                <textarea name="body" rows="3" required maxlength="2000"
                                    class="w-full rounded-xl border border-slate-200 p-2.5 text-sm text-slate-700 focus:border-violet-400 focus:outline-none focus:ring-2 focus:ring-violet-100">{{ $note->body }}</textarea>
                                <label class="flex items-center gap-2 text-xs text-slate-500">
                                    <input type="checkbox" name="is_private" value="1" {{ $note->is_private ? 'checked' : '' }}>
                                    Keep private (don't show to student)
                                </label>
                                <div class="flex gap-2">
                                    <button type="submit" class="rounded-xl bg-violet-700 px-4 py-1.5 text-xs font-semibold text-white hover:bg-violet-800">Save</button>
                                    <button type="button" class="rounded-xl border border-slate-200 px-4 py-1.5 text-xs font-semibold text-slate-500 hover:bg-slate-50"
                                        onclick="document.getElementById('note-edit-{{ $note->id }}').classList.add('hidden'); document.getElementById('note-view-{{ $note->id }}').classList.remove('hidden');">Cancel</button>
                                </div>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">No notes yet.</p>
                @endforelse
            </div>

            <form action="{{ route('lecturer.students.notes.store', $student->student_id) }}" method="POST" class="space-y-2">
                @csrf
                <textarea name="body" rows="3" required maxlength="2000" placeholder="Write a note about this student…"
                    class="w-full rounded-xl border border-slate-200 p-2.5 text-sm text-slate-700 placeholder-slate-400 focus:border-violet-400 focus:outline-none focus:ring-2 focus:ring-violet-100"></textarea>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-xs text-slate-500">
                        <input type="checkbox" name="is_private" value="1">
                        Keep private (don't show to student)
                    </label>
                    <button type="submit" class="rounded-xl bg-violet-700 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-800">
                        Add Note
                    </button>
                </div>
            </form>
        </div>

        <div class="lg:col-span-2 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-widest text-slate-400">CGPA Trend</h2>
                    <p class="text-sm text-slate-500">Semester-by-semester CGPA progression</p>
                </div>
            </div>
            @if($student->results->isNotEmpty())
                <div class="h-72">
                    <canvas id="lecturerCgpaTrendChart"></canvas>
                </div>
            @else
                <p class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">No CGPA history is available yet.</p>
            @endif
        </div>

        <div class="lg:col-span-2 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-widest text-slate-400">Academic Results</h2>

            @if($student->results->isNotEmpty())
                @foreach($student->results->sortBy(fn($result) => $result->semester?->semester_id ?? 0) as $result)
                    <div class="mb-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-600">Semester</p>
                                <p class="text-lg font-bold text-slate-900">{{ $result->semester?->semester_name ?: 'Semester ' . $result->semester_id }}</p>
                            </div>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <div class="rounded-2xl bg-white p-3 text-center shadow-sm">
                                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400">GPA</p>
                                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ $result->gpa ?? '—' }}</p>
                                </div>
                                <div class="rounded-2xl bg-white p-3 text-center shadow-sm">
                                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400">CGPA</p>
                                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ $result->cgpa ?? '—' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            @if($result->subjects->isNotEmpty())
                                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                                    <thead class="bg-slate-100 text-slate-500">
                                        <tr>
                                            <th class="px-4 py-3 font-semibold">Subject</th>
                                            <th class="px-4 py-3 font-semibold">Grade</th>
                                            <th class="px-4 py-3 font-semibold">Grade Point</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 bg-white">
                                        @foreach($result->subjects as $subject)
                                            <tr>
                                                <td class="px-4 py-3">{{ $subject->subjectT?->subjectT_name ?: '—' }}</td>
                                                <td class="px-4 py-3">{{ $subject->grade ?: '—' }}</td>
                                                <td class="px-4 py-3">{{ $subject->grade_point ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="rounded-2xl bg-white p-4 text-sm text-slate-500">No subject grades recorded for this semester.</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <p class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">Academic results are not available yet.</p>
            @endif
        </div>

    </div>
@endsection

@php
    $trendResults = $student->results->sortBy(fn($r) => $r->semester?->semester_id ?? 0)
        ->values()
        ->map(function($result, $key) {
            return [
                'label' => $result->semester?->semester_name ?? 'Sem ' . ($key + 1),
                'value' => $result->cgpa !== null ? (float) $result->cgpa : null,
            ];
        })->toArray();
@endphp

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('lecturerCgpaTrendChart');
            if (!ctx) return;

            const trendResults = @json($trendResults);

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trendResults.map(item => item.label),
                    datasets: [{
                        label: 'CGPA',
                        data: trendResults.map(item => item.value),
                        borderColor: '#7c3aed',
                        backgroundColor: 'rgba(124, 58, 237, 0.16)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointBackgroundColor: '#7c3aed',
                        borderWidth: 2,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 4,
                            ticks: { stepSize: 0.5 },
                        },
                    },
                    plugins: {
                        legend: { display: false },
                    },
                },
            });
        });
    </script>
@endsection
