<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;


use Config;
use Auth;
use Lang;
use DB;

use App\Product;
use App\Helpers\GeneralFunctions;
use App\ProductBargain;
use App\MongoProduct;

class PopUpController extends MarketPlace
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {   
        // dd('hi');
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function getBargainPopUp($id=null){
        
        $productdetail = Product::getProductDetailByID($id); 
        //dd($productdetail);
        if(empty($productdetail)){
            abort(404);
        }
        $productdetail->package_name = getPackageName($productdetail->package_id);
        $productdetail->unit_name = getUnitName($productdetail->base_unit_id);
        $quantity = $productdetail->stock?'unlimited':$productdetail->quantity;
        $productdetail->total_quantity = $quantity;
        return view('productBargin',['productDetail'=>$productdetail]);

    }


    public function getCheckBargainPopUp($id=null, $qty=0){
        
        $productdetail = Product::getProductDetailByID($id);
        //dd($productdetail, $qty);
        //dd($productdetail->getShop->shop_status);

        if(empty($productdetail)){
            return ['status'=>'fail','msg'=>Lang::get('checkout.invalid_product')];
        }
        /**checking if seller add own product***/
        if(Auth::User()->user_type == 'seller'){
            $owner_shop_id = session('user_shop_id');
            if($owner_shop_id == $productdetail->shop_id){
                $msg = Lang::get('checkout.you_cant_add_your_own_product');
                return ['status'=>'fail','msg'=>$msg];
            }
        }
        $userid = Auth::id();
        $bardata = \App\ProductBargain::where('user_id', $userid)->where('product_id',$id)->first();
        if($bardata){
            return ['status'=>'fail','msg'=>Lang::get('checkout.this_product_already_added_in_bargain.')];
        }
        
        if($productdetail->order_qty_limit == 0){
            if($productdetail->min_order_qty > $qty){
                return ['status'=>'fail','msg'=>Lang::get('checkout.order_quantity_is_less_then_minimun_order_quantity')];
            }
        }
        //$shop_info = \App\Shop::where('id',$productdetail->shop_id)->first();
        if(empty($productdetail->getShop)){
            return ['status'=>'fail','msg'=>Lang::get('checkout.invalid_shop')];
        }

        if(isset($productdetail->getShop->shop_status) && $productdetail->getShop->shop_status == 'close'){
            return ['status'=>'fail','msg'=>Lang::get('checkout.this_shop_is_close')];
        }

        if(isset($productdetail->getShop->bargaining) && $productdetail->getShop->bargaining == 'no'){
            return ['status'=>'fail','msg'=>Lang::get('checkout.bargaining_not_allow_in_this_shop')];
        }

        return ['status'=>'sucess'];
    }


    public function saveBargain(Request $request){
        $user_id = Auth::id();
		$input = $request->all();
        $input['user_id'] = $user_id;
        $validate = $this->validateForm($input);
        if ($validate->passes()) {
            $checkcondition = $this->getCheckBargainPopUp($request->product_id, $request->qty);
            if($checkcondition['status'] == 'sucess'){
                $prodata = \App\Product::where('id', $request->product_id)->first();
                if(!empty($prodata)){

                    /*****product if already bargain then delete*****/
                    $cart_manage = \App\Cart::manageBargain($user_id,$request->product_id);
                    
                    $result =  new ProductBargain; 
                    $result->product_id = $request->product_id;
                    $result->shop_id = $prodata->shop_id;
                    $result->qty = $request->qty;
                    $result->user_id = $user_id;
                    $result->unit_id = $request->unit_id;
                    
                    $result->base_unit_price = floatval($prodata->unit_price/$prodata->weight_per_unit);
                    $result->curr_unit_price = floatval($prodata->unit_price);
                    $result->curr_total_price = $request->qty*floatval($prodata->unit_price);
                    
                    $result->bar_status = '1';
                    $result->save();
                    $id = $result->id;
                    if(!empty($id)){
                        $barData = new \App\ProductBargainDetails;
                        $barData->bargain_id = $id;
                        
                        $unit_price = str_replace(",", "", $request->unit_price);
                        $base_unit_price = str_replace(",", "", $request->base_unit_price);

                        $barData->base_unit = $prodata->weight_per_unit;
                        $barData->base_unit_price = $base_unit_price;

                        $barData->unit_price = $unit_price;
                        $barData->total_price = $request->qty*$unit_price;
                        $barData->bar_status = '1';
                        $barData->created_by = 'buyer';
                        $barData->save();
                        // add product into shooping list when product add in bargain
                        $productData = \App\Product::where('id',$request->product_id)->first();
                        $this->addProductInShoppingList($productData);
                        // End

                        $msg_text = Lang::get('product.bargain_value_send_to_seller_successfully');
                        return json_encode(array('status'=>'success', 'message'=>$msg_text, 
                            'url'=>action('User\BargainController@index', 'bytime')));   
                    }else{

                       $msg_text = Lang::get('product.something_went_wrong');
                       return json_encode(array('status'=>'validate_error','message'=>$msg_text));
                    }

                      
                }else{

                   $msg_text = Lang::get('product.something_went_wrong');
                   return json_encode(array('status'=>'validate_error','message'=>$msg_text));
                }
            }else{
               return json_encode($checkcondition);

            }    
                      
        }
        else{

            $errors = json_decode($validate->errors()); 
            return json_encode(array('status'=>'validate_error','message'=>$errors));
        }
    }

    public function validateForm($input, $id=null) {
        
        $rules['user_id'] = numberRule();
        $rules['qty'] = numberRule();
        $rules['unit_price'] = 'Required';
        $rules['base_unit_price'] = 'Required';
        
        $error_msg['user_id.required'] = Lang::get('product.please_login');
        $error_msg['qty.required'] = Lang::get('product.please_enter_valid_qty');
        $error_msg['unit_price.required'] = Lang::get('product.please_enter_price');
        $error_msg['base_unit_price.required'] = Lang::get('product.please_enter_base_unit_price');

        $validate = Validator::make($input, $rules, $error_msg);
        return $validate;         
    }


    public function validateForPriceForm($input, $id=null) {
        
        $rules['unit_price'] = 'Required';
        $error_msg['unit_price.required'] = Lang::get('product.please_enter_price');
        $validate = Validator::make($input, $rules, $error_msg);
        return $validate;         
    }

    public function getSellerProductPopUp($id=null){
        
        $productdetail = Product::getProductDetailByID2Edit($id);
        //dd($productdetail);
        if(empty($productdetail)){
            abort(404);
        }
        $productdetail->package_name = getPackageName($productdetail->package_id);
        $productdetail->unit_name = getUnitName($productdetail->base_unit_id);
        $quantity = $productdetail->stock?'unlimited':$productdetail->quantity;
        $productdetail->total_quantity = $quantity;
        return view('sellerProductsQty',['productDetail'=>$productdetail]);

    }

    public function getSellerProductUnitEditPopUp($id=null){
        
        $productdetail = Product::getProductDetailByID2Edit($id);
        //dd($productdetail);
        if(empty($productdetail)){
            abort(404);
        }
        $productdetail->package_name = getPackageName($productdetail->package_id);
        $productdetail->unit_name = getUnitName($productdetail->base_unit_id);
        $quantity = $productdetail->stock?'unlimited':$productdetail->quantity;
        $productdetail->total_quantity = $quantity;
        return view('sellerProductsEditQtyUnit',['productDetail'=>$productdetail]);

    }


    public function savePrice(Request $request){
        $user_id = Auth::id();
        $input = $request->all();
        $input['user_id'] = $user_id;
        $shop_id = session('user_shop_id');
        $validate = $this->validateForPriceForm($input);
        if ($validate->passes()) {
            $prodata = \App\Product::where('id', $request->product_id)->where('shop_id',$shop_id)->first();
            if(!empty($prodata)){
                $unit_price = str_replace(',', '',$request->unit_price);
                $weight_per_unit = $prodata->weight_per_unit;
                $sum_convert_price = ($unit_price / $weight_per_unit);

                $prodata->unit_price = $unit_price;
                $prodata->unit_convert_price = $sum_convert_price;
                $prodata->save();
                $id = $prodata->id;
                MongoProduct::updatePrice($id, $unit_price);
                if(!empty($id)){
                    $msg_text = Lang::get('product.product_price_has_been_updated_successfully');
                    return json_encode(array('status'=>'success', 'message'=>$msg_text, 'url'=>'#'));   
                }else{

                   $msg_text = Lang::get('product.something_went_wrong');
                   return json_encode(array('status'=>'validate_error','message'=>$msg_text));
                }

                  
            }else{
                $msg_text = Lang::get('product.something_went_wrong');
                return json_encode(array('status'=>'validate_error','message'=>$msg_text));
            }
                    
        }
        else{

            $errors = json_decode($validate->errors()); 
            return json_encode(array('status'=>'validate_error','message'=>$errors));
        }
    }

    public function saveUnitPrice(Request $request){
        $user_id = Auth::id();
        $input = $request->all();
        $input['user_id'] = $user_id;
        $shop_id = session('user_shop_id');
        $validate = $this->validateForPriceForm($input);
        if ($validate->passes()) {
            $prodata = \App\Product::where('id', $request->product_id)->where('shop_id',$shop_id)->first();
            if(!empty($prodata)){
                $unit_price = str_replace(',', '',$request->unit_price);
                $weight_per_unit = $prodata->weight_per_unit;
                $sum_convert_price = ($unit_price * $weight_per_unit);
                
                $prodata->unit_convert_price = $unit_price;
                $prodata->unit_price = $sum_convert_price;
                $prodata->save();
                $id = $prodata->id;
                // MongoProduct::updatePrice($id, $unit_price);
                if(!empty($id)){
                    $msg_text = Lang::get('product.product_price_has_been_updated_successfully');
                    return json_encode(array('status'=>'success', 'message'=>$msg_text, 'url'=>'#'));   
                }else{

                   $msg_text = Lang::get('product.something_went_wrong');
                   return json_encode(array('status'=>'validate_error','message'=>$msg_text));
                }

                  
            }else{
                $msg_text = Lang::get('product.something_went_wrong');
                return json_encode(array('status'=>'validate_error','message'=>$msg_text));
            }
                    
        }
        else{

            $errors = json_decode($validate->errors()); 
            return json_encode(array('status'=>'validate_error','message'=>$errors));
        }
    }

}
