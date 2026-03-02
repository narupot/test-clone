<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\GeneralFunctions; 
use DB;
class OrderShop extends Model
{
    protected $table = 'order_shop';

    protected $fillable = [
        'order_id',
        'shop_id',
        'shop_user_id',
        'user_id',
        'user_name',
        'user_email',
        'ph_number',
        'order_status',
        'payment_slug',
        'shop_json',
        'order_json',
        'shipping_method',
        'total_core_cost',
        'total_final_price',
        'total_credit_amount',
        'shop_formatted_id',
        'commission_rate',
        'commission_fee',
        'total_smm_pay',
        'dc_delivery_starttime',
        'dc_delivery_endtime',
    ];

    public function getShop(){
        return $this->hasOne('App\Shop', 'id', 'shop_id');
    }

    public function getShopDesc(){
        return $this->hasOne('App\ShopDesc', 'shop_id', 'shop_id')->where('lang_id', session('default_lang'))->select('shop_id','shop_name');
    }

    public function getUser(){
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function getOrderDetail(){
        return $this->hasOne('App\OrderDetail', 'order_shop_id', 'id');
    }

    public function getSellerDetail(){
        return $this->hasOne('App\Seller', 'user_id', 'shop_user_id');
    }

    public function getOrderStatus(){
        return $this->hasOne('App\OrderStatusDesc', 'order_status_id', 'order_status')->where('lang_id', session('default_lang'));
    }
    public function order(){
        return $this->belongsTo(Order::class,'order_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }
    
    public function orderDetail()
    {
        return $this->hasMany(OrderDetail::class, 'order_shop_id');
    }

    public static function formattedOrder($shop_id){
    	$date = date('Ymd');
    	$order_id_key = SystemConfig::select('system_val')->where('system_name','SHOP_ORDER_ID')->first();
        $panel_no = \App\Shop::where('id',$shop_id)->value('panel_no');
    	$find = array('[PanelNo]','[YY]', '[MM]', '[DD]',' ',',');
    	$replace = array($panel_no,date('y'),date('m'),date('d'),'','-');
    	$format = str_replace($find, $replace, $order_id_key->system_val);
    	return str_replace('/','-',$format);
    }

    // public static function createShopOrder($shop_id,$main_order,$user,$shop_arr,$order_json=null){
    //     $shop_user_id = $shop_arr['shop_user_id'];
    //     $user_email = $user->email;
    //     $user_name = $user->first_name.' '.$user->last_name;
    //     $ph_number = $user->ph_number;
    //     $shop_obj = new OrderShop;
    //     $shop_obj->order_id = $main_order->id;
    //     $shop_obj->shop_id = $shop_id;
    //     $shop_obj->shop_user_id = $shop_user_id;
    //     $shop_obj->user_id = $main_order->user_id;
    //     $shop_obj->user_name = $user_name;
    //     $shop_obj->user_email = $user_email??'';
    //     $shop_obj->ph_number = $ph_number??'';
    //     $shop_obj->order_status    = 1;
    //     $shop_obj->payment_slug     = $main_order->payment_slug;
    //     $shop_obj->shop_json    = jsonEncode($shop_arr);
    //     $shop_obj->order_json    = $order_json ? $order_json:'';
    //     $shop_obj->shipping_method      = $main_order->shipping_method;
    //     $shop_obj->save();
    //     $shop_order_id = $shop_obj->id;

    //     $formatted_shop_id = Self::formattedOrder($shop_id).$shop_order_id;

    //     $updateformat = Self::where('id',$shop_order_id)->update(['shop_formatted_id'=>$formatted_shop_id]);

    //     /****update entry in order transaction******/
    //     $comment = GeneralFunctions::getOrderText('shop_order_created');
    //     $transaction_arr = ['order_id'=>$main_order->id,'order_shop_id'=>$shop_order_id,'order_detail_id'=>0,'event'=>'order','comment'=>$comment,'updated_by'=>'buyer'];

    //     $update_transaction = \App\OrderTransaction::updateOrdTrans($transaction_arr);
        
    //     return $shop_obj;
    // }


    // [แก้ไข] เพิ่ม Parameter $dc_start, $dc_end และกำหนดค่า Default เป็น null
public static function createShopOrder($shop_id, $main_order, $user, $shop_arr, $order_json=null, $dc_start=null, $dc_end=null){
    $shop_user_id = $shop_arr['shop_user_id'];
    $user_email = $user->email;
    $user_name = $user->first_name.' '.$user->last_name;
    $ph_number = $user->ph_number;
    
    $shop_obj = new OrderShop;
    $shop_obj->order_id = $main_order->id;
    $shop_obj->shop_id = $shop_id;
    $shop_obj->shop_user_id = $shop_user_id;
    $shop_obj->user_id = $main_order->user_id;
    $shop_obj->user_name = $user_name;
    $shop_obj->user_email = $user_email??'';
    $shop_obj->ph_number = $ph_number??'';
    $shop_obj->order_status    = 1;
    $shop_obj->payment_slug     = $main_order->payment_slug;
    $shop_obj->shop_json    = jsonEncode($shop_arr);
    $shop_obj->order_json    = $order_json ? $order_json:'';
    $shop_obj->shipping_method      = $main_order->shipping_method;

    // [เพิ่มใหม่] บันทึกเวลา DC Delivery (Seller Start/End Time)
    $shop_obj->dc_delivery_starttime = $dc_start;
    $shop_obj->dc_delivery_endtime   = $dc_end;

    $shop_obj->save();
    $shop_order_id = $shop_obj->id;

    $formatted_shop_id = Self::formattedOrder($shop_id).$shop_order_id;

    $updateformat = Self::where('id',$shop_order_id)->update(['shop_formatted_id'=>$formatted_shop_id]);

    /****update entry in order transaction******/
    $comment = GeneralFunctions::getOrderText('shop_order_created');
    $transaction_arr = ['order_id'=>$main_order->id,'order_shop_id'=>$shop_order_id,'order_detail_id'=>0,'event'=>'order','comment'=>$comment,'updated_by'=>'buyer'];

    $update_transaction = \App\OrderTransaction::updateOrdTrans($transaction_arr);
    
    return $shop_obj;
}

    public static function updateShopOrderPrice($shop_order_id){

        $core_details = OrderDetail::select(DB::raw('sum(total_price) AS tot_price'))->where('order_shop_id',$shop_order_id)->first();

        $credit_detail = OrderDetail::select(DB::raw('sum(total_price) AS tot_price'))->where(['order_shop_id'=>$shop_order_id,'payment_type'=>'credit'])->first();

        $credit_amount = 0;
        if(!empty($credit_detail)){
            $credit_amount = $credit_detail->tot_price;
        }

        $core_price = $core_details->tot_price;

        $affected = Self::where(['id' => $shop_order_id])->update(['total_core_cost'=>$core_price,'total_final_price' => $core_price,'total_credit_amount'=>$credit_amount]);
  
        return $core_price;
    }

}
