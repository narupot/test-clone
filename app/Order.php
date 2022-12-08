<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\GeneralFunctions; 
use DB;
class Order extends Model
{
    protected $table = 'order';

    public function getOrderDetail(){
        return $this->hasMany('App\OrderDetail', 'order_id', 'id');  
    }

    public function getAllProductsOrder(){
        return $this->hasMany('App\OrderDetail', 'order_id', 'id');  
    }

    public function getOrderShop(){
        return $this->hasMany('App\OrderShop', 'order_id', 'id');  
    }

    public function getUser(){
        return $this->hasOne('App\User', 'id', 'user_id');  
    }

    public function getOrderStatus(){
        return $this->hasOne('App\OrderStatusDesc', 'order_status_id', 'order_status')->where('lang_id',session('default_lang'));
    }

    public static function formattedOrder(){
    	$date = date('Ymd');
    	$order_id_key = SystemConfig::select('system_val')->where('system_name','MAIN_ORDER_ID')->first();
    	$find = array('[YY]', '[MM]', '[DD]');
    	$replace = array(date('y'),date('m'),date('d'));
    	$format = str_replace($find, $replace, $order_id_key->system_val);
    	return $format;
    }

    public static function createMainOrder($orderInfo,$user){

        $user_email = $user->email;
        $user_name = $user->first_name.' '.$user->last_name;
        $ph_number = $user->ph_number;
        $formattedOrder = Self::formattedOrder();

        $current_date = currentDateTime();
        /***inserting order data from temp order table to original order table********/
        $orderFinalPrice = $orderInfo->total_shipping_cost + $orderInfo->total_final_price;

        /*****entry into main order table********/
        $Orders = new Order;
        $Orders->user_id              = $orderInfo->user_id;
        $Orders->temp_formatted_id    = $orderInfo->formatted_order_id;
        $Orders->ip_address           = userIpAddress();
        $Orders->user_email           = $user_email??'';
        $Orders->user_name            = $user_name;
        $Orders->ph_number            = $ph_number??'';
        $Orders->order_status         = 1;
        $Orders->payment_slug         = $orderInfo->payment_slug;
        $Orders->shipping_method      = $orderInfo->shipping_method;
        $Orders->pickup_time          = $orderInfo->pickup_time;
        $Orders->user_phone_no        = $orderInfo->user_phone_no;
        $Orders->total_shipping_cost  = $orderInfo->total_shipping_cost;
        $Orders->kbank_qrcode_id      = $orderInfo->kbank_qrcode_id;
        
        $Orders->order_json           = isset($orderInfo->order_json)?$orderInfo->order_json:'';
        
        $Orders->save();
        $main_order_id = $Orders->id;

        $formattedOrderId = $formattedOrder.$main_order_id;

        $orderUpdate = Order::where(['id' => $main_order_id])->update(['formatted_id'=>$formattedOrderId]);

        /****update entry in order transaction******/
        $comment = GeneralFunctions::getOrderText('order_created');
        $transaction_arr = ['order_id'=>$main_order_id,'order_shop_id'=>0,'order_detail_id'=>0,'event'=>'order','comment'=>$comment,'updated_by'=>'buyer'];

        $update_transaction = \App\OrderTransaction::updateOrdTrans($transaction_arr);

        return $Orders;
    }

    public static function updateMainOrderPrice($orderId){

        $order_info = Self::where('id',$orderId)->first();

        $core_details = OrderDetail::select(DB::raw('sum(total_price) AS tot_price'))->where('order_id',$orderId)->first();

        $core_price = $core_details->tot_price;

        $shipping_price = $order_info->total_shipping_cost;

        $total_final_price = $shipping_price + $core_details->tot_price;
    
        $affected = Self::where(['id' => $orderId])->update(['total_core_cost'=>$core_price,'total_final_price' => $total_final_price]);
  
        return $total_final_price;
    }

    /*****use after payment done to update payment status*******/
    public static function updateOrderAfterPayment($orderInfo, $updated_by='buyer'){
        /**updating main order payment status***/
        $current_date = date('Y-m-d H:i:s');
        $orderInfo->order_status = 2;
        $orderInfo->payment_status = 1;
        $orderInfo->end_shopping_date = $current_date;
        $orderInfo->save();

        $shop_order = OrderShop::where('order_id',$orderInfo->id)->select('id','user_id','shop_id')->get();
        foreach ($shop_order as $key => $value) {
            foreach ($shop_order as $key => $value) {
                /***checking credit****/
                $shop_credit_period = \App\Credits::where(['user_id'=>$value->user_id,'shop_id'=>$value->shop_id,'seller_approval'=>'Approved'])->value('payment_period');
                if($shop_credit_period){
                    $credit_due_date = addDaysTodate($current_date,$shop_credit_period);
                }else{
                    $credit_due_date = null;
                }
                
                $shop_update = OrderShop::where('id',$value->id)->update(['end_shopping_date'=>$current_date,'credit_due_date'=>$credit_due_date,'updated_at'=>$current_date,'order_status'=>2,'payment_status'=>1]);

                if($orderInfo->payment_type == 'credit'){
                    $detail = OrderDetail::where('order_shop_id',$value->id)->select('total_price')->get();
                    foreach ($detail as $dkey => $dvalue) {
                        $shop_credit_info = \App\Credits::where(['user_id'=>$value->user_id,'shop_id'=>$value->shop_id,'seller_approval'=>'Approved'])->first();

                        if(count($shop_credit_info)){
                            $used_amount = $shop_credit_info->used_amount + $dvalue->total_price;
                            $remain_amt = $shop_credit_info->credited_amount - $used_amount;

                            $shop_credit_info->used_amount = $used_amount;
                            $shop_credit_info->remaining_amount = $remain_amt;
                            if($shop_credit_info->amount_paid=='1'){
                                $shop_credit_info->amount_paid = '0';
                            }
                            $shop_credit_info->save();
                        }
                    }
                }
            }
        }

        OrderDetail::where('order_id',$orderInfo->id)->update(['payment_status'=>1,'payment_date'=>$current_date,'status'=>2]);

         /****update entry in order transaction******/
        //$comment = 'Order end shopping with payment done';
        $comment = GeneralFunctions::getOrderText('order_end_shopping');

        $transaction_arr = ['order_id'=>$orderInfo->id,'order_shop_id'=>0,'order_detail_id'=>0,'event'=>'order','comment'=>$comment,'updated_by'=>$updated_by];

        $update_transaction = \App\OrderTransaction::updateOrdTrans($transaction_arr);

        return true;
    }

    /**currently this function only for cancel and received center status only*******/
    public static function updateOrdStatus($order_id){
        $order_details = OrderDetail::where('order_id',$order_id)->select('id','order_shop_id','status')->get();
        $shop_detail_status = $shop_id_arr = [];
        foreach ($order_details as $key => $value) {
            $shop_detail_status[$value->order_shop_id][] = $value->status;
            if(!in_array($value->order_shop_id, $shop_id_arr)){
                $shop_id_arr[] = $value->order_shop_id;
            }
        }
        //dd($shop_id_arr,$shop_detail_status);
        foreach ($shop_id_arr as $value) {
            $shop_status_arr = $shop_detail_status[$value];

            if(in_array(2, $shop_status_arr) || count(array_unique($shop_status_arr)) === 1 && end($shop_status_arr) == '3'){
                /****if any shop item pending or complete then not change status******/
            }elseif (count(array_unique($shop_status_arr)) === 1 && end($shop_status_arr) == '4') {

               /**means if all cart items cancel then shop order will be cancelled**/
               $update_shop = OrderShop::where('id',$value)->update(['order_status'=>4]);

            }elseif (count(array_unique($shop_status_arr)) === 1 && end($shop_status_arr) == '5') {

               /**means if all cart items center received then shop order will be Ready to pickup**/
               $update_shop = OrderShop::where('id',$value)->update(['order_status'=>6]);

            }else{

                /**if some center received and some cancel then shop order will be Ready to pickup**/
                $update_shop = OrderShop::where('id',$value)->update(['order_status'=>6]);

            }
        }
    }

    public static function updateMainOrdStatus($order_id){
        $update_shop = OrderShop::where('order_id',$order_id)->select('id','order_status')->get();
        $shop_id_arr = [];
        $not_update = 0;
        if(count($update_shop)){
            foreach ($update_shop as $key => $value) {
                if($value->order_status == '3' || $value->order_status == '4'){
                    $shop_id_arr[] = $value->order_status;
                }else{
                    $not_update = 1;/***means any shop order still processing*****/
                    break;
                }
            }
            /***3 or 4 means either shop order complete or cancel******/
            if(count($shop_id_arr) && $not_update == 0){
                if(count(array_unique($shop_id_arr)) == 1 && end($shop_id_arr) == '4'){
                    /***means all shop cancel***/
                    $update_shop = Self::where('id',$order_id)->update(['order_status'=>4]);
                }else{
                    $update_shop = Self::where('id',$order_id)->update(['order_status'=>3]);
                }
            }
        }
    }

    public static function createOrderJson($order_id){
        $main_order = Order::where('id',$order_id)->first();

        $order_shop = OrderShop::where('order_id',$main_order->id)->get()->toArray();
        if(count($order_shop)){
            foreach ($order_shop as $key => $value) {

                unset($order_shop[$key]['shipping_method'],$order_shop[$key]['total_discount'],$order_shop[$key]['total_final_weight'],$order_shop[$key]['seller_status'],$order_shop[$key]['shop_json'],$order_shop[$key]['order_json']);

                $order_detail = OrderDetail::where(['order_shop_id'=>$value['id']])->get()->toArray();

                $line= 0;
                foreach ($order_detail as $dkey => $dvalue) {
                    
                    $detail_arr = json_decode($dvalue['order_detail_json'],true);

                    $detail_arr['name'] = $detail_arr['name'][0]??'';
                    $detail_arr['package'] = $detail_arr['package'][0]??'';
                    $detail_arr['shop_name'] = $detail_arr['shop_name'][0]??'';
                    $detail_arr['payment_method'] = $detail_arr['payment_method'][0]??'';

                    $order_detail[$dkey]['item_detail_json'] = $detail_arr;

                    unset($order_detail[$dkey]['order_detail_json'],$order_detail[$dkey]['user_id'],$order_detail[$dkey]['shop_id'],$order_detail[$dkey]['order_id'],$order_detail[$dkey]['created_at'],$order_detail[$dkey]['updated_at']);

                    $arr = [];
                    $arr = ['line_no'=>++$line]+$order_detail[$dkey];
                    $order_detail[$dkey] = $arr;
                    
                }
                
                $order_shop[$key]['order_detail'] = $order_detail;
            }
        }

        if($main_order->shipping_method == 2){
            $main_ord_json = json_decode($main_order->order_json,true);
            $ord_json_arr = [];
            if(count($main_ord_json)){
                foreach ($main_ord_json as $key => $value) {
                    $value['shop_name'] = $value['shop_name'][0]??'';
                    $ord_json_arr[] = $value;
                }
            }
            $main_order->order_json = $ord_json_arr;
        }else{
            $json_arr = json_decode($main_order->order_json,true);
            $key_arr = ['first_name','last_name','provice','district','address','road','zip_code','ph_number','company_name','branch','tax_id','company_address','name','location','contact','estimate'];
            $arr_json = [];
            if($main_order->shipping_method == 3){
                foreach ($key_arr as $key => $value) {
                    $arr_json[$value] = $json_arr['shipping_address'][$value]??'';
                }
            }else{
                foreach ($key_arr as $key => $value) {
                    $arr_json[$value] = $json_arr[$value]??'';
                }
            }
            
            $main_order->order_json = $arr_json;
        }
        
        $shop_order_arr = $order_shop;

        $main_order->api_date = date('Y-m-d H:i:s');
        $main_order->order_shop_json = $shop_order_arr;
        $making_json = $main_order->toArray();
        unset($making_json['total_discount']);
        $full_order_json = json_encode($making_json);
        return $full_order_json;
    }
    public function getCurrency(){
        return $this->hasOne('App\Currency','id','currency_id')->select('id','code', 'symbol', 'name');
    }
}
