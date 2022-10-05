<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Auth;

class MenuItems extends Model {
    protected $table = 'megamenu_items';
    public $timestamps = false;  

    public function getMenuItemsDesc() {       
        return $this->hasMany('App\MenuItemsDesc', 'menu_item_id', 'id');
    } 

    public function getMenuItemDesc() { 
        return $this->hasOne('App\MenuItemsDesc', 'menu_item_id', 'id')->where('lang_id',session('default_lang'));
    }     
}
