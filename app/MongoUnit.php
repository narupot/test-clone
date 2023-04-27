<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Molequent;
class MongoUnit extends Molequent
{
    protected $connection = 'mongodb';
    protected $collection = 'unit';
 
    /***if data exist then update other wise update*****/
    public static function updateData($sql_data){
        $obj = Self::where('_id',$sql_data->id)->first();
        $language_arr = getActiveLanguage();
        if(empty($obj)){
            $obj = new Self;
        }

        $obj->_id = (int)$sql_data->id;
        $obj->title = $sql_data->title;
        $name = [];
        if(!empty($sql_data->unitdescAll)){
            foreach ($sql_data->unitdescAll as $key => $value) {
                if(isset($language_arr[$value->lang_id])){
                    $code = $value->lang_id?'_'.$language_arr[$value->lang_id]:'';
                    $code_val = $language_arr[$value->lang_id];
                    $col_name = 'unit_name'.$code;
                    if(!$code)
                        $obj->$col_name = $value->unit_name;

                    $name[$code_val] = $value->unit_name;
                }
            }
        }
        $obj->name       = $name;
        $obj->status = $sql_data->status;
        $obj->created_at = $sql_data->created_at;
        $obj->updated_at = $sql_data->updated_at;
        $obj->save();
    }

    public static function updateStatus($id,$status){
        $obj = Self::where('_id',(int)$id)->first();
        if($obj){
            $obj->status = $status;
            $obj->save();
        }
    }

    public static function deleteData($unit_id){

        Self::where('_id', (int)$unit_id)->delete();
    }

    public static function getAllUnit($id=null){
        $cache_key = 'product_unit';
        $unit_arr = [];
        if (cache_hasKey($cache_key) && \Config::get('constants.enable_cache')) {
            $unit_arr = cache_getDate($cache_key);
        }
        if(empty($unit_arr)){
            $unit = Self::get();
            if(count($unit)){
                foreach ($unit as $key => $value) {
                    $unit_arr[$value->id] = $value;
                }
                cache_putData($cache_key,$unit_arr);
            }
        }
        if($id){
            return isset($unit_arr[$id]) ? $unit_arr[$id] : [];
        }
        return $unit_arr;
    }
}
