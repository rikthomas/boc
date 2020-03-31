<?php

namespace App\Imports;

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
