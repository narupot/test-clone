<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Molequent;
use Auth;
class MongoProduct extends Molequent
{
    protected $connection = 'mongodb';
    protected $collection = 'product';
 
    /***if data exist then update other wise update*****/
    public static function updateData($sql_data){
        try{
            $obj = Self::where('_id',$sql_data->id)->first();
            
            if(empty($obj)){
                $obj = new Self;
                $obj->avg_star = 0;
            }
            //$language_arr = getActiveLanguage();

            $obj->_id = $sql_data->id;
            $obj->shop_id = (int)$sql_data->shop_id;
            $obj->url = $sql_data->url;

            /*if(!empty($sql_data->allDesc)){
                foreach ($sql_data->allDesc as $key => $value) {
                    if(isset($language_arr[$value->lang_id])){
                        $code = $value->lang_id?'_'.$language_arr[$value->lang_id]:'';
                        $col_name = 'shop_name'.$code;
                        $obj->$col_name = $value->shop_name;
                        $col_desc = 'description'.$code;
                        $obj->$col_desc = $value->description;
                    }
                }
            }*/

            $obj->sku = $sql_data->sku;
            $obj->cat_id = (int)$sql_data->cat_id;
            $obj->badge_id = (int)$sql_data->badge_id;
            $obj->show_price = $sql_data->show_price;
            $obj->unit_price = (float)$sql_data->unit_price;
            
            $obj->stock   = !empty($sql_data->stock)?$sql_data->stock:'0';
            $obj->quantity = (int)$sql_data->quantity;
            $obj->order_qty_limit    = $sql_data->order_qty_limit;
            $obj->min_order_qty = $sql_data->min_order_qty;
            $obj->thumbnail_image = $sql_data->thumbnail_image;

            $obj->is_tier_price   = $sql_data->is_tier_price;
            $obj->package_id = (int)$sql_data->package_id;
            $obj->base_unit_id = $sql_data->base_unit_id;
            $obj->weight_per_unit = $sql_data->weight_per_unit;
            

            

            $obj->status = $sql_data->status;
            $obj->created_at = $sql_data->created_at;
            $obj->updated_at = $sql_data->updated_at;
            $obj->created_by = $sql_data->created_by;
            $obj->updated_by = $sql_data->updated_by;

            

            $obj->created_from = $sql_data->created_from;
            $obj->updated_from = $sql_data->updated_from;

            $obj->description    = $sql_data->description;
            if($sql_data->image){
               $obj->image    = $sql_data->image;   
            }
            if($sql_data->tier_price_data){
               $obj->tier_price_data    = $sql_data->tier_price_data;
            }   
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
    public static function updateStock($id,$status){
        $obj = Self::where('_id',(int)$id)->first();
        if($obj){
            $obj->stock = $status;
            $obj->save();
        }
    }
    public static function updatePrice($id,$unit_price){
        $obj = Self::where('_id',(int)$id)->first();
        if($obj){
            $obj->unit_price = (float)$unit_price;
            $obj->save();
        }
    }



    public static function deleteData($unit_id){
        Self::where('_id', (int)$unit_id)->delete();
    }

    public function shop(){
        return $this->hasOne('App\MongoShop', '_id', 'shop_id')->select('shop_name','shop_url');
    }

    

    public function badge(){
        return $this->hasOne('App\MongoBadge', '_id', 'badge_id')->select('badge_name','badge_name_de','grade','icon','size','title');
    }

    public function unit(){
        return $this->hasOne('App\MongoUnit','_id','unit_id');
    }

    public function category(){
        return $this->hasOne('App\MongoCategory','_id','cat_id')->select('category_name','url');
    }

    public function wishlist(){
        return $this->hasMany('App\MongoWishlist','product_id','_id')->where('user_id',Auth::id());
    }

    // public function package(){
    //     return $this->hasOne('App\MongoPackage','_id','package_id')->select(''); 
    // }

    public static function updatePrdQunatity($id,$quantity){
        $obj = Self::where('_id',(int)$id)->first();
        if($obj){
            $obj->quantity = (int)$quantity;
            $obj->save();
        }
    }

    public static function totProductOfShop($shop_id){
        return Self::where(['shop_id'=>(int)$shop_id,'status'=>'1'])->count();
    }

    public static function avgPriceProduct(){
        $category_ids = [32];
        $badge_id = [17];
        $my_qry = \App\MongoProduct::where(['cat_id'=>32,'badge_id'=>17])->select('unit_price')->get()->toArray();
        
        $pp = \App\MongoProduct::raw(function($collection) use ($category_ids,$badge_id)
        {
        return $collection->aggregate([
                     [
                        '$match' => [
                            'cat_id' => ['$in' => $category_ids],
                            'badge_id' => ['$in' => $badge_id],
                        ],     
                    ],
                    [
                        '$group' => [
                            '_id' =>[
                                'cat_id'=>'$cat_id',
                                'badge_id'=>'$badge_id'
                            ],
                            'unit_price'=>[
                                '$avg'=> '$unit_price'
                            ]
                        ],
                        
                    ],
                    [   
                        '$sort' => ['unit_price' => -1]   
                    ], 
                    
                ]);
            })->toArray();
            dd($pp);
            foreach ($pp as $key => $value) {
                dd($value['unit_price'],(array)$value['_id']);
            }
    }
}
