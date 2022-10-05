<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
class DeliveryTime extends Model
{
    protected $table = 'delivery_time';
    
    public $timestamps = false;

    public static function getDeliveryTime($delivery_type='buyer_address'){
    	return Self::where('delivery_type',$delivery_type)->first();
    }

    public static function getDeliverYType($shipping_method){
        switch ($shipping_method) {
            case '1':
                $type = 'pickup_center';
                break;
            case '2':
                $type = 'shop_address';
                break;
            case '3':
                $type = 'buyer_address';
                break;
            
            default:
                $type = '';
                break;
        }
        return $type;
    }


    public static function updateDeliveryTime($request){
        if($request->delivery_time_after && $request->prepare_time_before){
            $delivery_obj = \App\DeliveryTime::getDeliveryTime($request->delivery_type);

            if(!$delivery_obj){
                $delivery_obj   = new \App\DeliveryTime;
            }
            if($request->time_slot){
                $slot_array = array_map('intval',array_filter(array_unique($request->time_slot),'strlen'));
                
                sort($slot_array);
            }
            $delivery_obj->delivery_type = $request->delivery_type;
            $delivery_obj->delivery_time_after = $request->delivery_time_after;
            $delivery_obj->prepare_time_before = $request->prepare_time_before;
            $delivery_obj->time_slot = $request->time_slot?implode(',',$slot_array):'';
            $delivery_obj->updated_at = date('Y-m-d-H-i-s');
            $delivery_obj->updated_by = \Auth::guard('admin_user')->user()->id;
            $delivery_obj->save();
            return true;
        }
        
    }
        
}
