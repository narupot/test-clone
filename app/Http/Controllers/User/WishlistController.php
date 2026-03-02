<?php 

namespace App\Http\Controllers\User;

use App\Http\Controllers\MarketPlace;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use Auth;
use App\Product;
use App\ProductDesc;
use App\Wishlist;

class WishlistController extends MarketPlace
{   
    public function __construct()
    {
        //$this->middleware('auth');
        $this->middleware('authenticate');
    }
    
    public function index(Request $request){

        $referer_url = $request->headers->get('referer');
        $breadcrumb = $this->getBreadcrumb($referer_url);
        return view(loadFrontTheme('user.wishlist'),['page'=>'user/*', 'page_class'=>'myaccount-wrap','breadcrumb'=>$breadcrumb,'show_per_page'=>json_encode(getShowRangePerPage()),'order_by_item'=>json_encode(getSortingItems())]);
    }

    public function getUserWishlist(Request $request){

        $perpage = !empty($request->per_page) ? $request->per_page : 9;
        $current_page = !empty($request->page) ? $request->page : 1;

        $user_id = Auth::id();
        $default_lang = session('default_lang');

        $my_wishlist_data = Wishlist::where('user_id',$user_id)->orderBy('id', 'DESC')->paginate($perpage);

        $wishlistDataArray = array();
        $prefix = DB::getTablePrefix();
        $current_date = date('Y-m-d');
        
        foreach($my_wishlist_data as $w_key =>$wishlist){
           
            $wishlistDataArray[$w_key]['wishlist_id'] = $wishlist->id;

            $productOnfo  = DB::table(with(new Product)->getTable().' as p')
                ->join(with(new ProductDesc)->getTable().' as ad',[['p.id', '=', 'ad.product_id'],['ad.lang_id', '=' , DB::raw($default_lang)]])
                // ->leftjoin(with(new \App\ProductWarehouse)->getTable().' as pw',[['p.id','=','pw.product_id']])
                ->select(DB::raw('Distinct('.$prefix.'p.id)'), 'p.url', 'p.thumbnail_image', 'p.sku','p.url','p.initial_price','p.sp_tp_bp_type','p.has_sp_tp_bp','p.special_price','p.currency_id','p.from_date','p.to_date','ad.name', 'ad.short_desc','p.expiry_timestamp','p.nexpiry','p.created_at','p.id as product_id','p.product_type as product_type','p.deleted_at',DB::raw('if('.$prefix.'p.sp_tp_bp_type=1 && ('.$prefix.'p.from_date<="'.$current_date.'" && '.$prefix.'p.to_date>="'.$current_date.'"),'.$prefix.'p.special_price,'.$prefix.'p.initial_price) as price'),'p.avg_rating'); 
            $productData = $productOnfo->where('p.id',$wishlist->product_id)->first();
            

            if(!empty($productData)){
                //format data as per need                
                $productDataArray = $this->formatProductData($productData);
                $wishlistDataArray[$w_key] = $productDataArray;
            } 
        }
        return ['data'=>$wishlistDataArray,'total'=>$my_wishlist_data->total(),'status'=>'success','itemsPerPage'=>$perpage,'currentPage'=>$current_page];
    }

    public function removeWishlist( Request $request){
        
        $product_id = $request->wishlist_id['id'];
        $user_id = Auth::id();
       
        $is_wishlist_removed = Wishlist::where(['user_id'=>$user_id,'product_id'=>$product_id])->delete();
        
        if($is_wishlist_removed){
            return ['status'=>'success','message'=>'Product is removed from wishlist'];
        }else{
            return ['status'=>'failed','message'=>'Oops! something went wrong, please try again.'];
        }
    }         
}