<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Molequent;
class MongoCategory extends Molequent
{
    protected $connection = 'mongodb';
    protected $collection = 'category';
 
    /***if data exist then update other wise update*****/
    public static function updateData($sql_data){


        $obj = Self::where('_id',$sql_data->id)->first();
        $language_arr = getActiveLanguage();
        if(empty($obj)){
            $obj = new Self;
        }

        $obj->_id = (int)$sql_data->id;
        $obj->url = $sql_data->url;
        $obj->parent_id = $sql_data->parent_id;
        $obj->img = $sql_data->img;
        $obj->comment = $sql_data->comment;
        $name = $desc = $meta_title = $meta_keyword = $meta_description = [];
        if(!empty($sql_data->descAll)){
            foreach ($sql_data->descAll as $key => $value) {
                if(isset($language_arr[$value->lang_id])){
                    $code = $value->lang_id?'_'.$language_arr[$value->lang_id]:'';
                    $code_val = $language_arr[$value->lang_id];
                    $col_name = 'category_name'.$code;
                    $col_desc = 'cat_description'.$code;
                    if(!$code){
                        $obj->$col_name = $value->category_name;
                        $obj->$col_desc = $value->cat_description;
                    }
                    
                    /******json object*******/
                    $name[$code_val] = $value->category_name;
                    $desc[$code_val] = $value->cat_description;
                    $meta_title[$code_val] = $value->meta_title;
                    $meta_keyword[$code_val] = $value->meta_keyword;
                    $meta_description[$code_val] = $value->meta_description;

                }
            }
            
            $obj->name          = $name;
            $obj->description   = $desc;
            $obj->meta_title    = $meta_title;
            $obj->meta_keyword  = $meta_keyword;
            $obj->meta_description = $meta_description;
        }
        $units = [];
        if(!empty($sql_data->Units)){
          foreach($sql_data->Units as $key => $value){
            $units[] = $value['unit_id'];
          } 
        }

        $obj->units = count($units)>0?json_encode($units):'';
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

    public static function getParentCategories(){
        return Self::where('parent_id',0)->where('status','1')->select('url','category_name')->get();
    } 

    public static function getSubCat($parent_cat_ids=null,$id_arr=null){
        $data = Self::where('status','1');
        if($parent_cat_ids){
            $data->whereIn('parent_id',$parent_cat_ids);
        }
        if($id_arr){
            $data->whereIn('_id',$id_arr);
        }
        $result = $data->get();

        return $result;
    }
}
