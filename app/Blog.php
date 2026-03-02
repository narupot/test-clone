<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Blog extends Model {

    protected $table = 'blog';  

    public function blogDesc() {       
        return $this->hasOne('App\BlogDesc', 'blog_id', 'id')->where('lang_id', session('admin_default_lang'));
    } 

    public function getBlogCategoryDescription(){
        return $this->hasOne('App\BlogCategoryDesc','cat_id','cat_id')->where('lang_id',session('default_lang'));
    }

    public function blogCat() {       
        return $this->hasMany('App\BlogCat', 'blog_id', 'id');
    } 

    public static function getBlog(){
    	return self::select('*')->with('blogDesc')->orderBy('created_at','desc')->get();
    }

    public static function getBlogList($id){
        return self::select('*')->whereNotIn('id',[$id])->with('blogDesc')->orderBy('created_at','desc')->get();
    }

    public static function getBlogbyId($id){
        return self::select('*',DB::raw('"1" as tags'))->where(['id'=>$id])->with(['blogDesc','blogSlider','blogDecses','blogCat','getBlogTag'=>function($query){
            $query->with('hasTags');
        }])->first();
    }

    public static function getBlogbyUrl($id){
        return self::select('*')->where(['url'=>$id])->first();
    }    

    public static function getLatestFeaturedBlog() {
        return self::where(['status'=>'1', 'publish'=>'1','features'=>'1'])->with('BlogDesc')->orderBy('publish_date', 'DESC')->first();
    }

    public static function getFeaturedBlog($limit=null, $ignore_id=[]) {
        if($limit > 0) {
            return self::where(['status'=>'1', 'publish'=>'1','features'=>'1'])->whereNotIn('id', $ignore_id)->with(['BlogDesc','blogCat'=>function($query){
                $query->with('getBlogCategoryDescription');
            }])->orderBy('updated_at', 'DESC')->take($limit)->get();
        }
        else {
            return self::where(['status'=>'1', 'publish'=>'1','features'=>'1'])->with(['BlogDesc','blogCat'=>function($query){
                $query->with('getBlogCategoryDescription');
            }])->orderBy('updated_at', 'DESC')->get();
        }
    } 

    public function blogSlider() {       
        return $this->hasMany('App\BlogSlider', 'blog_id', 'id');
    } 

    public function blogDecses() {       
        return $this->hasMany('App\BlogDesc', 'blog_id', 'id');
    }    

    public function getBlogTag(){
        return $this->hasMany('App\BlogTag', 'blog_id', 'id');
    }

    public function getBlogRelated(){
        return $this->hasMany('App\BlogRelated', 'blog_id', 'id');
    }

    public function hasPin(){
        return $this->hasMany('App\BlogPin', 'blog_id', 'id');
    }
            
}
