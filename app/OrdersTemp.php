<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class OrdersTemp extends Model
{
    protected $table = 'orders_temp';


    public function getCart(){
      	return $this->hasMany('App\Cart','order_id','id')->orderBy('id','DESC');
    }

    public function getCurrency(){
        return $this->hasOne('App\Currency','id','currency_id')->select('id','currency_code');
        
    }

    public function getCartQuantity(){
        return $this->hasOne('App\Cart','order_id','id')->select('id','order_id',DB::raw('COUNT(*) as cart_item,sum(quantity) as totQty'))->groupBy('order_id');
        
    }

    public function expireDays(){
        return $this->hasOne('App\Shop','id','shop_id')->select('id','make_offer_expire');
        
    }

    public function getSellerVat(){
        return $this->hasOne('App\Shop','id','shop_id')->select('id','vat');
        
    }
 
    public static function updateOrderPrice($orderId){

        $sumOfCar = Cart::select(DB::raw('sum(total_price) AS cartPrice'))->where('order_id',$orderId)->first();

        $sumOfCartPrice = $sumOfCar->cartPrice;

        $total_final_price = $sumOfCartPrice;

        $vatAmt = $vat = 0;
        if($total_final_price > 0){

        }else{
            $total_final_price = $vatAmt = 0;
        }
    
        $affected = Self::where(['id' => $orderId])->update(['total_core_cost'=>$sumOfCartPrice,'total_final_price' => $total_final_price,'vat'=>$vat,'vat_amount'=>$vatAmt]);
  
        return $total_final_price;
    }

    public static function getTempOrderInfo($orderId,$qr_id=null){
        $qry = DB::table(with(new OrdersTemp)->getTable().' as ord')  
                ->join(with(new PaymentOption)->getTable().' as popt', 'ord.payment_slug', '=', 'popt.slug')
               ->select('ord.*','popt.id as pay_opt_id');
                if($orderId)
                    $qry->where(['ord.id'=>$orderId]);
                else
                    $qry->where(['ord.kbank_qrcode_id'=>$qr_id]);

        $res = $qry->first();
        return $res; 
    }

}
