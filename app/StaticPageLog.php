<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class StaticPageLog extends Model {

    protected $table = 'static_page_log';  

    public static function getPageLog($page_id,$lang_id){
    	return StaticPageLog::where(['static_page_id'=>$page_id,'lang_id'=>$lang_id])->get();
    }
       
}
