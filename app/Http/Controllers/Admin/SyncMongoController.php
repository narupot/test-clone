<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\MarketPlace;
//use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Config;
use Lang;
use Illuminate\Support\Facades\Log;


class SyncMongoController extends MarketPlace
{
    
    public function __construct()
    {
        $this->middleware('admin.user');
    }
    
    public function index(Request $request)
    {   
        /******update unit*******/
        //$prd = \App\Product::count();
        
        //$mon = \App\MongoProduct::count();
        
        //dd($request->all(),$prd,$mon);
        
        if(isset($request->sync)){
            $sync = explode(',',$request->sync);
            foreach ($sync as  $synckey) {
                switch ($synckey) {
                    case 'unit':
                        $unit = $this->unitSync();
                        break;

                    case 'badge':
                        $badge = $this->badgeSync();
                        break;

                    case 'package':
                        $package = $this->packageSync();
                        break;

                    case 'category':
                        $category = $this->categorySync();
                        break;

                    case 'shop':
                        $shop = $this->shopSync();
                        break;

                    case 'product':
                        $prd_resp = $this->productSync($request);
                        if($prd_resp){
                            return redirect($prd_resp);
                        }
                        break;

                    case 'wishlist':
                        $unit = $this->wishlistSync();
                        break;
                    case 'sizegrade':
                        $unit = $this->sizegradeSync();
                        break;
                    case 'producttypetag':
                        $unit = $this->productTypeTagSync();
                        break;
                    case 'producttypetagcustom':
                        $start = $request->start ?? null;
                        $end = $request->end ?? null;
                        $unit = $this->productTypeTagCustomSync($start, $end);
                        break;
                     case 'parentcategory':
                        $unit = $this->parentCategorySync();
                        break;
                    
                    default:
                        # code...
                        break;
                }
            }
        }
        
    }

    public function unitSync(){

        $data = \App\Unit::with('unitdescAll')->get();
        $delete = \App\MongoUnit::where('_id','>',0)->delete();
        foreach ($data as $key => $value) {
            $up = \App\MongoUnit::updateData($value);
        }

    }  

    public function sizegradeSync(){

        $data = \App\SizeGrade::with('sizegradedescAll')->get();
        $delete = \App\MongoSizeGrade::where('_id','>',0)->delete();
        foreach ($data as $key => $value) {
            $up = \App\MongoSizeGrade::updateData($value);
        }

    } 

    public function badgeSync(){

        $data = \App\Badge::with('descAll')->get();
        $delete = \App\MongoBadge::where('_id','>',0)->delete();
        foreach ($data as $key => $value) {
            $up = \App\MongoBadge::updateData($value);
        }

    }

    public function packageSync(){
        $delete = \App\MongoPackage::where('_id','>',0)->delete();
        $data = \App\Package::with('packagedescAll')->get();
        foreach ($data as $key => $value) {
            $up = \App\MongoPackage::updateData($value);
        }

    }

    public function categorySync(){
        $delete = \App\MongoCategory::where('_id','>',0)->delete();
        $data = \App\Category::with('descAll')->with('Units')->get();

        foreach ($data as $key => $value) {
            //dd($value);
            $up = \App\MongoCategory::updateData($value);
        }

    }

    public function shopSync(){
        $delete = \App\MongoShop::where('_id','>',0)->delete();
        $shop_data = \App\Shop::with('allDesc')->with('shopUser')->get();
        foreach ($shop_data as $key => $value) {
            $check_cat = \App\ShopAssignCategory::where(['shop_id'=>$value->id])->pluck('category_id')->toArray();
            $value->shop_category = $check_cat;
            $update_data = \App\MongoShop::updateData($value);
        }
    }

    public function productSync($request){

        $page = !empty($request->page) ? $request->page : 0;
        $limit = 1000;
        $skip = $page * $limit;
        
        if($page==0){
            $delete = \App\MongoProduct::where('_id','>',0)->delete();
        }
        $products = \App\Product::skip($skip)->take($limit)->get();
        //dd($products);
        if(count($products)){
            foreach ($products as $key => $product) {
                $product_id = $product->id;
                $desc = \App\ProductDesc::where('product_id',$product->id)->value('description');
                $product->description = $desc;
                $image_arr = \App\ProductImage::where('product_id', $product_id)->pluck('image')->toArray();
                $product->image = $image_arr;
                //$product->thumbnail_image = isset($image_arr[0])?$image_arr[0]:'';

                $tier_price_arres = \App\ProductTierPrice::select('start_qty','end_qty','unit_price')->where('product_id', $product_id)->get()->toArray();
                if(count($tier_price_arres)){
                   $product->tier_price_data = $tier_price_arres;
                }

                \App\MongoProduct::updateData($product);
            }

            $page = $page + 1;
            $redirect_url = action('Admin\SyncMongoController@index', ['sync' => 'product', 'page' => $page]);
            
            return $redirect_url;

        }else{
            dd('done');
        }
        
        
    }

    public function productTypeTagSync()
    {
        try {
            \App\MongoProductTypeTag::where('_id', '>', 0)->delete();
            $tags = \App\ProductTypeTag::all();
            foreach ($tags as $tag) {
                $data = [
                    'product_type_id' => $tag->product_type_id,
                    'tag'             => $tag->tag,
                    'tag_status'      => $tag->tag_status,
                    'created_at'      => $tag->created_at,
                    'updated_at'      => $tag->updated_at,
                ];
                \App\MongoProductTypeTag::updateOrCreate(
                    ['product_type_id' => $tag->product_type_id, 'tag' => $tag->tag],
                    $data
                );
            }
            return response()->json([
                'status'  => 'success',
                'message' => 'Product Type Tags synced successfully!',
            ]);
        } catch (\Exception $e) {
            echo $e->getMessage();
            Log::error('Error syncing Product Type Tags: ' . $e->getMessage());
        }
      
    }

    public function parentCategorySync()
    {
        try {
            
            \App\MongoParentCategory::truncate();
            
            $categories = \App\ParentCategory::all();

            foreach ($categories as $cat) {
                
                $data = [
                    'category_name'    => $cat->category_name,
                    'url'              => $cat->url,
                    'img'              => $cat->img,
                    'is_deleted'       => $cat->is_deleted,
                    'meta_title'       => $cat->meta_title,
                    'sorting_no'       => $cat->sorting_no,
                    'group_id'         => $cat->group_id,
                    'subgroup_id'      => $cat->subgroup_id,
                    'meta_keyword'     => $cat->meta_keyword,
                    'meta_description' => $cat->meta_description,
                    'cat_description'  => $cat->cat_description,
                    'created_at'       => $cat->created_at, 
                    'created_by'       => $cat->created_by,
                    'updated_at'       => $cat->updated_at,
                    'updated_by'       => $cat->updated_by,
                    'mysql_id'         => $cat->id,
                ];

                \App\MongoParentCategory::updateOrCreate(
                    ['mysql_id' => $cat->id],
                    $data
                );
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Parent Categories synced successfully!',
            ]);

        } catch (\Exception $e) {
            \Log::error('Error syncing Parent Categories: ' . $e->getMessage());
            
            return response()->json([
                'status'  => 'error',
                'message Parent Categories' => $e->getMessage()
            ], 500);
        }
    }

    public function productTypeTagCustomSync($start,$end)
    {
        if(!$start || !$end){
            echo "Please provide start and end values.";
            return;
        }

        try {
            $tags = \App\ProductTypeTag::whereBetween('id', [$start, $end])->get();
            foreach ($tags as $tag) {
                $data = [
                    'product_type_id' => $tag->product_type_id,
                    'tag'             => $tag->tag,
                    'tag_status'      => $tag->tag_status,
                    'created_at'      => $tag->created_at,
                    'updated_at'      => $tag->updated_at,
                ];
                \App\MongoProductTypeTag::updateOrCreate(
                    ['product_type_id' => $tag->product_type_id, 'tag' => $tag->tag],
                    $data
                );
            }
            return response()->json([
                'status'  => 'success',
                'message' => 'Product Type Tags custom synced successfully!',
            ]);
        } catch (\Exception $e) {
            echo $e->getMessage();
            Log::error('Error custom syncing Product Type Tags: ' . $e->getMessage());
        }
      
    }



}
