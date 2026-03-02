<?php

namespace App\Http\Controllers\Admin\Translation;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

use App\TranslationSource;
use App\TranslationModule;
use App\Language;
use App\Menu;
use App\TranslationSourceDetails;

use Auth;
use Config;
use File;
use Excel;
use Lang;

class TranslationController extends MarketPlace
{
    public function __construct()
    {   

        $this->middleware('admin.user');      
    }     

    public function index(Request $request) {

        $permission = $this->checkUrlPermission('manage_translation');
        if($permission === true) {

            if(isset($request->type) && $request->type > 0) {
                $module_type = $request->type;
                $results = TranslationModule::select('id', 'module_name')->where('used_on', $module_type)->orderby('id', 'DESC')->get();
            }
            else {
                $module_type = 0;
                $results = TranslationModule::select('id', 'module_name')->orderby('id', 'DESC')->get();
            }            

            $tranSourceCount = TranslationSource::select( [DB::raw('count(id) as count_id'), 'module_id'])->groupBy('module_id')->get();

            $tranSourceeArray = array(); 
            foreach($tranSourceCount as $tranSourcees){
               $tranSourceeArray[$tranSourcees->module_id] = $tranSourcees->count_id;
            }

            $tranSourceDeatilsCount = TranslationSourceDetails::select( [DB::raw('count(id) as count_id, max(updated_at) as last_updated'), 'module_id', 'lang_id'])->groupBy(['module_id', 'lang_id'])->get();

            $percentcal = array();
            $lastUpdated = array();
            foreach( $tranSourceDeatilsCount  as $transSourceDetail){
              $percentcal[$transSourceDetail->module_id][$transSourceDetail->lang_id] = ceil(($transSourceDetail->count_id/$tranSourceeArray[$transSourceDetail->module_id] )*100);

               $lastUpdated[$transSourceDetail->module_id][$transSourceDetail->lang_id]  = getDateFormat($transSourceDetail->last_updated, 4);

            }
            $languages = Language::select('id','languageName')->where('status','1')->get();

            return view('admin.translation.translationList', ['results'=>$results, 'languages'=> $languages, 'percentcal'=>$percentcal, 'lastUpdated' => $lastUpdated, 'module_type'=>$module_type]);
        }
    }

    public function store(Request $request) {        
        //dd($request->all());
        $user_id = Auth::guard('admin_user')->user()->id;
        $insert = array();
		$sources = array_filter($request->sources);
		$checkboxes = $request->checkboxes;	
		$module_id = $request->module_id;
        
        if(isset($request->updateall) && !empty($sources)){ //Add and Update All
			foreach ($sources as $key=>$data) {
                try{
    			    $affected = TranslationSource::where(['id' => $key, 'module_id' => $module_id ])->update(['source' => $this->alias($data, '_'), 'comment' => $request->comments[$key], 'updated_by' => $user_id]);
    		        if(empty($affected)) {
    	               $insert[$key] = ['module_id' => $module_id, 'source' => $this->alias($data, '_'),'comment' => $request->comments[$key], 'created_by' => $user_id];
    		        }
                }catch(QueryException $e)  {
                    return redirect()->action('Admin\Translation\TranslationController@addsource', $module_id)->with('errorMsg', '"'.$data.'" '.Lang::get('translation.this_key_already_exist'));
                } 
			}

            $this->updateTransLog($module_id);

			if(!empty($insert)){
                try{        
                    TranslationSource::insert($insert);
                    return redirect()->action('Admin\Translation\TranslationController@index')->with('succMsg', Lang::get('common.records_updated_successfully'));
                }catch(QueryException $e)  {
                    return redirect()->action('Admin\Translation\TranslationController@addsource', $module_id)->with('errorMsg', Lang::get('translation.some_keys_already_exist'));
                } 
			}else{
				 return redirect()->action('Admin\Translation\TranslationController@index')->with('succMsg', Lang::get('common.records_updated_successfully'));
			}         
		}
        else if(isset($request->updateselected) && !empty($checkboxes) && !empty($sources)){  //Add and Update selected

			foreach ($checkboxes as $key=>$data) {
                try{
                	
                    $affected = TranslationSource::where(['id' => $key, 'module_id' => $module_id ])->update(['source' => $this->alias($sources[$key], '_'), 'comment' => !empty($request->comments[$key])?$request->comments[$key]:'', 'updated_by' => $user_id]);
                    if (empty($affected)) {
                        $insert[$key] = ['module_id' => $module_id, 'source' => $this->alias($sources[$key], '_'),'comment' => $request->comments[$key], 'created_by'=> $user_id]; 
                    }
                }catch(QueryException $e)  {
                    return redirect()->action('Admin\Translation\TranslationController@addsource', $module_id)->with('errorMsg', '"'.$data.'" '.Lang::get('translation.this_key_already_exist'));
                } 
			}
            $this->updateTransLog($module_id);
			if(!empty($insert)){
			    try{        
                    TranslationSource::insert($insert);
                    return redirect()->action('Admin\Translation\TranslationController@index')->with('succMsg',  Lang::get('common.records_updated_successfully'));
                }catch(QueryException $e)  { 
		            return redirect()->action('Admin\Translation\TranslationController@addsource', $module_id)->with('errorMsg', Lang::get('translation.some_keys_already_exist'));
			     } 
			}else{
                return redirect()->action('Admin\Translation\TranslationController@index')->with('succMsg',  Lang::get('common.records_updated_successfully'));
			}         
		}
        else if(isset($request->removeseleced) && !empty($checkboxes) && !empty($sources)){   //delete  selected

         	$delete_data = array();
            foreach ($checkboxes as $key=>$data) {
                $delete_data[] = $key;
            }
			if(!empty($delete_data)){
                TranslationSource::whereIn('id', $delete_data)->delete();
                $this->updateTransLog($module_id);
                return redirect()->action('Admin\Translation\TranslationController@index')->with('succMsg', Lang::get('common.records_deleted_successfully'));
			} 
        }
        else if(isset($request->removeall)) {    //delete All
            
         	TranslationSource::where('module_id', $module_id)->delete();
            $this->updateTransLog($module_id);
            return redirect()->action('Admin\Translation\TranslationController@index')->with('succMsg', Lang::get('common.records_deleted_successfully'));
	    }
        else {
            return redirect()->action('Admin\Translation\TranslationController@addsource', $module_id);
        }
    }

    private function updateTransLog($module_id){
        $moduledata = TranslationModule::find($module_id);
        /*update activity log start*/
        $action_type = "updated"; 
        $module_name = "translation module";            
        $logdetails = "Admin has updated ".$moduledata->module_name." ".$module_name;
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

        $this->updateLogActivity($logdata);
        /*update activity log End*/
    }

    public function addsource($module_id=null){
        
        $permission = $this->checkUrlPermission('manage_translation');
        if($permission === true) {  

            $user_id = Auth::guard('admin_user')->user()->id;
            $moduleName = TranslationModule::select('module_name')->where('id', $module_id)->first();
            $results = '';
            $results = TranslationSource::where(['module_id' => $module_id])->orderBy('id', 'desc')->get();
            return view('admin.translation.translationSourceAdd', ['module_id'=>$module_id, 'results'=>$results, 'moduleName'=>$moduleName]);
        }
    }

    public function addsourcevalue($module_id, $lang_id){

        $permission = $this->checkUrlPermission('manage_translation');
        if($permission === true) {        
        
            $user_id = Auth::guard('admin_user')->user()->id;
            $lang_name = Language::select('languageName')->where('id', $lang_id)->first();
            $moduleName = TranslationModule::select('module_name')->where('id', $module_id)->first();
            $lang_name->module_name = $moduleName->module_name;
            $results = TranslationSource::where(['module_id' => $module_id])->orderBy('id','DESC')->get();
           
            $saveddatas = TranslationSourceDetails::select('source_id', 'new_value', 'source_value', 'comment')->where(['module_id' => $module_id, 'lang_id'=>$lang_id])->get();

            $savechangetvalues = $savechangecomment = $save_new_value = array();
            foreach($saveddatas as $saveddata){
                $savechangetvalues[$saveddata->source_id]  = $saveddata['source_value'];
                $savechangecomment[$saveddata->source_id] = $saveddata['comment'];
                $save_new_value[$saveddata->source_id] = $saveddata['new_value'];
            }

            $import_status = TranslationModule::getImportDetail($module_id, $lang_id);
            if($import_status) {
                $import_detail['csv_import_status'] = 1;
                $import_detail['csv_import_date'] = $import_status->csv_import_date;
            }
            else{
                $import_detail['csv_import_status'] = 0;
                $import_detail['csv_import_date'] = '';
            }
            //dd($import_detail, $save_new_value);            

            return view('admin.translation.translationSourceValueAdd', ['module_id'=>$module_id, 'lang_id'=>$lang_id,'results'=>$results, 'lang_name'=>$lang_name, 'savechangetvalues'=>$savechangetvalues, 'savechangecomment'=>$savechangecomment, 'import_detail'=>$import_detail, 'save_new_value'=>$save_new_value]);
        }
    }

    public function addsinglesourcedata(Request $request){
        //dd($request->all());
        $user_id = Auth::guard('admin_user')->user()->id;
        $module_id = $request->module_id;
        $sources = $request->sources; 
        
        $key_arr = array_keys($sources); 
        $val_arr = array_values($sources);
        $key = array_shift($key_arr);
        $val = trim(array_shift($val_arr));
        //dd($user_id, $module_id, $key, $val);
        $moduledata = TranslationModule::find($module_id);
        $log_act = '';
        if(!empty($val) && !empty($moduledata)){
            try{
                $affected = TranslationSource::where(['id' => $key, 'module_id' => $module_id ])->update(['source' => $val, 'updated_by'=> $user_id]);
                $log_act = 'updated';
                if (empty($affected)) {
                    try{
                        $insert = ['source' => $val, 'module_id'=>$module_id, 'created_by'=> $user_id]; 
                        $translation =  TranslationSource::create($insert);
                        $json_data['id'] = $translation['id'];
                        $json_data['status'] = 'success';
                        $json_data['response'] = Lang::get('common.records_added_successfully');
                        $log_act = 'created';
                    }catch(QueryException $e)  {
                        $json_data['status'] = 'failed';
                        $json_data['response'] = Lang::get('translation.this_key_already_exist');
                    }

                }else{
                    $json_data['id'] = $key;
                    $json_data['status'] = 'success';
                    $json_data['response'] = Lang::get('common.records_updated_successfully');
                }
            }catch(QueryException $e)  {
                $json_data['status'] = 'failed';
                $json_data['response'] = Lang::get('translation.this_key_already_exist');
            }

            if($log_act){
                /*update activity log start*/
                $action_type = $log_act; 
                $module_name = "module key";            
                $logdetails = "Admin has $log_act key ".$val." in module  ".$moduledata->module_name;
                $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

                $this->updateLogActivity($logdata);
                /*update activity log End*/
            }            
        }
        else {
            $json_data['status'] = 'failed';
            $json_data['response'] = Lang::get('translation.please_enter_source_key');
        }
        echo json_encode($json_data);
    }

    public function addsourcevaluesave(Request $request){
        //dd($request->all());
        $user_id = Auth::guard('admin_user')->user()->id;
        $module_id = $request->module_id;
        $lang_id = $request->lang_id; 
        $comments = $request->comments;

        $moduledata = TranslationModule::find($module_id);
        $lang_name = \App\Language::where('id',$lang_id)->value('languageName');

        if($request->import_status == "1") {
            $source_value = $request->new_value;
        }
        else {
            $source_value = $request->source_value;
        }        

        if($request->import_status == "1" && $request->updateall == "reject"){

            foreach ($source_value as $key => $data) {
                if(!empty($data)){

                    TranslationSourceDetails::where(['source_id' => $key, 'lang_id' => $lang_id ])->update(['new_value' => '']);
                }
            }

            $this->resetImportData($module_id, $lang_id);

            return redirect()->action('Admin\Translation\TranslationController@addsourcevalue', [$module_id, $lang_id])->with('succMsg', Lang::get('common.records_updated_successfully'));                                 
        }
        else {

            $insert = array();
            foreach ($source_value as $key => $data) {
                if(!empty($data)){
                    $affected = TranslationSourceDetails::where(['source_id' => $key, 'lang_id' => $lang_id ])->update(['source_value' => $data, 'comment' => $comments[$key], 'updated_by'=> $user_id]);
                    if (empty($affected)) {
                       $insert[] = ['source_id' => $key, 'module_id'=>$module_id,  'lang_id'=> $lang_id, 'source_value' => $data, 'comment' => $comments[$key], 'created_at'=> date('Y-m-d H:i:s'), 'created_by'=> $user_id];
                    }
                }
            }

            if(!empty($insert)){
                TranslationSourceDetails::insert($insert);  
            }

            if($request->import_status == "1") {
                $this->resetImportData($module_id, $lang_id);
            }            

            $this->UpdateOrCreateLanguageKeyInFile($module_id, $lang_id);

            /*update activity log start*/
            $action_type = "updated"; 
            $module_name = "translation module";            
            $logdetails = "Admin has updated ".$moduledata->module_name." for language $lang_name";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

            return redirect()->action('Admin\Translation\TranslationController@addsourcevalue', [$module_id, $lang_id])->with('succMsg', Lang::get('common.records_updated_successfully'));         
        }
    }

    public function addsinglesourcevalue(Request $request) {
        
        $user_id = Auth::guard('admin_user')->user()->id;
        $insert = array();
        $source_value = $request->source_value;
        $module_id = $request->module_id;
        $lang_id = $request->lang_id;

        $key_arr = array_keys($source_value); 
        $val_arr = array_values($source_value);
        $key = array_shift($key_arr);
        $val = trim(array_shift($val_arr));
        //dd($user_id, $module_id, $key, $val);
        $moduledata = TranslationModule::find($module_id);
        $lang_name = \App\Language::where('id',$lang_id)->value('languageName');
        if(!empty($val)){
            try{
                $affected = TranslationSourceDetails::where(['source_id' => $key, 'lang_id' => $lang_id ])->update(['source_value' => $val, 'updated_by'=> $user_id]);
                if (empty($affected)) {
                    try{
                        $insert = ['source_id' => $key, 'module_id'=>$module_id, 'lang_id'=> $lang_id, 'source_value' => $val, 'created_at'=> date('Y-m-d H:i:s'), 'created_by'=> $user_id];

                        TranslationSourceDetails::insert($insert);

                        $this->UpdateOrCreateLanguageKeyInFile($module_id, $lang_id);

                        $json_data['status'] = 'success';
                        $json_data['response'] = Lang::get('common.records_added_successfully');
                    }catch(QueryException $e)  {
                        $json_data['status'] = 'failed';
                        $json_data['response'] = Lang::get('translation.this_key_already_exist');
                    }

                }else{
                    $this->UpdateOrCreateLanguageKeyInFile($module_id, $lang_id);

                    $json_data['status'] = 'success';
                    $json_data['response'] = Lang::get('common.records_updated_successfully');
                }


                /*update activity log start*/
                $action_type = "updated"; 
                $module_name = "translation module";            
                $logdetails = "Admin has updated ".$moduledata->module_name." for language $lang_name";
                $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

                $this->updateLogActivity($logdata);
                /*update activity log End*/ 
            }catch(QueryException $e)  {
                $json_data['status'] = 'failed';
                $json_data['response'] = Lang::get('translation.this_key_already_exist');
            }                      
        }
        else {
            $json_data['status'] = 'failed';
            $json_data['response'] = Lang::get('translation.please_enter_source_value');
        }
        echo json_encode($json_data);
    }    

    public function searchTranslation(Request $request){
        //dd($request->all());

        $languages = Language::where('status','1')->orderby('isDefault', 'DESC')->pluck('languageName', 'id');

        $modules = TranslationModule::where('status','1')->orderby('module_name', 'ASC')->pluck('module_name', 'id'); 
        $modules['0'] = 'All Modules'; 

        $results  = '';
        if(isset($request->lang_id)) {

            $query = DB::table(with(new TranslationSource)->getTable().' as ts')
                ->leftjoin(with(new TranslationSourceDetails)->getTable().' as tsd', [['ts.id', '=', 'tsd.source_id'], ['tsd.lang_id', '=',  DB::raw($request->lang_id)]])
                ->select('ts.module_id', 'ts.source', 'tsd.id', 'ts.id as source_id','tsd.lang_id', 'tsd.source_value');
                if(!empty($request->module_id)) {
                    $query->where(['ts.module_id'=>$request->module_id]);
                }
                if(isset($request->search_key) && !empty($request->search_key)) {
                    $query->where('ts.source', 'like', '%'.$request->search_key.'%')
                    ->orWhere('tsd.source_value', 'like', '%'.$request->search_key.'%');
                }                    
                
            $results = $query->get();              
       } 

       return view('admin.translation.searchTranslation', ['modules'=>$modules, 'languages'=> $languages, 'results'=>$results, 'request'=>$request]);
    }

    public function searchTranslationUpdate(Request $request) {
        //dd($request->all());
        $user_id = Auth::guard('admin_user')->user()->id;
        $source_value = $request->source_value;
        if($request->ajax()){

            $key_arr = array_keys($source_value); 
            $val_arr = array_values($source_value);
            $key_str = array_shift($key_arr);
            $key_str_arr = explode('_', $key_str);
            $val = trim(array_shift($val_arr));
            //dd($user_id, $module_id, $key, $val, $comments);
     
            if(!empty($val)){

                try{
                    $affected = TranslationSourceDetails::where(['id' => $key_str_arr['0']])->update(['source_value' => $val, 'updated_by'=> $user_id]);
                    if (empty($affected)) {
                        try{
                            $insert = ['source_id' => $key_str_arr['1'], 'module_id'=>$key_str_arr['2'], 'lang_id'=> $request->lang_id, 'source_value' => $val, 'created_at'=> date('Y-m-d H:i:s'), 'created_by'=> $user_id];

                            TranslationSourceDetails::insert($insert);

                            $this->UpdateOrCreateLanguageKeyInFile($key_str_arr['2'], $request->lang_id);

                            $json_data['status'] = 'success';
                            $json_data['response'] = Lang::get('common.records_added_successfully');
                        }catch(QueryException $e)  {
                            $json_data['status'] = 'failed';
                            $json_data['response'] = Lang::get('translation.this_key_already_exist');
                        }                            
                    }else{
                        $this->UpdateOrCreateLanguageKeyInFile($key_str_arr['2'], $request->lang_id);

                        $json_data['status'] = 'success';
                        $json_data['response'] = Lang::get('common.records_updated_successfully');
                    }
                }catch(QueryException $e)  {
                    $json_data['status'] = 'failed';
                    $json_data['response'] = Lang::get('translation.this_key_already_exist');
                }                            
            }
            else {
                $json_data['status'] = 'failed';
                $json_data['response'] = Lang::get('translation.please_enter_source_value');
            }
            echo json_encode($json_data);
        }
    }

    public function deleteSingleSource(Request $request) {

        if($request->ajax() && $request->id > 0){
            $source = TranslationSource::where('id', $request->id)->delete();
            return 'sucess'; 
        } 
    }

    public function UpdateOrCreateLanguageKeyInFile($module_id, $lang_id){
                     
        $folder_name = Language::select('languageCode')->where('id', $lang_id)->first();
        $langFileName = TranslationModule::select('lang_file_name')->where(['id' => $module_id])->first();
        $file_complete_path = Config('constants.multi_language_path').'/'.$folder_name->languageCode.'/'.$langFileName->lang_file_name.'.php';
        $saveddatas = TranslationSourceDetails::select('source_id', 'source_value', 'comment')->with('sourceName')->where(['module_id' => $module_id, 'lang_id'=>$lang_id])->get();

        $file_content = '<?php'."\n\n".'return ['."\n\n";
        foreach($saveddatas as $key => $value){
            $file_content .= "'".$value->sourceName->source."'=>'".addslashes($value->source_value)."',\n";
        }
                
        $file_content .= "\n\n".'];';
        File::put($file_complete_path, $file_content);        
    }

    public function exportSource($module_id, $lang_id) {
        //dd( $module_id, $lang_id);

        if($module_id > 0) {
            
            $language = Language::select('languageCode')->where('id', $lang_id)->first();
            $translation_module = TranslationModule::select('lang_file_name')->find($module_id);

            return Excel::download(new \App\Exports\TranslationExport($module_id, $lang_id, $language->languageCode), $translation_module->lang_file_name.'_'.$language->languageCode.'.xlsx');
            
            //return (new \App\Exports\TranslationExport($module_id, $lang_id))->download('invoices.xls');
            /*$sources_key = TranslationModule::getModuleSourceValue($module_id, $lang_id);
            $language = Language::select('languageCode')->where('id', $lang_id)->first();
            $sources_key_arr[] = ['source', $language->languageCode , 'comment'];
            foreach($sources_key as $source) {
                $sources_key_arr[] = [$source->source, $source->source_value, $source->comment];
            }
           // return (new \App\Exports\TranslationExport($sources_key_arr))->download('invoices.xls', \Maatwebsite\Excel\Excel::XLS);

            return Excel::download(new TranslationExport, 'users.xlsx');*/
            /*$sources_key = TranslationModule::getModuleSourceValue($module_id, $lang_id);
            if(count($sources_key) > 0) {
                $sources_key_arr[] = ['source', $language->languageCode , 'comment'];
                foreach($sources_key as $source) {
                    $sources_key_arr[] = [$source->source, $source->source_value, $source->comment];
                }        

                try{
                    


                    //return (new InvoicesExport)->download('invoices.xlsx');

                    Excel::create($source->lang_file_name.'_'.$language->languageCode, function($excel) use ($sources_key_arr) {

                    $excel->sheet('Sheet 1', function($sheet) use ($sources_key_arr) {
                        $sheet->fromArray($sources_key_arr, null, 'A1', false, false);
                    });
                    })->export('xls');


                }catch(\Exception $e){
                    dd($e->getMessage());
                }
                
            }*/
        }
    }

    public function importSource(Request $request) {
        //dd($request->all());
        $update_status = 'N';
        if(Input::hasFile('import_file') && !empty($request->module_id) && !empty($request->lang_id)){

            $import_status = TranslationModule::getImportDetail($request->module_id, $request->lang_id);
            if(count($import_status) > 0) {
                return redirect()->action('Admin\Translation\TranslationController@index')->with('errorMsg', Lang::get('translation.sorry_file_is_already_imported_please_update_imported_file_first'));
            }

            $language = Language::select('languageCode')->where('id', $request->lang_id)->first();
            $languageCode = $language->languageCode;

            $path = Input::file('import_file')->getRealPath();
            $data = Excel::load($path, function($reader) {
                //dd($reader);
                //dd($reader->getTitle());
            })->get();

            $heading_arr = $data->first()->keys()->toArray();
            $sheet_lang = $heading_arr['1'];

            if(!empty($data) && $data->count() && $languageCode == $sheet_lang){
                $insert = [];
                $admin_id = Auth::guard('admin_user')->user()->id;
                foreach ($data as $key => $value) {
                    if(!empty($value->$languageCode) && !empty($value->source)) {
                        $source_key = TranslationSource::select('id')->where(['module_id'=>$request->module_id, 'source'=>$value->source])->first();
                        if(count($source_key) > 0){
                            $affected = TranslationSourceDetails::where(['source_id'=>$source_key->id, 'lang_id'=>$request->lang_id])->update(['new_value'=>$value->$languageCode, 'comment'=>$value->comment, 'updated_by'=>$admin_id]);
                            if(empty($affected)) {
                                $insert[] = ['source_id'=>$source_key->id, 'module_id'=>$request->module_id, 'lang_id'=>$request->lang_id , 'new_value'=>$value->$languageCode, 'comment'=>$value->comment, 'created_by'=>$admin_id];
                            }
                            $update_status = 'Y';
                        }
                    }
                }
                if(!empty($insert)){
                    TranslationSourceDetails::insert($insert);
                }
            }
        }

        if($update_status == 'Y'){

            \App\TranslationImport::insert(['module_id'=>$request->module_id, 'lang_id'=>$request->lang_id, 'csv_import_date'=>date('Y-m-d H:i:s')]);

            return redirect()->action('Admin\Translation\TranslationController@index')->with('succMsg', Lang::get('translation.file_imported_successfully'));
        }        
        else {

            return redirect()->action('Admin\Translation\TranslationController@index')->with('errorMsg', Lang::get('translation.sorry_file_not_imported_file_has_some_error'));
        }      
    }    

    public function resetImportData($module_id, $lang_id) {

        \App\TranslationImport::where(['module_id'=>$module_id, 'lang_id'=>$lang_id])->delete();

        TranslationSourceDetails::where(['module_id'=>$module_id, 'lang_id'=>$lang_id])->update(['new_value'=>'']);
    }        

    public function create($id){
    }    

    public function show($id)
    {
    }

    public function edit($id)
    {
    }

    public function update(Request $request, $id)
    {
    }

    public function destroy($id)
    {
    }    
}
