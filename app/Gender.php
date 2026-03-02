<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Gender extends Model {

    protected $table = 'gender';  

    public function genderDesc() {       
        return $this->hasOne('App\GenderDesc', 'gender_id', 'id')->where('lang_id', session('admin_default_lang'));
    } 

    public static function getGender(){
    	return self::select('*')->with('genderDesc')->orderBy('created_at','desc')->get();
    }

    public static function getGenderbyId($id){
        return self::select('*')->where(['id'=>$id])->with('genderDesc')->first();
    }   
            
}
