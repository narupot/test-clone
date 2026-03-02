<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;

class OrderStatus extends Model
{   
    protected $table = 'order_status';

    public function orderStatusDesc() {       
        return $this->hasOne('App\OrderStatusDesc', 'order_status_id', 'id')->where('lang_id', session('default_lang'));
    }    

    public static function orderStatus($status_id) {
        return self::select('id')->where('id', $status_id)->with('orderStatusDesc')->first();
    }  

    public static function orderSpecificStatus($status_id,$status_ids){
    	return self::select('id')->where('id', $status_id)->whereIn('id',$status_ids)->with('orderStatusDesc')->first();

    } 
    public function orderStatusName() {
        return $this->hasOne('App\OrderStatusDesc', 'order_status_id', 'id')->where('lang_id', session('default_lang'));
    }

    public static function getOrderStatusAll() {

        return self::select('id')->with('orderStatusName')->get();
    }   
}
