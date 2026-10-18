<?php

namespace App\Imports;

use App\Models\Tasmi;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TasmiImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Tasmi([

            'student_id' => $row['student_id'],

            'lecturer_id' => $row['lecturer_id'],

            'semester_id' => $row['semester_id'],

            'week' => $row['week'],

            'target_page' => $row['target_page'],

            'tasmi_date' => $row['tasmi_date'],
        ]);
    }
}