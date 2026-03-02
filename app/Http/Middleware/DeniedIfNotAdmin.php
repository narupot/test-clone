<?php

namespace App\Http\Middleware;

use Closure;
use Redirect;

class DeniedIfNotAdmin {

    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [
            //
    ];

    public function handle($request, Closure $next) {
        
        $guard = 'admin_user';
        if (!\Auth::guard($guard)->check()) {

            $request->session()->put('admin_back_url',$request->fullUrl());
            //echo '==>'.session('admin_back_url');die;
            
            return redirect()->action('AdminAuth\LoginController@showLoginForm');
        }

        return $next($request);
    }
}
