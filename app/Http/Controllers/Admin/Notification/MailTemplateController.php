<?php

namespace App\Http\Controllers\Admin\Notification;

use Artisan;
use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use App\Http\Controllers\AESEncription;

use App\NotificationEvent;
use App\NotificationEventTemplate;
use App\NotificationEventTemplateDetail;
use App\MailTemplateMaster;
use App\Language;
use App\NotificationType;
use App\NotificationEventDetail;
use Config;
use Mail;
use Lang;
use App\Helpers\EmailHelpers;
use Auth;
use Exception;

class MailTemplateController extends MarketPlace
{   
    public $tableNoticeEventDesc; 
    public $server_url;
    public $server_port;
    public $client_key;

    public function __construct() {   
        $this->middleware('admin.user'); 
        $this->tableNoticeEventDesc = with(new NotificationEventDetail)->getTable();
        $this->tableNotificationSenderDetail = with(new \App\NotificationSenderDetail)->getTable();
        $this->server_url = $this->getConfigurationValue('API_SERVER');
        $this->server_port = $this->getConfigurationValue('API_PORT');
        $this->client_key = $this->getConfigurationValue('PLUGIN_PUBLIC_KEY');
        $this->default_timeout = $this->getConfigurationValue('API_DEFAULT_TIMEOUT');
        

    }     
    
    public function index() {
        $permission = $this->checkUrlPermission('manage_mail_template');
        if($permission === true) {

            $permission_arr['edit'] = $this->checkMenuPermission('add_mail_template');
            $permission_arr['add'] = $this->checkMenuPermission('add_mail_template_type');
            //dd( $permission_arr['edit']);
            $templete_type_list = NotificationEvent::with('GetNotificationEventDetails')->get();
            //dd($templete_type_list); 
            
            $templateType = NotificationType::select('id', 'name')->where('status','1')->pluck('name', 'id');

            return view('admin.notification.mailTemplateTypeList', ['templete_type_list'=>$templete_type_list, 'permission_arr'=>$permission_arr, 'templateType'=> $templateType]);
        }
    }

    public function create(Request $request) {
        
        //dd($request->all());

        $permission = $this->checkUrlPermission('add_mail_template'); 
        if($permission === true) {
            
            //$lang_used = NotificationEventTemplateDetail::where('noti_event_id', $request->tempId)->pluck('lang_id')->toArray();
            $lang_used = NotificationEventTemplateDetail::where(['noti_event_id'=> $request->tempId,'noti_type_id'=>$request->template_type])->pluck('lang_id')->toArray();
			
            $lang_lists = Language::where('status', '=', '1');
            if(count($lang_used)){
               $lang_lists = $lang_lists->whereNotIn('id', $lang_used);  
            }
            $lang_lists = $lang_lists->get(['id','languageName']);
            
            $master_templates = MailTemplateMaster::select('id', 'name')->where(['status'=>'1'])->get();

            return view('admin.notification.addMailTemplate', ['lang_lists'=>$lang_lists, 'tempId'=>$request->tempId, 'master_templates'=>$master_templates, 'template_type'=>$request->template_type]); 
        }       
    }

    public function addtemplatetype(Request $request) {

        //noti_event_id
         $permission = $this->checkUrlPermission('add_mail_template_type'); 
         if($permission === true) {
             $savedata = NotificationEvent::where(['id'=>$request->noti_event_id])->first();
             $template_type = '';
             if(count($request->template_type)>0){
                 $template_type = serialize($request->template_type);
             }
             $savedata->noti_type = $template_type;
             
             $affected = $savedata->save();

             if($affected){
                echo true;
                
             }
        }else{
            echo false;
        } 
    }

    public function store(Request $request) {               
        $input = $request->all();
        
        if($input['noti_type_id'] != '1'){
             unset($input['master_template']);
 
        }
     
        //dd($input);
               
        $validate = $this->validateMailTemplete($input); 
        
       // dd($validate);

        if ($validate->passes()) {                   
			
	    if(!empty($request->noti_type_id) && $request->noti_type_id=="6"){
		$mail_subject = "";
	    }else{
		$mail_subject = $request->mail_subject;
	    }
	    
            $user=Auth::guard('admin_user')->user();
            $mailObj = new NotificationEventTemplateDetail();            

            $mailObj->noti_event_id = $request->noti_event_id;
            $mailObj->noti_type_id = $request->noti_type_id;

            $mailObj->lang_id = $request->lang_id;
            $mailObj->mail_subject = $mail_subject;
            $mailObj->master_template_id = $request->master_template;
            $mailObj->mail_containt = $request->mail_containt;
            $mailObj->created_by = $user->id;
           
            try{
                 $mailObj->save();
                /*update activity log start*/
                $action_type = "created"; 
                $module_name = "mail template";            
                $logdetails = "Admin has created ".$request->mail_subject." ".$module_name;
                $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

                $this->updateLogActivity($logdata);
                /*update activity log End*/

                 return redirect()->action('Admin\Notification\MailTemplateController@showdetails', [$mailObj->noti_event_id, $mailObj->noti_type_id])->with('succMsg', 'Mail Template Updated Successfully!');
            }
            catch(QueryException $e){
                 return redirect()->action('Admin\Notification\MailTemplateController@showdetails', [$mailObj->noti_event_id, $mailObj->noti_type_id])->with('errorMsg', 'Mail Template in This Language Already Added!');
            }
        }
        else {
            return redirect()->action('Admin\Notification\MailTemplateController@create', ['tempId'=>$input['noti_event_id'], 'template_type'=>$input['noti_type_id']])->withErrors($validate)->withInput();
        }            
    }

    public function show(Request $request, $id) { 


        if($request->type == 'viewMail') {

            //NotificationEventTemplate::where('id', '=', $id)->first();
            //$master_template = MailTemplateMaster::select('template')->find($templete->master_template_id);

            $templete_detail = DB::table(with(new NotificationEventTemplateDetail)->getTable().' as ms')
            ->leftjoin(with(new NotificationEventTemplate)->getTable().' as mtd', 'ms.id', '=', 'mtd.noti_event_id')
            ->leftjoin(with(new NotificationEvent)->getTable().' as mt', 'ms.noti_event_id', '=', 'mt.id')
            ->leftjoin(with(new MailTemplateMaster)->getTable().' as mtm', 'mtm.id', '=', 'ms.master_template_id')
            ->select('ms.mail_subject', 'ms.mail_containt', 'mtd.sender', 'ms.noti_type_id','mtm.template')
            ->where(['ms.id'=>$id])
            ->first();
        
        //dd($templete_detail);
         if(!empty($templete_detail->template)){

            $templete_detail->mail_containt = str_replace('[CONTENT]', $templete_detail->mail_containt, $templete_detail->template);

         }else{

              $templete_detail->mail_containt = $templete_detail->mail_containt;

         }

            //dd($templete_detail);

            return view('admin.notification.viewMailTemplate', ['templete_list'=>$templete_detail]);
        }
        if($request->type == 'viewMaster') {
            $templete_list = MailTemplateMaster::getMasterTemplate($id);
            return view('admin.notification.viewMasterTemplate', ['templete_list'=>$templete_list]);
        }        

     }   

    public function showdetails(Request $request, $id, $template_type) {   
        //echo '<pre>==>'.$id;print_r($request->all());die;

        $template_data = NotificationEvent::find($id);
        //dd($templete_data);

        $templete_list = NotificationEvent::getNotificationEvent($id, $template_type);

        //dd($templete_list);

        //dd($templete_list);
        $notificationName = NotificationType::select('name')->where('id', $template_type)->pluck('name')->first();


        $permission_arr['add'] = $this->checkMenuPermission('add_mail_template');
        $permission_arr['edit'] = $this->checkMenuPermission('add_mail_template');

        return view('admin.notification.mailTemplateList', ['template_data'=>$template_data, 'templete_list'=>$templete_list, 'permission_arr'=>$permission_arr, 'template_type'=> $template_type, 'notificationName' => $notificationName]);
        
    }

    public function edit($id,$type=null) {
        $permission = $this->checkUrlPermission('add_mail_template');
        if($permission === true) {

            $lang_lists = Language::where('status', '=', '1')->get(['id','languageName']);
             
            //dd($lang_lists);

            $mail_template = NotificationEventTemplateDetail::where('id', '=', $id)->first();

            $master_templates = MailTemplateMaster::select('id', 'name')->where(['status'=>'1'])->get();
            
            return view('admin.notification.editMailTemplate', ['lang_lists'=>$lang_lists, 'mail_template'=>$mail_template, 'master_templates' => $master_templates]);
        }                
    }

    public function update(Request $request, $id) {
        if($id > 0) {
            //echo '<pre>==>'.$id;print_r($request->all());die;

            $input = $request->all();

            $validate = $this->validateMailTemplete($input); 


            if ($validate->passes()) {            

                $user=Auth::guard('admin_user')->user();
                $mailObj = NotificationEventTemplateDetail::find($id);           

                $mailObj->lang_id = $request->lang_id;
                $mailObj->mail_subject = $request->mail_subject;
                $mailObj->master_template_id = $request->master_template;
                $mailObj->mail_containt = $request->mail_containt;
                $mailObj->updated_by = $user->id;

                try{
                     $mailObj->save();
                    /*update activity log start*/
                    $action_type = "updated"; 
                    $module_name = "mail template";            
                    $logdetails = "Admin has updated ".$request->mail_subject." ".$module_name;
                    $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

                    $this->updateLogActivity($logdata);
                    /*update activity log End*/
                     return redirect()->action('Admin\Notification\MailTemplateController@showdetails', [$mailObj->noti_event_id, $mailObj->noti_type_id])->with('succMsg', 'Mail Template Updated Successfully!');
                }
                catch(QueryException $e){
                     return redirect()->action('Admin\Notification\MailTemplateController@showdetails', [$mailObj->noti_event_id, $mailObj->noti_type_id])->with('errorMsg', 'Mail Template in This Language Already Added!');
                }
            }
            else {
                return redirect()->action('MailTemplateController@edit', $id)->withErrors($validate)->withInput();
            }                               
        }
    }

    function editTemplateType(Request $request, $id, $template_type) {

        $permission = $this->checkUrlPermission('add_mail_template');
        if($permission === true) {

            $templete_type_dtl = NotificationEvent::getNotificationEventDetail($id, $template_type);

            //$sender = $this->systemConfig('EMAIL_SENDER');
            //$sender_arr = explode(',', $sender);

            $sender_arr = \App\NotificationSenderDetail::select('sender_name','id','sender_email')->where('status', '1')->get();
            $line_token = \App\LineTransmissionMethod::select('id','name','token')->where('status', '1')->get();
  
            $roles = \App\Role::getAllRoles();

            $notificationName = NotificationType::where('id', $template_type)->value('name');

            //dd($notificationName, $templete_type_dtl);

            return view('admin.notification.editMailTemplateType', ['templete_type_dtl'=>$templete_type_dtl, 'sender_arr'=>$sender_arr, 'roles'=>$roles, 'template_type'=> $template_type, 'notificationName'=>$notificationName, 'tableNoticeEventDesc' => $this->tableNoticeEventDesc,'line_token'=>$line_token]);  
        }
    }

    function updateTemplateType(Request $request) {

        //dd($request->all());die;
  
        $input = $request->all();

        $rules =[];
		
		if($request->noti_type_id == '6'){  
            $rules['token'] = 'Required';
        }
		
        if($request->noti_type_id == '1'){  
            $rules['sender'] = 'Required';
        }

        if($request->noti_type_id == '1' || $request->noti_type_id == '2'){
            $rules['recievers'] = 'Required';
        }
		if(!empty($request->token)){
			$token = implode(',',$request->token);
		}else{
			$token = '';
		}
		
        //$rules['mail_desc'] = 'Required|Min:20';      

        $error_msg['sender.required'] = 'Please select sender';

        if($request->noti_type_id == '1' || $request->noti_type_id == '2'){    
            $error_msg['recievers.required'] = 'Please select recievers';
        }

        //$error_msg['mail_desc.required'] = 'Please enter mail description';

        $validate = Validator::make($input, $rules, $error_msg);
   
        if ($validate->passes()) {  

            $data_arr['mail_desc'] = $request->mail_desc;
  
            //dd($request->all(),$token);die;

            if(isset($request->icon)) {

                $uploadDetails['path'] = Config::get('constants.social_icon_path');
                $uploadDetails['file'] =  $request->icon;
                $uploadDetails['width'] =  16;
                $uploadDetails['height'] =  16;   
                $imageName = $this->uploadFileCustom($uploadDetails); 
                NotificationEvent::where('id', $request->noti_event_id)->update(['icon' => $imageName]);

                //$topic_arr['topics_image'] = $imageName;
            }
 
            $data_arr_desc['noti_event_id'] = $request->noti_event_id;
            $data_arr_desc['noti_type_id'] = $request->noti_type_id;
            //$data_arr_desc['mail_desc'] = $request->mail_desc;

            $data_arr_desc['sender'] = $request->sender;
            $data_arr_desc['type'] = $request->type;
            $data_arr_desc['token'] = $token;  
            $data_arr_desc['to_buyer'] = '0';
            $data_arr_desc['buyer_phone_login'] = '0';
            $data_arr_desc['buyer_shipping_phone'] = '0';
            $data_arr_desc['to_seller'] = '0';  

            $admin_data_arr = array();
            if(count($request->recievers)){
                foreach ($request->recievers as $value) {
                    if($value == 'buyer') {
                        $data_arr_desc['to_buyer'] = '1';
                    }
                    if($value == 'seller') {
                        $data_arr_desc['to_seller'] = '1';
                    }
                    if($value == 'buyer_phone_login') {
                        $data_arr_desc['buyer_phone_login'] = '1';
                    }
                    if($value == 'buyer_shipping_phone') {
                        $data_arr_desc['buyer_shipping_phone'] = '1';
                    }
                    if($value != 'buyer' && $value != 'buyer_phone_login' && $value != 'buyer_shipping_phone') {
                        $admin_data_arr[] = $value;
                    }                                
                }
            }

            if(!empty($admin_data_arr)) {
                $data_arr_desc['to_admin'] = '-'.implode('-', $admin_data_arr).'-';
            }
            else {
                $data_arr_desc['to_admin'] = NULL;
            }

            if(!empty($request->cc)){
                foreach ($request->cc as $value) {
                    $cc_data_arr[] = $value;                               
                }
                $data_arr_desc['cc'] = '-'.implode('-', $cc_data_arr).'-';
            }
            
            if(!empty($request->bcc)){
                foreach ($request->bcc as $value) {
                    $bcc_data_arr[] = $value;                               
                }
                $data_arr_desc['bcc'] = '-'.implode('-', $bcc_data_arr).'-';
            }
            //dd($data_arr_desc);

            NotificationEvent::where(['id'=>$request->noti_event_id])->update($data_arr);
            NotificationEventTemplate::updateOrCreate(['noti_event_id'=> $request->noti_event_id, 'noti_type_id' => $request->noti_type_id], $data_arr_desc);

            //dd('Amit');

            /*update activity log start*/
            $action_type = "updated"; 
            $module_name = "email template";            
            $logdetails = "Admin has updated ".$request->mail_type." email type";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

            return redirect()->action('Admin\Notification\MailTemplateController@editTemplateType', [$request->noti_event_id, $request->noti_type_id])->with('succMsg', 'Records Updated Successfully!');
        }
        else {
            return redirect()->action('Admin\Notification\MailTemplateController@editTemplateType', [$request->noti_event_id, $request->noti_type_id])->withErrors($validate)->withInput();
        } 
    }

    public function editevent($id) {
        $permission = $this->checkUrlPermission('manage_mail_template');
        if($permission === true) {

            $templete_type_dtl = NotificationEvent::where('id',$id)->first();

            if (!$templete_type_dtl) {
               abort(404);
            }
 
            //dd($templete_type_dtl);

            /*$notificationName = NotificationType::select('name')->where('id', $template_type)->pluck('name')->first();*/
            // dd($notificationName);
            return view('admin.notification.editEventType', ['templete_type_dtl'=>$templete_type_dtl, 'tableNoticeEventDesc' => $this->tableNoticeEventDesc]);                 
       }
    }

    function updateeditevent(Request $request) {
       // dd($request->all());die;
        $result =  NotificationEvent::where(['id'=>$request->noti_event_id])->first(); 
          if (!$result) {
               abort(404);
          } 

          foreach ($request->mail_desc as $key => $value) {
                NotificationEventDetail::updateOrCreate(
                
                      ['noti_event_id'=> $request->noti_event_id, 'lang_id' => $key],
                    
                      ['noti_event_id'=>$request->noti_event_id, 'lang_id'=>$key, 'mail_desc'=>$value]

           
                 );
             }

            /*update activity log start*/
            $action_type = "updated"; 
            $module_name = "notification event";            
            $logdetails = "Admin has updated ".$result->mail_type." notification event";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

           return redirect()->action('Admin\Notification\MailTemplateController@editevent', [$request->noti_event_id])->with('succMsg', 'Records Updated Successfully!');

        }


    function masterTempateList() {
        $permission = $this->checkUrlPermission('manage_mail_template');
        if($permission === true) {
            //dd("ok");
            $permission_arr['add'] = $this->checkMenuPermission('master_mail_template');
            $permission_arr['edit'] = $this->checkMenuPermission('master_mail_template');
            $permission_arr['delete'] = $this->checkMenuPermission('master_mail_template');            

            $master_templates = MailTemplateMaster::getMasterTemplate();
            return view('admin.notification.masterMailTemplateList', ['master_templates'=>$master_templates, 'permission_arr'=>$permission_arr]);
        }
    }

    function masterTemplateCreate() {
        $permission = $this->checkUrlPermission('master_mail_template');
        if($permission === true) { 

            $languages = Language::getLangugeDetails();
            return view('admin.notification.masterMailTemplateAdd', ['languages'=>$languages]);
        }
    }

    function masterTemplateSubmit(Request $request) {
        //echo '<pre>';print_r($request->all());die;

        $input = $request->all();
        $validate = $this->validateMasterTemplete($input);        
        if ($validate->passes()) {

            $user_id = Auth::guard('admin_user')->user()->id;
            $templateObj = new MailTemplateMaster();           

            $templateObj->lang_id = $request->lang_id;
            $templateObj->name = $request->name;
            $templateObj->template = $request->template;
            $templateObj->created_by = $user_id;  
            $templateObj->save(); 

            /*update activity log start*/
            $action_type = "created"; 
            $module_name = "master template";            
            $logdetails = "Admin has created ".$request->name." ".$module_name;
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

            return redirect()->action('Admin\Notification\MailTemplateController@masterTempateList')->with('succMsg', 'Master Template Added Successfully!'); 
        }
        else {
            return redirect()->action('Admin\Notification\MailTemplateController@masterTemplateCreate')->withErrors($validate)->withInput();
        }                 
    }

    function masterTemplateEdit($id) {
        $permission = $this->checkUrlPermission('master_mail_template');
        if($permission === true) { 

            $languages = Language::getLangugeDetails();
            $master_template = MailTemplateMaster::getMasterTemplate($id);

            return view('admin.notification.masterMailTemplateEdit', ['languages'=>$languages, 'master_template'=>$master_template]);
        }
    }

    function masterTemplateUpdate(Request $request) {
        //echo '<pre>';print_r($request->all());die;

        if($request->master_template_id > 0){

            $input = $request->all();
            $validate = $this->validateMasterTemplete($input);        
            if ($validate->passes()) {            

                $user_id = Auth::guard('admin_user')->user()->id;
                $templateObj = MailTemplateMaster::find($request->master_template_id);

                $templateObj->lang_id = $request->lang_id;
                $templateObj->name = $request->name;
                $templateObj->template = $request->template;
                $templateObj->updated_at = date('Y-m-d H:i:s');
                $templateObj->updated_by = $user_id;  
                $templateObj->save(); 

                /*update activity log start*/
                $action_type = "updated"; 
                $module_name = "master template";            
                $logdetails = "Admin has updated ".$request->name." ".$module_name;
                $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

                $this->updateLogActivity($logdata);
                /*update activity log End*/

                return redirect()->action('Admin\Notification\MailTemplateController@masterTempateList')->with('succMsg', 'Master Template Updated Successfully!'); 
            }
            else {
                return redirect()->action('Admin\Notification\MailTemplateController@masterTemplateEdit', $request->master_template_id)->withErrors($validate)->withInput();
            }                 
        }    
    } 

    function deleteTemplete($id) {
        $permission = $this->checkUrlPermission('delete_mail_template');
        if($permission === true) { 

            $currency = MailTemplateMaster::find($id)->delete();

            /*update activity log start*/
            $action_type = "deleted"; 
            $module_name = "master template";            
            $logdetails = "Admin has deleted ".$module_name;
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

            return redirect()->action('Admin\Notification\MailTemplateController@masterTempateList')->with('succMsg', 'Templete Deleted Successfully!');
        }       
    }
	
	function deleteLineTransmission($id) {
		
		if(!empty($id)){

            $line = \App\LineTransmissionMethod::find($id)->delete();

            /*update activity log start*/
            $action_type = "deleted"; 
            $module_name = "line";            
            $logdetails = "Admin has deleted ".$module_name;
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/

            return redirect()->action('Admin\Notification\MailTemplateController@manageLineTransmission')->with('succMsg', 'Line Deleted Successfully!');
        }       
    }

    function validateMasterTemplete($input) {

        $rules['lang_id'] = 'Required';
        $rules['name'] = 'Required|Min:3';
        $rules['template'] = 'Required|Min:20';      

        $error_msg['lang_id.required'] = 'Please select language';
        $error_msg['name.required'] = 'Please enter template name';
        $error_msg['template.required'] = 'Please enter template containt';    

        $validate = Validator::make($input, $rules, $error_msg);
        //echo '<pre>';print_r($validate->errors());die;
        return $validate;
    } 

    function validateMailTemplete($input) {

        $rules['lang_id'] = 'Required';
        if($input['noti_type_id'] != '6'){  
			$rules['mail_subject'] = 'Required|Min:3';
		}
        if($input['noti_type_id'] == '1'){  
           $rules['master_template'] = 'Required';
        }

        $rules['mail_containt'] = 'Required|Min:20';      

        $error_msg['lang_id.required'] = 'Please select language';
        $error_msg['mail_subject.required'] = 'Please enter mail subject';
        $error_msg['master_template.required'] = 'Please select master template';
        $error_msg['mail_containt.required'] = 'Please enter mail containt';    

        $validate = Validator::make($input, $rules, $error_msg);
        //echo '<pre>';print_r($validate->errors());die;
        return $validate;
    }   

    public function manageEmailTransmission(Request $request){

        $permission = $this->checkUrlPermission('email_transmission_setting');
        if($permission === true) {

            $file_path = Config::get('constants.public_path').'/client_config/mail_server/mail_server_setting.json';
            $default_mail_server = \App\EmailTransmissionMethod::where('is_default','1')->first();
            $transMethodsData = $this->getJsonFileContent($file_path);
            $transMethods = is_null($transMethodsData)?[]:$transMethodsData;
            $default_sms_server = \App\EmailTransmissionMethod::where('type','sms')->first();
            $default_mail_server->password = base64_decode($default_mail_server->password);
            //dd($transMethods);
            return view('admin.notification.emailTransmission',['default_mail_server'=>$default_mail_server,'transMethods'=>json_decode(json_encode($transMethods)),'default_timeout'=>$this->default_timeout,'default_sms_server'=>$default_sms_server]);
        }
    } 


    public function manageSMSTransmission(Request $request){
        $permission = $this->checkUrlPermission('email_transmission_setting');
        if($permission === true) {
            //$file_path = Config::get('constants.public_path').'/client_config/mail_server/mail_server_setting.json';
            $default_mail_server = \App\SmsTransmissionMethod::where('is_default','1')->where('type', 'sms')->first();
            //$transMethodsData = $this->getJsonFileContent($file_path);
            //$transMethods = is_null($transMethodsData)?[]:$transMethodsData;
            $default_sms_server = \App\SmsTransmissionMethod::where('status','1')->where('type', 'sms')->get();
            //dd($transMethods);
            return view('admin.notification.smsTransmission',['default_mail_server'=>$default_mail_server,'default_timeout'=>$this->default_timeout, 'default_sms_server'=>$default_sms_server]);
        }
    } 

    public function manageOTPTransmission(Request $request){
        $permission = $this->checkUrlPermission('email_transmission_setting');
        if($permission === true) {
            //$file_path = Config::get('constants.public_path').'/client_config/mail_server/mail_server_setting.json';
            $default_mail_server = \App\SmsTransmissionMethod::where('is_default','1')->where('type', 'otp')->first();
            //dd($default_mail_server);
            //$transMethodsData = $this->getJsonFileContent($file_path);
            //$transMethods = is_null($transMethodsData)?[]:$transMethodsData;
            $default_sms_server = \App\SmsTransmissionMethod::where('status','1')->where('type', 'otp')->get();
            //dd($transMethods);
            return view('admin.notification.otpTransmission',['default_mail_server'=>$default_mail_server,'default_timeout'=>$this->default_timeout, 'default_sms_server'=>$default_sms_server]);
        }
    } 
	
	public function manageLineTransmission(Request $request){
        $permission = $this->checkUrlPermission('email_transmission_setting');
        if($permission === true) {
            $default_mail_server = \App\LineTransmissionMethod::where('is_default','1')->where('type', 'line')->first();
            $default_line_server = \App\LineTransmissionMethod::where('status','1')->where('type', 'line')->orderby('id','desc')->get();
            //dd($default_line_server);
            return view('admin.notification.lineTransmission',['default_mail_server'=>$default_mail_server,'default_timeout'=>$this->default_timeout, 'default_line_server'=>$default_line_server]);
        }
    } 

    public function getSelectdDriverData(Request $request){
       
        $value = $request->value;
        $parrent_value = $request->parent_val;
        $re_provider = $request->provider;
        $return_data = [];

        foreach ($request->setting_data['driver'] as $key => $set_val) {
            //$fix_val = ($request->attribute=='driver')?$value:$parrent_value;

            switch ($request->attribute) {
                case 'driver':
                    $fix_val = $value;
                break;
                case 'encription':
                    $fix_val = $parrent_value;
                break;
                default:
                   $fix_val = $parrent_value;
                break;
            }

            if($set_val['key']==$fix_val){
                foreach($set_val['provider'] as $p_key =>$provider){
                    //dd($provider);
                    if($request->attribute=='driver'){
                        $return_data[$provider['key']] = $provider['name'];
                    }else{
                        //dd($provider,$re_provider);
                        if($provider['key']==$value){
                            $return_data = $provider;
                        } 
                        
                        if($provider['key']==$re_provider){
                            foreach ($provider['encription'] as $enc_key => $encription) {

                                if($encription['name']==$value){
                                    $return_data['port'] = $encription['port'];
                                }
                            }
                        }  
                    }
                }
            }

            //dd($fix_val,$value,$request->attribute,$re_provider,$parrent_value,$set_val);
        }
        //dd($request->attribute,$return_data);
        //$emailTransMethods = \App\EmailTransmissionMethod::where('driver',$request->driver)->first();

        if(!empty($return_data)){
            $return  = ['success'=>'success','driverData'=>$return_data];
        }else{
            $return  = ['success'=>'unsuccess','driverData'=>''];
        }

        return $return;
    }

    public function getSmsData(Request $request){
        $re_provider = $request->provider;
        $return_data = [];
        $return_data = \App\SmsTransmissionMethod::where('id',$re_provider)->first();
        if(!empty($return_data)){
            $return  = ['status'=>'success','driverData'=>$return_data];
        }else{
            $return  = ['status'=>'unsuccess','driverData'=>''];
        }

        return $return;
    }

    public function updateEmailTransMethod(Request $request){
        
        $mainTransmissionMissionObj = \App\EmailTransmissionMethod::find(1);
        $is_default = 1;
        if(is_null($mainTransmissionMissionObj)){
            $mainTransmissionMissionObj = new \App\EmailTransmissionMethod;
            $mainTransmissionMissionObj->created_at = date("Y-m-d");
            $mainTransmissionMissionObj->status = '1';
        }

        $mainTransmissionMissionObj->driver = trim($request->driver);
        $mainTransmissionMissionObj->provider = trim($request->provider);
        $mainTransmissionMissionObj->host = trim($request->host);
        $mainTransmissionMissionObj->port = trim($request->port);
        $mainTransmissionMissionObj->email_from = trim($request->email_from);
        $mainTransmissionMissionObj->username = trim($request->username);
        $mainTransmissionMissionObj->password = base64_encode(trim($request->password));
        $mainTransmissionMissionObj->encription = trim($request->encription);


        if(isset($is_default)){
           $mainTransmissionMissionObj->is_default = '1'; 
        }
        
        $mainTransmissionMissionObj->save();

        if(isset($is_default)){
        
             \App\EmailTransmissionMethod::whereNotIn('id',[$mainTransmissionMissionObj->id])->update(['is_default' => '0']);
        }

        Artisan::call('cache:clear');
        
        Artisan::call('vendor:publish', array('--provider'=>'App\Providers\MailConfigServiceProvider','--force' => true));

        /*update activity log start*/
        $action_type = "Edit";
        $module_name = "Manage email transmission server";   //Changes module name like : blog etc         
        $logdetails = "Admin has updated SMTP email server credentials "; //Change update message as requirement 
        $old_data = ""; //Optional old data in json format key and value as per requirement 
        $new_data = ""; //Optional new data json format key and value as per requirement 

        //Prepaire array for send data
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );

        //Call method in module
        $this->updateLogActivity($logdata);
        /*update activity log end*/
        
        return redirect()->action('Admin\Notification\MailTemplateController@manageEmailTransmission')->with('succMsg', 'Mail server data is updated successfully !'); 
    }


    public function updateSMSTransMethod(Request $request){
        
        $mainTransmissionMissionObj = \App\SmsTransmissionMethod::where('id', $request->provider)->where('type', 'sms')->first();
        $is_default = 1;
        if(is_null($mainTransmissionMissionObj)){
            $mainTransmissionMissionObj = new \App\SmsTransmissionMethod;
            $mainTransmissionMissionObj->created_at = date("Y-m-d");
            $mainTransmissionMissionObj->status = '1';
        }

        //$mainTransmissionMissionObj->provider = trim($request->provider);
        $mainTransmissionMissionObj->api_url = trim($request->api_url);
        $mainTransmissionMissionObj->username = trim($request->username);
        $mainTransmissionMissionObj->password = trim($request->password);
        $mainTransmissionMissionObj->msisdn = trim($request->msisdn);
        //$mainTransmissionMissionObj->message = trim($request->message);
        $mainTransmissionMissionObj->sender = trim($request->sender);
        //$mainTransmissionMissionObj->send_to = trim($request->send_to);
        if(isset($is_default)){
           $mainTransmissionMissionObj->is_default = '1'; 
        }
        
        $mainTransmissionMissionObj->save();

        if(isset($is_default)){
            \App\SmsTransmissionMethod::whereNotIn('id',[$mainTransmissionMissionObj->id])->where('type', 'sms')->update(['is_default' => '0']);
        }

        //Artisan::call('cache:clear');
        
        //Artisan::call('vendor:publish', array('--provider'=>'App\Providers\MailConfigServiceProvider','--force' => true));

        /*update activity log start*/
        $action_type = "Edit";
        $module_name = "Manage SMS transmission server";   //Changes module name like : blog etc         
        $logdetails = "Admin has updated SMS server credentials "; //Change update message as requirement 
        $old_data = ""; //Optional old data in json format key and value as per requirement 
        $new_data = ""; //Optional new data json format key and value as per requirement 

        //Prepaire array for send data
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );

        //Call method in module
        $this->updateLogActivity($logdata);
        /*update activity log end*/
        
        return redirect()->action('Admin\Notification\MailTemplateController@manageSMSTransmission')->with('succMsg', 'SMS server details have updated successfully !'); 
    }

    public function updateOTPTransMethod(Request $request){
        
        $mainTransmissionMissionObj = \App\SmsTransmissionMethod::where('id', $request->provider)->where('type', 'otp')->first();
        $is_default = 1;
        if(is_null($mainTransmissionMissionObj)){
            $mainTransmissionMissionObj = new \App\SmsTransmissionMethod;
            $mainTransmissionMissionObj->created_at = date("Y-m-d");
            $mainTransmissionMissionObj->status = '1';
        }

        //$mainTransmissionMissionObj->provider = trim($request->provider);
        $mainTransmissionMissionObj->api_url = trim($request->api_url);
        $mainTransmissionMissionObj->username = trim($request->username);
        $mainTransmissionMissionObj->password = trim($request->password);
        $mainTransmissionMissionObj->msisdn = trim($request->msisdn);
        //$mainTransmissionMissionObj->message = trim($request->message);
        $mainTransmissionMissionObj->sender = trim($request->sender);
        //$mainTransmissionMissionObj->send_to = trim($request->send_to);
        if(isset($is_default)){
           $mainTransmissionMissionObj->is_default = '1'; 
        }
        
        $mainTransmissionMissionObj->save();

        if(isset($is_default)){
            \App\SmsTransmissionMethod::whereNotIn('id',[$mainTransmissionMissionObj->id])->where('type', 'otp')->update(['is_default' => '0']);
        }

        //Artisan::call('cache:clear');
        
        //Artisan::call('vendor:publish', array('--provider'=>'App\Providers\MailConfigServiceProvider','--force' => true));

        /*update activity log start*/
        $action_type = "Edit";
        $module_name = "Manage OTP transmission server";   //Changes module name like : blog etc         
        $logdetails = "Admin has updated OTP server credentials "; //Change update message as requirement 
        $old_data = ""; //Optional old data in json format key and value as per requirement 
        $new_data = ""; //Optional new data json format key and value as per requirement 

        //Prepaire array for send data
        $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );

        //Call method in module
        $this->updateLogActivity($logdata);
        /*update activity log end*/
        
        return redirect()->action('Admin\Notification\MailTemplateController@manageOTPTransmission')->with('succMsg', 'OTP server details have updated successfully!'); 
    }
	
	function addLineChannel(Request $request) {

		return view('admin.notification.addLineChannel');
        
    }
	
	public function storeLineChannel(Request $request){ 
        
		if(!empty($request->token) && !empty($request->name) && !empty($request->message) && !empty($request->remark)){
			//Add line transmission in database
			$mainTransmissionMissionObj = new \App\LineTransmissionMethod;
			$mainTransmissionMissionObj->token = trim($request->token);
			$mainTransmissionMissionObj->name = trim($request->name);
			$mainTransmissionMissionObj->message = trim($request->message);
			$mainTransmissionMissionObj->remark = trim($request->remark);
			$mainTransmissionMissionObj->created_at = date("Y-m-d");
			$mainTransmissionMissionObj->status = '1';
			$mainTransmissionMissionObj->type = 'line';
			$mainTransmissionMissionObj->save();
			
			//Add Line
		    /*$message = trim($request->message);
		    $token = trim($request->token);
		    //header('Content-Type: application/json'); // Specify the type of data
		    $ch = curl_init('https://notify-api.line.me/api/notify?message='.$message); // Initialise cURL
		    //$post = json_encode($message); // Encode the data array into a JSON string
		    $authorization = "Authorization: Bearer ".$token; // Prepare the authorisation token
		    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // Inject the token into the header
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $message); // Set the posted fields
		    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
		    $result = curl_exec($ch); // Execute the cURL statement
		    curl_close($ch); // Close the cURL connection
		    $response =  json_decode($result); // Return the received data*/

			/*update activity log start*/
			$action_type = "Add";
			$module_name = "Manage Line Transmission Chanel";   //Changes module name like : blog etc         
			$logdetails = "Admin has added Line Channel Credentials "; //Change update message as requirement 
			$old_data = ""; //Optional old data in json format key and value as per requirement 
			$new_data = ""; //Optional new data json format key and value as per requirement 

			//Prepaire array for send data
			$logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails,'old_data' =>$old_data,'new_data' =>$new_data );

			//Call method in module
			$this->updateLogActivity($logdata);
			/*update activity log end*/
			
			return redirect()->action('Admin\Notification\MailTemplateController@manageLineTransmission')->with('succMsg', 'Line channel added successfully !');
		}else{
			return redirect()->action('Admin\Notification\MailTemplateController@manageLineTransmission')->with('errorMsg', 'Empty not allow !');	
		}		
    }

    public function testSmsServerConnection(Request $request){
        
        $emailServerData = [];
        foreach($request->form_data as $key=>$value){
            
            if($key!='_token'){
                $emailServerData[$key] = $value;
            }
        }

        $lang_id = session('default_lang');

       
        $emailServerData['DATE'] = date('M d, Y H:i:s');
        $event_slug = 'email_server_test_connection_template'; // Email Server Test Connection Template
        $emailData = ['lang_id'=>$lang_id, 'relevantdata'=>$emailServerData, 'is_cron' => 2, 'sms_to'=>$emailServerData['msisdn']];
        
        $error = false;
        try {
            $resp = EmailHelpers::SendSMS($event_slug, $emailData, 'testing');
            $msg = 'Test Connection Success';
            if($resp['status'] == 'success'){
                $error = true;
                $msg = $resp['message'];
            }else{
                $error = true;
                $msg = $resp['message']; 
            }

        } catch (Exception $e) {
            $msg = $e->getMessage();
            $error = true;
        }

        $date_time = date('M d, Y H:i:s');
        return ['error'=>$error,'date'=>$date_time,'message'=>$msg];
    }

    public function testOTPServerConnection(Request $request){
        
        $emailServerData = [];
        foreach($request->form_data as $key=>$value){
            
            if($key!='_token'){
                $emailServerData[$key] = $value;
            }
        }

        //$lang_id = session('default_lang');
        $emailServerData['DATE'] = date('M d, Y H:i:s');
        /*$event_slug = 'email_server_test_connection_template'; // Email Server Test Connection Template
        $emailData = ['lang_id'=>$lang_id, 'relevantdata'=>$emailServerData, 'is_cron' => 2, 'sms_to'=>$emailServerData['msisdn']];*/

        $error = false;
        try {
            
            $resp = sendOtp($emailServerData['msisdn']);
            //dd($resp);
            $msg = 'Test Connection Success';
            if($resp['status'] == 'success'){
                $error = false;
                $msg = $resp['token'];
            }else{
                $error = true;
                $msg = $resp['msg']; 
            }

        } catch (Exception $e) {
            $msg = $e->getMessage();
            $error = true;
        }

        $date_time = date('M d, Y H:i:s');
        return ['error'=>$error,'date'=>$date_time,'message'=>$msg];
    }


    public function testEmailServerConnection(Request $request){
        
        $emailServerData = [];
        foreach($request->form_data as $key=>$value){
            
            if($key!='_token'){
                $emailServerData[$key] = $value;
            }
        }

        $lang_id = session('default_lang');
        $admin_url = action('Admin\AdminHomeController@index');

        $emailReplaceData = [];
        $emailReplaceData['EMAIL_SERVER'] = strtoupper($emailServerData['driver']);
        $emailReplaceData['SERVER_STATUS'] = 'SUCCESS';
        $emailReplaceData['ADMIN_LOGIN_URL'] = '<a href="'.$admin_url.'" class="btn">Admin</a>';
        $emailReplaceData['LOGIN'] = '<a href="'.$admin_url.'" class="btn">Login</a>';
        
        $emailReplaceData['DATE'] = date('M d, Y H:i:s');
        $emailReplaceData['SITE_FULL_NAME'] = getConfigurationValue('SITE_FULL_NAME');
        $emailReplaceData['SITE_NAME'] = getConfigurationValue('SITE_SHORT_NAME');
        $emailReplaceData['SITE_EMAIL'] = getConfigurationValue('SITE_EMAIL');
        $emailReplaceData['SITE_URL'] = url('/');
        $emailReplaceData['SITE_LOGO'] ='<img src="'.getSiteLogo('SITE_LOGO_HEADER').'?header-'.rand(10, 1000).'" id="site_logo_header">';

        $event_slug = 'email_server_test_connection_template'; // Email Server Test Connection Template
        $emailData = ['lang_id'=>$lang_id, 'relevantdata'=>$emailReplaceData,'', 'is_cron' => 2 , 'user_type' => 'admin','email_from'=>$emailServerData['email_from']];

        $error = false;

        try {
               
               switch($emailServerData['driver']){
                    case 'smtp':
                        $transport = Mail::getSwiftMailer()->getTransport();
            
                        $transport->setHost(trim($emailServerData['host']));
                        $transport->setPort(trim($emailServerData['port']));
                        $transport->setUsername(trim($emailServerData['username']));
                        $transport->setPassword(trim($emailServerData['password']));
                        $transport->setEncryption(trim($emailServerData['encription']));
                    break;
                    case 'mailgun':
                        # code...
                    break;
                    case 'sendmail':
                        # code...
                    break;
                    case 'mandrill':
                        # code...
                    break;
                    case 'sparkpost':
                        # code...
                    break;
                    case 'mail':
                        # code...
                    break;
                    case 'ses':
                        # code...
                    break;
               }

            $resp = $this->sendSMTPTestEmail($event_slug, $emailData);
            //dd($resp);
            $msg = 'Test Connection Success';
            if(!$resp['status']){
                $error = true;
                $msg = $resp['message'];
            }

        } catch (Exception $e) {
            // echo $e; die;
            $msg = $e->getMessage();
            $error = true;
        }

        $date_time = date('M d, Y H:i:s');
        
        return ['error'=>$error,'date'=>$date_time,'message'=>$msg];
    }
	
	public function testLineServerConnection(Request $request){
		
		if(!empty($request->form_data)){
		   $formdata = $request->form_data;
		   $message = trim($formdata['message']);
		   $token = trim($formdata['line_token']);
		   //dd($request->all(),$formdata,$message,$token);
		   //header('Content-Type: application/json'); // Specify the type of data
		   $ch = curl_init('https://notify-api.line.me/api/notify?message='.$message); // Initialise cURL
		   //$post = json_encode($message); // Encode the data array into a JSON string
		   $authorization = "Authorization: Bearer ".$token; // Prepare the authorisation token
		   curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // Inject the token into the header
		   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		   curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
		   curl_setopt($ch, CURLOPT_POSTFIELDS, $message); // Set the posted fields
		   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
		   $result = curl_exec($ch); // Execute the cURL statement
		   curl_close($ch); // Close the cURL connection
		   $response =  json_decode($result); // Return the received data
		   
		   $date_time = date('M d, Y H:i:s');
		   if(!empty($response)){
				return ['status'=>$response->status,'date'=>$date_time,'message'=>$response->message];
		   }else{
				return ['status'=>false,'date'=>$date_time,'message'=>"api not reponse"];
		   }
		}
    }
	
	public function testLineChannel($id) { 
		
        if(!empty($id)) {
            $channel = \App\LineTransmissionMethod::where(['id'=>$id])->first();	
		    $message = trim($channel->message);
		    $token = trim($channel->token);

            /******* old code ***********/
    			/*//header('Content-Type: application/json'); // Specify the type of data
    			$ch = curl_init('https://notify-api.line.me/api/notify?message='.$message); // Initialise cURL
    			//$post = json_encode($message); // Encode the data array into a JSON string
    			$authorization = "Authorization: Bearer ".$token; // Prepare the authorisation token
    			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // Inject the token into the header
    			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    			curl_setopt($ch, CURLOPT_POST, 1); // Specify the request method as POST
    			curl_setopt($ch, CURLOPT_POSTFIELDS, $message); // Set the posted fields
    			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // This will follow any redirects
    			$result = curl_exec($ch); // Execute the cURL statement
    			curl_close($ch); // Close the cURL connection
			    //add for response check

                $date_time = date('M d, Y H:i:s');  
                if($result){
                     $response =  json_decode($result, true); // Return the received data
                }else{
                    $response = ['status'=>'404', 'message'=>'data not found'];
                }
               
                return view('admin.notification.testLineChannel', ['status'=>$response['status'],'date'=>$date_time,'message'=>$response['message']]);*/
            /*********** end code **************/

            /********* new code added by RAJEEV 21/01/2021 ***********/
                $query = http_build_query(['message' => $message]);
                $header = [
                    'Content-Type: application/x-www-form-urlencoded',
                    'Authorization: Bearer ' . $token,
                    'Content-Length: ' . strlen($query)
                ];

                $ch = curl_init('https://notify-api.line.me/api/notify');
                $options = [
                    CURLOPT_RETURNTRANSFER  => true,
                    CURLOPT_POST            => true,
                    CURLOPT_HTTPHEADER      => $header,
                    CURLOPT_POSTFIELDS      => $query
                ];

                curl_setopt_array($ch, $options);
                $server_output = curl_exec($ch);
                curl_close($ch);
                //dd($server_output);
                $date_time = date('M d, Y H:i:s');  
                if($server_output){
                     $response =  json_decode($server_output, true); // Return the received data
                }else{
                    $response = ['status'=>'404', 'message'=>'data not found'];
                }
               
                return view('admin.notification.testLineChannel', ['status'=>$response['status'],'date'=>$date_time,'message'=>$response['message']]);
            /*********** end new code ********************************/
        }        

    } 

	function editLineChannel($id) {
        
        if(!empty($id)) { 
            $line_channel = \App\LineTransmissionMethod::where(['id'=>$id])->first();

            return view('admin.notification.editLineChannel', ['line_channel'=>$line_channel]);
        }
    }

    function updateLineChannel(Request $request) {

        if(!empty($request->line_id) && !empty($request->token) && !empty($request->name) && !empty($request->message) && !empty($request->remark) ){
			
			$lineObj = \App\LineTransmissionMethod::find($request->line_id);
			
			$lineObj->token = trim($request->token);
			$lineObj->name = trim($request->name);
			$lineObj->message = trim($request->message);
			$lineObj->remark = trim($request->remark);
			$lineObj->updated_at = date('Y-m-d H:i:s'); 
			$lineObj->save(); 

			/*update activity log start*/
			$action_type = "updated"; 
			$module_name = "Line Chanel";            
			$logdetails = "Admin has updated ".$request->name." ".$module_name;
			$logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);
			
			$this->updateLogActivity($logdata);
			/*update activity log End*/

			return redirect()->action('Admin\Notification\MailTemplateController@manageLineTransmission')->with('succMsg', 'Line Channel Updated Successfully!'); 
                            
        }else{
			return redirect()->action('Admin\Notification\MailTemplateController@manageLineTransmission')->with('errorMsg', 'Empty not allow!'); 
            
		}    
    } 
	
    protected function sendSMTPTestEmail($event_slug, $emailData){
        try{
            $status = true;
            extract($emailData);

            $user_email = isset($user_email)?$user_email:'';
            $is_cron = isset($is_cron)?$is_cron:2;
            $user_type = isset($user_type)?$user_type:'user';        
            $file_with_path = isset($attachment)?$attachment:'';

            $results = DB::table(with(new \App\NotificationEvent)->getTable().' as m')
                        ->leftjoin(with(new \App\NotificationEventTemplate)->getTable().' as md', [['m.id', '=', 'md.noti_event_id'], ['md.noti_type_id', '=' , DB::raw(1)]])
                        ->leftjoin(with(new \App\NotificationEventTemplateDetail)->getTable().' as ms', [['m.id', '=', 'ms.noti_event_id'],['ms.lang_id', '=', DB::raw($lang_id)]])

                        ->leftjoin(with(new \App\MailTemplateMaster)->getTable().' as lm', [['lm.id', '=', 'ms.master_template_id']])
                        ->where('m.slug', $event_slug)->first();

            $subject = stripslashes($results->mail_subject);
            $mail_content = stripslashes($results->mail_containt);

            /*Merge both layout and content*///template
            if(!empty($results->template)){
              $layout = stripslashes($results->template); 
              $mail_content = str_replace( '[CONTENT]' , $mail_content, $layout);
            }
            
            $emailReplaceData = [ 'SITE_NAME' => getConfigurationValue('SITE_FULL_NAME'),
                                  'SITE_URL'  => url('/') ];

            /*Merge data  comman and relevant data*/              
            $emailReplaceData = array_merge($emailReplaceData, $relevantdata);

            /*replace value in the subject and email content*/
            foreach($emailReplaceData as $key => $value){
                  $replaceKey = '['.$key.']';
                  $subject = str_replace( $replaceKey ,$value,$subject);
                  $mail_content = str_replace( $replaceKey ,$value, $mail_content);
            }

            /*find out to buyer email*/
            $to_email = [];
            if(!empty($results->to_buyer) && !empty($user_email)){
                $to_email[] =  $user_email;
            }

            /*find out to Admin email*/
            $admin_email = $admin_role_id = [];
            if(!empty($results->to_admin)){

                $admin_role_id = explode('-', $results->to_admin);
                $admin_email = \App\AdminUser::select('email')->whereIn('role_id', $admin_role_id)->pluck('email')->toArray();
                $to_email = array_merge($to_email, $admin_email);
            }

            /*cc*/
            $cc = [];
            if(!empty($results->cc)){
                /**/  
                $cc_id =  array_filter(explode('-', $results->cc));
                $cc = \App\AdminUser::select('email')->whereIn('role_id', $cc_id)->pluck('email')->toArray();
            }

            /*bcc*/
            $bcc = [];
            if(!empty($results->bcc)){
                $bcc_id =  array_filter(explode('-', $results->bcc));
                $bcc = \App\AdminUser::select('email')->whereIn('role_id', $bcc_id)->pluck('email')->toArray(); 
            }

            $data = []; 
            $data['mail_content'] = $mail_content;
            $data['to_email'] = $to_email;
            $data['subject'] = $subject;

            /*send email to other admin user */
            $data['cc'] = $cc;
            $data['bcc'] = $bcc;
            // $sender = isset($results->sender)?$results->sender:'';
            // $data['sender'] = $sender;
            // //$data['reply'] = $reply;
            $sender = $email_from;
            $data['sender'] = $sender;

            $data['attachment'] = $file_with_path;
           // dd($data);
            $is_send = 2;
            if($is_cron == 2){
                Mail::send(['html' =>'emails.mail'], $data, function($message) use ($data) {
                    $message->to($data['to_email']);
                    if(!empty($data['cc'])){
                        $message->cc($data['cc'], $name = null);
                    }  
                    if(!empty($data['bcc'])){
                        $message->bcc($data['bcc'], $name = null);
                    }
                    if(!empty($data['sender'])){
                        $message->from($data['sender'], $name = config('app.name'));
                        //$message->replyTo($data['reply'], $name = 'Reply');
                    } 
                    if(!empty($data['attachment'])){
                        $message->attach($data['attachment']);
                    }
                    $message->subject($data['subject']);
                });
                $is_send = 1;   
            }
            $msg = '';
        }
        catch(Exception $e){
            $is_send = 2;
            $is_cron = 1;
            $msg = $e->getMessage();
            $status = false;
        }

        $saveNotiQueue = new \App\NotificationQueue;
        $saveNotiQueue->subject = addslashes($subject);
        $saveNotiQueue->mail_content = addslashes(str_replace('↵', '', $mail_content));
        $saveNotiQueue->to_email = implode(',',$to_email);
        $saveNotiQueue->cc = implode(',', $cc);
        $saveNotiQueue->bcc = implode(',', $bcc);
        $saveNotiQueue->reply = $sender;
        $saveNotiQueue->is_cron = $is_cron;
        $saveNotiQueue->is_send = $is_send;
        $saveNotiQueue->notification_type = 'email';
        $saveNotiQueue->to_user = $user_type;
        $saveNotiQueue->attachment = $file_with_path;
        $saveNotiQueue->save();

        return ['status'=>$status,'message'=>$msg];
    }
    
    public function verifyOtp(Request $request){
        $otp = $request->otp;
        $token = $request->token;
        if(!empty($otp) && !empty($token)){
             $return = matchOtp($token, $otp);
        }else{
            $return = ['status'=>'fail','msg'=>Lang::get('common.not_match')];
        }
        return $return;
    }

    public function senderlist() {
        $permission = $this->checkUrlPermission('manage_mail_sender_list');
        if($permission === true) {
           $permission_arr['edit'] = $this->checkMenuPermission('edit_mail_sender');
            $permission_arr['delete'] = $this->checkMenuPermission('delete_mail_sender');

            $permission_arr['create'] = $this->checkMenuPermission('create_mail_sender');
            $results = \App\NotificationSenderDetail::get();

            return view('admin.notification.mailSenderList', ['results'=>$results, 'permission_arr'=>$permission_arr]);
        }
    }
    
    public function senderCreate(){
        $permission = $this->checkUrlPermission('create_mail_sender');
        if($permission === true) {
            return view('admin.notification.createMailSender');
        } 
    }

    public function senderStore(Request $request){
       
        //dd($request->all());
        $permission = $this->checkUrlPermission('create_mail_sender');
        if($permission === true) {
        //dd($id);
            $this->validate($request, [
               'sender_name' => 'required|unique:'.$this->tableNotificationSenderDetail, 
               'sender_email'=> 'required',
            ], $this->messagesSender());
            
            $result = new \App\NotificationSenderDetail;
            $result->sender_name = $request->sender_name;
            $result->sender_email = $request->sender_email;
            $result->is_default = isset($request->is_default)?'1':'';
            $result->status = $request->status;
            $result->save();
            $id = $result->id; 
            if(isset($request->is_default) && $request->is_default == 'on'){
               \App\NotificationSenderDetail::where('id','!=', $id)->update(['is_default'=>'0']); 
            }

            $action_type = "created"; 
            $module_name = "Email Sender";            
            $logdetails = "Admin has created ".$result->sender_name." Email Sender";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/
            return redirect()->action('Admin\Notification\MailTemplateController@senderlist')->with('succMsg', 'Records Added Successfully!');
        }

    }


    public function editSender($id) {
        $permission = $this->checkUrlPermission('edit_mail_sender');
        if($permission === true) {
            $result = \App\NotificationSenderDetail::where('id', $id)->first();
            return view('admin.notification.editMailSender', ['result'=>$result]);
        }                
    }

    public function updateSender(Request $request){
       
        //dd($request->all());
        $permission = $this->checkUrlPermission('edit_mail_sender');
        if($permission === true) {
            $id = $request->sender_id;
            //dd($id);
            $this->validate($request, [
               'sender_name'=>['required',
               Rule::unique($this->tableNotificationSenderDetail)->ignore($id, 'id')],
               'sender_email'=> 'required',
            ], $this->messagesSender());

            

            $result = \App\NotificationSenderDetail::where('id', $id)->first();
            
            $result->sender_name = $request->sender_name;
            $result->sender_email = $request->sender_email;
            $result->is_default = isset($request->is_default)?'1':'';
            $result->status = $request->status;
            $result->save();

            if(isset($request->is_default) && $request->is_default == 'on'){
               \App\NotificationSenderDetail::where('id','!=', $id)->update(['is_default'=>'0']); 
            }

            $action_type = "updated"; 
            $module_name = "Email Sender";            
            $logdetails = "Admin has updated ".$result->sender_name." Email Sender";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/
            return redirect()->action('Admin\Notification\MailTemplateController@senderlist')->with('succMsg', 'Record has been Updated Successfully!');
        }

    }

    public function messagesSender() {
        return [
            'sender_name.required' => 'Please enter sender name.',
            'sender_name.unique' => 'Sender name in already used.',
            'sender_email.required' => 'Please enter email.',
        ];
    } 

    public function deleteSender($id){
        $permission = $this->checkUrlPermission('delete_mail_sender');
        if($permission === true) {
            $result = \App\NotificationSenderDetail::where('id', $id)->first();
            if(!$result){
                abort(404);
            }
            $result->delete();
            $action_type = "deleted"; 
            $module_name = "Email Sender";            
            $logdetails = "Admin has delete ".$result->sender_name."  Email Sender";
            $logdata = array('action_type' =>$action_type,'module_name' =>$module_name,'logdetails' =>$logdetails);

            $this->updateLogActivity($logdata);
            /*update activity log End*/
            return redirect()->action('Admin\Notification\MailTemplateController@senderlist')->with('succMsg', 'Record has been deleted Successfully!');
        }
    }

}
