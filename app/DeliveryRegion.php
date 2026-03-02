<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeliveryRegion extends Model
{
    protected $table = 'delivery_region';
    protected $primaryKey = 'reg_id';

    public $timestamps = true;

    protected $fillable = [
        'reg_name',
        'reg_type',
        'status',
        'created_by',
        'updated_by',
        'st_del',
        'del_by',
        'del_at',
        'dc_address',
        'dc_tel'
    ];

    // ความสัมพันธ์กับตารางรายละเอียดพื้นที่
    public function details()
    {
        return $this->hasMany('App\DeliveryRegionDetail', 'region_id', 'reg_id');
    }

    // ความสัมพันธ์กับตารางรอบเวลา
    public function timeSlots()
    {
        return $this->hasMany('App\DeliveryTimeSlot', 'reg_id', 'reg_id');
    }
}