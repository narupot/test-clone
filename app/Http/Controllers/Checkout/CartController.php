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
use App\OrderQuantityHold;
use App\OrderGatewayLog;
use App\Helpers\GeneralFunctions;
use App\OrderDiscountCode;
use App\DiscountCode;
use App\Helpers\CustomHelpers;
use App\Helpers\EmailHelpers;
use App\Http\Controllers\DiscountCodeController;
use Illuminate\Support\Facades\Auth;
use App\Product;
use App\OrderDetail;
use App\OrderShop;
use Route;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\PaymentOption;
use Carbon\Carbon;
use App\Logs;

class CartController extends MarketPlace {

	public $query;

	public function __construct() {
		$this->middleware('authenticate');
	}

	public function index(Request $request) {
		try {
			DB::beginTransaction();
			$selectCartItems = Cart::where('is_selected',true)->pluck('id')->toArray();
			if(count($selectCartItems) == 0){
				return redirect()->action('Checkout\CartController@shoppingCart');
			}
			$checkout_type = request()->segment(2);

			$shop_address = $orderDetails = $paid_product = $user_address = $def_country_dtl = $shop_id_arr = [];
			$billing_address = $shipping_address = $ship_province_str = '';

			$userid = Auth::User()->id;
			$orderInfo = OrdersTemp::where(['user_id'=>$userid,'order_status'=>'0'])->first();
			$main_order = [];

			if(empty($orderInfo)){
				$orderInfo = OrdersTemp::newOrderTemp(['user_id'=>$userid]);
			}

			$totalAmount = OrdersTemp::updateOrderPrice($orderInfo->id);
			$orderInfo->refresh();
			$update_cart = Cart::where(['order_id'=>$orderInfo->id,'cart_status'=>2])->update(['cart_status'=>1]);
			$orderDetails = [];

			if($checkout_type == 'buy-now' || $checkout_type == 'buy-now-end-shopping'){
				$orderDetails = Cart::getCartList($orderInfo->id,[],$selectCartItems);
				if($orderDetails && count($orderDetails) > 0){
					foreach ($orderDetails as $key => $value) {
						if(!empty($value->getShop)){
							$shop_name = $value->getShopDesc->shop_name??'';
							$shop_address[$value->getShop->id] = [
								'shop_name'=>$shop_name,
								'panel_no'=>$value->getShop->panel_no,
								'market'=>$value->getShop->market,
								'ph_number'=>$value->getShop->ph_number
							];
							$shop_id_arr[$value->getShop->id] = $value->getShop->id;
						}
					}
				}
			}
			// if(!empty($main_order)){
			// 	$paid_product = \App\OrderDetail::getMainOrderDetail($main_order->id);
			// }
			
			/***for address***/
			if($checkout_type == 'end-shopping' || $checkout_type == 'buy-now-end-shopping' ){

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

        	$payment_option = \App\PaymentOption::where(['status'=>'1','payment_type'=>'1'])
            ->where('slug','!=','credit')
            // ->with(['paymentOptName', 'transactionFeeConfig'])
            ->get();

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
				$nd_arr = [];
				
				if($delivery_time->delivery_type !='shop_address'){
					foreach ($time_slot as $tkey => $tvalue) {
						
						$same_next_day_cut_off_order_time = $tvalue - $delivery_time->delivery_time_after;
						$cut_off_order_time = $tvalue - $delivery_time->delivery_time_after;
						if($cut_off_order_time<0){
							$cut_off_order_time = 24 + $cut_off_order_time;
						}

						if($delivery_time->delivery_type=='buyer_address'){
							$add_two = ($tvalue+3);
							$add_day = 1;
							$ndate = date('Y-m-d', strtotime(' +'.$add_day.' day'));
							$val_show = $tvalue.':00 - '.($add_two);
							
							if($tvalue >= $cur_time_start){
								if($add_two>=24){
									$add_two = $add_two-24;
									$val_show = $tvalue.':00 - '.($add_two);
									$ndate = date('Y-m-d', strtotime(' +1 day'));
									$expdate = explode('-', $ndate);
									$c_arr[] = ['key'=>$tvalue,'val'=>'('.$expdate[2].' '.getThaiMonth(date($expdate[1])).') '.$val_show.':00' ];
								}else{
									$c_arr[] = ['key'=>$tvalue,'val'=>$val_show.':00'];
								}

							}else{
								if($add_two>=24){
									$ndate = date('Y-m-d', strtotime(' +2 day'));
									$add_two = $add_two-24;
									$val_show = $tvalue.':00 - '.($add_two);
								}else{
									if($cut_off_order_time<=$cur_hr && $same_next_day_cut_off_order_time<=0){
										$ndate = date('Y-m-d', strtotime(' +2 day'));
										$expdate = explode('-', $ndate);
										$nd_arr[] = ['key'=>$tvalue.'nd_n','val'=>'('. $expdate[2].' '.getThaiMonth(date($expdate[1])).') '.$val_show.':00' ];
									
									}else{
										$expdate = explode('-', $ndate);
										$n_arr[] = ['key'=>$tvalue.'_n','val'=>'('. $expdate[2].' '.getThaiMonth(date($expdate[1])).') '.$val_show.':00' ];
									}
								}
								
							}
						}else{
							if($tvalue >= $cur_time_start){
								$c_arr[] = ['key'=>$tvalue,'val'=>$tvalue.':00'];
							}else{
								$ndate = date('Y-m-d', strtotime(' +1 day'));
								$expdate = explode('-', $ndate);
								$n_arr[] = ['key'=>$tvalue.'_n','val'=>'('. $expdate[2].' '.getThaiMonth($expdate[1]).')'.$tvalue.':00'];
							}
						}
						
					}
				}else{
					$j=0;
					$next_time=0;
					for($i=1;$i<=12;$i++){
						if($i==1){
							$next_time = $cur_time_start;
						}else{
							$next_time = $next_time +1;
						}
						
						if($next_time >=24){
							$ndate = date('Y-m-d', strtotime(' +1 day'));
							$expdate = explode('-', $ndate);
							$n_arr[] = ['key'=>$j.'_n','val'=>'('. $expdate[2].' '.getThaiMonth($expdate[1]).') '.$j.':00' ];
							$j++;
						}else{
							$c_arr[] = ['key'=>$next_time,'val'=>$next_time.':00'];
						}
					}
				}

					$time_arr = array_merge($c_arr, $n_arr, $nd_arr);

					usort($time_arr, function($a, $b) {
						$isDayA = preg_match('/\(\d+/', $a['val']);
						$isDayB = preg_match('/\(\d+/', $b['val']);

						if ($isDayA != $isDayB) {
							return $isDayA ? 1 : -1;
						}

						return strnatcmp($a['val'], $b['val']);
					});


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
			
			$shipping_fee = 0.00;
			$user_odd_info = \App\UserInfo::getUserInfo('odd-register');
			
			$transaction_fees = [
				'qr_code' => \App\TransactionFeeConfig::where('name', 'QR Code')->first(),
				'mobile_banking' => \App\TransactionFeeConfig::where('name', 'Mobile Banking')->first(),
				'credit_card' => \App\TransactionFeeConfig::where('name', 'Credit_Debit Card')->first(),
				'wallet' => \App\TransactionFeeConfig::where('name', 'Wallet')->first(),
			];

			DB::commit();
			return view('checkout.cart',compact(
					'def_country_dtl','ship_province_str','user_address','shipping_address',
					'billing_address','payment_option','checkout_type','pickup_center_address','delivery_details'
				),
				[
					'orderInfo' => $orderInfo,
					'orderDetails'=>$orderDetails,
					'page_class'=>'cart-wrap',
					'breadcrumb'=>$breadcrumb,
					'shop_address'=>$shop_address,
					'main_order'=>$main_order,
					'paid_product'=>$paid_product,
					'shipping_fee'=>$shipping_fee,
					'user_odd_info'=>$user_odd_info,
					'time_arr'=>[],'delivery_time_arr'=>$delivery_time_arr,
					'transaction_fees'=>$transaction_fees
				]
			);
		
		} catch (Exception $e) {
			DB::rollBack();
			Log::error($e);
			return redirect()->back()->with('error', Lang::get('messages.something_went_wrong'));
		}
	}

	public function deleteTempOrder(Request $request){
        return view('checkout.cartRemove');
    }

    public function checkCartExist(Request $request){
    	$userid = Auth::User()->id;
		$orderInfo = OrdersTemp::where(['user_id'=>$userid,'order_status'=>'0'])->first();
		if(!$orderInfo){
			return['status'=>'notexist','url'=>action('Checkout\CartController@deleteTempOrder')];
		}else{
			return['status'=>'exist'];
		}
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
		$shipProfileData = \App\ShippingProfile::where('id','1')->first();
		
		$total_deliver_fee = 0;
		$total_logistic_fee = 0;
		if(count($orderDetails)){
			foreach ($orderDetails as $key => $orderItem) {
				$itemsShipFees =    $this->getCalculateProductsShipFee($orderDetails, $shipping_address,$shipProfileData);
				if($itemsShipFees && isset($itemsShipFees['shipping_fee'])){
					$total_deliver_fee = $itemsShipFees['shipping_fee'];
			    	$total_logistic_fee = $itemsShipFees['logistic_fee'];
				}else{
					$total_deliver_fee = $shipProfileData->minimal_rate;
			    	$total_logistic_fee = $shipProfileData->minimal_rate;
				}
			}
		}elseif(count($paid_product)){
			foreach ($paid_product as $key => $product) {
				$itemsShipFee = $this->getProductShipFee($product,$shipping_address,$shipProfileData);
				$total_deliver_fee += $itemsShipFee['shipping_fee'];
				$total_logistic_fee += $itemsShipFee['logistic_fee'];
			}
		}
		return ['total_deliver_fee'=>$total_deliver_fee,'total_logistic_fee'=>$total_logistic_fee];
	}

	protected function calculateShippingFeeOfMethod($shippingMethodData){
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

				$base_rate_for_order = $shippingMethodData['product_rate_array'][$rateVal]['base_rate_for_order'] ?? 0;
				$logistic_base_rate_for_order = $shippingMethodData['product_rate_array'][$rateVal]['logistic_base_rate_for_order'] ?? 0;

				$products_var_total_fee += ($shippingMethodData['product_rate_array'][$rateVal]['fee_percentage_rate_per_product'] ?? 0) +
										($shippingMethodData['product_rate_array'][$rateVal]['fee_fixed_rate_per_product'] ?? 0) +
										($shippingMethodData['product_rate_array'][$rateVal]['fee_fixed_rate_per_unit'] ?? 0);

				$logistic_products_var_total_fee += ($shippingMethodData['product_rate_array'][$rateVal]['logistic_fee_percentage_rate_per_product'] ?? 0) +
												($shippingMethodData['product_rate_array'][$rateVal]['logistic_fee_fixed_rate_per_product'] ?? 0) +
												($shippingMethodData['product_rate_array'][$rateVal]['logistic_fee_fixed_rate_per_unit'] ?? 0);
			} else if (!empty($shippingMethodData['product_rate_array'])) {
				$base_rate_for_order = $shippingMethodData['product_rate_array'][0]['base_rate_for_order'] ?? 0;
				$products_var_total_fee += ($shippingMethodData['product_rate_array'][0]['fee_percentage_rate_per_product'] ?? 0) +
										($shippingMethodData['product_rate_array'][0]['fee_fixed_rate_per_product'] ?? 0) +
										($shippingMethodData['product_rate_array'][0]['fee_fixed_rate_per_unit'] ?? 0);

				$logistic_base_rate_for_order = $shippingMethodData['product_rate_array'][0]['logistic_base_rate_for_order'] ?? 0;
				$logistic_products_var_total_fee += ($shippingMethodData['product_rate_array'][0]['logistic_fee_percentage_rate_per_product'] ?? 0) +
												($shippingMethodData['product_rate_array'][0]['logistic_fee_fixed_rate_per_product'] ?? 0) +
												($shippingMethodData['product_rate_array'][0]['logistic_fee_fixed_rate_per_unit'] ?? 0);
			}
			
			$total_shipping_fee = $base_rate_for_order + $products_var_total_fee;
			$logistic_total_shipping_fee = $logistic_base_rate_for_order + $logistic_products_var_total_fee;

			switch ($shippingMethodData['shipping_calculation_type'] ?? '0') {
				case '0':
					$shipping_and_handling_fee = $total_shipping_fee;
					$logistic_shipping_and_handling_fee = $logistic_total_shipping_fee;
				break;
				case '1':
					$minimal_rate = $shippingMethodData['minimal_rate'] ?? 0;
					$shipping_and_handling_fee = ($minimal_rate <= $total_shipping_fee) ? $minimal_rate : $total_shipping_fee;
					$logistic_shipping_and_handling_fee = ($minimal_rate <= $logistic_total_shipping_fee) ? $minimal_rate : $logistic_total_shipping_fee;
				break;
				case '2':
					$maximal_rate = $shippingMethodData['maximal_rate'] ?? 0;
					$shipping_and_handling_fee = ($maximal_rate > $total_shipping_fee) ? $maximal_rate : $total_shipping_fee;
					$logistic_shipping_and_handling_fee = ($maximal_rate > $logistic_total_shipping_fee) ? $maximal_rate : $logistic_total_shipping_fee;
				break;
			}

			$returnShippingMethods = array(
				'shipping_fee' => $shipping_and_handling_fee,
				'logistic_fee' => $logistic_shipping_and_handling_fee ?? 0
			);

			return $returnShippingMethods;
		}
	}

	protected function getProductShipFee($item,$shipping_address,$shipProfileData){
		// Clacilation of product factor weight
		$productPackageData = \App\Product::where('product.id',$item->product_id)
							->leftJoin(with(new \App\Package)->getTable().' as pkg','pkg.id','=','product.package_id')
							->leftJoin(with(new \App\Unit)->getTable().' as unit','unit.id','=','product.base_unit_id')
							->select('pkg.height','pkg.width','pkg.depth','product.package_id','product.weight_per_unit','unit.unit_weight')->first();


		$total_weight = ($shipProfileData->use_dimension_weight=='1') ? round($item->quantity * 1000 * (($productPackageData->height * $productPackageData->width * $productPackageData->depth)/$shipProfileData->dimension_factor),2):round($item->quantity * 1000 * $productPackageData->weight_per_unit * $productPackageData->unit_weight,2);
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
							->orderBy('spr.zip_to', 'desc')
							->get();$shippingMethod = [];
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

		$astric = "*";
		$product_type = '0';
		$shipping_profile_id = $shipProfileData->id;

		$shipping_rate_data = DB::table(with(new \App\ShippingProfileRates)->getTable().' as spr')->select('spr.*')
		
		->leftJoin(with(new \App\ShippingProfileRatesDesc)->getTable().' as sprd', 'spr.id', '=', 'sprd.rate_id')
		->select('spr.id', 'spr.country_id', 'sprd.province_state','sprd.district_city', 'sub_district', 'spr.weight_from', 'spr.weight_to', 'spr.qty_from', 'spr.qty_to', 'spr.price_from', 'spr.price_to', 'spr.zip_from', 'spr.zip_to', 'spr.product_type_id','spr.estimate_shipping','spr.base_rate_for_order','spr.percentage_rate_per_product','spr.fixed_rate_per_product','spr.fixed_rate_per_unit_weight','spr.logistic_base_rate_for_order','spr.logistic_percentage_rate_per_product','spr.logistic_fixed_rate_per_product','spr.logistic_fixed_rate_per_unit_weight','spr.priority')
		                    //->whereIn('spr.country_id',[$shipping_address->country_id,$astric])
							->whereIn('sprd.province_state',[$shipping_address->province_state,$astric])
							->whereIn('sprd.district_city',[$shipping_address->city_district,$astric])
							//->whereIn('sprd.sub_district',[$shipping_address->sub_district,$astric])
							->where('sprd.lang_id', session('default_lang'))

							//->where('spr.shipping_profile_id', $shipping_profile_id)
							//->where('spr.weight_from','<=',$total_weight)
							//->where('spr.weight_to','>=',$total_weight)
							//->where('spr.qty_from','<=',$total_qty)
							//->where('spr.qty_to','>=',$total_qty)
							->where('spr.price_from','<=',$total_price)
							->where('spr.price_to','>=',$total_price)
							//->where('spr.product_type_id',$product_type)
							->whereRaw("zip_from <= IF(zip_from != '*',?,'*')",[$shipping_address->zip_code])
							->whereRaw("zip_to >= IF(zip_to != '*',?,'*')",[$shipping_address->zip_code])
							->orderBy('spr.zip_to', 'desc')
							->get();

		$shippingMethod = [];
		foreach($shipping_rate_data as $shp_key => $rateData){
			if($rateData->province_state<>'*'){
				$base_rate_for_order = $rateData->base_rate_for_order;
				/*tong j start*/
				$fee_percentage_rate_per_product = floor((($total_price * $rateData->percentage_rate_per_product)/100));
				/*tong j stop*/
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
		}

		$shippingFeeData = $this->calculateShippingFeeOfMethod($shippingMethod);

		return $shippingFeeData;
	}

	public function shoppingCart(Request $request) {

		$userid = Auth::User()->id;
		$orderInfo = OrdersTemp::where(['user_id'=>$userid,'order_status'=>'0'])->first();
		$user_credits = Credits::getUserCredit($userid);
		$show_credit = 0;
		$orderDetails = $cartAttributeData = $promotion = [];
		$inactiveItems = $activeItems = $priceChange = $closeProduct = $shopClose = $outOfStock = $passItems = [];
		$total_prom_disc_amt = 0;
		$shop_details = [];

		if($orderInfo){
			$direction = $request->sort??'asc';
			$orderDetails = Cart::getCartList($orderInfo->id)
			->{ $direction === 'desc' ? 'sortByDesc' : 'sortBy' }('created_at')
			->values();

			// validate cart items
			$cartItemIds = $orderDetails->pluck('id')->toArray();
			$newRequest = new Request(['cartItems' => $cartItemIds]);
			$validateResult = (new self)->validateProductCartItem($newRequest);
			$rsValid = $validateResult->getData();

			$passItems = collect($rsValid->passItems ?? [])->map(fn($item) => Cart::find($item->id))->filter();
			$priceChange = collect($rsValid->priceChange ?? [])->map(function ($item) {
				$cartItem = Cart::find($item->id);
				if ($cartItem) { $cartItem->is_price_change = true; }
				return $cartItem;
			})->filter();
			$closeProduct = collect($rsValid->productClose ?? [])->map( function ($item) {
				$cartItem = Cart::find($item->id);
				if ($cartItem) { $cartItem->is_product_close = true; }
				return $cartItem;
			})->filter();
			$shopClose = collect($rsValid->shopClose ?? [])->map(function ($item) {
				$cartItem = Cart::find($item->id);
				if ($cartItem) { $cartItem->is_shop_close = true; }
				return $cartItem;
			})->filter();
			$outOfStock = collect($rsValid->outOfStock ?? [])->map(function ($item) {
				$cartItem = Cart::find($item->id);
				if ($cartItem) { $cartItem->is_out_of_stock = true; }
				return $cartItem;
			})->filter();
			$shortStock = collect($rsValid->shortStock ?? [])->map(function ($item) {
				$cartItem = Cart::find($item->id);
				if ($cartItem) { $cartItem->is_short_stock = true; }
				return $cartItem;
			})->filter();

			$shortStock->each(function($item) {
				$item->is_short_stock = true;
			});

			$activeItems = $passItems->concat($priceChange)->concat($shortStock);
			// กรองเฉพาะ cart ที่มี product
			

			$inactiveItems = $closeProduct->concat($shopClose)->concat($outOfStock);
			Cart::unselectByIds([...$inactiveItems->pluck('id')->toArray(),...$shortStock->pluck('id')->toArray()]);
			// $orderDetails = $activeItems->{ $direction === 'desc' ? 'sortByDesc' : 'sortBy' }('id')->values();
			
			OrdersTemp::updateOrderPrice($orderInfo->id);
			$orderInfo->refresh();
		}


		// $referer_url = $request->headers->get('referer');
		// $breadcrumb = $this->getBreadcrumb($referer_url);

		// $default_shopping = session('shopping_list');
        // $pur_prds_in_shop_list = \App\UserShoppingListItems::where(['shopping_list_id'=>$default_shopping,'is_completed'=>'1'])->count();
        // $total_prds_in_shop_list = \App\UserShoppingListItems::where(['shopping_list_id'=>$default_shopping])->count();
		// return $orderDetails;

		$activeItems = $activeItems ?$activeItems->filter(function($item) { return $item->getPrd !== null; }) : collect();
		$inactiveItems	= $inactiveItems ?$inactiveItems->filter(function($item) { return $item->getPrd !== null; }) : collect();

		return view('checkout.shopping_cart', [
			'orderInfo' => $orderInfo,
			'orderDetails'=>$activeItems,
			'inactiveItems'=>$inactiveItems,
			'page_class'=>'cart-wrap',
			// 'breadcrumb'=>$breadcrumb,
			'user_credits'=>$user_credits,
			// 'shop_details'=>$shop_details,
			'show_credit'=>$show_credit,
			'page'=>'shopping_cart',
			// 'total_prds_in_shop_list'=>$total_prds_in_shop_list,
			// 'pur_prds_in_shop_list'=>$pur_prds_in_shop_list
		]);
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
		try {
			$cartId = $request->cartId;
			$newQuantity = (int)$request->quantity??0;
			$userid = Auth::User()->id;

			$cartresult = Cart::where(['id'=>$cartId,'user_id'=>$userid])->first();
			if(!empty($cartresult)){

				$oldQty = $cartresult->quantity;
				$changeQty = $newQuantity - $oldQty;
				$cartProductId = $cartresult->product_id;
				$product_det = $cartresult->getPrd??null;
				$shop_det = $cartresult->getShop??null;
				$package_name = $product_det->package->packagedesc->package_name??'';

				/******checking bargaining********/
				if($cartresult->product_from == 'bargain'){
					return ['status'=>'fail','msg'=>Lang::get('checkout.popup_price_has_already_bargained'),'qty'=>$oldQty];
				}

				$prd_min_order_qty = $product_det->min_order_qty??1;
				$prd_quantity = $product_det->quantity??0;
				// validate minimum order quantity
				if($product_det->order_qty_limit === '0' && ($newQuantity < $prd_min_order_qty) ) {
					return [
						'status'=>'fail',
						'msg'=>Lang::get('checkout.product_minimum_quantity_should_be').$prd_min_order_qty .' '.$package_name??'',
						'cartquantity'=>$cartresult->quantity,
						'maxqty'=>$product_det->quantity,
						'maxvalue'=>$product_det->quantity + $cartresult->quantity,
						"min_order_qty"=>$product_det->min_order_qty
					];
				}
				
				// validate stock overstock
				if($product_det->stock ==='0' && ($newQuantity > $product_det->quantity ) ){
					if($request->flag != 'decrease'){
						return [
							'status'=>'fail_maxqty',
							'msg'=>Lang::get('checkout.please_enter_quantity_less_or_equal')."\nจำนวนสต๊อกคงเหลือ ".$product_det->quantity.' '.$package_name??'',
							'cartquantity'=>$cartresult->quantity,
							'maxqty'=>$product_det->quantity,
							'maxvalue'=>$product_det->quantity + $cartresult->quantity,
							"min_order_qty"=>$product_det->min_order_qty
						];
					}
				}
				
				$product_price = GeneralFunctions::getProductPriceById($product_det->id,$newQuantity,$product_det);
				$total_price = $product_price * $newQuantity;
			
				// update smm_cart->quantity
				$affected = Cart::where(['id' => $cartId])
				->update([
					'quantity'=>$newQuantity,
					'original_price'=>$product_price,
					'cart_price' => $product_price,
					'total_price'=>$total_price
				]);
				$orderFinalPrice = OrdersTemp::updateOrderPrice($cartresult->order_id);
				$totQty = Cart::where('order_id',$cartresult->order_id)->sum('quantity');
				return [
					'status'=>'success',
					'ordAmount'=>number_format($orderFinalPrice,2),
					'totQty'=>$totQty,
					'tot_prd_price'=>number_format($total_price,2),
					'product_price'=>number_format($product_price,2),
					"cartid_"=>$cartresult->id,
					"min_order_qty"=>$product_det->min_order_qty,
					'cartquantity'=>$cartresult->quantity,
					'productQuantity'=>$product_det->quantity
				];
			}else{
				return ['status'=>'fail','msg'=>'invalid cart'];
			}
			
		} catch (\Exception $e) {
			Log::error('Update Cart Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
			return [
				'status' => 'error',
				'msg' => 'System error, please try again later.'
			];
		}
	}

 	/****Remove product from cart********/
	function removeCart(Request $request){
		$cartId = $request->cartId;
		$userid = Auth::User()->id;

		$cartresult = Cart::select('order_id','product_id','quantity')->where(['id'=>$cartId,'user_id'=>$userid])->first();
		
		if(!empty($cartresult)){
			$orderId = $cartresult->order_id;
			
			Cart::where('id', $cartId)->delete();
			$check_cart = Cart::where('order_id',$orderId)->count();
			$returnArr = [];
			if($check_cart>0){
				$totQty = Cart::where('order_id',$orderId)->sum('quantity');
				$orderFinalPrice = OrdersTemp::updateOrderPrice($orderId);
				$cart_item = Cart::totCartPrd($userid);
				$msg = Lang::get('checkout.product_deleted_successfully');
				$returnArr = array('delete'=>'cart','ordAmount'=>number_format($orderFinalPrice,2),'totQty'=>$totQty,'msg'=>$msg,'cart_item'=>$cart_item);
			}else{
				$temp_formatted_id = OrdersTemp::where('id', $orderId)->value('formatted_order_id');
				if($temp_formatted_id){
					$check_ord = \App\Order::where('temp_formatted_id',$temp_formatted_id)->count();
					if(empty($check_ord)){
						OrdersTemp::where('id', $orderId)->delete();
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

	public function removeMultiCart(Request $request)
	{
		$cartIds = (array) $request->cartIds;
		$userId  = Auth::id();

		$cartResults = Cart::whereIn('id', $cartIds)
			->where('user_id', $userId)
			->get(['id', 'order_id', 'product_id', 'quantity']);

		if ($cartResults->isEmpty()) {
			return ['status' => 'fail', 'msg' => 'invalid cart'];
		}

		$groupedByOrder = $cartResults->groupBy('order_id');
		$returnArr = [];

		foreach ($groupedByOrder as $orderId => $carts) {
			Cart::whereIn('id', $carts->pluck('id'))->delete();

			$checkCart = Cart::where('order_id', $orderId)->count();

			if ($checkCart > 0) {
				$totQty = Cart::where('order_id', $orderId)->sum('quantity');
				$orderFinalPrice = OrdersTemp::updateOrderPrice($orderId);
				$cartItem = Cart::totCartPrd($userId);
				$msg = Lang::get('checkout.product_deleted_successfully');

				$returnArr[] = [
					'delete'    => 'cart',
					'ordAmount' => number_format($orderFinalPrice, 2),
					'totQty'    => $totQty,
					'msg'       => $msg,
					'cart_item' => $cartItem,
					'order_id'  => $orderId,
				];
			} else {
				$tempFormattedId = OrdersTemp::where('id', $orderId)->value('formatted_order_id');
				if ($tempFormattedId) {
					$checkOrd = \App\Order::where('temp_formatted_id', $tempFormattedId)->count();
					if (empty($checkOrd)) {
						OrdersTemp::where('id', $orderId)->delete();
					}
				}

				$msg = Lang::get('checkout.order_deleted_successfully');

				$returnArr[] = [
					'delete'   => 'order',
					'msg'      => $msg,
					'order_id' => $orderId,
				];
			}
		}

		return [
			'status' => 'success',
			'results' => $returnArr,
		];
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
					if($request->type == 'buynow'){
						$url = route('buy-now');
					}else{
						$url = route('buy-now-end-shopping');
					}
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
				$rules['bill_address'] = reqRule();
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

		Log::info('Checkout Store Request: ', $request->all());
		try {
			DB::beginTransaction();
			$input = $request->all();
			$validate = $this->validateCart($input);
			if ($validate->fails()) {
				return ['status'=>'fail','msg'=>$validate->errors(),'validation'=>true];
			}

			$formatedId = $request->order_id;

			$userid = Auth::User()->id;
			$orderInfo = OrdersTemp::where(['formatted_order_id'=>$formatedId,'user_id'=>$userid,'order_status'=>'0'])->first();

			if(empty($orderInfo)){
				return ['status'=>'fail','msg'=>Lang::get('checkout.invalid_order'),'type'=>'invalid'];
			}
			/***only end shopping means order has paid product already*****/
			if($request->checkout_type =='end-shopping'){
				$main_order = \App\Order::where('temp_formatted_id',$orderInfo->formatted_order_id)->first();
				if(empty($main_order)){
					return ['status'=>'fail','msg'=>Lang::get('checkout.invalid_order')];
				}
			}
			$orderId = $orderInfo->id;
			$cartInfo = $orderInfo->getCart()->where('is_selected',1)->get();

			/*** validate product cart item */
			$newRequest = new Request(['cartItems' => $cartInfo->pluck('id')->toArray()]);
			$validateResult = (new self)->validateProductCartItem($newRequest);
			$rsValid = $validateResult->getData();
			if ($rsValid->status === 'error' || count($rsValid->passItems) === 0) {
				return response()->json([
					'status' => $rsValid->status??'error',
					'message' => $rsValid->message??'สิ้นค้ามีการเปลี่ยนแปลง กรุณาตรวจสอบอีกครั้ง'
				]);
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

			/**** calculating pickup time (Synced With Slot Logic) ****/
			$pickup_datetime = null;
			$end_pickup_datetime = null;
			$pickup_id = null;

			if (!empty($request->pickup_time)) {

				$slotId = $request->pickup_time;
				$pickup_id = $slotId;

				$slot = \App\DeliveryTimeSlot::find($slotId);

				if ($slot) {

					$now = \Carbon\Carbon::now('Asia/Bangkok');

					$cutoffTime   = $slot->order_cutoff_time;
					$deliPlusDays = (int) ($slot->deli_plus_days ?? 0);

					$startDiffMinutes = (int) $slot->start_deli_time;
					$endDiffMinutes   = (int) $slot->end_deli_time;

					$startDiffMinutes %= 1440;
					$endDiffMinutes   %= 1440;
					$isOverCutoff = $now->format('H:i:s') > $cutoffTime;
					$cutoffDays   = $isOverCutoff ? 1 : 0;

					$totalDays = $deliPlusDays + $cutoffDays;

					$baseDate = $now->copy()
						->startOfDay()
						->addDays($totalDays);

					$cutoffDateTime = \Carbon\Carbon::parse(
						$baseDate->format('Y-m-d') . ' ' . $cutoffTime,
						'Asia/Bangkok'
					);

					$pickupStartDateTime = $cutoffDateTime->copy()
						->addMinutes($startDiffMinutes);

					$pickupEndDateTime = $cutoffDateTime->copy()
						->addMinutes($endDiffMinutes);

					$pickup_datetime     = $pickupStartDateTime->toDateTimeString();
					$end_pickup_datetime = $pickupEndDateTime->toDateTimeString();

					\Log::info("--- Pickup Calculation (Start + End) ---");
					\Log::info("Slot ID: {$slotId}");
					\Log::info("Cutoff Days: {$cutoffDays}");
					\Log::info("Deli Plus Days: {$deliPlusDays}");
					\Log::info("Start Pickup: {$pickup_datetime}");
					\Log::info("End Pickup: {$end_pickup_datetime}");
				}
			}


			
			/** check valid shipping and billing address*/
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
					$errorMsg = "ไม่สามารถดำเนินการต่อได้ เนื่องจากข้อมูลที่อยู่สำหรับจัดส่ง (Shipping Address) หรือที่อยู่สำหรับออกใบเสร็จ (Billing Address) ไม่ถูกต้อง กรุณาตรวจสอบและกรอกข้อมูลให้ครบถ้วนอีกครั้ง";
					return ['status'=>'fail','type'=>'address','msg'=>$errorMsg];
				}
			}else{
				$shipping_address_id = $billing_address_id = 0;
				$user_phone_no = $request->phone_no;
				$billing_address_id = $request->bill_address;
				$billing_address = \App\ShippingAddress::where(['user_id'=>$userid,'id'=>$billing_address_id])->count();
			}

			$orderTmpPrice = $orderInfo->updateOrderPrice($orderId);
			$dcc_discount_purchase = 0;
			$dcc_discount_shipping = 0;
			
			$total_purchase = $orderTmpPrice ;
			$total_shipping_cost = $shipping_fee;
			$total_logistic_cost = $logistic_fee;

			/** check discount code */
			$total_discount = 0;
			if($request->dcc_discount_code && $request->dcc_discount_code !=''){
				$req_dcc = [
					'tot_amt_after_discount'	=>	(float)$request->tot_amt_after_discount,
					'dcc_discount_code'	=>	$request->dcc_discount_code,
					'dcc_purchase'	=>	(float)$request->dcc_purchase,
					'dcc_shipping'	=>	(float)$request->dcc_shipping,
				];
				$newRequest = new Request([
					'code' => $req_dcc['dcc_discount_code'],
					'purchase' => $total_purchase,
					'shippingCost' => $total_shipping_cost
				]);
				$discountController = new DiscountCodeController();
				$rs = $discountController->calulateDiscount($newRequest);
				$rsData = $rs->getData();

				if($rsData && $rsData->status ==='success'){
					$dcc_discount_purchase = (float) $rsData->data->discountPurchase;
					$dcc_discount_shipping = (float) $rsData->data->discountShipping;
					$dcc_total_discount = $dcc_discount_purchase+$dcc_discount_shipping;

					if( $orderTmpPrice - $dcc_discount_purchase <= 0){
						$dcc_discount_purchase = $orderTmpPrice;
						$orderTmpPrice = 0;
					}else{
						$orderTmpPrice -= $dcc_discount_purchase;
					}
					
					if($shipping_fee > 0 ){
						if($shipping_fee - $dcc_discount_shipping <= 0) {
							$dcc_discount_shipping = $shipping_fee;
							$shipping_fee = 0;
						}else{
							$shipping_fee -= $dcc_discount_shipping;
						}
					}else{
						$dcc_discount_shipping=0;
					}
					
					$msgErr = [];
					if (isset($req_dcc['dcc_purchase']) && $req_dcc['dcc_purchase'] != $dcc_discount_purchase) {
						array_push($msgErr,'ส่วนลดยอดซื้อไม่ถูกต้อง');
					}
					if (isset($req_dcc['dcc_shipping']) && $req_dcc['dcc_shipping'] != $dcc_discount_shipping) {
						array_push($msgErr,'ส่วนลดค่าขนส่งไม่ถูกต้อง');
					}
					if (isset($req_dcc['tot_amt_after_discount']) && round((float)$req_dcc['tot_amt_after_discount'],2) !=
						round((float)(($total_purchase + $total_shipping_cost) - $dcc_total_discount),2)) {
						array_push($msgErr,'ยอดสั่งซื้อสินค้าหลักหักส่วนลดไม่ถูกต้อง');
					}
					if(count($msgErr)>0){
						return [
							'status' => 'fail','type'=>'discount_code',
							'msg' => implode(', ', $msgErr),
						];
					}
				}elseif($rsData && $rsData->status ==='fail'){
					return ['status'=>'fail','type'=>'discount_code','msg'=>$rsData->message];
				}else{
					return ['status'=>'fail','type'=>'discount_code','msg'=>"ไม่พบโค้ดส่วนลด"];
				}
				$total_discount = $dcc_discount_purchase+$dcc_discount_shipping+$logistic_fee;
			}
			$total_final_price = $orderTmpPrice + $shipping_fee;

			$transactionFeeRate = $pay_det->transactionFeeConfig->current_tf ?? 0;
			$transactionFee = round($total_final_price * $transactionFeeRate / 100,2);
			if($transactionFee != $request->transaction_fee){
				return ['status'=>'fail','type'=>'payment','msg'=>Lang::get('checkout.invalid_payment_method')];
			}
			$total_final_price = round($total_final_price + $transactionFee, 2);
			
			$payment_type = $payment_slug = '';
			if($request->checkout_type =='end-shopping'){
				if($shipping_fee > 0){
					if(empty($pay_det)){
						return ['status'=>'fail','type'=>'payment','msg'=>Lang::get('checkout.invalid_payment_method')];
					}
				}
			}elseif($total_final_price > 0){
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
				if(strpos($pay_det->slug, 'beam') === 0){
					$payment_type = 'online';
					$payment_slug = $pay_det->slug;
				}
			}
			$dataUpdate =[
				'payment_type'=>$payment_type,
				'payment_slug'=>$payment_slug,
				'shipping_address_id'=>$shipping_address_id,
				'billing_address_id'=>$billing_address_id,
				'shipping_method'=>$shipping_method,
				'total_final_price'=>$total_final_price,
				'total_shipping_cost'=>$total_shipping_cost,
				'total_logistic_cost'=>$total_logistic_cost,
				'pickup_time'=>$pickup_datetime,
				'end_pickup_time'=>$end_pickup_datetime,
				'user_phone_no'=>$user_phone_no,
				'checkout_type'=>$request->checkout_type,
				'total_discount'=>$total_discount,
        		'dcc_purchase_discount'=> $dcc_discount_purchase,
        		'dcc_shipping_discount'=> $dcc_discount_shipping,
        		'transaction_fee'=>$transactionFee ?? 0,
				'del_t_s_id'=>$pickup_id ?? null,
			];
			/** Update order info **/
			$orderInfo->update($dataUpdate);
			$update_cart = Cart::where(['order_id'=>$orderInfo->id])->update(['cart_status'=>2]);

			$order_created_id = OrderController::saveFinalOrder($orderId);
			
			if(!$order_created_id){
				throw new Exception(json_encode([
					'type' => 'payment',
					'msg' => Lang::get('checkout.something_went_wrong')
				]));
			}
			 
			$main_order = \App\Order::where('id',$order_created_id)->first();

			// ----- mod start
			if($request->dcc_discount_code && $request->dcc_discount_code !=''){
				$dcc = DiscountCode::where('code',$request->dcc_discount_code)->first();
				if(!$dcc) {throw new Exception(json_encode([ 'type' => 'payment', 'msg' => "ไม่พบโค้ดส่วนลด" ]));}
				if(!$dcc->criteria){ throw new Exception(json_encode([ 'type' => 'payment', 'msg' => "ไม่พบโค้ดส่วนลด" ])); }
				if($dcc->criteria->is_limit == true){
					if ($dcc->remaining_quantity > 0) {
						$dcc->remaining_quantity -= 1;
						$dcc->save();
					} else {
						throw new Exception(json_encode([
							'type' => 'payment',
							'msg' => "ส่วนลดนี้ถูกใช้หมดแล้ว"
						]));
					}
				}
				
				$new_odc = new OrderDiscountCode();
				$new_odc->order_id = $order_created_id;
				$new_odc->discount_code = $dcc->code;
				$new_odc->purchase_discount_amount = $dcc_discount_purchase;
				$new_odc->shipping_discount_amount = $dcc_discount_shipping;
				$new_odc->discount_code_criteria_id = $dcc && $dcc->criteria ? ($dcc->criteria->id ?? null) : null;
				$new_odc->save();
			}
			
			if($payment_slug == 'kbank'){
				$url = action('Checkout\CartController@kbankPayment',$main_order->formatted_id);
					DB::commit();
					return ['status'=>'success','url'=>$url];
			}
			if($payment_slug == 'payplus'){
				$url = action('Checkout\CartController@payplusPayment',$main_order->formatted_id);
					DB::commit();
					return ['status'=>'success','url'=>$url];
			}
			if($payment_slug == 'odd'){
				$return = $this->oddPayment($main_order,$user_odd_info);
				DB::commit();
				return $return;
			}
			if(strpos($payment_slug, 'beam') === 0){
				$url = action('Checkout\CartController@beamPayment',$main_order->formatted_id);
				DB::commit();
				return ['status'=>'success','url'=>$url];
			}

			if($total_final_price <= 0 || $request->checkout_type =='end-shopping'){
				OrderController::saveOrderEndShopping($orderId,$main_order);
				$url = action('Checkout\OrderController@thanks',$main_order->formatted_id);
				DB::commit();
				return ['status'=>'success','url'=>$url,'msg'=>Lang::get('checkout.product_payment_paid_successfully')];
			}else{
				// return ['status'=>'fail','type'=>'payment','msg'=>Lang::get('checkout.invalid_payment_method')];
				throw new Exception(json_encode([
					'type' => 'payment',
					'msg' => Lang::get('checkout.invalid_payment_method')
				]));
			}
			
		} catch (Exception $e) {
			if (DB::transactionLevel() > 0) {
				DB::rollBack();
			}
			Log::error('Transaction failed: ' . $e->getMessage());
			$message = 'Server error'.$e->getMessage();
			$msgData = json_decode($message, true);

			if (json_last_error() === JSON_ERROR_NONE && isset($msgData['type'])) {
				return [
					'status' => 'fail',
					'type' => $msgData['type'],
					'msg' => $msgData['msg'] ?? 'เกิดข้อผิดพลาด'
				];
			}

			return ['status' => 'fail', 'msg' => $message];
		}
		
	}

	public function calculatePaymentMethod(Request $request){
		$validate = Validator::make($request->all(),[
			'paymentMethod' => 'required',
			'formattedId' => 'required'
		]);
		if($validate->fails()){
			return ['status'=>'fail','msg'=>"ไม่พบข้อมูลการชำระเงิน"];
		}

		try {
			DB::beginTransaction();
			$userId = Auth::User()->id;
			$formattedId = $request->formattedId;
			$orderInfo = Order::where(['formatted_id'=>$formattedId,'user_id'=>$userId,'payment_status'=>0])->where('order_status',1)->first();
			if(empty($orderInfo)){
				return ['status'=>'fail','msg'=>Lang::get('checkout.invalid_order')];
			}
			$paymentMethod = PaymentOption::where('id',$request->paymentMethod)->where('status','1')->first();
			if(empty($paymentMethod)){
				return ['status'=>'fail','msg'=>Lang::get('checkout.invalid_payment_method')];
			}
			
			$totalBeforeFee = round(($orderInfo->total_core_cost+$orderInfo->total_shipping_cost) - $orderInfo->total_discount,2);
			$transactionFeeRate = $paymentMethod->transactionFeeConfig->current_tf ?? 0;
			$transactionFee = $totalBeforeFee == 0 || $transactionFeeRate == 0 ?  0 : round($totalBeforeFee * $transactionFeeRate / 100,2);
			$totalFinalPrice = round($totalBeforeFee + $transactionFee,2);

			$orderInfo->payment_slug = $paymentMethod->slug;
			$orderInfo->transaction_fee = $transactionFee;
			$orderInfo->total_final_price = $totalFinalPrice;
			$orderInfo->save();

			$orderDetails = OrderDetail::where('order_id', $orderInfo->id)->get();
			foreach ($orderDetails as $detail) {
				$jsonData = json_decode($detail->order_detail_json, true);

				if (is_array($jsonData)) {
					$jsonData['payment_method'] = [$paymentMethod->name ?? $paymentMethod->slug];
	
					$detail->order_detail_json = json_encode($jsonData, JSON_UNESCAPED_UNICODE);
					$detail->payment_slug = $paymentMethod->slug;
					$detail->save();
				}
			}

			OrderShop::where('order_id', $orderInfo->id)
				->update(['payment_slug' => $paymentMethod->slug]);

			DB::commit();
			return ['status'=>'success',
				'msg'=>Lang::get('checkout.payment_method_updated_successfully'),
				'data'=>[
					'transactionFeeLabel' => $paymentMethod->transactionFeeConfig->name??null,
					'transactionFeeRate' => number_format($transactionFeeRate,2),
					'totalBeforeFee' => number_format($totalBeforeFee,2),
					'totalFinalPrice' => number_format($totalBeforeFee + $transactionFee,2),
					'transactionFee' => number_format($transactionFee,2)
				]
			];
		} catch (Exception $e) {
			DB::rollBack();
			return ['status'=>'fail','msg'=>'เกิดข้อผิดพลาด'];
		}
	}

	public function submitPayment(Request $request)
	{
		log::info('Repay Submit Payment Request: ', $request->all());
		$userid = Auth::id();
		$formatted_id = $request->formatted_id;
		
		// ดึงข้อมูล Order
		$orderInfo = Order::where(['formatted_id' => $formatted_id, 'user_id' => $userid, 'payment_status' => 0])
			->where('order_status', 1)
			->first();

		if (empty($orderInfo)) {
			return ['status' => 'fail', 'msg' => Lang::get('checkout.invalid_order')];
		}

		// ตรวจสอบว่ามีการส่ง pickup_time (ID) มาหรือไม่
		if ($request->has('pickup_time') && !empty($request->pickup_time)) {
			
			// ดึงข้อมูล Slot
			$slot = \App\DeliveryTimeSlot::where('del_t_s_id', $request->pickup_time)
				->where('status', 1)
				->first();

			if (!$slot) {
				return ['status' => 'fail', 'type' => 'pickup_time', 'msg' => 'ไม่พบรอบการจัดส่งที่คุณเลือก หรือรอบเวลาดังกล่าวถูกปิดการใช้งานแล้ว'];
			}

			$now = \Carbon\Carbon::now();
			$baseDate = $now->copy();
			$cutoffTimeStr = $slot->order_cutoff_time;


			$daysToAdd = (int)($slot->deli_plus_days ?? 0);
			if ($cutoffTimeStr && $now->format('H:i:s') > $cutoffTimeStr) {
				$daysToAdd += 1;
			}
			$targetDate = $baseDate->addDays($daysToAdd)->format('Y-m-d');

			$baseDateTimeStr = $targetDate . ' ' . $cutoffTimeStr;
			$baseTimestamp = strtotime($baseDateTimeStr);

			$startTimeStr = $this->calcRelativeTime($cutoffTimeStr, $slot->start_deli_time);
			$pickup_datetime = $targetDate . ' ' . $startTimeStr . ':00';

			// Check Lead Time 3 Hours
			// if (\Carbon\Carbon::parse($pickup_datetime)->lt($now->copy()->addHours(3))) {
			// 	return [
			// 		'status' => 'fail', 
			// 		'type' => 'pickup_time', 
			// 		'msg' => 'ขออภัย รอบการจัดส่งนี้ต้องสั่งล่วงหน้าอย่างน้อย 3 ชั่วโมง กรุณาเลือกใหม่อีกครั้ง'
			// 	];
			// }

			$dc_start_timestamp = $baseTimestamp + ($slot->seller_start_deli_time * 60);
			$dc_end_timestamp   = $baseTimestamp + ($slot->seller_end_deli_time * 60);

			$dc_delivery_starttime = date('Y-m-d H:i:s', $dc_start_timestamp);
			$dc_delivery_endtime   = date('Y-m-d H:i:s', $dc_end_timestamp);

			$orderInfo->pickup_time = $pickup_datetime;
			$orderInfo->del_t_s_id  = $request->pickup_time;
			$orderInfo->save();

			\App\OrderShop::where('order_id', $orderInfo->id)->update([
				'dc_delivery_starttime' => $dc_delivery_starttime,
				'dc_delivery_endtime'   => $dc_delivery_endtime
			]);
			
			Log::info("Repay Order {$formatted_id}: Updated TimeSlot ID {$request->pickup_time}");
			Log::info("New DC Start: {$dc_delivery_starttime} | End: {$dc_delivery_endtime}");
		}

		// --- Process Payment Gateway ---
		// (ส่วนนี้ถ้ามีการเปลี่ยน Payment Method จากหน้าบ้าน ต้องรับค่ามา update)
		if ($request->has('payment_method') && !empty($request->payment_method)) {
			$payment_option = \App\PaymentOption::where('id', $request->payment_method)
												->where('status', '1') 
												->first();
		
			if ($payment_option) {
				$orderInfo->payment_slug = $payment_option->slug;
				$orderInfo->save();
			}
		}

		$payment_slug = $orderInfo->payment_slug;

		if ($payment_slug == 'kbank') {
			return ['status' => 'success', 'url' => action('Checkout\CartController@kbankPayment', $orderInfo->formatted_id)];
		}
		if ($payment_slug == 'payplus') {
			return ['status' => 'success', 'url' => action('Checkout\CartController@payplusPayment', $orderInfo->formatted_id)];
		}
		if (strpos($payment_slug, 'beam') === 0) {
			return ['status' => 'success', 'url' => action('Checkout\CartController@beamPayment', $orderInfo->formatted_id)];
		}

		return ['status' => 'fail', 'msg' => 'ไม่พบข้อมูลวิธีการชำระเงิน'];
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
			return view('checkout.kbank' ,[
				'orderInfo' 	=>$orderInfo,
				'kbank_details'	=>$kbank_details,
				'order_id'		=>$order,
				'order_detail'	=>$order_detail,
				'shop_order'	=>$shop_order
			]);
		}else{
			abort(404);
		}
		
	}

	function payplusPayment(Request $request,$formatted_id=null){
		$userid = Auth::User()->id;
		$orderInfo = Order::where(['user_id'=>$userid,'payment_status'=>0,'order_status'=>1,'formatted_id'=>$formatted_id])->whereNull('end_shopping_date')->first();
		
		$kbank_details = [];
		if(!empty($orderInfo)){

			// if($orderInfo->payment_slug == '-+'){

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
			// }
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
        $ref_no = $orderInfo->id;
        $mobile = $request->input("phone");

        $post_array = array('amount'=>$orderInfo->total_final_price,'currency'=>'THB','description'=>'PayPLUS Description','source_type'=>'kplus_no','number'=>$mobile,'reference_order'=>$orderInfo->id,'ref_1'=>$orderInfo->id,'ref_2'=>$orderInfo->id);
        $post_json = json_encode($post_array);

		Log::info('PayPlus API Request', [
			'url' => $url,
			'headers' => ['x-api-key'=>$secret_key],
			'body' => $post_array
		]);

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

		
		if ($err) {
			Log::error('PayPlus API Error', ['error' => $err]);
		} else {
			Log::info('PayPlus API Response', ['response' => $response]);
		}

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

	/**shipping billing address section start**/
	// get shipping address popup form
	public function cartAddress(Request $request) {

        if($request->call_type == 'ajax_data') {

            $user_detail = Auth::user();

            $def_country_dtl = GeneralFunctions::getDefaultCountryDetail();
            $ship_province_str = '';
            $use_smm_address = false;
            if(getConfigValue('ADDRESS_TYPE') == 'dropdown') {
                if (!empty($def_country_dtl) && $def_country_dtl->short_code == 'TH') {
                    $ship_province_str = CustomHelpers::getSmmProvinceDD();
                    $use_smm_address = true;
                } elseif (!empty($def_country_dtl)) {
                    $ship_province_str = CustomHelpers::getProvinceStateNormalDD($def_country_dtl->id);
                }
            }

            return view('shipBillAddress.addressAdd', [
                'user_detail'=>$user_detail,
                'def_country_dtl'=>$def_country_dtl,
                'ship_province_str'=>$ship_province_str,
                'use_smm_address'=>$use_smm_address,
                'address_from'=>'cart',
                'address_type'=>$request->address_type
            ]);
        }
	}

	// when save shipping address
	function saveAddress(Request $request) {

		$input = $request->all();
        $validate = $this->validateAddressForm($input);

		if (!$validate->fails()) {

            $user_id = Auth::User()->id;

            $data_arr['user_id'] = $user_id;
            $address_data = $this->saveUserShippingBillingAddress($request, $data_arr);
            $address = $address_data['address'];
            $address_type = $address_data['address_type'];

			$addressId = $address->id;
		
			$str_common = '<p>'.$request->first_name.' '.$request->last_name.'</p><p>'.$request->address.', '.$request->sub_district.'</p><p>'.$address->city_district.', '.$address->province_state.' '.$request->zip_code.'</p><p>'.Lang::get("customer.tel").' : '.$request->ph_number.'</p>';

			// prepare billing HTML: include TAX ID when applicable
			$bill_str = $str_common;
			if(isset($request->tax_invoice) && !empty($request->tax_id)){
				$bill_str .= '<p>'.Lang::get('checkout.tax_id').' : '.$request->tax_id.'</p>';
			}

			$shipVal = $billVal = $ship_selected = $bill_selected = "";
			if($address_type == '3'){
				$ship_selected = $bill_selected = "selected='selected'";
				$shipVal = $str_common;
				$billVal = $bill_str;
			}elseif($address_type == '2' || $request->address_type == 'bill_address'){
				$bill_selected = "selected='selected'";
				$billVal = $bill_str;
			}elseif($address_type == '1' || $request->address_type == 'ship_address'){
				$ship_selected = "selected='selected'";
				$shipVal = $str_common;
			}

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
// 	function changeShipAddress(Request $request){
// 		$array_server = explode('/',$request->server('HTTP_REFERER'));
// 		$checkout_type = end($array_server);
// 		$checkout_type = explode('?', $checkout_type)[0];
// 		$orderDetails = $paid_product = [];
// 		$userid = Auth::User()->id;
// 		$orderInfo = OrdersTemp::where(['user_id'=>$userid,'order_status'=>'0'])->first();
// 		if(!$orderInfo){
// 			return json_encode(array('status'=>'fail','msg'=>'order not found'));
// 		}
		
// 		if($checkout_type == 'buy-now' || $checkout_type == 'buy-now-end-shopping' ){
// 			$orderDetails = Cart::getCartList($orderInfo->id);
// 			$orderDetails = $orderDetails->where('is_selected',true);
// 		}
// 		$main_order = \App\Order::where('temp_formatted_id',$orderInfo->formatted_order_id)->first();

// 		$total_amount = 0;
// 		if(count($orderDetails)){
// 			foreach ($orderDetails as $key => $item) {
// 				$total_amount += $item->total_price;
// 			}
// 		}

// 		if(!empty($main_order)){
// 			$paid_product = \App\OrderDetail::getMainOrderDetail($main_order->id);
// 		}
		
// 		$discountPurchase = 0;
// 		$discountShipping = 0;
// 		$discountCodeName = "";
// 		$shipping_fee=0;
// 		$total_shipping=0;
// 		$tot_amt_before_dc = 0;

// 		$str = "";
// 		$discount_fee = 0;

// 		if(!empty($request->shipId)){

// 			$shipAddress = ShippingAddress::where(['user_id'=>$userid,'id'=>$request->shipId])->first();
// 			$shippingRes = $this->getShippingFee($shipAddress,$orderDetails,$paid_product);
// 			$shipping_fee = $shippingRes['total_deliver_fee'];
// 			$discount_fee = $shippingRes['total_logistic_fee'];

// 			$total_shipping = $shipping_fee;

// 			if(!empty($shipAddress)){
// 				$str = "<table>
// 						<tr>
// 							<td class='d-flex pt-2'><ion-icon name='location-outline' class='mr-2'></ion-icon></td>
// 							<td><p>$shipAddress->first_name  $shipAddress->last_name</p>
// 							<p> $shipAddress->address  $shipAddress->road </p>
// 							<p> $shipAddress->city_district  $shipAddress->province_state $shipAddress->zip_code </p>
// 							<p></td>
// 						</tr>
// 						<tr>
// 							<td><ion-icon name='call-outline' class='mr-2'></ion-icon></td><td> $shipAddress->ph_number </p></td>
// 						</tr>
// 					</table>";
// 			}

// 			$timeSlotOptions = '<option value="">' . trans('checkout.select_pickup_time') . '</option>';

// 			if ($shipAddress && $shipAddress->zip_code) {
// 				$cleanSub = str_replace(['แขวง', 'ตำบล'], '', $shipAddress->sub_district);
// 				$cleanDist = str_replace(['เขต', 'อำเภอ'], '', $shipAddress->city_district);

// 				$regionDetail = \DB::table('delivery_region_detail as detail')
// 					->join('master_sub_districts as ms_sub', 'detail.subdistrict_id', '=', 'ms_sub.id')
// 					->join('master_districts as ms_dist', 'ms_sub.district_id', '=', 'ms_dist.id')
// 					->where('detail.postcode', $shipAddress->zip_code)
// 					->where('detail.status', 1)
// 					->where(function($query) use ($cleanSub, $cleanDist) {
// 						if (!empty($cleanSub)) {
// 							$query->where('ms_sub.name_th', 'LIKE', '%' . $cleanSub . '%');
// 						}
// 						if (!empty($cleanDist)) {
// 							$query->where('ms_dist.name_th', 'LIKE', '%' . $cleanDist . '%');
// 						}
// 					})
// 					->select('detail.region_id')
// 					->first();

// 				if (!$regionDetail) {
// 					$regionDetail = \DB::table('delivery_region_detail')
// 						->where('postcode', $shipAddress->zip_code)
// 						->where('status', 1)
// 						->first();
// 				}

// 				// --- ดึงข้อมูล TimeSlots ---
// 				if ($regionDetail) {
// 					$timeSlots = \App\DeliveryTimeSlot::where('reg_id', $regionDetail->region_id)
// 						->where('status', 1)
// 						->get();

// 					if ($timeSlots->count() > 0) {
// 						foreach ($timeSlots as $slot) {
// 							$cutoff = $slot->order_cutoff_time;
							
// 							$basePlusDays = (int)($slot->deli_plus_days ?? 0);
							
// 							$now = \Carbon\Carbon::now();
					
// 							if ($cutoff && $now->format('H:i:s') > $cutoff) {
// 								$basePlusDays += 1; 
// 							}
// 							$deliveryDate = \Carbon\Carbon::now()->addDays($basePlusDays);
// 							$dateLabel = $deliveryDate->locale('th')->translatedFormat('d M');
// 							$dayLabel = $deliveryDate->locale('th')->translatedFormat('D');
							
// 							$startTime = $this->calcRelativeTime($cutoff, $slot->start_deli_time);
// 							$endTime   = $this->calcRelativeTime($cutoff, $slot->end_deli_time);

// 							$fullLabel = "($dayLabel $dateLabel) $startTime - $endTime";
// 							$timeSlotOptions .= "<option value='{$slot->del_t_s_id}'>{$fullLabel}</option>";
// 						}
// 					} else {
// 						$timeSlotOptions = "<option value=''>ขออภัย ไม่มีรอบเวลาจัดส่งสำหรับพื้นที่นี้</option>";
// 					}
// 				} else {
// 					$timeSlotOptions = "<option value=''>ไม่พบข้อมูลพื้นที่จัดส่ง (Region)</option>";
// 				}
// 			} // ปิด if ($shipAddress && $shipAddress->zip_code)

// 			// --- ก้อนที่ 2: สำหรับกรณีมารับที่ศูนย์ (reg_type = 2) ---
// 			$pickupTimeSlotOptions = '<option value="">' . trans('checkout.select_pickup_time') . '</option>';
// 			$pickupRegion = \App\DeliveryRegion::where('reg_type', 2)->where('status', 1)->first();

// 			if ($pickupRegion) {
// 				$pSlots = \App\DeliveryTimeSlot::where('reg_id', $pickupRegion->reg_id)
// 					->where('status', 1)->get();

// 				foreach ($pSlots as $pSlot) {
// 					$pCutoff = $pSlot->order_cutoff_time;
					
// 					$pPlusDays = (int)($pSlot->deli_plus_days ?? 0);
// 					$pNow = \Carbon\Carbon::now();
// 					if ($pCutoff && $pNow->format('H:i:s') > $pCutoff) {
// 						$pPlusDays += 1;
// 					}
// 					$pDate = $pNow->addDays($pPlusDays);
// 					$pDateLabel = $pDate->locale('th')->translatedFormat('D d M');

// 					// คำนวณเวลาของฝั่ง Pickup
// 					$pStart = $this->calcRelativeTime($pCutoff, $pSlot->start_deli_time);
// 					$pEnd   = $this->calcRelativeTime($pCutoff, $pSlot->end_deli_time);

// 					$pFullLabel = "[$pDateLabel] $pStart - $pEnd";
// 					$pickupTimeSlotOptions .= "<option value='{$pSlot->del_t_s_id}'>{$pFullLabel}</option>";
// 				}
// 			} else {
// 				$pickupTimeSlotOptions = "<option value=''>ไม่พบข้อมูลจุดรับสินค้า</option>";
// 			}
		
// 		$tot_amt_before_dc = $total_amount+$total_shipping;


//         // -- mod start
// 		if(!empty($request->discountCode)){
// 			$newRequest = new Request([
// 				'code' => $request->discountCode,
// 				'purchase' => $total_amount,
// 				'shippingCost' => $shipping_fee
// 			]);
		
// 			$discountController = new DiscountCodeController();
// 			$rs = $discountController->calulateDiscount($newRequest);
// 			$rsData = $rs->getData();
			
// 			$discountCodeName = $rsData->data->discountCodeName;
// 			if($rsData && $rsData->status ==='success'){
// 				$discountPurchase = (float) $rsData->data->discountPurchase;
// 				$discountShipping = (float) $rsData->data->discountShipping;

// 				if( $total_amount - $discountPurchase <= 0){
// 					$discountPurchase = $total_amount;
// 					$total_amount = 0;
// 				}else{
// 					$total_amount -= $discountPurchase;
// 				}
				
// 				if($shipping_fee > 0 ){
// 					if($total_shipping - $discountShipping <= 0) {
// 						$discountShipping = $total_shipping;
// 						$total_shipping = 0;
// 					}else{
// 						$total_shipping -= $discountShipping;
// 					}
// 				}else{
// 					$discountShipping = 0;
// 				}

// 			}

// 		}
// 		// -- mod end
// 		$total_amount += $total_shipping;

// 		//transaction fee calculate
// 		$transactionFee = 0;
// 		$transactionFeeRate = 0;
// 		$transactionFeeName = '';

// 		if(!empty($request->paymentOptionId)){
// 			$paymentOption = PaymentOption::where('id',$request->paymentOptionId)->first();
// 			if(!empty($paymentOption)){
// 				// Only calculate transaction fees for Beam payment methods
// 				if(strpos($paymentOption->slug, 'beam') === 0 && !empty($paymentOption->transactionFeeConfig)){
// 					$transactionFeeRate = (float)$paymentOption->transactionFeeConfig->current_tf ?? 0;
// 					// ใช้ฟังก์ชัน helper สำหรับคำนวณ transaction fee
// 					$transactionFee = $this->calculateTransactionFee($total_amount, $transactionFeeRate);
// 					$total_amount = round($total_amount + $transactionFee, 2);
// 					$transactionFeeName = $paymentOption->transactionFeeConfig->name;
// 				} else {
// 					// For non-Beam methods, set transaction fee to 0
// 					$transactionFeeRate = 0;
// 					$transactionFee = 0;
// 					$transactionFeeName = '';
// 				}
// 			}
// 		}

// 		$transactionFee = number_format($transactionFee,2);
// 		$final_ship_fee = number_format($total_shipping,2);
// 		$final_discount_fee = number_format($discount_fee,2);

// 		// คำนวณยอดรวมสุดท้ายรวม transaction fee สำหรับแสดงผล
// 		$total_amount_with_fee = round($total_amount, 2);
		
// 		return json_encode(array(
// 			'status'=>'success',
// 			'shipVal'=>$str,
// 			'shipping_fee'=>$shipping_fee,
// 			'shipping_fee_txt'=>number_format($shipping_fee,2),
// 			'final_ship_fee'=>$final_ship_fee,
// 			'discount_fee'=>$final_discount_fee,
// 			'total_amount'=>number_format($total_amount_with_fee,2),
// 			'totAmt'=>$total_amount_with_fee,
			
// 			'discount_code_purchase'=> $discountPurchase,
// 			'discount_code_shipping'=> $discountShipping,
// 			'dcc_total_discount'=>$discountPurchase+$discountShipping,
// 			'discount_code'=> $request->discountCode,
// 			'discount_code_name'=> $discountCodeName,
// 			'tot_amt_before_dc'=>$tot_amt_before_dc,
// 			'tot_amt_after_dc'=>$tot_amt_before_dc - ($discountPurchase+$discountShipping),
			
// 			'discount_code_purchase_txt'=> number_format($discountPurchase,2),
// 			'discount_code_shipping_txt'=> number_format($discountShipping,2),

// 			'transaction_fee_rate'=>$transactionFeeRate,
// 			'transaction_fee'=>$transactionFee,
// 			'transaction_fee_name'=>$transactionFeeName,
// 			'transaction_fee_txt'=>number_format($transactionFee,2),
// 			'delivery_time_slots' => $timeSlotOptions ?? '',
// 			'pickup_time_slots'   => $pickupTimeSlotOptions,
// 		));

// 	}
// }


public function changeShipAddress(Request $request) {
    $array_server = explode('/', $request->server('HTTP_REFERER'));
    $checkout_type = end($array_server);
    $checkout_type = explode('?', $checkout_type)[0];
    $orderDetails = $paid_product = [];
    $userid = Auth::User()->id;

    $orderInfo = OrdersTemp::where(['user_id' => $userid, 'order_status' => '0'])->first();
    
    if (!$orderInfo) {
        return json_encode(array('status' => 'fail', 'msg' => 'order not found'));
    }

    if ($checkout_type == 'buy-now' || $checkout_type == 'buy-now-end-shopping') {
        $orderDetails = Cart::getCartList($orderInfo->id);
        $orderDetails = $orderDetails->where('is_selected', true);
    }
    $main_order = \App\Order::where('temp_formatted_id', $orderInfo->formatted_order_id)->first();

    $total_amount = 0;
    if (count($orderDetails)) {
        foreach ($orderDetails as $key => $item) {
            $total_amount += $item->total_price;
        }
    }

    if (!empty($main_order)) {
        $paid_product = \App\OrderDetail::getMainOrderDetail($main_order->id);
    }

    // --- กำหนดค่า Default ---
    $discountPurchase = 0;
    $discountShipping = 0;
    $discountCodeName = "";
    $shipping_fee = 0;
    $total_shipping = 0;
    $tot_amt_before_dc = 0;
    $str = ""; // ตัวแปร HTML ที่อยู่ที่จะส่งกลับ
    $discount_fee = 0;
        
    $currentDeliveryTimeId = $request->currentDeliveryTimeId ?? null; 
    $currentPickupTimeId   = $request->currentPickupTimeId ?? null;
    
    $timeSlotOptions = '<option value="">' . trans('checkout.select_address_first') . '</option>'; 
    $pickupTimeSlotOptions = '<option value="">' . trans('checkout.select_pickup_time') . '</option>';

    // รับค่า ship_method จาก JS (1 = Pickup, อื่นๆ = Delivery)
    $shipMethod = $request->ship_method ?? null;

    // ==================================================================================
    // ส่วนที่ 1: ตรวจสอบและสร้าง HTML ที่อยู่ ($str)
    // ==================================================================================

    // --- กรณี A: รับสินค้าที่ศูนย์ (Pickup) ---
    if ($shipMethod == '1') {
        // ดึงข้อมูลศูนย์ Pickup (reg_type = 2)
        $pickupRegion = \App\DeliveryRegion::where('reg_type', 2)->where('status', 1)->first();

        if ($pickupRegion) {
			$text ='จุดรับสินค้า';
            $centerName = $pickupRegion->reg_name ?? 'จุดรับสินค้า';
            $dcAddress  = $pickupRegion->dc_address ?? '-';
            $dcTel      = $pickupRegion->dc_tel ?? '02-995-0610'; 
            $str = "<table>
                        <tr>
                            <td class='d-flex pt-2'><ion-icon name='location-outline' class='mr-2'></ion-icon></td>
                            <td>
                                <p><strong>{$text} {$centerName}</strong></p>
                                <p>{$dcAddress}</p>
                            </td>
                        </tr>
                        <tr>
                            <td class='d-flex pt-2'><ion-icon name='call-outline' class='mr-2'></ion-icon></td>
                            <td><p>{$dcTel}</p></td>
                        </tr>
                    </table>";
        }
    }
    // --- กรณี B: จัดส่งตามที่อยู่ (Delivery) ---
    elseif (!empty($request->shipId)) {

        $shipAddress = ShippingAddress::where(['user_id' => $userid, 'id' => $request->shipId])->first();
        
        if ($shipAddress) {
            // คำนวณค่าส่ง
            $shippingRes = $this->getShippingFee($shipAddress, $orderDetails, $paid_product);
            $shipping_fee = $shippingRes['total_deliver_fee'];
            $discount_fee = $shippingRes['total_logistic_fee'];
            $total_shipping = $shipping_fee;

            // สร้าง HTML แสดงที่อยู่ลูกค้า
            $str = "<table>
                        <tr>
                            <td class='d-flex pt-2'><ion-icon name='location-outline' class='mr-2'></ion-icon></td>
                            <td><p>$shipAddress->first_name  $shipAddress->last_name</p>
                            <p> $shipAddress->address  $shipAddress->road </p>
                            <p> $shipAddress->city_district  $shipAddress->province_state $shipAddress->zip_code </p>
                            <p></td>
                        </tr>
                        <tr>
                            <td class='d-flex pt-2'><ion-icon name='call-outline' class='mr-2'></ion-icon></td><td> $shipAddress->ph_number </p></td>
                        </tr>
                    </table>";

            // --- คำนวณ TimeSlots สำหรับจัดส่งที่บ้าน (Delivery) ---
            $timeSlotOptions = '<option value="">' . trans('checkout.select_pickup_time') . '</option>';

            if ($shipAddress->zip_code) {
                $cleanSub = str_replace(['แขวง', 'ตำบล'], '', $shipAddress->sub_district);
                $cleanDist = str_replace(['เขต', 'อำเภอ'], '', $shipAddress->city_district);

                $regionDetail = \DB::table('delivery_region_detail as detail')
					->join('delivery_region as reg', 'detail.region_id', '=', 'reg.reg_id')
                    ->join('master_sub_districts as ms_sub', 'detail.subdistrict_id', '=', 'ms_sub.id')
                    ->join('master_districts as ms_dist', 'ms_sub.district_id', '=', 'ms_dist.id')
					->where('reg.reg_type', 3)
                    ->where('detail.postcode', $shipAddress->zip_code)
                    ->where('detail.status', 1)
                    ->where(function($query) use ($cleanSub, $cleanDist) {
                        if (!empty($cleanSub)) {
                            $query->where('ms_sub.name_th', 'LIKE', '%' . $cleanSub . '%');
                        }
                        if (!empty($cleanDist)) {
                            $query->where('ms_dist.name_th', 'LIKE', '%' . $cleanDist . '%');
                        }
                    })
                    ->select('detail.region_id')
                    ->first();

                if (!$regionDetail) {
					$regionDetail = \DB::table('delivery_region_detail as detail')
						->join('delivery_region as reg', 'detail.region_id', '=', 'reg.reg_id')
						->where('reg.reg_type', 3)
						->where('detail.postcode', $shipAddress->zip_code)
						->where('detail.status', 1)
						->select('detail.region_id')
						->first();
				}

				if ($regionDetail) {

					$timeSlots = \App\DeliveryTimeSlot::where('reg_id', $regionDetail->region_id)
						->where('status', 1)
						->get();

					if ($timeSlots->count() > 0) {

						$tempSlots = [];
						$now = \Carbon\Carbon::now('Asia/Bangkok');

						foreach ($timeSlots as $slot) {

							$cutoffTime   = $slot->order_cutoff_time;
							$deliPlusDays = (int) ($slot->deli_plus_days ?? 0);

							$startDiffMinutes = (int) $slot->start_deli_time;
							$endDiffMinutes   = (int) $slot->end_deli_time;

							$startDiffMinutes %= 1440;
							$endDiffMinutes   %= 1440;

							$isOverCutoff = $now->format('H:i:s') > $cutoffTime;
							$cutoffDays   = $isOverCutoff ? 1 : 0;
							$totalDays = $deliPlusDays + $cutoffDays;

							$baseDate = $now->copy()
								->startOfDay()
								->addDays($totalDays);

							$cutoffDateTime = \Carbon\Carbon::parse(
								$baseDate->format('Y-m-d') . ' ' . $cutoffTime,
								'Asia/Bangkok'
							);

							$deliveryStartDateTime = $cutoffDateTime->copy()
								->addMinutes($startDiffMinutes);

							$deliveryEndDateTime = $cutoffDateTime->copy()
								->addMinutes($endDiffMinutes);

							$dateLabel = $deliveryStartDateTime
								->locale('th')
								->translatedFormat('d M');

							$dayLabel = $deliveryStartDateTime
								->locale('th')
								->translatedFormat('D');

							$startTime = $deliveryStartDateTime->format('H:i');
							$endTime   = $deliveryEndDateTime->format('H:i');

							$fullLabel = "($dayLabel $dateLabel) $startTime - $endTime";

							$sortKey = $deliveryStartDateTime->format('YmdHi');
							$selectedAttr = ($slot->del_t_s_id == $currentDeliveryTimeId) ? 'selected' : '';

							$tempSlots[] = [
								'sort_key' => (int) $sortKey,
								'html'     => "<option value='{$slot->del_t_s_id}' {$selectedAttr}>{$fullLabel}</option>"
							];

							\Log::debug("Slot {$slot->del_t_s_id}", [
								'FinalStart' => $deliveryStartDateTime->toDateTimeString(),
							]);
						}

						usort($tempSlots, function ($a, $b) {
							return $a['sort_key'] <=> $b['sort_key'];
						});

						foreach ($tempSlots as $item) {
							$timeSlotOptions .= $item['html'];
						}

					} else {
						$timeSlotOptions = "<option value=''>ขออภัย ไม่มีรอบเวลาจัดส่งสำหรับพื้นที่นี้</option>";
					}

				} else {
					$timeSlotOptions = "<option value=''>ไม่พบข้อมูลพื้นที่จัดส่ง (Region)</option>";
				}

            }
        }
    }

    // ==================================================================================
    // ส่วนที่ 2: คำนวณ Time Slots สำหรับ Pickup (ทำงานเสมอ เพื่ออัปเดต Dropdown)
    // ==================================================================================
    $pickupRegionCheck = \App\DeliveryRegion::where('reg_type', 2)->where('status', 1)->first();
	$tempPickupSlots = [];
   if ($pickupRegionCheck) {
    // ดึงรอบเวลาที่สถานะใช้งานได้
    $pSlots = \App\DeliveryTimeSlot::where('reg_id', $pickupRegionCheck->reg_id)
        ->where('status', 1)
        ->get();

    $pickupTimeSlotOptions = '<option value="">' . trans('checkout.select_pickup_time') . '</option>';
    $tempPickupSlots = [];
    $pNow = \Carbon\Carbon::now('Asia/Bangkok');

    if ($pSlots->count() > 0) {
        foreach ($pSlots as $pSlot) {
            $pCutoff = $pSlot->order_cutoff_time; // เช่น "14:00:00"
            $pPlusDays = (int)($pSlot->deli_plus_days ?? 0);
            
            // 1. ตรวจสอบการตัดรอบ
            $isOverCutoff = $pCutoff && ($pNow->format('H:i:s') > $pCutoff);

            // 2. ปรับจำนวนวันตามเงื่อนไขตัดรอบ
            if ($isOverCutoff) {
                $pPlusDays += 1;
            } else {
                // ถ้ายังไม่ตัดรอบ และเวลาเริ่มจัดส่งตั้งไว้ข้ามวัน (>= 1440 นาที) 
                // อาจต้องลดวันลงเพื่อให้สอดคล้องกับ Logic ธุรกิจของคุณ
                if ((int)$pSlot->start_deli_time >= 1440) {
                    $pPlusDays = max(0, $pPlusDays - 1);
                }
            }

            // 3. หาวันพื้นฐานที่จะแสดง (เริ่มที่ 00:00 น.)
            $pDateBase = $pNow->copy()->startOfDay()->addDays($pPlusDays);

            // 4. คำนวณเวลาจากจุดเริ่ม (Cutoff Time ของวันนั้น)
            $pCutoffCarbon = \Carbon\Carbon::parse($pDateBase->format('Y-m-d') . ' ' . ($pCutoff ?: '00:00:00'));
            
            $pStartDateTime = $pCutoffCarbon->copy()->addMinutes((int)$pSlot->start_deli_time);
            $pEndDateTime   = $pCutoffCarbon->copy()->addMinutes((int)$pSlot->end_deli_time);

            // 5. เตรียม Label ภาษาไทย
            $pDateLabel = $pStartDateTime->locale('th')->translatedFormat('D d M');
            $pStart     = $pStartDateTime->format('H:i');
            $pEnd       = $pEndDateTime->format('H:i');

            $pFullLabel = "[$pDateLabel] $pStart - $pEnd";
            $sortKey    = $pStartDateTime->format('YmdHi');
            
            $pSelectedAttr = ($pSlot->del_t_s_id == $currentPickupTimeId) ? 'selected' : '';

            $tempPickupSlots[] = [
                'sort_value' => (int)$sortKey,
                'html'       => "<option value='{$pSlot->del_t_s_id}' {$pSelectedAttr}>{$pFullLabel}</option>"
            ];
        }

        // 6. เรียงลำดับตามเวลาที่เร็วที่สุดไปช้าที่สุด
        usort($tempPickupSlots, function($a, $b) {
            return $a['sort_value'] <=> $b['sort_value'];
        });

        foreach ($tempPickupSlots as $item) {
            $pickupTimeSlotOptions .= $item['html'];
        }
    } else {
        $pickupTimeSlotOptions = "<option value=''>ขออภัย ไม่มีรอบเวลารับสินค้าสำหรับพื้นที่นี้</option>";
    }
} else {
    $pickupTimeSlotOptions = "<option value=''>ไม่พบข้อมูลจุดรับสินค้า</option>";
}

    $tot_amt_before_dc = $total_amount + $total_shipping;

    // -- mod start: คำนวณส่วนลด --
    if (!empty($request->discountCode)) {
        $newRequest = new Request([
            'code' => $request->discountCode,
            'purchase' => $total_amount,
            'shippingCost' => $shipping_fee
        ]);

        $discountController = new DiscountCodeController();
        $rs = $discountController->calulateDiscount($newRequest);
        $rsData = $rs->getData();

        $discountCodeName = $rsData->data->discountCodeName ?? ''; 
        if ($rsData && $rsData->status === 'success') {
            $discountPurchase = (float) $rsData->data->discountPurchase;
            $discountShipping = (float) $rsData->data->discountShipping;

            if ($total_amount - $discountPurchase <= 0) {
                $discountPurchase = $total_amount;
                $total_amount = 0;
            } else {
                $total_amount -= $discountPurchase;
            }

            if ($shipping_fee > 0) {
                if ($total_shipping - $discountShipping <= 0) {
                    $discountShipping = $total_shipping;
                    $total_shipping = 0;
                } else {
                    $total_shipping -= $discountShipping;
                }
            } else {
                $discountShipping = 0;
            }
        }
    }
    // -- mod end --
    
    $total_amount += $total_shipping;

    // Transaction fee calculate
    $transactionFee = 0;
    $transactionFeeRate = 0;
    $transactionFeeName = '';

    if (!empty($request->paymentOptionId)) {
        $paymentOption = PaymentOption::where('id', $request->paymentOptionId)->first();
        if (!empty($paymentOption)) {
            if (strpos($paymentOption->slug, 'beam') === 0 && !empty($paymentOption->transactionFeeConfig)) {
                $transactionFeeRate = (float)$paymentOption->transactionFeeConfig->current_tf ?? 0;
                $transactionFee = $this->calculateTransactionFee($total_amount, $transactionFeeRate);
                $total_amount = round($total_amount + $transactionFee, 2);
                $transactionFeeName = $paymentOption->transactionFeeConfig->name;
            } else {
                $transactionFeeRate = 0;
                $transactionFee = 0;
                $transactionFeeName = '';
            }
        }
    }

    $transactionFee = number_format($transactionFee, 2);
    $final_ship_fee = number_format($total_shipping, 2);
    $final_discount_fee = number_format($discount_fee, 2);
    $total_amount_with_fee = round($total_amount, 2);

    return json_encode(array(
        'status' => 'success',
        'shipVal' => $str, // ค่า HTML ที่เราสร้างจะถูกส่งกลับไปที่นี่
        'shipping_fee' => $shipping_fee,
        'shipping_fee_txt' => number_format($shipping_fee, 2),
        'final_ship_fee' => $final_ship_fee,
        'discount_fee' => $final_discount_fee,
        'total_amount' => number_format($total_amount_with_fee, 2),
        'totAmt' => $total_amount_with_fee,

        'discount_code_purchase' => $discountPurchase,
        'discount_code_shipping' => $discountShipping,
        'dcc_total_discount' => $discountPurchase + $discountShipping,
        'discount_code' => $request->discountCode,
        'discount_code_name' => $discountCodeName,
        'tot_amt_before_dc' => $tot_amt_before_dc,
        'tot_amt_after_dc' => $tot_amt_before_dc - ($discountPurchase + $discountShipping),

        'discount_code_purchase_txt' => number_format($discountPurchase, 2),
        'discount_code_shipping_txt' => number_format($discountShipping, 2),

        'transaction_fee_rate' => $transactionFeeRate,
        'transaction_fee' => $transactionFee,
        'transaction_fee_name' => $transactionFeeName,
        'transaction_fee_txt' => number_format($transactionFee, 2),
        'delivery_time_slots' => $timeSlotOptions,
        'pickup_time_slots'   => $pickupTimeSlotOptions,
        'reset_delivery_selection' => false,
    ));
}

	private function calcRelativeTime($baseTime, $diffMinutes) {
		if (is_null($diffMinutes) || is_null($baseTime)) return '00:00';
		$time = strtotime($baseTime);
		$time += ($diffMinutes * 60); // แปลงนาทีเป็นวินาทีแล้วบวกเพิ่ม
		return date('H:i', $time);
	}

	function changeBillAddress(Request $request){

		$billId = $request->billId;
		$userid = Auth::User()->id;
		$billAddress = ShippingAddress::where(['user_id'=>$userid,'id'=>$billId])->first();

		if(!empty($billAddress)){

			if(!empty($billAddress->company_name)){
				if(!empty($billAddress->branch)){
					
				$str = '<table>
						<tr>
							<td class="d-flex pt-2"><ion-icon name="location-outline" class="mr-2"></ion-icon></td>
							<td>
								<p>'.$billAddress->company_name.' สาขา '.$billAddress->branch.'</p>
								<p>'.$billAddress->company_address.'</p>
								<p>TAX ID : '.$billAddress->tax_id.'</p>
							</td>
						</tr>
						<tr>
							<td><ion-icon name="call-outline" class="mr-2"></ion-icon></td>
							<td><p>'.$billAddress->ph_number.'</p></td>
						</tr>
					</table>
			';
				}else{
					$str = '<table>
								<tr>
									<td class="d-flex pt-2"><ion-icon name="location-outline" class="mr-2"></ion-icon></td>
									<td>
										<p>'.$billAddress->company_name.'</p>
										<p>'.$billAddress->company_address.'</p>
										<p>TAX ID : '.$billAddress->tax_id.'</p>
									</td>
								</tr>
								<tr>
									<td><ion-icon name="call-outline" class="mr-2"></ion-icon></td>
									<td><p>'.$billAddress->ph_number.'</p></td>
								</tr>
							</table>
					';
				}
			}else{
					$str = '<table>
								<tr>
									<td class="d-flex pt-2"><ion-icon name="location-outline" class="mr-2"></ion-icon></td>
									<td>
										<p>'.$billAddress->first_name.' '.$billAddress->last_name.'</p>
										<p>'.$billAddress->address.', '.$billAddress->road.'</p>
										<p>'.$billAddress->city_district.', '.$billAddress->province_state.', '.$billAddress->zip_code.'</p>
									</td>
								</tr>
								<tr>
									<td><ion-icon name="call-outline" class="mr-2"></ion-icon></td>
									<td><p>'.$billAddress->ph_number.'</p></td>
								</tr>
							</table>
					';
				}
			// add TAX ID row when present
			// if(!empty($billAddress->tax_id)){
			// 	$str .= '<tr>
			// 			<td><ion-icon name="mail-outline" class="mr-2"></ion-icon></td>
			// 			<td><p>TAX ID : '.$billAddress->tax_id.'</p></td>
			// 		</tr>';
			// }

			// $str .= '</table>';

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
	
	public function beamPayment(Request $request, $formatted_id = null) {
		$userid = Auth::User()->id;
		$orderInfo = Order::where([
			'user_id' => $userid,
			'payment_status' => 0,
			'order_status' => 1,
			'formatted_id' => $formatted_id
		])->whereNull('end_shopping_date')->first();
	
		if (empty($orderInfo) || strpos($orderInfo->payment_slug, 'beam') !== 0) {
			abort(404);
		}
	
		$order_detail = [];
		$shop_order = [];
		$main_order = $orderInfo;
	
		if (!empty($main_order)) {
			$order_detail = \App\OrderDetail::getMainOrderDetail($main_order->id);
			$shop_ord = \App\OrderShop::where('order_id', $main_order->id)
				->select('id', 'shop_formatted_id', 'order_status')
				->with('getOrderStatus')->get();
			foreach ($shop_ord as $value) {
				$status = $value->getOrderStatus->status ?? '';
				$shop_order[$value->id] = [
					'shop_formatted_id' => $value->shop_formatted_id,
					'status' => $status,
					'order_status' => $value->order_status
				];
			}
		}
	
		// รองรับ test mode (มาจากปุ่ม 4 ปุ่ม)
		$testMode = $request->boolean('test_mode', false);
		$testPaymentMethod = $request->get('test_payment_method');

		// กำหนดชื่อ payment method ตาม payment_slug
		$payment_method_name = 'Beam Payment';
		if ($orderInfo->payment_slug) {
			switch ($orderInfo->payment_slug) {
				case 'beam-qr':
				case 'beam-qrthb':
					$payment_method_name = 'QR พร้อมเพย์';
					break;
				case 'beam-credit':
				case 'beam-creditcard':
					$payment_method_name = 'Credit Card';
					break;
				case 'beam-banking':
				case 'beam-internetbanking':
					$payment_method_name = 'Mobile Banking';
					break;
				case 'beam-ewallet':
					$payment_method_name = 'E-Wallet';
					break;
				case 'beam':
				default:
					$payment_method_name = 'Beam Payment';
					break;
			}
		}
	
		// ใช้ยอดรวมที่ถูกต้องจากฐานข้อมูล (รวม transaction_fee แล้ว)
		$total_with_transaction_fee = $orderInfo->total_final_price;
		
		
		return view('checkout.beam', [
			'orderInfo' => $orderInfo,
			'order_detail' => $order_detail,
			'shop_order' => $shop_order,
			'payment_method_name' => $payment_method_name,
			'testMode' => $testMode,
			'testPaymentMethod' => $testPaymentMethod,
			'total_with_transaction_fee' => $total_with_transaction_fee
		]);
	}

	public function checkPaymentStatus(Request $request, $formatted_id = null) {
		try {
			$userid = Auth::User()->id;
			$orderInfo = Order::where([
				'user_id' => $userid,
				'formatted_id' => $formatted_id
			])->first();

			if (empty($orderInfo)) {
				return response()->json(['error' => 'Order not found'], 404);
			}

			// ตรวจสอบสถานะการชำระเงินจาก order table
			$payment_status = $orderInfo->payment_status == 1 ? 'completed' : 'pending';
			$order_status = $orderInfo->order_status;

			// ตรวจสอบจาก order_payments table (ถ้ามี)
			$payment_record = \App\OrderPayment::where('order_id', $orderInfo->id)
				->where('payment_slug', 'LIKE', 'beam%')
				->orderBy('created_at', 'desc')
				->first();

			$result = [
				'payment_status' => $payment_status,
				'order_status' => $order_status,
				'formatted_id' => $orderInfo->formatted_id,
				'order_id' => $orderInfo->id,
				'payment_record' => $payment_record ? json_decode($payment_record->response, true) : null
			];

			// เพิ่มข้อมูลสำหรับ redirect ถ้าชำระเงินเรียบร้อยแล้ว
			if ($payment_status === 'completed') {
				$result['redirect_url'] = url("/checkout/thanks/{$orderInfo->formatted_id}");
				$result['message'] = 'Payment completed successfully!';
			} else {
				$result['message'] = 'Waiting for payment confirmation...';
			}

			return response()->json($result);

		} catch (Exception $e) {
			Log::error('Error checking payment status', [
				'error' => $e->getMessage(),
				'formatted_id' => $formatted_id,
				'trace' => $e->getTraceAsString()
			]);

			return response()->json([
				'success' => false,
				'error' => 'Failed to check payment status', 
				'debug' => $e->getMessage()
			], 500);
		}
	}

	public function createBeamOrder(Request $request,$formatted_id=null){
		
		$userid = Auth::User()->id;
		$orderInfo = Order::where(['user_id'=>$userid,'payment_status'=>0,'formatted_id'=>$formatted_id])->whereNull('end_shopping_date')->first();

		if(empty($orderInfo)){
			return response()->json(['error' => 'Order not found'], 404);
		}

		// ดึง payment option ตาม payment_slug ที่เลือก (เช่น beam-qr, beam-credit, etc.)
		$pay_opt = \App\PaymentOption::where('slug', $orderInfo->payment_slug)->first();
		if(empty($pay_opt)){
			return response()->json(['error' => 'Payment option not found: ' . $orderInfo->payment_slug], 404);
		}

		if($pay_opt->mode == 2){
			$beam_details = json_decode($pay_opt->sandbox_detail,true);
		}else{
			$beam_details = json_decode($pay_opt->live_detail,true);
		}
		
        // รองรับโหมดทดสอบจากปุ่ม 4 ปุ่ม: บังคับ URL และ วิธีชำระเงิน
        if ($request->has('test_mode') && $request->test_mode) {
            $url = 'https://wms.simummuangonline.com/beam-api/public/';
            $supportedPaymentMethods = $request->test_payment_method ?? 'qrPromptPay';
        } else {
            $url = $beam_details['url'] ?? 'https://wms.simummuangonline.com/beam-api/public/';
            
            // กำหนด supportedPaymentMethods ตาม payment option ที่เลือกหรือใช้ default
            $supportedPaymentMethods = $this->getBeamPaymentMethods($orderInfo);
        }
        
        // ตรวจสอบและใช้ค่า default หากเป็นค่าว่าง
        if (empty($supportedPaymentMethods)) {
            $supportedPaymentMethods = 'qrPromptPay'; // default
        }
        
        // ใช้ยอดรวมที่ถูกต้องจาก frontend (รวม transaction fee แล้ว)
        $total_with_transaction_fee = $orderInfo->total_final_price;
        
        // ปรับปรุง payload ตามเอกสาร Beam API Charges
        $post_array = [
        	'orderInfo' => [
        		'supportedPaymentMethods' => $supportedPaymentMethods,
        		'currencyCode' => 'THB',
        		'description' => 'รายการชำระเงิน คำสั่งซื้อ ' . $orderInfo->formatted_id,
        		'merchantReferenceId' => $orderInfo->formatted_id,
        		'netAmount' => (int)round($total_with_transaction_fee * 100),
        		// 'redirectUrl' => url("/checkout/thanks/{$orderInfo->formatted_id}"),
        		'redirectUrl' => url("/checkout/beam/verify/{$orderInfo->formatted_id}"),
        		'expireTime' => 4
        	],
        	'orderItems' => []
        ];
		
        
        $post_json = json_encode($post_array);

        // Log สำหรับ debug - รายละเอียดของ payload


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
            "accept: application/json"
          ),
          CURLOPT_SSL_VERIFYPEER => false,
          CURLOPT_SSL_VERIFYHOST => false,
        ));

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);

        curl_close($curl);


        // ตรวจสอบ CURL error
        if($err) {
            return response()->json([
                'error' => 'CURL Error: ' . $err,
                'debug' => [
                    'url' => $url,
                    'payload' => $post_array
                ]
            ], 500);
        }

        // ตรวจสอบ HTTP status code
        if($http_code !== 200) {
            return response()->json([
                'error' => 'HTTP Error: ' . $http_code,
                'response' => $response,
                'debug' => [
                    'url' => $url,
                    'payload' => $post_array
                ]
            ], 400);
        }

        $result = json_decode($response, true);

        // ตรวจสอบ JSON decode error
        if(json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'error' => 'JSON Decode Error: ' . json_last_error_msg(),
                'raw_response' => $response
            ], 400);
        }

        // ตรวจสอบ response format (Beam API ใช้ 'id' และ 'url' แทน 'purchaseId' และ 'paymentLink')
        if(isset($result['success']) && $result['success'] && isset($result['id']) && isset($result['url'])){

        	$response_array = [
        		"success" => true,
        		"purchaseId" => $result['id'],
        		"paymentLink" => $result['url'],
        		"order_id" => $orderInfo->id,
        		"formatted_id" => $orderInfo->formatted_id
        	];
            $update_ord = Order::where('id',$orderInfo->id)->update(['kbank_qrcode_id'=>$result['id']]);

            return response()->json($response_array);
        }else{
        	return response()->json([
        		'error' => 'Invalid Beam API response format',
        		'response' => $result,
        		'debug' => [
                    'url' => $url,
                    'payload' => $post_array,
                    'expected_fields' => ['success', 'id', 'url'],
                    'received_fields' => array_keys($result ?? [])
                ]
        	], 400);
        }

	}

	public function beamWebhook(Request $request) {

		try {
			// อ่านชนิดอีเวนต์จาก Beam
			$beamEvent = $request->header('X-Beam-Event');
			if ($beamEvent) {
				Log::info('Beam Webhook: Event header detected', ['x_beam_event' => $beamEvent]);
			}

			// ตรวจสอบลายเซ็น X-Beam-Signature (HMAC SHA256 base64) หรือสำรอง X-Hub-Signature หากมี
			$signature = $request->header('X-Beam-Signature') ?? $request->header('X-Hub-Signature');
			if (!$signature) {
                Log::warning('Beam Webhook: Missing signature header');
                return response()->json(['error' => 'Missing signature'], 400);
            }
			
			if (!$this->validateSignature($request, $signature)) {
                Log::warning('Beam Webhook: Invalid signature', [
                    'signature' => $signature,
                    'body_hash' => hash('sha256', $request->getContent())
                ]);
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            Log::info('Beam Webhook: Signature validated successfully');

			// ===== 3️⃣ Decode JSON Payload =====
            $payload = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Beam Webhook: JSON Decode Error', [
                    'error' => json_last_error_msg()
                ]);
                return response()->json(['error' => 'Invalid JSON payload'], 400);
            }

			// Log payload structure only, not sensitive data
			Log::info('Beam Webhook Payload Parsed', [
				'event_type' => $beamEvent ?? 'unknown',
				'payload_keys' => array_keys($payload),
				'has_payment_data' => isset($payload['paymentLinkId']) || isset($payload['chargeId'])
			]);
			
			// แยกการประมวลผลตามรูปแบบอีเวนต์ใหม่ของ Beam
			$processed = false;
			$order = null;
			$status = null;
			$amount = null;
			$ackMeta = [];

			if (($beamEvent && strtolower($beamEvent) === 'payment_link.paid') || isset($payload['paymentLinkId'])) {
			// 	// กรณี payment_link.paid ตามเอกสาร
				$paymentLinkId = $payload['paymentLinkId'] ?? null;
				$status = $payload['status'] ?? null; // ควรเป็น PAID
				$amount = $payload['order']['netAmount'] ?? null;
				$referenceId = $payload['order']['referenceId'] ?? null;

				if (!$paymentLinkId || !$status) {
					Log::warning('Beam Webhook: Missing required fields for payment_link.paid', [
						'paymentLinkId' => $paymentLinkId,
						'status' => $status
					]);
					return response()->json(['error' => 'Missing required fields for payment_link.paid'], 400);
				}

				// หาออร์เดอร์ด้วย paymentLinkId ก่อน (เราเก็บไว้ที่ kbank_qrcode_id ตอนสร้างลิงก์)
				$order = Order::where('kbank_qrcode_id', $paymentLinkId)->first();
				if (!$order && $referenceId) {
					$ref = $referenceId;
					if (stripos($ref, 'order#') === 0) {
						$ref = substr($ref, 6);
					}
					$order = Order::where('formatted_id', $ref)->first();
				}

				if (!$order) {
					Log::warning('Beam Webhook: Order not found for payment_link.paid', [
						'paymentLinkId' => $paymentLinkId,
						'referenceId' => $referenceId
					]);
					return response()->json([
						'acknowledged' => true,
						'message' => 'Order not found but acknowledged',
						'paymentLinkId' => $paymentLinkId
					], 200);
				}

				$this->saveBeamCallback($order, $payload);

				switch (strtolower($status)) {
					case 'paid':
						$this->handleBeamPaymentSuccess($order, $payload);
						$processed = true;
						break;
					case 'failed':
					case 'cancelled':
					case 'expired':
						$this->handleBeamPaymentFailed($order, $payload);
						$processed = true;
						break;
					default:
						// payment_link.paid ควรเป็น PAID เท่านั้น แต่หากมีสถานะอื่นให้บันทึกไว้
						Log::warning('Beam Webhook: Unexpected status for payment_link.paid', [
							'status' => $status
						]);
						break;
				}

				$ackMeta = [
					'paymentLinkId' => $paymentLinkId,
					'referenceId' => $referenceId
				];

			} else if (($beamEvent && strtolower($beamEvent) === 'charge.succeeded') || isset($payload['chargeId'])) {
				// กรณี charge.succeeded เผื่อมีการใช้งานในอนาคต
				$status = $payload['status'] ?? null; // ควรเป็น SUCCEEDED
				$amount = $payload['amount'] ?? null;
				$referenceId = $payload['referenceId'] ?? null;
				$paymentLinkId = $payload['sourceId'] ?? null; // อาจอ้างอิงได้ถ้าเป็นจาก payment link

				$order = null;
				if ($referenceId) {
					$ref = $referenceId;
					if (stripos($ref, 'order#') === 0) {
						$ref = substr($ref, 6);
					}
					$order = Order::where('formatted_id', $ref)->first();
				}
				if (!$order && $paymentLinkId) {
					$order = Order::where('kbank_qrcode_id', $paymentLinkId)->first();
				}

				if (!$order) {
					Log::warning('Beam Webhook: Order not found for charge.succeeded', [
						'chargeId' => $payload['chargeId'] ?? null,
						'referenceId' => $referenceId,
						'sourceId' => $paymentLinkId
					]);
					return response()->json([
						'acknowledged' => true,
						'message' => 'Order not found but acknowledged'
					], 200);
				}

				$this->saveBeamCallback($order, $payload);

				if ($status && strtolower($status) === 'succeeded') {
					$this->handleBeamPaymentSuccess($order, $payload);
					$processed = true;
				} else if ($status && in_array(strtolower($status), ['failed','cancelled','expired','declined'])) {
					$this->handleBeamPaymentFailed($order, $payload);
					$processed = true;
				}

				$ackMeta = [
					'chargeId' => $payload['chargeId'] ?? null,
					'referenceId' => $referenceId
				];

			} else {
				// รองรับรูปแบบเดิม (purchaseId/state)
				$requiredFields = ['purchaseId', 'state'];
				foreach ($requiredFields as $field) {
					if (!isset($payload[$field])) {
						Log::warning('Beam Webhook: Missing required field (legacy)', [
							'missing_field' => $field,
							'payload' => $payload
						]);
						return response()->json(['error' => "Missing required field: {$field}"], 400);
					}
				}

				$purchaseId = $payload['purchaseId'];
				$status = $payload['state'];
				$amount = $payload['amount'] ?? null;
				$order = Order::where('kbank_qrcode_id', $purchaseId)->first();

				if (!$order) {
					Log::warning('Beam Webhook: Order not found (legacy)', [
						'purchaseId' => $purchaseId
					]);
					return response()->json([
						'acknowledged' => true,
						'message' => 'Order not found but acknowledged',
						'purchaseId' => $purchaseId
					], 200);
				}

				$this->saveBeamCallback($order, $payload);

				switch (strtolower($status)) {
					case 'complete':
					case 'completed':
					case 'successful':
					case 'paid':
						$this->handleBeamPaymentSuccess($order, $payload);
						$processed = true;
						break;
					case 'failed':
					case 'cancelled':
					case 'expired':
					case 'declined':
						$this->handleBeamPaymentFailed($order, $payload);
						$processed = true;
						break;
					case 'pending':
					case 'processing':
						$processed = true;
						break;
					default:
						Log::warning('Beam Webhook: Unknown status (legacy)', [
							'status' => $status
						]);
						break;
				}

				$ackMeta = [
					'purchaseId' => $purchaseId
				];
			}

			
			return response()->json(array_merge([
				'acknowledged' => true,
				'message' => 'Webhook processed successfully',
				'data' => [
					'orderId' => $order ? $order->formatted_id : null,
					'status' => $status,
					'processed' => $processed,
					'timestamp' => now()->toISOString()
				]
			], $ackMeta), 200);

		} catch (Exception $e) {
			 Log::error('Beam Webhook Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['acknowledged' => false, 'error' => 'Internal Server Error'], 500);
		}
	}

	// ตรวจสอบ webhook signature (HMAC SHA256) ตาม Beam standards
	private function validateSignature(Request $request, $signature) {
		try {
			$secret = env('BEAM_WEBHOOK_SECRET', 'your-secret-here');
			$expected = base64_encode(hash_hmac('sha256', $request->getContent(), $secret, true));
			return hash_equals($expected, $signature);
		} catch (Exception $e) {
			Log::error('Beam Webhook signature validation error', [
				'error' => $e->getMessage(),
				'signature' => $signature
			]);
			return false;
		}
	}

	// บันทึกข้อมูล callback จาก Beam
	private function saveBeamCallback($order, $payload) {
		try {
			// ใช้ DB::table เพื่อหลีกเลี่ยงปัญหา model
			$callbackData = [
				'order_id' => $order->id,
				'payment_slug' => $order->payment_slug,
				'response' => json_encode($payload),
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			];

			try {
				// ลองบันทึกลง order_payments table
				DB::table('smm_order_payment')->insert($callbackData);
				Log::info('Beam Callback Saved', [
					'order_id' => $order->id,
					'has_purchase_id' => !empty($payload['purchaseId']),
					'state' => $payload['state'] ?? 'unknown'
				]);
			} catch (Exception $e) {
				// ถ้า table ไม่มี ให้ log แต่ไม่ error
				Log::info('Could not save to order_payments table (table may not exist)', [
					'order_id' => $order->id,
					'error' => $e->getMessage()
				]);
			}

		} catch (Exception $e) {
			Log::error('Failed to save Beam callback', [
				'error' => $e->getMessage(),
				'order_id' => $order->id,
				'trace' => $e->getTraceAsString()
			]);
		}
	}

	// จัดการเมื่อการชำระเงินสำเร็จ
	private function handleBeamPaymentSuccess($order, $payload) {
		try {
			// ตรวจสอบว่า order ยังไม่ได้ชำระเงิน
			if ($order->payment_status == 1) {
				Log::info('Beam Payment: Order already paid', [
					'order_id' => $order->id,
					'formatted_id' => $order->formatted_id
				]);
				return;
			}

			// Log::info('Beam Payment: Processing payment success', [
			// 	'order_id' => $order->id,
			// 	'formatted_id' => $order->formatted_id,
			// 	'purchase_id' => $payload['purchaseId'] ?? 'unknown'
			// ]);

				// อัพเดตสถานะ order และบันทึกค่าธรรมเนียมธุรกรรม
			$order->payment_status = 1;
			$order->order_status = 2;
			$order->end_shopping_date = date('Y-m-d H:i:s'); // บันทึกวันที่และเวลาที่ชำระเงินสำเร็จ
			$order->save();

			// สร้าง OrderPayment record
			$current_date = date('Y-m-d H:i:s');
			$payment_data = [
				'order_id' => $order->id,
				'payment_slug' => $order->payment_slug,
				'reference_order' => $payload['purchaseId'] ?? '',
				'items' => '', // ไม่จำเป็นสำหรับ Beam
				'response' => json_encode($payload),
				'created_at' => $current_date
			];

			try {
				\App\OrderPayment::insert($payment_data);
				Log::info('OrderPayment record created successfully', [
					'order_id' => $order->id,
					'has_purchase_id' => !empty($payload['purchaseId'])
				]);
			} catch (Exception $e) {
				Log::error('Failed to create OrderPayment record', [
					'order_id' => $order->id,
					'error' => $e->getMessage(),
					'has_payment_data' => !empty($payment_data)
				]);
				// ไม่ throw error เพราะการอัพเดต order สำเร็จแล้ว
			}

			// อัพเดต order shops ให้เป็นสถานะรอการจัดส่ง
			\App\OrderShop::where('order_id', $order->id)->update([
				'end_shopping_date' => $current_date,
				'updated_at' => $current_date,
				'order_status' => 2,
				'payment_status' => 1
			]);

			// อัพเดต OrderDetail
			\App\OrderDetail::where('order_id',$order->id)->update([
				'payment_status' => 1,
				'payment_date' => $current_date,
				'status' => 2
			]);

			// Log::info('Beam Payment Success: Order updated', [
			// 	'order_id' => $order->id,
			// 	'formatted_id' => $order->formatted_id,
			// 	'payment_status' => $order->payment_status,
			// 	'order_status' => $order->order_status,
			// 	'purchase_id' => $payload['purchaseId'] ?? 'unknown',
			// 	'transaction_fee' => $transactionFee
			// ]);

			// ส่งอีเมลแจ้งเตือน
			try {
				if (class_exists('EmailHelpers')) {
					EmailHelpers::sendOrderNotificationEmail($order->formatted_id);
					Log::info('Order notification email sent', ['order_id' => $order->formatted_id]);
				}
			} catch (Exception $e) {
				Log::error('Failed to send order notification email', [
					'order_id' => $order->id,
					'error' => $e->getMessage()
				]);
			}

			// ส่ง push notification
			try {
				$this->buyerNotification($order);
				Log::info('Buyer notification sent', ['order_id' => $order->id]);
			} catch (Exception $e) {
				Log::error('Failed to send buyer notification', [
					'order_id' => $order->id,
					'error' => $e->getMessage()
				]);
			}

		} catch (Exception $e) {
			Log::error('Error handling Beam payment success', [
				'order_id' => $order->id,
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			]);
			throw $e; // Re-throw เพื่อให้ webhook handler จัดการ
		}
	}

	// จัดการเมื่อการชำระเงินล้มเหลว
	private function handleBeamPaymentFailed($order, $payload) {
		try {
			Log::info('Beam Payment Failed', [
				'order_id' => $order->id,
				'formatted_id' => $order->formatted_id,
				'status' => $payload['status'] ?? 'unknown',
				'reason' => $payload['failureReason'] ?? 'Not provided'
			]);

			// อัพเดตสถานะ order เป็น cancelled หรือ failed
			$updateData = [
				'payment_status' => 0,
				'order_status' => 0, // หรือสถานะที่เหมาะสม
				'updated_at' => date('Y-m-d H:i:s')
			];

			Order::where('id', $order->id)->update($updateData);

			// อาจจะส่งอีเมลแจ้งเตือนการยกเลิก (ถ้าต้องการ)

		} catch (Exception $e) {
			Log::error('Error handling Beam payment failure', [
				'order_id' => $order->id,
				'error' => $e->getMessage()
			]);
		}
	}

	// กำหนด supportedPaymentMethods สำหรับ Beam ตาม payment option ที่เลือก
	private function getBeamPaymentMethods($orderInfo) {
		// ตรวจสอบ payment option ที่เลือกจาก order (ใช้ slug แทน id)
		$paymentOption = \App\PaymentOption::where('slug', $orderInfo->payment_slug)->first();
		
		// กำหนด payment methods ตาม slug หรือ configuration
		if ($paymentOption && $paymentOption->slug) {
			switch ($paymentOption->slug) {
				case 'beam-qr':
				case 'beam-qrthb':
					Log::info('BEAM PAYMENT METHOD: QR Code selected', ['slug' => $paymentOption->slug, 'result' => 'qrPromptPay']);
					return 'qrPromptPay';
				case 'beam-credit':
				case 'beam-creditcard':
					Log::info('BEAM PAYMENT METHOD: Credit Card selected', ['slug' => $paymentOption->slug, 'result' => 'card']);
					return 'card';
				case 'beam-banking':
				case 'beam-internetbanking':
					Log::info('BEAM PAYMENT METHOD: Mobile Banking selected', ['slug' => $paymentOption->slug, 'result' => 'mobileBanking']);
					return 'mobileBanking';
				case 'beam-ewallet':
					Log::info('BEAM PAYMENT METHOD: E-Wallet selected', ['slug' => $paymentOption->slug, 'result' => 'eWallets']);
					return 'eWallets';
				case 'beam':
				default:
					Log::info('BEAM PAYMENT METHOD: All methods selected', ['slug' => $paymentOption->slug, 'result' => 'qrPromptPay,card,eWallets,mobileBanking']);
					return 'qrPromptPay,card,eWallets,mobileBanking';
			}
		}
		
		return 'qrPromptPay,card,eWallets,mobileBanking';
	}

	// ตรวจสอบสถานะการชำระเงิน (ใช้ Database เท่านั้น - Beam ไม่มี status API)
	function checkBeamPaymentStatus(Request $request, $formatted_id = null) {
		try {
			$userid = Auth::User()->id;
			$order = Order::where(['user_id' => $userid, 'formatted_id' => $formatted_id])->first();

			if (!$order) {
				return response()->json(['error' => 'Order not found'], 404);
			}

			$purchaseId = $order->kbank_qrcode_id;
			if (!$purchaseId) {
				return response()->json(['error' => 'Purchase ID not found'], 404);
			}
			
			// ตรวจสอบจาก order_payments table (Beam จะส่งข้อมูลมาที่ webhook เท่านั้น)
			$payment_record = \App\OrderPayment::where('order_id', $order->id)
				->where('payment_slug', 'LIKE', 'beam%')
				->orderBy('created_at', 'desc')
				->first();

			$result = null;

			if ($payment_record) {
				$payment_data = json_decode($payment_record->response, true);
				$result = [
					'id' => $purchaseId,
					'status' => $payment_data['status'] ?? 'pending',
					'source' => 'webhook_callback',
					'data' => $payment_data,
					'last_updated' => $payment_record->created_at,
					'purchase_id' => $purchaseId,
					'callback_received' => true
				];
			} else {
				// ถ้ายังไม่ได้รับ webhook callback ให้ใช้ order status
				$result = [
					'id' => $purchaseId,
					'status' => $order->payment_status == 1 ? 'completed' : 'pending',
					'source' => 'order_status',
					'order_payment_status' => $order->payment_status,
					'order_status' => $order->order_status,
					'last_updated' => $order->updated_at,
					'callback_received' => false,
					'waiting_for_webhook' => true
				];
			}

			$response_data = [
				'success' => true,
				'order_status' => $order->payment_status,
				'order_status_text' => $order->payment_status == 1 ? 'paid' : 'pending',
				'beam_status' => $result,
				'purchase_id' => $purchaseId,
				'order_info' => [
					'id' => $order->id,
					'formatted_id' => $order->formatted_id,
					'total' => $order->total_final_price,
					'created_at' => $order->created_at,
					'payment_status' => $order->payment_status,
					'order_status' => $order->order_status
				],
				'beam_info' => [
					'webhook_only' => true,
					'no_status_api' => true,
					'description' => 'Beam ส่งข้อมูลมาที่ webhook เท่านั้น ไม่มี status check API'
				]
			];

			// เพิ่มข้อมูลสำหรับ redirect ถ้าชำระเงินเรียบร้อยแล้ว
			if ($order->payment_status == 1) {
				$response_data['payment_complete'] = true;
				$response_data['redirect_url'] = url("/checkout/thanks/{$order->formatted_id}");
				$response_data['message'] = 'Payment completed successfully!';
			} else {
				$response_data['payment_complete'] = false;
				$response_data['message'] = 'Waiting for payment confirmation...';
			}

			return response()->json($response_data);

		} catch (Exception $e) {
			Log::error('Error checking Beam payment status', [
				'error' => $e->getMessage(),
				'formatted_id' => $formatted_id,
				'trace' => $e->getTraceAsString()
			]);

			return response()->json([
				'success' => false,
				'error' => 'Failed to check payment status', 
				'debug' => $e->getMessage()
			], 500);
		}
	}

	// ตรวจสอบสถานะการชำระเงินจาก Beam API
	public function checkBeamPaymentStatusFromAPI(Request $request, $formatted_id = null) {
		try {
			$userid = Auth::User()->id;
			$order = Order::where(['user_id' => $userid, 'formatted_id' => $formatted_id])->first();

			if (!$order) {
				return response()->json(['error' => 'Order not found'], 404);
			}

			$purchaseId = $order->kbank_qrcode_id;
			if (!$purchaseId) {
				return response()->json(['error' => 'Purchase ID not found'], 404);
			}

			// เรียก Beam API เพื่อตรวจสอบสถานะ
			$beamApiUrl = "https://wms.simummuangonline.com/beam-api/public/?id=" . $purchaseId;
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $beamApiUrl,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "GET",
				CURLOPT_HTTPHEADER => array(
					"cache-control: no-cache",
					"accept: application/json"
				),
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
			));

		 /**
          * LOG → บันทึกก่อนยิง API
          * เก็บข้อมูล URL, order_id, purchase_id, formatted_id
         **/
        Log::info('Before adjust Beam API :', [
            'order_id'      => $order->id,
			'formatted_id'   => $formatted_id,
			'payment_amount' => $order->total_final_price,
			'payment_method' => $order->payment_slug,
            'purchase_id'   => $purchaseId,
            'request_url'   => $beamApiUrl,
            'method'        => 'GET'
        ]);

			$response = curl_exec($curl);
			$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);

		 /**
          * LOG หลังยิง API
          * เก็บ: HTTP CODE, raw response, response length, URL, order_id, purchase_id
         **/
        Log::info('After adjust Beam API :', [
            'order_id'        => $order->id,
            'purchase_id'     => $purchaseId,
            'request_url'     => $beamApiUrl,
            'http_code'       => $http_code,
            'curl_error'      => $err ?: null,
            'response_raw'    => $response,
            'response_length' => strlen($response)
        ]);

			if ($err) {
				Log::error('Beam API CURL Error', [
					'error' => $err,
					'order_id' => $order->id,
					'has_purchase_id' => !empty($purchaseId)
				]);
				return response()->json(['error' => 'Failed to connect to Beam API'], 500);
			}

			if ($http_code !== 200) {
				Log::error('Beam API HTTP Error', [
					'http_code' => $http_code,
					'response_length' => strlen($response),
					'order_id' => $order->id
				]);
				return response()->json(['error' => 'Beam API returned error: ' . $http_code], 400);
			}

			$result = json_decode($response, true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				Log::error('Beam API JSON Decode Error', [
					'error' => json_last_error_msg(),
					'response_length' => strlen($response)
				]);
				return response()->json(['error' => 'Invalid response from Beam API'], 400);
			}

			// Log::info('Beam API Status Check Response', [
			// 	'order_id' => $order->id,
			// 	'purchase_id' => $purchaseId,
			// 	'api_response' => $result
			// ]);

			// ตรวจสอบสถานะการชำระเงินตาม Beam Payment API
			$beamStatus = $result['data']['status'] ?? 'unknown';
			
			if (isset($result['success']) && $result['success'] && $beamStatus === 'PAID') {
				
				// อัพเดตสถานะการชำระเงินเป็นสำเร็จ
				if ($order->payment_status != 1) {
					$order->payment_status = 1;
					$order->order_status = 2;
					$order->end_shopping_date = date('Y-m-d H:i:s'); // บันทึกวันที่และเวลาที่ชำระเงินสำเร็จ
					$order->save();

					// สร้าง OrderPayment record
					$current_date = date('Y-m-d H:i:s');
					$payment_data = [
						'order_id' => $order->id,
						'payment_slug' => $order->payment_slug,
						'reference_order' => $purchaseId,
						'items' => '',
						'response' => json_encode($result),
						'created_at' => $current_date
					];

					try {
						\App\OrderPayment::insert($payment_data);
						Log::info('OrderPayment record created from API check', [
							'order_id' => $order->id,
							'has_purchase_id' => !empty($purchaseId)
						]);
					} catch (Exception $e) {
						Log::error('Failed to create OrderPayment record from API check', [
							'order_id' => $order->id,
							'error' => $e->getMessage()
						]);
					}

					// อัพเดต order shops ให้เป็นสถานะรอการจัดส่ง
					\App\OrderShop::where('order_id', $order->id)->update([
						'order_status' => 2
					]);

					// ส่งอีเมลแจ้งเตือน
					try {
						if (class_exists('EmailHelpers')) {
							EmailHelpers::sendOrderNotificationEmail($order->formatted_id);
							Log::info('Order notification email sent from API check', ['order_id' => $order->formatted_id]);
						}
					} catch (Exception $e) {
						Log::error('Failed to send order notification email from API check', [
							'order_id' => $order->id,
							'error' => $e->getMessage()
						]);
					}

					// ส่ง push notification
					try {
						$this->buyerNotification($order);
						Log::info('Buyer notification sent from API check', ['order_id' => $order->id]);
					} catch (Exception $e) {
						Log::error('Failed to send buyer notification from API check', [
							'order_id' => $order->id,
							'error' => $e->getMessage()
						]);
					}

					// Log::info('Beam Payment Success: Order updated from API check', [
					// 	'order_id' => $order->id,
					// 	'formatted_id' => $order->formatted_id,
					// 	'payment_status' => $order->payment_status,
					// 	'order_status' => $order->order_status,
					// 	'purchase_id' => $purchaseId
					// ]);
				}

				return response()->json([
					'success' => true,
					'payment_status' => 'completed',
					'order_status' => $order->order_status,
					'message' => 'Payment completed successfully!',
					'redirect_url' => url("/checkout/thanks/{$order->formatted_id}")
				]);

			} elseif (in_array($beamStatus, ['DISABLED', 'EXPIRED'])) {
				// ชำระเงินไม่สำเร็จ
				return response()->json([
					'success' => true,
					'payment_status' => 'failed',
					'order_status' => $order->order_status,
					'message' => $beamStatus === 'EXPIRED' ? 'ลิงก์ชำระเงินหมดอายุ' : 'ลิงก์ชำระเงินถูกปิดใช้งาน',
					'beam_status' => $beamStatus
				]);
			} elseif ($beamStatus === 'ACTIVE') {
				// ยังไม่เสร็จสิ้น (active - รอการชำระเงิน)
				return response()->json([
					'success' => true,
					'payment_status' => 'pending',
					'order_status' => $order->order_status,
					'message' => 'กำลังรอการชำระเงิน',
					'beam_status' => $beamStatus
				]);
			} else {
				// สถานะไม่รู้จัก
				return response()->json([
					'success' => false,
					'payment_status' => 'unknown',
					'order_status' => $order->order_status,
					'message' => 'สถานะการชำระเงินไม่ถูกต้อง: ' . $beamStatus,
					'beam_status' => $beamStatus
				]);
			}

		} catch (Exception $e) {
			Log::error('Error checking Beam payment status from API', [
				'error' => $e->getMessage(),
				'formatted_id' => $formatted_id,
				'trace' => $e->getTraceAsString()
			]);

			return response()->json([
				'success' => false,
				'error' => 'Failed to check payment status from API', 
				'debug' => $e->getMessage()
			], 500);
		}
	}

	/**
	 * คำนวณ transaction fee ให้ถูกต้อง
	 */
	private function calculateTransactionFee($amount, $rate) {
		if ($rate <= 0) {
			return 0;
		}
		
		// คำนวณ transaction fee และปัดเศษให้ถูกต้อง
		$fee = round($amount * $rate / 100, 2);
		
		// ตรวจสอบว่าการคำนวณถูกต้องหรือไม่
		$expected_total = round($amount * (1 + $rate / 100), 2);
		$actual_total = round($amount + $fee, 2);
		
		// ถ้าไม่ตรงกัน ให้ปรับ transaction fee
		if ($expected_total != $actual_total) {
			$fee = round($expected_total - $amount, 2);
		}
		
		return $fee;
	}

	/**
	 * ตรวจสอบสถานะ Beam Payment และอัปเดตสถานะ order
	 * ใช้สำหรับ redirect URL จาก Beam Payment
	 */
	public function verifyBeamPayment(Request $request, $formatted_id = null) {
		try {
			$userid = Auth::User()->id;
			$order = Order::where(['user_id' => $userid, 'formatted_id' => $formatted_id])->first();

			if (!$order) {
				Log::warning('Beam Payment Verify: Order not found', [
					'formatted_id' => $formatted_id,
					'user_id' => $userid
				]);
				return redirect('/checkout/cart')->with('error', 'ไม่พบคำสั่งซื้อ');
			}

			$purchaseId = $order->kbank_qrcode_id;
			if (!$purchaseId) {
				Log::warning('Beam Payment Verify: Purchase ID not found', [
					'formatted_id' => $formatted_id,
					'order_id' => $order->id
				]);
				return redirect('/checkout/cart')->with('error', 'ไม่พบข้อมูลการชำระเงิน');
			}

			// ตรวจสอบสถานะจาก Beam API
			$beamApiUrl = "https://wms.simummuangonline.com/beam-api/public/?id=" . $purchaseId;
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => $beamApiUrl,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "GET",
				CURLOPT_HTTPHEADER => array(
					"cache-control: no-cache",
					"accept: application/json"
				),
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
			));

			/**
		     * LOG → บันทึกก่อนยิง API
		     * เก็บข้อมูล URL, order_id, purchase_id, formatted_id
		    **/
		    Log::info('Before Beam Payment Verify API Call :', [
			    'formatted_id'  => $formatted_id,
				'order_id'      => $order->id,
				'payment_amount'   => $order->total_final_price,
				'payment_method'   => $order->payment_slug,
			    'purchase_id'   => $purchaseId,
			    'request_url'   => $beamApiUrl,
			    'method'        => 'GET'
			]);

			$response = curl_exec($curl);
			$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);

			Log::info('Beam Payment Verify API Response', [
				'formatted_id' => $formatted_id,
				'purchase_id' => $purchaseId,
				'http_code' => $http_code,
				'response' => $response,
				'curl_error' => $err
			]);

			if ($err) {
				Log::error('Beam Payment Verify: CURL Error', [
					'formatted_id' => $formatted_id,
					'error' => $err
				]);
				return redirect('/checkout/cart')->with('error', 'เกิดข้อผิดพลาดในการตรวจสอบสถานะการชำระเงิน');
			}

			if ($http_code !== 200) {
				Log::error('Beam Payment Verify: HTTP Error', [
					'formatted_id' => $formatted_id,
					'http_code' => $http_code,
					'response' => $response
				]);
				return redirect('/checkout/cart')->with('error', 'ไม่สามารถตรวจสอบสถานะการชำระเงินได้');
			}

			$beamData = json_decode($response, true);
			
			if (!$beamData || !isset($beamData['data']['status'])) {
				Log::error('Beam Payment Verify: Invalid Response', [
					'formatted_id' => $formatted_id,
					'response' => $response,
					'beamData' => $beamData
				]);
				return redirect('/checkout/cart')->with('error', 'ข้อมูลการตอบกลับไม่ถูกต้อง');
			}

			$status = $beamData['data']['status'];
			
			Log::info('Beam Payment Verify: Status Check', [
				'formatted_id' => $formatted_id,
				'purchase_id' => $purchaseId,
				'beam_status' => $status,
				'current_payment_status' => $order->payment_status
			]);

			// ตรวจสอบสถานะการชำระเงินตาม Beam Payment API
			if ($status === 'PAID') {
				// ชำระเงินสำเร็จ
				if ($order->payment_status != 1) {
					// $this->handleBeamPaymentSuccess($order, $beamData);
					$updateResult = Order::updateOrderAfterPayment($order);
					
					Log::info('Beam Payment Verify: Payment Success', [
						'formatted_id' => $formatted_id,
						'order_id' => $order->id
					]);
				}
				return redirect("/checkout/thanks/{$formatted_id}")->with('success', 'ชำระเงินสำเร็จ');
				
			} elseif (in_array($status, ['DISABLED', 'EXPIRED'])) {
				// ชำระเงินไม่สำเร็จ
				Log::info('Beam Payment Verify: Payment Failed', [
					'formatted_id' => $formatted_id,
					'beam_status' => $status
				]);
				$errorMessage = $status === 'EXPIRED' ? 'ลิงก์ชำระเงินหมดอายุ' : 'ลิงก์ชำระเงินถูกปิดใช้งาน';
				return redirect('/checkout/cart')->with('error', 'การชำระเงินไม่สำเร็จ: ' . $errorMessage);
				
			} elseif ($status === 'ACTIVE') {
				// ยังไม่เสร็จสิ้น (active - รอการชำระเงิน)
				Log::info('Beam Payment Verify: Payment Pending', [
					'formatted_id' => $formatted_id,
					'beam_status' => $status
				]);
				return redirect("/checkout/beam/{$formatted_id}")->with('info', 'กำลังรอการชำระเงิน กรุณาทำการชำระเงิน');
				
			} else {
				// สถานะไม่รู้จัก
				Log::warning('Beam Payment Verify: Unknown Status', [
					'formatted_id' => $formatted_id,
					'beam_status' => $status
				]);
				return redirect('/checkout/cart')->with('error', 'สถานะการชำระเงินไม่ถูกต้อง: ' . $status);
			}

		} catch (\Exception $e) {
			Log::error('Beam Payment Verify: Exception', [
				'formatted_id' => $formatted_id,
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			]);
			return redirect('/checkout/cart')->with('error', 'เกิดข้อผิดพลาดในการตรวจสอบสถานะการชำระเงิน');
		}
	}

	public function selectedMultiCartItem(Request $request)
	{
		try {
			DB::beginTransaction();
			$userId = Auth::user()->id;
			$selectedItems = $request->input('selectedCartIds',[]);
			
			$orderTemp = OrdersTemp::where('user_id', $userId)->first();
			if (empty($orderTemp)) {
				return response()->json([
					'status' => 'error',
					'message' => 'ไม่สามารถทำการสั่งซื้อได้ กรุณาตรวจสอบรายการสั่งซื้ออีกครั้ง'
				]);
			}
			
			Cart::unselectByIds($orderTemp->getCart()->pluck('id')->toArray());
			if (!empty($selectedItems)) {
				$newRequest = new Request(['cartItems' => $selectedItems]);
				$validateResult = (new self)->validateProductCartItem($newRequest);
				$rsValid = $validateResult->getData();
				if ($rsValid->status === 'error' || count($rsValid->passItems) === 0) {
					return response()->json([
						'status' => $rsValid->status??'error',
						'message' => $rsValid->message??'สิ้นค้ามีการเปลี่ยนแปลง กรุณาตรวจสอบอีกครั้ง'
					]);
				}
			
				$passIds = collect($rsValid->passItems)->pluck('id')->toArray();
				$orderTemp->getCart()->where('order_id', $orderTemp->id)
				->update(['is_selected' => DB::raw("CASE WHEN id IN (" . implode(',', $passIds) . ") THEN 1 ELSE 0 END")]);
			}
				
			$totalAmount = OrdersTemp::updateOrderPrice($orderTemp->id??null);
			$totalAmount = number_format($totalAmount??0 , 2);

			DB::commit();
			return response()->json([
				'status' => 'success',
				'selectedItems' => $selectedItems,
				'totalAmount' => $totalAmount
			]);
		} catch (Exception $e) {
			DB::rollBack();
			Log::error('Error fetching selected cart item', [
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			]);
			return response()->json(['status' => 'error', 'message' => 'ไม่สามารถทำรายการได้'], 500);
		}
	}

	public function selectedCartItem(Request $request)
	{
		try {
			$userId = Auth::user()->id;
			$cartItemId = $request->input('cartItemId');
			$isSelected = $request->boolean('isSelected');
			if (empty($cartItemId)) {
				return response()->json(['status' => 'error', 'message' => 'ไม่พบรายการสั่งซื้อ']);
			}
			$cartItem = Cart::where('id', $cartItemId)->where('user_id', $userId)->first();
			if (empty($cartItem)) {
				return response()->json(['status' => 'error', 'message' => 'ไม่พบสินค้าในตะกร้า']);
			}

			$newRequest = new Request(['cartItems' => [$cartItemId]]);
			$validateResult = (new self)->validateProductCartItem($newRequest);
			$rsValid = $validateResult->getData();
			
			if ($rsValid->status === 'error' || count($rsValid->passItems) === 0) {
				return response()->json([
					'status' => 'error',
					'message' => $rsValid->message ?? 'สินค้าในตะกร้ามีการเปลี่ยนแปลง กรุณาตรวจสอบอีกครั้ง',
					'selectedItems' => $rsValid->selectedItems ?? [],
					'priceChange' => $rsValid->priceChange ?? [],
					'productClose' => $rsValid->productClose ?? [],
					'shopClose' => $rsValid->shopClose ?? [],
					'outOfStock' => $rsValid->outOfStock ?? [],
					'shortStock' => $rsValid->shortStock ?? [],
				]);
			}
			DB::beginTransaction();
			$cartItem->is_selected = $isSelected ? 1 : 0;
			$cartItem->save();
			$totalAmount = OrdersTemp::updateOrderPrice($cartItem->order_id??null);
			DB::commit();
			return response()->json(['status' => 'success', 'totalAmount' => number_format($totalAmount??0 , 2)]);
		}catch (Exception $e) {
			DB::rollBack();
			Log::error('Error selectedCartItem', [
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			]);
			return response()->json(['status' => 'error', 'message' => 'ไม่สามารถทำรายการได้'], 500);
		}
	}

	public function validateProductCartItem(Request $request)
	{
		try {
			$userId = Auth::id();
			$selectedCartItems = $request->input('cartItems', []);
			if (empty($selectedCartItems)) {
				return response()->json([
					'status' => 'error',
					'message' => 'No items selected',
					'selectedItems' => [],
					'Cartitems' => []
				], 422);
			}

			$cartItems = Cart::query()
				->where('user_id', $userId)
				->whereIn('id', $selectedCartItems)
				->with(['getShop', 'getPrd', 'getCatDesc'])
			->get();
			if ($cartItems->isEmpty()) {
				return response()->json([
					'status' => 'error',
					'message' => 'Items not found',
					'selectedItems' => $selectedCartItems,
					'itemCarts' => []
				], 422);
			}

			$priceChange = [];
			$productClose = [];
			$shopClose = [];
			$outOfStock = [];
			$shortStock = [];
			$passItems = [];

			// $dbSelectedIds = Cart::query()
			// ->where('user_id', $userId)
			// ->where('is_selected', 1)
			// ->pluck('id')
			// ->toArray();

			// $checkMatch = false;
			// $cartItemIds = $cartItems->pluck('id')->toArray();
			// $checkMatch =
			// 	empty(array_diff($selectedCartItems, $dbSelectedIds)) &&
			// 	empty(array_diff($dbSelectedIds, $selectedCartItems)) &&
			// 	empty(array_diff($selectedCartItems, $cartItemIds)) &&
			// 	empty(array_diff($cartItemIds, $selectedCartItems));

			// if (!$checkMatch) {
			// 	return response()->json([
			// 		'status' => 'error',
			// 		'message' => [$selectedCartItems, $dbSelectedIds, $cartItemIds],
			// 		'data' => [$selectedCartItems, $dbSelectedIds, $cartItemIds]
			// 	], 422);
			// }

			// $missingProduct = $cartItems->filter(fn($cart) => $cart->getPrd === null);
			// $missingShop = $cartItems->filter(fn($cart) => $cart->getShop === null);
			// if ($missingShop->isNotEmpty() || $missingProduct->isNotEmpty()) {
			// 	Log::warning('Invalid cart items found on function validateProductCartItem', [
			// 		'missingItems' => [
			// 			'product' => $missingProduct->pluck('id')->toArray(),
			// 			'shop' => $missingShop->pluck('id')->toArray()
			// 		]
			// 	]);
			// 	return response()->json([
			// 		'status' => 'error',
			// 		'message' => 'สินค้าในตะกร้าปิดการขาย หรือสินค้าปิดการขาย',
			// 	], 422);
			// }

			foreach ($cartItems as $cartVal) {
				$product = $cartVal->getPrd;
				$shop    = $cartVal->getShop;

				if (!$product || !$shop) {
					$productClose[] = $cartVal; continue;
				}

				if ($product->status === '0') {
					$productClose[] = $cartVal; continue;
				}

				if ($shop->status === '0') {
					$shopClose[] = $cartVal; continue;
				}

				if ($cartVal->cart_price !== $product->unit_price) {
					$priceChange[] = $cartVal; continue;
				}

				// $selectedCartItems[] = [
				// 	'id' => $cartVal->id,
				// 	'product_id' => $cartVal->product_id,
				// 	'quantity' => $cartVal->quantity,
				// 	'min_order_qty' => $product->min_order_qty
				// ];

				if ($product->stock === '0' ) {
					$qty = (int) $product->quantity;
					$min = (int) $product->min_order_qty;
					$cartQty = (int) $cartVal->quantity;

					if ($qty <= 0 || $qty < $min) {
						$outOfStock[] = $cartVal;
						continue;
					}

					if ($qty < $cartQty && $qty >= $min) {
						// $cartVal->quantity = $qty;
						$shortStock[] = $cartVal;
						continue;
					}
					
					if($cartQty < $min && $qty >= $min) {
						$shortStock[] = $cartVal;
						continue;
					}
					
					if($cartQty < $min) {
						$outOfStock[] = $cartVal;
						continue;
					}
				}
				

				$passItems[] = $cartVal;
			}

			$hasError = !empty($priceChange) || !empty($productClose) || !empty($shopClose) || !empty($outOfStock) || !empty($shortStock);
			$status = $hasError ? 'error' : 'success';
			$httpCode = $hasError ? 200 : 200;

			$result = [
				'status'       => $status,
				'message'      => $hasError ? 'สินค้าในตะกร้ามีการเปลี่ยนแปลง กรุณาตรวจสอบอีกครั้ง' : 'Success',
				'selectedItems'=> $selectedCartItems,
				'cartItems'    => $cartItems,
				'priceChange'  => $priceChange,
				'productClose' => $productClose,
				'shopClose'    => $shopClose,
				'outOfStock'   => $outOfStock,
				'shortStock'   => $shortStock,
				'passItems'    => array_merge($passItems),
				'failedItems'  => array_merge($priceChange, $productClose, $shopClose, $outOfStock )
			];

			return response()->json($result, $httpCode);

		} catch (\Exception $e) {
			Log::error('Error fetching selected cart item', [
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			]);

			return response()->json([
				'status' => 'error',
				'message' => 'Failed to fetch selected cart item'
			], 500);
		}
	}

	// public static function calculateSelectedCartItem(Request $request){
	// 	$cartId = $request->cartId;
	// 	$cartItem = Cart::where(['id'=>$cartId,'is_selected'=>true])->get();
	// 	return json_encode([
	// 		'status'=>'success',
	// 		'items'=>$cartItem,
	// 		'totalAmount'=>$cartItem->sum('cart_price')
	// 	]);
	// }

	public static function calculateSelectedCartItem(Request $request)
	{
		$cartId = $request->cartId;
		$cartItems = Cart::where([ 'id' => $cartId, 'is_selected' => true ])
			->with(['getPrd', 'getShop'])
			->get();
		$validated = [];
		$errors = [];
		$totalAmount = 0;

		foreach ($cartItems as $cart) {
			$itemErrors = $cart->validateItem();
			if (!empty($itemErrors)) {
				$errors[$cart->id] = $itemErrors;
			} else {
				$validated[] = $cart;
				$totalAmount += $cart->cart_price * $cart->quantity;
			}
		}

		return response()->json([
			'status' => empty($errors) ? 'success' : 'error',
			'items' => $validated,
			'errors' => $errors,
			'totalAmount' => $totalAmount
		]);
	}

}