<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class BlogDescRevision extends Model {

    protected $table = 'blog_desc_revision';

    public $timestamps = false; 

    public static function insertBlogDescRevision($data_arr, $blog_id) {

        $def_lang_id = session('admin_default_lang');            
        $revisiondata = BlogDescRevision::where(['blog_id'=>$blog_id,'lang_id'=>$def_lang_id])->get()->last();
        if(!empty($revisiondata->revision)){
            $rev_id = $revisiondata->revision;
            $rev_ids = $rev_id+1;                
        }else{
            $rev_ids = 1;
        }      

        foreach ($data_arr as $key=>$value){
            
            $blogdescrevision = new BlogDescRevision;
            $blogdescrevision->revision = $rev_ids;
            $blogdescrevision->blog_id = $blog_id;
            $blogdescrevision->lang_id = $value['lang_id'];
            $blogdescrevision->blog_title = $value['blog_title'];
            $blogdescrevision->blog_short_desc = $value['blog_short_desc']; 
            $blogdescrevision->blog_desc = $value['blog_desc'];                
            $blogdescrevision->meta_title = $value['meta_title'];  
            $blogdescrevision->meta_keyword = $value['meta_keyword'];
            $blogdescrevision->meta_desc = $value['meta_desc']; 
            $blogdescrevision->fbtitle = $value['fbtitle']; 
            $blogdescrevision->twtitle = $value['twtitle']; 
            $blogdescrevision->institle = $value['institle']; 
            $blogdescrevision->fbdesc = $value['fbdesc']; 
            $blogdescrevision->twdesc = $value['twdesc']; 
            $blogdescrevision->insdesc = $value['insdesc']; 

            $blogdescrevision->save();                   
        }
    }

    public static function updatebogDescRevision($data_arr, $blog_id) {      
        
        self::insertBlogDescRevision($data_arr,$blog_id);
    }                    
}
