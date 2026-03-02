<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class ShippingAddress extends Model {

    protected $table = 'shipping_address';
    
    protected $fillable = [
        'user_id', 'title', 'salutation', 'first_name', 'last_name', 
        'email', 'address', 'road', 'isd_code', 'country_id', 
        'province_state_id', 'city_district_id', 'sub_district_id', 
        'sub_district', 'city_district', 'province_state', 'country', 
        'zip_code', 'ph_number', 'lat', 'long',
        'is_company_add', 'company_name', 'branch', 'tax_id', 'company_address',
        'status', 'is_default', 'address_type', 'sequence'
    ];     

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
