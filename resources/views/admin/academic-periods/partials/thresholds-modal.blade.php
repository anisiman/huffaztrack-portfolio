{{-- WARNING LETTER THRESHOLDS MODAL --}}
<div id="thresholdsModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">

        <div class="flex items-center justify-between mb-2">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Warning Letter Thresholds</h2>
                <p class="text-sm text-slate-500">
                    Semester: <span id="thresholds_semester_name" class="font-semibold text-slate-700"></span>
                </p>
            </div>
            <button onclick="closeThresholdsModal()" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <div class="text-xs text-slate-500 mb-6 space-y-2">
            <p class="font-semibold text-slate-700">Set the number of unexcused absences (TH) allowed before each warning letter is issued.</p>

            <p>Example:</p>
            <ul class="list-disc pl-5 space-y-1">
                <li>Warning 1 = 4 → Warning Letter 1 is issued after 4 TH.</li>
                <li>Warning 2 = 7 → Warning Letter 2 is issued after 7 TH.</li>
                <li>Warning 3 = 10 → Warning Letter 3 is issued after 10 TH.</li>
            </ul>

            <p class="font-semibold text-slate-700">Excused absences (THS) are not counted.</p>
        </div>

        <form id="thresholdsForm" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Warning 1 (TH)</label>
                <input type="number" id="threshold_1" name="warning_1_threshold" min="1" required
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Warning 2 (TH, cumulative)</label>
                <input type="number" id="threshold_2" name="warning_2_threshold" min="1" required
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Warning 3 (TH, cumulative)</label>
                <input type="number" id="threshold_3" name="warning_3_threshold" min="1" required
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:ring-2 focus:ring-violet-500 focus:outline-none">
                <p class="text-xs text-amber-600 mt-2">
                    Reaching Warning 3 permanently terminates the student from the programme.
                </p>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeThresholdsModal()"
                    class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-violet-600 text-white hover:bg-violet-700">
                    Save Thresholds
                </button>
            </div>
        </form>
    </div>
</div>
