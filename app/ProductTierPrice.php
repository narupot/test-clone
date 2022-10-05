<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductTierPrice extends Model
{
    protected  $table = 'product_tier_price';
    protected $guarded = [];
    public $timestamps = false;
}
