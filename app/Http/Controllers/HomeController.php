<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MarketPlace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Session;
use Route;
use Cache;
use App\Helpers\GeneralFunctions;
use Lang;
use Config;
use DB;
class HomeController extends MarketPlace
{

    public function __construct()
    {
        //$this->middleware('auth');
    }

    public function index() {
        //dd("ok");

      return view('home');
    }

    public function mobileLogin(Request $request){
        if(Auth::check() && (Auth::User()->email || Auth::User()->login_use == 'ph_no')){
            if(Auth::User()->login_use == 'ph_no'){
               $email = Auth::User()->ph_number; 
            }else{
               $email = Auth::User()->email; 
            }
            

            $password = Auth::User()->password;
            
            //$main_url = env('MOBILE_APP_URL').'api/buyer/v1/';

            $main_url = Config::get('constants.mobile_app_url').'api/buyer/v1/';

            $mobile_app_url = Config::get('constants.mobile_app_chat_url');

            $token_url = $main_url.'token';
            $post_arr = ['key'=>'ohKznpVgJnmrSCldixDJGQcXcaZm9ZgT','secret_key'=>'qAOLED4T-mjDK0PTyJeEOAP50G5N03ITqmKy3EGI0jSRCEfvh76_4Qk9cp3UoUZP'];

            //dd($main_url, $mobile_app_url);

            $response = $this->handleCurlRequest($token_url,$post_arr);
            
            if(isset($response['return_code']) && $response['return_code']=='200'){
                $token = $response['token'];

                $login_url = $main_url.'login';
                $post_arr = ['username'=>$email,'password'=>$password,'uuid'=>'E621E1F8-C36C-495A-93FC-0C247A3E6E5F','pwd_type'=>'encrypted'];
                $headers = array(
                           "Accept: application/json",
                           "Authorization: ".$token,
                        );
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $login_url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 1,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $post_arr,
                    CURLOPT_HTTPHEADER => $headers,
                ));
                $response_login = curl_exec($curl);
                $resp_arr = json_decode($response_login,true);

                if(isset($resp_arr['return_code']) && $resp_arr['return_code']=='200'){
                    $mobile_app_url = $mobile_app_url.'?token='.$resp_arr['token'];
                    return ['status'=>'success','url'=>$mobile_app_url];
                }
            }

        }
        return ['status'=>'fail'];
        dd($response,'token');
    }

    public function myhome(){

    	/*$ph_number = '0623564951';
        $otp_response = $this->sendOtp($ph_number);
        dd($otp_response);*/

        /*$otp_response = $this->matchOtp('9n6oKm1qBXrEb11TpIjbgzLVwPQ3O0eZ',625663);
        dd($otp_response);*/

        dd('dd');
        
        
        //dd($request->all());
        /**************
        *****item and order status description that value logistic will send
        ***Item Status => Pending => 1,Item not match with ordered qty => 2,Item ready to delivery => 3,Item cancelled => 4,Item received => 5
        *******
        ***Order Status => Pending => 1,Order already at warehouse center=>2,Order delivery => 3,Order received=>4,Cancel Reason reject => 5,Cancel Reason returned => 6,Cancel Reason failed delivery => 7,Cancel Reason cancelled => 8
        *****This value will be replaced by order status table
        **********/
        $item_status_arr = [1=>1,2=>7,3=>8,4=>4,5=>3];
        $order_status_arr = [2=>5,3=>5,4=>6,5=>3,6=>9,7=>10,8=>11,9=>12];

        /****updating log **/
       
        $order_id = $order_shop_id = 0;
        try {
            
            $data = [];
            $request_json = \App\OrderUpdateApiLog::where('id',312)->value('api_json');
            $request = json_decode($request_json);
            //dd($request,$request->shop_formatter_id);
            
            if (!$request) {
                
            } else {
                $errordata = [];
                
                $shop_formatter_id = $request->shop_formatter_id;
                $shop_ord_detail = \App\OrderShop::where('shop_formatted_id',$shop_formatter_id)->first();
                if(empty($shop_ord_detail)){
                    $errordata['error'][] = 'Invalid shop formatted id';
                }

                $shop_status_id = isset($order_status_arr[$request->status_order])?$order_status_arr[$request->status_order]:0;
                $logistic_status_id = $shop_status_id;
                $logistic_shop_status = $request->status_order;
                if($shop_status_id == 0){
                    $errordata['error'][] = 'Invalid shop status';
                }
                $ord_detail_id_arr  = $item_detail_arr = [];
               
                foreach ($request->item_details as $key => $value) {
                    $value = json_decode(json_encode($value),true);
                    
                    $order_detail_id = (int)$value['order_detail_id'];

                    if(isset($item_status_arr[(int)$value['status_line']])){
                        $item_detail_arr[$order_detail_id] = ['order_detail_id'=>$order_detail_id,'status'=>(int)$value['status_line']];
                        $ord_detail_id_arr[] = $order_detail_id;
                    }else{
                        $errordata['error'][] = 'invalid status for item id '.$order_detail_id;
                    }
                    
                }

                if(empty($item_detail_arr)){
                    $errordata['error'][] = 'Invalid item details array';
                }
                $order_details = [];
                if(!empty($shop_ord_detail)){
                    $order_details = \App\OrderDetail::where(['order_shop_id'=>$shop_ord_detail->id])->whereIn('id',$ord_detail_id_arr)->get();
                    $order_id = $shop_ord_detail->order_id;
                    $order_shop_id = $shop_ord_detail->id;
                }
                
                if(count($ord_detail_id_arr)){
                  $ord_detail_id_arr = array_unique($ord_detail_id_arr);
                }
                
                /*print_r($ord_detail_id_arr);
                echo '<br>';
                print_r(count($order_details));
                exit;
                dd(count($ord_detail_id_arr),count($order_details),$errordata);*/
                
                if(count($ord_detail_id_arr) == count($order_details) && empty($errordata)){
                    foreach ($order_details as $okey => $ovalue) {
                        $item_status = $item_status_arr[$item_detail_arr[$ovalue->id]['status']];
                        $sender_id = $item_detail_arr[$ovalue->id]['status'];
                        dd($item_status,$sender_id);
                        if($ovalue->logistic_status != $item_status){
                            /***updating item status******/
                            $logistic_status_id = $item_status;
                            $status_id = $logistic_status_id;
                            if($sender_id == 1 or $sender_id == 2 or $sender_id == 3){
                                $status_id = 2;
                            }
                            dd($status_id,$logistic_status_id,$sender_id);
                            //$update_ord_detail = OrderDetail::where('id',$ovalue->id)->update(['status'=>$status_id,'logistic_status'=>$logistic_status_id]);
                            if($logistic_status_id == 3)
                                $comment = 'Received';
                            else
                                $comment = \App\OrderStatusDesc::getStatusVal($logistic_status_id);
                           
                            $comment = $comment.' ('.$ovalue->category_name.')';
                            
                            /****update entry in order transaction******/
                            $transaction_arr = ['order_id'=>$ovalue->order_id,'order_shop_id'=>$ovalue->order_shop_id,'order_detail_id'=>$ovalue->id,'event'=>'delivery','comment'=>$comment,'updated_by'=>'logistic'];

                        }
                    }

                    /****updating shop order status******/
                    if($logistic_shop_status >=6 && $logistic_shop_status <= 9){
                        $shop_status_id = 4;
                    }

                    $shop_ord_detail->order_status = $shop_status_id;
                    $shop_ord_detail->logistic_status = $logistic_status_id;
                    //$shop_ord_detail->save();

                    if($logistic_shop_status >=6){
                        if($logistic_shop_status = 5){
                            $comment = GeneralFunctions::getOrderText('order_completed');
                        }else{
                            $comment = GeneralFunctions::getOrderText('order_cancelled');
                        }
                        /****update entry in order transaction******/
                        $transaction_arr = ['order_id'=>$shop_ord_detail->order_id,'order_shop_id'=>$shop_ord_detail->id,'order_detail_id'=>0,'event'=>'delivery','comment'=>$comment,'updated_by'=>'logistic'];

                        //$update_transaction = \App\OrderTransaction::updateOrdTrans($transaction_arr);
                    }

                    /*******updating main order********/
                    //$main_ord = Order::updateMainOrdStatus($shop_ord_detail->order_id);
                    $success = [
                        'status' => ResponseHTTP::HTTP_OK ,
                        'data' => [],
                    ];

                    $message = 'Order updated';
                    
                }else{
                    
                }
            }

            /***updating log response***/
            dd($success);
        } catch (\Exception $e) {
            $success = [
                        'status' => 0,
                    ];
            dd($success);
        }
    	$data = \App\CmsSlider::getSliderDetail(1);
        dd($data);
    	////january 31
    	$category_ids = [32];
    	$badge_id = [17];
    	$my_qry = \App\MongoProduct::where(['cat_id'=>32,'badge_id'=>17])->select('unit_price')->get()->toArray();
    	
    	$pp = \App\MongoProduct::raw(function($collection) use ($category_ids,$badge_id)
		{
		return $collection->aggregate([
					 [
                        '$match' => [
                            'cat_id' => ['$in' => $category_ids],
                            'badge_id' => ['$in' => $badge_id],
                        ],     
                    ],
					[
						'$group' => [
							'_id' =>[
								'cat_id'=>'$cat_id',
								'badge_id'=>'$badge_id'
							],
							'unit_price'=>[
								'$avg'=> '$unit_price'
							]
						],
						
					],
					[   
                        '$sort' => ['unit_price' => -1]   
                    ], 
                    
				]);
		})->toArray();
		dd($pp);
		foreach ($pp as $key => $value) {
			dd($value['unit_price'],(array)$value['_id']);
		}
    	
    }

    public function exportOrder(Request $request){
        
        dd('dd');
        $data_h = "H";
        $data_p = "P";
        $data_p_product_code = "DCT";

        $data_client_code = getConfigValue('CLIENT_CODE_FOR_ORDER_EXPORT_FILE');
        $data_client_account_no = "3731037174";

        /*****H row data****/
        $record_identifier = $data_h;
        $no_use_2 = str_repeat(' ', 12);
        $no_use_3 = str_repeat(' ', 10);
        $no_use_4 = str_repeat(' ', 20);
        $no_use_5 = str_repeat(' ', 10);
        $no_use_6 = str_repeat(' ', 10);
        $no_use_7 = str_repeat(' ', 10);
        $no_use_8 = str_repeat(' ', 10);
        $no_use_9 = str_repeat(' ', 20);

        $h_data = $record_identifier . $no_use_2 . $no_use_3 . $no_use_4 . $no_use_5 . $no_use_6 . $no_use_7 . $no_use_8 . $no_use_9;

        /*****P row data************/

        $record_identifier = $data_p;

        $product_code = str_pad($data_p_product_code, 10, " ", STR_PAD_RIGHT);

        $no_use_3 = str_repeat(' ', 10);
        $no_use_4 = str_repeat(' ', 10);
        $no_use_5 = str_repeat(' ', 10);
        $no_use_6 = str_repeat(' ', 5);
        $no_use_7 = str_repeat(' ', 20);

        $client_code = str_pad($data_client_code, 20, " ", STR_PAD_RIGHT);

        $no_use_9 = str_repeat(' ', 10);
        
        $client_account_id = str_pad($data_client_account_no, 20, " ", STR_PAD_RIGHT);
        $no_use_11 = str_repeat(' ', 10);
        $no_use_12 = str_repeat(' ', 10);
        $no_use_13 = str_repeat(' ', 10);
        $no_use_14 = str_repeat(' ', 10);
        $no_use_15 = str_repeat(' ', 20);
        $no_use_16 = str_repeat(' ', 10);
        $no_use_17 = str_repeat(' ', 10);
        $no_use_18 = str_repeat(' ', 10);
        $no_use_19 = str_repeat(' ', 255);
        $no_use_20 = str_repeat(' ', 10);

        $p_data = $record_identifier . $product_code . $no_use_3 . $no_use_4 . $no_use_5 . $no_use_6 . $no_use_7 . $client_code . $no_use_9 . $client_account_id . $no_use_11 . $no_use_12 . $no_use_13 . $no_use_14 . $no_use_15 . $no_use_16 . $no_use_17 . $no_use_18 . $no_use_19 . $no_use_20;
        //echo strlen($p_data);exit;
        /*****I row data************/
        $data_benef_bank_code = "004";
        $data_benef_branch_code = "0040745";
        $record_identifier = $data_i = "I";
        $export_date = date('Y-m-d',strtotime("-1 days"));
        $export_date = '2021-09-26';
        $tot_order = \App\Order::where(DB::raw('date(end_shopping_date)'),$export_date)
                            ->where('order_status','!=',4)
                            ->where('payment_status',1)
                            ->count();

        $seller_order_data = \App\OrderShop::where(DB::raw('date(end_shopping_date)'),$export_date)
                            ->where('order_status','!=',4)
                            ->where('payment_status',1)
                            ->select(DB::raw('sum(total_final_price) as totPrice ,count(order_id) as totorder'),'shop_user_id','end_shopping_date','shop_json')
                            ->with('getSellerDetail')
                            ->groupBy('shop_id')
                            ->get();
        
        $total_order_amt = 0;
        $i_data = '';
        if($seller_order_data && count($seller_order_data)){
            foreach ($seller_order_data as $key => $value) {
                $total_order_amt = $total_order_amt + $value->totPrice;

                $record_identifier = $data_i = "I";
                $no_use_2 = str_repeat(' ', 20);
                $no_use_3 = str_repeat(' ', 10);

                $shop_json_arr = json_decode($value->shop_json,true);
                $seller_name = $value->getSellerDetail->account_name;
                $seller_name_len = mb_strlen($seller_name,'UTF-8');
                $rest_seller_name = str_repeat(' ', 80-$seller_name_len);
                $benef_desc = $seller_name.$rest_seller_name;
                
                $no_use_5 = str_repeat(' ', 10);
                $no_use_6 = str_repeat(' ', 10);
                $no_use_7 = str_repeat(' ', 10);
                $no_use_8 = str_repeat(' ', 20);
                
                $inst_payment_amnt = sprintf("%'020.2f", $value->totPrice);

                $no_use_10 = str_repeat(' ', 20);
                
                $inst_date = str_pad(date('d/m/Y',strtotime($value->end_shopping_date)), 10, " ", STR_PAD_RIGHT);

                $benef_bank_code = str_pad($data_benef_bank_code, 10, " ", STR_PAD_RIGHT);
                $benef_branch_code = str_pad($data_benef_branch_code, 10, " ", STR_PAD_RIGHT);

                $benef_bank_acc_no = str_pad($value->getSellerDetail->account_no, 20, " ", STR_PAD_RIGHT);

                $no_use_15 = str_repeat(' ', 16);
                $no_use_16 = str_repeat(' ', 4);
                $no_use_17 = str_repeat(' ', 150);
                $no_use_18 = str_repeat(' ', 150);
                $no_use_19 = str_repeat(' ', 255);
                $delivery_mode = str_repeat(' ', 10);
                $no_use_21 = str_repeat(' ', 10);
                $no_use_22 = str_repeat(' ', 10);
                $no_use_23 = str_repeat(' ', 1);
                $no_use_24 = str_repeat(' ', 1);
                $no_use_25 = str_repeat(' ', 20);
                $no_use_26 = str_repeat(' ', 20);
                $no_use_27 = str_repeat(' ', 10);
                $no_use_28 = str_repeat(' ', 24);
                $no_use_29 = str_repeat(' ', 20);
                $no_use_30 = str_repeat(' ', 20);
                $no_use_31 = str_repeat(' ', 20);
                
                //$payee_name = str_pad($value->getSellerDetail->account_name, 120, " ", STR_PAD_RIGHT);

                $payee_len = 120 - mb_strlen($value->getSellerDetail->account_name, 'UTF-8');
                $rest_space_name = str_repeat(' ', $payee_len);
                $payee_name = $value->getSellerDetail->account_name.$rest_space_name;
                
                $no_use_33 = str_repeat(' ', 20);
                $no_use_34 = str_repeat(' ', 54);
                $no_use_35 = str_repeat(' ', 2);
                $no_use_36 = str_repeat(' ', 1720);
                $no_use_37 = str_repeat(' ', 1);
                $no_use_38 = str_repeat(' ', 255);
                $no_use_39 = str_repeat(' ', 1);
                $no_use_40 = str_repeat(' ', 10);
                $no_use_41 = str_repeat(' ', 20);
                
                $beneficiary_pickup_location_code = str_repeat(' ', 30);

                $no_use_43 = str_repeat(' ', 50);
                $no_use_44 = str_repeat(' ', 50);

                $i_data .= $record_identifier . $no_use_2 . $no_use_3 . $benef_desc . $no_use_5 . $no_use_6 . $no_use_7 . $no_use_8 . $inst_payment_amnt . $no_use_10 . $inst_date   . $benef_bank_code . $benef_branch_code . $benef_bank_acc_no .$no_use_15. $no_use_16 . $no_use_17 . $no_use_18 . $no_use_19 . $delivery_mode . $no_use_21 . $no_use_22 . $no_use_23 . $no_use_24 . $no_use_25 . $no_use_26 . $no_use_27 . $no_use_28 . $no_use_29 . $no_use_30 . $no_use_31 . $payee_name . $no_use_33 . $no_use_34 . $no_use_35 . $no_use_36 . $no_use_37 . $no_use_38 . $no_use_39 . $no_use_40 . $no_use_41 . $beneficiary_pickup_location_code . $no_use_43 . $no_use_44;

                $i_data .="\n";
            }
        }
        
        /******T row data*****************/
        $data_t = "T";

        $record_identifier = $data_t;
        $no_use_2 = str_repeat(' ', 5);
        $no_use_3 = str_repeat(' ', 20);
        $no_use_4 = str_repeat(' ', 5);
        $no_use_5 = str_repeat(' ', 20);
        $t_data = $record_identifier . $no_use_2 . $no_use_3 . $no_use_4 . $no_use_5;

        $main_data = $h_data."\n".$p_data."\n".$i_data.$t_data;
        $main_data = iconv('utf8', 'tis620', $main_data);

        if($tot_order){
            $exp_log_count = \App\OrderExportLog::where(DB::raw('date(order_date)'),$export_date)->count();
            $exp_no = $exp_log_count ? $exp_log_count : 0;
            $ref_no = sprintf("%03d", $exp_no);

            $file_path = Config::get('constants.public_path');

        
            $client_code = $data_client_code;
            $date = str_replace('-', '', $export_date);
            $file_name = 'P-'.$client_code.'-'.$date.'-'.$ref_no;
            $file = $file_path.'/seller-payment/'.$file_name.'.txt';
            try {
                \File::put($file,$main_data);
            } catch (\Exception $e) {
                dd($e->getMessage());
            }
            
            $log_obj = new \App\OrderExportLog;
            $log_obj->total_order = $tot_order;
            $log_obj->file_name = $file_name;
            $log_obj->total_seller = count($seller_order_data);
            $log_obj->total_amount = $total_order_amt;
            $log_obj->status = 'pending';
            $log_obj->order_date = date('Y-m-d H:i:s',strtotime($export_date.date('H:i:s')));
            $log_obj->save();
        }

    }

}
