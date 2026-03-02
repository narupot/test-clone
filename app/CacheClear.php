<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Auth;

class CacheClear extends Model
{
    protected $table = 'cache_clear';
    
    public $timestamps = false;
    
    protected $fillable = ['module','updated_by','updated_at','updated_by_id'];

    public static function updateCache($module){

    	Self::updateOrCreate(['module'=>$module], ['module' => $module, 'updated_by' => Auth::guard('admin_user')->user()->first_name.' '.Auth::guard('admin_user')->user()->last_name, 'updated_by_id' => Auth::guard('admin_user')->user()->id,'updated_at'=>date('Y-m-d H:i:s')]);
    }

    public static function cacheClearTime($module){
    	$cache_key = 'cache_clear_time';
    	if (cache_hasKey($cache_key) && \Config::get('constants.enable_cache')) {
    		$cache_arr = cache_getDate($cache_key);

		}else{
			$cache_arr = Self::pluck('clear_time','module')->toArray();
			cache_putData($cache_key,$cache_arr,600);
		}

		if(isset($cache_arr[$module])){
			return $cache_arr[$module];
		}else{
			60;
		}
    }

    public static function deleteCmsSliderCache(){
        $ids = \App\CmsSlider::where('status','1')->pluck('id');
        $language_ids = \App\Language::getLangList();
        foreach ($ids as $key => $id) {
            foreach ($language_ids as $key => $value) {
                $cache_key = 'cms_slider_'.$id.'_'.$value->id;
                $cache_key_data = 'cms_slider_data_'.$id.'_'.$value->id;
                cache_deleteKey($cache_key);
                cache_deleteKey($cache_key_data);
            }
        }
    }
}
