<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class StaticPageDescRevision extends Model {

    protected $table = 'static_page_desc_revision';

    public $timestamps = false; 

    public static function insertStaticPageDescRevision($data_arr, $page_id) {
        $def_lang_id = session('admin_default_lang');
        $revisiondata = StaticPageDescRevision::where(['static_page_id'=>$page_id,'lang_id'=>$def_lang_id])->get()->last();
        if(!empty($revisiondata->revision)){
            $rev_id = $revisiondata->revision;
            $rev_ids = $rev_id+1;                
        }else{
            $rev_ids = 1;
        }      
        //dd($data_arr);
        foreach ($data_arr as $key=>$value){
            
            $staticpagedescrevision = new StaticPageDescRevision;
            $staticpagedescrevision->revision = $rev_ids;
            $staticpagedescrevision->static_page_id = $page_id;
            $staticpagedescrevision->lang_id = $key;
            $staticpagedescrevision->static_page_title = $value['page_title']; 
            $staticpagedescrevision->static_page_desc = $value['page_desc'];
            //dd($value['lang_id']);
            $staticpagedescrevision->save();                   
        }
    }

    public static function updatestatickpageDescRevision($data_arr, $page_id) {      
        
        self::insertStaticPageDescRevision($data_arr,$page_id);
    }                    
}
