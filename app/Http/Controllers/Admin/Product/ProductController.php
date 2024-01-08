<?php
namespace App\Http\Controllers\Admin\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\MarketPlace;
use Illuminate\Validation\Rule;
use Validator;
use Session;
use Config;
use Auth;
use DB;
use File;
use Lang;
use App\ShopAssignCategory;
use App\Currency;
use App\Badge;
use App\Shop;
use App\ShopDesc;
use App\Seller;
use App\User;
use App\Product;
use App\ProductImage;



class ProductController extends MarketPlace
{
    

    public function __construct(){
        $this->middleware('admin.user');

    }

    /**
     * Display a listing of the resource.
     *
     * @returnIlluminate\Http\Response
     ********code for sync mysql product to mongo******
    $prd = \App\Product::get();
    foreach ($prd as $key => $value) {
        $value->description = \App\ProductDesc::where('product_id',$value->id)->value('description');
        $image = \App\ProductImage::where('product_id', $value->id)->pluck('image')->toArray();
        if(count($image))
            $value->image = $image;

        $tier_price_arres = \App\ProductTierPrice::select('start_qty','end_qty','unit_price')->where('product_id', $value->id)->get()->toArray();
        if(count($tier_price_arres)){
           $value->tier_price_data = $tier_price_arres;
        }
        \App\MongoProduct::updateData($value);
    }
    ****end code******/
     
    public function index()
    {
        
        $filter = $this->getFilter('product');
        //dd($filter);
       return view('admin.product.list', ['filter'=>$filter]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(){   
        $permission = $this->checkUrlPermission('add_product');  
        if($permission === true) {
            $seller_prod_cat = ShopAssignCategory::getShopCategory();
            $prod_badge = Badge::getBadge();
            $currency_dtl = Currency::getDefaultCurrency();
            return view('admin.product.create', ['seller_prod_cat'=>$seller_prod_cat, 'prod_badge'=>$prod_badge, 'currency_dtl' => $currency_dtl]);
        }

    }


    public function getSellerCategory(Request $request){
        $shop_id = !empty($request->shop_id) ? $request->shop_id : '';
        $permission = $this->checkUrlPermission('add_product'); 
        
        $html = ''; 
        if($permission === true) {
            $shop_prod_cat = ShopAssignCategory::getShopCategory($shop_id);
            if(count($shop_prod_cat) > 0){
                foreach($shop_prod_cat as $prod_cat){
                    $html.='<li style="min-width:90px; text-align:center;"><div class="img-block" style="position:relative;"><img src="'.getCategoryImageUrl($prod_cat->img).'" width="76" height="57" alt="">
                            <label class="radio-wrap">
                            <input type="radio" name="product_cat" value="'.$prod_cat->id.'">
                                <span class="radio-mark"></span>
                            </label>
                            </div>                            
                            <div class="prod-name">'.$prod_cat->category_name.'</div>
                            </li>';
                }
            }
        } 
        echo $html;   
    }


    function SellerData(Request $request){

        $perpage = !empty($request->pq_rpp) ? $request->pq_rpp : 10;
        $currentPage = !empty($request->pq_curpage)?$request->pq_curpage:0;
        //dd($request->all());
        $offset = ($currentPage - 1) * $perpage;

        $order_by = 'id';
        $order_by_val = 'desc';
        if(isset($request->pq_sort)){
            $sort_data = jsonDecodeArr($request->pq_sort);
            $order_by = $sort_data[0]['dataIndx'];
            $order_by_val = ($sort_data[0]['dir']=='up')?'asc':'desc';
        }
        try{
           
           $results = DB::table(with(new Shop)->getTable().' as s')
                ->join(with(new ShopDesc)->getTable().' as sd', 's.id', '=', 'sd.shop_id')
                ->join(with(new Seller)->getTable().' as sl', 'sl.user_id', '=', 's.user_id');

           $results = $results->select('s.panel_no', 'sd.shop_name', 's.id');
           
           if(isset($request->pq_filter)){
                $filter_req = json_decode($request->pq_filter,true);
                if(!empty($filter_req['data'])){
                    $filter_arr = $filter_req['data'];
                    foreach ($filter_arr as $fkey => $fvalue) {
                        $searchval = $fvalue['value'];
                        switch ($fvalue['dataIndx']) {
                            case 'shop_name':$results->where('sd.shop_name','like', '%'.$searchval.'%'); break;
                            case 'panel_no':$results->where('s.panel_no', $searchval); break;
                            
                        }
                        
                    }
                }
            }


           $tcount = $results->count();
           $results = $results->limit($perpage)->offset($offset)->get()->toArray();
           $response = ['data'=>$results, 'total'=>$tcount, 'current_page'=>$currentPage];


           //$response = \App\User::where('user_type','seller')->paginate($perpage); 
        }catch(QueryException $e){
            $response = ['status'=>'fail','msg'=>$e->getMessage()];
        }
        
        return $response;
    }


    

    function productListData(Request $request){
        //dd($request->all());
        $perpage = !empty($request->pq_rpp) ? $request->pq_rpp : 10;
        $request->page = $current_page = !empty($request->pq_curpage)?$request->pq_curpage:0;

        $start_index = ($current_page - 1) * $perpage;
        //dd($perpage,$request->page);
        
        $order_by = 'id';
        $order_by_val = 'desc';
        if(isset($request->pq_sort)){
            $sort_data = jsonDecodeArr($request->pq_sort);
            $order_by = $sort_data[0]['dataIndx'];
            $order_by_val = ($sort_data[0]['dir']=='up')?'asc':'desc';
        }

        try{
            
            $query = DB::table(with(new \App\Product)->getTable().' as p')
                ->join(with(new \App\CategoryDesc)->getTable().' as cd', 
                            [
                              ['p.cat_id', '=', 'cd.cat_id']
                            ]
                )
                ->join(with(new \App\Category)->getTable().' as c', 'p.cat_id', '=', 'c.id')
                ->join(with(new \App\BadgeDesc)->getTable().' as bd', 
                            [
                              ['p.badge_id', '=', 'bd.badge_id']
                            ]
                )
                ->join(with(new Shop)->getTable().' as s','p.shop_id', '=', 's.id')
                  ->join(with(new ShopDesc)->getTable().' as sd',
                            [
                                ['s.id', '=', 'sd.shop_id']

                            ])
                  ->join(with(new User)->getTable().' as u','s.user_id', '=', 'u.id');
                 $query = $query->select('p.id','p.sku','p.thumbnail_image', 'cd.category_name', 'bd.badge_name', 'p.show_price', 'p.unit_price', 'p.stock', 'p.quantity', 'p.status', 'p.created_at', 'p.updated_at', 'p.created_from','s.shop_url','sd.shop_name','u.display_name','c.url as caturl');
            
            if(isset($request->pq_filter)){
                $filter_req = json_decode($request->pq_filter,true);
                if(!empty($filter_req['data'])){
                    $filter_arr = $filter_req['data'];
                    foreach ($filter_arr as $fkey => $fvalue) {

                        $searchval = $fvalue['value'];
                        switch ($fvalue['dataIndx']) {
                            case 'category_name':$query->where('cd.category_name','like', '%'.$searchval.'%'); break;
                            case 'sku':$query->where('p.sku','like', '%'.$searchval.'%'); break;
                            case 'badge_name':$query->where('bd.badge_name','like', '%'.$searchval.'%'); break;
                            case 'display_name':$query->where('u.display_name','like', '%'.$searchval.'%'); break;
                            case 'shop_name':$query->where('sd.shop_name','like', '%'.$searchval.'%'); break;
                            case 'status':$query->whereIn('p.status',$searchval); break;
                            case 'show_price':$query->whereIn('p.show_price',$searchval); break;
                            break;
                            case 'created_at':
                                $from_date = $fvalue['value']??'';
                                $to_date = $fvalue['value2']??'';
                                createDateFilter($query,'p.created_at',$from_date,$to_date);
                            break;
                            case 'updated_at':
                                $from_date = $fvalue['value']??'';
                                $to_date = $fvalue['value2']??'';
                                createDateFilter($query,'p.updated_at',$from_date,$to_date);
                            break;
                            
                        }
                        
                    }
                }
            }
            $response = $query->orderBy($order_by,$order_by_val)->paginate($perpage,['*'],'page',$current_page);
            $totrec = $response->total();
            //dd($response);
            if($start_index >= $totrec) {
                $current_page = ceil($totrec/$perpage);
                
                $response = $query->orderBy($order_by,$order_by_val)->paginate($perpage,['*'],'page',$current_page);
            }

            if(count($response)){
                foreach($response as $key=>$unitproduct){
                    $response[$key]->product_thumb = getProductImageUrl($unitproduct->thumbnail_image,'original');
                }       
            }

            /***save filter****/
            $this->setFilter('product',$request);

            
        }catch(QueryException $e){
            $response = ['status'=>'fail','msg'=>$e->getMessage()];
        }
        
        return $response;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){  

        $input = $request->all();
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
            $shop_id = $input['shop_id'];
            $data_arr['shop_id'] = $shop_id;
            $data_arr['created_by'] = Auth::guard('admin_user')->user()->id;
            $data_arr['created_from'] = 'admin';
            $this->saveProduct($request, $data_arr);
            $msg_text = Lang::get('product.product_added_successfully');
            
            return redirect()->action('Admin\Product\ProductController@index')->with('succMsg', $msg_text);
        }else{
            return  redirect()->action('Admin\Product\ProductController@create')->withInput()->withErrors($validate->errors());
            
        } 
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
    public function edit($id) {
        //dd($id);        
        $permission = $this->checkUrlPermission('edit_product');  
        if($permission === true) {
            $result = \App\Product::where('id',$id)->first();
            $seller_prod_cat = ShopAssignCategory::getShopCategory($result->shop_id);
            $prod_badge = Badge::getBadge();
            $badge = Badge::badgeData($result->badge_id);
            
            $currency_dtl = Currency::getDefaultCurrency();
            return view('admin.product.edit', ['seller_prod_cat'=>$seller_prod_cat, 'prod_badge'=>$prod_badge, 'currency_dtl' => $currency_dtl, 'result'=>$result, 'type'=>'edit','badge'=>$badge]);
        }
    }


    public function copy($id) {
        //dd($id);        
        $permission = $this->checkUrlPermission('edit_product');  
        if($permission === true) {
            $result = \App\Product::where('id',$id)->first();
            $seller_prod_cat = ShopAssignCategory::getShopCategory($result->shop_id);
            $badge = Badge::badgeData($result->badge_id);
            $prod_badge = Badge::getBadge();
            $currency_dtl = Currency::getDefaultCurrency();
            return view('admin.product.copy', ['seller_prod_cat'=>$seller_prod_cat, 'prod_badge'=>$prod_badge, 'currency_dtl' => $currency_dtl, 'result'=>$result, 'type'=>'copy','badge'=>$badge]);
        }
    }


   

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){
        
        $permission = $this->checkUrlPermission('edit_product'); 

        $result = \App\Product::where('id',$id)->first();
        if(empty($result)){
          abort(404);  
        }
        $input = $request->all();
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

        $validate = $this->validateProductForm($input, $id);
        if ($validate->passes()) {
            //$shop_id = session('user_shop_id');
            $data_arr['shop_id'] = $result->shop_id;
            $data_arr['updated_by'] = Auth::guard('admin_user')->user()->id;
            $data_arr['updated_from'] = 'admin';
            $this->saveProduct($request, $data_arr, $id);
            $msg_text = Lang::get('product.product_updated_successfully');
            return redirect()->action('Admin\Product\ProductController@index')->with('succMsg', $msg_text);
        }else{
            // dd($validate->errors());
            return redirect()->action('Admin\Product\ProductController@edit', $id)->withInput()->withErrors($validate->errors());
        } 


        
       
    }

  
    /*public function copystore($id=null){
        
        $result = \App\Product::where('id',$id)->first();

        $description = isset($result->productDesc->description)?$result->productDesc->description:'';
       
        $result =  $result->toArray();

        if(empty($result)){
            abort(404);
        }else{
            $tier_price = [];
            $tierPrices =  \App\ProductTierPrice::where('product_id', $id)->get();
            foreach ($tierPrices as $key => $value) {
               $tier_price['tier_price']['min_qty'][$key] = $value->start_qty;
               $tier_price['tier_price']['max_qty'][$key] = $value->end_qty;
               $tier_price['tier_price']['tier_unit_price'][$key] = $value->unit_price;     
            }
            $result['product_cat'] = $result['cat_id'];
            $result['product_badge'] = $result['badge_id'];
            $result['unit'] = $result['unit_id'];
            $result['description'] = $description;
            
            $result  = array_merge($result, $tier_price);

            $request = (object) $result;
            $data_arr['shop_id'] = $result['shop_id'];
            $data_arr['created_by'] = Auth::guard('admin_user')->user()->id;
            $data_arr['status'] = '0';
            $data_arr['created_from'] = 'admin';
            
            $newid = $this->saveProduct($request, $data_arr);
            
            $msg_text = Lang::get('product.product_copy_successfully');
            return redirect()->action('Admin\Product\ProductController@edit', $newid)->with('message', $msg_text);
        }    



    }*/


    public function copystore(Request $request, $id){  
        $input = $request->all();
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

        $validate = $this->validateProductForm($input);
        if ($validate->passes()) {
            //$shop_id = session('user_shop_id');
            $shop_id = $input['shop_id'];
            $data_arr['shop_id'] = $shop_id;
            $data_arr['created_by'] = Auth::guard('admin_user')->user()->id;
            $data_arr['created_from'] = 'admin';
            $this->saveProduct($request, $data_arr);
            $msg_text = Lang::get('product.product_added_successfully');
            return redirect()->action('Admin\Product\ProductController@index')->with('succMsg', $msg_text);
        }else{
            return  redirect()->action('Admin\Product\ProductController@copy', $id)->withInput()->withErrors($validate->errors());
            
        } 
           

    }



    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    

    public function deleteproduct($id) {
        $permission = $this->checkUrlPermission('delete_product');
        $result = \App\Product::where('id', $id)->first(); 
        if (!$result) {
            abort(404);
        }
        try{
            $product_images = \App\ProductImage::where('product_id',$id)->pluck('image')->toArray();
            if(count($product_images)){
                $product_images = array_unique($product_images);
                $del = $this->deletePrdImageByImages($product_images);
            }
           $result->delete();
           \App\MongoProduct::deleteData($id);
            $msg_text = Lang::get('product.product_delete_successfully');
            return redirect()->action('Admin\Product\ProductController@index')->with('succMsg', $msg_text);  
        }catch(Exception $e) {
            $msg_text = Lang::get('product.something_went_wrong');
            return json_encode(array('status'=>'validate_error','message'=>$msg_text));
        }
    }


    public function deleteSelectedproducts(Request $request) {
        $permission = $this->checkUrlPermission('delete_product');
        //dd($request->ids);
        $ids = isset($request->ids)?$request->ids:null;
        if(count($ids)){
            foreach($ids as $id){
                //dd($id);
                $result = \App\Product::where('id', $id)->first(); 
                //dd($result);
                if (!$result){
                    abort(404);
                }
                $product_images = \App\ProductImage::where('product_id',$id)->pluck('image')->toArray();
                if(count($product_images)){
                    $product_images = array_unique($product_images);
                    $del = $this->deletePrdImageByImages($product_images);
                }
                $result->delete();
                \App\MongoProduct::deleteData($id);
                /*try{
                   $result->delete();
                   \App\MongoProduct::deleteData($id);
                    //$msg_text = Lang::get('product.product_delete_successfully');
                    //return redirect()->action('Admin\Product\ProductController@index')->with('succMsg', $msg_text);  
                }catch(Exception $e) {
                    $msg_text = Lang::get('product.something_went_wrong');
                    return json_encode(array('status'=>'validate_error','message'=>$msg_text));
                }*/
            }

            return ['status'=>'success'];
            //$msg_text = Lang::get('product.product_delete_successfully');
            //return redirect()->action('Admin\Product\ProductController@index')->with('succMsg', $msg_text); 
        }else{
            return ['status'=>'unsuccess'];
           /* $msg_text = Lang::get('product.something_went_wrong');
            return json_encode(array('status'=>'validate_error','message'=>$msg_text));*/

        }    
    }
    
    public function changeStatusofSelectedproducts(Request $request){
        $permission = $this->checkUrlPermission('edit_product'); 
        $status = isset($request->status)?$request->status:null;
        $ids = isset($request->ids)?$request->ids:null;
        //dd($ids);
        if(count($ids) && $status !== null){
            foreach ($ids as $id) {
                $prodata = \App\Product::where('id', $id)->first();
                if(!empty($prodata)){
                   $prodata->status = $status;
                   $prodata->save();
                   $id = $prodata->id; 
                   \App\MongoProduct::updateStatus($id, $status); 
                }    
            }
            return ['status'=>'success'];
        }else{
           return ['status'=>'unsuccess'];
        } 
    }

    public function baseUnit($cat_id=null){
        if(!empty($cat_id)){
            $default_lang = '0';
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

    public function deleteProductManual(Request $request){
        $permission = $this->checkUrlPermission('delete_product_manual');
        if($permission === true) {
            return view('admin.product.deleteProduct',[
                    'duplicateArr'=>[],
                    'percentage'=>0,
                    'actionInsert'=>'0',
                    'class'=>'',
                    'globalstate'=>[]
             ]);
        }

    }

    public function deleteProductBySky(Request $request){  
        $permission = $this->checkUrlPermission('delete_product_manual');
        if($permission === true) {
            $productImportMessage = $productImportErrorMessage = [];
            $delete_type = $request->delete_type;
            $user_id = Auth::guard('admin_user')->user()->id;
            $errors = [];
            $success = [];
            $p_id_arr = [];
            $all_skus = '';
            $mtime = microtime();
            $mtime = explode(" ",$mtime);
            $mtime = $mtime[1] + $mtime[0];
            $starttime = $mtime;
            if($delete_type == 'sku'){
                $sku = $request->sku;
                $productData = Product::where('sku', $sku)->first();
                if($productData){
                    $p_id_arr[] = $productData->id;
                    $product_images = \App\ProductImage::whereIn('product_id',$p_id_arr)->pluck('image')->toArray();
                    if(count($product_images)){
                        $product_images = array_unique($product_images);
                        $del = $this->deletePrdImageByImages($product_images);
                    }

                    $response = Product::where('id',$productData->id)->delete();
                    $monog_del = \App\MongoProduct::deleteData($productData->id);
              
                    $logdetails = "Admin has deleted product with sku $sku "; 

                    //Prepaire array for send data
                    $logdata = array('action_type' =>"deleted",'module_name' =>"Product",'logdetails' =>$logdetails);
                    //Call method in module
                    $this->updateLogActivity($logdata);

                    $productImportMessage[] = $sku.' product has deleted with this SKU <br/>';
                    $percentage = 100;
                    $class='progress-bar-success';
                }else{
                    $percentage = 0;
                    $class='progress-bar-warning';
                    $productImportErrorMessage[] = $sku.' this product sku not exist in the database<br>'; 
                }
                $mtime = microtime();
                $mtime = explode(" ",$mtime);
                $mtime = $mtime[1] + $mtime[0];
                $endtime = $mtime;
                $totaltime = ($endtime - $starttime);
                $globalstate = [];
                $globalstate['Total Time']=floatval($totaltime).' seconds';
                $globalstate['Percentage']=$percentage.' %';
                $globalstate['Success']="<font color='green'><b>".count($productImportMessage).' Items (100%)'."</b></font>";
                $globalstate['Failed']=count($productImportErrorMessage);
                $globalstate['Warning']=count($productImportErrorMessage);
                if(!empty($productImportErrorMessage)){
                    $globalstate['WarningMess']=$productImportErrorMessage;
                }
                if(!empty($productImportMessage)){
                    $globalstate['SuccessMess'] = $productImportMessage;
                }

                return view('admin.product.deleteProduct',[
                    'duplicateArr'=>$errors,
                    'percentage'=>(int) $percentage,
                    'actionInsert'=>'1',
                    'class'=>$class,
                    'globalstate'=>$globalstate
                  ]);
            }elseif ($delete_type=='daterange') {
                $from_date = date('Y-m-d',strtotime($request->from_date));
                $to_date = date('Y-m-d',strtotime($request->to_date));
                $product_data = Product::whereDate('created_at', '>=', $from_date)
                        ->whereDate('created_at', '<=', $to_date)->select('id','sku')->get();
                if(count($product_data)){
                    $all_sku=[];
                    foreach ($product_data as $key => $value) {
                        $all_sku[] = $value->sku;
                        $product_images = \App\ProductImage::where('product_id',$value->id)->pluck('image')->toArray();
                        if(count($product_images)){
                            $product_images = array_unique($product_images);
                            $del = $this->deletePrdImageByImages($product_images);
                        }

                        $response = Product::where('id',$value->id)->delete();
                        $monog_del = \App\MongoProduct::deleteData($value->id);
                    }

                    if(count($all_sku)){
                        $all_skus = implode(', ',$all_sku);
                        $productImportMessage[] = $all_skus.' product has deleted with this SKU <br/>';
                        $percentage = 100;
                        $class='progress-bar-success';
                    }
              
                    $logdetails = "Admin has deleted product with sku $all_skus "; 

                    //Prepaire array for send data
                    $logdata = array('action_type' =>"deleted",'module_name' =>"Product",'logdetails' =>$logdetails);
                    //Call method in module
                    $this->updateLogActivity($logdata);
                }else{
                    $percentage = 0;
                    $class='progress-bar-warning';
                    $productImportErrorMessage[] = 'No record exist in this date range'; 
                }

                $mtime = microtime();
                $mtime = explode(" ",$mtime);
                $mtime = $mtime[1] + $mtime[0];
                $endtime = $mtime;
                $totaltime = ($endtime - $starttime);
                $globalstate = [];
                $globalstate['Total Time']=floatval($totaltime).' seconds';
                $globalstate['Percentage']=$percentage.' %';
                $globalstate['Success']="<font color='green'><b>".count($productImportMessage).' Items (100%)'."</b></font>";
                $globalstate['Failed']=count($productImportErrorMessage);
                $globalstate['Warning']=count($productImportErrorMessage);
                if(!empty($productImportErrorMessage)){
                    $globalstate['WarningMess']=$productImportErrorMessage;
                }
                if(!empty($productImportMessage)){
                    $globalstate['SuccessMess'] = $productImportMessage;
                }

                return view('admin.product.deleteProduct',[
                    'duplicateArr'=>$errors,
                    'percentage'=>(int) $percentage,
                    'actionInsert'=>'1',
                    'class'=>$class,
                    'globalstate'=>$globalstate
                  ]);

            }elseif ($delete_type=='csv') {

                $mtime = microtime();
                $mtime = explode(" ",$mtime);
                $mtime = $mtime[1] + $mtime[0];
                $starttime = $mtime;

                $this->validate($request, ['import_file' => 'required|mimes:csv,txt']);
                $csv_folder_path = '/public/csv/';            

                $imageName = md5(microtime()).time().'.'.$request->file('import_file')->getClientOriginalExtension();
                $original_ext = $request->file('import_file')->getClientOriginalExtension();
                if($original_ext=='csv'){
                    //check if directory exists
                    if(!File::exists(base_path().$csv_folder_path)){
                     File::makeDirectory(base_path().$csv_folder_path, 0777, true, true);
                    }else{
                      //directory already exists;
                    }

                    //upload image in directory
                    $response = $request->file('import_file')->move(base_path().$csv_folder_path, $imageName);
                    $filename_with_path = base_path().$csv_folder_path.$imageName;
                    if($response){
                        header('Content-Type: text/html; charset=UTF-8');
                        $fileD = fopen($filename_with_path,"r");
                        $column = fgetcsv($fileD); // csv first row column

                        $column = array_filter($column);
                        $column = array_map('trim', $column);
                        $column = array_map('strtolower', $column);

                        if(empty($column)){
                          $errors[] = 'some thing is wrong in this sheet.';  
                          //return false;
                        }
                        //put mapped column name over here
                        $info_fields = self::mappedColumn($column);
                        $to_delete = array(false, 'false');
                        $info_fields= array_diff($info_fields, $to_delete);

                        while(!feof($fileD)){
                           $rowData[]= fgetcsv($fileD);
                        }

                        $rowData = array_filter($rowData);
                        if(empty($rowData)){
                           $errors[] = 'please enter some data in the sheet.'; 
                        }
                        $duplicate = [];
                        $sku_index = $info_fields['sku_index'];
                        foreach($rowData as $key=>$value){
                              if(isset($value[$sku_index]) && !empty($value[$sku_index])){
                                $duplicate[] = $value[$sku_index];
                              }
                        }
                        $to_delete = array('1', 1);
                        $duplicateSkuCounts = array_diff(array_count_values($duplicate), $to_delete);
                        if(count($duplicateSkuCounts) > 0){
                             foreach($duplicateSkuCounts as $key=>$duplicateSkuCount){
                               //echo $key.'====>'.$duplicateSkuCount.'<br/>';
                             }
                             $errors[] = 'Duplicate skus in the sheet.'; 
                        }
                        $product_img_arr = [];
                        $pid_arr = [];
                        if(empty($errors)){
                            foreach($rowData as $key=>$value){
                                $p_id_arr = [];
                                $all_skus = '';
                                $sku = $value[$sku_index];
                                $productData = Product::where('sku', $sku)->first();
                                if($productData){
                                    array_push($p_id_arr,(Integer)$productData->id);
                                    $product_images = \App\ProductImage::whereIn('product_id',$p_id_arr)->pluck('image')->toArray();
                                    if(count($product_images)){
                                        $product_images = array_unique($product_images);
                                        $del = $this->deletePrdImageByImages($product_images);
                                    }

                                    // for keeping log only get all skus
                                    $all_sku = $productData->sku;

                                    $response = Product::whereIn('id',$p_id_arr)->delete();
                                    $monog_del = \App\MongoProduct::deleteData($productData->id);

                                    $logdetails = "Admin has deleted product with sku $all_sku "; 

                                    //Prepaire array for send data
                                    $logdata = array('action_type' =>"deleted",'module_name' =>"Product",'logdetails' =>$logdetails);
                                    //Call method in module
                                    $this->updateLogActivity($logdata);
                                    $productImportMessage[] = $all_sku.' product has deleted with this SKU <br/>';
                                }else{
                                    $productImportErrorMessage[] = $sku.' this product sku not exist in the database<br>';
                                }
                            }

                            $success['status'] = 'success';
                            $success['message'] = ' Imported';
                        }else{
                           
                          $success['status'] = 'fail';
                          $success['message'] = 'upload failure';

                        }    
                
                        /*Uploaded Csv Remove From server*/
                        @unlink($filename_with_path);

                        $mtime = microtime();
                        $mtime = explode(" ",$mtime);
                        $mtime = $mtime[1] + $mtime[0];
                        $endtime = $mtime;
                        $totaltime = ($endtime - $starttime);
                        $totalRows=count($rowData);
                        $totalInsert = count($productImportMessage);
                        $percentage = 0 ;
                        if($totalInsert > 0){
                            $percentage=($totalInsert/$totalRows)*100;
                        }

                        $class='';
                        if($percentage>=0 && $percentage<30){ $class='progress-bar-danger';}
                        if($percentage>30 && $percentage<50){ $class='progress-bar-warning';}
                        if($percentage>50 && $percentage<80){ $class='progress-bar-info';}
                        if($percentage>80 && $percentage<=100){ $class='progress-bar-success';}

                        //Set all Global state Of this Import
                        $percentage=(int) $percentage;
                        $globalstate['Total Time']=floatval($totaltime).' seconds';
                        $globalstate['Percentage']=$percentage.' %';
                        $globalstate['Success']="<font color='green'><b>".count($productImportMessage).' Items (100%)'."</b></font>";
                        $globalstate['Failed']=count($productImportErrorMessage);
                        $globalstate['Warning']=count($productImportErrorMessage);
                        if(!empty($productImportErrorMessage)){
                            $globalstate['WarningMess']=$productImportErrorMessage;
                        }
                        if(!empty($productImportMessage)){
                            $globalstate['SuccessMess'] = $productImportMessage;
                        }

                        if(!empty($notImportcategoryWithProductsku)){
                            $globalstate['categoryNotUploaded'] = $notImportcategoryWithProductsku;
                        }


                        return view('admin.product.deleteProduct',[
                            'duplicateArr'=>$errors,
                            'percentage'=>(int) $percentage,
                            'actionInsert'=>'1',
                            'class'=>$class,
                            'globalstate'=>$globalstate
                          ]);
                    }

                }

            } 

        }       

    }

    public static function mappedColumn($column=null, $languages=null){
                /*main product fields*/
                $sku = 'sku';
                $id  = 'id';
                //getting index of all in column
                $product_info = [
                  'sku_index'=>array_search($sku, $column),
                  'id_index'=>array_search($id, $column)
                ];
                return $product_info;

    }

    public function deleteUnusedProductImage(Request $request){

        
    }

}
