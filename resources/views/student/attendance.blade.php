@extends('layouts.student')

@section('title', 'My Attendance')

@section('content')

{{-- Page Header --}}
<div class="mb-6">
    <h1 class="text-4xl font-bold text-black">My Attendance</h1>
    <p class="text-sm text-slate-400 mt-1">Track your presence and session history.</p>
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

{{-- ── Attendance Rate Banner ── --}}
<div class="relative overflow-hidden rounded-2xl mb-6 p-6"
     style="background: linear-gradient(135deg, #3b0764 0%, #6d28d9 55%, #7c3aed 100%);">
    <div class="absolute -top-8 -right-8 w-40 h-40 rounded-full opacity-10" style="background: radial-gradient(circle, white, transparent);"></div>
    <div class="absolute bottom-0 right-20 w-24 h-24 rounded-full opacity-10" style="background: radial-gradient(circle, white, transparent);"></div>
    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-purple-200 text-xs font-bold uppercase tracking-widest mb-1">Overall Attendance Rate</p>
            <p class="text-5xl font-black text-white">{{ $attendancePercent }}<span class="text-2xl font-semibold text-purple-200">%</span></p>
            <p class="text-purple-200 text-sm mt-1">{{ $presentCount }} present out of {{ $totalAttendance }} sessions</p>
        </div>
        <div class="w-full sm:w-56">
            <div class="flex justify-between text-xs text-purple-200 mb-1.5">
                <span>Progress</span>
                <span>{{ $attendancePercent }}%</span>
            </div>
            <div class="w-full bg-white/20 rounded-full h-3">
                <div class="bg-white h-3 rounded-full transition-all" style="width: {{ $attendancePercent }}%"></div>
            </div>
            @if($attendancePercent >= 80)
                <p class="text-emerald-300 text-xs mt-2 font-semibold">Great attendance! Keep it up.</p>
            @elseif($attendancePercent >= 60)
                <p class="text-amber-300 text-xs mt-2 font-semibold">Try to attend more sessions.</p>
            @else
                <p class="text-red-300 text-xs mt-2 font-semibold">Attendance needs improvement.</p>
            @endif
        </div>
    </div>
</div>

{{-- ── Summary Cards ── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">

    {{-- Present --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-green-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Present</p>
        </div>
        <p class="text-3xl font-black text-green-600">{{ $presentCount }}</p>
        <p class="text-xs text-slate-400 mt-1">sessions</p>
    </div>

    {{-- Absent --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-red-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Absent</p>
        </div>
        <p class="text-3xl font-black text-red-500">{{ $absentCount }}</p>
        <p class="text-xs text-slate-400 mt-1">sessions</p>
    </div>

    {{-- Excused --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-amber-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                </svg>
            </div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Excused</p>
        </div>
        <p class="text-3xl font-black text-amber-500">{{ $excusedCount }}</p>
        <p class="text-xs text-slate-400 mt-1">sessions</p>
    </div>

    {{-- Total --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-9 h-9 rounded-xl bg-violet-100 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-violet-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/>
                </svg>
            </div>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total</p>
        </div>
        <p class="text-3xl font-black text-slate-700">{{ $totalAttendance }}</p>
        <p class="text-xs text-slate-400 mt-1">sessions</p>
    </div>

</div>

{{-- ── Attendance History Table ── --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

    <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
        <div class="w-2 h-2 rounded-full bg-violet-500"></div>
        <h2 class="text-sm font-bold text-slate-700">Attendance History</h2>
    </div>

    @if($attendanceHistory->isEmpty())
        <div class="py-16 text-center">
            <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-slate-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                </svg>
            </div>
            <p class="text-slate-500 font-semibold text-sm">No attendance records found.</p>
        </div>

    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider w-14">No.</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Remarks</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Appeal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($attendanceHistory as $index => $record)
                        @php
                            $status = strtolower($record->status ?? '');
                            $badge = match($status) {
                                'present'            => ['bg-green-50 text-green-700 border border-green-100', 'Present', 'bg-green-400'],
                                'absent'             => ['bg-red-50 text-red-600 border border-red-100', 'Absent', 'bg-red-400'],
                                'absent_with_reason' => ['bg-amber-50 text-amber-700 border border-amber-100', 'Excused', 'bg-amber-400'],
                                default              => ['bg-slate-100 text-slate-500', ucfirst($record->status ?? 'Unknown'), 'bg-slate-300'],
                            };
                        @endphp
                        @php $appeal = $appeals[$record->attendance_id] ?? null; @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4 text-xs text-slate-400 font-semibold">
                                {{ $attendanceHistory->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-2 h-2 rounded-full flex-shrink-0 {{ $badge[2] }}"></div>
                                    <span class="text-sm font-medium text-slate-700">
                                        {{ $record->date ? \Carbon\Carbon::parse($record->date)->format('d M Y, D') : '—' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-semibold px-3 py-1.5 rounded-full {{ $badge[0] }}">
                                    {{ $badge[1] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-400">
                                {{ $record->remarks ?: '—' }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($status === 'absent')
                                    @if (! $appeal)
                                        <button type="button"
                                            onclick="openAppealModal({{ $record->attendance_id }}, '{{ \Carbon\Carbon::parse($record->date)->format('d M Y') }}')"
                                            class="text-xs font-semibold px-3 py-1.5 rounded-full bg-violet-50 text-violet-700 border border-violet-200 hover:bg-violet-100 transition">
                                            Submit Appeal
                                        </button>
                                    @elseif ($appeal->isPending())
                                        <span class="text-xs font-semibold px-3 py-1.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                                            Pending Review
                                        </span>
                                    @elseif ($appeal->isApproved())
                                        <span class="text-xs font-semibold px-3 py-1.5 rounded-full bg-green-50 text-green-700 border border-green-200">
                                            Approved
                                        </span>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-semibold px-3 py-1.5 rounded-full bg-red-50 text-red-600 border border-red-200"
                                                  title="{{ $appeal->rejection_remarks ?: 'No remarks provided.' }}">
                                                Rejected
                                            </span>
                                            <button type="button"
                                                onclick="openAppealModal({{ $record->attendance_id }}, '{{ \Carbon\Carbon::parse($record->date)->format('d M Y') }}')"
                                                class="text-xs font-semibold px-3 py-1.5 rounded-full bg-violet-50 text-violet-700 border border-violet-200 hover:bg-violet-100 transition">
                                                Resubmit
                                            </button>
                                        </div>
                                    @endif
                                @elseif ($status === 'absent_with_reason' && $appeal?->isApproved())
                                    <span class="text-xs font-semibold px-3 py-1.5 rounded-full bg-green-50 text-green-700 border border-green-200">
                                        Approved
                                    </span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100">
            {{ $attendanceHistory->links() }}
        </div>
    @endif

</div>

@endif

{{-- ── Appeal Submission Modal ── --}}
<div id="appealModal"
     style="display:none;"
     class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 px-4"
     onclick="if(event.target===this) closeAppealModal()">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-800">Submit Excuse Letter</h3>
            <button type="button" onclick="closeAppealModal()" class="text-slate-400 hover:text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-5 h-5">
                    <path d="M5.28 4.22a.75.75 0 0 0-1.06 1.06L6.94 8l-2.72 2.72a.75.75 0 1 0 1.06 1.06L8 9.06l2.72 2.72a.75.75 0 1 0 1.06-1.06L9.06 8l2.72-2.72a.75.75 0 0 0-1.06-1.06L8 6.94 5.28 4.22Z"/>
                </svg>
            </button>
        </div>
        <p id="appealDateLabel" class="text-sm text-slate-500 mb-4"></p>

        <form method="POST" action="{{ route('student.attendance.appeal.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="attendance_id" id="appealAttendanceId">

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2.5">
                    <ul class="list-disc list-inside space-y-0.5 text-xs font-medium text-red-600">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Reason Type</label>
                <select name="reason_type" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-violet-300">
                    <option value="">— Select (optional) —</option>
                    @foreach (['sick_mc' => 'Sick / MC', 'official_program' => 'Official Program', 'family_emergency' => 'Family Emergency', 'personal_reason' => 'Personal Reason', 'other' => 'Other'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('reason_type') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Remarks <span class="font-normal text-slate-400">(optional)</span></label>
                <textarea name="remarks" rows="2"
                    placeholder="Brief explanation..."
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-700 resize-none focus:outline-none focus:ring-2 focus:ring-violet-300">{{ old('remarks') }}</textarea>
            </div>

            <div class="mb-5">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Supporting Document <span class="text-red-500">*</span></label>
                <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.webp,.heic,.heif,image/*" required
                    class="block w-full text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100">
                <p class="text-xs text-slate-400 mt-1">PDF or photo (JPG, PNG, WEBP, HEIC) — max 10 MB</p>
                @if (session('error') && str_contains(session('error'), 'already been submitted'))
                    <p class="text-xs text-amber-600 mt-1">You have already submitted an appeal for this date.</p>
                @endif
            </div>

            <div class="flex gap-2 justify-end">
                <button type="button" onclick="closeAppealModal()"
                    class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="submit"
                    class="px-4 py-2 rounded-xl bg-violet-600 text-white text-sm font-semibold hover:bg-violet-700 transition">
                    Submit Appeal
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Flash messages --}}
@if (session('success'))
<div id="flashSuccess"
     class="fixed bottom-5 right-5 z-50 bg-green-600 text-white text-sm font-semibold px-5 py-3 rounded-xl shadow-lg">
    {{ session('success') }}
</div>
@endif
@if (session('error'))
<div id="flashError"
     class="fixed bottom-5 right-5 z-50 bg-red-500 text-white text-sm font-semibold px-5 py-3 rounded-xl shadow-lg">
    {{ session('error') }}
</div>
@endif

@push('scripts')
<script>
function openAppealModal(attendanceId, dateLabel) {
    document.getElementById('appealAttendanceId').value = attendanceId;
    document.getElementById('appealDateLabel').textContent = dateLabel ? ('Absence date: ' + dateLabel) : '';
    document.getElementById('appealModal').style.display = 'flex';
}
function closeAppealModal() {
    document.getElementById('appealModal').style.display = 'none';
}
// Auto-hide flash messages
['flashSuccess','flashError'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) setTimeout(function() { el.remove(); }, 4000);
});
// Re-open the appeal modal (with entered values kept) when the submission
// bounced back with validation errors, so the failure isn't invisible.
@if ($errors->any() && old('attendance_id'))
    openAppealModal(@json(old('attendance_id')), '');
@endif
</script>
@endpush

@endsection
