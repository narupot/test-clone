<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class OrderSeller extends Model
{
    protected $table = 'order_seller';

    public function getOrderDetail(){
        return $this->hasOne('App\OrderDetail', 'order_id', 'id');  
    }

    public function getOrderShop(){
        return $this->hasMany('App\OrderShop', 'order_id', 'id');  
    }

    public static function formattedOrder(){
    	$date = date('Ymd');
    	$order_id_key = SystemConfig::select('system_val')->where('system_name','ORDER_ID')->first();
    	$find = array('[YYYY]', '[MM]', '[DD]');
    	$replace = array(date('Y'),date('m'),date('d'));
    	$format = str_replace($find, $replace, $order_id_key->system_val);
    	return $format;
    }
}
