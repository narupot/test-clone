<?php
namespace App\Http\Controllers\Admin\Unit;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Config;
use App\Language;
use App\Unit;
use App\UnitDesc;
use App\MongoUnit;
use Auth;
use Lang;
use DB;


class UnitController extends MarketPlace
{ 
    private $tblUnit;
    private $tblUnitDesc;
    private $module_name = "unit";
    public function __construct()
    {
        $this->middleware('admin.user');
        $this->tblUnit = with(new Unit)->getTable();
        $this->tblUnitDesc = with(new UnitDesc)->getTable();
    }

    public function index(){

        $permission = $this->checkUrlPermission('list_unit');
        if($permission === true) {
            $permission_arr['add'] = $this->checkMenuPermission('add_unit_management');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_unit_management');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_unit_management');

            $data_arr = array();
            $data_cus_arr = array();
            $unit_dtl = Unit::getAllUnit();
            //dd($unit_dtl);
            return view('admin.unit.listUnit', ['unit_dtl'=>$unit_dtl, 'permission_arr'=>$permission_arr]);
        }
    }   
    
    public function create(){

        $permission = $this->checkUrlPermission('add_unit_management');
        if($permission === true) {

            return view('admin.unit.createUnit');
        }
    }
    
    function store(Request $request){ 
        $input = $request->all();
        $input['un_name'] = $request->unit_name[session('admin_default_lang')];
        $validate = $this->validateUnit($input);

        if ($validate->passes()) {

            $cms = new Unit;

            $cms->status = !empty($request->status)?'1':'0';

            $cms->title = cleanValue($request->title);
            $cms->unit_weight = cleanValue($request->unit_weight);
            $cms->created_by = Auth::guard('admin_user')->user()->id;
            $cms->save();
            $unit_id = $cms->id;

            foreach ($request->unit_name as $key => $value) {
                if(empty($value)) {
                    $value = $input['un_name'];
                }

                $data[] = ["unit_id" => $unit_id, "lang_id" => $key, "unit_name" => $value];
            }
            DB::table($this->tblUnitDesc)->insert($data);

            /***update unit data into mongo db*****/
            $data = Unit::unitData($unit_id);
            $store = MongoUnit::updateData($data);
            /*update activity log start*/
            $action_type = "created";             
            $logdetails = "Admin has ".$action_type." ".$input['un_name']." $this->module_name";
            $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/
            
            if($request->submit_type == 'submit_continue') {

                return $response =['status'=>'success', 'url' =>action('Admin\Unit\UnitController@edit',$cms->id)];
            }
            else {
                
                return $response =['status'=>'success', 'url' =>action('Admin\Unit\UnitController@index')];
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

            $unit_dtls = Unit::getUnitbyId($id);
            
            return view('admin.unit.editUnit', ['unit_dtls'=>$unit_dtls, 'tblUnitDesc'=>$this->tblUnitDesc]);
        }
    }
    
    function update(Request $request, $unit_id){
  
        $input = $request->all();
        $input['un_name'] = $request->unit_name[session('admin_default_lang')];

        $badge_dtls = Unit::getUnitbyId($unit_id);

        if(empty($badge_dtls)){
            return array('status'=>'fail','message'=>'invalid id');
        }

        $validate = $this->validateUnit($input, $unit_id);      

        if ($validate->passes()) {

            $badge_dtls->status = !empty($request->status)?'1':'0';

            $badge_dtls->title = cleanValue($request->title);
            $badge_dtls->unit_weight = cleanValue($request->unit_weight);
            $badge_dtls->updated_by = Auth::guard('admin_user')->user()->id;
            $badge_dtls->save();

            foreach ($request->unit_name as $key => $value) {

                if(empty($value)) {
                    $value = $input['un_name'];
                }                
                        
                $data_arr = ["unit_id" => $unit_id, "lang_id" => $key, "unit_name" => $value];
                 UnitDesc::updateOrCreate(['unit_id' => $unit_id, 'lang_id' => $key], $data_arr);
            }

            /***update unit data into mongo db*****/
            $data = Unit::unitData($unit_id);
            $store = MongoUnit::updateData($data);

            /*update activity log start*/
            $action_type = "updated"; 
            $logdetails = "Admin has ".$action_type." ".$input['un_name']." $this->module_name";
            $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

            if($request->submit_type == 'submit_continue') {

                return $response =['status'=>'update', 'url' =>action('Admin\Unit\UnitController@edit',$unit_id)];
            }
            else {
                
                return $response =['status'=>'success','url' =>action('Admin\Unit\UnitController@index')];
            }
        }
        else {

            $errors =  $validate->errors(); 
            return array('status'=>'fail','message'=>$errors,'validation'=>true);
        }            
    }    
    
    function destroy($id){
        
        $permission = $this->checkUrlPermission('delete_unit_management');
        if($permission === true) {
            $static_cms = Unit::getUnitbyId($id);
            if(!empty($static_cms)){
                try {
                    $namedesc = $static_cms->unitdesc;
                    $logname = !empty($namedesc)?$namedesc->unit_name:$id;

                    Unit::where('id', $id)->delete();

                    /***delete from mongo***/
                    MongoUnit::deleteData($id);

                    /*update activity log start*/
                    $action_type = "deleted";            
                    $logdetails = "Admin has ".$action_type." $logname ";
                    $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

                    $this->updateLogActivity($logdata);

                    return redirect()->action('Admin\Unit\UnitController@index')->with('succMsg', Lang::get('admin_common.records_deleted_successfully'));

                }catch (Exception $e) {
                    return redirect()->action('Admin\Unit\UnitController@index')->with('errorMsg', $e->getMessage());
                }
                
            }else{
                return redirect()->action('Admin\Unit\UnitController@index')->with('errorMsg', Lang::get('admin_common.error'));
            }
            
        }
    }

    function changeStatus($id) {

        $static_cms = Unit::getUnitbyId($id);

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

        $namedesc = $static_cms->unitdesc;
        $logname = !empty($namedesc)?$namedesc->unit_name:$id;

        /***update status data into mongo db*****/
        $store = MongoUnit::updateStatus($id,$status);

        /*update activity log start*/
        $action_type = "updated";            
        $logdetails = "Admin has ".$action_type." $logname status to $status_msg ";
        $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/

        return $status_msg;
    }

    private function validateUnit($input, $unit_id='') {

        if(!empty($unit_id) && !empty($input['un_name'])) {
            $rules['un_name'] = uniqueIgnoreRule($this->tblUnitDesc,'unit_name',$unit_id,'unit_id');
            
        }else{
            $rules['un_name'] = uniqueRule($this->tblUnitDesc,'unit_name',$input['un_name']);
        }  

        $rules['title'] = reqRule();   
        
        $error_msg['un_name.required'] = Lang::get('admin_product.unit_name_is_required');
        $error_msg['un_name.unique'] = Lang::get('admin_product.unit_name_already_exist');
        $error_msg['title.required'] = Lang::get('admin_common.please_enter_title');
        $validate = Validator::make($input, $rules, $error_msg);
        
        return $validate; 
    }
  
}
