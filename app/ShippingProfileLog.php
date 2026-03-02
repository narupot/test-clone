<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class ShippingProfileLog extends Model {

    protected $table = 'shipping_profile_log';  

    public static function updateShippingChangeLog($change_log) {

        $shipping_change_log = new Self;
        if(isset($change_log['shipping_profile_id'])){
            $shipping_change_log->shipping_profile_id = $change_log['shipping_profile_id'];
        }
        if(isset($change_log['shipping_profile_rate_id'])){
            $shipping_change_log->shipping_profile_rate_id = $change_log['shipping_profile_rate_id'];
        }
        
        if(!empty($change_log['update_detail'])){
            $shipping_change_log->update_detail = json_encode($change_log['update_detail'],JSON_UNESCAPED_UNICODE);
        }
        
        if(isset($change_log['comment']))
            $shipping_change_log->comment = $change_log['comment'];
        $shipping_change_log->updated_by = createdBy();
        $shipping_change_log->save();

        return $shipping_change_log->id;
    }
     
}
