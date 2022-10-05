<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;
class Category extends Model
{
    protected  $table = 'category';
    
    protected $guarded = [];

    public function category(){
         return $this->hasMany('App\Category', 'parent_id', 'id')->select('id', 'url', 'parent_id','total_products');
    } 
    
    public function categorydesc(){

      return $this->hasOne('App\CategoryDesc', 'cat_id', 'id')->where('lang_id', session('default_lang'))->select('id','category_name','cat_id'); 
    }

    public function getCatDesc(){

      return $this->hasOne('App\CategoryDesc', 'cat_id', 'id')->where('lang_id', session('default_lang'))->select('id','category_name as name','cat_id'); 
    }

    public function categorydescShop(){
       return $this->hasOne('App\CategoryDesc', 'cat_id', 'id')->where('lang_id',session('default_lang')); 
    }    
    
    public function categorydesces(){
        return $this->hasMany('App\CategoryDesc', 'cat_id', 'id'); 
    }
    
    public function parent(){
        return $this->belongsTo(self::class, 'parent_id');
    }

    public static function getMainCategory(){
      return Self::where(['parent_id' => '0', 'status' => '1'])->get();
    }
    
    // public function getMainParentId($parent_id){        
    //   $sqlMenu = $this->parent()->where('parent_id',$parent_id)->get();        
    //   if ($sqlMenu->parent_id == 0) {            
    //     return $sqlMenu->parent_id;        
    //   } else {            
    //     return $this->getMainParentId($sqlMenu->parentId);        
    //   }   
    // }

    public function getcategoryDetail(){

       return $this->hasOne('App\CategoryDesc','cat_id','id')
              ->select('cat_id','category_name', 'cat_description', 'meta_title',  'meta_keyword',  'meta_description')
              ->where('lang_id', session('default_lang'));      
    } 

    public static function getActiveCategoryDetail($category_id = 0) {
        return self::select('id', 'url', 'parent_id')->with('categorydesc')->where(['status'=>'1', 'parent_id'=>$category_id])->get();
    }

    public static function getCategoryByid($ids){
      $categoryDetails = Self::whereIn('id',$ids)->pluck('id', 'id')->toArray();
      if(count($categoryDetails) > 0){
        return $categoryDetails;
      }else{
        return null;
      } 

    }

    public function descAll(){
       return $this->hasMany('App\CategoryDesc', 'cat_id', 'id'); 
    }

    public function Units(){
       return $this->hasMany('App\CategoryUnit', 'cat_id', 'id');
    }

    public static function categoryData($cat_id){
        return self::where('id',$cat_id)->with('descAll')->with('Units')->first(); 
    }
}
