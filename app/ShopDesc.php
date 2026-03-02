<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShopDesc extends Model
{
    protected $table = 'shop_desc';
    public $timestamps = false;
    protected $guarded = array();

    public function language(){
         return $this->hasOne('App\Language', 'id', 'lang_id');
    }
     
}
