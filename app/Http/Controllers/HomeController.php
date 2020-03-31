<?php

namespace App\Http\Controllers;

use App\Stats;
use JavaScript;
use Carbon\Carbon;
use App\Imports\BocImport;
use App\Imports\ReadingsImport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class HomeController extends Controller
{
    public function index()
    {
        $readings = $this->readings();

        $dates = [];
        $j_results = [];
        $r_results = [];
        $temp_j_results = [];
        $temp_r_results = [];

        $current_date = false;

        foreach ($readings as $reading) {
            $date = Carbon::parse($reading->time)->format('d/m/y');
            if (!in_array($date, $dates)) {
                array_push($dates, $date);
            }
            if (!$current_date) {
                array_push($temp_j_results, $reading->eng_flow);
                array_push($temp_r_results, $reading->real_flow);
                $current_date = $date;
            } elseif ($current_date === $date) {
                array_push($temp_j_results, $reading->eng_flow);
                array_push($temp_r_results, $reading->real_flow);
            } elseif ($current_date != $date) {
                array_push($j_results, $temp_j_results);
                array_push($r_results, $temp_r_results);
                $temp_j_results = [];
                $temp_r_results = [];
                $current_date = $date;
                array_push($temp_j_results, $reading->eng_flow);
                array_push($temp_r_results, $reading->real_flow);
            }
        }

        if (!empty($temp_r_results)) {
            array_push($j_results, $temp_j_results);
            array_push($r_results, $temp_r_results);
            $temp_j_results = [];
            $temp_r_results = [];
        }

        $avg_j_result = [];
        $avg_r_result = [];

        foreach ($j_results as $j_result) {
            array_push($avg_j_result, round(Stats::mean($j_result)));
        }

        foreach ($r_results as $r_result) {
            array_push($avg_r_result, round(Stats::mean($r_result)));
        }

        JavaScript::put([
            'dates' => array_reverse($dates),
            'avg_j_result' => array_reverse($avg_j_result),
            'avg_r_result' => array_reverse($avg_r_result)
        ]);

        return view('home', compact('readings'));
    }

    public function upload()
    {
        Excel::import(new BocImport, storage_path('UCLHNHS.xls'));

        return redirect('/');
    }

    public function data()
    {
        $readings = $this->readings();

        $data = [];

        foreach ($readings as $reading) {
            array_push($data, [strtotime($reading->time) * 1000, (float) $reading->real_flow]);
        }

        return json_encode(array_reverse($data));
    }

    public static function readings()
    {
        $readings = DB::select(
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
        );

        return array_filter($readings, function ($item) {
            return $item->minutes >= 60;
        });
    }
}
