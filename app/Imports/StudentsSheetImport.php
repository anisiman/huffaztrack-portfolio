<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Program;
use App\Models\Batch;
use App\Models\Sponsorship;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsSheetImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Skip rows missing required fields
        if (empty($row['student_id']) || empty($row['student_name']) || empty($row['batch_name'])) {
            return null;
        }

        // convert batch_name → batch_id (required)
        $batch = Batch::where('batch_name', $row['batch_name'])->first();

        // convert program_name → program_id (optional)
        $program = !empty($row['program_name'])
            ? Program::where('program_name', $row['program_name'])->first()
            : null;

        // convert sponsorship_name → sponsorship_id (optional)
        $sponsorship = !empty($row['sponsorship_name'])
            ? Sponsorship::where('sponsorship_name', $row['sponsorship_name'])->first()
            : null;

        $student = Student::firstOrNew(['student_id' => $row['student_id']]);

        $student->fill([
            'student_name'     => $row['student_name'],
            'batch_id'         => $batch?->batch_id,
            'program_id'       => $program?->program_id,
            'sponsorship_id'   => $sponsorship?->sponsorship_id,
            'gender'           => $row['gender'] ?? null,
            'nric'             => $row['nric'] ?? null,
            'email'            => $row['email'] ?? null,
            'phone'            => $row['phone'] ?? null,
            'address'          => $row['address'] ?? null,
            'town'             => $row['town'] ?? null,
            'state'            => $row['state'] ?? null,
            'postcode'         => $row['postcode'] ?? null,
            'secondary_school' => $row['secondary_school'] ?? null,
            'race'             => $row['race'] ?? null,
        ]);

        return $student;
    }
}