<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;
class CssJsEmbeded extends Model {

    protected $table = 'css_jss_embeded';  

    public static function getCssJsEmbeded($id = null){

    	if($id){
    		return self::where('id',$id)->first();
    	}else{
    		return self::orderBy('id','desc')->get();
    	}
    }        

    public static function getEmbededData(){
        $cache_key = 'embededcssjs_data';
        if (cache_hasKey($cache_key) && \Config::get('constants.enable_cache')) {
            $embeded_data = cache_getDate($cache_key);
        }else{
            $embeded_data = self::select('page_url','embeded_css','embeded_js','custom_url')->where('status','1')->get();
            cache_putData($cache_key,$embeded_data);
        }
        return $embeded_data;
    }
}
