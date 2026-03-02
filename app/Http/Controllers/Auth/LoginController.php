<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\MarketPlace;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Contract\Auth as Auth2;
use App\User;

use Session;
use App\FacebookProfile;
use Socialite;
use App\Currency;
use Config;
use Cache;
use Carbon\Carbon;
use App\Helpers\EmailHelpers;

use App\Helpers\GeneralFunctions; 


class LoginController extends MarketPlace {
    /*
      |--------------------------------------------------------------------------
      | Login Controller
      |--------------------------------------------------------------------------
      |
      | This controller handles authenticating users for the application and
      | redirecting them to your home screen. The controller uses a trait
      | to conveniently provide its functionality to your applications.
      |
     */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */

    public  $prefix;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public $auth;
    public function __construct(Auth2 $auth) {
        $this->auth = $auth;
        $this->prefix =  DB::getTablePrefix();
        $this->middleware('guest', ['except' => 'logout']);
    }

    protected function validateLogin(Request $request) {

        $this->validate($request, [
            'login_email_phone' => 'required', 'login_password' => 'required',
                ], $this->messages());
    }

    public function credentials(Request $request) {
        $credential_arr = [];
        if(is_numeric($request->get('login_email_phone'))){
            $credential_arr['ph_number'] =  $request->get('login_email_phone');
        }else{
            $credential_arr['email'] =  $request->get('login_email_phone');
        }
        $credential_arr['password'] = $request->login_password;
        $credential_arr['verified'] = 1;
        $credential_arr['status'] = '1';

        return $credential_arr;
    }

    public function messages() {
        return [
            'login_email_phone.required' => Lang::get('authorization.email_or_phone_required'),
            'login_password.required' => Lang::get('authorization.password_required')
        ];
    }

    protected function sendFailedLoginResponse(Request $request) {
        
        if (!User::where('email', $request->login_email_phone)->where('password', bcrypt($request->password))->first()) {
            return redirect()->action('Auth\RegisterController@login')
                            ->withInput($request->only('email', 'remember'))
                            ->withErrors([
                                'email' => Lang::get('auth.credentials_match'),
            ]);
        }
    }

    protected function checkVerifyEmail($request){
        $return = true;
        $user = User::where('email', $request->login_email_phone)->orWhere('ph_number',$request->login_email_phone)->first();
        return $user;
        /*if(! is_null($user)){
            if($user->verified=='0'){
                $return = false;
            }
        }

        return $return;*/
    }

    public function login(Request $request){
        
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.

        // This section will check if user attempts to login and user not verified email.
        $chk_verify = $this->checkVerifyEmail($request);
        if(!empty($chk_verify)){
            if($chk_verify->verified=='0'){
                $act = action('Auth\RegisterController@verifyUser').'?use='.$chk_verify->login_use.'&val='.$request->login_email_phone;
                return ['status'=>'resend_verify_email','mesg'=>\Lang::get('authorization.your_account_is_not_verify_yet'),'url'=>$act];
            }
        }
        
        /*if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }*/

        if ($this->attemptLogin($request)) {
    
            
            $this->userLogDetail(array('logType'=>'1','email'=>$request->login_email_phone)); 
            $this->getDefaultTimeZone();    // set default timezone to session
            if(isset($request->type) && $request->type=='popup'){
              $request->request->add(['type', 'popup']);
            }
            $resp = $this->sendLoginResponse($request);
            //dd($resp);
            return ['status'=>'success','url'=>$resp['url']];

        }else{
          
          $this->userLogDetail(array('logType'=>'2', 'email'=>$request->login_email_phone, 'password'=>$request->login_password));

       }
       
        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return ['status'=>'fail','mesg'=>\Lang::get('auth.credentials_match')];  

        if(isset($request->type) && $request->type=='popup'){
              return ['status'=>'error','mesg'=>\Lang::get('auth.credentials_match')];  
          }else{
              return $this->sendFailedLoginResponse($request);
        }

        // return $this->sendFailedLoginResponse($request);
    }

    protected function sendLoginResponse(Request $request)
    {    // no need to regeneate it get automatically regenerate once 
        // user attempt for login 
        //$request->session()->regenerate();

        $this->clearLoginAttempts($request);
        $redirect = session('lang_code').$this->redirectPath();

        //$this->setUserTimeZone();    // set user timezone to session

        $request->session()->put('user_group_id',  Auth::User()->group_id);

        $user_id = Auth::id();
        $u_id = Auth::user()->u_id;
        if(empty($u_id)){
            $user_id = Auth::id();
            $u_id = md5('user_'.$user_id);
            \App\User::where('id',$user_id)->update(['u_id'=>$u_id]);
        } 

        if(Auth::User()->user_type == 'seller'){
            $shop_info = \App\Shop::where('user_id',$user_id)->first();
            if(!empty($shop_info)){
                $request->session()->put('user_shop_id',  $shop_info->id);
            }else{
                $update_buyer = \App\User::where('id',$user_id)->update(['user_type'=>'buyer']);
            }
        }

        $email = '';
        $login_use = Auth::user($user_id)->login_use;
        if($login_use == 'ph_no'){
           $email = Auth::user($user_id)->ph_number; 
        }else{
           $email = Auth::user($user_id)->email;
        }

        $additionalClaims = ['username'=> $user_id, 'email'=> $email];
        $customToken = $this->auth->createCustomToken($u_id, $additionalClaims);
        $customTokenString = $customToken->toString();

        \App\User::where('id',$user_id)->update(['chat_token'=>$customTokenString]);
        
        //dd($customTokenString);
        //$request->session()->put('customTokenString',  $customTokenString);
        //session(['customTokenString' => $customTokenString]);
        //dd(session('CustomTokenString'));


        


        $expiresAt = Carbon::now()->addMinutes(720);

        //Redirect to back url
        $redirect = session('back_url');
        if(empty($redirect)) {
          $redirect = $request->session()->get('url.intended');
          //$redirect = action('HomeController@index');
        }
        $request->session()->forget('back_url');

        return ['status'=>'success','url'=>$redirect];
        
    }


    public function redirectToProvider($provider){

      //dd($provider);
      return Socialite::driver($provider)->redirect();
    }


    public function handleProviderCallback($provider)
    {
       $user = Socialite::driver($provider)->user();
       $authUser = $this->findOrCreateUser($user, $provider);
       Auth::login($authUser, true);
       return redirect(action('HomeController@index'));
    }


    public function findOrCreateUser($user, $provider)
    {
        $authUser = User::where('email', $user->email)->first();
        if ($authUser) {
            return $authUser;
        }
        
       $currency_id =  Currency::select('id')->where('isDefault', '1')->pluck('id')->first();
  
       /*copy image from different host*/
       $imageUrl = $user->avatar;
       $imageName = time().'.jpg';
       $fullPathimageName = Config::get('constants.users_path').'/'.$imageName;
       copy($imageUrl, $fullPathimageName);

        return User::create([
            'name'     => $user->name,
            'email'    => $user->email,
            'verified' => '1',
            'email_token' => null,
            'status' => '1',
            'default_language' => session('default_lang'),
            'default_currency' => $currency_id,
            'image'       => $imageName, 
            'provider' => $provider,
            'provider_id' => $user->id
        ]);
    }

}
