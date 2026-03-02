<?php

namespace App\Http\Controllers\Checkout;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\MarketPlace;
use App\Helpers\GeneralFunctions;
use App\Helpers\CustomHelpers;
use Illuminate\Http\Request;
use App\Order;
use App\OrderDetail;
use App\OrderShop;
use Hash;
use DateTime;
use DB;
use Auth;
use Lang;
use Config;
use Session;
use Exception;

class TrackOrderController extends MarketPlace
{   
    public function __construct() {
        
    }     

    public function trackOrder(Request $request){
        return view('checkout.track_order');
    }


    public function trackOrderDetail(Request $request,$ord_id=null){

        if(empty($ord_id)){
            return view('checkout.track_order_detail');
        }
        
        $main_order = [];
        $order_detail = [];
        $shop_order = [];
        $main_order = Order::where(['formatted_id'=>$ord_id])->first();
        // dd($main_order,$formatted_id);
        if(empty($main_order)){
          return view('checkout.track_order_detail');
        }
        if(!empty($main_order)){
            $order_detail = OrderDetail::getMainOrderDetail($main_order->id);
            $shop_ord = \App\OrderShop::where('order_id',$main_order->id)->select('id','shop_formatted_id','order_status')->with('getOrderStatus')->get();
            if(count($shop_ord)){
                foreach ($shop_ord as $key => $value) {
                    $status = $value->getOrderStatus->status ?? '';
                    $shop_order[$value->id] = ['shop_formatted_id'=>$value->shop_formatted_id,'status'=>$status,'order_status'=>$value->order_status];
                }
            }
            
        }

        return view('checkout.track_order_detail',['main_order' => $main_order,'order_detail'=>$order_detail,'shop_order'=>$shop_order]);
    }

    
}