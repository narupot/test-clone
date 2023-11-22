<?php  

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use DB;
Use Lang;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Order;
use App\OrderShop;
use App\OrderDetail;
use App\ShippingAddress;
use App\Helpers\GeneralFunctions; 
use App\Helpers\CustomHelpers;
use App\Helpers\EmailHelpers;
use App\User;
use Auth;
use Session;
use Config;
use File;
use Exception;


class OrderController extends MarketPlace {

    public function __construct() {

        $this->middleware('authenticate'); 

    }

    public function orderHistory(Request $request){

        return view('seller.order.order_history');
    }

    public function orderHistoryData(Request $request){
        $column_arr = ['os.end_shopping_date','os.shop_formatted_id','os.user_name','os.shipping_method','os.order_status','action'];
        $draw        = $request->draw;
        $start       = $request->start; //Start is the offset
        $length      = $request->length; //How many records to show
        $column      = $request->order[0]['column']; //Column to orderBy
        $dir         = $request->order[0]['dir']; //Direction of orderBy
        $searchValue = $request->search['value']; //Search value
        $order_by_column = $column_arr[$column];
        //Sets the current page
        Paginator::currentPageResolver(function () use ($start, $length) {
            return ($start / $length + 1);
        });


        $user_id = Auth::id();
        $shop_id = session('user_shop_id');
        $check_date = date('Y-m-d');
        /*$order_data = OrderShop::where('shop_id',$shop_id)->where('end_shopping_date','!=',null)->Where(function($query) use($check_date){
                $query->whereNotIn('order_status',[1,2,5,8]);
                $query->orWhereRaw(DB::raw("(order_status in(2,5,8) and date(seller_status_at) < '$check_date')"));
            });*/
        //new qry status complete or cancel or pickup date has passed
        $order_data= DB::table(with(new OrderShop)->getTable().' as os')
                ->join(with(new Order)->getTable().' as o', 'os.order_id', '=', 'o.id')
                ->join(with(new \App\OrderStatusDesc)->getTable().' as osd', 'os.order_status', '=', 'osd.order_status_id')
                ->select('os.end_shopping_date','os.shop_formatted_id','os.user_name','os.shipping_method','osd.status')
                ->where('os.shop_id',$shop_id)
                ->Where(function($query) use($check_date){
                    $query->whereIn('os.order_status',[3,4]);
                    $query->orWhere('o.pickup_time','<',$check_date);
                });

        if(!empty($searchValue)){
            $order_data->where('os.shop_formatted_id', 'LIKE',"%{$searchValue}%");
        }

        $order_list = $order_data->orderBy($order_by_column, $dir)->paginate($length);
        $data = array();
        if(!empty($order_list)){

            foreach ($order_list as $ord_val){

                $nestedData['end_shopping_date'] = getDateFormat($ord_val->end_shopping_date,7);

                $nestedData['buyer_name'] = $ord_val->user_name;
                $nestedData['shop_formatted_id'] = '<a href="'.action('Seller\OrderController@details',$ord_val->shop_formatted_id).'" class="link-skyblue">'.$ord_val->shop_formatted_id.'</a>';

                $nestedData['status'] = $ord_val->status;

                $nestedData['shipping_method'] = GeneralFunctions::getShippingMethod($ord_val->shipping_method);
                $nestedData['action'] = "<a href='".action('Seller\OrderController@details',$ord_val->shop_formatted_id)."' class='skyblue'>".Lang::get('common.view_detail')."</a>";
                $data[] = $nestedData;
            }
        }
        
         $json_data = array(
                    "draw"            => intval($draw),  
                    "recordsTotal"    => intval($order_list->total()),  
                    "recordsFiltered" => intval($order_list->total()), 
                    "data"            => $data   
                    );
            
        return $json_data; 
    }

    public function details(Request $request){
        $orderShopData = \App\OrderShop::where('shop_formatted_id',$request->id)->with(['getUser'])->first();
        //dd(session()->all());

        if($orderShopData==null)
            abort('404');

        $orderItems = \App\OrderDetail::getShopOrderDetail('',$orderShopData->id);
        $previous_url = url()->previous();
        $del_type = \App\DeliveryTime::getDeliverYType($orderShopData->shipping_method);
        $delivery_time = \App\DeliveryTime::getDeliveryTime($del_type);
        $prepare_time_before = $delivery_time->prepare_time_before;
        $main_ord = \App\Order::where('id',$orderShopData->order_id)->select('pickup_time','user_phone_no')->first();
        if($prepare_time_before && strtotime($main_ord->pickup_time)){
            $main_ord->pickup_time = date('Y-m-d H:i:s',strtotime('-'.$prepare_time_before.' hours', strtotime($main_ord->pickup_time)));
            
        }
        return view('seller.order.shop_order_detail',['orderShopData'=>$orderShopData,'orderItems'=>$orderItems,'previous_url'=>$previous_url,'main_ord'=>$main_ord]);
    }

    public function deliveryList(Request $request){
        $fielddata = $this->searchData();
        $section = ($request->section===null)?'prepare':'ready';
        $shop_id = session('user_shop_id');
        return view('seller.order.delivery_list',['fielddata'=>$fielddata,'section'=>$section]);
    }

    public function searchData() {
        $data = \App\TableColumnConfiguration::whereIn('column_name',['category_name','badge_name','status','unit_price', 'unit_name'])->get()->toArray();
        $datarray = array();
        foreach($data as $resv){
          $datarray[$resv['column_name']] = $resv; 
        }
        $fieldSets = [];
        $replace = ['0'=>false,'1'=>true];
        foreach ($datarray as $key => $res) { 

                $showName = str_replace('_', ' ', $res['column_name']);
                $tempSets = ['fieldName'=>$key,'showName'=>$showName,'sortable'=>$replace[$res['sort']],'filterable'=>$replace[$res['filter']],'width'=> $res['width'],'align'=> $res['align'],"fieldType" => $res['field_type']];
                
                if($res['field_type'] == 'textbox'){
                    $tempSets['textBoxType'] = 'single';
                    $tempSets['datatype'] = 'text';
                }
                if($res['field_type'] == 'selectbox'){
                    $tempSets['selectionType'] = 'single';
                    $tempSets['optionValType'] = 'collection';
                    $tempSets['defaultVal']    = '';

                    if($res['column_name'] == 'category_name'){
                        $seller_prod_cat = ShopAssignCategory::getShopCategoryForFilter();
                        $tempSets['optionArr']    = generatedDD($seller_prod_cat);
                    }
                    
                    if($res['column_name'] == 'unit_name'){
                        $unitsdata = \App\Unit::getUnitsForFilter();
                        $tempSets['optionArr']    = generatedDD($unitsdata);
                    }
                    
                    if($res['column_name'] == 'badge_name'){
                        $prod_badge = Badge::getBadgeForFilter();
                        $tempSets['optionArr']    = generatedDD($prod_badge);
                    }
                    
                    if($res['column_name'] == 'status'){
                        $statusArr = generatedDD(['0'=>'common.inactive','1'=>'common.active']);
                        $tempSets['optionArr']    = $statusArr;
                    }
                    
                }
            $fieldSets[] = $tempSets;
        }
        $table = \App\TableConfiguration::getTableConfig('order_list', 'slug');
        //'filter'=>$replace[$table->filter]
        $tableConfig = ['resizable'=>$replace[$table->resizable],'row_rearrange'=>$replace[$table->row_rearrange],'column_rearrange'=>$replace[$table->column_rearrange],'filter'=>false,'chk_action'=>$replace[$table->chk_action],'col_setting'=>$replace[$table->chk_action]];

       // dd($name->toArray());
        $marks = array('fieldSets' =>$fieldSets,'tableConfig'=>[$tableConfig]);
        return json_encode($marks);
    } 

    public function deliveryListData(Request $request){
        $perpage = !empty($request->per_page) ? $request->per_page : getPagination('limit');
        $user_id = Auth::id();
        $shop_id = session('user_shop_id');
        $lang_id = session('default_lang');
        /***
        ** order status 1 = pending and 2= processing
        ***/
        $check_date = date('Y-m-d');
        $prefix = DB::getTablePrefix();
        $order_data = DB::table(with(new \App\OrderShop)->getTable().' as os')
                ->leftJoin(with(new \App\User)->getTable().' as u',[['os.user_id', '=', 'u.id']])
                ->leftJoin(with(new \App\OrderStatusDesc)->getTable().' as osd',[['os.order_status', '=', 'osd.order_status_id']])
                ->where('os.shop_id',$shop_id)
                ->where('osd.lang_id',$lang_id)
                ->where('os.end_shopping_date','!=',null)
                ->orderBy('os.id', 'desc')
                ->select('os.user_name','os.shop_formatted_id','os.order_status','os.shipping_method','os.end_shopping_date','os.total_final_price', 'u.image','osd.status');

        if($request->section=='ready'){
            $order_data->where(function($query) use($check_date,$prefix){

                $query->whereIn('os.order_status',[3,5,8])->where(DB::raw('date(' . $prefix . 'os.seller_status_at)'), $check_date);

                $query->orWhereRaw("(".$prefix."os.seller_status in('sent') and date(".$prefix."os.seller_status_at) = '$check_date')");
            });
            
        }else{
            $order_data->whereIn('os.order_status',[1,2])->where('os.seller_status','!=','sent');
        }

        $order_list = $order_data->paginate($perpage);  
        
        if(!empty($order_list)){
            foreach ($order_list as $ord_val){
                switch ($ord_val->shipping_method) {
                    case '1':
                        $shipping_method = Lang::get('order.ship_pick_up');//"Pick UP";
                    break;
                    case '2':
                        $shipping_method = Lang::get('order.ship_shop_address');//"Shop Address";
                    break;
                    case '3':
                        $shipping_method = Lang::get('order.ship_buyer_address');//"Buyer Address";
                    break;
                }
                
                $ord_val->image_url = getUserImage($ord_val->image);
                $ord_val->shipping_method_name = $shipping_method;
                $ord_val->status = $ord_val->status;
                $ord_val->total_final_price = $ord_val->total_final_price.' '.Lang::get('common.currency');
                $ord_val->url = action('Seller\OrderController@details',$ord_val->shop_formatted_id);

            }
        }
        //dd($order_list);
        return $order_list; 
    }

    function updateShopOrdStatus(Request $request){
        //return ['status'=>'fail','msg'=>'can not update from here'];
        $shop_id = session('user_shop_id');
        
        $status = $request->ord_status??'';
        $order_shop_id = $request->ord_id??'';

        if(($status == 'ready' || $status == 'prepare' || $status == 'sent') && $order_shop_id>0){
            $ord_detail = OrderShop::where(['id'=>$order_shop_id,'shop_id'=>$shop_id])->first();

            if(!empty($ord_detail)){
                $ord_detail->seller_status  = $status;
                $ord_detail->seller_status_at = date('Y-m-d H:i:s');
                $ord_detail->save();
                return ['status'=>'success','msg'=>Lang::get('order.status_updated_successfully')];
            }else{
                return ['status'=>'fail','msg'=>'invalid order id'];
            }
        }else{
            return ['status'=>'fail','msg'=>'invalid parameter'];
        }
    }

    public function orderOutstandingBalance(Request $request){
        return view('seller.bill.outstanding_balance');
    }

    public function orderOutstandingBalanceData(Request $request){
        $column_arr = ['id','shop_formatted_id','user_name','total_final_price'];
        $draw        = $request->draw;
        $start       = $request->start; //Start is the offset
        $length      = $request->length; //How many records to show
        $column      = $request->order[0]['column']; //Column to orderBy
        $dir         = $request->order[0]['dir']; //Direction of orderBy
        $searchValue = $request->search['value']; //Search value
        $order_by_column = $column_arr[$column];
        //Sets the current page
        Paginator::currentPageResolver(function () use ($start, $length) {
            return ($start / $length + 1);
        });


        $user_id = Auth::id();
        $shop_id = session('user_shop_id');
        $shop_id = 1;
        $order_data = OrderShop::where('shop_id',$shop_id)->where('order_status',3);

        if(!empty($searchValue)){
            $order_data->where('shop_formatted_id', 'LIKE',"%{$searchValue}%");
        }

        $order_list = $order_data->with('getOrderStatus')->orderBy($order_by_column, $dir)->paginate($length);
        $data = array();

        if(!empty($order_list)){

            foreach ($order_list as $key => $ord_val){

                $nestedData['sno'] = ++$key;

                $nestedData['buyer_name'] = $ord_val->user_name;
                $nestedData['shop_formatted_id'] = '<a href="'.action('Seller\OrderController@details',$ord_val->shop_formatted_id).'" class="link-skyblue">'.$ord_val->shop_formatted_id.'</a>';

                $nestedData['total'] = numberFormat($ord_val->total_final_price);
                $nestedData['action'] = "<a href='".action('Seller\OrderController@details',$ord_val->shop_formatted_id)."' class='skyblue'>".Lang::get('common.view_detail')."</a>";
                $data[] = $nestedData;
            }
        }
        
         $json_data = array(
                    "draw"            => intval($draw),  
                    "recordsTotal"    => intval($order_list->total()),  
                    "recordsFiltered" => intval($order_list->total()), 
                    "data"            => $data   
                    );
            
        return $json_data; 
    }
}