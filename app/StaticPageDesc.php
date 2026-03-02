<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class StaticPageDesc extends Model {

    protected $table = 'static_page_desc';

    public $timestamps = false; 

    public static function insertPageDesc($data_arr, $page_id) {      

        foreach ($data_arr as $key=>$value){

            $Page_desc = new StaticPageDesc;
            $Page_desc->static_page_id = $page_id;
            $Page_desc->lang_id = $key;
            $Page_desc->page_title = $value['page_title'];
            $Page_desc->page_desc = $value['page_desc'];    
            $Page_desc->meta_title = $value['meta_title'];  
            $Page_desc->meta_keyword = $value['meta_keyword'];
            $Page_desc->meta_desc = $value['meta_desc'];
            $Page_desc->fbtitle = $value['fbtitle']; 
            $Page_desc->twtitle = $value['twtitle']; 
            $Page_desc->institle = $value['institle']; 
            $Page_desc->fbdesc = $value['fbdesc']; 
            $Page_desc->twdesc = $value['twdesc']; 
            $Page_desc->insdesc = $value['insdesc'];            
            $Page_desc->save();                   
        }
    }

    public static function updatepageDesc($data_arr, $page_id) {      

        foreach ($data_arr as $key=>$value){

			self::where(['static_page_id'=>$page_id, 'lang_id'=>$key])
				->update(['page_title' => $value['page_title'], 'page_desc' => $value['page_desc'], 'meta_title' => $value['meta_title'], 'meta_keyword' => $value['meta_keyword'],'meta_desc' => $value['meta_desc'],'fbtitle' => $value['fbtitle'],'twtitle' => $value['twtitle'],'institle' => $value['institle'],'fbdesc' => $value['fbdesc'],'twdesc' => $value['twdesc'],'insdesc' => $value['insdesc']]);                  
        }
    }                    
}
