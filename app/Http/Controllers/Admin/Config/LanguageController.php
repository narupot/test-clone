<?php

namespace App\Http\Controllers\Admin\Config;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Language;
use Auth;
use Config;

class LanguageController extends MarketPlace
{
    
    public function __construct()
    {   
        $this->middleware('admin.user');       
    }     
    
    public function index()
    {
        $permission = $this->checkUrlPermission('list_language');
        if($permission === true) {

            $permission_arr['add'] = $this->checkMenuPermission('add_language');
            $permission_arr['edit'] = $this->checkMenuPermission('edit_language');
            //$permission_arr['delete'] = $this->checkMenuPermission('delete_language');        
   
            $language_list = Language::getLangugeDetails();
            //dd($language_list);

            return view('admin.language.languageList', ['language_list'=>$language_list, 'permission_arr'=>$permission_arr]);
        }
    }

    public function create()
    {
        $permission = $this->checkUrlPermission('add_language');
        if($permission === true) {                
        
            return view('admin.addLanguage');
        }        
    }

    public function store(Request $request)
    {       
        
        $input = $request->all();
        $validate = $this->validateLanguage($input);
		
        if ($validate->passes()) { 
            $user = Auth::guard('admin_user')->user();

            if($request->is_default == '1') {

                Language::where('isDefault', '1')->update(['isDefault' => '0']);
            }
            else {
                $request->is_default = 0;
            }

            $language = new Language();            

            if(isset($request->language_flag)) {

                $uploadDetails['path'] = Config::get('constants.language_path');
                $uploadDetails['file'] =  $request->language_flag;   
                $imageName = $this->uploadFileCustom($uploadDetails); 
                $language->languageFlag = $imageName;
            }

            $language->languageName = $request->language_name;
            $language->languageCode = $request->language_code;
            $language->isDefault = $request->is_default;        
            $language->created_by = $user->id; 

            try{            
                $language->save();
                /*update activity log start*/
                $action_type = "created"; 
                $module_name = "language";            
                $logdetails = "Admin has created ".$request->language_name." ".$module_name;
                $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

                $this->updateLogActivity($logdata);
                /*update activity log End*/
                return redirect()->action('Admin\Config\LanguageController@index')->with('succMsg', 'Language Added Successfully!');
            }
            catch (QueryException $ex) {            
                return redirect()->action('Admin\Config\LanguageController@index')->with('errorMsg', 'This Language "'.$request->language_code.'" Already Added!');
            }
        } 
        else {
            return redirect()->action('Admin\Config\LanguageController@create')->withErrors($validate)->withInput();
        }                    
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $permission = $this->checkUrlPermission('edit_language');
        if($permission === true) {        
            $language_details = Language::where('id', '=', $id)->first();
            
            return view('admin..language.editLanguage', ['language_detail'=>$language_details]); 
        }       
    }

    public function update(Request $request, $id)
    {
        if($id > 0) {
            
            $input = $request->all();
            $validate = $this->validateLanguage($input, 'edit');

            if ($validate->passes()) {            
                $user = Auth::guard('admin_user')->user();
                
                if($request->is_default == '1') {
                    
                    Language::where('isDefault', '1')->update(['isDefault' => '0']);
                }
                else {
                    $request->is_default = 0;
                }            

                $language = Language::find($id);            
                
                if(isset($request->language_flag)) {
                    
                    //echo public_path('files/language/'.$language->languageFlag);die;
                    
                    Storage::delete(Config::get('constants.language_path').$language->languageFlag);
                  
                    $uploadDetails['path'] = Config::get('constants.language_path');
                    $uploadDetails['file'] =  $request->language_flag;   
                                   
                    $imageName = $this->uploadFileCustom($uploadDetails); 
                    
                    $language->languageFlag = $imageName;
                }

                $language->languageName = $request->language_name;
                $language->languageCode = $request->language_code;
                $language->isDefault = $request->is_default;        
                $language->updated_by = $user->id; 

                try{            
                    $language->save();
                    /*update activity log start*/
                    $action_type = "updated"; 
                    $module_name = "language";            
                    $logdetails = "Admin has updated ".$request->language_name." ".$module_name;
                    $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

                    $this->updateLogActivity($logdata);
                    /*update activity log End*/
                    return redirect()->action('Admin\Config\LanguageController@index')->with('succMsg', 'Language Updated Successfully!');
                }
                catch (QueryException $ex) {            
                    return redirect()->action('Admin\Config\LanguageController@index')->with('errorMsg', 'This Language "'.$request->language_code.'" Already Added!');
                }      
            }
            else {
                  return redirect()->action('Admin\Config\LanguageController@edit', $id)->withErrors($validate)->withInput();
            }
        }
    }

    public function destroy($id)
    {   
        $currency = Language::find($id);

        $currency->delete();

        return redirect()->action('Admin\Config\LanguageController@index')->with('succMsg', 'Language Deleted Successfully!');
    } 

    function validateLanguage($input, $type=null) {   

        $rules['language_name'] = 'Required';
        $rules['language_code'] = 'Required';
        if(empty($type)){
            $rules['language_flag'] = 'Required';
        }        

        $error_msg['language_name.required'] = 'Please enter language';
        $error_msg['language_code.required'] = 'Please enter language code';
        $error_msg['language_flag.required'] = 'Please select image';      

        $validate = Validator::make($input, $rules, $error_msg);
        //echo '<pre>';print_r($validate->errors());die;
        return $validate; 
    }       
}
