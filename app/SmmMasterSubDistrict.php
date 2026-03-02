<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Model สำหรับตาราง smm_master_sub_districts (ตำบล/แขวง)
 * มีคอลัมน์ zip_code สำหรับ auto-fill
 */
class SmmMasterSubDistrict extends Model
{
    protected $table = 'master_sub_districts';

    protected $fillable = ['zip_code', 'name_th', 'name_en', 'district_id'];

    public function district()
    {
        return $this->belongsTo('App\SmmMasterDistrict', 'district_id', 'id');
    }

    /**
     * ดึงรายการตำบล/แขวงตาม district_id
     */
    public static function getByDistrictId($district_id)
    {
        return self::where('district_id', $district_id)->orderBy('name_th')->get();
    }

    /**
     * ดึงรหัสไปรษณีย์จาก sub_district_id
     */
    public static function getZipBySubDistrictId($sub_district_id)
    {
        return self::where('id', $sub_district_id)->value('zip_code');
    }
}
