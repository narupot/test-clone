<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

use Auth;

class PushEmailNotification extends Model
{  
    //use SoftDeletes;

    protected $table = 'push_email_notification';
    protected $guarded = [];
    
    //public $timestamps = false;


    public function getAllList(){
       // return $this->hasOne('App\ProductDesc','product_id','id')
       //        ->select('product_id','name','description','lang_id', 'meta_title', 'meta_keyword', 'meta_description')
       //        ->where('lang_id', session('default_lang'));
    } 

}
