<?php
namespace App\Http\Controllers\Admin\Customer;

use Illuminate\Database\QueryException;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Validator;

use App\Helpers\GeneralFunctions;
use App\Helpers\EmailHelpers;
use App\Helpers\CustomHelpers;
use App\CustomerGroup;
use App\MongoShop;
use App\User;
use App\Shop;
use App\SellerData;
use App\ShopDesc;
use App\Seller;
use App\ShopAssignCategory;
use Exception;
use Auth;
use Lang;
use DB;
use Session;
use Config;


class SellerController extends MarketPlace
{ 
    
    public $last_user__filter_list;
    private $module_name = "seller";
    public function __construct() {
        $this->middleware('admin.user');
        $this->tblUser = with(new \App\User)->getTable();
    }

    public function index(){
        $permission = $this->checkUrlPermission('list_seller');
        if($permission === true) {

            $filter = $this->getFilter('seller');
            
            return view('admin.customer.listSeller', ['filter'=>$filter]);
        }
    }

    function sellerData(Request $request){
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
            
            $query = \DB::table(with(new User)->getTable().' as u')
                  ->join(with(new Shop)->getTable().' as s', 'u.id', '=', 's.user_id')
                  ->join(with(new Seller)->getTable().' as se', 'u.id', '=', 'se.user_id')
                 
                  ->join(with(new ShopDesc)->getTable().' as sd', 's.id', '=', 'sd.shop_id')
                  ->select('u.id','u.display_name','s.shop_url','sd.shop_name','u.ph_number','u.register_from','u.dob','s.created_at','s.updated_at','s.panel_no','u.email','u.status','u.verified','se.citizen_id','se.account_name','se.account_no','se.branch','s.seller_unique_id','s.shop_status')
                  ->where('u.user_type','seller');
            
            if(isset($request->pq_filter)){
                $filter_req = json_decode($request->pq_filter,true);
                if(!empty($filter_req['data'])){
                    $filter_arr = $filter_req['data'];
                    foreach ($filter_arr as $fkey => $fvalue) {

                        $searchval = $fvalue['value'];
                        switch ($fvalue['dataIndx']) {
                            case 'display_name':$query->where('u.display_name','like', '%'.$searchval.'%'); break;
                            case 'email':$query->where('u.email','like', '%'.$searchval.'%'); break;
                            case 'panel_no':$query->where('s.panel_no','like', '%'.$searchval.'%'); break;
                            case 'ph_number':$query->where('u.ph_number','like', '%'.$searchval.'%'); break;
                            case 'shop_url':$query->where('s.shop_url','like', '%'.$searchval.'%'); break;
                            case 'shop_name':$query->where('sd.shop_name','like', '%'.$searchval.'%'); break;
                            case 'register_from':$query->whereIn('u.register_from',$searchval); break;
                            case 'status':$query->whereIn('u.status',$searchval); break;
                            case 'verified':$query->whereIn('u.verified',$searchval); break;
                            case 'account_no':$query->where('se.account_no','like', '%'.$searchval.'%'); break;
                            case 'account_name':$query->where('se.account_name','like', '%'.$searchval.'%'); break;
                            case 'branch':$query->where('se.branch','like', '%'.$searchval.'%'); break;
                            case 'citizen_id':$query->where('se.citizen_id','like', '%'.$searchval.'%'); break;
                            case 'seller_unique_id':$query->where('sl.seller_unique_id','like', '%'.$searchval.'%'); break;
                            case 'dob':
                                $from_date = $fvalue['value']??'';
                                $to_date = $fvalue['value2']??'';
                                createDateFilter($query,'u.dob',$from_date,$to_date);
                            break;
                            case 'shop_status':$query->whereIn('s.shop_status',$searchval); break;
                            case 'created_at':
                                $from_date = $fvalue['value']??'';
                                $to_date = $fvalue['value2']??'';
                                createDateFilter($query,'s.created_at',$from_date,$to_date);
                            break;
                            case 'updated_at':
                                $from_date = $fvalue['value']??'';
                                $to_date = $fvalue['value2']??'';
                                createDateFilter($query,'s.updated_at',$from_date,$to_date);
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

            /***save filter****/
            $this->setFilter('seller',$request);

            
        }catch(QueryException $e){
            $response = ['status'=>'fail','msg'=>$e->getMessage()];
        }
        

        return $response;
    }


    public function addSeller(Request $request){

        $bank_list = \App\PaymentBank::activeBankList();

        return view('admin.customer.addSeller',['bank_list'=>$bank_list]);
    }

    public function saveSeller(Request $request){
        //dd($request->all());
        $input = $request->all();
        unset($input['_token']);        
        $validate = $this->validateSeller($input);
        
        if ($validate->passes()) {
            try{

                $check_panel_citizen = $this->checkPanelCitizen($request);
                if(isset($check_panel_citizen['status']) && $check_panel_citizen['status']=='fail'){
                    return redirect()->action('Admin\Customer\SellerController@addSeller')->with('errorMsg', $check_panel_citizen['msg'])->withInput();
                }

                $default_group_id = \App\CustomerGroup::select('id','require_approve')->where(['is_default'=>'1','status'=>'1'])->first();
                $group_id = $default_group_id->id;
                $user = new \App\User;
                $user->register_from = 'admin';

                if(!empty($request->email)){
                    $user->email = cleanValue($request->email);
                }
                
                if(!empty($request->ph_number)){
                    $user->ph_number = cleanValue($request->ph_number);
                }
                
                $user->login_use = $request->loginuse;
                if(isset($request->password)){
                    $user->password = bcrypt($request->password);
                }
                
                $user->first_name = cleanValue($request->first_name);
                $user->last_name = cleanValue($request->last_name);
                $user->display_name = $user->first_name.' '.$user->last_name;
                //$user->gender = $request->gender;
                $user->dob = date('Y-m-d', strtotime($request->dob));
                $user->register_ip = userIpAddress();
                $user->group_id = $group_id;
                $user->user_type = 'seller';
                $user->facebook_id = '';
                $user->verified = '1';
                $user->email_token = '';
                $user->register_step = 1;
                $user->status = '1';
                $email_required = 'no';
                $otp_required = 'no';

                if($user->save()){
                    $user_id = $user->id; 

                    $panel_citizen = \App\SellerData::where(['panel_id'=>$request->panel_no,'citizen_id'=>$request->citizen_id])->first();
                    // save data in seller db
                    $sellerObj = new \App\Seller;
                    $sellerObj->user_id = $user_id;
                    $sellerObj->citizen_id = $request->citizen_id;
                    $sellerObj->bank_id = $request->bank_id;
                    $sellerObj->bank_branch_id = $request->branch_id;
                    $sellerObj->account_name = $request->account_name;
                    $sellerObj->account_no = $request->account_no;
                    $sellerObj->branch = $request->branch;
                    
                    $path = Config::get('constants.customer_path');
                    if(isset($request->citizen_id_image)){
                        $ext = pathinfo($request->citizen_id_image->getClientOriginalName(), PATHINFO_EXTENSION);
                        $id_image_name = mt_rand(100,999).time().'citizen.'.$ext;
                        $this->uploadImage($id_image_name, $request->citizen_id_image, $path);
                        $sellerObj->citizen_id_image = $id_image_name;
                    }

                    if(isset($request->account_image)){
                        $cc_ext = pathinfo($request->account_image->getClientOriginalName(), PATHINFO_EXTENSION);
                        $acc_image_name = mt_rand(100,999).time().'account.'.$cc_ext;
                        $this->uploadImage($acc_image_name, $request->account_image, $path);
                        $sellerObj->account_image = $acc_image_name;
                    }

                    $sellerObj->save();

                    $shopObj = new \App\Shop;
                    $shopObj->user_id = $user_id;
                    $shop_url = str_replace(' ','-',strtolower($request->shop_url));
                    $shopObj->shop_url = $shop_url;
                    $shopObj->panel_no = $request->panel_no;
                    $shopObj->citizen_id = $request->citizen_id;
                    $shopObj->seller_unique_id = $request->seller_unique_id;
                    $shopObj->seller_description = $request->seller_description;

                    if($shopObj->save()){
                        $shopDescObj = new \App\ShopDesc;
                        $shopDescObj->shop_id = $shopObj->id;
                        $shopDescObj->lang_id = session('default_lang');
                        $shopDescObj->shop_name = $request->shop_name;
                        $shopDescObj->save();

                        /***update shop data into mongo******/
                        $shop_data = \App\Shop::where('id',$shopObj->id)->with('allDesc')->with('shopUser')->first();
                        $check_cat = \App\ShopAssignCategory::where(['shop_id'=>$shopObj->id])->pluck('category_id')->toArray();
                        $shop_data->shop_category = $check_cat;
                        $update_data = MongoShop::updateData($shop_data);
                        
                        if($update_data['status'] == 'fail'){
                            $return = redirect()->action('Admin\Customer\SellerController@addSeller')->with('errorMsg', Lang::get('admin_customer.seller_add_error_in_mongo'))->withInput();
                        }
                    }

                    $message = Lang::get('admin_customer.seller_add_successfully');
                    
                    if($request->submit_type == 'save_and_continue') {
                        $return = redirect()->action('Admin\Customer\UserController@show', $user_id)->with('succMsg', Lang::get('admin.seller_added_successfully'));
                    }
                    else{
                        $return = redirect()->action('Admin\Customer\SellerController@addSeller')->with('succMsg', Lang::get('admin.seller_added_successfully'));
                    }

                }else{

                    $return = redirect()->action('Admin\Customer\SellerController@addSeller')->with('errorMsg', Lang::get('admin_customer.seller_add_error'))->withInput();
                }
            }
            catch(Exception $e){
                $return = redirect()->action('Admin\Customer\SellerController@addSeller')->with('errorMsg', $e->getMessage())->withInput();
            }
        }else{
            $return = redirect()->action('Admin\Customer\SellerController@addSeller')->withErrors($validate)->withInput();    
        }

        return $return;
    }

    public function checkPanelCitizen($request){
        $panel_citizen = \App\SellerData::where(['panel_id'=>$request->panel_no,'citizen_id'=>$request->citizen_id])->count();
        if(empty($panel_citizen)){
            return['status'=>'fail','msg'=>Lang::get('shop.seller_not_found_on_this_panel_citizen_id')];
        }

        $check_exist = \App\SellerTemp::where(['panel_no'=>$request->panel_no,'citizen_id'=>$request->citizen_id])->where('user_id','!=',$request->user_id)->count();
        $check_shop = \App\Shop::where(['panel_no'=>$request->panel_no,'citizen_id'=>$request->citizen_id])->where('user_id','!=',$request->user_id)->count();

        if($check_exist > 0 || $check_shop>0){
            $response = ['status'=>'fail','msg'=>Lang::get('shop.this_panel_and_citizen_id_already_exist')];
        }else{
            $response = ['status'=>'success'];
        }
        return $response;

    }

    public function show($id){

        $permission = $this->checkUrlPermission('list_seller');
        $tblShopDesc =  with(new ShopDesc)->getTable();
        if($permission === true) {

            $user = User::where('id', $id)->first();
            //dd($user);
            $shop_details = $map_images = $shop_images = $seller_data = $prd_categories = $category_data = $favoriteShopList = $fav_products = [];
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

                    $shop_res = [];
                    foreach ($favoriteShopList as $key => $value) {
                        $category_name = '';

                        $mongoShopData = \App\MongoShop::where(['status'=>'1','shop_url'=>$value->getShops->shop_url])->first();

                        if(isset($mongoShopData->shop_category) && count($mongoShopData->shop_category)){
                            $mongocategory_data = \App\MongoCategory::whereIn('_id',$mongoShopData->shop_category)->pluck('category_name')->toArray();
                            if($mongocategory_data){
                                $category_name = implode(',', $mongocategory_data);
                            }
                        }

                        $lastUpdatedProduct = \App\MongoProduct::where(['shop_id'=>$value->shop_id])->with('category')->orderBy('updated_at','DESC')->first();

                        if(!is_null($lastUpdatedProduct)){
                            $date = new \DateTime($lastUpdatedProduct->updated_at);

                            $shop_res[$key]['last_updated_price'] = '<span class="date">'.$date->format("d/m/Y H:i").'</span>
                                                        <span class="update-price"><i class="fas fa-long-arrow-alt-right"></i> Update '.$lastUpdatedProduct->category->category_name.'</span>';
                        }else{
                            $shop_res[$key]['last_updated_price'] = 'NA';
                        }

                        $shop_res[$key]['shop_slug'] = $value->getShops->shop_url;
                        $shop_res[$key]['shop_category'] = $category_name;
                        $shop_res[$key]['logo'] = getImgUrl($value->getShops->logo,'logo');
                        $shop_res[$key]['shop_url'] = action('ShopController@index',$value->getShops->shop_url);
                        $shop_res[$key]['shop_name'] = isset($value->getShops->shopDesc->shop_name)?$value->getShops->shopDesc->shop_name : 'NA';
                        $shop_res[$key]['market'] = isset($value->getShops->seller_description)? $value->getShops->seller_description : 'NA';
                        $shop_res[$key]['avg_rating'] = isset($value->getShops->avg_rating)?(int)$value->getShops->avg_rating:0;
                        $shop_res[$key]['del_f_shop_url'] = action('User\UserController@deleteFavoriteShop',$value->getShops->id);

                    }

                    $favoriteShopList = $shop_res;

                    $fav_prd_req_array = ['shop_id'=>$user->shop_id,'blade'=>'wishlist','fillterAttributes'=>[],'search'=>'','itemsPerPage'=>10,'orderBy'=>'asc','order'=>''];

                    $fav_products_res = $this->getProductList(json_decode(json_encode($fav_prd_req_array)));
                    $fav_products = $fav_products_res['data'];
                }

                //dd($category_data);
                //Sdd($fav_products);
                return view('admin.customer.viewUser', ['user'=>$user,'shop_details'=>$shop_details,'map_images'=>$map_images,'shop_images'=>$shop_images,'seller_data'=>$seller_data,'prd_categories'=>$prd_categories,'categoryId'=>$categoryId,'category_data'=>$category_data,'tblShopDesc'=>$tblShopDesc,'favoriteShopList'=>$favoriteShopList,'fav_products'=>$fav_products]);
            }
        }   
    }

    public function getProductList($fav_prd_req_array){
        $shop_id = $fav_prd_req_array->shop_id;

        $blade = $fav_prd_req_array->blade;
        $filter_attributes = $fav_prd_req_array->fillterAttributes;
        $name = $fav_prd_req_array->search;
        $page_item = $fav_prd_req_array->itemsPerPage; 
        $order_by = $fav_prd_req_array->orderBy;
        $order = $fav_prd_req_array->order;
        //$cat_ids= null;

        $query = \App\MongoProduct::query();
        if(!is_null($shop_id))
            $query->where('shop_id',$shop_id);
        $query->where('status', '1')->with('badge')->with('category')->with('shop');
        if(Auth::check()){
            if($blade=='wishlist'){
                $query->has('wishlist');
            }
            $query->with('wishlist');    
        }

        $product_data = $query->paginate($page_item)->toArray();

        $product_data = $this->formatListingData($product_data);
        return $product_data;//['detail'=>$product_data,'status'=>'success'];
     
    }

    protected function validateSeller($input){
        $rules['first_name'] = nameRule();
        $rules['last_name'] = nameRule();
        $rules['dob'] = dateRule();
        $rules['panel_no'] = numberRule();
        $rules['shop_url'] = nameRule();
        $rules['shop_name'] = nameRule();
        $rules['citizen_id'] = reqRule();
        $rules['bank_id'] = reqRule();
        $rules['account_name'] = nameRule();
        $rules['account_no'] = numberRule();
        $rules['branch'] = nameRule();
        $rules['citizen_id_image'] = imageRule();
        $rules['account_image'] = imageRule();
        $rules['seller_unique_id'] = reqRule();
        if(isset($input['password']) && isset($input['password_confirm'])){
            $rules['password'] = passwordRule();
            $rules['password_confirm'] = confirmPasswordRule('password');
        }
        
        if(isset($input['loginuse']) && $input['loginuse']=='email'){
            $rules['email'] = emailRule($this->tblUser, 'email');
        }elseif(isset($input['loginuse']) && $input['loginuse']=='ph_no'){
            $rules['ph_number'] = phoneRule($this->tblUser, 'ph_number');
        }else{
            $rules['loginuse'] = reqRule();
        }        

        $error_msg['first_name.required'] = Lang::get('customer.enter_first_name');
        $error_msg['last_name.required'] = Lang::get('customer.enter_last_name');
        $error_msg['panel_no.required'] = Lang::get('customer.enter_panel_no');
        $error_msg['shop_url.required'] = Lang::get('customer.enter_shop_url');
        $error_msg['shop_name.required'] = Lang::get('customer.enter_shop_name');
        $error_msg['citizen_id.required'] = Lang::get('customer.enter_citizen_id');
        $error_msg['account_name.required'] = Lang::get('customer.enter_account_name');
        $error_msg['bank_id.required'] = Lang::get('customer.enter_bank_id');
        $error_msg['account_no.required'] = Lang::get('customer.enter_account_no');
        $error_msg['branch.required'] = Lang::get('customer.enter_branch');
        $error_msg['citizen_id_image.required'] = Lang::get('customer.choose_citizen_id_image');
        $error_msg['account_image.required'] = Lang::get('customer.choose_account_image');
        $error_msg['seller_unique_id.required'] = Lang::get('customer.vendor_code_required');
        if(isset($input['password']) && isset($input['password_confirm'])){
            $error_msg['password.required'] = Lang::get('customer.please_enter_password');
            $error_msg['password_confirm.required'] = Lang::get('customer.password_and_confirm_password_should_be_same'); 
        }

        if(isset($input['email'])){
            $error_msg['email.required'] = Lang::get('customer.please_enter_email');
            $error_msg['email.unique'] = Lang::get('customer.email_already_exist'); 
        }

        if(isset($input['ph_number'])){
            $error_msg['ph_number.required'] = Lang::get('customer.please_enter_phone_no');
            $error_msg['ph_number.unique'] = Lang::get('customer.phone_number_already_exist');
        }

        $error_msg['dob.required'] = Lang::get('customer.please_enter_dob');
        return $validate = Validator::make($input, $rules, $error_msg);
    }

    public function deleteSelectedSeller(Request $request) {
        $permission = $this->checkUrlPermission('delete_seller');
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
                $logdetails = "Admin has ".$action_type." seller ".$username." $this->module_name";
                $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

                $this->updateLogActivity($logdata);

                $update_shop = \App\Shop::where('user_id',$id)->update(['status'=>'0','shop_status'=>'close']);
                $result->status = '2';
                $result->save();
            }

            return ['status'=>'success'];
        }else{
            return ['status'=>'unsuccess'];
        }    
    }
    
    public function changeStatusofSelectedSeller(Request $request){
        $permission = $this->checkUrlPermission('edit_seller'); 
        $status = isset($request->status)?$request->status:0;
        $ids = isset($request->ids)?$request->ids:null;
        //dd($ids);
        if(count($ids) && $status !== null){
            foreach ($ids as $id) {
                $userdata = \App\User::where('id', $id)->first();
                if(!empty($userdata)){
                   $userdata->status = $status;
                   $userdata->save();
                   $shop_status = ($status==1)?"open":"close";
                   $update_shop = \App\Shop::where('user_id',$id)->update(['status'=>$status,'shop_status'=>$shop_status]);
                   $status_val = ($status)?'active':'inactive';
                    $action_type = "updated";             
                    $username = $userdata->email.' '.$userdata->ph_number;
                    $logdetails = "Admin has ".$action_type." seller ".$username." $this->module_name to ".$status_val;
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
