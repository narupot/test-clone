<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\ApiLog;
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
    protected $description = 'Delete API logs older than 3 days';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            
            $del_time = now()->subDays(3);
            $deleted = ApiLog::where('created_at', '<', $del_time)->delete();

            DB::statement("ALTER TABLE smm_api_log ENGINE=InnoDB");

            $this->info("Deleted {$deleted} API logs older than 3 days.");
            return 0;

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            \Log::error("ClearApiLog Error: " . $e->getMessage());
            return 1;
        }
    }
}
