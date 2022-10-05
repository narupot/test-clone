<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BannerGroup extends Model
{
    protected  $table = 'banner_group';

    public function banners(){

    	return $this->hasMany('App\Banner', 'group_id', 'id');


    }


   
}
