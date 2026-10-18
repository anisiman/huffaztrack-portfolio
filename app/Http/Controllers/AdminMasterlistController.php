<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\KhatamRecord;
use App\Models\Student;
use App\Models\User;
use App\Models\Program;
use App\Models\College;
use App\Models\Sponsorship;
use App\Models\Semester;
use App\Models\SubjectT;
use App\Models\Result;
use App\Models\StudentSubject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsNricImport;

class AdminMasterlistController extends Controller
{
    /**
     * Display the student masterlist with custom Batch sorting and full filtering.
     */
    public function index(Request $request): View
    {
        // 1. Konsisten menggunakan $request objek
        $currentBatch = trim($request->input('batch', 'Overall'));

        /* BASE QUERY - Ditambah Eager Loading awal untuk prestasi optimum */
        $baseQuery = Student::with(['batch', 'programs', 'sponsorship', 'khatams']);

        if ($currentBatch !== 'Overall') {
            $baseQuery->whereHas('batch', function ($q) use ($currentBatch) {
                $q->where('batch_name', $currentBatch);
            });
        }

        $studentsQuery = clone $baseQuery;

        // 3. Search Filter (Name or Student ID)
        if ($request->filled('search')) {
            $search = $request->search;
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        // 4. Advanced Filters
        /* GENDER FILTER */
        if ($request->filled('gender')) {
            $studentsQuery->where('gender', $request->gender);
        }

        /* PROGRAM FILTER */
        if ($request->filled('program')) {
            $studentsQuery->whereHas('programs', function ($q) use ($request) {
                // Qualified with table name — 'program_id' exists on both `program`
                // and the `student_programs` pivot, so it's ambiguous unqualified.
                $q->where('program.program_id', $request->program);
            });
        }

        /* COLLEGE FILTER */
        if ($request->filled('college')) {
            $studentsQuery->whereHas('programs', function ($q) use ($request) {
                $q->where('college_id', $request->college);
            });
        }

        /* SPONSORSHIP FILTER */
        if ($request->filled('sponsorship')) {
            $studentsQuery->where('sponsorship_id', $request->sponsorship);
        }

        /* STATUS FILTER */
        if ($request->filled('status')) {
            $studentsQuery->where('status', $request->status);
        }

        // 5. Pagination - withQueryString kekal berfungsi
        $students = $studentsQuery->orderByDesc('batch_id')->paginate(15)->withQueryString();

        // 6. Data for UI Elements
        // Normalize batch names and sort by numeric suffix (T<number>) descending
        $batches = Batch::all()
            ->map(function ($b) {
                $b->batch_name = trim(preg_replace('/\s+/', ' ', (string) $b->batch_name));
                return $b;
            })
            ->sortByDesc(function ($b) {
                return (int) preg_replace('/\D/', '', $b->batch_name);
            })->values();
        $programs = Program::with('college')->get();
        $colleges = College::all();
        $sponsorships = Sponsorship::all();

        // 7. Dashboard Card Counts (Menggunakan objek clone)
        $activeCount = (clone $baseQuery)->where('status', 'AKTIF')->count();
        $completedCount = (clone $baseQuery)->where('status', 'TAMAT PENSIJILAN')->count();
        $droppedCount = (clone $baseQuery)->whereIn('status', ['GAGAL DAN DIBERHENTIKAN', 'TARIK DIRI'])->count();
        $totalEnrollment = (clone $baseQuery)->count();
        $overallTotal = Student::count();

        // Percentages alongside each summary card's count
        $activePercent = $totalEnrollment > 0 ? round(($activeCount / $totalEnrollment) * 100, 1) : 0;
        $completedPercent = $totalEnrollment > 0 ? round(($completedCount / $totalEnrollment) * 100, 1) : 0;
        $droppedPercent = $totalEnrollment > 0 ? round(($droppedCount / $totalEnrollment) * 100, 1) : 0;
        $enrollmentPercent = $overallTotal > 0 ? round(($totalEnrollment / $overallTotal) * 100, 1) : 0;

        // For an inactive batch, the summary card shows Completed count instead of Active
        $currentBatchModel = $currentBatch !== 'Overall' ? $batches->firstWhere('batch_name', $currentBatch) : null;
        $isInactiveBatch = (bool) ($currentBatchModel && ! $currentBatchModel->is_active);

        return view('admin.masterlist', compact(
            'students',
            'batches',
            'programs',
            'currentBatch',
            'activeCount',
            'completedCount',
            'droppedCount',
            'totalEnrollment',
            'activePercent',
            'completedPercent',
            'droppedPercent',
            'enrollmentPercent',
            'isInactiveBatch',
            'colleges',
            'sponsorships'
        ));
    }

    /**
     * Handle Excel upload for masterlist. Matches/creates students by `nric`.
     */
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'batch_id' => 'nullable|exists:batch,batch_id',
        ]);

        $batchId = $validated['batch_id'] ?? null;

        try {
            Excel::import(new StudentsNricImport($batchId), $request->file('file'));
            return redirect()->route('admin.masterlist')->with('success', 'Masterlist uploaded successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Record a new Khatam date for a student from the masterlist table. A
     * student can khatam more than once, so this always adds a fresh row
     * rather than overwriting a single date.
     */
    public function addKhatamRecord(Request $request, $id): RedirectResponse
    {
        $student = Student::where('student_id', $id)->firstOrFail();

        $validated = $request->validate([
            'khatam_date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $student->khatams()->create([
            'khatam_date' => $validated['khatam_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Khatam date recorded.');
    }

    /**
     * Delete a single Khatam record (correcting a mistaken entry).
     */
    public function deleteKhatamRecord($id, $khatamId): RedirectResponse
    {
        KhatamRecord::where('student_id', $id)->where('id', $khatamId)->delete();

        return back()->with('success', 'Khatam record removed.');
    }

    /**
     * Show the form for creating a new student.
     */
public function create()
{
    // Only active batches are offered — mirrors Batch Management, and a new
    // student shouldn't be assignable into a retired/inactive batch.
    $batches = Batch::active()->get()
        ->map(fn($b) => tap($b, fn($x) => $x->batch_name = trim(preg_replace('/\s+/', ' ', (string) $x->batch_name))))
        ->sortByDesc(fn($b) => (int) preg_replace('/\D/', '', $b->batch_name))
        ->values();

    $programs = Program::all();

    $sponsorships = Sponsorship::all();

    $semesters = Semester::all();

    $subjects = SubjectT::orderBy('subjectT_code', 'asc')->get();

    $currentBatch = $batches->first()?->batch_name ?? null;

    return view('admin.masterlist.create', compact(

        'batches',
        'programs',
        'sponsorships',
        'semesters',
        'currentBatch',
        'subjects'

    ));
}

    /**
     * Show detailed information for a specific student.
     */
    public function show(Student $student)
    {
        // Eager-load the academic history PLUS the sidebar profile metadata relationships
        $student->load([
            'activeProgram',
            'batch',
            'sponsorship',
            'user',

            'results.semester',
            'results.subjects.subjectT',
            'notes.lecturer',
        ]);

        return view('admin.masterlist.show', compact('student'));
}
    

    /**
     * Show the edit form for a student.
     */
    public function edit(Student $student)
    {
        $student->load([
            'activeProgram',
            'results.semester',
            'results.subjects.subjectT',
            'khatams'
        ]);

        $programs = Program::all();
        $batches = Batch::all();
        $sponsorships = Sponsorship::all();
        $semesters = Semester::all();
        $subjects = SubjectT::orderBy('subjectT_code')->get();

        return view('admin.masterlist.edit', compact(
            'student',
            'programs',
            'batches',
            'sponsorships',
            'semesters',
            'subjects'
        ));
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([

            'student_id' => ['required', 'string', 'max:50', 'regex:/^[^\-\/]+$/'],
            'student_name' => 'required|string|max:255',

            'gender' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',

            'batch_id' => 'nullable',
            'program_id' => 'nullable',
            'sponsorship_id' => 'nullable',

            'status' => 'nullable|string',

            'address' => 'nullable|string',
            'state' => 'nullable|string',

            'secondary_school' => 'nullable|string',

            'remarks' => 'nullable|string',

            'semesters' => 'nullable|array',

            'semesters.*.semester_id' => 'nullable',

            'semesters.*.gpa' => 'nullable|numeric',
            'semesters.*.cgpa' => 'nullable|numeric',

            'semesters.*.subjects' => 'nullable|array',

            'semesters.*.subjects.*.subjectT_id' => 'nullable',
            'semesters.*.subjects.*.grade' => 'nullable',
            'semesters.*.subjects.*.grade_point' => 'nullable',

        ], [
            'student_id.regex' => 'ID Tahfiz cannot contain dashes (-) or slashes (/).',
        ]);

        DB::transaction(function () use ($validated, $student) {

            /*
        |--------------------------------------------------------------------------
        | UPDATE STUDENT
        |--------------------------------------------------------------------------
        */

            $student->update([

                'student_id' => $validated['student_id'],
                'student_name' => strtoupper($validated['student_name']),
                'gender' => $validated['gender'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'batch_id' => $validated['batch_id'] ?? null,
                'sponsorship_id' => $validated['sponsorship_id'] ?? null,
                'status' => $validated['status'] ?? null,
                'address' => $validated['address'] ?? null,
                'state' => $validated['state'] ?? null,
                'secondary_school' => $validated['secondary_school'] ?? null,
                'remarks' => $validated['remarks'] ?? null,

            ]);

/*
|--------------------------------------------------------------------------
| UPDATE PROGRAM RELATION
|--------------------------------------------------------------------------
*/

if (!empty($validated['program_id'])) {

    // remove old current flags
    $student->programs()->sync([]);

    // attach new current program
    $student->programs()->attach(

        $validated['program_id'],

        [
            'is_current' => true,
            'start_date' => now(),
        ]

    );
}

            /*
        |--------------------------------------------------------------------------
        | DELETE OLD RECORDS
        |--------------------------------------------------------------------------
        */

            StudentSubject::where('student_id', $student->student_id)->delete();

            Result::where('student_id', $student->student_id)->delete();

            /*
        |--------------------------------------------------------------------------
        | RECREATE RESULTS + SUBJECTS
        |--------------------------------------------------------------------------
        */
        if (!empty($validated['semesters'])) {

            foreach ($validated['semesters'] as $semesterData) {

            if (empty($semesterData['semester_id'])) {
                continue;
            }

            $result = Result::create([

                'student_id' => $student->student_id,
                'semester_id' => $semesterData['semester_id'],
                'gpa' => $semesterData['gpa'] ?? null,
                'cgpa' => $semesterData['cgpa'] ?? null,

            ]);

            if (!empty($semesterData['subjects'])) {

                foreach ($semesterData['subjects'] as $subjectData) {

                    if (empty($subjectData['subjectT_id'])) {
                        continue;
                    }

                    StudentSubject::create([

                        'student_id' => $student->student_id,
                        'result_id' => $result->result_id,
                        'subjectT_id' => $subjectData['subjectT_id'],
                        'grade' => $subjectData['grade'] ?? null,
                        'grade_point' => $subjectData['grade_point'] ?? null,

                    ]);
                }
            }
        }
        }
        });


        return redirect()
            ->route('admin.masterlist')
            ->with('success', 'Student updated successfully.');
    }

    /**
     * Delete a student and their associated records.
     */
    public function destroy($student_id): RedirectResponse
    {
        try {
            DB::transaction(function () use ($student_id) {
                $student = Student::where('student_id', $student_id)->firstOrFail();

                // Padam akaun User jika ada menggunakan emel pelajar
                User::where('email', $student->email)->delete();

                // Sekiranya perhubungan Many-to-Many digunakan, disyorkan untuk detach secara manual 
                // jika tiada 'cascade on delete' di peringkat pangkalan data / junction table migration.
                $student->programs()->detach();

                $student->delete();
            });

            return redirect()->route('admin.masterlist')->with('success', 'Student record deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting student: ' . $e->getMessage());
        }
    }

    /**
     * Store a new student and create their login account.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Validasi asas pelajar (Mengekalkan kod asal anda)
            'student_id' => ['required', 'string', 'max:50', 'regex:/^[^\-\/]+$/', 'unique:student,student_id'],
            'student_name' => 'required|string|max:255',
            'nric' => 'required|string|max:20',
            'email' => 'nullable|email|unique:student,email',
            'phone' => 'nullable|string|max:20',
            'gender' => 'required|in:LELAKI,PEREMPUAN',
            'status' => 'required|string',
            'batch_id' => 'required|exists:batch,batch_id',
            'program_id' => 'required|exists:program,program_id',
            'sponsorship_id' => 'nullable|exists:sponsorship,sponsorship_id',
            'address' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'secondary_school' => 'nullable|string|max:255',
            'asasi_diploma_id' => 'nullable|string|max:50',
            'ijazah_id' => 'nullable|string|max:50',
            'remarks' => 'nullable|string',

            // VALIDASI STRUKTUR NESTED SEMESTER TERBAHARU
            'semesters' => 'nullable|array',
            'semesters.*.semester_id' => 'nullable|exists:semester,semester_id',
            'semesters.*.gpa' => 'nullable|numeric|min:0|max:4',
            'semesters.*.cgpa' => 'nullable|numeric|min:0|max:4',
            'semesters.*.subjects' => 'nullable|array',
            'semesters.*.subjects.*.subjectT_id' => 'nullable|exists:subjectt,subjectT_id',
            'semesters.*.subjects.*.grade' => 'nullable|string|max:5',
            'semesters.*.subjects.*.grade_point' => 'nullable|numeric|min:0|max:4',
        ], [
            'student_id.regex' => 'ID Tahfiz cannot contain dashes (-) or slashes (/).',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // 1. Simpan Maklumat Pelajar
                $student = Student::create([
                    'student_id' => $validated['student_id'],
                    'student_name' => strtoupper($validated['student_name']),
                    'nric' => $validated['nric'],
                    'email' => $validated['email'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                    'gender' => $validated['gender'],
                    'address' => $validated['address'] ?? null,
                    'state' => $validated['state'] ?? null,
                    'secondary_school' => $validated['secondary_school'] ?? null,
                    'asasi_diploma_id' => $validated['asasi_diploma_id'] ?? null,
                    'ijazah_id' => $validated['ijazah_id'] ?? null,
                    'remarks' => $validated['remarks'] ?? null,
                    'status' => $validated['status'],
                    'batch_id' => $validated['batch_id'],
                    'sponsorship_id' => $validated['sponsorship_id'] ?? null,
                ]);

/*
|--------------------------------------------------------------------------
| ATTACH CURRENT PROGRAM
|--------------------------------------------------------------------------
*/

$student->programs()->attach(

    $validated['program_id'],

    [
        'is_current' => true,
        'start_date' => now(),
    ]

);

foreach ($validated['semesters'] as $semData) {

    if (empty($semData['semester_id'])) {
        continue;
    }

    $result = Result::create([

        'student_id' => $student->student_id,
        'semester_id' => $semData['semester_id'],
        'gpa' => $semData['gpa'] ?? null,
        'cgpa' => $semData['cgpa'] ?? null,

    ]);

    if (!empty($semData['subjects'])) {

        foreach ($semData['subjects'] as $subjectData) {

            if (empty($subjectData['subjectT_id'])) {
                continue;
            }

            StudentSubject::create([

                'student_id' => $student->student_id,
                'result_id' => $result->result_id,
                'subjectT_id' => $subjectData['subjectT_id'],
                'grade' => $subjectData['grade'] ?? null,
                'grade_point' => $subjectData['grade_point'] ?? null,

            ]);
        }
    }
}

/*
|--------------------------------------------------------------------------
| USER ACCOUNT
|--------------------------------------------------------------------------
| No login is created here. The student registers at /registration, which
| verifies their Student ID and email against this record and links
| student.user_id.
*/
            });
            return redirect()->route('admin.masterlist')->with('success', 'Rekod maklumat pelajar, kad semester, dan gred subjek berjaya disimpan.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
}
