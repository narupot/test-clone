<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\GeneralFunctions;
use DB;
use App\Order;
use App\OrderShop;
use App\OrderDetail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ReturnStock extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ReturnStock:returnStock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Return Stock';

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
        $this->info("✅ Cron command started");
        $start = Carbon::yesterday()->setTime(13, 1, 0);
        $end   = Carbon::today()->setTime(13, 0, 0);
        
        $cancleOrders = Order::with('getOrderDetail.product')
            ->whereBetween('pickup_time', [$start, $end])
            ->where('order_status', 4)
        ->get();

        $message = "Found {$cancleOrders->count()} canceled orders.";
        $this->info($message);
        Log::info($message);

        foreach ($cancleOrders as $order) {
            foreach ($order->getOrderDetail as $orderDetail) {
                $product = $orderDetail->product;

                $orderDetailJson = json_decode($orderDetail->order_detail_json, true); 
                $stock = is_array($orderDetailJson) ? data_get($orderDetailJson, 'stock') : null;

                if (!is_null($stock) && (int)$stock === 0) {
                    if ($product) {
                        $product->increment('quantity', $orderDetail->quantity);

                        $message = "Product SKU {$product->sku} returned {$orderDetail->quantity} quantity (Order ID {$order->id}).";
                        Log::info($message);
                        $this->info($message);
                    } else {
                        Log::warning("⚠ Product not found for OrderDetail ID {$orderDetail->id} (Order ID {$order->id}).");
                    }
                }
            }
        }
        $this->info("Stock returned successfully.");
        return Command::SUCCESS;
    }

}