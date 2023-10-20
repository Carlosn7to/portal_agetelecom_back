<?php

namespace App\Console\Commands\BillingRule;

use App\Http\Controllers\BillingRule\BuilderController;
use Illuminate\Console\Command;

class Send extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:billing-rule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Realiza o envio de SMS/E-mail e Whatsapp';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        set_time_limit(30000);


        $builder = new \App\Http\Controllers\AgeCommunicate\BillingRule\BuilderController();
        $builder->__invoke();
    }
}
