<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Auth;

class Menu extends Model {
    
    protected $table = 'menus';      
    protected $fillable = ['name', 'slug', 'url', 'status', 'parent_id', 'menu_type'];
    protected $guarded = ['id'];

    public static function getAdminRoleMenu($parent_id=0, $role_id=0){

        if(Auth::guard('admin_user')->user()->admin_level == -1 && $role_id == 0) {
            $menus = DB::table(with(new Menu)->getTable().' as m')
                ->join(with(new MenuDesc)->getTable().' as md', 'md.menu_id', '=', 'm.id')
                ->select('m.id', 'md.menu_name as name')
                ->where([['status', '=', '1'], ['parent_id', '=', $parent_id], ['md.lang_id', '=', session('default_lang')]])
                ->orderBy('order_by', 'asc')
                ->get();
        }
        else { 

            $role_id = ($role_id>0)?$role_id:Auth::guard('admin_user')->user()->role_id;
            
            $menus = DB::table(with(new MenusPermission)->getTable().' as mp')
                    ->join(with(new Menu)->getTable().' as m', 'mp.menu_id', '=', 'm.id')
                    ->join(with(new MenuDesc)->getTable().' as md', 'md.menu_id', '=', 'm.id')
                    ->select('m.id', 'md.menu_name as name')
                    ->where([['mp.role_id', '=', $role_id], ['m.status', '=', '1'], ['m.parent_id', '=', $parent_id], ['md.lang_id', '=', session('default_lang')]])
                    ->orderBy('order_by', 'asc')
                    ->get();  
        }

        return $menus;
    } 

    public static function getAdminMenu($parent_id=0){
        //dd(Auth::guard('admin_user')->user());
        if(Auth::guard('admin_user')->user()->admin_level == -1) {
            $menus = DB::table(with(new Menu)->getTable().' as m')
                ->join(with(new MenuDesc)->getTable().' as md', 'md.menu_id', '=', 'm.id')
                ->select('m.id', 'm.slug', 'm.url', 'm.parent_id', 'm.icon_class', 'm.menu_type', 'md.menu_name as name')
                ->where([['status', '=', '1'], ['parent_id', '=', $parent_id], ['menu_type', '!=', '2'], ['md.lang_id', '=', session('default_lang')]])
                ->orderBy('order_by', 'asc')
                ->get();
        }
        else { 
            $role_id = Auth::guard('admin_user')->user()->role_id;
            $menus = DB::table(with(new MenusPermission)->getTable().' as mp')
                    ->join(with(new Menu)->getTable().' as m', 'mp.menu_id', '=', 'm.id')
                    ->join(with(new MenuDesc)->getTable().' as md', 'md.menu_id', '=', 'm.id')
                    ->select('m.id', 'm.slug', 'm.url', 'm.parent_id', 'm.icon_class', 'm.menu_type', 'md.menu_name as name')
                    ->where([['mp.role_id', '=', $role_id], ['m.status', '=', '1'], ['m.parent_id', '=', $parent_id], ['m.menu_type', '!=', '2'], ['md.lang_id', '=', session('default_lang')]])
                    ->orderBy('order_by', 'asc')
                    ->get();  
        }

        return $menus;
    }

    public function menuName() {
        return $this->hasOne('App\MenuDesc', 'menu_id', 'id')->where('lang_id', session('default_lang'));
    }

    public static function getMenuAll() {

        return self::where('status', '1')->select('id')->with('menuName')->get();
    }            
}
