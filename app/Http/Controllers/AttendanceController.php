<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsQrAttendanceUrl;
use App\Models\Attendance;
use App\Models\AttendanceAppeal;
use App\Models\AttendanceQrSession;
use App\Models\Lecturer;
use App\Models\Student;
use App\Services\AttendanceFinalizer;
use App\Services\NotificationService;
use App\Services\WarningLetterService;
use App\Support\AttendanceDocuments;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AttendanceController extends Controller
{
    use BuildsQrAttendanceUrl;

    private const MYT = 'Asia/Kuala_Lumpur';
    public function index(Request $request): View
    {
        $today        = Carbon::today();
        $selectedDate = Carbon::parse($request->input('date', $today->toDateString()))->startOfDay();
        $isToday      = $selectedDate->isSameDay($today);
        $lecturerId   = $this->currentLecturerId();
        $students     = $this->assignedStudentsQuery($lecturerId)->get();
        $studentIds   = $students->pluck('student_id');

        // QR session — only relevant for today
        $currentSession   = null;
        $currentSessionId = '';
        $attendanceRows   = collect();

        if ($isToday) {
            $currentSessionId = trim((string) $request->query('session_id', session('lecturer_attendance_session_id', '')));
            $currentSession   = $this->getTodayLecturerSession($lecturerId)
                ?? $this->getLecturerSession($currentSessionId, $lecturerId);
            $currentSessionId = $currentSession?->session_id ?? '';
            if ($currentSessionId !== '') {
                session(['lecturer_attendance_session_id' => $currentSessionId]);
            }

            // Non-scanners become absent when the session expires. The scheduler
            // (attendance:finalize-expired) normally does this; this is the fallback.
            if ($currentSession && $currentSession->isExpired() && ! $currentSession->finalized_at) {
                app(AttendanceFinalizer::class)->finalizeTasmik($currentSession);
            }

            $sessionAttendances = $this->getSessionAttendances($currentSession?->session_id ?? '', $studentIds);
            $attendanceRows     = $this->buildAttendanceRows($sessionAttendances);
        }

        // All attendance for the selected date (QR + manual combined)
        $dateAttendance = Attendance::query()
            ->whereIn('student_id', $studentIds)
            ->whereDate('date', $selectedDate)
            ->get()
            ->keyBy('student_id');

        $totalStudents    = $students->count();
        $presentCount     = $dateAttendance->filter(fn ($a) => strtolower($a->status) === 'present')->count();
        $absentCount      = $dateAttendance->filter(fn ($a) => strtolower($a->status) === 'absent')->count();
        $excusedCount     = $dateAttendance->filter(fn ($a) => strtolower($a->status) === 'absent_with_reason')->count();
        $recordedCount    = $presentCount + $absentCount + $excusedCount;
        $notRecordedCount = $totalStudents - $recordedCount;
        $attendanceRate   = $recordedCount > 0 ? round(($presentCount / $recordedCount) * 100) : 0;

        $historyRows = $this->buildHistoryRows($students, $dateAttendance);

        $pendingAppealsCount = AttendanceAppeal::where('lecturer_id', $lecturerId)
            ->where('status', 'pending')
            ->count();

        return view('lecturer.attendance', compact(
            'today',
            'selectedDate',
            'isToday',
            'presentCount',
            'absentCount',
            'excusedCount',
            'notRecordedCount',
            'attendanceRate',
            'attendanceRows',
            'currentSessionId',
            'totalStudents',
            'currentSession',
            'students',
            'dateAttendance',
            'historyRows',
            'pendingAppealsCount',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        // One optional supporting document per student, keyed by student_id.
        // Types match the form's accept list (PDF, JPG, PNG).
        $request->validate([
            'document'   => 'nullable|array',
            'document.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            'document.*.file'  => 'Each supporting document must be a valid file.',
            'document.*.mimes' => 'Supporting documents must be a PDF, JPG or PNG file.',
            'document.*.max'   => 'Supporting documents may not be larger than 10 MB.',
        ]);

        $lecturerId = $this->currentLecturerId();
        $wls        = app(WarningLetterService::class);

        // The lecturer may be editing any date's attendance, not just today's.
        $targetDate = $request->filled('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : Carbon::today();
        $isTargetToday = $targetDate->isSameDay(Carbon::today());

        foreach ($request->input('attendance', []) as $studentId => $status) {
            if (! $this->assignedStudentsQuery($lecturerId)->where('student_id', $studentId)->exists()) {
                continue;
            }

            // Not Recorded = no meaningful record; remove any stale row and move on
            if ($status === 'not_recorded') {
                $stale = Attendance::where('student_id', $studentId)
                    ->whereDate('date', $targetDate)
                    ->whereNull('session_id')
                    ->first();
                AttendanceDocuments::delete($stale?->supporting_document);
                $stale?->delete();
                continue;
            }

            $isExcused  = $status === 'absent_with_reason';
            $reasonType = $isExcused ? ($request->input("reason_type.{$studentId}") ?: null) : null;
            $remarks    = $isExcused ? ($request->input("remarks.{$studentId}")    ?: null) : null;

            $updateData = ['status' => $status, 'reason_type' => $reasonType, 'remarks' => $remarks];

            // When not excused, clear any stored document
            if (! $isExcused) {
                $existing = Attendance::where('student_id', $studentId)
                    ->whereDate('date', $targetDate)
                    ->first();
                AttendanceDocuments::delete($existing?->supporting_document);
                $updateData['supporting_document'] = null;
            }

            $record = Attendance::updateOrCreate(
                ['student_id' => $studentId, 'date' => $targetDate->toDateString()],
                $updateData
            );

            // Handle supporting document upload for excused attendance
            if ($isExcused && $request->hasFile("document.{$studentId}")) {
                $file = $request->file("document.{$studentId}");
                // Delete old file if being replaced
                AttendanceDocuments::delete($record->supporting_document);
                // Extension from the detected content type, not the client's filename.
                $ext      = $file->extension();
                $filename = $studentId . '_' . $targetDate->format('Y-m-d') . '_' . time() . '.' . $ext;
                $path     = AttendanceDocuments::store($file, 'attendance_documents', $filename);
                $record->update(['supporting_document' => $path]);
            }

            $wls->recalculate($studentId);

            $student = Student::find($studentId);
            if ($student && $student->user_id) {
                $whenPhrase = $isTargetToday ? 'for today' : 'for ' . $targetDate->format('d M Y');
                if ($status === 'absent') {
                    NotificationService::send($student->user_id, 'attendance', 'Marked Absent', "You have been marked absent {$whenPhrase}. Contact your lecturer if this is incorrect.", '/student/attendance');
                } elseif ($status === 'absent_with_reason') {
                    NotificationService::send($student->user_id, 'attendance', 'Excused Absence Recorded', "Your absence with reason has been recorded {$whenPhrase}.", '/student/attendance');
                }
            }
        }

        return redirect()->back()->with('success', 'Attendance saved successfully.');
    }

    public function generateQr(Request $request): RedirectResponse
    {
        $lecturerId = $this->currentLecturerId();

        if (! $lecturerId) {
            abort(403, 'Only lecturer accounts can generate attendance QR codes.');
        }

        $qrSession = $this->getTodayLecturerSession($lecturerId);

        // Block: once a session has expired for the day, no new QR is allowed
        if ($qrSession && $qrSession->isExpired()) {
            return redirect()->route('lecturer.attendance')
                ->with('info', 'QR attendance for today has already been completed. Use the pencil icon to correct records if needed.');
        }

        $message = 'Using today\'s existing attendance QR session.';

        if (! $qrSession) {
            $durationMinutes = max(5, min(480, (int) $request->input('duration_minutes', 120)));
            $sessionId = 'ATT-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6));
            $startTime = now();

            $qrSession = AttendanceQrSession::query()->create([
                'session_id'  => $sessionId,
                'lecturer_id' => $lecturerId,
                'start_time'  => $startTime,
                'end_time'    => $startTime->copy()->addMinutes($durationMinutes),
            ]);
            $message = 'QR code generated successfully for the current attendance session.';
        }

        session(['lecturer_attendance_session_id' => $qrSession->session_id]);

        return redirect()->route('lecturer.attendance.qr.show', [
            'sessionId' => $qrSession->session_id,
        ])->with('success', $message);
    }

    public function showQrPage(string $sessionId): View|RedirectResponse
    {
        $lecturerId = $this->currentLecturerId();
        $qrSession = $this->getLecturerSession($sessionId, $lecturerId);

        if (! $qrSession) {
            abort(404);
        }

        // If the session has expired, send lecturer back to the attendance page — QR is finalised
        if ($qrSession->isExpired()) {
            return redirect()->route('lecturer.attendance')
                ->with('info', 'QR attendance for today has already been completed. Use the pencil icon to correct records if needed.');
        }

        $todaySession = $this->getTodayLecturerSession($lecturerId);
        if ($todaySession && $todaySession->session_id !== $qrSession->session_id) {
            return redirect()->route('lecturer.attendance.qr.show', [
                'sessionId' => $todaySession->session_id,
            ]);
        }

        session(['lecturer_attendance_session_id' => $qrSession->session_id]);
        $sessionId = $qrSession->session_id;

        $today = Carbon::today();
        $students = $this->assignedStudentsQuery($lecturerId)->get();
        $sessionAttendances = $this->getSessionAttendances($qrSession->session_id, $students->pluck('student_id'));
        $attendanceRows = $this->buildAttendanceRows($sessionAttendances);
        $qrUrl = $this->buildQrUrl('attendance.scan', ['sessionId' => $sessionId]);
        $qrCodeSvg = QrCode::size(280)->margin(1)->generate($qrUrl);
        $validFrom  = $qrSession->start_time->copy()->setTimezone(self::MYT);
        $validUntil = $qrSession->end_time->copy()->setTimezone(self::MYT);
        $presentCount = $attendanceRows->count();
        $totalStudents = $students->count();

        return view('lecturer.attendance-qr', compact(
            'today',
            'sessionId',
            'attendanceRows',
            'qrUrl',
            'qrCodeSvg',
            'validFrom',
            'validUntil',
            'presentCount',
            'totalStudents',
            'qrSession'
        ));
    }

    public function scan(Request $request, string $sessionId): RedirectResponse
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $request->session()->put('url.intended', route('attendance.scan.mark', ['sessionId' => $sessionId]));

        return redirect()->route('login');
    }

    public function markScan(Request $request, string $sessionId): RedirectResponse
    {
        $qrSession = AttendanceQrSession::query()
            ->where('session_id', $sessionId)
            ->first();

        if ($qrSession) {
            $qrSession = $this->getTodayLecturerSession($qrSession->lecturer_id) ?? $qrSession;
        }

        if (! $qrSession || $qrSession->hasEnded()) {
            $this->logoutAttendanceStudent($request);

            $errorMessage = $qrSession?->isManuallyPaused()
                ? 'QR session temporarily closed by lecturer.'
                : 'Attendance session has ended.';

            return redirect()->route('attendance.scan.confirmation', ['sessionId' => $sessionId])
                ->with('error', $errorMessage);
        }

        $student = $this->resolveAuthenticatedStudent();

        if (! $student) {
            $this->logoutAttendanceStudent($request);

            return redirect()->route('attendance.scan.confirmation', ['sessionId' => $sessionId])
                ->with('error', 'Only student accounts can mark attendance.');
        }

        if ((string) $student->lecturer_id !== (string) $qrSession->lecturer_id) {
            $this->logoutAttendanceStudent($request);

            return redirect()->route('attendance.scan.confirmation', ['sessionId' => $qrSession->session_id])
                ->with('error', 'Only students assigned to this lecturer can mark attendance for this session.');
        }

        $result = $this->recordAttendance($student, $qrSession->session_id);

        if ($result['type'] === 'success') {
            if ($student->user_id) {
                NotificationService::send($student->user_id, 'attendance', 'Attendance Recorded', 'Your QR attendance has been recorded successfully.', '/student/attendance');
            }
            $lecturerUserId = Lecturer::where('lecturer_id', $qrSession->lecturer_id)->value('user_id');
            if ($lecturerUserId) {
                NotificationService::send($lecturerUserId, 'attendance', 'Student Scanned In', "{$student->student_name} has scanned their QR attendance.", '/lecturer/attendance');
            }
        }

        $this->logoutAttendanceStudent($request);

        return redirect()->route('attendance.scan.confirmation', ['sessionId' => $qrSession->session_id])
            ->with($result['type'], $result['message']);
    }

    public function endQr(Request $request, string $sessionId): RedirectResponse
    {
        $lecturerId = $this->currentLecturerId();
        $qrSession  = $this->getLecturerSession($sessionId, $lecturerId);

        if (! $qrSession || $qrSession->isExpired()) {
            return redirect()->back()->with('error', 'Session not found or already expired.');
        }

        $qrSession->update(['ended_at' => now()]);

        return redirect()->back()->with('success', 'QR session paused. Students cannot scan until you resume it.');
    }

    public function resumeQr(Request $request, string $sessionId): RedirectResponse
    {
        $lecturerId = $this->currentLecturerId();
        $qrSession  = $this->getLecturerSession($sessionId, $lecturerId);

        if (! $qrSession || $qrSession->isExpired()) {
            return redirect()->back()->with('error', 'Cannot resume — the 2-hour window has expired.');
        }

        $qrSession->update(['ended_at' => null]);

        return redirect()->back()->with('success', 'QR session resumed. Students can scan again.');
    }

    public function confirmation(string $sessionId): View
    {
        return view('student.attendance-confirmation', compact('sessionId'));
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

        $student = Student::query()
            ->operational()
            ->where('user_id', $user->id)
            ->first();
        if ($student) {
            return $student;
        }

        $studentTable = (new Student())->getTable();
        if (Schema::hasColumn($studentTable, 'email') && filled($user->email)) {
            return Student::query()
                ->operational()
                ->where('email', $user->email)
                ->first();
        }

        return null;
    }

    private function recordAttendance(Student $student, string $sessionId): array
    {
        $today = Carbon::today();

        $existingAttendance = Attendance::query()
            ->where('student_id', $student->getKey())
            ->whereDate('date', $today)
            ->first();

        if ($existingAttendance) {
            return [
                'type' => 'info',
                'message' => 'Attendance already recorded today.',
            ];
        }

        Attendance::query()->create([
            'student_id' => $student->getKey(),
            'session_id' => $sessionId,
            'status' => 'present',
            'scanned_at' => now(),
            'date' => $today,
        ]);

        return [
            'type' => 'success',
            'message' => 'Attendance has been recorded successfully.',
        ];
    }

    private function logoutAttendanceStudent(Request $request): void
    {
        Auth::logout();
        $request->session()->regenerateToken();
    }

    private function currentLecturerId(): ?string
    {
        return Lecturer::query()->where('user_id', Auth::id())->value('lecturer_id');
    }

    private function assignedStudentsQuery(?string $lecturerId)
    {
        return Student::query()
            ->operational()
            ->when(
                $lecturerId,
                fn ($query) => $query->where('lecturer_id', $lecturerId),
                fn ($query) => $query->whereRaw('1 = 0')
            )
            ->orderBy('student_name');
    }

    private function getLecturerSession(string $sessionId, ?string $lecturerId): ?AttendanceQrSession
    {
        if ($sessionId === '' || ! $lecturerId) {
            return null;
        }

        return AttendanceQrSession::query()
            ->where('session_id', $sessionId)
            ->where('lecturer_id', $lecturerId)
            ->first();
    }

    private function getTodayLecturerSession(?string $lecturerId): ?AttendanceQrSession
    {
        if (! $lecturerId) {
            return null;
        }

        // Use Malaysia day boundaries so sessions near UTC midnight are found correctly
        $dayStart = Carbon::today(self::MYT)->setTimezone('UTC');
        $dayEnd   = Carbon::today(self::MYT)->endOfDay()->setTimezone('UTC');

        return AttendanceQrSession::query()
            ->where('lecturer_id', $lecturerId)
            ->whereBetween('start_time', [$dayStart, $dayEnd])
            ->orderBy('start_time')
            ->first();
    }

    private function getSessionAttendances(string $sessionId, $studentIds = null)
    {
        if ($sessionId === '') {
            return collect();
        }

        return Attendance::query()
            ->with('student')
            ->where('session_id', $sessionId)
            ->when($studentIds !== null, fn ($query) => $query->whereIn('student_id', $studentIds))
            ->orderBy('scanned_at')
            ->get();
    }

    private function buildAttendanceRows($sessionAttendances)
    {
        return $sessionAttendances->map(function (Attendance $attendance) {
            $student = $attendance->student;

            return (object) [
                'student_id' => $student?->getAttribute('tahfiz_id') ?: $student?->getKey() ?: '-',
                'student_name' => $student?->getAttribute('student_name') ?: 'Unknown student',
                'status' => strtolower($attendance->status ?: 'present'),
                'scanned_at' => $attendance->scanned_at?->copy()->setTimezone(self::MYT),
            ];
        });
    }

    private function buildHistoryRows($students, $dateAttendance): \Illuminate\Support\Collection
    {
        return $students->map(function (Student $student) use ($dateAttendance) {
            $att       = $dateAttendance->get($student->getKey());
            $rawStatus = strtolower($att?->status ?? '');

            $status = match ($rawStatus) {
                'present'            => 'present',
                'absent'             => 'absent',
                'absent_with_reason' => 'absent_with_reason',
                default              => 'not_recorded',
            };

            $statusLabel = match ($status) {
                'present'            => 'Present',
                'absent'             => 'Absent (TH)',
                'absent_with_reason' => 'Excused (THS)',
                default              => 'Not Recorded',
            };

            return (object) [
                'student_id'         => $student->getAttribute('tahfiz_id') ?: $student->getKey() ?: '-',
                'student_name'       => $student->getAttribute('student_name') ?: 'Unknown student',
                'warning_level'      => (int) ($student->warning_level ?? 0),
                'status'             => $status,
                'status_label'       => $statusLabel,
                'check_in'           => $att?->scanned_at?->copy()->setTimezone(self::MYT)->format('h:i A') ?? '-',
                'is_qr_scanned'      => $att && filled($att->session_id) && $att->scanned_at !== null,
                'reason_type'        => $att?->reason_type,
                'remarks'            => $att?->remarks,
                'supporting_document'=> $att?->supporting_document,
            ];
        });
    }
}
