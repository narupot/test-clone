<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
class UserInfo extends Model
{  
    protected $table = 'user_info';

    protected $fillable = ['id','user_id', 'info_type','reference_no','info_json','ph_number','citizen_id','data_sent','status'];

    public static function updateInfo($reference_no,$info_json=null,$request,$post_json=null,$status='0'){
        $user_id = Auth::id();

        Self::updateOrCreate(['user_id'=>$user_id,'info_type'=>'odd-register'],['user_id'=>$user_id,'info_type'=>'odd-register','reference_no'=>$reference_no,'info_json'=>$info_json,'citizen_id'=>$request->citizen_id,'ph_number'=>$request->ph_number,'data_sent'=>$post_json,'status'=>$status]);
        return true;
    }

    public static function getUserInfo($info_type,$user_id=null){
        $user_id = $user_id?$user_id:Auth::id();

        return Self::where(['user_id'=>$user_id,'info_type'=>$info_type])->first();
    }
}
