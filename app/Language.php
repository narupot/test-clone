<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Language extends Model
{
    use SoftDeletes;
    
    protected  $table = 'languages';
    
    protected $dates = ['deleted_at'];    

    public static function getLangugeDetails() {
    	return self::where('status', '1')->get();
    }

    public static function getDefaultLangugeFrontend() {
    	return self::where(['status'=>'1', 'isDefault'=>'1'])->first();
    }

    public static function getDefaultLanguge() {
    	return self::where(['status'=>'1', 'isSystem'=>'1'])->first();
    }       
}
