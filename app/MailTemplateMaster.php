<?php

namespace App;

//use Illuminate\Database\Eloquent\SoftDeletes;

class MailTemplateMaster extends User
{
    //use SoftDeletes;
    
    protected $table = 'mail_template_master';
    
    //protected $dates = ['deleted_at'];

    function languageName() {
        
        return $this->hasOne('App\Language', 'id', 'lang_id');
    }    
    
    public static function getMasterTemplate($id=null){

    	if(!empty($id)) {
    		return self::where(['id'=>$id, 'status'=>'1'])->first();
    	}
    	else{
    		return self::where('status', '1')->get();
    	}
    }    
}
