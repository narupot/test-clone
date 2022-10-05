<?php
namespace App\Http\Controllers\Admin\Banner;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

use App\BannerGroup;
use Auth;

class BannerGroupController extends MarketPlace
{
    
    public function __construct()
    {   
        $this->middleware('admin.user');       
    }     

    public function index()
    {     
        $permission = $this->checkUrlPermission('banner_group');
        if($permission === true) {
            $permission_arr['add'] = $this->checkMenuPermission('add_banner_group');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_banner_group');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_banner_group');
            $results = BannerGroup::all();
            return view('admin.banner.BannerGroupList', ['results' => $results, 'permission_arr'=>$permission_arr]);
        }
    }

    public function create()
    {
        $permission = $this->checkUrlPermission('add_banner_group');
        if($permission === true) {
            return view('admin.banner.addBannerGroup');
        }
    }

    public function store(Request $request)
    {        


        $user_id = Auth::guard('admin_user')->user()->id;
        $request->merge(array('group_name' => $this->alias($request->group_name,'_')));
        $this->validate($request, 
                ['group_name' => 'required|unique:banner_group']
        );

        $request->group_name = $this->alias($request->group_name,'_');
        $insertresult = new BannerGroup;
        $insertresult->created_by = $user_id;
        $insertresult->group_name = $request->group_name;
        $insertresult->status = $request->status;
        $insertresult->height = $request->height;
        $insertresult->width = $request->width;
        $insertresult->save();

        $dataArr = ['type'=>'banner','type_id'=>$insertresult->id,'updated_by'=>Auth::guard('admin_user')->user()->id,'status'=>$request->status];
        \App\Block::insertBlock($dataArr);

        /*update activity log start*/
        $action_type = "created"; 
        $module_name = "banner group";            
        $logdetails = "Admin has created ".$request->group_name." ". $module_name;
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/

        return redirect()->action('Admin\Banner\BannerGroupController@index')->with('succMsg', 'Records added Successfully!');
    }

    public function show($id) {

    }

    public function edit($id)
    {
        $result = BannerGroup::find($id);
        $banners = \App\Banner::where('group_id',$id)->where('status','1')->orderBy('sort_order','asc')->get();
        $dbanners = \App\Banner::where('group_id',$id)->where('status','2')->orderBy('sort_order','asc')->get();
        
        if (!$result) {
            abort(404);
        }

        $permission = $this->checkUrlPermission('edit_banner_group');
        if($permission === true) {
            return view('admin.banner.editBannerGroup', ['result'=>$result,'banners'=>$banners, 'dbanners'=>$dbanners]);
        }
    }

    public function update(Request $request, $id)
    {
        $user_id = Auth::guard('admin_user')->user()->id;
        $request->merge(array('group_name' => $this->alias($request->group_name,'_')));
        $this->validate($request, ['group_name' => ['required',
                Rule::unique('banner_group')->ignore($id, 'id'),
            ]
        ]);
       
        
        try{
            $insertresult = BannerGroup::find($id);
            $insertresult->updated_by = $user_id;
            $insertresult->group_name = $request->group_name;
            $insertresult->status = $request->status;
            $insertresult->auto_loop = $request->auto_loop ? 'true': 'false';
            $insertresult->slide_speed = $request->slider_speed;
            $insertresult->height = $request->height;
            $insertresult->width =  $request->width;
            $insertresult->save();

            $banners = $request->banner;

            if(!empty($banners)){
                foreach($banners as $key=>$banner){
                    $order_detail = explode('_',$banner);
                    if(count($order_detail)==2){
                        $banner_id = $order_detail[0];
                        $banner_sorting_index = $order_detail[1];
                        $banner = \App\Banner::find($banner_id);
                        $banner->sort_order = $banner_sorting_index;
                        $banner->save();
                    }
                }
            }    

            $blockData = \App\Block::where(['type_id'=>$id,'type'=>'banner'])->first();
            if(!empty($blockData)){
                $blockData->status = $request->status;
                $blockData->save();
            }
                    
        }catch(\Exception $ex){
            dd($ex->getMessage());
        }

        /*update activity log start*/
        $action_type = "updated"; 
        $module_name = "banner group";            
        $logdetails = "Admin has updated ".$request->group_name." ". $module_name;
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/
        
        return redirect()->action('Admin\Banner\BannerGroupController@index')->with('succMsg', 'Records updated Successfully!');
    }

    public function destroy($id)
    {

        $result = BannerGroup::find($id);
        if(!$result) {
            abort(404);
        }

        try {
            $result->delete();
            \App\Block::where(['type_id'=>$id,'type'=>'banner'])->delete();

            /*update activity log start*/
            $action_type = "deleted"; 
            $module_name = "banner group";            
            $logdetails = "Admin has deleted ".$result->group_name." ";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/
            return redirect()->action('Admin\Banner\BannerGroupController@index')->with('succMsg', 'Records Deleted Successfully!');
        }
        catch (QueryException $e) {
          return redirect()->action('Admin\Banner\BannerGroupController@index')->with('errorMsg', 'Sorry!, Please remove all banners assigned in this group then group can be deleted.');
        }
    }
}
