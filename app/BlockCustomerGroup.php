<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class BlockCustomerGroup extends Model {

    protected $table = 'block_customer_group';

    public $timestamps = false; 

    public static function insertBlockGroup($data_arr, $block_id) {      
            $custId = implode(',', $data_arr);
            $group = new BlockCustomerGroup;
            $group->block_id = $block_id;
            $group->customer_group_id = $custId;
            $group->save();                   
    }

    public static function updateBlockGroup($data_arr, $block_id) {      
            $countblock = Self::where('block_id',$block_id)->count();
            
            if($countblock > 0){
                $custId = implode(',', $data_arr);
                self::where(['block_id'=>$block_id])
                ->update(['customer_group_id' => $custId]);
            }else{
                Self::insertBlockGroup($data_arr, $block_id);
            }
    }
    
    public static function checkGroup($block_id,$group_id){
        return Self::whereRaw('FIND_IN_SET('.$group_id.',customer_group_id)')->where('block_id',$block_id)->count();
    }                    
}
