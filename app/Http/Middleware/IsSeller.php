<?php
namespace App\Http\Middleware;

use Closure;
use Route;

use Illuminate\Support\Facades\Auth;

class IsSeller {

    public function handle($request, Closure $next)
    {
                
        if (Auth::check())
        {

			$uri = Route::getCurrentRoute()->uri;
	        if(Auth::user()->user_type == 'buyer'){
	            return redirect()->action('HomeController@index');
	        }
            return $next($request);
        }

        return redirect()->action('Auth\RegisterController@login');

    }

}