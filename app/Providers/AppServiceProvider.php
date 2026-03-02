<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        //$this->app['request']->server->set('HTTPS', true);
        $agent = new Agent();

        View::share('agent', $agent);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //        
    }
}
