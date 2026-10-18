<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceAppeal;
use App\Models\Lecturer;
use App\Models\Student;
use App\Support\AttendanceDocuments;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves attendance supporting documents and appeal documents after checking
 * the signed-in user may see them. The file path always comes from the
 * database record, never from the request.
 */
class AttendanceDocumentController extends Controller
{
    /** Types shown in the browser; anything else is forced to download. */
    private const INLINE_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/heic',
        'image/heif',
    ];

    public function attendance(int $attendanceId): Response
    {
        $attendance = Attendance::findOrFail($attendanceId);

        abort_unless($this->canViewStudentDocument($attendance->student_id), 403);

        return $this->serve($attendance->supporting_document);
    }

    public function appeal(int $appealId): Response
    {
        $appeal = AttendanceAppeal::findOrFail($appealId);

        // Mirrors AttendanceAppealController::lecturerIndex(): the appeal's own
        // lecturer, or the student's current halaqah lecturer (history view).
        abort_unless($this->canViewStudentDocument($appeal->student_id, $appeal->lecturer_id), 403);

        return $this->serve($appeal->document_path);
    }

    /**
     * Admins: any document. Students: only their own. Lecturers: students
     * currently assigned to them (as in AttendanceController), or the lecturer
     * an appeal was submitted to.
     */
    private function canViewStudentDocument(string $studentId, $appealLecturerId = null): bool
    {
        $user = Auth::user();

        return match ($user->role) {
            'admin' => true,
            'student' => Student::where('user_id', $user->id)->value('student_id') === $studentId,
            'lecturer' => $this->lecturerCanView($user->id, $studentId, $appealLecturerId),
            default => false,
        };
    }

    private function lecturerCanView(int $userId, string $studentId, $appealLecturerId): bool
    {
        $lecturerId = Lecturer::where('user_id', $userId)->value('lecturer_id');

        if (! $lecturerId) {
            return false;
        }

        if ($appealLecturerId !== null && (string) $appealLecturerId === (string) $lecturerId) {
            return true;
        }

        return Student::where('student_id', $studentId)
            ->where('lecturer_id', $lecturerId)
            ->exists();
    }

    private function serve(?string $path): Response
    {
        $disk = AttendanceDocuments::diskFor($path);

        abort_unless($disk, 404);

        $mime = Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream';
        $inline = in_array($mime, self::INLINE_TYPES, true);

        return Storage::disk($disk)->response(
            $path,
            basename($path),
            [
                'Content-Type' => $inline ? $mime : 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ],
            $inline ? 'inline' : 'attachment'
        );
    }
}
