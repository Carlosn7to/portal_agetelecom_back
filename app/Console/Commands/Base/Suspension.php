<?php

namespace App\Console\Commands\Base;

use Illuminate\Console\Command;

class Suspension extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:suspension';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envio de e-mail de suspensão de contratos há mais de 120 dias.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $suspension = new \App\Http\Controllers\AgeCommunicate\Suspension\SuspensionController();
        $suspension->response();


        return Command::SUCCESS;
    }
}
