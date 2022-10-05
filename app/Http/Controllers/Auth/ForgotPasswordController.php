<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;
use App\Notifications\Notifications;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use App\Notifications\ResetPassword;
use Lang;
use DB;
use Config;
use App\User;

class ForgotPasswordController extends MarketPlace {
    /*
      |--------------------------------------------------------------------------
      | Password Reset Controller
      |--------------------------------------------------------------------------
      |
      | This controller is responsible for handling password reset emails and
      | includes a trait which assists in sending these notifications from
      | your application to your users. Feel free to explore this trait.
      |
     */
use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('guest');
    }


    public function sendResetLinkEmail(Request $request) {
     
        if($request->ajax()) {

            if($request->find_by_use == 'ph_no'){
                $phone_no = !empty($request->phone_no)?$request->phone_no:'';
                $user_det = User::where('ph_number',$phone_no)->first();
                if(!empty($user_det) && $phone_no){
                    $otp_response = $this->sendOtp($request->phone_no);
                    
                    if(Config::get('constants.localmode') == true){
                        $otp_response['status'] = 'success';
                        $otp_response['msg'] = '';
                        $otp_response['token']  = '';
                    }
                    if($otp_response['status'] == 'success'){
                        $token = $otp_response['token'];
                        $updateotp = User::where('ph_number',$request->phone_no)->update(['phone_otp_token'=>$token,'otp_generated_at'=>currentDateTime()]);
                        return ['status'=>'success','user_id'=>$user_det->id,'msg'=>Lang::get('customer.please_enter_4_digit_code_recieved_on_your_mobile')];
                    }else{
                        return ['status'=>'fail','msg'=>Lang::get('customer.phone_otp_error'),'error'=>$otp_response['msg']];
                    }
                    
                }else{
                    return ['status'=>'fail','msg'=>Lang::get('customer.phone_number_not_exist')];
                }
            }else{
                $email = !empty($request->email)?$request->email:'';
                $user_det = User::where('email',$email)->first();
                if(!empty($user_det) && $email){
                    return $this->sendOtpToEmail($user_det);
                }
                return ['status'=>'fail','msg'=>Lang::get('customer.email_not_exist')];
            }
            
            //$this->validate($request, ['email' => 'required|email'], $this->message());
            //print_r($request->only('email')); die;
            /*$response = $this->broker()->sendResetLink(
                    $request->only('email')
            );
              
            if($response == 'passwords.sent'){
               return array('status'=>'success','msg'=>'Mail Sent'); 
            }else{
               return array('status'=>'fail','msg'=>'Invalid Email address'); 
            }
            
            exit;*/
            
        }
    }

    public function messages() {
        return [
            'email.required' => Lang::get('authorization.email_required'),
            'email.email' => Lang::get('authorization.email_email'),
        ];
        exit;
    }

}
