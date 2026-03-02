<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShippingProfileCountry extends Model
{
    protected  $table = 'shipping_profile_country';
    
    public $timestamps = false;

    function countryName() {

        return $this->hasOne('App\CountryDesc', 'country_id', 'country_id')->select(['country_id','country_name','lang_id'])->where('lang_id', session('default_lang'));
    } 
}
