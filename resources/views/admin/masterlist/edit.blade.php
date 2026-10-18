@extends('layouts.admin')

@section('title', 'Edit Student')

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

    <div class="container mx-auto px-4 pt-7 pb-12">

        <div class="w-full">

            {{-- HEADER --}}
            <div class="users-page__header">
                <div>
                    {{-- Back --}}
                    @include('partials.back-button', ['href' => route('admin.masterlist'), 'label' => 'Masterlist'])
                    <h1 class="users-page__title">Edit Student</h1>
                    <p class="text-lg text-slate-500 mt-2">
                        {{ $student->student_name }}
                    </p>
                </div>
            </div>

            {{-- ERRORS --}}
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

            {{-- FORM --}}
            <form action="{{ route('admin.masterlist.update', $student->student_id) }}" method="POST">

                @csrf
                @method('PUT')

                <div class="grid grid-cols-12 gap-8 mb-8">

                    <div class="col-span-12 space-y-8">

                        {{-- Academic Information --}}
                        <div class="mt-7 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                            <div class="px-8 py-5 border-b border-gray-50 bg-gray-50/50">
                                <h3 class="font-black text-gray-700 uppercase tracking-widest text-sm">
                                    Academic Credentials
                                </h3>
                            </div>

                            <div class="p-8 grid grid-cols-1 md:grid-cols-3 gap-6">

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">
                                        ID Tahfiz
                                    </label>

                                    <input type="text" name="student_id"
                                        value="{{ old('student_id', $student->student_id) }}" required
                                        pattern="[^\-/]+" title="ID Tahfiz cannot contain dashes (-) or slashes (/)."
                                        oninput="this.value = this.value.replace(/[-\/]/g, '')"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">
                                        ID Asasi/Diploma
                                    </label>

                                    <input type="text" name="asasi_diploma_id"
                                        value="{{ old('asasi_diploma_id', $student->asasi_diploma_id) }}"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">
                                        ID Ijazah
                                    </label>

                                    <input type="text" name="ijazah_id"
                                        value="{{ old('ijazah_id', $student->ijazah_id) }}"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl">
                                </div>

                                {{-- PROGRAM --}}
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">
                                        Program
                                    </label>

                                    <select name="program_id"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl">

                                        @foreach ($programs as $program)
                                            <option value="{{ $program->program_id }}"
                                                {{ old('program_id', optional($student->activeProgram->first())->program_id) == $program->program_id ? 'selected' : '' }}>
                                                {{ $program->program_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- BATCH --}}
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">
                                        Batch
                                    </label>

                                    <select name="batch_id"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl">

                                        @foreach ($batches as $batch)
                                            <option value="{{ $batch->batch_id }}"
                                                {{ old('batch_id', $student->batch_id) == $batch->batch_id ? 'selected' : '' }}>
                                                {{ $batch->batch_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                        </div>

                        {{-- PERSONAL PROFILE --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                            <div class="px-8 py-5 border-b border-gray-50 bg-gray-50/50">
                                <h3 class="font-black text-gray-700 uppercase tracking-widest text-sm">
                                    Personal Profile
                                </h3>
                            </div>

                            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6">

                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">
                                        Full Name
                                    </label>

                                    <input type="text" name="student_name"
                                        value="{{ old('student_name', $student->student_name) }}" required
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl font-bold uppercase">
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">
                                        NRIC
                                    </label>

                                    <input type="text" name="nric" value="{{ old('nric', $student->nric) }}"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl">
                                </div>

                                {{-- GENDER --}}
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">
                                        Gender
                                    </label>

                                    <select name="gender"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl">

                                        <option value="LELAKI"
                                            {{ old('gender', $student->gender) == 'LELAKI' ? 'selected' : '' }}>
                                            LELAKI
                                        </option>

                                        <option value="PEREMPUAN"
                                            {{ old('gender', $student->gender) == 'PEREMPUAN' ? 'selected' : '' }}>
                                            PEREMPUAN
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">
                                        Phone
                                    </label>

                                    <input type="text" name="phone" value="{{ old('phone', $student->phone) }}"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">
                                        Email
                                    </label>

                                    <input type="email" name="email" value="{{ old('email', $student->email) }}"
                                        class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl">
                                </div>

                            </div>
                        </div>

                        {{-- Parent Side-by-Side Wrapper Container --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">

                            {{-- ADDITIONAL RECORDS --}}
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                                {{-- Header --}}
                                <div class="px-8 py-5 border-b border-gray-100 bg-gray-50/50">
                                    <h3 class="font-black text-gray-700 uppercase tracking-widest text-sm">
                                        Additional Records
                                    </h3>
                                </div>

                                {{-- Content --}}
                                <div class="p-8 space-y-6">

                                    {{-- Row 1 --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                        {{-- Sponsorship --}}
                                        <div>
                                            <label class="block text-[11px] font-black text-gray-400 uppercase mb-2">
                                                Sponsorship
                                            </label>

                                            <select name="sponsorship_id"
                                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition">

                                                <option value="">Select Sponsorship...</option>

                                                @foreach ($sponsorships as $sponsorship)
                                                    <option value="{{ $sponsorship->sponsorship_id }}"
                                                        {{ old('sponsorship_id', $student->sponsorship_id) == $sponsorship->sponsorship_id ? 'selected' : '' }}>
                                                        {{ $sponsorship->sponsorship_name }}
                                                    </option>
                                                @endforeach

                                            </select>
                                        </div>

                                        {{-- Status --}}
                                        <div>
                                            <label class="block text-[11px] font-black text-gray-400 uppercase mb-2">
                                                Status
                                            </label>

                                            <select name="status"
                                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition">

                                                <option value="AKTIF"
                                                    {{ old('status', $student->status) == 'AKTIF' ? 'selected' : '' }}>
                                                    AKTIF</option>
                                                <option value="TAMAT PENSIJILAN"
                                                    {{ old('status', $student->status) == 'TAMAT PENSIJILAN' ? 'selected' : '' }}>
                                                    TAMAT PENSIJILAN</option>
                                                <option value="TARIK DIRI"
                                                    {{ old('status', $student->status) == 'TARIK DIRI' ? 'selected' : '' }}>
                                                    TARIK DIRI</option>
                                                <option value="GAGAL DAN DIBERHENTIKAN"
                                                    {{ old('status', $student->status) == 'GAGAL DAN DIBERHENTIKAN' ? 'selected' : '' }}>
                                                    GAGAL DAN DIBERHENTIKAN</option>

                                            </select>
                                        </div>

                                    </div>

                                    {{-- Row 2 --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                        {{-- School --}}
                                        <div>
                                            <label class="block text-[11px] font-black text-gray-400 uppercase mb-2">
                                                Secondary School
                                            </label>

                                            <input type="text" name="secondary_school"
                                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition"
                                                value="{{ old('secondary_school', $student->secondary_school) }}">
                                        </div>

                                    </div>

                                    {{-- Remarks --}}
                                    <div>
                                        <label class="block text-[11px] font-black text-gray-400 uppercase mb-2">
                                            Remarks / Notes
                                        </label>

                                        <textarea name="remarks" rows="5"
                                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition resize-none">{{ old('remarks', $student->remarks) }}</textarea>
                                    </div>

                                </div>
                            </div>

                            {{-- RESIDENTIAL ADDRESS --}}
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                                {{-- Header --}}
                                <div class="px-8 py-5 border-b border-gray-100 bg-gray-50/50">
                                    <h3 class="font-black text-gray-700 uppercase tracking-widest text-sm">
                                        Residential Address
                                    </h3>
                                </div>

                                {{-- Content --}}
                                <div class="p-8 space-y-6">

                                    {{-- Address --}}
                                    <div>
                                        <label class="block text-[11px] font-black text-gray-400 uppercase mb-2">
                                            Home Address
                                        </label>

                                        <textarea name="address" rows="9"
                                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition resize-none"
                                            placeholder="Enter complete address...">{{ old('address', $student->address) }}</textarea>
                                    </div>

                                    {{-- State --}}
                                    <div>
                                        <label class="block text-[11px] font-black text-gray-400 uppercase mb-2">
                                            State
                                        </label>

                                        <input type="text" name="state"
                                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition"
                                            value="{{ old('state', $student->state) }}">
                                    </div>

                                </div>
                            </div>

                        </div>

                        {{-- ============================= --}}
                        {{-- SEMESTERS & RESULTS SECTION --}}
                        {{-- ============================= --}}

                        <div class="space-y-6" id="semestersContainer">

                            {{-- HEADER --}}
                            <div
                                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                                <div>
                                    <h3 class="font-black text-gray-700 uppercase tracking-widest text-sm">
                                        Academic Semesters & Results
                                    </h3>
                                </div>

                                <button type="button" onclick="addSemesterCard()"
                                    class="px-5 py-2.5 bg-violet-600 text-white rounded-xl text-sm font-bold hover:bg-violet-700 transition">
                                    + Add New Semester Card
                                </button>

                            </div>

                            {{-- LOOP EXISTING RESULTS --}}
                            @forelse($student->results as $semKey => $result)

                                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden semester-card"
                                    data-semester-index="{{ $semKey }}">

                                    {{-- RESULT ID --}}
                                    <input type="hidden" name="semesters[{{ $semKey }}][result_id]"
                                        value="{{ $result->result_id }}">

                                    {{-- TOP SECTION --}}
                                    <div
                                        class="p-8 border-b border-gray-100 bg-slate-50/50 grid grid-cols-1 md:grid-cols-4 gap-4 items-end relative">

                                        {{-- SEMESTER --}}
                                        <div>

                                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">
                                                Semester
                                            </label>

                                            <select name="semesters[{{ $semKey }}][semester_id]"
                                                class="semester-select w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl"
                                                onchange="onSemesterChange(this)">

                                                <option value="">-- Pilih Semester --</option>

                                                @foreach ($semesters as $semester)
                                                    <option value="{{ $semester->semester_id }}"
                                                        {{ $result->semester_id == $semester->semester_id ? 'selected' : '' }}>
                                                        {{ $semester->semester_name }}
                                                    </option>
                                                @endforeach

                                            </select>

                                        </div>

                                        {{-- GPA --}}
                                        <div>

                                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">
                                                GPA
                                            </label>

                                            <input type="number" step="0.01" min="0" max="4"
                                                name="semesters[{{ $semKey }}][gpa]" value="{{ $result->gpa }}"
                                                class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl">

                                        </div>

                                        {{-- CGPA --}}
                                        <div>

                                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">
                                                CGPA
                                            </label>

                                            <input type="number" step="0.01" min="0" max="4"
                                                name="semesters[{{ $semKey }}][cgpa]" value="{{ $result->cgpa }}"
                                                class="w-full px-4 py-2.5 bg-white border border-gray-200 rounded-xl">

                                        </div>

                                        {{-- ACTIONS --}}
                                        <div class="flex justify-between items-center pl-4">

                                            <button type="button" onclick="addSubjectRow({{ $semKey }})"
                                                class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-700 transition">
                                                + Add Subject
                                            </button>

                                            <button type="button" onclick="removeSemesterCard(this)"
                                                class="text-red-500 hover:text-red-700 text-xs font-bold transition">
                                                Remove Sem
                                            </button>

                                        </div>

                                    </div>

                                    {{-- SUBJECT TABLE --}}
                                    <div class="overflow-x-auto">

                                        <table class="w-full text-sm">

                                            <thead class="bg-slate-50 border-b border-slate-100">

                                                <tr>

                                                    <th
                                                        class="px-6 py-3 text-left font-black uppercase tracking-wider text-slate-500 text-[10px] w-1/2">
                                                        Subject
                                                    </th>

                                                    <th
                                                        class="px-6 py-3 text-left font-black uppercase tracking-wider text-slate-500 text-[10px]">
                                                        Grade
                                                    </th>

                                                    <th
                                                        class="px-6 py-3 text-left font-black uppercase tracking-wider text-slate-500 text-[10px]">
                                                        Grade Point
                                                    </th>

                                                    <th
                                                        class="px-6 py-3 text-center font-black uppercase tracking-wider text-slate-500 text-[10px]">
                                                        Action
                                                    </th>

                                                </tr>

                                            </thead>

                                            <tbody id="subjectsTableBody-{{ $semKey }}">

                                                @forelse($result->subjects as $subKey => $subject)
                                                    <tr class="border-b border-gray-50 subject-row">

                                                        <td class="px-6 py-3">

                                                            <select
                                                                name="semesters[{{ $semKey }}][subjects][{{ $subKey }}][subjectT_id]"
                                                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs">

                                                                <option value="">-- Pilih Subjek --</option>

                                                                @foreach ($subjects as $subjectItem)
                                                                    <option value="{{ $subjectItem->subjectT_id }}"
                                                                        {{ $subject->subjectT_id == $subjectItem->subjectT_id ? 'selected' : '' }}>
                                                                        [{{ $subjectItem->subjectT_code }}]
                                                                        {{ $subjectItem->subjectT_name }}
                                                                    </option>
                                                                @endforeach

                                                            </select>

                                                        </td>

                                                        {{-- GRADE --}}
                                                        <td class="px-6 py-3">

                                                            <select
                                                                name="semesters[{{ $semKey }}][subjects][{{ $subKey }}][grade]"
                                                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs">

                                                                @foreach (['A', 'B+', 'B', 'C+', 'C', 'D+', 'D', 'F'] as $grade)
                                                                    <option value="{{ $grade }}"
                                                                        {{ $subject->grade == $grade ? 'selected' : '' }}>
                                                                        {{ $grade }}
                                                                    </option>
                                                                @endforeach

                                                            </select>

                                                        </td>

                                                        {{-- GRADE POINT --}}
                                                        <td class="px-6 py-3">

                                                            <input type="number" step="0.01" min="0"
                                                                max="4"
                                                                name="semesters[{{ $semKey }}][subjects][{{ $subKey }}][grade_point]"
                                                                value="{{ $subject->grade_point }}"
                                                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs">

                                                        </td>

                                                        {{-- REMOVE --}}
                                                        <td class="px-6 py-3 text-center">

                                                            <button type="button" onclick="this.closest('tr').remove()"
                                                                class="text-red-500 hover:text-red-700 font-bold text-xs">
                                                                Remove
                                                            </button>

                                                        </td>

                                                    </tr>

                                                @empty

                                                    {{-- EMPTY SUBJECT ROW --}}
                                                    <tr class="border-b border-gray-50 subject-row">

                                                        <td class="px-6 py-3">

                                                            <select
                                                                name="semesters[{{ $semKey }}][subjects][0][subjectT_id]"
                                                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs">

                                                                <option value="">-- Pilih Subjek --</option>

                                                                @foreach ($subjects as $subjectItem)
                                                                    <option value="{{ $subjectItem->subjectT_id }}">
                                                                        [{{ $subjectItem->subjectT_code }}]
                                                                        {{ $subjectItem->subjectT_name }}
                                                                    </option>
                                                                @endforeach

                                                            </select>

                                                        </td>

                                                        <td class="px-6 py-3">

                                                            <select
                                                                name="semesters[{{ $semKey }}][subjects][0][grade]"
                                                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs">

                                                                <option value="">-- Grade --</option>

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

                                                            <input type="number" step="0.01" min="0"
                                                                max="4"
                                                                name="semesters[{{ $semKey }}][subjects][0][grade_point]"
                                                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs">

                                                        </td>

                                                        <td class="px-6 py-3 text-center">

                                                            <button type="button" onclick="this.closest('tr').remove()"
                                                                class="text-red-500 hover:text-red-700 font-bold text-xs">
                                                                Remove
                                                            </button>

                                                        </td>

                                                    </tr>
                                                @endforelse

                                            </tbody>

                                        </table>

                                    </div>

                                </div>

                            @empty

                                {{-- EMPTY SEMESTER CARD --}}
                                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden semester-card"
                                    data-semester-index="0">

                                    <div class="p-8">

                                        <h3 class="font-bold text-gray-500">
                                            No semester records found.
                                        </h3>

                                    </div>

                                </div>

                            @endforelse

                        </div>


                        {{-- BUTTONS --}}
                        <div class="flex justify-end gap-4 border-t border-gray-100 pt-6">
                            <a href="{{ route('admin.masterlist') }}"
                                class="px-8 py-3 text-sm font-bold text-gray-500 hover:text-red-500 transition">Cancel</a>
                            <button type="submit"
                                class="px-12 py-3 bg-purple-900 text-white rounded-xl font-black uppercase tracking-widest shadow-lg hover:bg-purple-800 transition">
                                Update Student
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            {{-- KHATAM RECORDS — kept as its own form outside the main student form,
                 since a student may khatam multiple times and each entry is its own row --}}
            <div class="mt-8 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-5 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="font-black text-gray-700 uppercase tracking-widest text-sm">
                        Khatam Records
                    </h3>
                    <p class="text-xs text-gray-400 mt-1">
                        A student can khatam more than once — every date is recorded separately.
                    </p>
                </div>

                <div class="p-8 space-y-4">
                    @forelse ($student->khatams as $khatam)
                        <div class="flex items-center justify-between gap-4 bg-slate-50 border border-gray-100 rounded-xl px-5 py-3">
                            <div>
                                <span class="font-bold text-gray-700">{{ $khatam->khatam_date->format('d M Y') }}</span>
                                @if ($khatam->notes)
                                    <span class="text-sm text-gray-400 ml-2">{{ $khatam->notes }}</span>
                                @endif
                            </div>
                            <form action="{{ route('admin.masterlist.khatam.destroy', [$student->student_id, $khatam->id]) }}"
                                method="POST" onsubmit="return confirm('Remove this Khatam record?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-bold text-red-500 hover:text-red-700 transition">
                                    Remove
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 italic">No Khatam dates recorded yet.</p>
                    @endforelse

                    <form action="{{ route('admin.masterlist.khatam.store', $student->student_id) }}" method="POST"
                        class="flex flex-wrap items-end gap-3 pt-4 border-t border-gray-100">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Khatam Date</label>
                            <input type="date" name="khatam_date" required
                                class="px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition">
                        </div>
                        <div class="flex-1 min-w-[180px]">
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-2">Notes (optional)</label>
                            <input type="text" name="notes" maxlength="255"
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition">
                        </div>
                        <button type="submit"
                            class="px-6 py-2.5 bg-violet-600 text-white rounded-xl text-sm font-bold hover:bg-violet-700 transition">
                            + Add Khatam
                        </button>
                    </form>
                </div>
            </div>
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

            let semesterCardIndex = document.querySelectorAll('.semester-card').length;

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
                            <th class="px-6 py-3 text-left font-black uppercase tracking-wider text-slate-500 text-[10px] w-1/2">Subject</th>
                            <th class="px-6 py-3 text-left font-black uppercase tracking-wider text-slate-500 text-[10px]">Grade</th>
                            <th class="px-6 py-3 text-left font-black uppercase tracking-wider text-slate-500 text-[10px]">Grade Point</th>
                            <th class="px-6 py-3 text-center font-black uppercase tracking-wider text-slate-500 text-[10px]">Action</th>
                        </tr>
                    </thead>
                    <tbody id="subjectsTableBody-${semesterCardIndex}">
                        <tr class="border-b border-gray-50 subject-row">
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
