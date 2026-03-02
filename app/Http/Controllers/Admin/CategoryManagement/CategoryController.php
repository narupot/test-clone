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
use App\ProductSubGroup;
use App\ProductGroup;
use App\ParentCategory;
use App\ProductTypeTag;
use App\Package;
use App\ParentCatPackage;
Use App\ParentCatBaseUnit;
use Carbon\Carbon;
use App\MongoParentCategory;
use App\MongoProductTypeTag;


use Config;
use Illuminate\Support\Facades\Log;


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
            $categoriesids = $category = '';
            $category = null;
            if (!empty($id)) {
                $category = Category::where(['id' => $id,'status' => '1'])->first();
            } else {
                 
                $cat_id = 0;
                $categoriesids = Category::select('id')->where(['status' => '1'])->where('is_default','!=','1')->where('parent_id', $cat_id)->get();

                $categorydropdown=$this->getCategoriesdropdown($cat_id, 0); 

                //dd($categorydropdown);
            }
            $seo_status='1';
            $active_tab = 'category';
            $units = Unit::where('status','1')->get();
            $packages = Package::where('status','1')->get();
            $productGroups = ProductGroup::where('status', 1)->orderBy('sorting_no', 'asc')->get();
            $productTypeTags = [];

            return view('admin.category-management.create', 
            ['category' => $category
            ,'categories' => $categories
            ,'active_tab'=>$active_tab
            , 'units'=>$units
            , 'status'=>0
            , 'categorydropdown'=>$categorydropdown
            , 'seo_status'=>$seo_status
            , 'productGroups' => $productGroups
            , 'packages' => $packages
            , 'productTypeTags' => $productTypeTags
           ]);
        }

    }

    public function getParentCategoryName($subgroupId)
    {
        // ดึงข้อมูล Category ทั้งหมดที่เกี่ยวข้องกับ subgroup_id
        $categories = ParentCategory::where('subgroup_id', $subgroupId)->get(['id', 'category_name']);

        // ส่งข้อมูลกลับในรูปแบบ JSON
        return response()->json($categories);
    }

    public function categorieslist(){
        
        $created_by = Auth::guard('admin_user')->user()->id;
        $tree = [];
        $cat_data_set = Category::select('id','total_products', 'parent_id','status','is_default')->with('categorydesc')->with('category')->get()->toArray();
        if(count($cat_data_set)){
        foreach ($cat_data_set as $a){
            $new[$a['parent_id']][] = $a;
        }
        // dd($new[0]); 
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
        $created_by   = Auth::guard('admin_user')->id();
        $now          = now();

        $this->validate($request, [
            'url'           => 'required|unique:parent_category,url',
        ], $this->messages());

        $category = new Category();
        $category->url        = createUrl($request->url);
        $category->parent_id  = $request->parent_id ?? 0;
        $category->status     = $request->status ?? 1;
        $category->img        = null;
        $category->sequence   = $request->sequence ?? 0;
        $category->comment    = $request->cat_comment ?? null;
        $category->created_by = $created_by;
        $category->updated_by = $created_by;
        $category->created_at = $now;
        $category->updated_at = $now;
        $category->save();

        $cat_id = $category->id;

        if ($request->filled('category_image')) {
            $image_name = 'cat' . md5(microtime()) . '.jpg';
            $cat_image_dir_path = Config::get('constants.category_img_path') . '/';
            $this->base64UploadImage($request->category_image, $cat_image_dir_path, $image_name);

            $category->img = $image_name;
            $category->save();
        }

        $desc = new CategoryDesc();
        $desc->cat_id           = $cat_id;
        $desc->lang_id          = $default_lang;
        $desc->category_name    = $this->addslashes($request->category_name ?? '');
        $desc->meta_title       = $this->addslashes($request->meta_title ?? '');
        $desc->meta_keyword     = $this->addslashes($request->meta_keyword ?? '');
        $desc->meta_description = $this->addslashes($request->meta_description ?? '');
        $desc->cat_description  = $this->addslashes($request->cat_description ?? '');
        $desc->save();

        if ($request->has('keywords')) {
            $keywords = json_decode($request->input('keywords', '[]'), true);

            if (is_array($keywords) && count($keywords) > 0) {
                $insertData = [];
                foreach ($keywords as $tag) {
                    $insertData[] = [
                        'product_type_id' => $cat_id,
                        'tag'             => $tag,
                        'tag_status'      => 1,
                        'created_at'      => $now,
                        'created_by'      => $created_by,
                        'updated_date'    => $now,
                        'updated_by'      => $created_by,
                    ];
                }
                ProductTypeTag::insert($insertData);
            }
        }

        $product_tag_data = ProductTypeTag::where('product_type_id', $category->id)->get();
        MongoProductTypeTag::updateProductTypeTag($category->id, $product_tag_data);

        $category_data = Category::with('categorydesc')->find($cat_id);
        MongoCategory::updateData($category_data);

        return redirect()
        ->action([CategoryController::class, 'edit'], $cat_id)
        ->with('message', 'Category has been created successfully.');
    }


    public function updateCategory(Request $request, $id)
    {
        $newUrl = createUrl($request->url);

        $request->validate([
            'url' => [
                'required',
                Rule::unique('category', 'url')->ignore($id),
            ],
            'parent_id' => 'required',
        ]);

        $category   = Category::findOrFail($id);
        $created_by = Auth::guard('admin_user')->user()->id;
        $now        = now();

        try {
            $image_name = $category->img;
            if ($request->has('category_image') && !empty($request->category_image)) {
                if (strlen($request->category_image) > 100) { 

                    $image_name = 'cat' . md5(microtime()) . '.jpg';
                    $cat_image_dir_path = Config::get('constants.category_img_path') . '/';
                    $this->base64UploadImage($request->category_image, $cat_image_dir_path, $image_name);
                }
            }

            $category->url        = $newUrl;
            $category->parent_id  = $request->parent_id ?? 0;
            $category->status     = $request->status ?? 1;
            $category->img        = $image_name;
            $category->comment    = $request->cat_comment ?? null;
            $category->updated_at = $now;
            $category->updated_by = $created_by;
            $category->save();
            \Log::info("After category->save()");

            CategoryDesc::updateOrCreate(
            [
                'cat_id'  => $category->id,
                'lang_id' => session('default_lang'),
            ],
            [
                'category_name'    => $this->addslashes($request->category_name ?? ''),
                'meta_title'       => $this->addslashes($request->meta_title ?? ''),
                'meta_keyword'     => $this->addslashes($request->meta_keyword ?? ''),
                'meta_description' => $this->addslashes($request->meta_description ?? ''),
                'cat_description'  => $this->addslashes($request->cat_description ?? ''),
            ]
        );

            ProductTypeTag::where('product_type_id', $category->id)->delete();

            if ($request->has('keywords')) {
                $keywords = json_decode($request->input('keywords', '[]'), true);
                if (is_array($keywords) && count($keywords) > 0) {
                    $insertData = [];
                    foreach ($keywords as $tag) {
                        $insertData[] = [
                            'product_type_id' => $category->id,
                            'tag'             => $tag,
                            'tag_status'      => 1,
                            'created_at'      => $now,
                            'created_by'      => $created_by,
                            'updated_date'    => $now,
                            'updated_by'      => $created_by,
                        ];
                    }
                    ProductTypeTag::insert($insertData);
                }
            }
            $product_tag_data = ProductTypeTag::where('product_type_id', $category->id)->get();
            MongoProductTypeTag::updateProductTypeTag($category->id, $product_tag_data);
            $category_data = Category::with('categorydesc')->find($category->id);
            MongoCategory::updateData($category_data);

            \Log::info("After MongoCategory::updateData()");
            return redirect()
            ->action([CategoryController::class, 'edit'], $category->id)
            ->with('message', 'Category has been updated successfully.');

        } catch (\Exception $e) {
            \Log::error("Update category error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }


    public function storeParentCategory(Request $request)
    {
      
            $created_by = Auth::guard('admin_user')->user()->id;
            $default_lang = session('default_lang');
            $now = Carbon::now();

            $name = $request->category_name;
            $url = createUrl($request->url);
            $request->merge(['name' => $name]);

            $this->validate($request, [
                'url'           => 'required|unique:parent_category,url',
                'category_name' => 'required|unique:parent_category,category_name',
                'product_group'      => 'required',
                'product_subgroup'   => 'required',
                'package'          => 'required',  
                'unit'             => 'required',   
            ], $this->messages());

            $image_name = null;
            if (isset($request->category_image) && !empty($request->category_image)) {
                $extension = 'jpg';
                $image_name = 'cat' . md5(microtime()) . '.' . $extension;
                $cat_image_dir_path = Config::get('constants.category_img_path') . '/';
                $this->base64UploadImage($request->category_image, $cat_image_dir_path, $image_name);
            }

            try {
                DB::beginTransaction();

                $parentCategory = new ParentCategory;
                $parentCategory->category_name      = $name;
                $parentCategory->url                = $url;
                $parentCategory->img                = $image_name;
                $parentCategory->is_deleted         = 0;
                $parentCategory->meta_title         = $this->addslashes($request->meta_title ?? '');
                $parentCategory->meta_keyword       = $this->addslashes($request->meta_keyword ?? '');
                $parentCategory->meta_description   = $this->addslashes($request->meta_description ?? '');
                $parentCategory->cat_description    = $this->addslashes($request->cat_description ?? '');
                $parentCategory->sorting_no         = $request->sorting_no ?? 0;
                $parentCategory->group_id           = $request->product_group ?? 0;
                $parentCategory->subgroup_id        = $request->product_subgroup ?? 0;
                $parentCategory->created_at         = $now;
                $parentCategory->created_by         = $created_by;
                $parentCategory->updated_at         = $now;
                $parentCategory->updated_by         = $created_by;
                $parentCategory->save();

                $parent_cat_id = $parentCategory->id;

                if (!empty($request->sorting_no)) {
                    DB::table('parent_category')
                        ->where('id', '!=', $parent_cat_id)
                        ->where('sorting_no', '>=', $request->sorting_no)
                        ->increment('sorting_no');
                }

                $url_count = ParentCategory::where('url', $url)->where('id', '!=', $parent_cat_id)->count();
                if ($url_count > 0) {
                    $parentCategory->url = $url . '-' . $parent_cat_id;
                    $parentCategory->save();
                }

                $packages = $request->input('package');
                if (!empty($packages)) {
                    $package_data = collect($packages)->map(function ($package_id) use ($parent_cat_id, $created_by, $now) {
                        return [
                            'parent_cat_id' => $parent_cat_id,
                            'package_id' => $package_id,
                            'created_by' => $created_by,
                            'updated_by' => $created_by,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    })->all();
                    ParentCatPackage::insert($package_data);
                }

                $units = $request->input('unit');
                if (!empty($units)) {
                    $unit_data = collect($units)->map(function ($base_unit_id) use ($parent_cat_id, $created_by, $now) {
                        return [
                            'parent_cat_id' => $parent_cat_id,
                            'base_unit_id' => $base_unit_id,
                            'created_by' => $created_by,
                            'updated_by' => $created_by,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    })->all();
                    ParentCatBaseUnit::insert($unit_data);
                }

                $mongo_data = ParentCategory::find($parent_cat_id);
                MongoParentCategory::updateData($mongo_data);

                DB::commit();

                $action_type = "created";
                $log_details = "Admin has created a Parent Category with name '$name' and URL '$parentCategory->url'";
                $log_data = ['action_type' => $action_type, 'module_name' => 'Parent Category', 'logdetails' => $log_details];
                $this->updateLogActivity($log_data);

                return redirect()->action('Admin\CategoryManagement\CategoryController@ParentCategoryEdit', $parent_cat_id)->with('message', 'The parent category has been added successfully.');

            } catch (QueryException $ex) {
                DB::rollBack();
                return redirect()->back()->withInput()->with('errorMsg', 'Error saving data: ' . $ex->getMessage());
            }
    }
    
    
    public function messages() {

        return [
            'name.required' => Lang::get('admin_category.please_enter_category_name'),
            'url.required' => Lang::get('admin_category.please_enter_category_url'),
            'url.unique' => Lang::get('admin_category.category_url_in_already_used'),
            'category_name.unique' => Lang::get('admin_category.category_name_in_already_used'),
            'category_name.required' => 'กรุณากรอกชื่อหมวดหมู่',
            'group_id.required'      => 'กรุณาเลือกกลุ่ม',
            'subgroup_id.required'   => 'กรุณาเลือกหมวด',
            'package.required'   => 'กรุณาเลือกแพ็กเกจ',
            'unit.required'   => 'กรุณาเลือกหน่วยสินค้า',
            'parent_id.required'   => 'กรุณาประเภทสินค้า',
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

    public function getParentID($id)
    {
        $visited = [];
        while ($id > 0) {
            if (in_array($id, $visited)) {
                \Log::warning("Category loop detected", ['id' => $id, 'visited' => $visited]);
                break;
            }

            $visited[] = $id;
            $cat = Category::select('id','parent_id')->find($id);
            if (!$cat) {
                return null;
            }

            if ($cat->parent_id == 0) {
                return $cat->id;
            }

            $id = $cat->parent_id;
        }

        return $id;
    }




    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
public function edit($id)
{
    \Log::info("edit() start", ['id' => $id]);
    $permission = $this->checkUrlPermission('edit_category'); 
    
    if ($permission === true) {
        $created_by = Auth::guard('admin_user')->user()->id;

        $category = Category::with(['categorydesc', 'productTypeTags'])->findOrFail($id);

        $subcat_mesg = $category->categorydesc->category_name ?? '';
        $categories = Category::where('parent_id', 0)->get();

        $parent_id = $category->parent_id;
        \Log::info("edit() parent_id", ['parent_id' => $parent_id]);

        $parentCategory = \App\ParentCategory::select('group_id', 'subgroup_id')
            ->where('id', $category->parent_id)
            ->first();

        $productGroups = ProductGroup::where('status', 1)->orderBy('sorting_no', 'asc')->get();
        $productSubGroups = ProductSubGroup::where('status', 1)->orderBy('sorting_no', 'asc')->get();

        $active_tab = 'category';
        $shop_data = \App\Shop::with(['shopUser','shopDesc'])->get();

        $assign_seller = ShopAssignCategory::where('category_id', $id)->pluck('shop_id')->toArray();
        $productTypeTags = $category->productTypeTags->pluck('tag')->toArray();

        // 5️⃣ ตรวจสอบว่า parentCategory มีไหม
        $currentGroupId = $parentCategory->group_id 
            ?? $category->product_group_id 
            ?? null;

        $currentSubGroupId = $parentCategory->subgroup_id 
            ?? $category->product_sub_group_id 
            ?? null;

        $status = $category->status;

        \Log::info("edit() before return view", [
            'subcat_mesg' => $subcat_mesg,
            'category_id' => $category->id,
            'parent_id' => $parent_id,
            'currentGroupId' => $currentGroupId,
            'currentSubGroupId' => $currentSubGroupId
        ]);

        return view('admin.category-management.subCreate', compact(
            'subcat_mesg',
            'category',
            'categories',
            'parent_id',
            'active_tab',
            'shop_data',
            'assign_seller',
            'productTypeTags',
            'productGroups',
            'productSubGroups',
            'status',
            'currentGroupId',
            'currentSubGroupId'
        ))->with('tableCategoryDesc', $this->tableCategoryDesc);
    }

    abort(403, 'You do not have permission to edit this category.');
}

    public function ParentCategoryEdit($id)
    {
        $permission = $this->checkUrlPermission('edit_category');  
        if ($permission !== true) {
            abort(403);
        }

        $created_by = Auth::guard('admin_user')->user()->id;

        $category = ParentCategory::with([
            'group',
            'subgroup',
        ])->findOrFail($id);

        $subcat_mesg = $category->category_name ?? '';

        $productGroups = ProductGroup::where('status', 1)->orderBy('sorting_no', 'asc')->get();
        $productSubGroups = ProductSubGroup::where('status', 1)
                            ->where('pro_group_id', $category->group_id)
                            ->orderBy('sorting_no', 'asc')
                            ->get();


    

        $packages = Package::get(); 
        $selectedPackages = ParentCatPackage::where('parent_cat_id',$id)
                                ->pluck('package_id')
                                ->toArray(); 

        $units = Unit::where('status','1')->get();

        $selectedUnits = ParentCatBaseUnit::where('parent_cat_id', $id)
                            ->pluck('base_unit_id')
                            ->toArray();
      
        return view('admin.category-management.create', [
            'category'        => $category,
            'subcat_mesg'     => $subcat_mesg,
            'productGroups'   => $productGroups,
            'productSubGroups'=> $productSubGroups,
            'units'           => $units,
            'selectedUnits'   => $selectedUnits,
            'packages'        => $packages,
            'selectedPackages' => $selectedPackages,
            'status'          => $category->is_deleted,
        ]);
    }

    public function updateParentCategory(Request $request, $id)
    {
       
        $updated_by = Auth::guard('admin_user')->user()->id;
        $default_lang = session('default_lang');
        $now = Carbon::now();

        $name = $request->category_name;
        $url = createUrl($request->url);
        $request->merge(['name' => $name]);

        $this->validate($request, [
            'url' => 'required|unique:parent_category,url,' . $id,
            "category_name" => 'required',
            'product_group'      => 'required',
            'product_subgroup'   => 'required',
            'package'          => 'required',  
            'unit'             => 'required', 
        ]);

        $parentCategory = ParentCategory::findOrFail($id);

        if (isset($request->category_image) && !empty($request->category_image)) {
            $cat_image_dir_path = Config::get('constants.category_img_path') . '/';

            if (str_contains($request->category_image, ';base64,')) {
                $extension = 'jpg';
                $image_name = 'cat' . md5(microtime()) . '.' . $extension;
                $this->base64UploadImage($request->category_image, $cat_image_dir_path, $image_name);
                $parentCategory->img = $image_name;
            } 
            else {
                $parentCategory->img = $request->category_image;
            }
        }

        try {
            DB::beginTransaction();

            $parentCategory->category_name      = $name;
            $parentCategory->url                = $url;
            $parentCategory->group_id           = $request->product_group ?? 0;
            $parentCategory->subgroup_id        = $request->product_subgroup ?? 0;
            $parentCategory->meta_title         = $this->addslashes($request->meta_title ?? '');
            $parentCategory->meta_keyword       = $this->addslashes($request->meta_keyword ?? '');
            $parentCategory->meta_description   = $this->addslashes($request->meta_description ?? '');
            $parentCategory->cat_description    = $this->addslashes($request->cat_description ?? '');
            $parentCategory->sorting_no         = $request->sorting_no ?? 0;
            $parentCategory->updated_at         = $now;
            $parentCategory->updated_by         = $updated_by;
            $parentCategory->is_deleted = $request->is_deleted;
            $parentCategory->save();

            // ---- จัดการ sorting_no ----
           if (!empty($request->sorting_no)) {
                $new_sort = (int) $request->sorting_no;
                $parentCategory->sorting_no = $new_sort;
                $parentCategory->save();

                $all = ParentCategory::orderBy('sorting_no')->orderBy('id')->get();
                $counter = 1;
                foreach ($all as $cat) {
                    if ($cat->id == $parentCategory->id) {
                        $cat->sorting_no = $new_sort;
                    } else {
                        if ($counter == $new_sort) {
                            $counter++; 
                        }
                        $cat->sorting_no = $counter;
                        $counter++;
                    }
                    $cat->save();
                }
            }

            $url_count = ParentCategory::where('url', $url)->where('id', '!=', $id)->count();
            if ($url_count > 0) {
                $parentCategory->url = $url . '-' . $id;
                $parentCategory->save();
            }

            ParentCatPackage::where('parent_cat_id', $id)->delete();
            $packages = $request->input('package');
            if (!empty($packages)) {
                $package_data = collect($packages)->map(function ($package_id) use ($id, $updated_by, $now) {
                    return [
                        'parent_cat_id' => $id,
                        'package_id' => $package_id,
                        'created_by' => $updated_by,
                        'updated_by' => $updated_by,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })->all();
                ParentCatPackage::insert($package_data);
            }

            ParentCatBaseUnit::where('parent_cat_id', $id)->delete();
            $units = $request->input('unit');
            if (!empty($units)) {
                $unit_data = collect($units)->map(function ($base_unit_id) use ($id, $updated_by, $now) {
                    return [
                        'parent_cat_id' => $id,
                        'base_unit_id' => $base_unit_id,
                        'created_by' => $updated_by,
                        'updated_by' => $updated_by,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })->all();
                ParentCatBaseUnit::insert($unit_data);
            }

            $mongo_data = ParentCategory::find($id);
            MongoCategory::updateData($mongo_data);

            DB::commit();

            $action_type = "updated";
            $log_details = "Admin has updated Parent Category with name '$name' and URL '$parentCategory->url'";
            $log_data = ['action_type' => $action_type, 'module_name' => 'Parent Category', 'logdetails' => $log_details];
            $this->updateLogActivity($log_data);

            return redirect()->back()->with('message', 'The parent category has been updated successfully.');

        } catch (QueryException $ex) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('errorMsg', 'Error updating data: ' . $ex->getMessage());
        }
    }

    public function editParent($id)
    {
        $permission = $this->checkUrlPermission('edit_category');  
        if ($permission !== true) {
            abort(403, 'Unauthorized action.');
        }

        $created_by = Auth::guard('admin_user')->id();
        $category = ParentCategory::find($id);
        if (!$category) {
            abort(404, 'Parent Category not found');
        }
        $subcat_mesg = $category->category_name;

        $categories = ParentCategory::where('subgroup_id', 0)
            ->where('group_id', 0)
            ->get();

        $parent_id = $this->getParentID($id);

        $active_tab = 'category';
        $shop_data = \App\Shop::with(['shopUser','shopDesc'])->get();

        $assign_seller = ShopAssignCategory::where('category_id', $id)
            ->pluck('shop_id')
            ->toArray();

        $units = Unit::where('status', 1)->get();
        $catunit = \App\CategoryUnit::where('cat_id', $id)
            ->pluck('id', 'unit_id');

        return view('admin.category-management.edit', [
            'subcat_mesg'       => $subcat_mesg,
            'category'          => $category,
            'categories'        => $categories,
            'tableCategoryDesc' => $this->tableCategoryDesc,
            'top_parent_id'     => $parent_id,
            'active_tab'        => $active_tab,
            'shop_data'         => $shop_data,
            'assign_seller'     => $assign_seller,
            'units'             => $units,
            'catunit'           => $catunit,
        ]);
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

            $productGroups = ProductGroup::where('status', 1)
            ->orderBy('sorting_no', 'asc')
            ->get();

            return view('admin.category-management.subCreate', ['categories' => $categories, 'category' => $category, 'categoriesids' => $categoriesids,
             'seo_status'=>$seo_status,'subcat_mesg'=> $subcategory_message,
             'active_tab'=>$active_tab, 'categorydropdown'=>$categorydropdown,
              'units'=>$units, 'status'=>1 , 'productGroups' => $productGroups
            ,'productTypeTags'=>'' , 'parent_id' => '', 'currentGroupId' => '', 'currentSubGroupId' => '']);
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

    public function assignTag(Request $request){
        $id = $request->input('id');
        
        if (!$id) {
            return response()->json([], 400);
        }
        $cattag = ProductTypeTag::where('product_type_id', $id)->pluck('tag');
        
        return response()->json($cattag);
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
				$return_response = array('status'=>'validate_error','message'=>$msg_text);
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
					$return_response = array('status'=>'success','message'=>$msg_text);  
				}catch(Exception $e) {
					$msg_text = Lang::get('category.something_went_wrong');
					$return_response = array('status'=>'validate_error','message'=>$msg_text);
				}
			}
		} else {
			$msg_text = Lang::get('category.delete_child_category_first');
            $return_response = array('status'=>'validate_error','message'=>$msg_text);
        }
		
		if($return_response['status']=='success')
		{
			if($result->parent_id!='0')
			{
				return redirect()->action('Admin\CategoryManagement\CategoryController@subcategorylist')->with('succMsg', $return_response['message']); 
			}
			else
			{
				return redirect()->action('Admin\CategoryManagement\CategoryController@index')->with('succMsg', $return_response['message']); 
			}			
		}
		else 
		{
			/* return json_encode($return_response); */
			if($result->parent_id!='0')
			{
				return redirect()->action('Admin\CategoryManagement\CategoryController@subcategorylist')->with('errorMsg', $return_response['message']); 
			}
			else
			{
				return redirect()->action('Admin\CategoryManagement\CategoryController@index')->with('errorMsg', $return_response['message']); 
			}			
		}
    }
	
	// function categoryListData(Request $request){
    //     //dd($request->all());
	// 	$page_type = !empty($request->page_type) ? $request->page_type : 'category';
    //     $perpage = !empty($request->pq_rpp) ? $request->pq_rpp : getPagination('limit');
    //     $request->page = $current_page = !empty($request->pq_curpage)?$request->pq_curpage:0;

    //     $start_index = ($current_page - 1) * $perpage;
    //     //dd($perpage,$request->page);
        
    //     $order_by = 'id';
    //     $order_by_val = 'desc';
    //     if(isset($request->pq_sort)){
    //         $sort_data = jsonDecodeArr($request->pq_sort);
    //         $order_by = $sort_data[0]['dataIndx'];
    //         $order_by_val = ($sort_data[0]['dir']=='up')?'asc':'desc';
    //     }

    //     try{
            
    //         $query = DB::table(with(new \App\Category)->getTable().' as b')
    //         ->Leftjoin(with(new \App\CategoryDesc)->getTable().' as bd', [['b.id', '=', 'bd.cat_id'], ['bd.lang_id', '=' , DB::raw(session('default_lang'))]])
    //         ->leftjoin(with(new \App\AdminUser)->getTable().' as au','au.id', '=', 'b.created_by');	 
	// 		if(isset($page_type) && $page_type=='sub_category')
	// 		{
	// 			$query = $query->where('b.parent_id','!=','0');
	// 		}
	// 		else
	// 		{
	// 			$query = $query->where(['b.parent_id'=>'0']);
	// 		}
	// 		$query = $query->select('b.*', 'bd.category_name','au.nick_name');
            
    //         if(isset($request->pq_filter)){
    //             $filter_req = json_decode($request->pq_filter,true);
    //             if(!empty($filter_req['data'])){
    //                 $filter_arr = $filter_req['data'];
    //                 foreach ($filter_arr as $fkey => $fvalue) {

    //                     $searchval = $fvalue['value'];
    //                     switch ($fvalue['dataIndx']) {
    //                         case 'category_name':$query->where('bd.category_name','like', '%'.$searchval.'%'); break;
	// 						case 'url':$query->where('b.url','like', '%'.$searchval.'%'); 
	// 							break;
	// 						case 'parent_category_name':
	// 							$parent_cat_ids=getParentCategoryIdsBySearchName($searchval);
	// 							$query->whereIn('b.parent_id',$parent_cat_ids);
	// 							break;
    //                         case 'status':$query->whereIn('b.status',$searchval); break;
	// 						case 'nick_name':$query->where('au.nick_name','like', '%'.$searchval.'%'); break;
    //                         case 'created_at':
    //                             $from_date = $fvalue['value']??'';
    //                             $to_date = $fvalue['value2']??'';
    //                             createDateFilter($query,'b.created_at',$from_date,$to_date);
    //                         break;
    //                         case 'updated_at':
    //                             $from_date = $fvalue['value']??'';
    //                             $to_date = $fvalue['value2']??'';
    //                             createDateFilter($query,'b.updated_at',$from_date,$to_date);
    //                         break;
                            
    //                     }
                        
    //                 }
    //             }
    //         }
    //         $response = $query->orderBy($order_by,$order_by_val)->paginate($perpage,['*'],'page',$current_page);
    //         $totrec = $response->total();
    //         //dd($response);
    //         if($start_index >= $totrec) {
    //             $current_page = ceil($totrec/$perpage);
                
    //             $response = $query->orderBy($order_by,$order_by_val)->paginate($perpage,['*'],'page',$current_page);
    //         }

    //         if(count($response)){
    //             foreach($response as $key=>$mainCategory){
    //                 $response[$key]->category_mage = getCategoryImageUrl($mainCategory->img);
	// 				if(isset($page_type) && $page_type=='sub_category')
	// 				{
	// 					$response[$key]->parent_category_name = getParentCategory($mainCategory->parent_id);
	// 				}
	// 				$response[$key]->created_at = getDateFormat($mainCategory->created_at, '1');
	// 				$response[$key]->updated_at = getDateFormat($mainCategory->updated_at, '1');
    //             }       
    //         }

            
    //     }catch(QueryException $e){
    //         $response = ['status'=>'fail','msg'=>$e->getMessage()];
    //     }
        
    //     return $response;
    // }

    public function categoryListData(Request $request)
    {
        $page_type = $request->page_type ?? 'category';
        $perpage   = $request->pq_rpp ?? getPagination('limit');
        $request->page = $current_page = $request->pq_curpage ?? 1;

        $start_index = ($current_page - 1) * $perpage;

        // $order_by     = 'pc.id';
        // $order_by_val = 'desc';
        $order_by   = 'pc.sorting_no'; 
        $order_by_val = 'asc'; 

        if ($request->pq_sort) {
            $sort_data   = jsonDecodeArr($request->pq_sort);
            $order_by    = $sort_data[0]['dataIndx'];
            $order_by_val = ($sort_data[0]['dir'] == 'up') ? 'asc' : 'desc';
        }

        try {
            $query = DB::table(with(new \App\ParentCategory)->getTable().' as pc')
            ->leftJoin(with(new \App\ProductGroup)->getTable().' as pg', 'pg.id', '=', 'pc.group_id')
            ->leftJoin(with(new \App\ProductSubGroup)->getTable().' as psg', 'psg.id', '=', 'pc.subgroup_id')
            ->leftJoin(with(new \App\AdminUser)->getTable().' as au', 'au.id', '=', 'pc.created_by');
            
            if ($request->pq_filter) {
                $filter_req = json_decode($request->pq_filter, true);
                if (!empty($filter_req['data'])) {
                    foreach ($filter_req['data'] as $fvalue) {
                        $searchval = $fvalue['value'] ?? null;

                        switch ($fvalue['dataIndx']) {
                            case 'category_name':
                                $query->where('pc.category_name', 'like', '%'.$searchval.'%');
                                break;
                            case 'url':
                                $query->where('pc.url', 'like', '%'.$searchval.'%');
                                break;
                            case 'group_name':
                                $query->where('pg.name', 'like', '%'.$searchval.'%');
                                break;
                            case 'subgroup_name':
                                $query->where('psg.subgroup_name', 'like', '%'.$searchval.'%');
                                break;
                           case 'nick_name':
                                $query->where(function ($q) use ($searchval) { 
                                    if (!empty($searchval)) {
                                        $q->where('au.nick_name', 'like', '%'.$searchval.'%')
                                        ->orWhereNull('pc.created_by'); 
                                    } else {
                                        $q->whereNotNull('au.nick_name')
                                        ->orWhereNull('pc.created_by'); 
                                    }
                                });
                                break;
                            case 'created_at':
                                $from_date = $fvalue['value'] ?? '';
                                $to_date   = $fvalue['value2'] ?? '';
                                createDateFilter($query, 'pc.created_at', $from_date, $to_date);
                                break;
                            case 'updated_at':
                                $from_date = $fvalue['value'] ?? '';
                                $to_date   = $fvalue['value2'] ?? '';
                                createDateFilter($query, 'pc.updated_at', $from_date, $to_date);
                                break;
                        }
                    }
                }
            }

            $query = $query->select(
                'pc.id',
                'pc.category_name',
                'pc.url',
                'pc.img',
                'pc.is_deleted',
                'pc.meta_title',
                'pc.sorting_no',
                'pc.group_id',
                'pc.subgroup_id',
                'pc.meta_keyword',
                'pc.meta_description',
                'pc.cat_description',
                'pc.created_at',
                'pc.updated_at',
                'au.nick_name',
                'pg.name as group_name',
                'pg.image as group_image',
                'psg.subgroup_name',
                'psg.images as subgroup_image'
            );

            $response = $query->orderBy($order_by, $order_by_val)
                ->paginate($perpage, ['*'], 'page', $current_page);

            $totrec = $response->total();

            if ($start_index >= $totrec) {
                $current_page = ceil($totrec / $perpage);
                $response = $query->orderBy($order_by, $order_by_val)
                    ->paginate($perpage, ['*'], 'page', $current_page);
            }

            if ($response->count()) {
                foreach ($response as $key => $row) {
                    $response[$key]->category_image   = getCategoryImageUrl($row->img);
                    $response[$key]->created_at     = getDateFormat($row->created_at, '1');
                    $response[$key]->updated_at     = getDateFormat($row->updated_at, '1');
                }
            }
        } catch (QueryException $e) {
            $response = ['status' => 'fail', 'msg' => $e->getMessage()];
        }
        
        return $response;
    }

    public function categoryTypeListData(Request $request)
    {
        $page_type = $request->page_type ?? 'category';
        $perpage   = $request->pq_rpp ?? getPagination('limit');
        $request->page = $current_page = $request->pq_curpage ?? 1;
        $start_index = ($current_page - 1) * $perpage;

        $order_by   = 'cd.category_name'; 
        $order_by_val = 'asc'; 

        if ($request->pq_sort) {
            $sort_data   = jsonDecodeArr($request->pq_sort);
            $order_by    = $sort_data[0]['dataIndx'];
            $order_by_val = ($sort_data[0]['dir'] == 'up') ? 'asc' : 'desc';
        }

        try {

            $query = DB::table(with(new \App\Category)->getTable().' as pc')
                ->leftJoin(with(new \App\CategoryDesc)->getTable().' as cd', 'cd.cat_id', '=', 'pc.id')
                ->leftJoin(with(new \App\ParentCategory)->getTable().' as parent_cat', 'parent_cat.id', '=', 'pc.parent_id')
                ->leftJoin(with(new \App\ProductGroup)->getTable().' as pg', 'pg.id', '=', 'parent_cat.group_id')
                ->leftJoin(with(new \App\ProductSubGroup)->getTable().' as psg', 'psg.id', '=', 'parent_cat.subgroup_id')
                ->leftJoin(with(new \App\AdminUser)->getTable().' as au', 'au.id', '=', 'pc.created_by')
                ->where('pc.is_deleted', '0')
                ->where('pc.parent_id','!=', 0);

            if ($request->pq_filter) {
                $filter_req = json_decode($request->pq_filter, true);
                if (!empty($filter_req['data'])) {
                    foreach ($filter_req['data'] as $fvalue) {
                        $searchval = $fvalue['value'] ?? null;

                        switch ($fvalue['dataIndx']) {
                            case 'category_name':
                                $query->where('cd.category_name', 'like', '%'.$searchval.'%');
                                break;

                            case 'parent_name':
                                $query->where('parent_cat.category_name', 'like', '%'.$searchval.'%');
                                break;

                            case 'description':
                                $query->where('cd.description', 'like', '%'.$searchval.'%');
                                break;

                            case 'group_name':
                                $query->where('pg.name', 'like', '%'.$searchval.'%');
                                break;

                            case 'subgroup_name':
                                $query->where('psg.subgroup_name', 'like', '%'.$searchval.'%');
                                break;

                            case 'status':
                                $query->where('pc.status', 'like', '%'.$searchval.'%');
                                break;

                            case 'nick_name':
                                $query->where(function ($q) use ($searchval) { 
                                    if (!empty($searchval)) {
                                        $q->where('au.nick_name', 'like', '%'.$searchval.'%')
                                        ->orWhereNull('pc.created_by'); 
                                    } else {
                                        $q->whereNotNull('au.nick_name')
                                        ->orWhereNull('pc.created_by'); 
                                    }
                                });
                                break;

                            case 'created_at':
                                $from_date = $fvalue['value'] ?? '';
                                $to_date   = $fvalue['value2'] ?? '';
                                createDateFilter($query, 'pc.created_at', $from_date, $to_date);
                                break;

                            case 'updated_at':
                                $from_date = $fvalue['value'] ?? '';
                                $to_date   = $fvalue['value2'] ?? '';
                                createDateFilter($query, 'pc.updated_at', $from_date, $to_date);
                                break;
                        }
                    }
                }
            }

            $query = $query->select(
                'pc.id',
                'cd.category_name',
                'pc.url',
                'pc.img',
                'pc.status',
                'parent_cat.group_id',
                'parent_cat.subgroup_id',
                'pc.parent_id',
                'cd.meta_title',
                'cd.meta_keyword',
                'cd.meta_description',
                'cd.cat_description',
                'pc.is_deleted',
                'pc.created_at',
                'pc.updated_at',
                'parent_cat.category_name as parent_name',
                'pg.name as group_name',
                'pg.image as group_image',
                'psg.subgroup_name',
                'psg.images as subgroup_image',
                'au.nick_name'
            );

            $response = $query->orderBy($order_by, $order_by_val)
                ->paginate($perpage, ['*'], 'page', $current_page);

            $totrec = $response->total();

            if ($start_index >= $totrec) {
                $current_page = ceil($totrec / $perpage);
                $response = $query->orderBy($order_by, $order_by_val)
                    ->paginate($perpage, ['*'], 'page', $current_page);
            }

            if ($response->count()) {
                foreach ($response as $key => $row) {
                    $response[$key]->category_image = getProductImageUrl($row->img,'original');
                    $response[$key]->created_at = getDateFormat($row->created_at, '1');
                    $response[$key]->updated_at = getDateFormat($row->updated_at, '1');
                }
            }

        } catch (QueryException $e) {
            $response = ['status' => 'fail', 'msg' => $e->getMessage()];
        }

        return $response;
    }

}
