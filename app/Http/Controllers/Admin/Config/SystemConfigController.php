<?php

namespace App\Http\Controllers\Admin\Config;

use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;

use App\SystemConfig;
use Config;
//use Auth;
use App\Timezones;
use File;
//use Illuminate\Support\Facades\Input;
use Lang;
use Validator;
use Redirect;

class SystemConfigController extends MarketPlace
{
    
    public function __construct()
    {   
        $this->middleware('admin.user');       
    }     
    
    public function index()
    {  
        $permission = $this->checkUrlPermission('general_setting');
        if($permission === true) {   

            $config_arr = $this->getConfiguration('general_setting');
            return view('admin.config.systemConfig', ['config_arr'=>$config_arr]);
        }
    }

    public function store(Request $request)
    {        
        //echo '<pre>';print_r($request->all());die;
        $postArr = $request->all();
        $configAllData = SystemConfig::all();
        foreach ($configAllData as $configVal) {
            if(isset($postArr[$configVal->system_name])) {
                $config = SystemConfig::find($configVal->id); 
                $config->system_name = $configVal->system_name;
                $config->system_val = $postArr[$configVal->system_name];
                $config->save();

                $this->updateSession($configVal, $postArr);                
            }  
        }

        $this->updateConfigFile($configAllData);
        /*update activity log start*/
        $action_type = "updated"; 
        $module_name = "system config";            
        $logdetails = "Admin has updated ".$module_name;
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/
        if(!empty($request->redirect_path)) {
            $action = $request->redirect_path;
        }
        else {
            $action = 'Admin\Config\SystemConfigController@index';
        }

        return redirect()->action($action)->with('succMsg', Lang::get('common.records_updated_successfully'));
    }

    public function updateConfigFile($config_data=null){

        $config_arr = [];
        $system_config = SystemConfig::all();
        if(!empty($system_config)){
            foreach ($system_config as $key => $value) {
                $config_arr[$value->system_name] = $value->system_val;
            }

            $file_complete_path = Config('constants.data_cache_path').'/system_config.dict';
            $json_data = json_encode($config_arr,JSON_UNESCAPED_UNICODE);
            File::put($file_complete_path, $json_data);
            \Cache::flush();
        }
    }

    public function updateSession($configVal, $postArr) {
        switch ($configVal->system_name) {
            case 'SITE_FULL_NAME':
                session(['site_name' => $postArr['SITE_FULL_NAME']]); 
                break;
            case 'TIMEZONE':
                $time_zone = \App\Timezones::getDefaultTimezoneDetail($postArr['TIMEZONE']);
                session(['default_time_zone' => $postArr['TIMEZONE']]);
                session(['default_time_zone_label' => $time_zone->gmt_offset.' '.$time_zone->timezone]);
                break;            
            default:
                # code...
                break;
        }
    }    

    public function show($id) {
        
        $permission = $this->checkUrlPermission('configuration');
        //dd($permission);
        if($permission === true) {

            $permission_arr['payment_method'] = $this->checkMenuPermission('payment_setting');
            $permission_arr['payment_option'] = $this->checkMenuPermission('manage_payment_option');
            $permission_arr['payment_bank'] = $this->checkMenuPermission('manage_payment_bank');
            $permission_arr['shipping_profile'] = $this->checkMenuPermission('shipping_profile');
            $permission_arr['shipping_profile_list'] = $this->checkMenuPermission('shipping_profile_list');
            $permission_arr['add_shipping_profile'] = $this->checkMenuPermission('add_shipping_profile');
            $permission_arr['warehouse'] = $this->checkMenuPermission('warehouse_listing');
            $permission_arr['team_member'] = $this->checkMenuPermission('list_users');
            $permission_arr['role_setting'] = $this->checkMenuPermission('list_roles');
            $permission_arr['site_setting'] = $this->checkMenuPermission('general_setting');
            $permission_arr['placeholder'] = $this->checkMenuPermission('manage_image_placeholder');
            $permission_arr['gender_list'] = $this->checkMenuPermission('gender_list');
            $permission_arr['avtar_images'] = $this->checkMenuPermission('manage_avtar_images');
            $permission_arr['image_placeholder'] = $this->checkMenuPermission('image_placeholder');
            $permission_arr['site_logo'] = $this->checkMenuPermission('site_logo');
            $permission_arr['seo'] = $this->checkMenuPermission('seo');
            $permission_arr['templete_seo'] = $this->checkMenuPermission('manage_global_seo');
            $permission_arr['product_seo'] = $this->checkMenuPermission('product_seo_management');
            $permission_arr['global_seo'] = $this->checkMenuPermission('pages_seo_management');
            $permission_arr['default_seo'] = $this->checkMenuPermission('default_seo_setting');
            $permission_arr['base_currency'] = $this->checkMenuPermission('currency_management');
            $permission_arr['manage_currency'] = $this->checkMenuPermission('currency_listing');
            $permission_arr['currency_detail'] = $this->checkMenuPermission('currency_detail');
            $permission_arr['manage_language'] = $this->checkMenuPermission('list_language');
            $permission_arr['mail_template'] = $this->checkMenuPermission('mail_template');
            $permission_arr['manage_mail_template'] = $this->checkMenuPermission('manage_mail_template');
            $permission_arr['manage_master_template'] = $this->checkMenuPermission('master_mail_template');
            $permission_arr['menu_management'] = $this->checkMenuPermission('list_menu');
            $permission_arr['table_management'] = $this->checkMenuPermission('table_management');
            $permission_arr['table_config'] = $this->checkMenuPermission('table_config');
            $permission_arr['column_config'] = $this->checkMenuPermission('column_config');
            $permission_arr['translation'] = $this->checkMenuPermission('translation_management');
            $permission_arr['translation_module'] = $this->checkMenuPermission('manage_translation_module');
            $permission_arr['translate_module_key'] = $this->checkMenuPermission('manage_translation');
            $permission_arr['translate_by_search'] = $this->checkMenuPermission('manage_translation_search');
            $permission_arr['translate_menu'] = $this->checkMenuPermission('manage_translation_menu');
            $permission_arr['world_wide_management'] = $this->checkMenuPermission('world_wide_management');
            $permission_arr['manage_country'] = $this->checkMenuPermission('manage_country');
            $permission_arr['manage_province_state'] = $this->checkMenuPermission('manage_province_state');
            $permission_arr['manage_city_district'] = $this->checkMenuPermission('manage_city_district');
            $permission_arr['store_location_seo'] = $this->checkMenuPermission('add_store_location');
            $permission_arr['logactivity'] = $this->checkUrlPermission('logactivity');

            $permission_arr['email_transmission_setting'] = $this->checkMenuPermission('email_transmission_setting');

            $permission_arr['pickup-at-center'] = $this->checkMenuPermission('pickup-at-center');
            $permission_arr['pickup-at-store'] = $this->checkMenuPermission('pickup-at-store');
            $permission_arr['delivery-at-address'] = $this->checkMenuPermission('delivery-at-address');
            $permission_arr['shipping'] = $this->checkMenuPermission('shipping');

            $warehouse_enabled = $this->systemConfig('WAREHOUSE_ENABLED');// to show warehouse if enabled otherwise will be disabled

            //dd($permission_arr);

            return view('admin.config.setting', ['permission_arr'=>$permission_arr, 'warehouse_enabled'=>$warehouse_enabled]);
        }        
    }    

    public function placeholderImageUpload(Request $request)
    {       
        $success = 'N';
        if(!empty($request->images)) {
            foreach ($request->images as $key=>$value) {

                $uploadDetails['path'] = Config::get('constants.placeholder_path');
                $uploadDetails['file'] =  $request->images[$key];
                $ext = pathinfo($uploadDetails['file']->getClientOriginalName(), PATHINFO_EXTENSION); 
                $uploadDetails['file_name'] = strtolower($key).'.'.$ext;
                $file_path = $uploadDetails['path'].'/'.$uploadDetails['file_name'];
                //dd($file_path, $this->fileDelete($file_path));
                $this->fileDelete($file_path);

                $file_name = $this->uploadFileCustom($uploadDetails);

                SystemConfig::where(['system_name'=>$key])->update(['system_val' => $file_name]);
            }
            /*update activity log start*/
            $action_type = "updated"; 
            $module_name = "placeholder image";            
            $logdetails = "Admin has updated ".$module_name;
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/
            $success = 'Y';
        }

        if(!empty($request->user_male)){
            SystemConfig::where(['system_name'=>'USER_IMAGE'])->update(['system_val' => $request->user_male]);
            $success = 'Y';
        }

        if(!empty($request->user_female)){
            SystemConfig::where(['system_name'=>'USER_IMAGE_FEMALE'])->update(['system_val' => $request->user_female]);
            $success = 'Y';
        }
        /****updating config file*******/
        $this->updateConfigFile();
        if($success == 'Y'){
            return redirect()->action('Admin\Config\SystemConfigController@placeholderImage')->with('succMsg', Lang::get('admin.images_updated_successfully'));
        }
        else {
            return redirect()->action('Admin\Config\SystemConfigController@placeholderImage')->with('errorMsg', Lang::get('admin.please_select_image'));
        }      
    }    

    public function placeholderImage()
    {  
        $permission = $this->checkUrlPermission('image_placeholder');
        if($permission === true) {   

            $user_images = \App\AdminAvatar::getAvatarImages();
            $user_images_arr = [];
            if(count($user_images) > 0) {
                foreach ($user_images as $value) {
                    if($value->gender == 'M') {
                        $user_images_arr['M'][] =  ['id'=>$value->id, 'name'=>$value->name];
                    }
                    elseif($value->gender == 'F') {
                       $user_images_arr['F'][] =  ['id'=>$value->id, 'name'=>$value->name];
                    }
                }
            }
            $config_arr = $this->getConfiguration('placeholder_image');
            //dd($config_arr, $user_images_arr);

            return view('admin.config.systemConfigPlaceholder', ['config_arr'=>$config_arr, 'user_images_arr'=>$user_images_arr]);
        }
    }

    public function siteLogoUpdate(Request $request)
    {       
        if(!empty($request->images)) {
            foreach ($request->images as $key=>$value) {
                //dd($request->images[$key]);
                if(($key=='SITE_LOGO_HEADER') && (!$request->images[$key]=="")){
                    $site_path = Config::get('constants.site_logo_path').'/';
                    $site_image_name = 'site_logo'.md5(microtime()).'.png';
                    $site_image_data = $this->base64UploadImage($request->images[$key],$site_path,$site_image_name);

                        if($site_image_data){
                            $uploadDetail = $site_image_name;
                            SystemConfig::where(['system_name'=>$key])->update(['system_val' => $uploadDetail]);
                        }
                }

                if($key=='SITE_FEVICON_ICON'){
                    $data = getimagesize($request->images['SITE_FEVICON_ICON']);
                    $width = $data[0];
                    $height = $data[1];
                    $uploadDetails['path'] = Config::get('constants.site_logo_path');
                    $uploadDetails['file'] =  $request->images[$key];
                    $ext = pathinfo($uploadDetails['file']->getClientOriginalName(), PATHINFO_EXTENSION); 
                    $uploadDetails['file_name'] = strtolower($key).'.'.$ext;
                    $file_path = $uploadDetails['path'].'/'.$uploadDetails['file_name'];
                    
                    if($width==$height)
                    {
                        $uploadDetail = $this->uploadFileCustom($uploadDetails);
                        SystemConfig::where(['system_name'=>$key])->update(['system_val' => $uploadDetail]);   
                    }
                    else{
                        return redirect()->action('Admin\Config\SystemConfigController@siteLogoEdit')->with('errorMsg', Lang::get('admin.select_square_favicon_images'));
                    }
                }
                //$this->fileDelete($file_path);
                //$file_name = $this->uploadFileCustom($uploadDetails);
                //dd($site_image_name);   
            }
            
            /****updating config file*******/
            $this->updateConfigFile();

            /*update activity log start*/
            $action_type = "updated"; 
            $module_name = "site logo";            
            $logdetails = "Admin has updated ".$module_name;
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/
        }
        return redirect()->action('Admin\Config\SystemConfigController@siteLogoEdit')->with('succMsg', Lang::get('admin.images_updated_successfully'));
    }    

    public function siteLogoEdit()
    {  
        $permission = $this->checkUrlPermission('site_logo');
        if($permission === true) {   

            $config_arr = $this->getConfiguration('site_logo');
            return view('admin.config.systemConfigSiteLogo', ['config_arr'=>$config_arr]);
        }
    }  

   /*** this is for the seo config ***/
    public function SEOConfig()
    {  
        $permission = $this->checkUrlPermission('default_seo_setting');
        if($permission === true) {
             $config_arr = $this->getConfiguration('seo_config');
             return view('admin.config.systemSEOConfig', ['config_arr'=>$config_arr]);
        }
    }                        

    public function storeSeoConfig(Request $request)
    {        
        
       // dd($request);

        $this->validate($request, [
             'OG_IMAGE' => 'image|mimes:jpeg,png,jpg|max:1024',
         ]);


        $postArr = $request->all();
        $postArr['GOOGLE_ANALYTICS_SETTING'] = isset($postArr['GOOGLE_ANALYTICS_SETTING'])?$postArr['GOOGLE_ANALYTICS_SETTING']:'';
        $postArr['FB_PIXEL_SETTING'] =  isset($postArr['FB_PIXEL_SETTING'])?$postArr['FB_PIXEL_SETTING']:'';

        $configAllData = SystemConfig::where('system_type', 'seo_config')->get();

        foreach ($configAllData as $configVal) {
            if(isset($postArr[$configVal->system_name])) {
                $config = SystemConfig::find($configVal->id); 
                $config->system_name = $configVal->system_name;
                $config->system_val = $postArr[$configVal->system_name];
                $config->save();                
            }   
        }
        
        $file_complete_path = Config::get('constants.public_path').'/'.'robots.txt';
        $robots_txt = isset($postArr['ROBOTS_TXT'])?$postArr['ROBOTS_TXT']:'';
        File::put($file_complete_path, $robots_txt);

       // dd($request->files);

         if(!empty($request->OG_IMAGE)) {
               $uploadDetails['path'] = Config::get('constants.social_share_path');
                $uploadDetails['file'] =  $request->OG_IMAGE;
                $ext = pathinfo($uploadDetails['file']->getClientOriginalName(), PATHINFO_EXTENSION); 
                $file_name = $this->uploadFileCustom($uploadDetails);
                SystemConfig::where(['system_name'=>'OG_IMAGE'])->update(['system_val' => $file_name]);
           
        }

        SystemConfig::where(['system_name'=>'FACEBOOK_URL'])->update(['system_val' => $request->FACEBOOK_URL]);
        SystemConfig::where(['system_name'=>'TWITTER_URL'])->update(['system_val' => $request->TWITTER_URL]);
        SystemConfig::where(['system_name'=>'GOOGLE_PLUS_URL'])->update(['system_val' => $request->GOOGLE_PLUS_URL]);
        SystemConfig::where(['system_name'=>'LINKEDIN_URL'])->update(['system_val' => $request->LINKEDIN_URL]);
        SystemConfig::where(['system_name'=>'PINTEREST_URL'])->update(['system_val' => $request->PINTEREST_URL]);
        SystemConfig::where(['system_name'=>'INSTAGRAM_URL'])->update(['system_val' => $request->INSTAGRAM_URL]);
        SystemConfig::where(['system_name'=>'YOUTUBE_URL'])->update(['system_val' => $request->YOUTUBE_URL]);

        if(!empty($request->redirect_path)) {
            $action = $request->redirect_path;
        }
        else {
            $action = 'Admin\Config\SystemConfigController@index';
        }
        /****updating config file*******/
        $this->updateConfigFile();

        /*update activity log start*/
        $action_type = "updated"; 
        $module_name = "seo config";            
        $logdetails = "Admin has updated seo config ";
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/

        return redirect()->action($action)->with('succMsg', 'Records Updated Successfully!');
    }


    public function siteLoaderEdit(){
        //$permission = $this->checkUrlPermission('site_logo');
        //if($permission === true) {   
        $permission = $this->checkUrlPermission('configuration');
        if($permission === true) {
            $config_arr = $this->getConfiguration('site_loader_image');            
            return view('admin.config.systemConfigSiteLoader', ['config_arr'=>$config_arr]);
        }
    }

    public function siteLoaderUpdate(Request $request){

        $messages = [
            'SITE_LOADER_IMAGE.required' => Lang::get('common.site_loader_required'),//'The :attribute field is required.',
            'SITE_LOADER_IMAGE.image' => Lang::get('common.site_loader_required'),
            //'The :attribute must be an image.',
            'SITE_LOADER_IMAGE.mimes'    => Lang::get('common.site_loader_mime_type_required')
            //'The :attribute must be a file of type: :values.'

        ];
        $rules = [
             'SITE_LOADER_IMAGE' => 'required|image|mimes:gif,svg|max:1024',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return Redirect::back()
                        ->withErrors($validator)
                        ->withInput();
        }

        if(!empty($request->SITE_LOADER_IMAGE)) {
            //foreach ($request->images as $key=>$value) {

                $uploadDetails['path'] = Config::get('constants.site_loader_path');

                $uploadDetails['file'] =  $request->SITE_LOADER_IMAGE;
                $ext = pathinfo($uploadDetails['file']->getClientOriginalName(), PATHINFO_EXTENSION); 
                $uploadDetails['file_name'] = strtolower('SITE_LOADER_IMAGE').'.'.$ext;
                $file_path = $uploadDetails['path'].'/'.$uploadDetails['file_name'];
                //dd($file_path);
                $this->fileDelete($file_path);
                $file_name = $this->uploadFileCustom($uploadDetails);

                SystemConfig::where(['system_name'=>'SITE_LOADER_IMAGE'])->update(['system_val' => $file_name]);

                $this->updateConfigFile();
                
                /*update activity log start*/
                $action_type = "updated"; 
                $module_name = "site loader";            
                $logdetails = "Admin has updated ".$module_name;
                $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

                $this->updateLogActivity($logdata);
                /*update activity log End*/
            //}
        }
        return redirect()->action('Admin\Config\SystemConfigController@siteLoaderEdit')->with('succMsg', Lang::get('admin.images_updated_successfully'));
    }

    public function pickupCenter(Request $request){
        
        $data = SystemConfig::where('system_name','PICKUP_CENTER')->first();
        $res = json_decode($data->system_val,true);
        $delivery_time = \App\DeliveryTime::getDeliveryTime('pickup_center');
        if($delivery_time){
            $delivery_time->time_slot = explode(',', $delivery_time->time_slot);
        }
        
        return view('admin.config.pickupCenter',['res'=>$res,'delivery_time'=>$delivery_time]);
    }

    public function pickupAtStore(Request $request){
        abort(404);
    }

    public function updatePickupCenter(Request $request){
        $name = cleanValue($request->name);
        $location = cleanValue($request->location);
        $contact = cleanValue($request->contact);
        //$estimate = cleanValue($request->estimate);

        $arr = ['name'=>$name,'location'=>$location,'contact'=>$contact];

        $json = json_encode($arr);

        $update = SystemConfig::where('system_name','PICKUP_CENTER')->update(['system_val'=>$json]);
        $request->delivery_type = 'pickup_center';
        $update_delivery_time = \App\DeliveryTime::updateDeliveryTime($request);
        return redirect()->action('Admin\Config\SystemConfigController@pickupCenter')->with('succMsg', Lang::get('common.records_updated_successfully'));
    }
    

}
