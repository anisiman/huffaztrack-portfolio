{{-- CREATE ACADEMIC PERIOD MODAL --}}
<div id="createPeriodModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Add Academic Semester</h2>
                <p class="text-sm text-slate-500">Create a new university semester/term</p>
            </div>
            <button onclick="closeCreatePeriodModal()" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form action="{{ route('admin.academic-periods.store') }}" method="POST"
            onsubmit="return confirmPeriodStatus('create')">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Name</label>
                <input type="text" name="name" placeholder="Example: Semester 1 2025/2026" required
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Academic Year</label>
                    <input type="text" name="academic_year" placeholder="2025/2026" required
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Semester No.</label>
                    <select name="period_number" required
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:ring-2 focus:ring-violet-500 focus:outline-none">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Start Date</label>
                    <input type="date" name="start_date" required
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">End Date</label>
                    <input type="date" name="end_date" required
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
            </div>

            <p class="text-xs text-slate-400 mb-4">
                Status (Upcoming / Active / Completed) is set automatically from these dates once saved.
            </p>

            <div class="mb-4 flex items-center gap-2">
                <input type="checkbox" id="create_override_status" name="override_status" value="1"
                    onchange="toggleStatusOverride('create')"
                    class="w-4 h-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                <label for="create_override_status" class="text-sm font-semibold text-slate-700">
                    Override status manually
                </label>
            </div>

            <div id="create_status_wrap" class="mb-6" style="display:none;">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Status</label>
                <select name="status" id="create_status"
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    <option value="upcoming">Upcoming</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                </select>
                <p class="text-xs text-amber-600 mt-2">
                    Setting this to Active will mark any other currently Active semester as Completed.
                </p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Notes (optional)</label>
                <textarea name="notes" rows="2"
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:ring-2 focus:ring-violet-500 focus:outline-none resize-none"></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeCreatePeriodModal()"
                    class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-violet-600 text-white hover:bg-violet-700">
                    Save Semester
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function confirmPeriodStatus(prefix) {
        const override = document.getElementById(prefix + '_override_status').checked;
        const status = document.getElementById(prefix + '_status').value;

        if (override && status === 'active') {
            return confirm('This will mark any other currently Active academic semester as Completed. Continue?');
        }

        return true;
    }
</script>
