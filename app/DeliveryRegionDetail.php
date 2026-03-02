<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeliveryRegionDetail extends Model
{
    protected $table = 'delivery_region_detail';
    protected $primaryKey = 'deli_reg_detail_id';

    public $timestamps = false;

    protected $fillable = [
        'region_id',
        'subdistrict_id',
        'postcode',
        'status',
        'created_by',
        'updated_by',
       
    ];

    public function subdistrict()
    {
        return $this->belongsTo('App\CountrySubDistrict', 'subdistrict_id', 'id');
    }

    public function region()
    {
        return $this->belongsTo('App\DeliveryRegion', 'region_id');
    }
}