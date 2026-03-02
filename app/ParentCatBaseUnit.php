<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentCatBaseUnit extends Model
{
     protected $table = 'parent_cat_baseunit';

     public function unit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id', 'id')
                    ->with('unitdesc');
    }
}
