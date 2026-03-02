<?php
namespace App\Http\Controllers\Admin\Customer;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;

use App\Language;
use App\CustomerGroup;
use App\CustomerGroupDesc;
use App\CustomerAttribute;
use Auth;
use Lang;

class CustGroupController extends MarketPlace
{ 
    private $tblCustomerGroup;
    private $tblCustomerGroupDesc;
    public function __construct()
    {
        $this->middleware('admin.user');

        $this->tblCustomerGroup = with(new CustomerGroup)->getTable();
        $this->tblCustomerGroupDesc = with(new CustomerGroupDesc)->getTable();
    }
    
    public function index(){

        //$permission = $this->checkUrlPermission('cust_group');
        $permission = true;
        if($permission === true) {
            $permission_arr['add'] = $this->checkMenuPermission('add_cust_group');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_cust_group');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_cust_group');

            $data_arr = array();
            $group_dtl = CustomerGroup::with('customerGroupDesc')->get()->toArray();
            
            //dd($aa); 
            $groupDetailsList = [];
            foreach($group_dtl as $g_key => $group){
                $groupDetailsList[$g_key] = $group; 
                $groupDetailsList[$g_key]['total_customers'] = \App\User::where('group_id',$group['id'])->count();
                
            }

                
            return view('admin.customer.listCustGroup', ['group_dtl'=>$groupDetailsList, 'permission_arr'=>$permission_arr]);
        }
    }
    
    public function create(){

        $permission = $this->checkUrlPermission('add_cust_group');
        if($permission === true) {

            $lang_lists = Language::getLangugeDetails();
            return view('admin.customer.createCustGroup', ['lang_lists'=>$lang_lists]);
        }
    }
    
    function store(Request $request){ 
        //echo '<pre>';print_r($request->all());die;
        $checkuse = checkLimitUsage('customer-group');
        
        if($checkuse['status'] == false){
            return redirect()->action('Admin\Customer\CustGroupController@index')->with('errorMsg', Lang::get('admin.please_check_your_package_limit'));
        }
        $input = $request->all();
        $input['group_name'] = $request->group_name[session('admin_default_lang')];
        $input['group_desc'] = $request->group_desc[session('admin_default_lang')];      

        $validate = CustomerGroup::validateCustomerGroup($input);

        if ($validate->passes()) {

            $group = new CustomerGroup;
            $group->require_approve = isset($request->require_approve) ? $request->require_approve : '0';
            $group->is_default = isset($request->is_default) ? $request->is_default : '0';
            $group->status = '1';
            $group->created_by = Auth::guard('admin_user')->user()->id;
            $group->save();

            if(isset($request->is_default) && $request->is_default=='1'){
               
                CustomerGroup::where('id','!=',$group->id)->update(['is_default'=>'0','updated_by'=>Auth::guard('admin_user')->user()->id]);
            }



            $data_arr = $this->filterGroupData($request);
            //echo '<pre>';print_r($data_arr);die;              

            CustomerGroupDesc::insertGroupDesc($data_arr, $group->id);

            // When new group will create then it will assign to all customer groups
            $customer_groups = implode(',',\App\CustomerGroup::where('status','1')->pluck('id')->toArray());
            CustomerAttribute::where('id','!=','')->update(['customer_group'=>$customer_groups]);
            
            /****after save update limit******/
            updateLimitUsage('customer-group','add');

            /*update activity log start*/
            $action_type = "created"; 
            $module_name = "customer group";            
            $logdetails = "Admin has created ".$request->group_name[session('admin_default_lang')]." ".$module_name;
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

            if($request->submit_type == 'submit_continue') {

                return redirect()->action('Admin\Customer\CustGroupController@edit', $group->id)->with('succMsg', 'Records Added Successfully!');
            }
            else {

                return redirect()->action('Admin\Customer\CustGroupController@index')->with('succMsg', 'Records Added Successfully!');
            }
        } 
        else {

            return redirect()->action('Admin\Customer\CustGroupController@create')->withErrors($validate)->withInput();
        }                 
    }

    public function filterGroupData($request) {

        $def_lang = session('admin_default_lang');
        $def_group = $request->group_name[$def_lang];
        $def_note = $request->group_desc[$def_lang];

        foreach ($request->group_name as $key=>$value){

            $group_name = $request->group_name[$key];
            $note = $request->group_desc[$key];

            if(empty($group_name)) {
                $group_name = $def_group;
            }
            if(empty($note)) {
                $note = $def_note;
            } 

            $data_arr[$key] = array('group_name'=>cleanValue($group_name), 'group_desc'=>cleanValue($note));
        } 
        return $data_arr;       
    }

    public function show($id) {
    }
    
    function edit($id) {
        $permission = $this->checkUrlPermission('edit_cust_group');
        if($permission === true) {

            $group_dtls = CustomerGroup::getCustomerGroupbyId($id);

            return view('admin.customer.editCustGroup', ['group_dtls'=>$group_dtls, 'tblCustomerGroupDesc'=>$this->tblCustomerGroupDesc]);
        }
    }
    
    function update(Request $request, $group_id){
        //echo "<pre>"; print_r($request->all()); die;
        $input = $request->all();
        $input['group_name'] = $request->group_name[session('admin_default_lang')];
        $input['group_desc'] = $request->group_desc[session('admin_default_lang')];     

        $validate = CustomerGroup::validateCustomerGroup($input);     

        if ($validate->passes()) {

            $require_approve = isset($request->require_approve) ? $request->require_approve : '0';
            CustomerGroup::where(['id'=>$group_id])->update(['require_approve'=>$require_approve,'updated_by'=>Auth::guard('admin_user')->user()->id]);

            if(isset($request->is_default) && $request->is_default=='1'){
                CustomerGroup::where(['id'=>$group_id])->update(['is_default'=>'1','updated_by'=>Auth::guard('admin_user')->user()->id]);

                CustomerGroup::where('id','!=',$group_id)->update(['is_default'=>'0','updated_by'=>Auth::guard('admin_user')->user()->id]);
            }
            
            $data_arr = $this->filterGroupData($request);         

            CustomerGroupDesc::updateGroupDesc($data_arr, $group_id);       

            /*update activity log start*/
            $action_type = "updated"; 
            $module_name = "customer group";            
            $logdetails = "Admin has updated ".$request->group_name[session('admin_default_lang')]." ".$module_name;
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

            return redirect()->action('Admin\Customer\CustGroupController@index')->with('succMsg', 'Records Updated Successfully!');
        }
        else {

            return redirect()->action('Admin\Customer\CustGroupController@edit', $group_id)->withErrors($validate)->withInput();
        }            
    }    
    
    function destroy($id){

        //echo '====>'.$id;die;
        $c_group = CustomerGroup::getCustomerGroupbyId($id);
        if(!empty($c_group)){
            CustomerGroup::where('id', $id)->delete();

            // When new group will create then it will assign to all customer groups
            $customer_groups = implode(',',\App\CustomerGroup::where('status','1')->pluck('id')->toArray());
            CustomerAttribute::where('id','!=','')->update(['customer_group'=>$customer_groups]);
            updateLimitUsage('customer-group','delete');

            $namedesc = $c_group->customerGroupDesc;
            $logname = !empty($namedesc)?$namedesc->group_name:$id;

            /*update activity log start*/
            $action_type = "deleted"; 
            $module_name = "customer group";            
            $logdetails = "Admin has deleted $logname ";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);
            $this->updateLogActivity($logdata);
            /*update activity log End*/
            return redirect()->action('Admin\Customer\CustGroupController@index')->with('succMsg', 'Record Deleted Successfully!');
        }else{
            return redirect()->action('Admin\Customer\CustGroupController@index')->with('errorMsg', 'error');
        }
        
    }

    function changeStatus($id) {

        $static_cms = CustomerGroup::getCustomerGroupbyId($id);

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

        $namedesc = $static_cms->customerGroupDesc;
        $logname = !empty($namedesc)?$namedesc->group_name:$id;

        /*update activity log start*/
        $action_type = "updated"; 
        $module_name = "customer group";            
        $logdetails = "Admin has updated $logname status to $status_msg ";
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);
        $this->updateLogActivity($logdata);
        /*update activity log End*/

        return $status_msg;
    }
   
}
