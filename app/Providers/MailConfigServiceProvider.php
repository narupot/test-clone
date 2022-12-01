<?php

namespace App\Providers;

use Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // if (\Schema::hasTable('email_transmission_method')) {
        //     $mail = DB::table('email_transmission_method')->where('is_default','1')->first();
            
        //     if ($mail) //checking if table is not empty
        //     {
        //         $config = array(
        //             'driver'     => $mail->driver,
        //             'host'       => $mail->host,
        //             'port'       => $mail->port,
        //             'from'       => array('address' => $mail->email_from, 'name' => $mail->from_name),
        //             'encryption' => $mail->encription,
        //             'username'   => $mail->username,
        //             'password'   => $mail->password,
        //             'sendmail'   => '/usr/sbin/sendmail -bs',
        //             'pretend'    => false,
        //         );
        //         Config::set('mail', $config);
        //     }
        // }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if (\Schema::hasTable('email_transmission_method')) {
            $mail = DB::table('email_transmission_method')->where('is_default','1')->first();
            
            if ($mail) //checking if table is not empty
            {
                $config = array(
                    'driver'     => $mail->driver,
                    'host'       => $mail->host,
                    'port'       => $mail->port,
                    'from'       => array('address' => $mail->email_from, 'name' => $mail->from_name),
                    'encryption' => $mail->encription,
                    'username'   => $mail->username,
                    'password'   => base64_decode($mail->password),
                    'sendmail'   => '/usr/sbin/sendmail -bs',
                    'pretend'    => false,
                );
                Config::set('mail', $config);
            }
        }	
    }
}