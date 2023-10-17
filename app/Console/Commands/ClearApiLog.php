<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class ClearApiLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ClearApiLog:clearApiLog';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It will delete buyer and seller api log';

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
        $clear_log = \App\ApiLog::where('created_at','<',$del_time)->delete();
    }
  
} 