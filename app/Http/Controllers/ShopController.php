<?php
namespace App\Http\Controllers;
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
        $this->tblShop = with(new Shop)->getTable();
        $this->tblShopDesc = with(new ShopDesc)->getTable();
    }

    public function index(Request $request) {  
         
        $shop_details = Shop::where('shop_url', $request->shop)
        ->with(['shopDesc'])
        ->withCount(['favoriteShop','product'=>function($q){
           // อ๊อฟแก้
            //$q->where('status',1)->where('stock',1);
            $q->where('status',1);
        }])
        ->first();
        if(!$shop_details) abort('404');
        
        $isFavorite = $this->isShopFavorit($request->shop);
        if(Auth::check()){
            $userCreditData = \App\Credits::where(['user_id'=>Auth::user()->id,'shop_id'=>$shop_details->id])->whereIn('seller_approval',['Pending','Approved'])->first();

            if(session('user_shop_id')==$shop_details->id){
                $credit_request = 'hide';
            }
            // end
            elseif($userCreditData==null){
                $credit_request = 'show';
            }else{
                if($userCreditData->seller_approval=='Pending'){
                    $credit_request = 'requested';
                }elseif($userCreditData->seller_approval=='Approved'){
                    $credit_request = 'hide';
                }
            }
        }
        else{
            $credit_request = 'show';
        }

        // $map_images = !empty($shop_details->map_image)?explode(',', $shop_details->map_image):[];
        // $shop_images = !empty($shop_details->shop_image)?explode(',', $shop_details->shop_image):[];

        $category = null;
        if(isset($request->cat_url)){
            $category = \App\Category::where('url',$request->cat_url)->select('id','url')->first();
        }



        $perPage = !empty($request->per_page) ? (int)$request->per_page : 12;
        $search = $request->search;
        $tab = isset($request->tab)?$request->tab:'home';
        // if($tab != 'home' && ( $tab != 'all' && $tab != 'bestsell' && $tab != 'recommend' )){
        //     $tab = 'home';
        // }
 
        // $order = [];
        // if($tab == 'bestsell'){
        //     $order = \App\OrderDetail::query()
        //     ->where('shop_id', $shop_details->id)
        //     ->select('product_id',DB::raw('COUNT(product_id) as total'))
        //     ->groupBy('product_id')
        //     ->orderByDesc('total')
        //     ->get()
        //     ->pluck('product_id')
        //     ->toArray();
            
        //     // if(count($order) > 0){
        //     //     $order = $order->implode(',');
        //     // }
        // }
        
        $product_list = \App\MongoProduct::query()
            ->where('shop_id', $shop_details->id)
            ->whereHas('shop', function ($q)  {
                $q->where('shop_status', 'open')->where('status', '1');
            })
            
            // อ๊อฟแก้
            //->where('stock', '1')
            ->where('status', '1')
            ->when(Auth::check(), function ($query) {
                $query->with('wishlist');
            })
            
            ->when($search, function ($qq) use ($search) {
                return $qq->whereHas('category', function ($qqq) use ($search) {
                    $qqq->where('category_name', 'like', '%' . $search . '%')
                    ->where('status', '1');
                });
            })
            

            
            // ->when($tab == 'home',function ($q) use ($search) {
            //         return $q
            //     }
            // )
            // ->when($tab == 'bestsell', function ($q) use ($order) {
            //     $q->when($order, function ($qq) use ($order) {
            //         return $qq->orderBy("created_at", "DESC");
            //     });
            // })
            // ->when($tab == 'recommend', function ($q) {
            //     return $q->orderby('avg_rating', 'desc');
            // })
        // ->orderByDesc('created_at')  
        ->paginate($perPage)->appends(request()->query());
        $product_list->each(function ($product) {
            $product->product_image = $product->thumbnail_image?getProductImageUrl($product->thumbnail_image,'original'):null;
            $product->shop_name = $product->shop->shop_name?? null;
            $product->unit_name = $product->base_unit_id?getUnitName($product->base_unit_id):null;
            $product->badge_img = $product->badge_id?getBadgeImage($product->badge_id):null;
            $product->cate_name = $product->category->category_name?? null;
            $product->package_name = $product->package_id?getPackageName($product->package_id): null;
            
        });


        return view(loadFrontTheme('shop.shop'),[
            'shop_details'=>$shop_details,
            // 'map_images'=>$map_images,
            // 'shop_images'=>$shop_images,
            'isFavorite'=>$isFavorite,
            'credit_request'=>$credit_request,
            'show_per_page'=>json_encode(getShowRangePerPage()),
            'order_by_item'=>json_encode(getSortingItems()),
            'category'=>$category,
            'product_list'=>$product_list,
        ]); 
        
    }

    public function shopFilter(Request $request){
        
        $shop_id = $request->shop_id;
        $all_badges = \App\MongoBadge::getAllBadge();
        $badge_arr = [];
        foreach ($all_badges as $key => $value) {
            $badge_arr[] = ['id'=>$value->_id,'badge_name'=>$value->badge_name,'icon'=>getBadgeImageUrl($value->icon)];
        }
        $seller_prod_cat = \App\ShopAssignCategory::getShopCategory($shop_id);
        if($seller_prod_cat){
            foreach ($seller_prod_cat as $key => $value) {
                $seller_prod_cat[$key]->img = getCatImgUrl($value->img,'50x50');
            }
        }

        return ['badges'=>$badge_arr,'category'=>$seller_prod_cat];
    }

    public function manageFavoriteShop(Request $request){
        $userDetail = Auth::user();
        if(Auth::user()!=null){
            $user_id = Auth::user()->id;
            $shopData = \App\Shop::select('id','user_id')->where('shop_url',$request->shop_url)->first();
            
            if($user_id == $shopData->user_id){
                return['status'=>'warning','msg'=>Lang::get('shop.you_can_not_add_your_own_shop')];
            }
            $shop_id = isset($shopData->id)?$shopData->id:0;
            $shop_fav_status = \App\FavoriteShop::where(['shop_id'=>$shop_id,'user_id'=>$user_id])->first();
            $shop_fav_obj = new \App\FavoriteShop;
            if($shop_fav_status===null){
                $shop_fav_obj->shop_id = $shop_id;
                $shop_fav_obj->user_id = $user_id;
                $shop_fav_obj->save();
                $message = Lang::get('shop.add_favorite_success');
                $favorite = true;
            }else{
                $shop_fav_obj->destroy($shop_fav_status->id);
                $message = Lang::get('shop.remove_favorite_success');
                $favorite = false;
            }

            $response = ['status'=>'success','msg'=>$message,'redirect_url'=>'','favorite'=>$favorite];
        }else{
            $message = Lang::get('shop.not_loggedin_message');
            $response = ['status'=>'warning','msg'=>$message,'redirect_url'=>action('Auth\RegisterController@login')];
        }
        
        return $response;
    }

    protected function isShopFavorit($shop_url){
        $shopData = \App\Shop::select('id')->where('shop_url',$shop_url)->first();
        $shop_id = isset($shopData->id)?$shopData->id:0;
        $user_id = isset(Auth::user()->id)?Auth::user()->id:0;
        $shop_fav_status = \App\FavoriteShop::where(['shop_id'=>$shop_id,'user_id'=>$user_id])->count();
        $favorite = ($shop_fav_status)?true:false;
        return $favorite;
    }

    public function shopList(Request $request,$cat_url=null){

        $search = $request->search;

        $cat_detail = [];
        if($cat_url){
            $cat_detail = \App\MongoCategory::where('url',$cat_url)->first();
            if(empty($cat_detail)){
                abort('404');
            }

        }else{
            $cat_url = 'list';
        }
        
        $fielddata = json_encode(['fieldSets' =>[], 'tableConfig'=>[]]);
        return view(loadFrontTheme('shop.shop_list'),['fielddata'=>$fielddata,'cat_url'=>$cat_url,'cat_detail'=>$cat_detail,'search'=>$search]);
    }

    public function shopListData(Request $request,$cat_url=null){
        set_time_limit(120);
        $search_text = $request->search_text;

        $perpage = !empty($request->per_page) ? (int)$request->per_page : 10;
        $cat_id = 0;

        if($cat_url !='list'){
            $cat_detail = \App\MongoCategory::where('url',$cat_url)->first();
            if($cat_detail){
                $cat_id = $cat_detail->id;
            }
        }
        
        if(isset($search_text) && !empty($search_text)){
            $shop_list = \App\MongoShop::where('shop_name','like','%'.$search_text.'%')->where('status','1')->where('shop_status','open');    
        }else{
            $shop_list = \App\MongoShop::where('status', '1')->where('shop_status','open');    
        }
        
        if($cat_id){
            $shop_list->where('shop_category',$cat_id);
        }
        $shop_res = $shop_list->paginate($perpage);
       

        if(count($shop_res)){
            foreach ($shop_res as $key => $value) {
                $category_name = '';
                if(isset($value->shop_category) && count($value->shop_category)){
                    $category_data = \App\MongoCategory::whereIn('_id',$value->shop_category)->pluck('category_name')->toArray();
                    if($category_data){
                        $category_name = implode(',', $category_data);
                    }
                }
                $shop_res[$key]->shop_slug = $value->shop_url;
                $shop_res[$key]->shop_category = $category_name;
                $shop_res[$key]->logo = getImgUrl($value->logo,'logo');
                $shop_res[$key]->shop_url = action('ShopController@index',$value->shop_url);
                $shop_res[$key]->avg_rating = isset($value->avg_rating)?(int)$value->avg_rating:0;


                if(Auth::check()){
                    $shop_fav_status = \App\FavoriteShop::where(['shop_id'=>$value->_id,'user_id'=>Auth::id()])->count();

                    $favorite = ($shop_fav_status)?true:false;
                }else{
                    $favorite  = false;
                }

                $shop_res[$key]->favorite = $favorite;
                //$category_data = 
            }
        }
        return $shop_res;
    }

    public function sendCreditRequest(Request $request){
        try{
            if(Auth::check()){
                $user_id = Auth::user()->id;
                $is_already_apply = \App\Credits::where(['user_id'=>$user_id,'shop_id'=>$request->shop_id])->whereIn('seller_approval',['Approved','Pending'])->count();
                if($is_already_apply){
                    $shopData = \App\Shop::find($request->shop_id);
                    $status = 'error';
                    $url = action('ShopController@index',['shop'=>$shopData->shop_url]);
                    $message = Lang::get('shop.already_credit_request_send_message');
                    $response = ['status'=>$status,'message'=>$message,'redirect_url'=>$url];
                }else{
                    $creditObj = new \App\Credits;
                    $creditObj->shop_id = $request->shop_id;
                    $creditObj->user_id = $user_id;
                    if($creditObj->save()){
                        $status = 'success';
                        $url = '';
                        $message = Lang::get('shop.credit_request_success_message');
                        $response = ['status'=>$status,'message'=>$message,'redirect_url'=>$url];
                    }
                }
            }else{
                $status = 'warning';
                $url = action('Auth\LoginController@login');
                $message = Lang::get('shop.not_loggedin_message');
                $response = ['status'=>$status,'message'=>$message,'redirect_url'=>$url];
            }
        }
        catch(Exception $e){
            $url = '';
            $status = 'error';
            $response = ['status'=>$status,'message'=>$e->getMessage(),'redirect_url'=>$url];
        }
        return $response;
    }

    public function checkLogin(Request $request){
        if(Auth::check()){
            $url = '';
            $status = 'loggedin';
            $title = Lang::get('shop.confirm_popup_title');
            $message = Lang::get('shop.credit_request_to_seller_confirmation_msg');
        }else{
            $url = action('Auth\LoginController@login');
            $status = 'not-loggedin';
            $title = Lang::get('shop.confirm_popup_title');
            $message = Lang::get('shop.not_loggedin_message');
        }
        return ['status'=>$status,'message'=>$message,'redirect_url'=>$url,'title'=>$title];
    }

}
