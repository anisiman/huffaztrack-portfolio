@extends('layouts.admin')

@section('title', 'Add New Student')

@section('content')

    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">
        <style>
            .users-page {
                padding: 1.5rem;
                max-width: 1600px;
                margin: 0 auto;
            }
        </style>
    @endpush

    {{-- Kontainer luar diletakkan di sini untuk memberikan ruang (pt-10) supaya tidak kelihatan compressed --}}
    <div class="container mx-auto px-4 pt-7 pb-12">

        {{-- Using Alpine.js to sync the preview card with the form inputs if needed --}}
        <div class="w-full" x-data="{
            name: '',
            tahfiz_id: '',
            programName: '',
            status: 'AKTIF',
            batchName: '{{ $currentBatch !== 'Overall' ? $currentBatch : 'Select Batch...' }}'
        }">

            {{-- Header --}}
            <div class="users-page__header">
                <div>
                    {{-- Back --}}
                    @include('partials.back-button', ['href' => route('admin.masterlist'), 'label' => 'Masterlist'])
                    <h1 class="users-page__title">Add New Student</h1>
                    
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 rounded-2xl p-5">
                    <div class="font-bold mb-2">
                        Please fix the following errors:
                    </div>
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.masterlist.store') }}" method="POST">
                @csrf

                {{-- INPUT HIDDEN UNTUK BATCH MEMASTIKAN VALUE SENTIASA DIHANTAR --}}
                <input type="hidden" name="batch" value="{{ $currentBatch }}">

                <div class="grid grid-cols-12 gap-8 mb-8">

                    {{-- Lebar Penuh (col-span-12) tanpa pembahagian kad profil kiri --}}
                    <div class="col-span-12 space-y-8">

                        {{-- 1. Academic Information --}}
                        <div class="mt-7 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-8 py-5 border-b border-gray-50 bg-gray-50/50">
                                <h3 class="font-black text-gray-700 uppercase tracking-widest text-sm">Academic Credentials
                                </h3>
                            </div>
                            <div class="p-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">ID Tahfiz <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" name="student_id" x-model="tahfiz_id" required
                                        pattern="[^\-/]+" title="ID Tahfiz cannot contain dashes (-) or slashes (/)."
                                        oninput="this.value = this.value.replace(/[-\/]/g, '')"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">ID
                                        Asasi/Diploma</label>
                                    <input type="text" name="asasi_diploma_id"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">ID
                                        Ijazah</label>
                                    <input type="text" name="ijazah_id"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Program <span
                                            class="text-red-500">*</span></label>
                                    <select name="program_id" required
                                        @change="programName = $event.target.options[$event.target.selectedIndex].text"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition">
                                        <option value="">Select Program...</option>
                                        @foreach ($programs as $program)
                                            <option value="{{ $program->program_id }}">{{ $program->program_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Batch <span
                                            class="text-red-500">*</span></label>
                                    <select name="batch_id" required
                                        @change="batchName = $event.target.options[$event.target.selectedIndex].text"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition font-semibold">
                                        <option value="">Select Batch...</option>
                                        @foreach ($batches as $batch)
                                            <option value="{{ $batch->batch_id }}"
                                                {{ old('batch_id', isset($currentBatch) ? $currentBatch : '') == $batch->batch_name ? 'selected' : '' }}>
                                                {{ $batch->batch_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Personal Profile --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-8 py-5 border-b border-gray-50 bg-gray-50/50">
                                <h3 class="font-black text-gray-700 uppercase tracking-widest text-sm">Personal Profile</h3>
                            </div>
                            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Full Name
                                        (NAMA) <span class="text-red-500">*</span></label>
                                    <input type="text" name="student_name" x-model="name" required
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition font-bold uppercase">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">NRIC Number
                                        <span class="text-red-500">*</span></label>
                                    <input type="text" name="nric" required placeholder="000000-00-0000"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Gender <span
                                            class="text-red-500">*</span></label>
                                    <select name="gender" required
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition">
                                        <option value="">Select...</option>
                                        <option value="LELAKI">LELAKI</option>
                                        <option value="PEREMPUAN">PEREMPUAN</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Phone
                                        Number <span class="text-red-500">*</span></label>
                                    <input type="text" name="phone"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition" required>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Email
                                        Address</label>
                                    <input type="email" name="email" required
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition">
                                </div>
                            </div>
                        </div>

                        {{-- Parent Side-by-Side Wrapper Container --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-stretch">

                            {{-- 3. Additional Details --}}
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden h-full">

                                <div class="px-8 py-5 border-b border-gray-50 bg-gray-50/50">
                                    <h3 class="font-black text-gray-700 uppercase tracking-widest text-sm">
                                        Additional Records
                                    </h3>
                                </div>

                                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">

                                    {{-- Sponsorship --}}
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">
                                            Sponsorship
                                        </label>

                                        <select name="sponsorship_id"
                                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition">

                                            <option value="">Select Sponsorship...</option>

                                            @foreach ($sponsorships as $sponsorship)
                                                <option value="{{ $sponsorship->sponsorship_id }}">
                                                    {{ $sponsorship->sponsorship_name }}
                                                </option>
                                            @endforeach

                                        </select>
                                    </div>

                                    {{-- Status --}}
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">
                                            Status
                                        </label>

                                        <select name="status" x-model="status" required
                                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition font-bold">

                                            <option value="AKTIF">AKTIF</option>
                                            <option value="TAMAT PENSIJILAN">TAMAT PENSIJILAN</option>
                                            <option value="TARIK DIRI">TARIK DIRI</option>
                                            <option value="GAGAL DAN DIBERHENTIKAN">GAGAL DAN DIBERHENTIKAN</option>

                                        </select>
                                    </div>

                                    {{-- Secondary School --}}
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">
                                            Secondary School
                                        </label>

                                        <input type="text" name="secondary_school"
                                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition">
                                    </div>

                                    {{-- Remarks --}}
                                    <div class="md:col-span-2">
                                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">
                                            Remarks / Notes
                                        </label>

                                        <textarea name="remarks" rows="4"
                                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition resize-none"></textarea>
                                    </div>

                                </div>
                            </div>

                            {{-- 4. Residential Address --}}
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden h-full">

                                <div class="px-8 py-5 border-b border-gray-50 bg-gray-50/50">
                                    <h3 class="font-black text-gray-700 uppercase tracking-widest text-sm">
                                        Residential Address
                                    </h3>
                                </div>

                                <div class="p-8 flex flex-col gap-6 h-full">

                                    {{-- Address --}}
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">
                                            Home Address
                                        </label>

                                        <textarea name="address" rows="8"
                                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition resize-none"
                                            placeholder="Enter complete address..."></textarea>
                                    </div>

                                    {{-- State --}}
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">
                                            State
                                        </label>

                                        <input type="text" name="state"
                                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition">
                                    </div>

                                </div>
                            </div>

                        </div>

                        {{-- 5. Tahfiz Subject Grades & Results --}}
                        <div class="space-y-6" id="semestersContainer">
                            <div
                                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div>
                                    <h3 class="font-black text-gray-700 uppercase tracking-widest text-sm">Academic
                                        Semesters & Results</h3>
                                </div>
                                <button type="button" onclick="addSemesterCard()"
                                    class="px-5 py-2.5 bg-violet-600 text-white rounded-xl text-sm font-bold hover:bg-violet-700 transition">
                                    + Add New Semester Card
                                </button>
                            </div>

                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden semester-card"
                                data-semester-index="0">
                                <div
                                    class="p-8 border-b border-gray-100 bg-slate-50/50 grid grid-cols-1 md:grid-cols-4 gap-4 items-end relative">
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Semester
                                            <span class="text-red-500">*</span></label>
                                        <select name="semesters[0][semester_id]"
                                            class="semester-select w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 outline-none transition"
                                            onchange="onSemesterChange(this)">
                                            <option value="">-- Pilih Semester --</option>
                                            @foreach ($semesters as $semester)
                                                <option value="{{ $semester->semester_id }}">
                                                    {{ $semester->semester_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[10px] font-black text-gray-400 uppercase mb-2">GPA</label>
                                        <input type="number" step="0.01" min="0" max="4"
                                            name="semesters[0][gpa]" placeholder="0.00"
                                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 outline-none transition">
                                    </div>
                                    <div>
                                        <label
                                            class="block text-[10px] font-black text-gray-400 uppercase mb-2">CGPA</label>
                                        <input type="number" step="0.01" min="0" max="4"
                                            name="semesters[0][cgpa]" placeholder="0.00"
                                            class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 outline-none transition">
                                    </div>
                                    <div class="flex justify-between items-center pl-4">
                                        <button type="button" onclick="addSubjectRow(0)"
                                            class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-700 transition">
                                            + Add Subject
                                        </button>
                                        <button type="button" onclick="removeSemesterCard(this)"
                                            class="text-red-500 hover:text-red-700 text-xs font-bold transition md:ml-auto">
                                            Remove Sem
                                        </button>
                                    </div>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead class="bg-slate-50 border-b border-slate-100">
                                            <tr>
                                                <th
                                                    class="px-6 py-3 text-left font-black uppercase tracking-wider text-slate-500 text-[10px] w-1/2">
                                                    Subject (Subjek)</th>
                                                <th
                                                    class="px-6 py-3 text-left font-black uppercase tracking-wider text-slate-500 text-[10px]">
                                                    Grade</th>
                                                <th
                                                    class="px-6 py-3 text-left font-black uppercase tracking-wider text-slate-500 text-[10px]">
                                                    Grade Point</th>
                                                <th
                                                    class="px-6 py-3 text-center font-black uppercase tracking-wider text-slate-500 text-[10px]">
                                                    Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="subjectsTableBody-0" data-subject-index="1">
                                            <tr class="border-b border-gray-50">
                                                <td class="px-6 py-3">
                                                    <select name="semesters[0][subjects][0][subjectT_id]"
                                                        class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 outline-none text-xs">
                                                        <option value="">-- Pilih Subjek --</option>
                                                    </select>
                                                </td>
                                                <td class="px-6 py-3">
                                                    <select name="semesters[0][subjects][0][grade]"
                                                        class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 outline-none text-xs font-bold">
                                                        <option value="A">A</option>
                                                        <option value="B+">B+</option>
                                                        <option value="B">B</option>
                                                        <option value="C+">C+</option>
                                                        <option value="C">C</option>
                                                        <option value="D+">D+</option>
                                                        <option value="D">D</option>
                                                        <option value="F">F</option>
                                                    </select>
                                                </td>
                                                <td class="px-6 py-3">
                                                    <input type="number" step="0.01" min="0" max="4"
                                                        name="semesters[0][subjects][0][grade_point]" placeholder="4.00"
                                                        class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 outline-none text-xs">
                                                </td>
                                                <td class="px-6 py-3 text-center">
                                                    <button type="button" onclick="this.closest('tr').remove()"
                                                        class="text-red-500 hover:text-red-700 font-bold text-xs transition">Remove</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-end gap-4 border-t border-gray-100 pt-6">
                    <button type="reset"
                        class="px-8 py-3 text-sm font-bold text-gray-400 hover:text-red-500 transition">Discard
                        Changes</button>
                    <button type="submit"
                        class="px-12 py-3 bg-purple-900 text-white rounded-xl font-black uppercase tracking-widest shadow-lg hover:bg-purple-800 transition">Save
                        Student Record</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            const semestersData = @json($semesters->map(function ($semester) {
                return ['id' => $semester->semester_id, 'name' => $semester->semester_name];
            })->values());

            const subjectsBySemester = {};
            @foreach ($subjects as $subject)
                subjectsBySemester['{{ $subject->semester_id }}'] = subjectsBySemester['{{ $subject->semester_id }}'] || [];
                subjectsBySemester['{{ $subject->semester_id }}'].push({
                    id: '{{ $subject->subjectT_id }}',
                    code: '{{ $subject->subjectT_code }}',
                    name: '{{ $subject->subjectT_name }}'
                });
            @endforeach

            let semesterCardIndex = 1;

            function buildSemesterOptions(selectedValue = '') {
                return semestersData.map(semester => {
                    const selected = semester.id.toString() === selectedValue.toString() ? ' selected' : '';
                    return `<option value="${semester.id}"${selected}>${semester.name}</option>`;
                }).join('');
            }

            function buildSubjectOptions(semesterId, selectedValue = '') {
                const subjects = subjectsBySemester[semesterId] || [];
                let html = '<option value="">-- Pilih Subjek --</option>';
                subjects.forEach(subject => {
                    const selected = subject.id.toString() === selectedValue.toString() ? ' selected' : '';
                    html += `<option value="${subject.id}"${selected}>[${subject.code}] ${subject.name}</option>`;
                });
                return html;
            }

            function updateSemesterSelectStates() {
                const selects = Array.from(document.querySelectorAll('.semester-select'));
                const selectedIds = selects.map(select => select.value).filter(Boolean);

                selects.forEach(select => {
                    const currentValue = select.value;
                    Array.from(select.options).forEach(option => {
                        if (!option.value) {
                            option.disabled = false;
                            return;
                        }
                        if (option.value === currentValue) {
                            option.disabled = false;
                        } else {
                            option.disabled = selectedIds.includes(option.value);
                        }
                    });
                });
            }

            function refreshSubjectSelects(cardEl, semesterId) {
                const selectRows = cardEl.querySelectorAll('select[name$="[subjectT_id]"]');
                selectRows.forEach(select => {
                    const currentValue = select.value;
                    select.innerHTML = buildSubjectOptions(semesterId, currentValue);
                    if (semesterId === '' || !subjectsBySemester[semesterId]?.some(s => s.id.toString() === currentValue.toString())) {
                        select.value = '';
                    }
                });
            }

            function onSemesterChange(select) {
                const semesterId = select.value;
                const duplicates = Array.from(document.querySelectorAll('.semester-select'))
                    .filter(other => other !== select && other.value === semesterId && semesterId !== '');

                if (duplicates.length > 0) {
                    alert('Semester ini telah dipilih dalam kad lain. Sila pilih semester yang lain.');
                    select.value = '';
                    refreshSubjectSelects(select.closest('.semester-card'), '');
                    updateSemesterSelectStates();
                    return;
                }

                refreshSubjectSelects(select.closest('.semester-card'), semesterId);
                updateSemesterSelectStates();
            }

            function addSemesterCard() {
                const container = document.getElementById('semestersContainer');
                const cardHTML = `
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden semester-card mt-6" data-semester-index="${semesterCardIndex}">
        <div class="p-8 border-b border-gray-100 bg-slate-50/50 grid grid-cols-1 md:grid-cols-4 gap-4 items-end relative">
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Semester <span class="text-red-500">*</span></label>
                <select name="semesters[${semesterCardIndex}][semester_id]" class="semester-select w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 outline-none transition" onchange="onSemesterChange(this)">
                    <option value="">-- Pilih Semester --</option>
                    ${buildSemesterOptions()}
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">GPA</label>
                <input type="number" step="0.01" min="0" max="4" name="semesters[${semesterCardIndex}][gpa]" placeholder="0.00" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 outline-none transition">
            </div>
            <div>
                <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">CGPA</label>
                <input type="number" step="0.01" min="0" max="4" name="semesters[${semesterCardIndex}][cgpa]" placeholder="0.00" class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 outline-none transition">
            </div>
            <div class="flex justify-between items-center pl-4">
                <button type="button" onclick="addSubjectRow(${semesterCardIndex})" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-700 transition">
                    + Add Subject
                </button>
                <button type="button" onclick="removeSemesterCard(this)" class="text-red-500 hover:text-red-700 text-xs font-bold transition md:ml-auto">
                    Remove Sem
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3 text-left font-black uppercase tracking-wider text-slate-500 text-[10px] w-1/2">Subject (Subjek)</th>
                        <th class="px-6 py-3 text-left font-black uppercase tracking-wider text-slate-500 text-[10px]">Grade</th>
                        <th class="px-6 py-3 text-left font-black uppercase tracking-wider text-slate-500 text-[10px]">Grade Point</th>
                        <th class="px-6 py-3 text-center font-black uppercase tracking-wider text-slate-500 text-[10px]">Action</th>
                    </tr>
                </thead>
                <tbody id="subjectsTableBody-${semesterCardIndex}" data-subject-index="1">
                    <tr class="border-b border-gray-50">
                        <td class="px-6 py-3">
                            <select name="semesters[${semesterCardIndex}][subjects][0][subjectT_id]" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 outline-none text-xs">
                                <option value="">-- Pilih Subjek --</option>
                            </select>
                        </td>
                        <td class="px-6 py-3">
                            <select name="semesters[${semesterCardIndex}][subjects][0][grade]" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 outline-none text-xs font-bold">
                                <option value="A">A</option><option value="B+">B+</option>
                                <option value="B">B</option><option value="C+">C+</option>
                                <option value="C">C</option><option value="D+">D+</option>
                                <option value="D">D</option><option value="F">F</option>
                            </select>
                        </td>
                        <td class="px-6 py-3">
                            <input type="number" step="0.01" min="0" max="4" name="semesters[${semesterCardIndex}][subjects][0][grade_point]" placeholder="4.00" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 outline-none text-xs">
                        </td>
                        <td class="px-6 py-3 text-center">
                            <button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700 font-bold text-xs transition">Remove</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>`;

                container.insertAdjacentHTML('beforeend', cardHTML);
                semesterCardIndex++;
                updateSemesterSelectStates();
            }

            function addSubjectRow(semIndex) {
                const tbody = document.getElementById(`subjectsTableBody-${semIndex}`);
                const subIndex = tbody.querySelectorAll('tr.subject-row').length;
                const semesterId = document.querySelector(`[name="semesters[${semIndex}][semester_id]"]`).value;
                const rowHTML = `
        <tr class="border-b border-gray-50 subject-row">
            <td class="px-6 py-3">
                <select name="semesters[${semIndex}][subjects][${subIndex}][subjectT_id]" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 outline-none text-xs">
                    ${buildSubjectOptions(semesterId)}
                </select>
            </td>
            <td class="px-6 py-3">
                <select name="semesters[${semIndex}][subjects][${subIndex}][grade]" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 outline-none text-xs font-bold">
                    <option value="A">A</option><option value="B+">B+</option>
                    <option value="B">B</option><option value="C+">C+</option>
                    <option value="C">C</option><option value="D+">D+</option>
                    <option value="D">D</option><option value="F">F</option>
                </select>
            </td>
            <td class="px-6 py-3">
                <input type="number" step="0.01" min="0" max="4" name="semesters[${semIndex}][subjects][${subIndex}][grade_point]" placeholder="4.00" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 outline-none text-xs">
            </td>
            <td class="px-6 py-3 text-center">
                <button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700 font-bold text-xs transition">Remove</button>
            </td>
        </tr>`;
                tbody.insertAdjacentHTML('beforeend', rowHTML);
            }

            function removeSemesterCard(btn) {
                const cards = document.querySelectorAll('.semester-card');
                if (cards.length > 1) {
                    btn.closest('.semester-card').remove();
                    updateSemesterSelectStates();
                } else {
                    alert('Pelajar mesti mempunyai sekurang-kurangnya satu kad semester.');
                }
            }

            function initializeSemesterCards() {
                document.querySelectorAll('.semester-card').forEach(card => {
                    const semesterSelect = card.querySelector('.semester-select');
                    if (semesterSelect) {
                        refreshSubjectSelects(card, semesterSelect.value);
                    }
                });

                updateSemesterSelectStates();
            }

            document.addEventListener('DOMContentLoaded', initializeSemesterCards);
        </script>
    @endpush
@endsection
