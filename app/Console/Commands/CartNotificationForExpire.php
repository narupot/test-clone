<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Http\Controllers\MarketPlace;
use Config;

class CartNotificationForExpire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CartNotificationForExpire:cartNotificationForExpire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Noitification for cart will expire in time';

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
        $clear_hr = getConfigValue('CART_CLEAR_TIME');

        $date = date('Y-m-d H:i:s');
        $current_time = strtotime($date);
        $orders = \App\OrdersTemp::where('created_at','<=',$one_hour_left)->where('order_status','0')->get();
        $title = 'ตระกร้าของคุณมีเวลาเหลือ ';
        $body = 'ตระกร้าของคุณมีเวลาเหลือ ';
        $url = Config::get('constants.mobile_notification_url');
        foreach($orders as $order){
            $sendNoti = 0;  
            $order_time_in_cart = strtotime($order->created_at);
            $order_time_in_mint = abs($current_time-$order_time_in_cart)/60;
            if($order_time_in_mint >= 120 && $order_time_in_mint < 150){
                 $messageTitle = $title. '60 นาที';
                 $messageBody = $body. '60 นาที';
                 if($order->noti_60 == '2'){
                     $sendNoti = 1; 
                     $order->noti_60 = '1';
                     $order->save();

                 }
             }elseif($order_time_in_mint >= 150 && $order_time_in_mint < 170){
                 $messageTitle = $title. '30 นาที';
                 $messageBody = $body. '30 นาที';
                 if($order->noti_30 == '2'){
                     $sendNoti = 1; 
                     $order->noti_30 = '1';
                     $order->save();

                 }


            }else if($order_time_in_mint > 178 && $order_time_in_mint <= 180){
                 $messageTitle = $title. 'ตระกร้าของคุณหมดอายุ';
                 $messageBody = $body. 'ตระกร้าของคุณหมดอายุ';
                 $sendNoti = 1; 
            }
            
            if($sendNoti == 1){
                $post_arr = ['user_id'=>$order->user_id, 'title'=>$messageTitle,'body'=>$messageBody, 'type_redirect'=>'cart_notify']; 
                $responce = $this->handleCurlRequest($url,$post_arr);
            }



        }

        echo 'done';
        exit;
    }
  
} 