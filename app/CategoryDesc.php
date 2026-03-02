<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryDesc extends Model
{
    protected  $table = 'category_desc';
    public $timestamps = false;
    protected $guarded = array();

    public function language(){
         return $this->hasOne('App\Language', 'id', 'lang_id');
    }
    
    
}
