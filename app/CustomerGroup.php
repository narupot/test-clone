<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

use Auth;
use Session;

class CustomerGroup extends Model
{
	use SoftDeletes;

    protected $table = 'customer_group';

    protected $dates = ['deleted_at'];

    protected $fillable = ['seller_id'];
    
    public function customerGroupDesc($lang_id=1){
       // $lang_code=Session::get();
       return $this->hasOne('App\CustomerGroupDesc', 'group_id', 'id')->select('id', 'group_id', 'group_name', 'group_desc')->where('lang_id', $lang_id); 
    }  


    public function getCustGroupDesc(){
        //dd(session('default_lang'));
       return $this->hasOne('App\CustomerGroupDesc', 'group_id', 'id')->select('id', 'group_id', 'group_name', 'group_desc')->where('lang_id', session('default_lang')); 
    }

    public static function getCustomerGroupbyId($id){
        return self::where(['id'=>$id])->with('customerGroupDesc')->first();
    }
    
    public static function validateCustomerGroup($input) {

        $rules['group_name'] = 'Required|Min:3';
        $rules['group_desc'] = 'Required';
        
        $error_msg['group_name.required'] = 'Group name is required';
        $error_msg['group_desc.required'] = 'Group note is required';

        $validate = Validator::make($input, $rules, $error_msg);
        
        return $validate;
    }   

    public static function getCustomerGroup() {
        return self::select('id')->where(['status'=>'1'])->with('customerGroupDesc')->get();
    } 


    public static function getCustomerGroupName() {
        return self::select('*')->where(['status'=>'1'])->with('customerGroupDesc')->get();
    }


}
