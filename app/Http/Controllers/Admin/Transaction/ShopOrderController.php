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

class ShopOrderController extends MarketPlace
{
    public function __construct(){
       $this->middleware('admin.user');
    }
    
    public function index(){

        $permission = $this->checkUrlPermission('shop_order');
        if($permission === true) {
            
            $filter = $this->getFilter('shop_order');

            return view('admin.transaction.listShopOrder', ['filter'=>$filter]);
        }      
    }

    public function listOrderData(Request $request){
        $perpage = !empty($request->pq_rpp) ? $request->pq_rpp : 10;
        $request->page = $current_page = !empty($request->pq_curpage)?$request->pq_curpage:0;

        $start_index = ($current_page - 1) * $perpage;
        //dd($perpage,$request->page);
        
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
                  ->join(with(new \App\Shop)->getTable().' as shop', 'sord.shop_id', '=', 'shop.id')
                  ->join(with(new \App\ShopDesc)->getTable().' as shopdesc', 'shop.id', '=', 'shopdesc.shop_id')
                  ->join(with(new \App\User)->getTable().' as seller', 'shop.user_id', '=', 'seller.id')
                  ->join(with(new \App\OrderStatusDesc)->getTable().' as osd', 'sord.order_status','=', 'osd.order_status_id')
                  ->select('seller.display_name as seller_name','seller.id as seller_id','sord.shop_formatted_id','ord.formatted_id','sord.total_final_price','sord.end_shopping_date','osd.status','sord.admin_remark','sord.payment_status','shopdesc.shop_name');
            
            if(isset($request->pq_filter)){
                $filter_req = json_decode($request->pq_filter,true);
                if(!empty($filter_req['data'])){
                    $filter_arr = $filter_req['data'];
                    foreach ($filter_arr as $fkey => $fvalue) {

                        $searchval = $fvalue['value'];
                        switch ($fvalue['dataIndx']) {
                            case 'formatted_id':$query->where('ord.formatted_id','like', '%'.$searchval.'%'); break;
                            case 'shop_formatted_id':$query->where('sord.shop_formatted_id','like', '%'.$searchval.'%'); break;
                            case 'seller_id':$query->where('seller.id','=',$searchval); break;
                            case 'shop_name':$query->where('shopdesc.shop_name','like', '%'.$searchval.'%'); break;
                            case 'admin_remark':$query->where('sord.admin_remark','like', '%'.$searchval.'%'); break;
                            case 'seller_name':$query->where('seller.display_name','like', '%'.$searchval.'%'); break;
                            case 'total_final_price':$query->where('sord.total_final_price','=', $searchval); break;
                            case 'status':$query->whereIn('u.status',$searchval); break;
                            case 'end_shopping_date':
                                $from_date = $fvalue['value']??'';
                                $to_date = $fvalue['value2']??'';
                                createDateFilter($query,'sord.end_shopping_date',$from_date,$to_date);
                            break;

                        }
                        
                    }
                }
            }
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
                
                $response[$key]->total_final_price = numberFormat($value->total_final_price);
                $response[$key]->status = $value->status??'';
                $response[$key]->detail_url = action('Admin\Transaction\ShopOrderController@orderDetail',$value->shop_formatted_id);
                $response[$key]->payment_status = ($value->payment_status == 1)?'c-tot':'';
            }

            /***save filter****/
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
        
        return view('admin.transaction.shopOrdDetail',['order_shop'=>$order_shop,'transaction'=>$transaction]);
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
            }elseif($status == 'cancel'){
                $status_id = 4;
                $comment = GeneralFunctions::getOrderText('order_cancelled');
                $msg = Lang::get('admin_order.order_cancelled');
            }else{
                return ['status'=>'fail','msg'=>'Invalid status'];
            }
            $order_shop->order_status = $status_id;
            $order_shop->save();

            /****update order detail status for this shop*******/
            $update_details = OrderDetail::where(['order_shop_id'=>$order_shop->id])->update(['status'=>$status_id]);

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
        //dd($perpage,$request->page);
        
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
                  ->join(with(new \App\Seller)->getTable().' as seller', 'sord.shop_user_id', '=', 'seller.user_id')
                  ->leftjoin(with(new \App\PaymentBankDesc)->getTable().' as pbd', [['seller.bank_id', '=', 'pbd.payment_bank_id'], ['pbd.lang_id', '=', DB::raw($default_lang)]])
                  ->select(DB::raw('sum(' . $prefix . 'sord.total_final_price) as tot_amount'),'sord.shop_id','sord.shop_json','pbd.bank_name')
                  ->whereDate('sord.end_shopping_date',$filter_date);
            
            if(isset($request->pq_filter)){
                $filter_req = json_decode($request->pq_filter,true);
                if(!empty($filter_req['data'])){
                    $filter_arr = $filter_req['data'];
                    foreach ($filter_arr as $fkey => $fvalue) {

                        $searchval = $fvalue['value'];
                        switch ($fvalue['dataIndx']) {
                            
                            case 'shop_name':$query->where('sord.shop_json','like', '%'.$searchval.'%'); break;
                            
                            case 'seller_name':$query->where('sord.shop_json','like', '%'.$searchval.'%'); break;

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
                $shop_name = $json_data->shop_name[0];
                $panel_no = $json_data->panel_no;
                $amount = numberFormat($value->tot_amount);
                $response[$key]->id = $value->shop_id;
                $response[$key]->seller_name = $seller_name;
                $response[$key]->shop_name = $shop_name;
                
                $response[$key]->panel_no = $panel_no;
                $response[$key]->amount = $amount;
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
    
    function edit($group_id){
    }
    
    function update(Request $request){
    }
    
}
