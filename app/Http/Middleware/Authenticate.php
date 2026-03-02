<?php
namespace App\Http\Middleware;

use Closure;
use Route;

use Illuminate\Support\Facades\Auth;

class Authenticate {

    public function handle($request, Closure $next)
    {
                
        if (Auth::check())
        {

			$uri = Route::getCurrentRoute()->uri;
	        if(session('order_by_ref')=='admin' && strpos($uri, '/rma')===FALSE){
	            return redirect()->action('HomeController@index');
	        }
            return $next($request);
        }

        $request->session()->put('back_url',$request->fullUrl());
        //echo '==>'.session('back_url');die;        

        return redirect()->action('Auth\RegisterController@login');

    }

}