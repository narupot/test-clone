<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use DB;

class ShopAssignCategory extends Model {

    protected $table = 'shop_assign_category';

    public static function getShopCategory($shop_id = null) {
    	$shop_id = $shop_id ? $shop_id : session('user_shop_id');
    	return DB::table(with(new ShopAssignCategory)->getTable().' as sac')
			->join(with(new Category)->getTable().' as cat', ['sac.category_id'=>'cat.id'])
			->join(with(new CategoryDesc)->getTable().' as catd', ['cat.id'=>'catd.cat_id'])
			->select('cat.id', 'cat.url', 'cat.img','catd.category_name')
			->where(['sac.shop_id'=>$shop_id, 'cat.status'=>'1', 'catd.lang_id'=>session('default_lang')])
			->get();
    }

    public static function getShopCategoryForFilter($shop_id = null) {
        $shop_id = $shop_id ? $shop_id : session('user_shop_id');
        return DB::table(with(new ShopAssignCategory)->getTable().' as sac')
            ->join(with(new Category)->getTable().' as cat', ['sac.category_id'=>'cat.id'])
            ->join(with(new CategoryDesc)->getTable().' as catd', ['cat.id'=>'catd.cat_id'])
            ->where(['sac.shop_id'=>$shop_id, 'cat.status'=>'1', 'catd.lang_id'=>session('default_lang')])
            ->pluck('catd.category_name','cat.id');
    }

    public function catDesc(){
        return $this->hasOne('App\CategoryDesc', 'category_id', 'category_id')->where('lang_id', session('default_lang'));  
    }

    public function getCat(){
        return $this->hasOne('App\Category', 'id', 'category_id')->select('id','img');  
    }

    public function shopDesc(){
        return $this->hasOne('App\ShopDesc', 'shop_id', 'shop_id')->where('lang_id', session('default_lang'));  
    }

}
