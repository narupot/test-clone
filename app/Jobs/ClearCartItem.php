<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\DB;
use App\Cart;
use App\Product;
use App\OrdersTemp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ClearCartItem implements ShouldQueue
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
        $clear_hr = \App\SystemConfig::getSystemValFromDb('CART_CLEAR_TIME');
        $date = date('Y-m-d H:i:s');
        $new_time = date("Y-m-d H:i:s", strtotime('-'.$clear_hr, strtotime($date)));
        if (!empty($clear_hr)) {
            preg_match('/([+-])?(\d+)\s*(minutes|minute|min|hours|hour|h)/i', $clear_hr, $matches);
            if ($matches) {
                try {
                    $cartItem = DB::table(with(new Cart)->getTable().' as c')
                        ->join(with(new Product)->getTable().' as p','c.product_id','=','p.id')
                        ->join(with(new OrdersTemp)->getTable().' as ot','c.order_id','=','ot.id')
                        ->where('ot.created_at','<=',$new_time)
                        ->where('ot.order_status','0')
                        ->select('c.product_id','c.quantity as total_quantity')
                        ->get();
                    foreach($cartItem as $item){
                        DB::table(with(new Product)->getTable().' as p')
                        ->where('id',$item->product_id)
                        ->increment('quantity',$item->total_quantity);
                    }

                    \App\OrdersTemp::where('created_at','<=',$new_time)
                    ->where('order_status','0')
                    ->delete();

                } catch (\Exception $e) {
                    echo "Clear expired carts failed: " . $e->getMessage() . "\n";
                    Log::error("Clear expired carts failed: " . $e->getMessage());
                }
            }
        }
    }
}
