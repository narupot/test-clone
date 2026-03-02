<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use DB;
use Illuminate\Support\Facades\Config;
use Exception;
use Illuminate\Support\Facades\Log;

class SendOrderLogistic implements ShouldQueue
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
        $order_data = \App\Order::whereIn('shipping_method',[1,3])->where('logistic_status','0')->where('end_shopping_date','!=',null)->limit(10)->get();
        if(count($order_data)){
            foreach ($order_data as $ordkey => $ordvalue) {
                try {
                    $main_order = $ordvalue;
                    $end_shopping_date = $ordvalue->end_shopping_date;
                    $json_arr = json_decode($main_order->order_json,true);
                    $key_arr = ['first_name','last_name','provice','district','address','road','zip_code','ph_number','company_name','branch','tax_id','company_address','name','location','contact','estimate'];
                    $arr_json = [];
                    if($main_order->shipping_method == 3){
                        foreach ($key_arr as $smkey => $smvalue) {
                            $arr_json[$smvalue] = $json_arr['shipping_address'][$smvalue]??'';
                        }
                        $arr_json['name'] = $json_arr['shipping_address']['title']??'';
                    }else{
                        foreach ($key_arr as $smkey => $smvalue) {
                            $arr_json[$smvalue] = $json_arr[$smvalue]??'';
                        }
                    }
                    if(isset($json_arr['total_logistic_cost']) && $json_arr['total_logistic_cost']>0){
                        $total_logistic_cost = $json_arr['total_logistic_cost'];
                    }else{
                        $total_logistic_cost = 0;
                    }

                    $main_order_json = [$arr_json];

                    $seller_send_prodcut_time = null;

                    $del_type = \App\DeliveryTime::getDeliverYType($main_order->shipping_method);
                    $delivery_time = \App\DeliveryTime::getDeliveryTime($del_type);
                    $prepare_time_before = $delivery_time->prepare_time_before;
                
                    if($prepare_time_before && strtotime($main_order->pickup_time)){
                        $seller_send_prodcut_time = date('Y-m-d H:i:s',strtotime('-'.$prepare_time_before.' hours', strtotime($main_order->pickup_time)));
                    }

                    $total_weight = 0;
                    $order_shop = \App\OrderShop::where('order_id',$main_order->id)->get()->toArray();
                    if(count($order_shop)){
                        foreach ($order_shop as $key => $value) {

                            $order_shop[$key]['shop_formatter_id'] = $value['shop_formatted_id'];
                            $order_shop[$key]['seller_send_prodcut_time'] = $seller_send_prodcut_time;
                            $order_shop[$key]['end_shopping_date'] = $end_shopping_date;

                            unset($order_shop[$key]['shop_formatted_id'],$order_shop[$key]['shipping_method'],$order_shop[$key]['total_discount'],$order_shop[$key]['total_final_weight'],$order_shop[$key]['seller_status'],$order_shop[$key]['shop_json'],$order_shop[$key]['order_json'],$order_shop[$key]['user_email']);

                            $order_detail = \App\OrderDetail::where(['order_shop_id'=>$value['id']])->get()->toArray();

                            $line= 0;
                            foreach ($order_detail as $dkey => $dvalue) {
                                
                                $detail_arr = json_decode($dvalue['order_detail_json'],true);
                                $total_weight = $total_weight + ($dvalue['total_weight']*$dvalue['quantity']);
                                $detail_arr['name'] = $detail_arr['name'][0]??'';
                                $detail_arr['package'] = $detail_arr['package'][0]??'';
                                $detail_arr['shop_name'] = $detail_arr['shop_name'][0]??'';
                                $detail_arr['pickup_time'] = $main_order->pickup_time;
                                $detail_arr['payment_method'] = $detail_arr['payment_method'][0]??'';
                                $detail_arr['remark'] = (float)$dvalue['total_weight'].' '.$dvalue['base_unit'].'/'.$dvalue['package_name'];
                                $order_detail[$dkey]['base_unit_type'] = '';
                                $order_detail[$dkey]['base_unit'] = '';
                                $order_detail[$dkey]['payment_date'] = $end_shopping_date;
                                $order_detail[$dkey]['payment_status'] = 1;
                                $order_detail[$dkey]['item_detail_json'] = [$detail_arr];

                                unset($order_detail[$dkey]['order_shop_id'],$order_detail[$dkey]['order_detail_json'],$order_detail[$dkey]['user_id'],$order_detail[$dkey]['shop_id'],$order_detail[$dkey]['order_id'],$order_detail[$dkey]['status'],$order_detail[$dkey]['credit_paid_status'],$order_detail[$dkey]['total_weight']);

                                $arr = [];
                                $arr = ['line_no'=>++$line]+$order_detail[$dkey];
                                $order_detail[$dkey] = $arr;
                                
                            }
                            
                            $order_shop[$key]['order_detail'] = $order_detail;
                            $order_shop[$key]['order_json'] = $main_order_json;
                        }
                    }

                    $shop_order_arr = $order_shop;

                    $main_order->id = (string)$main_order->id;
                    $main_order->user_id = (string)$main_order->user_id;
                    $main_order->shipping_method = (string)$main_order->shipping_method;
                    $main_order->total_core_cost = (string)$main_order->total_core_cost;
                    $main_order->total_logistic_cost = (string)$total_logistic_cost;
                    $main_order->total_final_price = (string)$main_order->total_final_price;
                    $main_order->total_weight = (string)$total_weight;
                    $main_order->order_status = (string)$main_order->order_status;

                    $main_order->api_date = date('Y-m-d H:i:s');
                    $main_order->order_shop_json = $shop_order_arr;
                    $making_json = $main_order->toArray();
                    unset($making_json['total_discount'],$making_json['logistic_status'],$making_json['logistic_date'],$making_json['logistic_msg'],$making_json['order_json'],$making_json['api_json'],$making_json['total_shipping_cost']);
                    $full_order_json = json_encode([$making_json]);

                    $resp = $this->sendOrderJson($full_order_json);

                    if(isset($resp['ret']) && $resp['ret'] == '0'){
                        $update_statue = '1';
                    }else{
                        $update_statue = '2';
                    }

                    $msg = is_array($resp) || is_object($resp)?json_encode($resp):$resp;
                    $update_log = \App\LogisticLog::insertLog($main_order->id,$msg,$full_order_json);
                    $update_ord = \App\Order::where('id',$main_order->id)->update(['logistic_status'=>$update_statue]);
                } catch (\Throwable $e) {
                    Log::error("Send order JSON failed for order #{$ordvalue->id}: " . $e->getMessage());
                    continue;
                }
            }
        }
    }

    private function sendOrderJson($order_json){

        $server_url = Config::get('constants.send_order_json_url');
        $post_data = $order_json;
        try{
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $server_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 1,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $post_data,
                CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/json"
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $return  = [];
            if ($err) {
                $returnResponse = ['status'=>'failed','message'=>$err];
            } else {
                if($response)
                    $returnResponse = json_decode($response,true);
                else
                    $returnResponse = ['status'=>'failed','message'=>$err];
            }
        }
        catch(Exception $e) {
            Log::error("Error sending order JSON in sendOrderJson() : " . $e->getMessage());
            $returnResponse = ['status'=>'failed','message'=>$e->getMessage()];
        }
        return $returnResponse;
    }
}
