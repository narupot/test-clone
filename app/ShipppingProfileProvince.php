<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShipppingProfileProvince extends Model
{
    protected  $table = 'shipping_profile_province';
    
    public $timestamps = false;

    function provinceName() {
        return $this->hasOne('App\CountryProvinceStateDesc', 'province_state_id', 'province_id')->where('lang_id', session('default_lang'));
    }
}
