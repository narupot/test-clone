<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Timezones extends Model
{
    protected  $table = 'timezones';
    
    public static function getTimezone(){
      return self::where('status', '1')->get();
   }

    public static function getDefaultTimezoneDetail($default_time_zone){
      return self::where('timezone', $default_time_zone)->first();
   	}   
}
