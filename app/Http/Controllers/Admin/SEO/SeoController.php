<?php

namespace App\Http\Controllers\Admin\SEO;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Hash;

use Carbon\Carbon;
use Cache;

use App\User;
use Auth;
use App\Shop;
use App\ShopDesc;
use DB;
use App\Language;
use Lang;

use App\SeoGlobal;

use App\SeoSuperAdmin;
use App\SeoSuperAdminDesc;

//use App\SeoProductWise;
//use App\SeoProductWiseDesc;


use App\ProductDesc;

use App\SeoPage;
use App\SeoPageDesc;

use App\Product;

class SeoController extends MarketPlace {

    private $tblUser;
    public $userStatus;
    public $prefix;
    
    public function __construct() {   
        $this->middleware('admin.user'); 
        $this->prefix =  DB::getTablePrefix();
        $this->tblUser = with(new User)->getTable(); 

        $this->tblSeoSuperAdmin = with(new SeoSuperAdmin)->getTable();
        $this->tblSeoSuperAdminDesc = with(new SeoSuperAdminDesc)->getTable();
       /*User Status*/
        
       // $this->tblSeoProductWise = with(new SeoProductWise)->getTable();
        

        $this->tblProductDesc = with(new ProductDesc)->getTable();

        $this->tblSeoPage = with(new SeoPage)->getTable();
        $this->tblSeoPageDesc = with(new SeoPageDesc)->getTable();


        

        $this->userStatus = ['1'=>'Pending', '2'=>'Rejected', '3'=>'Approved'];     
    }    
    
    public function index() {

        $permission = $this->checkUrlPermission('seller_seo_list');
        if($permission === true) {
            return view('admin.seo.SeoSellerwiseList');
        }  
       
    }
    
   

public function products(){
 //  SellerProductList
    $permission = $this->checkUrlPermission('product_list'); 
    $fieldsetdata = [ "fieldSets"=> 
                      [
                                [
                                    "fieldName"=>"id",
                                    "width"=> 100,
                                    "align"=> "center",
                                    "sortable"=>true,
                                    "filterable"=>false,                                
                                ],
                                [ 
                                    "fieldName" => "product_name",
                                    "width"=> 100,
                                    "align"=> "center", 
                                    "sortable" => true, 
                                    "filterable" => true, 
                                    "fieldType" => "textbox", 
                                    "textBoxType" => "single", 
                                    "datatype" => "text",
                                    'displayName'=>'Name'
                                ],
                                [
                                    "fieldName" => "sku",
                                    "width"=> 200,
                                    "align"=> "center",  
                                    "sortable" => true, 
                                    "filterable" => true, 
                                    "fieldType" => "textbox", 
                                    "textBoxType" => "single",
                                    "datatype" => "text",
                                    "displayName"=>'SKU'
                                ],
                                [
                                    'fieldName' => 'meta_title',
                                    'width'=>200,
                                    'align'=>"center",
                                    'sortable'=>true,
                                    'filterable'=>false,
                                    'displayName'=>'Meta Title'

                                ],
                                [
                                    "fieldName" => "meta_description", 
                                    "width"=> 200,
                                    'align'=>"center",
                                    'sortable'=>true,
                                    'filterable'=>false,
                                    'displayName'=>'Meta Description'
                                ],
                                [
                                    "fieldName" => "meta_keywords",
                                    "width"=> 200,
                                    "align"=> "center",  
                                    "sortable" => true, 
                                    "filterable" => false, 
                                    "fieldType" => "textbox", 
                                    "textBoxType" => "single",
                                    "datatype" => "text",
                                    "displayName"=>'Meta Keywords'
                                ],
                                [
                                    "fieldName" => "updated_at",
                                    "width"=> 200,
                                    "align"=> "center",  
                                    "sortable" => true, 
                                    "filterable" => false, 
                                    "fieldType" => "textbox", 
                                    "textBoxType" => "single",
                                    "datatype" => "text",
                                    "displayName"=>'Updated Date'
                                ]
                        
                            ],
                              "tableConfig"=> [
                                                    [
                                                    "resizable"=> true,
                                                    "row_rearrange"=> false,
                                                    "column_rearrange"=> true,
                                                    "filter"=> true,
                                                    "chk_action"=> true,
                                                    "col_setting"=> false
                                                    ]
                                                ]
                                ];       
    if($permission === true) { 
         // $user = User::where('id', $user_id)->select('id', 'name')->first();
          return view('admin.seo.ProductList', ['fieldsetdata'=>json_encode($fieldsetdata)]); 
    }
}



public function productlist(Request $request){



    //$permission = $this->checkUrlPermission('product_list');
   
    //$user_id = $request->user_id;

     $perpage = !empty($request->per_page) ? $request->per_page : 10;
     $page = !empty($request->page) ? $request->page : 1;
     $name = !empty($request->product_name) ? $request->product_name : '';
     $sku = !empty($request->sku) ? $request->sku : '';
     $dataConfig = $this->getOffsetLimit($page, $perpage);
     $page_limit = $dataConfig['0'];
     $offset = $dataConfig['1'];


    $default_lang = session('default_lang');

    $product_data = DB::table(with(new Product)->getTable().' as p')
        ->leftjoin(with(new ProductDesc)->getTable().' as pd', 
           [    
              ['pd.product_id', '=', 'p.id'], 
              ['pd.lang_id', '=' , DB::raw($default_lang)]
           ]
          )
       /*  ->leftjoin(with(new SeoProductWise)->getTable().' as saw', 'saw.product_id','=','p.id')
         ->leftjoin(with(new SeoProductWiseDesc)->getTable().' as sawd', [['saw.id', '=', 'sawd.seo_product_id'], ['sawd.lang_id', '=' , DB::raw($default_lang)]])*/
             
      ->select('p.id','p.sku', 'pd.name', 'pd.meta_title', 'pd.meta_title', 'pd.meta_keyword', 
        'pd.meta_description', 'p.created_at', 'p.updated_at');

        if(!empty($name)){
           $product_data = $product_data->where('pd.name', 'like', '%'.$name.'%');
        }


        if(!empty($sku)){
           $product_data = $product_data->where('p.sku', 'like', '%'.$sku.'%');
        }

       $product_data = $product_data->where(['p.site_visibility' => '1']);


       $total = $product_data->count();

       $product_data =  $product_data->skip($offset)
                        ->take($page_limit)
                        ->orderBy('p.id', 'Desc')
                        ->get();

     /* ->orderBy('p.id', 'Desc')
      ->get();*/

    //dd($product_data);
   
   /* $product_data = \App\Product::where(['user_id'=> $user_id, 'site_visibility' => '1'])->select('id','sku','currency_id')->with('getProductDetail')->get();*/



        $required_data = [];
        foreach($product_data as $key=>$pd){
           
          $required_data[$key]['name']= $pd->name;
          $required_data[$key]['sku']= $pd->sku;
          $required_data[$key]['meta_title']= $pd->meta_title;
          $required_data[$key]['meta_description']= $pd->meta_description; 
          $required_data[$key]['meta_keywords']= $pd->meta_keyword; 
          $required_data[$key]['product_sku'] = $pd->sku;
         
          $required_data[$key]['updated_at'] = !empty($pd->updated_at)?getDateFormat($pd->updated_at, '3'):getDateFormat($pd->created_at, '3');

          $required_data[$key]['add_action'] = action('Admin\SEO\SeoController@addproductseo', $pd->id);

          $required_data[$key]['add_action_text'] = Lang::get('Edit');
         
      
        }
       return ['data' => $required_data, 'total'=>$total];
   
}






public function addproductseo($id){

        $permission = $this->checkUrlPermission('add_product_seo');        
        if($permission === true) {     
        
         $seoSuperAdminTemplate  = SeoGlobal::where(['status'=> '1', 'type'=>'1'])->pluck('title', 'id'); 
          $result = \App\Product::where(['id'=> $id])->select('id','sku', 'meta_robots', 'admin_template_id', 'template_type')->with('getProductDetail')->first(); 

          $meta_robots = explode(',', $result->meta_robots);
          return view('admin.seo.seoAddProductwise', ['result' => $result, 'tblProductDesc' => $this->tblProductDesc, 'meta_robots'=>$meta_robots, 'seoSuperAdminTemplate' => 
            $seoSuperAdminTemplate]); 
        }       
}






function addseoproductstore(Request $request){


        $permission = $this->checkUrlPermission('add_product_seo');
        
        $type = '';
        /*if(isset($request->type) && $request->type == 'blog'){
            $type = $request->type;
        }*/
        $product_id = $request->product_id;
        $result =  Product::where('id', $product_id)->first();
        
        $result->template_type = $request->template_type;
        if($request->template_type == '2'){
          $result->admin_template_id = $request->admin_template_id; 
        }

        $result->meta_robots = !empty($request->meta_robots)?implode(',',$request->meta_robots):''; 

        $result->save();

        /*take the insert id*/
        //$seo_id = $result->id;
        $data = array();

        
        foreach ($request->meta_title as $key => $value) {
          
          ProductDesc::updateOrCreate(['product_id' => $product_id, 'lang_id' => $key], ['product_id' => $product_id, 'lang_id' => $key, "meta_title" => $value, "meta_description" => $request->meta_description[$key], "meta_keyword" => $request->meta_keyword[$key]]);

        }
        
        /*update activity log start*/
        $action_type = "created"; 
        $module_name = "product seo";            
        $logdetails = "Admin has created product seo for sku ".$result->sku." ";
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/
        /*if($type == 'blog'){

           return redirect()->action('SeoController@blogs', $request->user_id)->with('succMsg', 'Records added Successfully!');

        }else{*/
       
           return redirect()->action('Admin\SEO\SeoController@products')->with('succMsg', 'Records added Successfully!');
       // }


                     
} 

public function pages($page_type = null) {
     
        
        $permission = $this->checkUrlPermission('pages_seo_management');
        $results = SeoPage::get();
        if($permission === true) {
            // $page_type = empty($page_type) ? 'system' : $page_type;
             return view('admin.seo.seoPagesList', ['results'=> $results]);
        }        
    }


    public function allpageslist(Request $request) {
        
          $permission = $this->checkUrlPermission('pages_seo_management');
          if($permission === true) {

            $perpage = !empty($request->per_page) ? $request->per_page : 10;
            $page = !empty($request->page) ? $request->page : 1;

            $name = !empty($request->name) ? $request->name : '';
            $url = !empty($request->url) ? $request->url : '';

            $dataConfig = $this->getOffsetLimit($page, $perpage);
            $page_limit = $dataConfig['0'];
            $offset = $dataConfig['1'];

            /* $results = User::where(['user_type'=>'seller', 'status'=>'1'])->orderBy('id', 'Desc')->get();*/

         $default_lang = session('default_lang');
         //$page_type = empty($request->page_type) ? 'system' : $request->page_type;

          $results = DB::table(with(new SeoPage)->getTable().' as sp')
             ->leftjoin(with(new SeoPageDesc)->getTable().' as spd', [['sp.id', '=', 'spd.seo_page_id'], ['spd.lang_id', '=' , DB::raw($default_lang)]])
                 
          ->select('sp.id', 'sp.name', 'sp.url', 'sp.template_type', 'sp.template_type','spd.meta_title', 'spd.meta_title', 'spd.meta_keyword', 
            'spd.meta_description', 'sp.created_at', 'sp.updated_at');
          //->where('sp.page_type', $page_type);

           if(!empty($name)){
              $results = $results->where('sp.name', 'like', '%'.$name.'%');
               
           }


           if(!empty($name)){
              $results = $results->where('sp.name', 'like', '%'.$name.'%');
               
           }


           if(!empty($url)){
              $results = $results->where('sp.url', 'like', '%'.$url.'%');
               
           }





           $total = $results->count();
       
     
           $results = $results->skip($offset)
                        ->take($page_limit)
                        ->orderBy('sp.id', 'Desc')
                        ->get();

    //  dd($results);
            $data_arr = array();
            foreach ($results as $result) {
                
                $arr_temp['id'] = $result->id;
                $arr_temp['name'] = $result->name;
                $arr_temp['url'] = $result->url;
                
           
                $arr_temp['meta_title'] = $result->meta_title;
                $arr_temp['meta_description'] = $result->meta_description;
                $arr_temp['meta_keywords'] = $result->meta_keyword;
             
                $arr_temp['created_at'] = !empty($result->created_at)?getDateFormat($result->created_at, '3'):'';

                 $arr_temp['updated_at'] = !empty($result->updated_at)?getDateFormat($result->updated_at, '3'):'';

                $arr_temp['edit_action'] = action('Admin\SEO\SeoController@editpageseo', [$result->id]);

                $arr_temp['edit_action_text'] = 'Edit';
                
                $arr_temp['detete_action_url'] = action('Admin\SEO\SeoController@deletepageseo', [$result->id]);

                $arr_temp['detete_action_text'] = 'Delete';
              

                if($result->template_type == '3'){

                    $arr_temp['template'] = 'Manual';

                }elseif($result->template_type == '2'){

                    $arr_temp['template'] = 'Admin Template';

                }else{
                    $arr_temp['template'] = 'Auto';

                }

                $data_arr[] = $arr_temp;


            
         }
        
        return ['data'=>$data_arr, 'total'=> $total];
      }        
    }





 public function createpageseo()
 {
        $permission = $this->checkUrlPermission('add_page_seo');

        $seoSuperAdminTemplate  = SeoGlobal::where('status', '1')->pluck('title', 'id');        
        if($permission === true) { 
          return view('admin.seo.seopageAdd', ['seoSuperAdminTemplate' => $seoSuperAdminTemplate]); 
        }
    } 


public function storepageseo(Request $request){               
        
        
        $this->validate($request, 
                [ 
                  'name' => 'required|unique:seo_page',
                  'url' => 'required|unique:seo_page' 
                ]
         );

         
        $insertresult = new SeoPage;
        $insertresult->name = $request->name;

        $insertresult->template_type = $request->template_type;

        if($request->template_type == '2'){
          $insertresult->admin_template_id = $request->admin_template_id; 
        }

        $insertresult->url = $request->url;
        $insertresult->status = $request->status;
        //$insertresult->page_type = $request->page_type;
        $insertresult->meta_robots = !empty($request->meta_robots)?implode(',',$request->meta_robots):'';
        $insertresult->save();
        /*take the insert id*/
        $seo_id = $insertresult->id;
        $data = array();
        foreach ($request->meta_title as $key => $value) {
            $data[$key] = ["seo_page_id" => $seo_id, "lang_id" => $key, "meta_title" => $value, "meta_description" => $request->meta_description[$key], "meta_keyword" => $request->meta_keyword[$key] 
            ];
        }
        DB::table($this->tblSeoPageDesc)->insert($data);

        /*update activity log start*/
        $action_type = "created"; 
        $module_name = "seo pages";            
        $logdetails = "Admin has created ".$request->name." ".$module_name;
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/
        return redirect()->action('Admin\SEO\SeoController@pages')->with('succMsg', 'Records added Successfully!');            
    }


public function editpageseo($id) {
        
        $permission = $this->checkUrlPermission('edit_page_seo');
        if($permission === true) {
            $seoSuperAdminTemplate  = SeoGlobal::pluck('title', 'id'); 
            $result = SeoPage::where(['id'=> $id])->first();
           // dd($result);
            $meta_robots = '';
            $meta_robots = explode(',', $result->meta_robots);
            
            // dd($meta_robots);

            return view('admin.seo.seopageEdit', ['result'=>$result, 'meta_robots' => $meta_robots,'seoSuperAdminTemplate'=>$seoSuperAdminTemplate ,'tblSeoPageDesc'=>$this->tblSeoPageDesc]);

        }
    }


    public function deletepageseo($id) {
            
      $result = SeoPage::where(['id'=> $id])->first();
      $result->delete();
        /*update activity log start*/
        $action_type = "deleted"; 
        $module_name = "seo pages";            
        $logdetails = "Admin has deleted ".$result->name." ";
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/
      return redirect()->action('Admin\SEO\SeoController@pages')->with('succMsg', 'Record has deleted Successfully!');
          
    }


    public function updatepageSeo(Request $request, $id){
       
        
         $this->validate($request, 
                 [
                   'name' => ['required',
                      Rule::unique('seo_page')->ignore($id, 'id'),
                   ],
                   'url' => ['required',
                      Rule::unique('seo_page')->ignore($id, 'id'),
                   ]
                ]
                
         );
               

        $insertresult = SeoPage::where(['id'=>$id])->first();

        $insertresult->name = $request->name;
        $insertresult->url = $request->url;
        $insertresult->template_type = $request->template_type;
        if($request->template_type == '2'){
         $insertresult->admin_template_id = $request->admin_template_id; 
        }
       
        $insertresult->status = $request->status;
        //$insertresult->page_type = $request->page_type;
        
        $insertresult->meta_robots = !empty($request->meta_robots)?implode(',',$request->meta_robots):'';
        $insertresult->save();
        /*take the insert id*/
        $seo_id = $insertresult->id;
        if($request->template_type == '3'){
            foreach ($request->meta_title as $key => $value) {
               $affected = SeoPageDesc::updateOrCreate(
                       ['seo_page_id' => $seo_id, 'lang_id' => $key], 
                       ['seo_page_id' => $seo_id, 'lang_id' => $key, 
                       'meta_title' => $value, "meta_description" => $request->meta_description[$key], "meta_keyword" => $request->meta_keyword[$key]
                      ]);
                
            }
         }
        
        /*update activity log start*/
        $action_type = "updated"; 
        $module_name = "seo pages";            
        $logdetails = "Admin has updated ".$request->name." ".$module_name;
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/

        return redirect()->action('Admin\SEO\SeoController@pages')->with('succMsg', 'Records Edited Successfully!'); 
        
       
    }


}
