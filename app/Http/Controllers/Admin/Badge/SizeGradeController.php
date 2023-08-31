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
use App\SizeGrade;
use App\SizeGradeDesc;
use App\MongoSizeGrade;
use Auth;
use Lang;
use DB;


class SizeGradeController extends MarketPlace
{ 
    private $tblSizeGrade;
    private $tblSizeGradeDesc;
    private $module_name = "SizeGrade";
    public function __construct()
    {
        $this->middleware('admin.user');
        $this->tblSizeGrade = with(new SizeGrade)->getTable();
        $this->tblSizeGradeDesc = with(new SizeGradeDesc)->getTable();
    }

    public function index(){

        $permission = $this->checkUrlPermission('list_sizegrade');
        if($permission === true) {
            $permission_arr['add'] = $this->checkMenuPermission('add_sizegrade_management');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_sizegrade_management');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_sizegrade_management');

            $data_arr = array();
            $data_cus_arr = array();
            $SizeGrade_dtl = SizeGrade::getAllSizeGrade();
            //dd($SizeGrade_dtl);
            return view('admin.sizegrade.listSizeGrade', ['SizeGrade_dtl'=>$SizeGrade_dtl, 'permission_arr'=>$permission_arr]);
        }
    }   
    
    public function create(){

        $permission = $this->checkUrlPermission('add_sizegrade_management');
        if($permission === true) {

            return view('admin.badge.createSizeGrade');
        }
    }
    
    function store(Request $request){ 
        $input = $request->all();
        $input['sg_name'] = $request->name[session('admin_default_lang')];
        $validate = $this->validateSizeGrade($input);

        if ($validate->passes()) {

            $cms = new SizeGrade;
            $cms->status = !empty($request->status)?'1':'0';
            $cms->slug = createUrl($request->slug);
            $cms->type = cleanValue($request->type);
            $cms->save();
            $SizeGrade_id = $cms->id;

            foreach ($request->name as $key => $value) {
                if(empty($value)) {
                    $value = $input['sg_name'];
                }

                $data[] = ["size_grade_id" => $SizeGrade_id, "lang_id" => $key, "name" => $value];
            }
            DB::table($this->tblSizeGradeDesc)->insert($data);

            /***update SizeGrade data into mongo db*****/
            $data = SizeGrade::SizeGradeData($SizeGrade_id);
            $store = MongoSizeGrade::updateData($data);
            cache_deleteKey('size_grade');
            /*update activity log start*/
            $action_type = "created";             
            $logdetails = "Admin has ".$action_type." ".$input['un_name']." $this->module_name";
            $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/
            
            if($request->submit_type == 'submit_continue') {

                return $response =['status'=>'success', 'url' =>action('Admin\Badge\SizeGradeController@edit',$cms->id)];
            }
            else {
                
                return $response =['status'=>'success', 'url' =>action('Admin\Badge\SizeGradeController@index')];
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
        $permission = $this->checkUrlPermission('edit_sizegrade_management');
        if($permission === true) {

            $SizeGrade_dtls = SizeGrade::find($id);
            
            return view('admin.badge.editSizeGrade', ['sizegrade_dtls'=>$SizeGrade_dtls, 'tblSizeGradeDesc'=>$this->tblSizeGradeDesc]);
        }
    }
    
    function update(Request $request, $SizeGrade_id){
  
        $input = $request->all();
        $input['sg_name'] = $request->name[session('admin_default_lang')];

        $badge_dtls = SizeGrade::getSizeGradebyId($SizeGrade_id);

        if(empty($badge_dtls)){
            return array('status'=>'fail','message'=>'invalid id');
        }

        $validate = $this->validateSizeGrade($input, $SizeGrade_id);      

        if ($validate->passes()) {

            $badge_dtls->status = !empty($request->status)?'1':'0';

            $badge_dtls->slug = createUrl($request->title);
            $badge_dtls->type = cleanValue($request->type);
            $badge_dtls->save();

            foreach ($request->name as $key => $value) {

                if(empty($value)) {
                    $value = $input['sg_name'];
                }                
                        
                $data_arr = ["sige_grade_id" => $SizeGrade_id, "lang_id" => $key, "name" => $value];
                 SizeGradeDesc::updateOrCreate(['sige_grade_id' => $SizeGrade_id, 'lang_id' => $key], $data_arr);
            }

            /***update SizeGrade data into mongo db*****/
            $data = SizeGrade::SizeGradeData($SizeGrade_id);
            $store = MongoSizeGrade::updateData($data);
            cache_deleteKey('size_grade');
            /*update activity log start*/
            $action_type = "updated"; 
            $logdetails = "Admin has ".$action_type." ".$input['sg_name']." $this->module_name";
            $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

            if($request->submit_type == 'submit_continue') {

                return $response =['status'=>'update', 'url' =>action('Admin\Badge\SizeGradeController@edit',$SizeGrade_id)];
            }
            else {
                
                return $response =['status'=>'success','url' =>action('Admin\Badge\SizeGradeController@index')];
            }
        }
        else {

            $errors =  $validate->errors(); 
            return array('status'=>'fail','message'=>$errors,'validation'=>true);
        }            
    }    
    
    function destroy($id){
        
        $permission = $this->checkUrlPermission('delete_sizegrade_management');
        if($permission === true) {
            $static_cms = SizeGrade::find($id);
            if(!empty($static_cms)){
                try {
                    $namedesc = $static_cms->sizegradedesc;
                    $logname = !empty($namedesc)?$namedesc->name:$id;

                    SizeGrade::where('id', $id)->delete();

                    /***delete from mongo***/
                    MongoSizeGrade::deleteData($id);
                    cache_deleteKey('size_grade');
                    /*update activity log start*/
                    $action_type = "deleted";            
                    $logdetails = "Admin has ".$action_type." $logname ";
                    $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

                    $this->updateLogActivity($logdata);

                    return redirect()->action('Admin\Badge\SizeGradeController@index')->with('succMsg', Lang::get('admin_common.records_deleted_successfully'));

                }catch (Exception $e) {
                    return redirect()->action('Admin\Badge\SizeGradeController@index')->with('errorMsg', $e->getMessage());
                }
                
            }else{
                return redirect()->action('Admin\Badge\SizeGradeController@index')->with('errorMsg', Lang::get('admin_common.error'));
            }
            
        }
    }

    function changeStatus($id) {

        $static_cms = SizeGrade::find($id);

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

        $namedesc = $static_cms->sizegradedesc;
        $logname = !empty($namedesc)?$namedesc->name:$id;

        /***update status data into mongo db*****/
        $store = MongoSizeGrade::updateStatus($id,$status);

        /*update activity log start*/
        $action_type = "updated";            
        $logdetails = "Admin has ".$action_type." $logname status to $status_msg ";
        $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/

        return $status_msg;
    }

    private function validateSizeGrade($input, $SizeGrade_id='') {

        if(!empty($SizeGrade_id) && !empty($input['sg_name'])) {
            $rules['sg_name'] = uniqueIgnoreRule($this->tblSizeGradeDesc,'name',$SizeGrade_id,'size_grade_id');
            
        }else{
            $rules['sg_name'] = uniqueRule($this->tblSizeGradeDesc,'name',$input['sg_name']);
        }  

        $rules['slug'] = reqRule();   
        
        $error_msg['sg_name.required'] = Lang::get('admin_product.name_is_required');
        $error_msg['un_name.unique'] = Lang::get('admin_product.name_already_exist');
        $error_msg['slug.required'] = Lang::get('admin_common.please_enter_slug');
        $validate = Validator::make($input, $rules, $error_msg);
        
        return $validate; 
    }
  
}
