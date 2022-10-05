<?php

namespace App\Http\Controllers\Admin\Review;

use Illuminate\Http\Request;
use App\Http\Controllers\MarketPlace;
use DB;
use Lang;
use Config;

class ReviewController extends Marketplace
{
    
	public function __construct(){
        $this->middleware('admin.user');

    }


    public function showProductReview(Request $request){

    	$filter = $this->getFilter('review');
    	return view('admin.review.review_list',['filter'=>$filter]);
    }

    public function getProductReviews(Request $request){
    	$perpage = !empty($request->pq_rpp) ? $request->pq_rpp : 10;
        $request->page = $current_page = !empty($request->pq_curpage)?$request->pq_curpage:0;

        $start_index = ($current_page - 1) * $perpage;
        $prefix =  DB::getTablePrefix();
        //dd($perpage,$request->page);
        $order_by = 'id';
        $order_by_val = 'desc';
        if(isset($request->pq_sort)){
            $sort_data = jsonDecodeArr($request->pq_sort);
            $order_by = $sort_data[0]['dataIndx'];
            $order_by_val = ($sort_data[0]['dir']=='up')?'asc':'desc';
        }else{
            $order_by = 'rev.created_at';
            $order_by_val = 'desc';
        }

        try{

            $query = DB::table(with(new \App\OrderDetail)->getTable().' as ordd')
            ->join(with(new \App\Order)->getTable().' as ord', 'ord.id', '=', 'ordd.order_id')
            ->leftjoin(with(new \App\ShopDesc)->getTable().' as sd', 'ordd.shop_id', '=', 'sd.shop_id')
            ->leftjoin(with(new \App\ProductReview)->getTable().' as rev',[
                ['rev.order_id','=','ordd.order_id'],
                ['rev.product_id','=','ordd.product_id']
            ])
            ->join(with(new \App\Shop)->getTable().' as shop','shop.id','=','ordd.shop_id')
            ->join(with(new \App\User)->getTable().' as usr','shop.user_id','=','usr.id')
            ->join(with(new \App\User)->getTable().' as usrr','usrr.id','=','ordd.user_id')
            ->select('ord.formatted_id','rev.created_at','sd.shop_name','sd.shop_name','ordd.quantity','ordd.order_detail_json','rev.rating','rev.review','rev.id as review_id','ordd.shop_id','ordd.product_id','ord.id as order_id','ord.created_at as ordered_date','rev.seller_mesg','rev.seller_attachment','ordd.order_shop_id','ordd.sku','ordd.quantity','ordd.user_id',DB::raw("CONCAT(".$prefix."usr.first_name,' ',".$prefix."usr.middle_name,' ',".$prefix."usr.last_name) as seller_name"),DB::raw("CONCAT(".$prefix."usrr.first_name,' ',".$prefix."usrr.middle_name,' ',".$prefix."usrr.last_name) as buyer_name"),'ordd.total_price','rev.is_deleted','ordd.id')
            ->where(['sd.lang_id'=>session('default_lang')]);

            $query = $query->where('ordd.status',3);

            if($request->page_type =='reported_review'){
                $query->where('rev.seller_mesg','!=',null);
            }



            if(isset($request->pq_filter)){
                $filter_req = json_decode($request->pq_filter,true);
                if(!empty($filter_req['data'])){
                    $filter_arr = $filter_req['data'];
                    foreach ($filter_arr as $fkey => $fvalue) {
                        $searchval = $fvalue['value'];
                        switch ($fvalue['dataIndx']) {
                            case 'order_shop_id': 
                                $query->where('ordd.order_shop_id','like','%'.$searchval.'%');
                                break;
                            case 'sku': 
                                $query->where('ordd.sku','like','%'.$searchval.'%');
                                break;
                            case 'status':
                                if(in_array('all',$fvalue['value'])){



                                }elseif(in_array('deleted',$fvalue['value']) && !in_array('completed', $fvalue['value']) && !in_array('pending', $fvalue['value'])){

                                    $query->where('rev.is_deleted','1');



                                }elseif(in_array('completed',$fvalue['value']) && !in_array('deleted', $fvalue['value']) && !in_array('pending', $fvalue['value'])){
                                    $query->where('rev.rating','>',0);

                                }elseif(in_array('pending',$fvalue['value']) && !in_array('completed', $fvalue['value']) && !in_array('deleted', $fvalue['value'])){

                                     $query->where('rev.rating',null);
                                }

                                break;

                        }
                    }
                }
            }


            $response = $query->orderBy($order_by,$order_by_val)->paginate($perpage,['*'],'page',$current_page);
            $totrec = $response->total();
            if($start_index >= $totrec) {
                $current_page = ceil($totrec/$perpage);                
                $response = $query->orderBy($order_by,$order_by_val)->paginate($perpage,['*'],'page',$current_page);
            }

            if(count($response)){
               $response = $response->toArray();
               foreach ($response['data'] as $key => $value) {
                    $detail_json = json_decode($value->order_detail_json,true);
                    $product_name = '';
                    $unit_name = '';
                    if(!empty($detail_json)){
                        $product_name = $detail_json['name'][session('default_lang')] ?? $value->category_name;  
                        $unit_name = $detail_json['unit'][session('default_lang')] ?? '';
                    }
                $response['data'][$key]->product_name = $product_name;
                //$response['data'][$key]->unit_name = $unit_name;
                $response['data'][$key]->quantity = $value->quantity.' '.$unit_name;
                if(is_null($value->rating))
                    $response['data'][$key]->status = 'Pending';
                elseif($value->is_deleted=='1'){
                    $response['data'][$key]->status = 'Deleted';
                }else{
                    $response['data'][$key]->status = 'Completed';
                }
                
                $response['data'][$key]->created_at = getDateFormat($value->created_at,4); 
                if(!empty($value->seller_attachment))
                    $response['data'][$key]->seller_attachment = Config::get('constants.review_complain_file_url').$value->seller_attachment;
               }
            }




        // $required_data =[];

        // if(count($data)){
            // foreach ($data as  $key => $value) {


            //     $detail_json = json_decode($value->order_detail_json,true);
            //     $product_name = '';
            //     $unit_name = '';
            //     if(!empty($detail_json)){
            //         $product_name = $detail_json['name'][session('default_lang')] ?? $value->category_name;  
            //         $unit_name = $detail_json['unit'][session('default_lang')] ?? $value->unit_name;

            //         $standard = '';
            //         if(isset($detail_json['badge']['size'])){
            //             $standard = getBadgeImageUrl($detail_json['badge']['icon']); 
            //             $standard = "<span class='la'><img src='".$standard."'></span>";
            //         }
            //         $thumbnail_image = '';
            //         if(isset($detail_json['thumbnail_image'])){
                        
            //             $product_img = getProductImageUrlRunTime($detail_json['thumbnail_image'],'thumb_50x50');
            //             $product_img = '<div class="dbox-flex"><span class="prod-img pr-2"><img src="'.$product_img.'"  alt=""></span><span class="prod-name">ส้มเขียวหวาน</span></div>';
            //         }

                    

            //     }
                
            //     $rating = $value->rating ;
            //     $review = $value->review ; 
            //     if(is_null($value->review) && is_null($value->rating)){
            //         // $rating = '<span id="rating-'.$value->product_id.'-'.$value->order_id.'" ><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#reviewmodel" onclick="setData('.$value->order_id.','.$value->shop_id.','.$value->product_id.');"> Add Review </button></span>';
            //         // $review = '<span id="review-'.$value->product_id.'-'.$value->order_id.'"></span>';
            //     }
            //     else{
            //         $rating = $value->rating*20;
            //         $rating = '<div class="review-star" >
            //                         <div class="grey-stars"></div>
            //                         <div class="filled-stars" style="width: '.$rating.'%"></div>
            //                     </div>';
            //     }

            //     if(is_null($value->seller_mesg)){
            //         $action = '<a href="'.action('Seller\ReviewController@reportReview',['order_id'=> $value->order_id,'product_id'=>$value->product_id]).'"  class="btn btn-grey float-right">'.Lang::get('review.report_to_admin').'</a>';
            //     }else{
            //         $action = '<span>'.Lang::get('review.reported').'</span>';
            //     }
                


            //         $required_data[] =[
            //             "order_number"=>$value->formatted_id,
            //             "shop_name"=>$value->shop_name,
            //             "product_name"=>$product_img,
            //             "quantity"=>$value->quantity." ".$unit_name,
            //             "rating"=>$rating,
            //             "review"=>$review,
            //             "standard"=>$standard, 
            //             "review_date"=>getDateFormat($value->created_at,4),
            //             "ordered_date"=>getDateFormat($value->ordered_date),
            //             "action"=>$action
            //         ];
            //     }
            // }


            //$this->setFilter('review',$request);

        }catch(QueryException $e){
            $response = ['status'=>'fail','msg'=>$e->getMessage()];
        }
        
        return $response;




    } 

    public function showReportedProductReview(){
        $filter = $this->getFilter('review');
    	return view('admin.review.reported_review',['filter'=>$filter]);
    }

    public function destroy($id){
        //$permission = $this->checkUrlPermission('delete_product');
        $result = \App\ProductReview::find($id); 
        if (!$result) {
            abort(404);
        }
        try{
            $result->is_deleted='1';
            $result->save();
            $msg_text = Lang::get('product.review_delete_successfully');

            /****update rating*****/
            $update_rating = \App\ProductReview::updateReview($result->product_id,$result->shop_id);
            return redirect()->back()->with('succMsg', $msg_text);  
        }catch(Exception $e) {


        }
    }
}
