<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CountryProvinceState extends Model
{  
    protected $table = 'country_province_state';
    //public $timestamps = false;
    
    function provinceName() {
        return $this->hasOne('App\CountryProvinceStateDesc', 'province_state_id', 'id')->where('lang_id', session('default_lang'));
    }

    public static function getProvinceList($country_id) {
        return self::where('country_id', $country_id)->where('status','1')->with('provinceName')->get();
    }

    public static function getProvinceDetail($province_id) {
        return self::where('id', $province_id)->with('provinceName')->first();
    }     
}
