<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\GeneralFunctions;
use App\Helpers\CustomHelpers;
use App\Helpers\EmailHelpers;

use App\Menu;
use App\MenusPermission;
use Auth;
use File;
use App\Currency;
use Session;
use Config;
use App\Logactivity;
use App\Product;
use Lang;
use Exception;

class MarketPlace extends Controller {
    
    public function checkUrlPermission($slug) {        
        
        $menus = Menu::where([['status', '=', '1'],['slug', '=', $slug]])->first();
       
        if(!empty($menus)){

            //dd($menus, session('menu_permision_arr'));

            if(!empty(session('menu_permision_arr')) && in_array($menus->id, session('menu_permision_arr'))) {
                 return true;
            }
            else {
                return Redirect::to(action('Admin\AdminHomeController@index'))->send()->with('errorMsg', 'Permission Denied!');
            }            
        }
        else {
            return Redirect::to(action('Admin\AdminHomeController@index'))->send()->with('errorMsg', 'Permission Denied!');
        }        
    }

    public function checkMenuPermission($slug) {        
        
        $permission = false;
        $menus = \App\Menu::where([['status', '=', '1'],['slug', '=', $slug]])->first();
        if(!empty($menus)){
            if(in_array($menus->id, session('menu_permision_arr'))) {
                $permission = true;
            }           
        }
        return $permission;
    }        
    
    public function getMenuPermisionId() {    // called from LoginController

        if(Auth::guard('admin_user')->user()->admin_level == -1) {
            $role_permisions = Menu::select('id as menu_id')->where([['status', '=', '1']])->get();
        }
        else {
            $role_id=Auth::guard('admin_user')->user()->role_id;      
            $role_permisions = MenusPermission::select('menu_id')->where('role_id', '=', $role_id)->get();
        }
        
        $menu_permision_arr = array();
        foreach($role_permisions as $role_permision){
            
            $menu_permision_arr[] = $role_permision->menu_id;
        } 
        
        session(['menu_permision_arr'=>$menu_permision_arr]);
        
        //echo '<pre>';print_r(session('menu_permision_arr'));die;
    } 

    protected function getDefaultLangId() {
        
         $default_lang = \App\Language::getDefaultLanguge();
         session(['default_lang' => $default_lang->id]);
         //echo '==>'.session('default_lang');die;
    }

    function getConfiguration($type) {
   
        $conf_lists = \App\SystemConfig::getSystemConfig($type); 
        $conf_lists = $conf_lists->toArray();
        foreach($conf_lists as $val) {            
            $config_arr[$val['system_name']] = $val['system_val'];
        }
        //echo '<pre>';print_r($config_arr);die;

        return $config_arr;
    }

    protected function getAdminDefaultLangId() {
        
         $default_lang = \App\Language::getDefaultLanguge();
         
         session(['admin_default_lang' => $default_lang->id]);
         //echo '==>'.session('default_lang');die;
    }    

    protected function getDefaultTimeZone() {

        $config_time_zone = $this->systemConfig('TIMEZONE');
        $time_zone = \App\Timezones::getDefaultTimezoneDetail($config_time_zone);
        session(['default_time_zone' => $config_time_zone]);
        session(['default_time_zone_label' => $time_zone->gmt_offset.' '.$time_zone->timezone]);
    }

    protected function setUserTimeZone() {

        if(!empty(Auth::user()->time_zone)) {
            session(['default_time_zone' => Auth::user()->time_zone]);
        }else {
            $config_time_zone = $this->systemConfig('TIMEZONE');
            session(['default_time_zone' => $config_time_zone]);
        }
    }

    protected function getDefaultTheme() {

        $default_theme = $this->systemConfig('DEFAULT_THEME');
        session(['default_theme' => 'theme.'.$default_theme]);
    }    

    public function setLastLogin() {
        \App\AdminUser::where(['id'=>Auth::guard('admin_user')->user()->id])->update(['last_login'=>date('Y-m-d H:i:s')]);
    }             

    public function uploadFileCustom($files, $seq=null) {
      // dd($files, $seq);
        if(!empty($files)){
            
            if(!is_dir($files['path'])) {               
                mkdir($files['path'], 0777, true);               
            } 

            if(isset($files['file_name'])) {
                $file_name = $files['file_name'];
            }
            else {
                $ext = pathinfo($files['file']->getClientOriginalName(), PATHINFO_EXTENSION);
                $file_name = uniqid().$seq.'.'.$ext;
            } 

            if(isset($files['height']) && isset($files['width'])) {
                $files['path'] .= '/'.$file_name;
                if(!empty($files['original_path'])) {

                    if(!is_dir($files['original_path'])) {               
                        mkdir($files['original_path'], 0777, true);               
                    } 

                    $files['file']->move($files['original_path'], $file_name); // upload original image not resize

                    //Image::make($files['original_path'].'/'.$file_name)->resize($files['width'], $files['height'])->save($files['path']);   // resize and upload image

                    Image::make($files['original_path'].'/'.$file_name)->fit($files['width'], $files['height'], function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })->save($files['path']);   // resize and upload image

                }
                else {
                    //Image::make($files['file']->getRealPath())->resize($files['width'], $files['height'])->save($files['path']); // resize and upload image
                    Image::make($files['file']->getRealPath())->fit($files['width'], $files['height'], function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    })->save($files['path']);   // resize and upload image                    
                }
            }
            else {
                $sizeImage = $files['file']->getSize();
                if($sizeImage > 2048){
                    if(!is_dir($files['path'])) {               
                            mkdir($files['path'], 0777, true);               
                    } 
                    $width = Image::make($files['file']->getRealPath())->width(); 
                    $height = Image::make($files['file']->getRealPath())->height();
                    $percent = .5;
                    $newWidth  = floor($width*$percent);
                    $newHeight = floor($height*$percent);
                    $newWidth = (int) $newWidth;
                    $newHeight = (int) $newHeight;
                    if(!empty($newWidth) && !empty($newHeight)){
                        Image::make($files['file']->getRealPath())->fit($newWidth, $newHeight, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        })->save($files['path'].'/'.$file_name); 
                    }    
                }else{
                    $files['file']->move($files['path'], $file_name);  // upload original image not resize

                }
            }
            
            return $file_name;
        }
    }

    public function uploadFileRunTime($files, $type=null) {
        
        if(!empty($files)){
            if(!$type){
                if(!is_dir($files['path'])) {               
                    mkdir($files['path'], 0777, true);
                    //chmod($files['path'], 0777);               
                }
            }
             
            if(isset($files['width'])) {
                $file_name = $files['file_name'];
                $files['path'] .= '/'.$file_name;
                
                if(!empty($files['original_path'])) {
                    
                    $imagename_with_path = $files['original_path'].'/'.$file_name;
                    
                    $img_instance = Image::make($imagename_with_path);
                    $height = $files['height'];
                    $width = $files['width'];

                    if(empty($height)){
                        //height is given
                        $img_instance->resize(intval($width), null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    }
                    else{
                        //height  and width is given
                        $img_instance->fit($width, $height, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });   
                    }
                    if($type){
                        return $img_instance->response();
                    }
                    $img_instance->save($files['path']);
                } 
                return $file_name;
            }
        }
    }

    public function fileDelete($file_path,$get_response=false) {
            if($get_response){
                try{
                    $response = File::delete($file_path);
                    return ['status'=>$response];    
                }catch(Exception $ex){
                    return ['status'=>false];
                }
                
            }else{
                @File::delete($file_path);
                return 'success';    
            }
            
            
    }  

    public function base64UploadImage($file, $path, $image_name){ 

        if(!is_dir($path)) {               
            mkdir($path, 0777, true);               
        }

        $image_parts = explode(";base64,", $file);
        $image = base64_decode($image_parts[1]);
        $response =file_put_contents($path.'/'.$image_name, $image);        
        return $response;        
    }

    public function uploadImage($image_name, $file, $path){
        
        return $file->move($path,$image_name);
    }


    public function uploadImportImage($output_folder_path, $imagename_with_path, $imagename){
        echo "<br/>".$output_folder_path;
        echo "<br/>".$imagename_with_path;
        $img_instance = Image::make($imagename_with_path);
        $response = $img_instance->save($output_folder_path.$imagename);
        if($response){
            return true;
        }else{
            return false;
        }
        
    }


    //for resizing images
    public function resizeAndSave($width, $height, $output_folder, $imagename_with_path,$type='product'){
        
        $img_instance = Image::make($imagename_with_path);
        if($type=='blog'){
            $product_image_path = Config::get('constants.blog_path');
        }else{
            $product_image_path = Config::get('constants.product_path');
        }
        
        $basename = $img_instance->basename;
        if(is_null($height)){
            //height is given
            $img_instance->resize(intval($width), null, function ($constraint) {
                $constraint->aspectRatio();
            });
        }
        else{
            //height  and width is given
            $img_instance->fit($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });   
        }

        if(!is_dir($product_image_path."/". $output_folder)) {               
            mkdir($product_image_path."/". $output_folder, 0777, true);               
        } 


        $img_instance->save($product_image_path."/". $output_folder.'/'.$basename);

        return $basename;
    }

    public function checkDirectoryExists($folder_name,$dir_path){
        $user_id = Auth::id();                
        $complete_path = $dir_path."/".$folder_name;  
        $res = File::isDirectory($complete_path) ;      
        if(!$res){
            $response = File::makeDirectory($complete_path,0777,true);    
        }
        else{
            $response = true;
        }
        return $response;        
    }

    public function systemConfig($system_name=null) {
        $system_val = '';
        if(!empty($system_name)){ 
            $system = \App\SystemConfig::select('system_val')->where('system_name','=', $system_name)->first();
            $system_val = $system->system_val;
        }  
        return $system_val;         
    } 

    public function addslashes($content)
    {
        if (is_array($content)) {
            return array_map(function ($item) {
                return addslashes($item);
            }, $content);
        }

        return addslashes((string) $content);
    }


    public function blogcreateTree(&$list, $parent){

        $tree = array();
        foreach ($parent as $k=>$l){
            $l['checked'] = false;
            $l['name'] = $l['blogcategorydesc']['category_name'];
            $l['children'] = [];
            if(isset($list[$l['id']])){
                $l['children'] = $this->blogcreateTree($list, $list[$l['id']]);

            }
            $tree[] = $l;
        } 
        return $tree;
    }

    public function createTree(&$list, $parent){

        $tree = array();
        foreach ($parent as $k=>$l){
            $l['checked'] = false;
            if(isset($l['categorydesc'])){
                $l['name'] = $l['categorydesc']['category_name'];    
            }            
            if(isset($l['url']) && !empty($l['url'])){
                $l['url'] = getCategoryUrl($l['url']);
            }            
            $l['children'] = [];
            if(isset($list[$l['id']])){
                $l['children'] = $this->createTree($list, $list[$l['id']]);

            }
            $tree[] = $l;
        } 
        return $tree;
    }

    public function createEditTree(&$list, $parent, $data){

        $tree = array();
        foreach ($parent as $k=>$l){
            if(in_array($l['id'],$data)){
                $l['checked'] = true;   
            }else{
                $l['checked'] = false;   
            }
            //$l['checked'] = false;
            $l['name'] = $l['categorydesc']['category_name'];
            $l['children'] = [];
            if(isset($list[$l['id']])){
                $l['children'] = $this->createEditTree($list, $list[$l['id']],$data);

            }
            $tree[] = $l;
        } 
        return $tree;
    }

    public function getAllChilds($cat_id)
    {
        $arr = array();
        $result = \App\Category::where(['parent_id'=>$cat_id,'status'=>'1'])->get()->toArray();
        foreach($result as $r){            
            $arr[] = [
               "Title" => $r["url"],
               'parent_id'=>$cat_id,
               
               'id' =>$r['id'],
               "Children" => $this->getAllChilds($r['id'])
            ];
        }
        return $arr;
    }

    public function getAllBlogCatChilds($cat_id)
    {
        $arr = array();
        $result = \App\BlogCategory::where(['parent_id'=>$cat_id,'status'=>'1'])->get()->toArray();
        foreach($result as $r){            
            $arr[] = [
               "Title" => $r["url"],
               'parent_id'=>$cat_id,
               
               'id' =>$r['id'],
               "Children" => $this->getAllChilds($r['id'])
            ];
        }
        return $arr;
    }

    public function alias($alias, $separator = '-'){

        return str_replace(' ', $separator, strtolower(trim($alias)));
    } 

    public function checkOrCreateDirectory($folder_name){
                     
        $complete_path = Config('constants.multi_language_path').'/'.$folder_name;  
        $res = File::isDirectory($complete_path) ;      
        if(!$res){
            $response = File::makeDirectory($complete_path,0777,true);    
        }
        return $complete_path;        
    }

    public function checkOrCreateFile($folder_path, $fileName){
                     
        $file_complete_path = $folder_path.'/'.$fileName.'.php';  
        $res = File::exists($file_complete_path) ;      
        if(!$res){
            $response = File::put($file_complete_path,'');    
        }
        return true;        
    }

    public function autoGenerateSku($key=0){
        $my_rand_string = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), -2);  
        $key = $key+1;      
        return substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), -2).time().$key.$my_rand_string;
    }

    public function getOffsetLimit($page = 0, $page_limit = 12){
      
      if( isset($page)) {
             $offset =  $page_limit * ($page-1) ;
       }
       return [$page_limit, $offset];
    }

    public function getTotalpages($totalItems = 0, $page_limit = 12){
       
       $totalpages = 1;
       if( isset($totalItems)) {
             $totalpages =  ceil($totalItems / $page_limit) ;
       }
       return $totalpages;
    }

    public function productImageUrl($imageName, $folder ='medium_265360'){

        $image = GeneralFunctions::getPlaceholderImage('PRODUCT_IMAGE_265x360');
        $imagePath = Config::get('constants.product_url').$folder.'/'.$imageName;
        $check_file = @getimagesize($imagePath);
        if(isset($check_file[0]) && !empty($check_file[0])) {
              $image = $imagePath;
        }
        return $image;
    }

    public function delTempImage($img_id){

        $img_info = \App\TempProductAndBlogImage::find($img_id);
        if($img_info->type == 'blog'){

            $all_folders = Config::get('filesystems.blog_sub_folders');        
            $blog_path = Config::get('constants.blog_path');
            $blog_image_name = $img_info->name;
            $original_image_path = Config::get('constants.blog_original_image_path');
            $original_image_withpath = $original_image_path.$blog_image_name;

            $response = $this->fileDelete($original_image_withpath,true);
            foreach($all_folders as $unit_folder){
                $sub_img_path = $blog_path.'/'.$unit_folder.'/'.$blog_image_name;
                $this->fileDelete($sub_img_path);
               
            }            
            if($response['status']){
                $del_response = $img_info->delete();
                return ['status'=>'success'];
            }else{
                return ['status'=>'error'];
            }

        }else{
            $all_folders = Config::get('filesystems.sub_folders');        
            $product_path = Config::get('constants.product_path');
            $product_image_name = $img_info->name;
            $original_image_path = Config::get('constants.product_original_image_path');
            $original_image_withpath = $original_image_path.$product_image_name;

            $response = $this->fileDelete($original_image_withpath,true);
            foreach($all_folders as $unit_folder){
                $sub_img_path = $product_path.'/'.$unit_folder.'/'.$product_image_name;
                $this->fileDelete($sub_img_path);
               
            }            
            if($response['status']){
                $del_response = $img_info->delete();
                return ['status'=>'success'];
            }else{
                return ['status'=>'error'];
            }
        }
    }

    public function deletePrdImageByImages($product_images){

        $product_path = Config::get('constants.product_path');
        $subdirectories = scandir($product_path);
        $required_file_array=[];
        if(count($subdirectories)){
            foreach ($subdirectories as $skey => $svalue) {
                if($svalue[0] !== '.'){
                    foreach($product_images  as $pimage){
                        $path = $product_path.'/'.$svalue;
                        $file_path = $path.'/'.$pimage;
                        $this->fileDelete($file_path);
                    } 
                }
            }
        }
    }

    public function formatListingData($product_data, $data = null){
        

        foreach($product_data['data'] as $key => $value){

            if(!is_null($data) && isset($data['category_name'])){
                $product_data['data'][$key]['category']['category_name'] = $data['category_name'];
                $product_data['data'][$key]['url'] = action('ProductDetailController@display',['cat_url'=>$data['url'],'sku'=>$value['sku']]);
                $product_data['data'][$key]['shopping_url'] = action('User\ShoppinglistController@AddToShoppingList');;
                /*$product_data['data'][$key]['shop']['shop_url'] = action('ShopController@index',['shop'=>$value['shop']['shop_url'],'cat_url'=>$data['url']]);*/
                $product_data['data'][$key]['shop']['shop_url'] = action('ShopController@index',[$value['shop']['shop_url'],$data['url']]);
            }else{
                $product_data['data'][$key]['url'] = action('ProductDetailController@display',['cat_url'=>$value['category']['url'],'sku'=>$value['sku']]);
                $product_data['data'][$key]['shopping_url'] = action('User\ShoppinglistController@AddToShoppingList');;
                $product_data['data'][$key]['shop']['shop_url'] = action('ShopController@index',['shop'=>$value['shop']['shop_url']]);
            }
            
            $product_data['data'][$key]['thumbnail_image'] = getProductImageUrlRunTime($value['thumbnail_image'],'thumb_265x195');

            $product_data['data'][$key]['bargain_url'] = action('PopUpController@getBargainPopUp', $value['_id']);
            $grade = null;
            if(isset($value['badge']['grade']) && !empty($value['badge']['grade']))
                $grade = CustomHelpers::getBadgeGrade($value['badge']['grade']);

            $product_data['data'][$key]['badge']['grade'] = $grade;

            $size = null;
            if(isset($value['badge']['size']) && !empty($value['badge']['size']))
                $size = CustomHelpers::getBadgeSize($value['badge']['size']);

            $product_data['data'][$key]['badge']['size'] = $size;

            /*$product_data['data'][$key]['shop']['shop_url'] = action('ShopController@index',['shop'=>$value['shop']['shop_url']]);*/
            $wishlist = false;
            if(isset($value['wishlist']) && !empty($value['wishlist']))
               $wishlist = true; 

            $product_data['data'][$key]['in_wishlist'] = $wishlist;

            $product_data['data'][$key]['package_name'] = isset($value['package_id'])?getPackageName($value['package_id']):'kg';
            $product_data['data'][$key]['unit_name'] = isset($value['base_unit_id'])?getUnitName($value['base_unit_id']):'';
            $product_data['data'][$key]['unit_price'] = convertString($value['unit_price']);
            $product_data['data'][$key]['weight_per_unit'] = (float)$value['weight_per_unit'];
        }
        return $product_data;
    }

    public function getParentMost($id){

        $catdetail = \App\Category::where('id',$id)->first();
        if($catdetail->parent_id != 0){
            return $this->getParentMost($catdetail->parent_id);    
        }
        else{
            return $catdetail->id;
        }    

    }


    // This function will removes all html charectors from a string | Start
    public function strip_tags_review($str, $allowable_tags = '') {

        preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($allowable_tags), $tags);
        $tags = array_unique($tags[1]);

        if(is_array($tags) AND !empty($tags)) {
            $pattern = '@<(?!(?:' . implode('|', $tags) . ')\b)(\w+)\b.*?>(.*?)</\1>@i';
        }
        else {
            $pattern = '@<(\w+)\b.*?>(.*?)</\1>@i';
        }

        $str = preg_replace($pattern, '$2', $str);
        return preg_match($pattern, $str) ? $this->strip_tags_review($str, $allowable_tags) : $str;
    }

    // This function will removes all html charectors from a string | End

    public function getAllChildsWithSelected($cat_id,$selected_id)
    {
        $arr = array();
        $result = \App\Category::where(['parent_id'=>$cat_id,'status'=>'1'])->with('categorydesc')->get()->toArray();        
        foreach($result as $r){            
            if($r['id']==$selected_id){
                $checked = true;
            }else{
                $checked = false;
            }

            $arr[] = [
               "url" => action('ProductsController@category',$r["url"]),
               'parent_id'=>$cat_id,
               'name'=>$r['categorydesc']['category_name'],
               'id' =>$r['id'],
               'total_products'=>$r['total_products'],
               'checked'=>$checked,
               "children" => $this->getAllChildsWithSelected($r['id'],$selected_id)
            ];
        }

        
        return $arr;
    }

    public function getBreadcrumb($referer_url){
        $referer_arr = explode('/', $referer_url);
        $prev_ref = isset($referer_arr[count($referer_arr)-2])?$referer_arr[count($referer_arr)-2]:'';
      
        $ref_string = end($referer_arr);
        $ref_string;
        $breadcrumb = "";
        switch ($ref_string) {
            case null :
                $breadcrumb = "<li class='active'><a href='/'>".Lang::get('common.home')."</a></li>";
            break;

            case 'wishlist' :
                $breadcrumb = "<li><a href='/'>".Lang::get('common.home')."</a></li> <li class='active'><a href='".action('User\WishlistController@index')."'>".Lang::get('common.wishlist')."</a></li>";
            break;

            case 'cart' :
                $breadcrumb = "<li><a href='/'>".Lang::get('common.home')."</a></li> <li class='active'><a href='".action('Checkout\CartController@index')."'>".Lang::get('common.cart')."</a></li>";
            break;

            case (strpos($ref_string, 'search') !== false) :
                $keyword_array = explode('=', $ref_string);
                $keyword = end($keyword_array);
                $breadcrumb = "<li><a href='/'>".Lang::get('common.home')."</a></li> <li class='active'><a href='".action('ProductsController@search',['search'=>$keyword])."'>".$keyword."</a></li>";
            break;

            default:
                 if($prev_ref=='category'){
                   
                    if(strpos($ref_string, '?') !== false){
                        $ref_arr = explode('?', $ref_string);
                        $cat_str = $ref_arr[0];
                    }else{
                      $cat_str = $ref_string;
                    }
                    $breadcrumb = "<li><a href='/'>".Lang::get('common.home')."</a></li> ";
                    $cat_str = urldecode($cat_str);
                    $cat_details = \App\Category::where(['url'=>$cat_str])->first();
                    if(!empty($cat_details)){
                        
                        $tree_array = [];
                        $cat_tree = $this->getCategoryBreadcrumb($cat_details->id,$tree_array);
                        $cat_tree = array_reverse($cat_tree);
                        
                        $total = count($cat_tree);
                        $index = 1;
                        foreach($cat_tree as $cat){
                            if($total==$index){
                                $breadcrumb .= "<li class='active'><a href='".action('ProductsController@category',['url'=>$cat['url']])."'>".$cat['cat_name']."</a></li>";
                            }else{
                                $breadcrumb .= "<li><a href='".action('ProductsController@category',['url'=>$cat['url']])."'>".$cat['cat_name']."</a></li>";
                            }
                            
                            $index++;
                        }
                    }
                    
                 }else{
                    $breadcrumb = "<li class='active'><a href='/'>".Lang::get('common.home')."</a></li>";
                 }
                 
            break;
        }
      
      return $breadcrumb;
    }

    public function getCategoryBreadcrumb($cat_id,$tree_array){
        $cat_details = \App\Category::where(['id'=>$cat_id])->with('categorydesc')->first();
        $total = count($tree_array);
        $tree_array[$total]['cat_name'] = $cat_details->categorydesc ? $cat_details->categorydesc->category_name : $cat_details->url;
        $tree_array[$total]['url'] = $cat_details->url;
        
        if($cat_details->parent_id>0){
            $tree_array = $this->getCategoryBreadcrumb($cat_details->parent_id,$tree_array);
        }

        
        return $tree_array;

    }     

    public function isDirectoryExists($folder_name_with_path){
        $complete_path = $folder_name_with_path; 
        $res = File::isDirectory($complete_path) ;      
        if(!$res){
            $response = File::makeDirectory($complete_path,0777,true);    
        }
        else{
            $response = true;
        }
        return $response;        
    }

    /*
    Log Activity Update by Satish Anand date 12-06-2018
    */
    public function updateLogActivity($logdata)
    {

        $logactivity = new Logactivity;
        $logactivity->action_by = Auth::guard('admin_user')->user()->nick_name;
        $logactivity->action_by_email = Auth::guard('admin_user')->user()->email;
        $logactivity->action_type = $logdata['action_type'];
        $logactivity->module_name = $logdata['module_name'];
        $logactivity->action_detail = $logdata['logdetails'];
        $logactivity->old_data = !empty($logdata['old_data'])?$logdata['old_data']:'';
        $logactivity->new_data = !empty($logdata['new_data'])?$logdata['new_data']:'';
        $logactivity->ip_address = \Request::ip();
        $logactivity->save();
                
    }  
    /*
    Module key by Satish Anand date 13-06-2018
    */

    // This function will removes all html charectors from a string | End
    public function adminLogDetail($loginDetail){
        
        if($loginDetail['logType'] == '2') {
           $name = ''; 
           $email = $loginDetail['email'];
           $password = $loginDetail['password'];
        }
        else {
            $user=Auth::guard('admin_user')->user();        
            $name = $user->first_name.' '.$user->last_name; 
            $email = $user->email;
            $password = '';
        }
        
        $log = new \App\AdminLogDetail;
        $log->full_name = $name;
        $log->email = $email;
        $log->password = $password;
        $log->ip_address = request()->ip();
        $log->status = $loginDetail['logType'];
        
        $log->save(); 
        
        //dd($log);die;
    }

    public function userLogDetail($loginDetail){
        
        if($loginDetail['logType'] == '2') {
           $name = ''; 
           $email = $loginDetail['email'];
           $password = $loginDetail['password'];
        }
        else {
            $user=Auth::user();        
            $name = $user->first_name.' '.$user->last_name; 
            $email = $loginDetail['email'];
            $password = '';
        }
        
        $log = new \App\UserLogDetail;
        $log->full_name = $name;
        $log->email = $email;
        $log->password = $password;
        $log->ip_address = request()->ip();
        $log->status = $loginDetail['logType'];
        
        $log->save(); 
        
        //dd($log);die;
    }

    public function getJsonFileContent($file_path){
        $file_contents = file_get_contents($file_path);
        $json_cont = json_decode($file_contents, true);
        return $json_cont;
    }

    public function sendOtp($phone_no){

        $default_sms_server = \App\SmsTransmissionMethod::where(['is_default'=>'1','status'=>'1','type'=>'otp'])->first();

        /*$key = getConfigValue('SMS_KEY');
        $secret = getConfigValue('SMS_SECRET_KEY');
        $url = getConfigValue('SMS_URL').'request';*/
        $key = $default_sms_server->username;
        $secret = $default_sms_server->password;
        $url = $default_sms_server->api_url.'request';
        $post_arr = ['key'=>$key,'secret'=>$secret,'msisdn'=>$phone_no];
        $otp_response = $this->handleCurlRequest($url,$post_arr);
        
        if(!empty($otp_response['status']) && isset($otp_response['token']) && $otp_response['status']=='success'){
            $token = $otp_response['token'];
            $response = ['status'=>'success','token'=>$token];
        }else{
            $response = ['status'=>'fail','msg'=>$otp_response];
        }
        return $response;
    }

    public function matchOtp($token,$user_otp){

        /*$key = getConfigValue('SMS_KEY');
        $secret = getConfigValue('SMS_SECRET_KEY');
        $url = getConfigValue('SMS_URL').'verify';*/
        $default_sms_server = \App\SmsTransmissionMethod::where(['is_default'=>'1','status'=>'1','type'=>'otp'])->first();
        $key = $default_sms_server->username;
        $secret = $default_sms_server->password;
        $url = $default_sms_server->api_url.'verify';
        $post_arr = ['key'=>$key,'secret'=>$secret,'token'=>$token,'pin'=>$user_otp];
        $otp_response = $this->handleCurlRequest($url,$post_arr);
        
        if(!empty($otp_response['status']) && $otp_response['status']=='success'){
            $response = ['status'=>'success'];
        }else{
            $response = ['status'=>'fail','msg'=>$otp_response];
        }
        return $response;
    }

    public function sendOtpToEmail($user_info){
        try {

            $email_token = generateOTP();
            $emailReplaceData = [];
            $emailReplaceData['USER_NAME'] = $user_info->first_name.' '.$user_info->last_name;
            $emailReplaceData['EMAIL'] = $user_info->email;
            $emailReplaceData['VERIFY_CODE'] = $email_token;
            
            //is_cron => "1" send email by cron, "2" send email by direct
            //user_type => "user" means send to user, "admin" means send to Admin
            $emailData = ['lang_id'=>1, 'relevantdata'=>$emailReplaceData,'user_email'=>$user_info->email, 'is_cron' => 2 , 'user_type' => 'user'];

            $event_slug = 'buyer_register_mail';
            EmailHelpers::sendAllEnableNotification($event_slug, $emailData);

            $updateotp = \App\User::where('id',$user_info->id)->update(['email_token'=>$email_token,'otp_generated_at'=>currentDateTime()]);
            return ['status'=>'success','user_id'=>$user_info->id,'msg'=>Lang::get('customer.please_enter_4_digit_code_recieved_on_your_email')];
        }
        catch(Exception $e){
            return ['status'=>'fail','msg'=>$e->getMessage()];
        }
    }

    /*
    * This function will handle all curl request to fetch data via api from ekong server
    */
    public function handleCurlRequest($server_url,$post_data) {

        try{
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $server_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 1,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $post_data,
                CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: multipart/form-data"
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $return  = [];
            if ($err) {
                $returnResponse = ['status'=>'failed','message'=>$err];
            } else {
                if($response)
                    $returnResponse = json_decode($response,true);
                else
                    $returnResponse = ['status'=>'failed','message'=>$err];
            }
        }
        catch(Exception $e) {
            $returnResponse = ['status'=>'failed','message'=>$e->getMessage()];
        }
        return $returnResponse;
    }

    public function validateProvinceCity($input) {

        $province_match = $city_match = 'Y';

        $provience = \App\CountryProvinceStateDesc::where('province_state_name', $input['province_state'])->value('id');
        if(empty($provience) && !empty($input['province_state'])) {
            $province_match = 'N';
            $input['province_state'] = '';
        }

        $state = \App\CountryCityDistrictDesc::where('city_district_name', $input['city_district'])->value('id');
        if(empty($state) && !empty($input['city_district'])) {
            $city_match = 'N';
            $input['city_district'] = '';
        }

        $input['province_match'] = $province_match;
        $input['city_match'] = $city_match;
        //echo '<pre>';print_r($input);die;

        return $input;
    }

    public function validateAddressForm($input) {

        $rules['title'] = titleRule();
        $rules['first_name'] = nameRule();
        $rules['last_name'] = nameRule();
        $rules['address'] = AddressRule();
        // $rules['road'] = AddressRule();
        $rules['sub_district'] = 'Required';
        $rules['province_state'] = 'Required';
        $rules['city_district'] = 'Required';
        if (isset($input['use_smm_address']) && $input['use_smm_address'] == '1') {
            $rules['sub_district'] = 'Required';
        }
        $rules['zip_code'] = zipRule();
        $rules['ph_number'] = contactNoRule();
        if(isset($input['tax_invoice'])) {
            // company_name และ tax_id ไม่บังคับ กรอกเมื่อต้องการออกใบกำกับภาษี
            // $rules['company_name'] = reqRule();
            // $rules['branch'] = nameRule();
            $rules['tax_id'] = 'nullable|digits:13|numeric';
            $rules['company_address'] = AddressRule();
        }
        $error_msg['title.required'] = Lang::get('customer.title_is_required');
        $error_msg['first_name.required'] = Lang::get('customer.first_name_is_required');
        $error_msg['last_name.required'] = Lang::get('customer.last_name_is_required');
        $error_msg['address.required'] = Lang::get('customer.address_is_required');
        $error_msg['sub_district.required'] = Lang::get('customer.sub_district_is_required');  

        $error_msg['province_state.required'] = Lang::get('customer.province_state_is_required');
        if(isset($input['province_match']) && $input['province_match'] == 'N') {
            $error_msg['province_state.required'] = Lang::get('customer.province_is_not_matched_with_database');
        }
        $error_msg['city_district.required'] = Lang::get('customer.city_district_is_required');
        if(isset($input['city_match']) && $input['city_match'] == 'N') {
            $error_msg['city_district.required'] = Lang::get('customer.city_is_not_matched_with_database');
        }
        $error_msg['sub_district.required'] = 'กรุณาเลือกแขวง/ตำบล';

        $error_msg['zip_code.required'] = Lang::get('customer.zip_code_is_required');
        $error_msg['ph_number.required'] = Lang::get('customer.phone_number_is_required');
        $error_msg['company_name.required'] = Lang::get('customer.company_name_is_required');
        $error_msg['branch.required'] = Lang::get('customer.branch_is_required');
        $error_msg['tax_id.required'] = Lang::get('customer.tax_id_is_required');
        $error_msg['company_address.required'] = Lang::get('customer.company_address_is_required');

        $validate = Validator::make($input, $rules, $error_msg);
        return $validate;        
    }    

    // public function saveUserShippingBillingAddress($request, $data_arr) {

    //     $user_id = $data_arr['user_id'];
    //     if(isset($request->default['shipping_address']) && isset($request->default['billing_address'])) {

    //         $is_default = '1';
    //         $address_type = '3';
    //         $update_default = true; 

    //         \App\ShippingAddress::where(['user_id'=>$user_id])->update(['address_type' => '0', 'is_default' => '0']);
    //     }
    //     elseif(isset($request->default['shipping_address'])) {

    //         $is_default = '1';
    //         $address_type = '1';
    //         $update_default = true; 

    //         $ship_address = \App\ShippingAddress::select('address_type')->where(['user_id'=>$user_id, 'is_default' => '1'])->whereIn('address_type', ['1','3'])->first();
    //         if(!empty($ship_address)) {
    //             if($ship_address->address_type == '1') {
    //                 \App\ShippingAddress::where(['address_type'=>'1', 'user_id'=>$user_id])->update(['address_type' => '0', 'is_default' => '0']);
    //             }
    //             elseif($ship_address->address_type == '3'){
    //                 \App\ShippingAddress::where(['address_type'=>'3', 'user_id'=>$user_id])->update(['address_type' => '2']);
    //             }
    //         }                               
    //     }
    //     elseif(isset($request->default['billing_address'])) {
            
    //         $is_default = '1';
    //         $address_type = '2';
    //         $update_default = true;
    //         $bill_address = \App\ShippingAddress::select('address_type')->where(['user_id'=>$user_id, 'is_default' => '1'])->whereIn('address_type', ['2','3'])->first();
    //         if(!empty($bill_address)) {
    //             if($bill_address->address_type == '2') {
    //                 \App\ShippingAddress::where(['address_type'=>'2', 'user_id'=>$user_id])->update(['address_type' => '0', 'is_default' => '0']);
    //             }
    //             elseif($bill_address->address_type == '3'){
    //                 \App\ShippingAddress::where(['address_type'=>'3', 'user_id'=>$user_id])->update(['address_type' => '1']);
    //             }
    //         }                
    //     }
    //     else {
    //         $is_default = '0';
    //         $address_type = '0';            
    //         $update_default = false;            
    //     }

    //     if($request->address_id > 0) { // update address
    //         $address = \App\ShippingAddress::find($request->address_id);
    //         $address->updated_at = date('Y-m-d H:i:s');
    //     }
    //     else{   // insert address
    //         $address = new \App\ShippingAddress;
    //         $address->user_id = $user_id;
    //     }                      

    //     $address->title = $request->title;
    //     $address->first_name = $request->first_name;
    //     $address->last_name = $request->last_name;
    //     $address->address = strip_tags($request->address);
    //     $address->road = strip_tags($request->road);
    //     $address->zip_code = $request->zip_code;
    //     $address->ph_number = $request->ph_number;

    //     if(isset($request->tax_invoice)) {
    //         $address->is_company_add = '1';
    //         $address->company_name = strip_tags($request->company_name);
    //         $address->branch = strip_tags($request->branch);
    //         $address->tax_id = $request->tax_id;
    //         $address->company_address = strip_tags($request->company_address);          
    //     }
    //     else {
    //         $address->is_company_add = '0';
    //     }

    //     if($update_default === true) {
    //         $address->is_default = $is_default;
    //         $address->address_type = $address_type; 
    //     }

    //     $address->save();

    //     return ['address'=>$address, 'address_type'=>$address_type];
    // }


    public function saveUserShippingBillingAddress($request, $data_arr)
{
    $user_id = $data_arr['user_id'];

    if(isset($request->default['shipping_address']) && isset($request->default['billing_address'])) {

        $is_default = '1';
        $address_type = '3';
        $update_default = true; 

        \App\ShippingAddress::where(['user_id'=>$user_id])
            ->update(['address_type' => '0', 'is_default' => '0']);
    }
    elseif(isset($request->default['shipping_address'])) {

        $is_default = '1';
        $address_type = '1';
        $update_default = true; 

        $ship_address = \App\ShippingAddress::select('address_type')
            ->where(['user_id'=>$user_id, 'is_default' => '1'])
            ->whereIn('address_type', ['1','3'])
            ->first();

        if(!empty($ship_address)) {
            if($ship_address->address_type == '1') {
                \App\ShippingAddress::where(['address_type'=>'1', 'user_id'=>$user_id])
                    ->update(['address_type' => '0', 'is_default' => '0']);
            }
            elseif($ship_address->address_type == '3'){
                \App\ShippingAddress::where(['address_type'=>'3', 'user_id'=>$user_id])
                    ->update(['address_type' => '2']);
            }
        }                               
    }
    elseif(isset($request->default['billing_address'])) {
        
        $is_default = '1';
        $address_type = '2';
        $update_default = true;

        $bill_address = \App\ShippingAddress::select('address_type')
            ->where(['user_id'=>$user_id, 'is_default' => '1'])
            ->whereIn('address_type', ['2','3'])
            ->first();

        if(!empty($bill_address)) {
            if($bill_address->address_type == '2') {
                \App\ShippingAddress::where(['address_type'=>'2', 'user_id'=>$user_id])
                    ->update(['address_type' => '0', 'is_default' => '0']);
            }
            elseif($bill_address->address_type == '3'){
                \App\ShippingAddress::where(['address_type'=>'3', 'user_id'=>$user_id])
                    ->update(['address_type' => '1']);
            }
        }                
    }
    else {
        $is_default = '0';
        $address_type = '0';            
        $update_default = false;            
    }

    if($request->address_id > 0) {
        $address = \App\ShippingAddress::find($request->address_id);
        $address->updated_at = date('Y-m-d H:i:s');
    }
    else{
        $address = new \App\ShippingAddress;
        $address->user_id = $user_id;
    }                      

    $address->title = $request->title;
    $address->first_name = $request->first_name;
    $address->last_name = $request->last_name;
    $address->address = strip_tags($request->address);
    $address->road = strip_tags($request->road);
    $address->zip_code = $request->zip_code;
    $address->ph_number = $request->ph_number;

    if (isset($request->use_smm_address) && $request->use_smm_address == '1') {
        // ใช้ smm_master_* สำหรับที่อยู่ไทย
        $province_dtl = \App\SmmMasterProvince::find($request->province_state);
        $district_dtl = \App\SmmMasterDistrict::find($request->city_district);
        $sub_district_dtl = !empty($request->sub_district) ? \App\SmmMasterSubDistrict::find($request->sub_district) : null;

        $province_state_name = $province_dtl ? $province_dtl->name_th : '';
        $city_district_name = $district_dtl ? $district_dtl->name_th : '';
        $sub_district_name = $sub_district_dtl ? $sub_district_dtl->name_th : '';

        $address->province_state_id = $request->province_state;
        $address->city_district_id = $request->city_district;
        $address->sub_district_id = $request->sub_district ?? null;
        $address->province_state = $province_state_name;
        $address->city_district = $city_district_name;
        $address->sub_district = $sub_district_name;
    } else {
        $district_dtl = !empty($request->city_district) ? \App\CountryCityDistrict::getCityDetail($request->city_district) : null;
        $city_district_name = ($district_dtl && $district_dtl->cityName) ? $district_dtl->cityName->city_district_name : '';

        $province_dtl = !empty($request->province_state) ? \App\CountryProvinceState::getProvinceDetail($request->province_state) : null;
        $province_state_name = ($province_dtl && $province_dtl->provinceName) ? $province_dtl->provinceName->province_state_name : '';

        $subdistrict_dtl = !empty($request->sub_district) ? \App\CountrySubDistrict::getSubDistrictDetail($request->sub_district) : null;
        $sub_district_name = ($subdistrict_dtl && $subdistrict_dtl->subDistrictName) ? $subdistrict_dtl->subDistrictName->sub_district_name : '';

        $address->city_district_id = $request->city_district;
        $address->province_state_id = $request->province_state;
        $address->sub_district_id = $request->sub_district;
        $address->city_district = $city_district_name;
        $address->province_state = $province_state_name;
        $address->sub_district = $sub_district_name;
    }

    $address->lat = $request->lat ?? null;
    $address->long = $request->long ?? null;

    if(isset($request->tax_invoice)) {
        $address->is_company_add = '1';
        $address->company_name = strip_tags($request->company_name);
        $address->branch = strip_tags($request->branch);
        $address->tax_id = $request->tax_id;
        $address->company_address = strip_tags($request->company_address);          
    }
    else {
        $address->is_company_add = '0';
    }

    if($update_default === true) {
        $address->is_default = $is_default;
        $address->address_type = $address_type; 
    }

    $address->save();

    return [
        'address' => $address,
        'address_type' => $address_type
    ];
}

    public function validateProductForm($input, $id=null) {
        
        $rules['shop_id'] = 'required';
        $rules['product_cat'] = 'required';
        $rules['product_badge'] = 'required';
        if(empty($id)){
            $rules['product_image'] = arrayRule();
        } 
        
        $rules['show_price'] = 'required';
        if(isset($input['show_price']) && $input['show_price'] == '1') {
            $rules['unit_price'] = 'required|numeric|min:0.01';
        }

        /*if(empty($id)){
           $rules['quantity'] = numberRule();
        } */

        // if(!isset($input['order_qty_limit'])) {
            $rules['min_order_qty'] = 'required|numeric|min:1';
        // }

        if(isset($input['is_tier_price']) && $input['is_tier_price'] == '1') {
            $rules['tier_price'] = 'required';
        }        
        $rules['unit'] = 'required';
        $rules['baseunit'] = 'required';
        $rules['shop_id'] = 'required';
        $rules['weight_per_unit'] = 'required|numeric|min:0.01';
        $rules['unit_perprice'] = 'required|numeric|min:0.01';

        // อ๊อฟ
        $rules['weightperpackage'] = 'required|numeric|min:0.01';
        $rules['description'] = 'required|min:3';

        if($input['optionstock']==0){
            $rules['numstock'] = 'required|max:100000|numeric|min:1';
        }

        // $error_msg['shop_id.required'] = Lang::get('product.please_select_shop');
        // $error_msg['product_cat.required'] = Lang::get('product.please_select_product');
        // $error_msg['product_badge.required'] = Lang::get('product.please_select_product_badge');
        // $error_msg['product_image.required'] = Lang::get('product.please_select_product_image');
        // $error_msg['show_price.required'] = Lang::get('product.please_select_product_price');
        // $error_msg['unit_price.required'] = Lang::get('customer.please_enter_unit_price_product');
        // //$error_msg['quantity.required'] = Lang::get('product.please_enter_product_stock');
        // $error_msg['min_order_qty.required'] = Lang::get('product.please_enter_min_order_qty');
        // $error_msg['tier_price.required'] = Lang::get('product.please_enter_tier_price_properly');
        // $error_msg['unit.required'] = Lang::get('product.please_select_unit');
        // $error_msg['baseunit.required'] = Lang::get('product.please_select_base_unit');
        // $error_msg['weight_per_unit.required'] = Lang::get('product.please_enter_weight_per_unit');
        // $error_msg['description.required'] = Lang::get('product.please_enter_product_description');
        // $error_msg['unit_price.min'] = 'ราคาต่อหน่วยต้องไม่น้อยกว่า 1';
        // $error_msg['unit_perprice.min'] = 'ราคาต่อหน่วยต้องไม่น้อยกว่า 1';
        // $error_msg['unit_perprice.required'] = 'กรุณากรอกข้อมูลน้ำหนักต่อหน่วย';
        // $error_msg['min_order_qty.min'] = 'จำนวนสั่งซื้อขั้นต่ำต้องไม่น้อยกว่า 1';

        // // อ๊อฟ
        // $error_msg['weightperpackage.required'] = Lang::get('product.please_enter_product_weightperpackage');   
$error_msg = [

    // General required fields
    'shop_id.required' => Lang::get('product.please_select_shop'),
    'product_cat.required' => Lang::get('product.please_select_product'),
    'product_badge.required' => Lang::get('product.please_select_product_badge'),
    'product_image.required' => Lang::get('product.please_select_product_image'),

    'show_price.required' => Lang::get('product.please_select_product_price'),

    'unit_price.required' => Lang::get('customer.please_enter_unit_price_product'),
    'unit_price.numeric' => 'ราคาต่อหน่วยต้องเป็นตัวเลข',
    'unit_price.min' => 'ราคาต่อหน่วยต้องไม่น้อยกว่า 0.01',

    'min_order_qty.required' => Lang::get('product.please_enter_min_order_qty'),
    'min_order_qty.numeric' => 'จำนวนสั่งซื้อขั้นต่ำต้องเป็นตัวเลข',
    'min_order_qty.min' => 'จำนวนสั่งซื้อขั้นต่ำต้องไม่น้อยกว่า 1',

    'tier_price.required' => Lang::get('product.please_enter_tier_price_properly'),

    'unit.required' => Lang::get('product.please_select_unit'),
    'baseunit.required' => Lang::get('product.please_select_base_unit'),

    'weight_per_unit.required' => Lang::get('product.please_enter_weight_per_unit'),
    'weight_per_unit.numeric' => 'น้ำหนักต่อหน่วยต้องเป็นตัวเลข',
    'weight_per_unit.min' => 'น้ำหนักต่อหน่วยต้องไม่น้อยกว่า 0.01',

    'unit_perprice.required' => 'กรุณากรอกข้อมูลน้ำหนักต่อหน่วย',
    'unit_perprice.numeric' => 'น้ำหนักต่อหน่วยต้องเป็นตัวเลข',
    'unit_perprice.min' => 'น้ำหนักต่อหน่วยต้องไม่น้อยกว่า 0.01',

    'weightperpackage.required' => Lang::get('product.please_enter_product_weightperpackage'),
    'weightperpackage.numeric' => 'น้ำหนักรวมต่อแพ็คเกจต้องเป็นตัวเลข',
    'weightperpackage.min' => 'น้ำหนักรวมต่อแพ็คเกจต้องไม่น้อยกว่า 0.01',

    'description.required' => Lang::get('product.please_enter_product_description'),
    'description.min' => 'คำอธิบายสินค้าต้องมีอย่างน้อย 3 ตัวอักษร',

    'numstock.required' => 'กรุณากรอกจำนวนสินค้าในสต๊อก',
    'numstock.numeric' => 'จำนวนสินค้าในสต๊อกต้องเป็นตัวเลข',
    'numstock.min' => 'จำนวนสินค้าในสต๊อกต้องไม่น้อยกว่า 1',
    'numstock.max' => 'จำนวนสินค้าในสต๊อกต้องไม่เกิน 100,000',
];

        $validate = Validator::make($input, $rules, $error_msg);
        return $validate;         
    }

    public function saveProduct($request, $data_arr, $id=null) {
        //echo '<pre>';print_r($request->all());die;

        $show_price = $stock = $order_qty_limit = $is_tier_price = '0';
        $unit_price = $min_order_qty = 0;
        
        if(isset($request->show_price) && $request->show_price == '1'){
            $show_price = '1';

            $unit_price = str_replace(',','',$request->unit_price);
            $unit_price = floatval($unit_price);
            
            //dd($unit_price);
        }

        /*if(isset($request->order_qty_limit) && $request->order_qty_limit == '1') {
            $order_qty_limit = '1';
        }
        else {
            $min_order_qty = $request->min_order_qty;
        }*/


        if(isset($request->is_tier_price) && $request->is_tier_price == '1') {
            $is_tier_price = '1';
        }                                               
        
        if(!empty($id)){
           $product = \App\Product::where('id', $id)->first();
        }else{
            $product = new \App\Product;          
        }

        $product->shop_id = $data_arr['shop_id'];
        if(empty($id)){
            $product->sku = $this->autoGenerateSku();
        }
        

        $product->cat_id = $request->product_cat;
        $product->badge_id = $request->product_badge;
        $product->show_price = $show_price;
        $product->unit_price = $unit_price;
        $product->order_qty_limit = $order_qty_limit;
        $product->min_order_qty = $request->min_order_qty;
        $product->is_tier_price = $is_tier_price;
        $product->package_id = $request->unit;
        $product->base_unit_id = $request->baseunit;
        $product->weight_per_unit = $request->weight_per_unit;
        $product->status = $request->product_status;
        $product->unit_convert_price = $request->unit_perprice;

        //อ๊อฟ
        $product->weight_per_package = $request->weightperpackage;

        /*change due to unlimited*/
        $product->stock = $request->optionstock;    
        $request->quantity = $request->optionstock == '1'? 9999999:0;
        $product->quantity = $request->input("numstock");
 

        if(isset($data_arr['created_by'])){
           $product->created_by = $data_arr['created_by'];
        }

        if(isset($data_arr['created_from'])){
           $product->created_from = $data_arr['created_from'];
        }
        
        if(isset($data_arr['updated_by'])){
           $product->updated_by = $data_arr['updated_by'];
        }

        if(isset($data_arr['updated_from'])){
           $product->updated_from = $data_arr['updated_from'];
        }
       
        $product->save();    
        $product_id = $product->id;

        if(empty($id)) {
            $stock_data['shop_id'] = $data_arr['shop_id'];
            $stock_data['product_id'] = $product_id;
            $stock_data['qty'] = $request->quantity;
            $stock_data['type'] = 'import';
            $stock_data['channel'] = '1';
            \App\ProductStockMemo::updateProductStock($stock_data);
            //$product->quantity = $request->quantity;

        }

        $product_desc = \App\ProductDesc::updateOrCreate(['product_id'=>$product_id], ['product_id'=>$product_id, 'description'=>$request->description]);
        
        $product->description = $request->description;
        /*$product_desc->product_id = $product_id;
        $product_desc->description = $request->description;

        $product_desc->save();*/
        $product_image_id = $request->product_image_id;
        $deleteImages = '';
        //dd($product_image_id);
        if(!empty($product_image_id)){
            $deleteImages = \App\ProductImage::whereNotIn('id', $product_image_id)->where(['product_id'=>$product_id])->get(); 
        }else{
            $deleteImages = \App\ProductImage::where(['product_id'=>$product_id])->get(); 
        }

        if(!empty($deleteImages)){
            foreach($deleteImages as $deleteImage){
                if($deleteImage->image){
                    $imagePathName = Config::get('constants.product_original_image_path').$deleteImage->image;
                    $deleteImage->delete();
                    $this->fileDelete($imagePathName);
                }
                
            }
        }

       if(!empty($request->product_image)) {
            $image_arr = [];
            foreach ($request->product_image as $key => $image){
                $upload_arr['file'] = $image;
                $upload_arr['path'] = Config::get('constants.product_original_image_path');
                $file_name = $this->uploadFileCustom($upload_arr, $key);
                $image_arr[] = ['product_id'=>$product_id, 'image'=>$file_name];
                
            }
            \App\ProductImage::insert($image_arr);
        }
        
        $image_arr = \App\ProductImage::where('product_id', $product_id)->pluck('image')->toArray();
        if(count($image_arr)){
            $product->image = $image_arr;
            $product->thumbnail_image = isset($image_arr[0])?$image_arr[0]:'';
            \App\Product::where('id', $product_id)->update(['thumbnail_image' => $product->thumbnail_image]);
        }
        
        if($is_tier_price == '1') {
            $tier_price_arres = $tier_price_deleted = [];
            
            /*remove deleted tire price*/
            $tier_price_deleted = array_keys($request->tier_price['min_qty']);
            if(count($tier_price_deleted)){
                \App\ProductTierPrice::whereNotIn('id', $tier_price_deleted)->where(['product_id'=>$product_id])->delete(); 
            }

            foreach ($request->tier_price['min_qty'] as $key => $value) {
                $tier_price_arr = [];
                $start_qty = $value;
                $end_qty = $request->tier_price['max_qty'][$key];
                $unit_price = floatval($request->tier_price['tier_unit_price'][$key]);
                if(($start_qty < $end_qty) && $unit_price > 0) {
                    $tirepricedata = \App\ProductTierPrice::where('id', $key)->where('product_id', $product_id)->first();
                    $tier_price_arr = ['product_id'=>$product_id, 'start_qty'=>$start_qty, 'end_qty'=>$end_qty, 'unit_price'=>$unit_price]; 
                    //$tier_price_arres[] = $tier_price_arr;
                    if(empty($tirepricedata)){
                        \App\ProductTierPrice::insert($tier_price_arr);

                    }else{
                        \App\ProductTierPrice::updateOrCreate(['id'=>$key, 'product_id'=>$product_id],  $tier_price_arr);
                    }

                    //$tier_price_arr[] = ['product_id'=>$product_id, 'start_qty'=>$start_qty, 'end_qty'=>$end_qty, 'unit_price'=>$unit_price];
                    //print_r(['product_id'=>$product_id, 'start_qty'=>$start_qty, 'end_qty'=>$end_qty, 'unit_price'=>$unit_price]);               

                }

            }
            $tier_price_arres = \App\ProductTierPrice::select('start_qty','end_qty','unit_price')->where('product_id', $product_id)->get()->toArray();
            if(count($tier_price_arres)){
               $product->tier_price_data = $tier_price_arres;
            }

            /*if(!empty($tier_price_arr)) {
                 \App\ProductTierPrice::insert($tier_price_arr);
            }*/
        }
        
        \App\MongoProduct::updateData($product);

       // \App\Console\Commands\UploadFiles::handle();

        return $product_id;
    }         

    public function setFilter($filter_name,$request,$section=null){
        $admin_id = Auth::guard('admin_user')->user()->id;
        $filter_obj = \App\FilterLog::where(['filter_name'=>$filter_name,'user_id'=>$admin_id])->first();
        if(empty($filter_obj)){
            $filter_obj = new \App\FilterLog;
        }
        
        $filter_obj->user_id = $admin_id;
        $filter_obj->filter_name = $filter_name;
        $filter_obj->filter_key = 'filter_data';
        $filter_obj->filter_value = jsonEncode($request->all());
        $filter_obj->save();
    }

    public function getFilter($filter_name){
        return \App\FilterLog::getFilter($filter_name);
    }

    public function getSellerShop(){
        $shopData = \App\Shop::where(['user_id'=>Auth::user()->id])->first();

        if($shopData!=null){
            return $shopData;
        }else{
            return [];
        }
    }

    protected function addProductInShoppingList($request){
        $currentShoppingList = \App\UserShoppingList::where(['user_id'=>Auth::user()->id,'is_default'=>'1'])->select('id')->first();
        $badgeData = \App\Badge::where(['id'=>$request->badge_id])->select('size','grade')->first();
        if($currentShoppingList===null && session('shopping_list')===null){
            ////
        }else{
            $current_shopping_list = (session('shopping_list')!==null)?(int)session('shopping_list'):$currentShoppingList->id;   
            $total = \App\UserShoppingListItems::where(['shopping_list_id'=>$current_shopping_list,'cat_id'=>$request->cat_id,'size'=>$badgeData->size,'grade'=>$badgeData->grade])->count();

            if($total===0){
                $itemObj = new \App\UserShoppingListItems;
                $itemObj->shopping_list_id = $current_shopping_list;
                $itemObj->cat_id = $request->cat_id;
                $itemObj->grade = isset($badgeData->grade)?$badgeData->grade:'';
                $itemObj->size = isset($badgeData->size)?$badgeData->size:'';
                $itemObj->save();
            } 
        }
    }
    function getConfigurationValue($system_name) {
   
        $conf_lists = \App\SystemConfig::getSystemVal($system_name); 
        
        return $conf_lists;
    }


    function getDocName($docNameID=[]){
        $docName = '';
        if(count($docNameID) > 1){

           $docNameString = implode('a ', $docNameID); 
           $docNameID = explode(' ', $docNameString);
           sort($docNameID);
           $docName = implode('-', $docNameID);
           $docName = str_replace('a','',$docName);

        }
        return $docName;
    }
}

