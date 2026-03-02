<?php

namespace App\Http\Controllers\Admin\SEO;

use App\Http\Controllers\MarketPlace;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;     // when use Rule function
use Illuminate\Http\Request;

use App\SeoGlobal;
use App\SeoGlobalDesc;
use App\Language;
use Config;
use DB;

class SeoGlobalController extends MarketPlace
{
    private $tblSeoGlobalDesc;
    private $tblSeoGlobal;


    public function __construct()
    {   
        $this->middleware('admin.user'); 
        $this->tblSeoGlobalDesc = with(new SeoGlobalDesc)->getTable();
        $this->tblSeoGlobal = with(new SeoGlobal)->getTable();
    }     

    public function index()
    {

        $permission = $this->checkUrlPermission('manage_global_seo');

        if($permission === true) {

           $permission_arr['add'] = $this->checkMenuPermission('add_seo_global');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_seo_global');      
   
            $results = SeoGlobal::get(); 
            return view('admin.seo.SeoGlobalList', ['results'=>$results, 'permission_arr'=>$permission_arr]
            );
        }
    }

    public function create()
    {       
        $permission = $this->checkUrlPermission('add_page_seo');        
        if($permission === true) {         
          return view('admin.seo.seoglobalAdd'); 
        }       
    }

    public function store(Request $request)
    {               
        //echo '<pre>';print_r($request->all());die;
        $slug = $this->alias($request->title);
        $request->merge(array('slug' => $this->alias($slug)));
        
         $this->validate($request, 
                [ 
                  'title' => 'required|unique:seo_global',
                  //'slug' => 'required|unique:seo_global' 
                ]
         );

         
        $insertresult = new SeoGlobal;
        $insertresult->title = $request->title;
        $insertresult->slug = $this->alias($request->slug);
        $insertresult->status = $request->status;
        $insertresult->type = $request->type;
       // $insertresult->meta_robots = !empty($request->meta_robots)?$request->meta_robots:'0';
        $insertresult->save();
        /*take the insert id*/
        $seo_id = $insertresult->id;
        $data = array();
        foreach ($request->meta_title as $key => $value) {
            $data[$key] = ["seo_global_id" => $seo_id, "lang_id" => $key, "meta_title" => $value, "meta_description" => $request->meta_description[$key], "meta_keyword" => $request->meta_keyword[$key] 
            ];
        }
        DB::table($this->tblSeoGlobalDesc)->insert($data);
        /*update activity log start*/
        $action_type = "created"; 
        $module_name = "seo template";            
        $logdetails = "Admin has created ".$request->title." ".$module_name;
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/
        return redirect()->action('Admin\SEO\SeoGlobalController@index')->with('succMsg', 'Records added Successfully!');            
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $permission = $this->checkUrlPermission('edit_page_seo');        
        if($permission === true) {
            $result = SeoGlobal::where('id', '=', $id)->first();
            return view('admin.seo.seoglobaledit', ['result'=>$result, 'tblSeoGlobalDesc'=>$this->tblSeoGlobalDesc]);
        }            
    }

    public function update(Request $request, $id)
    {
        
        $this->validate($request, 
                 [
                   'title' => ['required',
                      Rule::unique('seo_global')->ignore($id, 'id'),
                   ]
                ]
                
         );

        //dd($request);

        $insertresult = SeoGlobal::find($id);
        $insertresult->title = $request->title;
        $insertresult->type = $request->type;
        $insertresult->status = $request->status;
       // $insertresult->meta_robots = !empty($request->meta_robots)?$request->meta_robots:'0';

        $insertresult->save();
        
        /*take the insert id*/
        $seo_id = $insertresult->id;
        foreach ($request->meta_title as $key => $value) {
            $affected = SeoGlobalDesc::updateOrCreate(
                   ['seo_global_id' => $seo_id, 'lang_id' => $key], 
                   ['seo_global_id' => $seo_id, 'lang_id' => $key, 
                   'meta_title' => $value, "meta_description" => $request->meta_description[$key], "meta_keyword" => $request->meta_keyword[$key]
                  ]);
            
        }

        /*update activity log start*/
        $action_type = "updated"; 
        $module_name = "seo template";            
        $logdetails = "Admin has updated ".$request->title." ".$module_name;
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/

        //DB::table($this->tblSeoGlobalDesc)->insert($data);
        return redirect()->action('Admin\SEO\SeoGlobalController@index')->with('succMsg', 'Records added Successfully!'); 
        
    }  






        
}
