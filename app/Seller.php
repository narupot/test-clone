<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{  
    protected $table = 'seller';

    public function getStoreInfo(){
        return $this->hasOne('App\SellerStore', 'seller_id', 'id'); 
    }
    
    public static function checkSellerData($user_id){
    	return Self::where('user_id',$user_id)->first();
    }

    public static function checkCitizenId($citizen_id,$user_id){
    	return Self::where('user_id','!=',$user_id)
                ->where('citizen_id',$citizen_id)
                ->count();
    }
}
