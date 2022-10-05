<?php

namespace App\Http\Controllers\Admin\Config;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use App\Language;
use App\CacheClear;
use Auth;
use Lang;
use Config;

class CacheController extends MarketPlace
{
    public function __construct()
    {   
        $this->middleware('admin.user');       
    }     
    
    public function clearWebsiteCache(){
		$permission = $this->checkUrlPermission('all_clear_cache');
        if($permission === true) {
			\Cache::flush();
			CacheClear::where('id','>',0)->update(['updated_by' => Auth::guard('admin_user')->user()->first_name.' '.Auth::guard('admin_user')->user()->last_name, 'updated_by_id' => Auth::guard('admin_user')->user()->id,'updated_at'=>date('Y-m-d H:i:s')]);

			return redirect()->action('Admin\Config\CacheController@index')->with('succMsg', Lang::get('common.cache_clear_successfully'));
		}
	}

    public function index()
    {
        $permission = $this->checkUrlPermission('cache_management');
        if($permission === true) {
			$permission_arr['view'] = $this->checkMenuPermission('view_clear_cache');
            $permission_arr['config'] = $this->checkMenuPermission('config_clear_cache');
            $permission_arr['route'] = $this->checkMenuPermission('route_clear_cache');
            $permission_arr['all'] = $this->checkMenuPermission('all_clear_cache');
            $permission_arr['clear'] = $this->checkMenuPermission('cache_clear_cache');
            $permission_arr['update'] = $this->checkMenuPermission('update_clear_cache');
			
            $cache_data = CacheClear::all();
            return view('admin.config.cacheManagement',['cache_data'=>$cache_data, 'permission_arr'=>$permission_arr]);
        }
    }

    public function cacheTimeUpdate(Request $request){
		$permission = $this->checkUrlPermission('cache_clear_cache');
        if($permission === true) {
			$id = $request->id;
			$time = $request->time;
			if(is_numeric($time)){
				CacheClear::where('id',$id)->update(['clear_time'=>$time]);
				return ['status'=>'success','msg'=>\Lang::get('admin_setting.cache_clear_time_updated_successfully')];
			}else{
				return ['status'=>'fail','msg'=>\Lang::get('admin_common.something_went_wrong')];
			}
		}
    }

    public function clearCache(Request $request){
		$permission = $this->checkUrlPermission('update_clear_cache');
        if($permission === true) {
			$type = $request->type;
			switch ($type) {
				case 'banner':
					$this->deleteBannerCache();
					break;
				case 'cms_slider':
					\App\CacheClear::deleteCmsSliderCache();
					break;
				case 'cms_tab':
					$this->deleteCmsTabCache();
					break;
				case 'layout_mgt':
					$this->deleteLayoutMgtCache();
					break;
				case 'flashsale':
					$this->deleteFlashsaleCache();
					break;
				case 'consent_checkbox':
					$this->deleteConsentCheckboxCache();
					break;
				case 'newsletter':
					$this->deleteNewsletterCache();
					break;
				case 'blog':
					$this->deleteBlogCache();
					break;		
				case 'badge':
					$this->deleteBadgeCache();
					break;
				case 'mega_menu':
					$this->deleteMegaMenuCache();
					break;
				case 'system_config':
					$this->deleteSystemConfigCache();
					break;
				case 'combine_css_js':
					$this->deleteCombineCssJsCache();
					break;
				case 'custom_block':
					$this->deleteCustomBlockCache();
					break;
				case 'page':
					$this->deleteStaticPageCache();
					break;
				case 'media_image':
					$this->deleteMediaImageCache();
					break;
				default:
					# code...
					break;
			}

			CacheClear::updateCache($type);

			return['status'=>'success','msg'=>\Lang::get('admin_setting.cache_clear_successfully')];
		}
    }

    function deleteBannerCache(){
        $ids = \App\BannerGroup::where('status','1')->pluck('id');
        foreach ($ids as $key => $id) {
            $cache_key = 'banner_slider_'.$id;
            cache_deleteKey($cache_key);
        }
    }

    function deleteCmsSliderCache(){
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

    function deleteCmsTabCache(){
        $ids = \App\CmsTab::where('status','1')->pluck('id');
        $language_ids = \App\Language::getLangList();
        foreach ($ids as $key => $id) {
            foreach ($language_ids as $key => $value) {
                $cache_key = 'cms_tab_'.$id.'_'.$value->id;
                cache_deleteKey($cache_key);
            }
        }
    }

    function deleteLayoutMgtCache(){
        $cache_key = 'layout_block';
        cache_deleteKey($cache_key);
    }

    function deleteFlashsaleCache(){
        $cache_key = 'flashsale_data_'.getDateFormat(date('Y-m-d'),'9');
        cache_deleteKey($cache_key);
    }
	
	function deleteConsentCheckboxCache(){
		//$date = date('Y-m-d');
        //$cache_key = 'consent_checkbox_'.$date;
		$checkbox_location = \App\PrivacyPolicyConsentCheckbox::select('checkbox_location')->where('status','1')->get();
		$consentbox = array();
        if(!empty($checkbox_location)){
			foreach ($checkbox_location as $key => $location) {
				$checkbox = explode(",",$location->checkbox_location);
				$consentbox = $checkbox;
			}
			$consentboxs = array_unique($consentbox);
			if(!empty($consentboxs)){
				foreach($consentboxs as $k=>$pagename){
					$cache_key = 'consent_checkbox_'.$pagename;
					cache_deleteKey($cache_key);
				}
			}
		}
    }
	
	function deleteNewsletterCache(){
		$date = date('Y-m-d');
        $cache_key = 'newsletter_'.$date;
        cache_deleteKey($cache_key);
    }
	
	function deleteBlogCache(){
		$date = date('Y-m-d');
		$cache_archive_key = 'blog_archive_'.$date;
		$cache_allcat_key = 'blog_allcat_'.$date;
		$cache_feature_conf_key = 'blog_feature_conf_'.$date;
		$cache_feature_blog_key = 'blog_feature_blog_'.$date;
		$cache_recent_conf_key = 'blog_recent_conf_'.$date;
		$cache_recent_key = 'blog_recent_'.$date;
		$cache_alltag_key = 'blog_alltag_'.$date;
		$cache_config_key = 'blog_config_'.$date;
		$cache_staticblock_key = 'blog_staticblock_'.$date;
		$cache_date_key = 'blog_date_'.$date;
		$cache_time_key = 'blog_time_'.$date;
		
		$cache_related_conf_key = 'blog_related_conf_'.$date;
		$cache_related_blog_key = 'blog_related_blog_'.$date;
		$cache_related_blog_tag_key = 'blog_related_blog_tag_conf_'.$date;
		$cache_recent_blog_key = 'blog_recent_blog_'.$date;
		$cache_recent_blog_tag_key = 'blog_recent_blog_tag_conf_'.$date;
		$cache_slider_key = 'blog_slider_'.$date;
		$cache_comment_enable_key = 'blog_comment_enable_'.$date;
		$cache_facebook_appid_key = 'blog_facebook_appid_'.$date;
		$cache_blog_product_key = 'blog_product_'.$date;
		$cache_blog_product_show_key = 'blog_product_show_'.$date;
				
        cache_deleteKey($cache_archive_key);
        cache_deleteKey($cache_allcat_key);
        cache_deleteKey($cache_feature_conf_key);
        cache_deleteKey($cache_feature_blog_key);
        cache_deleteKey($cache_recent_conf_key);
        cache_deleteKey($cache_recent_key);
        cache_deleteKey($cache_alltag_key);
        cache_deleteKey($cache_config_key);
        cache_deleteKey($cache_staticblock_key);
        cache_deleteKey($cache_date_key);
        cache_deleteKey($cache_time_key);
		
		cache_deleteKey($cache_related_conf_key);
		cache_deleteKey($cache_related_blog_key);
		cache_deleteKey($cache_related_blog_tag_key);
		cache_deleteKey($cache_recent_blog_key);
		cache_deleteKey($cache_recent_blog_tag_key);
		cache_deleteKey($cache_slider_key);
		cache_deleteKey($cache_comment_enable_key);
		cache_deleteKey($cache_facebook_appid_key);
		cache_deleteKey($cache_blog_product_key);
		cache_deleteKey($cache_blog_product_show_key);
    }

    function deleteBadgeCache(){
        $language_ids = \App\Language::getLangList();
        foreach ($language_ids as $key => $value) {
            $cache_key = 'badge_data_'.$value->id;
            cache_deleteKey($cache_key);
        }
    }

    function deleteMegaMenuCache(){
        $ids = \App\MegaMenu::where('status','1')->pluck('id');
        $language_ids = \App\Language::getLangList();
        foreach ($ids as $key => $id) {
            foreach ($language_ids as $key => $value) {
                $cache_key = 'mobilemega_menu_'.$id.'_'.$value->id;
                cache_deleteKey($cache_key);

                $cache_key = 'desktopmega_menu_'.$id.'_'.$value->id;
                cache_deleteKey($cache_key);
            }
        }
    }

    function deleteSystemConfigCache(){
        $cache_key = 'system_config_cache';
        cache_deleteKey($cache_key);
    }

    function deleteMediaImageCache(){
    	\App\MediaImage::deleteMediaImgCache();
    }

    function deleteCombineCssJsCache(){
        $combine_path = \Config::get('constants.public_path').'/combine/';
        $css_path = $combine_path.'css/*';
        $js_path = $combine_path.'js/*';
        $files = glob($css_path); //get all file names
        foreach($files as $file){
            if(is_file($file))
                unlink($file); //delete file
        }

        $files = glob($js_path); //get all file names
        foreach($files as $file){
            if(is_file($file))
                unlink($file); //delete file
        }
    }

    function deleteCustomBlockCache(){
        $ids = \App\CustomBlock::where('status','1')->pluck('id');
        $language_ids = \App\Language::getLangList();
        foreach ($ids as $key => $id) {
            foreach ($language_ids as $key => $value) {
                $cache_key = 'custom_block_'.$id.'_'.$value->id;
                cache_deleteKey($cache_key);
            }
        }
    }

    function deleteStaticPageCache(){
        $ids = \App\StaticPage::where('status','1')->pluck('url');
        foreach ($ids as $key => $id) {
            $cache_key = 'page_"'.$id.'"';
            cache_deleteKey($cache_key);
        }
    }

    public function clearWebsiteView(){
		$permission = $this->checkUrlPermission('view_clear_cache');
        if($permission === true) {
			\Artisan::call('view:clear');
			return redirect()->action('Admin\Config\CacheController@index')->with('succMsg', Lang::get('common.view_clear_successfully'));
		}
    }
    public function clearWebsiteConfig(){
		$permission = $this->checkUrlPermission('config_clear_cache');
        if($permission === true) {
			\Artisan::call('config:clear');
			\Artisan::call('config:cache');
			return redirect()->action('Admin\Config\CacheController@index')->with('succMsg', Lang::get('common.config_clear_successfully'));
		}
	}
    public function clearWebsiteRoute(){
		$permission = $this->checkUrlPermission('route_clear_cache');
        if($permission === true) {
			\Artisan::call('route:clear');
			return redirect()->action('Admin\Config\CacheController@index')->with('succMsg', Lang::get('common.route_clear_successfully'));
		}
    }
    public function updateVersion($value=''){
    	$time_stamp = now()->timestamp;
    	\App\SystemConfig::where('system_name','CSS_JS_VERSION')->update(['system_val'=>$time_stamp]);
    	return redirect()->action('Admin\Config\CacheController@index')->with('succMsg', Lang::get('common.record_updated_successfully'));
    }

    public function clearCloudeCache($value=''){
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://api.cloudflare.com/client/v4/zones/".getConfigValue('CLOUDEFLARE_ZONEID')."/purge_cache",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "{\"purge_everything\":true}",
		  CURLOPT_HTTPHEADER => array(
		    "Content-Type: application/json",
		    "X-Auth-Email: ".getConfigValue('CLOUDEFLARE_EMAIL')."",
		    "X-Auth-Key: ".getConfigValue('CLOUDEFLARE_API_KEY')."",
		    "cache-control: no-cache"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  	return redirect()->action('Admin\Config\CacheController@index')->with('errorMsg', $err);
		} else {
			return redirect()->action('Admin\Config\CacheController@index')->with('succMsg',Lang::get('common.cloude_cache_clear_successfully'));
		  	//echo $response;
		}
	}
}
