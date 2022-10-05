<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Molequent;
class MongoPackage extends Molequent
{
    protected $connection = 'mongodb';
    protected $collection = 'package';
 
    /***if data exist then update other wise update*****/
    public static function updateData($sql_data){
        
        $obj = Self::where('_id',$sql_data->id)->first();
        $language_arr = getActiveLanguage();

        if(empty($obj)){
            $obj = new Self;
        }

        $obj->_id = (int)$sql_data->id;
        $obj->title = $sql_data->title;
        $obj->height = $sql_data->height;
        $obj->width = $sql_data->width;
        $obj->depth = $sql_data->depth;
        $name = [];
        if(!empty($sql_data->packagedescAll)){
            foreach ($sql_data->packagedescAll as $key => $value) {
                if(isset($language_arr[$value->lang_id])){
                    $code = $value->lang_id?"_".$language_arr[$value->lang_id]:'';
                    $code_val = $language_arr[$value->lang_id];
                    $col_name = 'package_name'.$code;
                    if(!$code)
                        $obj->$col_name = $value->package_name;

                    $name[$code_val] = $value->package_name;
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
        $obj = Self::find((int)$id);
        if($obj){
            $obj->status = $status;
            $obj->save();
        }
    }

    public static function deleteData($package_id){

        Self::where('_id', (int)$package_id)->delete();
    }

    public static function getAllPackage(){
        $cache_key = 'product_package';
        $package_arr = [];
        if (cache_hasKey($cache_key) && \Config::get('constants.enable_cache')) {
            $package_arr = cache_getDate($cache_key);
        }
        if(empty($package_arr)){
            $package = Self::get();
            if(count($package)){
                foreach ($package as $key => $value) {
                    $package_arr[$value->id] = $value;
                }
                cache_putData($cache_key,$package_arr);
            }
        }
        return $package_arr;
    }
}
