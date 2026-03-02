<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Input;
use Validator;
use Lang;
use App\Helpers\EmailHelpers;
use App\User;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
    
    protected function resetPassword($user, $password)
    {
        $user->forceFill([
            'password' => bcrypt($password),
            'remember_token' => Str::random(60),
        ])->save();

        $this->sendEmailForResetPasswordToUser($user);
        
    }


    
    public function reset(Request $request){
        
       $this->validate($request, $this->rules(), $this->validationErrorMessages());
        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );
        return $response == Password::PASSWORD_RESET ?
          redirect()->action('Auth\RegisterController@login')->with('message',  Lang::get('auth.password_has_been_changed_successfully')):
         $this->sendResetFailedResponse($request, $response);
    }

    protected function sendEmailForResetPasswordToUser($user){
        
        $lang_id = session('default_lang');

        $emailReplaceData['CUSTOMER_FNAME'] = $user->first_name;
        $emailReplaceData['CUSTOMER_LNAME'] = $user->last_name;
        
        $emailData = ['lang_id'=>$lang_id, 'relevantdata'=>$emailReplaceData,'user_email'=>$user->email, 'is_cron' => 2 , 'user_type' => 'user'];

        $event_slug = 'reset_password';
        EmailHelpers::sendAllEnableNotification($event_slug, $emailData);
    }

    public function showResetForm(Request $request)
    {    
       $token = $request->token;
       
       return view(loadFrontTheme('auth.passwords.reset'),['token' => $token]);
       
 
    }

    public function resetPasswordPhone(Request $request)
    {    
        $user_id = !empty($request->user_id)?$request->user_id:0;
        $user_det = User::where('id',$user_id)->first();
        if(!empty($user_det) && $user_id){
			$input = $request->all();
       
            $rules['password'] = passwordRule();
            $rules['password_confirm'] = confirmPasswordRule('password');

            $error_msg['password.required'] = Lang::get('customer.password_is_required');
            $error_msg['password.min'] = Lang::get('customer.password_min_charector');
            $error_msg['password.max'] = Lang::get('customer.password_max_charector');
            $error_msg['password_confirm.min'] = Lang::get('customer.confirm_password_min_charector');
            $error_msg['password_confirm.max'] = Lang::get('customer.confirm_password_max_charector');
            $error_msg['password_confirm.required'] = Lang::get('customer.password_and_confirm_password_should_be_same');
            $error_msg['password_confirm.same'] = Lang::get('customer.password_and_confirm_password_should_be_same');
            //dd($error_msg);
            $validator = Validator::make($input, $rules, $error_msg);

            if ($validator->passes()) {

                $userupdate = User::where('id',$user_det->id)->update(['password'=>bcrypt($request->password)]);
                return ['status'=>'success','msg'=>Lang::get('auth.password_has_been_changed_successfully')];
            }else{
                $msg = '';
                //dd($validator->errors());
                foreach($validator->messages()->getMessages() as $field_name => $messages) {
                    foreach($messages AS $message) {
                       $msg .='<div>'. $message.'</div>';
                    }
                }
                
                return ['status'=>'fail','msg'=>$msg];
            }
        }else{
            return ['status'=>'fail','msg'=>Lang::get('customer.phone_number_not_exist')];
        }
        
    }

}
