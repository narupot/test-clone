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
use DB;

class RelatedConfigController extends MarketPlace
{
    
    public function __construct()
    {   
        $this->middleware('admin.user');       
    }     
    
    public function index()
    {
        // $permission = $this->checkUrlPermission('currency_listing');
        // if($permission === true) {

        //     $permission_arr['add'] = $this->checkMenuPermission('add_new_currency');
        //     $permission_arr['edit'] = $this->checkMenuPermission('edit_currency'); 

        //     $currecncy_list = Currency::get(); 
                
        //     return view('admin.currency.currencyList', ['currecncy_list'=>$currecncy_list, 'permission_arr'=>$permission_arr]);
        // }
    }

    public function create()
    {
        $related_product_setting = \App\RelatedConfig::orderBy('id')->first();

        if(!empty($related_product_setting)){
            $last_updated_date = ($related_product_setting->pcompleted=='0000-00-00 00:00:00') ? '' :  date("d/m/Y  H:i:s", strtotime($related_product_setting->pcompleted));
            $related_product_setting = $related_product_setting->toJson();
        }else{
            $last_updated_date  = '';
            $related_product_setting = '[]';
        }      
        return view('admin.config.related_config_add',['related_product_setting'=>$related_product_setting,'last_updated_date'=>$last_updated_date]);       
    }

    public function store(Request $request)
    {                

        $enable = $request->enable;
        $added_product = $request->added_product;
        $sort = $request->sort;
        $cat_condition = $request->cat;
        $price = $request->price;
        $selection = $price['selection'];
        $price_from = $price['from'];
        $price_to = $price['to'];
        $less_more = $price['less_more'];

        if($request->action =='save'){
            $related_obj = new \App\RelatedConfig;    
        }else{
            // update case
            $id = $request->id;
            $related_obj = \App\RelatedConfig::find($id);    
            DB::table(with(new \App\RelatedProduct)->getTable())->truncate();
            DB::table(with(new \App\Product)->getTable())->update(['rstatus'=>0]);
        }   
        $related_obj->pstatus = 0; //
        $related_obj->status = $enable;
        $related_obj->added_product = $added_product;
        $related_obj->cat_cond = $cat_condition;
        $related_obj->sort_by = $sort;
        $related_obj->price_cond = $selection;
        $related_obj->price_from = $price_from;
        $related_obj->price_to = $price_to;
        $related_obj->less_more = $less_more;
        $related_obj->save();

        /*update activity log start*/

        //Prepaire Data Variable
        $action_type = "updated"; //Change action name like: created,updated,deleted
        $module_name = "Related product config"; //Changes module name like : blog etc 
        $logdetails = "Admin has updated related product config setting"; //Change update message as requirement 
        $old_data = ""; //Optional old data in json format key and value as per requirement 
        $new_data = ""; //Optional new data json format key and value as per requirement 

        //Prepaire array for send data 
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );

        //Call method in module
        $this->updateLogActivity($logdata);

        /*update activity log end*/
        return ['status'=>'success','mesg'=>Lang::get('common.records_updated_successfully')];
    }

    // public function edit($id)
    // {
    //     // $permission = $this->checkUrlPermission('edit_currency');
    //     // if($permission === true) {

    //     //     $currency_details = Currency::where('id', '=', $id)->first();
            
    //     //     return view('admin.currency.currencyEdit', ['currency_detail'=>$currency_details]);
    //     // }        
    // }

    // public function update(Request $request, $id)
    // {
    //     if($id > 0) {
    //         //echo '<pre>';print_r($request->all());die;

    //         $input = $request->all();
    //         $validate = $this->validateCurrency($input, $id);

    //         if ($validate->passes()) {            
    //             $user = Auth::guard('admin_user')->user();
                
    //             if($request->is_default == '1') {
    //                 Currency::where('is_default', '1')->update(['is_default' => '0']);
    //                 $request->status = '1';
    //                 $this->updateCurrency($id);
    //             }             
                
    //             $currency = Currency::find($id);            
    //             if(isset($request->currency_image)) {
                    
    //                 $this->fileDelete(Config::get('constants.currency_path').'/'.$currency->currency_image);
                    
    //                 $uploadDetails['path'] = Config::get('constants.currency_path');
    //                 $uploadDetails['file'] =  $request->currency_image;   

    //                 $imageName = $this->uploadFileCustom($uploadDetails); 

    //                 $currency->currency_image = $imageName;
    //             }

    //             $currency->currency_name = $request->currency_name;
    //             $currency->currency_code = $request->currency_code;
    //             $currency->currency_symbol = $request->currency_symbol;
    //             $currency->status = $request->status?$request->status:'0';
    //             $currency->is_default = $request->is_default?$request->is_default:'0';
    //             $currency->updated_by = $user->id; 
    //             $currency->save();
                
    //             return redirect()->action('Admin\Config\CurrencyController@index')->with('succMsg', Lang::get('admin.currency_updated_successfully'));     
    //         }
    //         else {
    //               return redirect()->action('Admin\Config\CurrencyController@edit', $id)->withErrors($validate)->withInput();
    //         } 
    //     }           
    // }

    // public function destroy($id)
    // {   

    // }
    
    // public function detail()
    // {         
    //     $permission = $this->checkUrlPermission('currency_detail');
    //     if($permission === true) {

    //         $permission_arr['add'] = $this->checkMenuPermission('add_new_currency');
    //         $currencyDetails = Currency::where('status', '=', '1')->get();
                
    //         return view('admin.currency.currencyDetail', ['currencyDetails'=>$currencyDetails, 'permission_arr'=>$permission_arr]);
    //     }
    // }
    
    // public function detailUpdate(Request $request)
    // {         
    //     //echo '<pre>';print_r($request->currency);die;
        
    //     foreach ($request->currency as $currencyId=>$currencyVal) {
   
    //         if(is_numeric($currencyVal)) {
    //             $currency = Currency::find($currencyId);            
    //             $currency->currency_value = $currencyVal;
    //             $currency->save(); 
    //         }
    //     }
        
    //     return redirect()->action('Admin\Config\CurrencyController@detail')->with('succMsg', Lang::get('admin.currency_updated_successfully'));
    // }

    // function validateCurrency($input, $id=null) {      

    //     $rules['currency_name'] = nameRule();
    //     $rules['currency_symbol'] = 'Required';
    //     $rules['currency_code'] = 'Required|unique:'.with(new Currency)->getTable().',currency_code';
    //     if(!empty($id) && !empty($input['currency_code'])) {
    //         $rules['currency_code'] = Rule::unique(with(new Currency)->getTable())->ignore($id);
    //     }        
    //     else {
    //         $rules['currency_image'] = 'Required';
    //     }       

    //     $error_msg['currency_name.required'] = Lang::get('admin.please_enter_currency_name');
    //     $error_msg['currency_symbol.required'] = Lang::get('admin.please_enter_currency_symbol');
    //     $error_msg['currency_code.required'] = Lang::get('admin.please_enter_currency_code');
    //     $error_msg['currency_code.unique'] = Lang::get('admin.currency_already_exist');
    //     $error_msg['currency_image.required'] = Lang::get('admin.please_select_image');      

    //     $validate = Validator::make($input, $rules, $error_msg);
    //     //echo '<pre>';print_r($validate->errors());die;
    //     return $validate; 
    // }

    // function updateCurrency($currency_id) {
    //     DB::table(with(new \App\Product)->getTable())->update(['currency_id'=>$currency_id]);
    //     DB::table(with(new \App\OrdersTemp)->getTable())->delete();
    // }



}
