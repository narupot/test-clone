<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebsiteConfiguration extends Model
{
    protected $table = 'website_configuration';
    
    public $timestamps = false;

    public static function getWebsiteConfiguration($type) {
    	return self::where('website_config_type', $type)->get();
    }    

    public static function getWebsiteValue($website_config_name) {
    	return self::where('website_config_name', $website_config_name)->value('website_config_value');
    } 
}
