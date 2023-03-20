<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class CountryCityDistrictZip extends Model
{  
    protected $table = 'country_city_district_zip';
    //public $timestamps = false;
    
    

    public static function getSubDistList($district_id) {
        return self::where(['district_id' => $district_id])->with('subDistrictName')->get();
    }

    public static function getSubDistSortedList($district_id) {

        return DB::table(with(new CountrySubDistrict)->getTable().' as csd')
        ->join(with(new CountrySubDistrictDesc)->getTable().' as csdd', 'csd.id', '=', 'csdd.sub_district_id')
        ->select('csd.id', 'csdd.sub_district_name')
        ->where(['csd.district_id'=>$district_id, 'csdd.lang_id'=>session('default_lang')])
        ->orderBy(DB::raw("convert(sub_district_name using tis620)"), "ASC")
        ->get();
    }    

    public static function getSubDistrictDetail($sub_district_id) {
        return self::where('id', $sub_district_id)->with('subDistrictName')->first();
    }             
}
