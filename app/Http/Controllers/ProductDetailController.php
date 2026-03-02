<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MarketPlace;
use Illuminate\Support\Facades\Auth;
use Session;
use Route;
use Cache;
use App\Helpers\GeneralFunctions;
use App\Helpers\LayoutHtmlHelpers;
use Lang;
use DB;
use Config;
use App\Product;
use App\Packagedesc;
use App\OrdersTemp;
use App\Cart;

class ProductDetailController extends MarketPlace
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
      
      //dd(Config::get('constants.theme_url'));
      $page = 'home';
      return view(loadFrontTheme('home'),['page'=>$page]);

    }

    public function display(Request $request) {
        $cat_url = $request->cat_url;
        $sku = $request->sku;
        $lang_code = session('default_lang')?'_'.session('lang_code'):'';

        $productdetail = Product::getProductDetail($sku);
        
        // if(empty($productdetail)){
        //     //abort(404);
        //     return view('deeplink.product', [
        //     'category' => $cat_url,
        //     'id' => $sku
        //     ]);
        // }
        if(empty($productdetail)){
            return view('errors.not-found');
        }
        $productImage = $productdetail->images;
        if(!empty($productImage)){
            foreach ($productImage as $key => $value) {
                $productImage[$key]->thumb = getProductImageUrlRunTime($value->image,'thumb');
                $productImage[$key]->large = getProductImageUrlRunTime($value->image,'thumb_405');
                
                $productImage[$key]->original = getProductImageUrl($value->image,'original');
            }
        }else{
            $productImage[] = (object)['thumb'=>getProductImageUrl(''),'large'=>getProductImageUrl('','large_405'),'original'=>getProductImageUrl('','original')];
        }

        $badge_data = getStandardBadge();

        if(isset($badge_data[$productdetail->badge_id])){

            $productdetail->badge_name = $badge_data[$productdetail->badge_id]->badge_name;
            $productdetail->badge_image = getBadgeImage($productdetail->badge_id);
        }

        $productdetail->package_name = getPackageName($productdetail->package_id);
        $productdetail->unit_name = getUnitName($productdetail->base_unit_id);
        $productdetail->shopping_url = action('User\ShoppinglistController@AddToShoppingList');

        $quantity = $productdetail->stock?'unlimited':$productdetail->quantity;
        $productdetail->total_quantity = $quantity;
        $prd_data = $productdetail;
        unset($prd_data->productDesc,$prd_data->images,$prd_data->getShop,$prd_data->getShopDesc,$prd_data->categorydesc);

        $show_review_form = false;
        $required_rev_data = [];
        $wishlist = false;
        if(Auth::check()){
            $user_id = Auth::id();
            $product_id = $productdetail->id;
            $shop_id = $productdetail->shop_id;
            $rev_data = DB::table(with(new \App\OrderDetail)->getTable().' as ordd')
                    ->join(with(new \App\Order)->getTable().' as ord', 'ord.id', '=', 'ordd.order_id')->where(['ordd.product_id'=>$product_id,'ordd.user_id'=>$user_id])
                    ->leftjoin(with(new \App\ProductReview)->getTable().' as rev',[
                        ['rev.order_id','=','ordd.order_id'],
                        ['rev.product_id','=','ordd.product_id']
                    ])->where('ordd.status',3)->select('ordd.order_id','ordd.product_id','ord.formatted_id','rating','ordd.shop_id')->get();

            if(count($rev_data)){
                foreach ($rev_data as $key => $value) {
                    if($value->rating<1){
                        $show_review_form = true;
                        $required_rev_data[]=$value;
                    }
                }
            }

            $check_wishlist = \App\MongoWishlist::checkProductWishlist($product_id,$user_id);
            if($check_wishlist){
                $wishlist = true;
            }
        }
        $productdetail->in_wishlist = $wishlist;
        $tot_shop_prd = \App\MongoProduct::totProductOfShop($productdetail->shop_id);
        $productdetail->tot_shop_prd = $tot_shop_prd;
        $productdetail->weight_per_unit = (float)$productdetail->weight_per_unit;
        if(count($required_rev_data)>1)
            $required_rev_data = array_unique($required_rev_data,SORT_REGULAR);
        $page = 'products';
        $version = "?ver=".getConfigValue('CSS_JS_VERSION');
        return view('productDetail',['version'=>$version,'productDetail'=>$productdetail,'productImage'=>$productImage,'product_data'=>$prd_data,'show_review_form'=>$show_review_form,'rev_data'=>$required_rev_data,'page'=>$page]);

    }

    public function productPriceByQuantity(Request $request){
      $sum_price = 0;
      
      if(!empty($request->all())){
        foreach ($request->all() as $key => $value) {
          $productId = $value['product_id'];
          $quantity = $value['quantity'];
          $currency_id = $value['currency_id'];
          $productPrice = GeneralFunctions::getProductPriceById($productId,$quantity);

          $totprice = $productPrice * $quantity;
          $sum_price = $sum_price + $totprice;
        }
        return ['status'=>'success','amount'=>numberFormat($sum_price,$currency_id)];
      }
      else{
        return['status'=>'fail'];
      }
    }

    public function addProductToCart(Request $request){
        $value = $request->all();
        $userid = Auth::User()->id;

        $product_from = 'normal';
        /*From Bargaining*/
        if(isset($value['action']) && ($value['action'] == 'addtocartfrombargin' || $value['action'] == 'buynowfrombargin')){
            $bargain_id = isset($value['bar_id'])?$value['bar_id']:0;
            if($bargain_id){
                $bardata = \App\ProductBargain::where('user_id', $userid)->where('id',$bargain_id)->first();
                if(!$bardata){
                    return ['status'=>'fail','msg'=>Lang::get('checkout.invalid_product')];
                }
                $productId = $bardata->product_id;
                $product_from = 'bargain';

            }else{
               return ['status'=>'fail','msg'=>Lang::get('checkout.invalid_product')];
            }

        }
        
        $productId = $value['productId'];

        $productInfo = Product::where('id',$productId)->first();
        $oldCartDet = Cart::where(['user_id'=>$userid, 'product_id'=>$productId])->first();

        if(empty($productInfo)){
            return ['status'=>'fail','msg'=>Lang::get('checkout.invalid_product')];
        }

        /*****checking product in bargain if exist then can not add*********/
        if($product_from == 'normal'){
            $checkBardata = \App\ProductBargain::where('user_id', $userid)->where('product_id',$productInfo->id)->count();
            if($checkBardata){
                return ['status'=>'fail','msg'=>Lang::get('checkout.this_product_already_added_in_bargain')];
            }
        }
        /****checking shop close status*******/
        $shop_info = \App\Shop::where('id',$productInfo->shop_id)->first();
        if(empty($shop_info)){
            return ['status'=>'fail','msg'=>Lang::get('checkout.invalid_shop')];
        }

        if($shop_info->shop_status == 'close'){
            return ['status'=>'fail','msg'=>Lang::get('checkout.this_shop_is_close')];
        }
 
        /**** checking stock*******/
        $packagename = $productInfo->package->packagedesc->packagename??'';

        if($productInfo->stock == 0){
            if($value['quantity'] + ($oldCartDet ? $oldCartDet->quantity : 0) > $productInfo->quantity ){
                $msg = "จำนวนที่สามารถสั่งซื้อได้ ".$productInfo->quantity." ".$packagename;
                return ['status'=>'check_qty_stock','msg'=>$msg];
            }
            if($productInfo->quantity <= 0 ){
                $msg="สินค้าหมด " ;
                return ['status'=>'stock_zero','msg'=>$msg];
            }
        }
        
        // เช็คสั่งซื้่อขึ้นต่ำ
        if($value['quantity'] < $productInfo->min_order_qty ){
            $msg="จำนวนสั่งซื้อสินค้าขึ้นต่ำ  ".$productInfo->min_order_qty ." ".$packagename;
            return ['status'=>'fail','msg'=>$msg];
        }

        // เช็คค่าว่าง
        if($value['quantity'] == null  ){
            $msg="จำนวนสั่งซื้อสินค้าขึ้นต่ำ  ".$productInfo->min_order_qty ." ".$packagename;
            return ['status'=>'zero','msg'=>$msg];
        }

        if($oldCartDet){
            if($productInfo->unit_price !== $oldCartDet->original_price??null){
                $msg="ราคาสินค้ามีการเปลี่ยนแปลงจาก ".($oldCartDet->original_price??0)." บาท เป็น ".$productInfo->unit_price." บาท ";
                return ['status'=>'price_changed','msg'=>$msg];
            }
        }
        
        $low_saf_arr = [];
        $orderqry = OrdersTemp::where(['user_id'=>$userid,'order_status'=>'0'])->first();
        if($product_from == 'bargain'){
            $quantity = $bardata->qty;
            $product_price = $bardata->curr_unit_price;
            $prdQuantity = $productInfo->quantity;
            $qty = 0;
        }else{
            $quantity = $value['quantity'];
            $product_price = $productInfo->unit_price;
            $prdQuantity = $productInfo->quantity;
            $qty = Cart::where(['user_id'=>$userid,'product_id'=>$productId])->value('quantity');
        }
            
        
        /**checking if seller add own product***/
        if(Auth::User()->user_type == 'seller'){
            $owner_shop_id = session('user_shop_id');
            if($owner_shop_id == $productInfo->shop_id){
                $msg = Lang::get('checkout.you_cant_add_your_own_product');
                return ['status'=>'fail','msg'=>$msg];
            }
        }

        /**check quantity
        **check already added qty**/
         $chkquantity = $quantity;
        // if($qty > 0){
        //   $chkquantity = $chkquantity + $qty;
        // }

        // if($chkquantity > $prdQuantity){
        //     $msg = Lang::get('checkout.quantity_not_available');
        //     return ['status'=>'fail','msg'=>$msg];
        // }

        // /*****checking minimum quantity*******/
        // if($productInfo->order_qty_limit == '0' && $productInfo->min_order_qty > 0){
        //     if($chkquantity < $productInfo->min_order_qty){
        //         $msg = Lang::get('checkout.product_minimum_quantity_should_be').' '.$productInfo->min_order_qty;
        //         return ['status'=>'fail','msg'=>$msg];
        //     }
        // }

        /***check maximum order amount***/
        $totprdprice = $product_price * $chkquantity;
        
        $prev_price = !empty($orderqry)?$orderqry->total_final_price:0;
        $total_price = $totprdprice + $prev_price;
        
        if(validOrdAmt($total_price)== false){
            $msg = Lang::get('checkout.order_amount_exceeded');
            return ['status'=>'fail','msg'=>$msg];
        }
        
        /***if valid then add to cart****/
        $original_price = $productInfo->unit_price;
        if($product_from == 'normal'){
            $original_price = $product_price = GeneralFunctions::getProductPriceById($productInfo->id,$quantity,$productInfo);
        }

        /**
        ** if in order temp table has record of this user then update this order 
        ** otherwise create records new order temp 
        **/
        if(empty($orderqry)){
            $ordArr = ['user_id'=>$userid];
            $orderId = $this->insertOrder($ordArr);
        }else{
            $orderId = $orderqry->id;
        }

        $cart_status = ((isset($value['cart_action']) && $value['cart_action']=='buynow') || (isset($value['action']) && $value['action']=='buynowfrombargin'))?1:0;
        /**
        **if this product already in cart of this user then update quantity
        ** otherwise insert in cart
        **/
        

        if(!empty($oldCartDet)){
            if($oldCartDet->product_from == 'bargain'){
                return ['status'=>'fail','msg'=>Lang::get('checkout.bargained_product_already_in_cart')];
            }
            $cartId = $oldCartDet->id;
            $newQuantity = $oldCartDet->quantity + $quantity;
            $newProductPrice = $product_price;
            $totalPrice = $newProductPrice * $newQuantity;
            /****updating cart with quantity******/
            $affected = Cart::where(['id' => $cartId])->update([
                'quantity'=>$newQuantity,
                'original_price'=>$product_price,
                'cart_price' => $product_price,
                'total_price'=>$totalPrice,
                'cart_status'=>$cart_status,
                'is_selected'=>true
            ]);
            $this->addProductInShoppingList($productInfo);
        }else{
            $totalPrice = $product_price * $quantity;
            /**insert in cart table***/
            $cart = new Cart;
            $cart->order_id = $orderId;
            $cart->user_id = $userid;
            $cart->shop_id = $productInfo->shop_id;
            $cart->product_id = $productId;
            $cart->cat_id = $productInfo->cat_id;
            $cart->quantity = $quantity;
            $cart->original_price = $original_price;
            $cart->cart_price = $product_price;
            $cart->total_price = $product_price * $quantity;
            $cart->cart_status = $cart_status;
            $cart->product_from = $product_from;
            $cart->is_selected = true;
            $cart->save();
            $this->addProductInShoppingList($productInfo);
        }

        /*data delete from bargain*/
        $data_chart = [];
        $docName = '';
        if($product_from == 'bargain'){
           $seller_user = \App\Shop::where('id', $bardata->shop_id)->first();
           $seller_user_id = $seller_user->user_id; 
           //$docName = $seller_user_id.'-'.Auth::id(); 
           $docName = $this->getDocName([Auth::id(),$seller_user_id]);
           //$newDocName = Auth::id().'-'.$seller_user_id;

           $customer_name = Auth::user()->display_name;
           $product = \App\Product::getProductBasicInfo($productId, $bardata->shop_id); 

            $bargDetails = \App\ProductBargainDetails::where('bargain_id', $bargain_id)->where('created_by','seller')->where('bar_status','2')->orderBy('id', 'DESC')->first();
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
           
           $data_chart = ['bargainId'=> $bardata->id, 'baseUnitPrice'=>$bardata->base_unit_price, 'createdAt'=>date('F d, Y \a\t h:i:s A'), 'createdBy'=>(String)Auth::id(), 'createdByImg'=> getUserImageUrl(Auth::user()->image), 'createdByName'=>$customer_name, 'img'=>getProductImageUrl($product->thumbnail_image,'original'), 'name'=> $product->category_name, 'product'=>$productId, 'qty'=>$quantity, 'read'=>false, 'sellerId'=>(String)$seller_user_id, 'status'=> 2, 'totalPrice'=>(String)$bargDetails->total_price, 'type'=>'bargain', 'unitPrice'=>$product_price, 'packageName'=>$product->package_name, 'disabled'=>false, 'bargainDetailId'=> $bargainDetailId];

           $bardata->delete();
        }


        /****updating order price****/
        $updateOrder = OrdersTemp::updateOrderPrice($orderId);
        $cart_quantity = getCartProduct();
        $cart_price = getCartPrice();
        return [
            'status'=>'success',
            'cart_quantity'=>$cart_quantity,
            'cart_price'=>$cart_price,
            'docName'=>$docName,
            'chat_data'=>$data_chart,
            'product_quantity'=>$productInfo->quantity??0
        ];

    }

    public static function insertOrder($ordArr){
        $order = new OrdersTemp;

        $order->user_id = $ordArr['user_id'];
        $order->session_id = Session::getId();
        $order->save();
        $orderId = $order->id;
        $orderId1 = $orderId;
        if (strlen($orderId1) < 6) {

            $orderId1 = sprintf("%06d", $orderId1);
        }
        $formattedOrderId = date('d') . substr($orderId1, 0, -4) . date('m') . substr($orderId1, -4, -2) . date('y') . substr($orderId1, -2);

        $check_tem = OrdersTemp::where('formatted_order_id',$formattedOrderId)->first();
        if($check_tem){
            $formattedOrderId = generateUniqueNo();
        }
        
        $order = OrdersTemp::find($orderId);
        $order->formatted_order_id = $formattedOrderId;
        $order->save();

        return $orderId;
    }

    public function getAllReviews(Request $request){
        $product_id = $request->product_id; 
        $rev_data = DB::table(with(new \App\ProductReview)->getTable().' as prv')
                    ->where(['prv.product_id'=>$product_id])->select('rating','review','created_at')->where('prv.is_deleted','!=','1')->paginate(10)->toArray();
        if(count($rev_data)){
            foreach ($rev_data['data'] as $key => $value) {
                $rev_data['data'][$key]->rating = $value->rating*20; 
                $rev_data['data'][$key]->time = getcommentDateFormat($value->created_at);
            }
            return ['status'=>'success','data'=>$rev_data];
        }else{
            return [];
        }
    }

    public function getRelatedProducts(Request $request){
        $product_id = (int)$request->product_id ?? 0;

        $check_prd = \App\MongoProduct::where('_id',$product_id)->first();
        if(!empty($check_prd)){
            $cat_id = $check_prd->cat_id;
            $shop_id = $check_prd->shop_id;

            $cat_data = \App\MongoCategory::where('_id',$cat_id)->first();

            /*******related product condition******
            ** It should be 20 from any user and randum order on each refresh .
            **From same category getting product from review.
            **get number of order of this category product.
            **get last updated product.
            ******/

            /****getting product from review*****/
            $prd_result = [];
            $no_of_prd = 6;
            $pre = DB::getTablePrefix();
            $p_id_arr = [];
            $prd_id = DB::table(with(new \App\ProductReview)->getTable().' as pr')
                ->join(with(new \App\Product)->getTable().' as p', 'pr.product_id', '=', 'p.id')
                ->join(with(new \App\Shop)->getTable().' as shop', 'p.shop_id', '=', 'shop.id')
                ->select(DB::raw('DISTINCT('.$pre.'pr.product_id) as pid'))
                ->where(['p.cat_id'=>$cat_id, 'p.status'=>'1'])
                ->where('p.shop_id','!=',$shop_id)
                ->where('shop.shop_status','open')
                ->limit($no_of_prd)
                ->pluck('pid')->toArray();

            if(count($prd_id)){
                $p_id_arr = $prd_id;
            }

            if(count($p_id_arr) < $no_of_prd ){

                $limit = $no_of_prd - count($p_id_arr);

                /****getting product from no of order *****/
                $product_ord =  DB::table(with(new \App\OrderDetail)->getTable().' as od')
                ->join(with(new \App\Product)->getTable().' as p', 'od.product_id', '=', 'p.id')
                ->join(with(new \App\Shop)->getTable().' as shop', 'p.shop_id', '=', 'shop.id')
                ->select(DB::raw('count('.$pre.'od.product_id) as tot'),'od.product_id')
                ->where(['p.cat_id'=>$cat_id, 'p.status'=>'1'])
                ->whereNotIn('p.id',$p_id_arr)
                ->where('p.shop_id','!=',$shop_id)
                ->where('shop.shop_status','open')
                ->limit($limit)
                ->orderBy('tot','desc')
                ->groupBy('od.product_id')
                ->pluck('product_id')->toArray();

                if(count($product_ord)){
                    $p_id_arr = array_merge($p_id_arr,$product_ord);
                }

                if(count($p_id_arr) < $no_of_prd ){

                    $limit = $no_of_prd -  count($p_id_arr);
                    /****getting product latest *****/
                    $prd =  DB::table(with(new \App\Product)->getTable().' as p')
                        ->join(with(new \App\Shop)->getTable().' as shop', 'p.shop_id', '=', 'shop.id')
                        ->where(['p.cat_id'=>$cat_id, 'p.status'=>'1','shop.shop_status'=>'open'])
                        ->whereNotIn('p.id',$p_id_arr)
                        ->where('p.shop_id','!=',$shop_id)
                        ->limit($limit)
                        ->orderBy('p.id','desc')
                        ->pluck('p.id')->toArray();
                    /*$prd = Product::where(['cat_id'=>$cat_id, 'status'=>'1'])
                    ->whereNotIn('id',$p_id_arr)
                    ->where('shop_id','!=',$shop_id)
                    ->limit($limit)
                    ->orderBy('id','desc')
                    ->pluck('id')->toArray();*/
                    
                    if(count($prd)){
                        $p_id_arr =  array_merge($p_id_arr,$prd);
                    }
                    
                }
            }
            
            if(count($p_id_arr)){
                $prd_result = \App\MongoProduct::where(['cat_id'=>$cat_id,'status'=>'1'])->whereIn('_id',$p_id_arr)->with('shop')->with('badge')->when(Auth::check(),function($query){$query->with('wishlist');})->get()->toArray();
            }
            if(empty($prd_result)){
                return ['status'=>'fail'];
            }
            $product_data = [];
            $product_data['data'] = $prd_result;

            $shopping_url = action('User\ShoppinglistController@AddToShoppingList');

            $product_data = $this->formatListingData($product_data,['category_name'=>$cat_data->category_name,'url'=>$cat_data->url,'shopping_url'=>$shopping_url]);

            return ['status'=>'success','detail'=>$product_data,'cat_data'=>$cat_data];

        }else{
            return ['status'=>'fail','msg'=>'product not found'];
        }

    }

    public function getBuyerOrderHistory(Request $request){
        $product_id = (int)$request->product_id ?? 0;
        $check_prd = \App\MongoProduct::where('_id',$product_id)->first();
        if(!empty($check_prd) && Auth::check()){
            $cat_id = $check_prd->cat_id;
            $user_id = Auth::id();

            $order_data = DB::table(with(new \App\OrderDetail)->getTable().' as od')
                ->join(with(new \App\Order)->getTable().' as o', 'od.order_id', '=', 'o.id')
                ->select('o.formatted_id','o.end_shopping_date', 'od.quantity', 'od.last_price','od.total_price')
                ->where(['od.user_id'=>$user_id, 'od.cat_id'=>$cat_id])
                ->whereNotNull('o.end_shopping_date')
                ->orderBy('od.id','desc')
                ->limit(20)
                ->get();
            
            $data = [];
            $baht = Lang::get('common.baht');
            if(count($order_data)){
                foreach ($order_data as $key => $ord_val) {
                    $data_arr['end_shopping_date'] = getDateFormat($ord_val->end_shopping_date,7);

                    $data_arr['formatted_id'] = $ord_val->formatted_id;
                    $data_arr['quantity'] = $ord_val->quantity;
                    $data_arr['unit_price'] = numberFormat($ord_val->last_price).' '.$baht;
                    $data_arr['total_price'] = numberFormat($ord_val->total_price).' '.$baht;
                    $data_arr['ord_url'] = action('User\OrderController@mainOrderDetail',$ord_val->formatted_id);

                    $data[] = $data_arr;
                }
            }

            if(count($data)){
                return ['status'=>'success','data'=>$data];
            }

        }else{
            return ['status'=>'fail'];
        }
    }
}
