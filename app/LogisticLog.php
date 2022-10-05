<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class LogisticLog extends Model {

    protected $table = 'logistic_log';         

    public static function insertLog($order_id,$resp,$json) {
    	$obj = new LogisticLog;
    	$obj->order_id = $order_id;
    	$obj->logistic_response = $resp;
    	$obj->api_json = $json;
    	$obj->save();
    }
}
