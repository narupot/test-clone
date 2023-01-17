<?php

namespace App\Http\Controllers\Admin\WebsiteMaintenance;

use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;

use App\WebsiteConfiguration;
use App\Timezones;
use File;
use Lang;

class WebsiteMaintenanceController extends MarketPlace
{
    
    public function __construct()
    {   
        $this->middleware('admin.user');       
    }     
    
    public function index()
    {  
        $permission = $this->checkUrlPermission('website_configuration');
        if($permission === true) {   

            $config_arr = $this->getWebsiteConfiguration('website_configuration');
            $config_arr_search = $this->getWebsiteConfigurationSearch('website_configuration');
            return view('admin.websiteMaintenance.websiteConfiguration', ['config_arr'=>$config_arr,'config_arr_search'=>$config_arr_search]);
        }
    }

    public function store(Request $request)
    {                
        
        if($request->SITE_MAINTENANCE == '0'){
            /***checking language**/
            $langcount = \App\Language::where(['isDefault'=>'1','status'=>'1'])->count();

            /****checking currency*****/
           // $curcount = \App\Currency::where(['is_default'=>'1','status'=>'1'])->count();

            /***payment method****/
            /*$bank = true;
            $payoptcount = \App\PaymentOption::where(['status'=>'1'])->get();
            if(count($payoptcount)){
                $payment_option = true;
                foreach ($payoptcount as $key => $value) {
                    if($value->payment_type == '2'){
                        $bankcount = \App\PaymentBank::where(['status'=>'1'])->count();
                        $bank = ($bankcount > 0) ? true : false;
                    }
                }
            }else{
                $payment_option = false;
            }*/

            /****shipping profile***/
            /*$shippingcount = \App\ShippingProfile::where(['status'=>'1'])->count();

            if($langcount && $curcount && $shippingcount && $payment_option && $bank){
                
            }else{
                return redirect()->action('Admin\WebsiteMaintenance\WebsiteMaintenanceController@index')->with('errorMsg', Lang::get('admin.please_make_100_before_open_your_shop'));
            }*/
            
        }
        $postArr = $request->all();
        $configAllData = WebsiteConfiguration::all();

        foreach ($configAllData as $configVal) {            
            if(isset($postArr[$configVal->website_config_name])) {
                $config = WebsiteConfiguration::find($configVal->id); 
                $config->website_config_name = $configVal->website_config_name;                
                if($configVal->website_config_name=='WEBSITE_SEARCH_CONFIG'){                    
                    $searchValue = $postArr['WEBSITE_SEARCH_CONFIG'];  
                    $searchData = implode(',', $searchValue); 
                    $config->website_config_value = $searchData;
                }else{
                    $config->website_config_value = $postArr[$configVal->website_config_name];                
                }
                $config->save();           
            }  
        }

        /*update activity log start*/
        $action_type = "updated"; 
        $module_name = "websitemaintenance";            
        $logdetails = "Admin has updated website maintenance configuration";
        $old_data = "";
        $new_data = "";
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );
        $this->updateLogActivity($logdata);
        /*update activity log End*/ 

        if(!empty($request->redirect_path)) {
            $action = $request->redirect_path;
        }
        else {
            $action = 'Admin\WebsiteMaintenance\WebsiteMaintenanceController@index';
        }

        return redirect()->action($action)->with('succMsg', Lang::get('common.records_updated_successfully'));
    }                   

    public function getWebsiteConfiguration($type) {

        $conf_lists = \App\WebsiteConfiguration::getWebsiteConfiguration($type); 
        $conf_lists = $conf_lists->toArray();
        foreach($conf_lists as $val) {            
            $config_arr[$val['website_config_name']] = $val['website_config_value'];
        }                
        return $config_arr;
    }

    public function getWebsiteConfigurationSearch($type) {
   
        $conf_lists = \App\WebsiteConfiguration::getWebsiteConfiguration($type); 
        $conf_lists = $conf_lists->toArray();        
        foreach($conf_lists as $val) {
            if(!empty($val['website_config_value'])){  
                $data = explode(',', $val['website_config_value']);                
                $config_arr_search[$val['website_config_name']] = $data;
            }
        }        
        return $config_arr_search;
    }
    
    public function updateMaintenance(Request $request){

        if($request->SITE_MAINTENANCE == '0'){
            /***checking language**/
            $langcount = \App\Language::where(['isDefault'=>'1','status'=>'1'])->count();

            /****checking currency*****/
            $curcount = \App\Currency::where(['is_default'=>'1','status'=>'1'])->count();

            /***payment method****/
            $bank = true;
            $payoptcount = \App\PaymentOption::where(['status'=>'1'])->get();
            if(count($payoptcount)){
                $payment_option = true;
                foreach ($payoptcount as $key => $value) {
                    if($value->payment_type == '2'){
                        $bankcount = \App\PaymentBank::where(['status'=>'1'])->count();
                        $bank = ($bankcount > 0) ? true : false;
                    }
                }
            }else{
                $payment_option = false;
            }

            /****shipping profile***/
            $shippingcount = \App\ShippingProfile::where(['status'=>'1'])->count();

            if($langcount && $curcount && $shippingcount && $payment_option && $bank){
                
            }else{
                return ['status'=>'fail','msg'=>Lang::get('admin.please_make_100_before_open_your_shop')];
            }
            
        }
        $value = $request->SITE_MAINTENANCE;

        $update = WebsiteConfiguration::where('website_config_name','SITE_MAINTENANCE')->update(['website_config_value'=>$value]);

        /*update activity log start*/
        $action_type = "updated"; 
        $module_name = "websitemaintenance";            
        $logdetails = "Admin has updated website maintenance configuration";
        $old_data = "";
        $new_data = "";
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails );
        $this->updateLogActivity($logdata);
        /*update activity log End*/

        return ['status'=>'success','msg'=> Lang::get('common.records_updated_successfully')];
    }

    public function updateMobileMaintenance(Request $request){
        $value = $request->MOBILE_MAINTENANCE;

        $update = WebsiteConfiguration::where('website_config_name','MOBILE_MAINTENANCE')->update(['website_config_value'=>$value]);

        /*update activity log start*/
        $action_type = "updated"; 
        $module_name = "mobilemaintenance";            
        $logdetails = "Admin has updated mobile maintenance configuration";
        $old_data = "";
        $new_data = "";
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails );
        $this->updateLogActivity($logdata);
        /*update activity log End*/

        return ['status'=>'success','msg'=> Lang::get('common.records_updated_successfully')];
    }
}
