<?php

namespace App\Http\Controllers\Admin\Translation;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;

use App\OrderStatus;
use App\OrderStatusDesc;
use Lang;

class OrderStatusController extends MarketPlace
{
    private $tblOrderStatusDesc;

    public function __construct()
    {
        $this->middleware('admin.user');
        $this->tblOrderStatusDesc = with(new OrderStatusDesc)->getTable();
    }
    
    public function index()
    {
        $permission = $this->checkUrlPermission('orderstatus');
        if($permission === true) { 

            $permission_arr['edit'] = $this->checkMenuPermission('add_orderstatus'); 
            $menu_lists = OrderStatus::getOrderStatusAll();
            //dd($menu_lists);
            return view('admin.translation.orderStatusList', ['menu_lists'=>$menu_lists, 'permission_arr'=>$permission_arr]);
        }
    }
    
    function edit($order_status_id) {
        
        $menuData = \App\OrderStatusDesc::where(['order_status_id'=>$order_status_id,'lang_id'=>session('default_lang')])->first();
        
        $permission = $this->checkUrlPermission('edit_menu_language');
        if($permission === true) {        
            return view('admin.translation.orderStatusEdit', ['tblOrderStatusDesc'=>$this->tblOrderStatusDesc, 'id'=>$order_status_id,'menuData'=>$menuData]);
        }
    }
    
    function update(Request $request, $order_status_id)
    {
        //echo '<pre>';print_r($request->all());die;
        
        if($order_status_id> 0) {

            $default_menu_name = $request->status[session('default_lang')];

            $input = $request->all();
            $input['menu'] = $default_menu_name;

            $rules = ['menu' => 'Required|Min:3'];
            $error_msg['menu.required'] = Lang::get('admin.please_enter_menu_name');
                    
            $validate = Validator::make($input, $rules, $error_msg);

            if ($validate->passes()) {                       
                
                foreach($request->status as $key=>$value){

                    if(empty($value)) {
                        $value = $default_menu_name;
                    }
                    $menu_arr = ['status'=>$value];
                    OrderStatusDesc::updateOrCreate(['order_status_id'=>$order_status_id, 'lang_id'=>$key], $menu_arr);
                }

                /*update activity log Start*/                        
                $action_type = "update";
                $module_name = 'OrderStatus';            
                $logdetails = "Admin has $action_type ".$input['menu']." $module_name";
                $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails );
                $this->updateLogActivity($logdata);
                /*update activity log End*/

                return redirect()->action('Admin\Translation\OrderStatusController@index')->with('succMsg', Lang::get('admin.record_updated_successfully'));
            }
            else {
              return redirect()->action('Admin\Translation\OrderStatusController@edit', $order_status_id)->withErrors($validate)->withInput();
            }                       
        }
    }       
}
