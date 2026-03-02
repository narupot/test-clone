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
use Route;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\PaymentOption;

class CartController extends MarketPlace {

	public $query;

	public function __construct() {
		$this->middleware('authenticate');
	}

	public function index(Request $request) {
		
		$checkout_type = request()->segment(2);
		
		// ตรวจสอบว่าเป็นหน้า test หรือไม่
		$is_test_page = ($checkout_type === 'buy-now-end-shopping-test');

		$shop_address = $orderDetails = $paid_product = $user_address = $def_country_dtl = $shop_id_arr = [];
		$billing_address = $shipping_address = $ship_province_str = '';

		$userid = Auth::User()->id;
		$orderInfo = OrdersTemp::where(['user_id'=>$userid,'order_status'=>'0'])->first();

		
		$main_order = [];
		if(empty($orderInfo)){
			return redirect(action('Checkout\CartController@deleteTempOrder'));
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
		if($checkout_type == 'buy-now' || $checkout_type == 'buy-now-end-shopping' || $checkout_type == 'buy-now-end-shopping-test'){
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
		if(!empty($main_order)){
			$paid_product = \App\OrderDetail::getMainOrderDetail($main_order->id);
		}
		/***for address***/
		if($checkout_type == 'end-shopping' || $checkout_type == 'buy-now-end-shopping' || $checkout_type == 'buy-now-end-shopping-test'){

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

        // ดึง payment options ทั้งหมด
        $payment_option = \App\PaymentOption::where(['status'=>'1','payment_type'=>'1'])
            ->where('slug','!=','credit')
            ->with(['paymentOptName', 'transactionFeeConfig'])
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
			
			$time_arr = array_merge($c_arr,$n_arr,$nd_arr);
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
        
        // ดึงข้อมูลค่าธรรมเนียมสำหรับการชำระเงินแต่ละประเภท
        $transaction_fees = [
            'qr_code' => \App\TransactionFeeConfig::where('name', 'QR Code')->first(),
            'mobile_banking' => \App\TransactionFeeConfig::where('name', 'Mobile Banking')->first(),
            'credit_card' => \App\TransactionFeeConfig::where('name', 'Credit Debit Card')->first(),
            'wallet' => \App\TransactionFeeConfig::where('name', 'E-Wallet')->first(),
        ];

        //dd($shipping_address);
		return view('checkout.cart',compact(
			'def_country_dtl','ship_province_str','user_address','shipping_address','billing_address','payment_option','checkout_type','pickup_center_address','delivery_details'
		), [
			'orderInfo' => $orderInfo
			, 'orderDetails'=>$orderDetails
			, 'page_class'=>'cart-wrap'
			,'breadcrumb'=>$breadcrumb
			,'shop_address'=>$shop_address
			,'main_order'=>$main_order
			,'paid_product'=>$paid_product
			,'shipping_fee'=>$shipping_fee
			,'user_odd_info'=>$user_odd_info
			,'time_arr'=>[]
			,'delivery_time_arr'=>$delivery_time_arr
			,'transaction_fees'=>$transaction_fees
			]
		);
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
		$total_prom_disc_amt = 0;
		$shop_details = [];
		if(!empty($orderInfo)){
			$direction = $request->sort??'asc';
			$orderDetails = Cart::getCartList($orderInfo->id)
			->{ $direction === 'desc' ? 'sortByDesc' : 'sortBy' }('id')
			->values();

			foreach ($orderDetails as $key => $value) {
				if($value->getShop && $value->getShopDesc){
			    	$shop_details[$value->shop_id] = ['shop_name'=>$value->getShopDesc->shop_name,'shop_url'=>$value->getShop->shop_url];
			    	if(isset($user_credits[$value->shop_id])){
			    		$show_credit = 1;
			    	}
				}
			}
		}
		
		$referer_url = $request->headers->get('referer');
		$breadcrumb = $this->getBreadcrumb($referer_url);

		$default_shopping = session('shopping_list');
        $pur_prds_in_shop_list = \App\UserShoppingListItems::where(['shopping_list_id'=>$default_shopping,'is_completed'=>'1'])->count();
        $total_prds_in_shop_list = \App\UserShoppingListItems::where(['shopping_list_id'=>$default_shopping])->count();
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

		$cartId = $request->cartId;
		$newQuantity = $request->quantity;
		$userid = Auth::User()->id;
		$cartresult = Cart::where(['id'=>$cartId,'user_id'=>$userid])->first();

		// กรณีสินค้าในตะกร้าไ่มว่ามีการสร้าง order_id แล้ว
		if(!empty($cartresult)){
			$productId = $cartresult->product_id;
			$old_qty = $cartresult->quantity;
			$request_qty = $newQuantity - $old_qty;

			/******checking bargaining********/
			if($cartresult->product_from == 'bargain'){
				return ['status'=>'fail','msg'=>Lang::get('checkout.popup_price_has_already_bargained'),'qty'=>$old_qty];
			}
			$product_det = Product::where('id',$cartresult->product_id)->select('id','stock','quantity','unit_price','is_tier_price','order_qty_limit','min_order_qty','package_id')->first();
			
			// ดึง packagename
			$resultpackagename = DB::table("package_desc")->where("package_id","=",$product_det->package_id)->select("package_name")->first();

			// กรณี increase
			if($newQuantity >  $product_det->quantity + $cartresult->quantity ){
				return ['status'=>'fail_maxqty','msg'=>"คุณไม่สามารถเพิ่มสินค้าในตะกร้าได้เนื่องจากสต๊อกไม่เพียงพอหรือสต๊อกเท่ากับ ".$product_det->quantity ,'cartquantity'=>$cartresult->quantity,'maxqty'=>$product_det->quantity,'maxvalue'=>$product_det->quantity + $cartresult->quantity,"min_order_qty"=>$product_det->min_order_qty];
			}
			// End

			/*****checking minimum quantity*******/
			//เช็คปริมาณขั้นต่ำ
		    if($newQuantity < $product_det->min_order_qty ){
				// stock = 0
		        if($product_det->stock =='0' ){

						// คำนวณราคาตัด stock ใหม่ เมื่อกรอกจำนวนน้อยกว่าปริมาณสั่งซื้อขั้นต่ำ
						// เอา quantity ใน cart มาคิดใหม่
						$calnewquantityproduct =  $cartresult->quantity - $product_det->min_order_qty;
						$newquantityproduct =  $calnewquantityproduct + $product_det->quantity  ;

						// update smm_product เพิ่ม quantity ใน stock
						Product::where("id","=",$product_det->id)->update(["quantity"=>$newquantityproduct]);
						// update mongodb
						\App\MongoProduct::where("_id","=",$product_det->id)->update(["quantity"=>$newquantityproduct]);

						$product_price = GeneralFunctions::getProductPriceById($product_det->id,$newQuantity,$product_det);
						$total_price = $product_price * $product_det->min_order_qty;
					
						// update smm_cart ลด quantity ใน stock
						$affected = Cart::where(['id' => $cartId])->update(['quantity'=>$product_det->min_order_qty,'original_price'=>$product_price,'cart_price' => $product_price,'total_price'=>$total_price]);	
						$orderFinalPrice = OrdersTemp::updateOrderPrice($cartresult->order_id);
						$totQty = Cart::where('order_id',$cartresult->order_id)->sum('quantity');

		            	$msg = Lang::get('checkout.product_minimum_quantity_should_be').' '.$product_det->min_order_qty." ".$resultpackagename->package_name;
		            	return ['status'=>'fail','msg'=>$msg,"min_order_qty"=>$product_det->min_order_qty,'cartquantity'=>$cartresult->quantity,'ordAmount'=>number_format($orderFinalPrice,2),'totQty'=>$totQty,'tot_prd_price'=>number_format($total_price,2),'product_price'=>number_format($product_price,2),"cartid_"=>$cartresult->id];
		        	

				}else{
				// stock = 1 ไม่มีการตัด stock
					if($newQuantity < $product_det->min_order_qty){
						$product_price = GeneralFunctions::getProductPriceById($product_det->id,$newQuantity,$product_det);
						$total_price = $product_price * $product_det->min_order_qty;
						// update เฉพาะ Cart
						$affected = Cart::where(['id' => $cartId])->update(['quantity'=>$product_det->min_order_qty,'original_price'=>$product_price,'cart_price' => $product_price,'total_price'=>$total_price]);
						$orderFinalPrice = OrdersTemp::updateOrderPrice($cartresult->order_id);
						$totQty = Cart::where('order_id',$cartresult->order_id)->sum('quantity');
						
							
						$msg = Lang::get('checkout.product_minimum_quantity_should_be').' '.$product_det->min_order_qty." ".$resultpackagename->package_name;
						return ['status'=>'fail','msg'=>$msg,"min_order_qty"=>$product_det->min_order_qty,'cartquantity'=>$cartresult->quantity,'ordAmount'=>number_format($orderFinalPrice,2),'totQty'=>$totQty,'tot_prd_price'=>number_format($total_price,2),'product_price'=>number_format($product_price,2),"cartid_"=>$cartresult->id];
		        	}
				}
		    }

			// stock = 0 สินค้าไจำกัดจำนวน
			if($product_det->stock == '0' ){

				// แก้ไข stock สินค้า
				if($newQuantity <= ($product_det->quantity + $cartresult->quantity)){

					// คิด stock ใหม่
					$calnewqty =   $cartresult->quantity - $newQuantity;
					$calnewqty2 = $product_det->quantity + $calnewqty;

					$product_price = GeneralFunctions::getProductPriceById($product_det->id,$newQuantity,$product_det);
					$total_price = $product_price * $newQuantity;

					if(validOrdAmt($total_price)== false){
						return ['status'=>'fail','msg'=>Lang::get('checkout.order_amount_exceeded')];
					}

					// update smm_product เพิ่ม quantity ใน stock 
					Product::where("id","=",$product_det->id)->update(["quantity"=>$calnewqty2]);
					// update mongodb
        			\App\MongoProduct::where("_id","=",$product_det->id)->update(["quantity"=>$calnewqty2]);
					
					$affected = Cart::where(['id' => $cartId])->update(['quantity'=>$newQuantity,'original_price'=>$product_price,'cart_price' => $product_price,'total_price'=>$total_price]);
					$orderFinalPrice = OrdersTemp::updateOrderPrice($cartresult->order_id);
					$totQty = Cart::where('order_id',$cartresult->order_id)->sum('quantity');
					return array('status'=>'success','ordAmount'=>number_format($orderFinalPrice,2),'totQty'=>$totQty,'tot_prd_price'=>number_format($total_price,2),'product_price'=>number_format($product_price,2),"cartid_"=>$cartresult->id,"min_order_qty"=>$product_det->min_order_qty);

				}
				else{
					return ['status'=>'fail','msg'=>Lang::get('checkout.quantity_not_available'),'qty'=>$old_qty];
				}
			// stock = 1 สินค้าไม่จำกัดจำนวน
			}else{

				$product_price = GeneralFunctions::getProductPriceById($product_det->id,$newQuantity,$product_det);
				$total_price = $product_price * $newQuantity;

				if(validOrdAmt($total_price)== false){
		            return ['status'=>'fail','msg'=>Lang::get('checkout.order_amount_exceeded')];
		        }
				
				/****updating cart with quantity******/
				$affected = Cart::where(['id' => $cartId])->update(['quantity'=>$newQuantity,'original_price'=>$product_price,'cart_price' => $product_price,'total_price'=>$total_price]);
				$orderFinalPrice = OrdersTemp::updateOrderPrice($cartresult->order_id);
				$totQty = Cart::where('order_id',$cartresult->order_id)->sum('quantity');
				return array('status'=>'success','ordAmount'=>number_format($orderFinalPrice,2),'totQty'=>$totQty,'tot_prd_price'=>number_format($total_price,2),'product_price'=>number_format($product_price,2),"cartid_"=>$cartresult->id,"min_order_qty"=>$product_det->min_order_qty,'cartquantity'=>$cartresult->quantity);
			}
		}else{
			return ['status'=>'fail','msg'=>'invalid cart'];
		}
	}

 	/****Remove product from cart********/
	function removeCart(Request $request){
		$cartId = $request->cartId;
		$userid = Auth::User()->id;

		// อ๊อฟปรับแก้ เพิ่มดึงฟิวด์ product_id,quantity 
		$cartresult = Cart::select('order_id','product_id','quantity')->where(['id'=>$cartId,'user_id'=>$userid])->first();
		
		if(!empty($cartresult)){
			$orderId = $cartresult->order_id;
			
			// อ๊อฟ
			// ถ้ามีการลบสินค้าในตะกร้า แล้วคืน stock 
			// Start
				$quantityproduct = DB::table("product")->where("id","=",$cartresult->product_id)->select('id','stock','quantity')->first();
					
				if($quantityproduct->stock  == '0' ){	
					$calquantityproduct =  $quantityproduct->quantity + $cartresult->quantity;
					DB::table("product")->where("id","=",$quantityproduct->id)->update(["quantity"=>$calquantityproduct]);

					// update mongodb
        			\App\MongoProduct::where("_id","=",$quantityproduct->id)->update(["quantity"=>$calquantityproduct]);
				}
			// End

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
	
		$input = $request->all();
		$validate = $this->validateCart($input);
		if ($validate->fails()) {
			$errors =  $validate->errors();
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
			$time_slot = $delivery_detail->time_slot;

			if(strrpos($pickup_time,'nd')!==false){
				$pk_time = str_replace('nd_n', '', $pickup_time);
				$same_next_day = $pk_time - $delivery_detail->delivery_time_after;
			}
			else{
				$same_next_day = $ptime - $delivery_detail->delivery_time_after;
			}
			if($same_next_day<0){
				$selected_pk_time= 24 + $same_next_day;
			}else{
				$selected_pk_time = $ptime - $delivery_detail->delivery_time_after;
			}
			
			$cur_hour = date('H');

			if($time_slot){
				$exp_slot = explode(',',$time_slot);
				if(!in_array($ptime, $exp_slot) ){
					return ['status'=>'fail','type'=>'pickup_time','msg'=>'รอบการจัดส่งสินค้าที่คุณเลือกไว้หมดเวลาแล้ว กรุณาเลือกรอบการจัดส่งใหม่ 2 '.$pk_time];
				}
			}
			
			if(strrpos($pickup_time,'nd')===false){

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
					return ['status'=>'fail','type'=>'pickup_time','msg'=>'รอบการจัดส่งสินค้าที่เลือกไว้หมดเวลาแล้ว กรุณาเลือกรอบการจัดส่งสินค้าใหม่อีกครั้ง 3'];
				}
			}else{
				$tomorrow = date("Y-m-d", strtotime(" +2 day"));
				$pdate = $tomorrow.' '.$pk_time.':00:00';
				$pickup_datetime = date('Y-m-d H:i:s',strtotime($pdate));
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
				$errorMsg = "ไม่สามารถดำเนินการต่อได้ เนื่องจากข้อมูลที่อยู่สำหรับจัดส่ง (Shipping Address) หรือที่อยู่สำหรับออกใบเสร็จ (Billing Address) ไม่ถูกต้อง กรุณาตรวจสอบและกรอกข้อมูลให้ครบถ้วนอีกครั้ง";
				return ['status'=>'fail','type'=>'address','msg'=>$errorMsg];
			}
		}else{
			$shipping_address_id = $billing_address_id = 0;
			$user_phone_no = $request->phone_no;
			$billing_address_id = $request->bill_address;
			$billing_address = \App\ShippingAddress::where(['user_id'=>$userid,'id'=>$billing_address_id])->count();
		}

		try {
			DB::beginTransaction();
			
			$orderTmpPrice = OrdersTemp::updateOrderPrice($orderId);
			$dcc_discount_purchase = 0;
			$dcc_discount_shipping = 0;
			
			
			$total_purchase = $orderTmpPrice ;
			$total_shipping_cost = $shipping_fee; // ใช้ค่าจัดส่งจาก frontend โดยตรง (ไม่บวกส่วนลด)
			$total_logistic_cost = $logistic_fee;

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
			
			// ใช้ transaction fee จาก frontend ที่คำนวณแล้ว (ปัดเศษเป็นทศนิยม 2 ตำแหน่ง)
			$transactionFee = round(floatval($request->transaction_fee ?? 0), 2);
			$total_final_price = round($total_final_price + $transactionFee, 2);
			// $transactionFeeName = $pay_det->transactionFeeConfig->name;
			
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
				if(strpos($pay_det->slug, 'beam') === 0){
					$payment_type = 'online';
					$payment_slug = $pay_det->slug; // เก็บ slug แบบเต็ม เช่น beam-qr, beam-credit
				}
			}
			
			$orderUpdate = OrdersTemp::where(['id' => $orderId])->update([
				'user_id'=>$userid,
				'payment_type'=>$payment_type,
				'payment_slug'=>$payment_slug,
				'shipping_address_id'=>$shipping_address_id,
				'billing_address_id'=>$billing_address_id,
				'shipping_method'=>$shipping_method,
				'total_final_price'=>$total_final_price,
				'total_shipping_cost'=>$total_shipping_cost,
				'total_logistic_cost'=>$total_logistic_cost,
				'pickup_time'=>$pickup_datetime,
				'user_phone_no'=>$user_phone_no,
				'checkout_type'=>$request->checkout_type,
				'total_discount'=>$total_discount,
        		'dcc_purchase_discount'=> $dcc_discount_purchase,
        		'dcc_shipping_discount'=> $dcc_discount_shipping,
        		'transaction_fee'=>$transactionFee ?? 0 // บันทึก transaction_fee จาก frontend
			]);
 
			$update_cart = Cart::where(['order_id'=>$orderInfo->id])->update(['cart_status'=>2]);
 
			$order_created_id = OrderController::saveFinalOrder($orderId);
			
			if(!$order_created_id){
				// return ['status'=>'fail','type'=>'payment','msg'=>Lang::get('checkout.something_went_wrong')];
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
			// try{

				if($total_final_price <= 0 || $request->checkout_type =='end-shopping'){
					$formattedOrderId = OrderController::saveOrderEndShopping($orderId,$main_order);
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

			// }catch(Exception $e){
			// 	//dd($e);
			// 	return ['status'=>'fail','msg'=>$e->getMessage()];
			// }
			
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

		DB::beginTransaction();
		try {
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

			DB::commit();
			return ['status'=>'success',
				'msg'=>Lang::get('checkout.payment_method_updated_successfully'),
				'data'=>[
					'transactionFeeLabel' => $paymentMethod->transactionFeeConfig->name??null,
					'transactionFeeRate' => $transactionFeeRate,
					'totalBeforeFee' => $totalBeforeFee,
					'totalFinalPrice' => $totalBeforeFee + $transactionFee,
					'transactionFee' => $transactionFee
				]
			];
		} catch (Exception $e) {
			DB::rollBack();
			return ['status'=>'fail','msg'=>'เกิดข้อผิดพลาด'];
		}
	}

	public function submitPayment(Request $request){
        $userid = Auth::User()->id;
        $formatted_id = $request->formatted_id;
        $orderInfo = Order::where(['formatted_id'=>$formatted_id,'user_id'=>$userid,'payment_status'=>0])->where('order_status',1)->first();

        if(empty($orderInfo)){
            return ['status'=>'fail','msg'=>Lang::get('checkout.invalid_order')];
        }

        /****calculating pickup time*******/
        $pickup_datetime = null;
        if(isset($request->pickup_time)){
        	$delivery_type = \App\DeliveryTime::getDeliverYType($orderInfo->shipping_method);
			$delivery_detail = \App\DeliveryTime::getDeliveryTime($delivery_type);
            $pickup_time = $request->pickup_time;
            $nextday = !empty($request->nexday)?$request->nexday:'';
            $ptime = str_replace('_n', '', $pickup_time);
			$time_slot = $delivery_detail->time_slot;
			if($time_slot){
				$exp_slot = explode(',',$time_slot);
				if(!in_array($ptime, $exp_slot)){
					return ['status'=>'fail','type'=>'pickup_time','msg'=>'รอบการจัดส่งสินค้าที่คุณเลือกไว้หมดเวลาแล้ว กรุณาเลือกรอบการจัดส่งใหม่ :-('];
				}
			}
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
            $new_time = date("Y-m-d H:i:s", strtotime('+3 hours'));

            if(strtotime($new_time) > strtotime($pickup_datetime)){
            	return ['status'=>'fail','type'=>'pickup_time','msg'=>'รอบการจัดส่งสินค้าที่เลือกไว้หมดเวลาแล้ว กรุณาเลือกรอบการจัดส่งสินค้าใหม่อีกครั้ง'.$new_time.' '.$pickup_datetime];
            }
        }
        $orderInfo->pickup_time = $pickup_datetime;
        $orderInfo->save();
        
		$payment_slug = $orderInfo->payment_slug;

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
        if(strpos($payment_slug, 'beam') === 0){
            $url = action('Checkout\CartController@beamPayment',$orderInfo->formatted_id);
            return ['status'=>'success','url'=>$url];
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

		if (!$validate->fails()) {

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
		$checkout_type = explode('?', $checkout_type)[0];
		$orderDetails = $paid_product = [];
		$userid = Auth::User()->id;
		$orderInfo = OrdersTemp::where(['user_id'=>$userid,'order_status'=>'0'])->first();
		if(!$orderInfo){
			return json_encode(array('status'=>'fail','msg'=>'order not found'));
		}
		
		if($checkout_type == 'buy-now' || $checkout_type == 'buy-now-end-shopping' || $checkout_type == 'buy-now-end-shopping-test'){
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
		
		$discountPurchase = 0;
		$discountShipping = 0;
		$discountCodeName = "";
		$shipping_fee=0;
		$total_shipping=0;
		$tot_amt_before_dc = 0;

		$str = "";
		$discount_fee = 0;

		if(!empty($request->shipId)){

			$shipAddress = ShippingAddress::where(['user_id'=>$userid,'id'=>$request->shipId])->first();
			$shippingRes = $this->getShippingFee($shipAddress,$orderDetails,$paid_product);
			$shipping_fee = $shippingRes['total_deliver_fee'];
			$discount_fee = $shippingRes['total_logistic_fee'];

			$total_shipping = $shipping_fee;

			if(!empty($shipAddress)){
				$str = "<table>
						<tr>
							<td class='d-flex pt-2'><ion-icon name='location-outline' class='mr-2'></ion-icon></td>
							<td><p>$shipAddress->first_name  $shipAddress->last_name</p>
							<p> $shipAddress->address  $shipAddress->road </p>
							<p> $shipAddress->city_district  $shipAddress->province_state $shipAddress->zip_code </p>
							<p></td>
						</tr>
						<tr>
							<td><ion-icon name='call-outline' class='mr-2'></ion-icon></td><td> $shipAddress->ph_number </p></td>
						</tr>
					</table>";
			}

		}
		
		$tot_amt_before_dc = $total_amount+$total_shipping;


        // -- mod start
		if(!empty($request->discountCode)){
			$newRequest = new Request([
				'code' => $request->discountCode,
				'purchase' => $total_amount,
				'shippingCost' => $shipping_fee
			]);
		
			$discountController = new DiscountCodeController();
			$rs = $discountController->calulateDiscount($newRequest);
			$rsData = $rs->getData();
			
			$discountCodeName = $rsData->data->discountCodeName;
			if($rsData && $rsData->status ==='success'){
				$discountPurchase = (float) $rsData->data->discountPurchase;
				$discountShipping = (float) $rsData->data->discountShipping;

				if( $total_amount - $discountPurchase <= 0){
					$discountPurchase = $total_amount;
					$total_amount = 0;
				}else{
					$total_amount -= $discountPurchase;
				}
				
				if($shipping_fee > 0 ){
					if($total_shipping - $discountShipping <= 0) {
						$discountShipping = $total_shipping;
						$total_shipping = 0;
					}else{
						$total_shipping -= $discountShipping;
					}
				}else{
					$discountShipping = 0;
				}

			}

		}
		// -- mod end
		$total_amount += $total_shipping;

		//transaction fee calculate
		$transactionFee = 0;
		$transactionFeeRate = 0;
		$transactionFeeName = '';

		if(!empty($request->paymentOptionId)){
			$paymentOption = PaymentOption::where('id',$request->paymentOptionId)->first();
			if(!empty($paymentOption)){
				// Only calculate transaction fees for Beam payment methods
				if(strpos($paymentOption->slug, 'beam') === 0 && !empty($paymentOption->transactionFeeConfig)){
					$transactionFeeRate = (float)$paymentOption->transactionFeeConfig->current_tf ?? 0;
					// ใช้ฟังก์ชัน helper สำหรับคำนวณ transaction fee
					$transactionFee = $this->calculateTransactionFee($total_amount, $transactionFeeRate);
					$total_amount = round($total_amount + $transactionFee, 2);
					$transactionFeeName = $paymentOption->transactionFeeConfig->name;
				} else {
					// For non-Beam methods, set transaction fee to 0
					$transactionFeeRate = 0;
					$transactionFee = 0;
					$transactionFeeName = '';
				}
			}
		}

		$transactionFee = number_format($transactionFee,2);
		$final_ship_fee = number_format($total_shipping,2);
		$final_discount_fee = number_format($discount_fee,2);

		// คำนวณยอดรวมสุดท้ายรวม transaction fee สำหรับแสดงผล
		$total_amount_with_fee = round($total_amount, 2);
		
		return json_encode(array(
			'status'=>'success',
			'shipVal'=>$str,
			'shipping_fee'=>$shipping_fee,
			'shipping_fee_txt'=>number_format($shipping_fee,2),
			'final_ship_fee'=>$final_ship_fee,
			'discount_fee'=>$final_discount_fee,
			'total_amount'=>number_format($total_amount_with_fee,2),
			'totAmt'=>$total_amount_with_fee,
			
			'discount_code_purchase'=> $discountPurchase,
			'discount_code_shipping'=> $discountShipping,
			'dcc_total_discount'=>$discountPurchase+$discountShipping,
			'discount_code'=> $request->discountCode,
			'discount_code_name'=> $discountCodeName,
			'tot_amt_before_dc'=>$tot_amt_before_dc,
			'tot_amt_after_dc'=>$tot_amt_before_dc - ($discountPurchase+$discountShipping),
			
			'discount_code_purchase_txt'=> number_format($discountPurchase,2),
			'discount_code_shipping_txt'=> number_format($discountShipping,2),

			'transaction_fee_rate'=>$transactionFeeRate,
			'transaction_fee'=>$transactionFee,
			'transaction_fee_name'=>$transactionFeeName,
			'transaction_fee_txt'=>number_format($transactionFee,2),
		));

	}

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
        		'merchantReferenceId' => 'Order_' . $orderInfo->id,
        		'netAmount' => (int)round($total_with_transaction_fee * 100),
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
			if ($signature) {
				$isValidSignature = $this->validateBeamWebhookSignature($request, $signature);
				if (!$isValidSignature) {
					Log::warning('Beam Webhook: Invalid signature', [
						'signature' => $signature,
						'body_hash' => hash('sha256', $request->getContent())
					]);
					// Return non-2xx to trigger Beam retry
					return response()->json(['error' => 'Invalid signature'], 401);
				}
				Log::info('Beam Webhook: Signature validated successfully');
			}

			// รับข้อมูลจาก webhook - Beam ส่งมาในรูปแบบ JSON
			$payload = json_decode($request->getContent(), true);
			
			// ถ้า JSON decode ล้มเหลว ให้ลองใช้ request parameters
			if (!$payload) {
				$payload = $request->all();
			}

			if (json_last_error() !== JSON_ERROR_NONE) {
				Log::error('Beam Webhook: JSON decode error', [
					'error' => json_last_error_msg(),
					'raw_content' => $request->getContent()
				]);
				// Return non-2xx to trigger Beam retry
				return response()->json(['error' => 'Invalid JSON payload'], 400);
			}

			Log::info('Beam Webhook Payload Parsed', [
				'payload' => $payload,
				'x_beam_event' => $beamEvent ?? null
			]);
			
			// แยกการประมวลผลตามรูปแบบอีเวนต์ใหม่ของ Beam
			$processed = false;
			$order = null;
			$status = null;
			$amount = null;
			$ackMeta = [];

			if (($beamEvent && strtolower($beamEvent) === 'payment_link.paid') || isset($payload['paymentLinkId'])) {
				// กรณี payment_link.paid ตามเอกสาร
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
			Log::error('=== BEAM WEBHOOK ERROR ===');
			Log::error('Beam Webhook Processing Error', [
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString(),
				'raw_content' => $request->getContent(),
				'payload' => $request->all()
			]);

			// Return non-2xx to trigger Beam retry on internal errors
			return response()->json([
				'acknowledged' => false,
				'error' => 'Internal processing error',
				'message' => 'Webhook processing failed, will retry'
			], 500);
		}
	}

	// ตรวจสอบ webhook signature (HMAC SHA256) ตาม Beam standards
	private function validateBeamWebhookSignature(Request $request, $signature) {
		try {
			// ดึง webhook secret จาก payment option ใดๆ ที่เป็น Beam
			$pay_opt = \App\PaymentOption::where('slug', 'LIKE', 'beam%')->first();
			if (!$pay_opt) {
				Log::warning('No Beam payment option found for signature validation');
				return true; // ถ้าไม่มี config ให้ผ่าน
			}

			$beam_details = $pay_opt->mode == 2 ? 
				json_decode($pay_opt->sandbox_detail, true) : 
				json_decode($pay_opt->live_detail, true);

			$webhook_secret = $beam_details['webhook_secret'] ?? null;
			
			if (!$webhook_secret) {
				Log::info('No webhook secret configured, skipping signature validation');
				return true; // ถ้าไม่มี secret ให้ผ่าน
			}

			// คำนวณ signature ตาม Beam specification
			// Beam: X-Beam-Signature = base64(HMAC_SHA256(raw_body, base64Decode(secret)))
			$payload = $request->getContent();
			$decoded_secret = base64_decode($webhook_secret);
			$raw_hmac = hash_hmac('sha256', $payload, $decoded_secret, true);
			$expected_base64 = base64_encode($raw_hmac);
			// เผื่อรองรับรูปแบบเก่า 'sha256='+hex(hmac)
			$expected_hex = 'sha256=' . hash_hmac('sha256', $payload, $decoded_secret);

			$is_valid = hash_equals($expected_base64, $signature) || hash_equals($expected_hex, $signature);

			return $is_valid;

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
				\DB::table('smm_order_payment')->insert($callbackData);
				Log::info('Beam Callback Saved', [
					'order_id' => $order->id,
					'purchaseId' => $payload['purchaseId'] ?? '',
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
					'purchase_id' => $payload['purchaseId'] ?? 'unknown'
				]);
			} catch (Exception $e) {
				Log::error('Failed to create OrderPayment record', [
					'order_id' => $order->id,
					'error' => $e->getMessage(),
					'payment_data' => $payment_data
				]);
				// ไม่ throw error เพราะการอัพเดต order สำเร็จแล้ว
			}

			// อัพเดต order shops ให้เป็นสถานะรอการจัดส่ง
			\App\OrderShop::where('order_id', $order->id)->update([
				'order_status' => 2
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

			$response = curl_exec($curl);
			$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$err = curl_error($curl);
			curl_close($curl);

			if ($err) {
				Log::error('Beam API CURL Error', [
					'error' => $err,
					'order_id' => $order->id,
					'purchase_id' => $purchaseId
				]);
				return response()->json(['error' => 'Failed to connect to Beam API'], 500);
			}

			if ($http_code !== 200) {
				Log::error('Beam API HTTP Error', [
					'http_code' => $http_code,
					'response' => $response,
					'order_id' => $order->id
				]);
				return response()->json(['error' => 'Beam API returned error: ' . $http_code], 400);
			}

			$result = json_decode($response, true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				Log::error('Beam API JSON Decode Error', [
					'error' => json_last_error_msg(),
					'raw_response' => $response
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
							'purchase_id' => $purchaseId
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
					$this->handleBeamPaymentSuccess($order, $beamData);
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
}