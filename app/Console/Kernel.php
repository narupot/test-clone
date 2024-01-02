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
        '\App\Console\Commands\CompleteOrder',
        '\App\Console\Commands\ClearApiLog',
        '\App\Console\Commands\CartNotificationForExpire',
        '\App\Console\Commands\ResizeImage',
        '\App\Console\Commands\ResizeImageByFolder',
        '\App\Console\Commands\ClearLog',

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
        $cart_notification_expire = base_path("storage/logs/cart_notification_expire.log");
        $cancel_pending_order = base_path("storage/logs/complete_order.log");
        $clear_api_log = base_path("storage/logs/clear_api_log.log");

        $resize_image_log = base_path("storage/logs/resize_image_log.log");

        $schedule->command('sendOrderLogistic:sendOrderLogistic')->withoutOverlapping()->everyMinute()->sendOutputTo($send_order_logistic); 
        $schedule->command('ClearCartItem:clearCartItem')->withoutOverlapping()->everyMinute()->sendOutputTo($send_clear_cart); 
        $schedule->command('ExportOrder:exportOrder')->dailyAt('01:00')->sendOutputTo($send_export_order); 
        //cancel online pending order whose payment pending
        $schedule->command('CancelPendingOrder:cancelPendingOrder')->withoutOverlapping()->everyMinute()->sendOutputTo($cancel_pending_order);

        $schedule->command('CartNotificationForExpire:cartNotificationForExpire')->withoutOverlapping()->everyMinute()->sendOutputTo($cart_notification_expire);

        $schedule->command('CompleteOrder:completeOrder')->dailyAt('18:00')->sendOutputTo($send_export_order); 

        $schedule->command('ClearApiLog:clearApiLog')->dailyAt('23:00')->sendOutputTo($clear_api_log);
        $schedule->command('ClearLog:clearLog')->dailyAt('23:00');
        //$schedule->command('ResizeImage:resizeimage')->dailyAt('01:00')->sendOutputTo($resize_image_log);  

        

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
