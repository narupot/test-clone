<?php

namespace App\Http\Controllers\Admin\Menu;

use App\Http\Controllers\MarketPlace;
use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Http\Controllers\AESEncription;

use App\Language;
use App\MegaMenu;

use App\Category;
use App\Block;
use App\StaticBlock;
use App\Product;

use App\MenuItems;
use App\MenuItemsDesc;

use Auth;
use Validator;
use Config;
use Session;
use Lang;

class MenuController extends MarketPlace {

    public $Language;
    public $lang_id;
    public $lang_code;
    public $siteurl;
    public $pages;
    public $itemLink;
    public $category;
    public $server_url;
    public $client_key;
    public $setting_url;
    public $licence_key;

    public function __construct() {

        $this->middleware('admin.user');
       // dd(session('admin_default_lang'));
        ///if(session('admin_default_lang') > 0) {
          
          $this->Language = Language::where('isDefault', '1')->first();
          //$this->lang_id=$this->Language->id;
          $this->lang_id=session('admin_default_lang');
          $langData = Language::where('id', $this->lang_id)->first();
          $this->lang_code = $langData->languageCode;
          $this->siteurl='/'.$this->lang_code; 
          
          //$this->server_url = $this->getConfigurationValue('API_SERVER');
          //$this->client_key = $this->getConfigurationValue('PLUGIN_PUBLIC_KEY');
          //$this->setting_url = $this->server_url."/en/clientConfig";
          //$this->licence_key = $this->getConfigurationValue('LICENCE_KEY');

          //Make Global Json
          $this->pages=array("id"=>'',
            "menu_type_id"=>1,
            "title"=>"Pages",  
            "menu_icon"=>'link',
            "default_lang_id"=>$this->lang_id,
            "atr_menu_icon"=>"",
            "atr_link_input"=> "", 
            "atrcustomcss"=>"",
            "icon_show" => "none",
          );

          $this->itemLink=array(
            "id"=>'',
            "menu_type_id"=>3,
            "title"=>"Item Link",
            "menu_icon"=>'folder',
            "atr_menu_icon"=>"",
            "atr_link_input"=> "", 
            "atrcustomcss"=>"",      
            "default_lang_id"=>$this->lang_id,
            "icon_show" => "none",
          );
          
          $this->category=array(
            "id"=>'',
            "menu_type_id"=>6,
            "title"=>"Category",
            "menu_icon"=>'tasks',
            "atr_menu_icon"    =>"",
            "default_lang_id"=>$this->lang_id,
            "atr_link_input"=> "", 
            "atrcustomcss"=>"",
            "icon_show" => "none",
          ); 
        //}    
    }

    public function index() {

        $menu_data = $this->menulisting(10);

        $permission_arr['edit'] = $this->checkMenuPermission('list_menu');
        
        return view('admin.menu.index', ['menu_data'=>$menu_data,'permission_arr'=>$permission_arr]);
    }

    public function menulisting($request) {
        //commented by rajeev 18-apr-2018
        $perpage = !empty($request) ? $request : 10;
        $results = MegaMenu::with('block')->orderBy('id','DESC')->paginate($perpage);
        foreach ($results as $key => $result){
            $blockName=''; 
            $results[$key]->id = $result->id;
            $results[$key]->title = $result->title;
            $results[$key]->is_default = ($result->is_default==1)?'Yes':'No';
            $results[$key]->created_date = getDateFormat($result->created_at, '1');  
            $results[$key]->updated_date = getDateFormat($result->updated_at, '1');
            $results[$key]->status = ($result->status==1)?'Active':'Inactive';
            $results[$key]->edit_url = action('Admin\Menu\MenuController@edit',$result->id);
        }
        return $results;
    }

    public function getList(Request $request) {
        $menu= new MegaMenu;
        try {
            $data=$menu->get();
            return response()->json($data);
            //$res="[{status:'success',error:0}]";
            //echo $res;die;
        } catch (QueryException $ex) {
            echo $ex->getMessage();
        }          
    }

    public function saveMultilungualText($data){
          $langArr=Language::getLangugeDetails();
          foreach($langArr as $langCode){
            $langCodearr[]=$langCode->languageCode;
          }
        foreach($data as $k=>$typeObj){
            if(array_key_exists('enTitle', $typeObj)){
              $defaultStr=$typeObj['enTitle']['input'];
              foreach($langCodearr as $lng){
                if($typeObj[$lng.'Title']['input']==''){
                  $data[$k][$lng.'Title']['input']=$defaultStr;
                }
              }
            }
            if(!empty($typeObj['nodes'])){
               return $this->saveMultilungualText($typeObj['nodes']);
            }else{
              return $data;    
            }
          }
          
    }

    public function storeMenuItem($menu_id, $menu_items, $parent_id=0){
      //MenuItems // MenuItemsDesc;
      if(count($menu_items)>0){ 
        $menu_order = 1; 
        foreach ($menu_items as $key => $item){
            $mt_obj = new MenuItems;
            $mt_obj->menu_id = $menu_id;
            $mt_obj->menu_type_id = $item['menu_type_id'];
            $mt_obj->menu_type = $item['title'];
            $mt_obj->menu_icon = $item['menu_icon'];
            $mt_obj->atr_menu_icon = $item['atr_menu_icon'];
            $mt_obj->icon_show = $item['icon_show'];
            $assoc_item_id = 0;
            if($item['title'] == 'Category'){
              $assoc_item_id = $item['category_id'];
            }
            if($item['title'] == 'Pages'){
              $assoc_item_id = $item['page_id'];
            }
            
            $mt_obj->assoc_item_id = $assoc_item_id;
            //$mt_obj->url = $item['atr_link_input'];
            $mt_obj->custom_class = $item['atrcustomcss'];
            $mt_obj->parent_id = $parent_id;
            $mt_obj->menu_order = $menu_order;
            $mt_obj->save();
            $menu_item_id = $mt_obj->id;
            foreach ($item['lang'] as $key1 => $value) {
               $mts_obj = new MenuItemsDesc;
               $mts_obj->menu_item_id = $menu_item_id;
               $mts_obj->title = $value['input'];
               $mts_obj->lang_id = $value['id'];
               $mts_obj->url = $value['atr_link_input'];
               $mts_obj->save();
            }  
            if(count($item['nodes'])>0){
               $this->storeMenuItem($menu_id, $item['nodes'], $menu_item_id);
            }
          $menu_order++;  
        }
      }  
    }

    private function _getMessage($msg,$flag=false){
      return array('success'=>$flag,'message'=>$msg);
    }

    public function getMenu($type= Null,Request $request) {
      if($request->id){
        $id=$request->id;
      }else{
        $id=0;
      }

      $menu= new MegaMenu;
        try {
            if($id>0){
              $menuData = MegaMenu::where('id', '=', $id)->get();
              foreach($menuData as $val){
                $menuList['id']=$val->id;
                $menuList['decription']=$val->description;
                $menuList['menu_json']=json_decode($val->menu_json);
                $menuList['title']=$val->title;
                $menuList['is_default_block']=$val->is_default_block;
                $menuList['wrapper_id']=strtolower($val->wrapper_id);
                $menuList['wrapper_css_class']=$val->wrapper_css_class;
                $menuList['dropdown_animation']=$val->dropdown_animation;
                $menuList['dropdown_style']=$val->dropdown_style;
                $menuList['block_id']=$val->block_id;
                $menuList['block_name']=$val->block_name;
                $menuList['status']=$val->status;
              }
              $menuJson=$menuList;

            }else{
              $menuData=$menu->get();
              foreach($menuData as $val){
                $menuList[]=$val->menu_json;
              }
              $menuJson=end($menuList);
            }
            return response()->json($menuJson);
        } catch (QueryException $ex) {
            // echo $ex->getMessage();
            return redirect()->action('Admin\Attribute\AttributeController@edit', $id)->with('message', 'Whoops, looks like something went wrong.');
        } 

    }

    function imagesList(Request $request,$type=null){

      if($request->type){
          $type=$request->type;
      }

      //$APPURLSERVER=env('APP_URL_SERVER');DIE;
      $SITEURL=$this->siteurl;
      define('IMAGEPATH', 'images/');
      $dirs = array_filter(glob('images/*'), 'is_dir');
      // print_r($imag);
      
      if($type=='images'){
        foreach(glob(IMAGEPATH.'*') as $filename){
            $imag[] =  $SITEURL.IMAGEPATH.basename($filename);
        }
      }

      if($type=='product'){
        //Get All the Images From product
        define('IMAGEPATH1', 'images/product/');
        foreach(glob(IMAGEPATH1.'*') as $filename){
            $imag[] =  $SITEURL.IMAGEPATH1.basename($filename);
        }        
      }

     if($type=='staticpages'){
        //Get All the Images From Static Pages
        define('IMAGEPATH2', 'images/static-pages/');
        foreach(glob(IMAGEPATH2.'*') as $filename){
            $imag[] =  $SITEURL.IMAGEPATH2.basename($filename);
        }   
     }  

      $returnData=array('status'=>'success','list'=>$imag);
        return response()->json($returnData);
    }


    public function blocklist(){
      // Get Block List
       $block_list = Block::select('id','section_id','is_fix','type_id','type')->orderBy('order_by')->with('staticBlockDesc')->get();
       //dd($block_list);
            foreach ($block_list as $key => $value) {
                if($value->type == 'static-block'){
                    $block_list[$key]->title = ($value->staticBlockDesc) ? $value->staticBlockDesc->page_title : '';
                }elseif($value->type == 'banner'){
                    $block_list[$key]->title = $value->bannerGroup->group_name ;
                }
                
            }
            // dd(json_encode($block_list->toArray()));
            $returnData=array('status'=>'success','list'=>$block_list->toArray());
            return response()->json($returnData);

    }

    public function getTypeList(Request $request){
      
      //Getting All Product Category List
      $returnData=array('status'=>'error');
      if($request->type==1){
         $category=$this->getCategorieslist();
         $returnData=array('status'=>'success','list'=>$category);
      }

      //Getting All Blog Category List //Getting All System Page Link //Getting All Page Link ////Getting All Blog Link
      if($request->type==2 || $request->type==3 || $request->type==4 || $request->type==6){
         $pageList=$this->getPageList();
         $returnData=array('status'=>'success','list'=>$pageList);
      }

      return response()->json($returnData);
    }

    public function messages() {
       return [

            'name.required' => Lang::get('admin.please_enter_menu_name'),
            'name.unique' => Lang::get('admin.menu_already_exists')
        ];
    }

    public function edit($id){

        $data = [];
        $result = MegaMenu::where('id', $id)->first();
        if(!$result){
            abort(404);
        }
        $data['menu_name'] = $result->name;
        $data['status'] = $result->status;
        
        foreach($result->getMenuItems as $key=>$item){  
            $data['menu_json'][$key]['id'] = $item->id;
            $data['menu_json'][$key]['menu_type_id'] = $item->menu_type_id;
            $data['menu_json'][$key]['title'] = $item->menu_type;
            $data['menu_json'][$key]['menu_icon'] = $item->menu_icon;
            $data['menu_json'][$key]['atr_menu_icon'] = $item->atr_menu_icon;
            //$data['menu_json'][$key]['atr_link_input'] = $item->url;
            $data['menu_json'][$key]['icon_show'] = $item->icon_show;
            $data['menu_json'][$key]['atrcustomcss'] = $item->custom_class;
            if($item->menu_type == 'Category'){
                $data['menu_json'][$key]['category_id'] = $item->assoc_item_id;
            }
            if($item->menu_type == 'Pages'){
                $data['menu_json'][$key]['page_id'] = $item->assoc_item_id;
            }
            foreach($item->getMenuItemsDesc as $key1=> $itemdesc){
                 //dd($itemdesc->getLangData);
                 $data['menu_json'][$key]['lang'][$key1]['code'] = isset($itemdesc->getLangData->languageCode)?$itemdesc->getLangData->languageCode:'';
                 $data['menu_json'][$key]['lang'][$key1]['id'] = $itemdesc->lang_id; 
                 $data['menu_json'][$key]['lang'][$key1]['flag'] = isset($itemdesc->getLangData->languageCode)?Config::get('constants.language_url').$itemdesc->getLangData->languageFlag:'';
                 $data['menu_json'][$key]['lang'][$key1]['title'] = 'Menu item title';
                 $data['menu_json'][$key]['lang'][$key1]['input'] = $itemdesc->title;
                 $data['menu_json'][$key]['lang'][$key1]['atr_link_input'] = $itemdesc->url;
                
            }
            $data['menu_json'][$key]['nodes'] = $this->editSubMenudata($item->id);
          }
        
        $menu_json = '';
        if(count($data)>0){
           $menu_json = json_encode($data);
        }

        $langCodeArray=$this->getLanguges();
        //Make Global Json
        $pages=$this->pages;
        $itemLink=$this->itemLink;
        $category= $this->category;
        //For Language     
        $category['lang'] = array();
        $pages['lang'] = array();
        $itemLink['lang'] = array();
        
        //dd($langCodeArray);
        foreach($langCodeArray as $lng){        
            $lng['title']='Menu item title';
            $lng['input']='';
            $category['lang'][] = ['code'=> $lng['code'], 'id' =>$lng['id'], 'flag' => $lng['flag'], 'title'=>'Menu item title', 'input' => ''];
            $pages['lang'] = $category['lang'];
            $itemLink['lang'] = $category['lang'];
        }

        //dd($category, $pages,  $itemLink);

        $category["nodes"]=array();
        $pages["nodes"]=array();
        $itemLink["nodes"]=array(); 

        $globalTree=array(
          $pages,
          $category,
          $itemLink,
        );
        
        return view('admin.menu.edit', [
           'globalTree'=>json_encode($globalTree),
            'menu_json' =>$menu_json,
            'lang_code' => $this->lang_code,
            'id'=>$id      
        ]);
        
    }

    public function editSubMenudata($id) {
        $data = [];
        $results = MenuItems::where('parent_id', $id)->orderBy('menu_order','asc')->get();
        //dd($result->getMenuItems);
        if(empty($results)){
          return $data; 
        }
        foreach($results as $key=>$item){  
            $data[$key]['id'] = $item->id;
            $data[$key]['menu_type_id'] = $item->menu_type_id;
            $data[$key]['title'] = $item->menu_type;
            $data[$key]['menu_icon'] = $item->menu_icon;
            $data[$key]['atr_menu_icon'] = $item->atr_menu_icon;
            //$data[$key]['atr_link_input'] = $item->url;
            $data[$key]['icon_show'] = $item->icon_show;
            $data[$key]['atrcustomcss'] = $item->custom_class;
            if($item->menu_type == 'Category'){
                $data[$key]['category_id'] = $item->assoc_item_id;
            }
            if($item->menu_type == 'Pages'){
                $data[$key]['page_id'] = $item->assoc_item_id;
            }
            foreach($item->getMenuItemsDesc as $key1=> $itemdesc){
                $data[$key]['lang'][$key1]['code'] = isset($itemdesc->getLangData->languageCode)?$itemdesc->getLangData->languageCode:'';
                $data[$key]['lang'][$key1]['id'] = $itemdesc->lang_id; 
                $data[$key]['lang'][$key1]['flag'] = isset($itemdesc->getLangData->languageFlag)?Config::get('constants.language_url').$itemdesc->getLangData->languageFlag:'';
                $data[$key]['lang'][$key1]['title'] = 'Menu item title';
                $data[$key]['lang'][$key1]['input'] = $itemdesc->title;
                $data[$key]['lang'][$key1]['atr_link_input'] = $itemdesc->url;
                
            }
            $data[$key]['nodes'] = $this->editSubMenudata($item->id);

        } 

        if(count($data)>0){
           return $data;  
        }
    }
    
    public function update($id, Request $request){
        
        $user_id = Auth::guard('admin_user')->user()->id;
        $menu_slug = str_slug($request->menu_name);
        $menu_name = $request->menu_name;
        $status = $request->status;
        $rules = ['name' => ['required',Rule::unique('megamenu')->ignore($id, 'id')]];      
        $input_array= [
              'name'=>$menu_name
        ];
        
        $validator = Validator::make($input_array, $rules, $this->messages());
        if ($validator->fails())
        {
           return response()->json(['section'=>'menu','status'=>'error','error'=>$validator->errors()->all()]);
        }
         
        try{           
            $m_obj = MegaMenu::where('id',$id)->first();
            $m_obj->slug = $menu_slug;
            $m_obj->name = $menu_name;
            $m_obj->updated_by = $user_id;
            $m_obj->status = $status;
            $m_obj->save();
            $menu_id = $m_obj->id;
            $menu_items = $request->menu_json;
            $used_menu_item_id = $this->EditMenuItem($menu_id,$menu_items);

            //dd($used_menu_item_id);
            MenuItems::where('menu_id',$menu_id)->whereNotIn('id', $used_menu_item_id)->delete();
            return ['status'=>'success','mesg'=>'Menu Successfully updated', 'actionUrl'=>action('Admin\Menu\MenuController@index')];
        }catch(\Excetion $ex){
            return ['status'=>'unsuccess','mesg'=>$ex->getMessage()];

        }
    } 

    public function EditMenuItem($menu_id, $menu_items, $parent_id=0, $current_item_id = []){
      //MenuItems // MenuItemsDesc
      //dd($menu_items);
      if(count($menu_items)>0){  
        $menu_order = 1;
        foreach ($menu_items as $key => $item) {
          if(isset($item['id']) && !empty($item['id'])){
              $mt_obj = MenuItems::where('id', $item['id'])->first();
          }else{
              $mt_obj = new MenuItems;

          }
          $mt_obj->menu_id = $menu_id;
          $mt_obj->menu_type_id = $item['menu_type_id'];
          $mt_obj->menu_type = $item['title'];
          $mt_obj->menu_icon = $item['menu_icon'];
          $mt_obj->atr_menu_icon = $item['atr_menu_icon'];
          $mt_obj->icon_show = $item['icon_show'];
          $assoc_item_id = 0;
          if($item['title'] == 'Category'){
            $assoc_item_id = $item['category_id'];
          }
          if($item['title'] == 'Pages'){
            $assoc_item_id = $item['page_id'];
          }
          
          $mt_obj->assoc_item_id = $assoc_item_id;
          //$mt_obj->url = $item['atr_link_input'];
          $mt_obj->custom_class = $item['atrcustomcss'];
          $mt_obj->menu_order = $menu_order;
          $mt_obj->parent_id = $parent_id;
          $mt_obj->save();
          $menu_item_id = $mt_obj->id;
          $current_item_id[] = $menu_item_id;
          //dd($item['lang']);
          if(isset($item['lang'])){
            foreach($item['lang'] as $key1 => $value){
              $atr_link_input = isset($value['atr_link_input'])?$value['atr_link_input']:'';
              MenuItemsDesc::updateOrCreate(['menu_item_id'=>$menu_item_id, 'lang_id'=>$value['id']],['menu_item_id'=>$menu_item_id, 'lang_id'=>$value['id'], 'title'=>$value['input'], 'url'=>$atr_link_input]);
            }
          }  
          if(count($item['nodes'])>0){
            $current_item_id = $this->EditMenuItem($menu_id, $item['nodes'], $menu_item_id, $current_item_id);
          }
        $menu_order++;

        }
        
        return $current_item_id;

      }  
    }

    public function delete($id) {
       
    }

    public function checkUnique(Request $request) {
        /*$user_id = Auth::id();
        $name = $this->alias($request->attribute_code, '_');
        if (isset($request->attribute_id) && !empty($request->attribute_id)) {
            $data = Attribute::where([['attribute_code', $name], ['id', '!=', $request->attribute_id]])->first();
        } else {
            $data = Attribute::where(['attribute_code' => $name])->first();
        }
        if (isset($data->attribute_code) && !empty($data->attribute_code)) {
            echo 'false';
        } else {
            echo 'true';
        }

        exit;*/
    }

    public function getCategorieslist() {
         $created_by = Auth::guard('admin_user')->user()->id;
         $tree = [];
         $cat_data_set = Category::select('id','total_products', 'parent_id','url')
                                ->where('status','1')
                                ->with('categorydesc')
                                ->with('category')->get()->toArray();

         if(count($cat_data_set)){
            foreach ($cat_data_set as $a){
                $new[$a['parent_id']][] = $a;
            }
            $tree = $this->createTree($new, $new[0]); // changed         
              
         }
         return $tree;
    }

    public function getPageList(){

        $page_dtl = \DB::table(with(new \App\StaticPage)->getTable().' as p')
                ->join(with(new \App\StaticPageDesc)->getTable().' as pd', 'p.id', '=', 'pd.static_page_id')
                ->select('p.id', 'pd.page_title')
                ->where(['p.is_system'=>'0','status'=>'1','pd.lang_id'=> session('default_lang')])
                ->get();

        return $page_dtl;
    }

    public function getLanguges(){
        $langArr=Language::getLangugeDetails();
        $langCodeArray = [];
        foreach($langArr as $langCode){
          $langCodeArray[]=array('id'=>$langCode->id,'code'=>$langCode->languageCode,'flag'=>Config::get('constants.language_url').$langCode->languageFlag);
        }

        return $langCodeArray;
    }

   

}
