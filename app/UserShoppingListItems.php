<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class UserShoppingListItems extends Model {

    protected $table = 'user_shopping_list_items';  

    public function getCatDesc(){
    	return $this->hasOne('App\CategoryDesc', 'cat_id', 'cat_id')->where('lang_id', session('default_lang')); 
    }

    public function getCategory(){
    	return $this->hasOne('App\Category', 'id', 'cat_id'); 
    }
}
