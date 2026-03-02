<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;

class BlogTagList extends Model
{
    protected $table = 'blog_tag_list';

    public static function getAllTagsByMatchingString($str){

    	$data = self::where('tag_title','LIKE','%'.$str.'%')->select('id','tag_title')->get()->toArray();
        return $data;
    }

    public static function getAllTags(){

        $data = self::select('id','tag_title')->get()->toArray();
        return $data;
    }
}
