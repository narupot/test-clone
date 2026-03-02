<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class DiscountCodeCriteria extends Model
{
    use SoftDeletes; 
    protected  $table = 'discount_code_criteria';
    protected $primaryKey = 'id';
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
            $table->timestamps = false;
            $table->save();
        });
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class,'campaign_id');
    }
    public function discountCode()
    {
        return $this->hasMany(DiscountCode::class,'discount_code_criteria_id');
    }

    public function getDerivedStatusAttribute()
    {
        if ($this->status == false) {
            return [
                'badge_class'=>'badge-danger',
                'name_th'=>'ปิดการใช้งาน'
            ];
        }

        $now = Carbon::now();
        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);
        if ($this->status == true && $now->gt($end)) {
            return [
                'badge_class'=>'badge-danger',
                'name_th'=>'หมดเขต'
            ];
        }

        if ($this->status == true && $now->between($start, $end)) {
            $remaining_quantity = $this->discountCode->sum('remaining_quantity');
            if ($this->is_limit && $remaining_quantity == 0) {
                return [
                    'badge_class'=>'badge-danger',
                    'name_th'=>'หมด'
                ];
            }

            if ($this->is_limit == false || ($this->is_limit && $remaining_quantity > 0)) {
                return [
                    'badge_class'=>'badge-success',
                    'name_th'=>'ใช้งานได้'
                ];
            }
            
        }

        if ($this->status == true && $now->lt($start)) {
            return [
                'badge_class'=>'badge-primary',
                'name_th'=>'รอใช้งาน'
            ];
        }

        return ['badge_class'=>'','name_th'=>'-'];
    }

}