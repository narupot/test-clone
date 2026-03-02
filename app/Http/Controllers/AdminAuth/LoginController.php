<?php

namespace App\Http\Controllers\AdminAuth;

use App\Http\Controllers\MarketPlace;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Auth;
use Cookie;

class LoginController extends MarketPlace
{
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
    protected $redirectTo = '/admin/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('admin.guest',['except' => array('logout')]);
    }
    
    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm(Request $request)
    {

      $font_family = \App\SystemConfig::getSystemValFromDb("FONT_FAMILY");
      $font_size = \App\SystemConfig::getSystemValFromDb("FONT_SIZE");
      $font_col = \App\SystemConfig::getSystemValFromDb("FONT_COLOUR");
      $bg_color = \App\SystemConfig::getSystemValFromDb("BG_CLOUR");

        return view('admin-auth.login',['font_family'=>$font_family,'font_size'=>$font_size,'font_col'=>$font_col,'bg_color'=>$bg_color]);
    } 
    
    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {   
        return Auth::guard('admin_user');
    }    
    
    public function logout(Request $request)
    {
       // dd('hi');
        //$this->guard('admin_user')->logout();

        Auth::guard('admin_user')->logout();
        //$request->session()->flush();
        //$request->session()->regenerate();
        return redirect()->action('AdminAuth\LoginController@showLoginForm');
    }  
    
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {           

            $admin_user_id = Auth::guard('admin_user')->user()->id;
            Cookie::make('admin_user_id', $admin_user_id, 3600);

            $this->getMenuPermisionId();    // added to get user menu id in session
            $this->getAdminDefaultLangId();    // set default language id to session
            $this->getDefaultTimeZone();    // set default timezone to session
            $this->setLastLogin();            
            $this->adminLogDetail(array('logType'=>'1'));  // added to insert user log details
            return $this->sendLoginResponse($request);
        }else{
            $this->adminLogDetail(array('logType'=>'2', 'email'=>$request->email, 'password'=>$request->password)); // added to insert user log details when login failed
        }
       
        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);
        
        return $this->sendFailedLoginResponse($request);
    }

    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request), $request->has('remember')
        );
    }   


    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);

         /*loggedInUser ticket for image upload*/
        $expiresAt = 1440;
        $user_id = Auth::guard('admin_user')->user()->id;
        Cookie::queue('loggedInUser', $user_id, $expiresAt);

        //Redirect to back url
        $redirect = session('admin_back_url');
        if(empty($redirect)) {
          $redirect = action('Admin\AdminHomeController@index');
        }
        $request->session()->forget('admin_back_url');

        return redirect($redirect);
        
    }
}
