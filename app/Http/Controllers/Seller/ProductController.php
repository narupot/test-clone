<?php

namespace App\Http\Controllers\Seller;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;

use App\ShopAssignCategory;
use App\Currency;
use App\Badge;
use App\MongoProduct;

use Config;
use Auth;
use Lang;
use DB;

class ProductController extends MarketPlace
{   
    public function __construct() {
        $this->middleware('is-seller');
    }
    
    public function index() {
        //echo "<pre>"; print_r($request->all()); die;
        //dd(session()->all());       
       $fielddata = $this->searchData();
       return view('seller.product_list', ['fielddata'=>$fielddata]);
    } 
    
    public function getProductlist(Request $request) {
        
        $perpage = !empty($request->per_page) ? $request->per_page : getPagination('limit');
        //$filter['perpage'] = $perpage;
        
        /*$filter = []; 
        if($request->sub_action_type=='filter'){
              
            if(!empty($request->status)){
                $status = $request->status;
                if(isset($status) && $status == '1'){
                    $filter['status'] = '1';
                }else{
                    $filter['status'] = '0';  
                }
            }
           
            if(!empty($request->category_name)){
                $category_name = $request->category_name;
                if(isset($category_name)){
                    $filter['cat_id'] = $category_name;
                }
            }

            if(!empty($request->badge_name)){
                $badge_name = $request->badge_name;
                if(isset($badge_name)){
                    $filter['badge_id'] = $badge_name;
                }
            }


            if(!empty($request->unit_price)){
                $unit_price = $request->unit_price;
                if(isset($unit_price)){
                    $filter['unit_price'] = $unit_price;
                }
            }


            if(!empty($request->unit_name)){
                $unit_name = $request->unit_name;
                if(isset($unit_name)){
                    $filter['unit_id'] = $unit_name;
                }
            }
        }else{
            $filter = [];
        }*/

        $default_lang = session('default_lang');
        $shop_id = session('user_shop_id');
        $default_lang = 0;
        $prefix =  DB::getTablePrefix(); 
        
        $sql = DB::table(with(new \App\Product)->getTable().' as p')
                ->join(with(new \App\CategoryDesc)->getTable().' as cd', 
                            [
                              ['p.cat_id', '=', 'cd.cat_id'], 
                              ['cd.lang_id', '=', DB::raw($default_lang)]
                            ]
                )
                ->leftjoin(with(new \App\PackageDesc)->getTable().' as pd', 
                            [
                              ['p.package_id', '=', 'pd.package_id'], 
                              ['pd.lang_id', '=', DB::raw($default_lang)]
                            ]
                )
                ->leftjoin(with(new \App\Badge)->getTable().' as b', 'p.badge_id', '=', 'b.id')->join(with(new \App\Category)->getTable().' as c', 'p.cat_id', '=', 'c.id');  
        
        $sql = $sql->select('p.id', 'cd.category_name', 'c.url as caturl' ,'b.icon', 'p.show_price', 'p.unit_price', 'p.sku','p.stock', 'p.quantity', 'pd.package_name', 'p.thumbnail_image','p.status', 'p.created_at', 'p.updated_at', 'p.created_from');

       /* if(isset($filter['status'])){
            $sql->where('p.status', $filter['status']);    
        }

        if(isset($filter['cat_id'])){
            $sql->where('p.cat_id', $filter['cat_id']);    
        }

        if(isset($filter['badge_id'])){
            $sql->where('p.badge_id', $filter['badge_id']);    
        }

        if(isset($filter['unit_price'])){
            $sql->where('p.unit_price', $filter['unit_price']);    
        }

        if(isset($filter['unit_id'])){
            $sql->where('p.unit_id', $filter['unit_id']);    
        }*/
        
        $sql = $sql->where('shop_id',  $shop_id)->orderBy('p.id', 'desc');

        $products_data = $sql->paginate($perpage);  
        
        //dd($products_data);      

        foreach ($products_data as $key => $value) {
            $created_at = getDateFormat($value->created_at, '1');
            $updated_at = getDateFormat($value->updated_at, '1');
            $status = $value->status?'Active':'Inactive';
            $badgeimage = getBadgeImageUrl($value->icon);
            $productimg = '';
            $productimg = getProductImageUrlRunTime($value->thumbnail_image, 'thumb_59x50');  
            $detail_url = action('Seller\ProductController@edit', $value->id);
            $delete_url = action('Seller\ProductController@deleteProduct',$value->id);
            $copy_url = action('Seller\ProductController@copy',$value->id);
            $view_url = action('ProductDetailController@display',[$value->caturl, $value->sku]);

            $stock_memo_url = action('Seller\StockMemoController@edit',$value->id);

            $tracking_no = '';
            $tracking_arr = [];
            
            $products_data[$key]->status = $status;
            $products_data[$key]->badgeimage = $badgeimage;
            $products_data[$key]->productimg = $productimg;
             
            $products_data[$key]->created_at = $created_at;
            $products_data[$key]->updated_at = $updated_at;
            $products_data[$key]->detail_url = $detail_url;
            $products_data[$key]->delete_url = $delete_url;
            $products_data[$key]->copy_url = $copy_url;
            $products_data[$key]->view_url = $view_url;
            $products_data[$key]->stock_memo_url = $stock_memo_url;

            if($value->show_price == '0'){
                $products_data[$key]->unit_price = Lang::get('product.not_show'); 
            }

            if($value->stock == '1'){
                $products_data[$key]->quantity = Lang::get('product.in_stock'); 
            }else{
                $products_data[$key]->quantity = Lang::get('product.out_stock');
            }

        }
        return $products_data;


    }

    public function searchData() {
        $data = \App\TableColumnConfiguration::whereIn('column_name',['category_name','badge_name','status','unit_price', 'unit_name'])->get()->toArray();
        $datarray = array();
        foreach($data as $resv){
          $datarray[$resv['column_name']] = $resv; 
        }
        $fieldSets = [];
        $replace = ['0'=>false,'1'=>true];
        foreach ($datarray as $key => $res) { 

                $showName = str_replace('_', ' ', $res['column_name']);
                $tempSets = ['fieldName'=>$key,'showName'=>$showName,'sortable'=>$replace[$res['sort']],'filterable'=>$replace[$res['filter']],'width'=> $res['width'],'align'=> $res['align'],"fieldType" => $res['field_type']];
                
                if($res['field_type'] == 'textbox'){
                    $tempSets['textBoxType'] = 'single';
                    $tempSets['datatype'] = 'text';
                }
                if($res['field_type'] == 'selectbox'){
                    $tempSets['selectionType'] = 'single';
                    $tempSets['optionValType'] = 'collection';
                    $tempSets['defaultVal']    = '';

                    if($res['column_name'] == 'category_name'){
                        $seller_prod_cat = ShopAssignCategory::getShopCategoryForFilter();
                        $tempSets['optionArr']    = generatedDD($seller_prod_cat);
                    }
                    
                    if($res['column_name'] == 'unit_name'){
                        $unitsdata = \App\Unit::getUnitsForFilter();
                        $tempSets['optionArr']    = generatedDD($unitsdata);
                    }
                    
                    if($res['column_name'] == 'badge_name'){
                        $prod_badge = Badge::getBadgeForFilter();
                        $tempSets['optionArr']    = generatedDD($prod_badge);
                    }
                    
                    if($res['column_name'] == 'status'){
                        $statusArr = generatedDD(['0'=>'common.inactive','1'=>'common.active']);
                        $tempSets['optionArr']    = $statusArr;
                    }
                    
                }
            $fieldSets[] = $tempSets;
        }
        $table = \App\TableConfiguration::getTableConfig('order_list', 'slug');
        //'filter'=>$replace[$table->filter]
        $tableConfig = ['resizable'=>$replace[$table->resizable],'row_rearrange'=>$replace[$table->row_rearrange],'column_rearrange'=>$replace[$table->column_rearrange],'filter'=>false,'chk_action'=>$replace[$table->chk_action],'col_setting'=>$replace[$table->chk_action]];

       // dd($name->toArray());
        $marks = array('fieldSets' =>$fieldSets,'tableConfig'=>[$tableConfig]);
        return json_encode($marks);
    }      
    
    public function create() {
        $seller_prod_cat = ShopAssignCategory::getShopCategory();
        $prod_badge = Badge::getBadge();
        $currency_dtl = Currency::getDefaultCurrency();

        return view('seller.product_create', ['seller_prod_cat'=>$seller_prod_cat, 'prod_badge'=>$prod_badge, 'currency_dtl'=>$currency_dtl]);
    }

    
    function store(Request $request){
        $shop_id = session('user_shop_id');
        $input = $request->all();
        $input['shop_id'] = $shop_id;
        if($request->is_tier_price == '1') {
            $tier_price = '';
            foreach ($request->tier_price['min_qty'] as $key => $value) {
                $start_qty = $value;
                $end_qty = $request->tier_price['max_qty'][$key];
                $unit_price = floatval($request->tier_price['tier_unit_price'][$key]);
                if(($start_qty < $end_qty) && $unit_price > 0) {
                    $tier_price = '1';
                }
            }
            $input['tier_price'] = $tier_price;
        }
        $input['product_badge'] = '';
        if(isset($request->grade) && isset($request->size)){
            $badge = Badge::where('grade', $request->grade)->where('size', $request->size)->first();
            if(!empty($badge)){
                $input['product_badge'] =  $badge->id;
                $request->product_badge = $badge->id;
            }
        }

        $validate = $this->validateProductForm($input);

        if ($validate->passes(
        )) {
            $data_arr['shop_id'] = $shop_id;
            $data_arr['created_by'] = Auth::id();
            $data_arr['created_from'] = 'seller';
            $this->saveProduct($request, $data_arr);
            
            $msg_text = Lang::get('product.product_added_successfully');
            return json_encode(array('status'=>'success', 'message'=>$msg_text, 'url'=>action('Seller\ProductController@index')));            
        }
        else{

            //echo '<pre>';print_r($validate->errors());die;

            $errors = json_decode($validate->errors()); 
            return json_encode(array('status'=>'validate_error','message'=>$errors));
        }        
    }   

    public function show(Request $request) {
        //echo "<pre>"; print_r($request->all()); die;
    }
    
    function edit(Request $request, $id) {
        
        $shop_id = session('user_shop_id');
        $result = \App\Product::where('id',$id)->where('shop_id', $shop_id)->first();
        if(!$result){
          abort(404);
        }
        $seller_prod_cat = ShopAssignCategory::getShopCategory();
        $prod_badge = Badge::getBadge();
        
        $badge = Badge::badgeData($result->badge_id);

        $currency_dtl = Currency::getDefaultCurrency();
        return view('seller.product_edit', ['seller_prod_cat'=>$seller_prod_cat, 'prod_badge'=>$prod_badge, 'currency_dtl'=>$currency_dtl, 'result'=>$result,'type'=>'edit', 'badge'=>$badge]);
    }

    function update(Request $request, $id){
        
        $shop_id = session('user_shop_id');
        $result = \App\Product::where('id',$id)->where('shop_id', $shop_id)->first();
        if(empty($result)){
          abort(404);  
        }
        $input = $request->all();
        $input['shop_id'] = $shop_id;
        if($request->is_tier_price == '1') {
            $tier_price = '';
            if(isset($request->tier_price['min_qty'])){
              foreach ($request->tier_price['min_qty'] as $key => $value) {
                $start_qty = $value;
                $end_qty = $request->tier_price['max_qty'][$key];
                $unit_price = floatval($request->tier_price['tier_unit_price'][$key]);
                    if(($start_qty < $end_qty) && $unit_price > 0){
                        $tier_price = '1';
                    }
                }
                $input['tier_price'] = $tier_price;
            }
            
        }
        $input['product_badge'] = '';
        if(isset($request->grade) && isset($request->size)){
            $badge = Badge::where('grade', $request->grade)->where('size', $request->size)->first();
            if(!empty($badge)){
                $input['product_badge'] =  $badge->id;
                $request->product_badge = $badge->id;
            }
        }
        $validate = $this->validateProductForm($input, $id);
        if ($validate->passes()) {
            $data_arr['shop_id'] = $shop_id;
            $data_arr['updated_by'] = Auth::id();
            $data_arr['updated_from'] = 'seller';
            $this->saveProduct($request, $data_arr, $id);
            $msg_text = Lang::get('product.product_updated_successfully');
            return json_encode(array('status'=>'success', 'message'=>$msg_text, 'url'=>action('Seller\ProductController@index')));            
        }
        else{
            //echo '<pre>';print_r($validate->errors());die;
            $errors = json_decode($validate->errors()); 
            return json_encode(array('status'=>'validate_error','message'=>$errors));
        } 



    }     

    function delete(Request $request) {
        //dd($request->all());
    } 

    public function deleteProduct($id) {
        $shop_id = session('user_shop_id');
        $result = \App\Product::where('id', $id)->where('shop_id', $shop_id)->first(); 
        if (!$result) {
            abort(404);
        }
        try{
           $result->delete();
           \App\MongoProduct::deleteData($id);
           $msg_text = Lang::get('product.product_delete_successfully');
           return json_encode(array('status'=>'success', 'message'=>$msg_text, 'url'=>action('Seller\ProductController@index'))); 
        }catch(QueryException $e) {
           $msg_text = Lang::get('product.something_went_wrong');
           return json_encode(array('status'=>'validate_error','message'=>$msg_text));
        }

         
    } 

    public function copy($id) {
        $shop_id = session('user_shop_id');
        $result = \App\Product::where('id',$id)->where('shop_id', $shop_id)->first();
        if(empty($result)){
          abort(404);  
        }
        $seller_prod_cat = ShopAssignCategory::getShopCategory($result->shop_id);
        $badge = Badge::badgeData($result->badge_id);
        $prod_badge = Badge::getBadge();
        $currency_dtl = Currency::getDefaultCurrency();
        return view('seller.product_copy', ['seller_prod_cat'=>$seller_prod_cat, 'prod_badge'=>$prod_badge, 'currency_dtl' => $currency_dtl, 'result'=>$result, 'type'=>'copy', 'badge'=>$badge]);
        
    }

    public function copystore(Request $request, $id){  
        $shop_id = session('user_shop_id');
        $input = $request->all();
        $input['shop_id'] = $shop_id;
        $shop_id = session('user_shop_id');
        if($request->is_tier_price == '1') {
            $tier_price = '';
            foreach ($request->tier_price['min_qty'] as $key => $value) {
                $start_qty = $value;
                $end_qty = $request->tier_price['max_qty'][$key];
                $unit_price = floatval($request->tier_price['tier_unit_price'][$key]);
                if(($start_qty < $end_qty) && $unit_price > 0) {
                    $tier_price = '1';
                }
            }
            $input['tier_price'] = $tier_price;
        }

        $input['product_badge'] = '';
        if(isset($request->grade) && isset($request->size)){
            $badge = Badge::where('grade', $request->grade)->where('size', $request->size)->first();
            if(!empty($badge)){
                $input['product_badge'] =  $badge->id;
                $request->product_badge = $badge->id;
            }


        }

        $validate = $this->validateProductForm($input);
        if ($validate->passes()) {
            //$shop_id = session('user_shop_id');
            $data_arr['shop_id'] = $shop_id;
            $data_arr['created_by'] = Auth::id();
            $data_arr['created_from'] = 'seller';
            $this->saveProduct($request, $data_arr);
            
            $msg_text = Lang::get('product.product_added_successfully');
            return json_encode(array('status'=>'success', 'message'=>$msg_text, 'url'=>action('Seller\ProductController@index'))); 
        }else{
            $errors = json_decode($validate->errors()); 
            return json_encode(array('status'=>'validate_error','message'=>$errors));
            
        } 
           

    }
    
    public function baseUnit($cat_id=null){
        if(!empty($cat_id)){
            $default_lang = session('default_lang');
            $sql = DB::table(with(new \App\CategoryUnit)->getTable().' as cu')
                ->join(with(new \App\Unit)->getTable().' as u','u.id', '=', 'cu.unit_id')
                ->join(with(new \App\UnitDesc)->getTable().' as ud', 
                    [ ['u.id', '=', 'ud.unit_id'],
                      ['ud.lang_id', '=', DB::raw($default_lang)]
                    ]
                );

            $sql =  $sql->select('u.id','ud.unit_name')->where('u.status','1')->where('cu.cat_id', $cat_id)->get(); 

            return $sql;   
        }
    }

    public function sellerProduct(){
        $fielddata = $this->searchData();
        return view('seller.seller_product', ['fielddata'=>$fielddata, 'activetab'=>'seller_product_panel']);
    } 


    public function updateStatus($id=null, $status='0'){
        $user_id = Auth::id();
        $shop_id = session('user_shop_id');
        $prodata = \App\Product::where('id', $id)->where('shop_id',$shop_id)->first();
        if(!empty($prodata)){
            $prodata->status = $status;
            $prodata->save();
            $id = $prodata->id;
           
            MongoProduct::updateStatus($id, $status);
            if(!empty($id)){
                $msg_text = Lang::get('product.product_status_has_been_updated_successfully');
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

}
