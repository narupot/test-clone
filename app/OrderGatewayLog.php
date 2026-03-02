<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\GeneralFunctions; 
use DB;
class OrderGatewayLog extends Model
{
    protected $table = 'order_gateway_log';

    public static function insertLog($resp){

        $logObj = new OrderGatewayLog;
        $logObj->gateway_type = $resp['gateway_type'];
        $logObj->gateway_response = $resp['gateway_response'];
        if(isset($resp['gateway_response_two'])){
            $logObj->gateway_response_two = $resp['gateway_response_two'];
        }
        $logObj->save();
    
        $log_id = $logObj->id;
        return $log_id;
    }

    
}
