<?php

namespace App\Imports;

use App\Models\Program;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ProgramImport implements ToModel, WithStartRow
{
    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        return new Program([
            'college_id'    => $row[0],
            'program_name'  => $row[1],
            'program_level' => $row[2],
        ]);
    }
}