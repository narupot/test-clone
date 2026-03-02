<?php

namespace App\Http\Controllers\Admin\Table;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Hash;

use App\AdminUser;
use App\Role;
use App\AdminLogDetail;
use Auth;

class TableConfigController extends MarketPlace {

    private $tblTable;
    public function __construct() {   
        $this->middleware('admin.user');     
        $this->tblTable = with(new \App\TableConfiguration)->getTable();
    }    
    
    public function index() {
         
        $permission = $this->checkUrlPermission('table_config');
        if($permission === true) {

            $permission_arr['edit'] = $this->checkMenuPermission('edit_table_config');

            $lists = \App\TableConfiguration::select('id','table_name','section','tot_column','created_at','updated_at')->get();

            return view('admin.table.table-list', ['table_lists'=>$lists, 'permission_arr'=>$permission_arr]);
        }        
    }

    public function columnConfig() {
        $permission = $this->checkUrlPermission('column_config');
        if($permission === true) {

            $permission_arr['edit'] = $this->checkMenuPermission('edit_column_config');
            $lists = \App\TableColumnConfiguration::all();

            return view('admin.table.table-column-list', ['table_lists'=>$lists, 'permission_arr'=>$permission_arr]);
        }        
    }
    
    public function edit($table_id)
    {
        $permission = $this->checkUrlPermission('edit_table_config');  
            
        if($permission === true) {

            $table_detail = \App\TableConfiguration::where('id', '=', $table_id)->first();

            return view('admin.table.tableListEdit', ['table_detail'=>$table_detail]);
        }
    } 
    
    function store(Request $request)
    {            
    } 
    

    function editProfile() {
    }    
    
    function update(Request $request, $table_id)
    {
		$input = $request->all();
        $validate = $this->validateTableConfig($input,$table_id);
        if ($validate->passes()) {
            $table = \App\TableConfiguration::find($table_id);
            $table->table_name = cleanValue($request->table_name);
            $table->note = cleanValue($request->note);
            $table->resizable = $request->resizable;
            $table->row_rearrange = $request->row_rearrange;
            $table->column_rearrange = $request->column_rearrange;
            $table->bulk_action = $request->bulk_action;
            $table->filter = $request->filter;
            $table->chk_action = $request->chk_action;
            $table->dynamic_column = $request->dynamic_column;
            $table->save();

            /*update activity log start*/
            $action_type = "updated"; 
            $module_name = "table config";            
            $logdetails = "Admin has updated ".$request->table_name." ".$module_name;
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/
            return redirect()->action('Admin\Table\TableConfigController@index')->with('succMsg', 'Records Updated Successfully!');
        }
        else {

            return redirect()->action('Admin\Table\TableConfigController@edit', $table_id)->withErrors($validate)->withInput();
        } 
    }   


    function updateColumn(Request $request)
    {

        // echo '<pre>';print_r($request->all());die;
        $table = \App\TableColumnConfiguration::find($request->id);
        $table->width = cleanValue($request->width);
        $table->align = cleanValue($request->align);
        $table->filter = $request->filter;
        $table->sort = $request->sort;
        $table->save();

        /*update activity log start*/
        $action_type = "updated"; 
        $module_name = "table column config";            
        $logdetails = "Admin has updated ".$table->display_name." ".$module_name;
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/

        return json_encode($request->all());

    } 
    
    private function validateTableConfig($input, $table_id='') {

        $rules['table_name'] = 'Required|unique:'.$this->tblTable.',table_name';

        if(!empty($table_id) && !empty($input['table_name'])) {
            $rules['table_name'] = Rule::unique($this->tblTable)->ignore($table_id);
        }

        $rules['note'] = 'Required|Min:5';     

        $error_msg['table_name.required'] = 'Enter Table name';
        $error_msg['table_name.unique'] = 'This table name has Already Been Taken';
        $error_msg['note.required'] = 'Enter note';      

        $validate = Validator::make($input, $rules, $error_msg);
        
        return $validate; 
    }

    // function delete($user_id){
        
    //     $permission = $this->checkUrlPermission('delete_user');
    //     if($permission === true) {

    //         $user = AdminUser::find($user_id);
    //         $user->delete();

    //         return redirect()->action('AdminController@index')->with('succMsg', 'User Delete Successfully!');
    //     }
    // }

    // function logDetail() {
        
    //     $permission = $this->checkUrlPermission('log_detail');
    //     if($permission === true) {

    //         $permission_arr['delete'] = $this->checkMenuPermission('delete_log');

    //         $log_lists = AdminLogDetail::all(); 
    //         return view('admin.admin-log', ['log_lists'=>$log_lists, 'permission_arr'=>$permission_arr]); 
    //     }       
    // }
    
    // function deleteLog($log_id){
        
    //     $permission = $this->checkUrlPermission('delete_log');
    //     if($permission === true) {

    //         $user = AdminLogDetail::find($log_id);
    //         $user->delete();

    //         return redirect()->action('AdminController@logDetail')->with('succMsg', 'Log Deleted Successfully!');
    //     }
    // }
    
    // function clearLog(){
        
    //     $permission = $this->checkUrlPermission('delete_log');
    //     if($permission === true) {

    //         AdminLogDetail::truncate();

    //         return redirect()->action('AdminController@logDetail')->with('succMsg', 'Logs Updated Successfully!');
    //     }
    // }

    // function validateUser($input, $user_id=null) {

    //     $rules['role'] = 'Required';
    //     $rules['first_name'] = 'Required|Min:3';
    //     $rules['last_name'] = 'Required|Min:3';

    //     $rules['email'] = 'Required|email|unique:'.$this->tblAdminUser.',email';
    //     if(!empty($user_id) && !empty($input['email'])) {
    //         $rules['email'] = Rule::unique($this->tblAdminUser)->ignore($user_id);
    //     }

    //     $rules['password'] = 'Required|Min:6';
    //     $rules['password_confirm'] = 'Required|Min:6';        

    //     $error_msg['role.required'] = 'Please select user role';
    //     $error_msg['first_name.required'] = 'First name is required';
    //     $error_msg['last_name.required'] = 'Last name is required';
    //     $error_msg['email.required'] = 'Email is required';
    //     $error_msg['password.required'] = 'Please enter correct password';
    //     $error_msg['password_confirm.required'] = 'Password and confirm password should be same';       

    //     $validate = Validator::make($input, $rules, $error_msg);
    //     //echo '<pre>';print_r($validate->errors());die;
    //     return $validate; 
    // }    
}
