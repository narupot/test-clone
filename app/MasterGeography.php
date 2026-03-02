<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MasterGeography extends Model
{
    protected $table = 'master_geographies';
    public $timestamps = false;

    protected $fillable = ['name'];

    public function provinces()
    {
        return $this->hasMany('App\MasterProvince', 'geography_id', 'id');
    }
}