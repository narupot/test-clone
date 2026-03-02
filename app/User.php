<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;
use Collective\Html\Eloquent\FormAccessible;
use Auth;
use Cache;
use App\Notifications\ResetPassword;
use DB;
class User extends Authenticatable {
  
    use Notifiable;
    use FormAccessible;

    public static function userDetail($user_id) {        
        return self::find($user_id); 
    }  

    public static function checkVerify($id){
        return self::where(['id'=>$id,'verified'=>1,'status'=>'1'])->first();
    }

    public function verified() {
        $this->verified = 1;
        $this->email_token = null;
        $this->register_step = 1;
        $this->status = '1';
        $this->save();
    }

    public function isOnline(){
        return Cache::has('OnlineUsers['.$this->id.']');
    }


    public function sendPasswordResetNotification($token){
       $this->notify(new ResetPassword($token));
    }

    public function countryName(){
       return $this->hasOne('App\CountryDesc', 'country_id', 'country')->where('lang_id', session('default_lang'))->select('country_id','country_name'); 
    }

    public function getStoreInfo(){
        return $this->hasOne('App\CountryDesc', 'country_id', 'country')->where('lang_id', session('default_lang'))->select('country_id','country_name'); 
    }

    public function getCustGroupDesc(){
        //dd(session('default_lang'));
       return $this->hasOne('App\CustomerGroupDesc', 'group_id', 'group_id')->select('id', 'group_id', 'group_name', 'group_desc')->where('lang_id', session('default_lang')); 
    }

}
