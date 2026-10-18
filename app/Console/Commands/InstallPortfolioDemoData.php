<?php

namespace App\Console\Commands;

use App\Models\AcademicPeriod;
use App\Models\Attendance;
use App\Models\Batch;
use App\Models\College;
use App\Models\Lecturer;
use App\Models\Muqarrar;
use App\Models\Program;
use App\Models\Sponsorship;
use App\Models\Student;
use App\Models\Tasmi;
use App\Models\TasmiProgress;
use App\Models\User;
use App\Services\WarningLetterService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * LOCAL PORTFOLIO DEMO DATA ONLY.
 *
 * Inserts a small, entirely fictional data set into the local
 * `huffaztrack_portfolio` database so the app can be demonstrated.
 *
 * Safety rules:
 *  - refuses to run unless the active database is exactly `huffaztrack_portfolio`
 *    and the environment is not production;
 *  - never updates, resets or deletes existing records;
 *  - aborts (writing nothing) if any demo identifier already exists;
 *  - inserts everything in one transaction;
 *  - writes generated passwords to a gitignored file, never to the terminal.
 *
 * It is not called from DatabaseSeeder, so it never runs as part of a deploy.
 *
 * Run:      php artisan portfolio-demo:install
 * Logins:   storage/app/private/portfolio-demo-credentials.txt (gitignored)
 * Rebuild:  the command only installs into a database with none of the demo
 *           identifiers below. To rebuild, the demo rows (DEMO0000xx students,
 *           *.demo@example.com users, batches T98/T99, the DEMO period, the
 *           DEMO faculties/programs/sponsorship and their attendance/tasmi/
 *           muqarrar rows) must be removed first; nothing here deletes data.
 */
class InstallPortfolioDemoData extends Command
{
    protected $signature = 'portfolio-demo:install';

    protected $description = 'Insert fictional demo data into the local huffaztrack_portfolio database (local portfolio demo only)';

    public const DATABASE = 'huffaztrack_portfolio';

    public const CREDENTIALS_FILE = 'app/private/portfolio-demo-credentials.txt';

    public const ADMIN_EMAIL = 'admin.demo@example.com';

    public const LECTURER_EMAIL = 'lecturer.demo@example.com';

    public const LECTURER_NAME = 'DR. DEMO LECTURER';

    public const PERIOD_NAME = 'DEMO SEMESTER 1 2026/2027';

    public const PERIOD_YEAR = '2026/2027';

    public const PERIOD_NUMBER = 1;

    public const BATCHES = [
        'T98' => ['intake' => 'DEMO INTAKE 2025', 'date_registered' => '2025-09-01', 'first_page' => 301],
        'T99' => ['intake' => 'DEMO INTAKE 2026', 'date_registered' => '2026-09-01', 'first_page' => 1],
    ];

    /** College names match the labels counted on the admin dashboard. */
    public const PROGRAMS = [
        'FOE' => ['college' => 'FACULTY OF ENGINEERING (FOE)', 'program' => 'BACHELOR OF CIVIL ENGINEERING (DEMO)'],
        'FOC' => ['college' => 'FACULTY OF COMPUTING (FOC)', 'program' => 'BACHELOR OF COMPUTER SCIENCE (DEMO)'],
        'FOB' => ['college' => 'FACULTY OF BUSINESS (FOB)', 'program' => 'BACHELOR OF BUSINESS ADMINISTRATION (DEMO)'],
    ];

    public const SPONSORSHIP_NAME = 'DEMO SCHOLARSHIP FUND';

    /**
     * id, name, gender, batch, program, status, assigned to demo lecturer, has login.
     * Attendance plan per student (weekday dates in the two demo weeks):
     * 'absent' => unexcused (TH), 'excused' => absent_with_reason (THS).
     * Hafazan pace: pages relative to the weekly target (0 = on track).
     */
    public const STUDENTS = [
        ['DEMO000001', 'AHMAD DEMO',  'LELAKI',    'T99', 'FOC', 'AKTIF',      true,  true,  'absent' => [],                                          'excused' => [],             'pace' => 0],
        ['DEMO000002', 'BALQIS DEMO', 'PEREMPUAN', 'T99', 'FOE', 'AKTIF',      true,  true,  'absent' => ['2026-10-07'],                              'excused' => [],             'pace' => 0],
        ['DEMO000003', 'DANIAL DEMO', 'LELAKI',    'T99', 'FOB', 'AKTIF',      true,  true,  'absent' => [],                                          'excused' => ['2026-10-13'], 'pace' => 1],
        ['DEMO000004', 'FARAH DEMO',  'PEREMPUAN', 'T98', 'FOC', 'AKTIF',      true,  false, 'absent' => ['2026-10-06', '2026-10-08', '2026-10-12', '2026-10-14'], 'excused' => [], 'pace' => -4],
        ['DEMO000005', 'HAKIM DEMO',  'LELAKI',    'T98', 'FOE', 'AKTIF',      true,  false, 'absent' => [],                                          'excused' => [],             'pace' => 2],
        ['DEMO000006', 'IRDINA DEMO', 'PEREMPUAN', 'T98', 'FOB', 'AKTIF',      true,  false, 'absent' => ['2026-10-16'],                              'excused' => [],             'pace' => -2],
        ['DEMO000007', 'KHALID DEMO', 'LELAKI',    'T99', 'FOC', 'AKTIF',      false, false, 'absent' => [],                                          'excused' => [],             'pace' => 0],
        ['DEMO000008', 'MARYAM DEMO', 'PEREMPUAN', 'T98', 'FOB', 'TARIK DIRI', false, false, 'absent' => [],                                          'excused' => [],             'pace' => 0],
    ];

    /** Attendance + hafazan weeks (Monday dates), inside the demo semester. */
    public const RECORD_WEEKS = ['2026-10-05', '2026-10-12'];

    public function handle(): int
    {
        $database = DB::connection()->getDatabaseName();

        if ($database !== self::DATABASE) {
            $this->error("Refusing to run: the active database is not '" . self::DATABASE . "'. Nothing was written.");

            return self::FAILURE;
        }

        if (app()->environment('production')) {
            $this->error('Refusing to run in the production environment. Nothing was written.');

            return self::FAILURE;
        }

        $conflicts = $this->findExistingDemoRecords();

        if ($conflicts !== []) {
            $this->error('Demo data was not installed because these records already exist:');
            foreach ($conflicts as $conflict) {
                $this->line("  - {$conflict}");
            }
            $this->line('Nothing was changed. If the demo data is already installed, there is nothing to do;');
            $this->line('see the notes at the top of app/Console/Commands/InstallPortfolioDemoData.php to rebuild it.');

            return self::FAILURE;
        }

        $passwords = [];

        DB::transaction(function () use (&$passwords) {
            $passwords = $this->insertDemoData();
        });

        $this->writeCredentials($passwords);

        $this->info('Fictional demo data installed in ' . self::DATABASE . '.');
        $this->line('Login emails and generated passwords: storage/' . self::CREDENTIALS_FILE . ' (gitignored, local only).');

        return self::SUCCESS;
    }

    /** @return list<string> */
    private function findExistingDemoRecords(): array
    {
        $found = [];

        foreach ($this->demoEmails() as $email) {
            if (User::where('email', $email)->exists()) {
                $found[] = "user with email {$email}";
            }
        }
        foreach (self::STUDENTS as $s) {
            if (Student::whereKey($s[0])->exists()) {
                $found[] = "student {$s[0]}";
            }
            if (Student::where('email', $this->studentEmail($s[0]))->exists()) {
                $found[] = 'student with email ' . $this->studentEmail($s[0]);
            }
        }
        foreach (array_keys(self::BATCHES) as $name) {
            if (Batch::where('batch_name', $name)->exists()) {
                $found[] = "batch {$name}";
            }
        }
        foreach (self::PROGRAMS as $p) {
            if (College::where('college_name', $p['college'])->exists()) {
                $found[] = "college {$p['college']}";
            }
            if (Program::where('program_name', $p['program'])->exists()) {
                $found[] = "program {$p['program']}";
            }
        }
        if (Sponsorship::where('sponsorship_name', self::SPONSORSHIP_NAME)->exists()) {
            $found[] = 'sponsorship ' . self::SPONSORSHIP_NAME;
        }
        if (Lecturer::where('lecturer_name', self::LECTURER_NAME)->exists()) {
            $found[] = 'lecturer ' . self::LECTURER_NAME;
        }
        if (AcademicPeriod::where('name', self::PERIOD_NAME)->exists()
            || AcademicPeriod::where('academic_year', self::PERIOD_YEAR)->where('period_number', self::PERIOD_NUMBER)->exists()) {
            $found[] = 'academic period ' . self::PERIOD_NAME . ' (or another period ' . self::PERIOD_YEAR . ' #' . self::PERIOD_NUMBER . ')';
        }
        if (AcademicPeriod::where('status', 'active')->exists()) {
            $found[] = 'another academic period is already active';
        }

        return $found;
    }

    /** @return array<string, string> email => plain password */
    private function insertDemoData(): array
    {
        $passwords = [];

        // ── Accounts ───────────────────────────────────────────────────────
        $passwords[self::ADMIN_EMAIL] = $this->newPassword();
        $this->createUser('Demo Admin', self::ADMIN_EMAIL, 'admin', $passwords[self::ADMIN_EMAIL]);

        $passwords[self::LECTURER_EMAIL] = $this->newPassword();
        $lecturerUser = $this->createUser(self::LECTURER_NAME, self::LECTURER_EMAIL, 'lecturer', $passwords[self::LECTURER_EMAIL]);
        // Same linkage as AdminLecturerController::store(): profile.user_id -> users.id
        $lecturer = Lecturer::create(['lecturer_name' => self::LECTURER_NAME, 'user_id' => $lecturerUser->id]);

        // ── Reference data ─────────────────────────────────────────────────
        $period = AcademicPeriod::create([
            'name' => self::PERIOD_NAME,
            'academic_year' => self::PERIOD_YEAR,
            'period_number' => self::PERIOD_NUMBER,
            'start_date' => '2026-09-07',
            'end_date' => '2027-01-29',
            'status' => 'active',
            'status_locked' => false,
            'section_enrollment_open' => false,
            'notes' => 'Fictional local portfolio demo semester.',
        ]);

        $batches = [];
        foreach (self::BATCHES as $name => $b) {
            $batches[$name] = Batch::create([
                'batch_name' => $name,
                'intake' => $b['intake'],
                'is_active' => true,
                'date_registered' => $b['date_registered'],
            ]);
        }

        $programs = [];
        foreach (self::PROGRAMS as $key => $p) {
            $college = College::create(['college_name' => $p['college'], 'campus' => 'DEMO CAMPUS']);
            $programs[$key] = Program::create([
                'college_id' => $college->college_id,
                'program_name' => $p['program'],
                'program_level' => 'DEGREE',
            ]);
        }

        $sponsorship = Sponsorship::create(['sponsorship_name' => self::SPONSORSHIP_NAME, 'sponsorship_duration' => '4 YEARS']);

        // Weekly hafazan targets (Muqarrar), five pages a week per batch.
        $muqarrar = [];
        foreach (self::BATCHES as $name => $b) {
            for ($week = 1; $week <= 8; $week++) {
                $start = $b['first_page'] + 5 * ($week - 1);
                $muqarrar[$name][$week] = Muqarrar::create([
                    'batch_id' => $batches[$name]->batch_id,
                    'academic_period_id' => $period->id,
                    'week' => $week,
                    'week_start_date' => Carbon::parse('2026-09-07')->addWeeks($week - 1)->toDateString(),
                    'start_page' => $start,
                    'end_page' => $start + 4,
                    'notes' => 'Demo target',
                ]);
            }
        }

        // ── Students (same fields and pivot as AdminMasterlistController::store) ──
        foreach (self::STUDENTS as $i => $s) {
            [$id, $name, $gender, $batchName, $programKey, $status, $assigned, $hasLogin] = $s;

            $userId = null;
            if ($hasLogin) {
                $email = $this->studentEmail($id);
                $passwords[$email] = $this->newPassword();
                $userId = $this->createUser($name, $email, 'student', $passwords[$email])->id;
            }

            $student = Student::create([
                'student_id' => $id,
                'student_name' => $name,
                'nric' => sprintf('000000-00-%04d', $i + 1),
                'email' => $this->studentEmail($id),
                'gender' => $gender,
                'status' => $status,
                'batch_id' => $batches[$batchName]->batch_id,
                'program_id' => $programs[$programKey]->program_id,
                'sponsorship_id' => $sponsorship->sponsorship_id,
                'lecturer_id' => $assigned ? $lecturer->lecturer_id : null,
                'user_id' => $userId,
                'remarks' => 'Fictional demo student.',
            ]);

            $student->programs()->attach($programs[$programKey]->program_id, [
                'is_current' => true,
                'start_date' => self::BATCHES[$batchName]['date_registered'],
            ]);

            if ($assigned && $status === Student::STATUS_ACTIVE) {
                $this->insertAttendanceAndHafazan($s, $lecturer, $muqarrar[$batchName]);
            }
        }

        // Warning levels/letters via the app's own rules, not set by hand.
        $warnings = app(WarningLetterService::class);
        foreach (self::STUDENTS as $s) {
            if ($s[6] && $s[5] === Student::STATUS_ACTIVE) {
                $warnings->recalculate($s[0]);
            }
        }

        return $passwords;
    }

    /**
     * Manual (non-QR) attendance for each weekday in the demo weeks, and a
     * tasmi + tasmi_progress pair for every day the student was present —
     * the same records LecturerHifzController::saveAll() creates.
     */
    private function insertAttendanceAndHafazan(array $s, Lecturer $lecturer, array $batchMuqarrar): void
    {
        [$id] = $s;

        foreach (self::RECORD_WEEKS as $monday) {
            $weekStart = Carbon::parse($monday);
            $week = (int) Carbon::parse('2026-09-07')->diffInWeeks($weekStart) + 1;
            $target = $batchMuqarrar[$week];

            for ($day = 0; $day < 5; $day++) {
                $date = $weekStart->copy()->addDays($day)->toDateString();

                if (in_array($date, $s['absent'], true)) {
                    Attendance::create(['student_id' => $id, 'date' => $date, 'status' => 'absent']);
                    continue;
                }

                if (in_array($date, $s['excused'], true)) {
                    Attendance::create([
                        'student_id' => $id,
                        'date' => $date,
                        'status' => 'absent_with_reason',
                        'reason_type' => 'sick_mc',
                        'remarks' => 'Demo excused absence.',
                    ]);
                    continue;
                }

                Attendance::create(['student_id' => $id, 'date' => $date, 'status' => 'present']);

                $tasmi = Tasmi::create([
                    'student_id' => $id,
                    'lecturer_id' => $lecturer->lecturer_id,
                    'muqarrar_id' => $target->muqarrar_id,
                    'tasmi_date' => $date,
                ]);
                TasmiProgress::create([
                    'tasmi_id' => $tasmi->tasmi_id,
                    'page_reached' => max(1, $target->start_page + $day + $s['pace']),
                ]);
            }
        }
    }

    private function createUser(string $name, string $email, string $role, string $password): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function newPassword(): string
    {
        return Str::password(20, symbols: false);
    }

    private function studentEmail(string $studentId): string
    {
        return 'student' . (int) substr($studentId, 4) . '.demo@example.com';
    }

    /** @return list<string> */
    private function demoEmails(): array
    {
        $emails = [self::ADMIN_EMAIL, self::LECTURER_EMAIL];
        foreach (self::STUDENTS as $s) {
            $emails[] = $this->studentEmail($s[0]);
        }

        return $emails;
    }

    private function writeCredentials(array $passwords): void
    {
        $lines = [
            'HuffazTrack LOCAL PORTFOLIO DEMO credentials — fictional accounts, local use only.',
            'Generated ' . now()->toDateTimeString() . ' for database ' . self::DATABASE . '.',
            '',
        ];
        foreach ($passwords as $email => $password) {
            $lines[] = str_pad($email, 32) . $password;
        }

        $path = storage_path(self::CREDENTIALS_FILE);
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        file_put_contents($path, implode(PHP_EOL, $lines) . PHP_EOL);
    }
}
