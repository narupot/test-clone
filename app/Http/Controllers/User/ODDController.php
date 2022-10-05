<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\MarketPlace;
use App\Helpers\GeneralFunctions;
use App\Helpers\CustomHelpers;
use Illuminate\Http\Request;
use App\ShippingAddress;
use App\Country;
use App\User;
use App\UserInfo;
use Hash;

use Auth;
use Lang;
use Config;
use Session;

class ODDController extends MarketPlace
{   
    public function __construct() {
        $this->middleware('authenticate');
    }
    
    public function index(Request $request) {

        $userDetail = Auth::user();

        $userDetail->facebook_login = 'N';
        if(empty($userDetail->password) && !empty($userDetail->facebook_id)) {
            $userDetail->facebook_login = 'Y';
        }        
        $user_odd_info = UserInfo::getUserInfo('odd-register');

        return view('user.odd.register', ['userDetail'=>$userDetail,'page'=>'buyer','user_odd_info'=>$user_odd_info]);
    }

    public function oddToken(Request $request){
        $input = $request->all();
        $rules['ph_number'] = phoneRule();
        $rules['citizen_id'] = reqRule();
        $error_msg['ph_number.required'] = Lang::get('customer.please_enter_phone_no');
        $error_msg['ph_number.digits'] = Lang::get('customer.phone_no_must_be_10_digits');
        $validate = Validator::make($input, $rules, $error_msg);

        if ($validate->passes()) {

            $user_odd_info = UserInfo::getUserInfo('odd-register');

            if(!empty($user_odd_info) && $user_odd_info->status=='1'){
                return \Redirect::to(action('User\ODDController@index'))->send()->with('errorMsg', Lang::get('customer.you_have_been_already_register'));
            }
            
            $userDetail = Auth::user();

            $ref_no = generateUniqueNo();

            $pay_opt = \App\PaymentOption::where('slug','odd')->first();
            if($pay_opt->mode == 2)
                $pay_details = json_decode($pay_opt->sandbox_detail,true);
            else
                $pay_details = json_decode($pay_opt->live_detail,true);

            //dd($ref_no,strlen($ref_no),rand());
            //(pass phrase, external_system,payee_short_name, external_reference)

            $auth_str = $pay_details['pass_phrase'].$pay_details['external_system'].$pay_details['payee_short_name'].$ref_no;

            $sha = hash('sha256', $auth_str);
            $auth  = strtoupper($sha);

            $post_array = [];
            $post_array['transaction_type'] = $pay_details['transaction_type_register'];
            $post_array['encoding'] = $pay_details['encoding'];
            $post_array['external_system'] = $pay_details['external_system'];
            $post_array['payee_short_name'] = $pay_details['payee_short_name'];
            /*$post_array['payer_short_name'] = substr(str_replace(' ', '', $userDetail->display_name), 0,11);*/
            $post_array['payer_short_name'] = "";
            $post_array['user_email'] = "";//$userDetail->email;
            $post_array['user_mobile_no'] = $request->ph_number;
            $post_array['id'] = $request->citizen_id;
            $post_array['external_reference'] = $ref_no;
            $post_array['service_name'] = $pay_details['service_name'];
            $post_array['auth_parameter'] = $auth;
            
            $post_json = json_encode($post_array);
            //dd($post_json,$post_array);
            //https:// 203.146.18.96/ws/v1/registerinit
            //https://ws04.uatebpp.kasikornbank.com/ws/v1/registerinit
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL,$pay_details['curl_url']."registerinit");
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$post_json);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json')
            );

            $server_output = curl_exec($ch);
            //dd($server_output);
            //dd($server_output,$post_json,$pay_details['curl_url']."registerinit");
            if($server_output){
                
                $update_info = UserInfo::updateInfo($ref_no,$server_output,$request,$post_json);

                /*return view('user.odd.odd_response', ['response'=>$server_output]);*/
                $err = '';
                $resp = json_decode($server_output);
                if(isset($resp->return_status) && isset($resp->reg_id) && $resp->reg_id !=''){
                    $url = $pay_details['redirect_url'].'reg_id='.$resp->reg_id.'&langLocale=th_TH';
                    return redirect($url);
                    $status = '0';
                }else{
                    $status = '2';
                    $err = isset($resp->return_message)?$resp->return_message:'';
                }
                $update_info = UserInfo::updateInfo($ref_no,$server_output,$request,$post_json,$status);
                return \Redirect::to(action('User\ODDController@index'))->send()->with('errorMsg',$err);

            }
            return \Redirect::to(action('User\ODDController@index'))->send()->with('errorMsg', Lang::get('customer.invalid_od_info'));

        }else{
            return redirect()->action('User\ODDController@index')->withErrors($validate)->withInput();
        }
        
    }

    public function tracking(Request $request){

        $returnODDRegister = json_encode($request->all());
        $returnODDRegister = iconv("CP1257","UTF-8", $returnODDRegister);
        
        file_put_contents(Config::get('constants.public_path')."/odd_register.txt", $returnODDRegister);
        
        $response = $request->all();
        /*$response = '21082561011933 65B77EF620994DB727586497053E319896564A40F14B4D9231A7BD1338441FFA 9662588064 201808210234540K0000Success';

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

        $user_info = UserInfo::where('reference_no',$external_reference)->first();
        if(!empty($user_info)){
            $info_json = json_decode($user_info->info_json,true);
            if(count($info_json)){
                $info_json['tracking_response'] = $response;
                $new_json = $info_json;
            }else{
                $new_json = $response;
            }
            
            $user_info->status = '1';
            $user_info->espa_id = $espa_id;
            $user_info->info_json = $new_json;
            $user_info->save();
        }*/
        return true;
        // dd($request->all(),'aa');
    }
}