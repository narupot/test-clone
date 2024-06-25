<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\MarketPlace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Session;
use Route;
use Cache;
use App\Helpers\GeneralFunctions;
use App\Helpers\LayoutHtmlHelpers;
use App\Helpers\CustomHelpers;
use Lang;
use Config;
Use DB;

use App\Product;
use App\ProductDesc;



class ProductsController extends MarketPlace {
    public $prefix;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        //$this->middleware('auth');
        $this->prefix = DB::getTablePrefix();
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */


    public function getselectedCategories($selectedCategories = null){
        if(!empty($selectedCategories)){
            $subcategories = \App\Category::whereIn('id', $selectedCategories)->with('getcategoryDetail')->get();
            //dd($subcategories);
            $categoriesData = [];
            foreach($subcategories as $key=>$subcategory){
                $categoriesData[$key]['id']  = $subcategory->id;
                $categoriesData[$key]['name']  = $subcategory->getcategoryDetail->category_name;
                $categoriesData[$key]['url']  = $subcategory->url;  
            }
        return $categoriesData;  
        }
        return null;
    }


    // public function category(Request $request, $url){
        
    //     $referer_url = url()->current();
    //     $url = stripTags($url);
    //     $parent_cat_detail = \App\MongoCategory::where('url',$url)->first();
    //     if(empty($parent_cat_detail)){
    //         abort(404);
    //     }
        


    //     if($parent_cat_detail->parent_id<1){
    //         // if category is parent category show the subcategory

    //         $child_cat_data = \App\MongoCategory::where('parent_id',$parent_cat_detail->id)->select('url','category_name','img')->where('status','1')->get();

    //         if(count($child_cat_data)){
    //             foreach ($child_cat_data as $key => $value) {
    //                 $tot_prd = \App\MongoProduct::where(['cat_id'=>$value->id,'status'=>'1','stock'=>'1'])->count();
    //                 $child_cat_data[$key]->tot_prd = $tot_prd>0?$tot_prd:0;
                    
    //             }
    //         }
    //     }else{
    //         // if category is child category 
    //         $child_cat_data = [];
    //     }

    //     $breadcrumb = $this->getBreadcrumb($referer_url);
    //     $selectedAttributes ='';
    //     if(isset($request->filter_by)){
    //         $selectedAttributes = json_encode(['badge'=>[$request->filter_by]]);
    //     }
    //     //$selectedAttributes = $selectedAttributesvalue ='';
    //     if($parent_cat_detail->parent_id<1){
    //       $page = 'category';
    //       return view('categoryList',['parent_cat_detail'=>$parent_cat_detail,'child_cat_data'=>$child_cat_data->toJson(),'selectedAttributes'=>$selectedAttributes,'page'=>$page,'result'=>$parent_cat_detail]); 
    //     }else{
    //         /*****getting badge data********/
    //         $range_flag = false;
    //         $data = \App\MongoProduct::where('cat_id',$parent_cat_detail->_id)->select('badge_id','unit_price')->OrderBy('updated_at','DESC')->where('status',"1")->where('stock',"1")->get();
            
    //         if(count($data)){
    //           $data = $data->toArray();
    //           $all_badges = array_unique(array_column($data, 'badge_id'));
    //           $all_prices = array_unique(array_column($data, 'unit_price'));
    //           if(count($all_prices)>=2){
    //             $range_flag = true;
    //           }
    //           $all_badges = \App\MongoBadge::whereIn('_id',$all_badges)->get();
    //         }else{
    //           $all_badges = null;
    //         }
    //       $page = 'category';  
    //       return view('categoryProductList',['parent_cat_detail'=>$parent_cat_detail,'show_per_page'=>json_encode(getShowRangePerPage()),'order_by_item'=>json_encode(getSortingItems()),'rating_star_item'=>json_encode(getRatingStarItems()),'breadcrumb'=>$breadcrumb,'selectedAttributes'=>$selectedAttributes,'badges'=>$all_badges,'price_flag'=>$range_flag,'page'=>$page,'result'=>$parent_cat_detail]);  
    //     }
      
    // }

    public function category(Request $request, $url){
        
        $referer_url = $request->server('REQUEST_SCHEME')."://".$request->server('HTTP_HOST').'/'.$request->server('REQUEST_URI');
        //dd($referer_url);
        $url = stripTags($url);
        $parent_cat_detail = \App\MongoCategory::where('url',$url)->first();
        //dd($parent_cat_detail);
        if(empty($parent_cat_detail)){
            abort(404);
        }
        

        $child_cat_data = [];
        $breadcrumb = $this->getBreadcrumb($referer_url);
        $selectedAttributes ='';
        
            /*****getting badge data********/
        $range_flag = false;
        //$data = \App\MongoProduct::where('cat_id',$parent_cat_detail->_id)->select('badge_id','unit_price', 'shop_id')->OrderBy('updated_at','DESC')->where('status',"1")->get();
        

        $data = \App\MongoProduct::where('cat_id',$parent_cat_detail->_id)->where('status',"1")->OrderBy('updated_at','DESC')->groupBy('shop_id')->get(['shop_id','badge_id','unit_price']);

        //dd($data);
            
        if(count($data)){
            $data = $data->toArray();
            $all_badges = array_unique(array_column($data, 'badge_id'));
            $all_prices = array_unique(array_column($data, 'unit_price'));
            if(count($all_prices)>=2){
                $range_flag = true;
            }
            $all_badges = \App\MongoBadge::whereIn('_id',$all_badges)->get();
        }else{
            $all_badges = null;
        }
        $page = 'category';  
        return view('searchCategoryProductList',['parent_cat_detail'=>$parent_cat_detail,'show_per_page'=>json_encode(getShowRangePerPage()),'order_by_item'=>json_encode(getSortingItems()),'rating_star_item'=>json_encode(getRatingStarItems()),'breadcrumb'=>$breadcrumb,'selectedAttributes'=>$selectedAttributes,'badges'=>$all_badges,'price_flag'=>$range_flag,'page'=>$page,'result'=>$parent_cat_detail]);  
    
      
    }

    public function categorySearch(Request $request, $url){
        
        $referer_url = $request->server('REQUEST_SCHEME')."://".$request->server('HTTP_HOST').'/'.$request->server('REQUEST_URI');
        //dd($referer_url);
        $url = stripTags($url);
        $parent_cat_detail = \App\MongoCategory::where('url',$url)->first();
        //dd($parent_cat_detail);
        if(empty($parent_cat_detail)){
            abort(404);
        }
        

        $child_cat_data = [];
        $breadcrumb = $this->getBreadcrumb($referer_url);
        $selectedAttributes ='';
        
            /*****getting badge data********/
        $range_flag = false;
        //$data = \App\MongoProduct::where('cat_id',$parent_cat_detail->_id)->select('badge_id','unit_price', 'shop_id')->OrderBy('updated_at','DESC')->where('status',"1")->get();
        

        $data = \App\MongoProduct::where('cat_id',$parent_cat_detail->_id)->where('status',"1")->OrderBy('updated_at','DESC')->groupBy('shop_id')->get(['shop_id','badge_id','unit_price']);

        //dd($data);
            
        if(count($data)){
            $data = $data->toArray();
            $all_badges = array_unique(array_column($data, 'badge_id'));
            $all_prices = array_unique(array_column($data, 'unit_price'));
            if(count($all_prices)>=2){
                $range_flag = true;
            }
            $all_badges = \App\MongoBadge::whereIn('_id',$all_badges)->get();
        }else{
            $all_badges = null;
        }
        $page = 'category';  
        return view('searchCategoryProductList',['parent_cat_detail'=>$parent_cat_detail,'show_per_page'=>json_encode(getShowRangePerPage()),'order_by_item'=>json_encode(getSortingItems()),'rating_star_item'=>json_encode(getRatingStarItems()),'breadcrumb'=>$breadcrumb,'selectedAttributes'=>$selectedAttributes,'badges'=>$all_badges,'price_flag'=>$range_flag,'page'=>$page,'result'=>$parent_cat_detail]);  
    }

    public function getSearchProducts(Request $request){

        $cat_id = $request->cat_id;
        $page_item = $request->itemsPerPage;

        $cat_data = \App\MongoCategory::where('_id',$cat_id)->first();
        if(!$cat_data){
            return ['status'=>'fail'];
        }
        //dd($cat_data);

        $filter_attributes = $request->fillterAttributes;
        $range_flag = false;
        $order_by = $request->orderBy;
        $order = $request->order;

        $required_product_ids = []; 
        if(Auth::check()){
            $user_id = Auth::id();

            $wishlist_product_ids=\App\MongoWishlist::where('user_id',$user_id)->pluck('product_id');
            $orderd_product_ids = \App\OrderDetail::where('user_id',$user_id)->pluck('product_id');
            
            if(count($wishlist_product_ids))
                $wishlist_product_ids = array_unique($wishlist_product_ids->toArray());
            else
                $wishlist_product_ids=[];
            if(count($orderd_product_ids))
                $orderd_product_ids = array_unique($orderd_product_ids->toArray());
            else
                $orderd_product_ids = [];

            if(count($wishlist_product_ids) && count($orderd_product_ids))
                $orderd_product_ids = array_diff($orderd_product_ids, $wishlist_product_ids);

            $required_product_ids = array_merge($wishlist_product_ids,$orderd_product_ids);
                
        }

        // first time if filter is not there
        /*$data = \App\MongoProduct::where('cat_id',$cat_id)->select('badge_id','unit_price', 'shop_id')->where('status',"1")->OrderBy('updated_at','DESC')->groupBy('shop_id')->get();*/
       $product_ids = $product_price= [];

       $data = \App\MongoProduct::select('_id', 'unit_price', 'shop_id')->where('cat_id',$cat_id)->where('status',"1")->OrderBy('shop_id','ASC')->get();

       //dd($data);
       
       foreach ($data as $key => $val){
          //$product_price[$val['shop_id']] = $val['unit_price'];
          if(!isset($product_price[$val['shop_id']]) || ( $val['unit_price'] > 0 && $product_price[$val['shop_id']] > $val['unit_price'])){
              $product_price[$val['shop_id']] = $val['unit_price']; 
              $product_ids[$val['shop_id']] = $val['_id'];
          }  

           
       }

       //dd($product_ids, $product_price, $data);

        /*
        if(count($data)){

            $data = $data->toArray();
            
            $all_badges = array_unique(array_column($data, 'badge_id'));
            $all_prices = array_unique(array_column($data, 'unit_price'));
            if(count($all_prices)>=2){
                $range_flag = true;
            }
            $all_badges = \App\MongoBadge::whereIn('_id',$all_badges)->get();
        }else{
            $all_badges = null;
        }*/

        $all_badges = null;
        
        $shop_closed_id = \App\MongoShop::where('shop_status','close')->orWhere('status','0')->pluck('_id')->toArray();
        
        $product_data = \App\MongoProduct::select('url', 'sku', 'shop_id', 'avg_star', 'cat_id','badge_id','show_price', 'unit_price', 'stock', 'quantity','order_qty_limit','min_order_qty','thumbnail_image','is_tier_price','package_id','base_unit_id','weight_per_unit','status','created_at','updated_at','created_by','updated_by','created_from', 'updated_from', 'description', 'image')
            ->where(['cat_id'=>$cat_id,'status'=>"1"])
            ->whereNotIn('shop_id',$shop_closed_id)
            ->whereIn('_id', $product_ids)
            ->where('stock',"1")
            ->when(Auth::check(),function($query){$query->with('wishlist');})
            ->orderBy($order_by,$order)->paginate($page_item)->toArray();
        //dd($product_data);
        foreach ($product_data['data'] as $key => $value){
            $shop = \App\MongoShop::where('_id', $value['shop_id'])->first();
            //dd($product_data[$key]);
            $product_data['data'][$key]['shop'] = $shop;
            $product_data['data'][$key]['unit_name'] = getUnitName($value['base_unit_id']);
              //$product_data
        }


        
        $shopping_url = action('User\ShoppinglistController@AddToShoppingList');
        
        //dd($product_data);

        $product_data = $this->formatListingData($product_data,['category_name'=>$cat_data->category_name,'sku'=>$cat_data->sku,'url'=>$cat_data->url,'shopping_url'=>$shopping_url]);

        return ['detail'=>$product_data, 'status'=>'success', 'cat_data'=> $cat_data];
    }


    public function getProductsbycategory(Request $request){
        //dd($request->all());
        $cat_id = $request->cat_id;
        $page_item = $request->itemsPerPage;
        $cat_data = \App\MongoCategory::where('_id',$cat_id)->first();
        $filter_attributes = $request->fillterAttributes;
        $range_flag = false;
        $order_by = $request->orderBy;
        $order = $request->order;
        $shop_closed_id = \App\MongoShop::where('shop_status','close')->orWhere('status','0')->pluck('_id')->toArray();
        //dd($filter_attributes)
        if(!is_null($filter_attributes)){
            $price_range = isset($filter_attributes['price']) ? $filter_attributes['price'] : null;
            $reviews = isset($filter_attributes['review']) ? $filter_attributes['review'] : null;

            $query = \App\MongoProduct::query();
            $query2 = \App\MongoProduct::query();
            // for badge
            
            if(empty($filter_attributes['badge'])){
                $badges = null;                
            }else{
                $badges_data = array_unique($filter_attributes['badge']);
                $badges = array_map('intval', $badges_data);
            }
            // for query1
            $query->when($badges, function ($q,$badges){
                return $q->whereIn('badge_id',$badges);
            });
            // for query 2 
            $query2->when($badges, function ($q,$badges){
                return $q->whereIn('badge_id',$badges);
            });

            $query->when(Auth::check(),function($q){
                $q->with('wishlist');
            });

            // for price
            if(!is_null($price_range)){
                $range_flag = true;
                if($price_range['min']== 0 && $price_range['max']== 0){
                    $query->where('unit_price',0);
                    $query2->where('unit_price',0);
                }else if(empty($price_range['max']) && $price_range['min']>0){
                    $query->where('unit_price','>=',(int)$price_range['min']);
                    $query2->where('unit_price','>=',(int)$price_range['min']);
                }else if(empty($price_range['min']) && $price_range['max']>0){
                    $query->where('unit_price','<=',(int)$price_range['max']);
                    $query2->where('unit_price','<=',(int)$price_range['max']);
                }else if(!empty($price_range['min']) && !empty($price_range['max']) &&$price_range['min']< $price_range['max']){
                    $query->where('unit_price','>=',(int)$price_range['min']);
                    $query->where('unit_price','<=',(int)$price_range['max']);

                    $query2->where('unit_price','>=',(int)$price_range['min']);
                    $query2->where('unit_price','<=',(int)$price_range['max']);
                }else if(!empty($price_range['min']) && !empty($price_range['max']) &&$price_range['min'] == $price_range['max']){
                    $query->where('unit_price','=',(int)$price_range['min']);
                    $query2->where('unit_price','=',(int)$price_range['min']);
                }else{
                    $query->where('unit_price','=','XXXX');
                    $query2->where('unit_price','=','XXXX');

                }
            }

            $query->with('shop')->with('badge')->where('cat_id',$cat_id)->whereNotIn('shop_id',$shop_closed_id)->where('stock',"1")->where('status',"1");

            $query2->with('shop')->with('badge')->where('cat_id',$cat_id)->whereNotIn('shop_id',$shop_closed_id)->where('status',"1");

            $query->when($reviews,function($q,$reviews){
                return $q->where('avg_star','>=',min($reviews));
            });

            $query2->when($reviews,function($q,$reviews){
                return $q->where('avg_star','>=',min($reviews));
            });
            $result = $query2->get(['unit_price','badge_id','avg_star']); 
            $query->orderBy($order_by,$order);
            $product_data = $query->paginate($page_item)->toArray();
            //dd($product_data);

            $all_badges =[];
            $prices = [];
            
            if(count($result)){
                $result = $result->toArray();
                $prices = array_unique(array_column($result, 'unit_price'));

                $all_badges = array_unique(array_column($result, 'badge_id'));
                $all_prices = array_unique(array_column($result,'unit_price'));
                //$all_reviews = array_unique(array_column($result,'avg_star'));
            }
            if(count($all_badges)){
                $all_badges = \App\MongoBadge::whereIn('_id',$all_badges)->get();    
            }

            if(count($prices)>=2){
                $range_flag = true;
                $price_range = ['min'=>min($prices),'max'=>max($prices)];
            }

        }else{
            $required_product_ids = []; 
            if(Auth::check()){
                $user_id = Auth::id();
                $wishlist_product_ids=\App\MongoWishlist::where('user_id',$user_id)->pluck('product_id');
                $orderd_product_ids = \App\OrderDetail::where('user_id',$user_id)->pluck('product_id');

                //dd($wishlist_product_ids,$orderd_product_ids);
                if(count($wishlist_product_ids))
                    $wishlist_product_ids = array_unique($wishlist_product_ids->toArray());
                else
                    $wishlist_product_ids=[];

                if(count($orderd_product_ids))
                    $orderd_product_ids = array_unique($orderd_product_ids->toArray());
                else
                    $orderd_product_ids = [];

                if(count($wishlist_product_ids) && count($orderd_product_ids))
                    $orderd_product_ids = array_diff($orderd_product_ids, $wishlist_product_ids);

                $required_product_ids = array_merge($wishlist_product_ids,$orderd_product_ids);
                
            }



            // first time if filter is not there
            $data = \App\MongoProduct::where('cat_id',$cat_id)->select('badge_id','unit_price')->OrderBy('updated_at','DESC')->where('status',"1")->get();
            
            if(count($data)){
              $data = $data->toArray();
              $all_badges = array_unique(array_column($data, 'badge_id'));
              $all_prices = array_unique(array_column($data, 'unit_price'));
              if(count($all_prices)>=2){
                $range_flag = true;
              }
              $all_badges = \App\MongoBadge::whereIn('_id',$all_badges)->get();
            }else{
              $all_badges = null;
            }
            

            // $product_data = \App\MongoProduct::where(['cat_id'=>$cat_id,'status'=>'1'])->whereNotIn('shop_id',$shop_closed_id)->with('shop')->with('badge')->when(Auth::check(),function($query){$query->with('wishlist');});

            // if(count($required_product_ids)){
            //     $product_data = $product_data->orderBy('_id',$required_product_ids);
            // }else{
            //     $product_data = $product_data->orderBy($order_by,$order)->paginate($page_item)->toArray();
            // }

            // $product_data = $product_data->orderBy('avg_star','desc')->orderBy('updated_at','desc')->paginate($page_item)->toArray();
            
            $product_data = \App\MongoProduct::where(['cat_id'=>$cat_id,'status'=>"1"])->whereNotIn('shop_id',$shop_closed_id)->with('shop')->with('badge')->when(Auth::check(),function($query){$query->with('wishlist');})->orderBy($order_by,$order)->paginate($page_item)->toArray();
        }
        $shopping_url = action('User\ShoppinglistController@AddToShoppingList');
        


        $product_data = $this->formatListingData($product_data,['category_name'=>$cat_data->category_name,'sku'=>$cat_data->sku,'url'=>$cat_data->url,'shopping_url'=>$shopping_url]);

        return ['detail'=>$product_data,'status'=>'success','cat_data'=>$cat_data];
        /*return ['detail'=>$product_data,'status'=>'success','cat_data'=>$cat_data,'badges'=>$all_badges,'price_flag'=>$range_flag];*/

    }

    public function newCategory(Request $request, $url=null){
        //dd($request->all());
        //$search = $request->search;
        if(empty($url)){
            return view('categoryNotFound');  
        }
        $search = stripTags($request->search);
        $range_flag = false;
        $page_item = stripTags($request->itemsPerPage);
        $order_by = stripTags($request->orderBy);
        $order = stripTags($request->order);

        $referer_url = $request->headers->get('referer');
        $breadcrumb = $this->getBreadcrumb(null);

        //$search = trim($request->search);

        //$this->validate($request, ['search' => 'required']);
        $cat_da = \App\MongoCategory::where('url',$url)->select('category_name','img','url','parent_id')->first();
        if(!$cat_da){
            return redirect()->action('HomeController@index');
        }
        if($cat_da->parent_id!=0){
            return redirect()->action('ProductsController@category',$url);
        
        }
        $cat_data = \App\MongoCategory::where('url',$url)->select('category_name','img','url')->get()->toArray();
        if(count($cat_data)){
            $search=$cat_da->category_name;
            $request->search=$search;
            $cat_ids = array_unique(array_column($cat_data, '_id'));
        }else{
            $cat_ids = [];  
        }

        $filtered_products =  \App\MongoProduct::whereIn('cat_id',$cat_ids)->select('badge_id','unit_price','cat_id')->get();

        $badges = [];
        $product_cat_ids = [];
        if(count($filtered_products)){
            $filtered_products = $filtered_products->toArray();
            $badges = array_unique(array_column($filtered_products, 'badge_id'));
            $prices = array_unique(array_column($filtered_products, 'unit_price'));
            $product_cat_ids = array_unique(array_column($filtered_products, 'cat_id'));
        }
        
        if(count($badges)){
            $all_badges = \App\MongoBadge::whereIn('_id',$badges)->get();
        }else{
            $all_badges = json_encode([]);
        }

        if(isset($prices) && count($prices)){
            $range_flag = true;
        }

        $product_cats = [];
        foreach ($cat_data as $key => $value){
            if(in_array($value['_id'], $product_cat_ids)){
                $product_cats[]= $value;
            }
        }
        //dd($search);
        return view('catProductShopList',['status'=>'success','show_per_page'=>json_encode(getShowRangePerPage()),'breadcrumb'=>$breadcrumb,'order_by_item'=>json_encode(getSortingItems()),'rating_star_item'=>json_encode(getRatingStarItems()),'search'=>$search,'cat_data'=>$product_cats,'badges'=>$all_badges,'price_flag'=>$range_flag]);        
    }


    public function search(Request $request){
        //dd($request->all());
        //$search = $request->search;
        $search = stripTags($request->search);
        $range_flag = false;
        $page_item = stripTags($request->itemsPerPage);
        $order_by = stripTags($request->orderBy);
        $order = stripTags($request->order);

        $referer_url = $request->headers->get('referer');
        $breadcrumb = $this->getBreadcrumb(null);

        //$search = trim($request->search);

        $this->validate($request, ['search' => 'required']);

        $cat_data = \App\MongoCategory::where('category_name','like','%'.$search.'%')->select('category_name','img','url')->get()->toArray();

        if(count($cat_data)){
            $cat_ids = array_unique(array_column($cat_data, '_id'));
        }else{
            $cat_ids = [];  
        }

        $filtered_products =  \App\MongoProduct::whereIn('cat_id',$cat_ids)->select('badge_id','unit_price','cat_id')->get();

        $badges = [];
        $product_cat_ids = [];
        if(count($filtered_products)){
            $filtered_products = $filtered_products->toArray();
            $badges = array_unique(array_column($filtered_products, 'badge_id'));
            $prices = array_unique(array_column($filtered_products, 'unit_price'));
            $product_cat_ids = array_unique(array_column($filtered_products, 'cat_id'));
        }
        
        if(count($badges)){
            $all_badges = \App\MongoBadge::whereIn('_id',$badges)->get();
        }else{
            $all_badges = json_encode([]);
        }

        if(isset($prices) && count($prices)){
            $range_flag = true;
        }

        $product_cats = [];
        foreach ($cat_data as $key => $value){
            if(in_array($value['_id'], $product_cat_ids)){
                $product_cats[]= $value;
            }
        }
        
        return view('searchProductShopList',['status'=>'success','show_per_page'=>json_encode(getShowRangePerPage()),'breadcrumb'=>$breadcrumb,'order_by_item'=>json_encode(getSortingItems()),'rating_star_item'=>json_encode(getRatingStarItems()),'search'=>$search,'cat_data'=>$product_cats,'badges'=>$all_badges,'price_flag'=>$range_flag]);        
    }
  
    //getProductsBysearch

    public function getProductsBysearch(Request $request){

        $filter_attributes = $request->fillterAttributes;
        $name = stripTags($request->search);
        $range_flag = false;
        $page_item = stripTags($request->itemsPerPage);
        $order_by = stripTags($request->orderBy);
        $order = stripTags($request->order);
        $cat_ids= null; 
        
        if(!is_null($filter_attributes) && count($filter_attributes['cat_ids'] ?? [])>0){
            $cat_ids = $filter_attributes['cat_ids'];
        }
        if(!$cat_ids && $filter_attributes && array_key_exists('cat_ids',$filter_attributes)){
            unset($filter_attributes['cat_ids']);
        }
        
        if(count($cat_ids)){
            $cat_data = \App\MongoCategory::where('category_name','like','%'.$name.'%')->select('category_name','img','url')->whereIn('_id',$cat_ids)->get()->toArray();
        }else{

            $cat_data = \App\MongoCategory::where('category_name','like','%'.$name.'%')->select('category_name','img','url')->get()->toArray();

        }

        // //dd($filter_attributes['cat_ids']);
        if(count($cat_data)){
            $cat_ids = array_unique(array_column($cat_data, '_id'));
        }else{
            $cat_ids = [];  
        }


        //dd('hello');

        if(count($filter_attributes)){

            $price_range = isset($filter_attributes['price']) ? $filter_attributes['price'] : null;
            $reviews = isset($filter_attributes['review']) ? $filter_attributes['review'] : null;

            $query = \App\MongoProduct::query();
            $query2 = \App\MongoProduct::query();
            // for badge
            if(empty($filter_attributes['badge'])){
                $badges = null;                
            }else{
                $badges = array_unique($filter_attributes['badge']);
            }
            // for query1
            $query->when($badges, function ($q,$badges){
                return $q->whereIn('badge_id',$badges);
            });
            // for query 2 
            $query2->when($badges, function ($q,$badges){
                return $q->whereIn('badge_id',$badges);
            });

            // for price
            if(!is_null($price_range)){
                $range_flag = true;
                if($price_range['min']== 0 && $price_range['max']== 0){
                    $query->where('unit_price',0);
                    $query2->where('unit_price',0);
                }else if(empty($price_range['max']) && $price_range['min']>0){
                    $query->where('unit_price','>=',(int)$price_range['min']);
                    $query2->where('unit_price','>=',(int)$price_range['min']);
                }else if(empty($price_range['min']) && $price_range['max']>0){
                    $query->where('unit_price','<=',(int)$price_range['max']);
                    $query2->where('unit_price','<=',(int)$price_range['max']);
                }else if(!empty($price_range['min']) && !empty($price_range['max']) &&$price_range['min']< $price_range['max']){
                    $query->where('unit_price','>=',(int)$price_range['min']);
                    $query->where('unit_price','<=',(int)$price_range['max']);

                    $query2->where('unit_price','>=',(int)$price_range['min']);
                    $query2->where('unit_price','<=',(int)$price_range['max']);
                }else if(!empty($price_range['min']) && !empty($price_range['max']) &&$price_range['min'] == $price_range['max']){
                    $query->where('unit_price','=',(int)$price_range['min']);
                    $query2->where('unit_price','=',(int)$price_range['min']);
                }else{
                    $query->where('unit_price','=','XXXX');
                    $query2->where('unit_price','=','XXXX');

                }
            }

            // $query->when($cat_ids,function($q,$cat_ids){
            //     return $q->whereIn('cat_id',$cat_ids);
            // });

            // $query2->when($cat_ids,function($q,$cat_ids){
            //     return $q->whereIn('cat_id',$cat_ids); 
            // });
            if(count($cat_ids)){
                $query->whereIn('cat_id',$cat_ids);
                $query2->whereIn('cat_id',$cat_ids);    
            }
            

            $query->with('shop')->with('badge')->with('category')->where('status',"1");

            $query2->with('shop')->with('badge')->with('category')->where('status',"1");

            $query->when($reviews,function($q,$reviews){
                return $q->where('avg_star','>=',min($reviews));
            });

            $query2->when($reviews,function($q,$reviews){
                return $q->where('avg_star','>=',min($reviews));
            });

            $query->when(Auth::check(),function($q){
                $q->with('wishlist');
            });


            $result = $query2->get(['unit_price','badge_id','avg_star','cat_id']); 
            $query->orderBy($order_by,$order);
            $product_data = $query->paginate($page_item)->toArray();
            $all_badges =[];
            $prices = [];
            $product_cat_ids=[];
            if(count($result)){
                $result = $result->toArray();
                $prices = array_unique(array_column($result, 'unit_price'));
                $all_badges = array_unique(array_column($result, 'badge_id'));
                $all_prices = array_unique(array_column($result,'unit_price'));
                $product_cat_ids = array_unique(array_column($result, 'cat_id'));
                //$all_reviews = array_unique(array_column($result,'avg_star'));
            }
            if(count($all_badges)){
                $all_badges = \App\MongoBadge::whereIn('_id',$all_badges)->get();    
            }

            if(count($prices)>=2){
                $range_flag = true;
                $price_range = ['min'=>min($prices),'max'=>max($prices)];
            }

        }else{


            $filtered_products =  \App\MongoProduct::whereIn('cat_id',$cat_ids)->select('badge_id','unit_price','cat_id')->get();

            $badges = [];
            $product_cat_ids = [];
            if(count($filtered_products)){
                $filtered_products = $filtered_products->toArray();
                $badges = array_unique(array_column($filtered_products, 'badge_id'));
                $prices = array_unique(array_column($filtered_products, 'unit_price'));
                $product_cat_ids = array_unique(array_column($filtered_products, 'cat_id'));
            }
            
            if(count($badges)){
                $all_badges = \App\MongoBadge::whereIn('_id',$badges)->get();
            }else{
                $all_badges = null;
            }

            if(isset($prices) && count($prices)){
                $range_flag = true;
            }

            $product_data = \App\MongoProduct::whereIn('cat_id',$cat_ids)->select('images','thumbnail_image','sku','show_price','unit_price','stock','quantity','description','avg_star','shop_id','badge_id','cat_id')->with('shop')->with('badge')->with('category')->when(Auth::check(),function($query){$query->with('wishlist');})->orderBy($order_by,$order)->paginate($page_item)->toArray();     

        }

        $product_data = $this->formatListingData($product_data);

        // for getting category which comes in product
        $product_cats = [];
        foreach ($cat_data as $key => $value){
            if(in_array($value['_id'], $product_cat_ids)){
                $product_cats[]= $value;
            }
        }
        return ['detail'=>$product_data,'status'=>'success'];
        /*return ['detail'=>$product_data,'status'=>'success','cat_data'=>$product_cats,'badges'=>$all_badges,'price_flag'=>$range_flag];*/
    }
    
    
    public function getProductsShopBysearch(Request $request){

        //$filter_attributes = $request->fillterAttributes;
        $name = stripTags($request->search);
        $range_flag = false;
        /*$page_item = $request->itemsPerPage;
        $order_by = $request->orderBy;
        $order = $request->order;*/
        $cat_ids= null; 

        $shop_closed_id = \App\MongoShop::getShopClosedId();

        $product_data = \App\MongoCategory::where('category_name','like','%'.$name.'%')->where('status',"1")->where('parent_id','>',0)->select('category_name','img','url','_id')->get()->toArray(); 
        $res_cat_id=[];
        foreach ($product_data as $key => $value) {
            $product_data[$value['_id']]=['category_name'=>$value['category_name'],'img'=>$value['img'],'url'=>$value['url']];
            $res_cat_id[] = $value['_id'];
        }
       
        //getting product data which has low price.
        $data_prd = \App\Product::select(DB::raw('min(unit_price) as minunit'),'cat_id')->where('status','1')->where('stock','1')->whereIn('cat_id',$res_cat_id)->whereNotIn('shop_id',$shop_closed_id)->groupby('cat_id')->orderBy('minunit','asc')->get()->toArray();
        $main_res_data = [];
        $i=0;
        foreach ($data_prd as $rkey => $res) {
            if(isset($product_data[$res['cat_id']])){
                $result = $product_data[$res['cat_id']];

                $main_res_data[$i]['url'] = action('ProductsController@categorySearch', $result['url']);
                $main_res_data[$i]['name'] = $result['category_name'];
                $main_res_data[$i]['value'] = $result['category_name'];
                $main_res_data[$i]['sku'] = '';
                $main_res_data[$i]['price'] = '';
                $main_res_data[$i]['cat_id'] = $res['cat_id'];
                $main_res_data[$i]['image'] = getCategoryImageUrl($result['img']);

                $main_res_data[$i]['shop_name'] = '';//$result['shop']['shop_name'];
                $main_res_data[$i]['type'] = 'product';
                $main_res_data[$i]['i'] = $i;            
                $i++; 

            }
            
        }

        return ['detail'=>$main_res_data,'status'=>'success'];

        /*return ['detail'=>$product_data,'status'=>'success','cat_data'=>$product_cats,'badges'=>$all_badges,'price_flag'=>$range_flag];*/
    }

    public function getProductsShopByCategory(Request $request){

        $name = stripTags($request->search);
        $range_flag = false;
        $cat_ids= null; 

        /*$cat_data = \App\MongoCategory::where('category_name','like','%'.$name.'%')->where('status',"1")->where('parent_id','>',0)->select('category_name','img','url')->get()->toArray();
        if(count($cat_data)){
            $cat_ids = array_unique(array_column($cat_data, '_id'));
        }else{
            $cat_ids = [];  
        }
        $product_data_list =  \App\MongoProduct::whereIn('cat_id',$cat_ids)->where('status',"1")->select('badge_id','unit_price','cat_id')->get();
        $product_cat_ids = [];
        if(count($product_data_list)){
            $product_data_list = $product_data_list->toArray(); 
            $product_cat_ids = array_unique(array_column($product_data_list, 'cat_id'));
        }*/
        $cat_array = [];
        $product_data = [];
        $cat_check= \App\MongoCategory::where('category_name',$name)->where('status',"1")->first();
        if($cat_check){
            $categor_id =$cat_check->_id;
            $cat_array= \App\MongoCategory::where('parent_id',$categor_id)->pluck('_id')->toArray();
        }
        if($cat_array){
            $shop_closed_id = \App\MongoShop::where('shop_status','close')->orWhere('status','0')->pluck('_id')->toArray();
            $cat_Ids = \App\MongoProduct::where('status','1')->where('stock','1');
            $cat_Ids = $cat_Ids->whereNotIn('shop_id',$shop_closed_id)->whereIn('cat_id',$cat_array)->pluck('cat_id','cat_id')->toArray();
            $product_data = \App\MongoCategory::whereIn('_id', $cat_Ids)->where('status',"1")->select('category_name','img','url')->get()->toArray();
        }
        
        //$product_data = \App\MongoCategory::whereIn('_id',$product_cat_ids)->select('category_name','img','url')->get()->toArray();
        
        $i=0;
        foreach ($product_data as $key => $result) {            
            $product_data[$i]['url'] = action('ProductsController@categorySearch', $result['url']);
            $product_data[$i]['name'] = $result['category_name'];
            $product_data[$i]['value'] = $result['category_name'];
            $product_data[$i]['sku'] = '';
            $product_data[$i]['price'] = '';
            $product_data[$i]['image'] = getCategoryImageUrl($result['img']);

            $product_data[$i]['shop_name'] = '';//$result['shop']['shop_name'];
            $product_data[$i]['type'] = 'product';
            $product_data[$i]['i'] = $i;            
            $i++;            
        }


        return ['detail'=>$product_data,'status'=>'success'];

        /*return ['detail'=>$product_data,'status'=>'success','cat_data'=>$product_cats,'badges'=>$all_badges,'price_flag'=>$range_flag];*/
    }

    public function getShopByCategory(Request $request){

        //$filter_attributes = $request->fillterAttributes;
        $name = stripTags($request->search);

        $shop_closed_id = \App\MongoShop::where('shop_status','close')->pluck('_id')->toArray();
        $cat_Ids = [];
        $shop_ids = [];
        $cat_check= \App\MongoCategory::where('category_name',$name)->where('status',"1")->first();
        if($cat_check){
            $categor_id =$cat_check->_id;
            $cat_array= \App\MongoCategory::where('parent_id',$categor_id)->pluck('_id')->toArray();
            $shop_ids = \App\MongoProduct::where('status','1')->where('stock','1');
            $shop_ids = $shop_ids->whereNotIn('shop_id',$shop_closed_id)->whereIn('cat_id',$cat_array)->pluck('shop_id')->toArray();
        
        }
        
        //where('shop_name','like','%'.$name.'%')
        $product_data = \App\MongoShop::whereIn('_id', $shop_ids)->where('shop_status','open')->where('status','1')->get()->toArray();

            //$data = []; 
            //dd($product_data);
        $i = 0;
        foreach($product_data as $key=>$result){
            //dd($result->sku);
            $product_data[$i]['url'] = action('ShopController@index',['shop'=>$result['shop_url']]);
            $product_data[$i]['name'] = $result['shop_name'];
            $product_data[$i]['value'] = $result['shop_name'];

            $product_data[$i]['sku'] = '';
            $product_data[$i]['price'] = '';
            $product_data[$i]['image'] =  getImgUrl($result['logo'],'logo');
            $product_data[$i]['type'] = 'shop';
            $product_data[$i]['i'] = $i;
            $i++; 
        }
        return ['detail'=>$product_data,'status'=>'success'];
    }
    
    public function getShopBysearch(Request $request){

        //$filter_attributes = $request->fillterAttributes;
        $name = stripTags($request->search);

        $shop_closed_id = \App\MongoShop::where('shop_status','close')->pluck('_id')->toArray();
        $cat_Ids = [];
        $cat_Ids = \App\MongoProduct::where('status','1')->where('stock','1');
        $cat_Ids = $cat_Ids->whereNotIn('shop_id',$shop_closed_id)->pluck('cat_id')->toArray();
        $cat_data = \App\MongoCategory::whereIn('_id', $cat_Ids)->where('category_name','like','%'.$name.'%')->where('status',"1")->where('parent_id','>',0)->pluck('_id')->toArray(); 
        
        $shop_ids = [];
        $shop_ids = \App\MongoProduct::where('status','1')->where('stock','1');
        $shop_ids = $shop_ids->whereNotIn('shop_id',$shop_closed_id)->whereIn('cat_id',$cat_data)->pluck('shop_id')->toArray();
        //where('shop_name','like','%'.$name.'%')
        $product_data = \App\MongoShop::whereIn('_id', $shop_ids)->where('shop_status','open')->where('status','1')->get()->toArray();

            //$data = []; 
            //dd($product_data);
        $i = 0;
        foreach($product_data as $key=>$result){
            //dd($result->sku);
            $product_data[$i]['url'] = action('ShopController@index',['shop'=>$result['shop_url']]);
            $product_data[$i]['name'] = $result['shop_name'];
            $product_data[$i]['value'] = $result['shop_name'];

            $product_data[$i]['sku'] = '';
            $product_data[$i]['price'] = '';
            $product_data[$i]['image'] =  getImgUrl($result['logo'],'logo');
            $product_data[$i]['type'] = 'shop';
            $product_data[$i]['i'] = $i;
            $i++; 
        }
        return ['detail'=>$product_data,'status'=>'success'];
    }

    public function autosearch(Request $request){
        $term = stripTags($request->term);
        $autoData = [];
        $data = [];
        if($request->searchtype=='all'){
           
            $shop_closed_id = \App\MongoShop::where('shop_status','close')->orWhere('status','0')->pluck('_id')->toArray();
            $cat_Ids = \App\MongoProduct::where('status','1')->where('stock','1');
            $cat_Ids = $cat_Ids->whereNotIn('shop_id',$shop_closed_id)->pluck('cat_id','cat_id')->toArray();
            


            /*$cat_data = \App\MongoCategory::where('category_name','like','%'.$term.'%')->where('status',"1")->where('parent_id','>',0)->select('category_name','img','url')->get()->toArray(); 
            
            if(count($cat_data)){
                $cat_ids = array_unique(array_column($cat_data, '_id'));
            }else{
                $cat_ids = [];  
            }*/
            /*$product_data_list =  \App\MongoProduct::whereIn('cat_id',$cat_ids)->where('status',"1")->select('badge_id','unit_price','cat_id')->get();
            $product_cat_ids = [];
            if(count($product_data_list)){
                $product_data_list = $product_data_list->toArray(); 
                $product_cat_ids = array_unique(array_column($product_data_list, 'cat_id'));
            }*/


            
            //$product_data = \App\MongoCategory::whereIn('_id',$product_cat_ids)->select('category_name','img','url')->get()->toArray();

            $product_data = \App\MongoCategory::whereIn('_id', $cat_Ids)->where('category_name','like','%'.$term.'%')->where('status',"1")->where('parent_id','>',0)->select('_id','category_name','img','url')->get()->toArray(); 
           //dd($cat_data);

            //$cat_ids = array_unique(array_column($cat_data, '_id'));
            /*$product_data = \App\MongoProduct::whereIn('cat_id',$cat_ids)->select('images','thumbnail_image','sku','show_price','unit_price','stock','quantity','description','avg_star','shop_id','badge_id','cat_id','package_id')->with('shop')-> with('badge')->with('category')->where('status',"1")->paginate(100)->toArray();*/
            
            $default_currency = \App\Currency::where('is_default','1')->first();
            //dd($product_data);
            
            $i=0;
            //Config::get('constants.category_img_url')
            //ProductsController@category
            $cat_data = [];
            foreach($product_data as $key=>$result){
                //dd($result->sku);
                //$package = getPackageName($result['package_id']);

                /*$data[$i]['url'] = action('ProductDetailController@display',
                    ['cat_url'=>$result['category']['url'],'sku'=>$result['sku']]);*/
                $data[$i]['url'] = action('ProductsController@categorySearch', $result['url']);
                
                $data[$i]['name'] = $result['category_name'];
                $data[$i]['value'] = $result['category_name'];
                
                //$data[$key]['sku'] = $result['sku'];
                //$data[$i]['price'] = $result['unit_price'].' '.$default_currency->name.'/'.$package;

                $data[$i]['sku'] = '';
                $data[$i]['price'] = '';

                $data[$i]['image'] = getCategoryImageUrl($result['img']);

                //$data[$i]['image'] = Config::get('constants.category_img_url').$result['img'];  

                //getProductImageUrlRunTime($result['thumbnail_image'], 'thumb_50x50');
                
                $data[$i]['shop_name'] = '';//$result['shop']['shop_name'];
                $data[$i]['type'] = 'product';
                $data[$i]['i'] = $i;
                $cat_data[$i] = $result['_id'];
                $i++;

            }

            /*if(!empty($data) && count($data)){
                $autoData['product'] = $data;
            }*/
            //return $data;
            $shop_ids = [];
            $shop_ids = \App\MongoProduct::where('status','1')->where('stock','1');
            $shop_ids = $shop_ids->whereNotIn('shop_id',$shop_closed_id)->whereIn('cat_id',$cat_data)->pluck('shop_id')->toArray();
            //where('shop_name','like','%'.$term.'%')
            $all_shop = \App\MongoShop::whereIn('_id', $shop_ids)->where('shop_status','open')->where('status','1')->paginate(10)->toArray();
            //$data = []; 
            //dd($product_data);
            $j = 0;
            foreach($all_shop['data'] as $key=>$result){
                //dd($result->sku);
                $data[$i]['url'] = action('ShopController@index',['shop'=>$result['shop_url']]);
                $data[$i]['name'] = $result['shop_name'];
                $data[$i]['value'] = $result['shop_name'];

                $data[$i]['sku'] = '';
                $data[$i]['price'] = '';
                $data[$i]['image'] =  getImgUrl($result['logo'],'logo');
                $data[$i]['type'] = 'shop';
                $data[$i]['i'] = $i;
                $data[$i]['j'] = $j;
                $i++; $j++;
            }
            //return $data;
            /*if(!empty($data) && count($data)){
                $autoData['shop'] = $data;
            }*/

            return $data;


        }    

        /*if($request->searchtype=='product'){
            $cat_data = \App\MongoCategory::where('category_name','like','%'.$term.'%')->where('status',"1")->select('category_name','img','url')->get()->toArray(); 
            $cat_ids = array_unique(array_column($cat_data, '_id'));
            $product_data = \App\MongoProduct::whereIn('cat_id',$cat_ids)->select('images','thumbnail_image','sku','show_price','unit_price','stock','quantity','description','avg_star','shop_id','badge_id','cat_id','package_id')->with('shop')-> with('badge')->with('category')->where('status',"1")->paginate(100)->toArray();
            

            $default_currency = \App\Currency::where('is_default','1')->first();
            
            $data = [];
            //dd($product_data);
            foreach($product_data['data'] as $key=>$result){
                //dd($result->sku);
                $package = getPackageName($result['package_id']);
                $data[$key]['url'] = action('ProductDetailController@display',
                    ['cat_url'=>$result['category']['url'],'sku'=>$result['sku']]);
                $data[$key]['name'] = $result['category']['category_name'];
                $data[$key]['value'] = $result['category']['category_name'];

                //$data[$key]['sku'] = $result['sku'];
                $data[$key]['price'] = $result['unit_price'].' '.$default_currency->name.'/'.$package;
                $data[$key]['image'] =  getProductImageUrlRunTime($result['thumbnail_image'], 'thumb_50x50');
                $data[$key]['shop_name'] = $result['shop']['shop_name'];

            }
            return $data; 


        }
        if($request->searchtype=='shop'){
            $all_shop = \App\MongoShop::where('shop_name','like','%'.$term.'%')->where('shop_status','open')->where('status','1')->paginate(10)->toArray();
            $data = []; 
            //dd($product_data);
            foreach($all_shop['data'] as $key=>$result){
                //dd($result->sku);
                $data[$key]['url'] = action('ShopController@index',['shop'=>$result['shop_url']]);
                $data[$key]['name'] = $result['shop_name'];
                $data[$key]['value'] = $result['shop_name'];

                $data[$key]['sku'] = '';
                $data[$key]['price'] = '';
                $data[$key]['image'] =  getImgUrl($result['logo'],'logo');
            }
            return $data;

        }*/
       
        // $show_variant = $this->systemConfig('SHOW_VARIANT');

        // $default_lang = session('default_lang');
        // /*fetch associated attribute set with products to search*/
        // $search = '';
        // $search = trim($request->term);
        // if(empty($search)){
        //   echo false;
        //   exit;
        // }

        // $results = DB::table(with(new Product)->getTable().' as p')
        // ->join(with(new ProductDesc)->getTable().' as ad', [['p.id', '=', 'ad.product_id'], ['ad.lang_id', '=' , DB::raw($default_lang)]])
        // ->select(DB::raw('Distinct('.$this->prefix.'ad.name)'),'p.sku','p.initial_price', 'p.thumbnail_image', 'p.url')
        // ->whereIn('p.site_visibility', ['2','3'])
        // ->where(['p.status' => '1']);

        // if($show_variant == '0') {
        //     $results = $results->where(['p.parent_id'=>0]);
        // }
        
        // $results = $results->where(function($query) {
        //     $query->whereRaw(DB::raw(dateEmptyQuery()));
        // });
        
        // $results = $results->where(function($query) use ($search) {
        //     return $query->where('p.sku', 'like', '%'.$search.'%')
        //           ->Orwhere('ad.name', 'like', '%'.$search.'%');
        // });

        // $results = $results->orderBy('ad.name', 'ASC')
        // ->limit(10)
        // ->get(); 
        

        // $data = [];
        // foreach($results as $key=>$result){
        //     $data[$key]['url'] = getProductUrl($result->url);
        //     $data[$key]['value'] = $result->name;
        //     $data[$key]['sku'] = $result->sku;
        //     $data[$key]['price'] = session('default_currency_code').' '.$result->initial_price;
        //     $data[$key]['image'] =  getProductImageUrl($result->thumbnail_image, 'thumb_105145');
        // }

        // if(!empty($data)){
        //     echo json_encode($data);
        // }
    } 


    public function addIntoWishlist(Request $request){
    
        $product_id = (int)$request->product_id;
        if(isset($product_id) && !empty($product_id)){
            $user_id = Auth::id();
            $shop_id = session('user_shop_id');
            $prd_shop_id = \App\Product::where('id',$product_id)->value('shop_id');
            
            if(Auth::User()->user_type == 'seller' && $prd_shop_id == $shop_id){
                $json_data['status'] = 'unsuccess';
                $json_data['generated_at'] = date('M d, Y H:i a');
                $json_data['message'] = Lang::get('product.you_can_not_add_your_own_product');
                echo json_encode($json_data);
                exit();
            }

            $productFavourite = new \App\MongoWishlist;
            $productFavourite->product_id = (int)$request->product_id;
            $productFavourite->user_id = $user_id;
            $json_data = array();
            try{
            $result = \App\MongoWishlist::where(['user_id'=>$user_id,'product_id'=>$product_id])->get();
            if(count($result)){
              $json_data['message'] = Lang::get('product.already_exists_in_wishlist');              
            }else{
              $productFavourite->save();
              $json_data['message'] = Lang::get('product.product_added_into_wishlist');
            }
            $json_data['status'] = 'success';
            $json_data['generated_at'] = date('M d, Y H:i a');

            //$json_data['redirect_url'] = action('User\WishlistController@index');
            }catch(QueryException $e)  { 
                $json_data['status'] = 'unsuccess';
                $json_data['generated_at'] = date('M d, Y H:i a');
                $json_data['message'] = Lang::get('common.opps_something_went_wrong');
            }
            echo json_encode($json_data);
        }
        exit; 
    } 
   
    public function removeFromWishlist(Request $request){
        $product_id = $request->product_id;
        if(isset($product_id) && !empty($product_id)){
            $user_id = Auth::id();
            $saveData = \App\MongoWishlist::where(['product_id'=> (int)$product_id, 'user_id'=>$user_id])->first();
            $json_data = array();
            try{
                if(!empty($saveData)){
                    $saveData->delete();
                }
                $json_data['status'] = 'success';
                $json_data['generated_at'] = date('M d, Y H:i a');
                $json_data['message'] = Lang::get('common.product_removed_from_wishlist');
            }catch(QueryException $e)  { 
               $json_data['status'] = 'unsuccess';
               $json_data['generated_at'] = date('M d, Y H:i a');
               $json_data['message'] = Lang::get('common.oops_something_went_wrong');
            }

            echo json_encode($json_data);  

        }
        exit;
    }

    public function productsRenderhtml(){
        echo LayoutHtmlHelpers::productsRender();
    }

    public function getNewArrivalProducts(Request $request){
        $request->request->add(['from_page'=>'new_arrival']);
        return $this->getProductListing($request);
    }

    public function newArrivalProducts(Request $request){
        //$breadcrumb = 'New Arrival Product';
        //$referer_url = $request->server('REQUEST_SCHEME')."://".$request->server('HTTP_HOST').'/'.$request->server('REQUEST_URI');

        $breadcrumb = 'New Arrival';     
        return view(loadFrontTheme('newArrivalProduct'),['breadcrumb'=>$breadcrumb]); 
    }



    public function salesPromotion(){
        $breadcrumb = 'Sales & Permotion';
        return view(loadFrontTheme('salesPermotion'),['breadcrumb'=>$breadcrumb]);
    }

    public function salesPromotionProducts(Request $request){      
        $request->request->add(['from_page'=>'sales_permotion']);
        return $this->getProductListing($request);
    }


    public function getProductList(Request $request){
        $shop_id = null;
        if($request->shop_id){
            $shop_id = $request->shop_id;
        }

        $blade = $request->blade;
        $filter_attributes = $request->fillterAttributes;
        $name = stripTags($request->search);
        $page_item = $request->itemsPerPage; 
        $order_by = $request->orderBy;
        $order = $request->order;
        //$cat_ids= null;

        $query = \App\MongoProduct::query();
        if(!is_null($shop_id))
            $query->where('shop_id',$shop_id)
            ->where('stock','1');

        if(!empty($request->badge_id) && is_array($request->badge_id)){
            $badges = array_map('intval', $request->badge_id);
            $query->whereIn('badge_id',$badges);
        }

        if(!empty($request->cat_id) && is_array($request->cat_id)){
            $cats = array_map('intval', $request->cat_id);
            $cat_id_arr = [];
            foreach ($cats as $key => $value) {
                if($value){
                    $cat_id_arr[] = $value;
                }
                
            }
            
            if($cat_id_arr){
                $query->whereIn('cat_id',$cat_id_arr);
            }
            
        }

        $query->where('status', '1')->with('badge')->with('category')->with('shop');
        if(Auth::check()){
            if($blade=='wishlist'){
                $query->has('wishlist');
            }
            $query->with('wishlist');    
        }

        $product_data = $query->paginate($page_item)->toArray();

        $product_data = $this->formatListingData($product_data);
        return ['detail'=>$product_data,'status'=>'success'];
     
    }

    public function getProductsByShop(Request $request){
        $request->request->add(['blade'=>'shop']);
        return $this->getProductList($request);

    }
    public function getProductByWishlist(Request $request){
        $request->request->add(['blade'=>'wishlist']);
        return $this->getProductList($request);
    } 


    public function getAllReviews(){
        
        $rev_data = DB::table(with(new \App\ProductReview)->getTable().' as prv')
                    ->where(['prv.product_id'=>$product_id])->get();
        if(count($rev_data)){

        }else{
            return [];
        }
    }
}
