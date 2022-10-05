<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\MarketPlace;
//use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Config;
use Lang;
use Auth;
use App\CustomCss;

class AdminHomeController extends MarketPlace
{
    
    public function __construct()
    {
        $this->middleware('admin.user');
    }
    
    public function index(Request $request)
    {   
        $langcount = \App\Language::where(['isDefault'=>'1','status'=>'1'])->count();
        $language = ($langcount > 0) ? true : false;
        $allsection = $this->allconfig();

       
        return view('admin.admin-home',['totalcomplete','persection','allsection','ordAmtTot']);
    }   

    private function allconfig(){
        $allsection = [];
        $allsection['language'] = ['text'=>Lang::get('admin.language'),'url'=>action('Admin\Config\LanguageController@index')];
        
        return $allsection;
    }

    function dismiss(Request $request){
        $id = !empty($request->id)?$request->id:0;
        $user_id = \Auth::guard('admin_user')->user()->id;
        $curdate = date('Y-m-d H:i:s');
        if($id == 'all'){
            \App\CustomizeSection::where('status','0')->update(['status'=>'1','updated_at'=>$curdate,'updated_by'=>$user_id]);
            $response = ['status'=>'success'];
        }else{
            $cust = \App\CustomizeSection::find($id);
            if(!empty($cust)){
                $cust->status = '1';
                $cust->updated_by = $user_id;
                $cust->updated_at = $curdate;
                $cust->save();
                $response = ['status'=>'success'];
            }else{
                $response = ['status'=>'error'];
            }
        }
        return $response;
    }
    
        public function customeCss(Request $request){

        $customadmincss =Config('constants.css_url').'admin_custom_style.css?ver=1.1.1';
        $revisions = CustomCss::select('*')->orderBy('updated_at')->get();
        $last_update = CustomCss::select('*')->orderBy('id', 'DESC')->first();
        //dd($last_update);
        $revision = count($revisions);
        $css ='';
        //dd($customadmincss);
        $css = @file_get_contents($customadmincss);
        //dd($css);
        if (empty($css)) {
            $arrContextOptions=array(
                "ssl"=>array(
                    "verify_peer"=> false,
                    "verify_peer_name"=> false,
                ),
            );
            $css = @file_get_contents($customadmincss, false, stream_context_create($arrContextOptions));
        }
        return view('admin.customcss.customeCss',['css'=>$css,'revision'=>$revision,'last_update'=>$last_update]);
    }
    public function customeCssSet(Request $request){
        //dd($request->css);
        $data = $request->css;
        $customadmincss =base_path("public/css/admin_custom_style.css");
        file_put_contents($customadmincss, $data);
        $customecss = new CustomCss;
        $customecss->value = $request->css;
        $customecss->created_by = Auth::guard('admin_user')->user()->id;
        $customecss->updated_by = Auth::guard('admin_user')->user()->id;
        $customecss->save();
        $successMessage =  Lang::get('common.records_added_successfully');
        /*update activity log start*/
            $action_type = "update"; 
            $module_name = "customecss";            
            $logdetails = "Admin has update ".$module_name;
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/
        return $response =['status'=>'success', 'message' =>$successMessage];
        //return redirect()->action('Admin\AdminHomeController@customeCss')->with('succMsg', Lang::get('common.records_added_successfully'));
    }
    public function setPreviewdata(Request $request){
    
        $update_data = \App\CmsPreview::updatePreview($request);
        if(!empty($update_data)){
            return redirect(action('HomeController@preview',$update_data->id));
        }
        return action('HomeController@preview',$update_data->id);
        /*dd($update_data);
        $data = $request->page_desc[session('admin_default_lang')];
        if($data){
            cache_putData('preview_data',$data,3);
            return ['status'=>'success','url'=>action('HomeController@preview')];
        }else{
            return['status'=>'fail'];
        }*/
        
    }
}
