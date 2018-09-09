<?php

namespace App\Console\Commands;

use App\Mail\TesteSyncTrello;
use App\Services\APIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class syncTrello extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizar todas as tarefas da equipe.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        Mail::to("sdfsdfsd@dfsd.com")->send(new TesteSyncTrello(APIService::execute()));

    }
}
