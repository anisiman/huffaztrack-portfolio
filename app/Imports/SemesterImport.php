<?php

namespace App\Imports;

use App\Models\Semester;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SemesterImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Semester([

            'semester_name' => $row['semester_name'],
        ]);
    }
}