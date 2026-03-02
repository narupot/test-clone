<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class OrdersTemp extends Model
{
    protected $table = 'orders_temp';

    protected $fillable = [
        'formatted_order_id',
        'session_id',
        'user_id',
        'payment_type',
        'payment_slug',
        'pickup_time',
        'user_phone_no',
        'shipping_address_id',
        'billing_address_id',
        'shipping_method',
        'total_core_cost',
        'total_discount',
        'vat',
        'vat_amount',
        'total_shipping_cost',
        'total_logistic_cost',
        'total_final_price',
        'order_date',
        'order_status',
        'checkout_type',
        'kbank_qrcode_id',
        'order_json',
        'noti_60',
        'noti_30',
        'dcc_purchase_discount',
        'dcc_shipping_discount',
        'transaction_fee',
        'del_t_s_id',
        'end_pickup_time',
    ];
    
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

        $sumOfCar = Cart::select(DB::raw('sum(total_price) AS cartPrice'))
        ->where('order_id',$orderId)
        ->where('is_selected',true)
        ->first();

        $sumOfCartPrice = $sumOfCar->cartPrice;
        $total_final_price = $sumOfCartPrice;

        $vatAmt = $vat = 0;
        if($total_final_price <= 0){
            $total_final_price = $vatAmt = 0;
        }
        $affected = Self::where(['id' => $orderId])->update([
            'total_core_cost'=>$sumOfCartPrice,
            'total_final_price' => $total_final_price,
            'vat'=>$vat,
            'vat_amount'=>$vatAmt
        ]);
        return $total_final_price;
    }

    public static function getTempOrderInfo($orderId,$qr_id=null){
        $qry = DB::table(with(new OrdersTemp)->getTable().' as ord')
                ->join(with(new PaymentOption)->getTable().' as popt', 'ord.payment_slug', '=', 'popt.slug')
               ->select('ord.*','popt.id as pay_opt_id');
        if($orderId){
            $qry->where(['ord.id'=>$orderId]);
        }else{
            $qry->where(['ord.kbank_qrcode_id'=>$qr_id]);
        }
        return $qry->first();
    }

    public static function newOrderTemp(array $data): OrdersTemp
    {
        if (empty($data['user_id'])) {
            throw new \InvalidArgumentException('user_id is required to create an order');
        }
        $order = new self;
        $order->user_id = $data['user_id'] ?? null;
        $order->session_id = Session::getId();
        $order->save();

        $orderId = $order->id;
        $orderIdStr = str_pad($orderId, 6, '0', STR_PAD_LEFT);

        $formattedOrderId = date('d')
        . substr($orderIdStr, 0, -4)
        . date('m')
        . substr($orderIdStr, -4, -2)
        . date('y')
        . substr($orderIdStr, -2);

        $check = self::where('formatted_order_id', $formattedOrderId)->first();
        if ($check) {
            $formattedOrderId = generateUniqueNo();
        }

        $order->formatted_order_id = $formattedOrderId;
        $order->save();

        return $order;
    }

}
