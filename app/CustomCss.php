<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class CustomCss extends Model {

    protected $table = 'custom_css';  

    public static function getCustomCss(){
    	return self::select('id', 'name')->orderBy('created_at','desc')->get();
    }
}

    