<?php
namespace App\Http\Controllers\Admin\Block;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;

use App\Language;
use App\Block;
use App\BlockDesc;
use App\Section;
use Auth;

class BlockController extends MarketPlace
{ 
    private $tblBlock;
    private $tblBlockDesc;
    public function __construct()
    {
        $this->middleware('admin.user');

        $this->tblBlock = with(new Block)->getTable();
        $this->tblBlockDesc = with(new BlockDesc)->getTable();
    }
    
    public function index(){

        $permission = $this->checkUrlPermission('list_block');
        if($permission === true) {
            $permission_arr['add'] = $this->checkMenuPermission('add_block');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_block');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_block');

            //$section = Section::whereNotIn('id', [2])->get();
            $section = Section::get();

            $block_list = Block::select('id','section_id','is_fix','type_id','type','pages')
                            //->whereNotIn('section_id', [2])
                            ->where('status','1')
                            ->orderBy('order_by')
                            ->with('staticBlockDesc')
                            ->with('blockPage')
                            ->get();
            
            $data_arr = array();
            $block_page_arr = [];
            foreach ($block_list as $key => $value) {
                $page_url = ($value->pages!=1 && $value->blockPage)?$value->blockPage->page_url:'';
                $block_page_arr[$value->id] = ['pages'=>$value->pages,'page_url'=>$page_url];
                if($value->type == 'static-block'){
                    $block_list[$key]->title = ($value->staticBlockDesc) ? $value->staticBlockDesc->page_title : '';
                }elseif($value->type == 'banner'){
                    $block_list[$key]->title = ($value->bannerGroup)?$value->bannerGroup->group_name:'' ;
                }elseif($value->type == 'cms-slider'){

                    $slider_data = \App\CmsSlider::where('id',$value->type_id)->select('name','type')->first() ;
                    if(!empty($slider_data)){
                        $block_list[$key]->title = $slider_data->name;
                        $block_list[$key]->slider_type = $slider_data->type;
                    }
                }

                $data_arr[$value->section_id][] = $value; 
            }

            foreach ($section as $key => $value) {
                $section[$key]->sec_name = str_replace('-',' ',ucfirst($value->section_name));
                if(isset($data_arr[$value->id])){
                    $section[$key]->block_list = $data_arr[$value->id];
                }
            }
            //dd($section->toArray(),$data_arr);

            return view('admin.block.blockList', ['section'=>$section, 'permission_arr'=>$permission_arr,'block_disable'=>$data_arr,'block_page_arr'=>$block_page_arr]);
        }
    }

    public function updateBlockSection(Request $request){
        //dd($request->all());
        if(isset($request->data)){
            foreach ($request->data as $key => $result) {
                $section_id = str_replace('sec_', '', $result['name']);
                if(count($result['value'])){
                    $i = 1;
                    foreach ($result['value'] as $vkey => $value) {
                       Block::where(['id'=>$value])->update(['section_id'=>$section_id,'order_by'=>$i,'updated_by'=>Auth::guard('admin_user')->user()->id,'updated_at'=>date('Y-m-d H:i:s')]);
                    $i++;
                    }
                }
               
            }
            /*update activity log start*/
            $action_type = "updated"; 
            $module_name = "layout management";            
            $logdetails = "Admin has updated ".$module_name;
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/
            echo \Lang::get('admin.block_section_updated successfully');
        }
    }
    
    public function create(){

        $permission = $this->checkUrlPermission('create_admin_cms');
        if($permission === true) {
            $customer_group = \App\CustomerGroup::getCustomerGroup();
            $section = Section::getAll();
            $lang_lists = Language::getLangugeDetails();
            return view('admin.block.blockCreate', ['lang_lists'=>$lang_lists,'section'=>$section,'customer_group'=>$customer_group]);
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
      
        $permission = $this->checkUrlPermission('edit_block');
        if($permission === true) {

            $block_detail = Block::where('id',$id)->with('blockPage')->with(['blockCustGroup','staticBlockDesc'])->first();
            //dd($block_detail);
            if(!empty($block_detail)){
                $section = Section::whereNotIn('id', [1,2])->get();
                $customer_group = \App\CustomerGroup::getCustomerGroup();
                $group_id_arr = !empty($block_detail->blockCustGroup) ? explode(',',$block_detail->blockCustGroup->customer_group_id) : [];

                if(isset($block_detail->blockPage) && !empty($block_detail->blockPage->page_url)){
                    $page_arr = explode(',', $block_detail->blockPage->page_url);
                }else{
                    $page_arr = [];
                }

                $seo_pages = \App\SeoPage::select(['name','url'])->get()->toArray();
                if(count($seo_pages) > 0) {
                    foreach ($seo_pages as $key => $value) {
                        if($value['url'] == '/') {
                            $seo_pages[$key]['url'] = 'home';
                        }
                    }
                }

                return view('admin.block.blockEdit', ['detail'=>$block_detail,'section'=>$section,'customer_group'=>$customer_group,'group_id_arr'=>$group_id_arr,'seo_pages'=>$seo_pages,'page_arr'=>$page_arr]);
            }else{
                abort(404);
            }
        }
    }
    
    function update(Request $request, $block_id){
        
        //echo '<pre>';dd($request->all(),strtotime($request->start_date));die;   
            $isFix = isset($request->fixed) ? '1' : '0';
            $section_id = ($request->section) ? $request->section : 0;
            $strdate = (strtotime($request->start_date)>0)?date('Y-m-d H:i:s',strtotime($request->start_date)):null;
            $enddate = (strtotime($request->end_date)>0)?date('Y-m-d H:i:s',strtotime($request->end_date)):null;
            $blockdet = Block::find($block_id);
            if(empty($blockdet)){
                return redirect()->action('Admin\Block\BlockController@index')->with('errorMsg', 'error');
            }
            Block::where(['id'=>$block_id])->update(['is_fix'=>$isFix,'section_id'=>$section_id,'customer_group'=>$request->group,'pages'=>$request->pages,'updated_by'=>Auth::guard('admin_user')->user()->id,'updated_at'=>date('Y-m-d H:i:s'),'allow_ip'=>$request->allow_ip,'start_date'=>$strdate,'end_date'=>$enddate]);      
            if($request->group != '1' && !empty($request->group_id)){
                \App\BlockCustomerGroup::updateBlockGroup($request->group_id,$block_id);
            }else{
                \App\BlockCustomerGroup::where('block_id', $block_id)->delete();
            }

            if($request->pages != '1' && !empty($request->page_url)){
                $bpage_url = implode(',', $request->page_url);
                \App\BlockPage::updateBlockPage($bpage_url,$block_id);
            }else{
                \App\BlockPage::where('block_id', $block_id)->delete();
            }

            /*update activity log start*/
            if($blockdet->type == 'banner'){
                $name = \App\BannerGroup::where('id',$blockdet->type_id)->value('group_name');
            }else{
                $name = \App\StaticBlockDesc::where(['static_block_id'=>$blockdet->type_id,'lang_id'=>session('default_lang')])->value('page_title');
            }
            $action_type = "updated"; 
            $module_name = "layout management";            
            $logdetails = "Admin has updated $name";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

            return redirect()->action('Admin\Block\BlockController@edit', $block_id)->with('succMsg', 'Records Updated Successfully!');
           
    }    
    
    function delectBlock(Request $request){

        if(isset($request->id) && $request->id > 0){
            $block = Block::find($request->id);
            if(!empty($block)){
                //Block::where('id', $request->id)->delete();
                Block::where(['id'=>$block->id])->update(['section_id'=>0, 'updated_by'=>Auth::guard('admin_user')->user()->id,'updated_at'=>date('Y-m-d H:i:s')]);

                /*update activity log start*/
                if($block->type == 'banner'){
                    $name = \App\BannerGroup::where('id',$block->type_id)->value('group_name');
                }else{
                    $name = \App\StaticBlockDesc::where(['static_block_id'=>$block->type_id,'lang_id'=>session('default_lang')])->value('page_title');
                }
                $action_type = "updated"; 
                $module_name = "layout management";            
                $logdetails = "Admin has removed $name from section";
                $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

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

        //echo '====>'.$id;die;

        AdminBlock::where('id', $id)->delete();
        return redirect()->action('AdminBlockController@index')->with('succMsg', 'Record Deleted Successfully!');
    }

    function previewBlock(Request $request){

       // dd($request->all());
        $data = '';
        $block_detail = Block::where('id',$request->id)->select('type','type_id')->first();
        switch ($block_detail->type) {
            case 'static-block':
                $data .= \App\StaticBlockDesc::where(['static_block_id'=>$block_detail->type_id,'lang_id'=>session('default_lang')])->value('page_desc');
                break;
            case 'banner':
                $qry = \App\Banner::where(['group_id'=>$block_detail->type_id])->select('banner_image')->get();
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
