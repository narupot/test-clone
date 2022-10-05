<?php

namespace App\Http\Controllers\Admin\LoginLog;

use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use DB;
use App\AdminLogDetail;
use App\UserLogDetail;

class LoginLogController extends MarketPlace
{
    
    public function __construct()
    {   
        $this->middleware('admin.user'); 
       
    }     

    function admin() {
        
        $permission = $this->checkUrlPermission('admin_log');
        if($permission === true) {

            $permission_arr['delete'] = $this->checkMenuPermission('admin_log_delete');

            $log_lists = AdminLogDetail::orderBy('created_at','DESC')->get(); 
            return view('admin.loginlog.adminlog', ['log_lists'=>$log_lists, 'permission_arr'=>$permission_arr]); 
        }  

         /*$log_lists = AdminLogDetail::all(); 
         return view('admin.loginlog.adminlog', ['log_lists'=>$log_lists]); */    
    }
    
    function admindeleteLog(Request $request){
        $status = 'error';
        $msg = "Oops! Something went wrong.";
        $permission = $this->checkUrlPermission('admin_log_delete');
        if($permission === true) {

            $user = AdminLogDetail::find($request->log_id);
            if($user->delete()){
                $status = 'success';
                $msg = "this logs is deleted successfully.";
            }

            //return redirect()->action('Admin\LoginLog\LoginLogController@admin')->with('succMsg', 'Log Deleted Successfully!');

        }

        return ['status'=>$status,'message'=>$msg];
    }
    
    function adminclearLog(){
        $status = 'error';
        $msg = "Oops! Something went wrong.";
        $permission = $this->checkUrlPermission('admin_log_delete');
        if($permission === true) {

            if(AdminLogDetail::truncate()){
                $status = 'success';
                $msg = "All logs are cleared successfully.";
            }
            //return redirect()->action('Admin\LoginLog\LoginLogController@admin')->with('succMsg', 'Logs Updated Successfully!');
        }

        return ['status'=>$status,'message'=>$msg];
    }

    function user() {
        $permission = $this->checkUrlPermission('admin_log');
        if($permission === true) {

            $permission_arr['delete'] = $this->checkMenuPermission('user_log_delete');

            $log_lists = UserLogDetail::orderBy('created_at','DESC')->get(); 
            return view('admin.loginlog.userlog', ['log_lists'=>$log_lists, 'permission_arr'=>$permission_arr]); 
        }  

         /*$log_lists = AdminLogDetail::all(); 
         return view('admin.loginlog.adminlog', ['log_lists'=>$log_lists]); */    
    }
    
    function userdeleteLog(Request $request){
        $status = 'error';
        $msg = "Oops! Something went wrong.";
        $permission = $this->checkUrlPermission('user_log_delete');
        if($permission === true) {

            $user = UserLogDetail::find($request->log_id);
            if($user->delete()){
                $status = 'success';
                $msg = "this logs is deleted successfully.";
            }

            //return redirect()->action('Admin\LoginLog\LoginLogController@user')->with('succMsg', 'Log Deleted Successfully!');
        }

        return ['status'=>$status,'message'=>$msg];
    }
    
    function userclearLog(){
        $status = 'error';
        $msg = "Oops! Something went wrong.";
        $permission = $this->checkUrlPermission('user_log_delete');
        if($permission === true) {
            if(UserLogDetail::truncate()){
                $status = 'success';
                $msg = "All logs are cleared successfully.";
            }
            //return redirect()->action('Admin\LoginLog\LoginLogController@user')->with('succMsg', 'Logs Updated Successfully!');
        }

        return ['status'=>$status,'message'=>$msg];
    }

    






        
}
