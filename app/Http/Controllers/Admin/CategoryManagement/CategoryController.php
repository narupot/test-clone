<?php

namespace App\Http\Controllers\Admin\CategoryManagement;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MarketPlace;
use Auth;
use App\Category;
use App\CategoryDesc;
use App\MongoCategory;
use App\Language;
use App\ShopAssignCategory;
use Session;
use DB;
use Illuminate\Validation\Rule;
use Validator;
use Lang;
use App\Currency;
use App\Unit;
use Config;


class CategoryController extends MarketPlace
{
    public $tableCategoryDesc;
    public $categories;
    public $categoryTable;
    public $lang_id;
    private $module_name = "category";

    public function __construct(){
        $this->middleware('admin.user');
        $this->tableCategoryDesc = with(new CategoryDesc)->getTable();
        $this->categoryTable = with(new Category)->getTable();
        $this->lang_id = session('admin_default_lang');

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       $filter = $this->getFilter('category');
       return view('admin.category-management.list', ['filter'=>$filter]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $permission = $this->checkUrlPermission('add_category');  
        if($permission === true) {
            $created_by = Auth::guard('admin_user')->user()->id;
            $categories = Category::where(['parent_id' => '0', 'created_by' => $created_by])->get();
            $seo_status='1';
            $active_tab = 'category';
            $units = Unit::where('status','1')->get();

            return view('admin.category-management.create', ['categories' => $categories,'active_tab'=>$active_tab, 'units'=>$units, 'status'=>0]);
        }

    }

    public function categorieslist(){
        
        $created_by = Auth::guard('admin_user')->user()->id;
        $tree = [];
        $cat_data_set = Category::select('id','total_products', 'parent_id','status','is_default')->with('categorydesc')->with('category')->get()->toArray();
        if(count($cat_data_set)){
        foreach ($cat_data_set as $a){
            $new[$a['parent_id']][] = $a;
        }
        //dd($new[0]); 
        $tree = $this->createTree($new, $new[0]); // changed         
          
        }
        echo json_encode($tree);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {  
    

        $default_lang = session('default_lang');
        $created_by = Auth::guard('admin_user')->user()->id;
        $name = $request->category_name[$default_lang];
        $url = createUrl($request->url);
        $request->merge(array('name' => $name)); 
        $fileobject = $request->file('category_image');


        $default_cat_id = 0;
        if($request->parent_id<1){
            // if category don't have parent assign assign it in default
            $parent_id = $default_cat_id;
        }else{
            $parent_id = $request->parent_id;
        }

        $this->validate($request, 
                ['url' => 'required|unique:category','name' => ['required']], $this->messages());

        $category = new Category;
        $category->created_by = $created_by;
        $category->parent_id = $parent_id;
        $category->comment = $request->cat_comment;
        $category->updated_by = $created_by;
        $category->status = $request->status;
        // upload category image 

        if(isset($request->category_image) && !empty($request->category_image)){
           $extension = 'jpg'; 
           $image_name = 'cat'.md5(microtime()).'.'.$extension;
           $cat_image_dir_path = Config::get('constants.category_img_path').'/';
           $image = $request->category_image;
           $this->base64UploadImage($image, $cat_image_dir_path, $image_name);
           $category->img = $image_name;
        
        }

        try {
            $category->save();

            /** Logging category create information **/
              $action_type = "created";
              $logdetails = "Admin has created $this->module_name with name $name and url  $url";
              $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);
              $this->updateLogActivity($logdata);
            /** Logging category create information end **/


        } catch (QueryException $ex) {
            echo $ex->getMessage();
        }

        $cat_id = $category->id;
        $catCount = Category::where('url', $url)->count();
        if($catCount>0){
           $category->url = $url.'-'.$cat_id;
        }else{
           $category->url = $url;
        }
        $category->save();
        $data = array();
        $lang_ids = Language::where('status', '1')->pluck('id');
        foreach ($lang_ids as $lang_id) {
            $data[$lang_id] = ["cat_id" => $cat_id, "lang_id" => $lang_id, "category_name" => $name, "cat_description" => $this->addslashes($request->cat_description[$lang_id]), 
                'meta_title' => $this->addslashes($request->meta_title[$lang_id]), 
                'meta_keyword' => $this->addslashes($request->meta_keyword[$lang_id]), 
                'meta_description' => $this->addslashes($request->meta_description[$lang_id])
                ];
        }

        DB::table($this->tableCategoryDesc)->insert($data);
        
        $units = isset($request->unit)?$request->unit:[];
        if(count($units)>0){
            $unit_cat_data = [];
            foreach($units as $unit_id){
                $unit_cat_data[]=[
                    'cat_id'=>$cat_id,
                    'unit_id'=>$unit_id
                ];
            }
            if(count($unit_cat_data)){
                \App\CategoryUnit::insert($unit_cat_data);
            }
        }

        /*****update category in mongo******/
        $category_data = Category::categoryData($cat_id);
        $store = MongoCategory::updateData($category_data);

        return redirect()->action('Admin\CategoryManagement\CategoryController@edit', $cat_id)->with('message', 'The category has been added.');
    }

    public function messages() {

        return [
            'name.required' => Lang::get('admin_category.please_enter_category_name'),
            'url.required' => Lang::get('admin_category.please_enter_category_url'),
            'url.unique' => Lang::get('admin_category.category_url_in_already_used'),
        ];
    }


    public function categoryedit(Request $request){
        $permission = $this->checkUrlPermission('add_category');  
        if($permission === true) {
            $created_by = Auth::guard('admin_user')->user()->id;
            $id = $request->id;
            $category = Category::where(['id'=> $id])->with('categorydesces')->get()->first();   
            if($category){
                $category = $category->toArray();
            }

            if (!$category) {
                abort(404);
            }
            $category['deleteUrl'] = action('Admin\CategoryManagement\CategoryController@deletecat', $id);
            $category['previewLink'] = '';  
            foreach ($category['categorydesces'] as $key => $value) {
                $category['categorydesces'][$key]['cat_description'] = stripcslashes($value['cat_description']);
                $category['categorydesces'][$key]['meta_description'] = stripcslashes($value['meta_description']);
            }

            $prefix =  DB::getTablePrefix();
            $default_lang = session('default_lang');

            $requiredcat_productarray = [];

            $allCategory = Category::where(['status'=>'1'])->select('id')->with('categorydesc')->get();
            $requiredlist = [];
            foreach($allCategory as $key => $cat){
                if(isset($cat->categorydesc)){
                    $requiredlist[$key]['cat_id'] = $cat->categorydesc->cat_id;
                    $requiredlist[$key]['cat_name'] = $cat->categorydesc->category_name;  
                }           
            }
            $category['catproductlist'] = $requiredcat_productarray;
            $category['allcategorylist'] = $requiredlist;
            $category['img'] = getCategoryImageUrl($category['img']);
            
            echo json_encode($category);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function getParentID($id){
        $catArr=Category::select('id','parent_id')->where('id',$id)->first()->toArray();
        if($catArr['parent_id']>0){
            return $this->getParentID($catArr['parent_id']);
        }else{
            return $catArr['id'];
        }
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
    
        $permission = $this->checkUrlPermission('edit_category');  
        if($permission === true) {
            $created_by = Auth::guard('admin_user')->user()->id;
            $category = Category::where('id',$id)->with('categorydesc')->first();
            
            if(isset($category->categorydesc->category_name))
            {
                $subcat_mesg = $category->categorydesc->category_name;
            }else{
                $subcat_mesg = '';
            }  

            if (!$category) {
                abort(404);
            }
            
            $categories = Category::where(['parent_id' => '0'])->get();
            $parent_id=$this->getParentID($id); 
            $active_tab = 'category';   
            $shop_data = \App\Shop::with(['shopUser','shopDesc'])->get();
            $assign_seller = ShopAssignCategory::where('category_id',$id)->pluck('shop_id')->toArray();
            
            $units = Unit::where('status','1')->get();
            $catunit =  \App\CategoryUnit::where('cat_id', $id)->pluck('id','unit_id');
 
            
            return view('admin.category-management.edit', ['subcat_mesg'=>$subcat_mesg,'category' => $category, 'categories' => $categories, 'tableCategoryDesc' => $this->tableCategoryDesc,'top_parent_id'=>$parent_id, 'active_tab'=>$active_tab,'shop_data'=>$shop_data,'assign_seller'=>$assign_seller, 'units'=>$units, 'catunit'=>$catunit]);
        }
    }


   

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){
        
        $permission = $this->checkUrlPermission('add_category');  

        if($permission === true) {

            $category_id = $request->category_id;

            $created_by = Auth::guard('admin_user')->user()->id;

            $name = isset($request->category_name[$this->lang_id])?$request->category_name[$this->lang_id]:'';
            
            $url = createUrl($request->url);
            $data = array('url' => $url,'name'=>$name);

            $validator = Validator::make($data, [
               'url' => [
                 'required',
                Rule::unique('category')->ignore($id, 'id')
                ],
                'name' => ['required']
            ], $this->messages());
            
            if ($validator->fails()) {
                return redirect(action('Admin\CategoryManagement\CategoryController@edit', $id))
                            ->withErrors($validator)
                            ->withInput();
            }
           
            $category = Category::where('id', $id)->first();
            $category->updated_by = $created_by;
            $category->status = $request->status;

            if(isset($request->category_image) && !empty($request->category_image)){
                $extension = 'jpg'; 
                $image_name = 'cat'.md5(microtime()).'.'.$extension;
                $cat_image_dir_path = Config::get('constants.category_img_path').'/';
                $image = $request->category_image;
                $this->base64UploadImage($image, $cat_image_dir_path, $image_name);
                $category->img = $image_name;
        
            }
            
            $category->comment = $request->cat_comment;

            $change_data = [];
            try {
                $response = $category->save();
                if (!$category->wasRecentlyCreated) {
                    foreach ($category->getChanges() as $key => $value) {
                        $change_data = array_merge($change_data,[$key=>$value]);
                    }
                }

                //$response = true;
                if($response){

                }
            } catch (QueryException $ex) {
                echo $ex->getMessage();
            }
            
            $catCount = Category::where('url', $url)->where('id', '!=' , $id)->count();
            //dd($catCount);
            if($catCount>0){
               $category->url = $url.'-'.$id;
            }else{
               $category->url = $url;
            }
            $category->save();

            $lang_ids = Language::where('status', '1')->pluck('id');
            foreach ($lang_ids as $lang_id) {

                $cat_desc_model = CategoryDesc::updateOrCreate(['cat_id' => $id, 'lang_id' => $lang_id], ['cat_id' => $id, 'lang_id' => $lang_id, 'category_name' => $request->category_name[$lang_id], 'cat_description' => $this->addslashes($request->cat_description[$lang_id]),
                    'meta_title' => $this->addslashes($request->meta_title[$lang_id]),
                    'meta_keyword' => $this->addslashes($request->meta_keyword[$lang_id]),
                    'meta_description' => $this->addslashes($request->meta_description[$lang_id])
                    ]);

                if(!$cat_desc_model->wasRecentlyCreated) {
                    foreach ($cat_desc_model->getChanges() as $key => $value) {
                        $change_data = array_merge($change_data,[$key=>$value]);
                    }
                }

            }
            $units = isset($request->unit)?$request->unit:[];
            if(count($units)>0){
                $unit_cat_data = [];
                $cuids = [];
                foreach($units as $unit_id){
                    $cudata = \App\CategoryUnit::where('cat_id', $id)->where('unit_id', $unit_id)->first();
                    if(!$cudata){
                      $cunitdata = new \App\CategoryUnit;
                      $cunitdata->cat_id = $id; 
                      $cunitdata->unit_id = $unit_id;
                      $cunitdata->save();
                      $cuids[] = $cunitdata->id;
                    
                    }else{
                      $cuids[] = $cudata->id;

                    }
                   
                    //\App\CategoryUnit::updateOrCreate($unit_cat_data, $unit_cat_data);
                }


                if(count($cuids)>0){
                    \App\CategoryUnit::where('cat_id', $id)->whereNotIn('id', $cuids)->delete();
                }
                
            }else{

               \App\CategoryUnit::where('cat_id', $id)->delete();

            }



            /*****update category in mongo******/
            $category_data = Category::categoryData($id);

            $store = MongoCategory::updateData($category_data);
        }


        /** Logging category delete information **/
          $action_type = "updated"; //Change action name like: created,updated,deleted     
          $logdetails = "Admin has updated category with url $category->url "; //Change update message as requirement 
          $old_data = "";
          $new_data = json_encode($change_data); 

          //Prepaire array for send data
          $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );

          //Call method in module
          $this->updateLogActivity($logdata);
        /** Logging category delete information end **/

        return redirect()->action('Admin\CategoryManagement\CategoryController@edit', $id)->with('succMsg', 'The category has been updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id) {

        $permission = $this->checkUrlPermission('add_category');  
        if($permission === true) {
            $created_by = Auth::guard('admin_user')->user()->id;
            $category = Category::where('id', $id)->first();
            //$category = Category::find($id);
            if (!$category) {
                abort(404);
            }
            $deleteCatFlag = 1;
            foreach ($category->category as $cat) {
                $deleteCatFlag = 2;
                break;
            }
            if($deleteCatFlag == 1){
                try {
                    $category->delete();

                    /***delete from mongo***/
                    MongoCategory::deleteData($id);
                    /** Logging category delete information **/
                      $action_type = "delete";   
                      $logdetails = "Admin has deleted category with url  $category->url "; //Change update message as requirement  

                      //Prepaire array for send data
                      $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

                      //Call method in module
                      $this->updateLogActivity($logdata);
                    /** Logging category delete information end **/

                    return redirect()->action('Admin\CategoryManagement\CategoryController@create')->with('message', 'The category has been deleted.');
                } catch (QueryException $e) {
                    return redirect()->action('Admin\CategoryManagement\CategoryController@edit', $id)->with('error', 'Whoops, looks like something went wrong.');
                }
            } else {
                return redirect()->action('Admin\CategoryManagement\CategoryController@edit', $id)->with('error', 'Whoops, looks like something went wrong.');
            }
        }
    }



    public function deletecat($id){
        
        $permission = $this->checkUrlPermission('add_category');  
        if($permission === true) {
            $created_by = Auth::guard('admin_user')->user()->id;
            $category = Category::where('id', $id)->first();
            //$category = Category::find($id);
            //dd($category);
            if (!$category) {
                abort(404);
            }
            $deleteCatFlag = 1;
           
            foreach ($category->category as $cat) {
                $deleteCatFlag = 2;
                return redirect()->action('Admin\CategoryManagement\CategoryController@edit', $id)->with('cat_error', 'Whoops, Please delete child category first.'); 
            }
            if ($deleteCatFlag == 1) {
                try {
                    $category->delete();
                    /***delete from mongo***/
                    MongoCategory::deleteData($id);
                    /** Logging category delete information **/
                      $action_type = "delete"; //Change action name like: created,updated,deleted
                               
                      $logdetails = "Admin has deleted category with url  $category->url ";  

                      //Prepaire array for send data
                      $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

                      //Call method in module
                      $this->updateLogActivity($logdata);
                    /** Logging category delete information end **/

                    return redirect()->action('Admin\CategoryManagement\CategoryController@create')->with('message', 'The category has been deleted.');
                } catch (\Exception $e) {
                    return redirect()->action('Admin\CategoryManagement\CategoryController@edit', $id)->with('errorMsg', 'Whoops, looks like something went wrong.');
                }
            } else {
                return redirect()->action('Admin\CategoryManagement\CategoryController@edit', $id)->with('errorMsg', 'Whoops, looks like something went wrong.');
            }
        }
    }


    public function subcreate($id = null) {
        
        $permission = $this->checkUrlPermission('add_category');  
        if($permission === true) {
            $created_by = Auth::guard('admin_user')->user()->id;
             
            $categoriesids = $category = '';
            if (!empty($id)) {
                $category = Category::where(['id' => $id,'status' => '1'])->first();
            } else {
                 
                $cat_id = 0;
                $categoriesids = Category::select('id')->where(['status' => '1'])->where('is_default','!=','1')->where('parent_id', $cat_id)->get();

                $categorydropdown=$this->getCategoriesdropdown($cat_id, 0); 

                //dd($categorydropdown);
            }
            $categories = Category::where(['parent_id' => '0','status' => '1'])->get();

            $seo_status = [];
            
            $units = Unit::where('status','1')->get();
           
            $subcategory_message = \Lang::get('admin_category.create_fruit');

            $active_tab = 'subcategory';

            return view('admin.category-management.subCreate', ['categories' => $categories, 'category' => $category, 'categoriesids' => $categoriesids, 'seo_status'=>$seo_status,'subcat_mesg'=> $subcategory_message,'active_tab'=>$active_tab, 'categorydropdown'=>$categorydropdown, 'units'=>$units, 'status'=>1]);
        }
    }


    public function getCategoriesdropdown($cat_id, $count=0) {
        $created_by = Auth::guard('admin_user')->user()->id;
        $option = '';

        //$categoriesids = Category::select('id')->where('parent_id', $cat_id)->get();
        $categoriesids = Category::select('id')->where('parent_id', 0)->get();
        $space = '';
        for($i=0; $i<$count; $i++){
          $space .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; 
        }
        $count++;
        if(count($categoriesids)){
            foreach ($categoriesids as $key=>$category){
              if(isset($category->categorydesc->category_name) && !empty($category->categorydesc->category_name)){
                $option .=  '<option value="'.$category->id.'">'.$space.$category->categorydesc->category_name.'</option>'; 
                //$option .= $this->getCategoriesdropdown($category->id, $count);
              }              
            }                     
        }
        return $option;
    }

    public function checkUnique(Request $request) {
        $created_by = Auth::guard('admin_user')->user()->id;
        $name = $this->alias($request->url);
        if (isset($request->cat_id) && !empty($request->cat_id)) {
            $data = Category::where([['url' , $name],['status','1'],['id', '!=', $request->cat_id]])->first();
        } else {
            $data = Category::where(['url'=>$name,'status'=>'1'])->first();
        }
        if (isset($data->url) && !empty($data->url)) {
            echo 'false';
        } else {
            echo 'true';
        }

        exit;
    }

    public function allchildids($cat_id){
         $results = DB::table(with(new Category)->getTable().' as cs')
            ->join(with(new Category)->getTable().' as cs2','cs.parent_id', '=', 'cs2.id')
            ->where('cs.id',$cat_id)
            ->get();
            return results;
    }


    // to get all child category id from given tree of category with children
    public function getAllChildCatIds($array) {
        $result = array();
        foreach($array as $row) {
            $result[] = $row['id'];
            if(count($row['Children']) > 0) {
                $result = array_merge($result,$this->getAllChildCatIds($row['Children']));
            }
        }
        return $result;
    }

    public function getAllChildCat($array) {
        $result = array();
        foreach($array as $key => $row) {
            $result[] = $row;
            if(count($row['Children']) > 0){
                $result = array_merge($result,$this->getAllChildCat($row['Children']));
            }
        }
        return $result;
    }

    public function getCategorieslist() {

         $tree = [];
         $cat_data_set = Category::select('id','total_products', 'parent_id','url')
                                ->where(['status'=>'1', 'is_default'=>'0'])
                                ->with('categorydesc')
                                ->with('category')->get()->toArray();

         if(count($cat_data_set)){
            foreach ($cat_data_set as $a){
                $new[$a['parent_id']][] = $a;
            }
            $tree = $this->createTree($new, $new[0]);
         }
         return $tree;
    }

    public function assignSeller(Request $request){
        $shop_id = isset($request->shop_id) && $request->shop_id?$request->shop_id:'';
        $category_id = isset($request->category_id) && $request->category_id?$request->category_id:'';

        if($shop_id && $category_id){
            $shop_id_arr = explode(',', $shop_id);
            $assign_seller = ShopAssignCategory::where('category_id',$category_id)->pluck('shop_id')->toArray();
            $diff_id_arr = array_diff($assign_seller,$shop_id_arr);
            foreach ($shop_id_arr as $key => $value) {
                $check_shop = ShopAssignCategory::where(['shop_id'=>$value,'category_id'=>$category_id])->count();
                unset($shop_id_arr[$key]);
                if($check_shop < 1){
                    $cat_obj = new ShopAssignCategory;
                    $cat_obj->shop_id = $value;
                    $cat_obj->category_id = $category_id;
                    $cat_obj->created_by = Auth::guard('admin_user')->user()->id;
                    $cat_obj->save();
                }
            }
            
            if(count($diff_id_arr)){
                ShopAssignCategory::whereIn('shop_id',$diff_id_arr)->delete();
            }
            return['status'=>'success'];
        }else{
            return ['status'=>'fail','msg'=>Lang::get('admin_category.please_select_seller')];
        }
    }

    public function assignUnit(Request $request){
        $id = $request['id'];
        $catunit =  \App\CategoryUnit::where('cat_id', $id)->pluck('id','unit_id');
        return $catunit;
    }

    public function subcategorylist()
    {
       $filter = $this->getFilter('sub_category');
       return view('admin.category-management.sublist', ['filter'=>$filter]);
    }
	
	public function deletecategory($id) {
        //$permission = $this->checkUrlPermission('delete_category');
		$permission = $this->checkUrlPermission('add_category');
        $result = Category::where('id', $id)->first();		
        if (!$result) {
            abort(404);
        }
		$deleteCatFlag = 1;
		foreach ($result->category as $cat) {
			$deleteCatFlag = 2;
			break;
		}
		if($deleteCatFlag == 1){
			$cat_count=\App\Product::where('cat_id', $id)->get()->count();
			if($cat_count>0)
			{
				$msg_text = Lang::get('category.already_added_to_product_cannot_be_delete');
				return json_encode(array('status'=>'validate_error','message'=>$msg_text));
			}
			else 
			{
				try{
				   $result->delete();
				   
					/***delete from mongo***/
					MongoCategory::deleteData($id);
					/** Logging category delete information **/
					  $action_type = "delete";   
					  $logdetails = "Admin has deleted category with url  $result->url "; //Change update message as requirement  

					  //Prepaire array for send data
					  $logdata = array('action_type' =>$action_type,'module_name' =>$this->module_name,'logdetails' =>$logdetails);

					  //Call method in module
					  $this->updateLogActivity($logdata);
					/** Logging category delete information end **/
				   
					$msg_text = Lang::get('category.category_delete_successfully');
					return redirect()->action('Admin\CategoryManagement\CategoryController@index')->with('succMsg', $msg_text);  
				}catch(Exception $e) {
					$msg_text = Lang::get('category.something_went_wrong');
					return json_encode(array('status'=>'validate_error','message'=>$msg_text));
				}
			}
		} else {
			$msg_text = Lang::get('category.delete_child_category_first');
            return json_encode(array('status'=>'validate_error','message'=>$msg_text));
        }
    }
	
	function categoryListData(Request $request){
        //dd($request->all());
		$page_type = !empty($request->page_type) ? $request->page_type : 'category';
        $perpage = !empty($request->pq_rpp) ? $request->pq_rpp : 10;
        $request->page = $current_page = !empty($request->pq_curpage)?$request->pq_curpage:0;

        $start_index = ($current_page - 1) * $perpage;
        //dd($perpage,$request->page);
        
        $order_by = 'id';
        $order_by_val = 'desc';
        if(isset($request->pq_sort)){
            $sort_data = jsonDecodeArr($request->pq_sort);
            $order_by = $sort_data[0]['dataIndx'];
            $order_by_val = ($sort_data[0]['dir']=='up')?'asc':'desc';
        }

        try{
            
            $query = DB::table(with(new \App\Category)->getTable().' as b')
            ->Leftjoin(with(new \App\CategoryDesc)->getTable().' as bd', [['b.id', '=', 'bd.cat_id'], ['bd.lang_id', '=' , DB::raw(session('default_lang'))]])
            ->leftjoin(with(new \App\AdminUser)->getTable().' as au','au.id', '=', 'b.created_by');	 
			if(isset($page_type) && $page_type=='sub_category')
			{
				$query = $query->where('b.parent_id','!=','0');
			}
			else
			{
				$query = $query->where(['b.parent_id'=>'0']);
			}
			$query = $query->select('b.*', 'bd.category_name','au.nick_name');
            
            if(isset($request->pq_filter)){
                $filter_req = json_decode($request->pq_filter,true);
                if(!empty($filter_req['data'])){
                    $filter_arr = $filter_req['data'];
                    foreach ($filter_arr as $fkey => $fvalue) {

                        $searchval = $fvalue['value'];
                        switch ($fvalue['dataIndx']) {
                            case 'category_name':$query->where('bd.category_name','like', '%'.$searchval.'%'); break;
                            case 'status':$query->whereIn('b.status',$searchval); break;
                            case 'created_at':
                                $from_date = $fvalue['value']??'';
                                $to_date = $fvalue['value2']??'';
                                createDateFilter($query,'b.created_at',$from_date,$to_date);
                            break;
                            case 'updated_at':
                                $from_date = $fvalue['value']??'';
                                $to_date = $fvalue['value2']??'';
                                createDateFilter($query,'b.updated_at',$from_date,$to_date);
                            break;
                            
                        }
                        
                    }
                }
            }
            $response = $query->orderBy($order_by,$order_by_val)->paginate($perpage,['*'],'page',$current_page);
            $totrec = $response->total();
            //dd($response);
            if($start_index >= $totrec) {
                $current_page = ceil($totrec/$perpage);
                
                $response = $query->orderBy($order_by,$order_by_val)->paginate($perpage,['*'],'page',$current_page);
            }

            if(count($response)){
                foreach($response as $key=>$mainCategory){
                    $response[$key]->category_mage = getCategoryImageUrl($mainCategory->img);
					if(isset($page_type) && $page_type=='sub_category')
					{
						$response[$key]->parent_category_name = getParentCategory($mainCategory->parent_id);
					}
					$response[$key]->created_at = getDateFormat($mainCategory->created_at, '1');
					$response[$key]->updated_at = getDateFormat($mainCategory->updated_at, '1');
                }       
            }

            /***save filter****/
			if(isset($page_type) && $page_type=='sub_category')
			{
				$this->setFilter('sub_category',$request);
			}
			else
			{
				$this->setFilter('category',$request);
			}
            

            
        }catch(QueryException $e){
            $response = ['status'=>'fail','msg'=>$e->getMessage()];
        }
        
        return $response;
    }

}
