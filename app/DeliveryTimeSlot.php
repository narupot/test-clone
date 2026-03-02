<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeliveryTimeSlot extends Model
{
    protected $table = 'delivery_time_slot';
    protected $primaryKey = 'del_t_s_id';

    protected $fillable = [
        'del_t_s_name',
        'order_cutoff_time',
        'seller_start_deli_time',
        'seller_end_deli_time',
        'deli_plus_days',
        'start_deli_time',
        'end_deli_time',
        'reg_id',
        'status',
        'seller_print_active',
        'created_by',
        'updated_by'
    ];
}