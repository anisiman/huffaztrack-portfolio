<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceAppeal;
use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use App\Services\WarningLetterService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Private storage and authorised download of attendance supporting documents
 * and appeal documents, plus upload validation.
 *
 * Runs against the in-memory SQLite connection from phpunit.xml with only the
 * columns these flows touch, and faked "local"/"public" disks. All records and
 * files are fictional.
 */
class AttendanceDocumentTest extends TestCase
{
    private const PDF_BYTES = "%PDF-1.4\n1 0 obj << >> endobj\ntrailer << >>\n%%EOF\n";

    private User $admin;
    private User $lecturerUser;
    private User $otherLecturerUser;
    private User $studentUser;
    private User $otherStudentUser;
    private Lecturer $lecturer;
    private Lecturer $otherLecturer;
    private int $documentCount = 0;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);
        Storage::fake('local');
        Storage::fake('public');

        $this->createSchema();

        $this->admin             = $this->makeUser('admin', 'demo.admin@example.test');
        $this->lecturerUser      = $this->makeUser('lecturer', 'demo.lecturer@example.test');
        $this->otherLecturerUser = $this->makeUser('lecturer', 'demo.lecturer2@example.test');
        $this->studentUser       = $this->makeUser('student', 'demo.student@example.test');
        $this->otherStudentUser  = $this->makeUser('student', 'demo.student2@example.test');

        $this->lecturer      = Lecturer::create(['lecturer_name' => 'DR. DEMO LECTURER', 'user_id' => $this->lecturerUser->id]);
        $this->otherLecturer = Lecturer::create(['lecturer_name' => 'DR. DEMO LECTURER TWO', 'user_id' => $this->otherLecturerUser->id]);

        Student::create([
            'student_id' => 'DEMO000001', 'student_name' => 'DEMO STUDENT', 'status' => Student::STATUS_ACTIVE,
            'lecturer_id' => $this->lecturer->lecturer_id, 'user_id' => $this->studentUser->id,
        ]);
        Student::create([
            'student_id' => 'DEMO000002', 'student_name' => 'DEMO STUDENT TWO', 'status' => Student::STATUS_ACTIVE,
            'lecturer_id' => $this->otherLecturer->lecturer_id, 'user_id' => $this->otherStudentUser->id,
        ]);
    }

    // ── Attendance supporting documents ────────────────────────────────────

    public function test_guest_is_redirected_to_login(): void
    {
        $attendance = $this->attendanceWithDocument('DEMO000001');

        $this->get(route('documents.attendance', $attendance->attendance_id))->assertRedirect(route('login'));
    }

    public function test_admin_can_view_any_attendance_document(): void
    {
        $attendance = $this->attendanceWithDocument('DEMO000002');

        $response = $this->actingAs($this->admin)->get(route('documents.attendance', $attendance->attendance_id));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertSame(self::PDF_BYTES, $response->streamedContent());
    }

    public function test_student_can_view_only_their_own_attendance_document(): void
    {
        $own   = $this->attendanceWithDocument('DEMO000001');
        $other = $this->attendanceWithDocument('DEMO000002');

        $this->actingAs($this->studentUser)->get(route('documents.attendance', $own->attendance_id))->assertOk();
        $this->actingAs($this->studentUser)->get(route('documents.attendance', $other->attendance_id))->assertForbidden();
    }

    public function test_lecturer_can_view_only_assigned_students_attendance_documents(): void
    {
        $assigned   = $this->attendanceWithDocument('DEMO000001');
        $unassigned = $this->attendanceWithDocument('DEMO000002');

        $this->actingAs($this->lecturerUser)->get(route('documents.attendance', $assigned->attendance_id))->assertOk();
        $this->actingAs($this->lecturerUser)->get(route('documents.attendance', $unassigned->attendance_id))->assertForbidden();
    }

    public function test_legacy_document_on_public_disk_is_still_served_to_authorised_users(): void
    {
        $attendance = $this->attendanceWithDocument('DEMO000001', disk: 'public');

        $this->actingAs($this->studentUser)->get(route('documents.attendance', $attendance->attendance_id))->assertOk();
        $this->actingAs($this->otherStudentUser)->get(route('documents.attendance', $attendance->attendance_id))->assertForbidden();
    }

    public function test_missing_or_out_of_folder_paths_return_not_found(): void
    {
        $noDocument = Attendance::create(['student_id' => 'DEMO000001', 'date' => '2024-01-02', 'status' => 'absent']);
        Storage::disk('local')->put('avatars/demo.png', 'not a document');
        $outside = Attendance::create([
            'student_id' => 'DEMO000001', 'date' => '2024-01-03', 'status' => 'absent_with_reason',
            'supporting_document' => 'avatars/demo.png',
        ]);
        $traversal = Attendance::create([
            'student_id' => 'DEMO000001', 'date' => '2024-01-04', 'status' => 'absent_with_reason',
            'supporting_document' => 'attendance_documents/../avatars/demo.png',
        ]);
        $deletedFile = Attendance::create([
            'student_id' => 'DEMO000001', 'date' => '2024-01-05', 'status' => 'absent_with_reason',
            'supporting_document' => 'attendance_documents/DEMO000001_missing.pdf',
        ]);

        foreach ([$noDocument, $outside, $traversal, $deletedFile] as $attendance) {
            $this->actingAs($this->admin)->get(route('documents.attendance', $attendance->attendance_id))->assertNotFound();
        }
        $this->actingAs($this->admin)->get(route('documents.attendance', 999999))->assertNotFound();
    }

    public function test_non_document_content_is_forced_to_download(): void
    {
        $attendance = $this->attendanceWithDocument('DEMO000001', disk: 'public', name: 'legacy.html', contents: '<html><script>alert(1)</script></html>');

        $response = $this->actingAs($this->admin)->get(route('documents.attendance', $attendance->attendance_id));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/octet-stream');
        $this->assertStringStartsWith('attachment', $response->headers->get('Content-Disposition'));
    }

    // ── Appeal documents ───────────────────────────────────────────────────

    public function test_appeal_document_access_rules(): void
    {
        $appeal = $this->appealWithDocument('DEMO000001', $this->lecturer->lecturer_id);

        $url = route('documents.appeal', $appeal->id);

        $this->actingAs($this->admin)->get($url)->assertOk();
        $this->actingAs($this->studentUser)->get($url)->assertOk();
        $this->actingAs($this->lecturerUser)->get($url)->assertOk();
        $this->actingAs($this->otherStudentUser)->get($url)->assertForbidden();
        $this->actingAs($this->otherLecturerUser)->get($url)->assertForbidden();
    }

    public function test_appeal_lecturer_and_current_halaqah_lecturer_can_both_view_after_transfer(): void
    {
        // Submitted to the first lecturer, then the student moves halaqah.
        $appeal = $this->appealWithDocument('DEMO000001', $this->lecturer->lecturer_id);
        Student::where('student_id', 'DEMO000001')->update(['lecturer_id' => $this->otherLecturer->lecturer_id]);

        $url = route('documents.appeal', $appeal->id);

        $this->actingAs($this->lecturerUser)->get($url)->assertOk();
        $this->actingAs($this->otherLecturerUser)->get($url)->assertOk();
    }

    // ── Uploads ────────────────────────────────────────────────────────────

    public function test_student_appeal_upload_is_stored_on_private_disk(): void
    {
        $attendance = Attendance::create(['student_id' => 'DEMO000001', 'date' => '2024-01-02', 'status' => 'absent']);

        $this->actingAs($this->studentUser)->post(route('student.attendance.appeal.store'), [
            'attendance_id' => $attendance->attendance_id,
            'document' => UploadedFile::fake()->createWithContent('letter.pdf', self::PDF_BYTES),
        ])->assertSessionHasNoErrors();

        $path = AttendanceAppeal::where('attendance_id', $attendance->attendance_id)->value('document_path');
        $this->assertStringStartsWith('appeal_documents/', $path);
        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_lecturer_upload_is_stored_on_private_disk(): void
    {
        $this->mock(WarningLetterService::class)->shouldReceive('recalculate')->once();

        $this->actingAs($this->lecturerUser)->post(route('lecturer.attendance.store'), [
            'date' => '2024-01-02',
            'attendance' => ['DEMO000001' => 'absent_with_reason'],
            'document' => ['DEMO000001' => UploadedFile::fake()->createWithContent('letter.pdf', self::PDF_BYTES)],
        ])->assertSessionHasNoErrors();

        $path = Attendance::where('student_id', 'DEMO000001')->value('supporting_document');
        $this->assertStringStartsWith('attendance_documents/', $path);
        $this->assertStringEndsWith('.pdf', $path);
        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_lecturer_upload_rejects_disallowed_types_and_oversized_files(): void
    {
        $rejected = [
            UploadedFile::fake()->create('page.html', 5, 'text/html'),
            UploadedFile::fake()->create('tool.exe', 5, 'application/x-msdownload'),
            UploadedFile::fake()->create('photo.webp', 5, 'image/webp'),
            UploadedFile::fake()->create('big.pdf', 10241, 'application/pdf'),
        ];

        foreach ($rejected as $file) {
            $this->actingAs($this->lecturerUser)->post(route('lecturer.attendance.store'), [
                'date' => '2024-01-02',
                'attendance' => ['DEMO000001' => 'absent_with_reason'],
                'document' => ['DEMO000001' => $file],
            ])->assertSessionHasErrors('document.DEMO000001');
        }

        $this->assertSame(0, Attendance::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function makeUser(string $role, string $email): User
    {
        return User::create(['name' => 'Demo ' . $role, 'email' => $email, 'password' => Hash::make('demo-pass-123'), 'role' => $role]);
    }

    private function attendanceWithDocument(string $studentId, string $disk = 'local', string $name = 'note.pdf', string $contents = self::PDF_BYTES): Attendance
    {
        $date = Carbon::create(2024, 1, 10)->addDays($this->documentCount++)->toDateString();
        $path = 'attendance_documents/' . $studentId . '_' . $date . '_' . $name;
        Storage::disk($disk)->put($path, $contents);

        return Attendance::create([
            'student_id' => $studentId,
            'date' => $date,
            'status' => 'absent_with_reason',
            'supporting_document' => $path,
        ]);
    }

    private function appealWithDocument(string $studentId, int $lecturerId): AttendanceAppeal
    {
        $attendance = Attendance::create(['student_id' => $studentId, 'date' => '2024-02-01', 'status' => 'absent']);
        $path = 'appeal_documents/' . $studentId . '_2024-02-01_appeal.pdf';
        Storage::disk('local')->put($path, self::PDF_BYTES);

        return AttendanceAppeal::create([
            'attendance_id' => $attendance->attendance_id,
            'student_id' => $studentId,
            'lecturer_id' => $lecturerId,
            'date' => '2024-02-01',
            'document_path' => $path,
            'status' => 'pending',
        ]);
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('user');
            $table->boolean('is_active')->default(true);
            $table->string('profile_picture')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('lecturer', function (Blueprint $table) {
            $table->id('lecturer_id');
            $table->string('lecturer_name');
            $table->string('phone')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
        });

        Schema::create('student', function (Blueprint $table) {
            $table->string('student_id')->primary();
            $table->string('student_name');
            $table->string('email')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('lecturer_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('attendance', function (Blueprint $table) {
            $table->id('attendance_id');
            $table->string('student_id');
            $table->unsignedBigInteger('tasmi_id')->nullable();
            $table->string('session_id')->nullable();
            $table->date('date');
            $table->string('status');
            $table->string('reason_type')->nullable();
            $table->text('remarks')->nullable();
            $table->string('supporting_document')->nullable();
            $table->timestamp('scanned_at')->nullable();
        });

        Schema::create('attendance_appeals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_id')->unique();
            $table->string('student_id');
            $table->unsignedBigInteger('lecturer_id')->nullable();
            $table->date('date');
            $table->string('reason_type')->nullable();
            $table->text('remarks')->nullable();
            $table->string('document_path');
            $table->string('status');
            $table->text('rejection_remarks')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }
}
