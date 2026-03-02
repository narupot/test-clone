<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryUnit extends Model
{
    protected  $table = 'category_unit';
    public $timestamps = false;
    //protected $guarded = array();

    protected $fillable = ['cat_id','unit_id'];
    
    
}
