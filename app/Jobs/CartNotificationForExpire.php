<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Http\Controllers\MarketPlace;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class CartNotificationForExpire implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $clear_hr = getConfigValue('CART_CLEAR_TIME');

        $date = date('Y-m-d H:i:s');
        $current_time = strtotime($date);
        $orders = \App\OrdersTemp::where('order_status','0')->get();
        $title = 'กรุณาชำระเงินภายในเวลา ';
        $body = 'กรุณาชำระเงินภายในเวลา ';
        $url = Config::get('constants.mobile_notification_url');
        foreach($orders as $order){
            $sendNoti = 0;
            if($order->user_id > 0){
                $order_time_in_cart = strtotime($order->created_at);
                $order_time_in_mint = floor(abs($current_time-$order_time_in_cart)/60);
                if($order_time_in_mint >= 120 && $order_time_in_mint < 150){
                     $messageTitle = $title. '1 ชั่วโมง';
                     $messageBody = $body. '1 ชั่วโมง';
                     if($order->noti_60 == '2'){
                         $sendNoti = 1;
                         $order->noti_60 = '1';

                     }
                 }elseif($order_time_in_mint >= 150 && $order_time_in_mint < 170){
                     $messageTitle = $title. '30 นาที';
                     $messageBody = $body. '30 นาที';
                     if($order->noti_30 == '2'){
                         $sendNoti = 1;
                         $order->noti_30 = '1';
                     }


                }elseif($order_time_in_mint > 178 && $order_time_in_mint <= 180){
                    $messageTitle = 'คุณไม่ได้ชำระเงินในเวลาที่กำหนด กรุณาเลือกซื้อสินค้าอีกครั้ง';
                    $messageBody =  'คุณไม่ได้ชำระเงินในเวลาที่กำหนด กรุณาเลือกซื้อสินค้าอีกครั้ง';
                    $sendNoti = 1;
                }
                if($sendNoti == 1){
                    $post_arr = ['user_id'=>$order->user_id, 'title'=>$messageTitle,'body'=>$messageBody, 'type_redirect'=>'cart_notify'];
                    try {
                        $requestHandler  = new MarketPlace;
                        $responce = $requestHandler->handleCurlRequest($url,$post_arr);
                    } catch (\Exception $e) {
                        Log::error('Notification failed: '.$e->getMessage());
                    }
                    $order->save();
                }

            }

        }

    }
}
