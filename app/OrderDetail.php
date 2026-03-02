<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\GeneralFunctions;
use App\Helpers\CustomHelpers;
use DB;
class OrderDetail extends Model
{
    protected $table = 'order_detail';

    public static function createOrderDetail($main_order_id,$shop_order_id,$orderInfo,$detail,$shop_data){

    	$current_date = currentDateTime();
    	
    	$product_detail_arr = $package_name_arr = $badge_arr = $pay_name_arr = [];
        $cat_name = $sku = $package_name = $base_unit = '';
        $weight_per_unit = 0;
        $product_detail_arr = $shop_data;
        
        if(!empty($detail->getPrd)){

            /*****updating order quantity*******/
            if($detail->getPrd->stock == 0) {

                $stock_data['shop_id'] = $detail->shop_id;
                $stock_data['product_id'] = $detail->product_id;
                $stock_data['qty'] = $detail->quantity;
                $stock_data['type'] = 'sold';
                $stock_data['channel'] = '2';

                \App\ProductStockMemo::updateProductStock($stock_data);
            }

            /*****getting product detail json******/
            $product_detail_arr = [
                'sku'=>$detail->getPrd->sku,
                'thumbnail_image'=>$detail->getPrd->thumbnail_image,
                'stock'=>$detail->getPrd->stock
            ];
            $sku = $detail->getPrd->sku;
            $weight_per_unit = $detail->getPrd->weight_per_unit;

            $badge_data = \App\Badge::where('id',$detail->getPrd->badge_id)->first();
            if(!empty($badge_data)){
                $badge_arr = ['title'=>$badge_data->title,'size'=>CustomHelpers::getBadgeSize($badge_data->size),'grade'=>CustomHelpers::getBadgeGrade($badge_data->grade),'icon'=>$badge_data->icon];
            }
            
            $package_data = \App\PackageDesc::where('package_id',$detail->getPrd->package_id)->get();
            if(count($package_data)){
                foreach ($package_data as $ukey => $uvalue) {
                    $package_name_arr[$uvalue->lang_id] = $uvalue->package_name;
                    if($uvalue->lang_id == 0){
                        $package_name = $uvalue->package_name;
                    }
                }
            }

            $base_unit = \App\Unit::where('id',$detail->getPrd->base_unit_id)->value('title');
        }

        $name_arr = [];
        $cat_name_data  = \App\CategoryDesc::where('cat_id',$detail->cat_id)->get();
        if(count($cat_name_data)){
            foreach ($cat_name_data as $key => $value) {
                $name_arr[$value->lang_id]=$value->category_name;
                if($value->lang_id == 0){
                    $cat_name = $value->category_name;
                }
            }
        }

        $payment_data = \App\PaymentOptionDesc::where('payment_option_id',$orderInfo->pay_opt_id)->get();
        if(count($payment_data)){
            foreach ($payment_data as $key => $value) {
                $pay_name_arr[$value->lang_id]=$value->payment_option_name;
            }
        }
        if($detail->getCat){
            $product_detail_arr['cat_url'] = $detail->getCat->url;
        }

        $description_arr = []; $description = '';
        $description_data  = \App\ProductDesc::where('product_id',$detail->product_id)->get();
        if(count($description_data)){
            foreach ($description_data as $key => $value) {
                $description_arr[$value->lang_id]=$value->description;
                if($value->lang_id == 0){
                    $description = $value->description;
                }
            }
        }
        
        $product_detail_arr['name'] = $name_arr;
        $product_detail_arr['package'] = $package_name_arr;
        $product_detail_arr['badge'] = $badge_arr;
        $product_detail_arr['payment_method'] = $pay_name_arr;
        $product_detail_arr['description'] = $description_arr;
        $product_detail_arr = array_merge($product_detail_arr,$shop_data);
        
        $order_detail = new OrderDetail;
        $order_detail->user_id          = $orderInfo->user_id;
        $order_detail->shop_id          = $detail->shop_id;
        $order_detail->order_id         = $main_order_id;
        $order_detail->order_shop_id    = $shop_order_id;
        $order_detail->product_id       = $detail->product_id;
        $order_detail->cat_id           = $detail->cat_id;
        $order_detail->total_weight     = $weight_per_unit;
        $order_detail->category_name    = $cat_name;
        $order_detail->package_name     = $package_name;
        $order_detail->description     =  $description;
        $order_detail->base_unit        = $base_unit;
        $order_detail->sku              = $sku;
        $order_detail->quantity         = $detail->quantity;
        $order_detail->original_price   = $detail->original_price;
        $order_detail->last_price       = $detail->cart_price;
        $order_detail->total_price      = $detail->total_price;
        $order_detail->payment_type     = $orderInfo->payment_type;
        $order_detail->payment_slug     = $orderInfo->payment_slug;
        
        $order_detail->order_detail_json = jsonEncode($product_detail_arr);
        $order_detail->status           = 1;
        $order_detail->save();
        $order_detail_id = $order_detail->id;
        
        return $order_detail_id;
        /****deduct credit amount*******/
        /*if($orderInfo->payment_type == 'credit'){
            $shop_credit_info = \App\Credits::where(['user_id'=>$orderInfo->user_id,'shop_id'=>$detail->shop_id,'seller_approval'=>'Approved'])->first();
            if(!empty($shop_credit_info)){
                $used_amount = $shop_credit_info->used_amount + $detail->total_price;
                $remain_amt = $shop_credit_info->credited_amount - $used_amount;

                $shop_credit_info->used_amount = $used_amount;
                $shop_credit_info->remaining_amount = $remain_amt;
                if($shop_credit_info->amount_paid=='1'){
                    $shop_credit_info->amount_paid = '0';
                }
                $shop_credit_info->save();
            }
        }*/

        /****update entry in order transaction******/
        /*$comment = GeneralFunctions::getOrderText('item_payment_done',$cat_name,$orderInfo->payment_slug);
        $transaction_arr = ['order_id'=>$main_order_id,'order_shop_id'=>$shop_order_id,'order_detail_id'=>$order_detail_id,'event'=>'order','comment'=>$comment,'updated_by'=>'buyer'];

        $update_transaction = \App\OrderTransaction::updateOrdTrans($transaction_arr);*/
        
    }

    public static function getMainOrderDetail($main_order_id){
        return Self::where('order_id',$main_order_id)->orderBy('order_shop_id')->with('getOrderStatus')->get();
    }

    public function getPackage(){
         return $this->hasOne('App\Package','id','unit_id')
              ->select('height','width','depth','id')->get();
    }

    public static function getShopOrderDetail($user_id=null,$order_shop_id=null,$order_id=null){
        $result = Self::select('*');
        if($user_id){
            $result = Self::where(['user_id'=>$user_id]);
        }
        if($order_shop_id!=null){
            $result->where(['order_shop_id'=>$order_shop_id]);
        }

        if($order_id!=null){
            $result->where(['order_id'=>$order_id]);
        }

        return $result->with(['getOrderStatus'])->get();
                    
    }

    public function getPrd(){
       return $this->hasOne('App\Product','id','product_id')
              ->select('id','thumbnail_image','sku','unit_price','stock','quantity','package_id','badge_id','status','min_order_qty');
    }

    public function getCat(){
        return $this->hasOne('App\Category', 'id', 'cat_id')->select('id','url','img');
    }

    public function getCatDesc(){
        return $this->hasOne('App\CategoryDesc', 'cat_id', 'cat_id');
    }

    public function getShop(){
        return $this->hasOne('App\Shop', 'id', 'shop_id')->select('id','shop_url','logo','panel_no','market','ph_number','status','shop_status');
    }

    public function getShopDesc(){
        return $this->hasOne('App\ShopDesc', 'shop_id', 'shop_id')->where('lang_id', session('default_lang'))->select('shop_id','shop_name');
    }

    public function product(){
        return $this->hasOne('App\Product','id','product_id');
    }

    public function shop(){
        return $this->hasOne('App\Shop','id','shop_id');
    }

    public function order(){
        return $this->belongsTo('App\Order','order_id','id');
    }

    public function review(){
        return $this->hasMany('App\Review','order_id','order_id');
    }

    public function getOrderStatus(){
        return $this->hasOne('App\OrderStatusDesc', 'order_status_id', 'status')->where('lang_id', session('default_lang')); 
    }


    public function orderShop(){
        return $this->hasOne('App\OrderShop','id','order_shop_id');
    }


}
