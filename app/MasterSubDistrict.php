<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterSubDistrict extends Model
{
    use SoftDeletes;

    protected $table = 'master_sub_districts';

    protected $fillable = [
        'zip_code', 'name_th', 'name_en', 'district_id', 'lat', 'long'
    ];

    public function district()
    {
        return $this->belongsTo('App\MasterDistrict', 'district_id', 'id');
    }

     public function getNameAttribute()
     {
         return session('locale') == 'en' ? $this->name_en : $this->name_th;
     }
}