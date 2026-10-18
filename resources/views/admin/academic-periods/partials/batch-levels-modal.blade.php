{{-- MANAGE BATCH LEVELS MODAL --}}
<div id="batchLevelsModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">

        <div class="flex items-center justify-between mb-2">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Manage Batch Levels</h2>
                <p class="text-sm text-slate-500">
                    Semester: <span id="batch_levels_semester_name" class="font-semibold text-slate-700"></span>
                </p>
            </div>
            <button onclick="closeBatchLevelsModal()" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <p class="text-xs text-slate-500 mb-6">
            Assign which Semester Level (1–7) each Batch is at for this Academic Semester. This determines
            which subjects each batch's students take. Leave a batch blank to unassign it.
        </p>

        <form id="batchLevelsForm" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-3 mb-6">
                @forelse ($batches as $batch)
                    <div class="flex items-center justify-between gap-3">
                        <label class="text-sm font-semibold text-slate-700">{{ $batch->batch_name }}</label>
                        <select name="levels[{{ $loop->index }}][semester_id]"
                            data-batch-id="{{ $batch->batch_id }}"
                            class="w-40 rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                            <option value="">— Not set —</option>
                            @foreach ($semesterLevels as $level)
                                <option value="{{ $level->semester_id }}">{{ $level->semester_name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="levels[{{ $loop->index }}][batch_id]" value="{{ $batch->batch_id }}">
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No batches found. Create a batch first.</p>
                @endforelse
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeBatchLevelsModal()"
                    class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-violet-600 text-white hover:bg-violet-700">
                    Save Batch Levels
                </button>
            </div>
        </form>
    </div>
</div>
