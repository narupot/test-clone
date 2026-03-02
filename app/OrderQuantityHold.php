<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class OrderQuantityHold extends Model {

    protected $table = 'order_quantity_hold';

    public $timestamps = false; 

    public static function deleteHoldQty($order_id){
    	return Self::where('order_id',$order_id)->delete();
    }

    /*****
    ** url for delete hold quantity after few minuts that will set in cron.
    ** http://192.168.1.250:8005/en/checkout/releaseHoldQty
    **/
                    
}
