<?php
namespace App\Http\Controllers\Admin\Page;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Config;
use App\Language;
use App\StaticPage;
use App\StaticPageDesc;
use App\StaticPageDescRevision;
use Auth;
use Lang;


class StaticPageController extends MarketPlace
{ 
    private $tblStaticPage;
    private $tblStaticPageDesc;

    public function __construct()
    {
        $this->middleware('admin.user');

        $this->tblStaticPage = with(new StaticPage)->getTable();
        $this->tblStaticPageDesc = with(new StaticPageDesc)->getTable();
    }

    public function index(){

        $permission = $this->checkUrlPermission('static_page');
        if($permission === true) {
            $permission_arr['add'] = $this->checkMenuPermission('add_static_page');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_static_page');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_static_page');

            $data_arr = array();
            $data_cus_arr = array();
            //$page_dtl = StaticPage::getStaticPage();
            $custom_page_dtl = StaticPage::getStaticCustomeBlock();
            $system_page_dtl = StaticPage::getStaticSystemBlock();
            //dd($custom_page_dtl);


            if(count($system_page_dtl) > 0) {

                foreach ($system_page_dtl as $key => $value) {

                    $array_temp['id'] = $value->id;
                    $array_temp['url'] = $value->url;

                    $status = 'Inactive';
                    if($value->status == '1') {
                        $status = 'Active';
                    }

                    $array_temp['is_system'] = $value->is_system;
                    $array_temp['status'] = $status;
                    $array_temp['title'] = isset($value->staticBlockDesc->page_title) ? $value->staticBlockDesc->page_title :'';              
                    $array_temp['created_at'] = $value->created_at;
                    $array_temp['updated_at'] = $value->updated_at;

                    $data_arr[] = $array_temp;
                }

            }
            if(count($custom_page_dtl) > 0) {

                foreach ($custom_page_dtl as $key => $custom_value) {

                    $array_cus_temp['id'] = $custom_value->id;
                    $array_cus_temp['url'] = $custom_value->url;

                    $status = 'Inactive';
                    if($custom_value->status == '1') {
                        $status = 'Active';
                    }

                    $array_cus_temp['is_system'] = $custom_value->is_system;
                    $array_cus_temp['status'] = $status;
                    $array_cus_temp['title'] = isset($custom_value->staticPageDesc->page_title) ? $custom_value->staticPageDesc->page_title :'';              
                    //$array_cus_temp['created_at'] = getDateFormat($custom_value->created_at, '1');
                    $array_cus_temp['created_at'] = $custom_value->created_at;
                    $array_cus_temp['updated_at'] = $custom_value->updated_at;
                    $data_cus_arr[] = $array_cus_temp;
                }

            }
            return view('admin.page.staticPageList', ['page_dtls'=>$data_arr, 'permission_arr'=>$permission_arr,'page_cus_dtl'=>$data_cus_arr]);
        }
    }

    public function getPageList(){
        $data_arr = array();
        $page_dtl = StaticPage::getStaticPage();
        if(count($page_dtl) > 0) {

                foreach ($page_dtl as $key => $value) {

                    $array_temp['id'] = $value->id;
                    $array_temp['url'] = $value->url;

                    $status = 'Inactive';
                    if($value->status == '1') {
                        $status = 'Active';
                    }

                    $array_temp['status'] = $status;             
                    $array_temp['created_at'] = getDateFormat($value->created_at, '1');
                    $array_temp['updated_at'] = getDateFormat($value->updated_at, '1');

                    $data_arr[] = $array_temp;
                }
            //dd($data_arr);
            return $data_arr;
        }
    }    
    
    public function create(){

        $permission = $this->checkUrlPermission('add_static_page');
        if($permission === true) {

            $lang_lists = Language::getLangugeDetails();
            return view('admin.page.staticPageCreate', ['lang_lists'=>$lang_lists]);
        }
    }
    
    function store(Request $request){ 
        //dd($request->all());
        $input = $request->all();
        $input['page_ttl'] = $request->page_title[session('admin_default_lang')];
        $input['page_description'] = $request->page_desc[session('admin_default_lang')];
        $input['url'] = !empty($request->cms_url)?createUrl($request->cms_url):createUrl($request->page_title[session('admin_default_lang')]);        

        $validate = $this->validateCMS($input);

        if ($validate->passes()) {

            $cms = new StaticPage;

            if(!empty($request->cms_url)){
                $cms->url = createUrl($request->cms_url);
            }
            else{
                $cms->url = createUrl($request->page_title[session('admin_default_lang')]);
            }
            //dd($cms->url);  
            //$cms->url = str_slug($request->cms_url, '-');
            $cms->status = $request->status;
            $cms->header_footer = $request->header_footer;
            if(!empty($request->metaimage)) {
                
                $uploadDetails['path'] = Config::get('constants.page_socialshare_img_path');
                $uploadDetails['file'] =  $request->metaimage;
                $uploadDetails['width'] =  360;
                $uploadDetails['height'] = 250;

                $file_name = $this->uploadFileCustom($uploadDetails,'1');

                $cms->metaimage = $file_name;
            }

            if(!empty($request->fbimage)) {
                
                $uploadDetails['path'] = Config::get('constants.page_socialshare_img_path');
               
                $uploadDetails['file'] =  $request->fbimage;
                $uploadDetails['width'] =  360;
                $uploadDetails['height'] = 250;

                $file_name = $this->uploadFileCustom($uploadDetails,'1');

                $cms->fbimage = $file_name;
            }

            if(!empty($request->twimage)) {
                
                $uploadDetails['path'] = Config::get('constants.page_socialshare_img_path');
                $uploadDetails['file'] =  $request->twimage;
                $uploadDetails['width'] =  360;
                $uploadDetails['height'] = 250;

                $file_name = $this->uploadFileCustom($uploadDetails,'1');

                $cms->twimage = $file_name;
            }

            if(!empty($request->insimage)) {
                
                $uploadDetails['path'] = Config::get('constants.page_socialshare_img_path');
                $uploadDetails['file'] =  $request->insimage;
                $uploadDetails['width'] =  360;
                $uploadDetails['height'] = 250;

                $file_name = $this->uploadFileCustom($uploadDetails,'1');

                $cms->insimage = $file_name;
            }
            $cms->created_by = Auth::guard('admin_user')->user()->id;
            $cms->save();

            $data_arr = $this->filterPageData($request);              

            StaticPageDesc::insertPageDesc($data_arr, $cms->id);
            $page_id = $cms->id;
            StaticPageDescRevision::insertStaticPageDescRevision($data_arr, $page_id);

            /*update activity log start*/
            $action_type = "created"; 
            $module_name = "static page";            
            $logdetails = "Admin has created ".$request->page_title[session('admin_default_lang')]." static page";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/
            
            if($request->submit_type == 'submit_continue') {

                return $response =['status'=>'success', 'url' =>action('Admin\Page\StaticPageController@edit',$cms->id)];
            }
            else {
                
                return $response =['status'=>'success', 'url' =>action('Admin\Page\StaticPageController@index')];
            }
        } 
        else {
            
            $errors =  $validate->errors(); 
            return array('status'=>'fail','message'=>$errors);
        }                 
    }

    public function filterPageData($request) {

        $def_lang = session('admin_default_lang');
        $def_title = $request->page_title[$def_lang];
        $def_desc = $request->page_desc[$def_lang];
        $def_meta_title = $request->meta_title[$def_lang];
        $def_meta_keyword = $request->meta_keyword[$def_lang];
        $def_meta_desc = $request->meta_desc[$def_lang];
        $def_fbtitle = $request->fbtitle[$def_lang];
        $def_twtitle = $request->twtitle[$def_lang];
        $def_institle = $request->institle[$def_lang];
        $def_fbdesc = $request->fbdesc[$def_lang];
        $def_twdesc = $request->twdesc[$def_lang];
        $def_insdesc = $request->insdesc[$def_lang];

        foreach ($request->page_desc as $key=>$value){

            $page_title = $request->page_title[$key];
            $page_desc = $request->page_desc[$key];
            $meta_title = $request->meta_title[$key];
            $meta_keyword = $request->meta_keyword[$key];
            $meta_desc = $request->meta_desc[$key];
            $fbtitle = $request->fbtitle[$key];
            $twtitle = $request->twtitle[$key];
            $institle = $request->institle[$key];
            $fbdesc = $request->fbdesc[$key];
            $twdesc = $request->twdesc[$key];
            $insdesc = $request->insdesc[$key];
            //dd($fbtitle,$twtitle,$institle);

            if(empty($page_title)) {
                $page_title = $def_title;
            }

            if(empty($page_desc)) {
                $page_desc = $def_desc;
            } 

            if(empty($meta_title)) {
                $meta_title = $def_meta_title;
            }

            if(empty($meta_keyword)) {
                $meta_keyword = $def_meta_keyword;
            }
            if(empty($meta_desc)) {
                $meta_desc = $def_meta_desc;
            }
            if(empty($fbtitle)) {
                $fbtitle = $def_fbtitle;
            }

            if(empty($twtitle)) {
                $twtitle = $def_twtitle;
            }

            if(empty($institle)) {
                $institle = $def_institle;
            }

            if(empty($fbdesc)) {
                $fbdesc = $def_fbdesc;
            }

            if(empty($twdesc)) {
                $twdesc = $def_twdesc;
            }

            if(empty($insdesc)) {
                $insdesc = $def_insdesc;
            }

            $data_arr[$key] = array('page_title'=>$page_title, 'page_desc'=>$page_desc, 'meta_title'=>$meta_title,'meta_keyword'=>$meta_keyword, 'meta_desc'=>$meta_desc,'fbtitle' => $fbtitle,
                'twtitle' => $twtitle,'institle' => $institle,'fbdesc' => $fbdesc,'twdesc' => $twdesc,'insdesc' => $insdesc);
        } 
        return $data_arr;       
    }

    public function show($id) {
    }
    
    function edit($id) {
        $permission = $this->checkUrlPermission('edit_static_page');
        if($permission === true) {

            $page_dtls = StaticPage::getStaticPagebyId($id);
            $revisions = StaticPageDescRevision::select('*')->groupBy('revision')->where('static_page_id',$id)->selectRaw('count(*) as total, revision')->get();
            $revision = count($revisions);
            //dd($page_dtls);

            return view('admin.page.staticPageEdit', ['page_dtls'=>$page_dtls, 'tblStaticPageDesc'=>$this->tblStaticPageDesc,'revision'=>$revision]);
        }
    }
    
    function update(Request $request, $page_id){
  
        //echo '<pre>';print_r($request->all());die;

        $input = $request->all();
        $input['page_ttl'] = $request->page_title[session('admin_default_lang')];
        $input['page_description'] = $request->page_desc[session('admin_default_lang')];
        $input['url'] = createUrl($request->cms_url);

        $page_dtls = StaticPage::getStaticPagebyId($page_id);

        $validate = $this->validateCMS($input, $page_id);      

        if ($validate->passes()) {

            StaticPage::where(['id'=>$page_id])->update(['url' => createUrl($request->cms_url), 'status' => $request->status,'header_footer' => $request->header_footer,'updated_by'=>Auth::guard('admin_user')->user()->id]);
            $update_data=[];
            if(!empty($request->metaimage)) {
                
                $uploadDetails['path'] = Config::get('constants.page_socialshare_img_path');
                $uploadDetails['file'] =  $request->metaimage;
                $uploadDetails['width'] =  360;
                $uploadDetails['height'] = 250;

                $file_name = $this->uploadFileCustom($uploadDetails,'1');   
                
                $update_data['metaimage'] = $file_name;             

                $delete_file[] = Config::get('constants.page_socialshare_img_path').'/'.$page_dtls->metaimage;               
            }

            if(!empty($request->fbimage)) {
                
                $uploadDetails['path'] = Config::get('constants.page_socialshare_img_path');
                $uploadDetails['file'] =  $request->fbimage;
                $uploadDetails['width'] =  360;
                $uploadDetails['height'] = 250;

                $file_name = $this->uploadFileCustom($uploadDetails,'1');   
                
                $update_data['fbimage'] = $file_name;             

                $delete_file[] = Config::get('constants.page_socialshare_img_path').'/'.$page_dtls->fbimage;               
            }

            if(!empty($request->twimage)) {
                
                $uploadDetails['path'] = Config::get('constants.page_socialshare_img_path');
                $uploadDetails['file'] =  $request->twimage;
                $uploadDetails['width'] =  360;
                $uploadDetails['height'] = 250;

                $file_name = $this->uploadFileCustom($uploadDetails,'1');   
                
                $update_data['twimage'] = $file_name;             

                $delete_file[] = Config::get('constants.page_socialshare_img_path').'/'.$page_dtls->twimage;               
            }

            if(!empty($request->insimage)) {
                
                $uploadDetails['path'] = Config::get('constants.page_socialshare_img_path');
                $uploadDetails['file'] =  $request->insimage;

                $uploadDetails['width'] =  360;
                $uploadDetails['height'] = 250;

                $file_name = $this->uploadFileCustom($uploadDetails,'1');   
                
                $update_data['insimage'] = $file_name;             

                $delete_file[] = Config::get('constants.page_socialshare_img_path').'/'.$page_dtls->insimage;               
            }

            if(!empty($delete_file)) {
                $this->fileDelete($delete_file);
            }
            //dd($update_data);
            if(!empty($update_data)){
                StaticPage::where(['id'=>$page_id])->update($update_data);
            }
            
            
            $data_arr = $this->filterPageData($request);
            //echo '<pre>';print_r($data_arr);die;              

            StaticPageDesc::updatePageDesc($data_arr, $page_id);

            /*StaticPageDescRevision::updatestatickpageDescRevision($data_arr, $page_id);*/
            //for revision
            $revisions = StaticPageDescRevision::select('*')->groupBy('revision')->where('static_page_id',$page_id)->selectRaw('count(*) as total, revision')->get();
            $count = count($revisions);
            $oldpage_title = $page_dtls->staticPageDesc->page_title;
            $oldpage_desc = $page_dtls->staticPageDesc->page_desc;
            $newpage_title = $request->page_title[session('admin_default_lang')];
            $newpage_desc = $request->page_desc[session('admin_default_lang')];

            if($oldpage_title != $newpage_title || $oldpage_desc != $newpage_desc){
                StaticPageDescRevision::updatestatickpageDescRevision($data_arr, $page_id);
                $count = $count+1;
    
            }       

            /*update activity log start*/
            $action_type = "updated"; 
            $module_name = "static page";            
            $logdetails = "Admin has updated ".$request->page_title[session('admin_default_lang')]." static page";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);

            //return $response =['status'=>'success', 'url' =>action('Admin\Page\StaticPageController@index')];

            if($request->submit_type == 'submit_continue') {

                return $response =['status'=>'update','count'=>$count, 'url' =>action('Admin\Page\StaticPageController@edit',$page_id)];
            }
            else {
                
                return $response =['status'=>'success','count'=>$count, 'url' =>action('Admin\Page\StaticPageController@index')];
            }
        }
        else {

            $errors =  $validate->errors(); 
            return array('status'=>'fail','message'=>$errors);
        }            
    }    
    
    function destroy($id){

        $permission = $this->checkUrlPermission('delete_static_page');
        if($permission === true) {
            $static_cms = StaticPage::getStaticPagebyId($id);
            if(!empty($static_cms)){
                StaticPage::where('id', $id)->delete();

                $namedesc = $static_cms->staticPageDesc;
                $logname = !empty($namedesc)?$namedesc->page_title:$id;

                /*update activity log start*/
                $action_type = "deleted"; 
                $module_name = "static page";            
                $logdetails = "Admin has deleted $logname ";
                $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

                $this->updateLogActivity($logdata);
                /*update activity log End*/

                return redirect()->action('Admin\Page\StaticPageController@index')->with('succMsg', Lang::get('common.records_deleted_successfully'));
            }else{
                return redirect()->action('Admin\Page\StaticPageController@index')->with('errorMsg', Lang::get('common.error'));
            }
            
        }
    }

    function changeStatus($id) {

        $static_cms = StaticPage::getStaticPagebyId($id);

        if($static_cms->status == '1') {
            $status = '0';
            $status_msg = 'Inactive';
        }
        else {
            $status = '1';
            $status_msg = 'Active';            
        }

        $static_cms->status = $status;
        $static_cms->updated_at = date('Y-m-d H:i:s');
        $static_cms->updated_by = Auth::guard('admin_user')->user()->id;

        $static_cms->save();

        $namedesc = $static_cms->staticPageDesc;
        $logname = !empty($namedesc)?$namedesc->page_title:$id;

        /*update activity log start*/
        $action_type = "updated"; 
        $module_name = "static page";            
        $logdetails = "Admin has updated $logname status to $status_msg ";
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/

        return $status_msg;
    }

    private function validateCMS($input, $cms_id='') {

        $rules['url'] = 'Required|unique:'.$this->tblStaticPage.',url';

        if(!empty($cms_id) && !empty($input['url'])) {
            $rules['url'] = Rule::unique($this->tblStaticPage)->ignore($cms_id);
        }

        $rules['page_description'] = 'Required|Min:5';
        $rules['page_ttl'] = 'Required|Min:3';      

        $error_msg['url.required'] = Lang::get('admin_common.enter_url_key');
        $error_msg['url.unique'] = Lang::get('admin_common.this_url_key_already_taken');
        $error_msg['page_description.required'] = Lang::get('admin_common.enter_page_description');
        $error_msg['page_ttl.required'] = Lang::get('admin_common.enter_page_pitle');         

        $validate = Validator::make($input, $rules, $error_msg);
        
        return $validate; 
    }

    public function getBlogConfigValue($system_name=null) {
        $system_val = '';
        if(!empty($system_name)){ 
          $system_val = \App\BlogConfig::getBlogValue($system_name);
        }  
        return $system_val;         
    }

    function pagerevision($page_id) {

        $revisions = StaticPageDescRevision::select('*')->groupBy('revision')->where('static_page_id',$page_id)->selectRaw('count(*) as total, revision')->get();
        //dd($revisions);
        $revision = count($revisions);
        return view('admin.page.revisionPageList', ['revisions'=>$revisions,'revision'=>$revision,'page_id'=>$page_id]);

    }

    function restorepagerevision($page_id,$revision) {

        $revisions = StaticPageDescRevision::select('*')->where('static_page_id',$page_id)->where('revision',$revision)->first();

        $data = StaticPageDesc::where(['static_page_id'=>$page_id])
                ->update(['page_title' => $revisions->static_page_title,'page_desc' => $revisions->static_page_desc]); 
        
        $permission = $this->checkUrlPermission('static_block');
        if($permission === true) {
        return redirect()->action('Admin\Page\StaticPageController@index')->with('succMsg', 'Record updated Successfully!');
        }

    }    
}
