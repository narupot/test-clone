<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterDistrict extends Model
{
    use SoftDeletes;

    protected $table = 'master_districts';

    protected $fillable = [
        'name_th', 'name_en', 'province_id'
    ];
    public function province()
    {
        return $this->belongsTo('App\MasterProvince', 'province_id', 'id');
    }

    public function subDistricts()
    {
        return $this->hasMany('App\MasterSubDistrict', 'district_id', 'id');
    }

     public function getNameAttribute()
     {
         return session('locale') == 'en' ? $this->name_en : $this->name_th;
     }
}