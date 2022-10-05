<?php
namespace App\Http\Controllers\Admin\Block;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;

use App\Language;
use App\StaticBlock;
use App\StaticBlockDesc;
use App\StaticBlockDescRevision;
use Auth;
use Lang;

class StaticBlockController extends MarketPlace
{ 
    private $tblStaticBlock;
    private $tblStaticBlockDesc;
    public function __construct()
    {
        $this->middleware('admin.user');

        $this->tblStaticBlock = with(new StaticBlock)->getTable();
        $this->tblStaticBlockDesc = with(new StaticBlockDesc)->getTable();
    }
    
    public function index(){
        
        $permission = $this->checkUrlPermission('static_block');
        if($permission === true) {
            $permission_arr['add'] = $this->checkMenuPermission('add_static_block');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_static_block');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_static_block');

            $data_arr = array();
            $data_cus_arr = array();
            //$page_dtl = StaticBlock::getStaticBlock();
            $system_page_dtl = StaticBlock::getStaticSystemBlock();
            $custom_page_dtl = StaticBlock::getStaticCustomeBlock();
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
                    //$array_temp['created_at'] = getDateFormat($value->created_at, '1');
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
                    $array_cus_temp['title'] = isset($custom_value->staticBlockDesc->page_title) ? $custom_value->staticBlockDesc->page_title :'';              
                    $array_cus_temp['created_at'] = $custom_value->created_at;
                    $array_cus_temp['updated_at'] = $custom_value->updated_at;

                    $data_cus_arr[] = $array_cus_temp;
                }

            }
            //dd($data_cus_arr);
            return view('admin.block.staticBlockList', ['page_dtls'=>$data_arr, 'permission_arr'=>$permission_arr,'page_cus_dtl'=>$data_cus_arr]);
        }
    }
    
    public function create(){

        $permission = $this->checkUrlPermission('add_static_block');
        if($permission === true) {

            $lang_lists = Language::getLangugeDetails();
            return view('admin.block.staticBlockCreate', ['lang_lists'=>$lang_lists]);
        }
    }
    
    function store(Request $request){ 

        $input = $request->all();
        $input['page_ttl'] = $request->page_title[session('admin_default_lang')];
        $input['page_description'] = $request->page_desc[session('admin_default_lang')];
        $input['url'] = createUrl($input['page_ttl'], '-');       

        $validate = $this->validateCMS($input);

        if ($validate->passes()) {

            $cms = new StaticBlock;
            $cms->url = str_slug($input['page_ttl'], '-');
            $cms->status = $request->status;
            $cms->created_by = Auth::guard('admin_user')->user()->id;
            $cms->save();

            $data_arr = $this->filterBlockData($request);
            //echo '<pre>';print_r($data_arr);die;              

            StaticBlockDesc::insertBlockDesc($data_arr, $cms->id);
            $blok_id = $cms->id;
            StaticBlockDescRevision::insertStaticBloclDescRevision($data_arr, $blok_id);


            /***entry in block table**/
            $dataArr = ['type'=>'static-block','type_id'=>$cms->id,'updated_by'=>Auth::guard('admin_user')->user()->id,'status'=>$request->status];
            \App\Block::insertBlock($dataArr);

            /*update activity log start*/
            $action_type = "created"; 
            $module_name = "static block";            
            $logdetails = "Admin has created ".$request->page_title[session('admin_default_lang')]." ".$module_name;
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/
            
            if($request->submit_type == 'submit_continue') {

                return $response =['status'=>'success', 'url' =>action('Admin\Block\StaticBlockController@edit',$cms->id)];

            }
            else {

                return $response =['status'=>'success', 'url' =>action('Admin\Block\StaticBlockController@index')];
            }
        } 
        else {
            $errors =  $validate->errors(); 
            return array('status'=>'fail','message'=>$errors);
        }                
    }

    public function filterBlockData($request) {

        $def_lang = session('admin_default_lang');
        $def_title = $request->page_title[$def_lang];
        $def_desc = $request->page_desc[$def_lang];

        foreach ($request->page_desc as $key=>$value){

            $page_title = $request->page_title[$key];
            $page_desc = $request->page_desc[$key];

            if(empty($page_title)) {
                $page_title = $def_title;
            }
            if(empty($page_desc)) {
                $page_desc = $def_desc;
            } 

            $data_arr[$key] = array('page_title'=>$page_title, 'page_desc'=>$page_desc);
        } 
        return $data_arr;       
    }

    public function show($id) {
    }
    
    function edit($id) {
        $permission = $this->checkUrlPermission('edit_static_block');
        if($permission === true) {

            $page_dtls = StaticBlock::getStaticBlockbyId($id);
            $revisions = StaticBlockDescRevision::groupBy('revision')->where('static_block_id',$id)->selectRaw('count(*) as total, revision')->get();
            $revision = count($revisions);
            
            return view('admin.block.staticBlockEdit', ['page_dtls'=>$page_dtls, 'tblStaticBlockDesc'=>$this->tblStaticBlockDesc,'revision'=>$revision]);
        }
    }
    
    function update(Request $request, $page_id){
        
        //echo '<pre>';print_r($request->all());die;
        $block_data = StaticBlock::find($page_id);
        if(empty($block_data)){
            return redirect()->action('Admin\Block\StaticBlockController@index')->with('errorMsg', 'invalid id');
        }

        $input = $request->all();
        $input['page_ttl'] = $request->page_title[session('admin_default_lang')];
        $input['page_description'] = $request->page_desc[session('admin_default_lang')];        
        $page_dtls = StaticBlock::getStaticBlockbyId($page_id);
        $validate = $this->validateCMS($input, $page_id);      

        if ($validate->passes()) {
        
            StaticBlock::where(['id'=>$page_id])->update(['status' => $request->status,'updated_by'=>Auth::guard('admin_user')->user()->id]);
            
            $data_arr = $this->filterBlockData($request);
            //echo '<pre>';print_r($data_arr);die;              

            StaticBlockDesc::updateBlockDesc($data_arr, $page_id);  

            if($block_data->is_system < 1){
                $blockData = \App\Block::where(['type_id'=>$page_id,'type'=>'static-block'])->first();
                if(!empty($blockData)){
                    $blockData->status = $request->status;
                    $blockData->save();
                }else{
                    /***entry in block table if block not exist**/
                    $dataArr = ['type'=>'static-block','type_id'=>$page_id,'updated_by'=>Auth::guard('admin_user')->user()->id,'status'=>$request->status];
                    \App\Block::insertBlock($dataArr);
                }
            }

            /*$blok_id = $page_id;
            StaticBlockDescRevision::updatestatickblockDescRevision($data_arr, $blok_id);*/

            $oldpage_title = $page_dtls->staticBlockDesc->page_title;
            $oldpage_desc = $page_dtls->staticBlockDesc->page_desc;
            $newpage_title = $request->page_title[session('admin_default_lang')];
            $newpage_desc = $request->page_desc[session('admin_default_lang')];

            $revisions = StaticBlockDescRevision::groupBy('revision')->where('static_block_id',$page_id)->selectRaw('count(*) as total, revision')->get();
            $count = count($revisions);

            if($oldpage_title != $newpage_title || $oldpage_desc != $newpage_desc){
                $blok_id = $page_id;
                StaticBlockDescRevision::updatestatickblockDescRevision($data_arr, $blok_id);
               
                $count = $count+1;
    
            }

            
            /*update activity log start*/
            $action_type = "updated"; 
            $module_name = "static block";            
            $logdetails = "Admin has updated ".$request->page_title[session('admin_default_lang')]." ".$module_name;
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/    
            if($request->submit_type == 'submit_continue') {

                return $response =['status'=>'update','count'=>$count, 'url' =>action('Admin\Block\StaticBlockController@edit',$page_id)];
            }
            else {
                
                return $response =['status'=>'success','count'=>$count, 'url' =>action('Admin\Block\StaticBlockController@index')];
            }

        }
        else {

            //return redirect()->action('Admin\Block\StaticBlockController@edit', $page_id)->withErrors($validate)->withInput();

            $errors =  $validate->errors(); 
            return array('status'=>'fail','message'=>$errors);
        }            
    }    
    
    function destroy($id){

        //echo '====>'.$id;die;
        $static_cms = StaticBlock::getStaticBlockbyId($id);
        if(!empty($static_cms)){
            StaticBlock::where('id', $id)->delete();
            \App\Block::where(['type_id'=>$id,'type'=>'static-block'])->delete();

            $namedesc = $static_cms->staticBlockDesc;
            $logname = !empty($namedesc)?$namedesc->page_title:$id;

            /*update activity log start*/
            $action_type = "deleted"; 
            $module_name = "static block";            
            $logdetails = "Admin has deleted $logname ";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/
            return redirect()->action('Admin\Block\StaticBlockController@index')->with('succMsg', 'Record Deleted Successfully!');
        }else{
            return redirect()->action('Admin\Block\StaticBlockController@index')->with('errorMsg', 'error');
        }
        
    }

    function changeStatus($id) {

        $static_cms = StaticBlock::getStaticBlockbyId($id);
        
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

        if($static_cms->save()){
            if($static_cms->is_system < 1){
                $blockData = \App\Block::where(['type_id'=>$static_cms->id,'type'=>'static-block'])->first();
                if(!empty($blockData)){
                    $blockData->status = $static_cms->status;
                    $blockData->save();
                }
            }
        }

        $namedesc = $static_cms->staticBlockDesc;
        $logname = !empty($namedesc)?$namedesc->page_title:$id;

        /*update activity log start*/
        $action_type = "updated"; 
        $module_name = "static block";            
        $logdetails = "Admin has updated $logname status to $status_msg ";
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/

        return $status_msg;
    }

    private function validateCMS($input, $cms_id='') {

        if(empty($cms_id) && !empty($input['url'])) {
            $rules['url'] = 'Required|unique:'.$this->tblStaticBlock.',url';
        }

        $rules['page_ttl'] = 'Required|Min:5';
        $rules['page_description'] = 'Required|Min:5';      

        $error_msg['url.required'] = Lang::get('admin_common.enter_url_key');
        $error_msg['url.unique'] = Lang::get('admin_common.this_url_already_exist');
        $error_msg['page_ttl.required'] = Lang::get('admin_common.enter_page_title');
        $error_msg['page_description.required'] = Lang::get('admin_common.enter_page_description');       

        $validate = Validator::make($input, $rules, $error_msg);
        
        return $validate; 
    }

    function blockrevision($block_id) {

        $revisions = StaticBlockDescRevision::select('*')->groupBy('revision')->where('static_block_id',$block_id)->selectRaw('count(*) as total, revision')->get();
        //dd($revisions);
        $revision = count($revisions);
        return view('admin.block.revisionBlockList', ['revisions'=>$revisions,'revision'=>$revision,'block_id'=>$block_id]);

    }

    function restoreblockrevision($block_id,$revision) {

        $revisions = StaticBlockDescRevision::select('*')->where('static_block_id',$block_id)->where('revision',$revision)->first();

        $data = StaticBlockDesc::where(['static_block_id'=>$block_id])
                ->update(['page_title' => $revisions->static_block_title,'page_desc' => $revisions->static_block_desc]); 

        
        $permission = $this->checkUrlPermission('static_block');
        if($permission === true) {
            return redirect()->action('Admin\Block\StaticBlockController@index')->with('succMsg', Lang::get('common.records_updated_successfully'));
        }

    }    
}
