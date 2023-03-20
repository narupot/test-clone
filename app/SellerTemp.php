<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SellerTemp extends Model
{  
    protected $table = 'seller_temp';

    public static function checkShopUrl($shop_url,$user_id){
    	
    	return Self::where('user_id','!=',$user_id)
                ->where('shop_url',$shop_url)
                ->count();
    }

    public static function checkShopName($shop_name,$user_id){
    	
    	return Self::where('user_id','!=',$user_id)
                ->where('shop_name',$shop_name)
                ->count();
    }
    
    public static function checkPanel($panel_no,$user_id){
        
        return Self::where('user_id','!=',$user_id)
                ->where('panel_no',$panel_no)
               ->count();
    }

    public static function checkCitizen($citizen_id,$user_id){
        
        return Self::where('user_id','!=',$user_id)
                ->where('citizen_id',$citizen_id)
               ->count();
    }

    public static function createSeller($user_id){
        $temp_data = Self::where('user_id',$user_id)->first();

        if(!empty($temp_data)){

            try{
                $panel_citizen = \App\SellerData::where(['panel_id'=>$temp_data->panel_no,'citizen_id'=>$temp_data->citizen_id])->first();
                
                /****seller entry******/
                $seller_obj = new Seller;
                $seller_obj->user_id        = $temp_data->user_id;
                $seller_obj->citizen_id     = $temp_data->citizen_id;
                $seller_obj->citizen_id_image = $temp_data->citizen_id_image;
                $seller_obj->bank_id        = $temp_data->bank_id;
                $seller_obj->bank_branch_id = $temp_data->bank_branch_id;
                $seller_obj->account_name   = $temp_data->account_name;
                $seller_obj->account_no     = $temp_data->account_no;
                $seller_obj->branch         = $temp_data->branch;
                $seller_obj->branch_code    = $temp_data->branch_code;
                $seller_obj->account_image  = $temp_data->account_image;
                $seller_obj->status         = '1';
                $seller_obj->save();

                /****creating shop*******/
                $shop_obj = new Shop;
                $shop_obj->user_id          = $temp_data->user_id;
                $shop_obj->shop_url         = $temp_data->shop_url;
                $shop_obj->panel_no         = $temp_data->panel_no;
                $shop_obj->citizen_id       = $temp_data->citizen_id;
                $shop_obj->seller_unique_id = $panel_citizen->seller_unique_id;
                $shop_obj->seller_description = $panel_citizen->description?$panel_citizen->description:'';
                $shop_obj->status           = '1';
                $shop_obj->save();
                $shop_id = $shop_obj->id;

                /****creating shop desc*****/
                $shopdesc_obj = new ShopDesc;
                $shopdesc_obj->shop_id          = $shop_id;
                $shopdesc_obj->lang_id          = 0;
                $shopdesc_obj->shop_name        = $temp_data->shop_name;
                $shopdesc_obj->save();
                return ['status'=>'success'];
            }catch(Exception $e){
                return ['status'=>'fail','msg'=>'','error'=>'','qe'=>$e->getMessage()];
            }
        }else{
            return ['status'=>'fail','msg'=>\Lang::get('common.invalid_user')];
        }
    }
}
