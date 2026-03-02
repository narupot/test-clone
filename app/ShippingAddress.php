<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model {

    protected $table = 'shipping_address';     

    public static function getShippingAddress($userId){

    	$shipAddress = self::select('title','id')
         ->where('user_id',$userId)
         ->orderBy('is_default','Desc')->get();

         return $shipAddress;
    } 

    public static function getBillingAddress($userId){

    	$shipAddress = self::select('title','id')
         ->whereIn('address_type',array('2','3'))
         ->where('user_id',$userId)
         ->orderBy('is_default','Desc')->get();
         
         return $shipAddress;
    }

    public function userDetail() {
        
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public static function getUserAddress($userId){

        return self::where(['user_id'=>$userId])->orderBy('sequence', 'Asc')->orderBy('id', 'Desc')->get();
    }

    public static function getAddressById($address_id){

        return self::find($address_id);
    }           
}
