<?php

namespace App\Http\Controllers;

use Validator;
use JavaScript;
use Carbon\Carbon;
use App\Mail\DailyEmail;
use App\Imports\BocImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Artisan;

class HomeController extends Controller
{
    public function index()
    {
        $data = $this->parse_readings();

        $readings = $data['readings'];

        JavaScript::put([
            'dates' => $data['dates'],
            'avg_j_result' => $data['avg_j_result'],
            'avg_r_result' => $data['avg_r_result'],
            'avg_nhnn_result' => $data['avg_nhnn_result']
        ]);

        return view('home', compact('readings'));
    }

    public function parse_readings()
    {
        $readings = $this->readings();

        $dates = $readings[0]->map(function ($reading) {
            return Carbon::parse($reading->time)->format('d/m/y');
        })->reverse()->unique()->values()->toArray();

        $avg_j_result = $readings[0]->groupBy(function ($reading) {
            return Carbon::parse($reading->time)->format('d/m/y');
        })->map(function ($group) {
            return round($group->avg('eng_flow'));
        })->reverse()->values()->toArray();

        $avg_r_result = $readings[0]->groupBy(function ($reading) {
            return Carbon::parse($reading->time)->format('d/m/y');
        })->map(function ($group) {
            return round($group->avg('real_flow'));
        })->reverse()->values()->toArray();

        $avg_nhnn_result = $readings[1]->groupBy(function ($reading) {
            return Carbon::parse($reading->time)->format('d/m/y');
        })->map(function ($group) {
            return round($group->avg('flow'));
        })->reverse()->values()->toArray();

        return ['readings' => $readings, 'dates' => $dates, 'avg_j_result' => $avg_j_result, 'avg_r_result' => $avg_r_result, 'avg_nhnn_result' => $avg_nhnn_result];
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
        return self::readings()[0]->map(function ($reading) {
            return [strtotime($reading->time) * 1000, (float) $reading->real_flow];
        })->reverse()->values()->toJson();
    }

    public function data_nhnn()
    {
        return self::readings()[1]->map(function ($reading) {
            return [strtotime($reading->time) * 1000, (float) $reading->flow];
        })->reverse()->values()->toJson();
    }

    public static function readings()
    {
        $main_tank = collect(DB::select(
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

        $nhnn = collect(DB::select(
            "SELECT 
            b1.time AS 'time',
            b1.volume AS 'volume',
            ROUND(@ta_usage:=(b2.volume - b1.volume) * 1000,
                    2) AS 'usage',
            @minutes:=TIMESTAMPDIFF(MINUTE, b2.time, b1.time) AS 'minutes',
            @ta_flow:=ROUND(@ta_usage / @minutes) AS 'flow'
        FROM
            nhnn b1
                INNER JOIN
            nhnn b2 ON b2.id = b1.id + 1"
        ))->filter(function ($item) {
            return $item->flow > 0 && $item->minutes >= 60;
        })->slice(1);

        return [$main_tank, $nhnn];
    }

    public function rpa()
    {
        Artisan::call("migrate:rollback");
        Artisan::call("migrate");
        Excel::import(new BocImport, storage_path('UCLHNHS.xls'));
        return redirect('/');
    }

    public function daily_email()
    {
        $readings = $this->parse_readings();

        $dates = array_slice($readings['dates'], -6, 6);
        $uch = array_slice($readings['avg_r_result'], -6, 6);
        $nhnn = array_slice($readings['avg_nhnn_result'], -6, 6);

        $dates_format = array_map(function ($value) {
            return Carbon::createFromFormat('d/m/Y', $value)->format('jS M');
        }, $dates);

        $uch_limit = array_map(function ($value) {
            return round($value / 50);
        }, $uch);

        $nhnn_limit = array_map(function ($value) {
            return round($value / 30);
        }, $nhnn);

        $uch_change = [];

        $nhnn_change = [];

        for ($x = 1; $x < 6; $x++) {
            array_push($uch_change, round((($uch[$x] - $uch[$x - 1]) / 50), 1));
        }

        for ($x = 1; $x < 6; $x++) {
            array_push($nhnn_change, round((($nhnn[$x] - $nhnn[$x - 1]) / 30), 1));
        }

        array_shift($dates_format);
        array_shift($uch);
        array_shift($nhnn);
        array_shift($uch_limit);
        array_shift($nhnn_limit);

        //dd($dates, $dates_format, $uch, $nhnn, $uch_limit, $nhnn_limit, $uch_change, $nhnn_change);

        $uch_data = [];
        $nhnn_data = [];

        foreach ($dates_format as $key => $value) {
            array_push($uch_data, ['date' => $value, 'reading' => $uch[$key], 'limit' => $uch_limit[$key], 'change' => $uch_change[$key]]);
        }

        foreach ($dates_format as $key => $value) {
            array_push($nhnn_data, ['date' => $value, 'reading' => $nhnn[$key], 'limit' => $nhnn_limit[$key], 'change' => $nhnn_change[$key]]);
        }

        $data = ['uch' => $uch_data, 'nhnn' => $nhnn_data];

        dd($data);

        return new DailyEmail($data);
    }
}
