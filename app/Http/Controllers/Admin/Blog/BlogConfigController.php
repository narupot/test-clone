<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;

use App\BlogConfig;
use App\Timezones;
use File;
use Lang;

class BlogConfigController extends MarketPlace
{
    
    public function __construct()
    {   
        $this->middleware('admin.user');       
    }     
    
    public function index()
    {  
        $permission = $this->checkUrlPermission('blog_config');
        if($permission === true) {   

            $config_arr = $this->getBlogConfiguration('blog_config');
            $config_arr_search = $this->getBlogConfigurationSearch('blog_config');
            return view('admin.blog.blogConfig', ['config_arr'=>$config_arr,'config_arr_search'=>$config_arr_search]);
        }
    }

    public function store(Request $request)
    {                
        $postArr = $request->all();
        $configAllData = BlogConfig::all();

        foreach ($configAllData as $configVal) {            
            if(isset($postArr[$configVal->blog_config_name])) {
                $config = BlogConfig::find($configVal->id); 
                $config->blog_config_name = $configVal->blog_config_name;                
                if($configVal->blog_config_name=='BLOG_SEARCH_CONFIG'){                    
                    $searchValue = $postArr['BLOG_SEARCH_CONFIG'];  
                    $searchData = implode(',', $searchValue); 
                    $config->blog_config_value = $searchData;
                }else{
                    $config->blog_config_value = $postArr[$configVal->blog_config_name];                
                }
                $config->save();           
            }  
        }

        /*update activity log start*/
        $action_type = "updated"; 
        $module_name = "blogconfig";            
        $logdetails = "Admin has updated blog configuration";
        $old_data = "";
        $new_data = "";
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );
        $this->updateLogActivity($logdata);
        /*update activity log End*/ 

        if(!empty($request->redirect_path)) {
            $action = $request->redirect_path;
        }
        else {
            $action = 'Admin\Blog\BlogConfigController@index';
        }

        return redirect()->action($action)->with('succMsg', Lang::get('common.records_updated_successfully'));
    }                   

    public function getBlogConfiguration($type) {
   
        $conf_lists = \App\BlogConfig::getBlogConfig($type); 
        $conf_lists = $conf_lists->toArray();
        foreach($conf_lists as $val) {            
            $config_arr[$val['blog_config_name']] = $val['blog_config_value'];
        }                
        return $config_arr;
    }

    public function getBlogConfigurationSearch($type) {
   
        $conf_lists = \App\BlogConfig::getBlogConfig($type); 
        $conf_lists = $conf_lists->toArray();        
        foreach($conf_lists as $val) {
            if(!empty($val['blog_config_value'])){  
                $data = explode(',', $val['blog_config_value']);                
                $config_arr_search[$val['blog_config_name']] = $data;
            }
        }        
        return $config_arr_search;
    }
    

}
