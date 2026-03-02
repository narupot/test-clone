<?php
namespace App\Http\Controllers\Admin\Package;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Config;
use App\Language;
use App\Package;
use App\PackageDesc;
use App\MongoPackage;
use Auth;
use Lang;
use DB;


class PackageController extends MarketPlace
{ 
    private $tblPackage;
    private $tblPackageDesc;
    private $module_name = "package";
    public function __construct()
    {
        $this->middleware('admin.user');

        $this->tblPackage = with(new Package)->getTable();
        $this->tblPackageDesc = with(new PackageDesc)->getTable();
    }

    public function index(){

        $permission = $this->checkUrlPermission('list_package');
        if($permission === true) {
            $permission_arr['add'] = $this->checkMenuPermission('add_package_management');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_package_management');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_package_management');

            $data_arr = array();
            $data_cus_arr = array();
            $package_dtl = Package::getAllPackage();
            //dd($package_dtl);
            return view('admin.package.listPackage', ['package_dtl'=>$package_dtl, 'permission_arr'=>$permission_arr]);
        }
    }   
    
    public function create(){

        $permission = $this->checkUrlPermission('add_package_management');
        if($permission === true) {

            return view('admin.package.createPackage');
        }
    }
    
    function store(Request $request){ 
        //dd($request->all());
        $input = $request->all();
        $input['package_name'] = $request->package_name[session('admin_default_lang')];
        $validate = $this->validatePackage($input);

        if ($validate->passes()) {

            $cms = new Package;

            $cms->status = !empty($request->status)?'1':'0';

            $cms->title = cleanValue($request->title);
            $cms->height = cleanValue($request->height);
            $cms->width = cleanValue($request->width);
            $cms->depth = cleanValue($request->depth);
            $cms->created_by = Auth::guard('admin_user')->user()->id;
            $cms->save();
            $package_id = $cms->id;

            foreach ($request->package_name as $key => $value) {
                if(empty($value)) {
                    $value = $input['package_name'];
                }

                $data[] = ["package_id" => $package_id, "lang_id" => $key, "package_name" => $value];
            }
            DB::table($this->tblPackageDesc)->insert($data);

            /***update Package data into mongo db*****/
            $data = Package::packageData($package_id);
            $store = MongoPackage::updateData($data);
            /*update activity log start*/
            $action_type = "created";             
            $logdetails = "Admin has ".$action_type." ".$input['package_name']." $this->module_name";
            $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/
            
            if($request->submit_type == 'submit_continue') {

                return $response =['status'=>'success', 'url' =>action('Admin\Package\PackageController@edit',$cms->id)];
            }
            else {
                
                return $response =['status'=>'success', 'url' =>action('Admin\Package\PackageController@index')];
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

            $package_dtls = Package::getPackagebyId($id);
            
            return view('admin.package.editPackage', ['package_dtls'=>$package_dtls, 'tblPackageDesc'=>$this->tblPackageDesc]);
        }
    }
    
    function update(Request $request, $package_id){
  
        //echo '<pre>';print_r($request->all());die;

        $input = $request->all();
        $input['package_name'] = $request->package_name[session('admin_default_lang')];

        $badge_dtls = Package::getPackagebyId($package_id);

        if(empty($badge_dtls)){
            return array('status'=>'fail','message'=>'invalid id');
        }

        $validate = $this->validatePackage($input, $package_id);      

        if ($validate->passes()) {

            $badge_dtls->status = !empty($request->status)?'1':'0';

            $badge_dtls->title = cleanValue($request->title);
            $badge_dtls->height = cleanValue($request->height);
            $badge_dtls->width = cleanValue($request->width);
            $badge_dtls->depth = cleanValue($request->depth);
            $badge_dtls->updated_by = Auth::guard('admin_user')->user()->id;
            $badge_dtls->save();

            foreach ($request->package_name as $key => $value) {

                if(empty($value)) {
                    $value = $input['package_name'];
                }                
                        
                $data_arr = ["package_id" => $package_id, "lang_id" => $key, "package_name" => $value];
                 PackageDesc::updateOrCreate(['package_id' => $package_id, 'lang_id' => $key], $data_arr);
            }

            /***update Package data into mongo db*****/
            $data = Package::packageData($package_id);
            $store = MongoPackage::updateData($data);

            /*update activity log start*/
            $action_type = "updated"; 
            $logdetails = "Admin has ".$action_type." ".$input['package_name']." $this->module_name";
            $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

            if($request->submit_type == 'submit_continue') {

                return $response =['status'=>'update', 'url' =>action('Admin\Package\PackageController@edit',$package_id)];
            }
            else {
                
                return $response =['status'=>'success','url' =>action('Admin\Package\PackageController@index')];
            }
        }
        else {

            $errors =  $validate->errors(); 
            return array('status'=>'fail','message'=>$errors,'validation'=>true);
        }            
    }    
    
    function destroy($id){
        $permission = $this->checkUrlPermission('delete_package_management');
        if($permission === true) {
            $static_cms = Package::getPackagebyId($id);
            if(!empty($static_cms)){
                try {
                    $namedesc = $static_cms->packagedesc;
                    $logname = !empty($namedesc)?$namedesc->package_name:$id;

                    Package::where('id', $id)->delete();

                    /***delete from mongo***/
                    MongoPackage::deleteData($id);

                    /*update activity log start*/
                    $action_type = "deleted";            
                    $logdetails = "Admin has ".$action_type." $logname ";
                    $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

                    $this->updateLogActivity($logdata);

                    return redirect()->action('Admin\Package\PackageController@index')->with('succMsg', Lang::get('admin_common.records_deleted_successfully'));

                }catch (Exception $e) {
                    return redirect()->action('Admin\Package\PackageController@index')->with('errorMsg', $e->getMessage());
                }
                
            }else{
                return redirect()->action('Admin\Package\PackageController@index')->with('errorMsg', Lang::get('admin_common.error'));
            }
            
        }
    }

    function changeStatus($id) {

        $static_cms = Package::getPackagebyId($id);
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

        $namedesc = $static_cms->packagedesc;
        $logname = !empty($namedesc)?$namedesc->package_name:$id;

        /***update status data into mongo db*****/
        $store = MongoPackage::updateStatus($id,$status);

        /*update activity log start*/
        $action_type = "updated";            
        $logdetails = "Admin has ".$action_type." $logname status to $status_msg ";
        $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/

        return $status_msg;
    }

    private function validatePackage($input, $package_id='') {

        if(!empty($package_id) && !empty($input['package_name'])) {
            $rules['package_name'] = uniqueIgnoreRule($this->tblPackageDesc,'package_name',$package_id,'package_id');
            
        }else{
            $rules['package_name'] = uniqueRule($this->tblPackageDesc,'package_name',$input['package_name']);
        }  

        $rules['title'] = reqRule();   
        
        $error_msg['un_name.required'] = Lang::get('admin_product.package_name_is_required');
        $error_msg['un_name.unique'] = Lang::get('admin_product.package_name_already_exist');
        $error_msg['title.required'] = Lang::get('admin_common.please_enter_title');
        $validate = Validator::make($input, $rules, $error_msg);
        
        return $validate; 
    }
  
}
