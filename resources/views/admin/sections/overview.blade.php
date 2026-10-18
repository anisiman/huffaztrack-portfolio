@extends('layouts.admin')

@section('title', 'Subject Sections')

@section('content')
<div class="max-w-6xl mx-auto">

    @if (session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-semibold shadow-sm flex items-center gap-2">
            <span>✅</span> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm font-semibold shadow-sm flex items-center gap-2">
            <span>⚠️</span> {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Subject Sections</h1>
            <p class="text-sm text-slate-500 mt-1">Section capacity, assigned lecturers, and student enrollment for Madah/Academic subjects — by Academic Semester. Hafazan subjects use the existing Halaqah assignment instead.</p>
        </div>

        <form method="GET" class="flex items-center gap-2">
            <label class="text-xs font-semibold text-slate-500 uppercase">Semester</label>
            <select name="academic_period_id" onchange="this.form.submit()"
                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none bg-white">
                @forelse ($academicPeriods as $ap)
                    <option value="{{ $ap->id }}" {{ $period && $period->id === $ap->id ? 'selected' : '' }}>
                        {{ $ap->name }} @if($ap->status === 'active') (active) @endif
                    </option>
                @empty
                    <option value="">No academic semesters</option>
                @endforelse
            </select>
        </form>
    </div>

    @if (! $period)
        <div class="p-6 bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl text-sm font-semibold">
            No Academic Semester selected or active. Create/activate one under Academic Semesters first.
        </div>
    @else
        <div class="mb-6 p-5 bg-white rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-bold text-slate-800">Student Section Enrollment</p>
                <p class="text-xs text-slate-500 mt-0.5">
                    When closed, students can no longer choose or change their section. Admin can always manage enrollments.
                </p>
            </div>
            <form action="{{ route('admin.sections.enrollmentToggle', $period->id) }}" method="POST">
                @csrf
                @method('PUT')
                <button type="submit"
                    class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wide transition
                        {{ $period->section_enrollment_open ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200' }}">
                    {{ $period->section_enrollment_open ? 'Open — click to close' : 'Closed — click to open' }}
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3 text-left font-bold text-slate-500 uppercase text-[11px] tracking-wider">Subject</th>
                        <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Sections</th>
                        <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Capacity</th>
                        <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Enrolled</th>
                        <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Remaining</th>
                        <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $lastLevel = null; @endphp
                    @forelse ($subjects as $subject)
                        @if ($lastLevel !== $subject->semester_id)
                            @php $lastLevel = $subject->semester_id; @endphp
                            <tr class="bg-violet-50/60">
                                <td colspan="6" class="px-5 py-2 text-xs font-bold text-violet-700 uppercase tracking-wider">
                                    {{ $subject->semester?->semester_name ?? 'Semester Level ' . $subject->semester_id }}
                                </td>
                            </tr>
                        @endif
                        @php $stats = $sectionStats->get($subject->subjectT_id, ['sections' => 0, 'capacity' => 0, 'enrolled' => 0]); @endphp
                        <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                            <td class="px-5 py-3">
                                <p class="font-semibold text-slate-800">{{ $subject->subjectT_name }}</p>
                                <p class="text-xs text-slate-400">{{ $subject->subjectT_code }}</p>
                            </td>
                            <td class="px-5 py-3 text-center text-slate-600">{{ $stats['sections'] }}</td>
                            <td class="px-5 py-3 text-center text-slate-600">{{ $stats['capacity'] }}</td>
                            <td class="px-5 py-3 text-center text-slate-600">{{ $stats['enrolled'] }}</td>
                            <td class="px-5 py-3 text-center font-semibold {{ ($stats['capacity'] - $stats['enrolled']) <= 0 && $stats['sections'] > 0 ? 'text-red-500' : 'text-emerald-600' }}">
                                {{ max(0, $stats['capacity'] - $stats['enrolled']) }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                <a href="{{ route('admin.sections.manage', ['subject' => $subject->subjectT_id, 'academic_period_id' => $period->id]) }}"
                                    class="px-3 py-1.5 bg-violet-50 hover:bg-violet-600 text-violet-700 hover:text-white text-xs font-bold rounded-lg transition border border-violet-100">
                                    Manage Sections
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-slate-400">No subjects found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
