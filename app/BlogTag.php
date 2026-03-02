<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlogTag extends Model
{  
    protected $table = 'blog_tag';
    public $timestamps = false;

  
    public static function updateBlogInfo($id, $seller_cat_data){
        Self::where('blog_id',$id)->delete();
        Self::insert($seller_cat_data);
    }


    public static function blogTagById($id){
        return self::select('id', 'blog_id','tags')->where(['blog_id'=>$id,'lang_id'=>session('default_lang')])->first();
    }   

    public function hasBlog(){
        return $this->hasOne('App\Blog','id','blog_id');
    }

    public function hasTags(){
        return $this->hasOne('App\BlogTagList','id','tag_id')->select('id','tag_title');
    }
    
    public static function getAllTagsByMatchingString($str){

        $data = self::where('tag_title','LIKE','%'.$str.'%')->select('id','tag_title')->get()->toArray();
        return $data;
    } 

}
