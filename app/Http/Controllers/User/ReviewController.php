<?php

namespace App\Http\Controllers\User;

use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MarketPlace;
use DB;
Use Auth;
use Lang;
use Validator;

class ReviewController extends MarketPlace
{
    public function __construct() {
        $this->middleware('authenticate');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
            


        return view('user.review_rating');    

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       
        $input = $request->all();
        $rules['rating'] = 'Required';
        
        $error_msg['rating.required']=Lang::get('product_review.rating_is_required');
        $validator = Validator::make($input, $rules, $error_msg);

        if($validator->fails()){
            $errors =  $validator->errors(); 
            return array('status'=>'error','message'=>$errors);
        }
        $product_id = $request->product_id;
        $product_review = new \App\ProductReview;
        $product_review->order_id = $request->order_id;
        $product_review->user_id = Auth::id();
        $product_review->shop_id = $request->shop_id;
        $product_review->product_id = $product_id;
        $product_review->rating = $request->rating;
        $product_review->review = cleanValue($request->review);
        $product_review->status = 1;
        $product_review->save();

        /****update rating*****/
        $update_rating = \App\ProductReview::updateReview($product_id,$request->shop_id);

        if(empty($request->rating)){
            $rating = 0;
        }else{
            $rating = $request->rating*20;
        }

        return ['status'=>'success','mesg'=>Lang::get('common.successfully_added'),'rating'=>$rating,'review'=>$request->review];



    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function getOrderedProductList(Request $request){

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
        
        //$data = \App\OrderDetail::where('status',3)->with('order')->with(['getShop','getShopDesc','getCat'])->paginate($length)->toArray();
            

        $data = DB::table(with(new \App\OrderDetail)->getTable().' as ordd')
            ->join(with(new \App\Order)->getTable().' as ord', 'ord.id', '=', 'ordd.order_id')
            ->leftjoin(with(new \App\ShopDesc)->getTable().' as sd', 'ordd.shop_id', '=', 'sd.shop_id')
            ->leftjoin(with(new \App\ProductReview)->getTable().' as rev',[
                ['rev.order_id','=','ordd.order_id'],
                ['rev.product_id','=','ordd.product_id']
            ])
            ->select('ord.formatted_id','ord.created_at','sd.shop_name','ordd.quantity','ordd.order_detail_json','ordd.package_name','rev.rating','rev.review','rev.id as review_id','ordd.shop_id','ordd.product_id','ord.id as order_id','ordd.sku')
            ->where(['sd.lang_id'=>session('default_lang'),'ordd.user_id'=>Auth::id()])
            ->where('ordd.status',3)
            ->groupBy('ordd.product_id','ordd.order_id')
            ->orderBy('ord.created_at', $dir)->paginate($length);

        $required_data = [];
        if(count($data)){
            foreach ($data as  $key => $value) {
                $detail_json = json_decode($value->order_detail_json,true);
                $product_name = '';
                $product_url = '';
                $package_name = '';
                if(!empty($detail_json)){
                    $product_name = $detail_json['name'][session('default_lang')] ?? $value->category_name;  
                    $package_name = $detail_json['package'][session('default_lang')] ?? $value->package_name;
                    $cat_url= isset($detail_json['cat_url']) ?  $detail_json['cat_url'] : '';
                    $product_url = action('ProductDetailController@display',['cat_url'=>$cat_url,'sku'=>$value->sku]);
                    $product_name = '<a href="'.$product_url.'" >'.$product_name.'</a>';
                }
                
                $rating = $value->rating ;
                $review = $value->review ; 
                if(is_null($value->review) && is_null($value->rating)){
                    $rating = '<span id="rating-'.$value->product_id.'-'.$value->order_id.'" ><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#reviewmodel" onclick="setData('.$value->order_id.','.$value->shop_id.','.$value->product_id.');"> '.Lang::get('common.add_review').' </button></span>';
                    $review = '<span id="review-'.$value->product_id.'-'.$value->order_id.'"></span>';
                }
                else{
                    $rating = $value->rating*20;
                    $rating = '<div class="review-star" >
                                    <div class="grey-stars"></div>
                                    <div class="filled-stars" style="width: '.$rating.'%"></div>
                                </div>';
                }
                $shop_url= isset($detail_json['shop_url']) ?  $detail_json['shop_url'] : '';
                $shop_name = '<a href="'.action('ShopController@index',['shop'=>$shop_url]).'">'.$value->shop_name.'</a>';


                $required_data[] =[
                    "order_number"=>$value->formatted_id,
                    "shop_name"=>$shop_name,
                    "product_name"=>$product_name,
                    "quantity"=>$value->quantity." ".$package_name,
                    "rating"=>$rating,
                    "review"=>$review,
                    "order_date"=>getDateFormat($value->created_at,8)
                    
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


    
}
