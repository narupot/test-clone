<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CmsSliderCategory extends Model
{
    protected  $table = 'cms_slider_category';
    public $timestamps = false;


    public static function deleteCat($slider_id){
    	Self::where('cms_slider_id',$slider_id)->delete();
    }
}
