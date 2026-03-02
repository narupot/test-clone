<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;

use App\ShopAssignCategory;
use App\Currency;
use App\Badge;
use App\ProductBargain;

use Config;
use Auth;
use Lang;
use DB;
use Session;

class BargainController extends MarketPlace
{   
    public function __construct() {
        $this->middleware('authenticate');
    }
    
    public function index($sortby='bytime') {
        
        //$shop_id = session('user_shop_id');
        $user_id  = Auth::id();
        
        $prefix =  DB::getTablePrefix(); 
        $default_lang = 0;
        $results = DB::table(with(new \App\ProductBargain)->getTable().' as pb')
                ->join(with(new \App\Shop)->getTable().' as s', 'pb.shop_id', '=', 's.id')
                ->join(with(new \App\Product)->getTable().' as p', 'pb.product_id', '=', 'p.id')
                ->join(with(new \App\CategoryDesc)->getTable().' as cd',[['p.cat_id', '=', 'cd.cat_id'],['cd.lang_id', '=', DB::raw($default_lang)]])
                ->join(with(new \App\PackageDesc)->getTable().' as ud', [['p.package_id', '=', 'ud.package_id'],['ud.lang_id', '=', DB::raw($default_lang)]])
                ->join(with(new \App\ShopDesc)->getTable().' as sd', [['sd.shop_id', '=', 'pb.shop_id'],['sd.lang_id', '=', DB::raw($default_lang)]])
                ->join(with(new \App\Badge)->getTable().' as b', 'p.badge_id', '=', 'b.id')
                ->join(with(new \App\Category)->getTable().' as c', 'p.cat_id', '=', 'c.id')
                ->select('pb.id as bargain_id', 'pb.qty', 'p.id', 'sd.shop_name', 's.shop_url','sd.shop_id', 'cd.category_name', 'c.url as caturl' ,'b.icon', 'p.show_price', 'p.unit_price', 'p.sku','p.stock', 'p.quantity', 'ud.package_name as unit_name','p.base_unit_id', 'p.thumbnail_image','p.status', 'p.created_at', 'p.updated_at', 'p.created_from', 'pb.base_unit_price','curr_unit_price','curr_total_price', 'p.cat_id','p.weight_per_unit','s.logo', 'pb.product_id', 'p.package_id');
                 
        if($sortby == 'bystore'){
            $shopidArray = \App\ProductBargain::where('user_id', $user_id)->pluck('shop_id', 'shop_id')->toArray();
            $shopids = implode(',', $shopidArray); 
            if($shopids){
                $results = $results->orderBy('sd.shop_name', 'ASC')->orderByRaw(DB::raw("FIELD(".$prefix."sd.shop_id, $shopids)"));
            }
                
        }elseif($sortby == 'byproduct'){
             
            $productidArray = \App\ProductBargain::where('user_id', $user_id)->pluck('product_id', 'product_id')->toArray();
            $productids = implode(',', $productidArray);
            if($productids){ 
                $cateidArray = DB::table(with(new \App\Product)->getTable().' as p')
                   ->join(with(new \App\Category)->getTable().' as c', 'p.cat_id', '=', 'c.id')->whereIn('p.id', [$productids])->pluck('c.id', 'c.id')->toArray();
                $cateids = implode(',', $cateidArray); 
                if ($cateids) {
                    $results = $results->orderBy('cd.category_name', 'ASC')
                    ->orderByRaw(DB::raw("FIELD(".$prefix."p.id, $cateids)"));
                }
                
            }        

        }else{
            $results = $results->orderBy('pb.id', 'desc');
        }
        $results = $results->where('p.status', '1');
        $results = $results->where('pb.user_id', $user_id);
        $results = $results->get();
        
        //dd($results);

        $productBargainDetailsOfBuyer = $productBargainDetailsOfSeller = [];
        foreach($results as $key=>$result){
            $productBargainDetailsOfBuyer[$result->bargain_id] = \App\ProductBargainDetails::where('bargain_id', $result->bargain_id)->where('created_by', 'buyer')->orderBy('id', 'desc')->limit(2)->get()->reverse()->toArray(); 
            $productBargainDetailsOfSeller[$result->bargain_id] = \App\ProductBargainDetails::where('bargain_id', $result->bargain_id)->where('created_by', 'seller')->orderBy('id', 'desc')->limit(2)->get()->reverse()->toArray();

            $results[$key]->package_name = getPackageName($result->package_id);
            $results[$key]->unit_name = getUnitName($result->base_unit_id);
             
        }

        //dd($productBargainDetailsOfBuyer, $productBargainDetailsOfSeller);

        $default_shopping = session('shopping_list');
        $pur_prds_in_shop_list = \App\UserShoppingListItems::where(['shopping_list_id'=>$default_shopping,'is_completed'=>'1'])->count();
        $total_prds_in_shop_list = \App\UserShoppingListItems::where(['shopping_list_id'=>$default_shopping])->count();
        
        return view('user.bargain_list', ['results'=>$results, 'productBargainDetailsOfBuyer'=>$productBargainDetailsOfBuyer, 'productBargainDetailsOfSeller'=>$productBargainDetailsOfSeller, 'sortby' => $sortby, 'page'=>'bargain','total_prds_in_shop_list'=>$total_prds_in_shop_list,'pur_prds_in_shop_list'=>$pur_prds_in_shop_list]);
    }

    public function rejectBargain($id=null){
        $shop_id = session('user_shop_id');  
        $result = \App\ProductBargainDetails::where('id', $id)->first();
        $checking  = \App\ProductBargain::where('id', $result->bargain_id)->where('shop_id', $shop_id)->count();
        if(!$checking){
           abort(404);
        }
        $result->bar_status = '3';
        $result->save();
        
        $msg_text = Lang::get('product.rejected_successfully');
        return json_encode(array('status'=>'success', 'message'=>$msg_text, 'url'=>action('Seller\BargainController@index'))); 
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
        if(!empty($id)){
            $barData = new \App\ProductBargainDetails;
            $barData->bargain_id = $result->bargain_id;
            $barData->base_unit_price = $result->base_unit_price;
            $barData->unit_price = $result->unit_price;
            $barData->total_price = $checking->qty*$result->unit_price;
            $barData->bar_status = '1';
            $barData->created_by = 'seller';
            $barData->save(); 

            $checking->base_unit_price = $barData->base_unit_price; 
            $checking->curr_unit_price = $barData->unit_price; 
            $checking->curr_total_price = $barData->total_price; 
            $checking->save();

        }    
           
        $msg_text = Lang::get('product.accepted_successfully');
        return json_encode(array('status'=>'success', 'message'=>$msg_text, 'url'=>action('Seller\BargainController@index'))); 


    }


    public function bargainPriceFromBuyer(Request $request, $id){

        //$shop_id = session('user_shop_id'); 
        $chatData = []; 

        $user_id  = Auth::id();
        $result  = \App\ProductBargain::where('id', $id)->where('user_id', $user_id)->first();
        if(!$result){
           abort(404);
        }
        /*check bargain already accepted*/
        $acceptCount = \App\ProductBargainDetails::where('bargain_id', $result->id)->where('bar_status','2')->count();
        if($acceptCount){
           abort(404);  
        }

        /***Reject All Bargain waiting in***/
        \App\ProductBargainDetails::where('bargain_id', $result->id)->where('bar_status','1')->update(['bar_status'=>'3']);  
       /***********************************/
        $unit_price = str_replace(",", "", $request->unit_price);
        $base_unit_price = str_replace(",", "", $request->base_unit_price);
        $total_price = $result->qty*$unit_price;
        $barData = new \App\ProductBargainDetails;
        $barData->bargain_id = $result->id;
        $barData->base_unit = $result->base_unit;
        $barData->base_unit_price = $base_unit_price;
        $barData->unit_price = $unit_price;
        $barData->total_price = $total_price;

        $barData->bar_status = '1';
        $barData->created_by = 'buyer';
        $barData->save(); 
        $bargain_detail_id = $barData->id;

        /*$result->curr_unit_price = $barData->unit_price; 
        $result->curr_total_price = $barData->total_price; 
        $result->save();*/

        //$title = Lang::get('bargain.new_price_from_buyer');
        $customer_name = Auth::user()->display_name;

        //$body = $customer_name .' '.Lang::get('bargain.submit_price');
        $seller_user = \App\Shop::where('id', $result->shop_id)->first();
        $seller_user_id = $seller_user->user_id;
        /*if($seller_user_id){
            $post_arr = ['user_id'=>$seller_user_id, 'title'=>$title,'body'=>$body];
            $url = Config::get('constants.mobile_notification_url');
            $responce = $this->handleCurlRequest($url,$post_arr);

        }*/
        
        $product_id = $result->product_id;
        $product = \App\Product::getProductBasicInfo($product_id, $result->shop_id); 
        
        $data_chart = [];
        //$docName = $seller_user_id.'-'.Auth::id();
        $docName = $this->getDocName([Auth::id(),$seller_user_id]);
        //$data_chart['id'] = $docName;
        $sellerData = \App\User::where('id', $seller_user_id)->first();
        //$data_chart['imgs'] = [$seller_user_id => getUserImageUrl($sellerData->image), Auth::id() => getUserImageUrl(Auth::user()->image)];

        //T for timezone in date
        $totalPrice = number_format($barData->unit_price*$result->qty,2);
        $totalPrice = str_replace(",", "", $totalPrice);
        $data_chart = ['bargainId'=> $result->id, 'baseUnitPrice' => $base_unit_price, 'createdAt'=> date('F d, Y \a\t h:i:s A'), 'createdBy'=>(String)Auth::id(), 'createdByImg'=> getUserImageUrl(Auth::user()->image), 'createdByName'=>$customer_name, 'img'=>getProductImageUrl($product->thumbnail_image,'original'), 'name'=> $product->category_name, 'product'=>$product_id, 'qty'=>$result->qty, 'read'=>false, 'sellerId'=>(String)$seller_user_id, 'status'=> 1, 'totalPrice'=>$totalPrice, 'type'=>'bargain', 'unitPrice'=>$unit_price, 'packageName'=>$product->package_name, 'disabled'=>false, 'bargainDetailId'=>$bargain_detail_id];
        
        //$data_chart['members'] = [$seller_user_id, Auth::id()];

        //$data_chart['names'] = [$seller_user_id => $sellerData->display_name, Auth::id() => $customer_name];
        
        //$data_chart['productImg'] = getProductImageUrl($product->thumbnail_image,'original');

        //$data_chart['read']  = [$seller_user_id => '0', Auth::id() => '0'];

        
        $msg_text = Lang::get('product.accepted_successfully');
        return json_encode(array('status'=>'success', 'message'=>$msg_text, 'url'=>action('User\BargainController@index'),'chat_data'=>$data_chart, 'docName'=>$docName)); 
      



    }


    function removeBargain(Request $request){
        $b_id = $request->b_id;
        $user_id = Auth::User()->id;
        $result  = \App\ProductBargain::where('id', $b_id)->where('user_id', $user_id)->first();
        
        if($result){
            $seller_user = \App\Shop::where('id', $result->shop_id)->first();
            $seller_user_id = $seller_user->user_id;
            $docName = $this->getDocName([Auth::id(),$seller_user_id]);
            
            $data_chart = ['bargainId'=> $result->id];
            $msg = Lang::get('bargain.bargain_deleted_successfully');
            $result->delete();  /***delete bargain****/
            return ['msg'=>$msg,'status'=>'success', 'chat_data'=>$data_chart, 'docName'=>$docName];
        }else{
            return ['status'=>'fail','msg'=>Lang::get('bargain.invaild_bargain')];
        }
    }

    function removeAllBargain(Request $request){
        $data = json_decode($request->data, true);
        $user_id = Auth::User()->id;
        $ids = [];
        if(count($data)){
            foreach($data as $dat){
               $ids[] = $dat['barg_id'];        
            }    
        }
        if(count($ids)){
           $results  = \App\ProductBargain::whereIn('id', $ids)->where('user_id', $user_id)->get();
            foreach($results as $key=>$result){
                $seller_user = \App\Shop::where('id', $result->shop_id)->first();
                $seller_user_id = $seller_user->user_id;
                $docName = $this->getDocName([Auth::id(),$seller_user_id]);
                $data_chart[$key] = ['bargainId'=> $result->id, 'docName' => $docName];
                $result->delete();  /***delete bargain****/

           }
           $msg = Lang::get('bargain.bargain_deleted_successfully');
           return ['msg'=>$msg,'status'=>'success', 'chat_data' => $data_chart];

        }else{
            
            return ['status'=>'fail','msg'=>Lang::get('bargain.invaild_bargain')];

        }
    }

    function selectedAddtoCart(Request $request){
        $data = json_decode($request->data, true);
        $user_id = Auth::User()->id;
        $ids = [];
        if(count($data)){
            foreach($data as $dat){
               $ids[] = $dat['barg_id'];        
            }    
        }
        if(count($ids)){
            $results  = \App\ProductBargain::whereIn('id', $ids)->where('user_id', $user_id)->get();
            $product_from = 'bargain';
            $msgData = [];
            $data_chart = [];
            if($results){
                foreach($results as $bardata){
                    $productId = $bardata->product_id;
                    $productInfo = \App\Product::where('id',$productId)->first();
                    //dd($productInfo);
                    if(!empty($productInfo)){
                        $shop_info = \App\Shop::where('id',$productInfo->shop_id)->first();
                        //dd($shop_info);
                        
                        if(empty($shop_info)){
                               $msgData[$bardata->id] = ['status'=>'fail','sku'=>$productInfo->sku,'msg'=>Lang::get('checkout.invalid_shop')];
                               continue;
                        }
                        if(!empty($shop_info) && $shop_info->shop_status == 'close'){
                               $msgData[$bardata->id] = ['status'=>'fail','sku'=>$productInfo->sku, 'msg'=>Lang::get('checkout.this_shop_is_close')];
                               continue;
                        }    
                            

                        if(!empty($shop_info) && $shop_info->shop_status != 'close'){
                            $orderqry = \App\OrdersTemp::where(['user_id'=>$user_id,'order_status'=>'0'])->first();
                            $quantity = $bardata->qty;
                            $product_price = $bardata->curr_unit_price;
                            $prdQuantity = $productInfo->quantity;
                            $qty = 0;
                            
                            $chkquantity = $quantity;
                            if($qty > 0){
                              $chkquantity = $chkquantity + $qty;
                            }

                            if($chkquantity > $prdQuantity){
                                $msgData[$bardata->id] = ['status'=>'fail','sku'=>$productInfo->sku,'msg'=>Lang::get('checkout.quantity_not_available')];
                                    continue;
                            }

                            if($productInfo->order_qty_limit == '0' && $productInfo->min_order_qty > 0){
                                if($chkquantity < $productInfo->min_order_qty){
                                    $msgDa= Lang::get('checkout.product_minimum_quantity_should_be').' '.$productInfo->min_order_qty;

                                    $msgData[$bardata->id] = ['status'=>'fail','sku'=>$productInfo->sku,'msg'=>$msgDa];
                                    continue;
                                }
                            }
                            //dd($chkquantity,$prdQuantity);
                            
                            $totprdprice = $product_price * $chkquantity;
                            $prev_price =!empty($orderqry)?$orderqry->total_final_price:0;
                            $total_price = $totprdprice + $prev_price;
                            if(validOrdAmt($total_price)){
                                $original_price = $productInfo->unit_price;
                                if(empty($orderqry)){
                                    $ordArr = ['user_id'=>$user_id];
                                    $orderId = $this->insertOrder($ordArr);
                                }else{
                                    $orderId = $orderqry->id;
                                }
                                
                                $oldCartDet = \App\Cart::where(['user_id'=>$user_id, 'product_id'=>$productId])->first();
                                $cart_status = '1';
                                if(!empty($oldCartDet)){
                                    $cartId = $oldCartDet->id;
                                    $newQuantity = $oldCartDet->quantity + $quantity;
                                    $newProductPrice = $product_price;
                                    $totalPrice = $newProductPrice * $newQuantity;
                                    /****updating cart with quantity******/
                                    $affected = \App\Cart::where(['id' => $cartId])->update(['quantity'=>$newQuantity,'original_price'=>$product_price,'cart_price' => $product_price,'total_price'=>$totalPrice,'cart_status'=>$cart_status]);

                                    $this->addProductInShoppingList($productInfo);
                                    
                                }else{

                                    $totalPrice = $product_price * $quantity;
                                    /**insert in cart table***/
                                    $cart = new \App\Cart;
                                    $cart->order_id = $orderId;
                                    $cart->user_id = $user_id;
                                    $cart->shop_id = $productInfo->shop_id;
                                    $cart->product_id = $productId;
                                    $cart->cat_id = $productInfo->cat_id;
                                    $cart->quantity = $quantity;
                                    $cart->original_price = $original_price;
                                    $cart->cart_price = $product_price;
                                    $cart->total_price = $product_price * $quantity;
                                    $cart->cart_status = $cart_status;
                                    $cart->product_from = $product_from;
                                    $cart->save();
                                    $this->addProductInShoppingList($productInfo);
                                    
                                }

                                $seller_user = \App\Shop::where('id', $bardata->shop_id)->first();
                                $seller_user_id = $seller_user->user_id; 
                                $docName = $this->getDocName([Auth::id(),$seller_user_id]);
                                $customer_name = Auth::user()->display_name;
                                $product = \App\Product::getProductBasicInfo($productId, $bardata->shop_id); 

           
                                $bargDetails = \App\ProductBargainDetails::where('bargain_id', $bardata->id)->where('created_by','seller')->where('bar_status','2')->orderBy('id', 'DESC')->first();
                                $bargainDetailId = isset($bargDetails->id)?$bargDetails->id:0;
                                if(!$bargainDetailId){
                                    $bargDetails = new \App\ProductBargainDetails;
                                    $bargDetails->bargain_id = $bardata->id;
                                    
                                    $bargDetails->base_unit = $product->weight_per_unit;
                                    $bargDetails->base_unit_price = $bardata->base_unit_price;

                                    $bargDetails->unit_price = $bardata->curr_unit_price;
                                    $bargDetails->total_price = $bardata->curr_total_price;
                                    $bargDetails->bar_status = '2';
                                    $bargDetails->created_by = 'buyer';
                                    $bargDetails->save(); 
                                    $bargainDetailId = $bargDetails->id;
                               }
           
                               $data_chart[$bardata->id]['chat_data'] = ['bargainId'=> $bardata->id, 'baseUnitPrice'=>$bardata->base_unit_price, 'createdAt'=>date('F d, Y \a\t h:i:s A'), 'createdBy'=>(String)Auth::id(), 'createdByImg'=> getUserImageUrl(Auth::user()->image), 'createdByName'=>$customer_name, 'img'=>getProductImageUrl($product->thumbnail_image,'original'), 'name'=> $product->category_name, 'product'=>$productId, 'qty'=>$quantity, 'read'=>false, 'sellerId'=>(String)$seller_user_id, 'status'=> 2, 'totalPrice'=>(String)$bargDetails->total_price, 'type'=>'bargain', 'unitPrice'=>$product_price, 'packageName'=>$product->package_name, 'disabled'=>false, 'bargainDetailId'=> $bargainDetailId];

                                $data_chart[$bardata->id]['docName'] = $docName;

                           

                               /*data delete from bargain*/
                                $bardata->delete();
                                /****updating order price****/
                                $updateOrder = \App\OrdersTemp::updateOrderPrice($orderId);
                                $cart_quantity = getCartProduct();
                                $cart_price = getCartPrice();
                            }else{
                                $msgData[$bardata->id] = ['status'=>'fail','sku'=>$productInfo->sku,'msg'=>Lang::get('checkout.order_amount_exceeded')];
                                continue;
                                
                            }    
                        }   

                    }
                    $msgData[$bardata->id] = ['status'=>'success','sku'=>$productInfo->sku, 'msg'=>Lang::get('checkout.product_add_to_cart_successfully')];
                }
                return ['status'=>'success', 'msg' => $msgData, 'data_chart'=>$data_chart];
                
            }
        }else{
            
            return ['status'=>'fail','msg'=>Lang::get('bargain.invaild_bargain')];

        }
    }

    public function insertOrder($ordArr){
        $order = new \App\OrdersTemp;

        $order->user_id = $ordArr['user_id'];
        $order->session_id = Session::getId();
        $order->save();
        $orderId = $order->id;
        $orderId1 = $orderId;
        if (strlen($orderId1) < 6) {

            $orderId1 = sprintf("%06d", $orderId1);
        }
        $formattedOrderId = date('d') . substr($orderId1, 0, -4) . date('m') . substr($orderId1, -4, -2) . date('y') . substr($orderId1, -2);

        $order = \App\OrdersTemp::find($orderId);
        $order->formatted_order_id = $formattedOrderId;
        $order->save();

        return $orderId;
    }


}