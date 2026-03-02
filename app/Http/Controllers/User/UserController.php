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
use Hash;

use Auth;
use Lang;
use Config;
use Session;

class UserController extends MarketPlace
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

        return view('user.profile', ['userDetail'=>$userDetail,'page'=>'buyer']);
    }

    function update(Request $request) {
        
        $input = $request->all();
        $user_id = Auth::id();

        $validate_password = 0;
        if(isset($request->change_password) && $request->change_password == '1'){
            
            $validate_password = 1;

            if($request->facebook_login == 'N') {
                $user = User::find($user_id);
                $current_password = '';
                if(count($user)>0 && Hash::check($request->current_password, $user->password)) {
                    $current_password = 'correct';
                }
                $input['current_password'] = $current_password;
            }  
        }

        $validate = $this->validateProfile($input, $validate_password);
        if ($validate->passes()) {  
           
            $userObject = User::find($user_id);
            $userObject->display_name = $request->first_name.' '.$request->last_name;
            $userObject->first_name = $request->first_name;
            $userObject->last_name = $request->last_name;
            $userObject->dob = $request->dob;

            if(!empty($request->new_password)) {
                $userObject->password = bcrypt($request->new_password);
            }

            if(isset($request->image) && !empty($request->image)) {

                $image_name = mt_rand(100,999).uniqid().'.jpg';
                $file_path = Config('constants.user_path');
                $this->base64UploadImage($request->image, $file_path, $image_name);

                $this->fileDelete($file_path.'/'.$userObject->image);

                $userObject->image = $image_name;                
            }

            $userObject->save();

            $status = 'success';
            $message = Lang::get('common.records_updated_successfully');
        } 
        else {

            $status = 'validation_error';
            $message = json_decode($validate->errors());
        }

        return json_encode(['status'=>$status, 'msg'=>$message]);
    }

    function confirmPassword(Request $request) {
        //echo '<pre>';print_r($request->all());die;
        $user_id = Auth::id();
        $user = User::find($user_id);
        $status = 'fail';
        $message = Lang::get('customer.please_enter_correct_password');
        if(count($user)>0 && Hash::check($request->current_password, $user->password)) {
            $status = 'success';
            $message = '';
        }
        return json_encode(['status'=>$status, 'msg'=>$message]);
    }

    function sendUpdateOtp(Request $request) {

        if($request->ajax()) {

            $user_id = Auth::id();
            $input = $request->all();

            $status = $message = $error = '';

            if($request->login_type == 'phone'){

                $rules['phone_no'] = phoneRule(with(new user)->getTable(), 'phone_no');

                $validate = Validator::make($input, $rules);
                if($validate){
                    
                    if(Config::get('constants.localmode') == true){
                        $otp_response = ['status'=>'success', 'msg'=>'', 'token'=>''];
                    }
                    else {
                        $otp_response = $this->sendOtp($request->phone_no);
                    }

                    if($otp_response['status'] == 'success'){
                        $token = $otp_response['token'];
                        User::where('id', $user_id)->update(['phone_otp_token'=>$token,'otp_generated_at'=>currentDateTime()]);

                        session(['login_type'=>'phone', 'phone_no'=>$request->phone_no]);

                        $status = 'success';
                        $message = Lang::get('customer.please_enter_4_digit_code_recieved_on_your_mobile');
                    }else{
                        $status = 'fail';
                        $message = Lang::get('customer.unable_to_update_mobile_no_please_try_latter');
                        $error = $otp_response['msg'];
                    }

                }else{
                    $status = 'validation_error';
                    $message = json_decode($validate->errors());
                }

            }else{

                $rules['email'] = emailRule(with(new user)->getTable(), 'email');

                $validate = Validator::make($input, $rules);
                if($validate){

                    $userDetail = Auth::user();
                    $userDetail->email = $request->email;

                    $otp_response = $this->sendOtpToEmail($userDetail);

                    if($otp_response['status'] == 'success') {

                        session(['login_type'=>'email', 'email_id'=>$request->email]);

                        $status = 'success';
                        $message = Lang::get('customer.please_enter_4_digit_code_recieved_on_your_email');
                    }else{
                        $status = 'fail';
                        $message = Lang::get('customer.unable_to_update_email_id_please_try_latter');
                        $error = $otp_response['msg'];
                    }
                }else{
                    $status = 'validation_error';
                    $message = json_decode($validate->errors());
                }
            }

            return json_encode(['status'=>$status, 'msg'=>$message, 'error'=>$error]);
        }        
    }

    function confirmOtp(Request $request) {

        $user_id = Auth::id();
        $otp = $request->otp;

        $status = $message = $error = '';

        if($request->ajax() && $otp && !empty(session('login_type'))) {

            $user_det = User::userDetail($user_id);

            if(session('login_type') == 'phone'){

                if(Config::get('constants.localmode') == true){
                    $otp_response['status'] = 'success';
                }
                else {
                    $otp_response = $this->matchOtp($user_det->phone_otp_token, $otp);
                }

                if($otp_response['status'] == 'success'){
                    User::where('id', $user_det->id)->update(['login_use'=>'ph_no', 'ph_number'=>session('phone_no'), 'phone_otp_token'=>'']);

                    session()->forget(['login_type', 'phone_no']);

                    $status = 'success';
                    $message = Lang::get('customer.mobile_no_updated_successfully');
                }else{
                    $status = 'fail';
                    $message = Lang::get('customer.please_enter_correct_otp');                    
                    $error = $otp_response['msg'];
                }

            }else{

                if($user_det->email_token == $otp){

                    User::where('id', $user_det->id)->update(['login_use'=>'email', 'email'=>session('email_id'), 'email_token'=>'']);

                    session()->forget(['login_type', 'email_id']);
                    
                    $status = 'success';
                    $message = Lang::get('customer.email_id_updated_successfully');

                }else{

                    $status = 'fail';
                    $message = Lang::get('customer.please_enter_correct_otp');
                }
            } 
        }
        else {
            $status = 'fail';
            $message = Lang::get('customer.session_expired');            
        }

        return json_encode(['status'=>$status, 'msg'=>$message, 'error'=>$error]);
    }       

    private function validateProfile($input, $validate_password) {
        
        $rules['first_name'] = nameRule();
        $rules['last_name'] = nameRule();

        if($validate_password == '1'){
            if($input['facebook_login'] == 'N') {
                $rules['current_password'] = 'Required';
                $error_msg['current_password.required'] = Lang::get('customer.please_enter_correct_old_password');
            }

            $rules['new_password'] = passwordRule();
            $rules['confirm_password'] = confirmPasswordRule('new_password');

            $error_msg['new_password.required'] = Lang::get('customer.enter_new_password');
            $error_msg['confirm_password.required'] = Lang::get('customer.enter_confirm_password');
            $error_msg['confirm_password.same'] = Lang::get('customer.new_password_and_confirm_password_should_be_same');
        }

        $rules['dob'] = 'Required';        

        $error_msg['first_name.required'] = Lang::get('customer.enter_first_name');
        $error_msg['last_name.required'] = Lang::get('customer.enter_last_name');
        $error_msg['dob.required'] = Lang::get('customer.enter_date_of_birth');  

        $validate = Validator::make($input, $rules, $error_msg);
        
        return $validate; 
    }         
    
    public function create(Request $request) {

        if($request->call_type == 'ajax_data') {

            $user_detail = Auth::user();

            $def_country_dtl = GeneralFunctions::getDefaultCountryDetail();
            $ship_province_str = '';
            if(getConfigValue('ADDRESS_TYPE') == 'dropdown' && !empty($def_country_dtl)) {
                $ship_province_str = CustomHelpers::getProvinceStateNormalDD($def_country_dtl->id);
            }         
            
            return view('shipBillAddress.addressAdd', ['user_detail'=>$user_detail, 'def_country_dtl'=>$def_country_dtl, 'ship_province_str'=>$ship_province_str]);
        }       
    }    
    
    function store(Request $request){

        $input = $request->all();

        $validate = $this->validateAddressForm($input);

        if($validate->passes()) {

            $user_id = Auth::User()->id;

            $data_arr['user_id'] = $user_id;
            $this->saveUserShippingBillingAddress($request, $data_arr);

            if($request->address_id > 0) { // update address
                $msg_text = Lang::get('customer.address_updated_successfully');
            }
            else{   // insert address
                $msg_text = Lang::get('customer.address_added_successfully');
            }            
            
            return json_encode(array('status'=>'success', 'message'=>$msg_text));
        }
        else {
            
            $errors = json_decode($validate->errors()); 
            return json_encode(array('status'=>'validate_error','message'=>$errors));
        }                  
    }   

    public function show(Request $request) {

        $user_address = ShippingAddress::getUserAddress(Auth::id());
        //dd($user_address);

        $shipping_address = [];
        $billing_address = [];
        $all_address = [];

        foreach($user_address as $address) {

            if($address->is_default == '1') {
                if(($address->address_type == '1' || $address->address_type == '3')) {
                    $shipping_address[] = $address;
                }
                if(($address->address_type == '2' || $address->address_type == '3')) {
                    $billing_address[] = $address;
                }
            }
            else {
                $all_address[] = $address;
            }            
        }
        //dd($all_address, $shipping_address, $billing_address); 

        return view('shipBillAddress.addressList', ['billing_add'=>$billing_address, 'shipping_add'=>$shipping_address, 'all_address'=>$all_address]);
    }
    
    function edit(Request $request, $address_id) {
        //echo '<pre>';print_r($request->all());die;
        if($request->call_type == 'ajax_data') {
            $address = ShippingAddress::getAddressById($address_id);

            $address->shipping_address = false;
            $address->billing_address = false;
            if(($address->address_type == '1' || $address->address_type == '3') && $address->is_default == '1') {
                $address->shipping_address = true;
            }
            if(($address->address_type == '2' || $address->address_type == '3') && $address->is_default == '1') {
                $address->billing_address = true;
            }

            $def_country_dtl = GeneralFunctions::getDefaultCountryDetail();

            return view('shipBillAddress.addressEdit', ['address'=>$address, 'def_country_dtl'=>$def_country_dtl]);
        }         
    }

    function delete(Request $request) {
        //dd($request->all());

        if($request->action == 'delete' && $request->id > 0) {
            ShippingAddress::where('id', $request->id)->delete();
            return 'success';
        }
    }

    function updateSequence(Request $request) {
        //dd($request->all());

        $user_id = Auth::id();
        if(!empty($request->sequence)) {
            foreach ($request->sequence as $key => $value) {
                if($value > 0) {
                    ShippingAddress::where(['id'=>$value, 'user_id'=>$user_id])->update(['sequence'=>$key]);
                }
            }
        }
        return 'success';
    }

    function setDefaultAddress(Request $request) {
        //dd($request->all());

        if($request->address_id > 0 && $request->address_type > 0) {
            
            $userid = Auth::User()->id;

            $address = ShippingAddress::find($request->address_id);

            if($request->address_type != $address->address_type && $address->address_type != '0') {

                ShippingAddress::where(['user_id'=>$userid])->update(['address_type' => '0', 'is_default' => '0']);

                ShippingAddress::where(['id'=>$request->address_id, 'user_id'=>$userid])->update(['address_type' => '3', 'is_default' => '1']);
            }
            else {
                if($request->address_type == '1') {
                    $ship_address = ShippingAddress::select('address_type')->where(['user_id'=>$userid, 'is_default' => '1'])->whereIn('address_type', ['1','3'])->first();

                    if(!empty($ship_address)) {
                        if($ship_address->address_type == '1') {
                            ShippingAddress::where(['address_type'=>'1', 'user_id'=>$userid])->update(['address_type' => '0', 'is_default' => '0']);
                        }                    
                        elseif($ship_address->address_type == '3'){
                            ShippingAddress::where(['address_type'=>'3', 'user_id'=>$userid])->update(['address_type' => '2']);
                        }
                    }
                }
                elseif($request->address_type == '2') {

                    $bill_address = ShippingAddress::select('address_type')->where(['user_id'=>$userid, 'is_default' => '1'])->whereIn('address_type', ['2','3'])->first();
                    if(!empty($bill_address)) {
                        if($bill_address->address_type == '2') {
                            ShippingAddress::where(['address_type'=>'2', 'user_id'=>$userid])->update(['address_type' => '0', 'is_default' => '0']);
                        }
                        elseif($bill_address->address_type == '3'){
                            ShippingAddress::where(['address_type'=>'3', 'user_id'=>$userid])->update(['address_type' => '1']);
                        }
                    }                
                } 

                ShippingAddress::where(['id'=>$request->address_id, 'user_id'=>$userid])->update(['address_type' => $request->address_type, 'is_default' => '1']);
            }

            return 'success';                                          
        }        
    }

    public function favoriteShop(Request $request){
        $userid = Auth::User()->id;
        $favoriteShopList = \App\FavoriteShop::where('user_id',$userid)->with(['getShops'=>function($shop_sub_qry){
                $shop_sub_qry->with('shopDesc');
        }])->orderBy('add_date','desc')->get();

        $shop_res = [];
        foreach ($favoriteShopList as $key => $value) {
            $category_name = '';

            $mongoShopData = \App\MongoShop::where(['status'=>'1','shop_url'=>$value->getShops->shop_url])->first();

            if(isset($mongoShopData->shop_category) && count($mongoShopData->shop_category)){
                $category_data = \App\MongoCategory::whereIn('_id',$mongoShopData->shop_category)->pluck('category_name')->toArray();
                if($category_data){
                    $category_name = implode(',', $category_data);
                }
            }

            $lastUpdatedProduct = \App\MongoProduct::where(['shop_id'=>$value->shop_id])->with('category')->orderBy('updated_at','DESC')->first();

            if(!is_null($lastUpdatedProduct)){
                $date = new \DateTime($lastUpdatedProduct->updated_at);

                $shop_res[$key]['last_updated_price'] = '<span class="date">'.$date->format("d/m/Y H:i").'</span>
                                            <span class="update-price"><i class="fas fa-long-arrow-alt-right"></i> Update '.$lastUpdatedProduct->category->category_name.'</span>';
            }else{
                $shop_res[$key]['last_updated_price'] = 'NA';
            }

            $shop_res[$key]['shop_slug'] = $value->getShops->shop_url;
            $shop_res[$key]['shop_category'] = $category_name;
            $shop_res[$key]['logo'] = getImgUrl($value->getShops->logo,'logo');
            $shop_res[$key]['shop_url'] = action('ShopController@index',$value->getShops->shop_url);
            $shop_res[$key]['shop_name'] = isset($value->getShops->shopDesc->shop_name)?$value->getShops->shopDesc->shop_name : 'NA';
            $shop_res[$key]['market'] = isset($value->getShops->seller_description)? $value->getShops->seller_description : 'NA';
            $shop_res[$key]['avg_rating'] = isset($value->getShops->avg_rating)?(int)$value->getShops->avg_rating:0;
            $shop_res[$key]['del_f_shop_url'] = action('User\UserController@deleteFavoriteShop',$value->getShops->id);

        }

        return view('user.favorite_shop',['favoriteShopList'=>$shop_res]);
    }   

    public function deleteFavoriteShop($shop_id){
        try{
            \App\FavoriteShop::where(['shop_id'=>$shop_id,'user_id'=>Auth::user()->id])->delete();
            $return = ['status'=>'success','message'=>Lang::get('customer.favorite_shop_removed_successfully')];
        }
        catch(Exception $e){
            $return = ['status'=>'success','message'=>$e->getMessage()];
        }
        
        return $return;
    }         
}