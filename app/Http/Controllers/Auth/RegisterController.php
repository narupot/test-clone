<?php

namespace App\Http\Controllers\Auth;

use DB;
use Mail;
use App\User;
use Validator;
use App\Http\Controllers\MarketPlace;
use App\Helpers\GeneralFunctions;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use App\Mail\EmailVerification;
use App\Currency;
use App\Helpers\EmailHelpers;
use Lang;
use Session;
use Config;
use Exception;
use App\FacebookProfile;
use App\Country;

class RegisterController extends MarketPlace {
    /*
      |--------------------------------------------------------------------------
      | Register Controller
      |--------------------------------------------------------------------------
      |
      | This controller handles the registration of new users as well as their
      | validation and creation. By default this controller uses a trait to
      | provide this functionality without requiring any additional code.
      |
     */

    use RegistersUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    //protected $redirectTo = '/user/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $tblUser;
    public function __construct() {
        $this->middleware('guest', ['except' => ['checkUnique']]);
        $this->tblUser = with(new User)->getTable();
    }
    

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */

    public function index(Request $request){
        
        $default_group_id = \App\CustomerGroup::select('id')->where(['is_default'=>'1','status'=>'1'])->first();

        $referer_url = $request->headers->get('referer');
        $breadcrumb = $this->getBreadcrumb($referer_url);

        $facebook_arr = []; 
        if(isset($request->appid) && $request->appid !=''){ 
            $facebookdata = FacebookProfile::where('facebook_id',$request->appid)->first(); 
            if(!empty($facebookdata)){ 
                $facebook_arr = json_decode($facebookdata->json_data); 
                //dd($facebook_arr); 
            }else{ 
              return redirect(action('Auth\RegisterController@index')); 
            } 
        }

        return view(loadFrontTheme('auth.register'),['page'=>'register','breadcrumb'=>$breadcrumb,'facebook_arr'=>$facebook_arr]);
    }

    /****when user click on seller register link*******/
    public function sellerRegister(Request $request){
      
        $default_group_id = \App\CustomerGroup::select('id')->where(['is_default'=>'1','status'=>'1'])->first();

        $referer_url = $request->headers->get('referer');
        $breadcrumb = $this->getBreadcrumb($referer_url);

        $facebook_arr = []; 
        if(isset($request->appid) && $request->appid !=''){ 
            $facebookdata = FacebookProfile::where('facebook_id',$request->appid)->first(); 
            if(!empty($facebookdata)){ 
                $facebook_arr = json_decode($facebookdata->json_data); 
                //dd($facebook_arr); 
            }else{ 
              return redirect(action('Auth\RegisterController@index')); 
            } 
        }

        $step = 'register';
        $step_heading = Lang::get('auth.register');
        $done_step = 1;
        return view(loadFrontTheme('auth.seller_register'),['page'=>'register','breadcrumb'=>$breadcrumb,'facebook_arr'=>$facebook_arr,'step'=>$step,'step_heading'=>$step_heading,'done_step'=>$done_step]);
    }

    /***when user click login link from header****/
    public function login(Request $request){
      
        $referer_url = $request->headers->get('referer');
        $breadcrumb = $this->getBreadcrumb($referer_url);
        
        return view('auth.login',['page'=>'login','breadcrumb'=>$breadcrumb]);
    }

    /***when user click verify link ****/
    public function verifyUser(Request $request){
        $use = $request->use ?? '';
        $useval = $request->val ?? '';
        return view('auth.user_verify',['page'=>'login','use'=>$use,'useval'=>$useval]);
    }
    
    /***when user submit register form******/
    public function insert(Request $request) {
        
        //$input = Input::all();
		$input = $request->all();
        //echo "<pre>"; print_r($input); die;
        $default_group_id = \App\CustomerGroup::select('id','require_approve')->where(['is_default'=>'1','status'=>'1'])->first();

        $group_id = $default_group_id->id;


        if(!isset($request->terms_condition)){
          $input['terms_condition'] = '';
          $rules['terms_condition'] = userRequireRule('checkbox');
          $error_msg['terms_condition.required'] = Lang::get('common.shinup_terms_and_condition_rule');
        }

        $rules['first_name'] = nameRule();
        $rules['last_name'] = nameRule();
        if(isset($request->loginuse) && $request->loginuse=='email'){
            $rules['email'] = emailRule($this->tblUser, 'email');
        }elseif(isset($request->loginuse) && $request->loginuse=='ph_no'){
            $rules['ph_number'] = phoneRule($this->tblUser, 'ph_number');
        }else{
            $rules['loginuse'] = reqRule();
        }
        
        if(!isset($request->facebook_id) && empty($request->facebook_id)){ 
            $rules['password'] = passwordRule();
            $rules['password_confirm'] = confirmPasswordRule('password');
        }  
        if(isset($request->dob) && $request->dob !=''){
            $rules['dob'] = dateRule('date');
        }         

        $error_msg['first_name.required'] = Lang::get('customer.enter_first_name');
        $error_msg['last_name.required'] = Lang::get('customer.enter_last_name');
        $error_msg['first_name.min'] = Lang::get('customer.first_name_minimum_3_character');
        $error_msg['last_name.min'] = Lang::get('customer.enter_last_name_minimum_3_character');
        $error_msg['email.required'] = Lang::get('customer.please_enter_email');
        $error_msg['password.required'] = Lang::get('customer.please_enter_password');
        $error_msg['password_confirm.required'] = Lang::get('customer.password_and_confirm_password_should_be_same');
        $error_msg['password_confirm.same'] = Lang::get('customer.password_and_confirm_password_should_be_same'); 
        $error_msg['ph_number.required'] = Lang::get('customer.please_enter_phone_no');
        $error_msg['ph_number.digits'] = Lang::get('customer.phone_no_must_be_10_digits');
        $error_msg['email.unique'] = Lang::get('customer.email_already_exist'); 
        $error_msg['ph_number.unique'] = Lang::get('customer.phone_number_already_exist');
        unset($input['_token']);

        $validate = Validator::make($input, $rules, $error_msg);
        //dd($validate);
        if ($validate->passes()) {

            $dob = $request->year.'-'.$request->month.'-'.$request->date;
            
            // The following code will check if email verification is set Yes or No
            $default_email_setting = \App\SystemConfig::where('system_name','EMAIL_VERIFICATION')->first();
            
            $ph_otp = $email_token = '';

            $user = new User;
            $user->register_from = 'website';

            if($request->loginuse == 'ph_no'){
                $ph_number = $request->ph_number;
                $otp_response = $this->sendOtp($ph_number);
                if($otp_response['status'] == 'success'){
                    $token = $otp_response['token'];
                    $user->phone_otp_token = $token;
                    $user->otp_generated_at = currentDateTime();

                }else{
                    return jsonEncode(['success'=>false,'message'=>Lang::get('customer.phone_otp_error'),'error'=>$otp_response['msg'],'type'=>'otp']);
                }

                $loginuse_val = $request->ph_number;
            }else{
                //$email_token = str_random(10);
				$email_token = Str::random(10);
                $email_token = generateOTP();
                $loginuse_val = $request->email;
            }

            
            if(!empty($request->email) && $request->loginuse=='email'){
                $user->email = cleanValue($request->email);
            }
            
            if(!empty($request->ph_number) && $request->loginuse=='ph_no'){
                $user->ph_number = cleanValue($request->ph_number);
            }
            
            $user->login_use = $request->loginuse;

            if(isset($request->facebook_id) && !empty($request->facebook_id)){ 
              $user->password = ''; 
            }else{ 
              $user->password = bcrypt($request->password); 
            }
            $user->first_name = cleanValue($request->first_name);
            $user->last_name = cleanValue($request->last_name);
            $user->display_name = $user->first_name.' '.$user->last_name;
            //$user->gender = $request->gender;
            $user->dob = date('Y-m-d', strtotime($dob));

            $user->register_ip = userIpAddress();

            $user->group_id = $group_id;

            $user->facebook_id = (isset($request->facebook_id) && !empty($request->facebook_id)) ? $request->facebook_id : '';

            if($request->loginuse=='email' && $default_email_setting->system_val=='No'){
              $user->verified = '1';
              $user->email_token = '';
              $user->register_step = 1;
              $user->status = '1';
              $email_required = 'no';
              $otp_required = 'no';
            }else{
              $user->email_token = $email_token;
              $user->status = '1';
              $user->phone_otp = $ph_otp;
              $user->verified = '0';
              $user->register_step = '0';
              $email_required = 'yes';
              $otp_required = 'yes';
            }

            $user->save();

            $user_id = $user->id;
            
            $lang_id = session('default_lang');

            if($request->loginuse=='email' && $default_email_setting->system_val=='Yes'){

                try {
                    $emailReplaceData = [];
                    $emailReplaceData['USER_NAME'] = $request->first_name.' '.$request->last_name;
                    $emailReplaceData['EMAIL'] = $request->email;
                    $emailReplaceData['PASSWORD'] = $request->password;
                    $emailReplaceData['VERIFY_URL'] = action('Auth\RegisterController@verify', $email_token);
                    $emailReplaceData['VERIFY_CODE'] = $email_token;
                    
                    //is_cron => "1" send email by cron, "2" send email by direct
                    //user_type => "user" means send to user, "admin" means send to Admin
                    $emailData = ['lang_id'=>$lang_id, 'relevantdata'=>$emailReplaceData,'user_email'=>$user->email, 'is_cron' => 2 , 'user_type' => 'user'];

                    $event_slug = 'buyer_register_mail';
                    EmailHelpers::sendAllEnableNotification($event_slug, $emailData);
                    $message =  str_replace( '[USER_EMAIL]', $request->email, Lang::get('auth.email_has_been_send_content'));
                    $email_msg = "yes";
                }
                catch(Exception $e){
                    echo $e; die;
                }

            }else{

                $message = "You are register successfully with us. Please click <a href = '".action('Auth\RegisterController@login')."'>Login</a>";
                $email_msg = "no";
            }

            $user_info = User::where('id',$user_id)->select('id','login_use')->first();

            $step = 'waiting_confirm';
            $step_heading = Lang::get('auth.please_confirm_the_registration');
            $done_step = 2;
            $register_by = $request->register_by;
            $blade = (String) view('auth.otp',['register_by'=>$register_by,'user_info'=>$user_info,'step'=>$step,'step_heading'=>$step_heading,'done_step'=>$done_step]);
            if($register_by == 'buyer'){
                $url = route('buyerVerify', $user_id);
            }else{
                $url = route('sellerVerify', $user_id);
            }
            //$url  = action('Auth\RegisterController@verifyOtp',$user_id);

            return jsonEncode(['success'=>true,'user_id'=>$user_id,'email_required'=>$email_required,'register_user_by'=>$request->loginuse,'register_user_by_val'=>$loginuse_val,'url'=>$url,'blade'=>$blade]);

        }else{
            
            $errors =  $validate->errors(); 
            //dd($errors);
            $email_msg = "no";
            return jsonEncode(array('success'=>false,'message'=>$errors,'email'=>$email_msg,'type'=>'validation'));
        }
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

    /************
    **** Once user submit register form verify by email or opt function call.
    **** It will show one form for confirm otp or request otp
    ***/

    public function verifyOtp(Request $request,$id) {

        $user = User::where(['id'=>$id,'verified'=>0])->first();
        
        if(!empty($user)){
            $message = Lang::get('auth.account_verification');
            $current_url = \URL::current();
            if(strpos($current_url, 'seller-register') !== false){
                $register_by = 'seller';
            }else{
                $register_by = 'buyer';
            }
            $referer_url = $request->headers->get('referer');

            $breadcrumb = $this->getBreadcrumb($referer_url);
            $step = 'waiting_confirm';
            $step_heading = Lang::get('auth.please_confirm_the_registration');
            $done_step = 2;
            return view('auth.register_otp',['page'=>'register','breadcrumb'=>$breadcrumb,'register_by'=>$register_by,'user_info'=>$user,'step'=>$step,'step_heading'=>$step_heading,'done_step'=>$done_step]);

        }else{
          abort(404);
        }    
    }
    
    public function verify($token) {

        $user = User::where('email_token', $token)->first();
        if(!empty($user)){
            $message = Lang::get('auth.account_verification');
            User::where('email_token', $token)->firstOrFail()->verified();

            //code to send email to user
            try {
                $lang_id = session('default_lang');
                $emailReplaceDataUser['USER_NAME'] = $user->display_name;
                $emailReplaceDataUser['USER_LOGIN_URL'] = action('Auth\RegisterController@login');
                $emailReplaceDataUser['USER_MY_ACCOUNT_URL'] = action('User\UserController@index');

                $emailData = ['lang_id'=>$lang_id, 'relevantdata'=>$emailReplaceDataUser, 'user_email'=>$user->email, 'is_cron' => 2 , 'user_type' => 'user'];
                $event_slug = 'buyer_confirmation_mail';
                //code to send email ended

                //code to send email to admin
                $emailReplaceDataAdmin['USER_NAME'] = $user->display_name;
                $emailReplaceDataAdmin['USER_EMAIL'] = $user->email;
                $emailReplaceDataAdmin['ADMIN_LOGIN_URL'] = action('Admin\AdminHomeController@index');
                $emailDataAdmin = ['lang_id'=>$lang_id, 'relevantdata'=>$emailReplaceDataAdmin, '', 'is_cron' => 2 , 'user_type' => 'admin'];
                $event_slug_admin = 'buyer_confirmation_mail_to_admin';
                   
                EmailHelpers::sendAllEnableNotification($event_slug, $emailData);
                EmailHelpers::sendAllEnableNotification($event_slug_admin, $emailDataAdmin);
            
            } catch (Exception $e) {
                echo $e; die;
                $error = true;
            }
          
          //code to send email ended   

          return redirect()->action('Auth\RegisterController@login')->with('verify_msg', $message);
        }else{
          abort(404);
        }    
    }
    
    public function checkUnique(Request $request){
         
          $data = User::where('email', $request->email)->first();
          //dd($data);
          if(isset($data->email) && !empty($data->email) ){
             if($data->user_type == 'buyer'){
               if($data->verified == '0' && !empty($data->email_token)){
                   
                   echo 'notverified';

               }else if($data->verified == '1' && empty($data->email_token)){
                    echo 'verified';

               }else{

                   echo 'false';

               }
             }else if($data->user_type == 'seller'){
               if($data->seller_request_status == '1'){
                   
                   echo 'pending';

                }else if($data->seller_request_status == '2'){
                  
                  echo 'rejected';

                }else if($data->seller_request_status == '3'){

                    echo 'approved';

               }else{

                   echo 'false';

               }
             }

            
          }else{
            echo 'true';  
          }
          
        exit;            
    }

    /**when user request for new otp**/
    public function requestOtp(Request $request){

        $user_id = !empty($request->id)?$request->id:0;
        $user_det = User::where('id',$user_id)->first();

        if(!empty($user_det)){
            $use_by = !empty($request->use_by)?$request->use_by:$user_det->login_use;
            if($use_by == 'ph_no'){
                $ph_number = $user_det->ph_number;
                $otp_response = $this->sendOtp($ph_number);
                if($otp_response['status'] == 'success'){
                    $token = $otp_response['token'];
                    $updateotp = User::where('id',$user_id)->update(['phone_otp_token'=>$token,'otp_generated_at'=>currentDateTime()]);
                    return ['status'=>'success'];
                }else{
                    return ['status'=>'fail','msg'=>Lang::get('customer.phone_otp_error'),'error'=>$otp_response['msg']];
                }
            }else{
                return $this->sendOtpToEmail($user_det);
            }
            
        }else{
            $response = ['status'=>'fail','msg'=>Lang::get('customer.invalid_user')];
        }
        return $response;
    }


    /**when user submit otp for confirm registration***/
    public function confirmOtp(Request $request){
        $user_id = !empty($request->id)?$request->id:0;
        $user_det = User::where('id',$user_id)->first();
        $otp = isset($request->otp)?$request->otp:'';
        
        if(!empty($user_det) && $otp){
            $redirect_url = Action('Auth\SellerRegisterController@index',$user_det->id);
            $use_by = !empty($request->use_by)?$request->use_by:$user_det->login_use;
            if($use_by == 'ph_no'){
                $otp_response = $this->matchOtp($user_det->phone_otp_token,$otp);
                if(Config::get('constants.localmode') == true){
                    $otp_response['status'] = 'success';
                }
                if($otp_response['status'] == 'success'){
                    $this->otpUpdateUser($user_det);
                    
                    $response = ['status'=>'success','url'=>$redirect_url];
                }else{
                    $response = ['status'=>'fail','msg'=>Lang::get('customer.otp_missmatch'),'error'=>$otp_response];
                }
            }else{
                if($user_det->email_token == $otp){
                    $this->otpUpdateUser($user_det);
                    
                    $response = ['status'=>'success','url'=>$redirect_url];
                }else{
                    $response = ['status'=>'fail','msg'=>Lang::get('customer.otp_missmatch'),'error'=>$use_by];
                }
            }
            
            
        }else{
            $response = ['status'=>'fail','msg'=>Lang::get('customer.invalid_user')];
        }
        return $response;
    }

    protected function otpUpdateUser($user_det){
        if($user_det->verified == 0){
            $updateotp = User::where('id',$user_det->id)->update(['register_step'=>1,'verified'=>1,'phone_otp_token'=>'','email_token'=>'']);
        }else{
             $updateotp = User::where('id',$user_det->id)->update(['phone_otp_token'=>'','email_token'=>'']);
        }
    }

}
