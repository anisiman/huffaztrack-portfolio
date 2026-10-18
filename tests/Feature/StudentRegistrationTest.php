<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Public student self-registration (RegisterController::store).
 *
 * Runs against the in-memory SQLite connection from phpunit.xml. Instead of
 * the full migration set, setUp() creates only the columns this flow touches.
 * All records are fictional.
 */
class StudentRegistrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Cookie encryption needs a key; the portfolio copy ships without a .env.
        config(['app.key' => 'base64:' . base64_encode(random_bytes(32))]);

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

        Schema::create('student', function (Blueprint $table) {
            $table->string('student_id')->primary();
            $table->string('student_name');
            $table->string('email')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
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

    private function registrationPayload(array $overrides = []): array
    {
        return array_merge([
            'student_id' => 'DEMO000001',
            'name' => 'Demo Student',
            'email' => 'demo.student@example.test',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
        ], $overrides);
    }

    public function test_student_without_email_on_record_cannot_self_register(): void
    {
        Student::create(['student_id' => 'DEMO000001', 'student_name' => 'DEMO STUDENT', 'email' => null]);

        $response = $this->from('/registration')->post('/registration', $this->registrationPayload());

        $response->assertRedirect('/registration');
        $response->assertSessionHasErrors([
            'student_id' => 'No email address is on record for this Student ID. Please contact your administrator to create your account.',
        ]);
        $this->assertGuest();
    }

    public function test_rejected_registration_creates_no_account_and_leaves_record_unlinked(): void
    {
        Student::create(['student_id' => 'DEMO000001', 'student_name' => 'DEMO STUDENT', 'email' => null]);

        $this->from('/registration')->post('/registration', $this->registrationPayload());

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseMissing('users', ['email' => 'demo.student@example.test']);
        $this->assertNull(Student::find('DEMO000001')->user_id);
        $this->assertDatabaseCount('user_notifications', 0);
    }

    public function test_student_with_matching_email_can_still_register(): void
    {
        $admin = User::create([
            'name' => 'Demo Admin',
            'email' => 'demo.admin@example.test',
            'password' => Hash::make('admin-pass-123'),
            'role' => 'admin',
        ]);
        Student::create(['student_id' => 'DEMO000002', 'student_name' => 'DEMO STUDENT TWO', 'email' => 'demo.two@example.test']);

        // Email comparison is case-insensitive, as before.
        $response = $this->post('/registration', $this->registrationPayload([
            'student_id' => 'DEMO000002',
            'email' => 'Demo.Two@Example.test',
        ]));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasNoErrors();

        $user = User::where('email', 'Demo.Two@Example.test')->first();
        $this->assertNotNull($user);
        $this->assertSame('student', $user->role);
        $this->assertTrue(Hash::check('secret-pass-123', $user->password));
        $this->assertSame($user->id, (int) Student::find('DEMO000002')->user_id);
        $this->assertDatabaseHas('user_notifications', ['user_id' => $admin->id, 'title' => 'New Student Registration']);
    }

    public function test_mismatched_email_is_still_rejected(): void
    {
        Student::create(['student_id' => 'DEMO000003', 'student_name' => 'DEMO STUDENT THREE', 'email' => 'demo.three@example.test']);

        $response = $this->from('/registration')->post('/registration', $this->registrationPayload([
            'student_id' => 'DEMO000003',
            'email' => 'someone.else@example.test',
        ]));

        $response->assertRedirect('/registration');
        $response->assertSessionHasErrors(['email' => 'Email does not match the records for this Student ID.']);
        $this->assertDatabaseCount('users', 0);
        $this->assertNull(Student::find('DEMO000003')->user_id);
    }
}
