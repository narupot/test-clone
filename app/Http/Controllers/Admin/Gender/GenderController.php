<?php namespace App\Http\Controllers\Admin\Gender;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;

use App\Language;
use App\Gender;
use App\GenderDesc;
use Auth;
use Lang;
use Config;
use Session;

class GenderController extends MarketPlace
{ 
    public function __construct()
    {
        $this->middleware('admin.user');
        $this->tblGender = with(new Gender)->getTable();
        $this->tblGenderDesc = with(new GenderDesc)->getTable();        
    }

    /*
    * These function are belongs for Gender management | Start | By Satish Anand
    */
    
    public function index(){
        $permission = $this->checkUrlPermission('gender_list');
        if($permission === true) {
            $permission_arr['add'] = $this->checkMenuPermission('add_gender');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_gender');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_gender');
            $data_arr = array();
            $gender_dtl = Gender::getGender();            

            if(count($gender_dtl) > 0) {
                foreach ($gender_dtl as $key => $value) {
                    $array_temp['id'] = $value->id;                    
                    $status = 'Inactive';
                    if($value->status == '1') {
                        $status = 'Active';
                    }
                    $array_temp['status'] = $status;
                    if(isset($value->genderDesc->gender_name)){
                        $array_temp['gender_name'] = $value->genderDesc->gender_name;
                    }else{
                        $array_temp['gender_name'] ="";
                    }                                  
                    $array_temp['created_at'] = getDateFormat($value->created_at, '1');
                    $array_temp['updated_at'] = getDateFormat($value->updated_at, '1');

                    $data_arr[] = $array_temp;
                }
            }

            return view('admin.gender.genderList', ['genderlist'=>$data_arr, 'permission_arr'=>$permission_arr]);
        }
    }
   
    public function create(Request $request){

    	$permission = $this->checkUrlPermission('add_gender');
        if($permission === true) {

            $lang_lists = Language::getLangugeDetails();

            return view('admin.gender.genderAdd',['lang_lists'=>$lang_lists]);

        }
    }
    
    function store(Request $request){ 

        $input = $request->all();  
        $def_lang_id = session('admin_default_lang');     
        $input['gender_name'] = $request->gender_name[$def_lang_id];        

        $validate = $this->validateGender($input);

        if ($validate->passes()) {

            $gender = new Gender;
            $gender->status = $request->status;            
            $gender->created_by = Auth::guard('admin_user')->user()->id;            

            if($gender->save()){
                $created_gender_id = $gender->id;
            }else{
                $created_gender_id = '';
            }            

            $data_arr = $this->filterPageData($request);

            GenderDesc::insertGenderDesc($data_arr, $gender->id);
            
            /*update activity log Start*/                        
            $action_type = "created";
            $module_name = "gender";            
            $logdetails = "Admin has created ".$input['gender_name']." gender";
            $old_data = "";
            $new_data = "";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );
            $this->updateLogActivity($logdata);
            /*update activity log End*/

            return redirect()->action('Admin\Gender\GenderController@index')->with('succMsg', Lang::get('common.records_added_successfully'));
            
        } 
        else {

            return redirect(action('Admin\Gender\GenderController@create'))->withErrors($validate)->withInput($request->all());
        }       
    }    

    public function filterPageData($request) {

        $def_lang = session('admin_default_lang');        
        $def_name = $request->gender_name[$def_lang];        

        foreach ($request->gender_name as $key=>$value){
            
            $gender_name = $request->gender_name[$key];
            
            if(empty($gender_name)) {
                $gender_name = $def_name;
            }

            $data_arr[$key] = array(

                'gender_name'=>$gender_name                
            );
        } 

        return $data_arr;       
    }
    
    function edit($id){

        Session::put('edit_gender_id',$id);
        $permission = $this->checkUrlPermission('edit_gender');
        if($permission === true) {

            $gender_dtls = Gender::getGenderbyId($id);  
            
            $page='edit_gender';
            return view('admin.gender.genderEdit', [
                'gender_dtls'=>$gender_dtls, 
                'tblGenderDesc'=>$this->tblGenderDesc,                
                'page'=>$page
            ]);
        }
    }    


    function update(Request $request, $gender_id ){
        
        $input = $request->all();       
        $def_lang_id = session('admin_default_lang');     
        $input['gender_name'] = $request->gender_name[$def_lang_id];                

        $validate = $this->validateGender($input, $gender_id);

        if ($validate->passes()) {
                        
            $gender = Gender::find($gender_id); 

            $update_data = ['status'=>$request->status, 'updated_by'=>Auth::guard('admin_user')->user()->id];            
            
            $data_arr = $this->filterPageData($request);

            Gender::where(['id'=>$gender_id])->update($update_data);

            GenderDesc::updategenderDesc($data_arr, $gender_id);              

            /*update activity log start*/
            $action_type = "updated"; 
            $module_name = "gender";            
            $logdetails = "Admin has updated ".$input['gender_name']." gender";
            $old_data = "";
            $new_data = "";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );
            $this->updateLogActivity($logdata);
            /*update activity log End*/

            return redirect()->action('Admin\Gender\GenderController@index')->with('succMsg', Lang::get('common.records_updated_successfully'));

        }
        else {

            return redirect(action('Admin\Gender\GenderController@edit',$gender_id))->withErrors($validate)->withInput($request->all());
            
        }      
                 
    }     
    
    function destroy($id){

        Gender::where('id', $id)->delete();
        GenderDesc::where('gender_id', $id)->delete();        

        /*update activity log start*/
        $action_type = "deleted"; 
        $module_name = "gender";            
        $logdetails = "Admin has deleted gender id ".$id;
        $old_data = "";
        $new_data = "";
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );
        $this->updateLogActivity($logdata);
        /*update activity log End*/ 

        return redirect()->action('Admin\Gender\GenderController@index')->with('succMsg',Lang::get('common.record_deleted_successfully'));
    }
    

    function changeStatus($id) {
        
        $gender = Gender::getGenderbyId($id);

        if($gender->status == '1') {
            $status = '0';
            $status_msg = 'Inactive';
        }
        else {
            $status = '1';
            $status_msg = 'Active';            
        }

        $gender->status = $status;
        $gender->updated_at = date('Y-m-d H:i:s');
        $gender->updated_by = Auth::guard('admin_user')->user()->id;
        $gender->save();

        return $status_msg;
    }

    private function validateGender($input, $gender_id='') {   
     
        $rules['status'] = 'Required';
        $rules['gender_name'] = 'Required';
        $rules['gender_name'] = 'unique:'.$this->tblGenderDesc.',gender_name';    

        $error_msg['status.required'] = Lang::get('gender.select_status');
        $error_msg['gender_name.unique'] = Lang::get('gender.this_gender_has_already_been_taken');
        $error_msg['gender_name.required'] = Lang::get('gender.enter_gender_name');                 

        $validate = Validator::make($input, $rules, $error_msg);
        return $validate; 
    } 

    
    
}
