<?php   

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\MarketPlace;
use App\Http\Controllers\Checkout\OrderController;
use DB;
Use Lang;
use Illuminate\Http\Request;
use App\Order;
use App\Cart;
use App\OrdersTemp;
use App\PaymentOption;
use App\User;
use Auth;
use Session;
use Config;
use Illuminate\Support\Facades\Redirect;


class PaymentGatewayController extends MarketPlace {

    public function __construct() {

    }

  
    public function ReturnTransaction(request $request){

        file_put_contents(Config::get('constants.public_path')."/kbank_ret.txt",json_encode($request->all()));
        $charge_id = $request->input("id");
        $pay_opt = \App\PaymentOption::where('slug','kbank')->first();
        if(count($pay_opt)){
            if($pay_opt->mode == 2)
                $kbank_details = json_decode($pay_opt->sandbox_detail,true);
            else
                $kbank_details = json_decode($pay_opt->live_detail,true);
        }

        $secret_key = $kbank_details['secret_key'];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,$kbank_details['qr_url']."qr/".$charge_id);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'x-api-key: '.$secret_key.'')
        );

        $server_output = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($server_output);
        file_put_contents(Config::get('constants.public_path')."/kbank_resp.txt",json_encode($server_output));
        $current_date = date('Y-m-d H:i:s');
        /*$response = json_decode(json_encode(['order_id'=>'order_test_81d4ce8bb0a38147adb2e693c00cf426f9']));*/
        
        
        if(isset($response->order_id)){
            $temp_ord_info = OrdersTemp::getTempOrderInfo('',$response->order_id);
            
            if(count($temp_ord_info)){
                $save_ord_cart_wise = OrderController::saveOrderCartWise($temp_ord_info->id,$temp_ord_info);
                
                if($temp_ord_info->checkout_type == 'buy-now-end-shopping' || $temp_ord_info->checkout_type == 'end-shopping'){

                    $main_order = \App\Order::where('temp_formatted_id',$temp_ord_info->formatted_order_id)->whereNull('end_shopping_date')->first();
                    if(count($main_order)){
                        $update_main_ord = OrderController::saveOrderEndShopping($temp_ord_info->id,$main_order,$temp_ord_info);
                        if($main_order->total_shipping_cost > 0){
                            /*****updating paymet log for shipping*****/
                            $payment_arr = $request->all();
                            $ref_ord = $payment_arr['reference_order']??'';
                            $arr = ['order_id'=>$main_order->id,'payment_slug'=>'kbank','reference_order'=>$ref_ord,'items'=>'shipping','response'=>json_encode($request->all()),'created_at'=>$current_date];
                            $update_pay_resp = \App\OrderPayment::insert($arr);
                        }
                    }

                }

            }
        }
        
    }

    public function Check($order_id){

        $order = Order::select("*")->where("kbank_qrcode_id","=",$order_id)->first();
        if(count($order)){
            if(strtotime($order->end_shopping_date) > 0){
                $url = action('Checkout\OrderController@thanks',$order->formatted_id);
            }else{
                $url = action('Checkout\CartController@alreadyPaid');
            }
            return ['status'=>'success','url'=>$url];
        }else{
            return ['status'=>'pending'];
        }
    }

    public function payplusReturnTransaction(Request $request){

        $message = $request->input("PMGWRESP2");

        $decrypted_message = exec("java -jar ".storage_path()."/aes.jar decrypt \"$message\"");

        file_put_contents(Config::get('constants.public_path')."/payplus_responses.txt",$decrypted_message);

        $trans_code = substr($decrypted_message,0,4);
        $merchant = substr($decrypted_message,4,5);
        $currency = substr($decrypted_message,29,3);
        $invoice = substr($decrypted_message,32,12);
        $date = strtotime(substr($decrypted_message,44,8)." ".substr($decrypted_message,52,6));
        $mobile = substr($decrypted_message,58,10);
        $amount = intval(substr($decrypted_message,85,12))/100;
        $response_code = substr($decrypted_message,97,2);
        $reference1 = substr($decrypted_message,108,18);
        $reference2 = substr($decrypted_message,168,18);

        $response = ["trans_code"=>$trans_code,"merchant"=>$merchant,"currency"=>$currency,
                                          "invoice"=>$invoice,"date"=>$date,"time"=>date("Y-m-d H:i:s",$date),
                                          "mobile"=>$mobile,"amount"=>$amount,"response_code"=>$response_code,
                                          "reference1"=>$reference1,"reference2"=>$reference2];
                                          

        /*$invoice = '002556056518';
        $response_code = '00';
        $response = ['invoice'=>$invoice];*/

        if($invoice && $response_code=='00'){
            $temp_ord_info = OrdersTemp::getTempOrderInfo('',$invoice);
            $current_date = date('Y-m-d H:i:s');
            if(count($temp_ord_info)){
                
                $save_ord_cart_wise = OrderController::saveOrderCartWise($temp_ord_info->id,$temp_ord_info,$response);
                
                if($temp_ord_info->checkout_type == 'buy-now-end-shopping' || $temp_ord_info->checkout_type == 'end-shopping'){

                    $main_order = \App\Order::where('temp_formatted_id',$temp_ord_info->formatted_order_id)->whereNull('end_shopping_date')->first();
                    if(count($main_order)){
                        $update_main_ord = OrderController::saveOrderEndShopping($temp_ord_info->id,$main_order,$temp_ord_info);
                        if($main_order->total_shipping_cost > 0){
                            /*****updating paymet log for shipping*****/
                            $payment_arr = $request->all();
                            $ref_ord = $payment_arr['reference_order']??'';
                            $arr = ['order_id'=>$main_order->id,'payment_slug'=>'payplus','reference_order'=>$invoice,'items'=>'shipping','response'=>json_encode($response),'created_at'=>$current_date];
                            $update_pay_resp = \App\OrderPayment::insert($arr);
                        }
                    }

                }
            }
        }
    }

    public function payplusCheck($inv_id){
        $check_inv = \App\OrderPayment::where('reference_order',$inv_id)->first();
        if(count($check_inv)){
            $order = Order::where("id","=",$check_inv->order_id)->first();
            if(strtotime($order->end_shopping_date)>0){
                $url = action('Checkout\OrderController@thanks',$order->formatted_id);
            }else{
                $url = action('Checkout\CartController@alreadyPaid');
            }
            return ['status'=>'success','url'=>$url];
        }else{
            return ['status'=>'pending'];
        }
    }

    public function oddRegisterTracking(Request $request){
        
        file_put_contents(Config::get('constants.public_path')."/odd_register.txt",json_encode($request->all(),JSON_UNESCAPED_UNICODE));
        
        if(!isset($request->returnStatus)){
            
            exit();
        }

        /*$response = '{"returnStatus":"322110350545883                                   4AF5F60748A81F41BF4C25C04AB9B9EB9F42D135720ACCE13E6F6FD54217B0FF                                    0481879086           00202106170203090K0025Your Online Direct Debit Registration is successful."}';*/

        $response = $request->returnStatus;
        $external_reference = substr($response,0,20);
        $payer_short_name  = substr($response,20,30);
        $espa_id = substr($response,50,100);
        $account_no = substr($response,150,20);
        
        $user_email = substr($response,170,1);
        $mobile = substr($response,171,1);
        $matching_flag = substr($response,172,1);
        $timestamp = substr($response,173,14);
        $return_status = substr($response,187,1);
        $return_code = substr($response,188,5);
        $return_msg = substr($response,193);

        $user_info = \App\UserInfo::where('reference_no',$external_reference)->first();


        if($user_info && is_object($user_info)){
            
            $info_json = json_decode($user_info->info_json,true);

            if(count($info_json)){
                $info_json['tracking_response'] = $response;
                $new_json = json_encode($info_json,JSON_UNESCAPED_UNICODE);
            }else{
                $new_json = $response;
            }
            if($return_status == '0' || $return_status == 0){
                $user_info->status = '1';
                $user_info->espa_id = $espa_id;
            }else{
                $user_info->status = '0';
                $user_info->espa_id = '';
            }
            
            $user_info->info_json = $new_json;
            $user_info->save();
           
        }
        
    }

}