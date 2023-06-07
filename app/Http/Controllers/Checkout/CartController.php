<?php  
 
namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\MarketPlace;
use App\Http\Controllers\Checkout\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Cart;
use App\OrdersTemp;
use App\Order;
use App\Credits;
use App\ShippingAddress;
use App\OrderGatewayLog;
use App\Helpers\GeneralFunctions;
use App\Helpers\CustomHelpers;
use App\Helpers\EmailHelpers;
use Auth;
use App\Product;
use Session;
use Config;
use Route;
use Exception;
use DB;
Use Lang;

class CartController extends MarketPlace {

	public $query;

	public function __construct() {
		$this->middleware('authenticate');  
	}

	public function index(Request $request) {

		
		$checkout_type = request()->segment(2);

		$shop_address = $orderDetails = $paid_product = $user_address = $def_country_dtl = $shop_id_arr = [];
		$billing_address = $shipping_address = $ship_province_str = '';

		$userid = Auth::User()->id;
		$orderInfo = OrdersTemp::where(['user_id'=>$userid,'order_status'=>'0'])->first();

		
		$main_order = [];
		if(empty($orderInfo)){
			$check_pending_order = Order::where(['user_id'=>$userid,'payment_status'=>0,'order_status'=>1])->orderBy('id','desc')->first();
			if($check_pending_order){
				return redirect(action('User\OrderController@mainOrderDetail',$check_pending_order->formatted_id));
			}

			abort(404);
		}

		$update_cart = Cart::where(['order_id'=>$orderInfo->id,'cart_status'=>2])->update(['cart_status'=>1]);

		if(!empty($main_order) && empty($orderInfo)){
			/****if somehow temp order deleted but not end shopping then create randam temp order******/
			$orderInfo = new OrdersTemp;
	        $orderInfo->user_id = $userid;
	        $orderInfo->session_id = Session::getId();
	        $orderInfo->formatted_order_id = $main_order->temp_formatted_id;
	        $orderInfo->save();
		}

		/****cart items*****/
		/**
		*** if buy now or buy now with end shopping then cart items will show.
		*** if only end shopping then only paid product will be list.
		*** if end shopping then shipping method will be show
		***/
		if($checkout_type == 'buy-now' || $checkout_type == 'buy-now-end-shopping'){
			if($orderInfo){
				$orderDetails = Cart::getCartList($orderInfo->id);       
				if(count($orderDetails)){
					foreach ($orderDetails as $key => $value) {
						if(!empty($value->getShop)){
							$shop_name = $value->getShopDesc->shop_name??'';
							$shop_address[$value->getShop->id] = ['shop_name'=>$shop_name,'panel_no'=>$value->getShop->panel_no,'market'=>$value->getShop->market,'ph_number'=>$value->getShop->ph_number];
							$shop_id_arr[$value->getShop->id] = $value->getShop->id;
						}
					}
				}
			}
		}
		//dd($orderDetails);
		/****already paid product*****/
		//$main_order = \App\Order::where('temp_formatted_id',$orderInfo->formatted_order_id)->first();

		if(!empty($main_order)){
			$paid_product = \App\OrderDetail::getMainOrderDetail($main_order->id);
		}
		//dd($orderDetails,$paid_product);
		/***for address***/
		if($checkout_type == 'end-shopping' || $checkout_type == 'buy-now-end-shopping'){

	        $def_country_dtl = GeneralFunctions::getDefaultCountryDetail();
	        if(getConfigValue('ADDRESS_TYPE') == 'dropdown' && !empty($def_country_dtl)) {
	            $ship_province_str = CustomHelpers::getProvinceStateDD($def_country_dtl->id);
	        }

	        $user_address = ShippingAddress::getUserAddress($userid);
	        foreach($user_address as $address) {
	        	if($address->is_default == '1') {
	        		if($address->address_type == '1') {
	        			$shipping_address = $address;
	        		}
	        		elseif($address->address_type == '2') {
	        			$billing_address = $address;
	        		}
	        		elseif($address->address_type == '3') {
	        			$shipping_address = $billing_address = $address;
	        		}	        		
	        	}         
	        }

	        if(count($paid_product)){
	        	foreach ($paid_product as $key => $value) {
	        		if($value->getShop){
	        			$shop_name = $value->getShopDesc->shop_name??'';
						$shop_address[$value->getShop->id] = ['shop_name'=>$shop_name,'panel_no'=>$value->getShop->panel_no,'market'=>$value->getShop->market,'ph_number'=>$value->getShop->ph_number];
						$shop_id_arr[$value->getShop->id] = $value->getShop->id;
	        		}
	        	}
	        }
    	}

        $payment_option = \App\PaymentOption::getPaymentOptions();

		$referer_url = $request->headers->get('referer');
		$breadcrumb = $this->getBreadcrumb($referer_url);
		$cur_hr = date('H');
		$center_estimate_time = 0;
		$all_del_time = \App\DeliveryTime::get();
		$delivery_time_arr = [];
		foreach ($all_del_time as $key => $delivery_time) {
			if($delivery_time->delivery_type =='pickup_center'){
				$center_estimate_time = $delivery_time->delivery_time_after;
			}
			$cur_time_start = $cur_hr + 1 + $delivery_time->delivery_time_after;
			$time_slot = explode(',',$delivery_time->time_slot);
			$time_arr = [];
			$c_arr = $n_arr = [];
			
			if($delivery_time->delivery_type !='shop_address'){
				foreach ($time_slot as $tkey => $tvalue) {

					if($delivery_time->delivery_type=='buyer_address'){
						$add_two = ($tvalue+2);
						$add_day = 1;
						$ndate = date('Y-m-d', strtotime(' +'.$add_day.' day'));
						$val_show = $tvalue.':00 - '.($add_two);
						
						if($tvalue >= $cur_time_start){
							if($add_two>=24){
								$add_two = $add_two-24;
								$val_show = $tvalue.':00 - '.($add_two);
								$ndate = date('Y-m-d', strtotime(' +1 day'));
								$expdate = explode('-', $ndate);
								$c_arr[] = ['key'=>$tvalue,'val'=>$val_show.':00 ('.$expdate[2].' '.getThaiMonth(date($expdate[1])).')'];
							}else{
								$c_arr[] = ['key'=>$tvalue,'val'=>$val_show.':00'];
							}

						}else{
							if($add_two>=24){
								$ndate = date('Y-m-d', strtotime(' +2 day'));
								$add_two = $add_two-24;
								$val_show = $tvalue.':00 - '.($add_two);
							}
							$expdate = explode('-', $ndate);
							$n_arr[] = ['key'=>$tvalue.'_n','val'=>$val_show.':00 ('. $expdate[2].' '.getThaiMonth(date($expdate[1])).')'];
						}
					}else{
						if($tvalue >= $cur_time_start){
							$c_arr[] = ['key'=>$tvalue,'val'=>$tvalue.':00'];
						}else{
							$ndate = date('Y-m-d', strtotime(' +1 day'));
							$expdate = explode('-', $ndate);
							$n_arr[] = ['key'=>$tvalue.'_n','val'=>$tvalue.':00 ('. $expdate[2].' '.getThaiMonth($expdate[1]).')'];
						}
					}
					
				}
			}else{
				$j=0;
				
				for($i=1;$i<=12;$i++){
					if($i==1){
						$next_time = $cur_time_start;
					}else{
						$next_time = $next_time +1;
					}
					
					if($next_time >=24){
						$ndate = date('Y-m-d', strtotime(' +1 day'));
						$expdate = explode('-', $ndate);
						$n_arr[] = ['key'=>$j.'_n','val'=>$j.':00 ('. $expdate[2].' '.getThaiMonth($expdate[1]).')'];
						$j++;
					}else{
						$c_arr[] = ['key'=>$next_time,'val'=>$next_time.':00'];
					}
				}
			}
			
			$time_arr = array_merge($c_arr,$n_arr);
			$delivery_time_arr[$delivery_time->delivery_type] = $time_arr;
		}
		
		$pickup_center = \App\SystemConfig::where('system_name','PICKUP_CENTER')->value('system_val');
		$pickup_center_address = $pickup_center?jsonDecodeArr($pickup_center):'';
		$item_pickup_time = 0;
		if(count($shop_id_arr)){
			$estimate = $center_estimate_time;
			$item_pickup_time = $estimate;
		}
		$cal_time = $cal_hour = $tomorrow = null;
		if($item_pickup_time > 0){
			$cal_time = date("Y-m-d H:i:s", strtotime('+'.$item_pickup_time.' hours'));
			$cal_hour = date('H',strtotime($cal_time)); 
			if($cal_hour >= 23){
				$tomorrow = true;
			}
		}
		
		$delivery_details = ['item_pickup_time'=>$item_pickup_time,'cal_time'=>$cal_time,'cal_hour'=>$cal_hour,'tomorrow'=>$tomorrow];
		
		// if(!empty($shipping_address)){
		// 	$shipping_fee = $this->getShippingFee($shipping_address,$orderDetails,$paid_product);
		// }else{
		// 	$shipping_fee = 0.00;
		// }
		//dd($shipping_fee);
        $shipping_fee = 0.00;
        $user_odd_info = \App\UserInfo::getUserInfo('odd-register');
        //dd($shipping_address);
		return view('checkout.cart',compact('def_country_dtl','ship_province_str','user_address','shipping_address','billing_address','payment_option','checkout_type','pickup_center_address','delivery_details'), ['orderInfo' => $orderInfo, 'orderDetails'=>$orderDetails, 'page_class'=>'cart-wrap','breadcrumb'=>$breadcrumb,'shop_address'=>$shop_address,'main_order'=>$main_order,'paid_product'=>$paid_product,'shipping_fee'=>$shipping_fee,'user_odd_info'=>$user_odd_info,'time_arr'=>[],'delivery_time_arr'=>$delivery_time_arr]);        
	} 

	public function pickupTime(Request $request){
		$logistic_time_arr = [10,14,16,18,20,22];
		$calculated_time = $request->tot_delivery_time;
		$selected_time = $request->val;

	}

	protected function getUnpaidProducts(){
		$userid = Auth::User()->id;
		$orderInfo = OrdersTemp::where(['user_id'=>$userid,'order_status'=>'0'])->first();

		$orderDetails = Cart::getCartList($orderInfo->id,[1]);       
		
		return $orderDetails;
	}

	protected function getPaidProducts(){
		$userid = Auth::User()->id;
		$orderInfo = OrdersTemp::where(['user_id'=>$userid,'order_status'=>'0'])->first();
		$main_order = \App\Order::where('temp_formatted_id',$orderInfo->formatted_order_id)->first();
		$paid_product = [];
		if(!empty($main_order)){
			$paid_product = \App\OrderDetail::getMainOrderDetail($main_order->id);
		}

		return $paid_product;
	}

	protected function getDeliveryAddress(){

		$billing_address = $shipping_address = $ship_province_str = '';
		$userid = Auth::User()->id;
		$user_address = ShippingAddress::getUserAddress($userid);
        foreach($user_address as $address) {
        	if($address->is_default == '1') {
        		if($address->address_type == '1') {
        			$shipping_address = $address;
        		}
        		elseif($address->address_type == '2') {
        			$billing_address = $address;
        		}
        		elseif($address->address_type == '3') {
        			$shipping_address = $billing_address = $address;
        		}	        		
        	}         
        }

        return ['shipping_address'=>$shipping_address,'billing_address'=>$billing_address,'shipping_address'=>$shipping_address];
	}

	// Function to get shipping fee 
	protected function getShippingFee($shipping_address,$orderDetails,$paid_product){
		//dd($shipping_address,$orderDetails,$paid_product);
		$shipProfileData = \App\ShippingProfile::where('id','1')->first();
		
		$total_deliver_fee = 0;
		$total_logistic_fee = 0;
		if(count($orderDetails)){
			foreach ($orderDetails as $key => $orderItem) {
				$itemsShipFees =    $this->getCalculateProductsShipFee($orderDetails, $shipping_address,$shipProfileData);
			    $total_deliver_fee = $itemsShipFees['shipping_fee'];	
			    $total_logistic_fee = $itemsShipFees['logistic_fee'];

				/*$itemsShipFee = $this->getProductShipFee($orderItem,$shipping_address,$shipProfileData);
				$total_deliver_fee += $itemsShipFee['shipping_fee'];	
				$total_logistic_fee += $itemsShipFee['logistic_fee'];*/
			}
		}
		
		if(count($paid_product)){
			foreach ($paid_product as $key => $product) {
				$itemsShipFee = $this->getProductShipFee($product,$shipping_address,$shipProfileData);
				$total_deliver_fee += $itemsShipFee['shipping_fee'];
				$total_logistic_fee += $itemsShipFee['logistic_fee'];
			}
		}
		return ['total_deliver_fee'=>$total_deliver_fee,'total_logistic_fee'=>$total_logistic_fee];
	}

	protected function calculateShippingFeeOfMethod($shippingMethodData){
		//dd($shippingMethodData);
		// this step will check that every cart item's number is same with searched shipping rate for all cart item data count for a shipping method means all cart item have the same rate of a shipping method.
		
		if(isset($shippingMethodData['product_rate_array'])){
			$shipping_rates = array();
			$base_rate_for_order = 0;
			$logistic_base_rate_for_order = 0;
			$logistic_products_var_total_fee = 0;
			$products_var_total_fee = 0;
			$shipping_and_handling_fee = 0;
			
			if(count($shippingMethodData['product_rate_array']) > 1){
					foreach($shippingMethodData['product_rate_array'] as $r_key => $rate_value){
						$priority_array[$rate_value['rate_id']] = $rate_value['priority'];
					}
					$maximum_priority_rate = min($priority_array);
					$key_rate = array_search($maximum_priority_rate, $priority_array);
					$rateVal = array_search($key_rate, array_column($shippingMethodData['product_rate_array'], 'rate_id'));

					$base_rate_for_order = $shippingMethodData['product_rate_array'][$rateVal]['base_rate_for_order'];
					$logistic_base_rate_for_order = $shippingMethodData['product_rate_array'][$rateVal]['logistic_base_rate_for_order'];

					$products_var_total_fee += $shippingMethodData['product_rate_array'][$rateVal]['fee_percentage_rate_per_product']+$shippingMethodData['product_rate_array'][$rateVal]['fee_fixed_rate_per_product']+$shippingMethodData['product_rate_array'][$rateVal]['fee_fixed_rate_per_unit'];

					$logistic_products_var_total_fee += $shippingMethodData['product_rate_array'][$rateVal]['logistic_fee_percentage_rate_per_product']+$shippingMethodData['product_rate_array'][$rateVal]['logistic_fee_fixed_rate_per_product']+$shippingMethodData['product_rate_array'][$rateVal]['logistic_fee_fixed_rate_per_unit'];
			}else{
					$base_rate_for_order = $shippingMethodData['product_rate_array'][0]['base_rate_for_order'];
					$products_var_total_fee += $shippingMethodData['product_rate_array'][0]['fee_percentage_rate_per_product']+$shippingMethodData['product_rate_array'][0]['fee_fixed_rate_per_product']+$shippingMethodData['product_rate_array'][0]['fee_fixed_rate_per_unit'];

					$logistic_base_rate_for_order = $shippingMethodData['product_rate_array'][0]['logistic_base_rate_for_order'];
					$logistic_products_var_total_fee += $shippingMethodData['product_rate_array'][0]['logistic_fee_percentage_rate_per_product']+$shippingMethodData['product_rate_array'][0]['logistic_fee_fixed_rate_per_product']+$shippingMethodData['product_rate_array'][0]['logistic_fee_fixed_rate_per_unit'];
			}
			
			$total_shipping_fee = $base_rate_for_order+$products_var_total_fee;
			$logistic_total_shipping_fee = $logistic_base_rate_for_order+$logistic_products_var_total_fee;

			switch ($shippingMethodData['shipping_calculation_type']) {
					case '0':
							$shipping_and_handling_fee = $total_shipping_fee;
							$logistic_shipping_and_handling_fee = $logistic_total_shipping_fee;
					break;
					case '1':

							$shipping_and_handling_fee = ($shippingMethodData['minimal_rate'] <= $total_shipping_fee)?$shippingMethodData['minimal_rate'] :$total_shipping_fee;

							$logistic_shipping_and_handling_fee = ($shippingMethodData['minimal_rate'] <= $logistic_total_shipping_fee)?$shippingMethodData['minimal_rate'] :$logistic_total_shipping_fee;
					break;
					case '2':
							
							$shipping_and_handling_fee = ($shippingMethodData['maximal_rate'] > $total_shipping_fee)?$shippingMethodData['maximal_rate'] : $total_shipping_fee;
							$logistic_shipping_and_handling_fee = ($shippingMethodData['maximal_rate'] > $logistic_total_shipping_fee)?$shippingMethodData['maximal_rate'] : $logistic_total_shipping_fee;
					break;       
			}

			$returnShippingMethods = array('shipping_fee'=>$shipping_and_handling_fee,'logistic_fee'=>$logistic_shipping_and_handling_fee);

			return $returnShippingMethods;
		}
	}

	protected function getProductShipFee($item,$shipping_address,$shipProfileData){
		//dd($item,$shipping_address,$shipProfileData);
		// Clacilation of product factor weight
		$productPackageData = \App\Product::where('product.id',$item->product_id)
							->leftJoin(with(new \App\Package)->getTable().' as pkg','pkg.id','=','product.package_id')
							->leftJoin(with(new \App\Unit)->getTable().' as unit','unit.id','=','product.base_unit_id')
							->select('pkg.height','pkg.width','pkg.depth','product.package_id','product.weight_per_unit','unit.unit_weight')->first();


		//dd($item,$productPackageData);
		$total_weight = ($shipProfileData->use_dimension_weight=='1') ? round($item->quantity * 1000 * (($productPackageData->height * $productPackageData->width * $productPackageData->depth)/$shipProfileData->dimension_factor),2):round($item->quantity * 1000 * $productPackageData->weight_per_unit * $productPackageData->unit_weight,2);
		// End
		//dd($total_weight,$productPackageData,$item);
		$astric = "*";
		$product_type = '0';
		$shipping_profile_id = $shipProfileData->id;
		$shipping_rate_data = DB::table(with(new \App\ShippingProfileRates)->getTable().' as spr')
							->leftJoin(with(new \App\ShippingProfileRatesDesc)->getTable().' as sprd', 'spr.id', '=', 'sprd.rate_id')
							->select('spr.id', 'spr.country_id', 'sprd.province_state','sprd.district_city', 'sub_district', 'spr.weight_from', 'spr.weight_to', 'spr.qty_from', 'spr.qty_to', 'spr.price_from', 'spr.price_to', 'spr.zip_from', 'spr.zip_to', 'spr.product_type_id','spr.estimate_shipping','spr.base_rate_for_order','spr.percentage_rate_per_product','spr.fixed_rate_per_product','spr.fixed_rate_per_unit_weight','spr.logistic_base_rate_for_order','spr.logistic_percentage_rate_per_product','spr.logistic_fixed_rate_per_product','spr.logistic_fixed_rate_per_unit_weight','spr.priority')
							->whereIn('spr.country_id',[$shipping_address->country_id,$astric])
							->whereIn('sprd.province_state',[$shipping_address->province_state,$astric])
							->whereIn('sprd.district_city',[$shipping_address->city_district,$astric])
							->whereIn('sprd.sub_district',[$shipping_address->sub_district,$astric])
							->where(['sprd.lang_id'=>session('default_lang'),'spr.shipping_profile_id'=>$shipping_profile_id])
							->where('spr.weight_from','<=',$total_weight)
							->where('spr.weight_to','>=',$total_weight)
							->where('spr.qty_from','<=',$item->quantity)
							->where('spr.qty_to','>=',$item->quantity)
							->where('spr.price_from','<=',$item->total_price)
							->where('spr.price_to','>=',$item->total_price)
							->where('spr.product_type_id',$product_type)
							->whereRaw("zip_from <= IF(zip_from != '*',?,'*')",[$shipping_address->zip_code])
							->whereRaw("zip_to >= IF(zip_to != '*',?,'')",[$shipping_address->zip_code])
							->get();
		// dd($shipping_rate_data,$shipping_address->country_id,$shipping_address->province_state,$shipping_address->city_district,$shipping_address->sub_district,$shipping_profile_id,$total_weight,$item->quantity,$item->total_price,$product_type);
		$shippingMethod = [];
		foreach($shipping_rate_data as $shp_key => $rateData){
			$base_rate_for_order = $rateData->base_rate_for_order;
			$fee_percentage_rate_per_product = round((($item->total_price * $rateData->percentage_rate_per_product)/100),2);
			$fee_flat_rate_per_product = $item->quantity * $rateData->fixed_rate_per_product;
			$fee_fixed_rate_per_unit = $item->total_weight * $rateData->fixed_rate_per_unit_weight;

			$logistic_base_rate_for_order = $rateData->logistic_base_rate_for_order;
			$logistic_fee_percentage_rate_per_product = round((($item->total_price * $rateData->logistic_percentage_rate_per_product)/100),2);
			$logistic_fee_flat_rate_per_product = $item->quantity * $rateData->logistic_fixed_rate_per_product;
			$logistic_fee_fixed_rate_per_unit = $item->total_weight * $rateData->logistic_fixed_rate_per_unit_weight;

			$shippingMethod['product_rate_array'][$shp_key] = array('shipping_profile_id'=>$shipping_profile_id,'fee_percentage_rate_per_product'=>$fee_percentage_rate_per_product,'fee_fixed_rate_per_product'=>$fee_flat_rate_per_product,'base_rate_for_order'=>$base_rate_for_order,'fee_fixed_rate_per_unit'=>$fee_fixed_rate_per_unit,'logistic_fee_percentage_rate_per_product'=>$logistic_fee_percentage_rate_per_product,'logistic_fee_fixed_rate_per_product'=>$logistic_fee_flat_rate_per_product,'logistic_base_rate_for_order'=>$logistic_base_rate_for_order,'logistic_fee_fixed_rate_per_unit'=>$logistic_fee_fixed_rate_per_unit,'rate_id'=>$rateData->id,'priority'=>$rateData->priority);

			$shippingMethod['maximal_rate']=$shipProfileData->maximal_rate;
			$shippingMethod['minimal_rate']=$shipProfileData->minimal_rate;
			$shippingMethod['shipping_calculation_type'] = $shipProfileData->shipping_calculation_type;
		}

		$shippingFeeData = $this->calculateShippingFeeOfMethod($shippingMethod);

		return $shippingFeeData;
	}

	protected function getCalculateProductsShipFee($items,$shipping_address,$shipProfileData){
		//dd($item,$shipping_address,$shipProfileData);
		// Clacilation of product factor weight
        $total_weight = 0;
        $total_qty = 0;
        $total_price = 0;
        foreach($items as $item){
			$productPackageData = \App\Product::where('product.id',$item->product_id)
								->leftJoin(with(new \App\Package)->getTable().' as pkg','pkg.id','=','product.package_id')
								->leftJoin(with(new \App\Unit)->getTable().' as unit','unit.id','=','product.base_unit_id')
								->select('pkg.height','pkg.width','pkg.depth','product.package_id','product.weight_per_unit','unit.unit_weight')->first();

            $total_weight += ($shipProfileData->use_dimension_weight=='1') ? round($item->quantity * 1000 * (($productPackageData->height * $productPackageData->width * $productPackageData->depth)/$shipProfileData->dimension_factor),2):round($item->quantity * 1000 * $productPackageData->weight_per_unit * $productPackageData->unit_weight,2);
            $total_qty += $item->quantity; 
            $total_price += $item->total_price;
        }


		// End
		//dd($total_weight,$productPackageData,$item, $total_price, $total_qty);
		$astric = "*";
		$product_type = '0';
		$shipping_profile_id = $shipProfileData->id;

		//dd($shipping_address, $shipping_profile_id);
        	//dd(session('default_lang'));

		$shipping_rate_data = DB::table(with(new \App\ShippingProfileRates)->getTable().' as spr')->select('spr.*')
		/*
		->leftJoin(with(new \App\ShippingProfileRatesDesc)->getTable().' as sprd', 'spr.id', '=', 'sprd.rate_id')->select('spr.id', 'spr.country_id', 'sprd.province_state','sprd.district_city', 'sub_district', 'spr.weight_from', 'spr.weight_to', 'spr.qty_from', 'spr.qty_to', 'spr.price_from', 'spr.price_to', 'spr.zip_from', 'spr.zip_to', 'spr.product_type_id','spr.estimate_shipping','spr.base_rate_for_order','spr.percentage_rate_per_product','spr.fixed_rate_per_product','spr.fixed_rate_per_unit_weight','spr.logistic_base_rate_for_order','spr.logistic_percentage_rate_per_product','spr.logistic_fixed_rate_per_product','spr.logistic_fixed_rate_per_unit_weight','spr.priority')*/
		                    ->whereIn('spr.country_id',[$shipping_address->country_id,$astric])
							/*->whereIn('sprd.province_state',[$shipping_address->province_state,$astric])
							->whereIn('sprd.district_city',[$shipping_address->city_district,$astric])
							->whereIn('sprd.sub_district',[$shipping_address->sub_district,$astric])
							->where('sprd.lang_id', session('default_lang'))*/

							->where('spr.shipping_profile_id', $shipping_profile_id)
							->where('spr.weight_from','<=',$total_weight)
							->where('spr.weight_to','>=',$total_weight)
							->where('spr.qty_from','<=',$total_qty)
							->where('spr.qty_to','>=',$total_qty)
							->where('spr.price_from','<=',$total_price)
							->where('spr.price_to','>=',$total_price)
							->where('spr.product_type_id',$product_type)
							->whereRaw("zip_from <= IF(zip_from != '*',?,'*')",[$shipping_address->zip_code])
							->whereRaw("zip_to >= IF(zip_to != '*',?,'*')",[$shipping_address->zip_code])
							->get();
		
		//dd($shipping_rate_data,$shipping_address->country_id,$shipping_address->province_state,$shipping_address->city_district,$shipping_address->sub_district,$shipping_profile_id,$total_weight,$product_type, $total_price, $shipping_address->zip_code);

		$shippingMethod = [];
		foreach($shipping_rate_data as $shp_key => $rateData){
			$base_rate_for_order = $rateData->base_rate_for_order;
			$fee_percentage_rate_per_product = round((($item->total_price * $rateData->percentage_rate_per_product)/100),2);
			$fee_flat_rate_per_product = $item->quantity * $rateData->fixed_rate_per_product;
			$fee_fixed_rate_per_unit = $item->total_weight * $rateData->fixed_rate_per_unit_weight;

			$logistic_base_rate_for_order = $rateData->logistic_base_rate_for_order;
			$logistic_fee_percentage_rate_per_product = round((($item->total_price * $rateData->logistic_percentage_rate_per_product)/100),2);
			$logistic_fee_flat_rate_per_product = $item->quantity * $rateData->logistic_fixed_rate_per_product;
			$logistic_fee_fixed_rate_per_unit = $item->total_weight * $rateData->logistic_fixed_rate_per_unit_weight;

			$shippingMethod['product_rate_array'][$shp_key] = array('shipping_profile_id'=>$shipping_profile_id,'fee_percentage_rate_per_product'=>$fee_percentage_rate_per_product,'fee_fixed_rate_per_product'=>$fee_flat_rate_per_product,'base_rate_for_order'=>$base_rate_for_order,'fee_fixed_rate_per_unit'=>$fee_fixed_rate_per_unit,'logistic_fee_percentage_rate_per_product'=>$logistic_fee_percentage_rate_per_product,'logistic_fee_fixed_rate_per_product'=>$logistic_fee_flat_rate_per_product,'logistic_base_rate_for_order'=>$logistic_base_rate_for_order,'logistic_fee_fixed_rate_per_unit'=>$logistic_fee_fixed_rate_per_unit,'rate_id'=>$rateData->id,'priority'=>$rateData->priority);

			$shippingMethod['maximal_rate']=$shipProfileData->maximal_rate;
			$shippingMethod['minimal_rate']=$shipProfileData->minimal_rate;
			$shippingMethod['shipping_calculation_type'] = $shipProfileData->shipping_calculation_type;
		}

		$shippingFeeData = $this->calculateShippingFeeOfMethod($shippingMethod);

		//dd($shippingFeeData);

		return $shippingFeeData;
	}

	public function shoppingCart(Request $request) {

		$userid = Auth::User()->id;

		$orderInfo = OrdersTemp::where(['user_id'=>$userid,'order_status'=>'0'])->first();

		$user_credits = Credits::getUserCredit($userid);
		$show_credit = 0;
		$orderDetails = $cartAttributeData = $promotion = [];
		$total_prom_disc_amt = 0;
		$shop_details = [];
		if(!empty($orderInfo)){ 
			$orderDetails = Cart::getCartList($orderInfo->id);  
			//dd($orderDetails);
			foreach ($orderDetails as $key => $value) {
				if($value->getShop && $value->getShopDesc){
			    	$shop_details[$value->shop_id] = ['shop_name'=>$value->getShopDesc->shop_name,'shop_url'=>$value->getShop->shop_url];
			    	if(isset($user_credits[$value->shop_id])){
			    		$show_credit = 1;
			    	}
				}
			}                      
		}        
		
		//dd($user_credits,$shop_details);
		$referer_url = $request->headers->get('referer');
		$breadcrumb = $this->getBreadcrumb($referer_url);

		$default_shopping = session('shopping_list');
        $pur_prds_in_shop_list = \App\UserShoppingListItems::where(['shopping_list_id'=>$default_shopping,'is_completed'=>'1'])->count();
        $total_prds_in_shop_list = \App\UserShoppingListItems::where(['shopping_list_id'=>$default_shopping])->count();
		//dd($orderDetails);
		return view('checkout.shopping_cart', ['orderInfo' => $orderInfo, 'orderDetails'=>$orderDetails, 'page_class'=>'cart-wrap','breadcrumb'=>$breadcrumb,'user_credits'=>$user_credits,'shop_details'=>$shop_details,'show_credit'=>$show_credit,'page'=>'shopping_cart','total_prds_in_shop_list'=>$total_prds_in_shop_list,'pur_prds_in_shop_list'=>$pur_prds_in_shop_list]);        
	}

	public function alreadyPaid(Request $request) {
		$userid = Auth::User()->id;
		$orderInfo = OrdersTemp::where(['user_id'=>$userid,'order_status'=>'0'])->first();
		if(empty($orderInfo)){
			$main_order =  \App\Order::whereNull('end_shopping_date')->where('user_id',$userid)->first();
			
			if(empty($main_order))
				return redirect()->action('Checkout\CartController@shoppingCart');
		}else{
			$main_order = \App\Order::where('temp_formatted_id',$orderInfo->formatted_order_id)->first();
		}
		
		$paid_product = [];
		if(!empty($main_order)){
			$paid_product = \App\OrderDetail::getMainOrderDetail($main_order->id);
		}       
		
		$referer_url = $request->headers->get('referer');
		$breadcrumb = $this->getBreadcrumb($referer_url);

		$default_shopping = session('shopping_list');
        $pur_prds_in_shop_list = \App\UserShoppingListItems::where(['shopping_list_id'=>$default_shopping,'is_completed'=>'1'])->count();
        $total_prds_in_shop_list = \App\UserShoppingListItems::where(['shopping_list_id'=>$default_shopping])->count();

		return view('checkout.already_paid', ['orderInfo' => $orderInfo, 'main_order'=>$main_order, 'paid_product'=>$paid_product,'breadcrumb'=>$breadcrumb,'page'=>'already_paid','total_prds_in_shop_list'=>$total_prds_in_shop_list,'pur_prds_in_shop_list'=>$pur_prds_in_shop_list]);        
	}            

	/****update cart when change product quantity********/
	public function updateCart(Request $request){
		//dd($request->all());
		$cartId = $request->cartId;
		$newQuantity = $request->quantity;
		$userid = Auth::User()->id;
		$cartresult = Cart::where(['id'=>$cartId,'user_id'=>$userid])->first();

		if(!empty($cartresult)){
			$productId = $cartresult->product_id;
			$old_qty = $cartresult->quantity;
			$request_qty = $newQuantity - $old_qty;

			/******checking bargaining********/
			if($cartresult->product_from == 'bargain'){
				return ['status'=>'fail','msg'=>Lang::get('checkout.popup_price_has_already_bargained'),'qty'=>$old_qty];
			}

			$product_det = Product::where('id',$cartresult->product_id)->select('id','stock','quantity','unit_price','is_tier_price','order_qty_limit','min_order_qty')->first();

			if($product_det->stock !='1' &&  $newQuantity > $product_det->quantity){
				return ['status'=>'fail','msg'=>Lang::get('checkout.quantity_not_available'),'qty'=>$old_qty];
			}else{

				/*****checking minimum quantity*******/
		        if($product_det->order_qty_limit == '0' && $product_det->min_order_qty > 0){
		            if($newQuantity < $product_det->min_order_qty){
		                $msg = Lang::get('checkout.product_minimum_quantity_should_be').' '.$product_det->min_order_qty;
		                return ['status'=>'fail','msg'=>$msg];
		            }
		        }

				$product_price = GeneralFunctions::getProductPriceById($product_det->id,$newQuantity,$product_det);
				
				$total_price = $product_price * $newQuantity;

				if(validOrdAmt($total_price)== false){
		            return ['status'=>'fail','msg'=>Lang::get('checkout.order_amount_exceeded')];
		        }

				/****updating cart with quantity******/
				$affected = Cart::where(['id' => $cartId])->update(['quantity'=>$newQuantity,'original_price'=>$product_price,'cart_price' => $product_price,'total_price'=>$total_price]);

				$orderFinalPrice = OrdersTemp::updateOrderPrice($cartresult->order_id);

				$totQty = Cart::where('order_id',$cartresult->order_id)->sum('quantity');

				return array('status'=>'success','ordAmount'=>convert_string($orderFinalPrice),'totQty'=>$totQty,'tot_prd_price'=>convert_string($total_price),'product_price'=>convert_string($product_price));
			}

		}else{
			return ['status'=>'fail','msg'=>'invalid cart'];
		}		
	}

 	/****Remove product from cart********/
	function removeCart(Request $request){
		$cartId = $request->cartId;
		$userid = Auth::User()->id;
		$cartresult = Cart::select('order_id')->where(['id'=>$cartId,'user_id'=>$userid])->first();
		
		if(!empty($cartresult)){
			$orderId = $cartresult->order_id;

			Cart::where('id', $cartId)->delete();  /***delete product****/
			$check_cart = Cart::where('order_id',$orderId)->count();
			$returnArr = [];
			if($check_cart>0){
				$totQty = Cart::where('order_id',$orderId)->sum('quantity');
				$orderFinalPrice = OrdersTemp::updateOrderPrice($orderId);
				$cart_item = Cart::totCartPrd($userid);
				$msg = Lang::get('checkout.product_deleted_successfully');
				$returnArr = array('delete'=>'cart','ordAmount'=>numberFormat($orderFinalPrice),'totQty'=>$totQty,'msg'=>$msg,'cart_item'=>$cart_item);
				
			}
			else{
				$temp_formatted_id = OrdersTemp::where('id', $orderId)->value('formatted_order_id');
				if($temp_formatted_id){
					$check_ord = \App\Order::where('temp_formatted_id',$temp_formatted_id)->count();
					if(empty($check_ord)){
						OrdersTemp::where('id', $orderId)->delete();  /**delete order***/
					}
				}
				
				$msg = Lang::get('checkout.order_deleted_successfully');
				$returnArr = array('delete'=>'order','msg'=>$msg);
			}
			$returnArr['status']='success';
			return $returnArr;
		}else{
			return ['status'=>'fail','msg'=>'invalid cart'];
		}
	}

	/****update cart price if change product price after add to cart*********/
	function updateCartPrice(Request $request){
		$userid = Auth::User()->id;
		$orderInfo = OrdersTemp::where(['user_id'=>$userid])->select('id')->first();
		if(!empty($orderInfo)){
			$price_update = 'N';
			$cart_details = Cart::where(['order_id'=>$orderInfo->id])->with('getPrd')->get();
			$updated_at = date('Y-m-d H:i:s');
			foreach ($cart_details as $key => $value) {
				/**If product price updated to lower than cart price then show alert to user for update cart price**/
				if(strtotime($value->getPrd->updated_at) > strtotime($value->created_at)){

					if($value->cart_price > $value->getPrd->unit_price) {
							
						$cart_price = $value->getPrd->unit_price;
						$original_price = $value->getPrd->unit_price;
						$total_price = $cart_price * $value->quantity;

						Cart::where(['id'=>$value->id])->update(['original_price'=>$original_price,'cart_price'=>$cart_price,'total_price'=>$total_price,'updated_at'=>$updated_at]);

						$price_update = 'Y';
					}                            
				}         
			}
			if($price_update == 'Y') {
					OrdersTemp::updateOrderPrice($orderInfo->id);
			}

			return $price_update;
		}
	}

	/***when user select product for pay***/
	function payProduct(Request $request){
		//dd($request->all());
		$userid = Auth::User()->id;
		$cart_det = jsonDecode($request->data);
		$orderInfo = OrdersTemp::where('user_id',$userid)->first();
		$cart_arr = [];
		if(count($cart_det) && $orderInfo){
			//dd($orderInfo,$cart_det);
			/***checking product quantity and updated date******/
			foreach ($cart_det as $key => $value) {
				$cart_res = Cart::where(['id'=>$value->cartId,'user_id'=>$userid])->with(['getPrd','getCatDesc','getShop'])->first();
				if(empty($cart_res)){
					return ['status'=>'fail','msg'=>Lang::get('checkout.this_product_has_been_deleted'),'cart_id'=>$value->cartId];
				}

				$prdavailqty = $cart_res->getPrd->quantity;
			    $stock = $cart_res->getPrd->stock;
			    $cartQty = $cart_res->quantity;
			    $cart_arr[$value->cartId] = $cart_res;

			    /****checking shop status******/
			    if($cart_res->getShop->shop_status == 'close'){
			    	$errorMsg = Lang::get('product.product').' "'.$cart_res->getCatDesc->category_name.'" '.Lang::get('checkout.this_shop_is_close');
					
					return ['status'=>'fail','type'=>'quantity','msg'=>$errorMsg,'cart_id'=>$value->cartId];
			    }

			    /****checking quantity******/
			    if($cartQty == 0){
			    	$errorMsg = Lang::get('product.product').' "'.$cart_res->getCatDesc->category_name.'" '.Lang::get('checkout.select_quantity');
					
					return ['status'=>'fail','type'=>'quantity','msg'=>$errorMsg,'cart_id'=>$value->cartId];
			    }

			    if($stock == 0 && $cartQty > $prdavailqty){
			    	$errorMsg = Lang::get('product.product').' "'.$cart_res->getCatDesc->category_name.'" '.Lang::get('checkout.quantity_not_available');
					
					return ['status'=>'fail','type'=>'quantity','msg'=>$errorMsg,'cart_id'=>$value->cartId];
			    }

			    if(strtotime($cart_res->getPrd->updated_at) > strtotime($cart_res->created_at)) {
			    	/**If product price updated to lower than cart price then show alert to user for update cart price**/
			    	if($cart_res->cart_price > $cart_res->getPrd->unit_price){

			    		$errorMsg = Lang::get('product.product').' "'.$cart_res->getCatDesc->category_name.'" '.Lang::get('checkout.price_updated');
						
						return ['status'=>'fail','type'=>'price','msg'=>$errorMsg,'cart_id'=>$value->cartId];
						
			    	}
				} 
			}
			
			/***end validation****/
			switch ($request->type) {
				case 'buynow':
				case 'end_shopping':
					$update_cart = Cart::where(['user_id'=>$userid,'cart_status'=>1])->update(['cart_status'=>0]);

					foreach ($cart_det as $key => $value) {
						
						//$cartresult = $cart_arr[$value->cartId];
						$cartresult = Cart::where(['id'=>$value->cartId,'user_id'=>$userid])->first();
						
						if($cartresult){
							
							$cartresult->cart_status = 1;
							$cartresult->save();
						}
					}
					if($request->type == 'buynow')
						$url = route('buy-now');
					else
						$url = route('buy-now-end-shopping');
					
					return ['status'=>'success','url'=>$url];
					break;
				case 'all_credit':

					$user_credits = Credits::getUserCredit($userid);

					if(empty($user_credits)){
						return ['status'=>'fail','msg'=>Lang::get('checkout.sorry_you_dont_have_sufficient_credit_amount')];
					}

					$update_cart = Cart::where(['user_id'=>$userid,'cart_status'=>2])->update(['cart_status'=>0]);
					$cart_id_arr = [];
					foreach ($cart_det as $key => $value) {
						$cart_res = $cart_arr[$value->cartId];

						if(!empty($cart_res)){
							if(isset($user_credits[$cart_res->shop_id]) && $user_credits[$cart_res->shop_id]->remain_credit >= $cart_res->total_price){

									$cart_id_arr[] = $value->cartId;
							}else{
								return ['status'=>'fail','msg'=>Lang::get('checkout.sorry_you_dont_have_sufficient_credit_amount'),'id'=>$value->cartId];
							}

						}else{
							return ['status'=>'fail','msg'=>Lang::get('checkout.invalid_cart'),'id'=>$value->cartId];
						}
					}

					/****if all cases pass then change selected cart items to payment mode*****/
					if(count($cart_id_arr)){
						$change_status = Cart::where('user_id',$userid)->whereIn('id',$cart_id_arr)->update(['cart_status'=>2]);
						$payment_type = 'credit';
						$payment_slug = 'credit';

						$orderUpdate = OrdersTemp::where(['id' => $orderInfo->id])->update(['user_id'=>$userid,'payment_type'=>$payment_type,'payment_slug'=>$payment_slug]);
						try{
							$save_order = OrderController::saveOrderCartWise($orderInfo->id);
							$url = action('Checkout\CartController@alreadyPaid');
							return ['status'=>'success','url'=>$url,'msg'=>Lang::get('checkout.product_payment_paid_successfully')];
				        }catch(Exception $e){
				            return ['status'=>'fail','msg'=>$e->getMessage()];
				        }
					}
					break;
				
			}
		}else{
			return ['status'=>'fail','msg'=>Lang::get('checkout.please_select_product')];
		}
	}

	function validateCart($input){

        $rules['ship_method'] = reqRule();
        if(isset($input['ship_method']) && ($input['ship_method'] == 1 || $input['ship_method'] == 2 || $input['ship_method'] == 3)){
        	if($input['ship_method'] == 3){
        		$rules['ship_address'] = reqRule();
        		$rules['bill_address'] = reqRule();
        	}else{
        		$rules['phone_no'] = phoneRule();
        	}
        }else{
        	$input['ship_method'] = '';
        }
        $rules['ship_method'] = reqRule();
        if($input['check_pay_method'])
        	$rules['payment_method'] = reqRule();  
        $rules['order_id'] = reqRule();
        
        $error_msg['ship_method.required'] = Lang::get('checkout.select_shipping_method');
        $error_msg['payment_method.required'] = Lang::get('checkout.select_payment_method');
        $error_msg['ship_address.required'] = Lang::get('checkout.select_shipping_address');
        $error_msg['bill_address.required'] = Lang::get('checkout.select_billing_address');
        $error_msg['received_time.required'] = Lang::get('checkout.select_time_to_recieved');
        $error_msg['phone_no.digits'] = Lang::get('checkout.phone_no_must_be_10_digit');
        $error_msg['phone_no.numeric'] = Lang::get('checkout.phone_no_must_be_numeric');
        $error_msg['order_id.required'] = Lang::get('checkout.invalid_order');
        $validate = Validator::make($input, $rules, $error_msg);
        
        return $validate;
	}

	function store(Request $request){

		$input = $request->all();
		$validate = $this->validateCart($input);
		if ($validate->passes()) {
			$formatedId = $request->order_id;
		
			$userid = Auth::User()->id;
			$orderInfo = OrdersTemp::where(['formatted_order_id'=>$formatedId,'user_id'=>$userid,'order_status'=>'0'])->first();

			if(empty($orderInfo)){
				return ['status'=>'fail','msg'=>Lang::get('checkout.invalid_order')];
			}
			/***only end shopping means order has paid product already*****/
			if($request->checkout_type =='end-shopping'){
				$main_order = \App\Order::where('temp_formatted_id',$orderInfo->formatted_order_id)->first();
				if(empty($main_order)){
					return ['status'=>'fail','msg'=>Lang::get('checkout.invalid_order')];
				}
			}
			
			$orderId = $orderInfo->id;
			$cartInfo = Cart::getCartList($orderId);
			if($request->checkout_type !='end-shopping'){
				
				$price_update = 'N';
				foreach ($cartInfo as $key => $value) {
				    $prdavailqty = $value->getPrd->quantity;
				    $stock = $value->getPrd->stock;
				    $cartQty = $value->quantity;

				    if($cartQty == 0){
				    	$errorMsg = Lang::get('product.product').' "'.$value->getCatDesc->category_name.'" '.Lang::get('checkout.select_quantity');
						
						return ['status'=>'fail','type'=>'quantity','msg'=>$errorMsg];
				    }

				    if($stock == 0 && $cartQty > $prdavailqty){
				    	$errorMsg = Lang::get('product.product').' "'.$value->getCatDesc->category_name.'" '.Lang::get('checkout.quantity_not_available');
						
						return ['status'=>'fail','type'=>'quantity','msg'=>$errorMsg];
				    }

				    if($value->getPrd->status != '1') {
							
						/**checking product status**/
			    		$errorMsg = Lang::get('product.product').' "'.$value->getCatDesc->category_name.'" '.Lang::get('checkout.disable');
						
						return ['status'=>'fail','type'=>'price','msg'=>$errorMsg,'cart_id'=>$value->id];
					}

				    if(strtotime($value->getPrd->updated_at) > strtotime($value->created_at)) {
							
						/**If product price updated to lower than cart price then show alert to user for update cart price**/
				    	if($value->cart_price > $value->getPrd->unit_price){

				    		$errorMsg = Lang::get('product.product').' "'.$value->getCatDesc->category_name.'" '.Lang::get('checkout.price_updated');
							
							return ['status'=>'fail','type'=>'price','msg'=>$errorMsg,'cart_id'=>$value->id];
							
				    	}
					} 
				}
			}

			/***checking payment method******/
			$pay_det = [];
			$user_odd_info = [];
			if(!empty($request->payment_method)){
				$pay_det = \App\PaymentOption::where('id',$request->payment_method)->first();
				if(empty($pay_det)){
					return ['status'=>'fail','type'=>'payment','msg'=>Lang::get('checkout.invalid_payment_method')];
				}
				if($pay_det->slug == 'odd'){
					$user_odd_info = \App\UserInfo::getUserInfo('odd-register');
					if(!$user_odd_info || $user_odd_info->espa_id==''){
						return ['status'=>'fail','type'=>'payment','msg'=>Lang::get('checkout.you_have_not_register_odd')];
					}
				}
				
			}

			/****calculating pickup time*******/
			$pickup_datetime = null;
			if(isset($request->pickup_time)){
				$delivery_type = \App\DeliveryTime::getDeliverYType($request->ship_method);
				$delivery_detail = \App\DeliveryTime::getDeliveryTime($delivery_type);
				$pickup_time = $request->pickup_time;
				$nextday = !empty($request->nexday)?$request->nexday:'';
				$ptime = str_replace('_n', '', $pickup_time);
				if(strrpos($pickup_time,'_n')!==false){
					
					$cur_hr = date('H');
					$time_cal = $cur_hr + $delivery_detail->delivery_time_after;
					
					if($cur_hr <=3 && $ptime >= $time_cal){
						$pdate = date('Y-m-d').' '.$ptime.':00:00';
					}else{
						$tomorrow = date("Y-m-d", strtotime("+1 day"));
						$pdate = $tomorrow.' '.$ptime.':00:00';
					}
					
				}else{
					$pdate = date('Y-m-d').' '.$ptime.':00:00';
				}
				$pickup_datetime = date('Y-m-d H:i:s',strtotime($pdate));

				$new_time = date("Y-m-d H:i:s", strtotime('+'.$delivery_detail->delivery_time_after.' hours'));

	            if(strtotime($new_time) > strtotime($pickup_datetime)){
	            	return ['status'=>'fail','type'=>'pickup_time','msg'=>'รอบการจัดส่งสินค้าที่เลือกไว้หมดเวลาแล้ว กรุณาเลือกรอบการจัดส่งสินค้าใหม่อีกครั้ง'];
	            }
				
			}
			
			/**check valid shipping and billing address*/
			$shipping_method = $request->ship_method;
			$shipping_fee = 0;
			$logistic_fee = 0;
			$user_phone_no = '';
			if($shipping_method == 3){
				$shipping_address_id = $request->ship_address;
				$billing_address_id = $request->bill_address;

				$shipAddress = \App\ShippingAddress::where(['user_id'=>$userid,'id'=>$shipping_address_id])->first();
				$shipping_address = \App\ShippingAddress::where(['user_id'=>$userid,'id'=>$shipping_address_id])->count();

				$billing_address = \App\ShippingAddress::where(['user_id'=>$userid,'id'=>$billing_address_id])->count();
				$paid_product = [];
				if(!empty($main_order)){
					$paid_product = \App\OrderDetail::getMainOrderDetail($main_order->id);
				}
				
				$shippingRes = $this->getShippingFee($shipAddress,$cartInfo,$paid_product);
				$shipping_fee = $shippingRes['total_deliver_fee'];
				$logistic_fee = $shippingRes['total_logistic_fee'];
				if(!$shipping_address || !$billing_address){
					return ['status'=>'fail','type'=>'address','msg'=>$errorMsg];
				}
			}else{
				$shipping_address_id = $billing_address_id = 0;
				$user_phone_no = $request->phone_no;
			}

			$orderTmpPrice = OrdersTemp::updateOrderPrice($orderId);
			
			$total_shipping_cost = $shipping_fee;
			$total_logistic_cost = $logistic_fee;
			$total_final_price = $orderTmpPrice+$shipping_fee;
			$payment_type = $payment_slug = '';

			if($request->checkout_type =='end-shopping'){
				if($shipping_fee > 0){
					if(empty($pay_det)){
						return ['status'=>'fail','type'=>'payment','msg'=>Lang::get('checkout.invalid_payment_method')];
					}
				}
			}
			elseif($total_final_price > 0){
				if(empty($pay_det)){
					return ['status'=>'fail','type'=>'payment','msg'=>Lang::get('checkout.invalid_payment_method')];
				}
				
			}
			
			if(!empty($pay_det)){
				if($pay_det->slug == 'kbank'){
					$payment_type = 'online';
					$payment_slug = 'kbank';
				}
				if($pay_det->slug == 'payplus'){
					$payment_type = 'online';
					$payment_slug = 'payplus';
				}
				if($pay_det->slug == 'odd'){
					$payment_type = 'online';
					$payment_slug = 'odd';
				}
			}
			
			// dd($shipping_fee,$total_final_price);
			$orderUpdate = OrdersTemp::where(['id' => $orderId])->update(['user_id'=>$userid,'payment_type'=>$payment_type,'payment_slug'=>$payment_slug,'shipping_address_id'=>$shipping_address_id,'billing_address_id'=>$billing_address_id,'shipping_method'=>$shipping_method,'total_final_price'=>$total_final_price,'total_shipping_cost'=>$total_shipping_cost,'total_logistic_cost'=>$total_logistic_cost,'pickup_time'=>$pickup_datetime,'user_phone_no'=>$user_phone_no,'checkout_type'=>$request->checkout_type]);

			$update_cart = Cart::where(['order_id'=>$orderInfo->id])->update(['cart_status'=>2]);

			$order_created_id = OrderController::saveFinalOrder($orderId);

			if(!$order_created_id){
				return ['status'=>'fail','type'=>'payment','msg'=>Lang::get('checkout.something_went_wrong')];
			}
			$main_order = \App\Order::where('id',$order_created_id)->first();
			if($payment_slug == 'kbank'){
				$url = action('Checkout\CartController@kbankPayment',$main_order->formatted_id);
				return ['status'=>'success','url'=>$url];
			}
			if($payment_slug == 'payplus'){
				$url = action('Checkout\CartController@payplusPayment',$main_order->formatted_id);
				return ['status'=>'success','url'=>$url];
			}
			if($payment_slug == 'odd'){
				$return = $this->oddPayment($main_order,$user_odd_info);
				return $return;
			}
			try{
				if($total_final_price <= 0 || $request->checkout_type =='end-shopping'){
					$formattedOrderId = OrderController::saveOrderEndShopping($orderId,$main_order);
					$url = action('Checkout\OrderController@thanks',$main_order->formatted_id);
					return ['status'=>'success','url'=>$url,'msg'=>Lang::get('checkout.product_payment_paid_successfully')];
				}else{

					return ['status'=>'fail','type'=>'payment','msg'=>Lang::get('checkout.invalid_payment_method')];
				}

			}catch(Exception $e){
				//dd($e);
				return ['status'=>'fail','msg'=>$e->getMessage()];
			}
			     
		}else{
			$errors =  $validate->errors(); 
            return ['status'=>'fail','msg'=>$validate->errors(),'validation'=>true];
		}   

	}	

	public function submitPayment(Request $request){
        $userid = Auth::User()->id;
        $formatted_id = $request->formatted_id;
        $orderInfo = Order::where(['formatted_id'=>$formatted_id,'user_id'=>$userid,'payment_status'=>0])->where('order_status',1)->first();

        if(empty($orderInfo)){
            return ['status'=>'fail','msg'=>Lang::get('checkout.invalid_order')];
        }

        /***checking payment method******/
        $pay_det = [];
        $user_odd_info = [];
        if(!empty($request->payment_method)){
            $pay_det = \App\PaymentOption::where('id',$request->payment_method)->first();
            if(empty($pay_det)){
                return ['status'=>'fail','type'=>'payment','msg'=>Lang::get('checkout.invalid_payment_method')];
            }
            if($pay_det->slug == 'odd'){
                $user_odd_info = \App\UserInfo::getUserInfo('odd-register');
                if(!$user_odd_info || $user_odd_info->espa_id==''){
                    return ['status'=>'fail','type'=>'payment','msg'=>Lang::get('checkout.you_have_not_register_odd')];
                }
            }
            
        }

        /****calculating pickup time*******/
        $pickup_datetime = null;
        if(isset($request->pickup_time)){
            $pickup_time = $request->pickup_time;
            $nextday = !empty($request->nexday)?$request->nexday:'';
            $ptime = str_replace('_n', '', $pickup_time);
            if(strrpos($pickup_time,'_n')!==false){
                $tomorrow = date("Y-m-d", strtotime("+1 day"));
                $pdate = $tomorrow.' '.$ptime.':00:00';
            }else{
                $pdate = date('Y-m-d').' '.$ptime.':00:00';
            }
            $pickup_datetime = date('Y-m-d H:i:s',strtotime($pdate));
            $new_time = date("Y-m-d H:i:s", strtotime('+3 hours'));

            if(strtotime($new_time) > strtotime($pickup_datetime)){
            	return ['status'=>'fail','type'=>'pickup_time','msg'=>Lang::get('checkout.invalid_pickup_time')];
            }
        }
        $payment_slug = $pay_det->slug;
        $orderInfo->pickup_time = $pickup_datetime;
        $orderInfo->payment_slug = $pay_det->slug;
        $orderInfo->save();

        if($payment_slug == 'kbank'){
            $url = action('Checkout\CartController@kbankPayment',$orderInfo->formatted_id);
            return ['status'=>'success','url'=>$url];
        }
        if($payment_slug == 'payplus'){
            $url = action('Checkout\CartController@payplusPayment',$orderInfo->formatted_id);
            return ['status'=>'success','url'=>$url];
        }
        if($payment_slug == 'odd'){
            $return = $this->oddPayment($orderInfo,$user_odd_info);
            return $return;
        }
    }

	function kbankPayment(Request $request,$formatted_id=null){
		$userid = Auth::User()->id;
		$orderInfo = Order::where(['user_id'=>$userid,'payment_status'=>0,'order_status'=>1,'formatted_id'=>$formatted_id])->whereNull('end_shopping_date')->first();
		
		$kbank_details = [];
		if(!empty($orderInfo)){
			if($orderInfo->payment_slug == 'kbank'){
				$pay_opt = \App\PaymentOption::where('slug',$orderInfo->payment_slug)->first();
				if(!empty($pay_opt)){
					if($pay_opt->mode == 2)
						$kbank_details = json_decode($pay_opt->sandbox_detail,true);
					else
						$kbank_details = json_decode($pay_opt->live_detail,true);
				}
			}
		}
		if(empty($kbank_details)){
			abort(404);
		}
		//dd($orderInfo);

		$order = OrderController::createKbankOrder($orderInfo,$kbank_details);
		
		if($order){

			$order_detail = [];
	        $shop_order = [];
	        $main_order = $orderInfo;
	        if(!empty($main_order)){
	            $order_detail = \App\OrderDetail::getMainOrderDetail($main_order->id);
	            $shop_ord = \App\OrderShop::where('order_id',$main_order->id)->select('id','shop_formatted_id','order_status')->with('getOrderStatus')->get();
	            if(count($shop_ord)){
	                foreach ($shop_ord as $key => $value) {
	                    $status = $value->getOrderStatus->status ?? '';
	                    $shop_order[$value->id] = ['shop_formatted_id'=>$value->shop_formatted_id,'status'=>$status,'order_status'=>$value->order_status];
	                }
	            }
	            
	        }
			return view('checkout.kbank' ,['orderInfo' => $orderInfo, 'kbank_details'=>$kbank_details,'order_id'=>$order,'order_detail'=>$order_detail,'shop_order'=>$shop_order]); 
		}else{
			abort(404);
		}
		
	}

	function payplusPayment(Request $request,$formatted_id=null){
		$userid = Auth::User()->id;
		$orderInfo = Order::where(['user_id'=>$userid,'payment_status'=>0,'order_status'=>1,'formatted_id'=>$formatted_id])->whereNull('end_shopping_date')->first();
		
		$kbank_details = [];
		if(!empty($orderInfo)){

			if($orderInfo->payment_slug == 'payplus'){

				$order_detail = [];
		        $shop_order = [];
		        $main_order = $orderInfo;
		        if(!empty($main_order)){
		            $order_detail = \App\OrderDetail::getMainOrderDetail($main_order->id);
		            $shop_ord = \App\OrderShop::where('order_id',$main_order->id)->select('id','shop_formatted_id','order_status')->with('getOrderStatus')->get();
		            if(count($shop_ord)){
		                foreach ($shop_ord as $key => $value) {
		                    $status = $value->getOrderStatus->status ?? '';
		                    $shop_order[$value->id] = ['shop_formatted_id'=>$value->shop_formatted_id,'status'=>$status,'order_status'=>$value->order_status];
		                }
		            }
		            
		        }
				return view('checkout.payplus' ,['orderInfo' => $orderInfo,'order_detail'=>$order_detail,'shop_order'=>$shop_order]);
			}
		}else{
			abort(404);
		}
		
	}

	function payplusWaiting(Request $request, $order=null){
		if($order){
			$userid = Auth::User()->id;
			$order = Order::where(['user_id'=>$userid,'payment_status'=>0,'order_status'=>1,'formatted_id'=>$order])->whereNull('end_shopping_date')->first();
			return view('checkout.payplusWaiting')->with(["order"=>$order]);
		}else{
			abort(404);
		}
		
	}

	function createPayPlusOrder(Request $request,$formatted_id=null){
		
		$userid = Auth::User()->id;
		$orderInfo = Order::where(['user_id'=>$userid,'payment_status'=>0,'formatted_id'=>$formatted_id])->whereNull('end_shopping_date')->first();
		
		$pay_opt = \App\PaymentOption::where('slug','payplus')->first();
		if(!empty($pay_opt)){
			if($pay_opt->mode == 2)
				$payplus_details = json_decode($pay_opt->sandbox_detail,true);
			else
				$payplus_details = json_decode($pay_opt->live_detail,true);
			
		}
		$secret_key = $payplus_details['web_secret_key'];
		$url = $payplus_details['url'];
        $ref_no = $orderInfo->id;//substr(number_format(time() * rand(),0,'',''),0,10);
        $mobile = $request->input("phone");

        $post_array = array('amount'=>$orderInfo->total_final_price,'currency'=>'THB','description'=>'PayPLUS Description','source_type'=>'kplus_no','number'=>$mobile,'reference_order'=>$orderInfo->id,'ref_1'=>$orderInfo->id,'ref_2'=>$orderInfo->id);
        $post_json = json_encode($post_array);

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $post_json,
          CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/json",
            "x-api-key: ".$secret_key
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        $result = json_decode($response);

        if(isset($result->order_id) && isset($result->status) && $result->status=='success'){

        	$response_array = ["invoice"=>$ref_no,"ref1"=>$orderInfo->id,"ref2"=>$orderInfo->id,"phone"=>$mobile];
            $update_ord = Order::where('id',$orderInfo->id)->update(['kbank_qrcode_id'=>$result->order_id]);

            echo base64_encode(json_encode($response_array));
        }else{
            echo 'error';
        }

	}

	function oddPayment($main_order,$user_odd_info){
		$userid = Auth::User()->id;
		$order_id = $main_order->id;

		$userDetail = Auth::user();

		$amount = number_format($main_order->total_final_price,2);
		$amount = "$amount";
        $ref_no = generateUniqueNo();

        $update_ord = Order::where('id',$order_id)->update(['kbank_qrcode_id'=>$ref_no]);

        $pay_opt = \App\PaymentOption::where('slug','odd')->first();
        if($pay_opt->mode == 2)
            $pay_details = json_decode($pay_opt->sandbox_detail,true);
        else
            $pay_details = json_decode($pay_opt->live_detail,true);

        $espa_id = $user_odd_info->espa_id;
        
        //(pass phrase, external_system,payee_short_name, external_reference,amount)

        $auth_str = $pay_details['pass_phrase'].$pay_details['external_system'].$pay_details['payee_short_name'].$ref_no.$amount;

        $sha = hash('sha256', $auth_str);
        $auth  = strtoupper($sha);

        $post_array = [];
        $post_array['transaction_type'] = $pay_details['transaction_type_checkout'];
        $post_array['transaction_mode'] = $pay_details['transaction_mode'];
        $post_array['encoding'] = $pay_details['encoding'];
        $post_array['external_system'] = $pay_details['external_system'];
        $post_array['auth_parameter'] = $auth;

        $transaction_list = [];
        $transaction_list['user_id'] = "";

        $transaction_list['external_reference'] = $ref_no;
        $transaction_list['payer_short_name'] = '';
        $transaction_list['payee_short_name'] = $pay_details['payee_short_name'];
        $transaction_list['entity_type'] = $pay_details['entity_type'];
        $transaction_list['amount'] = $amount;
        $transaction_list['payer_account'] = '';
        $transaction_list['timestamp'] = date('YmdHis');
        $transaction_list['effective_date'] = date('Ymd');
        $transaction_list['fee_multiplier_factor'] = "";
        $transaction_list['espa_id'] = $espa_id;

        $reference_list = ['reference1'=>"",'reference2'=>"",'reference3'=>"",'reference4'=>""];

        $transaction_list['reference_list'] = [$reference_list];

        $post_array['transaction_list'] = [$transaction_list];

        $post_json = json_encode($post_array);
        
        //https:// 203.146.18.96/ws/v1/registerinit
        //https://ws04.uatebpp.kasikornbank.com/ws/v1/registerinit
        $check_ping_resolve = ["$pay_details[host]:$pay_details[port]:$pay_details[ip]"];
        $ch = curl_init();
       
        curl_setopt($ch, CURLOPT_URL,$pay_details['curl_url']."ssopay");
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_RESOLVE, $check_ping_resolve);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json')
        );

        $server_output = curl_exec($ch);
        //dd($server_output,$pay_details['curl_url']."ssopay",$post_json,$check_ping_resolve);
        $gateway_log_id = \App\OrderGatewayLog::insertLog(['gateway_type'=>'odd','gateway_response'=>$server_output]);
        
        file_put_contents(Config::get('constants.public_path')."/odd_checkout.txt",$server_output);
        $cancel_response_code = '';
        
        if($server_output){
        	$orderInfo = $main_order;
        	$response = json_decode($server_output,true);
        	$cancel_response_code = isset($response['transaction_list'][0]['return_status'])?isset($response['transaction_list'][0]['return_status']):'';
        	if($response &&  isset($response['transaction_list'][0]['return_status']) && $response['transaction_list'][0]['return_status']=='0'){
        		
        		$current_date = date('Y-m-d H:i:s');

        		$update_log = OrderGatewayLog::where('id',$gateway_log_id)->update(['order_id'=>$orderInfo->id]);

        		$invoice = $response['transaction_list'][0]['external_reference'] ?? '';
            	$arr = ['order_id'=>$orderInfo->id,'payment_slug'=>'odd','reference_order'=>$invoice,'items'=>'','response'=>json_encode($response),'created_at'=>$current_date];
            	$update_pay_resp = \App\OrderPayment::insert($arr);

                $updateOrder = Order::updateOrderAfterPayment($orderInfo);

                /*for notification*/
                EmailHelpers::sendOrderNotificationEmail($orderInfo->formatted_id);
                /*for notification*/

                /*send noti at mobile*/
                $this->buyerNotification($orderInfo);

                return ['status'=>'success','url'=>action('Checkout\OrderController@thanks',$orderInfo->formatted_id)];
            }
        }
        if($cancel_response_code !=''){
        	$cancel_url = action('Checkout\OrderController@cancel').'?gateway=odd&code='.$cancel_response_code;
        }else{
        	$cancel_url = action('Checkout\OrderController@cancel');
        }
        return ['status'=>'success','url'=>$cancel_url];
	}

	public function buyerNotification($orderInfo){
        $title = 'New Order';
        $body = 'Order id '. $orderInfo->formatted_id;
        $post_arr = ['user_id'=>$orderInfo->user_id, 'title'=>$title,'body'=>$body, 'type_redirect'=>'payment_success', 'order_id'=>$orderInfo->id, 'formatted_order_id'=>$orderInfo->formatted_id];
        $url = Config::get('constants.mobile_notification_url');
        $responce = $this->handleCurlRequest($url,$post_arr);

    }

	/****backup store function for all product checkout same time*****
	function store(Request $request){

		$input = $request->all();
		$validate = $this->validateCart($input);
		if ($validate->passes()) {
			$formatedId = $request->order_id;
		
			$userid = Auth::User()->id;
			$orderInfo = OrdersTemp::where(['formatted_order_id'=>$formatedId,'user_id'=>$userid,'order_status'=>'0'])->first();
			if(empty($orderInfo)){
				return ['status'=>'fail','msg'=>Lang::get('checkout.invalid_order')];
			}

			if(strlen($orderInfo->total_final_price) > 10) {
				$errorMsg = Lang::get('checkout.order_price_can_not_be_more_than_10_digit');
				return ['status'=>'fail','msg'=>$errorMsg];	
			} 

			$orderId = $orderInfo->id;

			$cartInfo = Cart::getCartList($orderId);
			$price_update = 'N';
			foreach ($cartInfo as $key => $value) {
			    $prdavailqty = $value->getPrd->quantity;
			    $stock = $value->getPrd->stock;
			    $cartQty = $value->quantity;

			    if($cartQty == 0){
			    	$errorMsg = Lang::get('product.product').' "'.$value->getCatDesc->category_name.'" '.Lang::get('checkout.select_quantity');
					
					return ['status'=>'fail','type'=>'quantity','msg'=>$errorMsg];
			    }

			    if($stock == 0 && $cartQty > $prdavailqty){
			    	$errorMsg = Lang::get('product.product').' "'.$value->getCatDesc->category_name.'" '.Lang::get('checkout.quantity_not_available');
					
					return ['status'=>'fail','type'=>'quantity','msg'=>$errorMsg];
			    }

			    if(strtotime($value->getPrd->updated_at) > strtotime($value->created_at)) {
						
					/**Need to discuss then work**
				} 

				/**check valid shipping and billing address*
				$shipping_method = $request->ship_method;
				if($shipping_method == 3){
					$shipping_address_id = $request->ship_address;
					$billing_address_id = $request->bill_address;
					
					$shipping_address = \App\ShippingAddress::where(['user_id'=>$userid,'id'=>$shipping_address_id])->whereIn('address_type',['1','3'])->count();

					$billing_address = \App\ShippingAddress::where(['user_id'=>$userid,'id'=>$billing_address_id])->whereIn('address_type',['2','3'])->count();
					if(!$shipping_address || !$billing_address){
						return ['status'=>'fail','type'=>'address','msg'=>$errorMsg];
					}
				}else{
					$shipping_address_id = $billing_address_id = 0;
				}
				

				/**updating product quantity to hold**
				$qty_hold_arr = [];
				$curdate = date('Y-m-d H:i:s');
			
				foreach ($cartInfo as $key => $value) {
						$qty_hold_arr[] = ['order_id'=>$orderId,'cart_id'=>$value->id,'product_id'=>$value->product_id,'quantity'=>$value->quantity,'created_at'=>$curdate];
				}      
				if(count($qty_hold_arr)){
						\App\OrderQuantityHold::insert($qty_hold_arr);
				}
				$payment_type = 'credit';
				$payment_slug = 'credit';

				$orderUpdate = OrdersTemp::where(['id' => $orderId])->update(['user_id'=>$userid,'payment_type'=>$payment_type,'payment_slug'=>$payment_slug,'shipping_address_id'=>$shipping_address_id,'billing_address_id'=>$billing_address_id,'shipping_method'=>$shipping_method]);

				if($payment_type == 'credit' || $payment_type=='offline'){
					$formattedOrderId = OrderController::saveFinalOrder($orderId);
				}

				dd($orderUpdate);
			}       
		}else{
			$errors =  $validate->errors(); 
            return ['status'=>'fail','msg'=>$validate->errors(),'validation'=>true];
		}   

	}
	/**end backup store function***********/

	/**shipping billing address section start**/
	// get shipping address popup form
	public function cartAddress(Request $request) {

        if($request->call_type == 'ajax_data') {

            $user_detail = Auth::user();

            $def_country_dtl = GeneralFunctions::getDefaultCountryDetail();
            $ship_province_str = '';
            if(getConfigValue('ADDRESS_TYPE') == 'dropdown' && !empty($def_country_dtl)) {
                $ship_province_str = CustomHelpers::getProvinceStateNormalDD($def_country_dtl->id);
            }         
            
            return view('shipBillAddress.addressAdd', ['user_detail'=>$user_detail, 'def_country_dtl'=>$def_country_dtl, 'ship_province_str'=>$ship_province_str, 'address_from'=>'cart', 'address_type'=>$request->address_type]);
        }
	}   

	// when save shipping address
	function saveAddress(Request $request) {

		$input = $request->all();
        $validate = $this->validateAddressForm($input);

        if($validate->passes()) {

            $user_id = Auth::User()->id;

            $data_arr['user_id'] = $user_id;
            $address_data = $this->saveUserShippingBillingAddress($request, $data_arr);
            $address = $address_data['address'];
            $address_type = $address_data['address_type'];

			$addressId = $address->id;            
		
			$str = '<p>'.$request->first_name.' '.$request->last_name.'</p><p>'.$request->address.', '.$request->road.'</p><p>'.$address->city_district.', '.$address->province_state.' '.$request->zip_code.'</p><p>'.Lang::get("customer.tel").' : '.$request->ph_number.'</p>';

			$shipVal = $billVal = $ship_selected = $bill_selected = "";
			if($address_type == '3'){
				$ship_selected = $bill_selected = "selected='selected'";
				$shipVal = $billVal = $str;
			}elseif($address_type == '2' || $request->address_type == 'bill_address'){
				$bill_selected = "selected='selected'";
				$billVal = $str;
			}elseif($address_type == '1' || $request->address_type == 'ship_address'){
				$ship_selected = "selected='selected'";
				$shipVal = $str;
			}

			//$ship_selected = $bill_selected = "selected='selected'";
			//$shipVal = $billVal = $str;			

			$shipingAdd = '<option value="'.$addressId.'" '.$ship_selected.'>'.$request->title.'</option>';
			$billingAdd = '<option value="'.$addressId.'" '.$bill_selected.'>'.$request->title.'</option>';			

			return json_encode(array('status'=>'success','shipVal'=>$shipVal,'billVal'=>$billVal,'shipdd'=>$shipingAdd,'billdd'=>$billingAdd));
        }
        else {
            
            $errors = json_decode($validate->errors()); 
            return json_encode(array('status'=>'validate_error','message'=>$errors));
        }              
	}

	// when user change shipping address
	function changeShipAddress(Request $request){
		$array_server = explode('/',$request->server('HTTP_REFERER'));
		$checkout_type = end($array_server);
		$orderDetails = $paid_product = [];
		$userid = Auth::User()->id;
		$orderInfo = OrdersTemp::where(['user_id'=>$userid,'order_status'=>'0'])->first();
		if(!$orderInfo){
			return json_encode(array('status'=>'fail','msg'=>'order not found'));
		}
		
		if($checkout_type == 'buy-now' || $checkout_type == 'buy-now-end-shopping'){
			$orderDetails = Cart::getCartList($orderInfo->id);
		}
		  
		$main_order = \App\Order::where('temp_formatted_id',$orderInfo->formatted_order_id)->first();

		$total_amount = 0;
		if(count($orderDetails)){
			foreach ($orderDetails as $key => $item) {
				$total_amount += $item->total_price;
			}
		}

		if(!empty($main_order)){
			$paid_product = \App\OrderDetail::getMainOrderDetail($main_order->id);
		}
		if(!empty($request->shipId)){
			$shipAddress = ShippingAddress::where(['user_id'=>$userid,'id'=>$request->shipId])->first();
			$shippingRes = $this->getShippingFee($shipAddress,$orderDetails,$paid_product);
			$shipping_fee = $shippingRes['total_deliver_fee'];
			$discount_fee = $shippingRes['total_logistic_fee'];
			$final_ship_fee = convert_string($shipping_fee);
			$final_discount_fee = convert_string($discount_fee);
			$total_amount += $shipping_fee;
			if(!empty($shipAddress)){
				$str = '<p>'.$shipAddress->first_name.' '.$shipAddress->last_name.'</p><p>'.$shipAddress->address.', '.$shipAddress->road.'</p><p>'.$shipAddress->city_district.', '.$shipAddress->province_state.' '.$shipAddress->zip_code.'</p><p>'.Lang::get("customer.tel").' : '.$shipAddress->ph_number.'</p>';
			}
		}else{
			$final_ship_fee = 'false';
			$final_discount_fee = 'false';
			$str = "";
		}
		
		return json_encode(array('status'=>'success','shipVal'=>$str,'shipping_fee'=>$final_ship_fee,'discount_fee'=>$final_discount_fee,'total_amount'=>convert_string($total_amount),'totAmt'=>$total_amount));
	}

	// when user change billing address
	function changeBillAddress(Request $request){

		$billId = $request->billId;
		$userid = Auth::User()->id;
		$billAddress = ShippingAddress::where(['user_id'=>$userid,'id'=>$billId])->first();

		if(!empty($billAddress)){

			$str = '<p>'.$billAddress->first_name.' '.$billAddress->last_name.'</p><p>'.$billAddress->address.', '.$billAddress->road.'</p><p>'.$billAddress->city_district.', '.$billAddress->province_state.' '.$billAddress->zip_code.'</p><p>'.Lang::get("customer.tel").' : '.$billAddress->ph_number.'</p>';

			return array('status'=>'success','billVal'=>$str);
		}
	}	
	/**shipping billing address section ended**/

	private function checkProductPriceUpdate($order_id) {
			$price_update = 'N';
			$cart_details = Cart::where(['order_id'=>$order_id])->with('getProductDetail')->get();
			foreach ($cart_details as $key => $value) {
					if(strtotime($value->getProductDetail->updated_at) > strtotime($value->created_at)) {
							$prod_dtl_arr['productId'] = $value->product_id;
							$prod_dtl_arr['quantity'] = $value->quantity;

							$productPriceDet = $this->getCartProductPriceByOption($prod_dtl_arr);
							$productPrice = $productPriceDet[2];
							if($productPrice != $value->product_price) {
									$productPriceWithQty = $productPriceDet[0];
									$productPriceWithOption = $productPriceDet[1];
									$originalPrice = $value->getProductDetail->initial_price;
									Cart::where(['id'=>$value['id']])->update(['original_price'=>$originalPrice,'product_price'=>$productPrice,'unit_price'=>$productPriceWithOption,'total_price'=>$productPriceWithQty,'total_final_price'=>$productPriceWithQty]);
									$price_update = 'Y';
							}                            
					}         
			}
			if($price_update == 'Y') {
					OrdersTemp::updateOrderPrice($order_id);
			}

			return $price_update;
	}

	public function releaseHoldQty(Request $request){
		$data = OrderQuantityHold::get();
		$curtime = date('Y-m-d H:i:s');
		$add_minut = 1;
		$release_time = strtotime($curtime . "+$add_minut minutes");
		
		if(count($data)){
			foreach ($data as $key => $value) {
				$release_time = strtotime($value->created_at . "+$add_minut minutes");
				if(strtotime($curtime) >= $release_time){
					$del = OrderQuantityHold::where('id',$value->id)->delete();
				}
			}
		}
	}

}