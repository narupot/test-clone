<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class UserShoppingList extends Model {

    protected $table = 'user_shopping_list';  

    public function getShoppingDesc(){
    	return $this->hasOne('App\UserShoppingListDesc', 'shopping_list_id', 'id')->where('lang_id', session('default_lang')); 
    }

    public function getShoppingItems(){
    	return $this->hasMany('App\UserShoppingListItems', 'shopping_list_id', 'id'); 
    }

    
}
