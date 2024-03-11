<?php 

namespace App\Http\Controllers\Admin\Transaction;

use App\Http\Controllers\MarketPlace;
use App\Helpers\GeneralFunctions;
use App\Helpers\CustomHelpers;
use App\Helpers\EmailHelpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use Auth;
use App\Order;
use App\OrderDetail;
use App\OrderShop;
use App\User;
use App\Product;
use Lang;
use Config;
use Excel;
use PDF;

class OrderController extends MarketPlace
{
    public function __construct(){
       $this->middleware('admin.user');
    }
    
    public function index(){

        $permission = $this->checkUrlPermission('main_order');
        if($permission === true) {
           $filter = $this->getFilter('main_order');
           $order_status = \App\OrderStatusDesc::where('lang_id',session('default_lang'))->select('order_status_id','status')->get();
            $status_arr = [];
            foreach ($order_status as $key => $value) {
                $status_arr[] = [$value->order_status_id=>$value->status];
            }

 
            $shipping_method = [['1'=>Lang::get('checkout.pick_up_at_center')], ['2'=>Lang::get('checkout.pick_up_at_the_store')], ['3'=>Lang::get('checkout.delivery_at_the_address')]];


            return view('admin.transaction.listOrder', ['filter'=>$filter,'ord_status'=>json_encode($status_arr), 'shipping_method'=>json_encode($shipping_method)]);
        }      
    }

    public function listOrderData(Request $request){
        $perpage = !empty($request->pq_rpp) ? $request->pq_rpp : getPagination('limit');
        $request->page = $current_page = !empty($request->pq_curpage)?$request->pq_curpage:0;

        $start_index = ($current_page - 1) * $perpage;
        //dd($perpage,$request->page);
        
        $order_by = 'id';
        $order_by_val = 'desc';
        if(isset($request->pq_sort)){
            $sort_data = jsonDecodeArr($request->pq_sort);
            $order_by = $sort_data[0]['dataIndx'];
            $order_by_val = ($sort_data[0]['dir']=='up')?'asc':'desc';

            if($order_by=='end_shopping_date_time'){
                $order_by = 'end_shopping_date';
            }
        }

        try{
            
            //Order::select('*');
            $prefix = DB::getTablePrefix();
            $query = DB::table(with(new Order)->getTable().' as o')->select("o.*", DB::raw("(SELECT sum(total_weight*quantity) FROM ".$prefix.with(new OrderDetail)->getTable()." WHERE order_id = ".$prefix."o.id) as total_weight"));

            //OrderDetail::select(DB::raw('sum(total_weight*quantity) as sum_total_weight') )->where('order_id', $value->id)->value('sum_total_weight');

            
            if(isset($request->pq_filter)){
                $filter_req = json_decode($request->pq_filter,true);
                if(!empty($filter_req['data'])){
                    $filter_arr = $filter_req['data'];
                    foreach ($filter_arr as $fkey => $fvalue) {
                        $searchval = $fvalue['value'];
                        switch ($fvalue['dataIndx']) {
                            case 'formatted_id':$query->where('formatted_id','like', '%'.$searchval.'%'); break;
                            case 'user_name':$query->where('user_name','like', '%'.$searchval.'%'); break;
                            case 'admin_remark':$query->where('admin_remark','like', '%'.$searchval.'%'); break;
                            case 'total_final_price':$query->where('total_final_price','=',$searchval); break;
                            case 'order_status':
                                $query->whereIn('order_status', $searchval);
                                break;
                            case 'time':
                               
                                $query->where(function ($query) use ($searchval) {
                                    $count= 0;
                                    foreach ($searchval as $searchdata) {
                                        $count++;
                                        if($count==1){
                                            $query = $query->where('pickup_time','like', '%'.$searchdata.'%');
                                        }else{
                                            $query = $query->orwhere('pickup_time','like', '%'.$searchdata.'%');

                                        }    
                                    }
									
								});
                                break;
                            case 'dob':
                                $from_date = $fvalue['value']??'';
                                $to_date = $fvalue['value2']??'';
                                createDateFilter($query,'u.dob',$from_date,$to_date);
                            break;
                            case 'end_shopping_date_time':
                                $from_date = $fvalue['value']??'';
                                $to_date = $fvalue['value2']??'';
                                createDateFilter($query,'end_shopping_date',$from_date,$to_date);
                            break;
                            case 'end_shopping_date':
                                $from_date = $fvalue['value']??'';
                                $to_date = $fvalue['value2']??'';
                                createDateFilter($query,'end_shopping_date',$from_date,$to_date);
                            break;
                            case 'pickup_time':
                                $from_date = $fvalue['value']??'';
                                $to_date = $fvalue['value2']??'';
                                createDateFilter($query,'pickup_time',$from_date,$to_date);
                            break;
                            case 'shipping_method':
                                $query->whereIn('shipping_method', $searchval);
                            break;
                            case 'total_weight':
                               $query->where(DB::raw("(SELECT sum(total_weight*quantity) FROM ".$prefix.with(new OrderDetail)->getTable()." WHERE order_id = ".$prefix."o.id)"),'=',$searchval); 
                            break;
                            
                        }
                        
                    }
                }
            }

            //dd( $query->toSql());
            $response = $query->orderBy($order_by,$order_by_val)->paginate($perpage,['*'],'page',$current_page);
            $totrec = $response->total();
            //dd($response);
            if($start_index >= $totrec) {
                $current_page = ceil($totrec/$perpage);
                
                $response = $query->orderBy($order_by,$order_by_val)->paginate($perpage,['*'],'page',$current_page);
            }

            foreach ($response as $key => $value) {

                $response[$key]->end_shopping_date_time = $value->end_shopping_date;
                $response[$key]->end_shopping_date = $value->end_shopping_date?date('Y-m-d',strtotime($value->end_shopping_date)):null;
                $response[$key]->pickup_time = $value->pickup_time?date('Y-m-d H:i:s',strtotime($value->pickup_time)):null;
                $response[$key]->time = $value->pickup_time?date('H:i:s',strtotime($value->pickup_time)):null;
                
                $response[$key]->total_final_price = numberFormat($value->total_final_price);
                
                $response[$key]->shipping_method = GeneralFunctions::getShippingMethod($value->shipping_method);

                $response[$key]->detail_url = action('Admin\Transaction\OrderController@orderDetail',$value->formatted_id);
                $response[$key]->payment_status = ($value->payment_status == 1)?'c-tot':'';
                //$to_weight = OrderDetail::select(DB::raw('sum(total_weight*quantity) as sum_total_weight') )->where('order_id', $value->id)->value('sum_total_weight');
                //dd($to_weight);
                //$response[$key]->total_weight = $to_weight;


            }

            /***save filter****/
            $this->setFilter('main_order',$request); 
            
        }catch(QueryException $e){
            $response = ['status'=>'fail','msg'=>$e->getMessage()];
        }
        

        return $response;
    }

    public function orderDetail(Request $request) {

        $formatted_id = $request->oid; 
        $main_order = Order::where('formatted_id',$formatted_id)->with(['getUser','getOrderStatus'])->first();
        // dd($main_order,$formatted_id);
        if(empty($main_order)){
          abort(404);
        }

        $order_shop = OrderShop::where('order_id',$main_order->id)->with(['getOrderStatus'])->get();
        if(count($order_shop)){
            foreach ($order_shop as $key => $value) {
                $order_detail = OrderDetail::getShopOrderDetail('',$value->id);
                $order_shop[$key]->details = $order_detail;
            }
        }
        $main_order->tot_shop = count($order_shop);
        
        //dd($order_shop);
        $transaction = \App\OrderTransaction::where('order_id',$main_order->id)->orderBy('id')->get();
		
		$main_order->pickup_time = null;
		if($main_order->id>0)
		{
			$order_info = Order::where('id',$main_order->id)->first();
			if($order_info)
			{
				$main_order->pickup_time=$order_info->pickup_time;
			}
		}
		
		/* Start:: If Product Detail Not Available in Order Details */
		if(count($order_shop))
		{
			foreach($order_shop as $skey => $shop_ord_val)
				{
					foreach($shop_ord_val->details as $key => $val)
					{
						if($val->description=='' || $val->description==null)
						{
							$productDetail = \App\Product::getProductDetail($val->sku);
							$order_shop[$skey]->details[$key]->description=isset($productDetail->productDesc)?$productDetail->productDesc->description:"";
						}
					}
				}
		}
		/* Start:: If Product Detail Not Available in Order Details */
		
        return view('admin.transaction.mainOrddetail',['main_order' => $main_order,'order_shop'=>$order_shop,'transaction'=>$transaction]);
    }
    /*********** for check create order json ************/
    public function orderJson($order_id) {
      
        $main_order = Order::where('id',35)->first();

        if(empty($main_order)){
          abort(404);
        }

        $order_shop = OrderShop::where('order_id',$main_order->id)->get()->toArray();
        if(count($order_shop)){
            foreach ($order_shop as $key => $value) {

                unset($order_shop[$key]['shipping_method'],$order_shop[$key]['total_discount'],$order_shop[$key]['total_final_weight'],$order_shop[$key]['seller_status'],$order_shop[$key]['shop_json'],$order_shop[$key]['order_json']);

                $order_detail = OrderDetail::where(['order_shop_id'=>$value['id']])->get()->toArray();

                $line= 0;
                foreach ($order_detail as $dkey => $dvalue) {
                    
                    $detail_arr = json_decode($dvalue['order_detail_json'],true);

                    $detail_arr['name'] = $detail_arr['name'][0]??'';
                    $detail_arr['package'] = $detail_arr['package'][0]??'';
                    $detail_arr['shop_name'] = $detail_arr['shop_name'][0]??'';
                    $detail_arr['payment_method'] = $detail_arr['payment_method'][0]??'';

                    $order_detail[$dkey]['item_detail_json'] = $detail_arr;

                    unset($order_detail[$dkey]['order_detail_json'],$order_detail[$dkey]['user_id'],$order_detail[$dkey]['shop_id'],$order_detail[$dkey]['order_id'],$order_detail[$dkey]['created_at'],$order_detail[$dkey]['updated_at']);

                    $arr = [];
                    $arr = ['line_no'=>++$line]+$order_detail[$dkey];
                    $order_detail[$dkey] = $arr;
                    
                }
                
                $order_shop[$key]['order_detail'] = $order_detail;
            }
        }

        if($main_order->shipping_method == 2){
            $main_ord_json = json_decode($main_order->order_json,true);
            $ord_json_arr = [];
            if(count($main_ord_json)){
                foreach ($main_ord_json as $key => $value) {
                    $value['shop_name'] = $value['shop_name'][0]??'';
                    $ord_json_arr[] = $value;
                }
            }
            $main_order->order_json = $ord_json_arr;
        }else{
            $json_arr = json_decode($main_order->order_json,true);
            $key_arr = ['first_name','last_name','provice','district','address','road','zip_code','ph_number','company_name','branch','tax_id','company_address','name','location','contact','estimate'];
            $arr_json = [];
            if($main_order->shipping_method == 3){
                foreach ($key_arr as $key => $value) {
                    $arr_json[$value] = $json_arr['shipping_address'][$value]??'';
                }
            }else{
                foreach ($key_arr as $key => $value) {
                    $arr_json[$value] = $json_arr[$value]??'';
                }
            }
            
            $main_order->order_json = $arr_json;
        }
        
        $shop_order_arr = $order_shop;

        $main_order->api_date = date('Y-m-d H:i:s');
        $main_order->order_shop_json = $shop_order_arr;
        $making_json = $main_order->toArray();
        //$a = array_map('strval', $making_json);
        /**********/
        //unset($making_json['total_discount']);
        
        dd($making_json,json_encode($making_json),json_encode($making_json, JSON_FORCE_OBJECT));
        return view('admin.transaction.mainOrddetail',['main_order' => $main_order,'order_shop'=>$order_shop]);
    }/*********/

    public function ordChangeItemStatus(Request $request){
        $order_detail_id = $request->order_detail_id??0;
        $type = $request->type??'';
        $order_detail = OrderDetail::where('id',$order_detail_id)->first();

        if($order_detail_id && $order_detail && $type){

            if($type == 'cancel'){
                $status = 4;
                $comment = GeneralFunctions::getOrderText('item_cancel',$order_detail->category_name);
                $msg = Lang::get('admin_order.item_cancelled');
            }elseif($type == 'receive'){
                $status = 5;
                $comment = GeneralFunctions::getOrderText('item_center_receive',$order_detail->category_name);
                $msg = Lang::get('admin_order.item_center_received');
            }
            $order_detail->status = $status;
            $order_detail->save();

            /****update entry in order transaction******/
            $transaction_arr = ['order_id'=>$order_detail->order_id,'order_shop_id'=>$order_detail->order_shop_id,'order_detail_id'=>$order_detail->id,'event'=>'delivery','comment'=>$comment,'updated_by'=>'admin'];

            $update_transaction = \App\OrderTransaction::updateOrdTrans($transaction_arr);

            /*****function change order status*******/
            $change_status = \App\Order::updateOrdStatus($order_detail->order_id);

            /**new status****/
            $item_status = \App\OrderStatusDesc::getStatusVal($status);

            $shop_status_id = \App\OrderShop::where('id',$order_detail->order_shop_id)->value('order_status');
            $shop_status = \App\OrderStatusDesc::getStatusVal($shop_status_id);

            return ['status'=>'success','msg'=>$msg,'item_status'=>$item_status,'shop_status'=>$shop_status];
        }else{
            return ['status'=>'fail','msg'=>'Invalid order id'];
        }

    }  
    /**resend order to logistic */
    public function resendLogistic(Request $request) {
        $order_id = $request->order_id;
        $order = Order::where('id',$request->order_id)->first();
        if($order) {
            $order->logistic_status = '0';
            $order->save();

            return ['status'=>'success','msg'=>'Success ! It will send after one min'];
        }
        return ['status'=>'fail','msg'=>\Lang::get('admin_common.something_went_wrong')];
    }     

    public function updateRemark(Request $request) {
        $order_id = $request->order_id;
        $order = Order::where('id',$request->order_id)->first();
        if($order) {
            $order->admin_remark = trim($request->remark);
            $order->admin_remark_by = Auth::guard('admin_user')->user()->nick_name;
            $order->save();

            return ['status'=>'success','msg'=>\Lang::get('admin_order.remark_updated_successfully')];
        }
        return ['status'=>'fail','msg'=>\Lang::get('admin_common.something_went_wrong')];
    }

    public function updateOrderStatus(Request $request){

        $order_id = $request->order_id;
        $order_status_id = $request->order_status_id;

        $orderInfo = Order::find($order_id);
        if($orderInfo && $orderInfo->order_status == '1' && $order_status_id == '2') {

            $updated_by = 'admin';
            Order::updateOrderAfterPayment($orderInfo, $updated_by);

            /*for notification*/
            EmailHelpers::sendOrderNotificationEmail($orderInfo->formatted_id);
            /*for notification*/

            /*send noti at mobile*/ 
            $title = 'New Order';
            $body = 'Order id '. $orderInfo->formatted_id;
            $post_arr = ['user_id'=>$orderInfo->user_id, 'title'=>$title,'body'=>$body, 'type_redirect'=>'payment_success', 'order_id'=>$orderInfo->id, 'formatted_order_id'=>$orderInfo->formatted_id];
            $url = Config::get('constants.mobile_notification_url');
            $responce = $this->handleCurlRequest($url,$post_arr);                      

            return ['status'=>'success','msg'=>\Lang::get('admin_order.order_status_updated_successfully')];
        }
        else{

            return ['status'=>'error','msg'=>\Lang::get('admin_order.invalid_order_id')];
        }
    }        
    
    public function create(){
    }
    
    function store(Request $request){
    }
    
    function edit($group_id){
    }
    
    function update(Request $request){
    }

    public function orderDetailExport(Request $request) {

        $formatted_id = $request->oid; 
        $main_order = Order::where('formatted_id',$formatted_id)->with(['getUser','getOrderStatus'])->first();
        // dd($main_order,$formatted_id);
        if(empty($main_order)){
          abort(404);
        }

        $order_shop = OrderShop::where('order_id',$main_order->id)->with(['getOrderStatus'])->get();
        if(count($order_shop)){
            foreach ($order_shop as $key => $value) {
                $order_detail = OrderDetail::getShopOrderDetail('',$value->id);
                $order_shop[$key]->details = $order_detail;
            }
        }
        $main_order->tot_shop = count($order_shop);
        
        //dd($order_shop);
        $transaction = \App\OrderTransaction::where('order_id',$main_order->id)->orderBy('id')->get();
		
		$main_order->pickup_time = null;
		if($main_order->id>0)
		{
			$order_info = Order::where('id',$main_order->id)->first();
			if($order_info)
			{
				$main_order->pickup_time=$order_info->pickup_time;
			}
		}
		
		/* Start:: If Product Detail Not Available in Order Details */
		if(count($order_shop))
		{
			foreach($order_shop as $skey => $shop_ord_val)
				{
					foreach($shop_ord_val->details as $key => $val)
					{
						if($val->description=='' || $val->description==null)
						{
							$productDetail = \App\Product::getProductDetail($val->sku);
							$order_shop[$skey]->details[$key]->description=isset($productDetail->productDesc)?$productDetail->productDesc->description:"";
						}
					}
				}
		}
		/* Start:: If Product Detail Not Available in Order Details */
		$pdf = PDF::loadView('admin.transaction.mainOrddetailExport', ['main_order' => $main_order,'order_shop'=>$order_shop,'transaction'=>$transaction]);

        return $pdf->download($main_order->formatted_id.'.pdf');
        //return view('admin.transaction.mainOrddetailExport',['main_order' => $main_order,'order_shop'=>$order_shop,'transaction'=>$transaction]);
    }
    public function generateOrderPdf(Request $request) {

        //$formatted_id = $request->order_list; 
        $formatted_id = json_decode($request->order_list,true); 
        $total_order = Order::whereIn('formatted_id',$formatted_id)->with(['getUser','getOrderStatus'])->get();
        // dd($main_order,$formatted_id);
        if(empty($total_order)){
          abort(404);
        }
        foreach ($total_order as $key => $main_order) {
            $order_shop = OrderShop::where('order_id',$main_order->id)->with(['getOrderStatus'])->get();
            if(count($order_shop)){
                foreach ($order_shop as $key => $value) {
                    $order_detail = OrderDetail::getShopOrderDetail('',$value->id);
                    $order_shop[$key]->details = $order_detail;
                }
            }
            $main_order->tot_shop = count($order_shop);
            
            //dd($order_shop);
            $transaction = \App\OrderTransaction::where('order_id',$main_order->id)->orderBy('id')->get();
            
            $main_order->pickup_time = null;
            if($main_order->id>0)
            {
                $order_info = Order::where('id',$main_order->id)->first();
                if($order_info)
                {
                    $main_order->pickup_time=$order_info->pickup_time;
                }
            }
            
            /* Start:: If Product Detail Not Available in Order Details */
            if(count($order_shop))
            {
                foreach($order_shop as $skey => $shop_ord_val)
                    {
                        foreach($shop_ord_val->details as $key => $val)
                        {
                            if($val->description=='' || $val->description==null)
                            {
                                $productDetail = \App\Product::getProductDetail($val->sku);
                                $order_shop[$skey]->details[$key]->description=isset($productDetail->productDesc)?$productDetail->productDesc->description:"";
                            }
                        }
                    }
            }
            /* Start:: If Product Detail Not Available in Order Details */
            $pdf = PDF::loadView('admin.transaction.mainOrderListlExport', ['main_order' => $main_order,'order_shop'=>$order_shop,'transaction'=>$transaction]);

            return $pdf->download($main_order->formatted_id.'.pdf');
            //return view('admin.transaction.mainOrddetailExport',['main_order' => $main_order,'order_shop'=>$order_shop,'transaction'=>$transaction]);
        }

        
    }
    
}
