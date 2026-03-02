<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlogConfig extends Model
{
    protected $table = 'blog_config';
    
    public $timestamps = false;

    public static function getBlogConfig($type) {
    	return self::where('blog_config_type', $type)->get();
    }    

    public static function getBlogValue($blog_config_name) {
    	return self::where('blog_config_name', $blog_config_name)->value('blog_config_value');
    } 
}
