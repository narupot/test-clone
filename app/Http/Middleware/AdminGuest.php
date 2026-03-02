<?php

namespace App\Http\Middleware;

use Closure;
use Redirect;

class AdminGuest {

    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [
            //
    ];

    public function handle($request, Closure $next) {
        //echo 'test'; exit;
        $guard = 'admin_user';
        if (\Auth::guard($guard)->check()) {
            //return Redirect::to('/en/admin/home');
            return Redirect::action('Admin\AdminHomeController@index');
        }

        return $next($request);
        
    }

}
