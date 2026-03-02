<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Jobs\CancelPendingOrder;
use App\Jobs\CartNotificationForExpire;
use App\Jobs\SendOrderLogistic;
use App\Jobs\ClearCartItem;
use App\Jobs\SendOrderWMS;

use App\Jobs\SendOrderMovemaxJob;
use App\Order;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        
        '\App\Console\Commands\ExportOrder',
        '\App\Console\Commands\CompleteOrder',
        '\App\Console\Commands\ClearApiLog',
        '\App\Console\Commands\ResizeImage',
        '\App\Console\Commands\ResizeImageByFolder',
        '\App\Console\Commands\ClearLog',
        '\App\Console\Commands\UpdateCurrentTransactionFee',
        '\App\Console\Commands\ReturnStock',
        // '\App\Console\Commands\ProcessBeamPayment',

        // '\App\Console\Commands\CancelPendingOrder',
        // '\App\Console\Commands\CartNotificationForExpire',
        // '\App\Console\Commands\SendOrderLogistic',
        // '\App\Console\Commands\ClearCartItem',
        '\App\Console\Commands\SendOrderWMS',
        '\App\Console\Commands\SendOrderMovemax',


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
        $cancel_pending_order = base_path("storage/logs/cancel_pending_order.log");
        $cart_notification_expire = base_path("storage/logs/cart_notification_expire.log");
        $complete_order = base_path("storage/logs/complete_order.log");
        $clear_api_log = base_path("storage/logs/clear_api_log.log");
        $queue_log = base_path("storage/logs/queue.log");
        $transaction_fee_update = base_path("storage/logs/transaction_fee_update.log");
        // $send_export_order = base_path("storage/logs/send_export_order.log");
        // $resize_image_log = base_path("storage/logs/resize_image_log.log");
        $send_order_movemax = base_path("storage/logs/send_order_movemax.log");
        $send_order_wms = base_path("storage/logs/send_order_wms.log");

       

        $schedule->command('CompleteOrder:completeOrder')->dailyAt('18:00')->sendOutputTo($complete_order);
        $schedule->command('ClearApiLog:clearApiLog')->dailyAt('06:00')->sendOutputTo($clear_api_log);
        $schedule->command('ClearLog:clearLog')->dailyAt('06:00');
        
        // Update transaction fee current_tf every midnight
        $schedule->command('transaction-fee:update-current-tf')->dailyAt('00:00')->sendOutputTo($transaction_fee_update);

        // $schedule->command('CancelPendingOrder:cancelPendingOrder')->withoutOverlapping()->everyMinute()->sendOutputTo($cancel_pending_order);
        // $schedule->command('CartNotificationForExpire:cartNotificationForExpire')->withoutOverlapping()->everyMinute()->sendOutputTo($cart_notification_expire);
        // $schedule->command('sendOrderLogistic:sendOrderLogistic')->withoutOverlapping()->everyMinute()->sendOutputTo($send_order_logistic);
        // $schedule->command('ClearCartItem:clearCartItem')->withoutOverlapping()->everyMinute()->sendOutputTo($send_clear_cart);

        //$schedule->command('ResizeImage:resizeimage')->dailyAt('01:00')->sendOutputTo($resize_image_log);  
        // $schedule->command('ExportOrder:exportOrder')->dailyAt('01:00')->sendOutputTo($send_export_order);


        // Queue jobs
        $schedule->call(function () {
            dispatch(new CancelPendingOrder);
        })->name('cancel_pending_order_job')->withoutOverlapping()->everyMinute()->sendOutputTo($cancel_pending_order);

        $schedule->call(function () {
            dispatch(new CartNotificationForExpire);
        })->name('cart_notification_expire_job')->withoutOverlapping()->everyMinute()->sendOutputTo($cart_notification_expire);

        // $schedule->call(function () {
        //     dispatch(new SendOrderLogistic);
        // })->name('send_order_logistic_job')->withoutOverlapping()->everyMinute()->sendOutputTo($send_order_logistic);

        $schedule->call(function () {
            dispatch(new ClearCartItem);
        })->name('clear_cart_item_job')->withoutOverlapping()->everyMinute()->sendOutputTo($send_clear_cart);
        
        $schedule->command('SendOrderWMS:sendOrderWMS')->everyMinute()->sendOutputTo($send_order_wms);  
        

        $schedule->command('SendOrderMovemax:sendOrderMovemax')
        ->name('send_order_movemax_job')
        ->withoutOverlapping()
        ->everyMinute()
        ->sendOutputTo($send_order_movemax);
        
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
