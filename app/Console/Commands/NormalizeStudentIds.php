<?php

namespace App\Console\Commands;

use App\Models\Student;
use Illuminate\Console\Command;

class NormalizeStudentIds extends Command
{
    protected $signature = 'students:normalize-ids';

    protected $description = 'Strip dashes and slashes from student_id (ID Tahfiz) values and propagate the rename to all referencing tables';

    public function handle(): int
    {
        $students = Student::whereRaw("student_id REGEXP '[-/]'")->get(['student_id']);

        if ($students->isEmpty()) {
            $this->info('No student IDs contain dashes or slashes.');
            return self::SUCCESS;
        }

        $renamed = [];
        foreach ($students as $student) {
            $oldId = $student->student_id;
            $newId = str_replace(['-', '/'], '', $oldId);

            if (! Student::renameId($oldId, $newId)) {
                if ($newId !== $oldId && $newId !== '') {
                    $this->error("Skipping {$oldId}: target ID {$newId} already exists.");
                }
                continue;
            }

            $renamed[$oldId] = $newId;
        }

        if (empty($renamed)) {
            $this->info('Nothing to normalize.');
            return self::SUCCESS;
        }

        $this->info(count($renamed) . ' student ID(s) normalized across student and ' . implode(', ', Student::ID_REFERENCING_TABLES) . ':');
        foreach ($renamed as $oldId => $newId) {
            $this->line("  {$oldId} -> {$newId}");
        }

        return self::SUCCESS;
    }
}
