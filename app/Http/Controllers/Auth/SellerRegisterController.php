<?php

namespace App\Http\Controllers\Auth;

use DB;
use Mail;
use App\User;
use App\Shop;
use App\ShopDesc;
use App\SellerTemp;
use App\Seller;
use App\SellerData;
use App\PaymentBank;
use Validator;
use App\Http\Controllers\MarketPlace;
use App\Helpers\GeneralFunctions;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Database\QueryException;
use App\Mail\EmailVerification;
use App\Helpers\EmailHelpers;
use Lang;
use Session;
use Config;
use Exception;

class SellerRegisterController extends MarketPlace {
    private $tblSellerTemp;
    public function __construct() {
        $this->tblSellerTemp = with(new SellerTemp)->getTable();
    }
    
    public function index(Request $request,$id){
        $user_info = User::checkVerify($id);
        $check_seller = Seller::checkSellerData($id);
        if(!empty($user_info) && empty($check_seller)){

            $step = 'shop_info';
            $step_heading = Lang::get('shop.shop_information');
            $done_step = 3;
            $temp_data = SellerTemp::where('user_id',$user_info->id)->first();
            if($temp_data){
                if($temp_data->step>1){
                    return redirect(action('Auth\SellerRegisterController@accountInfo',$user_info->id));
                }
            }
           
            return view('auth.seller_shop_info',['page'=>'register','user_info'=>$user_info,'step'=>$step,'step_heading'=>$step_heading,'done_step'=>$done_step,'temp_data'=>$temp_data]);
        }else{
            abort(404);
        }
    }
    
    public function accountInfo(Request $request,$id){
        $user_info = User::checkVerify($id);
        $check_seller = Seller::checkSellerData($id);
        if(!empty($user_info)  && empty($check_seller)){

            $step = 'bank_info';
            $step_heading = Lang::get('shop.bank_information');
            $done_step = 4;
            $temp_data = SellerTemp::where(['user_id'=>$user_info->id,'status'=>'0'])->first();
            
            if($temp_data){
                $bank_list = PaymentBank::activeBankList();

                return view('auth.seller_account_info',['page'=>'register','user_info'=>$user_info,'step'=>$step,'step_heading'=>$step_heading,'done_step'=>$done_step,'temp_data'=>$temp_data,'bank_list'=>$bank_list]);
            }else{
                return redirect(action('Auth\RegisterController@login'));
            }
            
        }else{
            abort(404);
        }
    }

    public function getBranchList(Request $request){
        $bank_id = $request->bank_id;
        $branch_data = \App\PaymentBankBranch::where('payment_bank_id',$bank_id)->with('branchName')->get();
        if($branch_data){
            return ['status'=>'success','data'=>$branch_data];
        }else{
            return['status'=>'fail','msg'=>Lang::get('common.something_went_wrong')];
        }
    }

    public function thanks(Request $request,$id){
        $user_info = User::checkVerify($id);

        if(!empty($user_info)){

            $step = 'done';
            $step_heading = Lang::get('common.you_have_already_registered');
            $done_step = 5;
            $temp_data = SellerTemp::where(['user_id'=>$user_info->id,'status'=>'1'])->first();

            if($temp_data){
                return view('auth.seller_thanks',['page'=>'register','user_info'=>$user_info,'step'=>$step,'step_heading'=>$step_heading,'done_step'=>$done_step,'temp_data'=>$temp_data]);
            }else{
                return redirect(action('Auth\SellerRegisterController@index',$user_info->id));
            }
            
        }else{
            abort(404);
        }
    }

    public function checkCitizenId(Request $request){

        $citizen_id = (isset($request->citizen_id) && trim($request->citizen_id))?trim($request->citizen_id):'';
        $user_id = isset($request->user_id)?$request->user_id:0;
        if($citizen_id && $user_id){
            $citizen_exist = SellerData::where('citizen_id',$citizen_id)->count();
            if(empty($citizen_exist)){
                return['status'=>'fail','msg'=>Lang::get('shop.invalid_citizen_id')];
            }

            $check_exist = SellerTemp::checkCitizen($citizen_id,$user_id);
            /***check from shop******/
            $check_citizen = \App\Seller::checkCitizenId($citizen_id,$user_id);
            if($check_exist > 0 || $check_citizen>0){
                $response = ['status'=>'fail','msg'=>Lang::get('shop.citizen_id_already_exist')];
            }else{
                $response = ['status'=>'success'];
            }
        }else{
            $response = ['status'=>'fail','msg'=>Lang::get('shop.citizen_id_required')];
        }
        return $response;
    }

    public function checkPanelNo(Request $request){

        $panel_no = (isset($request->panel_no) && trim($request->panel_no))?trim($request->panel_no):'';
        $user_id = isset($request->user_id)?$request->user_id:0;
        if($panel_no && $user_id){
            $panel_exist = SellerData::where('panel_id',$panel_no)->count();
            if(empty($panel_exist)){
                return['status'=>'fail','msg'=>Lang::get('shop.invalid_panel_no')];
            }

            $check_exist = SellerTemp::checkPanel($panel_no,$user_id);
            /***check from shop******/
            $check_panel = \App\Shop::checkPanelNo($panel_no,$user_id);
            if($check_exist > 0 || $check_panel>0){
                $response = ['status'=>'fail','msg'=>Lang::get('shop.panel_no_already_exist')];
            }else{
                $response = ['status'=>'success'];
            }
        }else{
            $response = ['status'=>'fail','msg'=>Lang::get('shop.panel_no_required')];
        }
        return $response;
    }

    public function checkStoreName(Request $request){

        $store_name = (isset($request->store_name) && trim($request->store_name))?trim($request->store_name):'';
        $user_id = isset($request->user_id)?$request->user_id:0;
        if($store_name && $user_id){
            $check_exist = SellerTemp::checkShopName($store_name,$user_id);
            /***check from shop******/
            $check_shop = \App\Shop::checkShopName($store_name,$user_id);
            if($check_exist > 0 || $check_shop>0){
                $response = ['status'=>'fail','msg'=>Lang::get('shop.store_name_already_exist')];
            }else{
                $store_url = createUrl($store_name);
                $check_shop = \App\Shop::checkShopUrl($store_url,$user_id);
                if($check_shop > 0){
                    $store_url = $store_url.substr(str_shuffle("0123456789"), 0, 3);
                }
                $response = ['status'=>'success','store_url'=>$store_url];
            }
        }else{
            $response = ['status'=>'fail','msg'=>Lang::get('shop.store_name_required')];
        }
        return $response;
    }

    public function checkStoreUrl(Request $request){

        $store_url = (isset($request->store_url) && trim($request->store_url))?createUrl(trim($request->store_url)):'';
        $user_id = isset($request->user_id)?$request->user_id:0;
        if($store_url && $user_id){
            $store_url = createUrl($store_url);
            $check_exist = SellerTemp::checkShopUrl($store_url,$user_id);
            /***check from shop******/
            $check_shop = \App\Shop::checkShopUrl($store_url,$user_id);
            if($check_exist > 0 || $check_shop > 0){
                $response = ['status'=>'fail','msg'=>Lang::get('shop.store_url_already_exist')];
            }else{

                $response = ['status'=>'success'];
            }
        }else{
            $response = ['status'=>'fail','msg'=>Lang::get('shop.store_url_required')];
        }
        return $response;
    }

    public function checkPanelCitizen($request){
        $panel_citizen = SellerData::where(['panel_id'=>$request->panel_no,'citizen_id'=>$request->citizen_id])->count();
        if(empty($panel_citizen)){
            return['status'=>'fail','msg'=>Lang::get('shop.seller_not_found_on_this_panel_citizen_id')];
        }

        $check_exist = SellerTemp::where(['panel_no'=>$request->panel_no,'citizen_id'=>$request->citizen_id])->where('user_id','!=',$request->user_id)->count();
        $check_shop = \App\Shop::where(['panel_no'=>$request->panel_no,'citizen_id'=>$request->citizen_id])->where('user_id','!=',$request->user_id)->count();

        if($check_exist > 0 || $check_shop>0){
            $response = ['status'=>'fail','msg'=>Lang::get('shop.this_panel_and_citizen_id_already_exist')];
        }else{
            $response = ['status'=>'success'];
        }
        return $response;

    }

    /****insert basic shop info******/
    public function insertShopInfo(Request $request) {

        //$input = Input::all();
		$input = $request->all();
        $check_panel = $this->checkPanelNo($request);

        //dd($check_panel);

        $rules['panel_no'] = reqRule();
        $rules['store_url'] = reqRule();
        $rules['store_name'] = reqRule();
        $rules['citizen_id'] = reqRule();
        if(!empty($request->citizen_id_image)){
            $rules['citizen_id_image'] = imageRule();
        }
       
        $error_msg['panel_no.required'] = Lang::get('shop.panel_no_is_required');
        $error_msg['store_url.required'] = Lang::get('shop.store_url_is_required');
        $error_msg['store_name.required'] = Lang::get('shop.store_name_is_required');
        $error_msg['citizen_id.required'] = Lang::get('shop.citizen_id_is_required');
        $error_msg['citizen_id_image.required'] = Lang::get('shop.citizen_id_image_is_required'); 
        $error_msg['store_url.unique'] = Lang::get('shop.store_url_already_exist');
        $error_msg['store_name.unique'] = Lang::get('shop.store_name_already_exist');
        unset($input['_token']);

        $validate = Validator::make($input, $rules, $error_msg);
        
        if ($validate->passes()) {
            /***checking unique********/
            $check_panel_citizen = $this->checkPanelCitizen($request);
            //dd($check_panel_citizen);
            if(isset($check_panel_citizen['status']) && $check_panel_citizen['status']=='fail'){
                return $check_panel_citizen;
            }

            $check_store = $this->checkStoreUrl($request);
            if(isset($check_store['status']) && $check_store['status']=='fail'){
                return $check_store;
            }
            $check_store_name = $this->checkStoreName($request);
            if(isset($check_store_name['status']) && $check_store_name['status']=='fail'){
                return $check_store_name;
            }
            
            try {
                $seller_temp = SellerTemp::where('user_id',$request->user_id)->first();
                if(empty($seller_temp)){
                    $seller_temp = new SellerTemp;
                }
                
                $seller_temp->user_id = $request->user_id;
                $seller_temp->panel_no = cleanValue($request->panel_no);
                $seller_temp->shop_name = cleanValue($request->store_name);
                $seller_temp->shop_url = createUrl($request->store_url);
                $seller_temp->citizen_id = cleanValue($request->citizen_id);
                if(!empty($request->citizen_id_image)){
                    $uploadDetails['path'] = Config::get('constants.seller_img_path');
                    $uploadDetails['file'] =  $request->citizen_id_image;
                    $fileName = $this->uploadFileCustom($uploadDetails);
                    
                    if(!empty($seller_temp) && !empty($seller_temp->citizen_id_image)){
                        $this->fileDelete(Config::get('constants.seller_img_path').'/'.$seller_temp->citizen_id_image);
                    }
                    $seller_temp->citizen_id_image = $fileName;
                }

                $seller_temp->step = 1;

                $seller_temp->save();
                $redirect_url = action('Auth\SellerRegisterController@accountInfo',$request->user_id);
                $response = ['status'=>'success','url'=>$redirect_url];

            }catch(Exception $e){
                $response = ['status'=>'fail','msg'=>$e->getMessage(),'error'=>''];
            }

        }else{

            $errors =  $validate->errors(); 
            $response = ['status'=>'fail','msg'=>$errors,'error'=>'validation'];
        }
        return $response;
    }

    /***insert account info***
    ****if user submit correct data then seller data insert in seller and shop table
    ****/
    public function insertAccountInfo(Request $request) {
        
        //$input = Input::all();
		$input = $request->all();
        $rules['bank_id'] = reqRule();
        //$rules['branch_id'] = reqRule();
        $rules['account_name'] = nameRule();
        $rules['account_no'] = numericRule('r');
        $rules['branch'] = nameRule();
        $rules['branch_code'] = reqRule();
        //$rules['account_image'] = imageRule();
        
        $error_msg['bank_id.required'] = Lang::get('shop.bank_is_required');
        $error_msg['branch_id.required'] = Lang::get('shop.branch_is_required');
        $error_msg['account_name.required'] = Lang::get('shop.account_name_is_required');
        $error_msg['account_no.required'] = Lang::get('shop.account_no_is_required');
        $error_msg['branch.required'] = Lang::get('shop.branch_is_required');
        $error_msg['branch_code.required'] = Lang::get('shop.branch_code_is_required');
        $error_msg['account_image.required'] = Lang::get('shop.account_image_is_required');

        unset($input['_token']);

        $validate = Validator::make($input, $rules, $error_msg);
        
        if ($validate->passes()) {
            try {

                /****checking store name, url , panel_no and citizen id******/
                $user_id = $request->user_id;
                $seller_temp = SellerTemp::where('user_id',$request->user_id)->first();

                $request->panel_no = $seller_temp->panel_no;
                $request->store_url = $seller_temp->shop_url;
                $request->citizen_id = $seller_temp->citizen_id;
                $request->store_name = $seller_temp->shop_name;

                $check_panel_citizen = $this->checkPanelCitizen($request);
                if(isset($check_panel_citizen['status']) && $check_panel_citizen['status']=='fail'){
                    return $check_panel_citizen;
                }
                $check_store = $this->checkStoreUrl($request);
                if(isset($check_store['status']) && $check_store['status']=='fail'){
                    return $check_store;
                }
                $check_store_name = $this->checkStoreName($request);
                if(isset($check_store_name['status']) && $check_store_name['status']=='fail'){
                    return $check_store_name;
                }

                /****if shop name and url pass*****/
                $seller_temp->bank_id = $request->bank_id;
                if ($request->branch_id) {
                    $branch_arr = explode("##", $request->branch_id);
                    $seller_temp->bank_branch_id = $branch_arr[0];
                } else {
                    $pay_bank = new \App\PaymentBankBranch();
                    $pay_bank->payment_bank_id = $request->bank_id;
                    $pay_bank->branch_code = $request->branch_code;
                    $pay_bank->save();
                    $bank_branch_id = $pay_bank->id;
                    $payment_bank_name_arr[] = ['bank_branch_id' => $bank_branch_id, 'lang_id' => 0, 'branch_name' => $request->branch];
                    $payment_bank_name_arr[] = ['bank_branch_id' => $bank_branch_id, 'lang_id' => 1, 'branch_name' => $request->branch];
                    \App\PaymentBankBranchDesc::insert($payment_bank_name_arr);
                    $seller_temp->bank_branch_id = $bank_branch_id;
                }

                $seller_temp->account_name = cleanValue($request->account_name);
                $seller_temp->account_no = cleanValue($request->account_no);
                $seller_temp->branch = createUrl($request->branch);
                $seller_temp->branch_code = $request->branch_code;

                if(!empty($request->account_image)){
                    $uploadDetails['path'] = Config::get('constants.seller_img_path');
                    $uploadDetails['file'] =  $request->account_image;
                    $fileName = $this->uploadFileCustom($uploadDetails);
                    
                    if(!empty($seller_temp) && !empty($seller_temp->account_image)){
                        $this->fileDelete(Config::get('constants.seller_img_path').'/'.$seller_temp->account_image);
                    }
                    $seller_temp->account_image = $fileName;
                }

                $seller_temp->step = 2;

                $seller_temp->save();

                /***insert into original seller and shop table****/

                $seller_insert = SellerTemp::createSeller($request->user_id);

                if($seller_insert['status'] == 'success'){
                    $updateTemp = SellerTemp::where('id',$seller_temp->id)->update(['status'=>'1']);
                    $updateUsertoSeller = User::where('id',$user_id)->update(['user_type'=>'seller']);

                    $shop_info = Shop::where('user_id',$user_id)->with('allDesc')->with('shopUser')->first();

                    /***update shop data into mongo******/
                    $update_data = \App\MongoShop::updateData($shop_info);

                    $request->session()->put('user_shop_id',  $shop_info->id);
                    /***send mail to seller for successfully create shop***/
                    $redirect_url = action('Auth\SellerRegisterController@thanks',$request->user_id);
                    $response = ['status'=>'success','url'=>$redirect_url];
                }else{
                    $response = ['status'=>'fail','msg'=>$seller_insert['msg']];
                }
                
            }catch(Exception $e){
                $response = ['status'=>'fail','msg'=>$e->getMessage(),'error'=>''];
            }

        }else{

            $errors =  $validate->errors(); 
            $response = ['status'=>'fail','msg'=>$errors,'error'=>'validation'];
        }
        return $response;
    }

    
    public function resendverificationlink(Request $request) {
            
        $user = User::where('email', $request->email)->first();

        if(isset($user->email) && !empty($user->email)){
             if($user->verified == '0' && !empty($user->email_token)){
                
                try{
                    $lang_id = $user->default_language;

                    $emailReplaceData = [];
                    $emailReplaceData['USER_NAME'] = $user->first_name;
                    $emailReplaceData['EMAIL'] = $user->email;
                    $emailReplaceData['VERIFY_URL'] = action('Auth\RegisterController@verify', $user->email_token);

                    $emailData = ['lang_id'=>$lang_id, 'relevantdata'=>$emailReplaceData,'user_email'=>$user->email, 'is_cron' => 2 , 'user_type' => 'user'];

                    $event_slug = 're-send_email_to_activate_account';
                    EmailHelpers::sendAllEnableNotification($event_slug, $emailData);
                }
                catch(Exception $e){
                    echo $e; die;
                }

                $data = [];
                $message = Lang::get('auth.resend_verification_link_message_success');
                $data['status'] = 'success';
                $data['message'] = $message; 
                
             }else{
                
               $message = Lang::get('auth.resend_verification_link_unsuccess');
               $data['status'] = 'unsuccess';
               $data['message'] = $message;

             }
        }else{
           
           $message = Lang::get('auth.resend_verification_link_unsuccess');
           $data['status'] = 'fail';
           $data['msg'] = $message;

        }   
        return $data; 
    }

}
