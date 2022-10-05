<?php
namespace App\Http\Controllers\Admin\Banner;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

use App\BannerGroup;
use App\Banner;
use App\BannerDesc;
use App\Language;
use Auth;
use Config;
use Lang;

class BannerController extends MarketPlace
{
    public $tableBannerDesc;
    public $lang_id;
    public $groups;

    public function __construct()
    {   
        $this->middleware('admin.user');
        $this->tableBannerDesc = with(new BannerDesc)->getTable(); 
        $this->lang_id = Language::where('isDefault', '1')->pluck('id')->first();
        $this->groups = BannerGroup::where('status', '1')->pluck('group_name', 'id')->toArray();
        //$this->groups = array('0'=>'Please select Group') + $this->groups;
    }     
    
    public function index()
    {
        $permission = $this->checkUrlPermission('list_banner');
        if($permission === true) {
            $permission_arr['add'] = $this->checkMenuPermission('add_banner');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_banner');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_banner');
            $results = Banner::get();
            //dd($this->groups);
            //dd($results);
            return view('admin.banner.BannerList', ['results' => 
                $results, 'groups'=> $this->groups ,'permission_arr'=>$permission_arr]);
        }
    }

    public function create()
    { 
        $permission = $this->checkUrlPermission('add_banner');
        $banner_groups_data = BannerGroup::select('id','group_name','status','width','height')->get()->toJson();
        $page = 'banner_add';
        if($permission === true) {
            return view('admin.banner.addBanner', ['groups'=> $this->groups,'page'=>$page,'banner_groups_data'=>$banner_groups_data]);
        }
    }

    public function store(Request $request)
    {        
        
        $banner_image = $request->banner_image;
        $user_id = Auth::guard('admin_user')->user()->id;
        $this->validate($request, 
                [
                 'title' => 'required',
                 'banner_image' => 'required',
                 'start_date' => 'required'
                ]
        );

        $insertresult = new Banner;
        /*upload image*/

        if(!empty($banner_image)){
            $banner_path = Config::get('constants.banner_path').'/';
            $banner_image_name = 'banner'.md5(microtime()).'.png';
            $banner_image_data = $this->base64UploadImage($banner_image,$banner_path,$banner_image_name);
            if($banner_image_data){
                $insertresult->banner_image = $banner_image_name;
            }
        }


      
        $insertresult->created_by = $user_id;
        $insertresult->updated_by = $user_id;
        $insertresult->group_id = $request->group_id; 
        $insertresult->start_date = date('Y-m-d',strtotime($request->start_date));
        $insertresult->end_date = $request->end_date?date('Y-m-d',strtotime($request->end_date)):'';
        $insertresult->admin_title = $request->title;
        $insertresult->url_target = $request->url_target;
        $insertresult->additional_html_code = $this->addslashes($request->additional_html_code);
        $insertresult->sort_order = $request->sort_order or 0;
        
        $insertresult->banner_url = $request->banner_url;
        $insertresult->status = $request->status;
        $insertresult->save();

        $banner_id = $insertresult->id;
        $data = array();
        $default_title = $request->banner_title[session('default_lang')];

        foreach ($request->banner_title as $key => $value) {
            if(empty($value)) {
                $value = $default_title;
            }

            $data[] = ["banner_id" => $banner_id, "lang_id" => $key, "banner_title" => $value];
        }
        DB::table($this->tableBannerDesc)->insert($data);

        /*update activity log start*/
        $action_type = "created"; 
        $module_name = "banner";            
        $logdetails = "Admin has created ".$default_title." " .$module_name;
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/

        if($request->submit_type == 'submit_continue') {
            return redirect()->action('Admin\Banner\BannerController@edit', $insertresult->id)->with('succMsg', Lang::get('common.records_added_successfully'));
        }
        else {
            return redirect()->action('Admin\Banner\BannerController@index')->with('succMsg', Lang::get('common.records_added_successfully'));
        }
    }

    public function show($id)
    {

    }

    public function edit($id)
    {
        $result = Banner::with('bannerdesc')->find($id);
        if(!$result){
            abort(404);
        }
        $permission = $this->checkUrlPermission('edit_banner');
        $banner_groups_data = BannerGroup::select('id','group_name','status','width','height')->get()->toJson();

        $page = 'banner_add';
        if($permission === true) {
            //$this->groups = BannerGroup::where('status', '1')->pluck('group_name', 'id');
            return view('admin.banner.editBanner', ['result'=>$result, 'groups'=>$this->groups, 'tableBannerDesc'=>$this->tableBannerDesc,'page'=>$page,'banner_groups_data'=>$banner_groups_data]);
        }
    }

    public function update(Request $request, $id)
    {

        //dd($request->all());

        $rules['title'] = 'required';
        //$rules['banner_image'] = 'required';
        $rules['start_date'] = 'required|date';

        $this->validate($request, $rules);

        $user_id = Auth::guard('admin_user')->user()->id;

        $insertresult = Banner::find($id);
        
        $banner_image = $request->banner_image;

        if(!empty($banner_image)){
            $banner_path = Config::get('constants.banner_path').'/';
            $banner_image_name = 'banner'.md5(microtime()).'.png';
            $banner_image_data = $this->base64UploadImage($banner_image,$banner_path,$banner_image_name);
            if($banner_image_data){
                $insertresult->banner_image = $banner_image_name;
            }
        }
        $insertresult->updated_by = $user_id;
        $insertresult->group_id = $request->group_id; 
        $insertresult->start_date = date('Y-m-d',strtotime($request->start_date));
        $insertresult->end_date = $request->end_date?date('Y-m-d',strtotime($request->end_date)):'';
        $insertresult->admin_title = $request->title;
        $insertresult->url_target = $request->url_target;
        $insertresult->additional_html_code = $this->addslashes($request->additional_html_code);
        $insertresult->sort_order = $request->sort_order or 0;

        $insertresult->banner_url = $request->banner_url;
        $insertresult->status = $request->status;
        $insertresult->save();

        $default_title = $request->banner_title[session('default_lang')];

        foreach ($request->banner_title as $key => $value) {

            if(empty($value)) {
                $value = $default_title;
            }                
                    
            $data_arr = ["banner_id" => $id, "lang_id" => $key, "banner_title" => $value];
             BannerDesc::updateOrCreate(['banner_id' => $id, 'lang_id' => $key], $data_arr);
        }

        /*update activity log start*/
        $action_type = "updated"; 
        $module_name = "banner";            
        $logdetails = "Admin has updated ".$default_title." " .$module_name;
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/

        return redirect()->action('Admin\Banner\BannerController@index')->with('succMsg', Lang::get('admin.record_updated_successfully'));
    }

    public function destroy($id)
    {
        $result = Banner::find($id);
        if (!$result) {
            abort(404);
        }

        try {

            $namedesc = $result->bannerdesc;
            $logname = !empty($namedesc)?$namedesc->banner_title:$id;

            $result->delete();
            
            /*update activity log start*/
            $action_type = "deleted"; 
            $module_name = "banner";            
            $logdetails = "Admin has deleted ".$logname." ";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/
            return redirect()->action('Admin\Banner\BannerController@index')->with('succMsg', 'The Records has been deleted.');
        } catch (QueryException $e) {
            return redirect()->route('Admin\Banner\BannerController@index')->with('succMsg', 'Whoops, looks like something went wrong.');
        }
    }

    public function addCategoryBanner($banner_id){

        return view('admin.banner.addCategoryBanner');
    }
}
