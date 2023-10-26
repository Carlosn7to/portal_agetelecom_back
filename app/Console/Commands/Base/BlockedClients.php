<?php

namespace App\Console\Commands\Base;

use App\Http\Controllers\AgeCommunicate\BlockedClients\BlockedClientsController;
use Illuminate\Console\Command;

class BlockedClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:blockedClients';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia e-mail avisando sobre a rotina de bloqueio automático de clientes inadimplentes.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $blockedClients = new BlockedClientsController();

        $blockedClients->response();


        return Command::SUCCESS;
    }
}
