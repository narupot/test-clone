<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Model สำหรับตาราง shipping_address_log (smm_shipping_address_log)
 * บันทึกเมื่อลูกค้าค้นหา/เลือกพื้นที่นอกเขตจัดส่ง เพื่อวิเคราะห์ขยายพื้นที่ขายในอนาคต
 */
class ShippingAddressLog extends Model
{
    protected $table = 'shipping_address_log';

    protected $fillable = [
        'user_id', 'title', 'salutation', 'first_name', 'last_name', 'email',
        'address', 'road', 'isd_code', 'country_id', 'province_state_id', 'city_district_id',
        'sub_district_id', 'sub_district', 'city_district', 'province_state', 'country',
        'zip_code', 'ph_number', 'lat', 'long',
        'is_company_add', 'company_name', 'branch', 'tax_id', 'company_address',
        'status', 'is_default', 'address_type', 'sequence',
    ];

    /**
     * บันทึก log เมื่อลูกค้าเลือกพื้นที่นอกเขตจัดส่ง
     *
     * @param int $userId
     * @param int $subDistrictId
     * @param int|string $zipCode
     * @param float|null $lat
     * @param float|null $long
     * @param array $addressParts ['sub_district'=>, 'district'=>, 'province'=>, 'zip_code'=>]
     * @param int|null $provinceId
     * @param int|null $districtId
     */
    public static function logOutsideZoneSearch($userId, $subDistrictId, $zipCode, $lat = null, $long = null, $addressParts = [], $provinceId = null, $districtId = null)
    {
        $user = \App\User::find($userId);
        if (!$user) return;

        $subName = $addressParts['sub_district'] ?? '';
        $districtName = $addressParts['district'] ?? '';
        $provinceName = $addressParts['province'] ?? '';

        if (empty($subName) || empty($districtName) || empty($provinceName)) {
            $sub = \App\SmmMasterSubDistrict::with('district.province')->find($subDistrictId);
            if ($sub) {
                $subName = $sub->name_th ?? $subName;
                if ($sub->district) {
                    $districtName = $sub->district->name_th ?? $districtName;
                    $districtId = $districtId ?? $sub->district_id;
                    if ($sub->district->province) {
                        $provinceName = $sub->district->province->name_th ?? $provinceName;
                        $provinceId = $provinceId ?? $sub->district->province_id;
                    }
                }
            }
        }

        // ดึง province_id, district_id จาก master ถ้ายังไม่มี (เช่น โหมดแผนที่)
        if (($provinceId === null || $districtId === null) && $subDistrictId) {
            $sub = \App\SmmMasterSubDistrict::with('district.province')->find($subDistrictId);
            if ($sub && $sub->district) {
                $districtId = $districtId ?? $sub->district_id;
                if ($sub->district->province) {
                    $provinceId = $provinceId ?? $sub->district->province_id;
                }
            }
        }

        $now = date('Y-m-d H:i:s');
        self::insert([
            'user_id' => $userId,
            'title' => 'นอกเขตจัดส่ง',
            'salutation' => $user->salutation ?? '',
            'first_name' => $user->first_name ?? '',
            'last_name' => $user->last_name ?? '',
            'email' => $user->email ?? '',
            'address' => $subName . ' ' . $districtName . ' ' . $provinceName,
            'road' => $districtName,
            'isd_code' => null,
            'country_id' => 1,
            'province_state_id' => $provinceId ?? 0,
            'city_district_id' => $districtId ?? 0,
            'sub_district_id' => $subDistrictId,
            'sub_district' => $subName . ' ' . $districtName . ' ' . $provinceName,
            'city_district' => $districtName,
            'province_state' => $provinceName,
            'country' => 'Thailand',
            'zip_code' => (int) $zipCode,
            'ph_number' => $user->ph_number ?? $user->phone ?? '',
            'is_company_add' => '0',
            'company_name' => '',
            'branch' => '',
            'tax_id' => '',
            'company_address' => '',
            'status' => '1',
            'is_default' => '0',
            'created_at' => $now,
            'updated_at' => $now,
            'address_type' => '0',
            'sequence' => 0,
            'lat' => $lat ? (string) $lat : null,
            'long' => $long ? (string) $long : null,
        ]);
    }
}
