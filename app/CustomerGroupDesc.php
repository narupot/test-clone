<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class CustomerGroupDesc extends Model
{
    protected $table = 'customer_group_desc';

    public $timestamps = false;      

    public static function insertGroupDesc($data_arr, $group_id) {      

        foreach ($data_arr as $key=>$value){

            $Group_desc = new CustomerGroupDesc;
            $Group_desc->group_id = $group_id;
            $Group_desc->lang_id = $key;
            $Group_desc->group_name = $value['group_name'];
            $Group_desc->group_desc = $value['group_desc'];               
            $Group_desc->save();                   
        }
    }

    public static function updateGroupDesc($data_arr, $group_id) {      

        foreach ($data_arr as $key=>$value){

			self::where(['group_id'=>$group_id, 'lang_id'=>$key])
				->update(['group_name' => $value['group_name'], 'group_desc' => $value['group_desc']]);                  
        }
    }
}
