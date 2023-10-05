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
        $clear_log_days = \App\SystemConfig::getSystemValFromDb('CLEAR_MOBILE_API_LOG');
        $date = date('Y-m-d H:i:s');
        $new_time = date($date, strtotime('-'.$clear_log_days, strtotime($date)));

       
        $del_time = date("Y-m-d H:i:s", strtotime('-2 month', strtotime($date)));
        $clear_log = \App\ApiLog::where('created_at','<',$del_time)->delete();
    }
  
} 