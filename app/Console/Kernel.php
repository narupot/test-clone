<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        
        '\App\Console\Commands\SendOrderLogistic',
        '\App\Console\Commands\ClearCartItem',
        '\App\Console\Commands\ExportOrder',
        '\App\Console\Commands\CancelPendingOrder',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $send_order_logistic = base_path("storage/logs/send_order_logistic.log");
        $send_clear_cart = base_path("storage/logs/send_clear_cart.log");
        $send_export_order = base_path("storage/logs/send_export_order.log");
        $cancel_pending_order = base_path("storage/logs/cancel_pending_order.log");

        $schedule->command('sendOrderLogistic:sendOrderLogistic')->everyMinute()->sendOutputTo($send_order_logistic); 

        $schedule->command('ClearCartItem:clearCartItem')->everyTenMinutes()->sendOutputTo($send_clear_cart); 
        $schedule->command('ExportOrder:exportOrder')->dailyAt('01:00')->sendOutputTo($send_export_order); 
        //cancel online pending order whose payment pending
        $schedule->command('CancelPendingOrder:cancelPendingOrder')->everyMinute()->sendOutputTo($cancel_pending_order);

    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
