<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use App\Language;
use App\Country;
use App\AttributeValue;
use App\Menu;
use App\MenusPermission;
use App\Province;
use App\Currency;
use Config;
use Auth;
use Form;
use Session;
use Lang;
use Illuminate\Support\Str;

use Carbon\Carbon;
use App\Order;

class CustomHelpers {
    
    public static function generateRandomString($length = 8) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characterLength = strlen($characters);
        $code = '';
        for ($j = 0; $j < $length; $j++) {
            $code .= $characters[rand(0, $characterLength - 1)];
        }
        return $code;
    }

    public static function getEnumValues(string $table, string $column): array
    {
        $type = DB::selectOne("SHOW COLUMNS FROM {$table} WHERE Field = '{$column}'")->Type;
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        return isset($matches[1])
            ? array_map(fn($v) => trim($v, "'"), explode(',', $matches[1]))
            : [];
    }


    public static function getUserMenu() {

        $default_lang_code = '';

        //$default_lang_code = session('lang_code');

        //dd(session()->all());

        // to select parent menu
        $menu_path = '/'.\Request::path();

        $menu_path = str_replace($default_lang_code.'/', '', $menu_path);

        $menus = Menu::select('id', 'parent_id')->where('url','=', $menu_path)->first();
        
        $main_menus_id = 0;
        $sub_menus_id = 0;
        $final_menus_id = 0;
        
        if(!empty($menus)) {
            
            if($menus->parent_id > 0){
                $sub_menus_id = $menus->id;
                $main_menus_id = $menus->parent_id;

                $menus2 = Menu::select('parent_id')->where('id','=', $menus->parent_id)->first();

                if(!empty($menus2)){

                    $final_menus_id = $menus->id;
                    $sub_menus_id = $menus->parent_id;
                    $main_menus_id = $menus2->parent_id;
                }
            }
            else {
                $main_menus_id = $menus->id;
            }            
        }

        //echo '====>'.$main_menus_id.'=='.$sub_menus_id.'=='.$final_menus_id;

        // to select parent menu        

        $menu_str = '';

        $users_main_menus = Menu::getAdminMenu();
        foreach ($users_main_menus as $users_main_menu){
            
            $menu_link = 'javascript:void(0);';
            if($users_main_menu->menu_type == '1'){
                $menu_link = $default_lang_code.$users_main_menu->url;
            }

            $class_str = '';                            
            if($main_menus_id == $users_main_menu->id) {                               
                $class_str = 'class="active"';
            }             
          
            $menu_icon = 'icon-seller-menu3';
            if(!empty($users_main_menu->icon_class)) {
                $menu_icon = $users_main_menu->icon_class;
            }

            $menu_str .= '<li '.$class_str.'><a href="'.$menu_link.'" title="'.$users_main_menu->name.'"> <span class="icon '.$menu_icon.'"></span>'.$users_main_menu->name.'</a>';

            $users_sub_menus = Menu::getAdminMenu($users_main_menu->id);       
            if($users_sub_menus) {                  

                $menu_str .= '<div class="submenu-wrapper">
                                <div class="close-menu"><span class="fa fa-times"></span></div>
                                <h3>'.$users_main_menu->name.'</h3>
                                <ul class="adm-submenu">';
                                                       
                foreach ($users_sub_menus as $users_sub_menu){
                    
                    $menu_link = 'javascript:void(0);';
                    $arrow_sign = '<span class="fa angle-arrow fa-angle-right"></span>';
                    if($users_sub_menu->menu_type == '1'){
                        $menu_link = $default_lang_code.$users_sub_menu->url;
                        $arrow_sign = '';
                    } 

                    $class_str = '';                            
                    if($sub_menus_id == $users_sub_menu->id) {
                        $class_str = 'class="active"';
                    }                                      
                    
                    $menu_str .= '<li '.$class_str.' id="'.$users_sub_menu->slug.'"><a href="'.$menu_link.'" title="'.$users_sub_menu->name.'">'.$users_sub_menu->name.' '.$arrow_sign.'</a>';

                    $users_final_menus = Menu::getAdminMenu($users_sub_menu->id);
                    if(!empty($users_final_menus)) { 

                        $menu_str .= '<ul>'; 

                        foreach ($users_final_menus as $users_final_menu){
                            
                            $menu_link = 'javascript:void(0);';
                            if($users_final_menu->menu_type == '1'){
                                $menu_link = $default_lang_code.$users_final_menu->url;
                            }  

                            $class_str = '';                            
                            if($final_menus_id == $users_final_menu->id) {                               
                                $class_str = 'class="active"';
                            } 

                            $menu_str .= '<li '.$class_str.'><a href="'.$menu_link.'" title="'.$users_final_menu->name.'">- '.$users_final_menu->name.'</a></li>';
                        }
                        $menu_str .= '</ul>'; 
                    }
                    $menu_str .= '</li>';
                }
                $menu_str .= '</ul></div>'; 
            }
            $menu_str .= '</li>';
        }
        
        return $menu_str;
    } 

    public static function getSetMenu($menu_id = null, $class=''){
        $menuHtml = '';
        if(!empty($menu_id)){

            $cache_key = 'desktopmega_menu_'.$menu_id.'_'.session('default_lang');
            if (cache_hasKey($cache_key) && \Config::get('constants.enable_cache')) {
                $menuHtml = cache_getData($cache_key);
            }
            if(!$menuHtml){
                $result = \App\MegaMenu::where('id', $menu_id)->where('status', '1')->first();
                if(!empty($result)){
                    $menuHtml .= '<ul id="menu'.$result->menu_design_id.'" class="'.$class.'">';
                    foreach($result->getMenuItems as $key=>$item){
                        //echo $item->id;
                        $url = Self::getMenuUrl($item);
                        $iconleft = $iconright = '';
                        if($item->icon_show == 'after_text'){
                          if(!empty($item->atr_menu_icon)) $iconright = '<span class="arrow ricon"><i class="'.$item->atr_menu_icon.'"></i></span>';
                        }else{
                          if(!empty($item->atr_menu_icon)) $iconleft = '<span class="arrow licon"><i class="'.$item->atr_menu_icon.'"></i></span>';  
                        }

                        $menuHtml .= '<li><span>'.$iconleft.'<a href="'.$url.'">'.$item->getMenuItemDesc->title.'</a>'.$iconright.'</span>';
                        $menuHtml .= Self::getSetSubMenu($item->id);      
                        $menuHtml .= '</li>';
                    }
                    $menuHtml .='</ul>'; 
                    cache_putData($cache_key,$menuHtml); 
                }   
            }
        }
        return $menuHtml;
    }

public static function getSimummuangMegaMenu($menu_id = null)
    {
        $html = '';
        
        $cache_key = 'mega_menu_v18_final_fix_' . session('default_lang');
        
        if (\Config::get('constants.enable_cache') && function_exists('cache_hasKey') && cache_hasKey($cache_key)) {
            return cache_getData($cache_key);
        }

        $productGroups = \App\ProductGroup::where('status', 1)
                            ->orderBy('sorting_no', 'asc')
                            ->with(['subgroups' => function($q) {
                                $q->where('status', 1)->orderBy('sorting_no', 'asc');
                            }, 'subgroups.categories']) 
                            ->get();

        if ($productGroups->count() > 0) {
            $html .= '<div class="mega-panel" id="megaPanel">';

            // --- 1. Mobile Header ---
            $html .= '
            <div class="mobile-top-bar">
                <div class="mobile-header-row" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <div style="display:flex; align-items:center;">
                        <span id="megaMenuBackBtn" style="display:none; cursor:pointer; font-size:20px; margin-right:15px; color:#333; padding: 5px;">
                            <i class="fas fa-chevron-left"></i>
                        </span>
                        <span class="menu-title" id="megaMenuTitle" style="font-weight:bold; font-size:18px; color:#333;">หมวดหมู่สินค้า</span>
                    </div>
                    <span class="close-btn" id="closeMegaMobile" style="font-size:28px; cursor:pointer; line-height:1; color:#999; padding: 5px;">&times;</span>
                </div>
            </div>';

            // --- 2. Left Menu ---
            $html .= '<ul class="left-menu" id="leftMenu">';
            $first = true;
            foreach ($productGroups as $group) {
                $activeClass = $first ? 'active' : '';
                $targetId = 'group-' . $group->id;
                
                $html .= '<li class="'.$activeClass.' mobile-drill-item" data-target="'.$targetId.'" data-title="'.$group->name.'">';
                $html .= '<span class="menu-text">' . $group->name . '</span>';
                $html .= '<span class="menu-badge"><i class="fas fa-chevron-right ml-1" style="font-size:12px; color:#ccc;"></i></span>';
                $html .= '</li>';
                $first = false;
            }
            $html .= '</ul>';

            $html .= '<div class="right-content-wrapper" id="rightContentWrapper">';
            $first = true;
            foreach ($productGroups as $group) {
                $activeClass = $first ? 'active' : '';
                $targetId = 'group-' . $group->id;
                
                $html .= '<div class="right-panel '.$activeClass.' layout-text-banner" id="'.$targetId.'">';
                $html .= '<div class="text-layout-wrap">';
                $html .= '<div class="text-area w-100" style="padding-right:0;">'; 
                
                $html .= '<div class="link-list-container">';
                
                if ($group->subgroups->count() > 0) {
                    foreach ($group->subgroups as $subgroup) {
                        
                        $level3Html = ''; 

                        if(!empty($subgroup->categories) && $subgroup->categories->count() > 0){
                            foreach($subgroup->categories as $cat){

                                $hasProduct = \DB::table('product as p')
                                            ->join('category as c', 'p.cat_id', '=', 'c.id')
                                            ->where('c.parent_id', $cat->id) 
                                            ->where('p.status', 1)
                                            ->exists();

                                if ($hasProduct) {
                                    $paramValue = !empty($cat->url) ? $cat->url : $cat->id;
                                    $catUrl = action('ProductsController@search') . "?productCate=" . $paramValue;
                                    
                                    $level3Html .= '<a href="'.$catUrl.'" class="sub-link">' . $cat->category_name .'</a>';
                                }
                            }
                        }

                        if (!empty($level3Html)) {
                            $html .= '<div class="menu-group">';
                        
                            $subgroupSlug = str_replace(' ', '_', trim($subgroup->subgroup_name));
                            $subUrl = action('ProductsController@search') . "?productType=" . $subgroupSlug;
                            $html .= '<a href="'.$subUrl.'" class="group-header">' . $subgroup->subgroup_name . '</a>';
                            
                            $html .= $level3Html;
                            
                            $html .= '</div>'; 
                        }
                    }
                } else {
                    $html .= '<p class="text-muted">ไม่พบข้อมูลหมวดหมู่ย่อย</p>';
                }

                $html .= '</div></div>'; 
                $html .= '</div>'; 
                $html .= '</div>'; 
                $first = false;
            }
            $html .= '</div>'; 

            // --- 4. Mobile Footer ---
            $html .= '
            <div class="mobile-footer" id="mobileMegaFooter">
                <ul class="service-menu">

                    <li><a href="'.action('Checkout\TrackOrderController@trackOrderDetail').'"><i class="fas fa-box"></i> ติดตามสถานะสินค้า</a></li>
                </ul>
            </div>';

            $html .= '</div>'; // End Wrapper

            if (function_exists('cache_putData')) {
                cache_putData($cache_key, $html);
            }
        }
        return $html;
    }

    

    public static function getExtraMenuFromDB($menu_id = null)
    {
        $menuHtml = '';

        if (empty($menu_id)) return '';
        $cache_key = 'extra_menu_db_' . $menu_id . '_' . session('default_lang');
        if (cache_hasKey($cache_key) && \Config::get('constants.enable_cache')) {
            return cache_getData($cache_key);
        }
        $result = \App\MegaMenu::where('id', $menu_id)->where('status', '1')->first();

        if (!empty($result)) {
            $menuHtml .= '<ul class="extra-nav pl-2">';

            foreach ($result->getMenuItems as $key => $item) {
                $url = Self::getMenuUrl2($item); 
                
                $iconHtml = '';
                if (!empty($item->atr_menu_icon)) {
                    if (strpos($item->atr_menu_icon, 'fa-') !== false || strpos($item->atr_menu_icon, 'fas') !== false) {
                        $iconHtml = '<i class="' . $item->atr_menu_icon . '"></i> ';
                    } else {
                        $iconHtml = '<img src="' . asset($item->atr_menu_icon) . '" style="width:20px; height:auto; margin-right:5px;"> ';
                    }
                }

                $title = $item->getMenuItemDesc->title;
                $activeClass = '';
                if (stripos($title, 'promotion') !== false || stripos($title, 'โปรโมชั่น') !== false) {
                    $activeClass = 'menu-highlight';
                }

                $subMenuHtml = Self::getExtraSubMenuFromDB($item->id);
                $hasChild = !empty($subMenuHtml);

                $menuHtml .= '<li class="extra-nav-item">';
                $menuHtml .= '<a href="' . $url . '" class="extra-nav-link ' . $activeClass . '">';
                $menuHtml .= $iconHtml . $title;
                
                if ($hasChild) {
                    $menuHtml .= ' <i class="fas fa-chevron-down" style="font-size:10px; margin-left:5px; opacity:0.5;"></i>';
                }
                $menuHtml .= '</a>';
                if ($hasChild) {
                    $menuHtml .= $subMenuHtml;
                }

                $menuHtml .= '</li>';
            }

            $menuHtml .= '</ul>';

            if (function_exists('cache_putData')) {
                cache_putData($cache_key, $menuHtml);
            }
        }

        return $menuHtml;
    }

    public static function getExtraSubMenuFromDB($parent_id)
    {
        $subMenuHtml = '';
        $results = \App\MenuItems::where('parent_id', $parent_id)->orderBy('menu_order', 'asc')->get();

        if (!empty($results) && count($results) > 0) {
            $subMenuHtml .= '<div class="extra-dropdown">';
            foreach ($results as $item) {
                $url = Self::getMenuUrl2($item);
                $title = $item->getMenuItemDesc->title;
                
                $subMenuHtml .= '<a href="' . $url . '" class="extra-sub-item">' . $title . '</a>';
            }
            $subMenuHtml .= '</div>';
        }

        return $subMenuHtml;
    }

    private static function generateRightPanelContent($items, $layoutType)
    {
        $html = '';

        if ($layoutType == 'layout-grid-icon') {
            // --- แบบที่ 1: Grid Icons (รูปภาพ + ชื่อ) ---
            $html .= '<div class="category-grid">';
            foreach ($items as $subItem) {
                $url = Self::getMenuUrl2($subItem);
                $title = $subItem->getMenuItemDesc->title ?? '';
                // สมมติว่าใช้ atr_menu_icon เก็บ URL รูปภาพ หรือ Class Icon
                // ถ้าเก็บเป็น Path รูปภาพ: <img src="'.$subItem->atr_menu_icon.'">
                // ถ้าเก็บเป็น Class Icon: <i class="'.$subItem->atr_menu_icon.'"></i>
                
                // ตัวอย่างนี้สมมติว่า atr_menu_icon เก็บ URL รูปภาพ
                $iconHtml = !empty($subItem->atr_menu_icon) 
                            ? '<img src="'.asset($subItem->atr_menu_icon).'">' 
                            : '<img src="https://via.placeholder.com/40">'; 

                $html .= '<div class="category-item" onclick="window.location.href=\''.$url.'\'">';
                $html .= $iconHtml;
                $html .= '<span>' . $title . '</span>';
                $html .= '</div>';
            }
            $html .= '</div>';

        } else {
            // --- แบบที่ 2: Text List + Banner ---
            $html .= '<div class="layout-text-banner-inner" style="display:flex; width:100%;">';
            
            // ส่วน Text Column
            $html .= '<div class="text-area" style="flex:1;">';
            $html .= '<div class="link-list-container">';
            
            // ในแบบ Text เราอาจจะมองว่า SubItem คือ Group Header
            // และต้องไปดึงลูกของ SubItem อีกที (Level 3) ถ้ามี
            // แต่ถ้า DB มีแค่ 2 ชั้น เราจะแสดงรายการเลย
            foreach ($items as $subItem) {
                 $url = Self::getMenuUrl2($subItem);
                 $title = $subItem->getMenuItemDesc->title ?? '';
                 // แสดงเป็น Link ธรรมดา
                 $html .= '<a href="'.$url.'" class="sub-link">'.$title.'</a>';
            }
            
            $html .= '</div></div>'; // จบ Text Area

            // ส่วน Promo Column (Hardcode ไว้ก่อน หรือดึงจาก DB field พิเศษถ้ามี)
            $html .= '<div class="promo-column">';
            $html .= '<div class="promo-box">';
            $html .= '<div class="promo-img" style="height:100px; background:#eee;"></div>';
            $html .= '<div class="promo-btn">ดูสินค้า</div>';
            $html .= '</div>';
            $html .= '</div>'; // จบ Promo Column

            $html .= '</div>';
        }

        return $html;
    }

    public static function getSetMenu2($menu_id = null, $class=''){
        $menuHtml = '';
        $menuHtmlMobile = '';
        if(!empty($menu_id)){

            $cache_key = 'desktopmega_menu_'.$menu_id.'_'.session('default_lang');
            if (cache_hasKey($cache_key) && \Config::get('constants.enable_cache')) {
                $menuHtml = cache_getData($cache_key);
            }

            if(!$menuHtml){
                $result = \App\MegaMenu::where('id', $menu_id)->where('status', '1')->first();
                if(!empty($result) ){
                    $menuHtml .= '<ul id="menu'.$result->menu_design_id.'" class="  '.$class.'">';
                    foreach($result->getMenuItems as $key=>$item){
                        $url = Self::getMenuUrl2($item);
                        $iconleft = $iconright = '';
                        if($item->icon_show == 'after_text'){
                          if(!empty($item->atr_menu_icon)) $iconright = '<span class="arrow ricon"><i class="'.$item->atr_menu_icon.'"></i></span>';
                        }else{
                          if(!empty($item->atr_menu_icon)) $iconleft = '<span class="arrow licon"><i class="'.$item->atr_menu_icon.'"></i></span>';  
                        }

                        $menuHtml .= '<li class="nav-item"><span>'.$iconleft.'<a class="nav-link" href="'.$url.'">'.$item->getMenuItemDesc->title.'</a>'.$iconright.'</span>';
                        $menuHtml .= Self::getSetSubMenu2($item->id);
                        $menuHtml .= '</li>';

                    }
                    $menuHtml .='</ul>'; 
                    cache_putData($cache_key,$menuHtml); 
                }   
            }
        }
        return $menuHtml;
    }
    public static function getSetSubMenu2($id){
        
        $menuHtml = '';
        $results = \App\MenuItems::where('parent_id', $id)->orderBy('menu_order','asc')->get();
        if(!empty($results) && count($results)>0){
            $menuHtml .= '<ul>';
            foreach($results as $key=>$item){
                $iconleft = $iconright = '';
                if($item->icon_show == 'after_text'){
                  if(!empty($item->atr_menu_icon)) $iconright = '<span class="arrow ricon"><i class="'.$item->atr_menu_icon.'"></i></span>';
                }else{
                  if(!empty($item->atr_menu_icon)) $iconleft = '<span class="arrow licon"><i class="'.$item->atr_menu_icon.'"></i></span>';  
                }
                $url = Self::getMenuUrl2($item);

                $menuHtml .= '<li><span><a href="'.$url.'">'.$iconleft.$item->getMenuItemDesc->title.'</a>'.$iconright.'</span>';
                $menuHtml .= Self::getSetSubMenu2($item->id);     
                $menuHtml .= '</li>';
            }
            $menuHtml .='</ul>';
        }
        return $menuHtml;
    }
    public static function getMenuUrl2($item=null){
        $url = 'javascript:void(0)';
        if(!empty($item)){
            if($item->menu_type == 'Category'){
                // $catslug = \App\Category::where('id', $item->assoc_item_id)->value('url');
                //$url = action('ProductsController@category', $catslug);
                $cate = \App\Category::where('id', $item->assoc_item_id)->first();
                if(!empty($cate)){

                    $catslug = $cate->url;
                    if($cate->parent_id == 0){
                        $url = action('ProductsController@search')."?productCate=".$catslug;
                    }else{
                        $url = action('ProductsController@search')."?productType=".$catslug;
                    }
                }
            }elseif($item->menu_type == 'Pages'){
                $pageslug = \App\StaticPage::where('id', $item->assoc_item_id)->value('url');
                $url = action('StaticPageController@pagedata', $pageslug);
            }else{
                if(isset($item->getMenuItemDesc->url) && !empty($item->getMenuItemDesc->url)) $url = $item->getMenuItemDesc->url;  
            }
            
        } 
        return $url; 
    }


    public static function getMenuUrl($item=null){
        $url = 'javascript:void(0)';
        if(!empty($item)){
            if($item->menu_type == 'Category'){
                $catslug = \App\Category::where('id', $item->assoc_item_id)->value('url');
                //$url = action('ProductsController@category', $catslug);
                $url = action('ProductsController@newCategory', $catslug);
            }elseif($item->menu_type == 'Pages'){
                $pageslug = \App\StaticPage::where('id', $item->assoc_item_id)->value('url');
                $url = action('StaticPageController@pagedata', $pageslug);
            }else{
                if(isset($item->getMenuItemDesc->url) && !empty($item->getMenuItemDesc->url)) $url = $item->getMenuItemDesc->url;  
            }
            
        } 
        return $url; 
    }

    public static function getSetSubMenu($id){
        $menuHtml = '';
        $results = \App\MenuItems::where('parent_id', $id)->orderBy('menu_order','asc')->get();
        if(!empty($results)){
            $menuHtml .= '<ul>';
            foreach($results as $key=>$item){
                $iconleft = $iconright = '';
                if($item->icon_show == 'after_text'){
                  if(!empty($item->atr_menu_icon)) $iconright = '<span class="arrow ricon"><i class="'.$item->atr_menu_icon.'"></i></span>';
                }else{
                  if(!empty($item->atr_menu_icon)) $iconleft = '<span class="arrow licon"><i class="'.$item->atr_menu_icon.'"></i></span>';  
                }
                $url = Self::getMenuUrl($item);

                $menuHtml .= '<li><span><a href="'.$url.'">'.$iconleft.$item->getMenuItemDesc->title.'</a>'.$iconright.'</span>';
                $menuHtml .= Self::getSetSubMenu($item->id);     
                $menuHtml .= '</li>';
            }
            $menuHtml .='</ul>';
        }
        return $menuHtml;
    }

    public static function getRoleMenu() {

        $main_menus = Menu::getAdminRoleMenu();

        $menu_str = '';
        
        foreach ($main_menus as $main_menu) {

            $menu_str .= '<ul class="rolelist-check admin_menu_wrapper">
                            <li><label class="check-wrap"><input type="checkbox" name="menu_check[]" value="'.$main_menu->id.'"> <span class="chk-label">'.$main_menu->name.'</span></label>';

                $sub_menus = Menu::getAdminRoleMenu($main_menu->id);
                foreach ($sub_menus as $sub_menu) {
                    $menu_str .= '<ul>
                                    <li><label class="check-wrap"><input type="checkbox" name="menu_check[]" value="'.$sub_menu->id.'"> <span class="chk-label">'.$sub_menu->name.'</span></label>';

                        $final_menus = Menu::getAdminRoleMenu($sub_menu->id);                   
                        foreach ($final_menus as $final_menu) {
                            $menu_str .= '<ul>
                                            <li><label class="check-wrap"><input type="checkbox" name="menu_check[]" value="'.$final_menu->id.'"> <span class="chk-label">'.$final_menu->name.'</span></label>';

                                $finals = Menu::getAdminRoleMenu($final_menu->id);
                                foreach ($finals as $final) {
                                    $menu_str .= '<ul>
                                                    <li><label class="check-wrap"><input type="checkbox" name="menu_check[]" value="'.$final->id.'"> <span class="chk-label">'.$final->name.'</span></label>
                                                    </li>
                                                </ul>';
                                }
                                $menu_str .= '</li>
                            </ul>';
                        }
                        $menu_str .= '</li>
                    </ul>'; 
                }
                $menu_str .= '</li>
            </ul>';  
        }

        return $menu_str;       
    }     

    public static function getRoleMenuEdit($group_id) {

        $role_permisions = MenusPermission::where('role_id', '=', $group_id)->get();
        
        $menu_permision_arr = array();
        foreach($role_permisions as $role_permision){
            $menu_permision_arr[] = $role_permision->menu_id;
        }        

        $menu_str = '';

        $main_menus = Menu::getAdminRoleMenu();
        foreach ($main_menus as $main_menu) {

            $checked = '';
            if(in_array($main_menu->id, $menu_permision_arr)) {
                $checked = 'checked=checked';
            }

            $menu_str .= '<ul class="rolelist-check admin_menu_wrapper">
                            <li><label class="check-wrap"><input type="checkbox" name="menu_check[]" value="'.$main_menu->id.'" '.$checked.'> <span class="chk-label">'.$main_menu->name.'</span></label>';

                $sub_menus = Menu::getAdminRoleMenu($main_menu->id);
                foreach ($sub_menus as $sub_menu) {

                    $checked = '';
                    if(in_array($sub_menu->id, $menu_permision_arr)) {
                        $checked = 'checked=checked';
                    }

                    $menu_str .= '<ul>
                                    <li><label class="check-wrap"><input type="checkbox" name="menu_check[]" value="'.$sub_menu->id.'" '.$checked.'> <span class="chk-label">'.$sub_menu->name.'</span></label>';

                        $final_menus = Menu::getAdminRoleMenu($sub_menu->id);                   
                        foreach ($final_menus as $final_menu) {

                            $checked = '';
                            if(in_array($final_menu->id, $menu_permision_arr)) {
                                $checked = 'checked=checked';
                            }

                            $menu_str .= '<ul>
                                            <li><label class="check-wrap"><input type="checkbox" name="menu_check[]" value="'.$final_menu->id.'" '.$checked.'> <span class="chk-label">'.$final_menu->name.'</span></label>';

                                $finals = Menu::getAdminRoleMenu($final_menu->id);
                                foreach ($finals as $final) {

                                    $checked = '';
                                    if(in_array($final->id, $menu_permision_arr)) {
                                        $checked = 'checked=checked';
                                    }

                                    $menu_str .= '<ul>
                                                    <li><label class="check-wrap"><input type="checkbox" name="menu_check[]" value="'.$final->id.'" '.$checked.'> <span class="chk-label">'.$final->name.'</span></label>
                                                    </li>
                                                </ul>';
                                }
                                $menu_str .= '</li>
                            </ul>';
                        }
                        $menu_str .= '</li>
                    </ul>'; 
                }
                $menu_str .= '</li>
            </ul>';  
        }

        return $menu_str;       
    }

    public static function getRoleMenuDisplay($role_id) {        

        $menu_str = '';
        $i = 0;

        $main_menus = Menu::getAdminRoleMenu(0, $role_id);
        foreach ($main_menus as $main_menu) {

            $menu_str .= '<div class="rolemenu-row">
                <span class="menu-num">'.++$i.'</span>
                <ul class="menulist">
                    <li><a href="javascript:void(0)">'.$main_menu->name.'<i class="glyphicon glyphicon-menu-down"></i></a>';

            $sub_menus = Menu::getAdminRoleMenu($main_menu->id, $role_id);
            if($sub_menus) {
                $menu_str .= '<ul class="submenulist">';
                foreach ($sub_menus as $sub_menu) {
                    $menu_str .= '<li> <a href="javascript:void(0)">'.$sub_menu->name.'</a>';

                    $final_menus = Menu::getAdminRoleMenu($sub_menu->id, $role_id); 
                    if($sub_menus) { 
                        $menu_str .= '<ul class="submenulist">';                 
                        foreach ($final_menus as $final_menu) {
                            $menu_str .= '<li> <a href="javascript:void(0)">'.$final_menu->name.'</a>';

                            $finals = Menu::getAdminRoleMenu($final_menu->id, $role_id);
                            if($sub_menus) {
                                $menu_str .= '<ul class="submenulist">';
                                foreach ($finals as $final) {
                                    $menu_str .= '<li> <a href="javascript:void(0)">'.$final->name.'</a></li>';
                                }
                                $menu_str .= '</ul>';
                            }
                            $menu_str .= '</li>';
                        }
                        $menu_str .= '</ul>';
                    }
                    $menu_str .= '</li>'; 
                }
                $menu_str .= '</ul>';
            }
            $menu_str .= '</li></ul></div>';  
        }

        return $menu_str;       
    }                         

    public static function getCurrencyDorpDown($currency_id=null,$currency_id_arr=array()) {

        $currency_lists = \App\Currency::select('id', 'code')->where('status','1')->get();
        
        $currency_str = '';
        
        foreach($currency_lists as $currency)
        {            
           $selected = ''; 
            
           if($currency->id == $currency_id || in_array($currency->id, $currency_id_arr)) {
              
               $selected = 'selected="selected"'; 
           }
            
           $currency_str .= '<option value="'.$currency->id.'" '.$selected.'>'.$currency->code.'</option>';
        } 
        
        //echo '====>'.$country_str;die;
        
        return $currency_str;
    }


    public static function textWithEditLanuage($fieldType, $name, $tablename, $table_id, $table_field, $edtorClass=null, $errors=null, $errorkey=null, $validatorClass=null) {

        //return 'Amit';

        $languages = Language::where('status', '1')->orderBy('isDefault', 'desc')->get();
        $genTable = '';
        $datas = DB::table($tablename)->select($name, 'lang_id')->where([$table_field => $table_id])->get();
        $langdata = array();
        foreach ($datas as $data) {
            if (isset($data->lang_id)) {
                $langdata[$name][$data->lang_id] = $data->$name;
            }
        }
        foreach ($languages as $language) {
            
            $field_class = '';
            $error_class = '';
            
            if($language->isDefault == '1' && !empty($validatorClass)) {
                $field_class = $validatorClass;
                $error_class = 'has-error';
            }            

            $genTable .= '<div class="form-group">';
            $genTable .= '<div class="col-sm-2">';
            $genTable .= !empty($language->languageFlag) ?
                    '<img src="' . Config::get('constants.language_url') . $language->languageFlag . '" width="20" height="20" '
                    . 'title="' . $language->languageName . '" class="pull-right">' : '';
            $genTable .= '</div>';
            $genTable .= '<div class="col-sm-10 '.$error_class.'">';
            $genTable .= Form::$fieldType($name . '[' . $language->id . ']', old($name . '[' . $language->id . ']', isset($langdata[$name][$language->id]) ? $langdata[$name][$language->id] : ""), ['class' => 'form-control '.$edtorClass.' '.$field_class, 'placeholder' => '']);

            if($language->isDefault == '1'){
             if(!empty($errors) && !empty($errorkey)){
                if($errors->first($errorkey)){
                  $genTable .='<p id="name-error" class="error error-msg">'.$errors->first($errorkey).'</p>';
                }
              
             }

            }  
            $genTable .= '</div>';
            $genTable .= '</div>';
        }
        return $genTable;
    }    
    public static function textWithLanuage($fieldType, $name, $edtorClass = null, $validatorClass = null, $errors=null, $errorkey = null) {

        $languages = Language::where('status', '1')->orderBy('isDefault', 'desc')->get();
        $genTable = '';
        foreach ($languages as $language) {
            
            $field_class = '';
            $error_class = '';
            $def_lang = '';
            
            if($language->isDefault == '1' && !empty($validatorClass)) {
                $field_class = $validatorClass;
                $error_class = 'has-error';
                $def_lang = '<input type="hidden" name="def_lang_id" value="'.$language->id.'">';
            }
            
            $genTable .= $def_lang;
            $genTable .= '<div class="form-group">';
            $genTable .= '<div class="col-sm-2">';
            $genTable .= !empty($language->languageFlag) ?
                    '<img src="' . Config::get('constants.language_url') . $language->languageFlag . '" width="20" height="20" '
                    . 'title="' . $language->languageName . '" class="pull-right">' : '';
            $genTable .= '</div>';
            $genTable .= '<div class="col-sm-10 '.$error_class.'">';
            $genTable .= Form::$fieldType($name . '[' . $language->id . ']', old($name . '[' . $language->id . ']'), ['class' => 'form-control ' . $edtorClass .' '.$field_class, 'placeholder' => '']);

            
            if($language->isDefault == '1'){
             if(!empty($errors) && !empty($errorkey)){
                if($errors->first($errorkey)){
                  $genTable .='<p id="name-error" class="error error-msg">'.$errors->first($errorkey).'</p>';
                }
              
             }

            }               
                           
            $genTable .= '</div>';
            $genTable .= '</div>';
        }
        return $genTable;
    }
    public static function fieldstabWithLanuage($inputfielddatas, $tabseq = null, $errors=null, $is_angular = null ) {

        $default_lang = session('admin_default_lang');        

        $languages = Language::where('status', '1')
         ->orderByRaw(DB::raw("FIELD(id, $default_lang) DESC"))         
         ->orderBy('id', 'asc')
         ->get();
        $tot_active_lang = count($languages);
        $tabclass = ($tot_active_lang > 1)?'tab-content language-tab':'';

        $langTab = '<ul class="nav nav-tabs lang-nav-tabs">';
        $genTable = '<div class="'.$tabclass.'">';
        
        foreach ($languages as $language) {    
            $viewtab = '';
            if($tot_active_lang > 1){
                $langTab .= '<li '.$viewtab.'><a class="' . (($default_lang == $language->id) ? 'active' : '') . ' tablang_'.$tabseq . $language->id .'" data-toggle="tab" href="#lang' . $tabseq . $language->id . '">';
                $langTab .= !empty($language->languageFlag) ?
                        '<img src="' . Config::get('constants.language_url') . $language->languageFlag . '" width="29" height="29" '
                        . 'title="' . $language->languageName . '">' : $language->languageName;
                $langTab .= '</a></li>';
                
            }
            $genTable .= '<div id="lang' . $tabseq . $language->id . '" class="tab-pane fade show ' . (($default_lang == $language->id) ? 'active' : '') . '"'.$viewtab.'>';
            foreach ($inputfielddatas as $inputfielddata) {
                $field = isset($inputfielddata['field']) ? $inputfielddata['field'] : 'text';
                $cssClass = isset($inputfielddata['cssClass']) ? $inputfielddata['cssClass'] : '';
                $placeHolder = isset($inputfielddata['label']) ? ucwords($inputfielddata['label']) : '';
                $name = isset($inputfielddata['name']) ? $inputfielddata['name'] : 'name';
                $required_filed = isset($inputfielddata['required']) ? $inputfielddata['required'] : '';

                $froalaOptions = $froala = 'null';
                if(isset($inputfielddata['froala'])){
                    $froala = 'froala';
                } 
                $errorkey=isset($inputfielddata['errorkey']) ? $inputfielddata['errorkey'] : '';
               
                $error_class=""; 
                $required = "";
                $name_required = '';

                $required_html = '';

                if($language->isSystem == '1'){
                    if(!empty($errorkey)){
                        if($errors->first($errorkey))
                        {
                            $error_class="error";
                        }    
                    }

                    if(!empty($required_filed)){
                        $required = $required_filed;

                        $required_html = '<i class="strick">*</i>';
                    }


                }
                

                $genTable .= '<div class="form-group '.$error_class.'" >';
                $genTable .= isset($inputfielddata['label']) ? '<label>' . $inputfielddata['label'] . $required_html.'</label>' : '';

                //dd(!empty($is_angular));
                if(!empty($is_angular)){

                    if($field=='textarea'){
                        $genTable .= Form::$field($name.'['.$language->id.']', old($name . '[' . $language->id . ']'), ['ng-model'=>$name.'[' . $language->id . ']','class' => 'form-control ' . $cssClass, 'placeholder' => '', $froala=>'' ]); 
                    }
                    else{
                        if($language->id == session('default_lang')){
                            $genTable .= Form::$field($name.'_'.$language->id, old($name . '[' . $language->id . ']'), ['ng-model'=>$name.'['.$language->id.']','class' => 'form-control ' . $cssClass, 'placeholder' => '','required'=>'required']);

                            $genTable .= '<span ng-show="(productform.productName_'.$language->id.'.$touched || productform.$submitted) && productform.productName_'.$language->id.'.$error.required" class="error-msg block">'.
                                    Lang::get('product.required').'</span>';
                        }
                        else{
                            $genTable .= Form::$field($name.'_'.$language->id, old($name . '[' . $language->id . ']'), ['ng-model'=>$name.'['.$language->id.']','class' => 'form-control ' . $cssClass, 'placeholder' => '']);
                        }                             
                    }


                }else {
                  
                    if($field=='textarea'){
                        
                        $genTable .= Form::$field($name.'['.$language->id.']', old($name . '[' . $language->id . ']'), ['ng-model'=>$name.'[' . $language->id . ']','class' => 'form-control ' . $cssClass, 'placeholder' => '',$required, $froala=>'' ]);
                    }
                    else{
                        
                        $genTable .= Form::$field($name.'['.$language->id.']', old($name . '[' . $language->id . ']'), ['ng-model'=>$name.'['.$language->id.']','class' => 'form-control '. $cssClass, 'placeholder' => '',$required]);    
                    }
                }
                
               
                if($language->isSystem == '1'){
                    if(!empty($errors) && !empty($errorkey)){
                        if($errors->first($errorkey)){
                            $genTable .='<p id="name-error" class="red">'.$errors->first($errorkey).'</p>';
                        }
                    }
                }                    
                $genTable .= '</div>';
            }
            $genTable .= '</div>';
        }

        $genTable .= '</div>';
        $langTab .= '</ul>';

        return $langTab . $genTable;
    }

    public static function fieldstabWithLanuageEdit($inputfielddatas, $tabseq = null, $table_field, $table_id, $tableName, $errors=null, $is_angular=null) {
        $fetchField = array();
        foreach ($inputfielddatas as $fieldName) {
            $fetchField[] = $fieldName['name'];
        }
        $fetchField[] = 'lang_id';
        $datas = DB::table($tableName)->select($fetchField)->where([$table_field => $table_id])->get();
        $langdata = array();
        foreach ($datas as $data) {
                foreach ($fetchField as $fieldName) {
                    $langdata[$fieldName][$data->lang_id] = $data->$fieldName;
                }
        }

       $default_lang = session('admin_default_lang');
       
       $languages = Language::where('status', '1')
         ->orderByRaw(DB::raw("FIELD(id, $default_lang) DESC"))
         ->orderBy('id', 'asc')
         ->get();
        $tot_active_lang = count($languages);
        $tabclass = ($tot_active_lang > 1)?'tab-content language-tab':'';

        $langTab = '<ul class="nav nav-tabs lang-nav-tabs">';
        $genTable = '<div class="'.$tabclass.'">';

        foreach ($languages as $language) {
            if($tot_active_lang > 1){
                $langTab .= '<li><a class="' . (($default_lang == $language->id) ? 'active' : '') . ' tablang_'.$tabseq . $language->id.'" data-toggle="tab" href="#lang' . $tabseq . $language->id . '">';
                $langTab .= !empty($language->languageFlag) ?
                        '<img src="' . Config::get('constants.language_url') . $language->languageFlag . '" width="29" height="29" '
                        . 'title="' . $language->languageName . '">' : $language->languageName;
                $langTab .= '</a></li>';
                
            }
            $genTable .= '<div id="lang' . $tabseq . $language->id . '" class="tab-pane fade show ' . (($default_lang == $language->id) ? 'active' : '') . '">';
            foreach ($inputfielddatas as $inputfielddata) {
                $field = isset($inputfielddata['field']) ? $inputfielddata['field'] : 'text';
                $cssClass = isset($inputfielddata['cssClass']) ? $inputfielddata['cssClass'] : '';
                $placeHolder = isset($inputfielddata['label']) ? ucwords($inputfielddata['label']) : '';
                $name = isset($inputfielddata['name']) ? $inputfielddata['name'] : 'name';
                $required_filed = isset($inputfielddata['required']) ? $inputfielddata['required'] : '';
                $editor_required = isset($inputfielddata['editor_required']) ? $inputfielddata['editor_required'] : '';

                $froalaOptions = $froala = 'null';
                if(isset($inputfielddata['froala'])){
                    $froala = 'froala';
                } 
                $errorkey=isset($inputfielddata['errorkey']) ? $inputfielddata['errorkey'] : '';
               

                $error_class=""; 
                $required = "";
                $name_required = '';
                if($language->isSystem == '1'){
                    if(!empty($errorkey)){
                        if($errors->first($errorkey))
                        {
                                $error_class="error";
                        }    
                    }

                    if(!empty($required_filed)){

                        $required = '<i class="strick">*</i>';
                    }
                }
                
                $genTable .= '<div class="form-group '.$error_class.'">';
                $genTable .= isset($inputfielddata['label']) ? '<label>' . $inputfielddata['label'] . $required. '</label>' : '';

                $fld_value = isset($langdata[$name][$language->id])?stripslashes($langdata[$name][$language->id]):'';

                if($is_angular=='angular'){

                    $froala = '';
                    if($field=='textarea'){
                        $froala = 'froala';
                    }

                    $genTable .= Form::$field($name.'['.$language->id.']', old($name . '[' . $language->id . ']', $fld_value), ['ng-model'=>$name.'[' . $language->id . ']', 'ng-init'=>$name.$language->id."='".$fld_value."'", 'class' => "form-control" . $cssClass, 'placeholder'=>'', $froala=>'']);    
                }
                else{

                    $froala = '';
                    if($field=='textarea' && $editor_required != 'N'){
                        $froala = 'froala-editor-apply';
                    }

                    $genTable .= Form::$field($name . '[' . $language->id . ']', old($name . '[' . $language->id . ']', isset($langdata[$name][$language->id]) ? stripslashes($langdata[$name][$language->id]) : ""), ['class' => "form-control $froala" . $cssClass, 'placeholder' => '' ]);
                }

                $errorkey=isset($inputfielddata['errorkey']) ? $inputfielddata['errorkey'] : '';
                if($language->isSystem == '1'){
                    if(!empty($errors) && !empty($errorkey)){
                        if($errors->first($errorkey)){
                            $genTable .='<p id="name-error" class="red">'.$errors->first($errorkey).'</p>';
                        }
                    }
                } 
                
                $genTable .= '</div>';
            }
            $genTable .= '</div>';
        }

        $genTable .= '</div>';
        $langTab .= '</ul>';

        return $langTab . $genTable;
    }

    public static function getCountryDorpDown($country_id=null,$country_id_arr=array()) {
        
        $country_lists = Country::select('id', 'country_isd', 'short_code')->orderBy('country_code','ASC')->get();
        
        $country_str = '';
        foreach($country_lists as $country)
        {            
           $selected = ''; 
           if($country->id == $country_id || in_array($country->id, $country_id_arr)) {
               $selected = 'selected="selected"'; 
           }
            
           $country_str .= '<option isd_code="'.$country->country_isd.'" value="'.$country->id.'" '.$selected.'>'.$country->countryName->country_name.'</option>';
        } 
        //echo '====>'.$country_str;die;
        
        return $country_str;
    }

    public static function getProvinceStateDD($country_id, $province_state='') {
        $province_list = \App\CountryProvinceState::getProvinceList($country_id);
        //dd($province_list);
        $option_str = '';
        $selected_flag = 0;
        if($province_list) {
            foreach ($province_list as $province_details) {
                $selected = '';
                if($province_details->provinceName->province_state_name == $province_state) {
                    $selected = 'selected="selected"';
                    $selected_flag = 1;
                }
                $option_str .= '<option value="'.$province_details->id.'" '.$selected.'>'.$province_details->provinceName->province_state_name.'</option>';
            }
        }
        if($selected_flag === 0) {
            $option_str .= '<option value="" selected="selected">'.$province_state.'</option>';
        }        

        return $option_str;
    }

    /**
     * Dropdown จังหวัดจาก smm_master_provinces
     * กรองเฉพาะจังหวัดที่อยู่ในเขตจัดส่ง (พื้นที่นอกเขตจะไม่แสดงใน dropdown)
     * กรณีแก้ไขที่อยู่: ถ้า province_id ที่บันทึกอยู่นอกเขต ยังคงแสดงใน dropdown
     */
    public static function getSmmProvinceDD($province_id = '') {
        $province_ids_in_zone = \App\SmmDeliveryRegionDetail::getProvinceIdsInZone();
        $province_list = \App\SmmMasterProvince::getList();
        if ($province_ids_in_zone->isNotEmpty()) {
            $province_list = $province_list->whereIn('id', $province_ids_in_zone);
        }
        // กรณีแก้ไข: ถ้า province ที่บันทึกอยู่นอกเขต ยังให้แสดงใน dropdown
        if ($province_id && $province_list->where('id', $province_id)->isEmpty()) {
            $p = \App\SmmMasterProvince::find($province_id);
            if ($p) {
                $province_list = $province_list->prepend($p);
            }
        }
        $option_str = '<option value="">--'.Lang::get('common.select').'--</option>';
        if ($province_list->isNotEmpty()) {
            foreach ($province_list as $p) {
                $selected = ($p->id == $province_id) ? ' selected="selected"' : '';
                $option_str .= '<option value="'.$p->id.'"'.$selected.'>'.$p->name_th.'</option>';
            }
        }
        return $option_str;
    }

    /**
     * Dropdown เขตจาก smm_master_districts
     * กรองเฉพาะเขตที่อยู่ในเขตจัดส่ง (พื้นที่นอกเขตจะไม่แสดงใน dropdown)
     * กรณีแก้ไขที่อยู่: ถ้า district_id ที่บันทึกอยู่นอกเขต ยังคงแสดงใน dropdown
     */
    public static function getSmmCityDistrictDD($province_id, $district_id = '') {
        $district_ids_in_zone = \App\SmmDeliveryRegionDetail::getDistrictIdsInZone($province_id);
        $district_list = \App\SmmMasterDistrict::getByProvinceId($province_id);
        if ($district_ids_in_zone->isNotEmpty()) {
            $district_list = $district_list->whereIn('id', $district_ids_in_zone);
        }
        // กรณีแก้ไข: ถ้า district ที่บันทึกอยู่นอกเขต ยังให้แสดงใน dropdown
        if ($district_id && $district_list->where('id', $district_id)->isEmpty()) {
            $d = \App\SmmMasterDistrict::find($district_id);
            if ($d && $d->province_id == $province_id) {
                $district_list = $district_list->prepend($d);
            }
        }
        $option_str = '<option value="">--'.Lang::get('common.select').'--</option>';
        if ($district_list->isNotEmpty()) {
            foreach ($district_list as $d) {
                $selected = ($d->id == $district_id) ? ' selected="selected"' : '';
                $option_str .= '<option value="'.$d->id.'"'.$selected.'>'.$d->name_th.'</option>';
            }
        }
        return $option_str;
    }

    /**
     * Dropdown ตำบล/แขวงจาก smm_master_sub_districts
     * กรองเฉพาะตำบล/แขวงที่อยู่ในเขตจัดส่ง (พื้นที่นอกเขตจะไม่แสดงใน dropdown)
     * กรณีแก้ไขที่อยู่: ถ้า sub_district_id ที่บันทึกอยู่นอกเขต ยังคงแสดงใน dropdown
     */
    public static function getSmmSubDistrictDD($district_id, $sub_district_id = '') {
        $sub_list = \App\SmmDeliveryRegionDetail::getSubDistrictsInZone($district_id);
        // กรณีแก้ไข: ถ้า sub_district ที่บันทึกอยู่นอกเขต ยังให้แสดงใน dropdown
        if ($sub_district_id && $sub_list->where('id', $sub_district_id)->isEmpty()) {
            $s = \App\SmmMasterSubDistrict::find($sub_district_id);
            if ($s && $s->district_id == $district_id) {
                $sub_list = $sub_list->prepend($s);
            }
        }
        $option_str = '<option value="">--'.Lang::get('common.select').'--</option>';
        if ($sub_list->isNotEmpty()) {
            foreach ($sub_list as $s) {
                $selected = ($s->id == $sub_district_id) ? ' selected="selected"' : '';
                $zip_val = isset($s->zip_code) ? $s->zip_code : '';
                $option_str .= '<option value="'.$s->id.'" data-zip="'.$zip_val.'"'.$selected.'>'.$s->name_th.'</option>';
            }
        }
        return $option_str;
    }

    public static function getProvinceStateNormalDD($country_id, $province_state_id='') {
        //echo '===========>'.$country_id.'===='.$province_state_id;die;
        $province_list = \App\CountryProvinceState::getProvinceList($country_id);
        //dd($province_list);
        $option_str = '<option value="" >--'.Lang::get('common.select').'--</option>';
        if($province_list) {
            foreach ($province_list as $province_details) {
                $selected = '';
                if($province_details->id == $province_state_id) {
                    $selected = 'selected="selected"';
                }
                $option_str .= '<option value="'.$province_details->id.'" '.$selected.'>'.$province_details->provinceName->province_state_name.'</option>';
            }
        }        

        return $option_str;
    }    

    public static function getCityDistrictDD($province_id, $city_district='') {
        $city_list = \App\CountryCityDistrict::getCityList($province_id);
        //dd($province_list);
        $option_str = '';
        $selected_flag = 0;
        if($city_list) {
            foreach ($city_list as $city_details) {
                $selected = '';
                if($city_details->cityName->city_district_name == $city_district) {
                    $selected = 'selected="selected"';
                    $selected_flag = 1;
                }
                $option_str .= '<option value="'.$city_details->id.'" '.$selected.'>'.$city_details->cityName->city_district_name.'</option>';
            }            
        }
        if($selected_flag === 0) {
            $option_str .= '<option value="" selected="selected">'.$city_district.'</option>';
        }  
              
        return $option_str;
    }

    public static function getCityDistrictNormalDD($province_id, $city_district_id='') {
        $city_list = \App\CountryCityDistrict::getCityList($province_id);
        //dd($province_list);
        $option_str = '<option value="" >--'.Lang::get('common.select').'--</option>';
        if($city_list) {
            foreach ($city_list as $city_details) {
                $selected = '';
                if($city_details->id == $city_district_id) {
                    $selected = 'selected="selected"';
                }
                $option_str .= '<option value="'.$city_details->id.'" '.$selected.'>'.$city_details->cityName->city_district_name.'</option>';
            }            
        }  
              
        return $option_str;
    }    

    public static function getSubDistrictDD($district_id, $sub_dist='') {
        $sub_dist_list = \App\CountrySubDistrict::getSubDistList($district_id);
        //dd('dfgdsgdsg',$district_id, $sub_dist);
        $option_str = '';
        $selected_flag = 0;
        if($sub_dist_list) {
            foreach ($sub_dist_list as $sub_dist_details) {
                $selected = '';
                if($sub_dist_details->subDistrictName->sub_district_name == $sub_dist) {
                    $selected = 'selected="selected"';
                    $selected_flag = 1;
                }
                $option_str .= '<option value="'.$sub_dist_details->id.'" '.$selected.'>'.$sub_dist_details->subDistrictName->sub_district_name.'</option>';
            }            
        }
        if($selected_flag === 0) {
            $option_str .= '<option value="" selected="selected">'.$sub_dist.'</option>';
        }        
        return $option_str;
    }
    
   
    /**
    * This helper finction will fetch the contents of newsletter from and return html contents to footer script
    * Added By @Dinesh Kumar Kovid | ***** End ***** | Date : 29/01/2017
    */        

    public static function getBlogCategories(){

        $blogCategoryList = \App\BlogCategory::where('parent_id','0')->with('blogcategorydesc')->get();
        
        $blogCatMenu = "";
        foreach($blogCategoryList as $blog_cat_key => $blogCatDetail){

            $url = getBlogCategoryUrl($blogCatDetail->url);
            
            $blogCatMenu .= "<li><a href='".$url."' data-toggle='tooltip' data-placement='right' title='".$blogCatDetail->blogcategorydesc->category_name."''>".$blogCatDetail->blogcategorydesc->category_name."</a></li>";
        }
        
        return $blogCatMenu;
    }  

    public static function getTimeZoneDorpDown($time_zone=null) {

        $timezones = \App\Timezones::getTimezone();
        
        $timezones_str = '';
        
        foreach($timezones as $values)
        {            
           $selected = ''; 
            
           if($values->timezone == $time_zone) {
              
               $selected = 'selected="selected"'; 
           }
            
           $timezones_str .= '<option value="'.$values->timezone.'" '.$selected.'>'.$values->gmt_offset.' '.$values->timezone.'</option>';
        }
        
        return $timezones_str;
    }    


    public static function getAllBroadcastNotificationCount(){
        $total = \App\Broadcast::where(['is_readed'=>'0','is_removed'=>'0'])->count();
        return $total;
    }

    public static function getAllBroadcastNotification(){
        $data = \App\Broadcast::where(['is_readed'=>'0','is_removed'=>'0'])->get();
        return $data;
    }

    public static function getLatestPopupNotification(){
        $broadcastData = \App\Broadcast::where('open_in_popup','1')->first();
        if(!empty($broadcastData)){
            switch ($broadcastData->type) {
                case 'news':
                    $notifi_type_img = "news-icon.png";
                break;
                case 'plugin':
                    $notifi_type_img = "plugin-icon.png";
                break;
                case 'notice':
                    $notifi_type_img = "notice-icon.png";
                break;
                case 'system':
                    $notifi_type_img = "system_icon.png";
                break;
            }

            $broadcastData->notifi_type_img = Config::get('constants.files_url').'broadcast/'.$notifi_type_img;
        }
        
        return  $broadcastData;
    }

    public static function getGroupName($group_id,$lang_id){
        //echo $group_id; 
        
        $groupData = \App\CustomerGroupDesc::where(['group_id'=>$group_id,'lang_id'=>$lang_id])->first();
        return $groupData->group_name;
    }


    public static function sendDataToNodeServerToCombine($request_params){
        try{

            $nodeServerUrl  = getConfigValue('COMBINE_NODE_SERVRE_URL');
            $request_params['licenseKey'] = getConfigValue('LICENCE_KEY');
            $request_params['id'] = '6685ac9b713d9e55356236a893a5953b364fc804ae';

           
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => $nodeServerUrl,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => json_encode($request_params),
              CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/json"
              ),
            ));

            $response = curl_exec($curl);
            
            // Print LICENCE_KEY
            //dd($response,$request_params, $nodeServerUrl, getConfigValue('LICENCE_KEY'));
            $err = curl_error($curl);
            curl_close($curl);
            $return  = [];
            if ($err) {
                $data = [];
                $returnResponse = ['status'=>'failed','message'=>$err,'data'=>$data];
            } else {
                $returnResponse = json_decode($response,true);
            }
        }
        catch(Exception $e) {
            //echo $e->getMessage(); die;
            $data = [];
            $returnResponse = ['status'=>'failed','message'=>Lang::get('website_management.something_went_wrong'),'data'=>$data];
        }
        return $returnResponse;
    } 
    public static function combineCssJs($file_arr,$typefile,$response_api=null){
        //dd($file_arr,$typefile);

        $version = "?ver=".getConfigValue('CSS_JS_VERSION');
        $newFile = true;
        $public_path = \Config::get('constants.public_path').'/';
        $public_url = \Config::get('constants.public_url');
        $make_str = '';
        //$debug = false;
        if(\Config::get('constants.localmode')==true){
            $debug = true;
        }else{
            $debug = false;
        }
        
        // unMinify CSS and JS to uncomment below Line
        //$debug = false;
        if ($response_api=="0") {
            $debug = true;
        }
        $debug = true;
        if($debug){
            $res_debug = "";
            foreach ($file_arr as $key) {

                $path_file = $public_path.$key.'.'.$typefile;
                $fn =  $key.'.'.$typefile;
                if(is_file($path_file)){
                    
                    if($typefile=="js"){
                        $res_debug .= '<script type="text/javascript" src="'.$public_url.$fn.'?ver='.getConfigValue('CSS_JS_VERSION').'"></script>';
                    }else{
                        $res_debug .= '<link type="text/css" rel="stylesheet" href="'.$public_url.$fn.'?ver='.getConfigValue('CSS_JS_VERSION').'"/>';
                    }
                }
            }
            return $res_debug;
        }

        if(isset($file_arr)&&!empty($file_arr)){
            $file_arr = array_unique($file_arr);
            $filename = implode(',', $file_arr);
            $filename_gen = preg_replace('/\//','_',$filename);
            $filename_gen = md5($filename_gen);
            $res = 'combine/'.$typefile.'/'.$filename_gen.'.'.$typefile;
            $cachename = $public_path.$res;

            if(is_file($cachename)){
                $newFile = false;
                $lastModified = filemtime($cachename);
                foreach ($file_arr as $key) {
                    $fileRead = $public_path.$key.'.'.$typefile;
                    if(is_file($fileRead)){
                        $time = filemtime($fileRead);
                        if( $time > $lastModified){
                            //@unlink($cachename);
                            $newFile = true;
                            break;
                        }
                    }
                }
            }

            if($newFile){
                $css_arr = $js_arr = [];
                foreach ($file_arr as $fkey => $fvalue) {
                    $fileRead = $public_path.$fvalue.'.'.$typefile;
                    if(is_file($fileRead)){
                        if($typefile == 'css')
                            $css_arr[] = ['id'=>++$fkey,'name'=>$fvalue.'.'.$typefile];
                        else
                            $js_arr[] = ['id'=>++$fkey,'name'=>$fvalue.'.'.$typefile];

                    }else{
                        //echo $fvalue.'<br>';
                    }
                }
                
                if(!empty($css_arr) || !empty($js_arr)){
                    
                    $request_params['combine_source_url'] = $public_url;
                    if(!empty($css_arr)){
                        $request_params['css_files'] = $css_arr;
                        $request_params['css_combine_name'] = $filename_gen;
                    }
                    if(!empty($js_arr)){
                        $request_params['js_files'] = $js_arr;
                        $request_params['js_combine_name'] = $filename_gen;
                    }

                    $response = self::sendDataToNodeServerToCombine($request_params);
                    
                    if(isset($response['status']) && $response['status']==200 ){
                        if($typefile == 'css'){
                            $filegeturl = $response['data']['cssUrl'];
                            //dd($filegeturl);
                        }else{
                            $filegeturl = $response['data']['jsUrl'];
                        }

                        if(is_file($cachename)){
                            @unlink($cachename);
                        }

                        if ( copy($filegeturl, $cachename) ) {
                           
                        }
                        
                    }else{
                        return self::combineCssJs($file_arr,$typefile,"0");
                    }
                    
                }
            }
            
            if($typefile=="js"){
                return '<script type="text/javascript" src="'.$public_url.$res.$version.'/" ></script>';
            }else{

                return '<link type="text/css"  rel="stylesheet" href="'.$public_url.$res.$version.'" media="screen"/>';
                
            }
        }

    }

    public static function getCommonCss(){

        $public = 'css/';

        $css_arr[] = $public.'font-awesome';
        $css_arr[] = $public.'bootstrap';
        $css_arr[] = $public.'slick';
        $css_arr[] = $public.'flickity';
        $css_arr[] = $public.'sweetalert.min';
        $css_arr[] = $public.'flatpickr.min';
        $css_arr[] = $public.'magicscroll';
        $css_arr[] = $public.'global';
        $css_arr[] = $public.'style-front';
        
        return Self::combineCssJs($css_arr,'css');
    }

    public static function getCommonJs(){

        $public = 'js/';
        $js_arr[] = $public.'jquery.min';
        $js_arr[] = $public.'bootstrap.bundle';
        $js_arr[] = $public.'bootstrap.min';
        $js_arr[] = $public.'sweetalert.min';
        $js_arr[] = $public.'toastr.min';
        $js_arr[] = $public.'flatpickr.min';
        $js_arr[] = $public.'slick';
        $js_arr[] = $public.'magicscroll';
        $js_arr[] = $public.'flickity.pkgd.min';

        
        //$js_arr[] = $public.'jquery.touchSwipe.min';
        //$js_arr[] = $public.'TweenMax.min';
        //$js_arr[] = $public.'slider3d';
        //$js_arr[] = $public.'sgCustom';
        $js_arr[] = $public.'common';
        

        return Self::combineCssJs($js_arr,'js');
    }

    public static function getUnitOption($selectedval = null) {
        $unit_str = '';
        $units = \App\Unit::getUnits();
        if($units) {
            foreach ($units as $key => $value) {
                $selected = '';
                if($value->id == $selectedval){
                   $selected = 'selected="selected"';
                }
                $unit_str .= '<option value="'.$value->id.'" '.$selected.'>'.$value->unitdesc->unit_name.'</option>';
            }
        }
        return $unit_str;
    }


    // public static function getCatUnitOption($cat_id = null, $selectedval=null) {
    //     $out_str = '';
    //     if(!empty($cat_id)){
    //         $default_lang = session('default_lang');
    //         $sql = DB::table(with(new \App\CategoryUnit)->getTable().' as cu')
    //             ->join(with(new \App\Unit)->getTable().' as u','u.id', '=', 'cu.unit_id')
    //             ->join(with(new \App\UnitDesc)->getTable().' as ud', 
    //                         [ ['u.id', '=', 'ud.unit_id'],
    //                           ['ud.lang_id', '=', DB::raw($default_lang)]
    //                         ]
    //             );

    //         $results =  $sql->select('u.id','ud.unit_name')->where('u.status','1')->where('cu.cat_id', $cat_id)->get(); 
    //         foreach ($results as $key => $value) {
    //             $selected = '';
    //             if($value->id == $selectedval){
    //                $selected = 'selected="selected"';
    //             }
    //             $out_str .= '<option value="'.$value->id.'" '.$selected.'>'.$value->unit_name.'</option>';
    //         }
           
            
    //     }
    //     return $out_str;
    // }

    public static function getParentCatBaseUnitOption($parent_cat_id = null, $selectedval = null) {
        $out_str = '';

        if (!empty($parent_cat_id)) {
            $default_lang = session('default_lang');
            $category = DB::table(with(new \App\Category)->getTable().' as c')
            ->select('c.parent_id')
            ->where('c.id', $parent_cat_id)
            ->first();

            if (!$category || !$category->parent_id) {
            $out_str .= '<option value="" >ไม่มีข้อมูล</option>';
            }

            $parentId = $category->parent_id;

            $sql = DB::table(with(new \App\ParentCatBaseUnit)->getTable().' as pcb')
                ->join(with(new \App\Unit)->getTable().' as u', 'u.id', '=', 'pcb.base_unit_id')
                ->join(with(new \App\UnitDesc)->getTable().' as ud', [
                    ['u.id', '=', 'ud.unit_id'],
                    ['ud.lang_id', '=', DB::raw($default_lang)]
                ]);

            $results = $sql->select('u.id', 'ud.unit_name')
                ->where('u.status', '1')
                ->where('pcb.parent_cat_id', $parentId)
                ->get();

            foreach ($results as $value) {
                $selected = ($value->id == $selectedval) ? 'selected="selected"' : '';
                $out_str .= '<option value="' . $value->id . '" ' . $selected . '>' . $value->unit_name . '</option>';
            }
        }

        return $out_str;
    }


    // public static function getPackagesOptain($selectedval = null) {
    //     $package_str = '';
    //     $packages = \App\Package::getPackages();
    //     if($packages) {
    //         foreach ($packages as $key => $value) {
    //             $selected = '';
    //             if($value->id == $selectedval){
    //                $selected = 'selected="selected"';
    //             }
    //             $package_str .= '<option value="'.$value->id.'" '.$selected.'>'.$value->packagedesc->package_name.'</option>';
    //         }
    //     }
    //     return $package_str;
    // }

    public static function getPackagesOptain($selectedval = null, $parent_cat_id = null) {
        $package_str = '';

        // ดึง parent_id จาก Category
        $category = DB::table(with(new \App\Category)->getTable().' as c')
            ->select('c.parent_id')
            ->where('c.id', $parent_cat_id)
            ->first();

        if (!$category || !$category->parent_id) {
            // ถ้าไม่เจอ category หรือไม่มี parent_id
            return '<option value="">ไม่มีข้อมูล</option>';
        }

        $parentId = $category->parent_id;

        // Query Packages
        $packages = DB::table('parent_cat_package as pcp')
            ->join('package as p', 'p.id', '=', 'pcp.package_id')
            ->select(
                'pcp.id as parent_package_id',
                'pcp.parent_cat_id',
                'pcp.package_id',
                'pcp.status as pcp_status',
                'p.title as package_title',
                'p.status as package_status'
            )
            ->where('pcp.status', '1')
            ->where('p.status', '1')
            ->where('pcp.parent_cat_id', $parentId)
            ->get();

        if ($packages->count() > 0) {
            foreach ($packages as $value) {
                $selected = ($value->package_id == $selectedval) ? 'selected="selected"' : '';
                $package_str .= '<option value="'.$value->package_id.'" '.$selected.'>'.$value->package_title.'</option>';
            }
        } else {
            $package_str .= '<option value="">ไม่มีข้อมูล</option>';
        }

        return $package_str;
}



    public static function getBadgeSize($key=null){

        /*$arr = ['jumbo'=>Lang::get('admin_product.jumbo'),'large'=>Lang::get('admin_product.large'),'medium'=>Lang::get('admin_product.medium'),'small'=>Lang::get('admin_product.small'),'mini'=>Lang::get('admin_product.mini'),'non'=>Lang::get('admin_product.non')];*/
        
        if($key){
            $data_val = \App\MongoSizeGrade::getAllSizeGrade($key);
            return $data_val ? $data_val->name : '';
        }else{
            $arr = \App\MongoSizeGrade::getSize();
            return $arr;
        }
    }

    public static function getBadgeGrade($key=null){
        /*$arr = ['very_good'=>Lang::get('admin_product.very_good'),'good'=>Lang::get('admin_product.good'),'general'=>Lang::get('admin_product.general'),'mix'=>Lang::get('admin_product.mix'),'non'=>Lang::get('admin_product.non')];*/
        
        if($key){
            $data_val = \App\MongoSizeGrade::getAllSizeGrade($key);
            return $data_val ? $data_val->name : '';
        }else{
            $arr = \App\MongoSizeGrade::getGrade();
            return $arr;
        }
    }

    public static function dataTableCss(){
        $css = '<link rel="stylesheet" href="'.Config('constants.admin_css_url').'table/pqgrid.min.css"/>
        <link rel="stylesheet" href="'.Config('constants.admin_css_url').'table/pqgrid.ui.min.css"/>
        <link rel="stylesheet" href="'.Config('constants.admin_css_url').'table/pqgrid.css"/>
        <link rel="stylesheet" href="'.Config('constants.admin_css_url').'table/pqselect.min.css"/>';
        
        return $css;
    }

    public static function dataTableJs(){
        $js = '<script src="'.Config('constants.admin_js_url').'table/pqgrid.min.js"></script>
        <script src="'.Config('constants.admin_js_url').'table/pqselect.min.js"></script>
        <script src="'.Config('constants.admin_js_url').'table/jquery.resize.js"></script>
        <script src="'.Config('constants.admin_js_url').'table/pqtouch.min.js"></script>
        <script src="'.Config('constants.admin_js_url').'table/jszip.min.js"></script>
        <script src="'.Config('constants.admin_js_url').'table/FileSaver.min.js"></script>
        <script src="'.Config('constants.admin_js_url').'table/jqGrid.app.js"></script>';
        
        return $js;
    }

    public static function buyerShipBillTo($orderJson,$add_type, $return_type='N'){
        $orderInfoJson = jsonDecodeArr($orderJson);
        $html='';
      
        if(!empty($orderInfoJson[$add_type]['title'])) {
            $html .= '<p>'.str_replace("​","",$orderInfoJson[$add_type]['title']).'</p>';
        }
        $html .= '<p>'. $orderInfoJson[$add_type]['first_name'].' '.$orderInfoJson[$add_type]['last_name'].'</p>';
        $html .= '<p>'.str_replace("​","",$orderInfoJson[$add_type]['address']).'</p><p>';
        if(!empty($orderInfoJson[$add_type]['road'])) {
            $html .= $orderInfoJson[$add_type]['road'].', ';
        }
        if(!empty($orderInfoJson[$add_type]['sub_district'])) {
            $html .= $orderInfoJson[$add_type]['sub_district'].', ';
        }
        if(!empty($orderInfoJson[$add_type]['district'])) {
            $html .= $orderInfoJson[$add_type]['district'].', ';
        }
        $html .= $orderInfoJson[$add_type]['provice'].', '.$orderInfoJson[$add_type]['zip_code'].'</p><p><a href="tel:0'.$orderInfoJson[$add_type]['ph_number'].'">'.$orderInfoJson[$add_type]['ph_number'].'</a></p>';

        if($return_type == 'Y'){
          return $html;
        }else{
          echo $html;
        }
    }


    public static function buyerShipBillingTo($orderJson,$add_type, $return_type='N'){
        $orderInfoJson = jsonDecodeArr($orderJson);
        $html='';
        if(!empty($orderInfoJson[$add_type]['company_name'])) {
            if(!empty($orderInfoJson[$add_type]['branch'])) {
                $html .= '<p>'.str_replace("​", "", $orderInfoJson[$add_type]['company_name']) . " สาขา " . $orderInfoJson[$add_type]['branch'] . '</p>';
            }else{
                $html .= '<p>'.str_replace("​", "", $orderInfoJson[$add_type]['company_name']). '</p>';
            }
            $html .= '<p>'.str_replace("​","",$orderInfoJson[$add_type]['company_address']).'</p><p>';
            if(!empty($orderInfoJson[$add_type]['tax_id'])) {
                $html .= '<p> TAX ID: '. $orderInfoJson[$add_type]['tax_id'].'</p>';
            }
        }else{
            $html .= '<p>'. $orderInfoJson[$add_type]['first_name'].' '.$orderInfoJson[$add_type]['last_name'].'</p>';
            $html .= '<p>'.str_replace("​","",$orderInfoJson[$add_type]['address']).'</p><p>';
            if(!empty($orderInfoJson[$add_type]['road'])) {
                $html .= $orderInfoJson[$add_type]['road'].', ';
            }
            if(!empty($orderInfoJson[$add_type]['sub_district'])) {
                $html .= $orderInfoJson[$add_type]['sub_district'].', ';
            }
            if(!empty($orderInfoJson[$add_type]['district'])) {
                $html .= $orderInfoJson[$add_type]['district'].', ';
            }
            $html .= $orderInfoJson[$add_type]['provice'].', '.$orderInfoJson[$add_type]['zip_code'].'</p><p><a href="tel:0'.$orderInfoJson[$add_type]['ph_number'].'">'.$orderInfoJson[$add_type]['ph_number'].'</a></p>';
            
        }

        
        
        if($return_type == 'Y'){
          return $html;
        }else{
          echo $html;
        }
    }

    public static function centerAddress($center_json){
        $pickup_center_address = jsonDecodeArr($center_json);
        $html = '';
        if($pickup_center_address){
            $name = $pickup_center_address['name']??"";
            $location = $pickup_center_address['location']??"";
            $contact = $pickup_center_address['contact']??"";
            $html = '<p>'.$name.'</p><address>'.$location.'<br><a href="tel:'.$contact.'">'.$contact.'</a></address>';
        }
        return $html;
    }

    // public static function storeAddress($store_json){
    //     $shop_address = jsonDecodeArr($store_json);
    //     foreach ($shop_address as $value) {
    //         if(is_array($value)){
    //             $shop_address = $shop_address;
    //         }else{
    //            $shop_address = [$shop_address]; 
    //         }
    //         break;
    //     }
    //     $html = '';
    //     if($shop_address){
    //         foreach ($shop_address as $key => $val) {
    //             $shopname = $val['shop_name'][session('default_lang')]??'';
    //             $html .= '<p>'.$shopname.'</p><address>'.$val['panel_no'].' '.$val['market'].'</address>';
    //         }
    //     }
    //     return $html;
    // }

    public static function storeAddress($store_json){
        $shop_address = jsonDecodeArr($store_json);
        foreach ($shop_address as $value) {
            if(is_array($value)){
                $shop_address = $shop_address;
            }else{
               $shop_address = [$shop_address];
            }
            break;
        }
        $html = '';
        if($shop_address){
            foreach ($shop_address as $key => $val) {
                $ph_number = $val['ph_number']??'';
                $shopname = $val['shop_name'][session('default_lang')]??'';
                $html .= '<p><span class="label">'.Lang::get("checkout.shop_name").' : </span>'.$shopname.'</p><address><span class="label">'.Lang::get("checkout.panel_no").' : </span> '.$val['panel_no'].' '.$val['market'].'<br><span class="label">'.Lang::get("checkout.contact").' : </span> '.$ph_number.'</address>';
            }
        }
        return $html;
    }

    public static function orderProductDetailinTable($orderInfo,$ordDetailJson){
        $promotion = '';
        
        $html = '
            <table width="100%" style="border-collapse: collapse;">
               ';
        
        
        $html .='<thead>
                  <tr>
                     <th colspan="2" style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;text-align: left; font-size: 12px; font-weight: bold; padding: 10px 0px;">'.Lang::get("checkout.product").'</th>
                     <th style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;text-align: center; font-size: 12px; font-weight: bold; padding: 10px 0px;">'.Lang::get("checkout.price").'</th>
                     <th style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;text-align: center; font-size: 12px; font-weight: bold; padding: 10px 0px; ">'.Lang::get("checkout.qty").'</th>
                     <th style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;text-align: center; font-size: 12px; font-weight: bold; padding: 10px 0px;">'.Lang::get("checkout.row_total").'</th>
                  </tr>
               </thead><tbody>';
        
        $html .= '';
        if($orderInfo){
            foreach($orderInfo->getOrderDetail as $key => $orderDetailRes){
                //dd($orderDetailRes,$orderInfo);
                $prd_name = !empty($ordDetailJson[$orderDetailRes->id]['name'][session('default_lang')]) ? $ordDetailJson[$orderDetailRes->id]['name'][session('default_lang')]:'';
                $html .='<tr style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border-bottom: solid 1px #cccccc;">
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: left; font-size: 12px; font-weight: normal; padding: 10px 0px; color: #000;" valign="top">
                            <div style="display: inline-block;vertical-align: top;margin-right: 10px;">
                             <a style="text-decoration: none;" href="'.getProductUrl($orderDetailRes->url).'"><img width="60" src="'.getProductImageUrl($ordDetailJson[$orderDetailRes->id]['thumbnail_image'], '',$ordDetailJson[$orderDetailRes->id],'product').'" alt=""></a>
                            </div>
                     </td>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: left; font-size: 12px; font-weight: normal; padding: 10px 0px; color: #000;" valign="top">
                            <div style="display: inline-block;vertical-align: top;">
                            <div style="margin: 0;font-weight: bold;">
                             <a style="text-decoration:none; color: #000;" href="'.getProductUrl($orderDetailRes->url).'">'.$prd_name.'</a>
                             </div>
                             <span>'.$ordDetailJson[$orderDetailRes->id]['sku'].'</span></div>
                     </td>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 12px; text-align: center; vertical-align: top; margin: 0; padding: 10px 0px;border:none;" valign="top">'.formatVal($orderDetailRes->unit_price,$orderInfo->currency_id,$orderInfo->getCurrency).'</td>
                     <td  style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 12px; text-align: center; vertical-align: top; margin: 0; padding: 10px 0px;border:none;" valign="top">'.$orderDetailRes->quantity.'</td>
                     <td  style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box; font-size: 12px; text-align: center; vertical-align: top; margin: 0; padding: 10px 0px;border:none;" valign="top">'.formatVal($orderDetailRes->total_final_price,$orderInfo->currency_id,$orderInfo->getCurrency).'</td>
                  </tr>';
            }
        }
        $html .='</tbody></table>';
        $html .='<table style="padding: 15px 0;" width="100%">
               <tbody>
                  <tr>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: left; font-size: 12px; font-weight: bold; padding: 5px 0;">'.Lang::get('checkout.total_units').'</td>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: center; font-size: 12px; font-weight: normal; padding: 5px 0;">&nbsp; </td>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: center; font-size: 12px; font-weight: normal; padding: 5px 0;">&nbsp; </td>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: center; font-size: 12px; font-weight: normal; padding: 5px 0;">&nbsp; </td>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: right; font-size: 12px; font-weight: normal; padding:5px 0;">'.$orderInfo->ttl_unit.'</td>
                  </tr>
                  <tr>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: left; font-size: 12px; font-weight: normal; padding: 5px 0px;">'.Lang::get('checkout.item_total').'</td>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: center; font-size: 12px; font-weight: normal; padding: 5px 0px;">&nbsp; </td>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: center; font-size: 12px; font-weight: normal; padding: 5px 0px;">&nbsp; </td>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: center; font-size: 12px; font-weight: normal; padding: 5px 0px;">&nbsp; </td>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: right; font-size: 12px; font-weight: normal; padding:5px 0px;">'.formatVal($orderInfo->total_core_cost,$orderInfo->currency_id,$orderInfo->getCurrency).'</td>
                  </tr>';
            
            
            $html .='<tr>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: left; font-size: 12px; font-weight: normal; padding: 5px 0px;">'.Lang::get('checkout.shipping_cost').'</td>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: center; font-size: 12px; font-weight: normal; padding: 5px 0px;">&nbsp; </td>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: center; font-size: 12px; font-weight: normal; padding: 5px 0px;">&nbsp; </td>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: center; font-size: 12px; font-weight: normal; padding: 5px 0px;">&nbsp; </td>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: right; font-size: 12px; font-weight: normal; padding: 5px 0px;">'.formatVal($orderInfo->total_shipping_cost,$orderInfo->currency_id,$orderInfo->getCurrency).'</td>
                    </tr>';
            
            $html .='<tr>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: left; font-size: 12px; padding: 5px 0px;font-weight: bold;">'.Lang::get('checkout.total').'</td>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: center; font-size: 12px; font-weight: normal; padding: 5px 0px;">&nbsp; </td>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: center; font-size: 12px; font-weight: normal; padding: 5px 0px;">&nbsp; </td>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: center; font-size: 12px; font-weight: normal; padding: 5px 0px;">&nbsp; </td>
                     <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;text-align: right; font-size: 12px; font-weight: normal; padding: 5px 0px;font-weight: bold;">'.formatVal($orderInfo->total_final_price,$orderInfo->currency_id,$orderInfo->getCurrency).'</td>
                    </tr>';
        $html .='</tbody>
            </table>';
        
        return $html;
    }
    
    public static function orderProductDetailinText($orderInfo,$ordDetailJson){
        $promotion = '';
        
                
        $html = '';
        if($orderInfo){
            foreach($orderInfo->getOrderDetail as $key => $orderDetailRes){
                $prd_name = !empty($ordDetailJson[$orderDetailRes->id]['name'][session('default_lang')]) ? $ordDetailJson[$orderDetailRes->id]['name'][session('default_lang')]:'';
                                  
                $html .=Lang::get("checkout.product").':<a href="'.getProductUrl($orderDetailRes->url).'">'.$prd_name.'</a>'."\n"
                        .$ordDetailJson[$orderDetailRes->id]['sku'].' '.Self::getOrderAttributeDetails($ordDetailJson[$orderDetailRes->id])."\n"
                        .Lang::get("checkout.price").':'.formatVal($orderDetailRes->unit_price,$orderInfo->currency_id,$orderInfo->getCurrency)."\n"
                        .Lang::get("checkout.qty").':'.$orderDetailRes->quantity."\n"
                        .Lang::get("checkout.row_total").':'.formatVal($orderDetailRes->total_final_price,$orderInfo->currency_id,$orderInfo->getCurrency)."\n";  
            }
        }
        $html .=Lang::get('checkout.total_units').':'.$orderInfo->ttl_unit."\n".Lang::get('checkout.item_total').':'.formatVal($orderInfo->total_core_cost,$orderInfo->currency_id,$orderInfo->getCurrency)."\n";
            
            if($orderInfo->total_promotion_discount){
                $html .=Lang::get('order.discount');
   
                        if($promotion) {
                            $html .='- '.$promotion;
                        } 
                        
                $html.=':-'.formatVal($orderInfo->total_promotion_discount,$orderInfo->currency_id,$orderInfo->getCurrency)."\n";        
                
                $subtotal = $orderInfo->total_core_cost-($orderInfo->total_promotion_discount);
                
                $html.=Lang::get('checkout.sub_total').':'.($subtotal>0?formatVal($subtotal,$orderInfo->currency_id,$orderInfo->getCurrency):'0.00')."\n";
                
            }
            
            $html .=Lang::get('checkout.shipping_cost').':'.formatVal($orderInfo->total_shipping_cost,$orderInfo->currency_id,$orderInfo->getCurrency)."\n";
            $html .=Lang::get('checkout.total').':'.formatVal($orderInfo->total_final_price,$orderInfo->currency_id,$orderInfo->getCurrency)."\n";
                    
        return $html;
    }

    public static function getOrderUserInfo($orderInfo, $return_type='N'){

        $user_str = '<span class="name-detail">'.Lang::get('order.name').' : '.$orderInfo->user_name.' </span>
            <span class="name-detail">'.Lang::get('order.email').' : '.$orderInfo->user_email .'</span>';

        if($return_type == 'Y'){
          return $user_str;
        }else{
          echo $user_str;
        }
    }
    
    public static function getOrderUserInfoText($orderInfo, $return_type='N'){

        $user_str = Lang::get('order.name').':'.$orderInfo->user_name."\n".Lang::get('order.email').':'.$orderInfo->user_email ."\n";

        if($return_type == 'Y'){
          return $user_str;
        }else{
          echo $user_str;
        }
    }
    public static function buyerShipTo($orderInfoJson, $return_type='N'){

        $html = '';
        if(!empty($orderInfoJson['shipping_address'])) {
            
            $html = '<p style="margin: 0 0 3px;">'. $orderInfoJson['shipping_address']['first_name'].' '.$orderInfoJson['shipping_address']['last_name'].'</p>';
            

            if(isset($orderInfoJson['shipping_address']['address']) && !empty($orderInfoJson['shipping_address']['address'])) {
                $html .= $orderInfoJson['shipping_address']['address'].', ';
            }

            if(isset($orderInfoJson['shipping_address']['address_no']) && !empty($orderInfoJson['shipping_address']['address_no'])) {
                $html .= $orderInfoJson['shipping_address']['address_no'].', ';
            }

            if(isset($orderInfoJson['shipping_address']['building_name']) && !empty($orderInfoJson['shipping_address']['building_name'])) {
                $html .= $orderInfoJson['shipping_address']['building_name'].'. ';
            }

            if(isset($orderInfoJson['shipping_address']['soi']) && !empty($orderInfoJson['shipping_address']['soi'])) {
                $html .= $orderInfoJson['shipping_address']['soi'].', ';
            }

            if(isset($orderInfoJson['shipping_address']['road']) && !empty($orderInfoJson['shipping_address']['road'])) {
                $html .= $orderInfoJson['shipping_address']['road'].', ';
            }

            if(!empty($orderInfoJson['shipping_address']['sub_district'])) {
                $html .= $orderInfoJson['shipping_address']['sub_district'].', ';
            }
            
            if(!empty($orderInfoJson['shipping_address']['district'])) {
                $html .= $orderInfoJson['shipping_address']['district'].', ';
            } 
            //dd($orderInfoJson);       
            $html .= $orderInfoJson['shipping_address']['provice'].', '.$orderInfoJson['shipping_address']['zip_code'].'</p><p style="margin: 0 0 3px;"><a href="tel:0'.$orderInfoJson['shipping_address']['ph_number'].'">+'.$orderInfoJson['shipping_address']['ph_number'].'</a></p><p style="margin: 0 0 3px;"></p>';
        }

        if($return_type == 'Y'){
          return $html;
        }else{
          echo $html;
        }
    }

    public static function buyerBillTo($orderInfoJson, $return_type='N'){
        $html = '';
        if(!empty($orderInfoJson['billing_address'])) {
            $html = '<p style="margin: 0 0 3px;">'. $orderInfoJson['billing_address']['first_name'].' '.$orderInfoJson['billing_address']['last_name'].'</p>';

            if(isset($orderInfoJson['tax_profile']) && !empty($orderInfoJson['tax_profile'])) {
                $html .='<p style="margin: 0 0 3px;">';
                if($orderInfoJson['tax_profile']['tax_id']){
                    $html.= Lang::get('checkout.tax_id').' : '.$orderInfoJson['tax_profile']['tax_id'];
                }else{
                    $html.= Lang::get('checkout.citizen_id').' : '.$orderInfoJson['tax_profile']['citizen_id'];
                }
                $html .='</p>';
                $html .= '<p style="margin: 0 0 3px;">'. $orderInfoJson['tax_profile']['title'].'</p>';
                if($orderInfoJson['tax_profile']['branch_name']){
                    $html .= '<p style="margin: 0 0 3px;">'. $orderInfoJson['tax_profile']['branch_name'].'</p>';
                }
            }
            
            if(isset($orderInfoJson['billing_address']['address']) && !empty($orderInfoJson['billing_address']['address'])) {
                $html .= $orderInfoJson['billing_address']['address'].', ';
            }

            if(isset($orderInfoJson['billing_address']['address_no']) && !empty($orderInfoJson['billing_address']['address_no'])) {
                $html .= $orderInfoJson['billing_address']['address_no'].', ';
            }

            if(isset($orderInfoJson['billing_address']['building_name']) && !empty($orderInfoJson['billing_address']['building_name'])) {
                $html .= $orderInfoJson['billing_address']['building_name'].'. ';
            }

            if(isset($orderInfoJson['billing_address']['soi']) && !empty($orderInfoJson['billing_address']['soi'])) {
                $html .= $orderInfoJson['billing_address']['soi'].', ';
            }

            if(isset($orderInfoJson['billing_address']['road']) && !empty($orderInfoJson['billing_address']['road'])) {
                $html .= $orderInfoJson['billing_address']['road'].', ';
            }
            
            if(!empty($orderInfoJson['billing_address']['sub_district'])) {
                $html .= $orderInfoJson['billing_address']['sub_district'].', ';
            }
            if(!empty($orderInfoJson['billing_address']['district'])) {
                $html .= $orderInfoJson['billing_address']['district'].', ';
            }
            $html .= $orderInfoJson['billing_address']['provice'].','.$orderInfoJson['billing_address']['zip_code'].'</p><p style="margin: 0 0 3px;"><a href="tel:0'.$orderInfoJson['billing_address']['ph_number'].'">+'.$orderInfoJson['billing_address']['ph_number'].'</a></p>';
        }

        if($return_type == 'Y'){
          return $html;
        }else{
          echo $html;
        }
    }
    public static function getOrderAttributeDetails($orderAttribute){
        $html = '';
        //dd($orderAttribute);
        if(isset($orderAttribute['attributeDetail'])){
            foreach ($orderAttribute['attributeDetail'] as $attrDet) {
                $attribute_name = isset($attrDet['attribute_name'][session('default_lang')])?$attrDet['attribute_name'][session('default_lang')]:'';
                $attribute_value_name = isset($attrDet['attribute_value_name'][session('default_lang')])?$attrDet['attribute_value_name'][session('default_lang')]:'';
                if(isset($attrDet['attribute_type'])&&$attrDet['attribute_type'] == 2){

                    $html .= "<div class='size-color-row' style='font-size: 12px;margin: 0 0 3px 0'>";
                    $html .=$attribute_name.' ';
                    $html .='<label class="shop-item-size skyblue" style="font-size: 12px;margin: 0 0 3px 0">';
                    if($attrDet['front_input']=='text' || $attrDet['front_input']=='textarea'){
                        $html.= $attrDet['attribute_value'].' ';
                    }elseif($attrDet['front_input']=='browse_file'){
                        $html .=' <a style="text-decoration: none;color:#000;font-size: 12px;" href="'.Config::get('constants.cart_option_url').$attrDet['attribute_value'].'" target="_blank">Image</a> ';
                    }else{
                        $html.=$attribute_value_name.' ';
                    }
                    $html.='</label>';
                    $html .= "</div>";

                }else{

                    $html .= "<div class='size-color-row' style='font-size: 12px;margin: 0 0 3px 0'>";

                    $html .= $attribute_name;
                    if(isset($attrDet['color_code_image']) && !empty($attrDet['color_code_image'])){
                        
                        if($attrDet['color_code_image']['color_image'] != ''){
                            $html .= '<img src= "'.attrValImgUrl($attrDet['color_code_image']['color_image']).'" width="20" height="20" style="max-width:20px;">';
                        }elseif($attrDet['color_code_image']['color_code'] != ''){
                            $html .='<span style="background:'.$attrDet['color_code_image']['color_code'].'; display: inline-block; margin-left:5px;max-width:18px;width: 18px;height:18px;max-height: 18px;vertical-align: middle;">&nbsp;&nbsp;&nbsp;&nbsp;</span>';
                        }else{
                            $html .='<label class="shop-item-size skyblue">'.$attribute_value_name.'
                            </label>';
                        }
                    }else{
                            if(isset($attrDet['attribute_value_name'][session('default_lang')])){
                                $html .='<label class="shop-item-size skyblue"> : '.$attrDet['attribute_value_name'][session('default_lang')].'
                            </label>';
                            }
                    }

                   $html .= "</div>";
                }
            }
        }
        return $html;
    }
    public static function getZipCodeDD($district_id, $zip_code='') {
        //echo '====>'.$district_id.'==='.$zip_code;die;
        $zip_code_list = \App\CountryCityDistrictZip::where('district_id', $district_id)->get();
        $option_str = '';
        $selected_flag = 0;
        if($zip_code_list) {
            foreach ($zip_code_list as $detail) {
                $selected = '';
                if($detail->zip == $zip_code) {
                    $selected = 'selected="selected"';
                    $selected_flag = 1;
                }
                $option_str .= '<option value="'.$detail->zip.'" '.$selected.'>'.$detail->zip.'</option>';
            }            
        }
        if($selected_flag === 0) {
            $option_str .= '<option value="" selected="selected">'.$zip_code.'</option>';
        }        
        return $option_str;
    }

    /**
     * Get Beam payment method name based on order's payment option
     */
    public static function getBeamPaymentMethodName($orderInfo) {
        if (!$orderInfo || !$orderInfo->payment_option_id) {
            return 'Beam';
        }
        
        $paymentOption = \App\PaymentOption::find($orderInfo->payment_option_id);
        
        if ($paymentOption && $paymentOption->slug) {
            switch ($paymentOption->slug) {
                case 'beam-qr':
                case 'beam-qrthb':
                    return 'QR Code';
                case 'beam-credit':
                case 'beam-creditcard':
                    return 'Credit Card';
                case 'beam-banking':
                case 'beam-internetbanking':
                    return 'Mobile Banking';
                case 'beam-ewallet':
                    return 'e-Wallet';
                case 'beam':
                default:
                    return 'Beam Payment';
            }
        }
        
        return 'Beam';
    }

    /**
     * Format payment method display name based on payment slug
     * Handles Beam payment types and other payment methods
     */
    public static function formatPaymentMethodName($paymentSlug, $paymentMethodJson = null, $langCode = null)
    {
       
        $lowerPaymentSlug = strtolower($paymentSlug);
        switch ($lowerPaymentSlug) {
            case 'direct_transfer':
                $name = 'โอนตรง';
                break;
            case 'kbank':
                $name = 'QR พร้อมเพย์';
                break;
            case 'payplus':
                $name = 'KBank/PayPlus';
                break;
            case 'credit_acc1':
                $name = 'ลูกค้าเครดิต 1 วัน';
                break;
            case 'credit_acc7':
                $name = 'ลูกค้าเครดิต 7 วัน';
                break;
            case 'beam-qr':
                $name = 'โอนตรง';
                break;
            case 'beam-credit':
                $name = 'บัตรเครดิต';
                break;
            case 'beam-banking':
                $name = 'Mobile Banking';
                break;
            case 'beam-ewallet':
                $name = 'E-Wallet';
                break;
            default:
                $name = str_replace('_', ' ', $paymentSlug);
                break;
        }

        return $name ?: strtoupper($paymentSlug);
    }


    public static function getshippingAddressCount(){

     $today = Carbon::today('Asia/Bangkok');

    $count = \App\ExportShippingAddress::where('status', '1')
        ->whereDate('created_at', $today)
        ->count();

        return $count;

    }

}