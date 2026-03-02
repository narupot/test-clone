<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WmsPickupLog extends Model
{
    use HasFactory;

    protected $table = 'wms_pickup_logs';
    
    protected $fillable = [
        'pickup_time',
        'truck_plan',
        'total_orders',
        'updated_count',
        'failed_count',
        'failed_orders',
        'request_data',
        'status'
    ];

    protected $casts = [
        'truck_plan' => 'array',
        'failed_orders' => 'array',
        'request_data' => 'array',
        'pickup_time' => 'datetime'
    ];
}
