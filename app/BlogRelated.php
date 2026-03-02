<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlogRelated extends Model
{  
    protected $table = 'blog_related';
    public $timestamps = false;

  
    public static function updateBlogRelatedInfo($id, $related_data){
        Self::where('blog_id',$id)->delete();
        Self::insert($related_data);
    }    

}
