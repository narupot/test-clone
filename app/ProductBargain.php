<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class ProductBargain extends Model {
    protected $table = 'product_bargains';
     
    public function productDesc(){
        $default_lang = session('default_lang');
        return $this->hasOne('App\ProductDesc', 'product_id', 'product_id')->where('lang_id', $default_lang); 

    }

    public function getShop(){
        return $this->hasOne('App\Shop', 'id', 'shop_id'); 
    }


    public function getBargainDetails(){
        return $this->hasMany('App\ProductBargainDetails', 'bargain_id', 'id'); 
    }

    public static function totBargainCountForBuyerNoti($user_id){
        return  \DB::table(with(new ProductBargain)->getTable().' as pb')
                ->join(with(new Product)->getTable().' as p', 'p.id', '=', 'pb.product_id')
                ->where(['p.status'=>'1','pb.user_id'=>$user_id])
                 ->count();
    }

    public static function totBargainCountForSellerNoti($shop_id){
        return  \DB::table(with(new ProductBargain)->getTable().' as pb')
                ->join(with(new Product)->getTable().' as p', 'p.id', '=', 'pb.product_id')
                ->where(['p.status'=>'1','pb.shop_id'=>$shop_id])
                 ->count();
    }   





}




