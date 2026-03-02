<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;

class OrderStatusDesc extends Model
{   
    protected $table = 'order_status_desc';
    protected $guarded = [];
    public $timestamps = false;      

    public static function getStatusVal($status_id){
    	$lang_id = session('default_lang')??0;
    	return Self::where(['lang_id'=>$lang_id,'order_status_id'=>$status_id])->value('status');
    }
}
