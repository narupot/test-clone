<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CountrySubDistrict extends Model
{  
    protected $table = 'country_sub_district';
    //public $timestamps = false;
    
    function subDistrictName() {
        return $this->hasOne('App\CountrySubDistrictDesc', 'sub_district_id', 'id')->where('lang_id', session('default_lang'));
    }

    public static function getSubDistList($district_id) {
        return self::where(['district_id' => $district_id])->with('subDistrictName')->get();
    }

    public static function getSubDistrictDetail($sub_district_id) {
        return self::where('id', $sub_district_id)->with('subDistrictName')->first();
    }             
}
