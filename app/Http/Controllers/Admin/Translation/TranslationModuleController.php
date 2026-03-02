<?php
namespace App\Http\Controllers\Admin\Translation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

use App\TranslationModule;
use App\Language;
use App\Menu;
use Auth;
use Config;
use Lang;

class TranslationModuleController extends MarketPlace
{
    public function __construct()
    {   
        
        $this->middleware('admin.user');       
    }      

    public function index(Request $request)
    {
        
        $permission = $this->checkUrlPermission('manage_translation_module');
        if($permission === true) { 
            
            if(isset($request->type) && $request->type > 0) {
                $module_type = $request->type;
                $results = TranslationModule::where('used_on', $module_type)->get();
            }
            else {
                $module_type = 0;
                $results = TranslationModule::all();
            }
            return view('admin.translation.translationModuleList', ['results' => $results, 'module_type'=>$module_type]);
        }
    }

    public function create()
    {
        $permission = $this->checkUrlPermission('manage_translation_module');
        if($permission === true) {
            return view('admin.translation.translationModuleAdd');
        }

    }

    public function store(Request $request)
    {        
        //dd($request->all());
        $user_id = Auth::guard('admin_user')->user()->id;

        $input = $request->all();   
        $rules = ['module_name' => 'required|unique:'.with(new TranslationModule)->getTable()]; 
        $error_msg['module_name.required'] = Lang::get('translation.please_enter_module_name');
        $error_msg['module_name.unique'] = Lang::get('translation.module_name_already_exist');
        $validate = Validator::make($input, $rules, $error_msg);

        if ($validate->passes()) {

            $insertresult = new TranslationModule;
            $insertresult->created_by = $user_id;
            $insertresult->module_name = $request->module_name;
            $insertresult->lang_file_name = $this->alias($request->module_name, '_');
            $insertresult->remark = $request->remark;
            $insertresult->status = $request->status;
            $insertresult->save();
            
            $languages = Language::select('languageCode')->where('status','1')->get();
            foreach($languages as $language){
                $folder_path = $this->checkOrCreateDirectory($language->languageCode);
                $this->checkOrCreateFile($folder_path, $insertresult->lang_file_name);
            }

            /*update activity log start*/
            $action_type = "created"; 
            $module_name = "translation module";            
            $logdetails = "Admin has created ".$request->module_name." ".$module_name;
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

            return redirect()->action('Admin\Translation\TranslationModuleController@index')->with('succMsg', Lang::get('translation.module_name_added_successfully'));
        }
        else {
          return redirect()->action('Admin\Translation\TranslationModuleController@create')->withErrors($validate)->withInput();
        }             
    }

    public function show($id)
    {
    }

    public function edit($id)
    {
        $permission = $this->checkUrlPermission('manage_translation_module');
        if($permission === true) {

            $result = TranslationModule::find($id);
            if (!$result) {
                abort(404);
            }
            return view('admin.translation.translationModuleEdit', ['result'=>$result]);
        }
    }

    public function update(Request $request, $id)
    {
        //dd($request->all());
        $user_id = Auth::guard('admin_user')->user()->id;

        $input = $request->all();   
        $rules = ['module_name' => 'required']; 
        if(!empty(trim($request->module_name))) {
            $rules['module_name'] = Rule::unique(with(new TranslationModule)->getTable())->ignore($id);
        }        
        $error_msg['module_name.required'] = Lang::get('translation.please_enter_module_name');
        $error_msg['module_name.unique'] = Lang::get('translation.module_name_already_exist');
        $validate = Validator::make($input, $rules, $error_msg);

        if ($validate->passes()) {        
         
            $insertresult = TranslationModule::find($id);
            $insertresult->updated_by = $user_id;
            $insertresult->module_name = $request->module_name;
            $insertresult->remark = $request->remark;
            $insertresult->status = $request->status;
            $insertresult->save();

            $languages = Language::select('languageCode')->where('status','1')->get();
            foreach($languages as $language){
              $folder_path = $this->checkOrCreateDirectory($language->languageCode);
              $this->checkOrCreateFile($folder_path, $insertresult->lang_file_name);
            }

            /*update activity log start*/
            $action_type = "updated"; 
            $module_name = "translation module";            
            $logdetails = "Admin has updated ".$request->module_name." ".$module_name;
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/
            return redirect()->action('Admin\Translation\TranslationModuleController@index')->with('succMsg', Lang::get('common.records_updated_successfully'));
        }
        else {
            return redirect()->action('Admin\Translation\TranslationModuleController@edit', $id)->withErrors($validate)->withInput();
        }            
    }

    public function deleteModule($id)
    {
        $permission = $this->checkUrlPermission('manage_translation_module');
        if($permission === true) {

            $result = TranslationModule::find($id);
            try {
                $result->delete();

                $languages = Language::select('languageCode')->where('status','1')->get();
                foreach($languages as $language){

                    $file_path = Config('constants.multi_language_path').'/'.$language->languageCode.'/'.$result->lang_file_name.'.php';

                    $this->fileDelete($file_path);
                }        

                /*update activity log start*/
                $action_type = "deleted"; 
                $module_name = "translation module";            
                $logdetails = "Admin has deleted ".$result->module_name." ";
                $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

                $this->updateLogActivity($logdata);
                /*update activity log End*/        

                return redirect()->action('Admin\Translation\TranslationModuleController@index')->with('succMsg', Lang::get('common.records_deleted_successfully'));
            }
            catch (QueryException $e) {

              return redirect()->action('Admin\Translation\TranslationModuleController@index')->with('errorMsg', Lang::get('translation.please_delete_all_keys_of_this_module_first'));
            }
        }
    }

    public function insertDataFromFile(Request $request){
        $dir = Config('constants.public_path').'/en/';

        $b = scandir($dir,1);
        foreach ($b as $key => $value) {
            if ($value{0} !== '.') {
                
                $module_key = str_replace('.php', '', $value);
                $module_name = ucfirst(str_replace('_',' ', $module_key));
                if(strpos($module_key, 'admin')!==false){
                    $used_on = '2';
                }else{
                    $used_on = '1';
                }

                try{
                    $insertresult = new TranslationModule;
                    $insertresult->module_name = $module_name;
                    $insertresult->lang_file_name = $module_key;
                    $insertresult->used_on        = $used_on;
                    $insertresult->status = '1';
                    $insertresult->save();
                    $module_id = $insertresult->id;

                    
                    $file = include $dir.$value;
                   
                    if(is_array($file)){
                        foreach ($file as $lkey => $lvalue) {
                            if(!is_array($lvalue) && !empty($lvalue)){
                                //echo $lkey.' = '.$lvalue.'<br>';
                                $sourceObj = new \App\TranslationSource;
                                $sourceObj->module_id = $module_id;
                                $sourceObj->source = trim($lkey);
                                $sourceObj->save();
                                $source_id = $sourceObj->id;

                                $detailObj = new \App\TranslationSourceDetails;
                                $detailObj->source_id = $source_id;
                                $detailObj->module_id = $module_id;
                                $detailObj->lang_id = 0;
                                $detailObj->source_value = trim($lvalue);
                                $detailObj->save();
                            }
                        }
                    }
                }catch(QueryException $e){
                    print_r($e->getMessage());
                    echo '<br>';
                }
                
            }
            # code...
        }
    }
}
