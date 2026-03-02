<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Model สำหรับตาราง smm_master_districts (เขต/อำเภอ)
 */
class SmmMasterDistrict extends Model
{
    protected $table = 'master_districts';

    public function province()
    {
        return $this->belongsTo('App\SmmMasterProvince', 'province_id', 'id');
    }

    public function subDistricts()
    {
        return $this->hasMany('App\SmmMasterSubDistrict', 'district_id', 'id');
    }

    /**
     * ดึงรายการเขตตาม province_id
     */
    public static function getByProvinceId($province_id)
    {
        return self::where('province_id', $province_id)->orderBy('name_th')->get();
    }
}
