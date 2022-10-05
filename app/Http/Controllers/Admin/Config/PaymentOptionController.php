<?php

namespace App\Http\Controllers\Admin\Config;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\MarketPlace;
use App\PaymentOption;
use App\PaymentOptionDesc;
use App\Currency;
use Config;
use Auth;
use File;
use Lang;

class PaymentOptionController extends MarketPlace
{
    private $tblPaymentOptionDesc;

    public function __construct()
    {   
        $this->middleware('admin.user');
        $this->tblPaymentOptionDesc = with(new PaymentOptionDesc)->getTable();  
    }     

    public function index()
    {
        $permission = $this->checkUrlPermission('manage_payment_option');
        if($permission === true) {   

            $permission_arr['add'] = $this->checkMenuPermission('add_payment_option');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_payment_option');             
       
            $pay_opt_list = PaymentOption::get();

            $pay_opt_arr = array();

            foreach($pay_opt_list as $value) { 

                $currency_list = '';
                $currency_arr = array();

                /*if(!empty($value->currency_id)) {

                    $currency = Currency::select('code')
                                ->where('status', '1')
                                ->whereIn('id', explode(',', $value->currency_id))
                                ->get();

                    foreach ($currency as $val) {
                        $currency_arr[] = $val->code;
                    } 

                    $currency_list = implode(',', $currency_arr);          
                }*/

                if($value->status == '1') {
                    $status = 'Active';
                }
                else {
                    $status = 'Inactive';
                }            
                $mode = ($value->mode == '1') ? 'Live' : 'Sandbox';
                $pay_opt_arr[] = ['id'=>$value->id, 'name'=>$value->paymentOptName->payment_option_name??'', 'payment_type'=>$value->payment_type, 'image'=>$value->image_name, 'created_at'=>$value->created_at, 'updated_at'=>$value->updated_at, 'status'=>$status,'mode'=>$mode];
            } 
            //dd($pay_opt_arr);
                
            return view('admin.paymentSetting.listPaymentOption', ['pay_opt_arr'=>$pay_opt_arr, 'permission_arr'=>$permission_arr]);
        }
    }

    public function create()
    {       
        $permission = $this->checkUrlPermission('add_payment_option');
        if($permission === true) {        
            return view('admin.paymentSetting.createPaymentOption'); 
        }       
    }

    public function store(Request $request)
    {               
        //echo '<pre>';print_r($request->all());die;

        $payment_option_name = $request->payment_option_name[session('admin_default_lang')];

        $input = $request->all();
        $input['option_name'] = $payment_option_name;

        $validate = $this->validatePaymentOption($input);

        if ($validate->passes()) {

            $payOpt = new PaymentOption();            

            if(isset($request->image_name) && $request->image_name !='') {

                $uploadDetails['path'] = Config::get('constants.payment_option_path');
                $uploadDetails['file'] =  $request->image_name;   

                $imageName = $this->uploadFileCustom($uploadDetails); 

                $payOpt->image_name = $imageName;
            }

            $payOpt->status = $request->status;
            $payOpt->payment_type = $request->payment_type;
            /*$payOpt->currency_id = $this->getCurrencyArray($request->currency_id);*/
            $payOpt->created_by = Auth::guard('admin_user')->user()->id;
            $payOpt->save();

            $payment_option_name_arr = array();
            
            foreach($request->payment_option_name as $lang=>$val) {

                if(empty($val)) {
                    $val = $payment_option_name;
                }

                $payment_option_name_arr[] = ['payment_option_id'=>$payOpt->id, 'lang_id'=>$lang, 'payment_option_name'=>$val];
            }
            PaymentOptionDesc::insert($payment_option_name_arr);

            /*update activity log start*/
            $action_type = "created"; 
            $module_name = "payment option";            
            $logdetails = "Admin has created ".$payment_option_name." payment option";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

            if($request->submit_type == 'submit_continue') {
                return redirect()->action('Admin\Config\PaymentOptionController@edit', $payOpt->id)->with('succMsg', Lang::get('common.records_added_successfully'));
            }
            else {
                return redirect()->action('Admin\Config\PaymentOptionController@index')->with('succMsg', Lang::get('common.records_added_successfully'));
            }         
        }
        else {

            return redirect()->action('Admin\Config\PaymentOptionController@create')->withErrors($validate)->withInput();
        } 
    }
    public function show($id)
    {
        //
    }

    public function edit($id)
    {

        $permission = $this->checkUrlPermission('edit_payment_option');
        if($permission === true) {         
            
            $pay_opt_detail = PaymentOption::with('paymentOptName')->where('id', $id)->first();
            
            $field_name = ($pay_opt_detail->field_name) ? json_decode($pay_opt_detail->field_name,true) : [];
            $live_detail = ($pay_opt_detail->live_detail) ? json_decode($pay_opt_detail->live_detail,true) : [];
            $sandbox_detail = ($pay_opt_detail->sandbox_detail) ? json_decode($pay_opt_detail->sandbox_detail,true) : [];
            //dd($pay_opt_detail);
            return view('admin.paymentSetting.editPaymentOption', ['pay_opt_detail'=>$pay_opt_detail, 'tblPaymentOptionDesc'=>$this->tblPaymentOptionDesc,'field_name'=>$field_name,'live_detail'=>$live_detail,'sandbox_detail'=>$sandbox_detail]);
        }        
    }

    public function update(Request $request, $id)
    {
        //echo '<pre>';print_r($request->all());die;

        $payment_option_name = $request->payment_option_name[session('admin_default_lang')];

        $input = $request->all();
        $input['option_name'] = $payment_option_name;
        /*if(empty(array_filter($request->currency_id))) {
            $input['currency_id'] = '';
        } */       

        $validate = $this->validatePaymentOption($input);

        if ($validate->passes()) {

            $payOpt = PaymentOption::find($id);            

            if(isset($request->image_name) && $request->image_name !='') {

                $file_path = Config::get('constants.payment_option_path');
                File::delete($file_path.'/'.$payOpt->image_name);

                $uploadDetails['path'] = $file_path;
                $uploadDetails['file'] =  $request->image_name;   

                $imageName = $this->uploadFileCustom($uploadDetails); 

                $payOpt->image_name = $imageName;
            }

            $sandbox_detail = $live_detail = [] ;
            if(count($request->field_name)){
                foreach ($request->field_name as $key => $value) {
                    $sandbox_detail[$value] = $request->sandbox[$value];
                    $live_detail[$value] = $request->live[$value];
                }
            }
            
            $payOpt->status = $request->status;
            $payOpt->payment_type = $request->payment_type;
            $payOpt->live_detail = json_encode($live_detail);
            $payOpt->sandbox_detail = json_encode($sandbox_detail);
            /*$payOpt->currency_id = $this->getCurrencyArray($request->currency_id);*/
            $payOpt->updated_by = Auth::guard('admin_user')->user()->id;
            $payOpt->save();

            foreach($request->payment_option_name as $lang=>$val) {

                if(empty($val)) {
                    $val = $payment_option_name;
                }

                PaymentOptionDesc::where(['payment_option_id'=>$payOpt->id, 'lang_id'=>$lang])->update(['payment_option_name'=>$val]);
            }

            /*update activity log start*/
            $action_type = "updated"; 
            $module_name = "payment option";            
            $logdetails = "Admin has updated ".$payment_option_name." payment option";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

            return redirect()->action('Admin\Config\PaymentOptionController@index')->with('succMsg', Lang::get('common.records_updated_successfully'));         
        }
        else {

            return redirect()->action('Admin\Config\PaymentOptionController@edit', $id)->withErrors($validate)->withInput();
        }        
    }

    //public function destroy($id)
    //{   
        //$currency = Language::find($id);

        //$currency->delete();

        //return redirect()->action('LanguageController@index')->with('succMsg', 'Language Deleted Successfully!');
    //} 

    private function validatePaymentOption($input, $type='') {

        $rules['payment_type'] = 'Required';
        /*$rules['currency_id'] = 'Required';*/
        $rules['option_name'] = 'Required';

        $error_msg['payment_type.required'] = 'Select payment type';
        /*$error_msg['currency_id.required'] = 'Select payment currenct';*/
        $error_msg['option_name.required'] = 'Select payment option name';

        $validate = Validator::make($input, $rules, $error_msg);
        
        return $validate;        
    }   

    private function getCurrencyArray($currency_id) {

        if(in_array('all', $currency_id)) {

            $currency = Currency::select('id')->where('status', '1')->get();
            foreach ($currency as $val) {
                $currency_arr[] = $val->id;
            } 

            $currency = implode(',', $currency_arr);
        }
        else {
            $currency = implode(',', $currency_id);
        }

        //echo '<pre>';print_r($currency);die;

        return $currency;
    } 

    function changePayOptStatus($pay_opt_id) {

        $payOpt = PaymentOption::select('id', 'status', 'updated_at', 'updated_by')->where('id',$pay_opt_id)->first();

        if($payOpt->status == '1') {
            $status = '0';
            $status_msg = 'Inactive';
        }
        else {
            $status = '1';
            $status_msg = 'Active';            
        }

        $payOpt->status = $status;
        $payOpt->updated_at = date('Y-m-d H:i:s');
        $payOpt->updated_by = Auth::guard('admin_user')->user()->id;

        $payOpt->save();

        $namedesc = $payOpt->paymentOptName;
        $logname = !empty($namedesc)?$namedesc->payment_option_name:$pay_opt_id;

        /*update activity log start*/
        $action_type = "updated"; 
        $module_name = "payment option";            
        $logdetails = "Admin has updated $logname status to $status_msg ";
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);
        $this->updateLogActivity($logdata);
        /*update activity log End*/

        return $status_msg;
    }     

    function changePayOptMode($pay_opt_id) {

        $payOpt = PaymentOption::select('id', 'mode', 'updated_at', 'updated_by')->where('id',$pay_opt_id)->first();

        if($payOpt->mode == '1') {
            $mode = '2';
            $status_msg = 'Sandbox';
        }
        else {
            $mode = '1';
            $status_msg = 'Live';            
        }

        $payOpt->mode = $mode;
        $payOpt->updated_at = date('Y-m-d H:i:s');
        $payOpt->updated_by = Auth::guard('admin_user')->user()->id;

        $payOpt->save();

        return $status_msg;
    }    
}
