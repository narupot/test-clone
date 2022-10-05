<?php
namespace App\Http\Controllers\Admin\Customer;

use Illuminate\Database\QueryException;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Input;
use App\User;
use App\Shop;
use App\ShopDesc;
use App\Seller;
use App\ShopAssignCategory;
use App\MongoShop;
use Auth;
use Lang;
use DB;
use Session;
use Config;


class UserController extends MarketPlace
{ 
    
    public $last_user__filter_list;
    private $module_name = "customer";
    public function __construct() {
        $this->middleware('admin.user');
        $this->tblUser = with(new \App\User)->getTable();
    }

    public function index(){
        $permission = $this->checkUrlPermission('list_customer');
        if($permission === true) {
            
            $filter = $this->getFilter('user');

            return view('admin.customer.listCustomer', ['filter'=>$filter]);
        }
    }    

    function customerData(Request $request){

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
            $query = User::select('*');
            if(isset($request->pq_filter)){
                $filter_req = json_decode($request->pq_filter,true);
                if(!empty($filter_req['data'])){
                    $filter_arr = $filter_req['data'];
                    foreach ($filter_arr as $fkey => $fvalue) {

                        $searchval = $fvalue['value'];
                        switch ($fvalue['dataIndx']) {
                            case 'display_name':$query->where('display_name','like', '%'.$searchval.'%'); break;
                            case 'email':$query->where('email','like', '%'.$searchval.'%'); break;
                            case 'ph_number':$query->where('ph_number','like', '%'.$searchval.'%'); break;
                            case 'user_type':$query->whereIn('user_type',$searchval); break;
                            case 'register_from':$query->whereIn('register_from',$searchval); break;
                            case 'status':$query->whereIn('status',$searchval); break;
                            case 'verified':$query->whereIn('verified',$searchval); break;
                            case 'dob':
                                $from_date = $fvalue['value']??'';
                                $to_date = $fvalue['value2']??'';
                                createDateFilter($query,'dob',$from_date,$to_date);
                            break;
                            case 'created_at':
                                $from_date = $fvalue['value']??'';
                                $to_date = $fvalue['value2']??'';
                                createDateFilter($query,'created_at',$from_date,$to_date);
                            break;
                            case 'updated_at':
                                $from_date = $fvalue['value']??'';
                                $to_date = $fvalue['value2']??'';
                                createDateFilter($query,'updated_at',$from_date,$to_date);
                            break;
                            
                        }
                        
                    }
                }
            }
            $response = $query->orderBy($order_by,$order_by_val)->paginate($perpage,['*'],'page',$current_page);
			//dd($response);
            $totrec = $response->total();

            if($start_index >= $totrec) {
                $current_page = ceil($totrec/$perpage);
                
                $response = $query->orderBy($order_by,$order_by_val)->paginate($perpage,['*'],'page',$current_page);
            }
			
			foreach($response as $key=>$row){
				 $response[$key]->created_at_dt = getDateFormat($row->created_at,9);
                 $response[$key]->updated_at_dt = getDateFormat($row->updated_at,9);
			}

            /***save filter****/
            $this->setFilter('user',$request);

            
        }catch(QueryException $e){
            $response = ['status'=>'fail','msg'=>$e->getMessage()];
        }
        

        return $response;
    }

    public function show($id){
        //dd($id);
        $permission = $this->checkUrlPermission('list_customer');
        
        $tblShopDesc =  with(new ShopDesc)->getTable();
        if($permission === true) {

            $user = User::where('id', $id)->first();
            $shop_details = $map_images = $shop_images = $seller_data = $prd_categories = $category_data = $shop_res = $fav_products = $wishlistData = [];
            $categoryId = [];
            if(!empty($user)){
                if($user->user_type == 'seller'){
                    $shop_details = Shop::where('user_id',$user->id)->with(['shopDesc'])->first();
                    //dd($shop_details);
                    if(!empty($shop_details)){
                        $map_images = !empty($shop_details->map_image)?explode(',', $shop_details->map_image):[];
                        $shop_images = !empty($shop_details->shop_image)?explode(',', $shop_details->shop_image):[];
                        $seller_data = Seller::where('user_id',$user->id)->first();

                        $prd_categories = \App\Category::getMainCategory();

                        $categoryId = ShopAssignCategory::where('shop_id',$shop_details->id)->pluck('category_id')->toArray();

                        $category_data = ShopAssignCategory::getShopCategory($shop_details->id);
                        if(count($category_data)){
                            $categoryId = $category_data->pluck('id')->toArray();
                        }
                    }else{
                        /* if somehow seller shop not created then update user as buyer and delete related records from seller table */
                        $sellerData = \App\Seller::where('user_id',$user->id)->count();
                        if($sellerData){
                            \App\Seller::where('user_id',$user->id)->delete();
                            \App\User::where('id',$user->id)->update(['user_type'=>'buyer']);
                        }
                    }
                    //dd($category_data);

                    $userid = $user->id;
                    $favoriteShopList = \App\FavoriteShop::where('user_id',$userid)->with(['getShops'=>function($shop_sub_qry){
                            $shop_sub_qry->with('shopDesc');
                    }])->orderBy('add_date','desc')->get();

                    //dd($favoriteShopList);

                    foreach ($favoriteShopList as $key => $value) {
                        $category_name = '';

                        $lastUpdatedProduct = \App\MongoProduct::where(['shop_id'=>$value->shop_id])->with('category')->orderBy('updated_at','DESC')->first();

                        if(!is_null($lastUpdatedProduct)){
                            $date = new \DateTime($lastUpdatedProduct->updated_at);

                            $shop_res[$key]['last_updated_price'] = '<span class="date">'.$date->format("d/m/Y H:i").'</span>
                                                        <span class="update-price"><i class="fas fa-long-arrow-alt-right"></i> Update '.$lastUpdatedProduct->category->category_name.'</span>';
                        }else{
                            $shop_res[$key]['last_updated_price'] = 'NA';
                        }

                        //$shop_res[$key]['last_updated_price'] = 'NA';
                        $shop_res[$key]['shop_slug'] = $value->getShops->shop_url;
                        $shop_res[$key]['shop_category'] = $category_name;
                        $shop_res[$key]['logo'] = getImgUrl($value->getShops->logo,'logo');
                        $shop_res[$key]['shop_url'] = action('ShopController@index',$value->getShops->shop_url);
                        $shop_res[$key]['shop_name'] = isset($value->getShops->shopDesc->shop_name)?$value->getShops->shopDesc->shop_name : 'NA';
                        $shop_res[$key]['market'] = isset($value->getShops->seller_description)? $value->getShops->seller_description : 'NA';
                        $shop_res[$key]['avg_rating'] = isset($value->getShops->avg_rating)?(int)$value->getShops->avg_rating:0;
                        $shop_res[$key]['del_f_shop_url'] = action('User\UserController@deleteFavoriteShop',$value->getShops->id);

                    }

                    $wishlistDataArray = $this->getProductByWishlist($user->id);
                    $wishlistData = isset($wishlistDataArray['detail']['data'])?$wishlistDataArray['detail']['data']:[];
                }

                //dd($wishlistDataArray['detail']['data']);
                return view('admin.customer.viewUser', ['user'=>$user,'shop_details'=>$shop_details,'map_images'=>$map_images,'shop_images'=>$shop_images,'seller_data'=>$seller_data,'prd_categories'=>$prd_categories,'categoryId'=>$categoryId,'category_data'=>$category_data,'tblShopDesc'=>$tblShopDesc,'favoriteShopList'=>$shop_res,'fav_products'=>$wishlistData]);
            }
        }   
    }

    public function getProductByWishlist($id){
        //$request->request->add(['blade'=>'wishlist']);
        $req_bag = ['blade'=>'wishlist','id'=>$id,'search'=>'','fillterAttributes'=>[],'orderBy'=>'name','order'=>'asc','itemsPerPage'=>10,'shop_id'=>null];
        return $this->getProductList(json_decode(json_encode($req_bag)));
    }

    public function getProductList($req_bag){
        $shop_id = null;
        //dd($req_bag);
        if($req_bag->shop_id){
            $shop_id = $req_bag->shop_id;
        }

        $blade = $req_bag->blade;
        //$filter_attributes = $request->fillterAttributes;
        $name = $req_bag->search;
        $page_item = $req_bag->itemsPerPage; 
        $order_by = $req_bag->orderBy;
        $order = $req_bag->order;
        //$cat_ids= null;

        $query = \App\MongoProduct::query();
        if(!is_null($shop_id))
            $query->where('shop_id',$shop_id);
        $query->where('status', '1')->with('badge')->with('category')->with('shop');
        if($req_bag->id){
            if($blade=='wishlist'){
                $query->has('wishlist');
            }
            $query->with('wishlist');    
        }

        $product_data = $query->paginate($page_item)->toArray();

        $product_data = $this->formatListingData($product_data);
        return ['detail'=>$product_data,'status'=>'success'];
     
    }

    public function saveBuyer(Request $request){
        $input = $request->all();
        //dd($input);
        unset($input['_token']);
        $user = \App\User::find($request->id);

        if(isset($request->email) && $user->email==$request->email){
            unset($input['email']);
        }

        if(isset($request->ph_number) && $user->ph_number==$request->ph_number){
            unset($input['ph_number']);
        }

        if(!isset($request->change_password)){
            unset($input['password']);
            unset($input['password_confirm']);
        }

        $validate = $this->validateBuyer($input);
        if ($validate->passes()) {

            $default_group_id = \App\CustomerGroup::select('id','require_approve')->where(['is_default'=>'1','status'=>'1'])->first();
            $group_id = $default_group_id->id;
            // upload customer image 
            
            if(isset($input['image']) && !empty($input['image'])){
                $image_parts = explode(";base64,", $input['image']);
                $extension = last(explode('/', $image_parts[0]));
                $file_name = time().mt_rand().'.'.$extension;
                $profile_img_path = Config::get('constants.user_path');
                $opload_response = $this->base64UploadImage($input['image'],$profile_img_path,$file_name);
                $user->image = $file_name;
            }
            
            // end
            
            $user->register_from = 'admin';

            if(!empty($request->email) && $user->email!=$request->email){
                $user->email = cleanValue($request->email);
            }
            
            if(!empty($request->ph_number) && $user->ph_number!==$request->ph_number){
                $user->ph_number = cleanValue($request->ph_number);
            }
            
            $user->login_use = $request->loginuse;

            if(isset($request->change_password)){
                $user->password = bcrypt($request->password);
            }
            
            $user->first_name = cleanValue($request->first_name);
            $user->last_name = cleanValue($request->last_name);
            $user->display_name = $user->first_name.' '.$user->last_name;
            //$user->gender = $request->gender;
            $user->dob = date('Y-m-d', strtotime($request->dob));
            $user->register_ip = userIpAddress();
            $user->group_id = $group_id;

            $user->facebook_id = '';
            $user->verified = '1';
            $user->email_token = '';
            $user->register_step = 1;
            $user->status = '1';
            $email_required = 'no';
            $otp_required = 'no';

            if($user->save()){
                $return = redirect()->action('Admin\Customer\UserController@show', $user->id)->with('succMsg', Lang::get('admin.buyer_updated_successfully'));
            }else{
                $return = redirect()->action('Admin\Customer\UserController@show',$user->id)->with('errorMsg', Lang::get('admin_customer.buyer_update_error'));
            }

        }else{
            $return = redirect()->action('Admin\Customer\UserController@show', $user->id)->withErrors($validate)->withInput();  
        }
        return $return;
    }

    /****
    ** when admin assign master product(category) to seller.
    ** only this master product seller can sell.
    ****/
    public function assignCategorySeller(Request $request){
        
        $input = $request->all();
        $rules['prd_cat_id'] = 'Required|Array|min:1';
        $error_msg['prd_cat_id.required'] = Lang::get('admin_customer.please_select_category');

        $validate = Validator::make($input, $rules, $error_msg);

        $shop_id = $request->shop_id;

        if ($validate->passes()) {
            try{
                $cat_id_arr = $request->prd_cat_id;

                $assign_cat_id = ShopAssignCategory::where('shop_id',$shop_id)->pluck('category_id')->toArray();
                $diff_id_arr = array_diff($assign_cat_id,$cat_id_arr);
                foreach ($cat_id_arr as $key => $value) {
                    $check_cat = ShopAssignCategory::where(['shop_id'=>$shop_id,'category_id'=>$value])->count();
                    if($check_cat < 1){
                        $cat_obj = new ShopAssignCategory;
                        $cat_obj->shop_id = $shop_id;
                        $cat_obj->category_id = $value;
                        $cat_obj->created_by = Auth::guard('admin_user')->user()->id;
                        $cat_obj->save();
                    }
                }
                if(count($diff_id_arr)){
                    ShopAssignCategory::whereIn('category_id',$diff_id_arr)->where('shop_id',$shop_id)->delete();
                }

                /***update shop data into mongo******/
                $shop_data = \App\Shop::where('id',$shop_id)->with('allDesc')->with('shopUser')->first();
                $check_cat = \App\ShopAssignCategory::where(['shop_id'=>$shop_id])->pluck('category_id')->toArray();

                $shop_data->shop_category = $check_cat;
                $update_data = \App\MongoShop::updateData($shop_data);

                $response = ['status'=>'success'];
            }catch(QueryException $e){
                $response = ['status'=>'fail','msg'=>$e->getMessage()];
            }
            
        }else{
            $response = ['status'=>'fail','msg'=>$validate->errors(),'error'=>'validation'];
        }
        return $response;
    }

    /***when admin update seller information***/
    public function updateShopInfo(Request $request){
        $input = $request->all();
        $user_id = Auth::id();
        $shop_id = $request->shop_id;
        $tblShop = with(new Shop)->getTable();
        $tblShopDesc = with(new ShopDesc)->getTable();
        $rules['shop_url'] = uniqueIgnoreRule($tblShop,'shop_url',$shop_id,'id');
        $rules['shop_name'] = uniqueIgnoreRule($tblShopDesc,'shop_name',$shop_id,'shop_id');
        $rules['seller_unique_id'] = reqRule();
        $error_msg['shop_url.required'] = Lang::get('shop.store_url_is_required');
        $error_msg['shop_name.required'] = Lang::get('shop.store_name_is_required');
        $error_msg['shop_url.unique'] = Lang::get('shop.store_url_already_exist');
        $error_msg['shop_name.unique'] = Lang::get('shop.store_name_already_exist');
        $error_msg['seller_unique_id.required'] = Lang::get('customer.vendor_code_required');
        unset($input['shop_name']);
        unset($input['_token']);

        if(isset($request->shop_name[session('default_lang')])){
            $input['shop_name'] = $request->shop_name[session('default_lang')];
        }
        

        $validate = Validator::make($input, $rules, $error_msg);
        if ($validate->passes()) {
            try {
                $change_data = [];
                $shop_info = Shop::find($shop_id);
                $shop_status = (isset($request->shop_status) && $request->shop_status=='1')?'open':'close';

                $bargaining = (isset($request->bargaining) && $request->bargaining=='1')?'yes':'no';
                if(!empty($request->shop_url)){
                    $shop_info->shop_url = createUrl($request->shop_url);
                }

                $shop_info->panel_no = $request->panel_no;
                /*$shop_info->market = $request->market;*/
                $shop_info->seller_description = $request->seller_description;
                $shop_info->shop_status = $shop_status;
                $shop_info->status = ($shop_status=='open')?'1':'0';
                $shop_info->bargaining = $bargaining;

                $shop_info->open_time = cleanValue($request->open_time);
                $shop_info->close_time = cleanValue($request->close_time);
                $shop_info->product_pickup_time = cleanValue($request->product_pickup_time);
                $shop_info->center_pickup_time = cleanValue($request->center_pickup_time);
                $shop_info->ph_number = $request->ph_number;
                $shop_info->line_link = $request->line_link;
                $shop_info->seller_unique_id = $request->seller_unique_id;
                $shop_info->seller_description = $request->seller_description;
                /**uploading map images***/
                if(isset($request->location_image) && count($request->location_image)){
                    $uploadDetails['path'] = Config::get('constants.shop_original_path');
                    $map_name_arr = !empty($shop_info->map_image)?explode(',',$shop_info->map_image):[];
                    foreach ($request->location_image as $key => $value) {
                        
                        $uploadDetails['file'] =  $value;
                        $fileName = $this->uploadFileCustom($uploadDetails);
                        $map_name_arr[] = $fileName;
                    }
                    if(count($map_name_arr)){
                        $shop_info->map_image = implode(',', $map_name_arr);
                    }
                }

                /***uploading shop images****/
                if(isset($request->shop_image) && count($request->shop_image)){
                    $uploadDetails['path'] = Config::get('constants.shop_original_path');
                    $shop_name_arr = !empty($shop_info->shop_image)?explode(',',$shop_info->shop_image):[];
                    foreach ($request->shop_image as $key => $value) {
                        
                        $uploadDetails['file'] =  $value;
                        $fileName = $this->uploadFileCustom($uploadDetails);
                        $shop_name_arr[] = $fileName;
                    }
                    if(count($shop_name_arr)){
                        $shop_info->shop_image = implode(',', $shop_name_arr);
                    }
                    
                }
                if(isset($request->banner_image) && !empty($request->banner_image)){
                   $extension = 'jpg'; 
                   $image_name = 'banner'.md5(microtime()).'.'.$extension;
                   $banner_image_dir_path = Config('constants.shop_img_path').'/';
                   $image = $request->banner_image;
                   $this->base64UploadImage($image, $banner_image_dir_path, $image_name);
                   if($shop_info->banner){
                        $this->fileDelete(Config('constants.shop_img_path').'/'.$shop_info->banner);
                   }
                   $shop_info->banner = $image_name;
                
                }

                if(isset($request->logo_image) && !empty($request->logo_image)){
                   $extension = 'jpg'; 
                   $image_name = 'logo'.md5(microtime()).'.'.$extension;
                   $logo_image_dir_path = Config('constants.shop_img_path').'/';
                   $image = $request->logo_image;
                   $this->base64UploadImage($image, $logo_image_dir_path, $image_name);
                   if($shop_info->logo){
                        $this->fileDelete(Config('constants.shop_img_path').'/'.$shop_info->logo);
                   }
                   $shop_info->logo = $image_name;
                
                }
                $shop_info->save();

                if (!$shop_info->wasRecentlyCreated) {
                    foreach ($shop_info->getChanges() as $key => $value) {
                        $change_data = array_merge($change_data,[$key=>$value]);
                    }
                }

                /***update shop name and description***/

                $lang_ids = \App\Language::where('status', '1')->pluck('id');
                foreach ($lang_ids as $lang_id) {
                    $shop_name = isset($request->shop_name[$lang_id])?$request->shop_name[$lang_id]:'';
                    $description = isset($request->description[$lang_id])?$request->description[$lang_id]:'';
                    $update_shop_desc_model = ShopDesc::updateOrCreate(['shop_id' => $shop_id, 'lang_id' => $lang_id],['shop_id' => $shop_id, 'lang_id' => $lang_id, 'shop_name' => $shop_name, 'description' => $description
                    ]);

                    if(!$update_shop_desc_model->wasRecentlyCreated) {
                        foreach ($update_shop_desc_model->getChanges() as $key => $value) {
                            $change_data = array_merge($change_data,[$key=>$value]);
                        }
                    }
                }

                /***update shop data into mongo******/
                $shop_data = Shop::where('id',$shop_id)->with('allDesc')->with('shopUser')->first();
                $update_data = MongoShop::updateData($shop_data);
                if($update_data['status'] == 'fail'){
                    return $update_data;
                }
                /** Logging category delete information **/
                $action_type = "updated"; //Change action name like: created,updated,deleted
                $module_name = "Seller";   //Changes module name like : blog etc         
                $logdetails = "Admin has updated seller information"; //Change update message as requirement 
                $old_data = "";//Optional old data in json format key and value as per requirement 
                //dd(json_encode($change_data));
                $new_data = json_encode($change_data); //Optional new data json format key and value as per requirement 

                //Prepaire array for send data
                $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );

                //Call method in module
                $this->updateLogActivity($logdata);
                /** Logging category delete information end **/

                
                $response = ['status'=>'success','msg'=>Lang::get('common.records_updated_successfully')];
                
            }catch(Exception $e){
                $response = ['status'=>'fail','msg'=>'','error'=>$e->getMessage()];
            }
        }else{
            $errors =  $validate->errors(); 
            $response = ['status'=>'fail','msg'=>$errors,'error'=>'validation'];
        }
        return $response;
    }

    protected function validateBuyer($input){
        $rules['first_name'] = nameRule();
        $rules['last_name'] = nameRule();

        if(isset($input['password']) && isset($input['password_confirm'])){
            $rules['password'] = passwordRule();
            $rules['password_confirm'] = confirmPasswordRule('password');
        }

        if(isset($input->dob) && $input->dob !=''){
            $rules['dob'] = dateRule('date');
        }         

        $error_msg['first_name.required'] = Lang::get('customer.enter_first_name');
        $error_msg['last_name.required'] = Lang::get('customer.enter_last_name');

        if(isset($input['password']) && isset($input['password_confirm'])){
            $error_msg['password.required'] = Lang::get('customer.please_enter_password');
            $error_msg['password_confirm.required'] = Lang::get('customer.password_and_confirm_password_should_be_same'); 
        }

        if(isset($input['email'])){
            $error_msg['email.required'] = Lang::get('customer.please_enter_email');
            $error_msg['email.unique'] = Lang::get('customer.email_already_exist'); 
        }

        if(isset($input['ph_number'])){
            $rules['ph_number'] = phoneRule($this->tblUser, 'ph_number');
            $error_msg['ph_number.required'] = Lang::get('customer.please_enter_phone_no');
            $error_msg['ph_number.unique'] = Lang::get('customer.phone_number_already_exist');
        }

        return $validate = Validator::make($input, $rules, $error_msg);
    }

    function deleteShopImg(Request $request){
        $shop_id = $request->shop_id;
        $shop_info = Shop::find($shop_id);
        
        if(!empty($request->type) && !empty($request->val) && $shop_info){
            $val = $request->val;
            if($request->type == 'shop'){
                
                $img_arr = $shop_info->shop_image?explode(',',$shop_info->shop_image):[];
            }else{
                $img_arr = $shop_info->map_image?explode(',',$shop_info->map_image):[];
            }

            if(count($img_arr)){
                $pos = array_search( $val , $img_arr );
                if(isset($img_arr[$pos])){

                    $this->fileDelete(Config::get('constants.shop_original_path').'/'.$val);
                    unset($img_arr[$pos]);
                    $str_img = count($img_arr)?implode(',', $img_arr):'';

                    if($request->type == 'shop')
                        $shop_info->shop_image = $str_img;
                    else
                        $shop_info->map_image = $str_img;
                    $shop_info->save();
                    $response = ['status'=>'success'];
                }else{
                    $response = ['status'=>'fail','msg'=>'no index'];
                }
                
            }
        }else{
            $response = ['status'=>'fail','msg'=>'no image'];
        }
        return $response;
    }

    public function deleteSelectedCustomers(Request $request) {
        $permission = $this->checkUrlPermission('delete_customer');
        //dd($request->ids);
        $ids = isset($request->ids)?$request->ids:null;
        if(count($ids)){
            foreach($ids as $id){
                
                $result = \App\User::where('id', $id)->first(); 
                if (!$result){
                    abort(404);
                }
                $action_type = "deleted";             
                $username = $result->email.' '.$result->ph_number;
                $logdetails = "Admin has ".$action_type." customer ".$username." $this->module_name";
                $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

                $this->updateLogActivity($logdata);
                $result->status = '2';
                $result->save();
                
                $update_shop = \App\Shop::where('user_id',$id)->update(['status'>'0']);
                
            }

            return ['status'=>'success'];
        }else{
            return ['status'=>'unsuccess'];
        }    
    }
    
    public function changeStatusofSelectedCustomer(Request $request){
        $permission = $this->checkUrlPermission('edit_customer'); 
        $status = isset($request->status)?$request->status:0;
        $ids = isset($request->ids)?$request->ids:null;
        //dd($ids);
        if(count($ids) && $status !== null){
            foreach ($ids as $id) {
                $userdata = \App\User::where('id', $id)->first();
                if(!empty($userdata)){
                   $userdata->status = $status;
                   $userdata->save();

                   $status_val = ($status)?'active':'inactive';
                    $action_type = "updated";             
                    $username = $userdata->email.' '.$userdata->ph_number;
                    $logdetails = "Admin has ".$action_type." customer ".$username." $this->module_name to ".$status_val;
                    $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

                    $this->updateLogActivity($logdata);
                }    
            }
            return ['status'=>'success'];
        }else{
           return ['status'=>'unsuccess'];
        } 
    }

}
