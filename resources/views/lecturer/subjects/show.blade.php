@extends('layouts.lecturer')

@section('title', $subject->subjectT_name)

@section('content')
<div class="max-w-4xl mx-auto">

    <a href="{{ route('lecturer.subjects') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-violet-600 mb-4">
        &larr; Back to My Subjects
    </a>

    @if (session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-semibold shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6">
        <p class="text-xs font-bold text-violet-600 uppercase tracking-wider mb-1">
            {{ $subject->semester?->semester_name ?? 'Semester Level ' . $subject->semester_id }} &middot; {{ $period->name }}
        </p>
        <h1 class="text-2xl font-bold text-slate-800">{{ $subject->subjectT_name }}</h1>
        <p class="text-sm text-slate-500 mt-1">{{ $subject->subjectT_code }} &middot; {{ $subject->credit_hours }} credit hours</p>
    </div>

    <form action="{{ route('lecturer.subjects.marks.store', $subject->subjectT_id) }}" method="POST">
        @csrf
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-4">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3 text-left font-bold text-slate-500 uppercase text-[11px] tracking-wider">Student</th>
                        <th class="px-5 py-3 text-left font-bold text-slate-500 uppercase text-[11px] tracking-wider">Batch</th>
                        <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Carry Marks 1</th>
                        <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Carry Marks 2</th>
                        <th class="px-5 py-3 text-center font-bold text-slate-500 uppercase text-[11px] tracking-wider">Total Carry Marks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $row)
                        @php $sid = $row['student']->student_id; @endphp
                        <tr class="border-b border-slate-50">
                            <td class="px-5 py-3">
                                <p class="font-semibold text-slate-800">{{ $row['student']->student_name }}</p>
                                <p class="text-xs text-slate-400">{{ $sid }}</p>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $row['student']->batch?->batch_name ?? '—' }}</td>
                            <td class="px-5 py-3 text-center">
                                <input type="number" step="0.01" min="0" max="100"
                                    name="rows[{{ $sid }}][carry_mark_1]"
                                    value="{{ $row['carry_mark_1'] }}"
                                    class="w-24 text-center rounded-lg border border-slate-200 px-2 py-1.5 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none carry-input"
                                    data-row="{{ $sid }}" data-part="1">
                            </td>
                            <td class="px-5 py-3 text-center">
                                <input type="number" step="0.01" min="0" max="100"
                                    name="rows[{{ $sid }}][carry_mark_2]"
                                    value="{{ $row['carry_mark_2'] }}"
                                    class="w-24 text-center rounded-lg border border-slate-200 px-2 py-1.5 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none carry-input"
                                    data-row="{{ $sid }}" data-part="2">
                            </td>
                            <td class="px-5 py-3 text-center font-bold text-violet-700 total-cell" data-row="{{ $sid }}">
                                {{ (is_numeric($row['carry_mark_1']) || is_numeric($row['carry_mark_2'])) ? number_format((float) $row['carry_mark_1'] + (float) $row['carry_mark_2'], 2) : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-slate-400">
                                No students are currently mapped to this subject's Semester Level for this Academic Semester.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($students->isNotEmpty())
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-violet-600 text-white rounded-xl font-bold hover:bg-violet-700 transition">
                    Save All Marks
                </button>
            </div>
        @endif
    </form>
</div>
@endsection

@section('scripts')
<script>
    function recalcTotal(studentId) {
        const cm1Input = document.querySelector(`.carry-input[data-row="${studentId}"][data-part="1"]`);
        const cm2Input = document.querySelector(`.carry-input[data-row="${studentId}"][data-part="2"]`);
        const totalCell = document.querySelector(`.total-cell[data-row="${studentId}"]`);
        if (!totalCell) return;

        const cm1 = cm1Input && cm1Input.value !== '' ? parseFloat(cm1Input.value) : null;
        const cm2 = cm2Input && cm2Input.value !== '' ? parseFloat(cm2Input.value) : null;

        if (cm1 === null && cm2 === null) {
            totalCell.textContent = '—';
        } else {
            totalCell.textContent = ((cm1 || 0) + (cm2 || 0)).toFixed(2);
        }
    }

    document.querySelectorAll('.carry-input').forEach(function (input) {
        input.addEventListener('input', function () {
            recalcTotal(input.dataset.row);
        });
    });
</script>
@endsection
