<?php

namespace App\Http\Controllers\Admin\Config;

use App\Http\Controllers\MarketPlace;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;

use Validator;
use Storage;
use Config;
use View;
use Auth;
use Lang;

use App\AdminAvatar;

class AvatarController extends MarketPlace
{
    public function __construct(){   

        $this->middleware('admin.user');       
    }  

    public function index(){
        
        $permission = $this->checkUrlPermission('manage_avtar_images');
        if($permission === true) {

            $permission_arr['add'] = $this->checkMenuPermission('add_avtar');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_avtar');

    	    $avtarlist = AdminAvatar::get(); 
    	    return view::make('admin.avatar.avatarList', compact('avtarlist', 'permission_arr'));
        }
    } 

    public function create(){

        $permission = $this->checkUrlPermission('add_avtar');
        if($permission === true) {  
            return view('admin.avatar.avatarAdd');
        }
    }

    public function store(Request $request){
    	$input = $request->all();
        $validate = $this->validateAvatar($input);
        if ($validate->passes()) {        

            $avatar = new AdminAvatar();

            if(!empty($request->avatarimage)){
            $avatar_path = Config::get('constants.avtar_images_path').'/';
            $avatar_image_name = 'avatar'.md5(microtime()).'.png';
            $avatar_image_data = $this->base64UploadImage($request->avatarimage,$avatar_path,$avatar_image_name);
            if($avatar_image_data){
                $avatar->name = $avatar_image_name;
            }
            }

            $avatar->title = $request->title;
            $avatar->description = $request->descrption;
            $avatar->gender = $request->gender;
            $avatar->status = $request->status;
            $avatar->created_by = Auth::guard('admin_user')->user()->id;
            $avatar->save();

            return redirect()->action('Admin\Config\AvatarController@index')->with('succMsg', Lang::get('common.records_added_successfully'));
        } 
        else {
            //echo '<pre>';print_r($validate->errors());die;
            return redirect()->action('Admin\Config\AvatarController@create')->withErrors($validate)->withInput();
        } 
    }

    public function edit($id){

        $permission = $this->checkUrlPermission('edit_avtar');
        if($permission === true) {

    	   $avatar_detail = AdminAvatar::find($id);
    	   return view::make('admin.avatar.avatarEdit',compact('avatar_detail'));
        }
    }

    public function update(Request $request, $id){
	   //dd($request->avatar_image);
		if($id > 0){

            $input = $request->all();
            $validate = $this->validateAvatar($input, $id);
            if ($validate->passes()) {            
            
                $adminavatar = AdminAvatar::find($id);            
                if(!empty($request->avatar_image)){
                $avatar_path = Config::get('constants.avtar_images_path').'/';
                $avatar_image_name = 'avatar'.md5(microtime()).'.png';
                $avatar_image_data = $this->base64UploadImage($request->avatar_image,$avatar_path,$avatar_image_name);
                    if($avatar_image_data){
                        $adminavatar->name = $avatar_image_name;
                    }
                }

                $adminavatar->title = $request->title;
                $adminavatar->description = $request->description;
                $adminavatar->gender = $request->gender;
                $adminavatar->status =$request->status;
                $adminavatar->updated_by = Auth::guard('admin_user')->user()->id;
                $adminavatar->save();

                return redirect()->action('Admin\Config\AvatarController@index')->with('succMsg', Lang::get('common.records_updated_successfully'));
            } 
            else {
                //echo '<pre>';print_r($validate->errors());die;
                return redirect()->action('Admin\Config\AvatarController@edit', $id)->withErrors($validate)->withInput();
            }
        }
    }

    public function destroy($id)
    {   
        $currency = AdminAvatar::find($id);
        $currency->delete();
        return redirect()->route('currency.index')->with('succMsg', Lang::get('common.records_deleted_successfully'));
    }

    function validateAvatar($input, $id=null) {   

        if(empty($id)) {
            $rules['avatarimage'] = 'Required';
        } 
        $rules['title'] = nameRule();   

        $error_msg['avatarimage.required'] = Lang::get('common.please_select_image');
        $error_msg['title.required'] = Lang::get('common.please_enter_title');     
        $validate = Validator::make($input, $rules, $error_msg);
        
        return $validate; 
    }    
}
