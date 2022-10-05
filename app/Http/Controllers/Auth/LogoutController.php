<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Session;
use Auth;
use Carbon\Carbon;
use Cache;
class LogoutController extends Controller {


    public function logout(Request $request)
    {   
       
        if(Auth::check()) {
          $user_id = Auth::id();
          Cache::forget('OnlineUsers['.$user_id.']');
          Cache::forget('OnlineProducts['.$user_id.']');
        }
        //Auth::logout();
        Auth::guard('web')->logout();
        //$request->session()->flush();
        //$request->session()->regenerate();
        return redirect('/');
    }
  




}
