<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Molequent;
class MongoWishlist extends Molequent
{
    protected $connection = 'mongodb';
    protected $collection = 'wishlist';

    public static function checkProductWishlist($product_id,$user_id){
    	return Self::where(['product_id'=>$product_id,'user_id'=>$user_id])->count();
    }
       
}
