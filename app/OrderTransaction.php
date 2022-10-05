<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class OrderTransaction extends Model
{
    protected $table = 'order_transaction';
    public $timestamps = false;
    
    public static function updateOrdTrans($transaction_arr){
        if(isset($transaction_arr['updated_by'])){
            if($transaction_arr['updated_by'] == 'logistic' || $transaction_arr['updated_by'] == 'cron'){
                $updated_by_id = 0;
            }else{
                $updated_by_id = $transaction_arr['updated_by']=='admin'?\Auth::guard('admin_user')->user()->id:\Auth::id();
            }
            
        }else{
            $updated_by_id = 0;
        }

        if(is_null($updated_by_id)){
            $updated_by_id = 0;
        }

        $obj = new OrderTransaction;

        $obj->order_id          = $transaction_arr['order_id'];
        $obj->order_shop_id     = $transaction_arr['order_shop_id'];
        $obj->order_detail_id   = $transaction_arr['order_detail_id'];
        $obj->event             = $transaction_arr['event'];
        $obj->comment           = $transaction_arr['comment'];
        $obj->updated_by        = $transaction_arr['updated_by'];
        $obj->updated_by_id     = $updated_by_id;
        $obj->created_at        = date('Y-m-d H:i:s');
        $obj->save();
    }
}
