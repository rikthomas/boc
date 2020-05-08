<?php

namespace App\Imports;

use App\Nhnn;
use App\TankA;
use App\TankB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ReadingsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if ($row['channel_desc'] === '*tank A') {
                TankA::create([
                    'time' => Carbon::parse($row['datetime'])->format('Y-m-d H:i:s'),
                    'volume' => $row['equip_units']
                ]);
            } elseif ($row['channel_desc'] === '*tank B') {
                TankB::create([
                    'time' => Carbon::parse($row['datetime'])->format('Y-m-d H:i:s'),
                    'volume' => $row['equip_units']
                ]);
            } elseif ($row['channel_desc'] === ' MAIN TANK') {
                Nhnn::create([
                    'time' => Carbon::parse($row['datetime'])->format('Y-m-d H:i:s'),
                    'volume' => $row['equip_units']
                ]);
            }
        }
    }
}
