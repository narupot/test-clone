<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;

use Auth;
use Lang;
use DB;

class StockMemoController extends MarketPlace
{   
    public function __construct() {
        $this->middleware('authenticate');
    }
    
    public function index() {
        //dd(session()->all()); 

        $fielddata = json_encode(['fieldSets' =>[], 'tableConfig'=>[]]);

        return view('seller.stock_memo.product_stock', ['fielddata'=>$fielddata]);
    }

    public function getStockList(Request $request) {

        //$perpage = !empty($request->per_page) ? $request->per_page : getPagination('limit');

        $default_lang = session('default_lang');
        $shop_id = session('user_shop_id');
        
        $sql = DB::table(with(new \App\Product)->getTable().' as p')
                ->join(with(new \App\Category)->getTable().' as c', 'p.cat_id', '=', 'c.id')
                ->join(with(new \App\CategoryDesc)->getTable().' as cd', 
                            ['p.cat_id'=>'cd.cat_id', 'cd.lang_id'=>DB::raw($default_lang)])
                ->leftjoin(with(new \App\UnitDesc)->getTable().' as ud', ['p.base_unit_id'=>'ud.unit_id', 'ud.lang_id' => DB::raw($default_lang)])
                ->leftjoin(with(new \App\PackageDesc)->getTable().' as pkd',['p.package_id'=>'pkd.package_id','pkd.lang_id'=>DB::raw($default_lang)])
                ->join(with(new \App\Badge)->getTable().' as b', 'p.badge_id', '=', 'b.id')
                ->select('p.id', 'p.sku','p.stock', 'p.quantity', 'p.weight_per_unit', 'p.thumbnail_image', 'cd.category_name', 'c.url' ,'b.icon', 'ud.unit_name','pkd.package_name','ud.unit_name')
                ->where('p.shop_id',  $shop_id)
                ->latest('p.updated_at');

        $products_data = $sql->paginate();        

        foreach ($products_data as $key => $value) {

            $badge_img = getBadgeImageUrl($value->icon);
            $product_img = getProductImageUrlRunTime($value->thumbnail_image, 'thumb_59x50');  
            $view_url = action('ProductDetailController@display',[$value->url, $value->sku]);

            $manage_stock_url = '';
            if($value->stock == '0') {
                $manage_stock_url = action('Seller\StockMemoController@edit',$value->id);
            }

            $products_data[$key]->badge_img = $badge_img;
            $products_data[$key]->product_img = $product_img;
            $products_data[$key]->view_url = $view_url;
            $products_data[$key]->manage_stock_url = $manage_stock_url;
            if(isset($value->unit_name))
                $products_data[$key]->weight_per_unit = $value->weight_per_unit."/".$value->unit_name;
            if($value->stock == '1'){
                $products_data[$key]->quantity = Lang::get('product.unlimited'); 
            }
        }

        return $products_data;
    }        
    
    public function create() {
    }

    function store(Request $request) {
        //echo "<pre>"; print_r($request->all()); die;

        $stock_data['shop_id'] = session('user_shop_id');
        $stock_data['channel'] = '3';

        $stock_data['product_id'] = $request->product_id;
        $stock_data['qty'] = $request->qty;
        $stock_data['type'] = $request->type;

        return \App\ProductStockMemo::updateProductStock($stock_data);
    }   

    public function show(Request $request) {
        //echo "<pre>"; print_r($request->all()); die;
    }
    
    function edit(Request $request, $id) {
        //echo "<pre>"; print_r($request->all()); die;

        $fielddata = json_encode(['fieldSets' =>[], 'tableConfig'=>[]]);

        $shop_id = session('user_shop_id');

        $product_data = \App\Product::getProductBasicInfo($id, $shop_id);
        if(!empty($product_data)) {
            $badge_img = getBadgeImageUrl($product_data->icon);
            $product_img = getProductImageUrlRunTime($product_data->thumbnail_image, 'thumb_59x50');  
            $view_url = action('ProductDetailController@display',[$product_data->url, $product_data->sku]);

            $product_data->badge_img = $badge_img;
            $product_data->product_img = $product_img;
            $product_data->view_url = $view_url;                
            //dd($product_data);
            return view('seller.stock_memo.product_stock_manage', ['product_data'=>$product_data, 'fielddata'=>$fielddata]);
        }
    }

    public function getStockMemo(Request $request, $id) {
        //echo '====>'.$id;die;

        $perpage = !empty($request->per_page) ? $request->per_page : getPagination('limit');
        $shop_id = session('user_shop_id');
        $sql = DB::table(with(new \App\Product)->getTable().' as p')
                ->leftjoin(with(new \App\UnitDesc)->getTable().' as ud', ['p.base_unit_id'=>'ud.unit_id', 'ud.lang_id' => DB::raw(session('default_lang'))])
                ->join(with(new \App\ProductStockMemo)->getTable().' as psm', 'p.id', '=', 'psm.product_id')
                ->select('p.id as product_id','ud.unit_name','psm.*')
                ->where(['p.id'=>$id, 'p.shop_id'=>$shop_id])
                ->latest('psm.created_at');

        $products_data = $sql->paginate($perpage);        
        foreach ($products_data as $key => $value) {
            $products_data[$key]->date = getDateFormat($value->created_at);
            $products_data[$key]->time = getDateFormat($value->created_at, 'T');
            $products_data[$key]->balance_val = $value->balance.' '.$value->unit_name;
            $value->channel = getChannel($value->channel);             

            if($value->import > 0) {
                $products_data[$key]->sold_val = '-';
                $products_data[$key]->import_val = '+'.$value->import.' '.$value->unit_name;
                $products_data[$key]->comulative_val = $value->balance-$value->import.' '.$value->unit_name;
            }
            elseif($value->sold > 0) {
                $products_data[$key]->sold_val = '-'.$value->sold.' '.$value->unit_name;
                $products_data[$key]->import_val = '-';                
                $products_data[$key]->comulative_val = $value->balance+$value->sold.' '.$value->unit_name;
            }
            else {
                $products_data[$key]->comulative_val = '-';
            }           
        }

        return $products_data;
    }    

    function update(Request $request, $id) {
    }     

    function delete(Request $request) {
    }
}