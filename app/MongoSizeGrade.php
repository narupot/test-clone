<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Molequent;
class MongoSizeGrade extends Molequent
{
    protected $connection = 'mongodb';
    protected $collection = 'size_grade';
 
    /***if data exist then update other wise update*****/
    public static function updateData($sql_data){
        $obj = Self::where('_id',$sql_data->id)->first();
        $language_arr = getActiveLanguage();
        if(empty($obj)){
            $obj = new Self;
        }

        $obj->_id = (int)$sql_data->id;
        $obj->slug = $sql_data->slug;
        $obj->type = $sql_data->type;
        $name = [];
        if(!empty($sql_data->sizegradedescAll)){
            foreach ($sql_data->sizegradedescAll as $key => $value) {
                if(isset($language_arr[$value->lang_id])){
                    $code = $value->lang_id?'_'.$language_arr[$value->lang_id]:'';
                    $code_val = $language_arr[$value->lang_id];
                    $col_name = 'name'.$code;
                    if(!$code)
                        $obj->$col_name = $value->name;

                    $name[$code_val] = $value->name;
                }
            }
        }
        $obj->size_grade_name = $name;
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

    public static function getAllSizeGrade($slug=null){
        $cache_key = 'size_grade';
        $size_grade_arr = [];
        if (cache_hasKey($cache_key) && \Config::get('constants.enable_cache')) {
            $size_grade_arr = cache_getDate($cache_key);
        }
        if(empty($size_grade_arr)){
            $unit = Self::get();
            if(count($unit)){
                foreach ($unit as $key => $value) {
                    $size_grade_arr[$value->slug] = $value;
                }
                cache_putData($cache_key,$size_grade_arr);
            }
        }
        if($slug){
            return isset($size_grade_arr[$slug]) ? $size_grade_arr[$slug] : [];
        }
        return $size_grade_arr;
    }

    public static function getSize(){
        $data = Self::getAllSizeGrade();
        $size_arr = [];
        foreach ($data as $key => $value) {
            if($value->type=='size'){
                $size_arr[$value->slug] = $value->name;
            }
        }
        return $size_arr;
    }

    public static function getGrade(){
        $data = Self::getAllSizeGrade();
        $grade_arr = [];
        foreach ($data as $key => $value) {
            if($value->type=='grade'){
                $grade_arr[$value->slug] = $value->name;
            }
        }
        return $grade_arr;
    }
}
