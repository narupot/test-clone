<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\GeneralFunctions;
use DB;
use App\Order;  
use App\OrderShop;
use App\OrderDetail;

use Carbon\Carbon;

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
        echo "✅ Cron command started\n";
        // complete online order

        $before_date = date("Y-m-d", strtotime("-2 day"));
        $order_data = \App\Order::where(['payment_status'=>1])->whereIn('order_status',[2])->where(DB::raw('date(pickup_time)'),'>=',$before_date)->get();
        $curtime = date('Y-m-d H:i:s');

        $shopping_date = $order_data->first()->end_shopping_date;
        $pickup_day = date('d', strtotime($shopping_date));

        // สร้าง pattern สำหรับการค้นหา: SMM + ปี(2หลัก) + เดือน(2หลัก)
        $shopping_ym = date('ym', strtotime($shopping_date));
        $search_pattern = 'SMM' . $shopping_ym;
        
        if($shopping_date == '01') {
            // ถ้าเป็นวันที่ 1 ให้ running = 1
            $newRunning = 1;
        } else {
            
            
            // หา max shipping_rept_no ที่ตรงกับ pattern
            $maxShippingNo = \App\Order::where(DB::raw('substring(shipping_rept_no, 1, 7)'), '=', $search_pattern)
                                    ->max('shipping_rept_no');
            
            if($maxShippingNo === null || $maxShippingNo == '') {
                $newRunning = 1;
            } else {
                
                // ดึงเลข running ออกมาจาก 4 หลักสุดท้าย
                $newRunning = intval(substr($maxShippingNo, -5)) + 1;
            }
        }


        echo "จำนวน order: " . count($order_data) . "\n";
        \Log::info("🔍 Found orders: " . count($order_data));
        if(count($order_data)) { 
            foreach ($order_data as $key => $value) {
                $shopping_date = $value->end_shopping_date;
                
                if(strtotime($curtime) >= strtotime($shopping_date)){
                                        
                    /****update order to complete*****/
                    if(($value->shipping_method == 3) || ($value->shipping_method == 1 && $value->transaction_fee > 0)){
                        $shippingReptNo=$search_pattern.str_pad($newRunning, 5, '0', STR_PAD_LEFT) ;
                        $complete_ord = \App\Order::where('id',$value->id)->whereIn('order_status',[2])
                        ->update(['order_status'=>3,'shipping_rept_no' => $shippingReptNo]);
                        $newRunning ++;
                    } else {
                        $complete_ord = \App\Order::where('id',$value->id)->whereIn('order_status',[2])
                        ->update(['order_status'=>3]);
                    }
                    // $complete_ord = \App\Order::where('id',$value->id)->whereIn('order_status',[2])
                    // ->update(['order_status'=>3,'shipping_rept_no' => $shippingReptNo]);

                    $update_details = \App\OrderShop::where(['order_id'=>$value->id])->whereIn('order_status',[2])->update(['order_status'=>3]);

                    $update_details = \App\OrderDetail::where(['order_id'=>$value->id])->whereIn('status',[2])->update(['status'=>3]);

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

            $today = Carbon::today()->toDateString();

            $orderShops = OrderShop::with(['order', 'shop'])
                ->where('order_status', 3)
                ->whereHas('order', function ($q) use ($today) {
                    $q->whereDate('pickup_time', $today);
                })
                ->whereHas('shop')
                ->get();

          

            foreach ($orderShops as $os) {
                if ($os->shop && $os->total_final_price) {
                    $rate = $os->shop->commission_rate ?? 0;
                    $total = $os->total_final_price;
                    $fee = $total * $rate / 100;
                    $pay = $total - $fee;

                    if($os->shop->comm_effective_date <= $today){
                        $os->commission_rate = $rate;
                        $os->commission_fee = $fee;
                        $os->total_smm_pay = $pay;
                    }else{
                        $os->total_smm_pay = $total;
                    }
                    $os->save();
                }
            }

            echo "✅ Updated commission on {$orderShops->count()} order_shop records\n";
    }
}
