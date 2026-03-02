<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Model สำหรับตาราง delivery_region_detail (smm_delivery_region_detail)
 * เก็บเขตพื้นที่จัดส่ง - สนใจเฉพาะ status=1 (active) ถ้ามีแถว status=1 ถือว่ารองรับจัดส่ง
 */
class SmmDeliveryRegionDetail extends Model
{
    protected $table = 'delivery_region_detail';

    protected $primaryKey = 'deli_reg_detail_id';

    public $timestamps = false;

    protected $fillable = [
        'subdistrict_id', 'postcode', 'status', 'region_id',
        'created_by', 'created_at', 'updated_by', 'updated_at',
    ];

    /**
     * ตรวจสอบว่าพื้นที่ subdistrict_id + postcode รองรับการจัดส่งหรือไม่
     * สนใจเฉพาะ status=1 เท่านั้น - ถ้าค้นหาแล้วไม่เจอ status=1 เลย ถือว่าไม่รองรับพื้นที่นั้น
     *
     * @param int|string $subdistrictId รหัสตำบล/แขวง (6 หลัก)
     * @param int|string $postcode รหัสไปรษณีย์
     * @return bool true = รองรับจัดส่ง (มีแถว status=1), false = ไม่รองรับ (ไม่มี status=1)
     */
    public static function isInDeliveryZone($subdistrictId, $postcode)
    {
        if (empty($subdistrictId) || empty($postcode)) {
            return false;
        }

        return self::where('subdistrict_id', (int) $subdistrictId)
            ->where('postcode', (int) $postcode)
            ->where('status', 1)
            ->exists();
    }

    /**
     * ดึงรายการ province_id ที่อยู่ในเขตจัดส่ง (สำหรับกรอง dropdown)
     * กรองเฉพาะจังหวัดที่มี status=1 (เขตพื้นที่การขาย)
     */
    public static function getProvinceIdsInZone()
    {
        $t = (new self)->getTable();
        return self::query()
            ->join('master_sub_districts as ms_sub', "{$t}.subdistrict_id", '=', 'ms_sub.id')
            ->join('master_districts as ms_dist', 'ms_sub.district_id', '=', 'ms_dist.id')
            ->join('master_provinces as ms_prov', 'ms_dist.province_id', '=', 'ms_prov.id')
            ->where("{$t}.status", 1)
            ->where('ms_prov.status', 1)
            ->distinct()
            ->pluck('ms_dist.province_id')
            ->filter()
            ->values();
    }

    /**
     * ดึงรายการ district_id ที่อยู่ในเขตจัดส่ง (ตาม province_id)
     * กรองเฉพาะจังหวัดที่มี status=1 (เขตพื้นที่การขาย)
     */
    public static function getDistrictIdsInZone($provinceId)
    {
        if (empty($provinceId)) {
            return collect();
        }
        $t = (new self)->getTable();
        return self::query()
            ->join('master_sub_districts as ms_sub', "{$t}.subdistrict_id", '=', 'ms_sub.id')
            ->join('master_districts as ms_dist', 'ms_sub.district_id', '=', 'ms_dist.id')
            ->join('master_provinces as ms_prov', 'ms_dist.province_id', '=', 'ms_prov.id')
            ->where("{$t}.status", 1)
            ->where('ms_dist.province_id', (int) $provinceId)
            ->where('ms_prov.status', 1)
            ->distinct()
            ->pluck('ms_dist.id')
            ->values();
    }

    /**
     * ดึงรายการ subdistrict ที่อยู่ในเขตจัดส่ง (ตาม district_id) พร้อม zip_code
     * กรองเฉพาะจังหวัดที่มี status=1 (เขตพื้นที่การขาย)
     * @return \Illuminate\Support\Collection [['id'=>..., 'zip_code'=>...], ...]
     */
    public static function getSubDistrictsInZone($districtId)
    {
        if (empty($districtId)) {
            return collect();
        }
        $t = (new self)->getTable();
        return self::query()
            ->join('master_sub_districts as ms_sub', "{$t}.subdistrict_id", '=', 'ms_sub.id')
            ->join('master_districts as ms_dist', 'ms_sub.district_id', '=', 'ms_dist.id')
            ->join('master_provinces as ms_prov', 'ms_dist.province_id', '=', 'ms_prov.id')
            ->where("{$t}.status", 1)
            ->where('ms_sub.district_id', (int) $districtId)
            ->where('ms_prov.status', 1)
            ->select('ms_sub.id', 'ms_sub.name_th', 'ms_sub.zip_code')
            ->distinct()
            ->orderBy('ms_sub.name_th')
            ->get();
    }
}
