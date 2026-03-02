<?php

namespace App\Http\Controllers\Admin\Config;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;

use App\Currency;
use Auth;
use Config;
use Lang;
use File;
use DB;

class CurrencyController extends MarketPlace
{
    
    public function __construct()
    {   
        $this->middleware('admin.user');       
    }     
    
    public function index()
    {
        $permission = $this->checkUrlPermission('currency_listing');
        if($permission === true) {

            $permission_arr['add'] = $this->checkMenuPermission('add_new_currency');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_currency'); 

            $currecncy_list = Currency::get(); 
                
            return view('admin.currency.currencyList', ['currecncy_list'=>$currecncy_list, 'permission_arr'=>$permission_arr]);
        }
    }

    public function create()
    {
        $permission = $this->checkUrlPermission('add_new_currency');
        if($permission === true) {                
        
            return view('admin.currency.currencyAdd');
        }        
    }

    public function store(Request $request)
    {                
        $input = $request->all();
        $validate = $this->validateCurrency($input);

        if ($validate->passes()) {        
            
            if($request->is_default == '1') {
                Currency::where('is_default', '1')->update(['is_default' => '0']);
                $request->status = '1';
            }        
            
            $currency = new Currency();
            if(isset($request->currency_image)) {

                $uploadDetails['path'] = Config::get('constants.currency_path');
                $uploadDetails['file'] =  $request->currency_image;   

                $imageName = $this->uploadFileCustom($uploadDetails); 

                $currency->currency_image = $imageName;
            }              

            $currency->currency_name = $request->currency_name;
            $currency->currency_code = $request->currency_code;
            $currency->currency_symbol = $request->currency_symbol;        
            $currency->status = $request->status?$request->status:'0';
            $currency->is_default = $request->is_default?$request->is_default:'0';
            $currency->created_by = Auth::guard('admin_user')->user()->id; 
            $currency->save();

            if($request->is_default == '1') {
                $this->updateCurrency($currency->id);
            }

            /*update activity log start*/
            $action_type = "created"; 
            $module_name = "currency";            
            $logdetails = "Admin has created ".$request->currency_name." ".$module_name;
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/
            
            return redirect()->action('Admin\Config\CurrencyController@index')->with('succMsg', Lang::get('admin.currency_added_successfully'));
        } 
        else {
            return redirect()->action('Admin\Config\CurrencyController@create')->withErrors($validate)->withInput();
        }             
    }

    public function edit($id)
    {
        $permission = $this->checkUrlPermission('edit_currency');
        if($permission === true) {

            $currency_details = Currency::where('id', '=', $id)->first();
            
            return view('admin.currency.currencyEdit', ['currency_detail'=>$currency_details]);
        }        
    }

    public function update(Request $request, $id)
    {
        if($id > 0) {
            $input = $request->all();
            $validate = $this->validateCurrency($input, $id);

            if ($validate->passes()) {            
                $user = Auth::guard('admin_user')->user();
                
                if($request->is_default == '1') {
                    Currency::where('is_default', '1')->update(['is_default' => '0']);
                    $request->status = '1';
                    $this->updateCurrency($id);
                }             
                
                $currency = Currency::find($id);            
                if(isset($request->currency_image)) {
                    
                    $this->fileDelete(Config::get('constants.currency_path').'/'.$currency->currency_image);
                    
                    $uploadDetails['path'] = Config::get('constants.currency_path');
                    $uploadDetails['file'] =  $request->currency_image;   

                    $imageName = $this->uploadFileCustom($uploadDetails); 

                    $currency->currency_image = $imageName;
                }

                $currency->currency_name = $request->currency_name;
                $currency->currency_code = $request->currency_code;
                $currency->currency_symbol = $request->currency_symbol;
                $currency->status = $request->status?$request->status:'0';
                $currency->is_default = $request->is_default?$request->is_default:'0';
                $currency->updated_by = $user->id; 
                $currency->save();

                /*update activity log start*/
                $action_type = "updated"; 
                $module_name = "currency";            
                $logdetails = "Admin has updated ".$request->currency_name." ".$module_name;
                $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

                $this->updateLogActivity($logdata);
                /*update activity log End*/
                
                return redirect()->action('Admin\Config\CurrencyController@index')->with('succMsg', Lang::get('admin.currency_updated_successfully'));     
            }
            else {
                  return redirect()->action('Admin\Config\CurrencyController@edit', $id)->withErrors($validate)->withInput();
            } 
        }           
    }

    public function destroy($id)
    {   

    }
    
    public function detail()
    {         
        $permission = $this->checkUrlPermission('currency_detail');
        if($permission === true) {

            $permission_arr['add'] = $this->checkMenuPermission('add_new_currency');
            $currencyDetails = Currency::where('status', '=', '1')->get();
                
            return view('admin.currency.currencyDetail', ['currencyDetails'=>$currencyDetails, 'permission_arr'=>$permission_arr]);
        }
    }
    
    public function detailUpdate(Request $request)
    {         
        //echo '<pre>';print_r($request->currency);die;
        $currency_arr = [];
        foreach ($request->currency as $currencyId=>$currencyVal) {
   
            if(is_numeric($currencyVal)) {
                $currency = Currency::find($currencyId);            
                $currency->currency_value = $currencyVal;
                $currency->save(); 
                $currency_arr[$currencyId] = $currencyVal;
            }
        }

        $file_complete_path = Config('constants.data_cache_path').'/currency.dict';
        $json_data = json_encode($currency_arr);
        File::put($file_complete_path, $json_data);
        
        return redirect()->action('Admin\Config\CurrencyController@detail')->with('succMsg', Lang::get('admin.currency_updated_successfully'));
    }

    function validateCurrency($input, $id=null) {      

        $rules['currency_name'] = nameRule();
        $rules['currency_symbol'] = 'Required';
        $rules['currency_code'] = 'Required|unique:'.with(new Currency)->getTable().',currency_code';
        if(!empty($id) && !empty($input['currency_code'])) {
            $rules['currency_code'] = Rule::unique(with(new Currency)->getTable())->ignore($id);
        }        
        else {
            $rules['currency_image'] = 'Required';
        }       

        $error_msg['currency_name.required'] = Lang::get('admin.please_enter_currency_name');
        $error_msg['currency_symbol.required'] = Lang::get('admin.please_enter_currency_symbol');
        $error_msg['currency_code.required'] = Lang::get('admin.please_enter_currency_code');
        $error_msg['currency_code.unique'] = Lang::get('admin.currency_already_exist');
        $error_msg['currency_image.required'] = Lang::get('admin.please_select_image');      

        $validate = Validator::make($input, $rules, $error_msg);
        //echo '<pre>';print_r($validate->errors());die;
        return $validate; 
    }

    function updateCurrency($currency_id) {

        DB::table(with(new \App\Product)->getTable())->update(['currency_id'=>$currency_id]);
        DB::table(with(new \App\OrdersTemp)->getTable())->delete();
    }
}
