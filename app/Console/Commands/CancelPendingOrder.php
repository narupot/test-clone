<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\TempProductImage;
use App\Http\Controllers\MarketPlace;

class CancelPendingOrder extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CancelPendingOrder:cancelPendingOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will cancel online/offline pending order whose payment pending';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {

        // cancel online order
        //$add_minut = getConfigValue('AUTO_CANCEL_ONLINE_PENDING_ORDER');
        $add_minut = 180;
        $order_data = \App\Order::where(['payment_status'=>0,'order_status'=>1])->limit(10)->get();
        $curtime = date('Y-m-d H:i:s');
        
        if(count($order_data) && $add_minut) { 
            foreach ($order_data as $key => $value) {
                $order_created_at = $value->created_at;
                $cancel_time = strtotime($order_created_at . "+$add_minut minutes");
               
                if(strtotime($curtime) >= $cancel_time){

                    /****update order to cancel*****/
                    $cancel_ord = \App\Order::where('id',$value->id)->update(['order_status'=>4]);

                    $update_details = \App\OrderShop::where(['order_id'=>$value->id])->update(['order_status'=>4]);

                    $update_details = \App\OrderDetail::where(['order_id'=>$value->id])->update(['status'=>4]);

                    //cteating order transaction
                    $transaction_arr = ['order_id'=>$value->id,'order_shop_id'=>0,'order_detail_id'=>0,'event'=>'cancel','updated_by_id'=>0,'updated_by'=>'cron','comment'=>'Online Order cancelled'];

                    $update_transaction = \App\OrderTransaction::updateOrdTrans($transaction_arr);
                }
            }
        }
     
    }
}
