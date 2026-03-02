<?php

namespace App\Http\Controllers\Admin\Role;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use App\Http\Controllers\MarketPlace;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Role;
use App\Menu;
use App\MenusPermission;
use App\RoleDepartment;
use Lang;
use Auth;

class GroupController extends MarketPlace
{
    private $tblRole;
    private $tblRoleDepartment;

    public function __construct()
    {
        $this->middleware('admin.user');
        $this->tblRole = with(new Role)->getTable();
        $this->tblRoleDepartment = with(new RoleDepartment)->getTable();
    }
    
    public function index()
    {
        $permission = $this->checkUrlPermission('list_roles');
        if($permission === true) { 

            $permission_arr['add'] = $this->checkMenuPermission('add_new_role');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_role');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_role');               
        
            $admin_lists = Role::getAdminRole();
            return view('admin.role.roleList', ['admin_lists'=>$admin_lists, 'permission_arr'=>$permission_arr]);
        }
    }
    
    public function create()
    {
        $permission = $this->checkUrlPermission('add_new_role');        
        if($permission === true) {                
            return view('admin.role.roleCreate');
        }
    }
    
    function store(Request $request)
    {
        //echo '<pre>';print_r($request->all());die;
        

        $def_department_name = $request->department_name[session('admin_default_lang')];

        $input = $request->all();
        $input['depart_name'] = $def_department_name;		
        $input['slug'] = Str::slug($request->name);
        $validate = $this->validateRole($input);
        if ($validate->passes()) {

            $role = new Role;
            $role->name = $request->name;
            $role->slug = Str::slug($request->name);
            $role->created_by = Auth::guard('admin_user')->user()->id;
            $role->save();

            $department_arr = array();
            foreach ($request->department_name as $key=>$value){
                if(empty($value)) {
                    $value = $def_department_name;
                }
                $department_arr[] = ['role_id'=>$role->id, 'lang_id'=>$key, 'department_name'=>$value];
            }
            RoleDepartment::insert($department_arr);             

            foreach($request->menu_check as $key=>$menus){
                $roles_arr[] = ['role_id'=> $role->id, 'menu_id' => $menus];            
            }
            MenusPermission::insert($roles_arr);

            /****after save update limit******/
            //updateLimitUsage('admin-role','add');

            /*update activity log start*/
            $action_type = "created"; 
            $module_name = "role";            
            $logdetails = "Admin has created ".$input['depart_name']." role";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

            if($request->submit_type == 'save_and_continue') {
                return redirect()->action('Admin\Role\GroupController@edit', $role->id)->with('succMsg', Lang::get('admin.role_added_successfully'));
            }
            else {
                return redirect()->action('Admin\Role\GroupController@index')->with('succMsg', Lang::get('admin.role_added_successfully'));
            }
        }
        else {
            return redirect()->action('Admin\Role\GroupController@create')->withErrors($validate)->withInput();
        }                           
    }
    
    function edit($group_id) {

        $permission = $this->checkUrlPermission('edit_role');
        if($permission === true) {        

            $role = Role::where('id', '=', $group_id)->first();
            //dd($role);
            return view('admin.role.roleEdit', ['role'=>$role, 'tblRoleDepartment'=>$this->tblRoleDepartment]);
        }
    }
    
    function update(Request $request)
    {
        //echo '<pre>';print_r($request->all());die;
        
        if($request->role_id > 0) {

            $def_department_name = $request->department_name[session('admin_default_lang')];

            $input = $request->all();
            $input['depart_name'] = $def_department_name;
            $input['slug'] = Str::slug($request->name);
            $validate = $this->validateRole($input, $request->role_id);

            if ($validate->passes()) {            
               
                $role = Role::find($request->role_id);
                $role->name = $request->name;
                $role->slug = Str::slug($request->name);
                $role->updated_at = date('Y-m-d H:i:s');
                $role->updated_by = Auth::guard('admin_user')->user()->id;
                $role->save();

                $department_arr = array();
                foreach ($request->department_name as $key=>$value){
                    if(empty($value)) {
                        $value = $def_department_name;
                    }
                    $department_arr[] = ['role_id'=>$role->id, 'lang_id'=>$key, 'department_name'=>$value];
                }
                RoleDepartment::where('role_id', $request->role_id)->delete();
                RoleDepartment::insert($department_arr);             

                foreach($request->menu_check as $key=>$menus){
                    $roles_arr[] = ['role_id'=> $role->id, 'menu_id' => $menus];            
                }
                MenusPermission::where('role_id', $request->role_id)->delete();
                MenusPermission::insert($roles_arr);

                /*update activity log start*/
                $action_type = "updated"; 
                $module_name = "role";            
                $logdetails = "Admin has updated ".$input['depart_name']." role";
                $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

                $this->updateLogActivity($logdata);
                /*update activity log End*/

                return redirect()->action('Admin\Role\GroupController@index')->with('succMsg', Lang::get('admin.role_updated_successfully'));
            }
            else {
              //dd($validate->errors()); 
              return redirect()->action('Admin\Role\GroupController@edit', $request->role_id)->withErrors($validate)->withInput();
            }                       
        }
    }    
    
    function delete($id){
        
        $permission = $this->checkUrlPermission('delete_role');
        if($permission === true) {
            
            $group = Role::find($id);
            
            try{            
                $group->delete();
                //updateLimitUsage('admin-role','delete');

                /*update activity log start*/
                $action_type = "deleted"; 
                $module_name = "role";            
                $logdetails = "Admin has deleted role ".$group->name;
                $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);
                $this->updateLogActivity($logdata);
                /*update activity log End*/ 

                return redirect()->action('Admin\Role\GroupController@index')->with('succMsg', Lang::get('admin.role_deleted_successfully'));           
            }
            catch (QueryException $ex) {            
                return redirect()->action('Admin\Role\GroupController@index')->with('errorMsg', Lang::get('admin.this_role_can_not_be_deleted_because_it_is_assigned_to_some_user'));
            }
        }        
    }

    function validateRole($input, $role_id=null) {

        $rules['name'] = 'Required|Min:3';
        $rules['slug'] = 'unique:'.$this->tblRole.',slug';
        if(!empty($role_id) && !empty($input['slug'])) {
            $rules['slug'] = Rule::unique($this->tblRole)->ignore($role_id);
        }
        $rules['depart_name'] = nameRule();
        $rules['menu_check'] = arrayRule();        

        $error_msg['name.required'] = Lang::get('admin.please_enter_role_name');
        $error_msg['slug.unique'] = Lang::get('admin.role_already_exist');
        $error_msg['depart_name.required'] = Lang::get('admin.please_enter_department_name');
        $error_msg['menu_check.required'] = Lang::get('admin.please_select_role_menu');

        $validate = Validator::make($input, $rules, $error_msg);
        //echo '<pre>';print_r($validate->errors());die;
        return $validate; 
    }    
}
