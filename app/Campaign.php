<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use SoftDeletes; 
    protected  $table = 'campaign';
    protected $dates = ['deleted_at'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($table) {
            $table->created_by = auth('admin_user')->id()??request()->ip();
            $table->created_at = now();
            $table->updated_at = now();
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

    public function megacampaign()
    {
        return $this->belongsTo(Campaign::class,'parent_id');
    }

    public function campaign()
    {
        return $this->hasMany(Campaign::class,'parent_id');
    }

    public function discountCodeCriteia()
    {
        return $this->hasMany(DiscountCodeCriteria::class,'campaign_id');
    }

}