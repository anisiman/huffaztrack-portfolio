<?php

namespace App\Console\Commands;

use App\Models\AcademicPeriod;
use App\Models\Attendance;
use App\Models\Lecturer;
use App\Models\Muqarrar;
use App\Models\Student;
use App\Models\Tasmi;
use App\Models\TasmiProgress;
use App\Services\WarningLetterService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * LOCAL PORTFOLIO DEMO DATA ONLY.
 *
 * Adds fictional attendance and hafazan history for weeks 1–4 of the demo
 * semester (7 Sep – 2 Oct 2026) to the students created by
 * portfolio-demo:install. Run that command first.
 *
 * Safety rules:
 *  - refuses to run unless the active database is exactly `huffaztrack_portfolio`
 *    and the environment is not production;
 *  - only touches the six demo students assigned to the demo lecturer;
 *  - inserts only: an attendance row is skipped if the student already has one
 *    on that date, and a tasmi row is skipped if one exists for that date;
 *  - never updates or deletes existing rows, so running it twice adds nothing;
 *  - warning levels are re-checked with WarningLetterService (the absences
 *    below keep every student at their current level, so no letters are
 *    added).
 *
 * Hafazan pages follow LecturerHifzController's daily checkpoint
 * (round(start + (end - start) / 5 * day), five learning days a week) plus
 * the student's pace for that week.
 *
 * Run: php artisan portfolio-demo:seed-activity
 */
class SeedPortfolioDemoActivity extends Command
{
    protected $signature = 'portfolio-demo:seed-activity';

    protected $description = 'Add fictional weeks 1-4 attendance and hafazan history to the local portfolio demo (local portfolio demo only)';

    /** Mondays of the weeks to fill (weeks 1–4 of the demo semester). */
    public const WEEKS = ['2026-09-07', '2026-09-14', '2026-09-21', '2026-09-28'];

    /**
     * Per student: unexcused absences, excused absences (date => reason_type,
     * values from the lecturer attendance form), and pages relative to the
     * daily checkpoint for each of the four weeks.
     */
    public const PLAN = [
        'DEMO000001' => ['absent' => [],                           'excused' => [],                                'pace' => [0, 0, 0, 0]],
        'DEMO000002' => ['absent' => ['2026-09-16'],               'excused' => [],                                'pace' => [0, 0, 0, 0]],
        'DEMO000003' => ['absent' => [],                           'excused' => ['2026-09-24' => 'official_program'], 'pace' => [0, 0, 1, 1]],
        'DEMO000004' => ['absent' => ['2026-09-22', '2026-09-30'], 'excused' => [],                                'pace' => [0, -1, -2, -3]],
        'DEMO000005' => ['absent' => [],                           'excused' => ['2026-09-10' => 'family_emergency'], 'pace' => [1, 1, 2, 2]],
        'DEMO000006' => ['absent' => ['2026-09-18', '2026-09-29'], 'excused' => [],                                'pace' => [0, 0, -1, -2]],
    ];

    private const LEARNING_DAYS = 5;

    public function handle(): int
    {
        if (DB::connection()->getDatabaseName() !== InstallPortfolioDemoData::DATABASE) {
            $this->error("Refusing to run: the active database is not '" . InstallPortfolioDemoData::DATABASE . "'. Nothing was written.");

            return self::FAILURE;
        }

        if (app()->environment('production')) {
            $this->error('Refusing to run in the production environment. Nothing was written.');

            return self::FAILURE;
        }

        $problems = $this->checkDemoInstall();
        if ($problems !== []) {
            $this->error('Demo activity was not added:');
            foreach ($problems as $problem) {
                $this->line("  - {$problem}");
            }
            $this->line('Nothing was changed. Run php artisan portfolio-demo:install first.');

            return self::FAILURE;
        }

        $counts = ['attendance' => 0, 'attendance_skipped' => 0, 'tasmi' => 0, 'tasmi_skipped' => 0];

        DB::transaction(function () use (&$counts) {
            $lecturerId = Lecturer::where('lecturer_name', InstallPortfolioDemoData::LECTURER_NAME)->value('lecturer_id');

            foreach (self::PLAN as $studentId => $plan) {
                $student = Student::find($studentId);

                foreach (self::WEEKS as $weekIndex => $monday) {
                    for ($day = 1; $day <= self::LEARNING_DAYS; $day++) {
                        $date = Carbon::parse($monday)->addDays($day - 1)->toDateString();
                        $this->addDay($student, $lecturerId, $date, $day, $plan, $plan['pace'][$weekIndex], $counts);
                    }
                }
            }

            // Re-check warning levels with the application's own rules.
            $warnings = app(WarningLetterService::class);
            foreach (array_keys(self::PLAN) as $studentId) {
                $warnings->recalculate($studentId);
            }
        });

        $this->info('Demo activity for weeks 1-4 checked in ' . InstallPortfolioDemoData::DATABASE . '.');
        $this->line("  attendance: {$counts['attendance']} added, {$counts['attendance_skipped']} already present (left unchanged)");
        $this->line("  hafazan (tasmi): {$counts['tasmi']} added, {$counts['tasmi_skipped']} already present (left unchanged)");

        return self::SUCCESS;
    }

    /** @return list<string> */
    private function checkDemoInstall(): array
    {
        $problems = [];

        $period = AcademicPeriod::current();
        if (! $period || $period->name !== InstallPortfolioDemoData::PERIOD_NAME) {
            $problems[] = 'the active academic period is not ' . InstallPortfolioDemoData::PERIOD_NAME;
        } elseif ($period->start_date->gt(Carbon::parse(self::WEEKS[0]))) {
            $problems[] = 'the demo period starts after ' . self::WEEKS[0];
        }

        $lecturerId = Lecturer::where('lecturer_name', InstallPortfolioDemoData::LECTURER_NAME)->value('lecturer_id');
        if (! $lecturerId) {
            $problems[] = 'lecturer ' . InstallPortfolioDemoData::LECTURER_NAME . ' not found';
        }

        foreach (array_keys(self::PLAN) as $studentId) {
            $ok = Student::operational()->whereKey($studentId)->where('lecturer_id', $lecturerId)->exists();
            if (! $ok) {
                $problems[] = "student {$studentId} is missing, inactive or not assigned to the demo lecturer";
            }
        }

        if (Carbon::parse(self::WEEKS[array_key_last(self::WEEKS)])->addDays(self::LEARNING_DAYS - 1)->isFuture()) {
            $problems[] = 'the planned dates are not all in the past';
        }

        return $problems;
    }

    private function addDay(Student $student, int $lecturerId, string $date, int $day, array $plan, int $pace, array &$counts): void
    {
        $id = $student->student_id;

        if (Attendance::where('student_id', $id)->whereDate('date', $date)->exists()) {
            $counts['attendance_skipped']++;
        } elseif (in_array($date, $plan['absent'], true)) {
            Attendance::create(['student_id' => $id, 'date' => $date, 'status' => 'absent']);
            $counts['attendance']++;
        } elseif (isset($plan['excused'][$date])) {
            Attendance::create([
                'student_id' => $id,
                'date' => $date,
                'status' => 'absent_with_reason',
                'reason_type' => $plan['excused'][$date],
                'remarks' => 'Demo excused absence.',
            ]);
            $counts['attendance']++;
        } else {
            Attendance::create(['student_id' => $id, 'date' => $date, 'status' => 'present']);
            $counts['attendance']++;
        }

        // Recitations only happen on days the student was present.
        $present = Attendance::where('student_id', $id)->whereDate('date', $date)->where('status', 'present')->exists();
        if (! $present) {
            return;
        }

        if (Tasmi::where('student_id', $id)->whereDate('tasmi_date', $date)->exists()) {
            $counts['tasmi_skipped']++;

            return;
        }

        // Same lookup as LecturerHifzController: latest batch target started on or before the date.
        $target = Muqarrar::where('batch_id', $student->batch_id)
            ->whereNotNull('week_start_date')
            ->whereDate('week_start_date', '<=', $date)
            ->orderByDesc('week_start_date')
            ->first();

        $checkpoint = (int) min(
            $target->end_page,
            round($target->start_page + (($target->end_page - $target->start_page) / self::LEARNING_DAYS) * $day)
        );

        $tasmi = Tasmi::create([
            'student_id' => $id,
            'lecturer_id' => $lecturerId,
            'muqarrar_id' => $target->muqarrar_id,
            'tasmi_date' => $date,
        ]);
        TasmiProgress::create(['tasmi_id' => $tasmi->tasmi_id, 'page_reached' => max(1, $checkpoint + $pace)]);
        $counts['tasmi']++;
    }
}
