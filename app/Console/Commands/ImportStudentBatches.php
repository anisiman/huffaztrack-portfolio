<?php

namespace App\Console\Commands;

use App\Models\Batch;
use App\Models\Lecturer;
use App\Models\Program;
use App\Models\Sponsorship;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportStudentBatches extends Command
{
    protected $signature = 'students:import-batches {from=10} {to=15} {--dry-run : Preview row counts without writing to the database}';

    protected $description = 'Import student rows from database/data/StudentT{n}.csv for a range of batch numbers only — '
        . 'the student-table portion of MasterlistSeeder, without its lookup-table, attendance, or demo-data steps.';

    public function handle(): int
    {
        $from = (int) $this->argument('from');
        $to = (int) $this->argument('to');
        $dryRun = (bool) $this->option('dry-run');

        if ($from < 1 || $to < $from) {
            $this->error('Invalid range: "from" must be >= 1 and <= "to".');
            return self::FAILURE;
        }

        if ($dryRun) {
            $this->warn('Dry run — no changes will be written.');
        }

        $dataPath = base_path('database/data/');
        $totalRows = 0;

        for ($i = $from; $i <= $to; $i++) {
            $fileName = "StudentT{$i}.csv";
            $filePath = $dataPath . $fileName;

            if (! file_exists($filePath)) {
                $this->warn("File not found, skipping: {$fileName}");
                continue;
            }

            $this->info("Importing students from: {$fileName}");
            $count = 0;

            if (($handle = fopen($filePath, 'r')) !== false) {
                fgetcsv($handle); // skip header

                while (($data = fgetcsv($handle, 0, ',')) !== false) {
                    if (! isset($data[0]) || trim($data[0]) === '') {
                        continue;
                    }

                    $studentId = $this->normalizeStudentId($data[0]);
                    if (! $studentId) {
                        continue;
                    }

                    if (! $dryRun) {
                        DB::transaction(fn () => $this->importRow($studentId, $data));
                    }

                    $count++;
                }

                fclose($handle);
            }

            $this->info("  {$count} row(s) " . ($dryRun ? 'would be processed' : 'processed') . " from {$fileName}.");
            $totalRows += $count;
        }

        $verb = $dryRun ? 'would be processed' : 'processed';
        $this->info("Done. {$totalRows} student row(s) {$verb} across batches T{$from}-T{$to}.");

        return self::SUCCESS;
    }

    /**
     * Mirrors MasterlistSeeder's per-row student import exactly (same column
     * indices, same lookups), scoped to just this one row.
     */
    private function importRow(string $studentId, array $data): void
    {
        $programName = isset($data[9]) ? trim(str_replace("\u{A0}", ' ', $data[9])) : '';
        $sponsorshipName = isset($data[10]) ? trim(str_replace("\u{A0}", ' ', $data[10])) : '';

        $program = !empty($programName)
            ? Program::where('program_name', 'LIKE', $programName)->first()
            : null;

        $sponsorship = !empty($sponsorshipName)
            ? Sponsorship::where('sponsorship_name', 'LIKE', $sponsorshipName)->first()
            : null;

        $batch = isset($data[11])
            ? Batch::where('batch_name', trim($data[11]))->first()
            : null;

        $statusVal = isset($data[12]) ? strtoupper(trim($data[12])) : null;
        $asasiDiplomaId = isset($data[13]) ? trim($data[13]) : null;
        $ijazahId = isset($data[14]) ? trim($data[14]) : null;

        // Lecturer only exists if column index 15 is populated (like T13/T14)
        $lecturer = null;
        if (isset($data[15]) && !empty(trim($data[15]))) {
            $lecturer = Lecturer::where('lecturer_name', 'LIKE', trim($data[15]))->first();
        }

        $student = Student::updateOrCreate(
            ['student_id' => $studentId],
            [
                'student_name'     => strtoupper(trim($data[1] ?? '')),
                'nric'             => trim($data[2] ?? ''),
                'secondary_school' => trim($data[3] ?? ''),
                'gender'           => strtoupper(trim($data[4] ?? '')),
                'email'            => trim($data[5] ?? ''),
                'phone'            => trim($data[6] ?? ''),
                'address'          => trim($data[7] ?? ''),
                'state'            => trim($data[8] ?? ''),
                'status'           => $statusVal,
                'program_id'       => $program->program_id ?? null,
                'sponsorship_id'   => $sponsorship->sponsorship_id ?? null,
                'batch_id'         => $batch->batch_id ?? null,
                'asasi_diploma_id' => $asasiDiplomaId,
                'ijazah_id'        => $ijazahId,
                'lecturer_id'      => $lecturer->lecturer_id ?? null,
            ]
        );

        if ($program) {
            DB::table('student_programs')->updateOrInsert(
                [
                    'student_id' => $student->student_id,
                    'program_id' => $program->program_id,
                ],
                [
                    'is_current' => true,
                    'start_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function normalizeStudentId(string $studentId): ?string
    {
        $cleaned = trim($studentId);
        if ($cleaned === '') {
            return null;
        }

        return str_replace('UTN7- ', 'UTN7-', $cleaned);
    }
}
