<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Product extends Model
{
    protected  $table = 'product';

    public function shop(){
    	//session('default_lang')
    	$default_lang = session('default_lang');
        return $this->hasOne('App\ShopDesc', 'shop_id', 'shop_id')->where('lang_id', $default_lang); 
    }
    
    public function images(){
        return $this->hasMany('App\ProductImage', 'product_id', 'id'); 
    }


    public function tierPrices(){
        $default_lang = 0;
        return $this->hasMany('App\ProductTierPrice', 'product_id', 'id'); 
    }

    public function productDesc(){
        $default_lang = session('default_lang');
        return $this->hasOne('App\ProductDesc', 'product_id', 'id')->where('lang_id', $default_lang); 

    }

    public function getShop(){
        return $this->hasOne('App\Shop', 'id', 'shop_id'); 
    }

    public function getShopDesc(){
        return $this->hasOne('App\ShopDesc', 'shop_id', 'shop_id')->where('lang_id', session('default_lang')); 
    }

    public function categorydesc(){

        return $this->hasOne('App\CategoryDesc', 'cat_id', 'cat_id')->where('lang_id', session('default_lang'))->select('category_name','cat_id'); 
    }

    public function getbadge(){
        return $this->hasOne('App\Badge', 'id', 'badge_id');

    }

    public function getPackage(){
        return $this->hasOne('App\Package', 'id', 'unit_id');

    }

    public static function getProductDetail($sku){
        return Self::where(['sku'=>$sku,'status'=>'1'])->with(['productDesc','images','getShop','getShopDesc','categorydesc','tierPrices'])->first();
    }

    public static function getProductDetailByID($id){
        return Self::where(['id'=>$id,'status'=>'1'])->with(['productDesc','images','getShop','getShopDesc','categorydesc','tierPrices', 'getbadge'])->first();
    }

    public static function getProductBasicInfo($id, $shop_id){
        $default_lang = session('default_lang');
        return DB::table(with(new \App\Product)->getTable().' as p')
                ->join(with(new \App\Category)->getTable().' as c', 'p.cat_id', '=', 'c.id')
                ->join(with(new \App\CategoryDesc)->getTable().' as cd', 
                            ['p.cat_id'=>'cd.cat_id', 'cd.lang_id'=>DB::raw($default_lang)])
                ->leftjoin(with(new \App\UnitDesc)->getTable().' as ud', ['p.base_unit_id'=>'ud.unit_id', 'ud.lang_id' => DB::raw($default_lang)])
                ->leftjoin(with(new \App\PackageDesc)->getTable().' as pd', ['p.package_id'=>'pd.package_id', 'pd.lang_id' => DB::raw($default_lang)])
                ->join(with(new \App\Badge)->getTable().' as b', 'p.badge_id', '=', 'b.id')
                ->select('p.id', 'p.shop_id', 'p.sku','p.stock', 'p.quantity', 'p.weight_per_unit', 'p.thumbnail_image', 'cd.category_name', 'c.url' ,'b.icon', 'ud.unit_name', 'pd.package_name')
                ->where(['p.id'=>$id, 'shop_id'=>$shop_id])
                ->first();
    }    
   
}
