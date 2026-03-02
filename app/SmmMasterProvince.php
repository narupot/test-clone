<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Model สำหรับตาราง smm_master_provinces (จังหวัด)
 */
class SmmMasterProvince extends Model
{
    protected $table = 'master_provinces';

    public function districts()
    {
        return $this->hasMany('App\SmmMasterDistrict', 'province_id', 'id');
    }

    /**
     * ดึงรายการจังหวัดทั้งหมด (กรองเฉพาะ status=1 เขตพื้นที่การขาย)
     */
    public static function getList()
    {
        return self::where('status', 1)->orderBy('name_th')->get();
    }
}
