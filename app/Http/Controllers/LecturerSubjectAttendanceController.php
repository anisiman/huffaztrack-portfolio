<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsQrAttendanceUrl;
use App\Models\AcademicPeriod;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\StudentSubjectSection;
use App\Models\SubjectAttendanceRecord;
use App\Models\SubjectAttendanceSession;
use App\Models\SubjectSection;
use App\Models\SubjectT;
use App\Services\AttendanceFinalizer;
use App\Services\NotificationService;
use App\Services\SubjectOwnershipResolver;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Subject Attendance — Madah subjects only.
 *
 * Hafazan subjects (Hifz Syafawi/Tahriri) are assessment/carry-mark components
 * of the existing Halaqah/Tasmik process, not independent teaching sessions —
 * a single Halaqah/Tasmik attendance session (see AttendanceController) already
 * represents attendance for that underlying activity. Creating a *second*,
 * separate QR session here for Hifz Syafawi or Hifz Tahriri would duplicate
 * that same roster for a class meeting that doesn't independently exist, so
 * this controller only ever lists/creates sessions for Madah subjects.
 *
 * Historical Hafazan sessions/records created before this restriction remain
 * fully intact and readable (see rosterFor()/studentBelongsToSession()) —
 * only the ability to create *new* ones is removed. Carry marks for Hafazan
 * subjects are entirely unaffected — see LecturerSubjectController, which
 * never touches this controller or its data.
 */
class LecturerSubjectAttendanceController extends Controller
{
    use BuildsQrAttendanceUrl;

    private const MYT = 'Asia/Kuala_Lumpur';

    public function index(): View
    {
        $lecturer = $this->currentLecturer();
        $period = AcademicPeriod::current();

        $rows = collect();

        if ($lecturer && $period) {
            // Madah only — see class docblock for why Hafazan is excluded here.
            $rows = SubjectOwnershipResolver::madahSectionsOwnedBy($lecturer, $period)
                ->map(fn (SubjectSection $section) => [
                    'subject' => $section->subject,
                    'section' => $section,
                    'student_count' => $section->enrollments_count,
                ])
                ->sortBy([
                    fn ($row) => $row['subject']->subjectT_code,
                    fn ($row) => $row['section']->section_name,
                ])
                ->values();
        }

        $todaySessions = collect();
        if ($lecturer) {
            $todaySessions = $this->todaySessionsQuery($lecturer->lecturer_id)
                ->with(['subject', 'batch', 'section'])
                ->withCount('records')
                ->get();
        }

        return view('lecturer.subject-attendance.index', compact('lecturer', 'period', 'rows', 'todaySessions'));
    }

    public function createSession(Request $request): RedirectResponse
    {
        $lecturer = $this->currentLecturer();
        $period = AcademicPeriod::current();

        if (! $lecturer || ! $period) {
            return redirect()->route('lecturer.subjectAttendance')
                ->with('error', 'No active academic semester is currently available.');
        }

        $validated = $request->validate([
            'subjectT_id' => 'required|exists:subjectt,subjectT_id',
        ]);

        $subject = SubjectT::findOrFail($validated['subjectT_id']);

        // Hafazan attendance is tracked via Tasmik Attendance — see class docblock.
        // Checked before the subject_section_id validation below so a stale/forged
        // request against a Hafazan subject gets this specific message rather than
        // a generic "section required" error.
        abort_if($subject->isHafazan(), 403, 'Hifz Syafawi/Tahriri attendance is tracked via Tasmik Attendance, not Subject Attendance.');

        $validated += $request->validate([
            'subject_section_id' => 'required|exists:subject_sections,id',
        ]);

        $section = SubjectSection::findOrFail($validated['subject_section_id']);

        if ($section->subjectT_id != $subject->subjectT_id
            || $section->academic_period_id != $period->id
            || $section->lecturer_id != $lecturer->lecturer_id) {
            abort(403, 'You are not assigned to teach this Section.');
        }

        $roster = SubjectOwnershipResolver::studentsInSection($section);

        if ($roster->isEmpty()) {
            abort(403, 'You have no students to take attendance for in this subject.');
        }

        $existing = $this->todaySessionsQuery($lecturer->lecturer_id)
            ->where('subjectT_id', $subject->subjectT_id)
            ->where('subject_section_id', $section->id)
            ->first();

        if ($existing && $existing->isExpired()) {
            return redirect()->route('lecturer.subjectAttendance')
                ->with('info', 'Today\'s session for this subject has already ended. Use the "Create Session" button again to start a new one (e.g. a makeup class).');
        }

        if ($existing) {
            return redirect()->route('lecturer.subjectAttendance.show', $existing->session_id)
                ->with('success', 'Using today\'s existing session for this subject.');
        }

        $durationMinutes = max(5, min(480, (int) $request->input('duration_minutes', 120)));
        $startTime = now();

        $session = SubjectAttendanceSession::create([
            'session_id' => 'SATT-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)),
            'academic_period_id' => $period->id,
            'subjectT_id' => $subject->subjectT_id,
            'batch_id' => null,
            'subject_section_id' => $section->id,
            'lecturer_id' => $lecturer->lecturer_id,
            'start_time' => $startTime,
            'end_time' => $startTime->copy()->addMinutes($durationMinutes),
        ]);

        return redirect()->route('lecturer.subjectAttendance.show', $session->session_id)
            ->with('success', 'QR code generated successfully for the current attendance session.');
    }

    public function show(string $sessionId): View|RedirectResponse
    {
        $lecturer = $this->currentLecturer();
        $session = $this->ownedSession($sessionId, $lecturer);

        if (! $session) {
            abort(404);
        }

        $eligibleStudents = $this->rosterFor($session);

        // Only on real expiry — a paused session can still be resumed and scanned.
        // The scheduler normally gets here first; this is the fallback.
        if ($session->isExpired() && ! $session->finalized_at) {
            app(AttendanceFinalizer::class)->finalizeSubject($session);
        }

        $records = SubjectAttendanceRecord::where('subject_attendance_session_id', $session->id)
            ->with('student')
            ->orderBy('scanned_at')
            ->get();

        $scannedRows = $records->where('status', 'present')->map(fn (SubjectAttendanceRecord $r) => (object) [
            'student_id' => $r->student?->student_id ?? $r->student_id,
            'student_name' => $r->student?->student_name ?? 'Unknown student',
            'scanned_at' => $r->scanned_at?->copy()->setTimezone(self::MYT),
        ])->values();

        // Full section roster with each student's current record — drives the
        // manual entry/correction table (QR fallback). Only students enrolled in
        // this session's Section appear here (see rosterFor()).
        $recordsByStudent = $records->keyBy('student_id');
        $rosterRows = $eligibleStudents->map(function (Student $s) use ($recordsByStudent) {
            $rec = $recordsByStudent->get($s->student_id);

            return (object) [
                'student_id'   => $s->student_id,
                'student_name' => $s->student_name,
                'nric'         => $s->nric ?? null,
                'status'       => $rec->status ?? 'not_recorded',
                'scanned_at'   => $rec && $rec->scanned_at ? $rec->scanned_at->copy()->setTimezone(self::MYT) : null,
                'via_qr'       => (bool) ($rec && $rec->scanned_at),
            ];
        })->values();

        $session->load(['subject', 'batch', 'section']);

        $qrUrl = $this->buildQrUrl('attendance.subject.scan', ['sessionId' => $session->session_id]);
        $qrCodeSvg = QrCode::size(280)->margin(1)->generate($qrUrl);

        $validFrom = $session->start_time->copy()->setTimezone(self::MYT);
        $validUntil = $session->end_time->copy()->setTimezone(self::MYT);
        $presentCount = $scannedRows->count();
        $totalStudents = $eligibleStudents->count();

        return view('lecturer.subject-attendance.qr', compact(
            'session', 'qrUrl', 'qrCodeSvg', 'validFrom', 'validUntil',
            'presentCount', 'totalStudents', 'scannedRows', 'rosterRows'
        ));
    }

    /**
     * Manual attendance entry / correction — the QR fallback, mirroring
     * Tasmik's AttendanceController::store(). Works whatever the session's
     * state (active, paused, or expired), since a lecturer may only discover
     * a scan failure after the window has closed.
     *
     * Only students enrolled in this session's Section can be marked — any
     * posted student_id outside rosterFor() is silently ignored.
     */
    public function manualUpdate(Request $request, string $sessionId): RedirectResponse
    {
        $lecturer = $this->currentLecturer();
        $session = $this->ownedSession($sessionId, $lecturer);

        if (! $session) {
            abort(404);
        }

        $validated = $request->validate([
            'attendance'   => 'required|array',
            'attendance.*' => 'in:present,absent,not_recorded',
        ]);

        $roster = $this->rosterFor($session)->keyBy('student_id');
        $marked = 0;

        foreach ($validated['attendance'] as $studentId => $status) {
            $student = $roster->get($studentId);
            if (! $student) {
                continue; // not enrolled in this Section — ignore
            }

            $existing = SubjectAttendanceRecord::where('subject_attendance_session_id', $session->id)
                ->where('student_id', $studentId)
                ->first();

            if ($status === 'not_recorded') {
                $existing?->delete();
                $marked++;
                continue;
            }

            if ($existing) {
                $existing->update([
                    'status' => $status,
                    // Preserve a genuine QR check-in time; clear it when a
                    // never-scanned row is set manually so "Check-in" stays honest.
                    'scanned_at' => $status === 'present' ? $existing->scanned_at : null,
                ]);
            } else {
                SubjectAttendanceRecord::create([
                    'subject_attendance_session_id' => $session->id,
                    'student_id'         => $studentId,
                    'academic_period_id' => $session->academic_period_id,
                    'subjectT_id'        => $session->subjectT_id,
                    'batch_id'           => $student->batch_id,
                    'subject_section_id' => $session->subject_section_id,
                    'lecturer_id'        => $session->lecturer_id,
                    'status'             => $status,
                    'scanned_at'         => null,
                    'date'               => $session->start_time->toDateString(),
                ]);
            }

            if ($status === 'absent' && $student->user_id) {
                NotificationService::send(
                    $student->user_id,
                    'attendance',
                    'Subject Attendance — Marked Absent',
                    'You were marked absent for ' . ($session->subject?->subjectT_name ?? 'a subject class')
                        . ' on ' . $session->start_time->format('d M Y') . '. Contact your lecturer if this is incorrect.',
                    '/student/subject-attendance'
                );
            }

            $marked++;
        }

        return redirect()->route('lecturer.subjectAttendance.show', $session->session_id)
            ->with('success', "Attendance updated for {$marked} student(s).");
    }

    public function end(string $sessionId): RedirectResponse
    {
        $lecturer = $this->currentLecturer();
        $session = $this->ownedSession($sessionId, $lecturer);

        if (! $session || $session->isExpired()) {
            return redirect()->back()->with('error', 'Session not found or already expired.');
        }

        $session->update(['ended_at' => now()]);

        return redirect()->back()->with('success', 'QR session paused. Students cannot scan until you resume it.');
    }

    public function resume(string $sessionId): RedirectResponse
    {
        $lecturer = $this->currentLecturer();
        $session = $this->ownedSession($sessionId, $lecturer);

        if (! $session || $session->isExpired()) {
            return redirect()->back()->with('error', 'Cannot resume — the session window has expired.');
        }

        $session->update(['ended_at' => null]);

        return redirect()->back()->with('success', 'QR session resumed. Students can scan again.');
    }

    // ── Public QR scan flow (student-facing, mirrors Tasmik's AttendanceController) ──

    public function scan(Request $request, string $sessionId): RedirectResponse
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $request->session()->put('url.intended', route('attendance.subject.scan.mark', ['sessionId' => $sessionId]));

        return redirect()->route('login');
    }

    public function markScan(Request $request, string $sessionId): RedirectResponse
    {
        $session = SubjectAttendanceSession::where('session_id', $sessionId)->first();

        if (! $session || $session->hasEnded()) {
            $this->logoutScanningStudent($request);

            $errorMessage = $session?->isManuallyPaused()
                ? 'QR session temporarily closed by lecturer.'
                : 'Attendance session has ended.';

            return redirect()->route('attendance.subject.scan.confirmation', ['sessionId' => $sessionId])
                ->with('error', $errorMessage);
        }

        $student = $this->resolveAuthenticatedStudent();

        if (! $student) {
            $this->logoutScanningStudent($request);

            return redirect()->route('attendance.subject.scan.confirmation', ['sessionId' => $sessionId])
                ->with('error', 'Only student accounts can mark attendance.');
        }

        if (! $this->studentBelongsToSession($student, $session)) {
            $this->logoutScanningStudent($request);

            return redirect()->route('attendance.subject.scan.confirmation', ['sessionId' => $sessionId])
                ->with('error', 'You are not part of this session\'s roster.');
        }

        $result = $this->recordAttendance($student, $session);

        if ($result['type'] === 'success' && $student->user_id) {
            NotificationService::send($student->user_id, 'attendance', 'Subject Attendance Recorded', 'Your subject attendance has been recorded successfully.', '/student/subject-attendance');
        }

        $this->logoutScanningStudent($request);

        return redirect()->route('attendance.subject.scan.confirmation', ['sessionId' => $session->session_id])
            ->with($result['type'], $result['message']);
    }

    public function confirmation(string $sessionId): View
    {
        return view('student.attendance-confirmation', compact('sessionId'));
    }

    /**
     * The session's roster, resolved the same way it was resolved at creation
     * time. Only the Section branch is reachable for sessions created from now
     * on (createSession() only ever creates Madah/Section-scoped sessions) —
     * the other two branches exist solely to keep pre-existing historical
     * sessions readable:
     *   - Hafazan: a lecturer's whole Halaqah — from back when this controller
     *     still (incorrectly) created separate sessions for Hifz Syafawi/Tahriri,
     *     duplicating what Tasmik Attendance already tracks. See class docblock.
     *   - Legacy Madah (subject_section_id NULL): the whole batch, from before
     *     Sections existed at all.
     */
    private function rosterFor(SubjectAttendanceSession $session): Collection
    {
        return app(AttendanceFinalizer::class)->subjectRoster($session);
    }

    /** Same branches as rosterFor() (see its docblock), phrased as a membership check for scan validation. */
    private function studentBelongsToSession(Student $student, SubjectAttendanceSession $session): bool
    {
        if ($session->subject_section_id) {
            return StudentSubjectSection::where('subject_section_id', $session->subject_section_id)
                ->where('student_id', $student->student_id)
                ->exists();
        }

        if ($session->subject?->isHafazan()) {
            return (string) $student->lecturer_id === (string) $session->lecturer_id;
        }

        return (string) $student->batch_id === (string) $session->batch_id;
    }

    private function resolveAuthenticatedStudent(): ?Student
    {
        if (! Auth::check()) {
            return null;
        }

        $user = Auth::user();
        if (($user->role ?? null) !== 'student') {
            return null;
        }

        $student = Student::query()->active()->where('user_id', $user->id)->first();
        if ($student) {
            return $student;
        }

        $studentTable = (new Student())->getTable();
        if (Schema::hasColumn($studentTable, 'email') && filled($user->email)) {
            return Student::query()->active()->where('email', $user->email)->first();
        }

        return null;
    }

    private function recordAttendance(Student $student, SubjectAttendanceSession $session): array
    {
        $existing = SubjectAttendanceRecord::where('subject_attendance_session_id', $session->id)
            ->where('student_id', $student->getKey())
            ->first();

        if ($existing) {
            return [
                'type' => 'info',
                'message' => 'Attendance already recorded for this session.',
            ];
        }

        SubjectAttendanceRecord::create([
            'subject_attendance_session_id' => $session->id,
            'student_id' => $student->getKey(),
            'academic_period_id' => $session->academic_period_id,
            'subjectT_id' => $session->subjectT_id,
            'batch_id' => $student->batch_id,
            'subject_section_id' => $session->subject_section_id,
            'lecturer_id' => $session->lecturer_id,
            'status' => 'present',
            'scanned_at' => now(),
            'date' => $session->start_time->toDateString(),
        ]);

        return [
            'type' => 'success',
            'message' => 'Attendance has been recorded successfully.',
        ];
    }

    private function logoutScanningStudent(Request $request): void
    {
        Auth::logout();
        $request->session()->regenerateToken();
    }

    private function currentLecturer(): ?Lecturer
    {
        return Lecturer::where('user_id', Auth::id())->first();
    }

    private function ownedSession(string $sessionId, ?Lecturer $lecturer): ?SubjectAttendanceSession
    {
        if (! $lecturer) {
            return null;
        }

        return SubjectAttendanceSession::where('session_id', $sessionId)
            ->where('lecturer_id', $lecturer->lecturer_id)
            ->first();
    }

    private function todaySessionsQuery(string $lecturerId)
    {
        $dayStart = Carbon::today(self::MYT)->setTimezone('UTC');
        $dayEnd = Carbon::today(self::MYT)->endOfDay()->setTimezone('UTC');

        return SubjectAttendanceSession::where('lecturer_id', $lecturerId)
            ->whereBetween('start_time', [$dayStart, $dayEnd])
            ->orderByDesc('start_time');
    }
}
