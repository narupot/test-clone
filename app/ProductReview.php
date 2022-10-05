<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    protected  $table = 'product_review';

    public static function updateReview($product_id,$shop_id){
    	/***getting avg product rating**/
    	$avg_rating = Self::where('product_id',$product_id)->where('is_deleted','!=','1')->avg('rating');
        $avgrating = (int)round($avg_rating);
        /***updating product rating***/
        \App\Product::where('id',$product_id)->update(['avg_rating'=>$avgrating]);
        \App\MongoProduct::where('_id',(int)$product_id)->update(['avg_star'=>$avgrating]);
        /***getting shop rating***/
        $shop_rating = \App\Product::where('shop_id',$shop_id)->where('avg_rating','>','0')->avg('avg_rating');
        $shop_avg_rating = (int)round($shop_rating);
        /****updating shop rating*****/
        \App\Shop::where('id',$shop_id)->update(['avg_rating'=>$shop_avg_rating]);
        \App\MongoShop::where('_id',(int)$shop_id)->update(['avg_rating'=>$shop_avg_rating]);
    }
}
