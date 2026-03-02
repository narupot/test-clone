<?php

namespace App\Http\Controllers\Seller;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

use App\ShopAssignCategory;
use App\Currency;
use App\Badge;
use App\ProductBargain;

use Config;
use Auth;
use Lang;
use DB;

class BargainController extends MarketPlace
{   
    public function __construct() {
        $this->middleware('authenticate');
    }
    
    public function index($sortby='bytime') {
        
        $shop_id = session('user_shop_id'); 

        $prefix =  DB::getTablePrefix(); 
        $default_lang = 0;
        $results = DB::table(with(new \App\ProductBargain)->getTable().' as pb')
                ->join(with(new \App\User)->getTable().' as u', 'pb.user_id', '=', 'u.id')
                ->join(with(new \App\Product)->getTable().' as p', 'pb.product_id', '=', 'p.id')
                ->join(with(new \App\CategoryDesc)->getTable().' as cd',[['p.cat_id', '=', 'cd.cat_id'],['cd.lang_id', '=', DB::raw($default_lang)]])
                ->join(with(new \App\UnitDesc)->getTable().' as ud', [['p.base_unit_id', '=', 'ud.unit_id'],['ud.lang_id', '=', DB::raw($default_lang)]])
                ->join(with(new \App\Badge)->getTable().' as b', 'p.badge_id', '=', 'b.id')
                ->join(with(new \App\Category)->getTable().' as c', 'p.cat_id', '=', 'c.id')
                ->select('pb.id as bargain_id', 'pb.qty', 'p.id', 'u.display_name', 'cd.category_name', 'c.url as caturl' ,'b.icon', 'p.show_price', 'p.unit_price', 'p.sku','p.stock', 'p.quantity', 'ud.unit_name','p.base_unit_id', 'p.thumbnail_image','p.status', 'p.created_at', 'p.updated_at', 'p.created_from', 'pb.base_unit_price','pb.curr_unit_price','pb.curr_total_price', 'u.image', 'u.id as user_id', 'p.cat_id','p.weight_per_unit','p.package_id');
        
        if($sortby == 'bycustomer'){
            
            $useridArray = \App\ProductBargain::where('shop_id', $shop_id)->pluck('user_id', 'user_id')->toArray();
            $userids = implode(',', $useridArray); 
            if ($userids) {
               $results = $results->orderBy('u.display_name', 'ASC')->orderByRaw(DB::raw("FIELD(".$prefix."u.id, $userids)"));
            }

        }elseif($sortby == 'byproduct'){
             
            $productidArray = \App\ProductBargain::where('shop_id', $shop_id)->pluck('product_id', 'product_id')->toArray();
            $productids = implode(',', $productidArray);
            if ($productids) {
                $cateidArray = DB::table(with(new \App\Product)->getTable().' as p')
               ->join(with(new \App\Category)->getTable().' as c', 'p.cat_id', '=', 'c.id')->whereIn('p.id', [$productids])->pluck('c.id', 'c.id')->toArray();
                $cateids = implode(',', $cateidArray);
                if ($cateids) {
                   $results = $results->orderBy('cd.category_name', 'ASC')->orderByRaw(DB::raw("FIELD(".$prefix."p.id, $cateids)")); 
                } 
                
            } 
            

        }else{
             $results = $results->orderBy('pb.id', 'desc');
        }

        $results = $results->where('pb.shop_id', $shop_id)->where('p.status','1');
        $results = $results->get();

        //dd($results);
        
        $productBargainDetailsOfBuyer = $productBargainDetailsOfSeller = [];
        foreach($results as $key=>$result){
            $productBargainDetailsOfBuyer[$result->bargain_id] = \App\ProductBargainDetails::where('bargain_id', $result->bargain_id)->where('created_by', 'buyer')->orderBy('id', 'desc')->limit(2)->get()->reverse()->toArray();
            //array_reverse($productBargainDetailsOfBuyer[$result->bargain_id]); 
            $productBargainDetailsOfSeller[$result->bargain_id] = \App\ProductBargainDetails::where('bargain_id', $result->bargain_id)->where('created_by', 'seller')->orderBy('id', 'desc')->limit(2)->get()->reverse()->toArray();
            //array_reverse($productBargainDetailsOfSeller[$result->bargain_id]); 

            $results[$key]->package_name = getPackageName($result->package_id);
            $results[$key]->unit_name = getUnitName($result->base_unit_id);
            
        }
        return view('seller.bargain_list', ['results'=>$results, 'productBargainDetailsOfBuyer'=>$productBargainDetailsOfBuyer, 'productBargainDetailsOfSeller'=>$productBargainDetailsOfSeller, 'sortby' => $sortby, 'activetab'=>'bargain']);
    }

    public function rejectBargain($id=null){
        $shop_id = session('user_shop_id');  
        $result = \App\ProductBargainDetails::where('id', $id)->first();
        $checking  = \App\ProductBargain::where('id', $result->bargain_id)->where('shop_id', $shop_id)->first();
        if(!$checking){
           abort(404);
        }

        $result->bar_status = '3';
        $result->save();
        $id = $result->id; 
        if(!empty($id)){
            $barData = new \App\ProductBargainDetails;
            $barData->bargain_id = $result->bargain_id;
            
            $barData->base_unit = $checking->unit_id;
            $barData->base_unit_price = $checking->base_unit_price;

            $barData->unit_price = $checking->curr_unit_price;
            $barData->total_price = $checking->qty*$checking->curr_unit_price;
            $barData->bar_status = '3';
            $barData->created_by = 'seller';
            $barData->save(); 

            $bargain_detail_id =  $barData->id;

            $unit_price = $barData->unit_price;
            $customer_name = Auth::user()->display_name;
            //$docName = Auth::id().'-'.$checking->user_id;
            $docName = $this->getDocName([Auth::id(),$checking->user_id]);
           //$seller_user = \App\Shop::where('id', $result->shop_id)->first();
            $seller_user_id = Auth::id();
            $product_id = $checking->product_id;
            $product = \App\Product::getProductBasicInfo($product_id, $checking->shop_id); 
           //dd($product);  
            $totalPrice = $barData->total_price;
            $data_chart = ['bargainId'=> $checking->id, 'baseUnitPrice'=>$checking->base_unit_price, 'createdAt'=>date('F d, Y \a\t h:i:s A'), 'createdBy'=>(String)Auth::id(), 'createdByImg'=> getUserImageUrl(Auth::user()->image), 'createdByName'=>$customer_name, 'img'=>getProductImageUrl($product->thumbnail_image,'original'), 'name'=> $product->category_name, 'product'=>$product_id, 'qty'=>$checking->qty, 'read'=>false, 'sellerId'=>(String)Auth::id(), 'status'=> 3, 'totalPrice'=>(String)$totalPrice, 'type'=>'bargain', 'unitPrice'=>(String)$result->unit_price, 'packageName'=>$product->package_name, 'disabled'=>false, 'bargainDetailId'=> $bargain_detail_id];

            $msg_text = Lang::get('product.rejected_successfully');
            return json_encode(array('status'=>'success', 'message'=>$msg_text, 'url'=>action('Seller\BargainController@index'), 'chat_data'=>$data_chart, 'docName'=>$docName));

        }   
        
         
    }

    public function rejectAllBargain(Request $request){
        $shop_id = session('user_shop_id'); 
        $data = json_decode($request->data, true);
        $data_charts = [];
        if(count($data)){

            foreach($data as $dat){
                $checking  = \App\ProductBargain::where('id', $dat['reject_id'])->where('shop_id', $shop_id)->first();
                if($checking){
                    \App\ProductBargainDetails::where('bargain_id', $dat['reject_id'])->where('bar_status', '1')->where('created_by','buyer')->update(['bar_status'=>'3']);
                    
                    $barData = new \App\ProductBargainDetails;
                    $barData->bargain_id = $dat['reject_id'];
                    
                    $barData->base_unit = $checking->unit_id;
                    $barData->base_unit_price = $checking->base_unit_price;

                    $barData->unit_price = $checking->curr_unit_price;
                    $barData->total_price = $checking->qty*$checking->curr_unit_price;
                    $barData->bar_status = '3';
                    $barData->created_by = 'seller';
                    $barData->save(); 
                    $bargain_detail_id = $barData->id;

                    $unit_price = $barData->unit_price;
                    $customer_name = Auth::user()->display_name;
                    $docName = $this->getDocName([Auth::id(),$checking->user_id]);
                    //$docName = Auth::id().'-'.$checking->user_id;
                   //$seller_user = \App\Shop::where('id', $result->shop_id)->first();
                    $seller_user_id = Auth::id();
                    $product_id = $checking->product_id;
                    $product = \App\Product::getProductBasicInfo($product_id, $checking->shop_id); 
                   //dd($product); 
                    $totalPrice = $barData->total_price;
                    $data_chart = ['bargainId'=> $checking->id, 'baseUnitPrice'=>$unit_price, 'createdAt'=>date('F d, Y \a\t h:i:s A'), 'createdBy'=>(String)Auth::id(), 'createdByImg'=> getUserImageUrl(Auth::user()->image), 'createdByName'=>$customer_name, 'img'=>getProductImageUrl($product->thumbnail_image,'original'), 'name'=> $product->category_name, 'product'=>$product_id, 'qty'=>$checking->qty, 'read'=>false, 'sellerId'=>(String)Auth::id(), 'status'=> 3, 'totalPrice'=>(String)$totalPrice, 'type'=>'bargain', 'unitPrice'=>(String)$unit_price, 'packageName'=>$product->package_name, 'disabled'=>false, 'bargainDetailId' => $bargain_detail_id];

                    $data_charts[] = ['docName' => $docName, 'chat_data'=>$data_chart];


                }



                         
            }
            $msg_text = Lang::get('product.rejected_successfully');
            return  ['status'=>'success','msg'=>$msg_text, 'data_charts'=>$data_charts]; 
        }else{
            return ['status'=>'fail','msg'=>Lang::get('bargain.invaild_bargain')];
        }
    }
    
    public function acceptBargain($id=null){
        
        $shop_id = session('user_shop_id');  
        $result = \App\ProductBargainDetails::where('id', $id)->first();
        $checking  = \App\ProductBargain::where('id', $result->bargain_id)->where('shop_id', $shop_id)->first();
        if(!$checking){
           abort(404);
        }
        $result->bar_status = '2';
        $result->save();
        $id = $result->id; 

        $data_chart = [];
        if(!empty($id)){
            $barData = new \App\ProductBargainDetails;
            $barData->bargain_id = $result->bargain_id;
            
            $barData->base_unit = $result->base_unit;
            $barData->base_unit_price = $result->base_unit_price;

            $barData->unit_price = $result->unit_price;
            $barData->total_price = $checking->qty*$result->unit_price;
            $barData->bar_status = '2';
            $barData->created_by = 'seller';
            $barData->save(); 
            $bargain_detail_id = $barData->id;
            
            $checking->base_unit_price = $result->base_unit_price;
            $checking->curr_unit_price = $barData->unit_price; 
            $checking->curr_total_price = $barData->total_price; 
            $checking->save();


            $unit_price = $barData->unit_price;
            $customer_name = Auth::user()->display_name;
            $docName = $this->getDocName([Auth::id(),$checking->user_id]);
            //$docName = Auth::id().'-'.$checking->user_id;
           //$seller_user = \App\Shop::where('id', $result->shop_id)->first();
           $seller_user_id = Auth::id();
           $product_id = $checking->product_id;
           $product = \App\Product::getProductBasicInfo($product_id, $checking->shop_id); 
           //dd($product); 
           $totalPrice = $barData->total_price; 
           $data_chart = ['bargainId'=> $checking->id, 'baseUnitPrice'=>$result->base_unit_price, 'createdAt'=>date('F d, Y \a\t h:i:s A'), 'createdBy'=>(String)Auth::id(), 'createdByImg'=> getUserImageUrl(Auth::user()->image), 'createdByName'=>$customer_name, 'img'=>getProductImageUrl($product->thumbnail_image,'original'), 'name'=> $product->category_name, 'product'=>$product_id, 'qty'=>$checking->qty, 'read'=>false, 'sellerId'=>(String)Auth::id(), 'status'=> 2, 'totalPrice'=>(String)$totalPrice, 'type'=>'bargain', 'unitPrice'=>(String)$result->unit_price, 'packageName'=>$product->package_name, 'disabled'=>false, 'bargainDetailId' => $bargain_detail_id];

           $msg_text = Lang::get('product.accepted_successfully');
           return json_encode(array('status'=>'success', 'message'=>$msg_text, 'url'=>action('Seller\BargainController@index'),  'chat_data'=>$data_chart, 'docName'=>$docName)); 

        }    
           
        


    }


    public function adjustPriceFromSeller(Request $request, $id){

        $shop_id = session('user_shop_id'); 
        $result  = \App\ProductBargain::where('id', $id)->where('shop_id', $shop_id)->first();
        if(!$result){
           abort(404);
        }
        
        $unit_price = str_replace(",", "", $request->unit_price);
        $base_unit_price = str_replace(",", "", $request->base_unit_price);

        $bar_status = '4';

        /*$checkLastOffer = \App\ProductBargainDetails::where('bargain_id', $result->id)->where('created_by', 'seller')->orderBy('id','DESC')->first();
        if($checkLastOffer->bar_status == '2' || $checkLastOffer->bar_status == '3' || $checkLastOffer->bar_status == '4'){
           $bar_status = '4';
        }*/

        $barData = new \App\ProductBargainDetails;
        $barData->bargain_id = $result->id;
        
        $barData->base_unit = $request->weight_per_unit;
        $barData->base_unit_price = $base_unit_price;

        $barData->unit_price =  $unit_price;     //floatval($request->unit_price);
        $barData->total_price = $result->qty*$unit_price; //floatval($request->unit_price);

        $barData->bar_status = $bar_status;
        $barData->created_by = 'seller';
        $barData->save(); 

        $bargain_detail_id = $barData->id;
        $result->base_unit_price = $barData->base_unit_price; 
        $result->curr_unit_price = $barData->unit_price; 
        $result->curr_total_price = $barData->total_price; 
        $result->save();
        
        $customer_name = Auth::user()->display_name;
        $docName = $this->getDocName([Auth::id(),$result->user_id]);
        //$docName = Auth::id().'-'.$result->user_id;
        //$seller_user = \App\Shop::where('id', $result->shop_id)->first();
        $seller_user_id = Auth::id();
        $product_id = $result->product_id;
        $product = \App\Product::getProductBasicInfo($product_id, $result->shop_id);

        $totalPrice = $barData->total_price; 
        $data_chart = ['bargainId'=> $result->id, 'baseUnitPrice'=>$base_unit_price, 'createdAt'=> date('F d, Y \a\t h:i:s A'), 'createdBy'=>(String)Auth::id(), 'createdByImg'=> getUserImageUrl(Auth::user()->image), 'createdByName'=>$customer_name, 'img'=>getProductImageUrl($product->thumbnail_image,'original'), 'name'=> $product->category_name, 'product'=>$product_id, 'qty'=>$result->qty, 'read'=>false, 'sellerId'=>(String)Auth::id(), 'status'=> 1, 'totalPrice'=>(String)$totalPrice, 'type'=>'bargain', 'unitPrice'=>(String)$unit_price, 'packageName'=>$product->package_name, 'disabled'=>false, 'bargainDetailId' => $bargain_detail_id];
           
        $msg_text = Lang::get('product.accepted_successfully');
        return json_encode(array('status'=>'success', 'message'=>$msg_text, 'url'=>action('Seller\BargainController@index'), 'chat_data'=>$data_chart,'docName'=>$docName)); 
      



    }

}