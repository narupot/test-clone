<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Cart;
use App\Product;
use App\OrdersTemp;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ClearCartItem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ClearCartItem:clearCartItem';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It will delete all cart product after given time setting';

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
        
        $clear_hr = \App\SystemConfig::getSystemValFromDb('CART_CLEAR_TIME');
        $date = date('Y-m-d H:i:s');
        $new_time = date("Y-m-d H:i:s", strtotime('-'.$clear_hr, strtotime($date)));
        
        if (!empty($clear_hr)) {
            preg_match('/([+-])?(\d+)\s*(minutes|minute|min|hours|hour|h)/i', $clear_hr, $matches);
            if ($matches) {
                $value = (int) $matches[2];
                $unit = strtolower($matches[3]);

                if (in_array($unit, ['minute', 'minutes', 'min'])) {
                    $interval = "$value minute";
                } elseif (in_array($unit, ['hour', 'hours', 'h'])) {
                    $interval = "$value hour";
                } else {
                    $interval = "180 minute"; 
                }
                
                // $new_time = Carbon::now()->modify($clear_hr);
                $cartItem = \DB::table(with(new Cart)->getTable().' as c')
                ->join(with(new Product)->getTable().' as p','c.product_id','=','p.id')
                ->join(with(new OrdersTemp)->getTable().' as ot','c.order_id','=','ot.id')
                // ->where("ot.created_at + INTERVAL $interval <= NOW()")
                ->where('ot.created_at','<=',$new_time)
                ->where('ot.order_status','0')
                ->where('p.stock','0')
                ->select('c.product_id','c.quantity as total_quantity')
                // ->groupBy('c.product_id')
                ->get();

                // Log::info('Found cart items to clear', [
                //     'count' => $cartItem->count(),
                //     'cutoff_time' => $new_time
                // ]);

                foreach($cartItem as $item){
                    \DB::table(with(new Product)->getTable().' as p')
                    ->where('id',$item->product_id)
                    ->increment('quantity',$item->total_quantity);
                }

                // \App\OrdersTemp::whereRaw("smm_orders_temp.created_at + INTERVAL $interval <= NOW()")
                \App\OrdersTemp::where('created_at','<=',$new_time)
                ->where('order_status','0')
                ->delete();
            }
        }
    }
  
} 