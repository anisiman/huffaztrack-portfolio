<?php

namespace App\Imports;

use App\Models\Batch;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BatchImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Batch([

            'batch_name' => $row['batch_name'],

            'intake' => $row['intake'],

            'date_registered' => $row['date_registered'],
        ]);
    }
}