<?php

namespace App\Console\Commands\Aniel;

use App\Http\Controllers\Aniel\Services\OrderServiceV2Controller;
use Illuminate\Console\Command;

class Orders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exporta ordens de serviço para o Aniel';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $exportAniel = new OrderServiceV2Controller();

        $exportAniel->store();


        return Command::SUCCESS;
    }
}
