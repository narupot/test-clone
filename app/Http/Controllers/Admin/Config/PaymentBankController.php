<?php

namespace App\Http\Controllers\Admin\Config;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\MarketPlace;
use App\PaymentBank;
use App\PaymentBankDesc;
use Config;
use Auth;
use File;
use Lang;

class PaymentBankController extends MarketPlace
{
    private $tblPaymentBankDesc;

    public function __construct()
    {   
        $this->middleware('admin.user');
        $this->tblPaymentBankDesc = with(new PaymentBankDesc)->getTable();  
    }     

    public function index()
    {   

        $permission = $this->checkUrlPermission('manage_payment_bank');
        if($permission === true) {   

            $permission_arr['add'] = $this->checkMenuPermission('add_payment_bank');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_payment_bank');        
       
            $bank_list = PaymentBank::get();

            return view('admin.paymentSetting.listPaymentBank', ['bank_list'=>$bank_list, 'permission_arr'=>$permission_arr]);
        }
    }

    public function create()
    {       
        $permission = $this->checkUrlPermission('add_payment_bank');
        if($permission === true) {         
        
            return view('admin.paymentSetting.createPaymentBank');
        }        
    }

    public function store(Request $request)
    {               
        //echo '<pre>';print_r($request->all());die;

        $payment_bank_name = $request->bank_name[session('admin_default_lang')];

        $input = $request->all();
        
        $input['bnk_name'] = $payment_bank_name;

        $validate = $this->validatePaymentBank($input);

        if ($validate->passes()) {

            $pay_bank = new PaymentBank();            

            if(isset($request->bank_image) && $request->bank_image !='') {

                $uploadDetails['path'] = Config::get('constants.payment_bank_path');
                $uploadDetails['file'] =  $request->bank_image;   

                $imageName = $this->uploadFileCustom($uploadDetails); 

                $pay_bank->bank_image = $imageName;
            }

            //echo $pay_bank->bank_image;die;

            $pay_bank->status = $request->status;
            //$pay_bank->payment_option_id = $request->payment_option_id;
            //$pay_bank->account_no = ($request->account_no !='') ? cleanValue($request->account_no) : '';
            //$pay_bank->account_name = ($request->account_name !='') ? cleanValue($request->account_name) : '';
            //$pay_bank->branch = ($request->branch !='') ? cleanValue($request->branch) : '';
            $pay_bank->bank_code = ($request->bank_code !='') ? cleanValue($request->bank_code) : '';
            $pay_bank->account_type = ($request->account_type !='') ? $request->account_type : '';
            $pay_bank->created_by = Auth::guard('admin_user')->user()->id;
            $pay_bank->save();

            $payment_bank_name_arr = array();
            
            foreach($request->bank_name as $lang=>$val) {

                if(empty($val)) {
                    $val = $payment_bank_name;
                }

                $payment_bank_name_arr[] = ['payment_bank_id'=>$pay_bank->id, 'lang_id'=>$lang, 'bank_name'=>$val];
            }
            PaymentBankDesc::insert($payment_bank_name_arr);

            /*update activity log start*/
            $action_type = "created"; 
            $module_name = "Payment Bank";            
            $logdetails = "Admin has created ".$payment_bank_name." Payment Bank";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

            if($request->submit_type == 'submit_continue') {
                return redirect()->action('Admin\Config\PaymentBankController@edit', $pay_bank->id)->with('succMsg', Lang::get('common.records_added_successfully'));
            }
            else {
                return redirect()->action('Admin\Config\PaymentBankController@index')->with('succMsg', Lang::get('common.records_added_successfully'));
            }
        }
        else {

            return redirect()->action('Admin\Config\PaymentBankController@create')->withErrors($validate)->withInput();
        } 
    }
    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $permission = $this->checkUrlPermission('edit_payment_bank');
        if($permission === true) {        

            $bank_detail = PaymentBank::with('paymentBankName')->where('id', '=', $id)->first();
            
            return view('admin.paymentSetting.editPaymentBank', ['bank_detail'=>$bank_detail, 'tblPaymentBankDesc'=>$this->tblPaymentBankDesc]);
        }        
    }

    public function update(Request $request, $id)
    {
        //echo '<pre>';print_r($request->all());die;

        $def_bank_name = $request->bank_name[session('admin_default_lang')];

        $input = $request->all();
        $input['bnk_name'] = $def_bank_name;      

        $validate = $this->validatePaymentBank($input);

        if ($validate->passes()) {

            $paybank = PaymentBank::find($id);            

            if(isset($request->bank_image) && $request->bank_image !='') {

                $file_path = Config::get('constants.payment_bank_path');
                File::delete($file_path.'/'.$paybank->bank_image);

                $uploadDetails['path'] = $file_path;
                $uploadDetails['file'] =  $request->bank_image;   

                $imageName = $this->uploadFileCustom($uploadDetails); 

                $paybank->bank_image = $imageName;
            }

            $paybank->status = $request->status;
            //$paybank->payment_option_id = $request->payment_option_id;
            // $paybank->account_no = ($request->account_no !='') ? cleanValue($request->account_no) : '';
            // $paybank->account_name = ($request->account_name !='') ? cleanValue($request->account_name) : '';
            // $paybank->branch = ($request->branch !='') ? cleanValue($request->branch) : '';
            $pay_bank->bank_code = ($request->bank_code !='') ? cleanValue($request->bank_code) : '';
            $paybank->account_type = ($request->account_type !='') ? $request->account_type : '';
            $paybank->updated_by = Auth::guard('admin_user')->user()->id;
            $paybank->save();

            foreach($request->bank_name as $lang=>$val) {

                if(empty($val)) {
                    $val = $def_bank_name;
                }

                PaymentBankDesc::where(['payment_bank_id'=>$paybank->id, 'lang_id'=>$lang])->update(['bank_name'=>$val]);
            }

            /*update activity log start*/
            $action_type = "updated"; 
            $module_name = "payment bank";            
            $logdetails = "Admin has updated ".$def_bank_name." Payment Bank";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

            return redirect()->action('Admin\Config\PaymentBankController@index')->with('succMsg', Lang::get('common.records_updated_successfully'));         
        }
        else {

            return redirect()->action('Admin\Config\PaymentBankController@edit', $id)->withErrors($validate)->withInput();
        }        
    }

    //public function destroy($id)
    //{   
        //$currency = Language::find($id);

        //$currency->delete();

        //return redirect()->action('LanguageController@index')->with('succMsg', 'Language Deleted Successfully!');
    //} 

    private function validatePaymentBank($input, $type='') {

        //$rules['payment_option_id'] = 'Required';
        $rules['bnk_name'] = 'Required|Min:3';
        //$rules['account_no'] = 'Required|Min:3';
        $rules['bank_code'] = 'Required';
        $rules['account_type'] = 'Required';
        
        $error_msg['payment_option_id.required'] = Lang::get('payment.select_payment_option');
        $error_msg['bnk_name.required'] = Lang::get('payment.enter_bank_name');
        //$error_msg['account_no.required'] = Lang::get('payment.enter_bank_account_no');
        //$error_msg['account_name.required'] = Lang::get('payment.enter_bank_account_name');
        $error_msg['bank_code.required'] = Lang::get('payment.enter_bank_code');
        $error_msg['account_type.required'] = Lang::get('payment.enter_bank_acc_type');
        

        $validate = Validator::make($input, $rules, $error_msg);
        
        return $validate;          
    }   

    function changeBankStatus($pay_opt_id) {

        $payBank = PaymentBank::select('id', 'status', 'updated_at', 'updated_by')->where('id',$pay_opt_id)->first();

        if($payBank->status == '1') {
            $status = '0';
            $status_msg = 'Inactive';
        }
        else {
            $status = '1';
            $status_msg = 'Active';            
        }

        $payBank->status = $status;
        $payBank->updated_at = date('Y-m-d H:i:s');
        $payBank->updated_by = Auth::guard('admin_user')->user()->id;

        $payBank->save();

        $namedesc = $payBank->paymentBankName;
        $logname = !empty($namedesc)?$namedesc->bank_name:$pay_opt_id;

        /*update activity log start*/
        $action_type = "updated"; 
        $module_name = "payment bank";            
        $logdetails = "Admin has updated $logname status to $status_msg ";
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);
        $this->updateLogActivity($logdata);
        /*update activity log End*/

        return $status_msg;
    }      


    public function  uploadBank(Request $request){
        exit;
        $file_path = public_path()."/bank_list.csv";

        if(\File::exists($file_path)) {

            $file_open = fopen($file_path, "r");
            $column = array_filter(fgetcsv($file_open)); // csv first row column

            $row_data = [];
            while(!feof($file_open)) {
                $row_data[] = fgetcsv($file_open);
            }
            $row_data = array_filter($row_data);

            foreach ($row_data as $key => $value) {

                $payment_bank_name_arr = [];
                $bank_code = $value[0];

                $thai_name = $value[1];
                $eng_name   = $value[2];
                if($bank_code && $thai_name){
                    $pay_bank = new PaymentBank();
                    $pay_bank->payment_option_id = 1;
                    $pay_bank->bank_code = $bank_code;
                    $pay_bank->save();
                    $bank_id = $pay_bank->id;

                    $payment_bank_name_arr[] = ['payment_bank_id'=>$bank_id, 'lang_id'=>0, 'bank_name'=>$thai_name];
                    $payment_bank_name_arr[] = ['payment_bank_id'=>$bank_id, 'lang_id'=>1, 'bank_name'=>$eng_name];

                    PaymentBankDesc::insert($payment_bank_name_arr);
                }else{
                    break;
                    exit();
                }
                

            }
            echo 'success';exit;
        }
    }

    public function  uploadBankBranch(Request $request){
       exit;
        $file_path = public_path()."/bank_branch_list_3.csv";

        if(\File::exists($file_path)) {

            $file_open = fopen($file_path, "r");
            //$column = array_filter(fgetcsv($file_open)); // csv first row column

            $row_data = [];
            while(!feof($file_open)) {
                $row_data[] = fgetcsv($file_open);
            }
           
            $row_data = array_filter($row_data);

            foreach ($row_data as $key => $value) {
                
                $payment_bank_name_arr = [];
                $bank_code = $value[0];
                $branch_code = $value[1];
                $thai_name = $value[2];
                $eng_name   = $value[3];

                if($bank_code && $thai_name){
                    $bank_id = PaymentBank::where('bank_code',$bank_code)->value('id');
                    
                    if($bank_id){
                        $pay_bank = new \App\PaymentBankBranch();
                        $pay_bank->payment_bank_id = $bank_id;
                        $pay_bank->branch_code = $branch_code;
                        $pay_bank->save();
                        $bank_branch_id = $pay_bank->id;

                        $payment_bank_name_arr[] = ['bank_branch_id'=>$bank_branch_id, 'lang_id'=>0, 'branch_name'=>$thai_name];
                        $payment_bank_name_arr[] = ['bank_branch_id'=>$bank_branch_id, 'lang_id'=>1, 'branch_name'=>$eng_name];
                        
                        \App\PaymentBankBranchDesc::insert($payment_bank_name_arr);
                    }
                    
                }else{
                    break;
                    exit();
                }
                

            }
            echo 'success';exit;
        }
    }
}
