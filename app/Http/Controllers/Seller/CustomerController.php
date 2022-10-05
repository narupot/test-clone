<?php

namespace App\Http\Controllers\Seller;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\MarketPlace;
use App\Helpers\GeneralFunctions;
use App\Helpers\CustomHelpers;
use Illuminate\Http\Request;
use App\ShopCustomer;
use DateTime;
use DB;
use Auth;
use Lang;
use Config;
use Session;
use Exception;

class CustomerController extends MarketPlace
{   
    public function __construct() {
        $this->middleware('authenticate');
    }     

    public function index(Request $request){

        $fielddata = json_encode(['fieldSets' =>[], 'tableConfig'=>[]]);
        return view('seller.customer.cust_list',['fielddata'=>$fielddata]);
    }

    public function customerListData(Request $request){
        $perpage = !empty($request->per_page) ? (int)$request->per_page : 10;
        $shop_id = session('user_shop_id');
        $query = \DB::table(with(new \App\OrderShop)->getTable().' as sord')
                  ->join(with(new \App\User)->getTable().' as user', 'sord.user_id', '=', 'user.id')
                  ->leftJoin(with(new ShopCustomer)->getTable().' as scust',[['sord.user_id', '=', 'scust.user_id'], ['scust.shop_id', '=' , DB::raw($shop_id)]])
                  ->leftJoin(with(new \App\Credits)->getTable().' as c',[['c.user_id', '=', 'user.id'], ['c.shop_id', '=' , DB::raw($shop_id)]])
                  ->select('sord.id','sord.user_id as user_id','sord.user_name','sord.user_email','sord.ph_number','user.status','user.image','scust.nic_name','c.credited_amount','c.seller_approval')
                  ->where('sord.shop_id',$shop_id)
                  
                  ->groupBy('sord.user_id');

        $response = $query->orderBy('sord.id','desc')->paginate($perpage);
        if($response->total()){
            foreach ($response as $key => $value) {
                $response[$key]->image = getUserImageUrl($value->image);
                $response[$key]->user_status = $value->status?Lang::get('common.active'):Lang::get('common.inactive');
                $response[$key]->desinated_val = $value->nic_name;
                $response[$key]->history_view_url = action('Seller\CustomerController@details',['id'=>$value->user_id]);

                if($value->seller_approval=='Approved' && $value->credited_amount!=0){
					$response[$key]->manage_credit_url = action('Seller\CreditController@manageUserCredit',['uid'=>$value->user_id]);
                }else{
                    $response[$key]->manage_credit_url = "";
                }
            }
        }

        return $response;
    }

    public function changeCustName(Request $request){

        if(isset($request->name) && isset($request->user_id)){
            $shop_id = session('user_shop_id');
            $name = cleanValue($request->name);
            $user_id = $request->user_id;

            $shop_customer = ShopCustomer::where(['shop_id'=>$shop_id,'user_id'=>$user_id])->first();

            if(empty($shop_customer)){
                $shop_customer = new ShopCustomer;

                $shop_customer->shop_id = $shop_id;
                $shop_customer->user_id = $user_id;
            }
            $shop_customer->nic_name = $name;
            $shop_customer->save();

            return ['status'=>'success'];

        }else{  
            return ['status'=>'fail','msg'=>'validation error'];
        }
    }

    public function details(Request $request,$id){

        $shopData = $this->getSellerShop();
        $customerDetails = \App\User::where('id',$id)->with(['getCustGroupDesc'])->first();

        $img_url = getUserImageUrl($customerDetails->image);
        $paymentOptions = \App\PaymentPeriods::select('name','value')->where('status','1')->get();

        $userCredit = \App\Credits::where(['shop_id'=>$shopData->id,'user_id'=>$id,'seller_approval'=>'Approved'])->first();

        $select_option = "";
        
        foreach($paymentOptions as $p_option){
            if(isset($userCredit->payment_period) && $p_option->value==$userCredit->payment_period){
                $selected = 'selected="selected"';
            }else{
                $selected = '';
            }
            $select_option .= '<option value="'.$p_option->value.'" '.$selected.' >'.$p_option->name.'</option>';
        }

        if($customerDetails==null)
            abort('404');

        
        //dd($customerDetails,$userCredit);
        return view('seller.customer.customer_order_history',['customerDetails'=>$customerDetails,'userCredit'=>$userCredit,'select_option'=>$select_option,'img_url'=>$img_url]);
    } 

    public function getUserOrderList(Request $request){
        $draw        = $request->draw;
        $start       = $request->start; //Start is the offset
        $length      = $request->length; //How many records to show
        $column      = $request->order[0]['column']; //Column to orderBy
        $dir         = $request->order[0]['dir']; //Direction of orderBy
        $searchValue = $request->search['value']; //Search value
        
        //Sets the current page

        $shopData = $this->getSellerShop();

        Paginator::currentPageResolver(function () use ($start, $length) {
            return ($start / $length + 1);
        });

        $results = \App\OrderShop::where(['user_id'=>$request->user_id,'shop_id'=>$shopData->id])->with(['getOrderDetail','getOrderStatus']);
        $shopOrderList = $results->orderBy('end_shopping_date', $dir)->paginate($length);
        $data = array();
        if(!empty($shopOrderList))
        {
            foreach ($shopOrderList as $cr_request)
            {
                $order_detail_json = json_decode($cr_request->getOrderDetail->order_detail_json);
                $action = "<a class='skyblue' href='".action('Seller\OrderController@details',['id'=>$cr_request->shop_formatted_id])."'>".Lang::get('shop.detail')."</a>";
                $nestedData['order_number'] = "<span class='skyblue'>".$cr_request->shop_formatted_id.'</span>';
                $nestedData['date'] = "<span class='gray'>".getDateFormat($cr_request->end_shopping_date,8)."</span>";
                $nestedData['credit_status'] = $cr_request->getOrderStatus->status;
                $nestedData['shipping_method'] = GeneralFunctions::getShippingMethod($cr_request->shipping_method);

                if($cr_request->getOrderDetail->payment_type=='credit'){

                    if($cr_request->getOrderDetail->credit_paid_status=='1'){
                        $is_checked = 'checked="checked"';
                        $fix_paid = '';
                        $lbl_dissable = 'style="pointer-events:none"';
                    }else{
                        $is_checked = '';
                        $fix_paid = 'credit_paid'; 
                        $lbl_dissable = '';
                    }
                    
                    // $nestedData['action'] = '<div class="d-flex align-items-center justify-content-center">
                    //                         '.$action.'
                    //                         <label class="button-switch-sm ml-3" '.$lbl_dissable.'>
                    //                            <input type="checkbox" name="credit_paid" class="switch switch-orange '.$fix_paid.'" '.$is_checked.' data-order_id="'.$cr_request->getOrderDetail->id.'">                        
                    //                              <span for="autoRelated" class="lbl-off">'.Lang::get('customer.paid').'</span>
                    //                              <span for="autoRelated" class="lbl-on">'.Lang::get('customer.paid').'</span>
                    //                        </label>     
                    //                    </div>';
                   $nestedData['action'] = '<div class="d-flex align-items-center justify-content-center">
                        '.$action.'</div>';
                }else{
                    $nestedData['action'] = $action;
                }
                
                $data[] = $nestedData;
            }
        }
          
        $json_data = array(
                    "draw"            => intval($draw),  
                    "recordsTotal"    => intval($shopOrderList->total()),  
                    "recordsFiltered" => intval($shopOrderList->total()), 
                    "data"            => $data   
                    );
            
        return $json_data;
    }

    public function paidCredit(Request $request){
        try {
            $shopData = $this->getSellerShop();
            $orderDetailData = \App\OrderDetail::where(['user_id'=>$request->user_id,'payment_type'=>'credit','credit_paid_status'=>null,'shop_id'=>$shopData->id]);

            if(!empty($request->order_id)){
                $orderDetailData->where('id',$request->order_id);
            }

            $orderDetails = $orderDetailData->get();
            $total_paid_credit = 0;
            foreach ($orderDetails as $key => $order) {
                $order->credit_paid_status = '1';
                $order->save();
                $total_paid_credit += $order->total_price;
            }
            
            $userCredit = \App\Credits::where(['user_id'=>$request->user_id,'seller_approval'=>'Approved','shop_id'=>$shopData->id])->first();

            $usedCreditAmount = $userCredit->used_amount - $total_paid_credit;
            $remaining_amount = $userCredit->remaining_amount + $total_paid_credit;
            if($usedCreditAmount==0){
                $userCredit->amount_paid = '1';
            }

            $userCredit->used_amount = ($usedCreditAmount > 0)?$usedCreditAmount:0;
            $userCredit->remaining_amount = ($remaining_amount < $userCredit->credited_amount)?$remaining_amount:$userCredit->credited_amount;
            
            $userCredit->save();

            $return = ['status'=>'success','message'=>Lang::get('shop.credit_paid_updated_successfully')];
        }
        catch(Exception $e){
            $return = ['status'=>'error','message'=>$e->getMessage()];
        }

        return $return;
        
    }

   
}