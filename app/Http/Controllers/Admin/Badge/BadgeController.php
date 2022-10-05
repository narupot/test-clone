<?php
namespace App\Http\Controllers\Admin\Badge;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Config;
use App\Language;
use App\Badge;
use App\BadgeDesc;
use App\MongoBadge;
use Auth;
use Lang;
use DB;


class BadgeController extends MarketPlace
{ 
    private $tblBadge;
    private $tblBadgeDesc;
    private $module_name = "standard badge";
    public function __construct()
    {
        $this->middleware('admin.user');

        $this->tblBadge = with(new Badge)->getTable();
        $this->tblBadgeDesc = with(new BadgeDesc)->getTable();
    }

    public function index(){

        $permission = $this->checkUrlPermission('product_standard_badge');
        if($permission === true) {
            $permission_arr['add'] = $this->checkMenuPermission('create_new_prd_badge');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_new_prd_badge');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_new_prd_badge');

            $data_arr = array();
            $data_cus_arr = array();
            $badge_dtl = Badge::getAllBadge();

            return view('admin.badge.listBadge', ['badge_dtl'=>$badge_dtl, 'permission_arr'=>$permission_arr]);
        }
    }   
    
    public function create(){

        $permission = $this->checkUrlPermission('add_static_page');
        if($permission === true) {

            return view('admin.badge.createBadge');
        }
    }
    
    function store(Request $request){ 
        $input = $request->all();
        $input['bg_name'] = $request->badge_name[session('admin_default_lang')];
        
        $validate = $this->validateBadge($input);

        if ($validate->passes()) {

            $cms = new Badge;

            $cms->status = !empty($request->status)?'1':'0';
            if(!empty($request->icon)) {
                
                $uploadDetails['path'] = Config::get('constants.standard_badge_path');
                $uploadDetails['file'] =  $request->icon;

                $file_name = $this->uploadFileCustom($uploadDetails,'1');

                $cms->icon = $file_name;
            }

            $cms->title = cleanValue($request->title);
            $cms->size = cleanValue($request->size);
            $cms->grade = cleanValue($request->grade);
            $cms->created_by = Auth::guard('admin_user')->user()->id;
            $cms->save();
            $badge_id = $cms->id;

            foreach ($request->badge_name as $key => $value) {
                if(empty($value)) {
                    $value = $input['bg_name'];
                }

                $data[] = ["badge_id" => $badge_id, "lang_id" => $key, "badge_name" => $value];
            }
            DB::table($this->tblBadgeDesc)->insert($data);

            /***update unit data into mongo db*****/
            $data = Badge::badgeData($badge_id);
            $store = MongoBadge::updateData($data);

            /*update activity log start*/
            $action_type = "created";             
            $logdetails = "Admin has ".$action_type." ".$input['bg_name']." $this->module_name";
            $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/
            
            if($request->submit_type == 'submit_continue') {

                return $response =['status'=>'success', 'url' =>action('Admin\Badge\BadgeController@edit',$cms->id)];
            }
            else {
                
                return $response =['status'=>'success', 'url' =>action('Admin\Badge\BadgeController@index')];
            }
        } 
        else {
            
            $errors =  $validate->errors(); 
            return array('status'=>'fail','message'=>$errors,'validation'=>true);
        }                 
    }

    public function show($id) {
    }
    
    function edit($id) {
        $permission = $this->checkUrlPermission('edit_static_page');
        if($permission === true) {

            $badge_dtls = Badge::getBadgebyId($id);
            
            return view('admin.badge.editBadge', ['badge_dtls'=>$badge_dtls, 'tblBadgeDesc'=>$this->tblBadgeDesc]);
        }
    }
    
    function update(Request $request, $badge_id){
  
        $input = $request->all();
        $input['bg_name'] = $request->badge_name[session('admin_default_lang')];
        
        $badge_dtls = Badge::getBadgebyId($badge_id);

        if(empty($badge_dtls)){
            return array('status'=>'fail','message'=>'invalid id');
        }

        $validate = $this->validateBadge($input, $badge_id);      

        if ($validate->passes()) {

            $badge_dtls->status = !empty($request->status)?'1':'0';
            if(!empty($request->icon)) {
                
                $uploadDetails['path'] = Config::get('constants.standard_badge_path');
                $uploadDetails['file'] =  $request->icon;

                $file_name = $this->uploadFileCustom($uploadDetails,'1');

                if($badge_dtls->icon){
                    $this->fileDelete(Config::get('constants.standard_badge_path').'/'.$badge_dtls->icon);
                }

                $badge_dtls->icon = $file_name;
            }

            $badge_dtls->title = cleanValue($request->title);
            $badge_dtls->size = cleanValue($request->size);
            $badge_dtls->grade = cleanValue($request->grade);
            $badge_dtls->updated_by = Auth::guard('admin_user')->user()->id;
            $badge_dtls->save();

            foreach ($request->badge_name as $key => $value) {

                if(empty($value)) {
                    $value = $input['bg_name'];
                }                
                        
                $data_arr = ["badge_id" => $badge_id, "lang_id" => $key, "badge_name" => $value];
                 BadgeDesc::updateOrCreate(['badge_id' => $badge_id, 'lang_id' => $key], $data_arr);
            }

            /***update badge data into mongo db*****/
            $data = Badge::badgeData($badge_id);
            $store = MongoBadge::updateData($data);

            /*update activity log start*/
            $action_type = "updated"; 
            $logdetails = "Admin has ".$action_type." ".$input['bg_name']." $this->module_name";
            $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

            if($request->submit_type == 'submit_continue') {

                return $response =['status'=>'update', 'url' =>action('Admin\Badge\BadgeController@edit',$badge_id)];
            }
            else {
                
                return $response =['status'=>'success','url' =>action('Admin\Badge\BadgeController@index')];
            }
        }
        else {

            $errors =  $validate->errors(); 
            return array('status'=>'fail','message'=>$errors,'validation'=>true);
        }            
    }    
    
    function destroy($id){

        $permission = $this->checkUrlPermission('delete_new_prd_badge');
        if($permission === true) {
            $static_cms = Badge::getBadgebyId($id);
            if(!empty($static_cms)){
                try {
                    $namedesc = $static_cms->badgedesc;
                    $logname = !empty($namedesc)?$namedesc->badge_name:$id;

                    if($static_cms->icon){
                        $this->fileDelete(Config::get('constants.standard_badge_path').'/'.$static_cms->icon);
                    }

                    Badge::where('id', $id)->delete();
                    /***delete from mongo***/
                    MongoBadge::deleteData($id);

                    /*update activity log start*/
                    $action_type = "deleted";            
                    $logdetails = "Admin has ".$action_type." $logname ";
                    $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

                    $this->updateLogActivity($logdata);

                    return redirect()->action('Admin\Badge\BadgeController@index')->with('succMsg', Lang::get('admin_common.records_deleted_successfully'));

                }catch (Exception $e) {
                    return redirect()->action('Admin\Badge\BadgeController@index')->with('errorMsg', $e->getMessage());
                }
                
            }else{
                return redirect()->action('Admin\Badge\BadgeController@index')->with('errorMsg', Lang::get('admin_common.error'));
            }
            
        }
    }

    function changeStatus($id) {

        $static_cms = Badge::getBadgebyId($id);

        if($static_cms->status == '1') {
            $status = '0';
            $status_msg = 'Inactive';
        }
        else {
            $status = '1';
            $status_msg = 'Active';            
        }

        $static_cms->status = $status;
        $static_cms->updated_at = currentDateTime();
        $static_cms->updated_by = Auth::guard('admin_user')->user()->id;

        $static_cms->save();

        $namedesc = $static_cms->badgedesc;
        $logname = !empty($namedesc)?$namedesc->badge_name:$id;

        /***update status data into mongo db*****/
        $store = MongoBadge::updateStatus($id,$status);

        /*update activity log start*/
        $action_type = "updated";            
        $logdetails = "Admin has ".$action_type." $logname status to $status_msg ";
        $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/

        return $status_msg;
    }

    private function validateBadge($input, $badge_id='') {

        if(!empty($badge_id) && !empty($input['bg_name'])) {
            $rules['bg_name'] = uniqueIgnoreRule($this->tblBadgeDesc,'badge_name',$badge_id,'badge_id');
            $res = Badge::where('id','!=',$badge_id)->where(['size'=>$input['size'],'grade'=>$input['grade']])->count();
            if($res){
                $input['size'] = '';
            }
            
        }else{
            $rules['bg_name'] = uniqueRule($this->tblBadgeDesc,'badge_name',$input['bg_name']);
            $res = Badge::where(['size'=>$input['size'],'grade'=>$input['grade']])->count();
            if($res){
                $input['size'] = '';
            }
        }  

        $rules['title'] = titleRule();
        $rules['size'] = reqRule();
        $rules['grade'] = reqRule();   
        
        $error_msg['bg_name.required'] = Lang::get('admin_product.badge_name_is_required');
        $error_msg['bg_name.unique'] = Lang::get('admin_product.badge_name_already_exist');
        $error_msg['title.required'] = Lang::get('admin_common.please_enter_title');
        $error_msg['size.required'] = Lang::get('admin_product.please_select_unique_combination_of_size_and_grade');
        $validate = Validator::make($input, $rules, $error_msg);
        
        return $validate; 
    }
  
}
