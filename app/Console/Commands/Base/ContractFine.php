<?php

namespace App\Console\Commands\Base;

use App\Http\Controllers\Voalle\ContractFineController;
use Illuminate\Console\Command;

class ContractFine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:warning';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia um aviso via whatsapp para alertar que a rotina parou de funcionar.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $warning = new ContractFineController();

        $warning->verifyTime();

        return Command::SUCCESS;
    }
}
