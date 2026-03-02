<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDiscountCode extends Model
{
    use SoftDeletes;
    protected  $table = 'order_discount_code';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'order_id',
        'discount_code',
        'discount_code_criteria_id',
        'purchase_discount_amount',
        'shipping_discount_amount',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($table) {
            $table->created_by = auth()->id()??request()->ip();
        });

        static::updating(function ($table) {
            $table->updated_by = auth()->id()??request()->ip();
        });

        static::deleting(function ($table) {
            $table->deleted_by = auth()->id()??request()->ip(); 
            $table->deleted_at = now();
            $table->timestamps = false;
        });
    }

    public function discountCode(){
        return $this->belongsTo(DiscountCode::class,'discount_code','code');
    }
    public function order(){
        return $this->belongsTo(Order::class,'order_id');
    }
    public function criteria(){
        return $this->belongsTo(DiscountCodeCriteria::class,'discount_code_criteria_id');
    }
    
    
}