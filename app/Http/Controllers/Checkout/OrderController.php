<?php  

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use DB;
Use Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Order;
use App\OrderShop;
use App\OrderDetail;
use App\Cart;
use App\OrdersTemp;
use App\ShippingAddress;
use App\Helpers\GeneralFunctions; 
use App\Helpers\CustomHelpers;
use App\Helpers\EmailHelpers;
use App\User;
use Auth;
use Session;
use Config;
use File;
use Exception;


class OrderController extends MarketPlace {

    public function __construct() {

        //$this->middleware('authenticate'); 

    }

    public static function saveFinalOrder($orderId){

        $orderInfo = OrdersTemp::getTempOrderInfo($orderId);

        $user = \App\User::where('id',$orderInfo->user_id)->first();
        $current_date = currentDateTime();

        /*****getting cart info for selected product*********/
        $cartInfo = Cart::where(['order_id'=>$orderInfo->id])->with(['getPrd','getCat'])->get();

        $shop_id_arr = $shop_detail_arr = [];
        $shop_cart_detail = [];
        if(count($cartInfo)){
            foreach ($cartInfo as $cvalue) {

                if(!in_array($cvalue->shop_id,$shop_id_arr)){
                    $shop_detail = \App\Shop::where('id',$cvalue->shop_id)->with(['allDesc','shopUser'])->first();
                    $shop_id_arr[] = $cvalue->shop_id;
                    $shop_detail_arr[$cvalue->shop_id] = self::getShopArr($shop_detail);
                }
                $shop_cart_detail[$cvalue->shop_id][] = $cvalue;
                
            }
        }

        
        if($orderInfo->shipping_method == '3') {

            $shipAddInfo = ShippingAddress::find($orderInfo->shipping_address_id);
            $shipAddArr = self::getAddressArr($shipAddInfo);
            if($orderInfo->shipping_address_id != $orderInfo->billing_address_id) {
                $billAddInfo = ShippingAddress::find($orderInfo->billing_address_id);
                $billAddArr = self::getAddressArr($billAddInfo);
            }else{
                $billAddInfo = $shipAddInfo;
                $billAddArr = $shipAddArr;
            }

            $order_json = jsonEncode(['shipping_address'=>$shipAddArr,'billing_address'=>$billAddArr,'total_logistic_cost'=>$orderInfo->total_logistic_cost]);

        }elseif($orderInfo->shipping_method == '1'){
            $order_json = \App\SystemConfig::where('system_name','PICKUP_CENTER')->value('system_val');

        }elseif($orderInfo->shipping_method == '2'){
            if(count($shop_detail_arr)){
                $order_json = jsonEncode($shop_detail_arr);
            }
        }

        $orderInfo->order_json = $order_json;

        $main_order = Order::createMainOrder($orderInfo,$user);

        $main_order_id = $main_order->id;


        $order_detail_id_arr = [];
        if(count($shop_id_arr)){
            /***entry into order shop if data exist then update otherwise create*********/
            foreach ($shop_id_arr as $skey => $svalue) {

                if($orderInfo->shipping_method == '2' && count($shop_detail_arr) && isset($shop_detail_arr[$svalue])){
                    $order_json = jsonEncode($shop_detail_arr[$svalue]);
                }
                
                $shop_order = OrderShop::createShopOrder($svalue,$main_order,$user,$shop_detail_arr[$svalue],$order_json);
                $shop_order_id = $shop_order->id;

                /***entry into order details*******/
                if(isset($shop_cart_detail[$svalue]) && count($shop_cart_detail[$svalue])){
                    $shop_total_price = $total_credit_amount = 0;
                    $shop_data = [];
                    foreach ($shop_cart_detail[$svalue] as $key => $detail) {
                        if(isset($shop_detail_arr[$svalue]) && count($shop_detail_arr[$svalue])){
                            $shop_arr = $shop_detail_arr[$svalue];
                            $shop_data = ['shop_url'=>$shop_arr['shop_url'],'logo'=>$shop_arr['logo'],'shop_name'=>$shop_arr['shop_name'],'panel_id'=>$shop_arr['panel_no']];
                        }
                        /****creating order details*******/
                        $create_order_details = OrderDetail::createOrderDetail($main_order_id,$shop_order_id,$orderInfo,$detail,$shop_data);
                        $order_detail_id_arr[] = $create_order_details;
                        /****deleting this cart after success payment*******/
                        $delete_cart = Cart::where('id',$detail->id)->delete();
                    }

                }

                /*****updating shop order price*******/
                $update_price = OrderShop::updateShopOrderPrice($shop_order_id);
            }
        }

        $update_ord_price = Order::updateMainOrderPrice($main_order_id);
        
        /****delete temp order****/
        $del = OrdersTemp::where('id',$orderId)->delete();
        return $main_order_id;
    }

    public static function saveOrderEndShopping($orderId,$main_order,$temp_ord_info=null) {
        if(empty($temp_ord_info))
            $temp_ord_info = OrdersTemp::where('id',$orderId)->first();
        //dd($temp_ord_info);
        $current_date = currentDateTime();

        $shop_order = OrderShop::where('order_id',$main_order->id)->get();

        $order_json = '';
        // logistic cost

        

        $shop_detail_arr = [];
        if($temp_ord_info->shipping_method == '3') {

            $shipAddInfo = ShippingAddress::find($temp_ord_info->shipping_address_id);
            $shipAddArr = self::getAddressArr($shipAddInfo);
            if($temp_ord_info->shipping_address_id != $temp_ord_info->billing_address_id) {
                $billAddInfo = ShippingAddress::find($temp_ord_info->billing_address_id);
                $billAddArr = self::getAddressArr($billAddInfo);
            }else{
                $billAddInfo = $shipAddInfo;
                $billAddArr = $shipAddArr;
            }

            $order_json = jsonEncode(['shipping_address'=>$shipAddArr,'billing_address'=>$billAddArr,'total_logistic_cost'=>$temp_ord_info->total_logistic_cost]);

        }elseif($temp_ord_info->shipping_method == '1'){
            $order_json = \App\SystemConfig::where('system_name','PICKUP_CENTER')->value('system_val');

        }elseif($temp_ord_info->shipping_method == '2'){
            if(count($shop_order)){
                foreach ($shop_order as $key => $value) {
                    $shop_detail = \App\Shop::where('id',$value->shop_id)->with(['allDesc','shopUser'])->first();
                    $shop_detail_arr[$value->shop_id] = self::getShopArr($shop_detail);
                }
                $order_json = jsonEncode($shop_detail_arr);
            }
        }


        
        $main_order->shipping_method      = $temp_ord_info->shipping_method;
        $main_order->pickup_time          = $temp_ord_info->pickup_time;
        $main_order->user_phone_no        = $temp_ord_info->user_phone_no;
        $main_order->total_shipping_cost  = $temp_ord_info->total_shipping_cost;
        $main_order->kbank_qrcode_id      = $temp_ord_info->kbank_qrcode_id;
        $main_order->end_shopping_date    = $current_date;
        $main_order->order_status         = 2;
        $main_order->order_json           = $order_json;
        $main_order->save();

        if(count($shop_order)){
            foreach ($shop_order as $key => $value) {
                /***checking credit****/
                $shop_credit_period = \App\Credits::where(['user_id'=>$value->user_id,'shop_id'=>$value->shop_id,'seller_approval'=>'Approved'])->value('payment_period');
                if($shop_credit_period){
                    $credit_due_date = addDaysTodate($current_date,$shop_credit_period);
                }else{
                    $credit_due_date = null;
                }
                if(count($shop_detail_arr) && isset($shop_detail_arr[$value->shop_id])){
                    $order_json = jsonEncode($shop_detail_arr[$value->shop_id]);
                }

                $shop_update = OrderShop::where('order_id',$main_order->id)->update(['end_shopping_date'=>$current_date,'credit_due_date'=>$credit_due_date,'updated_at'=>$current_date,'order_status'=>2,'order_json'=>$order_json,'shipping_method'=>$temp_ord_info->shipping_method]);

                $update_price = OrderShop::updateShopOrderPrice($value->id);
            }
        }

        $update_ord_price = Order::updateMainOrderPrice($main_order->id);
        
        /****update entry in order transaction******/
        $comment = GeneralFunctions::getOrderText('order_end_shopping');
        $transaction_arr = ['order_id'=>$main_order->id,'order_shop_id'=>0,'order_detail_id'=>0,'event'=>'order','comment'=>$comment,'updated_by'=>'buyer'];

        $update_transaction = \App\OrderTransaction::updateOrdTrans($transaction_arr);

        /****delete temp order****/
        $del = OrdersTemp::where('id',$orderId)->delete();

    }

    public static function saveOrderCartWise($orderId,$orderInfo=null,$gateway_resp=null){
        if(empty($orderInfo))
            $orderInfo = OrdersTemp::getTempOrderInfo($orderId);

        $user = \App\User::where('id',$orderInfo->user_id)->first();

        $current_date = currentDateTime();

        /****check order if already exist then update otherwise create******/
        $main_order = Order::where('temp_formatted_id',$orderInfo->formatted_order_id)->whereNull('end_shopping_date')->first();

        if(empty($main_order)){
            /***create main order******/
            $main_order = Order::createMainOrder($orderInfo,$user);
            $main_order_id = $main_order->id;
        }else{
            if($orderInfo->payment_slug == 'kbank'){
                $main_order->kbank_qrcode_id = $orderInfo->kbank_qrcode_id;
                $main_order->save();
            }
            $main_order_id = $main_order->id;
        }

        /*****getting cart info for selected product*********/
        $cartInfo = Cart::where(['order_id'=>$orderInfo->id])->with(['getPrd','getCat'])->get();

        $shop_id_arr = $shop_detail_arr = [];
        $shop_cart_detail = [];
        if(count($cartInfo)){
            foreach ($cartInfo as $cvalue) {

                if(!in_array($cvalue->shop_id,$shop_id_arr)){
                    $shop_detail = \App\Shop::where('id',$cvalue->shop_id)->with(['allDesc','shopUser'])->first();
                    $shop_id_arr[] = $cvalue->shop_id;
                    $shop_detail_arr[$cvalue->shop_id] = self::getShopArr($shop_detail);
                }
                $shop_cart_detail[$cvalue->shop_id][] = $cvalue;
                
            }
        }
        $order_detail_id_arr = [];
        if(count($shop_id_arr)){
            /***entry into order shop if data exist then update otherwise create*********/
            foreach ($shop_id_arr as $skey => $svalue) {
                
                $shop_order = OrderShop::where(['order_id'=>$main_order_id,'shop_id'=>$svalue])->first();
                if(empty($shop_order)){
                    /***create shop order******/
                    $shop_order = OrderShop::createShopOrder($svalue,$main_order,$user,$shop_detail_arr[$svalue]);
                    $shop_order_id = $shop_order->id;
                }else{
                    $shop_order_id = $shop_order->id;
                }

                /***entry into order details*******/
                if(isset($shop_cart_detail[$svalue]) && count($shop_cart_detail[$svalue])){
                    $shop_total_price = $total_credit_amount = 0;
                    $shop_data = [];
                    foreach ($shop_cart_detail[$svalue] as $key => $detail) {
                        if(isset($shop_detail_arr[$svalue]) && count($shop_detail_arr[$svalue])){
                            $shop_arr = $shop_detail_arr[$svalue];
                            $shop_data = ['shop_url'=>$shop_arr['shop_url'],'logo'=>$shop_arr['logo'],'shop_name'=>$shop_arr['shop_name'],'panel_id'=>$shop_arr['panel_no']];
                        }
                        /****creating order details*******/
                        $create_order_details = OrderDetail::createOrderDetail($main_order_id,$shop_order_id,$orderInfo,$detail,$shop_data);
                        $order_detail_id_arr[] = $create_order_details;
                        /****deleting this cart after success payment*******/
                        $delete_cart = Cart::where('id',$detail->id)->delete();
                    }

                }
            }
        }

        if($orderInfo->payment_slug == 'kbank' && count($order_detail_id_arr)){
            /*****updating paymet log for items*****/
            $payment_data = file_get_contents(Config::get('constants.public_url')."kbank_ret.txt");
            if(!empty($payment_data)){
                $payment_arr = json_decode($payment_data,true);
                $ref_ord = $payment_arr['reference_order']??'';
                $arr = ['order_id'=>$main_order_id,'payment_slug'=>'kbank','reference_order'=>$ref_ord,'items'=>implode(',', $order_detail_id_arr),'response'=>$payment_data,'created_at'=>$current_date];
                $update_pay_resp = \App\OrderPayment::insert($arr);
            }
        }
        if($orderInfo->payment_slug == 'payplus' && !empty($gateway_resp)){
            $invoice = $gateway_resp['invoice'] ?? '';
            $arr = ['order_id'=>$main_order_id,'payment_slug'=>'payplus','reference_order'=>$invoice,'items'=>implode(',', $order_detail_id_arr),'response'=>json_encode($gateway_resp),'created_at'=>$current_date];
            $update_pay_resp = \App\OrderPayment::insert($arr);
        }
        if($orderInfo->payment_slug == 'odd' && !empty($gateway_resp)){
            $invoice = $gateway_resp['transaction_list'][0]['external_reference'] ?? '';
            $arr = ['order_id'=>$main_order_id,'payment_slug'=>'odd','reference_order'=>$invoice,'items'=>implode(',', $order_detail_id_arr),'response'=>json_encode($gateway_resp),'created_at'=>$current_date];
            $update_pay_resp = \App\OrderPayment::insert($arr);
        }
        /*****updating main order amount*****/
        $update_ord_price = Order::updateMainOrderPrice($main_order_id);
        /****updating temp order price********/
        $update_tempord_price = OrdersTemp::updateOrderPrice($orderInfo->id);
    }
  
    public function thanks(Request $request){
    
        $formatted_id = $request->id; 
        $main_order = Order::where('formatted_id',$formatted_id)->with('getUser')->first();
        // dd($main_order,$formatted_id);
        if(empty($main_order)){
          abort(404);
        }

        $order_detail = [];
        $shop_order = [];
        if(!empty($main_order)){

            $title = 'New Order';
            $customer_name = '';
            $customer_name =  \App\User::where('id', $main_order->user_id)->value('display_name');
            $body = $customer_name .' and order id '. $main_order->formatted_id;
            
            $order_detail = OrderDetail::getMainOrderDetail($main_order->id);
            $shop_ord = \App\OrderShop::where('order_id',$main_order->id)->get();
            //dd($shop_ord);
            if(count($shop_ord)){
                foreach ($shop_ord as $key => $value) {
                    $shop_order[$value->id] = ['shop_formatted_id'=>$value->shop_formatted_id];
                    $post_arr = ['user_id'=>$value->shop_user_id, 'title'=>$title,'body'=>$body, 'type_redirect'=>'payment_success', 'order_id'=>$main_order->id, 'formatted_order_id'=>$main_order->formatted_id];
                    $url = Config::get('constants.mobile_notification_url');//url().'/api/buyer/v1/sendMobileNotification';
                    if($value->send_noti == '2'){
                       $responce = $this->handleCurlRequest($url,$post_arr);
                       $value->send_noti = '1';
                       $value->save();
                    }

                    //dd($responce, $url);
                }

            }
        }
        
        /*if($orderInfo->is_new == '1'){
          $updateOrder = Orders::where(['id' => $orderInfo->id])->update(['is_new'=>'0']);
          
        }else{
          //return redirect()->action('HomeController@index');
        }*/

        $orderInfoJson = jsonDecodeArr($main_order->order_json);
        $total_logistic_cost = isset($orderInfoJson['total_logistic_cost'])?$orderInfoJson['total_logistic_cost']:0;
        

        $referer_url = $request->headers->get('referer');
        $breadcrumb = $this->getBreadcrumb($referer_url);
        return view(loadFrontTheme('checkout/thanks'),['main_order' => $main_order,'order_detail'=>$order_detail,'page'=>'thanks','breadcrumb'=>$breadcrumb,'shop_order'=>$shop_order,'total_logistic_cost'=>$total_logistic_cost]);
    }

    public function cancel(Request $request){

        $referer_url = $request->headers->get('referer');
        $breadcrumb = $this->getBreadcrumb($referer_url);
        $code = !empty($request->code)?$request->code:'';
        return view(loadFrontTheme('checkout/cancel'),['code'=>$code]);
    }

    public function getPayplusResp(Request $request){
        //qrcode get response from charge_id or order_id
        /*$ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,'https://kpaymentgateway-services.kasikornbank.com/qr/v2/qr/order_prod_91723d22652bea744ea9607ce8edec8adcf');
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'x-api-key: skey_prod_917PfxePJ1XGSTr7o2GZvPQys3XVEZ5MhPn')
        );

        $server_output = curl_exec($ch);*/
        // $post_data = ['USERNAME'=>'charansak.a@simummuangmarket.com','TMERCHANTID'=>'33860','TDATE'=>'29072021','TINVOICE'=>'003137425475','TAMOUNT'=>1125,'TSTATUS'=>'A'];
        // $ch = curl_init();

        // curl_setopt($ch, CURLOPT_URL,'https://rt05.kasikornbank.com/Payplus/InquiryTransaction.aspx');
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        // $server_output = curl_exec($ch);
        // dd($server_output);
        return view(loadFrontTheme('checkout/payplusresp'));
    }

    public static function getAddressArr($addInfo) {

        $AddressArr = ['title'=>$addInfo->title,'first_name'=>$addInfo->first_name,'last_name'=>$addInfo->last_name,'provice'=>$addInfo->province_state,'district'=>$addInfo->city_district,'sub_district'=>$addInfo->sub_district,'address'=>$addInfo->address,'road'=>$addInfo->road,'zip_code'=>$addInfo->zip_code,'ph_number'=>$addInfo->ph_number,'company_name'=>$addInfo->company_name,'branch'=>$addInfo->branch,'tax_id'=>$addInfo->tax_id,'company_address'=>$addInfo->company_address];

        return $AddressArr;
    }

    public static function getShopArr($shop_detail){
        $arr = [];
        if(!empty($shop_detail)){
            $name_arr = [];
            if($shop_detail->allDesc){
                foreach ($shop_detail->allDesc as $key => $value) {
                    $name_arr[$value->lang_id] = $value->shop_name;
                }
            }
            $arr = ['shop_user_id'=>$shop_detail->user_id,'shop_url'=>$shop_detail->shop_url,'panel_no'=>$shop_detail->panel_no,'market'=>$shop_detail->market,'logo'=>$shop_detail->logo,'shop_name'=>$name_arr,'ph_number'=>$shop_detail->ph_number];
            if($shop_detail->shopUser){
                $arr['seller_email'] = $shop_detail->shopUser->email;
                $arr['seller_name'] = $shop_detail->shopUser->first_name.' '.$shop_detail->shopUser->last_name;
                $arr['seller_ph_number'] = $shop_detail->shopUser->ph_number;
            }
        }
        return $arr;
    }
  
    public static function createKbankOrder($orderInfo,$kbank_details){
        $secret_key = $kbank_details['secret_key'];
        $ref_no = substr(number_format(time() * rand(),0,'',''),0,10);
        $post_array = array('amount'=>$orderInfo->total_final_price,'currency'=>'THB','description'=>'item','source_type'=>'qr','reference_order'=>$ref_no);
        $post_json = json_encode($post_array);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,$kbank_details['qr_url']."order");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'x-api-key: '.$secret_key.'')
        );

        $server_output = curl_exec($ch);

        $response = json_decode($server_output);
        if(!empty($response->id)){
            $update_ord = Order::where('id',$orderInfo->id)->update(['kbank_qrcode_id'=>$response->id]);
            return $response->id;
        }else{
            return 0;
        }
        
    }
}