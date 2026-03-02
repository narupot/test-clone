<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CountryCityDistrict extends Model
{  
    protected $table = 'country_city_district';
    //public $timestamps = false;
    
    function cityName() {
        return $this->hasOne('App\CountryCityDistrictDesc', 'city_district_id', 'id')->where('lang_id', session('default_lang'));
    }

    public static function getCityList($province_id) {
    	return self::where(['province_state_id' => $province_id])->with('cityName')->get();
    }    

    public static function getCityDetail($city_id) {
        return self::where('id', $city_id)->with('cityName')->first();
    }

    public static function getZipCode($city_id) {
        return self::where('id', $city_id)->value('zip');
    }              
}
