<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\TransactionFeeConfig;
use Carbon\Carbon;

class UpdateCurrentTransactionFee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transaction-fee:update-current-tf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update current_tf to tf when effective_date is today or in the past';

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
     * @return int
     */
    public function handle()
    {
        $today = Carbon::today();
        $this->info('Starting transaction fee update at: ' . $today->format('Y-m-d H:i:s'));

        $configs = TransactionFeeConfig::where('effective_date', '<=', $today)
            ->whereColumn('current_tf', '!=', 'tf')
            ->get();

        $this->info('Found ' . $configs->count() . ' transaction fee configs to update.');

        $updatedCount = 0;
        foreach ($configs as $config) {
            $oldValue = $config->current_tf;
            $config->current_tf = $config->tf;
            $config->save();
            
            $this->info("Updated config ID {$config->id}: {$oldValue} -> {$config->current_tf}");
            $updatedCount++;
        }

        $this->info("Transaction fee update completed. Total updated: {$updatedCount}");
        
        if ($updatedCount > 0) {
            $this->info('Transaction fee current_tf updated successfully.');
        } else {
            $this->info('No transaction fee configs needed updating.');
        }
    }
}
