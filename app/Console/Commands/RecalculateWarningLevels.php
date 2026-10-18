<?php

namespace App\Console\Commands;

use App\Models\AcademicPeriod;
use App\Models\Student;
use App\Services\WarningLetterService;
use Illuminate\Console\Command;

class RecalculateWarningLevels extends Command
{
    protected $signature = 'wl:recalculate {--student= : Recalculate a single student by ID}';
    protected $description = 'Recalculate warning levels for all students based on existing attendance records';

    public function handle(WarningLetterService $wls): int
    {
        $studentId = $this->option('student');

        $students = $studentId
            ? Student::where('student_id', $studentId)->get()
            : Student::all();

        if ($students->isEmpty()) {
            $this->warn($studentId ? "Student [{$studentId}] not found." : 'No students found.');
            return self::FAILURE;
        }

        $this->info("Recalculating warning levels for {$students->count()} student(s)...");
        $this->newLine();

        $changed = 0;

        foreach ($students as $student) {
            $before = (int) ($student->warning_level ?? 0);

            $wls->recalculate($student->student_id);

            $student->refresh();
            $after = (int) ($student->warning_level ?? 0);

            $absentCount = $wls->absentCountFor($student->student_id, AcademicPeriod::current());

            $label = match ($after) {
                1 => 'WL1',
                2 => 'WL2',
                3 => 'Terminated',
                default => 'Active',
            };

            if ($before !== $after) {
                $this->line(
                    sprintf(
                        '  <fg=yellow>CHANGED</> %-20s  absences=%-3d  %s → %s',
                        $student->student_id,
                        $absentCount,
                        $this->levelLabel($before),
                        $label,
                    )
                );
                $changed++;
            } else {
                $this->line(
                    sprintf(
                        '  <fg=gray>   ok   </> %-20s  absences=%-3d  %s',
                        $student->student_id,
                        $absentCount,
                        $label,
                    )
                );
            }
        }

        $this->newLine();
        $this->info("Done. {$changed} student(s) updated.");

        return self::SUCCESS;
    }

    private function levelLabel(int $level): string
    {
        return match ($level) {
            1 => 'WL1',
            2 => 'WL2',
            3 => 'Terminated',
            default => 'Active',
        };
    }
}
