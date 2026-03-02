<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlogCategoryDesc extends Model
{
    protected  $table = 'blog_category_desc';
    public $timestamps = false;
    protected $guarded = array();

    public function language(){
         return $this->hasOne('App\Language', 'id', 'lang_id');
    }
    
    public static function updateCategoryDesc($data_arr, $cat_id) {      

        foreach ($data_arr as $key=>$value){

            self::where(['cat_id'=>$cat_id, 'lang_id'=>$key])
                ->update(['name' => $value['name'], 'comments' => $value['comments'],'description' => $value['description'],'meta_title' => $value['meta_title'],'meta_keyword' => $value['meta_keyword'],'meta_description' => $value['meta_description']]);                  
        }
    } 
}
