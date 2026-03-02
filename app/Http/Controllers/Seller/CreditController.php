<?php
namespace App\Http\Controllers\Seller;

use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request;
use App\Http\Requests;
use Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\MarketPlace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use DateTime;
use Session;
use Route;
use Cache;
use App\Helpers\GeneralFunctions;
use Lang;
use Config;
use Exception;

class CreditController extends MarketPlace
{
    /**
     * Show the manage shop .
     *
     * @return \Illuminate\Http\Response
     */
    
    public function __construct()
    {
        //$this->middleware('auth');
        $this->middleware('authenticate');
        
    }

    public function index(Request $request){
        $shopData = $this->getSellerShop();
        $shop_id = isset($shopData->id)?$shopData->id:0;

        $total_credit_request = \App\Credits::where('shop_id',$shop_id)
        ->where('seller_approval','Pending')->count();
        
        return view('seller.manage_credit',['total_credit_request'=>$total_credit_request]);
    }

    public function getAllOverdueCredits(Request $request){
        $column = ['customer_name','last_credit_use','status','payment_period','action'];
        $draw        = $request->draw;
        $start       = $request->start; //Start is the offset
        $length      = $request->length; //How many records to show
        $column      = $request->order[0]['column']; //Column to orderBy
        $dir         = $request->order[0]['dir']; //Direction of orderBy
        $searchValue = $request->search['value']; //Search value
        
        //Sets the current page
        Paginator::currentPageResolver(function () use ($start, $length) {
            return ($start / $length + 1);
        });

        $shopData = $this->getSellerShop();
        $shop_id = isset($shopData->id)?$shopData->id:0;

        $ordersSql = \App\OrderShop::where('order_shop.shop_id',$shop_id)
                    ->leftJoin(with(new \App\User)->getTable().' as u', 'order_shop.user_id', '=', 'u.id')
                    ->leftJoin(with(new \App\Credits)->getTable().' as c', 'order_shop.user_id', '=', 'c.user_id')
                    ->where('c.shop_id',$shop_id)
                    ->select('order_shop.*','u.display_name','u.image','c.amount_paid');

        if(!empty($searchValue)){
            $ordersSql->where('u.display_name', 'LIKE',"%{$searchValue}%");
        }

        $allOrders = $ordersSql->orderBy('order_shop.shop_formatted_id', $dir)->paginate($length);
        
        $data = array();
        if(!empty($allOrders))
        {
            foreach ($allOrders as $order)
            {
                $customer_name =   "<div class='user-wraps'><span class='circle-img'><img src='".getUserImageUrl($order->image)."' width='50' alt=''></span><span class='prod-name'> ".$order->display_name." </span></div>";

                $view_detail_url = action('Seller\CustomerController@details',['id'=>$order->user_id]);
                $order_detail_url = action('Seller\OrderController@details',['id'=>$order->shop_formatted_id]);
                $dueDate = getDateFormat($order->credit_due_date,8);
                $nestedData['customer_name'] = $customer_name;
                $nestedData['order_number'] = "<a href='".$order_detail_url."' class='skyblue'>".$order->shop_formatted_id."</a>";
                $nestedData['last_credit_use'] = "<div class='last-credit'><span>".numberFormat($order->total_credit_amount)." ".Lang::get('common.baht')."</span><div class='use-lastcredit'> ".Lang::get('shop.use_the_last_credit')." ".getDateFormat($order->end_shopping_date,8)."</div></div>";
                $nestedData['status'] = ($order->amount_paid=='1')?'<span class="green">'.Lang::get('shop.paid').'</span>':'<span class="red">'.Lang::get('shop.unpaid').'</span>';
                $nestedData['payment_period'] = $dueDate;
                $nestedData['action'] = "<a href='".$view_detail_url."' class='skyblue'>".Lang::get('shop.detail')."</a>";
                $data[] = $nestedData;
            }
        }
        
         $json_data = array(
                    "draw"            => intval($draw),  
                    "recordsTotal"    => intval($allOrders->total()),  
                    "recordsFiltered" => intval($allOrders->total()), 
                    "data"            => $data   
                    );
            
        echo json_encode($json_data); 
    
    }

    public function getAllCredits(Request $request){
        $draw        = $request->draw;
        $start       = $request->start; //Start is the offset
        $length      = $request->length; //How many records to show
        $column      = $request->order[0]['column']; //Column to orderBy
        $dir         = $request->order[0]['dir']; //Direction of orderBy
        $searchValue = $request->search['value']; //Search value
        
        //Sets the current page
        Paginator::currentPageResolver(function () use ($start, $length) {
            return ($start / $length + 1);
        });

        $shopData = $this->getSellerShop();
        $shop_id = isset($shopData->id)?$shopData->id:0;

        $credits = \App\Credits::where('credits.shop_id',$shop_id)
        ->where('credits.seller_approval','Approved')
        ->leftJoin(with(new \App\User)->getTable().' as u', 'credits.user_id', '=', 'u.id')
        ->select('credits.*','u.display_name','u.image');
        if(!empty($searchValue)){
            $credits->where('u.display_name', 'LIKE',"%{$searchValue}%");
        }

        $creditList = $credits->orderBy('credits.created_at', $dir)->paginate($length);

        $data = array();
        if(!empty($creditList))
        {
            foreach ($creditList as $credit)
            {
                $customer_name =   "<div class='user-wraps'><span class='circle-img'><img src='".getUserImageUrl($credit->image)."' width='50' alt=''></span><span class='prod-name'> ".$credit->display_name." </span></div>";

                $lastOrder = \App\OrderShop::where('order_shop.user_id',$credit->user_id)->orderBy('end_shopping_date','desc')
                ->leftjoin(with(new \App\OrderDetail)->getTable().' as od','order_shop.order_id','=', 'od.order_id')
                ->where('od.payment_slug','credit')
                ->select('order_shop.total_credit_amount','order_shop.end_shopping_date')
                ->first();

                if(is_null($lastOrder)){
                    $last_credit_use = 0;
                    $last_credit_use_date = 'NA';

                    $nestedData['last_credit_use'] = "<div class='last-credit'><span> - </span><div class='use-lastcredit'></div></div>";

                    $nestedData['payment_period'] = " - ";
                }else{
                    $last_credit_use = $lastOrder->total_credit_amount;
                    $last_credit_use_date = $lastOrder->end_shopping_date;

                    $nestedData['last_credit_use'] = "<div class='last-credit'><span>".numberFormat($last_credit_use)."</span><div class='use-lastcredit'> ".Lang::get('shop.use_the_last_credit')." ".getDateFormat($last_credit_use_date,8)."</div></div>";

                    $from = new DateTime($credit->credit_due_date);
                    $to = new DateTime();
                    $remaining_time = '';
                    if($from>$to){
                        $diff = $to->diff($from);
                        if($diff->y!=0){
                            $remaining_time .= $diff->y." Year ";
                        }
                        if($diff->m!=0){
                            $remaining_time .= $diff->m." Month ";
                        }
                        $remaining_time .= $diff->d." Days ".$diff->h. " hours ".$diff->i. " minutes ".$diff->s. " seconds" ;
                    }else{
                        $remaining_time .= "Credit due date over.";
                    }

                    $nestedData['payment_period'] = $remaining_time;
                }
                
                $view_detail_url = action('Seller\CustomerController@details',['id'=>$credit->user_id]);

                $nestedData['customer_name'] = $customer_name;
                $nestedData['credit_limit'] = "<div class='last-credit'><span>".numberFormat($credit->credited_amount)."</span><div class='use-lastcredit'> ".Lang::get('shop.use_the_last_credit')." ".getDateFormat($credit->updated_at,8)."</div></div>";
                $nestedData['credit_balance'] = "<div class='last-credit'><span class='green'>".numberFormat($credit->remaining_amount)."</span></div>";
                

                
                $nestedData['action'] = "<div class='view-action'>
                                                        <div class='d-flex align-items-center'>
                                                           <a href='".$view_detail_url."' class='link'>".Lang::get('shop.detail')."</a>
                                                           <button class='btn remove_credit' id='".$credit->id."'>".Lang::get('shop.remove_credit')."</button>
                                                       </div>
                                                     </div>";
                $data[] = $nestedData;
            }
        }
        
         $json_data = array(
                    "draw"            => intval($draw),  
                    "recordsTotal"    => intval($creditList->total()),  
                    "recordsFiltered" => intval($creditList->total()), 
                    "data"            => $data   
                    );
            
        echo json_encode($json_data); 
    }

    

    public function getCreditsRequest(Request $request){
        $draw        = $request->draw;
        $start       = $request->start; //Start is the offset
        $length      = $request->length; //How many records to show
        $column      = $request->order[0]['column']; //Column to orderBy
        $dir         = $request->order[0]['dir']; //Direction of orderBy
        $searchValue = $request->search['value']; //Search value
        
        //Sets the current page
        Paginator::currentPageResolver(function () use ($start, $length) {
            return ($start / $length + 1);
        });

        $shopData = $this->getSellerShop();
        $shop_id = isset($shopData->id)?$shopData->id:0;

        $credits = \App\Credits::where('credits.shop_id',$shop_id)
        ->where('credits.seller_approval','Pending')
        ->leftJoin(with(new \App\User)->getTable().' as u', 'credits.user_id', '=', 'u.id')
        ->select('credits.*','u.display_name','u.image','u.email','u.ph_number');

        if(!empty($searchValue)){
            $credits->where('u.display_name', 'LIKE',"%".$searchValue."%");
            $credits->orWhere('credits.user_designated_name', 'LIKE',"%".$searchValue."%");
            $credits->orWhere('u.email', 'LIKE',"%".$searchValue."%");
            $credits->orWhere('u.ph_number', 'LIKE',"%".$searchValue."%");
        }
        //DB::enableQueryLog();
        $creditList = $credits->orderBy('credits.created_at', $dir)->paginate($length);
        //dd(DB::getQueryLog());

        //dd($creditList);
        $data = array();
        if(!empty($creditList))
        {
            foreach ($creditList as $credit)
            {
                $img_url = getUserImageUrl($credit->image);
                $customer_name =   "<div class='user-wraps'><span class='circle-img'><img src='".$img_url."' width='50' alt=''></span><span class='prod-name'> ".$credit->display_name." </span></div>";

                $view_history_url = action('Seller\CustomerController@details',['id'=>$credit->user_id]);
                
                $paymentOptions = $this->getPaymentOptions();
                $select_option = "";
                foreach($paymentOptions as $p_option){
                    $select_option .= '<option value="'.$p_option->value.'">'.$p_option->name.'</option>';
                }
                $nestedData['customer_name'] = $customer_name;
                $nestedData['designated_name'] = ($credit->user_designated_name!='')?$credit->user_designated_name." <a href='javascript://' class='link' data-toggle='modal' data-target='#editNickName' data-id='".$credit->id."'>".Lang::get('shop.edit_designated_name')."</a>":"<a href='javascript://' class='link' data-toggle='modal' data-target='#editNickName' data-id='".$credit->id."'>".Lang::get('shop.create_designated_name')."</a>";

                $nestedData['email'] = $credit->email;
                $nestedData['telephone'] = $credit->ph_number;
                $nestedData['credit_request_date'] = getDateFormat($credit->created_at,8);
                $nestedData['action'] = "<div class='view-action'>
                                            <div class='d-flex align-items-center'>
                                               <a href='".$view_history_url."' class='link'>".Lang::get('shop.view_history')."</a>
                                               <button class='btn give_credit' data-toggle='modal' data-target='#giveCredit' data-select_options='".$select_option."' data-customer_name='".$credit->display_name."' data-customer_email='".$credit->email."' data-id='".$credit->id."' data-image='".$img_url."'>".Lang::get('shop.give')."</button>
                                               <button class='btn reject_credit' id='".$credit->id."'>".Lang::get('shop.reject')."</button>
                                            </div>
                                        </div>";
                $data[] = $nestedData;
            }
        }
        
         $json_data = array(
                    "draw"            => intval($draw),  
                    "recordsTotal"    => intval($creditList->total()),  
                    "recordsFiltered" => intval($creditList->total()), 
                    "data"            => $data   
                    );
            
        echo json_encode($json_data); 
    }

    public function editBuyerNickName(Request $request){
        $message = Lang::get('shop.something_went_wrong');
        $status = 'error';
        try{
            $creditData = \App\Credits::find($request->id);
            if($creditData!=null){
                $creditData->user_designated_name = $request->user_designated_name;
                if($creditData->save()){
                    $message = Lang::get('shop.user_designated_name_updated_successfully');
                    $status = 'success';
                }
            }
        }
        catch(Exception $e){
            $message = $e->getMessage();
        }

        return ['status'=>$status,'message'=>$message];
    }

    public function giveCredit(Request $request){
        $message = Lang::get('shop.something_went_wrong');
        $status = 'error';
        try{
            $creditData = \App\Credits::find($request->id);
            if($creditData!=null){
                $remaining_amount = ($request->credited_amount - $creditData->used_amount);
                $creditData->credited_amount = $request->credited_amount;
                $creditData->remaining_amount = $remaining_amount;
                $creditData->seller_approval = 'Approved';
                $creditData->payment_period = $request->payment_period;
                
                if($creditData->save()){
                    $message = Lang::get('shop.give_credit_successfull');
                    $status = 'success';
                }
            }
        }
        catch(Exception $e){
            $message = $e->getMessage();
        }

        return ['status'=>$status,'message'=>$message];
    }

    public function manageCreditAjaxRequest(Request $request){
        
        $shopData = $this->getSellerShop();
        try{
            switch ($request->action) {
                case 'remove_credit':
                    $status = "Removed";
                    $reqSta = "Approved";
                    $message = Lang::get('shop.credit_removed_successfull');
                break;
                case 'reject_credit':
                    $status = "Rejected";
                    $reqSta = "Pending";
                    $message = Lang::get('shop.credit_rejected_successfull');
                break;
            }
            $creditData = \App\Credits::where(['id'=>$request->id,'shop_id'=>$shopData->id,'seller_approval'=>$reqSta])->first();
            //dd($request->id,$shopData->id,$reqSta);
            //dd($request->all());
            if($creditData!=null){
                $creditData->seller_approval = $status;
                if($creditData->save()){
                    $st = 'success';
                }
            }else{
                $st = Lang::get('shop.something_went_wrong');
            }
        }
        catch(Exception $e){
            dd($e);
            $message = $e->getMessage();
            $st = 'error';
        }

        return ['status'=>$st,'message'=>$message];
    }

    public function willCreditRemove(Request $request){

        $message = Lang::get('shop.something_went_wrong');
        $status = 'error';
        $title = Lang::get('shop.confirm_popup_title');
        try{
            $shopData = $this->getSellerShop();
            //dd($shopData);
            $shop_id = $shopData->id;
            $creditData = \App\Credits::where(['id'=>$request->id,'shop_id'=>$shop_id,'seller_approval'=>'Approved'])->first();
            
            if($creditData!=null){
                if(($creditData->credited_amount==$creditData->remaining_amount && $creditData->used_amount==0) || ($creditData->amount_paid=='1')){
                    $message = Lang::get('shop.overdue_paid_and_can_remove_credit');
                    $status = 'remove';
                }else{
                    $status = 'overdue';
                    $message = Lang::get('shop.credit_overdues_not_paid');
                }
            }
        }
        catch(Exception $e){
            // echo $e; die;
            $message = $e->getMessage();
        }

        return ['status'=>$status,'message'=>$message,'title'=>$title];
    }

    public function manageUserCredit(Request $request){
        $shopData = $this->getSellerShop();
        $paymentOptions = $this->getPaymentOptions();
        $creditData = \App\Credits::where(['user_id'=>$request->uid,'shop_id'=>$shopData->id,'seller_approval'=>'Approved'])->with('getUser')->first();
        //dd($creditData);
        return view('seller.manage_user_credit',['creditData'=>$creditData,'paymentOptions'=>$paymentOptions]);
    }

    protected function getPaymentOptions(){
        $paymentOptions = \App\PaymentPeriods::select('name','value')->where('status','1')->get();

        return $paymentOptions;
    }
}
