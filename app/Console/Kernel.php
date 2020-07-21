<?php

namespace App\Console;

use App\Imports\BocImport;
use App\Mail\DailyEmail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            Artisan::call("migrate:refresh");
            Excel::import(new BocImport, storage_path('UCLHNHS.xls'));
        })->dailyAt('7:50');

        $schedule->call('App\Http\Controllers\HomeController@DailyEmail')->dailyAt('8:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
