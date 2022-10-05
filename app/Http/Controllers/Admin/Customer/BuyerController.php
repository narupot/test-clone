<?php
namespace App\Http\Controllers\Admin\Customer;

use Illuminate\Database\QueryException;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Validator;

use App\Helpers\GeneralFunctions;
use App\Helpers\EmailHelpers;
use App\Helpers\CustomHelpers;
use App\CustomerGroupDesc;
use App\CustomerGroup;
use App\Country;
use Exception;
use Auth;
use Lang;
use DB;
use Session;
use Config;


class BuyerController extends MarketPlace
{ 
    
    public $last_user__filter_list;

    public function __construct() {
        $this->middleware('admin.user');
        $this->tblUser = with(new \App\User)->getTable();
    }

    public function addNewBuyer(Request $request){
        return view('admin.customer.addBuyer');
    }

    public function saveBuyer(Request $request){
        //dd($request->all());
        $input = $request->all();

        unset($input['_token']);

        if(isset($request->id)){
            $user = \App\User::find($request->id);

            if(isset($request->email) && $user->email==$request->email){
                unset($input['email']);
            }

            if(isset($request->ph_number) && $user->ph_number==$request->ph_number){
                unset($input['ph_number']);
            }

            if(isset($request->password) && $user->password==$request->password){
                unset($input['password']);
            }

            if(isset($request->password_confirm) && $user->password_confirm==$request->password_confirm){
                unset($input['password_confirm']);
            }
        }
        
        $validate = $this->validateBuyer($input);

        if ($validate->passes()) {

            $default_group_id = \App\CustomerGroup::select('id','require_approve')->where(['is_default'=>'1','status'=>'1'])->first();
            $group_id = $default_group_id->id;

            if(isset($request->id) && $request->id!='' && $request->id!='0'){
                $user = \App\User::find($request->id);
            }else{
                $user = new \App\User;
            }
            
            $user->register_from = 'admin';

            if(!empty($request->email) && $user->email!=$request->email && $request->loginuse!='ph_no'){
                $user->email = cleanValue($request->email);
            }
            
            if(!empty($request->ph_number) && $user->ph_number!==$request->ph_number && $request->loginuse=='ph_no'){
                $user->ph_number = cleanValue($request->ph_number);
            }
            
            $user->login_use = $request->loginuse;

            if(isset($request->password) && $user->password!=$request->password){
                $user->password = bcrypt($request->password);
            }
            

            $user->first_name = cleanValue($request->first_name);
            $user->last_name = cleanValue($request->last_name);
            $user->display_name = $user->first_name.' '.$user->last_name;
            //$user->gender = $request->gender;
            $user->dob = date('Y-m-d', strtotime($request->dob));
            $user->register_ip = userIpAddress();
            $user->group_id = $group_id;

            $user->facebook_id = '';
            $user->verified = '1';
            $user->email_token = '';
            $user->register_step = 1;
            $user->status = '1';
            $email_required = 'no';
            $otp_required = 'no';

            if($user->save()){
                $status = true;
                $user_id = $user->id; 
                $message = Lang::get('admin_customer.buyer_add_successfully');

                if($request->submit_type == 'save_and_continue') {
                    $return = redirect()->action('Admin\Customer\UserController@show', $user_id)->with('succMsg', Lang::get('admin.buyer_added_successfully'));
                }
                else{
                    $return = redirect()->action('Admin\Customer\BuyerController@addNewBuyer')->with('succMsg', Lang::get('admin.buyer_added_successfully'));
                }

            }else{
                $return = redirect()->action('Admin\Customer\BuyerController@addNewBuyer')->with('errorMsg', Lang::get('admin_customer.buyer_add_error'));
            }

        }else{

            if(isset($request->id)){
                $return = redirect()->action('Admin\Customer\UserController@show',$request->id)->withErrors($validate)->withInput();
            }else{
                $return = redirect()->action('Admin\Customer\BuyerController@addNewBuyer')->withErrors($validate)->withInput();
            }
            
        }
        return $return;
    }

    public function editBuyer(Request $request){
        
        $buyer = \App\User::find($request->id);
        if($buyer===null)
            abort('404');

        //dd($buyer);
        return view('admin.customer.editBuyer',['buyer'=>$buyer]);
    }


    protected function validateBuyer($input){
        $rules['first_name'] = nameRule();
        $rules['last_name'] = nameRule();

        if(isset($input['password']) && isset($input['password_confirm'])){
            $rules['password'] = passwordRule();
            $rules['password_confirm'] = confirmPasswordRule('password');
        }
        
        if(isset($input['loginuse']) && $input['loginuse']=='email' && isset($input['email'])){
            $rules['email'] = emailRule($this->tblUser, 'email');
        }elseif(isset($input['loginuse']) && $input['loginuse']=='ph_no' && isset($input['ph_number'])){
            $rules['ph_number'] = phoneRule($this->tblUser, 'ph_number');
        }else{
            $rules['loginuse'] = reqRule();
        }

        if(isset($input->dob) && $input->dob !=''){
            $rules['dob'] = dateRule('date');
        }         

        $error_msg['first_name.required'] = Lang::get('customer.enter_first_name');
        $error_msg['last_name.required'] = Lang::get('customer.enter_last_name');

        if(isset($input['password']) && isset($input['password_confirm'])){
            $error_msg['password.required'] = Lang::get('customer.please_enter_password');
            $error_msg['password_confirm.required'] = Lang::get('customer.password_and_confirm_password_should_be_same'); 
        }

        if(isset($input['email'])){
            $error_msg['email.required'] = Lang::get('customer.please_enter_email');
            $error_msg['email.unique'] = Lang::get('customer.email_already_exist'); 
        }

        if(isset($input['ph_number'])){
            $error_msg['ph_number.required'] = Lang::get('customer.please_enter_phone_no');
            $error_msg['ph_number.unique'] = Lang::get('customer.phone_number_already_exist');
        }

        return $validate = Validator::make($input, $rules, $error_msg);
    }

}
