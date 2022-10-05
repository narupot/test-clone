<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductDesc extends Model
{  
    protected $table = 'product_desc';
    public $timestamps = false;
    protected $guarded = [];
}
