<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Cart extends Model
{  
    protected $table = 'cart';

    public function getPrd(){ 
       return $this->hasOne('App\Product','id','product_id')
              ->select('id','thumbnail_image','sku','unit_price','stock','quantity','package_id','base_unit_id','badge_id','weight_per_unit','updated_at','status');
    }

    public static function getCartList($order_id,$statusArr=[]){
        
        $qry = Self::with(['getPrd','getShop','getShopDesc','getCat','getCatDesc']);
        if(count($statusArr)){
            $qry->whereIn('cart_status',$statusArr);
        }
        $res = $qry->where('order_id',$order_id)->get();
        return $res;
    }

    public function getCat(){
        return $this->hasOne('App\Category', 'id', 'cat_id')->select('id','url'); 
    }

    public function getCatDesc(){

        return $this->hasOne('App\CategoryDesc', 'cat_id', 'cat_id')->where('lang_id', session('default_lang'))->select('category_name','cat_id'); 
    }

    public function getShop(){
        return $this->hasOne('App\Shop', 'id', 'shop_id')->select('id','shop_url','logo','panel_no','market','shop_status','ph_number'); 
    }

    public function getShopDesc(){
        return $this->hasOne('App\ShopDesc', 'shop_id', 'shop_id')->where('lang_id', session('default_lang'))->select('shop_id','shop_name'); 
    }

    public static function getShipProfile()
    {
    	$result = ShippingProfile::select('id','province_mode','product_mode','rest_country','shipping_type','free_order_amount')->where('status','1')->with('getShippingProfileDesc')->get()->toArray();
    	return $result;
    }


    public static function getBankDetails(){
      $qry = DB::table(with(new PaymentBank)->getTable().' as pb')
                ->join(with(new PaymentBankDesc)->getTable().' as pbd', 'pb.id', '=', 'pbd.payment_bank_id')
               ->select('pb.id','pb.branch','pb.account_name','pb.account_type','pb.account_no','pbd.bank_name','pb.bank_image')
                ->where(['pb.status'=>'1','pbd.lang_id'=>session('default_lang')])
                 ->get();
                 return $qry;
    }

    public static function getPaymentOpt($currency_id)
    {
        return DB::table(with(new PaymentOption)->getTable().' as po')    
                ->join(with(new PaymentOptionDesc)->getTable().' as pod', 'po.id', '=', 'pod.payment_option_id')    
               ->select('po.*', 'pod.payment_option_name')
                ->where(['po.status'=>'1', 'pod.lang_id'=>session('default_lang')])
                ->whereRaw('FIND_IN_SET('.$currency_id.',currency_id)')
                 ->get();
    }

    public static function getTotCartPrdNoti(){
        $userid = \Auth::User()->id;
        $totPaidPrd = $totCartPrd = $totBargainPrd = 0;
        $formatted_order_id = OrdersTemp::where('user_id',$userid)->value('formatted_order_id');
        $order_id = Order::whereNull('end_shopping_date')->where('user_id',$userid)->value('id');
        if($order_id){
            $totPaidPrd = OrderDetail::where(['user_id'=>$userid,'payment_status'=>'1','order_id'=>$order_id])->count();
        }

        if($formatted_order_id){

            $totCartPrd = Self::totCartPrd($userid);
        }
        $totBargainPrd = \DB::table(with(new ProductBargain)->getTable().' as pb')
                ->join(with(new Product)->getTable().' as p', 'p.id', '=', 'pb.product_id')
                ->where(['p.status'=>'1','pb.user_id'=>$userid])
                 ->count();

        $tot = $totCartPrd + $totPaidPrd + $totBargainPrd;

        return ['tot'=>$tot,'cart_prd'=>$totCartPrd,'paid_prd'=>$totPaidPrd,'bargain_prd'=>$totBargainPrd];
    }

    public static function totCartPrd($user_id){
        return Self::where('user_id',$user_id)->count();
    }

    public static function manageBargain($user_id,$product_id){
        $check_cart = Self::where(['user_id'=>$user_id,'product_id'=>$product_id])->first();
        if(!empty($check_cart)){
            $temp_ord_id = $check_cart->order_id;
            $check_cart->delete();

            $check_remain_cart = Self::where('order_id',$temp_ord_id)->count();

            if($check_remain_cart){
                /****update order price*****/
                $update = OrdersTemp::updateOrderPrice($temp_ord_id);
            }else{
                $temp_formatted_id = OrdersTemp::where('id', $temp_ord_id)->value('formatted_order_id');
                if($temp_formatted_id){
                    $check_ord = \App\Order::where('temp_formatted_id',$temp_formatted_id)->count();
                    if(empty($check_ord)){
                        OrdersTemp::where('id', $temp_ord_id)->delete();  /**delete order***/
                    }
                }
            }
        }
    }

}
