<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Shop extends Model
{  
    protected $table = 'shop';

    public static function checkShopName($shop_name,$user_id){

    	return DB::table(with(new Shop)->getTable().' as s')
                ->join(with(new ShopDesc)->getTable().' as sd', 's.id', '=', 'sd.shop_id')
                ->where('s.user_id','!=',$user_id)
                ->where('sd.shop_name',$shop_name)
                ->count();
    }

    public static function checkShopUrl($shop_url,$user_id){

    	return Self::where('user_id','!=',$user_id)
                ->where('shop_url',$shop_url)
                ->count();
    }

    public static function checkPanelNo($panel_no,$user_id){

        return Self::where('user_id','!=',$user_id)
                ->where('panel_no',$panel_no)
                ->count();
    }

    public function shopDesc(){
        return $this->hasOne('App\ShopDesc', 'shop_id', 'id')->where('lang_id', session('default_lang'));  
    }

    public function allDesc(){
        return $this->hasMany('App\ShopDesc', 'shop_id', 'id');  
    }

    public function shopUser(){
        return $this->hasOne('App\User', 'id', 'user_id');  
    }

    public function getShopSeller(){
        return $this->hasOne('App\Seller', 'user_id', 'id');  
    }
    
}
