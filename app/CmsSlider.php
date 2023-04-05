<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Http\Controllers\MarketPlace;
use Auth; 

class CmsSlider extends Model
{
    protected  $table = 'cms_slider';

    public function sliderdesc(){
       return $this->hasOne('App\CmsSliderDesc', 'cms_slider_id', 'id')->where('lang_id', session('default_lang')); 
    }

    public function sliderCat(){
       return $this->hasMany('App\CmsSliderCategory', 'cms_slider_id', 'id'); 
    }

    public static function getCmsSlider(){
        return self::with('sliderdesc')->orderBy('id','DESC')->get();
    }

    public static function getSliderbyId($id){
        return Self::where('id',$id)->with(['sliderdesc','sliderCat'])->first();
    } 

    public static function getSliderDetail($slider_id){
    	$slider_data = Self::where('id',$slider_id)->with(['sliderdesc','sliderCat'])->first();
        $sliderdesign = designVal();
    	if(!empty($slider_data)){
    		if($slider_data->banner){
    			$slider_data->banner = getSliderImageUrl($slider_data->banner);
    		}

            if(isMobile() && $slider_data->banner_mob_image){
                $slider_data->banner = getSliderImageUrl($slider_data->banner_mob_image);
            }

            $slider_option = json_decode($slider_data->slider_option,true);
            $slider_data->slider_option = $slider_option;
            //dd($slider_data->toArray());
            switch ($slider_data->type) {
                case 'product':
                    if($slider_data->name == 'product-for-you'){
                       $data = Self::productForYou($slider_data);
                    }else{
                       $data = Self::getSliderProduct($slider_data);
                    }
                    if($data){
                      $slider_data->slider = $data;   
                    }else{
                      $slider_data->slider = []; 
                    }
                    break;
                case 'blog':
                    $data = Self::getSliderBlog($slider_data);
                    $slider_data->slider = $data;
                    break;
                case 'category':
                    $data = Self::getSliderCategory($slider_data);
                    $slider_data->slider = $data;
                    break;
                case 'brand':
                    $data = Self::getSliderBrand($slider_data);
                    $slider_data->slider = $data;
                    break;
                default:
                    $slider_data->slider = [];
                    break;
            }
    		
            if($slider_data->design){
                $slider_data->design_val = explode('_',$sliderdesign[$slider_data->design]);
            }else{
                $slider_data->design_val = [];
            }
            

    	}
    	return $slider_data;
    }

    public static function getSliderProduct($slider_data,$data_type=null){
        
        $badge_id_arr = $slider_data->badge_id?array_map('intval', explode(',', $slider_data->badge_id)):[];

        $package_id_arr = $slider_data->package_id?array_map('intval', explode(',', $slider_data->package_id)):[];

        if(isset($slider_data->slider_option['sort_by'])){
            switch ($slider_data->slider_option['sort_by']) {
                case 'name':
                    $sort_by = 'name.'.session('lang_code');
                    break;
                case 'updated_at':
                    $sort_by = 'updated_at';
                    break;
                case 'created_at':
                    $sort_by = 'created_at';
                    break;
                case 'price':
                    $sort_by = 'unit_price';
                    break;
                default:
                    $sort_by = '_id';
                    break;
            }
        }else{
            $sort_by = '_id';
        }

        if(isset($slider_data->slider_option['sort_by_val'])){
            $sort_by_val = $slider_data->slider_option['sort_by_val'];
        }else{
            $sort_by_val = 'desc';
        }
        $sort_by_val_int = $sort_by_val=='asc'?1:-1;

        $sub_cat_ids = [];
        $sub_cat_data = [];
        $prd_data_arr = [];
        $slider_con = $slider_data->slider_condition;
        if($slider_con == 'master_level_1'){
            $parent_cat_ids = $slider_data->sliderCat->pluck('category_id')->toArray();

            if(count($parent_cat_ids)){
               
                $sub_cat_ids_data = MongoCategory::getSubCat($parent_cat_ids,null)->toArray();
                if(count($sub_cat_ids_data)){
                    foreach ($sub_cat_ids_data as $key => $value) {
                        $sub_cat_data[$value['_id']] = $value;
                        $sub_cat_ids[] = $value['_id'];
                    }
                }
                
            }
        }else{
            
            if($slider_data->custom_id){
                $id_arr = array_map('intval', explode(',', $slider_data->custom_id));
                $sub_cat_ids_data = MongoCategory::getSubCat(null,$id_arr)->toArray();
                if(count($sub_cat_ids_data)){
                    foreach ($sub_cat_ids_data as $key => $value) {
                        $sub_cat_data[$value['_id']] = $value;
                        $sub_cat_ids[] = $value['_id'];
                    }
                }
            }
        } 
        
        if(count($badge_id_arr) && count($sub_cat_ids) && count($package_id_arr)){
            
            $product_data = \App\MongoProduct::where('status','1')->raw(function($collection) use ($sub_cat_ids,$badge_id_arr,$package_id_arr,$sort_by,$sort_by_val_int)
            {
                return $collection->aggregate([
                         [
                            '$match' => [
                                'cat_id' => ['$in' => $sub_cat_ids],
                                'badge_id' => ['$in' => $badge_id_arr],
                                'package_id' => ['$in' => $package_id_arr],
                                'status' => '1'
                            ],     
                        ],
                        [
                            '$group' => [
                                '_id' =>[
                                    'cat_id'=>'$cat_id',
                                    'badge_id'=>'$badge_id',
                                    'package_id'=>'$package_id'
                                ],
                                'unit_price'=>[
                                    '$avg'=> '$unit_price'
                                ]
                            ],
                            
                        ],
                        [   
                            '$sort' => [$sort_by => $sort_by_val_int]   
                        ], 
                        [
                            '$limit' => 20
                        ],
                        
                    ]);
            })->toArray();
            //dd($product_data,$badge_id_arr,$sub_cat_ids,$package_id_arr);
            
            if(count($product_data)){
                foreach ($product_data as $key => $value) {
                    $id_arr = (array)$value['_id'];
                    $id_arr['unit_price'] = $value['unit_price'];
                    if($id_arr['unit_price']<1){
                        //dd($value);
                    }
                    if(isset($sub_cat_data[$id_arr['cat_id']]) && $id_arr['unit_price']>0){
                        $cat_data = $sub_cat_data[$id_arr['cat_id']];
                        $id_arr['cat_name'] = $cat_data['name'][session('lang_code')];
                        $id_arr['cat_url'] = getCategoryUrl($cat_data['url']);
                        $id_arr['cat_img'] = getCategoryImageUrl($cat_data['img']);
                        $id_arr['badge_img'] = getBadgeImage($id_arr['badge_id']);
                        $id_arr['package_name'] = getPackageName($id_arr['package_id']);
                        $prd_data_arr[] = $id_arr;
                    }
                    
                }
            }
            
        }
        return $prd_data_arr;

    }

    public static function getSliderBlog($slider_data,$data_type=null){

        $default_lang = session('default_lang');
        $prefix = DB::getTablePrefix();
        $current_date = date('Y-m-d');
        $qry = false;
        $blog_limit = (isset($slider_data->tot_item) && $slider_data->tot_item > 0) ? $slider_data->tot_item:20;
        $results = DB::table(with(new Blog)->getTable().' as b')
                ->leftjoin(with(new BlogDesc)->getTable().' as bd', [['b.id', '=', 'bd.blog_id'], ['bd.lang_id', '=' , DB::raw($default_lang)]])
                ->leftjoin(with(new BlogCat)->getTable().' as bc', 'b.id', '=', 'bc.blog_id'
                    )
                ->select(DB::raw('Distinct('.$prefix.'b.id)'), 'b.url', 'b.feature_image','b.created_at', 'bd.blog_title')
                ->where(['b.status' => '1','publish'=>'1']);
                
        switch ($slider_data->slider_condition) {
            
            case 'custom_id':
                if($slider_data->custom_id){
                    $qry = true;
                    $bid = explode(',', $slider_data->custom_id);
                    $results->whereIn('b.id',$bid);
                }
                break;
            
            case 'cat_latest_blog':
                if(!empty($slider_data->sliderCat)){
                    $qry = true;
                    $cat_ids = $slider_data->sliderCat->pluck('category_id');
                    $results->whereIn('bc.cat_id', $cat_ids);
                }
                break;
            default:
                # code...
                break;
        }

        if(isset($slider_data->slider_option['sort_by'])){
            switch ($slider_data->slider_option['sort_by']) {
                case 'name':
                    $sort_by = 'bd.blog_title';
                    break;
                case 'updated_at':
                    $sort_by = 'b.updated_at';
                    break;
                case 'created_at':
                    $sort_by = 'b.created_at';
                    break;
                default:
                    $sort_by = 'b.id';
                    break;
            }
        }else{
            $sort_by = 'b.id';
        }

        if(isset($slider_data->slider_option['sort_by_val'])){
            $sort_by_val = $slider_data->slider_option['sort_by_val'];
        }else{
            $sort_by_val = 'desc';
        }

        if($qry){
            $results->orderBy($sort_by,$sort_by_val);
            if($data_type){
                return $results;
            }
            $results = $results->paginate($blog_limit);
            
            return $results;
        }else{
            return [];
        }
        
    }

    public static function getSliderCategory($slider_data,$data_type=null){

        $cat_ids = $slider_data->sliderCat->pluck('category_id');
        $default_lang = session('default_lang');
        $prefix = DB::getTablePrefix();
        $current_date = date('Y-m-d');
        $qry = false;
        $cat_limit = (isset($slider_data->tot_item) && $slider_data->tot_item > 0) ? $slider_data->tot_item:20;
        if(count($cat_ids)){

            $results = DB::table(with(new \App\Category)->getTable().' as c')
                ->leftjoin(with(new \App\CategoryDesc)->getTable().' as cd', [['c.id', '=', 'cd.cat_id'], ['cd.lang_id', '=' , DB::raw($default_lang)]])
                ->select('c.url', 'c.img', 'cd.category_name')
                ->where(['c.status' => '1','is_deleted'=>'0'])
                ->whereIn('c.id',$cat_ids);
                if(isset($slider_data->slider_option['sort_by'])){
                    switch ($slider_data->slider_option['sort_by']) {
                        case 'name':
                            $sort_by = 'cd.category_name';
                            break;
                        case 'updated_at':
                            $sort_by = 'c.updated_at';
                            break;
                        case 'created_at':
                            $sort_by = 'c.created_at';
                            break;
                        default:
                            $sort_by = 'c.id';
                            break;
                    }
                }else{
                    $sort_by = 'c.id';
                }

                if(isset($slider_data->slider_option['sort_by_val'])){
                    $sort_by_val = $slider_data->slider_option['sort_by_val'];
                }else{
                    $sort_by_val = 'desc';
                }
                $results->orderBy($sort_by,$sort_by_val);
                $results = $results->paginate($cat_limit);
            if(count($results)){
                foreach ($results as $key => $value) {
                    $results[$key]->url = action('ProductsController@category', $value->url);
                    $results[$key]->img = getCategoryImageUrl($value->img);
                }
            }
            return $results;
        }
        return [];
        
    }

    public static function getSliderBrand($slider_data,$data_type=null){
        $default_lang = session('default_lang');
        $prefix = DB::getTablePrefix();
        $current_date = date('Y-m-d');
        $qry = false;
        $brand_limit = (isset($slider_data->tot_item) && $slider_data->tot_item > 0) ? $slider_data->tot_item:20;
        $brand_ids = ($slider_data->brand_id)?explode(',', $slider_data->brand_id):[];
        if(count($brand_ids)){
            $results = DB::table(with(new \App\Brand)->getTable().' as b')
                ->leftjoin(with(new \App\BrandDesc)->getTable().' as bd', [['b.id', '=', 'bd.brand_id'], ['bd.lang_id', '=' , DB::raw($default_lang)]])
                ->select('b.url', 'b.brand_logo', 'bd.brand_name','bd.brand_title')
                ->where(['b.status' => '1'])
                ->whereIn('b.id',$brand_ids);
                //->paginate($brand_limit);

            if(isset($slider_data->slider_option['sort_by'])){
                switch ($slider_data->slider_option['sort_by']) {
                    case 'name':
                        $sort_by = 'bd.brand_name';
                        break;
                    case 'updated_at':
                        $sort_by = 'b.updated_at';
                        break;
                    case 'created_at':
                        $sort_by = 'b.created_at';
                        break;
                    default:
                        $sort_by = 'b.id';
                        break;
                }
            }else{
                $sort_by = 'b.id';
            }

            if(isset($slider_data->slider_option['sort_by_val'])){
                $sort_by_val = $slider_data->slider_option['sort_by_val'];
            }else{
                $sort_by_val = 'desc';
            }
            $results->orderBy($sort_by,$sort_by_val);
            $results = $results->paginate($brand_limit);
            if(count($results)){
                foreach ($results as $key => $value) {
                    $results[$key]->url = action('BrandController@brand', $value->url);
                    $results[$key]->brand_logo = getBrandLogoUrl($value->brand_logo);
                }
            }

            return $results;
        }
        return [];
    }

    public static function getFormatPrd($results){
        $obj = new MarketPlace;

        if(count($results)){
            foreach($results as $key=>$result){
                //format data as per need  
                $productDataArray = (object)$obj->formatProductData($result);
                $results[$key] =  $productDataArray;
            }
        }
        return $results;
    }

    public static function getFeatureProduct($slider_data){
        if($slider_data->feature_sku){
            $sku_arr = explode(',', $slider_data->feature_sku);
            if(count($sku_arr)){
                $default_lang = session('default_lang');
                $prefix = DB::getTablePrefix();
                $current_date = date('Y-m-d');
                $results = DB::table(with(new Product)->getTable().' as p')
                        ->leftjoin(with(new ProductDesc)->getTable().' as ad', [['p.id', '=', 'ad.product_id'], ['ad.lang_id', '=' , DB::raw($default_lang)]])
                        ->select(DB::raw('Distinct('.$prefix.'p.id)'), 'p.url', 'p.thumbnail_image', 'p.sku', 'p.product_type', 'p.initial_price','p.currency_id', 'p.special_price','p.currency_id','ad.name','ad.short_desc','p.created_at', 'p.from_date', 'p.to_date','p.has_sp_tp_bp', 'p.sp_tp_bp_type',DB::raw('if('.$prefix.'p.sp_tp_bp_type=1 && ('.$prefix.'p.from_date<="'.$current_date.'" && '.$prefix.'p.to_date>="'.$current_date.'"),'.$prefix.'p.special_price,'.$prefix.'p.initial_price) as price'),'p.avg_rating')
                        ->whereIn('p.site_visibility', ['2','3'])
                        ->where(['p.status' => '1']);
                        $results = $results->where(function($query) {
                            $query->whereRaw(DB::raw(dateEmptyQuery()));
                        });
                        $results->whereIn('p.sku',$sku_arr)->orderBy('ad.name', 'ASC');
                        $results = $results->get();
                if(count($results)){
                    $get_prd = Self::getFormatPrd($results);
                
                    return $results;
                }
            }
        }
        return [];
    }

    public static function getSliderProductTestMike($slider_data,$data_type=null){
        
        $badge_id_arr = $slider_data->badge_id?array_map('intval', explode(',', $slider_data->badge_id)):[];

        $package_id_arr = $slider_data->package_id?array_map('intval', explode(',', $slider_data->package_id)):[];

        if(isset($slider_data->slider_option['sort_by'])){
            switch ($slider_data->slider_option['sort_by']) {
                case 'name':
                    $sort_by = 'name.'.session('lang_code');
                    break;
                case 'updated_at':
                    $sort_by = 'updated_at';
                    break;
                case 'created_at':
                    $sort_by = 'created_at';
                    break;
                case 'price':
                    $sort_by = 'unit_price';
                    break;
                default:
                    $sort_by = '_id';
                    break;
            }
        }else{
            $sort_by = '_id';
        }

        if(isset($slider_data->slider_option['sort_by_val'])){
            $sort_by_val = $slider_data->slider_option['sort_by_val'];
        }else{
            $sort_by_val = 'desc';
        }
        $sort_by_val_int = $sort_by_val=='asc'?1:-1;

        $sub_cat_ids = [];
        $sub_cat_data = [];
        $prd_data_arr = [];
        $slider_con = $slider_data->slider_condition;
        if($slider_con == 'master_level_1'){
            $parent_cat_ids = $slider_data->sliderCat->pluck('category_id')->toArray();

            if(count($parent_cat_ids)){
               
                $sub_cat_ids_data = MongoCategory::getSubCat($parent_cat_ids,null)->toArray();
                if(count($sub_cat_ids_data)){
                    foreach ($sub_cat_ids_data as $key => $value) {
                        $sub_cat_data[$value['_id']] = $value;
                        $sub_cat_ids[] = $value['_id'];
                    }
                }
                
            }
        }else{
            
            if($slider_data->custom_id){
                $id_arr = array_map('intval', explode(',', $slider_data->custom_id));
                $sub_cat_ids_data = MongoCategory::getSubCat(null,$id_arr)->toArray();
                if(count($sub_cat_ids_data)){
                    foreach ($sub_cat_ids_data as $key => $value) {
                        $sub_cat_data[$value['_id']] = $value;
                        $sub_cat_ids[] = $value['_id'];
                    }
                }
            }
        } 
        
        if(count($badge_id_arr) && count($sub_cat_ids) && count($package_id_arr)){
            
            $product_data = \App\MongoProduct::where('status','1')->raw(function($collection) use ($sub_cat_ids,$badge_id_arr,$package_id_arr,$sort_by,$sort_by_val_int)
            {
                return $collection->aggregate([
                         [
                            '$match' => [
                                'cat_id' => ['$in' => $sub_cat_ids],
                                'badge_id' => ['$in' => $badge_id_arr],
                                'package_id' => ['$in' => $package_id_arr],
                                'status' => '1'
                            ],     
                        ],
                        [
                            '$group' => [
                                '_id' =>[
                                    'cat_id'=>'$cat_id',
                                    'badge_id'=>'$badge_id',
                                    'package_id'=>'$package_id'
                                ],
                                'unit_price'=>[
                                    '$avg'=> '$unit_price'
                                ]
                            ],
                            
                        ],
                        [   
                            '$sort' => [$sort_by => $sort_by_val_int]   
                        ], 
                        [
                            '$limit' => 20
                        ],
                        
                    ]);
            })->toArray();
            //dd($product_data,$badge_id_arr,$sub_cat_ids,$package_id_arr);
            
            if(count($product_data)){
                foreach ($product_data as $key => $value) {
                    $id_arr = (array)$value['_id'];
                    $id_arr['unit_price'] = $value['unit_price'];
                    if($id_arr['unit_price']<1){
                        //dd($value);
                    }
                    if(isset($sub_cat_data[$id_arr['cat_id']])){
                        $cat_data = $sub_cat_data[$id_arr['cat_id']];
                        $id_arr['cat_name'] = $cat_data['name'][session('lang_code')];
                        $id_arr['cat_url'] = getCategoryUrl($cat_data['url']);
                        $id_arr['cat_img'] = getCategoryImageUrl($cat_data['img']);
                        $id_arr['badge_img'] = getBadgeImage($id_arr['badge_id']);
                        $id_arr['package_name'] = getPackageName($id_arr['package_id']);
                        $prd_data_arr[] = $id_arr;
                    }
                    
                }
            }
            
        }
        return $prd_data_arr;

    }

    public static function productForYou($slider_data){
        if(!Auth::check()){
          return false;
        }
        $badge_id_arr = $slider_data->badge_id?array_map('intval', explode(',', $slider_data->badge_id)):[];

        $package_id_arr = $slider_data->package_id?array_map('intval', explode(',', $slider_data->package_id)):[];

        if(isset($slider_data->slider_option['sort_by'])){
            switch ($slider_data->slider_option['sort_by']) {
                case 'name':
                    $sort_by = 'name.'.session('lang_code');
                    break;
                case 'updated_at':
                    $sort_by = 'updated_at';
                    break;
                case 'created_at':
                    $sort_by = 'created_at';
                    break;
                case 'price':
                    $sort_by = 'unit_price';
                    break;
                default:
                    $sort_by = '_id';
                    break;
            }
        }else{
            $sort_by = '_id';
        }

        if(isset($slider_data->slider_option['sort_by_val'])){
            $sort_by_val = $slider_data->slider_option['sort_by_val'];
        }else{
            $sort_by_val = 'desc';
        }
        $sort_by_val_int = $sort_by_val=='asc'?1:-1;

        $sub_cat_ids = [];
        $sub_cat_data = [];
        $prd_data_arr = [];
        $slider_con = $slider_data->slider_condition;
        //dd($slider_con);
        if($slider_con == 'master_level_1'){
            $parent_cat_ids = $slider_data->sliderCat->pluck('category_id')->toArray();
            if(count($parent_cat_ids)){
               
                $sub_cat_ids_data = MongoCategory::getSubCat($parent_cat_ids,null)->toArray();
                if(count($sub_cat_ids_data)){
                    foreach ($sub_cat_ids_data as $key => $value) {
                        $sub_cat_data[$value['_id']] = $value;
                        $sub_cat_ids[] = $value['_id'];
                    }
                }
                
            }
        }else{
            if($slider_data->custom_id){
                $id_arr = array_map('intval', explode(',', $slider_data->custom_id));
                $sub_cat_ids_data = MongoCategory::getSubCat(null,$id_arr)->toArray();
                if(count($sub_cat_ids_data)){
                    foreach ($sub_cat_ids_data as $key => $value) {
                        $sub_cat_data[$value['_id']] = $value;
                        $sub_cat_ids[] = $value['_id'];
                    }
                }
            }
        } 
        
        if(count($badge_id_arr) && count($sub_cat_ids) && count($package_id_arr)){
            $newdate = date("Y-m-d", strtotime("-6 months"));
            $prefix = DB::getTablePrefix();
            $result_arr  = [];
            $user_id = Auth::id();
            $product_data = DB::table(with(new \App\OrderDetail)->getTable() . ' as od')
                ->join(with(new \App\Product)->getTable() . ' as p', [['od.product_id', '=', 'p.id']])
                ->join(with(new \App\Shop)->getTable() . ' as s', [['p.shop_id', '=', 's.id']])
                ->select(DB::raw('count(' . $prefix . 'od.sku) as totord'), 'od.sku','od.order_detail_json','p.unit_price','p.base_unit_id','p.thumbnail_image','p.badge_id','p.package_id','p.id', 'p.weight_per_unit', 'p.cat_id')
                ->whereDate('od.created_at','>=',$newdate)
                ->where('od.user_id',$user_id)
                ->whereIn('p.cat_id', $sub_cat_ids)
                ->whereIn('p.badge_id', $badge_id_arr)
                ->whereIn('p.package_id', $package_id_arr)
                ->where('p.status','1')
                ->where('s.shop_status','open')
                ->groupBy('od.sku')
                ->orderBy('totord','desc')->limit(20)
                ->get()->toArray();
            
            if($product_data){ 
                $tot_data = count($product_data);
                if($tot_data < 20){
                    $remain = 20-$tot_data;
                    $remain_record = DB::table(with(new \App\OrderDetail)->getTable() . ' as od')
                    ->join(with(new \App\Product)->getTable() . ' as p', [['od.product_id', '=', 'p.id']])
                    ->join(with(new \App\Shop)->getTable() . ' as s', [['p.shop_id', '=', 's.id']])
                    ->select(DB::raw('count(' . $prefix . 'od.sku) as totord'), 'od.sku','od.order_detail_json','p.unit_price','p.base_unit_id','p.thumbnail_image','p.badge_id','p.package_id','p.id', 'p.weight_per_unit','p.cat_id')
                    ->whereDate('od.created_at','>=',$newdate)
                    ->where('od.user_id','!=',$user_id)
                    ->whereIn('p.cat_id', $sub_cat_ids)
                    ->whereIn('p.badge_id', $badge_id_arr)
                    ->whereIn('p.package_id', $package_id_arr)
                    ->where('p.status','1')
                    ->where('s.shop_status','open')
                    ->groupBy('od.sku')
                    ->orderBy('totord','desc')
                    ->limit($remain)
                    ->get()->toArray();
                    if($remain_record){
                        $product_data = array_merge($product_data, $remain_record);
                    }

                }    

            }else{
                
                $newdate = date("Y-m-d", strtotime("-3 months"));
                $product_data = DB::table(with(new \App\OrderDetail)->getTable() . ' as od')
                    ->join(with(new \App\Product)->getTable() . ' as p', [['od.product_id', '=', 'p.id']])
                    ->join(with(new \App\Shop)->getTable() . ' as s', [['p.shop_id', '=', 's.id']])
                    ->select(DB::raw('count(' . $prefix . 'od.sku) as totord'), 'od.sku','od.order_detail_json','p.unit_price','p.base_unit_id','p.thumbnail_image','p.badge_id','p.package_id','p.id', 'p.weight_per_unit','p.cat_id')
                    ->whereDate('od.created_at','>=',$newdate)
                    ->where('p.status','1')
                    ->whereIn('p.cat_id', $sub_cat_ids)
                    ->whereIn('p.badge_id', $badge_id_arr)
                    ->whereIn('p.package_id', $package_id_arr)
                    ->where('s.shop_status','open')
                    ->groupBy('od.sku')
                    ->orderBy('totord','desc')
                    ->limit(20)
                    ->get()->toArray();



            }   
            
            if(count($product_data)){
                foreach ($product_data as $key => $value) {
                    $id_arr = [];
                    $id_arr['_id'] = $value->id;
                    $id_arr['unit_price'] = $value->unit_price;
                    $cat_data = $sub_cat_data[$value->cat_id];
                    $id_arr['cat_name'] = $cat_data['name'][session('lang_code')];
                    $id_arr['cat_url'] = getCategoryUrl($cat_data['url']);
                    $id_arr['cat_img'] = getCategoryImageUrl($cat_data['img']);
                    $id_arr['badge_img'] = getBadgeImage($value->badge_id);
                    $id_arr['package_name'] = getPackageName($value->package_id);
                    $prd_data_arr[] = $id_arr;
                    
                    
                }
            }
            
        }
        //dd($prd_data_arr);
        return $prd_data_arr;

    }


}
