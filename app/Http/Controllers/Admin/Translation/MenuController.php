<?php

namespace App\Http\Controllers\Admin\Translation;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;

use App\Menu;
use App\MenuDesc;
use Lang;

class MenuController extends MarketPlace
{
    private $tblMenuDesc;

    public function __construct()
    {
        $this->middleware('admin.user');
        $this->tblMenuDesc = with(new MenuDesc)->getTable();
    }
    
    public function index()
    {
        $permission = $this->checkUrlPermission('manage_translation_menu');
        if($permission === true) { 

            $permission_arr['edit'] = $this->checkMenuPermission('edit_menu_language'); 
            $menu_lists = Menu::getMenuAll();
            return view('admin.translation.menuList', ['menu_lists'=>$menu_lists, 'permission_arr'=>$permission_arr]);
        }
    }
    
    function edit($menu_id) {
        
        $menuData = \App\MenuDesc::where(['menu_id'=>$menu_id,'lang_id'=>session('default_lang')])->first();
        
        $permission = $this->checkUrlPermission('edit_menu_language');
        if($permission === true) {        
            return view('admin.translation.menuEdit', ['tblMenuDesc'=>$this->tblMenuDesc, 'id'=>$menu_id,'menuData'=>$menuData]);
        }
    }
    
    function update(Request $request, $menu_id)
    {
        //echo '<pre>';print_r($request->all());die;
        
        if($menu_id> 0) {

            $default_menu_name = $request->menu_name[session('default_lang')];

            $input = $request->all();
            $input['menu'] = $default_menu_name;

            $rules = ['menu' => 'Required|Min:3'];
            $error_msg['menu.required'] = Lang::get('admin.please_enter_menu_name');
                    
            $validate = Validator::make($input, $rules, $error_msg);

            if ($validate->passes()) {                       
                
                MenuDesc::where('menu_id', $menu_id)->delete();

                foreach($request->menu_name as $key=>$value){

                    if(empty($value)) {
                        $value = $default_menu_name;
                    }
                    $menu_arr[] = ['menu_id'=>$menu_id, 'lang_id'=>$key, 'menu_name'=>$value];
                }

                MenuDesc::insert($menu_arr);

                return redirect()->action('Admin\Translation\MenuController@index')->with('succMsg', Lang::get('admin.record_updated_successfully'));
            }
            else {
              return redirect()->action('Admin\Translation\MenuController@edit', $menu_id)->withErrors($validate)->withInput();
            }                       
        }
    }       
}
