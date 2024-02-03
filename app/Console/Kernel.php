<?php

namespace App\Console;

use App\Http\Controllers\AgeCommunicate\Base\Welcome\WelcomeController;
use App\Http\Controllers\AgeRv\VoalleSalesController;
use App\Http\Controllers\DataWarehouse\Voalle\AuthenticationContractsController;
use App\Http\Controllers\DataWarehouse\Voalle\ContractsController;
use App\Http\Controllers\DataWarehouse\Voalle\ContractsTypeController;
use App\Http\Controllers\DataWarehouse\Voalle\PeoplesController;
use App\Http\Controllers\DataWarehouse\Voalle\ServiceProductsController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Voalle\ContractFineController;
use App\Models\AgeRv\VoalleSales;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        $schedule->command('update:tables')->dailyAt('3:00');
        $schedule->command('send:billing-rule')->weekdays()->dailyAt('11:30');
        $schedule->command('send:suspension')->weekdays()->dailyAt('9:00');
        $schedule->command('send:welcome')->everyFiveMinutes();
        $schedule->call(function () {
           $report = new WelcomeController();
           $report->sendReport();
        })->dailyAt('22:00');
        $schedule->command('send:blockedClients')->weekdays()->dailyAt('17:00');
        $schedule->command('export:order')->everyFiveMinutes();
        $schedule->command('send:warning')->everyMinute();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');


    }
}
