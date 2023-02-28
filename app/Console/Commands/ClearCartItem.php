<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

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
        $clear_hr = getConfigValue('CART_CLEAR_TIME');

        $date = date('Y-m-d H:i:s');
        //$new_time = date("Y-m-d H:i:s", strtotime('-3 hours', strtotime($date)));
        $new_time = date("Y-m-d H:i:s", strtotime($clear_hr, strtotime($date)));

        if($new_time){
            \App\OrdersTemp::where('created_at','<=',$new_time)
            ->where('order_status','0')
            ->delete();
        }
    }
  
} 