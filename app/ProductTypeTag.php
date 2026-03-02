<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTypeTag extends Model
{

    protected $table = 'product_type_tag';

    public $timestamps = false;

    protected $fillable = [
        'product_type_id',
        'tag',
        'created_at',
        'created_by',
        'tag_status',
        'updated_date',
        'updated_by',
    ];


}