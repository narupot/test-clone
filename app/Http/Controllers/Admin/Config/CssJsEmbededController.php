<?php

namespace App\Http\Controllers\Admin\Config;

use App\Http\Controllers\MarketPlace;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;     // when use Rule function
use Illuminate\Http\Request;
use App\CssJsEmbeded;
use App\Language;
use Config;
use DB;
use Lang;
class CssJsEmbededController extends MarketPlace
{


    public function __construct()
    {   
        $this->middleware('admin.user'); 
       
    }     

    public function index()
    {

        $permission = $this->checkUrlPermission('css_js_embeded_list');

        if($permission === true) {

            $permission_arr['add'] = $this->checkMenuPermission('add_cssjssembeded');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_cssjssembeded');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_cssjssembeded');      
   
            $results = CssJsEmbeded::getCssJsEmbeded(); 
            //dd($results);

            return view('admin.config.cssJssEmbededList', ['results'=>$results, 'permission_arr'=>$permission_arr]
            );
        }
    }

    public function create()
    {       
        $permission = $this->checkUrlPermission('add_cssjssembeded');        
        if($permission === true) { 
        $seo_pages = \App\SeoPage::select(['name','url'])->where('status','1')->get()->toArray();
        if(count($seo_pages) > 0) {
            foreach ($seo_pages as $key => $value) {
                if($value['url'] == '/') {
                    $seo_pages[$key]['url'] = 'home';
                }
            }
        }        
          return view('admin.config.cssJssEmbededCreate',['seo_pages'=>$seo_pages]); 
        }       
    }

    public function store(Request $request)
    {               
        //echo '<pre>';print_r($request->all());die;
        //dd(implode(',', $request->page_url));        
        $insertresult = new CssJsEmbeded;
        $insertresult->title = $request->title;

        if ($request->page_url) {
        	$url=implode(',', $request->page_url);
        	$insertresult->page_url = $url;
        }
        //$insertresult->page_url = $request->page_url;
        $insertresult->custom_url = $request->custom_url;
        /*$css ='';
        $js ='';
        if ($request->embeded_css) {
        	$css=implode(',', $request->embeded_css);
        	$insertresult->embeded_css = $css;
        }
        
        if ($request->embeded_js) {
        	$js=implode(',', $request->embeded_js);
        	$insertresult->embeded_js = $js;
        }*/
        $css ='';
        $js ='';
        if ($request->embeded_css) {
            $css=implode(',', $request->embeded_css);
        }
        $insertresult->embeded_css = $css;
        if ($request->embeded_js) {
            $js=implode(',', $request->embeded_js);
        }
        $insertresult->embeded_js = $js;
        $insertresult->status = $request->status;

        $insertresult->save();
        $action_type = "created"; 
        $module_name = "embeded css js";            
        $logdetails = "Admin has created ".$request->title." ".$module_name;
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/
        return redirect()->action('Admin\Config\CssJsEmbededController@index')->with('succMsg', 'Records added Successfully!');            
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $permission = $this->checkUrlPermission('edit_cssjssembeded');        
        if($permission === true) {
            $seo_pages = \App\SeoPage::select(['name','url'])->where('status','1')->get()->toArray();
            if(count($seo_pages) > 0) {
                foreach ($seo_pages as $key => $value) {
                    if($value['url'] == '/') {
                        $seo_pages[$key]['url'] = 'home';
                    }
                }
            }
            $result = CssJsEmbeded::getCssJsEmbeded($id);
            if(isset($result) && !empty($result->page_url)){
                    $page_arr = explode(',', $result->page_url);
            }else{
                $page_arr = [];
            }
            return view('admin.config.cssJssEmbededEdit', ['result'=>$result, 'seo_pages'=>$seo_pages,'page_arr'=>$page_arr]);
        }            
    }

    public function update(Request $request, $id){
        //dd($request);
        $insertresult = CssJsEmbeded::find($id);
        $insertresult->title = $request->title;
        if ($request->page_url) {
        	$url=implode(',', $request->page_url);
        	$insertresult->page_url = $url;
        }
        $insertresult->custom_url = $request->custom_url;
        $css ='';
        $js ='';
        if ($request->embeded_css) {
        	$css=implode(',', $request->embeded_css);
        }
        $insertresult->embeded_css = $css;
        if ($request->embeded_js) {
        	$js=implode(',', $request->embeded_js);
        }
        $insertresult->embeded_js = $js;
        $insertresult->status = $request->status;

        $insertresult->save();

        /*update activity log start*/
        $action_type = "updated"; 
        $module_name = "embeded css js";            
        $logdetails = "Admin has updated ".$request->title." ".$module_name;
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/
        if($request->submit_type == 'submit_continue') {

                return redirect()->action('Admin\Config\CssJsEmbededController@edit',$id)->with('succMsg', 'Records added Successfully!'); 
            }
            else {
                
                return redirect()->action('Admin\Config\CssJsEmbededController@index')->with('succMsg', 'Records added Successfully!'); 
            }
        //return redirect()->action('Admin\Config\CssJsEmbededController@index')->with('succMsg', 'Records added Successfully!'); 
        
    }

    function destroy($id){

        $permission = $this->checkUrlPermission('delete_cssjssembeded');
        if($permission === true) {
            $embeded = CssJsEmbeded::getCssJsEmbeded($id);
            if(!empty($embeded)){
                try{

                    CssJsEmbeded::where('id', $id)->delete();
                    
                    $namedesc = $embeded->title;
                    $logname = !empty($namedesc)?$namedesc:$id;

                    /*update activity log start*/
                    $action_type = "deleted"; 
                    $module_name = "embeded css js";            
                    $logdetails = "Admin has $action_type $logname ";
                    $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

                    $this->updateLogActivity($logdata);
                    /*update activity log End*/

                    return redirect()->action('Admin\Config\CssJsEmbededController@index')->with('succMsg', Lang::get('admin_common.records_deleted_successfully'));

                }catch (QueryException $ex){
                    return redirect()->action('Admin\Config\CssJsEmbededController@index')->with('errorMsg', $ex->getMessage());
                }
                
            }else{
                return redirect()->action('Admin\Config\CssJsEmbededController@index')->with('errorMsg', Lang::get('admin_common.error'));
            }
            
        }
    }
        
}
