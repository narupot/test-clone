<?php

namespace App\Http\Controllers\Admin\User;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Hash;
use Config;
use Auth;
use Lang;

use App\AdminUser;
use App\Role;
use App\AdminProductPermission;
use App\AdminProduct;
use App\AdminCustomerPermission;
use App\AdminCustomer;
use App\AdminOrderPermission;
use App\AdminOrder;
use App\AdminEntity;

class AdminController extends MarketPlace {

    private $tblAdminUser;
    
    public function __construct() {   
        $this->middleware('admin.user'); 

        $this->tblAdminUser = with(new AdminUser)->getTable();      
    }    
    
    public function index() {
        
        $permission = $this->checkUrlPermission('list_users');
        if($permission === true) {
            $permission_arr['add'] = $this->checkMenuPermission('add_new_user');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_user');

            $admin_lists = AdminUser::orderBy('admin_level', 'ASC')->orderBy('id', 'DESC')->get();
            $profile_image = AdminEntity::getImageDetail();
            //dd( $admin_lists,$profile_image);

            
            return view('admin.user.userList', ['admin_lists'=>$admin_lists, 'permission_arr'=>$permission_arr,'profile_image'=>$profile_image]);
        }        
    }
    
    public function create()
    {
        $permission = $this->checkUrlPermission('add_new_user');        
        if($permission === true) {
            $permission_arr['manage_order'] = $this->checkMenuPermission('manage_admin_order_restrict');
            $permission_arr['manage_product'] = $this->checkMenuPermission('manage_admin_product_restrict');
            $permission_arr['manage_customer'] = $this->checkMenuPermission('manage_admin_customer_restrict');

            $role_lists = Role::getRoles();
            return view('admin.user.userAdd', ['role_lists'=>$role_lists, 'permission_arr'=>$permission_arr]);
        }
    } 
    
    function store(Request $request)
    {        
		$input = $request->all();
        $validate = $this->validateUser($input);

        if ($validate->passes()) {

            $key_val = uniqid();

            $user = new AdminUser;
            $user->role_id = $request->role;
            $user->nick_name = $request->nick_name;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->key_val = $key_val;

            
            //$user->contact_no = $request->contact_no;
            //$user->address = $request->address;
            //$user->dob = date('Y-m-d', strtotime($request->dob));
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->created_by = Auth::guard('admin_user')->user()->id;
            $user->status = $request->status;
            $user->save();
            $data = [];
            if(!empty($request->entity) && count($request->entity)){
            
                foreach ($request->entity as $key=> $entity_val)
                {
                    $data[]=array('user_id'=>$user->id,"entity_key"=>$key,"entity_value"=>$entity_val);
                     
                }
            }

            if(isset($request->image) && !empty($request->image)) {
                $data[] = ['user_id'=>$user->id,"entity_key"=>'profile_image',"entity_value"=>$request->image];
            }

            if(count($data)){
                AdminEntity::insert($data);
            }
            /****after save update limit******/
            //updateLimitUsage('user-admin','add');

            //update sync log data for server
            $data_arr = ['email'=>$request->email,'name'=>$request->first_name.' '.$request->last_name,'key_val'=>$key_val];
            updateSyncData('user-admin','add',$data_arr);

            if(isset($request->product_permission_type)) {
                AdminProductPermission::insert(['admin_id'=>$user->id, 'permission_type'=>$request->product_permission_type, 'created_at'=>currentDateTime(), 'created_by'=>Auth::guard('admin_user')->user()->id]);
                if($request->product_permission_type == '4') {
                    // AdminProduct::insert(['admin_id'=>$user->id, 'product_id'=>'']);
                }
            }
            if(isset($request->customer_permission_type)) {
                AdminCustomerPermission::insert(['admin_id'=>$user->id, 'permission_type'=>$request->customer_permission_type, 'created_at'=>currentDateTime(), 'created_by'=>Auth::guard('admin_user')->user()->id]);
                if($request->customer_permission_type == '2') {
                    // AdminCustomer::insert(['admin_id'=>$user->id, 'user_id'=>'']);
                }
            }
            if(isset($request->order_permission_type)) {
                AdminOrderPermission::insert(['admin_id'=>$user->id, 'permission_type'=>$request->order_permission_type, 'created_at'=>currentDateTime(), 'created_by'=>Auth::guard('admin_user')->user()->id]);
                if($request->order_permission_type == '2') {
                    // AdminOrder::insert(['admin_id'=>$user->id, 'order_id'=>'']);
                }
            }

            /*update activity log start*/
            $action_type = "created"; 
            $module_name = "team member";            
            $logdetails = "Admin has created ".$request->first_name.' '.$request->last_name." team member";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/                        

            if($request->submit_type == 'save_and_continue') {
                return redirect()->action('Admin\User\AdminController@edit', $user->id)->with('succMsg', Lang::get('admin.user_added_successfully'));
            }
            else{
                return redirect()->action('Admin\User\AdminController@index')->with('succMsg', Lang::get('admin.user_added_successfully'));
            } 
        }
        else {
            return redirect()->action('Admin\User\AdminController@create')->withErrors($validate)->withInput();
        }               
    }
    
    function edit($admin_id) {

        //dd(Auth::guard('admin_user')->user());
        $permission = $this->checkUrlPermission('list_users');
        $admin_details = AdminUser::getAdminDetail($admin_id);
        $admin_entitydetails = AdminEntity::getAdminEntityDetail($admin_id);
        $entity_arr = [];
        if(count($admin_entitydetails)){
            foreach ($admin_entitydetails as $key => $value) {
                
                $entity_arr[$value->entity_key] = $value->entity_value;
            }
        }

        if(($permission === true && $admin_details->admin_level == 0) || Auth::guard('admin_user')->user()->admin_level == -1) {

            $permission_arr['manage_order'] = $this->checkMenuPermission('manage_admin_order_restrict');
            $permission_arr['manage_product'] = $this->checkMenuPermission('manage_admin_product_restrict');
            $permission_arr['manage_customer'] = $this->checkMenuPermission('manage_admin_customer_restrict');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_user');
            $permission_arr['edit_role'] = $this->checkMenuPermission('edit_role');            

            $role_lists = Role::getRoles();
            
            $admin_details->dob = date('d-m-Y', strtotime($admin_details->dob));

            $admin_permission['product_permission_type'] = '';
            $admin_permission['customer_permission_type'] = '';
            $admin_permission['order_permission_type'] = '';

            $product_permission = AdminProductPermission::getProductPermission($admin_id);
            if(!empty($product_permission)) {
                $admin_permission['product_permission_type'] = $product_permission->permission_type;
            }

            $customer_permission = AdminCustomerPermission::getCustomerPermission($admin_id);
            if(!empty($customer_permission)) {
                $admin_permission['customer_permission_type'] = $customer_permission->permission_type;
            }  

            $order_permission = AdminOrderPermission::getOrderPermission($admin_id);
            if(!empty($order_permission)) {
                $admin_permission['order_permission_type'] = $order_permission->permission_type;
            }
            //dd($admin_permission);

            $admin_activity_logs = \App\Logactivity::where('action_by_email','LIKE',"%".$admin_details->email."%")->get();

            return view('admin.user.userEdit', ['role_lists'=>$role_lists, 'admin_details'=>$admin_details,'entity_arr'=>$entity_arr, 'admin_permission'=>$admin_permission, 'permission_arr'=>$permission_arr, 'page_type'=>'user_account','admin_activity_logs'=>$admin_activity_logs]);
        }
        abort(404);
    }   
    
    function update(Request $request)
    {
        if($request->admin_id > 0) {

            $input = $request->all();
            $validate = $this->validateUser($input, $request->admin_id);
            if ($validate->passes()) {

                $user = AdminUser::find($request->admin_id);
                $user->role_id = $request->role;
                $user->nick_name = $request->nick_name;
                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                /*if(isset($request->image) && !empty($request->image)) {
                    $file_path = Config('constants.user_path').'/'.$user->image;
                    $this->fileDelete($file_path);

                    $user->image = $request->image;
                }*/
                //$entity = $request->entity;
                //$user->dob = date('Y-m-d', strtotime($request->dob));
                $user->email = $request->email;
                $user->status = $request->status;
                $user->updated_by = Auth::guard('admin_user')->user()->id;
                $user->save();
                
                if(!empty($request->entity) && count($request->entity)){
                    
                    foreach ($request->entity as $key=> $entity_val)
                    {
                        //dd("ok");
                        AdminEntity::where(['user_id'=>$user->id,'entity_key'=>$key])->update(['entity_value'=>$entity_val]);
                    }
                    
                }

                if(isset($request->image) && !empty($request->image)) {
                    $file_path = Config('constants.user_path').'/'.$user->image;
                    $this->fileDelete($file_path);

                    AdminEntity::where(['user_id'=>$user->id,'entity_key'=>'profile_image'])->update(['entity_value'=>$request->image]);
                }

                if(isset($request->product_permission_type)) {
                    $product_permission = AdminProductPermission::getProductPermission($request->admin_id);
                    if(!empty($product_permission)) {
                        AdminProductPermission::where('id', $product_permission->id)->update(['permission_type'=>$request->product_permission_type, 'updated_at'=>currentDateTime(), 'updated_by'=>Auth::guard('admin_user')->user()->id]);
                    }
                    else {
                        AdminProductPermission::insert(['admin_id'=>$user->id, 'permission_type'=>$request->product_permission_type, 'created_at'=>currentDateTime(), 'created_by'=>Auth::guard('admin_user')->user()->id]);
                    }

                    if($request->product_permission_type == '4') {
                        // AdminProduct::insert(['admin_id'=>$user->id, 'product_id'=>'']);
                    }
                }
                if(isset($request->customer_permission_type)) {
                    $customer_permission = AdminCustomerPermission::getCustomerPermission($request->admin_id);
                    if(!empty($customer_permission)) {
                        AdminCustomerPermission::where('id', $customer_permission->id)->update(['permission_type'=>$request->customer_permission_type, 'updated_at'=>currentDateTime(), 'updated_by'=>Auth::guard('admin_user')->user()->id]);
                    }
                    else {
                        AdminCustomerPermission::insert(['admin_id'=>$user->id, 'permission_type'=>$request->customer_permission_type, 'created_at'=>currentDateTime(), 'created_by'=>Auth::guard('admin_user')->user()->id]);
                    }

                    if($request->customer_permission_type == '2') {
                        // AdminCustomer::insert(['admin_id'=>$user->id, 'user_id'=>'']);
                    }
                }
                if(isset($request->order_permission_type)) {
                    $order_permission = AdminOrderPermission::getOrderPermission($request->admin_id);
                    if(!empty($order_permission)) {
                        AdminOrderPermission::where('id', $order_permission->id)->update(['permission_type'=>$request->order_permission_type, 'updated_at'=>currentDateTime(), 'updated_by'=>Auth::guard('admin_user')->user()->id]);
                    }
                    else {
                        AdminOrderPermission::insert(['admin_id'=>$user->id, 'permission_type'=>$request->order_permission_type, 'created_at'=>currentDateTime(), 'created_by'=>Auth::guard('admin_user')->user()->id]);
                    }

                    if($request->order_permission_type == '2') {
                        // AdminOrder::insert(['admin_id'=>$user->id, 'order_id'=>'']);
                    }
                }      

                $data_arr = ['email'=>$request->email,'name'=>$request->first_name.' '.$request->last_name,'key_val'=>$user->key_val];
                //update sync log data for server
                updateSyncData('user-admin','edit',$data_arr); 

                /*update activity log start*/
                $action_type = "updated"; 
                $module_name = "team member";            
                $logdetails = "Admin has updated ".$request->first_name.' '.$request->last_name." team member";
                $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

                $this->updateLogActivity($logdata);
                /*update activity log End*/                                

                if(isset($request->page_type) && $request->page_type == 'my_account') {
                    return redirect()->action('Admin\User\AdminController@accountDetail')->with('succMsg', Lang::get('admin.user_updated_successfully'));
                }
                else {
                    return redirect()->action('Admin\User\AdminController@index')->with('succMsg', Lang::get('admin.user_updated_successfully'));
                }
            }
            else {
                //echo '<pre>';print_r($validate->errors());die;
                if(isset($request->page_type) && $request->page_type == 'my_account') {
                    return redirect()->action('Admin\User\AdminController@accountDetail')->withErrors($validate)->withInput();
                }
                else {
                    return redirect()->action('Admin\User\AdminController@edit', $request->admin_id)->withErrors($validate)->withInput();
                }
            }                                        
        }
    }

    function accountDetail() {

        $permission_arr['manage_order'] = false;
        $permission_arr['manage_product'] = false;
        $permission_arr['manage_customer'] = false;
        $permission_arr['edit'] = true;
        $permission_arr['edit_role'] = false;

        $admin_permission['product_permission_type'] = '';
        $admin_permission['customer_permission_type'] = '';
        $admin_permission['order_permission_type'] = '';                    

        $role_lists = Role::getRoles();

        $admin_details = AdminUser::getAdminDetail(Auth::guard('admin_user')->user()->id);
        $admin_details->dob = date('d-m-Y', strtotime($admin_details->dob));

        $admin_entitydetails = AdminEntity::getAdminEntityDetail($admin_details->id);
        //dd($admin_entitydetails);
        $entity_arr = [];
        if(count($admin_entitydetails)){
            foreach ($admin_entitydetails as $key => $value) {
                $entity_arr[$value->entity_key] = $value->entity_value;
            }
        }
        //dd($entity_arr);

        $admin_activity_logs = \App\Logactivity::where('action_by_email','LIKE',"%".$admin_details->email."%")->get();

        return view('admin.user.userEdit', ['role_lists'=>$role_lists, 'admin_details'=>$admin_details, 'admin_permission'=>$admin_permission, 'permission_arr'=>$permission_arr, 'page_type'=>'my_account','admin_activity_logs'=>$admin_activity_logs,'entity_arr'=>$entity_arr]);
    }

    function changePassword(Request $request) {
        //dd($request->all());
        
        $user = AdminUser::select('id', 'password')->where('id', $request->admin_id)->first();

        $validate_password = '';
        if(!empty($user) && Hash::check($request->old_password, $user->password)) {
            $validate_password = 'correct';
        }

        $input = $request->all();
        $input['old_password'] = $validate_password;
        $validate = $this->validatePassword($input);
        if ($validate->passes()) {

            $user->password = bcrypt($request->password);
            $user->save();

            $arr['status'] = 'success';
            $arr['msg'] = $this->getSuccessMessage(Lang::get('admin.success_to_change_password'));

            return json_encode($arr);
        }
        else {
            //echo '<pre>';print_r($validate->errors());die;

            $arr['status'] = 'validate_error';
            $arr['msg'] = json_decode($validate->errors());

            return json_encode($arr);
        }                 
    }    

    function confirmPassword(Request $request) {

        $validate_password = '';
        if(Hash::check($request->confirm_password, Auth::guard('admin_user')->user()->password)) {
            $validate_password = 'correct';
        }

        $input = $request->all();
        $input['confirm_password'] = $validate_password;
        $validate = $this->validateConfirmPassword($input);
        if ($validate->passes()) {

            $arr['status'] = 'success';
            $arr['msg'] = '';
        }
        else {
            //echo '<pre>';print_r($validate->errors());die;

            $arr['status'] = 'validate_error';
            $arr['msg'] = json_decode($validate->errors());
        }

        return json_encode($arr);                 
    }

    function delete($user_id){
        
        $permission = $this->checkUrlPermission('delete_user');
        if($permission === true) {
            
            $user = AdminUser::find($user_id);
            //$admin_entity = AdminEntity::where('user_id',$user_id)->get();
            if($user->admin_level != '-1') {

                $user->delete();

                //$admin_entity->delete();

                //updateLimitUsage('user-admin','delete');

                $data_arr = ['email'=>$user->email,'name'=>$user->first_name.' '.$user->last_name,'key_val'=>$user->key_val];
                //update sync log data for server
                updateSyncData('user-admin','delete',$data_arr); 

                /*update activity log start*/
                $action_type = "deleted"; 
                $module_name = "team member";            
                $logdetails = "Admin has deleted ".$user->first_name.' '.$user->last_name;
                $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);
                $this->updateLogActivity($logdata);
                /*update activity log End*/

                return redirect()->action('Admin\User\AdminController@index')->with('succMsg', 'User Delete Successfully!');
            }
        }
    }

    function validateUser($input, $user_id=null) {

        $rules['role'] = 'Required';
        //$rules['entity'] = 'Required';
        $rules['nick_name'] = nameRule();
        $rules['first_name'] = nameRule();
        $rules['last_name'] = nameRule();
        //$rules['entity[contact_no]'] = numberRule();
        //$rules["entity[gender]"]= nameRule();
        //$rules['dob'] = 'Required';
        //$rules['email'] = 'Required|email|unique:'.$this->tblAdminUser.',email';
        $rules['email'] = emailRule($this->tblAdminUser, 'email');
        if(!empty($user_id) && !empty(trim($input['email']))) {
            $rules['email'] = Rule::unique($this->tblAdminUser)->ignore($user_id);
        }
        else {
            $rules['password'] = passwordRule();
            $rules['password_confirm'] = confirmPasswordRule('password');            
        }        

        $error_msg['role.required'] = Lang::get('admin.please_select_user_role');
        $error_msg['nick_name.required'] = Lang::get('admin.nick_name_is_required');
        $error_msg['first_name.required'] = Lang::get('admin.first_name_is_required');
        $error_msg['last_name.required'] = Lang::get('admin.last_name_is_required');
        $error_msg['entity[contact_no].required'] = Lang::get('admin.contact_number_is_required');
        //$error_msg['entity[gender].required'] = Lang::get('admin.please_select_gender');
        $error_msg['dob.required'] = Lang::get('admin.please_select_date_of_birth');
        $error_msg['email.required'] = Lang::get('admin.email_address_is_required');
        $error_msg['password.required'] = Lang::get('admin.password_is_required');
        $error_msg['password_confirm.required'] = Lang::get('admin.password_and_confirm_password_should_be_same');       

        $validate = Validator::make($input, $rules, $error_msg);
        //echo '<pre>';print_r($validate->errors());die;
        return $validate; 
    }

    function validatePassword($input) {

        $rules['old_password'] = 'Required';
        $rules['password'] = passwordRule();
        $rules['password_confirm'] = confirmPasswordRule('password');

        $error_msg['old_password.required'] = Lang::get('admin.please_enter_correct_old_password');
        $error_msg['password.required'] = Lang::get('admin.password_is_required');
        $error_msg['password_confirm.required'] = Lang::get('admin.password_and_confirm_password_should_be_same');
        $error_msg['password_confirm.same'] = Lang::get('admin.password_and_confirm_password_should_be_same');       

        $validate = Validator::make($input, $rules, $error_msg);
        //echo '<pre>';print_r($validate->errors());die;
        return $validate; 
    }

    function validateConfirmPassword($input) {

        $rules['confirm_password'] = 'Required';    

        $error_msg['confirm_password.required'] = Lang::get('admin.please_enter_correct_password');      

        $validate = Validator::make($input, $rules, $error_msg);
        //echo '<pre>';print_r($validate->errors());die;
        return $validate; 
    }         
       
}
