{{-- show.blade.php --}}
@extends('layouts.admin')

@section('title', $student->student_name)

@section('content')
<div class="space-y-6 p-4 md:p-6 lg:p-8">

    {{-- Back --}}
    @include('partials.back-button', ['href' => route('admin.masterlist'), 'label' => 'Masterlist'])

    {{-- Profile header card --}}
    <div class="rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
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
                    <h1 class="text-3xl font-bold tracking-tight text-slate-800" style="letter-spacing:-0.02em;">{{ $student->student_name }}</h1>
                    <p class="mt-0.5 text-sm text-slate-400">{{ $student->student_id }}</p>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-semibold text-green-700">
                            {{ $student->status ?: 'Active' }}
                        </span>

                        @if(strtoupper($student->gender) === 'LELAKI')
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600">Male</span>
                        @elseif(strtoupper($student->gender) === 'PEREMPUAN')
                            <span class="rounded-full bg-pink-50 px-3 py-1 text-xs font-semibold text-pink-600">Female</span>
                        @endif

                        @php
                            $programLabel = $student->program?->program_level ?? optional($student->activeProgram->first())->program_name;
                        @endphp

                        @if($programLabel)
                            <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">
                                {{ $programLabel }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.masterlist.edit', $student->student_id) }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700">
                    Edit Record
                </a>
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
                    <dd class="text-sm font-medium text-slate-800">{{ $student->program?->program_name ?: optional($student->activeProgram->first())->program_name ?: '—' }}</dd>
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

        {{-- Additional Details --}}
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

        {{-- Lecturer Notes (read-only) --}}
        <div class="lg:col-span-2 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-widest text-slate-400">Lecturer Notes</h2>

            @if($student->notes->isEmpty())
                <p class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">No notes recorded yet.</p>
            @else
                <div class="space-y-3">
                    @foreach($student->notes as $note)
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <p class="text-sm text-slate-700 whitespace-pre-line">{{ $note->body }}</p>
                            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-400">
                                <span class="font-semibold text-slate-500">{{ $note->lecturer?->lecturer_name ?? 'Unknown lecturer' }}</span>
                                <span>&middot;</span>
                                <span>{{ $note->created_at->format('d M Y, g:i A') }}</span>
                                @if($note->is_private)
                                    <span class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-600">Private (not visible to student)</span>
                                @else
                                    <span class="rounded-full bg-green-50 px-2 py-0.5 text-[11px] font-semibold text-green-600">Visible to student</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- CGPA Trend --}}
        <div class="lg:col-span-2 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-widest text-slate-400">CGPA Trend</h2>
                    <p class="text-sm text-slate-500">Semester-by-semester CGPA progression</p>
                </div>
            </div>
            @if($student->results->isNotEmpty())
                <div class="h-72">
                    <canvas id="adminCgpaTrendChart"></canvas>
                </div>
            @else
                <p class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">No CGPA history is available yet.</p>
            @endif
        </div>

        {{-- Academic Results --}}
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
</div>

@php
    $trendResults = $student->results->sortBy(fn($result) => $result->semester?->semester_id ?? 0)
        ->values()
        ->map(function($result, $key) {
            return [
                'label' => $result->semester?->semester_name ?? 'Sem ' . ($key + 1),
                'value' => $result->cgpa !== null ? (float) $result->cgpa : null,
            ];
        })->toArray();
@endphp

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('adminCgpaTrendChart');
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
@endpush
@endsection
