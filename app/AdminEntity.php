<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminEntity extends Model
{  
    protected $table = 'admin_entity';    
    // public $timestamps = false;


    public static function getAdminEntityDetail($admin_id) {
    	return self::where('user_id', '=', $admin_id)->get();
    }

    public static function getImageDetail() {
        return self::where('entity_key', '=', 'profile_image')->pluck('entity_value','user_id');
    }
    
   
}
