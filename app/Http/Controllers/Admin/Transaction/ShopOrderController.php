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
use Lang;
use Config;
use Excel;
use PDF;
use Illuminate\Database\QueryException;
class ShopOrderController extends MarketPlace
{
    public function __construct(){
       $this->middleware('admin.user');
    }
    
    public function index(){

        $permission = $this->checkUrlPermission('shop_order');
        if($permission === true) {
            
            $filter = $this->getFilter('shop_order');
            $order_status = \App\OrderStatusDesc::where('lang_id',session('default_lang'))->select('order_status_id','status')->get();
            $status_arr = [];
            foreach ($order_status as $key => $value) {
                $status_arr[] = [$value->order_status_id=>$value->status];
            }

            return view('admin.transaction.listShopOrder', ['filter'=>$filter,'ord_status'=>json_encode($status_arr)]);
        }
    }

    public function viewSearchOrder(Request $request){

        $search    = $request->input('search_order');
        if($search){
            $order = Order::query()
            ->when($search,function($q) use ($search) {
                    $q->where('formatted_id',$search);
            })
            ->first();
            if($order){
                return redirect()->action('Admin\Transaction\OrderController@orderDetail',['oid'=>$order->formatted_id]);
            }
        }else{
            $order=[];
        }
        
        return view('admin.transaction.searchOrder',['order'=>$order]);
    }

    public function listOrderData(Request $request){
        // สร้าง request fingerprint เพื่อป้องกันการเรียกซ้ำ
        $request_fingerprint = md5(serialize([
            'pq_curpage' => $request->pq_curpage,
            'pq_rpp' => $request->pq_rpp,
            'pq_sort' => $request->pq_sort,
            'pq_filter' => $request->pq_filter,
            'user_id' => Auth::guard('admin_user')->id(),
            'timestamp' => floor(time() / 5) // 5 วินาที window
        ]));
        
        // ตรวจสอบว่า request นี้ถูกเรียกไปแล้วหรือไม่
        $session_key = 'last_request_' . $request_fingerprint;
        if (session()->has($session_key)) {
            $last_call_time = session($session_key);
            if (time() - $last_call_time < 5) { // 5 วินาที
                \Log::info('Duplicate request blocked', [
                    'fingerprint' => $request_fingerprint,
                    'time_diff' => time() - $last_call_time
                ]);
                return response()->json(['status' => 'duplicate_request'], 429);
            }
        }
        
        // บันทึกเวลาการเรียก request นี้
        session([$session_key => time()]);
        
        // เพิ่ม debug log
        \Log::info('listOrderData called', [
            'timestamp' => now(),
            'request_id' => uniqid(),
            'fingerprint' => $request_fingerprint,
            'pq_curpage' => $request->pq_curpage,
            'pq_rpp' => $request->pq_rpp,
            'user_agent' => $request->header('User-Agent')
        ]);
        
        $perpage = !empty($request->pq_rpp) ? $request->pq_rpp : getPagination('limit');
        $current_page = $request->pq_curpage ?? 0;
        $request->merge(['page' => $current_page]);
        $start_index = ($current_page - 1) * $perpage;
        
        $order_by = 'sord.id';
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
            $query = \DB::table(with(new OrderShop)->getTable().' as sord')
                  ->join(with(new Order)->getTable().' as ord', 'sord.order_id', '=', 'ord.id')
                  ->select('sord.id', 'sord.shop_id', 'sord.order_id', 'sord.shop_formatted_id', 'sord.total_final_price', 'sord.end_shopping_date', 'sord.order_status', 'sord.admin_remark', 'sord.payment_status', 'ord.formatted_id', 'ord.pickup_time', 'sord.commission_rate', 'sord.commission_fee', 'sord.total_smm_pay');

            if(isset($request->pq_filter)){
                $filter_req = json_decode($request->pq_filter,true);
                if(!empty($filter_req['data'])){
                    $filter_arr = $filter_req['data'];
                    foreach ($filter_arr as $fkey => $fvalue) {
                        $searchval = $fvalue['value'];
                        switch ($fvalue['dataIndx']) {
                            case 'formatted_id':$query->where('ord.formatted_id','like', '%'.$searchval.'%'); break;
                            case 'shop_formatted_id':$query->where('sord.shop_formatted_id','like', '%'.$searchval.'%'); break;
                            case 'admin_remark':$query->where('sord.admin_remark','like', '%'.$searchval.'%'); break;
                            case 'total_final_price':$query->where('sord.total_final_price','=', $searchval); break;
                            case 'commission_rate':$query->where('sord.commission_rate','=', $searchval); break;
                            case 'commission_fee':$query->where('sord.commission_fee','=', $searchval); break;
                            case 'total_smm_pay':$query->where('sord.total_smm_pay','=', $searchval); break;
                            case 'order_status':$query->whereIn('sord.order_status',$searchval); break;
                            case 'end_shopping_date_time':
                                $from_date = $fvalue['value']??'';
                                $to_date = $fvalue['value2']??'';
                                createDateFilter($query,'sord.end_shopping_date',$from_date,$to_date);
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
                            case 'pickup_time':
                                $from_date = $fvalue['value']??'';
                                $to_date = $fvalue['value2']??'';
                                createDateFilter($query,'ord.pickup_time',$from_date,$to_date);
                            break;
                            case 'seller_id':
                                $seller_id_filter = $searchval;
                                break;
                            case 'shop_name':
                                $shop_name_filter = $searchval;
                                break;
                            case 'seller_name':
                                $seller_name_filter = $searchval;
                                break;
                        }
                    }
                }
            }

            if(isset($seller_id_filter) || isset($shop_name_filter) || isset($seller_name_filter)) {
                $shop_query = \DB::table(with(new \App\Shop)->getTable().' as shop')
                    ->join(with(new \App\User)->getTable().' as seller', 'shop.user_id', '=', 'seller.id')
                    ->leftJoin(with(new \App\ShopDesc)->getTable().' as shopdesc', 'shop.id', '=', 'shopdesc.shop_id')
                    ->select('shop.id');

                if(isset($seller_id_filter)) {
                    $shop_query->where('seller.id', '=', $seller_id_filter);
                }
                if(isset($shop_name_filter)) {
                    $shop_query->where('shopdesc.shop_name', 'like', '%'.$shop_name_filter.'%');
                }
                if(isset($seller_name_filter)) {
                    $shop_query->where('seller.display_name', 'like', '%'.$seller_name_filter.'%');
                }

                $shop_ids = $shop_query->pluck('id');
                $query->whereIn('sord.shop_id', $shop_ids);
            }

            $response = $query->orderBy($order_by,$order_by_val)->paginate($perpage,['*'],'page',$current_page);
            $totrec = $response->total();



            if($start_index >= $totrec) {
                $current_page = ceil($totrec/$perpage);
                // เพิ่ม log เพื่อตรวจสอบการเรียกซ้ำ
                \Log::info('Pagination adjustment', [
                    'original_page' => $request->pq_curpage,
                    'adjusted_page' => $current_page,
                    'total_records' => $totrec,
                    'per_page' => $perpage
                ]);
                $response = $query->orderBy($order_by,$order_by_val)->paginate($perpage,['*'],'page',$current_page);
            }

            if($response->count() > 0) {
                $shop_ids = $response->pluck('shop_id')->unique();
                $order_status_ids = $response->pluck('order_status')->unique();

                $shops = \DB::table(with(new \App\Shop)->getTable().' as shop')
                    ->join(with(new \App\User)->getTable().' as seller', 'shop.user_id', '=', 'seller.id')
                    ->leftJoin(with(new \App\ShopDesc)->getTable().' as shopdesc', 'shop.id', '=', 'shopdesc.shop_id')
                    ->whereIn('shop.id', $shop_ids)
                    ->select('shop.id', 'seller.display_name as seller_name', 'seller.id as seller_id', 'shopdesc.shop_name')
                    ->get()
                    ->keyBy('id');

                $order_statuses = \DB::table(with(new \App\OrderStatusDesc)->getTable())
                    ->whereIn('order_status_id', $order_status_ids)
                    ->where('lang_id', session('default_lang'))
                    ->select('order_status_id', 'status')
                    ->get()
                    ->keyBy('order_status_id');

                foreach ($response as $key => $value) {
                    $shop_data = $shops->get($value->shop_id);
                    $status_data = $order_statuses->get($value->order_status);

                    $response[$key]->seller_name = $shop_data->seller_name ?? '';
                    $response[$key]->seller_id = $shop_data->seller_id ?? '';
                    $response[$key]->shop_name = $shop_data->shop_name ?? '';
                    $response[$key]->commission_rate = number_format((float)$value->commission_rate, 2, '.', ',');
                    $response[$key]->commission_fee = number_format((float)$value->commission_fee, 2, '.', ',');
                    $response[$key]->total_smm_pay = number_format((float)$value->total_smm_pay, 2, '.', ',');
                    $response[$key]->status = $status_data->status ?? '';

                    $response[$key]->end_shopping_date_time = $value->end_shopping_date;
                    $response[$key]->end_shopping_date = $value->end_shopping_date?date('Y-m-d',strtotime($value->end_shopping_date)):null;
                    $response[$key]->time = $value->pickup_time?date('H:i:s',strtotime($value->pickup_time)):null;
                    $response[$key]->total_final_price = number_format((float)$value->total_final_price, 2, '.', ',');
                    $response[$key]->detail_url = action('Admin\Transaction\ShopOrderController@orderDetail',$value->shop_formatted_id);
                    $response[$key]->payment_status = ($value->payment_status == 1)?'c-tot':'';
                }
            }

            $this->setFilter('shop_order',$request);
            
        }catch(QueryException $e){
            $response = ['status'=>'fail','msg'=>$e->getMessage()];
        }

        return $response;
    }

    public function orderDetail(Request $request) {

        $formatted_id = $request->oid; 
        $order_shop = OrderShop::where('shop_formatted_id',$formatted_id)->with(['getOrderStatus'])->first();
       
        if(empty($order_shop)){
          return redirect()->action('Admin\Transaction\ShopOrderController@index');
        }
        $order_detail = OrderDetail::getShopOrderDetail('',$order_shop->id);
        $order_shop->details = $order_detail;
        $transaction = \App\OrderTransaction::where('order_shop_id',$order_shop->id)->get();
        if(count($transaction) < 2){
            $transaction = \App\OrderTransaction::where('order_id',$order_shop->order_id)->where('order_shop_id',0)->orderBy('id')->get();
        }
		$order_shop->pickup_time = null;
		if($order_shop->order_id>0)
		{
			$order_info = Order::where('id',$order_shop->order_id)->first();
			if($order_info)
			{
				$order_shop->pickup_time=$order_info->pickup_time;
			}
		}
		/* Start:: If Product Detail Not Available in Order Details */
		if($order_shop->details)
		{
			if(!empty($order_shop->details))
			{
				foreach($order_shop->details as $key => $val)
				{
					if($val->description=='' || $val->description==null)
					{
						$productDetail = \App\Product::getProductDetailAll($val->sku);
						$order_shop->details[$key]->description=isset($productDetail->productDesc)?$productDetail->productDesc->description:"";
					}
				}
			}
		}
        $shop_json = json_decode($order_shop->shop_json,true);
		/* Start:: If Product Detail Not Available in Order Details */
        return view('admin.transaction.shopOrdDetail',['order_shop'=>$order_shop,'transaction'=>$transaction,'main_order_info'=>$order_info,'shop_json'=>$shop_json]);
    } 
    
    public function orderDetailTest(Request $request) {

        $formatted_id = $request->oid; 
        $order_shop = OrderShop::where('shop_formatted_id',$formatted_id)->with(['getOrderStatus'])->first();
       
        if(empty($order_shop)){
          return redirect()->action('Admin\Transaction\ShopOrderController@index');
        }
        $order_detail = OrderDetail::getShopOrderDetail('',$order_shop->id);
        $order_shop->details = $order_detail;
        $transaction = \App\OrderTransaction::where('order_shop_id',$order_shop->id)->get();
        if(count($transaction) < 2){
            $transaction = \App\OrderTransaction::where('order_id',$order_shop->order_id)->where('order_shop_id',0)->orderBy('id')->get();
        }
		$order_shop->pickup_time = null;
		if($order_shop->order_id>0)
		{
			$order_info = Order::where('id',$order_shop->order_id)->first();
			if($order_info)
			{
				$order_shop->pickup_time=$order_info->pickup_time;
			}
		}
		/* Start:: If Product Detail Not Available in Order Details */
		if($order_shop->details)
		{
			if(!empty($order_shop->details))
			{
				foreach($order_shop->details as $key => $val)
				{
					if($val->description=='' || $val->description==null)
					{
						$productDetail = \App\Product::getProductDetail($val->sku);
						$order_shop->details[$key]->description=isset($productDetail->productDesc)?$productDetail->productDesc->description:"";
					}
				}
			}
		}
        $shop_json = json_decode($order_shop->shop_json,true);
		/* Start:: If Product Detail Not Available in Order Details */
        return view('admin.transaction.shopOrdDetailTest',['order_shop'=>$order_shop,'transaction'=>$transaction,'main_order_info'=>$order_info,'shop_json'=>$shop_json]);
    } 
    
    public function changeShopOrderStatus(Request $request){

        $order_shop_id = $request->order_shop_id ?? 0;
        $status = $request->type ?? '';

        $order_shop = OrderShop::where('id',$order_shop_id)->first();

        if($order_shop_id && $status && !empty($order_shop)){
            if($status == 'complete'){
                $status_id = 3;
                $comment = GeneralFunctions::getOrderText('order_completed');
                $msg = Lang::get('admin_order.order_completed');

                $update_details = OrderDetail::where(['order_shop_id'=>$order_shop->id])->whereNotIn('status',[4,9,10,11,12])->update(['status'=>$status_id]);
            }elseif($status == 'cancel'){
                $status_id = 4;
                $comment = GeneralFunctions::getOrderText('order_cancelled');
                $msg = Lang::get('admin_order.order_cancelled');
                
                $update_details = OrderDetail::where(['order_shop_id'=>$order_shop->id])->whereNotIn('status',[3,5,6,8])->update(['status'=>$status_id]);
            }else{
                return ['status'=>'fail','msg'=>'Invalid status'];
            }
            $order_shop->order_status = $status_id;
            $order_shop->save();

            /****update entry in order transaction******/
            $transaction_arr = ['order_id'=>$order_shop->order_id,'order_shop_id'=>$order_shop->id,'order_detail_id'=>0,'event'=>'delivery','comment'=>$comment,'updated_by'=>'admin'];

            $update_transaction = \App\OrderTransaction::updateOrdTrans($transaction_arr);

            /****update main order status****/

            $shop_ord = OrderShop::where('order_id',$order_shop->order_id)->select('id','order_status')->get();
            $shop_status_arr = [];
            /*****if shop status 3 or 4 then update main order****/
            $update_main_ord = 0;
            foreach ($shop_ord as $key => $value) {
                if($value->order_status == 3 || $value->order_status == 4){
                    $shop_status_arr[] = $value->order_status;
                }else{
                   $update_main_ord = 1;
                   /**no need to update because some order processing**/
                }
                
            }

            if(!$update_main_ord && count($shop_status_arr)){
                if (count(array_unique($shop_status_arr)) === 1 && end($shop_status_arr) == 3){
                    $main_ord_status_id = 3;
                }elseif(count(array_unique($shop_status_arr)) === 1 && end($shop_status_arr) == 4){
                    $main_ord_status_id = 4;
                }else{
                    $main_ord_status_id = 3;
                }
                /*****update main order status*****/
                $main_ord_update = \App\Order::where('id',$order_shop->order_id)->update(['order_status'=>$main_ord_status_id]);
            }

            return ['status'=>'success','msg'=>$msg];

        }else{
            return ['status'=>'fail','msg'=>'Invalid details'];
        }
        
    }

    public function updateRemark(Request $request){
        $order_id = $request->order_shop_id;
        $order = OrderShop::where('id',$request->order_shop_id)->first();
        if($order){
            $order->admin_remark = trim($request->remark);
            $order->admin_remark_by = Auth::guard('admin_user')->user()->nick_name;
            $order->save();

            return ['status'=>'success','msg'=>\Lang::get('admin_order.remark_updated_successfully')];
        }
        return ['status'=>'fail','msg'=>\Lang::get('admin_common.something_went_wrong')];
    }
    
    public function sellerOrder(Request $request){
        $permission = $this->checkUrlPermission('seller_order_export');
        if($permission === true) {
            $filter_date = null;
            if (!empty($request->filter_date)) {
                $filter_date = $request->filter_date;
            }
            $filter = $this->getFilter('seller_order_export');
           
            return view('admin.transaction.listSellerOrder', ['filter'=>$filter,'filter_date'=>$filter_date]);
        }
    }

    public function listSellerOrderData(Request $request){
        $perpage = !empty($request->pq_rpp) ? $request->pq_rpp : 10;
        $request->page = $current_page = !empty($request->pq_curpage)?$request->pq_curpage:0;

        $start_index = ($current_page - 1) * $perpage;
        $order_by = 'sord.id';
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
            $filter_date = $request->filter_date;
            $prefix = DB::getTablePrefix();
            $default_lang = 0;
            $query = \DB::table(with(new OrderShop)->getTable().' as sord')

                    ->join(with(new \App\Order)->getTable().' as ord', 'sord.order_id', '=', 'ord.id')
                    ->join(with(new \App\Seller)->getTable().' as seller', 'sord.shop_user_id', '=', 'seller.user_id')
                    ->leftjoin(with(new \App\PaymentBankDesc)->getTable().' as pbd', [['seller.bank_id', '=', 'pbd.payment_bank_id'], ['pbd.lang_id', '=', DB::raw($default_lang)]])
                    ->select(DB::raw('sum(' . $prefix . 'sord.total_final_price) as tot_amount'),'sord.shop_id','sord.shop_json','pbd.bank_name')
                    ->whereDate('ord.pickup_time',$filter_date);
            
            if(isset($request->pq_filter)){
                $filter_req = json_decode($request->pq_filter,true);
                if(!empty($filter_req['data'])){
                    $filter_arr = $filter_req['data'];
                    foreach ($filter_arr as $fkey => $fvalue) {

                        $searchval = $fvalue['value'];
                        switch ($fvalue['dataIndx']) {
                            
                            case 'shop_name':$query->where('sord.shop_json','like', '%'.$searchval.'%'); break;
                            
                            case 'seller_name':$query->where('sord.shop_json','like', '%'.$searchval.'%'); break;

                            case 'bank_name':$query->where('pbd.bank_name','like', '%'.$searchval.'%'); break;

                            case 'seller_ph_number':$query->where('sord.shop_json','like', '%'.$searchval.'%'); break;
                        }
                        
                    }
                }
            }
            $query->groupBy('sord.shop_id');
            $response = $query->orderBy($order_by,$order_by_val)->paginate($perpage,['*'],'page',$current_page);
            $totrec = $response->total();
            //dd($response);
            if($start_index >= $totrec) {
                $current_page = ceil($totrec/$perpage);
                
                $response = $query->orderBy($order_by,$order_by_val)->paginate($perpage,['*'],'page',$current_page);
            }

            foreach ($response as $key => $value) {
                $json_data = json_decode($value->shop_json);
                $seller_name = $json_data->seller_name;
                $seller_ph_number = $json_data->seller_ph_number;
                $shop_name = $json_data->shop_name[0];
                $panel_no = $json_data->panel_no;
                $amount = numberFormat($value->tot_amount);
                $last_exp_log = \App\OrderExportLog::whereDate('order_date',$filter_date)->whereRaw('FIND_IN_SET(?, shop_ids)', [$value->shop_id])->orderBy('created_at','desc')->value('created_at');
                if($last_exp_log){
                    $latest_log_date = getDateFormat($last_exp_log,4);
                }else{
                    $latest_log_date = 'N/A';
                }
                $response[$key]->id = $value->shop_id;
                $response[$key]->seller_name = $seller_name;
                $response[$key]->seller_ph_number = $seller_ph_number;
                $response[$key]->shop_name = $shop_name;
                $response[$key]->latest_log_date = $latest_log_date;
                $response[$key]->panel_no = $panel_no;
                $response[$key]->amount = $amount;
                $response[$key]->status = 'N/A';
                $response[$key]->detail_url = action('Admin\Transaction\ShopOrderController@sellerDetail').'?shop_id='.$value->shop_id.'&order_date='.$filter_date;
            }

            /***save filter****/
            $this->setFilter('seller_order_export',$request);

            
        }catch(QueryException $e){
            $response = ['status'=>'fail','msg'=>$e->getMessage()];
        }
        

        return $response;
    }
    
    public function sellerDetail(Request $request) {

        $shop_id = $request->shop_id; 
        $date = $request->order_date;
        $shop_details = \App\Shop::where('id',$shop_id)->with(['shopDesc'])->first();
        if(!$shop_details){
            abort(404);
        }
        $order_shop = [];
        if($date){
            $order_shop = \App\OrderShop::where('shop_id',$shop_id)->whereDate('end_shopping_date',$date)->get();
        }
        
        return view('admin.transaction.sellerDetail',['shop_details'=>$shop_details,'order_shop'=>$order_shop,'order_date'=>$date]);
    }
    
    public function getGeneratedLog(Request $request){
        $shop_id = $request->shop_id;
        $order_date = $request->order_date;
        $exp_log = \App\OrderExportLog::select('file_name','created_at','bank_type')->whereDate('order_date',$order_date)->whereRaw('FIND_IN_SET(?, shop_ids)', [$shop_id])->get();
        if($exp_log){
            foreach ($exp_log as $key => $value) {
                $exp_log[$key]->log_at = getDateFormat($value->created_at,4);                
            }
            return ['status'=>'success','data'=>$exp_log];
        }
        else{
            return ['status'=>'fail'];
        }
    }
    
    function update(Request $request){
    }

    public function orderDetailExport(Request $request) {

        $formatted_id = $request->oid; 
        $order_shop = OrderShop::where('shop_formatted_id',$formatted_id)->with(['getOrderStatus'])->first();
        
        if(empty($order_shop)){
          return redirect()->action('Admin\Transaction\ShopOrderController@index');
        }
        $shop_name ='';
        $json_name = json_decode($order_shop->shop_json,true);
        if($json_name){
            $shop_name = $json_name['shop_name'][0];
        }
        $order_detail = OrderDetail::getShopOrderDetail('',$order_shop->id);
        $order_shop->details = $order_detail;
        $transaction = \App\OrderTransaction::where('order_shop_id',$order_shop->id)->get();
        if(count($transaction) < 2){
            $transaction = \App\OrderTransaction::where('order_id',$order_shop->order_id)->where('order_shop_id',0)->orderBy('id')->get();
        }
		$order_shop->pickup_time = null;
		if($order_shop->order_id>0)
		{
			$order_info = Order::where('id',$order_shop->order_id)->first();
			if($order_info)
			{
				$order_shop->pickup_time=$order_info->pickup_time;
			}
		}
		/* Start:: If Product Detail Not Available in Order Details */
		if($order_shop->details)
		{
			if(!empty($order_shop->details))
			{
				foreach($order_shop->details as $key => $val)
				{
					if($val->description=='' || $val->description==null)
					{
						$productDetail = \App\Product::getProductDetail($val->sku);
						$order_shop->details[$key]->description=isset($productDetail->productDesc)?$productDetail->productDesc->description:"";
					}
				}
			}
		}
		/* Start:: If Product Detail Not Available in Order Details */
        //return view('admin.transaction.shopOrdDetail',['order_shop'=>$order_shop,'transaction'=>$transaction]);
        $pdf = PDF::loadView('admin.transaction.shopOrddetailExport',['order_shop'=>$order_shop,'transaction'=>$transaction,'shop_name'=>$shop_name]);
        
        return $pdf->download($order_shop->shop_formatted_id.'.pdf');
    }
    
    public function generateOrderPdf(Request $request) {

        $formatted_id = explode(',',$request->order_list); 
        $total_order = OrderShop::whereIn('shop_formatted_id',$formatted_id)->with(['getOrderStatus'])->orderby('id')->get();
        
        if(empty($total_order)){
          abort(404);
        }
        $pdf = PDF::loadView('admin.transaction.shopOrdListlExport', ['total_order' => $total_order]);
        return $pdf->download('shop-order.pdf');
        //return view('admin.transaction.mainOrderListlExport',['total_order' => $total_order]);
    
        //return ['status'=>'success','message'=>'Pdf Download Successfully'];
        
    }
    
}
