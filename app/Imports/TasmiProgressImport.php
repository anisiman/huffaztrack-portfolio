<?php

namespace App\Imports;

use App\Models\TasmiProgress;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TasmiProgressImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new TasmiProgress([

            'tasmi_id' => $row['tasmi_id'],

            'page_reached' => $row['page_reached'],
        ]);
    }
}