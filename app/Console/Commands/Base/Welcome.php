<?php

namespace App\Console\Commands\Base;

use App\Http\Controllers\AgeCommunicate\Base\Welcome\WelcomeController;
use Illuminate\Console\Command;

class Welcome extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:welcome';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Régua de boas vindas';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $welcome = new WelcomeController();

        $welcome->__invoke();

        return Command::SUCCESS;
    }
}
