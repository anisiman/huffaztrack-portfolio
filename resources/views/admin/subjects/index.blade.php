@extends('layouts.admin')

@section('title', 'Subject Management')

@section('content')
<div class="max-w-6xl mx-auto" x-data="{ showCreate: false, editing: null }">

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

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm font-semibold shadow-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Subject Management</h1>
            <p class="text-sm text-slate-500 mt-1">View and manage subjects, marks, and lecturer assignments for each academic semester.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.sections', ['academic_period_id' => $period?->id]) }}"
                class="px-4 py-2.5 bg-white border border-slate-200 hover:border-violet-300 text-slate-700 text-sm font-semibold rounded-xl transition">
                Manage Sections
            </a>

            <button type="button" @click="showCreate = true"
                class="px-4 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                + Add Subject
            </button>

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
    </div>

    @if (! $period)
        <div class="p-6 bg-amber-50 border border-amber-200 text-amber-800 rounded-2xl text-sm font-semibold">
            No Academic Semester selected or active. Create/activate one under Academic Semesters first.
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3 text-left font-bold text-slate-500 uppercase text-[11px] tracking-wider">Subject</th>
                        <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Type</th>
                        <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Credit Hrs</th>
                        <th class="px-5 py-3 text-left font-bold text-slate-500 uppercase text-[11px] tracking-wider">Ownership</th>
                        <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Marks Entered</th>
                        <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Action</th>
                        <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Edit</th>
                    </tr>
                </thead>
                <tbody>
                    @php $lastLevel = null; @endphp
                    @forelse ($subjects as $subject)
                        @if ($lastLevel !== $subject->semester_id)
                            @php $lastLevel = $subject->semester_id; @endphp
                            <tr class="bg-violet-50/60">
                                <td colspan="7" class="px-5 py-2 text-xs font-bold text-violet-700 uppercase tracking-wider">
                                    {{ $subject->semester?->semester_name ?? 'Semester Level ' . $subject->semester_id }}
                                </td>
                            </tr>
                        @endif
                        <tr class="border-b border-slate-50 hover:bg-slate-50/50">
                            <td class="px-5 py-3">
                                <p class="font-semibold text-slate-800">{{ $subject->subjectT_name }}</p>
                                <p class="text-xs text-slate-400">{{ $subject->subjectT_code }}</p>
                            </td>
                            <td class="px-5 py-3 text-center">
                                @if ($subject->isHafazan())
                                    <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold">Hafazan</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-violet-50 text-violet-700 text-xs font-semibold">Madah</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center text-slate-600">{{ $subject->credit_hours }}</td>
                            <td class="px-5 py-3">
                                @if ($subject->isHafazan())
                                    <a href="{{ route('admin.lecturers') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 hover:text-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Managed via Halaqah
                                    </a>
                                @else
                                    @php $summary = $sectionSummaries->get($subject->subjectT_id); @endphp
                                    @if (! $summary || $summary['sections'] === 0)
                                        <a href="{{ route('admin.sections.manage', ['subject' => $subject->subjectT_id, 'academic_period_id' => $period->id]) }}"
                                            class="text-xs font-semibold text-slate-400 hover:text-violet-600">
                                            No sections yet
                                        </a>
                                    @else
                                        <a href="{{ route('admin.sections.manage', ['subject' => $subject->subjectT_id, 'academic_period_id' => $period->id]) }}"
                                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-violet-700 hover:text-violet-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span>
                                            {{ $summary['sections'] }} {{ Str::plural('Section', $summary['sections']) }}
                                            &middot; {{ $summary['lecturers'] }} {{ Str::plural('Lecturer', $summary['lecturers']) }}
                                        </a>
                                    @endif
                                @endif
                            </td>
                            <td class="px-5 py-3 text-center text-slate-600">
                                {{ $marksEnteredCounts->get($subject->subjectT_id, 0) }} / {{ $eligibleCounts->get($subject->subjectT_id, 0) }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.subjects.marks', ['subject' => $subject->subjectT_id, 'academic_period_id' => $period->id]) }}"
                                        class="px-3 py-1.5 bg-violet-50 hover:bg-violet-600 text-violet-700 hover:text-white text-xs font-bold rounded-lg transition border border-violet-100">
                                        View Marks
                                    </a>
                                    @if ($subject->isMadah())
                                        <a href="{{ route('admin.sections.manage', ['subject' => $subject->subjectT_id, 'academic_period_id' => $period->id]) }}"
                                            class="px-3 py-1.5 bg-slate-50 hover:bg-slate-600 text-slate-600 hover:text-white text-xs font-bold rounded-lg transition border border-slate-100">
                                            Sections
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button"
                                        @click="editing = {
                                            id: {{ $subject->subjectT_id }},
                                            subjectT_name: @js($subject->subjectT_name),
                                            subjectT_code: @js($subject->subjectT_code),
                                            type: @js($subject->type),
                                            credit_hours: @js($subject->credit_hours),
                                            semester_id: {{ $subject->semester_id ?? 'null' }}
                                        }"
                                        class="px-2.5 py-1.5 text-slate-500 hover:text-violet-600 hover:bg-violet-50 rounded-lg transition text-xs font-semibold">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.subjects.destroy', $subject->subjectT_id) }}" method="POST"
                                        onsubmit="return confirm('Delete this subject? This is blocked if it has sections, enrollments, marks, or attendance history.');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="academic_period_id" value="{{ $period->id }}">
                                        <button type="submit" class="px-2.5 py-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition text-xs font-semibold">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-slate-400">No subjects found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    {{-- Create Subject modal --}}
    <div x-show="showCreate" x-cloak style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4">
        <div @click.outside="showCreate = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Add Subject</h2>
            <form action="{{ route('admin.subjects.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="academic_period_id" value="{{ $period?->id }}">
                <div>
                    <label class="text-xs font-semibold text-slate-500 uppercase">Subject Name</label>
                    <input type="text" name="subjectT_name" required
                        class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500 uppercase">Subject Code</label>
                    <input type="text" name="subjectT_code"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500 uppercase mb-1 block">Type</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 border border-slate-200 rounded-xl px-3 py-2.5 text-sm cursor-pointer has-[:checked]:border-violet-400 has-[:checked]:bg-violet-50">
                            <input type="radio" name="type" value="madah" checked class="text-violet-600 focus:ring-violet-500">
                            Madah <span class="text-slate-400 text-xs">(sections)</span>
                        </label>
                        <label class="flex items-center gap-2 border border-slate-200 rounded-xl px-3 py-2.5 text-sm cursor-pointer has-[:checked]:border-violet-400 has-[:checked]:bg-violet-50">
                            <input type="radio" name="type" value="hafazan" class="text-violet-600 focus:ring-violet-500">
                            Hafazan <span class="text-slate-400 text-xs">(Halaqah)</span>
                        </label>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-500 uppercase">Credit Hours</label>
                        <input type="number" min="0" name="credit_hours"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 uppercase">Semester Level</label>
                        <select name="semester_id" required
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none bg-white">
                            @foreach ($semesters as $semester)
                                <option value="{{ $semester->semester_id }}">{{ $semester->semester_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showCreate = false" class="px-4 py-2 text-sm font-semibold text-slate-500 hover:text-slate-700">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold rounded-xl">Create Subject</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Subject modal --}}
    <div x-show="editing" x-cloak style="display:none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4">
        <div @click.outside="editing = null" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6" x-show="editing">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Edit Subject</h2>
            <form :action="editing ? '{{ url('admin/subjects') }}/' + editing.id : '#'" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="academic_period_id" value="{{ $period?->id }}">
                <div>
                    <label class="text-xs font-semibold text-slate-500 uppercase">Subject Name</label>
                    <input type="text" name="subjectT_name" required x-model="editing.subjectT_name"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500 uppercase">Subject Code</label>
                    <input type="text" name="subjectT_code" x-model="editing.subjectT_code"
                        class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500 uppercase mb-1 block">Type</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 border border-slate-200 rounded-xl px-3 py-2.5 text-sm cursor-pointer has-[:checked]:border-violet-400 has-[:checked]:bg-violet-50">
                            <input type="radio" name="type" value="madah" x-model="editing.type" class="text-violet-600 focus:ring-violet-500">
                            Madah <span class="text-slate-400 text-xs">(sections)</span>
                        </label>
                        <label class="flex items-center gap-2 border border-slate-200 rounded-xl px-3 py-2.5 text-sm cursor-pointer has-[:checked]:border-violet-400 has-[:checked]:bg-violet-50">
                            <input type="radio" name="type" value="hafazan" x-model="editing.type" class="text-violet-600 focus:ring-violet-500">
                            Hafazan <span class="text-slate-400 text-xs">(Halaqah)</span>
                        </label>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-500 uppercase">Credit Hours</label>
                        <input type="number" min="0" name="credit_hours" x-model="editing.credit_hours"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-500 uppercase">Semester Level</label>
                        <select name="semester_id" required x-model="editing.semester_id"
                            class="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none bg-white">
                            @foreach ($semesters as $semester)
                                <option value="{{ $semester->semester_id }}">{{ $semester->semester_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="editing = null" class="px-4 py-2 text-sm font-semibold text-slate-500 hover:text-slate-700">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold rounded-xl">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
