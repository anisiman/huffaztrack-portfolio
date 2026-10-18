{{-- CREATE BATCH MODAL --}}
<div id="createBatchModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">

        {{-- HEADER --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Add Batch</h2>
                <p class="text-sm text-slate-500">
                    Create a new intake batch
                </p>
            </div>
            <button onclick="closeCreateBatchModal()" class="text-slate-400 hover:text-slate-600">
                ✕
            </button>
        </div>

        {{-- FORM --}}
        <form action="{{ route('admin.batches.store') }}" method="POST">
            @csrf

            {{-- BATCH NAME --}}
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Batch Name
                </label>
                <input type="text"
                    name="batch_name"
                    placeholder="Example: T16"
                    required
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>

            {{-- INTAKE --}}
            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 mb-2">
                    Intake
                </label>

                <input type="text"
                    name="intake"
                    placeholder="Example: 2026/2027"
                    required
                    class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:ring-2 focus:ring-violet-500 focus:outline-none">
            </div>

            {{-- IS ACTIVE --}}
            <div class="mb-6 flex items-center gap-2">
                <input type="checkbox"
                    id="create_is_active"
                    name="is_active"
                    value="1"
                    checked
                    class="w-4 h-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                <label for="create_is_active" class="text-sm font-semibold text-slate-700">
                    Batch is active
                </label>
            </div>

            {{-- BUTTONS --}}
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeCreateBatchModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50">
                    Cancel
                </button>

                <button type="submit" class="px-5 py-2.5 rounded-xl bg-violet-600 text-white hover:bg-violet-700">
                    Save Batch
                </button>
            </div>
        </form>
    </div>
</div>