<?php

namespace App\Http\Controllers\User;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\MarketPlace;
use App\Helpers\GeneralFunctions;
use App\Helpers\CustomHelpers;
use Illuminate\Http\Request;
use Hash;
use DateTime;
use DB;
use Auth;
use Lang;
use Config;
use Session;
use Exception;

class CreditController extends MarketPlace
{   
    public function __construct() {
        $this->middleware('authenticate');
    }     

    public function index(Request $request){

        $creditRequets = \App\Credits::where(['user_id'=>Auth::user()->id])->with(['getShops'=>function($shopQuery){
                $shopQuery->with('shopDesc');
        }])->get();

        return view('user.credit_requests');
    } 

    public function getAllCredits(Request $request){
        $columns = ['shop_store','request_date','response_date','status'];
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

        $results = \App\Credits::where(['credits.user_id'=>Auth::user()->id,'credits.seller_approval'=>$request->status])
            ->join(with(new \App\Shop)->getTable().' as s', 'credits.shop_id', '=', 's.id')
            ->join(with(new \App\ShopDesc)->getTable().' as sd', 's.id', '=', 'sd.shop_id')
            ->select('credits.*', 's.shop_url','s.logo', 'sd.shop_name')
            ->where('sd.lang_id',session('default_lang'));

        if(!empty($searchValue)){
            $results->where('sd.shop_name', 'LIKE',"%{$searchValue}%");
        }
        //DB::enableQueryLog();
        $creditRequest = $results->orderBy('credits.created_at', $dir)->paginate($length);
        //dd(DB::getQueryLog());
        //dd($creditRequest);
        $data = array();
        if(!empty($creditRequest))
        {
            foreach ($creditRequest as $cr_request)
            {
                $store_html = "<div class='product-wrap'>
                                    <div class='prod-img'>
                                        <img src=".getImgUrl($cr_request->logo,'logo')." width='50' height='50'>                         
                                    </div>
                                    <div class='product-info'>
                                        <div class='shop-name'>
                                            <a href=".action('ShopController@index',['shop'=>$cr_request->shop_url]).">".$cr_request->shop_name."</a>
                                        </div>       
                                    </div>
                                </div>";
                if($cr_request->seller_approval=='Pending'){
                    $class = 'grey';
                }elseif($cr_request->seller_approval=='Approved'){
                    $class = 'green';
                }else{
                    $class = 'red';
                }

                $nestedData['shop_store'] = $store_html;
                $nestedData['status'] = "<span class='".$class."'>".$cr_request->seller_approval."</span>";
                $nestedData['request_date'] = getDateFormat($cr_request->created_at,8);
                $nestedData['response_date'] = getDateFormat($cr_request->updated_at,8);
                $data[] = $nestedData;
            }
        }
          
        $json_data = array(
                    "draw"            => intval($draw),  
                    "recordsTotal"    => intval($creditRequest->total()),  
                    "recordsFiltered" => intval($creditRequest->total()), 
                    "data"            => $data   
                    );
            
        echo json_encode($json_data); 
    }   

    public function creditBalance(Request $request){
        return view('user.credit_balance');
    }  

    public function creditUsage(Request $request){

        return view('user.credit_usage');
    }

    public function getAllCreditUsage(Request $request){
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
        $results = DB::table(with(new \App\OrderShop)->getTable().' as os')
            ->join(with(new \App\Shop)->getTable().' as s', 'os.shop_id', '=', 's.id')
            ->join(with(new \App\ShopDesc)->getTable().' as sd', 's.id', '=', 'sd.shop_id')
            ->select('os.*', 's.shop_url','s.logo', 'sd.shop_name')
            ->where(['sd.lang_id'=>session('default_lang'),'os.user_id'=>Auth::user()->id]);

        if(!empty($searchValue)){
            $results->where('sd.shop_name', 'LIKE',"%{$searchValue}%");
        }

        $shopOrderList = $results->orderBy('os.created_at', $dir)->paginate($length);
        $data = array();
        if(!empty($shopOrderList))
        {
            foreach ($shopOrderList as $cr_request)
            {
                $store_html = "<div class='product-wrap'>
                                    <div class='prod-img'>
                                        <img src=".getImgUrl($cr_request->logo,'logo')." width='50' height='50'>                         
                                    </div>
                                    <div class='product-info'>
                                        <div class='shop-name'>
                                            <a href=".action('ShopController@index',['shop'=>$cr_request->shop_url]).">".$cr_request->shop_name."</a>
                                        </div>      
                                    </div>
                                </div>";


                $from = new DateTime($cr_request->credit_due_date);
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
                    $remaining_time .= $diff->d." Days ".$diff->h. " hours ".$diff->i. " minutes ".$diff->s. " seconds Left" ;
                }else{
                    $remaining_time .= Lang::get('shop.credit_due_date_over');
                }
                
                $action = "<a class='skyblue' href='".action('User\OrderController@shopOrderDetails',['order_id'=>$cr_request->shop_formatted_id])."'>".Lang::get('shop.detail')."</a>";
                $nestedData['order_number'] = "<span class='skyblue'>".$cr_request->shop_formatted_id.'</span>';
                $nestedData['shop_store'] = $store_html;
                $nestedData['order_credit'] = "<span class='red'>".numberFormat($cr_request->total_credit_amount)."</span>";
                $nestedData['order_date'] = "<span class='gray'>".getDateFormat($cr_request->end_shopping_date,8)."</span>";
                $nestedData['due_date'] = "<div class='gray'>".getDateFormat($cr_request->credit_due_date,8)."</div><div class='red'>".$remaining_time."</div>";
                $nestedData['action'] = $action;
                $data[] = $nestedData;
            }
        }
          
         $json_data = array(
                    "draw"            => intval($draw),  
                    "recordsTotal"    => intval($shopOrderList->total()),  
                    "recordsFiltered" => intval($shopOrderList->total()), 
                    "data"            => $data   
                    );
            
        echo json_encode($json_data); 
    }

    public function getAllCreditBalance(Request $request){
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

        $results = \App\Credits::where('credits.user_id',Auth::user()->id)->where('credits.seller_approval','Approved')
            ->join(with(new \App\Shop)->getTable().' as s', 'credits.shop_id', '=', 's.id')
            ->join(with(new \App\ShopDesc)->getTable().' as sd', 's.id', '=', 'sd.shop_id')
            ->select('credits.*', 's.shop_url','s.logo', 'sd.shop_name')
            ->where('sd.lang_id',session('default_lang'));

        if(!empty($searchValue)){
            $results->where('sd.shop_name', 'LIKE',"%{$searchValue}%");
        }

        $creditRequest = $results->orderBy('credits.created_at', $dir)->paginate($length);

        $data = array();
        if(!empty($creditRequest))
        {
            foreach ($creditRequest as $cr_request)
            {
                $store_html = "<div class='product-wrap'>
                                    <div class='prod-img'>
                                        <img src=".getImgUrl($cr_request->logo,'logo')." width='50' height='50'>                         
                                    </div>
                                    <div class='product-info'>
                                        <div class='shop-name'>
                                            <a href=".action('ShopController@index',['shop'=>$cr_request->shop_url]).">".$cr_request->shop_name."</a>
                                        </div>      
                                    </div>
                                </div>";

                $remainig_credit = "<span>".(numberFormat($cr_request->remaining_amount))."</span>/<span class='grey'>".numberFormat($cr_request->credited_amount)." ".Lang::get('shop.currency')."</span>";

                $overdue_credit = "<span class='red'>".(numberFormat($cr_request->used_amount))." ".Lang::get('shop.currency')."</span>";
                $action = "<a class='skyblue' href='".action('ShopController@index',['shop'=>$cr_request->shop_url])."'>".Lang::get('shop.go_to_shop')."</a>";

                $nestedData['shop_store'] = $store_html;
                $nestedData['remaining_credits'] = $remainig_credit;
                $nestedData['overdue_credits'] = $overdue_credit;
                $nestedData['time_left_to_pay_credit'] = '';
                 $nestedData['action'] = $action;
                $data[] = $nestedData;
            }
        }
          
        $json_data = array(
                    "draw"            => intval($draw),  
                    "recordsTotal"    => intval($creditRequest->total()),  
                    "recordsFiltered" => intval($creditRequest->total()), 
                    "data"            => $data   
                    );
            
        echo json_encode($json_data);
    }
}