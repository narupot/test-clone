<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscountCode extends Model
{
    use SoftDeletes; 
    protected  $table = 'discount_code';
    protected $dates = ['deleted_at'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($table) {
            $table->created_by = auth('admin_user')->id()??request()->ip();
        });

        static::updating(function ($table) {
            $table->updated_by = auth('admin_user')->id()??request()->ip();
        });

        static::deleting(function ($table) {
            $table->deleted_by = auth('admin_user')->id()??request()->ip(); 
            $table->deleted_at = now();
            $table->timestamps = false;
        });
    }

    public function criteria()
    {
        return $this->belongsTo(DiscountCodeCriteria::class,'discount_code_criteria_id');
    }

    public function order()
    {
        return $this->hasManyThrough(
            Order::class,
            OrderDiscountCode::class,
            'discount_code',      // foreign key ใน OrderDiscountCode ที่อ้างอิง discount_code
            'id',                 // primary key ของ Order
            'code',               // local key ใน DiscountCode
            'order_id'            // foreign key ใน OrderDiscountCode ที่อ้างอิง Order
        );
    }

}