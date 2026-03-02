<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use Request;
use App;

class LanguageHandler
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //dd(session()->all());

        
        App::setLocale('de');
        
        if(empty(session('lang_code')) || empty(session('default_lang'))){

            $langprefix = \App\Language::select('languageCode', 'id')->where('isSystem', '1')->first();

            Session::put('lang_code', $langprefix->languageCode);
            Session::put('default_lang', $langprefix->id);
        }


        return $next($request);
    }
}
