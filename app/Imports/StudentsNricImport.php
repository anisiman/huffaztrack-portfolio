<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Program;
use App\Models\Batch;
use App\Models\Sponsorship;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Schema;

class StudentsNricImport implements ToModel, WithHeadingRow
{
    protected $defaultBatchId;

    public function __construct($defaultBatchId = null)
    {
        $this->defaultBatchId = $defaultBatchId;
    }

    public function model(array $row)
    {
        // Require NRIC (IC number) only — skip rows without it
        $nric = $row['nric'] ?? null;
        if (empty($nric)) {
            return null;
        }

        // find existing student by nric
        $student = Student::where('nric', $nric)->first();

        // determine batch
        $batchId = null;
        if (!empty($this->defaultBatchId)) {
            $batchId = $this->defaultBatchId;
        } elseif (!empty($row['batch_name'])) {
            $rawName = strtoupper(trim(preg_replace('/\s+/', ' ', (string) $row['batch_name'])));
            $batch = Batch::whereRaw('UPPER(TRIM(batch_name)) = ?', [$rawName])->first();
            $batchId = $batch?->batch_id;
        }

        // optional program and sponsorship lookup
        $program = null;
        if (!empty($row['program_name'])) {
            $progName = trim(preg_replace('/\s+/', ' ', (string) $row['program_name']));
            $program = Program::whereRaw('TRIM(program_name) = ?', [$progName])->first();
        }

        $sponsorship = null;
        if (!empty($row['sponsorship_name'])) {
            $sponName = trim(preg_replace('/\s+/', ' ', (string) $row['sponsorship_name']));
            $sponsorship = Sponsorship::whereRaw('TRIM(sponsorship_name) = ?', [$sponName])->first();
        }

        if ($student) {
            // Allow a re-upload to fix an existing student's ID too. student_id is a
            // primary key referenced by several other tables (attendance, results,
            // tasmi, etc.), so it must be renamed everywhere via Student::renameId()
            // rather than just overwriting the attribute — a plain assignment here
            // would silently orphan that student's history in every other table.
            if (!empty($row['student_id'])) {
                $newId = $this->normalizeStudentId($row['student_id']);
                if ($newId !== '' && $newId !== $student->student_id) {
                    if (Student::renameId($student->student_id, $newId)) {
                        $student = Student::where('student_id', $newId)->first();
                    }
                }
            }
        } else {
            // create new student; if student_id not provided, use NRIC as fallback
            $student = new Student();
            $student->student_id = $this->normalizeStudentId($row['student_id'] ?? $nric);
        }

        // fill allowed fields — nulls are acceptable; only set columns that exist
        $setIfExists = function ($attr, $value) use ($student) {
            if (Schema::hasColumn($student->getTable(), $attr)) {
                $student->{$attr} = $value;
            }
        };

        $setIfExists('student_name', !empty($row['student_name']) ? strtoupper($row['student_name']) : ($student->student_name ?? null));
        $setIfExists('nric', $nric);
        $setIfExists('email', $row['email'] ?? $student->email ?? null);
        $setIfExists('phone', $row['phone'] ?? $student->phone ?? null);
        $setIfExists('gender', $row['gender'] ?? $student->gender ?? null);
        $setIfExists('address', $row['address'] ?? $student->address ?? null);
        $setIfExists('town', $row['town'] ?? $student->town ?? null);
        $setIfExists('state', $row['state'] ?? $student->state ?? null);
        $setIfExists('postcode', $row['postcode'] ?? $student->postcode ?? null);
        $setIfExists('secondary_school', $row['secondary_school'] ?? $student->secondary_school ?? null);
        $setIfExists('race', $row['race'] ?? $student->race ?? null);
        $setIfExists('status', $row['status'] ?? $student->status ?? null);
        $setIfExists('asasi_diploma_id', $row['asasi_diploma_id'] ?? $student->asasi_diploma_id ?? null);
        $setIfExists('ijazah_id', $row['ijazah_id'] ?? $student->ijazah_id ?? null);
        $setIfExists('batch_id', $batchId ?? $student->batch_id ?? null);
        $setIfExists('program_id', $program?->program_id ?? $student->program_id ?? null);
        $setIfExists('sponsorship_id', $sponsorship?->sponsorship_id ?? $student->sponsorship_id ?? null);

        $student->save();
        return null;
    }

    /** Strip dashes/slashes so uploaded IDs match the format used everywhere else (see students:normalize-ids). */
    private function normalizeStudentId(string $studentId): string
    {
        return str_replace(['-', '/'], '', trim($studentId));
    }
}
