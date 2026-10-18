<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class StudentsImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [

            'T1' => new StudentsSheetImport(),
            'T2' => new StudentsSheetImport(),
            'T3' => new StudentsSheetImport(),
            'T4' => new StudentsSheetImport(),
            'T5' => new StudentsSheetImport(),
            'T6' => new StudentsSheetImport(),
            'T7' => new StudentsSheetImport(),
            'T8' => new StudentsSheetImport(),
            'T9' => new StudentsSheetImport(),
            'T10' => new StudentsSheetImport(),
            'T11' => new StudentsSheetImport(),
            'T12' => new StudentsSheetImport(),
            'T13' => new StudentsSheetImport(),
            'T14' => new StudentsSheetImport(),
            'T15' => new StudentsSheetImport(),

        ];
    }
}