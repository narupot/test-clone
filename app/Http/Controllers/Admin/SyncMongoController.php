<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\MarketPlace;
//use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Config;
use Lang;

class SyncMongoController extends MarketPlace
{
    
    public function __construct()
    {
        $this->middleware('admin.user');
    }
    
    public function index(Request $request)
    {   
        /******update unit*******/
        $prd = \App\Product::count();
        
        $mon = \App\MongoProduct::count();
        
        dd($request->all(),$prd,$mon);
        
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
                        $unit = $this->productSync();
                        break;

                    case 'wishlist':
                        $unit = $this->wishlistSync();
                        break;
                    case 'sizegrade':
                        $unit = $this->sizegradeSync();
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
        $delete = \App\MongoUnit::where('_id','>',0)->delete();
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

    public function productSync(){
        $delete = \App\MongoProduct::where('_id','>',0)->delete();
        $products = \App\Product::get();
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
    }

}
