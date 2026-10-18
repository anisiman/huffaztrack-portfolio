<?php

namespace App\Imports;

use App\Models\College;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class CollegeImport implements ToModel, WithStartRow
{
    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        if (empty($row[0])) {
            return null;
        }

        return new College([
            'college_name' => $row[0],
            'campus'       => $row[1],
        ]);
    }
}