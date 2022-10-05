<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class BlogDesc extends Model {

    protected $table = 'blog_desc';

    public $timestamps = false; 

    public static function insertBlogDesc($data_arr, $blog_id) {      

        foreach ($data_arr as $key=>$value){
            
            $blogdesc = new BlogDesc;
            $blogdesc->blog_id = $blog_id;
            $blogdesc->lang_id = $value['lang_id'];
            $blogdesc->blog_title = $value['blog_title'];
            $blogdesc->blog_short_desc = $value['blog_short_desc']; 
            $blogdesc->blog_desc = $value['blog_desc'];                
            $blogdesc->meta_title = $value['meta_title'];  
            $blogdesc->meta_keyword = $value['meta_keyword'];
            $blogdesc->meta_desc = $value['meta_desc']; 
            $blogdesc->fbtitle = $value['fbtitle']; 
            $blogdesc->twtitle = $value['twtitle']; 
            $blogdesc->institle = $value['institle']; 
            $blogdesc->fbdesc = $value['fbdesc']; 
            $blogdesc->twdesc = $value['twdesc']; 
            $blogdesc->insdesc = $value['insdesc']; 


            $blogdesc->save();                   
        }
    }

    public static function updatebogDesc($data_arr, $blog_id) {      
        
        self::where(['blog_id'=>$blog_id])->delete();   
        self::insertBlogDesc($data_arr,$blog_id);
    }                    
}
