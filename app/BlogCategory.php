<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;
use DB;

class BlogCategory extends Model {

    protected $table = 'blog_category';

    public function category(){
        return $this->hasMany('App\BlogCategory', 'parent_id', 'id')->select('id', 'url', 'parent_id');
    }    

    public static function getCategoryDetail($id) {
        return self::where(['id' => $id])->first();
    }

    public static function getAllCategory() {
        return self::where(['status' => '1'])->get();
    }

    public static function getMainCategory() {
        return self::where(['parent_id' => '0', 'status' => '1'])->get();
    }

    public function getCatDesc() {
        return $this->hasOne('App\BlogCategoryDesc', 'cat_id', 'id')->where('lang_id',session('default_lang'));
    }   

    public function blogcategorydesc(){

      $default_language = session('default_lang');
      return $this->hasOne('App\BlogCategoryDesc', 'cat_id', 'id')->where('lang_id', $default_language)->select('id','name','cat_id','comments','description','meta_title','meta_keyword','meta_description'); 
    }

    public function blogcategory(){
         return $this->hasMany('App\BlogCategory', 'parent_id', 'id')->select('id', 'url', 'parent_id');
    } 

    
    public static function getCategoryIdByUrl($url){

       return self::select('*')
              ->where('url', $url)->with('blogcategorydesc');
              
    }
                     
}
