<?php
namespace App\Http\Controllers\Admin\Logactivity;

use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Auth;
use App\Logactivity;

class LogactivityController extends MarketPlace
{ 
    public function __construct() {   
        $this->middleware('admin.user'); 
    }  
 
    public function index()
    {

        $permission = $this->checkUrlPermission('logactivity');
        if($permission === true) {            
            $activitylog = Logactivity::latest('id')->get();                        
            return view('admin.logactivity.logactivityList', ['activity_logs'=>$activitylog]);
        } 
                
    }

    public function productView($id)
    {

        $permission = $this->checkUrlPermission('logactivity');
        if($permission === true) {
            $productview = Logactivity::where('id',$id)->first();
            $old_data = json_decode($productview['old_data'],true);
            $new_data = json_decode($productview['new_data'],true);
            return view('admin.logactivity.productView', ['product_view'=>$productview, 'old_data'=>$old_data, 'new_data'=>$new_data]);
        } 
                
    } 

    public function orderView($id)
    {

        $permission = $this->checkUrlPermission('logactivity');
        if($permission === true) {                                    
            $orderview = Logactivity::where('id',$id)->first();
            $old_data = json_decode($orderview['old_data'],true);
            $new_data = json_decode($orderview['new_data'],true);   
            return view('admin.logactivity.orderView', ['order_view'=>$orderview, 'old_data'=>$old_data, 'new_data'=>$new_data]);
        } 
                
    }

    public function logDetails($id)
    {

        $permission = $this->checkUrlPermission('logactivity');
        if($permission === true) {
            $productview = Logactivity::where('id',$id)->first();
            $old_data = json_decode($productview['old_data'],true);
            $new_data = json_decode($productview['new_data'],true);
            return view('admin.logactivity.logDetails', ['log_details'=>$productview, 'old_data'=>$old_data, 'new_data'=>$new_data]);
        } 
                
    }   
    
    /*public function updateLogActivity($logdata)
    {        
        if(!empty($logdata['old_data'] && $logdata['new_data'])){
            $logactivity = new Logactivity;
            $logactivity->action_by = Auth::guard('admin_user')->user()->nick_name;
            $logactivity->action_by_email = Auth::guard('admin_user')->user()->email;
            $logactivity->action_type = $logdata['action_type'];
            $logactivity->module_name = $logdata['module_name'];
            $logactivity->action_detail = $logdata['logdetails'];
            $logactivity->old_data = $logdata['old_data'];
            $logactivity->new_data = $logdata['new_data'];
            $logactivity->save();
        }elseif(!empty($logdata['new_data'])){
            $logactivity = new Logactivity;
            $logactivity->action_by = Auth::guard('admin_user')->user()->nick_name;
            $logactivity->action_by_email = Auth::guard('admin_user')->user()->email;
            $logactivity->action_type = $logdata['action_type'];
            $logactivity->module_name = $logdata['module_name'];
            $logactivity->action_detail = $logdata['logdetails'];
            $logactivity->new_data = $logdata['new_data'];
            $logactivity->save();
        }else{
            $logactivity = new Logactivity;
            $logactivity->action_by = Auth::guard('admin_user')->user()->nick_name;
            $logactivity->action_by_email = Auth::guard('admin_user')->user()->email;
            $logactivity->action_type = $logdata['action_type'];
            $logactivity->module_name = $logdata['module_name'];
            $logactivity->action_detail = $logdata['logdetails'];
            $logactivity->save();
        }
                
    }*/  
}
