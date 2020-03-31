<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BocImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Telemetry Data' => new ReadingsImport()
        ];
    }
}
