<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // เพิ่ม Use Auth

use App\DeliveryRegion;
use App\DeliveryRegionDetail;
use App\DeliveryTimeSlot;

class DeliveryTime extends Model
{
    protected $table = 'delivery_time';

    public $timestamps = false;

    public static function getDeliveryTime($delivery_type = 'buyer_address')
    {
        return Self::where('delivery_type', $delivery_type)->first();
    }

    public static function getDeliverYType($shipping_method)
    {
        switch ($shipping_method) {
            case '1':
                $type = 'pickup_center';
                break;
            case '2':
                $type = 'shop_address';
                break;
            case '3':
                $type = 'buyer_address';
                break;

            default:
                $type = '';
                break;
        }
        return $type;
    }

    // --- ฟังก์ชันอัปเดต (ตัดส่วนลบเขตอื่นออกแล้ว) ---
    // public static function updateDeliveryTime($request)
    // {


    //     if ($request->has('delivery_time_after') && $request->has('prepare_time_before')) {
    //         return self::saveLegacyFormat($request);
    //     }

    //     // --- เริ่ม Logic สำหรับระบบใหม่ (SMM Tables) ---
    //     DB::beginTransaction();
    //     try {
    //         $adminId = Auth::guard('admin_user')->user()->id ?? 0;
    //         $currentDate = date('Y-m-d H:i:s');

    //         // -------------------------------------------------------------
    //         // 1. กำหนด reg_type ให้ชัดเจน

    //         $regType = $request->filled('reg_type')
    //             ? $request->reg_type
    //             : (($request->deliveryType == 'pickup') ? 2 : 3);

    //         // 2. จัดการตารางแม่ (Region)
    //         $regionData = [
    //             'reg_name'   => $request->region_name,
    //             'reg_type'   => $regType,
    //             'status'     => $request->Status ?? 1,
    //             'dc_address' => $request->pickup_address ?? '',
    //             'dc_tel'     => $request->pickup_phone ?? '',
    //             'updated_by' => $adminId,
    //             'updated_at' => $currentDate
    //         ];

    //         if (!empty($request->reg_id)) {
    //             // กรณีแก้ไข
    //             $region = DeliveryRegion::find($request->reg_id);
    //             if ($region) {
    //                 $region->update($regionData);
    //             } else {
    //                 $regionData['created_by'] = $adminId;
    //                 $regionData['created_at'] = $currentDate;
    //                 $region = DeliveryRegion::create($regionData);
    //             }
    //         } else {
    //             // กรณีสร้างใหม่
    //             $regionData['created_by'] = $adminId;
    //             $regionData['created_at'] = $currentDate;
    //             $region = DeliveryRegion::create($regionData);
    //         }

    //         $regId = $region->reg_id;

    //         // 3. รวบรวม ID ตำบลที่จะบันทึก (Subdistricts)
    //         $finalSubdistrictIds = [];

    //         if (!empty($request->selected_subdistricts)) {
    //             $finalSubdistrictIds = is_array($request->selected_subdistricts)
    //                 ? $request->selected_subdistricts
    //                 : explode(',', $request->selected_subdistricts);
    //         }

    //         if (!empty($request->selected_geographies)) {

    //             $gIds = is_array($request->selected_geographies)
    //                 ? $request->selected_geographies
    //                 : explode(',', $request->selected_geographies);

    //             $subFromGeos = DB::table('master_sub_districts as sd')
    //                 ->join('master_districts as d', 'sd.district_id', '=', 'd.id')
    //                 ->join('master_provinces as p', 'd.province_id', '=', 'p.id')
    //                 ->whereIn('p.geography_id', $gIds)
    //                 ->pluck('sd.id')
    //                 ->toArray();

    //             $finalSubdistrictIds = array_merge($finalSubdistrictIds, $subFromGeos);
    //         }

    //         // ข. กรณีเหมา "ทั้งจังหวัด"
    //         if (!empty($request->selected_provinces)) {
    //             $pIds = is_array($request->selected_provinces)
    //                 ? $request->selected_provinces
    //                 : explode(',', $request->selected_provinces);
    //             $subFromProvinces = DB::table('master_sub_districts as sd')
    //                 ->join('master_districts as d', 'sd.district_id', '=', 'd.id')
    //                 ->whereIn('d.province_id', $pIds)
    //                 ->pluck('sd.id')
    //                 ->toArray();
    //             $finalSubdistrictIds = array_merge($finalSubdistrictIds, $subFromProvinces);
    //         }

    //         // ค. กรณีเหมา "ทั้งอำเภอ"
    //         if (!empty($request->selected_districts)) {
    //             $dIds = is_array($request->selected_districts)
    //                 ? $request->selected_districts
    //                 : explode(',', $request->selected_districts);
    //             $subFromDistricts = DB::table('master_sub_districts')
    //                 ->whereIn('district_id', $dIds)
    //                 ->pluck('id')
    //                 ->toArray();
    //             $finalSubdistrictIds = array_merge($finalSubdistrictIds, $subFromDistricts);
    //         }

    //         // ตัด ID ซ้ำออก
    //         $finalSubdistrictIds = array_values(array_unique(array_filter($finalSubdistrictIds)));

    //         // ----------------------------------------------------------------------
    //         // [จุดสำคัญ] ลบตำบลนี้ออกจาก "เขตอื่น" ที่มี "reg_type เดียวกัน" เท่านั้น
    //         // ----------------------------------------------------------------------
    //         if (!empty($finalSubdistrictIds)) {

    //             // 1. หา Region ID อื่นๆ ที่เป็น Type เดียวกัน (ไม่รวมตัวเอง)
    //             $otherRegionIdsSameType = DeliveryRegion::where('reg_type', $regType)
    //                 ->where('reg_id', '!=', $regId)
    //                 ->pluck('reg_id')
    //                 ->toArray();

    //             // 2. สั่งลบตำบลเหล่านั้นออกจาก Region พวกนั้น (เพื่อกันซ้ำใน Type เดียวกัน)
    //             if (!empty($otherRegionIdsSameType)) {
    //                 DeliveryRegionDetail::whereIn('region_id', $otherRegionIdsSameType)
    //                     ->whereIn('subdistrict_id', $finalSubdistrictIds)
    //                     ->delete();
    //             }
    //         }


    //         // --- 4. บันทึกลงตาราง smm_delivery_region_detail ---

    //         // ลบข้อมูลเก่าของ "Region นี้" ทิ้งก่อน (เพื่อ Update ใหม่)
    //         DeliveryRegionDetail::where('region_id', $regId)->delete();

    //         if (!empty($finalSubdistrictIds)) {

    //             // ดึงข้อมูล zip_code 
    //             $masterData = DB::table('master_sub_districts')
    //                 ->whereIn('id', $finalSubdistrictIds)
    //                 ->pluck('zip_code', 'id');

    //             $detailsData = [];
    //             foreach ($finalSubdistrictIds as $subId) {
    //                 $zip = $masterData[$subId] ?? 0;

    //                 $detailsData[] = [
    //                     'region_id'      => $regId,
    //                     'subdistrict_id' => $subId,
    //                     'postcode'       => $zip,
    //                     'status'         => 1,
    //                     'created_by'     => $adminId,
    //                     'created_at'     => $currentDate,
    //                     'updated_by'     => $adminId,
    //                     'updated_at'     => $currentDate,
    //                 ];
    //             }

    //             if (!empty($detailsData)) {
    //                 foreach (array_chunk($detailsData, 500) as $chunk) {
    //                     DB::table('delivery_region_detail')->insert($chunk);
    //                 }
    //             }
    //         }

    //         // 5. จัดการรอบเวลา (Time Slots)
    //         DeliveryTimeSlot::where('reg_id', $regId)->delete();

    //         if (!empty($request->cutoff_time)) {
    //             $slotsData = [];
    //             foreach ($request->cutoff_time as $key => $cutoffVal) {
    //                 if (empty($cutoffVal)) continue;

    //                 $deliDays = (int)($request->delivery_day[$key] ?? 0);

    //                 $slotsData[] = [
    //                     'reg_id'             => $regId,
    //                     'del_t_s_name'       => 'รอบ ' . ($key + 1),
    //                     'order_cutoff_time'  => $cutoffVal,
    //                     'seller_print_active' => $request->print_active_hour[$key] ?? 1,
    //                     'seller_start_deli_time' => self::calculateDiffInMinutes($cutoffVal, $request->seller_start[$key] ?? '00:00'),
    //                     'seller_end_deli_time'  => self::calculateDiffInMinutes($cutoffVal, $request->seller_end[$key] ?? '00:00'),
    //                     'deli_plus_days'        => $deliDays,
    //                     'start_deli_time'       => self::calculateDiffInMinutes($cutoffVal, $request->delivery_start[$key] ?? '00:00', $deliDays),
    //                     'end_deli_time'         => self::calculateDiffInMinutes($cutoffVal, $request->delivery_end[$key] ?? '00:00', $deliDays),
    //                     'status'             => $request->is_active[$key] ? ($request->is_active[$key] ?? 1) : 0,
    //                     'created_by'         => $adminId,
    //                     'created_at'         => $currentDate,
    //                     'updated_by'         => $adminId,
    //                     'updated_at'         => $currentDate,
    //                 ];
    //             }

    //             if (!empty($slotsData)) {
    //                 DeliveryTimeSlot::insert($slotsData);
    //             }
    //         }

    //         DB::commit();
    //         return true;
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         Log::error("Error updateDeliveryTime: " . $e->getMessage());
    //         return false;
    //     }
    // }

    // public static function updateDeliveryTime($request)
    // {
    //     // 0. เช็คระบบเก่า (Legacy)
    //     if ($request->has('delivery_time_after') && $request->has('prepare_time_before')) {
    //         return self::saveLegacyFormat($request);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $adminId = Auth::guard('admin_user')->user()->id ?? 0;
    //         $currentDate = date('Y-m-d H:i:s');
    //         $regId = $request->reg_id;
    //         $isUpdate = !empty($regId);

    //         $regType = $request->filled('reg_type')
    //             ? $request->reg_type
    //             : (($request->deliveryType == 'pickup') ? 2 : 3);

    //         $regionData = [
    //             'reg_name'   => $request->region_name,
    //             'reg_type'   => $regType,
    //             'status'     => $request->Status ?? 1,
    //             'dc_address' => $request->pickup_address ?? '',
    //             'dc_tel'     => $request->pickup_phone ?? '',
    //             'updated_by' => $adminId,
    //             'updated_at' => $currentDate
    //         ];

    //         if ($isUpdate) {
    //             $region = DeliveryRegion::findOrFail($regId);
    //             $region->update($regionData);
    //         } else {
    //             $regionData['created_by'] = $adminId;
    //             $regionData['created_at'] = $currentDate;
    //             $region = DeliveryRegion::create($regionData);
    //             $regId = $region->reg_id;
    //         }

    //         $finalSubdistrictIds = self::processSubdistricts($request);

    //         if (!empty($finalSubdistrictIds)) {

    //             $provinceIds = DB::table('master_sub_districts')
    //                 ->join('master_districts', 'master_sub_districts.district_id', '=', 'master_districts.id')
    //                 ->whereIn('master_sub_districts.id', $finalSubdistrictIds)
    //                 ->distinct()
    //                 ->pluck('master_districts.province_id');

    //             if ($provinceIds->isNotEmpty()) {
    //                 \App\MasterProvince::whereIn('id', $provinceIds)
    //                     ->update([
    //                         'status' => 1,
    //                         'updated_at' => $currentDate
    //                     ]);
    //             }

    //             $otherRegionIds = DeliveryRegion::where('reg_type', $regType)
    //                 ->where('reg_id', '!=', $regId)
    //                 ->pluck('reg_id');

    //             if ($otherRegionIds->isNotEmpty()) {
    //                 DeliveryRegionDetail::whereIn('region_id', $otherRegionIds)
    //                     ->whereIn('subdistrict_id', $finalSubdistrictIds)
    //                     ->delete();
    //             }

    //             DeliveryRegionDetail::where('region_id', $regId)->delete();

    //             $masterData = DB::table('master_sub_districts')
    //                 ->whereIn('id', $finalSubdistrictIds)
    //                 ->pluck('zip_code', 'id');

    //             $detailsData = [];
    //             foreach ($finalSubdistrictIds as $subId) {
    //                 $detailsData[] = [
    //                     'region_id'      => $regId,
    //                     'subdistrict_id' => $subId,
    //                     'postcode'       => $masterData[$subId] ?? 0,
    //                     'status'         => 1,
    //                     'created_by'     => $adminId,
    //                     'created_at'     => $currentDate,
    //                     'updated_by'     => $adminId,
    //                     'updated_at'     => $currentDate,
    //                 ];
    //             }

    //             foreach (array_chunk($detailsData, 500) as $chunk) {
    //                 DB::table('delivery_region_detail')->insert($chunk);
    //             }
    //         }

    //         $existingSlotIdsInDb = DeliveryTimeSlot::where('reg_id', $regId)->pluck('del_t_s_id')->toArray();
    //         $keptSlotIds = [];

    //         if ($request->has('cutoff_time') && is_array($request->cutoff_time)) {
    //             foreach ($request->cutoff_time as $key => $cutoffVal) {
    //                 if (empty($cutoffVal)) continue;

    //                 try {
    //                     $deliDays      = (int)($request->delivery_day[$key] ?? 0);
    //                     $startTimeRaw  = $request->delivery_start[$key] ?? '00:00';
    //                     $endTimeRaw    = $request->delivery_end[$key] ?? '00:00';
    //                     $sellerStart   = $request->seller_start[$key] ?? '00:00';
    //                     $sellerEnd     = $request->seller_end[$key] ?? '00:00';
    //                     $printHour     = $request->print_active_hour[$key] ?? 1;
    //                     $isActive      = (isset($request->is_active[$key]) && $request->is_active[$key] == 1) ? 1 : 0;
    //                     $currentSlotId = $request->slot_id[$key] ?? null;

    //                     $slotData = [
    //                         'reg_id'                 => $regId,
    //                         'del_t_s_name'           => 'รอบ ' . ($key + 1),
    //                         'order_cutoff_time'      => $cutoffVal,
    //                         'seller_print_active'    => $printHour,
    //                         'seller_start_deli_time' => self::calculateDiffInMinutes($cutoffVal, $sellerStart),
    //                         'seller_end_deli_time'   => self::calculateDiffInMinutes($cutoffVal, $sellerEnd),
    //                         'deli_plus_days'         => $deliDays,
    //                         'start_deli_time'        => self::calculateDiffInMinutes($cutoffVal, $startTimeRaw, $deliDays),
    //                         'end_deli_time'          => self::calculateDiffInMinutes($cutoffVal, $endTimeRaw, $deliDays),
    //                         'status'                 => $isActive,
    //                         'updated_by'             => $adminId,
    //                         'updated_at'             => $currentDate
    //                     ];

    //                     if (!empty($currentSlotId) && in_array($currentSlotId, $existingSlotIdsInDb)) {
    //                         $slot = DeliveryTimeSlot::find($currentSlotId);
    //                         if ($slot) {
    //                             $slot->update($slotData);
    //                             $keptSlotIds[] = $currentSlotId;

    //                             // ดึงออเดอร์ใน Slot นี้
    //                             $ordersToUpdate = \App\Order::where('del_t_s_id', $currentSlotId)
    //                                 ->whereIn('order_status', [1, 2])
    //                                 ->get();

    //                             foreach ($ordersToUpdate as $order) {

    //                                 $orderDate     = \Carbon\Carbon::parse($order->created_at, 'Asia/Bangkok');
    //                                 $cutoffTime    = $slot->order_cutoff_time;
    //                                 $deliPlusDays  = (int)($slot->deli_plus_days ?? 0);

    //                                 $cutoffDateTime = \Carbon\Carbon::parse(
    //                                     $orderDate->format('Y-m-d') . ' ' . $cutoffTime,
    //                                     'Asia/Bangkok'
    //                                 );

    //                                 if ($orderDate->gt($cutoffDateTime)) {
    //                                     \Log::info("SKIPPED (Over Cutoff): Order #{$order->id}");
    //                                     continue;
    //                                 }

    //                                 // ----------------------------
    //                                 // CUSTOMER PICKUP TIME
    //                                 // ----------------------------

    //                                 $baseDate = $orderDate->copy()
    //                                     ->startOfDay()
    //                                     ->addDays($deliPlusDays);

    //                                 $cutoffBase = \Carbon\Carbon::parse(
    //                                     $baseDate->format('Y-m-d') . ' ' . $cutoffTime,
    //                                     'Asia/Bangkok'
    //                                 );

    //                                 $newPickupTime = $cutoffBase->copy()
    //                                     ->addMinutes((int)$slot->start_deli_time % 1440);

    //                                 $newEndPickupTime = $cutoffBase->copy()
    //                                     ->addMinutes((int)$slot->end_deli_time % 1440);

    //                                 $order->update([
    //                                     'pickup_time'     => $newPickupTime->toDateTimeString(),
    //                                     'end_pickup_time' => $newEndPickupTime->toDateTimeString(),
    //                                     'updated_at'      => $currentDate
    //                                 ]);

    //                                 // ----------------------------
    //                                 // SELLER / DC DELIVERY TIME
    //                                 // ----------------------------

    //                                 $startMinutes = (int)($slot->seller_start_deli_time ?? 0);
    //                                 $endMinutes   = (int)($slot->seller_end_deli_time ?? 0);

    //                                 $sellerBaseDateTime = $orderDate->copy()
    //                                     ->startOfDay()
    //                                     ->addDays($deliPlusDays)
    //                                     ->setTimeFromTimeString($cutoffTime);

    //                                 $dcStartTime = $sellerBaseDateTime->copy()
    //                                     ->addMinutes($startMinutes);

    //                                 if ($endMinutes < $startMinutes) {
    //                                     $dcEndTime = $sellerBaseDateTime->copy()
    //                                         ->addMinutes($endMinutes + 1440);
    //                                 } else {
    //                                     $dcEndTime = $sellerBaseDateTime->copy()
    //                                         ->addMinutes($endMinutes);
    //                                 }

    //                                 DB::table('order_shop')
    //                                     ->where('order_id', $order->id)
    //                                     ->update([
    //                                         'dc_delivery_starttime' => $dcStartTime->toDateTimeString(),
    //                                         'dc_delivery_endtime'   => $dcEndTime->toDateTimeString(),
    //                                         'updated_at'            => $currentDate
    //                                     ]);

    //                                 \Log::info("SYNC SUCCESS: Order #{$order->id}");
    //                             }

    //                         }
    //                     } else {
    //                         $slotData['created_by'] = $adminId;
    //                         $slotData['created_at'] = $currentDate;
    //                         $newSlot = DeliveryTimeSlot::create($slotData);
    //                         $keptSlotIds[] = $newSlot->del_t_s_id;
    //                     }
    //                 } catch (\Exception $slotEx) {
    //                     \Log::error("Slot Save Failed at Row [{$key}]: " . $slotEx->getMessage());
    //                     throw $slotEx;
    //                 }
    //             }
    //         }

    //         $slotsToDelete = array_diff($existingSlotIdsInDb, $keptSlotIds);
    //         if (!empty($slotsToDelete)) {
    //             DeliveryTimeSlot::whereIn('del_t_s_id', $slotsToDelete)->delete();
    //         }

    //         DB::commit();
    //         return true;
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         Log::error("Error updateDeliveryTime: " . $e->getMessage());
    //         return false;
    //     }
    // }

public static function updateDeliveryTime($request)
{
    // 0. เช็คระบบเก่า (Legacy)
    if ($request->has('delivery_time_after') && $request->has('prepare_time_before')) {
        return self::saveLegacyFormat($request);
    }

    DB::beginTransaction();
    try {
        $adminId = Auth::guard('admin_user')->user()->id ?? 0;
        $currentDate = date('Y-m-d H:i:s');
        $regId = $request->reg_id;
        $isUpdate = !empty($regId);
        $requestStatus = $request->Status ?? 1; // สถานะที่ส่งมาจากหน้าบ้าน

        $regType = $request->filled('reg_type')
            ? $request->reg_type
            : (($request->deliveryType == 'pickup') ? 2 : 3);

        $regionData = [
            'reg_name'   => $request->region_name,
            'reg_type'   => $regType,
            'status'     => $requestStatus,
            'dc_address' => $request->pickup_address ?? '',
            'dc_tel'     => $request->pickup_phone ?? '',
            'updated_by' => $adminId,
            'updated_at' => $currentDate
        ];

        // เก็บข้อมูลจังหวัดเดิมไว้ก่อนอัปเดต (เพื่อเช็คกรณีลดพื้นที่หรือปิดเขต)
        $oldProvinceIds = collect();
        if ($isUpdate) {
            $region = DeliveryRegion::findOrFail($regId);
            $region->update($regionData);
            
            $oldProvinceIds = DB::table('delivery_region_detail')
                ->join('master_sub_districts', 'delivery_region_detail.subdistrict_id', '=', 'master_sub_districts.id')
                ->join('master_districts', 'master_sub_districts.district_id', '=', 'master_districts.id')
                ->where('delivery_region_detail.region_id', $regId)
                ->distinct()
                ->pluck('master_districts.province_id');
        } else {
            $regionData['created_by'] = $adminId;
            $regionData['created_at'] = $currentDate;
            $region = DeliveryRegion::create($regionData);
            $regId = $region->reg_id;
        }

        // --- 1. Resolve Subdistrict IDs (กรณีเลือกยกจังหวัด / ยกอำเภอ / รายตำบล) ---
        $resolvedSubIds = self::processSubdistricts($request); 

        // กวาดตำบลทั้งหมดจาก "จังหวัด" (selected_provinces[])
        if ($request->filled('selected_provinces')) {
            $pSubs = DB::table('master_sub_districts')
                ->join('master_districts', 'master_sub_districts.district_id', '=', 'master_districts.id')
                ->whereIn('master_districts.province_id', $request->selected_provinces)
                ->pluck('master_sub_districts.id')->toArray();
            $resolvedSubIds = array_merge($resolvedSubIds, $pSubs);
        }

        // กวาดตำบลทั้งหมดจาก "อำเภอ" (selected_districts[])
        if ($request->filled('selected_districts')) {
            $dSubs = DB::table('master_sub_districts')
                ->whereIn('district_id', $request->selected_districts)
                ->pluck('id')->toArray();
            $resolvedSubIds = array_merge($resolvedSubIds, $dSubs);
        }

        $finalSubdistrictIds = array_unique($resolvedSubIds);

        // --- 2. จัดการข้อมูลพื้นที่ขาย (Region Detail) ---
        $newProvinceIds = collect();

        if (!empty($finalSubdistrictIds)) {
            // หาว่าตำบลที่เลือกมา ทั้งหมดอยู่ในจังหวัดอะไรบ้าง
            $newProvinceIds = DB::table('master_sub_districts')
                ->join('master_districts', 'master_sub_districts.district_id', '=', 'master_districts.id')
                ->whereIn('master_sub_districts.id', $finalSubdistrictIds)
                ->distinct()
                ->pluck('master_districts.province_id');

            // ถ้าสถานะเขตเปิด (Status=1) ให้ไปเปิดสถานะจังหวัดด้วย
            if ($requestStatus == 1 && $newProvinceIds->isNotEmpty()) {
                \App\MasterProvince::whereIn('id', $newProvinceIds)->update([
                    'status' => 1,
                    'updated_at' => $currentDate
                ]);
            }

            // ลบตำบลเหล่านี้ออกจากเขตอื่นที่ประเภทเดียวกัน (ป้องกันพื้นที่ทับซ้อน)
            $otherRegionIds = DeliveryRegion::where('reg_type', $regType)
                ->where('reg_id', '!=', $regId)
                ->pluck('reg_id');

            if ($otherRegionIds->isNotEmpty()) {
                DeliveryRegionDetail::whereIn('region_id', $otherRegionIds)
                    ->whereIn('subdistrict_id', $finalSubdistrictIds)
                    ->delete();
            }

            // ล้างข้อมูลเก่าของเขตนี้และ Insert ใหม่
            DeliveryRegionDetail::where('region_id', $regId)->delete();

            $masterData = DB::table('master_sub_districts')
                ->whereIn('id', $finalSubdistrictIds)
                ->pluck('zip_code', 'id');

            $detailsData = [];
            foreach ($finalSubdistrictIds as $subId) {
                $detailsData[] = [
                    'region_id'      => $regId,
                    'subdistrict_id' => $subId,
                    'postcode'       => $masterData[$subId] ?? 0,
                    'status'         => 1,
                    'created_by'     => $adminId,
                    'created_at'     => $currentDate,
                    'updated_by'     => $adminId,
                    'updated_at'     => $currentDate,
                ];
            }

            foreach (array_chunk($detailsData, 500) as $chunk) {
                DB::table('delivery_region_detail')->insert($chunk);
            }
        }

        // --- 3. ตรวจสอบสถานะจังหวัด (MasterProvince Status Sync) ---
        // เช็คจังหวัดที่เคยมีแต่ตอนนี้ไม่มีแล้ว OR ทุกจังหวัดในเขตนี้หากมีการกดปิดเขต (Status=0)
        $checkProvinceIds = $oldProvinceIds->diff($newProvinceIds);
        if ($requestStatus == 0) {
            $checkProvinceIds = $checkProvinceIds->merge($newProvinceIds)->unique();
        }

        foreach ($checkProvinceIds as $pId) {
            $isStillUsed = DB::table('delivery_region_detail')
                ->join('delivery_region', 'delivery_region_detail.region_id', '=', 'delivery_region.reg_id')
                ->join('master_sub_districts', 'delivery_region_detail.subdistrict_id', '=', 'master_sub_districts.id')
                ->join('master_districts', 'master_sub_districts.district_id', '=', 'master_districts.id')
                ->where('master_districts.province_id', $pId)
                ->where('delivery_region.reg_id', '!=', $regId)
                ->where('delivery_region.status', 1)
                ->where('delivery_region.st_del', 0)
                ->exists();

            if (!$isStillUsed) {
                \App\MasterProvince::where('id', $pId)->update(['status' => 0]);
            }
        }

        // --- 4. จัดการ Time Slots และ Sync Orders ---
        $existingSlotIdsInDb = DeliveryTimeSlot::where('reg_id', $regId)->pluck('del_t_s_id')->toArray();
        $keptSlotIds = [];

        if ($request->has('cutoff_time') && is_array($request->cutoff_time)) {
            foreach ($request->cutoff_time as $key => $cutoffVal) {
                if (empty($cutoffVal)) continue;

                $deliDays      = (int)($request->delivery_day[$key] ?? 0);
                $startTimeRaw  = $request->delivery_start[$key] ?? '00:00';
                $endTimeRaw    = $request->delivery_end[$key] ?? '00:00';
                $sellerStart   = $request->seller_start[$key] ?? '00:00';
                $sellerEnd     = $request->seller_end[$key] ?? '00:00';
                $printHour     = $request->print_active_hour[$key] ?? 1;
                $isActive      = (isset($request->is_active[$key]) && $request->is_active[$key] == 1) ? 1 : 0;
                $currentSlotId = $request->slot_id[$key] ?? null;

                $slotData = [
                    'reg_id'                 => $regId,
                    'del_t_s_name'           => 'รอบ ' . ($key + 1),
                    'order_cutoff_time'      => $cutoffVal,
                    'seller_print_active'    => $printHour,
                    'seller_start_deli_time' => self::calculateDiffInMinutes($cutoffVal, $sellerStart),
                    'seller_end_deli_time'   => self::calculateDiffInMinutes($cutoffVal, $sellerEnd),
                    'deli_plus_days'         => $deliDays,
                    'start_deli_time'        => self::calculateDiffInMinutes($cutoffVal, $startTimeRaw, $deliDays),
                    'end_deli_time'          => self::calculateDiffInMinutes($cutoffVal, $endTimeRaw, $deliDays),
                    'status'                 => $isActive,
                    'updated_by'             => $adminId,
                    'updated_at'             => $currentDate
                ];

                if (!empty($currentSlotId) && in_array($currentSlotId, $existingSlotIdsInDb)) {
                    $slot = DeliveryTimeSlot::find($currentSlotId);
                    if ($slot) {
                        $slot->update($slotData);
                        $keptSlotIds[] = $currentSlotId;
                        
                        // Sync Orders (เฉพาะสถานะรอดำเนินการ 1, 2)
                        $ordersToUpdate = \App\Order::where('del_t_s_id', $currentSlotId)
                            ->whereIn('order_status', [1, 2])
                            ->get();

                        foreach ($ordersToUpdate as $order) {
                            $orderDate = \Carbon\Carbon::parse($order->created_at, 'Asia/Bangkok');
                            $cutoffDateTime = \Carbon\Carbon::parse($orderDate->format('Y-m-d') . ' ' . $slot->order_cutoff_time, 'Asia/Bangkok');

                            if ($orderDate->gt($cutoffDateTime)) continue;

                            $baseDate = $orderDate->copy()->startOfDay()->addDays($slot->deli_plus_days);
                            $order->update([
                                'pickup_time'     => $baseDate->copy()->addMinutes((int)$slot->start_deli_time % 1440)->toDateTimeString(),
                                'end_pickup_time' => $baseDate->copy()->addMinutes((int)$slot->end_deli_time % 1440)->toDateTimeString(),
                                'updated_at'      => $currentDate
                            ]);

                            // Sync order_shop (DC Times)
                            $sellerBase = $orderDate->copy()->startOfDay()->addDays($slot->deli_plus_days)->setTimeFromTimeString($slot->order_cutoff_time);
                            $dcStart = $sellerBase->copy()->addMinutes((int)$slot->seller_start_deli_time);
                            $dcEnd   = $sellerBase->copy()->addMinutes((int)$slot->seller_end_deli_time);
                            
                            if ($slot->seller_end_deli_time < $slot->seller_start_deli_time) {
                                $dcEnd->addDay();
                            }

                            DB::table('order_shop')->where('order_id', $order->id)->update([
                                'dc_delivery_starttime' => $dcStart->toDateTimeString(),
                                'dc_delivery_endtime'   => $dcEnd->toDateTimeString(),
                                'updated_at'            => $currentDate
                            ]);
                        }
                    }
                } else {
                    $slotData['created_by'] = $adminId;
                    $slotData['created_at'] = $currentDate;
                    $newSlot = DeliveryTimeSlot::create($slotData);
                    $keptSlotIds[] = $newSlot->del_t_s_id;
                }
            }
        }

        $slotsToDelete = array_diff($existingSlotIdsInDb, $keptSlotIds);
        if (!empty($slotsToDelete)) {
            DeliveryTimeSlot::whereIn('del_t_s_id', $slotsToDelete)->delete();
        }

        DB::commit();
        return true;
    } catch (\Exception $e) {
        DB::rollback();
        Log::error("Error updateDeliveryTime: " . $e->getMessage() . " line " . $e->getLine());
        return false;
    }
}

    private static function processSubdistricts($request)
    {
        $ids = [];
        if (!empty($request->selected_subdistricts)) {
            $ids = is_array($request->selected_subdistricts) ? $request->selected_subdistricts : explode(',', $request->selected_subdistricts);
        }

        return array_values(array_unique(array_filter($ids)));
    }

    private static function saveLegacyFormat($request)
    {
        $delivery_obj = \App\DeliveryTime::getDeliveryTime($request->delivery_type);

        if (!$delivery_obj) {
            $delivery_obj   = new \App\DeliveryTime;
        }
        $slot_string = '';
        if ($request->time_slot) {
            $slot_array = array_map('intval', array_filter(array_unique($request->time_slot), 'strlen'));
            sort($slot_array);
            $slot_string = implode(',', $slot_array);
        }
        $delivery_obj->delivery_type = $request->delivery_type;
        $delivery_obj->delivery_time_after = $request->delivery_time_after;
        $delivery_obj->prepare_time_before = $request->prepare_time_before;
        $delivery_obj->time_slot = $slot_string;
        $delivery_obj->updated_at = date('Y-m-d H:i:s');
        $delivery_obj->updated_by = Auth::guard('admin_user')->user()->id ?? 0;
        $delivery_obj->save();
        return true;
    }

    // ฟังก์ชันคำนวณส่วนต่างเวลาเป็นนาที (Target - Base)
    private static function calculateDiffInMinutes($baseTime, $targetTime, $plusDays = 0)
    {
        if (empty($baseTime) || empty($targetTime)) return 0;

        // แปลงเวลา "HH:mm" เป็น array [HH, mm]
        $baseParts = explode(':', $baseTime);
        $targetParts = explode(':', $targetTime);

        // คำนวณเป็นนาทีทั้งหมดเริ่มจาก 00:00
        $baseMinutes = ((int)$baseParts[0] * 60) + (int)$baseParts[1];
        $targetMinutes = ((int)$targetParts[0] * 60) + (int)$targetParts[1];

        // หาผลต่าง
        $diff = $targetMinutes - $baseMinutes;

        // กรณีข้ามวัน (เช่น ตัดรอบ 23:00 แต่เริ่มส่ง 01:00 ของวันถัดไป) ผลลัพธ์จะติดลบ
        // ให้บวก 1440 นาที (24 ชั่วโมง) เข้าไป
        if ($diff < 0) {
            $diff += 1440;
        }

        // บวกจำนวนวันที่ระบุเพิ่ม (ถ้ามี)
        if ($plusDays > 0) {
            $diff += ($plusDays * 1440);
        }

        return $diff;
    }
}
