<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class ClearLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ClearLog:clearLog';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It will delete all log';

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
        $clear = \App\SystemConfig::getSystemValFromDb('ADMIN_ACTIVITY_LOG');
        $days_ago = date('Y-m-d', strtotime('-'.$clear, strtotime(date('Y-m-d'))));
        $clear_apilog = \App\ApiLog::where('created_at','<',$days_ago)->delete();
        $clear_adminlog = \App\AdminLogDetail::where('created_at','<',$days_ago)->delete();
        $clear_activitylog = \App\Logactivity::where('created_at','<',$days_ago)->delete();
        $clear_userlog = \App\UserLogDetail::where('created_at','<',$days_ago)->delete();
    }
  
} 