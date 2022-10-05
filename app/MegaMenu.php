<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Auth;

class MegaMenu extends Model {
    
    protected $table = 'megamenu';      
    protected $fillable = ['title', 'descriptions','menu_json'];
    protected $guarded = ['id'];

   
    public function block() {       
        return $this->hasOne('App\Block','id','block_id');
    } 

    public function staticBlock() {       
        return $this->hasOne('App\StaticBlock', 'id','type_id');
    }

    public function staticBlockDesc() {       
        return $this->hasOne('App\StaticBlockDesc', 'id', 'static_block_id')->where('lang_id', session('default_lang'));
    }
    public static function getAdminMenu($parent_id=0){

        if(Auth::guard('admin_user')->user()->admin_level == -1) {
            $menus = DB::table(with(new Menu)->getTable().' as m')
                ->join(with(new MenuDesc)->getTable().' as md', 'md.menu_id', '=', 'm.id')
                ->select('m.id', 'md.menu_name as name', 'm.url', 'm.parent_id', 'm.icon_class', 'm.menu_type')
                ->where([['status', '=', '1'], ['parent_id', '=', $parent_id], ['menu_type', '!=', '2'], ['md.lang_id', '=', session('default_lang')]])
                ->orderBy('order_by', 'asc')
                ->get();
        }
        else { 
            $role_id = Auth::guard('admin_user')->user()->role_id;
            $menus = DB::table(with(new MenusPermission)->getTable().' as mp')
                    ->join(with(new Menu)->getTable().' as m', 'mp.menu_id', '=', 'm.id')
                    ->join(with(new MenuDesc)->getTable().' as md', 'md.menu_id', '=', 'm.id')
                    ->select('m.id', 'md.menu_name as name', 'm.url', 'm.parent_id', 'm.icon_class', 'm.menu_type')
                    ->where([['mp.role_id', '=', $role_id], ['m.status', '=', '1'], ['m.parent_id', '=', $parent_id], ['m.menu_type', '!=', '2'], ['md.lang_id', '=', session('default_lang')]])
                    ->orderBy('order_by', 'asc')
                    ->get();  
        }

        return $menus;
    }

    public static function getMenuAll() {
        return self::select('id')->get();
    }     
    
    //Get Header Menu
    public function getHeaderMenu() {
         return $this->hasOne('App\Block','block_id','id')
              ->select('*')
              ->where('status','1');
    } 

    public function getMenuItems() {       
        return $this->hasMany('App\MenuItems', 'menu_id','id')->where('parent_id',0)->orderBy('menu_order','asc');
    }

    /*public function getMenuDesign() {       
        return $this->hasOne('App\MenuDesign', 'id','menu_design_id');
    }*/

    


   

}
