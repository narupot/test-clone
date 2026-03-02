<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Config;

class SystemConfig extends Model
{
    protected $table = 'system_config';
    
    public $timestamps = false;

    public static function getSystemConfig($type) {
    	return self::where('system_type', $type)->get();
    }    

    public static function getSystemVal($system_name) {
        $cache_key = 'system_config_cache';
    	if (cache_hasKey($cache_key) && \Config::get('constants.enable_cache')) {
    		$system_arr = cache_getDate($cache_key);

		    $system_val = isset($system_arr[$system_name])?$system_arr[$system_name]:self::getSystemValFromDb($system_name);
		}else{
			$minutes = 10;
            if(\Config::get('constants.enable_cache')){
                $sys_config_cache = @file_get_contents(Config('constants.data_cache_path').'/system_config.dict');
                $system_arr = json_decode($sys_config_cache,true);

                /****storing value in cache***/
                cache_putData($cache_key,$system_arr,$minutes);
                $system_val = isset($system_arr[$system_name])?$system_arr[$system_name]:self::getSystemValFromDb($system_name);
            }else{
                $system_val = self::getSystemValFromDb($system_name);
            }
			
		}

    	return $system_val;

    } 

    public static function getSystemValFromDb($system_name){
    	return self::where('system_name', $system_name)->value('system_val');
    }
}
