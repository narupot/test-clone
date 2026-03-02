<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Country extends Model
{  
    protected $table = 'country';
    
    function countryName() {
        return $this->hasOne('App\CountryDesc', 'country_id', 'id')->where('lang_id', session('default_lang'));
    } 
    
    function countryDesc() {
        return $this->hasMany('App\CountryDesc', 'country_id', 'id');
    }

    public static function getCountry() {
        return self::where('status', '1')->with('countryName')->get();
    }

    public static function getCountryAll() {
        return self::with('countryName')->get();
    } 

    public static function getCountryDetail($country_id, $type='') {
        
        if($type == 'default') {
            return self::where('is_default', '1')->with('countryName')->first();
        }
        elseif($type == 'country_code') {
            return self::where('short_code', $country_id)->with('countryName')->first();
        }        
        elseif($country_id > 0) {
            return self::where('id', $country_id)->with('countryName')->first();
        }
        else {
          return false;  
        }        
    } 

    public static function getCountryCode($country_id) {
        return self::select('country_code', 'short_code', 'country_isd')->where('id', $country_id)->first();
    }

    public static function getCountryStateDistrict($sub_district_id) {

        return DB::table(with(new \App\CountryProvinceState)->getTable().' as cps')
        ->join(with(new \App\CountryProvinceStateDesc)->getTable().' as cpsd', 'cps.id', '=', 'cpsd.province_state_id')
        ->join(with(new \App\CountryCityDistrict)->getTable().' as ccd', 'cps.id', '=', 'ccd.province_state_id')
        ->join(with(new \App\CountryCityDistrictDesc)->getTable().' as ccdd', 'ccdd.city_district_id', '=', 'ccd.id')
        ->join(with(new \App\CountrySubDistrict)->getTable().' as csd', 'ccd.id', '=', 'csd.district_id')
        ->join(with(new \App\CountrySubDistrictDesc)->getTable().' as csdd', 'csdd.sub_district_id', '=', 'csd.id')               
        ->select('cpsd.province_state_name', 'ccd.zip', 'ccdd.city_district_name', 'csdd.sub_district_name')
        ->where(['csd.id'=>$sub_district_id, 'cpsd.lang_id'=>session('default_lang'), 'ccdd.lang_id'=>session('default_lang'), 'csdd.lang_id'=>session('default_lang')])
        ->first();        
    }   

    function getProvince() {
        return $this->hasMany('\App\CountryProvinceState', 'country_id', 'id')->with('provinceName');
    }            
}
