<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class FavoriteShop extends Model
{  
    protected $table = 'favorite_shop';
    public $timestamps = false;

    public function getShops(){
        return $this->hasOne('App\Shop', 'id', 'shop_id');  
    }

}
