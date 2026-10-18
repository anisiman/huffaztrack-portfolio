<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceAppeal;
use App\Models\Lecturer;
use App\Models\Student;
use App\Services\NotificationService;
use App\Services\WarningLetterService;
use App\Support\AttendanceDocuments;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceAppealController extends Controller
{
    // ── Student: submit excuse letter ──────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $student = Student::where('user_id', Auth::id())->first();
        if (! $student) {
            abort(403);
        }

        $request->validate([
            'attendance_id' => 'required|integer|exists:attendance,attendance_id',
            'reason_type'   => 'nullable|string|max:100',
            'remarks'       => 'nullable|string|max:500',
            // Phones commonly produce heic/heif/webp — accept them so a valid
            // excuse photo isn't silently rejected.
            'document'      => 'required|file|mimes:pdf,jpg,jpeg,png,webp,heic,heif|max:10240',
        ], [
            'document.required' => 'Please attach a supporting document (PDF or photo).',
            'document.mimes'    => 'The document must be a PDF or an image (JPG, PNG, WEBP, HEIC).',
            'document.max'      => 'The document may not be larger than 10 MB.',
        ]);

        $attendance = Attendance::where('attendance_id', $request->attendance_id)
            ->where('student_id', $student->student_id)
            ->where('status', 'absent')
            ->first();

        if (! $attendance) {
            return redirect()->back()->with('error', 'This attendance record is not eligible for an appeal.');
        }

        // One live appeal per date. A *rejected* one may be resubmitted — the
        // row is reused (unique key on attendance_id) and reset to pending.
        $existing = AttendanceAppeal::where('attendance_id', $attendance->attendance_id)->first();
        if ($existing && ! $existing->isRejected()) {
            return redirect()->back()->with('error', 'An appeal for this date has already been submitted.');
        }

        $file     = $request->file('document');
        $ext      = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'dat');
        // student_id can contain "/" (e.g. DEMO-0001/2024) — sanitise so storeAs()
        // writes one flat file instead of scattering into sub-directories.
        $safeId   = str_replace(['/', '\\', ' '], '-', $student->student_id);
        $filename = $safeId . '_' . $attendance->date->format('Y-m-d') . '_' . now()->timestamp . '_appeal.' . $ext;
        $path     = AttendanceDocuments::store($file, 'appeal_documents', $filename);

        if ($existing) {
            // Resubmission after rejection: swap in the new document, clear the
            // previous decision, and send it back to the pending queue.
            AttendanceDocuments::delete($existing->document_path);
            $existing->update([
                'reason_type'       => $request->reason_type ?: null,
                'remarks'           => $request->remarks ?: null,
                'document_path'     => $path,
                'status'            => 'pending',
                'rejection_remarks' => null,
                'decided_at'        => null,
            ]);
        } else {
            AttendanceAppeal::create([
                'attendance_id' => $attendance->attendance_id,
                'student_id'    => $student->student_id,
                'lecturer_id'   => $student->lecturer_id,
                'date'          => $attendance->date,
                'reason_type'   => $request->reason_type ?: null,
                'remarks'       => $request->remarks ?: null,
                'document_path' => $path,
                'status'        => 'pending',
            ]);
        }

        NotificationService::notifyLecturerByStudentId(
            $student->student_id,
            'attendance',
            $existing ? 'Excuse Letter Resubmitted' : 'Excuse Letter Submitted',
            "{$student->student_name} " . ($existing ? 'resubmitted' : 'submitted') . ' an excuse letter for '
                . $attendance->date->format('d M Y') . '.',
            '/lecturer/attendance/appeals'
        );

        return redirect()->back()->with('success', 'Your excuse letter has been submitted. Your lecturer will review it shortly.');
    }

    // ── Lecturer: view pending + history ───────────────────────────────────

    public function lecturerIndex(): View
    {
        $lecturerId = Lecturer::where('user_id', Auth::id())->value('lecturer_id');
        if (! $lecturerId) {
            abort(403);
        }

        $pending = AttendanceAppeal::with('student')
            ->where('lecturer_id', $lecturerId)
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get();

        // Include decisions made by a previous lecturer for students now in
        // this halaqah, so history survives a halaqah transfer.
        $history = AttendanceAppeal::with(['student', 'lecturer'])
            ->where(fn ($q) => $q->where('lecturer_id', $lecturerId)
                ->orWhereIn('student_id', Student::where('lecturer_id', $lecturerId)->select('student_id')))
            ->whereIn('status', ['approved', 'rejected'])
            ->orderByDesc('decided_at')
            ->limit(50)
            ->get();

        return view('lecturer.attendance-appeals', compact('pending', 'history', 'lecturerId'));
    }

    // ── Lecturer: approve ──────────────────────────────────────────────────

    public function approve(Request $request, int $id): RedirectResponse
    {
        $lecturerId = Lecturer::where('user_id', Auth::id())->value('lecturer_id');
        $appeal = AttendanceAppeal::where('id', $id)
            ->where('lecturer_id', $lecturerId)
            ->where('status', 'pending')
            ->firstOrFail();

        $appeal->update(['status' => 'approved', 'decided_at' => now()]);

        $attendance = Attendance::find($appeal->attendance_id);
        if ($attendance) {
            $attendance->update([
                'status'             => 'absent_with_reason',
                'reason_type'        => $appeal->reason_type,
                'remarks'            => $appeal->remarks,
                'supporting_document'=> $appeal->document_path,
            ]);
            app(WarningLetterService::class)->recalculate($appeal->student_id);
        }

        NotificationService::notifyStudent(
            $appeal->student_id,
            'attendance',
            'Excuse Letter Approved',
            'Your excuse letter for ' . $appeal->date->format('d M Y') . ' has been approved. Your attendance is now Excused (THS).',
            '/student/attendance'
        );

        return redirect()->back()->with('success', 'Appeal approved. Attendance updated to Excused (THS).');
    }

    // ── Lecturer: reject ───────────────────────────────────────────────────

    public function reject(Request $request, int $id): RedirectResponse
    {
        $lecturerId = Lecturer::where('user_id', Auth::id())->value('lecturer_id');
        $appeal = AttendanceAppeal::where('id', $id)
            ->where('lecturer_id', $lecturerId)
            ->where('status', 'pending')
            ->firstOrFail();

        $rejectionRemarks = $request->input('rejection_remarks') ?: null;

        $appeal->update([
            'status'             => 'rejected',
            'decided_at'         => now(),
            'rejection_remarks'  => $rejectionRemarks,
        ]);

        $msg = 'Your excuse letter for ' . $appeal->date->format('d M Y') . ' was not approved.';
        if ($rejectionRemarks) {
            $msg .= ' Remarks: ' . $rejectionRemarks;
        }

        NotificationService::notifyStudent(
            $appeal->student_id,
            'attendance',
            'Excuse Letter Not Approved',
            $msg,
            '/student/attendance'
        );

        return redirect()->back()->with('success', 'Appeal rejected.');
    }

    // ── Lecturer: undo a decision (mis-click recovery) ─────────────────────

    /**
     * Send a decided appeal back to "pending". If it had been approved, the
     * approval's side-effects on the attendance row (flipped to Excused, reason
     * /remarks/document copied over) are rolled back too — but only if that row
     * still looks exactly as approve() left it, so a later manual edit by the
     * lecturer is never silently overwritten.
     */
    public function undo(Request $request, int $id): RedirectResponse
    {
        $lecturerId = Lecturer::where('user_id', Auth::id())->value('lecturer_id');
        $appeal = AttendanceAppeal::where('id', $id)
            ->where('lecturer_id', $lecturerId)
            ->whereIn('status', ['approved', 'rejected'])
            ->firstOrFail();

        $wasApproved = $appeal->isApproved();

        if ($wasApproved) {
            $attendance = Attendance::find($appeal->attendance_id);
            if ($attendance
                && $attendance->status === 'absent_with_reason'
                && (string) $attendance->supporting_document === (string) $appeal->document_path) {
                $attendance->update([
                    'status'              => 'absent',
                    'reason_type'         => null,
                    'remarks'             => null,
                    'supporting_document' => null,
                ]);
                app(WarningLetterService::class)->recalculate($appeal->student_id);
            }
        }

        $appeal->update([
            'status'            => 'pending',
            'decided_at'        => null,
            'rejection_remarks' => null,
        ]);

        NotificationService::notifyStudent(
            $appeal->student_id,
            'attendance',
            'Excuse Letter — Decision Reverted',
            'The decision on your excuse letter for ' . $appeal->date->format('d M Y')
                . ' has been undone by your lecturer. It is pending review again.',
            '/student/attendance'
        );

        $note = $wasApproved
            ? 'Approval undone. Attendance reverted to Absent (TH) and the appeal is pending review again.'
            : 'Rejection undone. The appeal is pending review again.';

        return redirect()->back()->with('success', $note);
    }
}
