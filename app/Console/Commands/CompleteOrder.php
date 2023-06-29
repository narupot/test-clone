<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\GeneralFunctions;
use DB;
class CompleteOrder extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CompleteOrder:completeOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will complete order after delivery pickup time at 6pm whose payment complete';

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

        // complete online order

        $before_date = date("Y-m-d", strtotime("-2 day"));
        $order_data = \App\Order::where(['payment_status'=>1])->whereNotIn('order_status',[3,4])->where(DB::raw('date(pickup_time)'),'>=',$before_date)->get();
        $curtime = date('Y-m-d H:i:s');
        
        if(count($order_data)) { 
            foreach ($order_data as $key => $value) {
                $pickup_time = $value->pickup_time;
                
                if(strtotime($curtime) >= strtotime($pickup_time)){

                    /****update order to complete*****/
                    $complete_ord = \App\Order::where('id',$value->id)->update(['order_status'=>3]);

                    $update_details = \App\OrderShop::where(['order_id'=>$value->id])->whereNotIn('order_status',[4,9,10,11,12])->update(['order_status'=>3]);

                    $update_details = \App\OrderDetail::where(['order_id'=>$value->id])->whereNotIn('status',[4,9,10,11,12])->update(['status'=>3]);

                    //cteating order transaction
                    //$comment = 'Online Order cancelled'
                    $comment = GeneralFunctions::getOrderText('order_completed');
                    $transaction_arr = ['order_id'=>$value->id,'order_shop_id'=>0,'order_detail_id'=>0,'event'=>'order','updated_by_id'=>0,'comment'=>$comment,'updated_by'=>'cron'];

                    $update_transaction = \App\OrderTransaction::updateOrdTrans($transaction_arr);

                    $order_shop = \App\OrderShop::select('id')->where('order_id',$value->id)->get();
                    
                    foreach ($order_shop as $shopkey => $shopvalue) {
                        $transaction_arr = ['order_id'=>$value->id,'order_shop_id'=>$shopvalue->id,'order_detail_id'=>0,'event'=>'order','updated_by_id'=>0,'comment'=>$comment,'updated_by'=>'cron'];

                        $update_transaction = \App\OrderTransaction::updateOrdTrans($transaction_arr);
                    }
                }
            }

            echo "Cron run for order ".count($order_data);
        }
     
    }
}
