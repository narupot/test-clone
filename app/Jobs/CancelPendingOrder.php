<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Helpers\GeneralFunctions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CancelPendingOrder implements ShouldQueue
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
        $add_minut = 60;
        $order_data = \App\Order::where(['payment_status'=>0,'order_status'=>1])->limit(10)->get();
        $curtime = date('Y-m-d H:i:s');
        
        if(count($order_data) && $add_minut) {
            foreach ($order_data as $key => $value) {
                try {
                    $order_created_at = $value->created_at;
                    $cancel_time = strtotime($order_created_at . "+$add_minut minutes");
                
                    if(strtotime($curtime) >= $cancel_time){

                        $cancel_ord = \App\Order::where('id',$value->id)->update(['order_status'=>4]);
                        $update_details = \App\OrderShop::where(['order_id'=>$value->id])->update(['order_status'=>4]);
                        $update_details = \App\OrderDetail::where(['order_id'=>$value->id])->update(['status'=>4]);
                        $comment = GeneralFunctions::getOrderText('order_cancelled');
                        $transaction_arr = ['order_id'=>$value->id,'order_shop_id'=>0,'order_detail_id'=>0,'event'=>'cancel','updated_by_id'=>0,'comment'=>$comment,'updated_by'=>'cron'];
                        $update_transaction = \App\OrderTransaction::updateOrdTrans($transaction_arr);
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to cancel order #{$value->id}: " . $e->getMessage());
                }
            }
        }
    }
}
