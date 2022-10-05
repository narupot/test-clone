<?php
namespace App\Http\Controllers\Admin\CmsSlider;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;

use App\Language;
use App\CmsSlider;
use App\CmsSliderDesc;
use App\CmsSliderCategory;
use Auth;
use Lang;
use Config;

class CmsSliderController extends MarketPlace
{ 
    private $tblCmsSlider;
    private $tblSCmsSliderDesc;

    public function __construct()
    {
        $this->middleware('admin.user');

        $this->tblCmsSlider = with(new CmsSlider)->getTable();
        $this->tblCmsSliderDesc = with(new CmsSliderDesc)->getTable();
    }

    public function index(){

        $permission = $this->checkUrlPermission('list_cms_slider');
        if($permission === true) {
            $permission_arr['add'] = $this->checkMenuPermission('add_cms_slider');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_cms_slider');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_cms_slider');

            $data_arr = array();
            $slider_dtl = CmsSlider::getCmsSlider();
            
            return view('admin.cmsSlider.listSlider', ['slider_dtl'=>$slider_dtl, 'permission_arr'=>$permission_arr]);
        }
    }

    public function create(){
        $permission = $this->checkUrlPermission('add_cms_slider');
        if($permission === true) {
            $prd_categories = \App\Category::getMainCategory();
            $blog_categories = \App\BlogCategory::getMainCategory();
            $badge_dtl = \App\Badge::getBadge();
            $package_dtl = \App\Package::getAllPackage();
            return view('admin.cmsSlider.createSlider', ['prd_categories'=>$prd_categories,'blog_categories'=>$blog_categories,'badge_dtl'=>$badge_dtl,'package_dtl'=>$package_dtl]);
        }
    }
    
    function store(Request $request){ 

        $input = $request->all();
        /*$input['slider_ttl'] = $request->title[session('admin_default_lang')];*/

        $validate = $this->validateSlider($input);

        if ($validate->passes()) {

            $slider = new CmsSlider;
            $slider->type = $request->slider_type;
            $slider->name = createSlug($request->name);
            $slider->status = $request->status;
            $slider->design = 1;
            $slider->badge_id = !empty($request->badge)?implode(',', $request->badge):'';
            $slider->package_id = !empty($request->package)?implode(',', $request->package):'';
            if($request->slider_type == 'product' || $request->slider_type == 'blog'){
                $slider->design = ($request->slider_type == 'product')?$request->prd_design:$request->blog_design;
                $slider->slider_condition = $request->slider_con;
                $slider->show_slider = 'yes';
            }else{
                $slider->show_slider = $request->show_slider;
            }
        
            $slider->tot_item = $request->tot_item;
            if(isset($request->show_slider) && $request->show_slider == 'no'){
            	if($slider->design == 1){
            		$slider->show_slider = 'no';
            	}
            }

            if($request->slider_type == 'product' && $request->slider_con =='specific_level_2'){
                $slider->custom_id = cleanValue($request->custom_cat_id);
            }else{
                $slider->custom_id = '';
            }

            if($request->slider_type == 'blog' && $request->slider_con =='custom_id'){
                $slider->custom_id = cleanValue($request->custom_id);
                $slider->custom_sku = '';
            }

            if($request->slider_type == 'product' && $request->prd_design > 17){
                $slider->feature_sku = cleanValue($request->feature_sku);
            }
            $setting_slider = isset($request->setting_slider)?$request->setting_slider:'';
            /****making json for slider option*****/
            $option = ['item_per_slider'=>$request->item_per_slider,'cont_space_top'=>$request->cont_space_top,'cont_space_bottom'=>$request->cont_space_bottom,'thumb_space'=>$request->thumb_space,'sort_by'=>$request->sort_by,'sort_by_val'=>$request->sort_by_val,'setting_slider'=>$setting_slider];
            $slider->slider_option = json_encode($option);

            if($slider->design == '1'){
                
                $slider->container_width = isset($request->cont_width)?$request->cont_width:'';
            }else{

                $slider->container_width = '';
            }
            if($request->slider_type == 'product' || $request->slider_type == 'blog'){
                if(!empty($request->banner_image)){
                    $banner_image = $request->banner_image;
                    $banner_path = Config::get('constants.cms_slider_path').'/';
                    $banner_image_name = 'banner'.md5(microtime()).'.png';
                    $banner_image_data = $this->base64UploadImage($banner_image,$banner_path,$banner_image_name);
                    if($banner_image_data){
                        $slider->banner = $banner_image_name;
                    }
                }

                if(!empty($request->banner_mobile) && !empty($request->banner_image_mob)){
                    $banner_path = Config::get('constants.cms_slider_path').'/';
                    $banner_image_name = 'banner'.md5(microtime()).'.png';
                    $banner_image_data = $this->base64UploadImage($request->banner_image_mob,$banner_path,$banner_image_name);
                    if($banner_image_data){
                        $slider->banner_mob_image = $banner_image_name;
                    }
                }

                if(!empty($request->banner_url)){
                    $slider->banner_url = trim($request->banner_url);
                }
            }
            

            $slider->created_by = Auth::guard('admin_user')->user()->id;
            $slider->save();
            $slider_id = $slider->id;
            $data_arr = [];
            /***updating title****/
            foreach ($request->title as $key => $value) {
                if($value) {
                    $data_arr[] = ['cms_slider_id'=>$slider_id,'lang_id'=>$key,"title" => $value];
                }
            }

            if(count($data_arr)){
                CmsSliderDesc::insert($data_arr);
            }

            if($request->slider_con !='sku' && $request->slider_con !='custom_id'){
                if($request->slider_type == 'product' || $request->slider_type == 'category'){
                    $cat_id_arr = (isset($request->prd_cat_id) && count($request->prd_cat_id))?$request->prd_cat_id:[];
                }else{
                    $cat_id_arr = (isset($request->blog_cat_id) && count($request->blog_cat_id))?$request->blog_cat_id:[];
                }

                if(count($cat_id_arr) > 0) {
                    $blog_cat_id = [];
                    foreach ($cat_id_arr as $key => $value) {
                        $blog_cat_data[] = ['cms_slider_id'=>$slider_id, 'category_id'=>$value];
                    }                
                    CmsSliderCategory::insert($blog_cat_data);
                }
            }

            /***entry in block table**/
            $dataArr = ['type'=>'cms-slider','type_id'=>$slider_id,'updated_by'=>Auth::guard('admin_user')->user()->id,'status'=>$request->status];
            \App\Block::insertBlock($dataArr);

            /*update activity log start*/
            $action_type = "created"; 
            $module_name = "cms slider";            
            $logdetails = "Admin has created ".$request->title[session('admin_default_lang')]." $module_name";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/
            
            if($request->submit_type == 'submit_continue') {
                return redirect()->action('Admin\CmsSlider\CmsSliderController@edit', $slider_id)->with('succMsg', Lang::get('common.records_added_successfully'));
            }
            else {
                return redirect()->action('Admin\CmsSlider\CmsSliderController@index')->with('succMsg', Lang::get('common.records_added_successfully'));
            }
        } 
        else {
           
            return redirect()->action('Admin\CmsSlider\CmsSliderController@create')->withErrors($validate)->withInput();
        }                 
    }
    
    function edit($id) {
        $permission = $this->checkUrlPermission('edit_cms_slider');
        if($permission === true) {

            $slider_dtls = CmsSlider::getSliderbyId($id);
            if(!empty($slider_dtls)){
                $prd_categories = \App\Category::getMainCategory();
                $blog_categories = \App\BlogCategory::getMainCategory();
                $categoryId = [];
                if(isset($slider_dtls->sliderCat) && !empty($slider_dtls->sliderCat)) {
                    foreach ($slider_dtls->sliderCat as $value) {
                        $categoryId[] = $value->category_id;
                    }
                }
                $blogCategoryId = $categoryId;
                $option = ($slider_dtls->slider_option)?json_decode($slider_dtls->slider_option,true):'';
                $badge_id_arr = [];
                if($slider_dtls->badge_id){
                    $badge_id_arr = explode(',', $slider_dtls->badge_id);
                }
                $package_id_arr = [];
                if($slider_dtls->package_id){
                    $package_id_arr = explode(',', $slider_dtls->package_id);
                }
                $badge_dtl = \App\Badge::getBadge();
                $package_dtl = \App\Package::getAllPackage();
                return view('admin.cmsSlider.editSlider', ['slider_dtls'=>$slider_dtls, 'tblCmsSliderDesc'=>$this->tblCmsSliderDesc,'prd_categories'=>$prd_categories,'blog_categories'=>$blog_categories,'categoryId'=>$categoryId,'blogCategoryId'=>$blogCategoryId,'slider_opt'=>$option,'badge_dtl'=>$badge_dtl,'badge_id_arr'=>$badge_id_arr,'package_dtl'=>$package_dtl,'package_id_arr'=>$package_id_arr]);
            }
            
        }
    }
    
    function update(Request $request, $slider_id){
  
        $input = $request->all();
        /*$input['slider_ttl'] = $request->title[session('admin_default_lang')];*/     

        $validate = $this->validateSlider($input, $slider_id);      

        if ($validate->passes()) {
            $slider = CmsSlider::find($slider_id);
            $slider->type = $request->slider_type;
            $slider->name = createSlug($request->name);
            $slider->status = $request->status;
            $slider->badge_id = !empty($request->badge)?implode(',', $request->badge):'';
            $slider->package_id = !empty($request->package)?implode(',', $request->package):'';
            if($request->slider_type == 'product' || $request->slider_type == 'blog'){
                $slider->design = ($request->slider_type == 'product')?$request->prd_design:$request->blog_design;
                $slider->slider_condition = $request->slider_con;
                $slider->show_slider = 'yes';
            }else{
                $slider->show_slider = $request->show_slider;
                $slider->design = 1;
                $slider->slider_condition = '';
            }

            $slider->tot_item = $request->tot_item;
            if(isset($request->show_slider) && $request->show_slider == 'no'){
            	if($slider->design == 1){
            		$slider->show_slider = 'no';
            	}
            }
            if($request->slider_type == 'product' && $request->slider_con =='specific_level_2'){
                $slider->custom_id = cleanValue($request->custom_cat_id);
            }else{
                $slider->custom_id = '';
            }

            if($request->slider_type == 'blog' && $request->slider_con =='custom_id'){
                $slider->custom_id = cleanValue($request->custom_id);
                $slider->custom_sku = '';
            }

            if($request->slider_type == 'product' && $request->prd_design > 17){
                $slider->feature_sku = cleanValue($request->feature_sku);
            }
            $setting_slider = isset($request->setting_slider)?$request->setting_slider:'';

            /****making json for slider option*****/
            $option = ['item_per_slider'=>$request->item_per_slider,'cont_space_top'=>$request->cont_space_top,'cont_space_bottom'=>$request->cont_space_bottom,'thumb_space'=>$request->thumb_space,'sort_by'=>$request->sort_by,'sort_by_val'=>$request->sort_by_val,'setting_slider'=>$setting_slider];
            $slider->slider_option = json_encode($option);

            if($slider->design == '1'){
                
                if($slider->banner){
                    $banner_path = Config::get('constants.cms_slider_path').'/';

                    $this->fileDelete($banner_path.$slider->banner);
                    $slider->banner = '';
                }
                $slider->container_width = isset($request->cont_width)?$request->cont_width:'';
                $slider->banner_url = '';

            }else{
                $slider->container_width = '';
                $slider->banner_url = '';
                if(!empty($request->banner_url)){
                    $slider->banner_url = trim($request->banner_url);
                }
            }
            $banner_path = Config::get('constants.cms_slider_path').'/';
            if($request->slider_type == 'product' || $request->slider_type == 'blog'){
                if(!empty($request->banner_image)){
                    $banner_image = $request->banner_image;
                    $banner_image_name = 'banner'.md5(microtime()).'.png';
                    $banner_image_data = $this->base64UploadImage($banner_image,$banner_path,$banner_image_name);
                    if($slider->banner){
                        $this->fileDelete($banner_path.$slider->banner);
                    }
                    if($banner_image_data){
                        $slider->banner = $banner_image_name;
                    }
                }

                if(!empty($request->banner_mobile) && !empty($request->banner_image_mob)){
                    $banner_image_name = 'banner'.md5(microtime()).'.png';
                    $banner_image_data = $this->base64UploadImage($request->banner_image_mob,$banner_path,$banner_image_name);

                    if($slider->banner_mob_image){
                        $this->fileDelete($banner_path.$slider->banner_mob_image);
                    }
                    if($banner_image_data){
                        $slider->banner_mob_image = $banner_image_name;
                    }
                }
            }else{
                if($slider->banner){
                    $this->fileDelete($banner_path.$slider->banner);
                }
                if($slider->banner_mob_image){
                    $this->fileDelete($banner_path.$slider->banner_mob_image);
                }
            }

            $slider->updated_by = Auth::guard('admin_user')->user()->id;
            $slider->save();
            
            /***updating title****/
            foreach ($request->title as $key => $value) {
                if($value) {
                    $data_arr = ['cms_slider_id'=>$slider_id,'lang_id'=>$key,"title" => $value];

                    CmsSliderDesc::updateOrCreate(['cms_slider_id' => $slider_id, 'lang_id' => $key], $data_arr);
                }
                
            }

            /****updating category***/
            CmsSliderCategory::deleteCat($slider_id);

            if($request->slider_con !='sku' && $request->slider_con !='custom_id'){
                if($request->slider_type == 'product'  || $request->slider_type == 'category'){
                    $cat_id_arr = (isset($request->prd_cat_id) && !empty($request->prd_cat_id))?$request->prd_cat_id:[];
                }else{
                    $cat_id_arr = (isset($request->blog_cat_id) && !empty($request->blog_cat_id))?$request->blog_cat_id:[];
                }
                if(count($cat_id_arr) > 0) {
                    foreach ($cat_id_arr as $key => $value) {
                        $blog_cat_data[] = ['cms_slider_id'=>$slider_id, 'category_id'=>$value];
                    }                
                    CmsSliderCategory::insert($blog_cat_data);
                }
            }

            /****updating block table*****/
            $blockData = \App\Block::where(['type_id'=>$slider_id,'type'=>'cms-slider'])->first();
            if(!empty($blockData)){
                $blockData->status = $request->status;
                $blockData->save();
            }else{
                /***entry in block table if block not exist**/
                $dataArr = ['type'=>'cms-slider','type_id'=>$slider_id,'updated_by'=>Auth::guard('admin_user')->user()->id,'status'=>$request->status];
                \App\Block::insertBlock($dataArr);
            }

            /*update activity log start*/
            $action_type = "updated"; 
            $module_name = "cms slider";            
            $logdetails = "Admin has updated ".$request->title[session('admin_default_lang')]." $module_name";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

            return redirect()->action('Admin\CmsSlider\CmsSliderController@index')->with('succMsg', Lang::get('common.records_updated_successfully'));
        }
        else {

            return redirect()->action('Admin\CmsSlider\CmsSliderController@edit', $slider_id)->withErrors($validate)->withInput();
        }            
    }    
    
    function destroy($id){

        $permission = $this->checkUrlPermission('delete_cms_slider');
        if($permission === true) {
            $static_cms = CmsSlider::getSliderbyId($id);
            if(!empty($static_cms)){
                $namedesc = $static_cms->sliderdesc;
                $logname = !empty($namedesc)?$namedesc->title:$id;
                
                $banner_path = Config::get('constants.cms_slider_path').'/';
                if($static_cms->banner){
                    $this->fileDelete($banner_path.$static_cms->banner);
                }
                if($static_cms->banner_mob_image){
                    $this->fileDelete($banner_path.$static_cms->banner_mob_image);
                }

                CmsSlider::where('id', $id)->delete();

                \App\Block::where(['type_id'=>$id,'type'=>'cms-slider'])->delete();
                /*update activity log start*/
                $action_type = "deleted"; 
                $module_name = "cms slider";            
                $logdetails = "Admin has deleted $logname ";
                $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

                $this->updateLogActivity($logdata);
                /*update activity log End*/

                return redirect()->action('Admin\CmsSlider\CmsSliderController@index')->with('succMsg', Lang::get('common.records_deleted_successfully'));
            }else{
                return redirect()->action('Admin\CmsSlider\CmsSliderController@index')->with('errorMsg', Lang::get('common.error'));
            }
            
        }
    }

    function changeStatus($id) {

        $static_cms = CmsSlider::getSliderbyId($id);

        if($static_cms->status == '1') {
            $status = '0';
            $status_msg = 'Inactive';
        }
        else {
            $status = '1';
            $status_msg = 'Active';            
        }

        $static_cms->status = $status;
        $static_cms->updated_at = date('Y-m-d H:i:s');
        $static_cms->updated_by = Auth::guard('admin_user')->user()->id;

        $static_cms->save();

        $blockData = \App\Block::where(['type_id'=>$static_cms->id,'type'=>'cms-slider'])->first();
        if(!empty($blockData)){
            $blockData->status = $static_cms->status;
            $blockData->save();
        }

        $namedesc = $static_cms->sliderdesc;
        $logname = !empty($namedesc)?$namedesc->title:$id;

        /*update activity log start*/
        $action_type = "updated"; 
        $module_name = "cms slider";            
        $logdetails = "Admin has updated $logname status to $status_msg ";
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/

        return $status_msg;
    }

    private function validateSlider($input, $cms_id='') {

        $rules['name'] = 'Required';      
        //$rules['slider_ttl'] = 'Required|Min:3';         

        $validate = Validator::make($input, $rules);
        return $validate; 
    }    
}
