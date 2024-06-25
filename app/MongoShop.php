<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Molequent;
class MongoShop extends Molequent
{
    protected $connection = 'mongodb';
    protected $collection = 'shop';
 
    /***if data exist then update other wise update*****/
    public static function updateData($sql_data){
        try{
            $obj = Self::where('_id',$sql_data->id)->first();
        
            if(empty($obj)){
                $obj = new Self;
            }

            $language_arr = getActiveLanguage();

            if(isset($sql_data->shop_category)){
                $shop_category = $sql_data->shop_category;
            }else{
                $shop_category = [];
            }

            $obj->_id           = (int)$sql_data->id;
            $obj->user_id       = (int)$sql_data->user_id;
            $obj->shop_url      = $sql_data->shop_url;
            $name = $desc = [];
            if(!empty($sql_data->allDesc)){
                foreach ($sql_data->allDesc as $key => $value) {
                    if(isset($language_arr[$value->lang_id])){
                        $code = $value->lang_id?'_'.$language_arr[$value->lang_id]:'';
                        $code_val = $language_arr[$value->lang_id];
                        if(!$code){
                            $col_name = 'shop_name'.$code;
                            $obj->$col_name = $value->shop_name;
                            $col_desc = 'description'.$code;
                            $obj->$col_desc = $value->description;
                        }
                        $name[$code_val] = $value->shop_name;
                        $desc[$code_val] = $value->description;
                    }
                }
            }
            $obj->name          = $name;
            $obj->description   = $desc;
            $obj->ph_number     = $sql_data->ph_number;
            $obj->line_link     = $sql_data->line_link;
            $obj->panel_no      = $sql_data->panel_no;
            $obj->citizen_id    = $sql_data->citizen_id;
            $obj->seller_unique_id = $sql_data->seller_unique_id;
            $obj->seller_description = $sql_data->seller_description;
            $obj->market        = $sql_data->market;
            $obj->logo          = $sql_data->logo;
            $obj->banner        = $sql_data->banner;
            $obj->status        = $sql_data->status;
            $obj->shop_status   = $sql_data->shop_status;
            $obj->bargaining    = $sql_data->bargaining;
            $obj->product_pickup_time    = $sql_data->product_pickup_time;
            $obj->center_pickup_time = $sql_data->center_pickup_time;
            $obj->shop_status   = $sql_data->shop_status;
            $obj->open_time     = $sql_data->open_time;
            $obj->close_time    = $sql_data->close_time;
            $obj->map_image     = $sql_data->map_image?explode(',',$sql_data->map_image):'';
            $obj->shop_image    = $sql_data->shop_image?explode(',',$sql_data->shop_image):'';
            $obj->shop_category = $shop_category;
            $obj->created_at    = $sql_data->created_at;
            $obj->updated_at    = $sql_data->updated_at;
            

            $obj->save();
            return ['status'=>'success'];
        }catch(Exception $e){
            return ['status'=>'fail','msg'=>'','error'=>'','qe'=>$e->getMessage()];
        }
        
    }

    public static function updateStatus($id,$status){
        $obj = Self::where('_id',(int)$id)->first();
        if($obj){
            $obj->status = $status;
            $obj->save();
        }
    }

    public static function updateShopColumn($id,$column,$val){
        $obj = Self::where('_id',(int)$id)->first();
        if($obj){
            $obj->$column = $val;
            $obj->save();
        }
    }

    public static function deleteData($unit_id){

        Self::where('_id', (int)$unit_id)->delete();
    }

    public static function getShopClosedId(){
        $cache_key = 'shop_closed_ids';
        $shop_closed_id = [];
        if (cache_hasKey($cache_key) && \Config::get('constants.enable_cache')) {
            $shop_closed_id = cache_getDate($cache_key);
        }
        else{
            $shop_closed_id = Self::where('shop_status','close')->orWhere('status','0')->pluck('_id')->toArray();
            cache_putData($cache_key,$shop_closed_id);
        }
        return $shop_closed_id;
    }

    public static function deleteShopClosedIdCache(){
        $cache_key = 'shop_closed_ids';
        cache_deleteKey($cache_key);
    }
}
