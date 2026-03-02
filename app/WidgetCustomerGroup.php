<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class WidgetCustomerGroup extends Model {

    protected $table = 'widget_customer_group';

    public $timestamps = false; 

    public static function insertBlockGroup($data_arr, $widget_id) {      
            $custId = implode(',', $data_arr);
            $group = new WidgetCustomerGroup;
            $group->widget_id = $widget_id;
            $group->customer_group_id = $custId;
            $group->save();                   
    }

    public static function updateBlockGroup($data_arr, $widget_id) {      
            $countblock = Self::where('widget_id',$widget_id)->count();
            
            if($countblock > 0){
                $custId = implode(',', $data_arr);
                self::where(['widget_id'=>$widget_id])
                ->update(['customer_group_id' => $custId]);
            }else{
                Self::insertBlockGroup($data_arr, $widget_id);
            }
    }
    
    public static function checkGroup($widget_id,$group_id){
        return Self::whereRaw('FIND_IN_SET('.$group_id.',customer_group_id)')->where('widget_id',$widget_id)->count();
    }                    
}
