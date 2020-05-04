<?php

namespace App\Http\Controllers;

use Validator;
use JavaScript;
use Carbon\Carbon;
use App\Imports\BocImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    public function index()
    {
        $readings = $this->readings();

        $dates = $readings->map(function ($reading) {
            return Carbon::parse($reading->time)->format('d/m/y');
        })->reverse()->unique()->values()->toArray();

        $avg_j_result = $readings->groupBy(function ($reading) {
            return Carbon::parse($reading->time)->format('d/m/y');
        })->map(function ($group) {
            return round($group->avg('eng_flow'));
        })->reverse()->values()->toArray();

        $avg_r_result = $readings->groupBy(function ($reading) {
            return Carbon::parse($reading->time)->format('d/m/y');
        })->map(function ($group) {
            return round($group->avg('real_flow'));
        })->reverse()->values()->toArray();

        JavaScript::put([
            'dates' => $dates,
            'avg_j_result' => $avg_j_result,
            'avg_r_result' => $avg_r_result
        ]);

        return view('home', compact('readings'));
    }

    public function upload(Request $request)
    {

        Artisan::call("migrate:rollback");
        Artisan::call("migrate");

        Excel::import(new BocImport, $request->file);

        return redirect('/');
    }

    public function data()
    {
        return self::readings()->map(function ($reading) {
            return [strtotime($reading->time) * 1000, (float) $reading->real_flow];
        })->reverse()->values()->toJson();
    }

    public static function readings()
    {
        return collect(DB::select(
            "SELECT
            b1.time AS 'time',
            b1.volume AS 'ta_volume',
            ROUND(@ta_usage:=(b2.volume - b1.volume) * 1000,
                    2) AS 'ta_usage',
            @minutes:=TIMESTAMPDIFF(MINUTE, b2.time, b1.time) AS 'minutes',
            @ta_flow:=ROUND(@ta_usage / @minutes) AS 'ta_flow',
            b3.volume AS 'tb_volume',
            ROUND(@tb_usage:=(b4.volume - b3.volume) * 1000,
                    2) AS 'tb_usage',
            @tb_flow:=ROUND(@tb_usage / @minutes) AS 'tb_flow',
            @ta_flow + @tb_flow AS 'eng_flow',
            IF(SIGN(@ta_flow) != 1
                    AND SIGN(@tb_flow) != 1,
                NULL,
                IF(SIGN(@ta_flow) != 1,
                    @tb_flow,
                    IF(SIGN(@tb_flow) != 1,
                        @ta_flow,
                        @ta_flow + @tb_flow))) AS 'real_flow'
        FROM
            tank_a b1
                INNER JOIN
            tank_a b2 ON b2.id = b1.id + 1
                INNER JOIN
            tank_b b3 ON b1.id = b3.id
                INNER JOIN
            tank_b b4 ON b4.id = b3.id + 1"
        ))->filter(function ($item) {
            return $item->minutes >= 60;
        })->slice(1);
    }

    public function rpa()
    {
        Artisan::call("migrate:rollback");
        Artisan::call("migrate");

        Excel::import(new BocImport, 'http://uclh-icu.org.uk/vie/current/public/UCLHNHS.xls');

        return redirect('/');
    }
}
