<?php

namespace App\Http\Controllers;

use App\Models\KhatamRecord;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\StudentNote;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LecturerStudentController extends Controller
{
    private function getLecturerId(): ?string
    {
        return Lecturer::where('user_id', Auth::id())->value('lecturer_id');
    }

    public function index(Request $request)
    {
        $lecturerId = $this->getLecturerId();

        $search = $request->input('search');

        $query = Student::with(['batch', 'program', 'khatams'])
            ->operational()
            ->where('lecturer_id', $lecturerId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('student_name')->get();

        return view('lecturer.students.index', compact('students', 'search'));
    }

    public function show(string $studentId)
    {
        $lecturerId = $this->getLecturerId();

        $student = Student::with([
                'batch',
                'program',
                'sponsorship',
                'user',
                'results.semester',
                'results.subjects.subjectT',
                'notes.lecturer',
            ])
            ->where('lecturer_id', $lecturerId)
            ->findOrFail($studentId);

        return view('lecturer.students.show', compact('student', 'lecturerId'));
    }

    public function storeKhatam(Request $request, string $studentId)
    {
        $lecturerId = $this->getLecturerId();

        $student = Student::where('lecturer_id', $lecturerId)->findOrFail($studentId);

        $validated = $request->validate([
            'khatam_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $student->khatams()->create($validated);

        return back()->with('success', 'Khatam record added.');
    }

    public function destroyKhatam(string $studentId, KhatamRecord $khatam)
    {
        $lecturerId = $this->getLecturerId();

        Student::where('lecturer_id', $lecturerId)->findOrFail($studentId);

        if ($khatam->student_id !== $studentId) {
            abort(403);
        }

        $khatam->delete();

        return back()->with('success', 'Khatam record removed.');
    }

    public function storeNote(Request $request, string $studentId)
    {
        $lecturerId = $this->getLecturerId();

        $student = Student::where('lecturer_id', $lecturerId)->findOrFail($studentId);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'is_private' => ['nullable', 'boolean'],
        ]);

        $note = $student->notes()->create([
            'lecturer_id' => $lecturerId,
            'body' => $validated['body'],
            'is_private' => $request->boolean('is_private'),
        ]);

        if (! $note->is_private) {
            NotificationService::notifyStudent(
                $studentId,
                'note',
                'New note from your lecturer',
                \Illuminate\Support\Str::limit($validated['body'], 100),
                route('student.notes')
            );
        }

        return back()->with('success', 'Note added.');
    }

    public function updateNote(Request $request, string $studentId, StudentNote $note)
    {
        $lecturerId = $this->getLecturerId();

        Student::where('lecturer_id', $lecturerId)->findOrFail($studentId);

        if ($note->student_id !== $studentId || (string) $note->lecturer_id !== (string) $lecturerId) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'is_private' => ['nullable', 'boolean'],
        ]);

        $note->update([
            'body' => $validated['body'],
            'is_private' => $request->boolean('is_private'),
        ]);

        return back()->with('success', 'Note updated.');
    }

    public function destroyNote(string $studentId, StudentNote $note)
    {
        $lecturerId = $this->getLecturerId();

        Student::where('lecturer_id', $lecturerId)->findOrFail($studentId);

        if ($note->student_id !== $studentId || (string) $note->lecturer_id !== (string) $lecturerId) {
            abort(403);
        }

        $note->delete();

        return back()->with('success', 'Note removed.');
    }
}
