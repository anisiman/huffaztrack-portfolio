<?php

namespace App\Imports;

use App\Models\Sponsorship;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SponsorshipImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Sponsorship([

            'sponsorship_name' => $row['sponsorship_name'],

            'sponsorship_duration' => $row['sponsorship_duration'],
        ]);
    }
}