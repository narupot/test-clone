<?php

namespace App\Http\Controllers\Seller;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use App\Http\Controllers\MarketPlace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


use Session;
use DB;
use Lang;
use Validator;
use Config;



class ReviewController extends MarketPlace
{
    public function __construct()
    {
        $this->middleware('authenticate');
    }


    public function index(Request $request){
    	$user_id = Auth::id();
    	$shop_id = Session::get('user_shop_id');
    	//$total_review = \App\ProductReview::where('shop_id',$shop_id)->count();
    	$review_data = \App\ProductReview::where('shop_id',$shop_id)->groupBy('rating')->selectRaw("count('user_id') as buyer_count,rating")->orderBy('rating','desc')->where('is_deleted','!=','1')->get();
        
   	
    	$rating_data = [];
    	$total_reviewer = 0;
    	$total_review = 0;
    	$total_reviewer_review = 0;
    	$star_reviews = [];
    	foreach ($review_data as $key => $unit_review) {
    		$star_reviews[$unit_review->rating] = $unit_review->buyer_count;
    		$total_reviewer += $unit_review->buyer_count;
    		$total_reviewer_review += $unit_review->buyer_count * $unit_review->rating;
    		$total_review += $unit_review->buyer_count*5;
    	}

    	for ($i=5;$i>0;--$i) {
    		if(!isset($star_reviews[$i])){
    			$star_reviews[$i] = 0;
    		}
    	}
    	
    	krsort($star_reviews);
    	
    	if($total_review>0){
    		$shop_percent_review = round(($total_reviewer_review*100)/$total_review);	
    	}else{
    		$shop_percent_review = 0;
    	}
    	

    	$rating_data['total_reviews'] = $total_reviewer;
    	$rating_data['total_percent'] = $shop_percent_review;
    	$rating_data['star_reviews'] = $star_reviews;    	
        $shop_details = \App\Shop::where('user_id', Auth::id())->first();
    	return view('seller.rating',['rating_data'=>$rating_data,'shop_details'=>$shop_details]);

    }

    public function getProductRatings(Request $request){
    	$shop_id = Session::get('user_shop_id');
    	$table_type  = $request->table;
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

    	$data = DB::table(with(new \App\OrderDetail)->getTable().' as ordd')
            ->join(with(new \App\Order)->getTable().' as ord', 'ord.id', '=', 'ordd.order_id')
            ->leftjoin(with(new \App\ShopDesc)->getTable().' as sd', 'ordd.shop_id', '=', 'sd.shop_id')
            ->leftjoin(with(new \App\ProductReview)->getTable().' as rev',[
                ['rev.order_id','=','ordd.order_id'],
                ['rev.product_id','=','ordd.product_id']
            ])
            ->select('ord.formatted_id','rev.created_at','sd.shop_name','ordd.quantity','ordd.order_detail_json','ordd.package_name','rev.rating','rev.review','rev.id as review_id','ordd.shop_id','ordd.product_id','ord.id as order_id','ord.created_at as ordered_date','rev.seller_mesg','rev.is_deleted')
            ->where(['sd.lang_id'=>session('default_lang')])
            ->where('ordd.shop_id', $shop_id)
            ->where('ordd.status',3)
            ->groupBy('ordd.product_id','ordd.order_id');

            if($table_type=='reviewed'){
            	$data->where('rev.id','!=',null);
            }
            if($table_type=='not-reviewed'){
            	$data->where('rev.id',null);
            }

            $data = $data->orderBy('ord.created_at', $dir)->paginate($length);

        $required_data =[];

        if(count($data)){
            foreach ($data as  $key => $value) {


                $detail_json = json_decode($value->order_detail_json,true);
                $product_name = '';
                $package_name = '';
                if(!empty($detail_json)){
                    $product_name = $detail_json['name'][session('default_lang')] ?? $value->category_name;  
                    $package_name = $detail_json['package'][session('default_lang')] ?? $value->package_name;

                    $standard = '';
                    if(isset($detail_json['badge']['size'])){
                    	$standard = getBadgeImageUrl($detail_json['badge']['icon']); 
                    	$standard = "<span class='la'><img src='".$standard."'></span>";
                    }
                    $thumbnail_image = '';
                    if(isset($detail_json['thumbnail_image'])){
                    	
                    	$product_img = getProductImageUrlRunTime($detail_json['thumbnail_image'],'thumb_50x50');
                    	$product_img = '<div class="dbox-flex"><span class="prod-img pr-2"><img src="'.$product_img.'"  alt=""></span><span class="prod-name">'.$product_name.'</span></div>';
                    }
                }

                if($value->is_deleted=='1'){
                    $status = 'deleted';
                }else{
                    $status = '';
                }
                
                $rating = $value->rating ;
                $review = $value->review ; 
                if(is_null($value->review) && is_null($value->rating)){
                    // $rating = '<span id="rating-'.$value->product_id.'-'.$value->order_id.'" ><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#reviewmodel" onclick="setData('.$value->order_id.','.$value->shop_id.','.$value->product_id.');"> Add Review </button></span>';
                    // $review = '<span id="review-'.$value->product_id.'-'.$value->order_id.'"></span>';
                }
                else{
                    $rating = $value->rating*20;
                    $rating = '<div class="review-star" >
                                    <div class="grey-stars"></div>
                                    <div class="filled-stars" style="width: '.$rating.'%"></div>
                                </div>';
                }

                if(is_null($value->seller_mesg)){
                    $action = '<a href="'.action('Seller\ReviewController@reportReview',['order_id'=> $value->order_id,'product_id'=>$value->product_id]).'"  class="btn btn-grey">'.Lang::get('shop.report_to_admin').'</a>';
                }elseif($value->is_deleted=='1'){
                    $action = '<span class="btn-light-red">'.Lang::get('review.admin_deleted').'</span>';
                }else{
                    $action = '<span class="btn-green">'.Lang::get('review.reported').'</span>';
                }
                
                


                $required_data[] =[
                    "order_number"=>$value->formatted_id,
                    "shop_name"=>$value->shop_name,
                    "product_name"=>$product_img,
                    "quantity"=>$value->quantity." ".$package_name,
                    "rating"=>$rating,
                    "review"=>$review,
                    "standard"=>$standard, 
                    "review_date"=>getDateFormat($value->created_at,8),
                    "ordered_date"=>getDateFormat($value->ordered_date),
                    "action"=>$action
                ];
            }
        }
        
        $json_data = [
                "draw"            => intval($draw),  
                "recordsTotal"    => intval($data->total()),  
                "recordsFiltered" => intval($data->total()), 
                "data"            => $required_data   
            ];

        return $json_data;
    }

    public function reportReview(Request $request,$order_id,$product_id){
        

        $data = DB::table(with(new \App\OrderDetail)->getTable().' as ordd')
            ->join(with(new \App\OrderShop)->getTable().' as ords', 'ordd.order_shop_id', '=', 'ords.id')
            ->leftjoin(with(new \App\ProductReview)->getTable().' as rev',[
                ['rev.order_id','=','ordd.order_id'],
                ['rev.product_id','=','ordd.product_id']
            ])
            ->where('ordd.order_id',$order_id)
            ->where('ordd.product_id',$product_id)
            ->select('shop_formatted_id','ordd.order_detail_json','rev.rating','rev.review')->first();
        $detail_json = jsonDecodeArr($data->order_detail_json);

        $required_data = [];
        if(isset($data->shop_formatted_id)){
            $required_data['shop_order_id'] = $data->shop_formatted_id;
        }else{
            $required_data['shop_order_id'] = '';
        }
        $required_data['product_name']= $detail_json['name'][session('default_lang')]??$val->category_name ?? '';
        $required_data['product_id'] = $product_id;
        $required_data['order_id'] = $order_id;

        $required_data['rating'] = $data->rating*20;
        $required_data['review'] = $data->review;
        $required_data['product_img'] = getProductImageUrlRunTime($detail_json['thumbnail_image']??'','thumb');


        
        return view('seller.review_report_to_admin',['required_data'=>$required_data]);
    } 


    public function sendReport(Request $request){

        $file = $request->report_attachment;

        $data = $request->all();
        $rules= [
            'report_mesg'=>'required',
            'report_attachment'=>'required'
        ];

        $message = [
            'report_mesg.required'=>Lang::get('review.report_mesg_required'),
            'report_attachment.required'=>Lang::get('review.report_attachment_required'),

        ];

        $complain_file_path = Config::get('constants.review_complain_file_path');
        $validator = Validator::make($data,$rules,$message);
        
        if($validator->fails()){
            $arrayWithErrors = $validator->errors();
            return back()->withErrors($arrayWithErrors);
        }else{
            $file_name = Str::random(10).'-'.uniqid().'.'.$file->getClientOriginalExtension();
            $this->uploadImage($file_name, $file, $complain_file_path);
            $response = \App\ProductReview::where(['product_id'=>$request->product_id,'order_id'=>$request->order_id])->update(['seller_mesg'=>$request->report_mesg,'seller_attachment'=>$file_name]);
            if($response){
                return back()->with(['verify_msg'=>Lang::get('review.report_send_to_admin_successfully')]);
            }else{
                return back()->with(['not_verify_msg'=>Lang::get('review.something_wrong')]);
            }
        }
        // $file_name = Str::random(10).'-'.uniqid().'.'.$file->getClientOriginalExtension(); 
        // $this->uploadImage($file_name, $file, $complain_file_path);
        // $response = \App\ProductReview::where(['product_id'=>$request->product_id,'order_id'=>$request->order_id])->update(['seller_mesg'=>$request->report_mesg,'seller_attachment'=>$file_name]);
        // dd($response);
        
    }
}
