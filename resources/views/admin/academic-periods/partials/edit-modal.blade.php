{{-- EDIT ACADEMIC PERIOD MODAL --}}
<div id="editPeriodModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Edit Academic Semester</h2>
                <p class="text-sm text-slate-500">Update semester details or override its status</p>
            </div>
            <button onclick="closeEditPeriodModal()" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <form id="editPeriodForm" method="POST" onsubmit="return confirmPeriodStatus('edit')">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Name</label>
                <input type="text" id="edit_name" name="name" required
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Academic Year</label>
                    <input type="text" id="edit_academic_year" name="academic_year" required
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Semester No.</label>
                    <select id="edit_period_number" name="period_number" required
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
                    <input type="date" id="edit_start_date" name="start_date" required
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">End Date</label>
                    <input type="date" id="edit_end_date" name="end_date" required
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:ring-2 focus:ring-violet-500 focus:outline-none">
                </div>
            </div>

            <div class="mb-4 flex items-center gap-2">
                <input type="checkbox" id="edit_override_status" name="override_status" value="1"
                    onchange="toggleStatusOverride('edit')"
                    class="w-4 h-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                <label for="edit_override_status" class="text-sm font-semibold text-slate-700">
                    Override status manually
                </label>
            </div>

            <div id="edit_status_wrap" class="mb-6" style="display:none;">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Status</label>
                <select name="status" id="edit_status"
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:ring-2 focus:ring-violet-500 focus:outline-none">
                    <option value="upcoming">Upcoming</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                </select>
                <p class="text-xs text-amber-600 mt-2">
                    Setting this to Active will mark any other currently Active semester as Completed.
                    Unchecking this box lets the status go back to being computed automatically from the dates above.
                </p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Notes (optional)</label>
                <textarea id="edit_notes" name="notes" rows="2"
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:ring-2 focus:ring-violet-500 focus:outline-none resize-none"></textarea>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeEditPeriodModal()"
                    class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-violet-600 text-white hover:bg-violet-700">
                    Update Semester
                </button>
            </div>
        </form>
    </div>
</div>
