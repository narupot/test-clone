<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class ShippingProfile extends Model
{
    protected  $table = 'shipping_profile';
    
    public function getShippingProfileDesc(){
       return $this->hasOne('App\ShippingProfileDesc','shipping_profile_id','id')
               ->where('lang_id', session('default_lang'));
    }

    public function getSelectedProduct(){
    	return $this->hasMany('App\ShipppingProfileProduct','shipping_profile_id','id');
    } 

    public function getSelectedProvince(){
    	return $this->hasMany('App\ShipppingProfileProvince','shipping_profile_id','id');
    }

    public function shopAddress(){
        return $this->hasOne('App\ShopAddress','id','shop_address_id');
    }

    public static function getDeleveryType($shipping_profile_id) {
        $shipping_profile = self::select('id')->where(['id'=>$shipping_profile_id])->with('getShippingProfileDesc')->first();
        return $shipping_profile->getShippingProfileDesc->shipping_label;
    }     	
    // Adde for manage Shipping profile rate | Start | @Dinesh Kumar Kovid | Date 03/01/2017	
    public function getAllShippingProfileRates(){

        return $this->hasMany('App\ShippingProfileRates','shipping_profile_id','id');

    }

    public static function getShippingProfile() {
        return Self::whereIn('shipping_type',['flat-rate','free-shipping'])->with('getShippingProfileDesc')->with('getShippingProfileCountry')->with('getShippingProfileProduct')->orderBy('id','Desc')->get();
    }

    public function getShippingProfileCountry(){
        return $this->hasMany('App\ShippingProfileCountry','shipping_profile_id','id');
    }

    public function getShippingProfileProduct(){
        return $this->hasMany('App\ShipppingProfileProduct','shipping_profile_id','id');
    }     
    
    public static function getShippingProfiles($limit=10,$shipping_type,$name=null,$status=null){


        // return self::with(['getShippingProfileDesc'=>function($query) use ($name){
            
        //         $query->where('name','like','%'.$name.'%');
            
        // }])
        // ->whereIn('status',$status)
        // ->where('shipping_type',$shipping_type)
        // ->orderBy('id', 'desc')
        // ->paginate($limit);

        $prefix =  DB::getTablePrefix();
        $default_lang = session('default_lang');

        $sql =  DB::table(with(new \App\ShippingProfile)->getTable().' as sp')
                  ->leftjoin(with(new \App\ShippingProfileDesc)->getTable().' as spd', [['sp.id', '=', 'spd.shipping_profile_id'], ['spd.lang_id','=',DB::raw($default_lang)]])
                  ->select('sp.id','spd.name', 'sp.status','sp.logo','sp.created_at','sp.updated_at')
                  ->where('sp.shipping_type',$shipping_type);
                
                  if(isset($name)){
                    $sql->where('spd.name','like','%'.$name.'%');
                  }

                  if(isset($status) && is_array($status)){
                    $sql->whereIn('sp.status',$status);
                  }
                  if(isset($status) && !is_array($status)){
                    $sql->where('sp.status',$status); 
                  }

        return $sql->orderBy('sp.id', 'desc')->paginate($limit);
    } 
   	
}
