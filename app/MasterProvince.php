<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterProvince extends Model
{
    use SoftDeletes;

    protected $table = 'master_provinces';

   protected $fillable = [
        'name_th',
        'name_en',
        'geography_id',
        'status'
    ];

    public function districts()
    {
        return $this->hasMany('App\MasterDistrict', 'province_id', 'id');
    }

    public function getNameAttribute()
    {
        return session('locale') == 'en' ? $this->name_en : $this->name_th;
    }
    
    public function geography()
    {
        return $this->belongsTo('App\MasterGeography', 'geography_id', 'id');
    }
}