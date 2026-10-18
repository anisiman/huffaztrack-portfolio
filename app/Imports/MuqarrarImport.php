<?php

namespace App\Imports;

use App\Models\Muqarrar;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MuqarrarImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Muqarrar([

            'semester_id' => $row['semester_id'],

            'week' => $row['week'],

            'target_page' => $row['target_page'],
        ]);
    }
}