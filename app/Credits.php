<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 
 */
class Credits extends Model
{
	protected $table = "credits";

    public function getShops(){
        return $this->hasOne('App\Shop', 'id', 'shop_id');  
    }

    public function getUser(){
        return $this->hasOne('App\User', 'id', 'user_id');  
    }

    public static function getUserCredit($userid){
    	$data = Self::select(\DB::raw('sum(remaining_amount) AS remain_credit,sum(credited_amount) as tot_credit'),'shop_id')->where(['seller_approval'=>'Approved','user_id'=>$userid])->groupBy('shop_id')->get();
        
    	$credit_data = [];
    	if(count($data)){
    		foreach ($data as $key => $value) {
    			$value->tot_remain_credit = $value->remain_credit;
    			$credit_data[$value->shop_id] = $value;
    		}
    	}
    	return $credit_data;
    }
}