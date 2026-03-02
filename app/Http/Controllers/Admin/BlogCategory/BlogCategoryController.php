<?php

namespace App\Http\Controllers\Admin\BlogCategory;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;

use App\BlogCategory;
use App\BlogCategoryDesc;
use App\Language;
use Config;
use Auth;
use DB;

class BlogCategoryController extends MarketPlace
{

    public function __construct(){
        $this->middleware('admin.user');
        $this->tableCategoryDesc = with(new BlogCategoryDesc)->getTable();
        $this->categoryTable = with(new BlogCategory)->getTable();
        $this->lang_id = Language::where('isDefault', '1')->pluck('id')->first();
    }

    public function index()
    {
        return redirect()->action('Admin\BlogCategory\BlogCategoryController@create');
    }

    public function create()
    {

        $permission = $this->checkUrlPermission('blogs_category');  
        if($permission === true) {

            $categories = BlogCategory::getMainCategory();            

            return view('admin.blogcategory.blogsCategoryCreate', ['categories' => $categories, 'type'=>'main_category']);
        }

    }

    public function store(Request $request) {        
        
        $input = $request->all();   
        $def_lang_id = session('admin_default_lang');     
        $input['name'] = $request->name[$def_lang_id]; 
        $input['comments'] = $request->comments[$def_lang_id];        
        $input['description'] = $request->description[$def_lang_id];
        $input['meta_title'] = $request->meta_title[$def_lang_id];
        $input['meta_keyword'] = $request->meta_keyword[$def_lang_id];
        $input['meta_description'] = $request->meta_description[$def_lang_id];
        $url = $request->name[$def_lang_id];        
        $validate = $this->validateCategory($input);

        if ($validate->passes()) {

            $created_by = Auth::guard('admin_user')->user()->id;            

            $category = new BlogCategory;

            $category->parent_id = $request->parent_id;
            $category->url = createUrl($url);
            $category->status = $request->status;                   
            $category->created_by = $created_by;    
            $category->save();        

            $def_name = $request->name[$def_lang_id];
            $def_comments = $request->comments[$def_lang_id];
            $def_description = $request->description[$def_lang_id];
            $def_meta_title = $request->meta_title[$def_lang_id];
            $def_meta_keyword = $request->meta_keyword[$def_lang_id];
            $def_meta_description = $request->meta_description[$def_lang_id];
            
            foreach($request->name as $lang=>$name) {
                                
                $comments = $request->comments[$lang];
                $description = $request->description[$lang];
                $meta_title = $request->meta_title[$lang];
                $meta_keyword = $request->meta_keyword[$lang];
                $meta_description = $request->meta_description[$lang];                                
                
                if(empty($name)) {
                    $name = $def_name;
                }
                if(empty($comments)) {
                    $comments = $def_comments;
                }    
                if(empty($description)) {
                    $description = $def_description;
                }
                if(empty($meta_title)) {
                    $meta_title = $def_meta_title;
                }
                if(empty($meta_keyword)) {
                    $meta_keyword = $def_meta_keyword;
                }
                if(empty($meta_description)) {
                    $meta_description = $def_meta_description;
                }                       
                
                $category_desc = new BlogCategoryDesc();
                $category_desc->cat_id = $category->id;
                $category_desc->lang_id = $lang;
                $category_desc->name = $name;
                $category_desc->comments = $comments;  
                $category_desc->description = $description; 
                $category_desc->meta_title = $meta_title; 
                $category_desc->meta_keyword = $meta_keyword; 
                $category_desc->meta_description = $meta_description;                
                $category_desc->save();
            }

            /*update activity log Start*/                        
            $action_type = "created";
            $module_name = "blogcategory";            
            $logdetails = "Admin has created ".$input['name']." blog category";
            $old_data = "";
            $new_data = "";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );
            $this->updateLogActivity($logdata);
            /*update activity log End*/

            try{            
                
                return redirect()->action('Admin\BlogCategory\BlogCategoryController@edit', $category->id)->with('succMsg', 'Category added successfully!');
            }
            catch (QueryException $ex) {            
                return redirect()->action('Admin\BlogCategory\BlogCategoryController@create')->with('errorMsg', 'This category "'.$request->name.'" already exist!');
            }
        }
        else{

            if($request->type == 'sub_category') {
                return redirect()->action('Admin\BlogCategory\BlogCategoryController@subcreate')->withErrors($validate)->withInput();
            }
            else {
                return redirect()->action('Admin\BlogCategory\BlogCategoryController@create')->withErrors($validate)->withInput();
            }
        }        
    }

    public function subcreate() {

        $permission = $this->checkUrlPermission('blogs_category');  
        if($permission === true) {

            $categories = BlogCategory::getMainCategory();            

            return view('admin.blogcategory.blogsCategoryCreate', ['categories'=>$categories, 'type'=>'sub_category']);
        }
    }    

    public function edit($id) {

        $category = BlogCategory::getCategoryDetail($id);

        if (!$category) {
            abort(404);
        }

        $categories = BlogCategory::getMainCategory();

        return view('admin.blogcategory.blogsCategoryEdit', ['category' => $category, 'categories' => $categories,'tblcategoryDesc'=>$this->tableCategoryDesc]);
    }

    public function update(Request $request, $id) {

        $input = $request->all();
        $def_lang_id = session('admin_default_lang');     
        $input['name'] = $request->name[$def_lang_id];
        $input['comments'] = $request->comments[$def_lang_id];        
        $input['description'] = $request->description[$def_lang_id];
        $input['meta_title'] = $request->meta_title[$def_lang_id];
        $input['meta_keyword'] = $request->meta_keyword[$def_lang_id];
        $input['meta_description'] = $request->meta_description[$def_lang_id];
        $name = $request->name[$def_lang_id];
        $validate = $this->validateCategory($input,$id);        

        if ($validate->passes()) {

            $updated_by = Auth::guard('admin_user')->user()->id;

            $category = BlogCategory::find($id);
            $category->url = createUrl($name);
            $category->status = $request->status;                  
            $category->updated_by = $updated_by;
            $category->save(); 

            $data_arr = $this->filterCatData($request);   
            BlogCategoryDesc::updateCategoryDesc($data_arr, $id);    

            /*update activity log start*/
            $action_type = "updated"; 
            $module_name = "blogcategory";            
            $logdetails = "Admin has updated ".$input['name']." blog category";
            $old_data = "";
            $new_data = "";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );
            $this->updateLogActivity($logdata);
            /*update activity log End*/ 

            try{     
                                      
                return redirect()->action('Admin\BlogCategory\BlogCategoryController@edit', $id)->with('succMsg', 'Category updated successfully!');
            }
            catch (QueryException $ex) {            
                return redirect()->action('Admin\BlogCategory\BlogCategoryController@edit', $id)->with('errorMsg', 'This category "'.$name.'" already exist!');
            }
        }
        else{

            return redirect()->action('Admin\BlogCategory\BlogCategoryController@edit', $id)->withErrors($validate)->withInput();
        }                 
    }

    public function filterCatData($request) {

        $def_lang_id = session('admin_default_lang');
        $def_name = $request->name[$def_lang_id];
        $def_comments = $request->comments[$def_lang_id];
        $def_description = $request->description[$def_lang_id];
        $def_meta_title = $request->meta_title[$def_lang_id];
        $def_meta_keyword = $request->meta_keyword[$def_lang_id];
        $def_meta_description = $request->meta_description[$def_lang_id];
            
            foreach($request->name as $lang=>$name) {
                                
                $comments = $request->comments[$lang];  
                $description = $request->description[$lang];
                $meta_title = $request->meta_title[$lang];  
                $meta_keyword = $request->meta_keyword[$lang];  
                $meta_description = $request->meta_description[$lang];                  
                
                if(empty($name)) {
                    $name = $def_name;
                }
                if(empty($comments)) {
                    $comments = $def_comments;
                }  
                if(empty($description)) {
                    $description = $def_description;
                } 
                if(empty($meta_title)) {
                    $meta_title = $def_meta_title;
                }
                if(empty($meta_keyword)) {
                    $meta_keyword = $def_meta_keyword;
                }
                if(empty($meta_description)) {
                    $meta_description = $def_meta_description;
                }                                        

                $data_arr[$lang] = array('name'=>$name, 'comments'=>$comments,'description'=>$description,'meta_title'=>$meta_title,'meta_keyword'=>$meta_keyword,'meta_description'=>$meta_description);
            } 
        
        return $data_arr;       
    }

    public function destroy($id) {

        $category = BlogCategory::where(['id' => $id])->first();
        $categoryDes = BlogCategoryDesc::where(['cat_id' => $id]);

        $category->delete();        
        $categoryDes->delete();

        /*update activity log start*/
        $action_type = "deleted"; 
        $module_name = "blogcategory";            
        $logdetails = "Admin has deleted blog category id ".$id;
        $old_data = "";
        $new_data = "";
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );
        $this->updateLogActivity($logdata);
        /*update activity log End*/ 

        return redirect()->action('Admin\BlogCategory\BlogCategoryController@create')->with('succMsg', 'Category deleted successfully');        
    }    

    private function validateCategory($input,$id=''){

        if(empty($id)){            
            $rules['name'] = 'Required|unique:'.$this->tableCategoryDesc.',name';
        }else{
            $rules['name'] = 'required';
        }

        if(isset($input['type']) && $input['type'] == 'sub_category') {
            $rules['parent_id'] = 'required';
        }

        $error_msg['name.parent_id'] = 'Please select parent category';
        $error_msg['name.required'] = 'Please enter category name';

        $validate = Validator::make($input, $rules, $error_msg);
        
        return $validate;        
    }    
}