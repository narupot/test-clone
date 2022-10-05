<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminAvatar extends Model
{  
    protected $table = 'admin_avatar';    
    // public $timestamps = false;
    
    public static function getAvatarImages() {
    	return self::select('id', 'name', 'gender')->where(['status'=>'1'])->get();
    }
}
