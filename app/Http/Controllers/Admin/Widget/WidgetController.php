<?php
namespace App\Http\Controllers\Admin\Widget;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;

use App\Language;
use App\Widget;
use App\WidgetSection;
use Auth;

class WidgetController extends MarketPlace
{ 
    private $tblWidget;    
    public function __construct()
    {
        $this->middleware('admin.user');
        $this->tblWidget = with(new Widget)->getTable();        
    }
    
    public function index(){

        $permission = $this->checkUrlPermission('list_widget');
        if($permission === true) {
            $permission_arr['add'] = $this->checkMenuPermission('add_widget');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_widget');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_widget');

            $data_arr = array();
            $section = WidgetSection::whereNotIn('id', [1,5])->get();

            $block_list = Widget::select('id','section_id','is_fix','type_name','type')
                            ->whereNotIn('section_id', [1,5])
                            ->where('status','1') 
                            ->orderBy('order_by')                            
                            ->get();
            
            foreach ($block_list as $key => $value) {
                if($value->type == 'widget'){
                    $block_list[$key]->title = ($value->staticBlockDesc) ? $value->staticBlockDesc->page_title : '';
                }elseif($value->type == 'banner'){
                    $block_list[$key]->title = $value->bannerGroup->group_name ;
                } 
            }
            
            foreach ($block_list as $key => $value) {
                $data_arr[$value->section_id][] = $value;
            }
            
            foreach ($section as $key => $value) {
                $section[$key]->sec_name = str_replace('-',' ',ucfirst($value->section_name));
                if(isset($data_arr[$value->id])){
                    $section[$key]->block_list = $data_arr[$value->id];
                }
            }
            
            return view('admin.widget.widgetList', ['section'=>$section, 'permission_arr'=>$permission_arr,'widget_disable'=>$data_arr]);
        }
    }

    public function updateWidgetSection(Request $request){
        
        if(isset($request->data)){
            foreach ($request->data as $key => $result) {
                $section_id = str_replace('sec_', '', $result['name']);
                if(count($result['value'])){
                    $i = 1;
                    foreach ($result['value'] as $vkey => $value) {
                       Widget::where(['id'=>$value])->update(['section_id'=>$section_id,'order_by'=>$i,'updated_by'=>Auth::guard('admin_user')->user()->id,'updated_at'=>date('Y-m-d H:i:s')]);
                    $i++;
                    }
                }
               
            }
            /*update activity log start*/
            $action_type = "updated"; 
            $module_name = "blogwidget";            
            $logdetails = "Admin has updated widget order";
            $old_data = "";
            $new_data = "";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );
            $this->updateLogActivity($logdata);
            /*update activity log End*/ 
            echo \Lang::get('admin.block_section_updated successfully');
        }
    }
    
    public function create(){

        $permission = $this->checkUrlPermission('create_admin_cms');
        if($permission === true) {
            $customer_group = \App\CustomerGroup::getCustomerGroup();
            $section = WidgetSection::getAll();
            $lang_lists = Language::getLangugeDetails();
            return view('admin.widget.widgetCreate', ['lang_lists'=>$lang_lists,'section'=>$section,'customer_group'=>$customer_group]);
        }
    }
    
    function store(Request $request){                  
    }

    public function filterBlockData($request) {

        $def_lang = session('default_lang');
        $def_title = $request->title[$def_lang];
        $def_desc = $request->desc[$def_lang];
        $def_heading = $request->heading[$def_lang];
        foreach ($request->desc as $key=>$value){

            $page_title = $request->title[$key];
            $page_desc = $request->desc[$key];
            $page_heading = $request->heading[$key];
            if(empty($page_title)) {
                $page_title = $def_title;
            }
            if(empty($page_desc)) {
                $page_desc = $def_desc;
            } 
            if(empty($page_heading)) {
                $page_heading = $def_heading;
            }

            $data_arr[$key] = array('title'=>cleanValue($page_title), 'desc'=>cleanValue($page_desc), 'heading'=>cleanValue($page_heading));
        } 
        
        return $data_arr;       
    }

    public function show($id) {
    }
    
    function edit(Request $request,$id) {
      
        $permission = $this->checkUrlPermission('edit_widget');
        if($permission === true) {

            $block_detail = Widget::where('id',$id)->first();
                       
            $section = WidgetSection::whereNotIn('id', [1,5])->get();

            $customer_group = \App\CustomerGroup::getCustomerGroup();
            $group_id_arr = !empty($block_detail->blockCustGroup) ? explode(',',$block_detail->blockCustGroup->customer_group_id) : [];
            
            if(isset($block_detail->blockPage) && !empty($block_detail->blockPage->page_url)){
                $page_arr = explode(',', $block_detail->blockPage->page_url);
            }else{
                $page_arr = [];
            }

            $seo_pages = \App\SeoPage::select(['name','url'])->whereIn('id', [11,12,22,23,25,26,27])->get()->toArray();
            if(!empty($seo_pages)) {
                foreach ($seo_pages as $key => $value) {
                    if($value['url'] == '/') {
                        $seo_pages[$key]['url'] = 'home';
                    }
                }
            }            

            return view('admin.widget.widgetEdit', ['detail'=>$block_detail,'section'=>$section,'customer_group'=>$customer_group,'group_id_arr'=>$group_id_arr,'seo_pages'=>$seo_pages,'page_arr'=>$page_arr]);
        }
    }
    
    function update(Request $request, $widget_id){            
  
            $isFix = isset($request->fixed) ? '1' : '0';
            $section_id = ($request->section) ? $request->section : 0;

            Widget::where(['id'=>$widget_id])->update(['is_fix'=>$isFix,'section_id'=>$section_id,'customer_group'=>$request->group,'pages'=>$request->pages,'updated_by'=>Auth::guard('admin_user')->user()->id,'updated_at'=>date('Y-m-d H:i:s')]);      

            if($request->group != '1' && count($request->group_id)){                
                \App\WidgetCustomerGroup::updateBlockGroup($request->group_id,$widget_id);

            }else{
                \App\WidgetCustomerGroup::where('widget_id', $widget_id)->delete();
            }

            if($request->pages != '1' && count($request->page_url) >0){
                $bpage_url = implode(',', $request->page_url);
                \App\WidgetPage::updateBlockPage($bpage_url,$widget_id);
            }else{
                \App\WidgetPage::where('widget_id', $widget_id)->delete();
            }

            if(!empty($detail->blockPage)){
                $block_page = explode(',', $detail->blockPage->page_url);
            }else{
                $block_page = [];
            }

            /*update activity log start*/
            $action_type = "updated"; 
            $module_name = "blogwidget";            
            $logdetails = "Admin has updated ".$widget_id." blog widget";
            $old_data = "";
            $new_data = "";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );
            $this->updateLogActivity($logdata);
            /*update activity log End*/ 

            return redirect()->action('Admin\Widget\WidgetController@edit', $widget_id)->with('succMsg', 'Records Updated Successfully!');
           
    }    
    
    function delectWidget(Request $request){

        if(isset($request->id) && $request->id > 0){
            $block = Widget::find($request->id);
            if(!empty($block)){
                //Block::where('id', $request->id)->delete();
                Widget::where(['id'=>$block->id])->update(['section_id'=>0, 'updated_by'=>Auth::guard('admin_user')->user()->id,'updated_at'=>date('Y-m-d H:i:s')]);
                /*update activity log start*/
                $action_type = "deleted"; 
                $module_name = "blogwidget";            
                $logdetails = "Admin has deleted blog widget id ".$request->id;
                $old_data = "";
                $new_data = "";
                $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );
                $this->updateLogActivity($logdata);
                /*update activity log End*/ 
                echo \Lang::get('admin.block_deleted_successfully');                
            }else{
                echo 'error';
            }
        }else{
            echo 'error';
        }
    }

    function destroy($id){

        AdminBlock::where('id', $id)->delete();
        return redirect()->action('AdminBlockController@index')->with('succMsg', 'Record Deleted Successfully!');
    }

    function previewWidget(Request $request){
       
        $data = '';
        $block_detail = Widget::where('id',$request->id)->select('type','type_name')->first();
        switch ($block_detail->type) {
            case 'widget':
                $data .= \App\StaticBlockDesc::where(['static_block_id'=>$block_detail->type_name,'lang_id'=>session('default_lang')])->value('page_desc');
                break;
            case 'banner':
                $qry = \App\Banner::where(['group_id'=>$block_detail->type_name])->select('banner_image')->get();
                if(count($qry)){
                    foreach ($qry as $key => $value) {
                       $data .='<img src="'.\Config::get('constants.banner_url').$value->banner_image.'" width="100" height="100">';
                    }
                }
                break;
            default:                
                break;
        }
        return $data;
    }    
}
