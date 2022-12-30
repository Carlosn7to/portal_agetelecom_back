<?php

namespace App\Console;

use App\Http\Controllers\AgeRv\VoalleSalesController;
use App\Http\Controllers\DataWarehouse\Voalle\AuthenticationContractsController;
use App\Http\Controllers\DataWarehouse\Voalle\ContractsController;
use App\Http\Controllers\DataWarehouse\Voalle\ContractsTypeController;
use App\Http\Controllers\DataWarehouse\Voalle\PeoplesController;
use App\Http\Controllers\DataWarehouse\Voalle\ServiceProductsController;
use App\Http\Controllers\TestController;
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

        $schedule->call(new AuthenticationContractsController())->dailyAt('15:54');
        $schedule->call(new ContractsController())->dailyAt('15:54');
        $schedule->call(new ContractsTypeController())->dailyAt('15:54');
        $schedule->call(new PeoplesController())->dailyAt('15:54');
        $schedule->call(new ServiceProductsController())->dailyAt('15:54');
        $schedule->call(new VoalleSalesController())->dailyAt('15:54');


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
