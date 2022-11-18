<?php
namespace App\Http\Controllers\Seller;
use Illuminate\Http\Request;
use App\Http\Requests;
use Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\MarketPlace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Session;
use Route;
use Cache;
use App\Helpers\GeneralFunctions;
use Lang;
use Config;
use Exception;
use App\Shop;
use App\ShopDesc;
use App\Seller;
use App\User;

class ShopController extends MarketPlace
{
    /**
     * Show the manage shop .
     *
     * @return \Illuminate\Http\Response
     */
    private $tblShop;
    private $tblShopDesc;
    public function __construct()
    {
        //$this->middleware('auth');
        $this->middleware('is-seller');
        $this->tblShop = with(new Shop)->getTable();
        $this->tblShopDesc = with(new ShopDesc)->getTable();
    }

    public function index(Request $request) {  
        /*$prd = \App\Product::select(\DB::raw('avg(unit_price) as avgprice'),'id','cat_id','badge_id')->groupBy('cat_id')->get()->toArray();
        \DB::connection( 'mongodb' )->enableQueryLog();
        $prd2 = \App\MongoProduct::groupBy(['cat_id'])->get(['cat_id','badge_id'])->toArray();
        dd(\dd(DB::connection('mongodb')->getQueryLog()));
        //$prd3 = \App\MongoProduct::avgPriceProduct();
        dd($prd,$prd2);*/
        $shop_details = Shop::where('user_id', Auth::id())->with('shopDesc')->first();
        
        if(empty($shop_details)){
            return redirect(action('HomeController@index'));
        }
        
        $map_images = !empty($shop_details->map_image)?explode(',', $shop_details->map_image):[];
        $shop_images = !empty($shop_details->shop_image)?explode(',', $shop_details->shop_image):[];
        $seller_prod_cat = \App\ShopAssignCategory::getShopCategory();

        return view(loadFrontTheme('seller.manage_shop'),['shop_details'=>$shop_details,'map_images'=>$map_images,'shop_images'=>$shop_images,'page'=>'seller','seller_prod_cat'=>$seller_prod_cat]);
    }

    public function updateStore(Request $request){
        $input = $request->all();
        $user_id = Auth::id();
        $shop_id = session('user_shop_id');
        $rules['store_url'] = uniqueIgnoreRule($this->tblShop,'shop_url',$user_id,'user_id');
        $rules['store_name'] = uniqueIgnoreRule($this->tblShopDesc,'shop_name',$shop_id,'shop_id');
        /*if(!empty($request->ph_number)){
            $rules['ph_number'] = phoneRule();
        }*/
        $error_msg['store_url.required'] = Lang::get('shop.store_url_is_required');
        $error_msg['store_name.required'] = Lang::get('shop.store_name_is_required');
        $error_msg['store_url.unique'] = Lang::get('shop.store_url_already_exist');
        $error_msg['store_name.unique'] = Lang::get('shop.store_name_already_exist');
        $error_msg['ph_number.digits'] = Lang::get('customer.phone_no_must_be_10_digits');
        unset($input['_token']);

        $validate = Validator::make($input, $rules, $error_msg);
        if ($validate->passes()) {
            try {
                $shop_info = Shop::find($shop_id);
                

                $shop_info->open_time = cleanValue($request->open_time);
                $shop_info->close_time = cleanValue($request->close_time);
                $shop_info->product_pickup_time = cleanValue($request->product_pickup_time);
                $shop_info->center_pickup_time = cleanValue($request->center_pickup_time);
                $shop_info->ph_number = cleanValue($request->ph_number);
                $shop_info->line_link = cleanValue($request->line_link);
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

                /***update shop name and description***/
                $update = ShopDesc::where('shop_id',$shop_id)->update(['shop_name'=>$request->store_name,'description'=>$request->description]);

                /***update shop data into mongo******/
                $shop_data = \App\Shop::where('id',$shop_id)->with('allDesc')->with('shopUser')->first();
                $check_cat = \App\ShopAssignCategory::where(['shop_id'=>$shop_id])->pluck('category_id')->toArray();
                $shop_data->shop_category = $check_cat;
                $update_data = \App\MongoShop::updateData($shop_data);

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

    function deleteShopImg(Request $request){
        $shop_id = session('user_shop_id');
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
                    $response = ['status'=>'fail','msg'=>''];
                }
                
            }
        }else{
            $response = ['status'=>'fail','msg'=>''];
        }
        return $response;
    }

    function updateShopStatus(Request $request){

        $user_id = Auth::id();
        $shop_id = session('user_shop_id');
        try {
            $shop_info = Shop::find($shop_id);
            if(isset($request->type) && $request->type == 'shop_status'){
                if($shop_info->shop_status == 'open'){
                    $value = 'close';
                    $return = '0';
                    $status = '0';
                }else{
                    $value = 'open';
                    $return = '1';
                    $status = '1';
                }
                $shop_info->status = $status;
                $shop_info->shop_status = $value;
                $column_name = 'shop_status';
            }elseif(isset($request->type) && $request->type == 'bargaining'){
                if($shop_info->bargaining == 'yes'){
                    $value = 'no';
                    $return = '0';
                }else{
                    $value = 'yes';
                    $return = '1';
                }
                
                $shop_info->bargaining = $value;
                $column_name = 'bargaining';
            }
            $shop_info->save();

            $update_data = \App\MongoShop::updateShopColumn($shop_id,$column_name,$value);
            if(isset($status)){
                $update_data = \App\MongoShop::updateShopColumn($shop_id,'status',$status);
            }

            $response = ['status'=>'success','msg'=>Lang::get('common.records_updated_successfully'),'value'=>$return];
            
        }catch(Exception $e){
            $response = ['status'=>'fail','msg'=>'','error'=>$e->getMessage()];
        }
        return $response;
    }

}
