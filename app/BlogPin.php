<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlogPin extends Model
{  
    protected $table = 'blog_pin';
    public $timestamps = false;

    public static function saveData($save){
        //var_dump($save); die;
        Self::insert($save);
    }

     public static function pinOutData($save){
            Self::where('blog_id',$save['blog_id'])->delete();
    }

    public function hasBlog(){
        return $this->hasOne('App\Blog','id','blog_id');
    }


    public function hasUser(){
        return $this->hasOne('App\User','id','user_id')->where('lang_id',session('default_lang'))->select('id','name','lang_id','blog_id');
    }  

}
