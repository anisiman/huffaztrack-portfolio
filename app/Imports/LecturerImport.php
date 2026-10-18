<?php

namespace App\Imports;

use App\Models\Lecturer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LecturerImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Lecturer([
            'lecturer_id' => $row['lecturer_id'],
            'lecturer_name' => $row['lecturer_name'],
        ]);
    }
}