<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlogCat extends Model
{  
    protected $table = 'blog_cat';
    public $timestamps = false;

    public function getBlogCategoryDescription(){
    	return $this->hasOne('App\BlogCategoryDesc','cat_id','cat_id')->where('lang_id',session('default_lang'));
    }

    public function getAllCategories($blog_id){
    	$details = $this::where('blog_id',$blog_id)->with('getCategoryDescription')->get();
    	$categories = [];        
        foreach($details as $detail){
            $categories[]=$detail->getCategoryDescription->category_name;
        }
        return $categories;

    }

    public static function updateBlogInfo($id, $seller_cat_data){
        Self::where('blog_id',$id)->delete();
        Self::insert($seller_cat_data);
    }

    public function hasBlog(){
        return $this->hasOne('App\Blog','id','blog_id');
    }


    public function hasBlogDesc(){
        return $this->hasOne('App\BlogDescription','id','blog_id')->where('lang_id',session('default_lang'))->select('id','name','lang_id','blog_id');
    }  


    public function getBlogDesc(){
        return $this->hasOne('App\BlogDesc','blog_id','blog_id')->where('lang_id',session('default_lang'));
    }  


    public static function blogCatById($id){
        return self::select('id', 'blog_id', 'cat_id')->where(['blog_id'=>$id])->with('getBlogCategoryDescription')->get();
    } 

     public function getBlogCategory(){
        return $this->hasOne('App\BlogCategory','id','cat_id');
    }    

}
