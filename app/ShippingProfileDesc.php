<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShippingProfileDesc extends Model
{
    protected  $table = 'shipping_profile_desc';
    
    public $timestamps = false;

    public static function getShippingProfileName($shipping_profile_id) {
    	return self::where('shipping_profile_id', $shipping_profile_id)->get();
    }
}
