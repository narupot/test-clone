<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShippingProfileRates extends Model
{
    protected  $table = 'shipping_profile_rates';

    public function getTableColumns() {
        return $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
    }

    public static function getShippingProfileRates($limit=10,$shipping_profile_id,$searchData) {

        return self::orderBy('id', 'desc')
        ->where('shipping_profile_id',$shipping_profile_id)
        ->with('getRatesDescription')
        ->paginate($limit);
    }

    public function getRatesDescription(){

        return $this->hasOne('App\ShippingProfileRatesDesc','rate_id','id')->where('lang_id', session('default_lang'));

    }

    public function getRatesAllLangDesc(){

        return $this->hasMany('App\ShippingProfileRatesDesc','rate_id','id');

    }
    	
}
 