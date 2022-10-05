<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use Config;
use Lang;
use Auth;
use Mail;
use App\NotificationEvent;
use App\NotificationEventTemplateDetail;
use App\NotificationSenderDetail;
use App\MailTemplateMaster;
use App\AdminUser;
use App\User;
use App\ShippingAddress;
use App\NotificationEventTemplate;
use App\NotificationEventLog;
use App\Product;
use App\Helpers\GeneralFunctions;
use App\Helpers\CustomHelpers;
use Exception;
use App\NotificationQueue;
use session;

class EmailHelpers extends Mailable {

      use Queueable, SerializesModels;


      public static function sendMailToUserByCron($data){
         Mail::send(['html' =>'emails.mail'], $data, function($message) use ($data) {
                 $message->to($data['to_email']);
                 // if(!empty($data['cc'])){
                 //   $message->cc($data['cc'], $name = null);
                 // }

                 // if(!empty($data['bcc'])){
                 //   $message->bcc($data['bcc'], $name = null);
                 // }

                 if(!empty($data['reply'])){
                   $message->replyTo($data['reply'], $name = $data['reply_name']);
                 } 
                 $message->subject($data['subject']);
             });

      }

    public static function MailSend($event_slug, $emailData) {
      try{
          extract($emailData);
          //dd($emailData,$ORDER_NO);
          $user_email = isset($user_email)?$user_email:'';
          $is_cron = isset($is_cron)?$is_cron:2;
          $user_type = isset($user_type)?$user_type:'user';        
          $file_with_path = isset($attachment)?$attachment:'';

          /*fetch email template and revent data*/
          $notifications = DB::table(with(new NotificationEvent)->getTable().' as m')
                      ->join(with(new NotificationEventTemplate)->getTable().' as md', 'm.id', '=', 'md.noti_event_id')
                      ->join(with(new NotificationEventTemplateDetail)->getTable().' as ms', 'm.id', '=', 'ms.noti_event_id')
                      ->leftjoin(with(new MailTemplateMaster)->getTable().' as lm', 'lm.id', '=', 'ms.master_template_id')
                      ->leftjoin(with(new NotificationSenderDetail)->getTable().' as nsd', 'nsd.id', '=', 'md.sender')
                      ->where('m.slug', $event_slug)
                      ->where('md.noti_type_id', '1')
                      ->where('ms.noti_type_id', '1')
                      ->where('ms.lang_id',$lang_id);

          $results = $notifications->first();
          //dd($results->noti_type);
          //dd(unserialize($results->noti_type));
          if(!$results){
            return false;
          }
          $subject = stripslashes($results->mail_subject);
          $mail_content = stripslashes($results->mail_containt);

          /*Merge both layout and content*///template
          if(!empty($results->template)){
            $layout = stripslashes($results->template); 
            $mail_content = str_replace( '[CONTENT]' , $mail_content, $layout);
          }
          $site_logo ='<img src="'.getSiteLogo('SITE_LOGO_HEADER').'?header-'.rand(10, 1000).'" id="site_logo_header">';
          $emailReplaceData = [ 'SITE_NAME' => getConfigurationValue('SITE_SHORT_NAME'),
                                'SITE_FULL_NAME' => getConfigurationValue('SITE_FULL_NAME'),
                                'SITE_EMAIL' => getConfigurationValue('SITE_EMAIL'),
                                'SITE_URL'  => url('/'),
                                'SITE_CONTACT' => getConfigurationValue('SITE_CONTACT'),
                                'SITE_LOGO' => $site_logo,  
                ];

          /*Merge data  comman and relevant data*/              
          $emailReplaceData = array_merge($emailReplaceData, $relevantdata);

          /*replace value in the subject and email content*/
          foreach($emailReplaceData as $key => $value){
              $replaceKey = '['.$key.']';
              $subject = str_replace( $replaceKey, $value, $subject);
              $mail_content = str_replace( $replaceKey ,$value, $mail_content);
          }
          //dd($mail_content, $emailData);

          /*find out to buyer email*/
          $to_email = [];
            if(!empty($results->to_buyer) && !empty($user_email)){
                $to_email[] =  $user_email;
            }

          /*find out to seller email*/
            if ($user_type=="seller") {
                $seller_email ='';
                if(!empty($results->to_seller)){
                    if (isset($relevantdata) && $relevantdata['ORDER_NO']) {
                        $data =\App\Order::select('id')->where('formatted_id',$relevantdata['ORDER_NO'])->first();
                        if (isset($data) && $data) {
                            $datas =\App\OrderShop::select('shop_json')->where('order_id',$data->id)->get();
                            //dd($datas);
                            if (isset($datas) && $datas) {
                                foreach ($datas as $key => $values) {
                                    $shop_data = json_decode($values->shop_json);
                                    if (isset($shop_data) &&  $shop_data) {
                                        $seller_email = $shop_data->seller_email;
                                        if (isset($seller_email) && $seller_email) {
                                            $to_email[] =  $seller_email;
                                        }
                                        
                                    }
                                }
                                
                            }            
                        }
                    }         
                }
            }
            

          /*find out to Admin email*/
          $admin_email = $admin_role_id = [];
          if(!empty($results->to_admin)){
              //$admin_id =  array_filter(explode('-', $results->to_admin));
              $admin_role_id = explode('-', $results->to_admin);
              $admin_email = AdminUser::select('email')->whereIn('role_id', $admin_role_id)->pluck('email')->toArray();
              $to_email = array_merge($to_email, $admin_email);
          }

          /*cc*/
          $cc = [];
          if(!empty($results->cc)){
              /**/  
              $cc_id =  array_filter(explode('-', $results->cc));
              $cc = AdminUser::select('email')->whereIn('role_id', $cc_id)->pluck('email')->toArray();
          }

          /*bcc*/
          $bcc = [];
          if(!empty($results->bcc)){
              $bcc_id =  array_filter(explode('-', $results->bcc));
              $bcc = AdminUser::select('email')->whereIn('role_id', $bcc_id)->pluck('email')->toArray(); 
          }
          //echo $mail_content;
          //exit;

          $data = []; 
          $data['mail_content'] = $mail_content;
          $data['to_email'] = $to_email;
          $data['subject'] = $subject;

          /*send email to other admin user */
          $data['cc'] = $cc;
          $data['bcc'] = $bcc;
          $sender_email = isset($results->sender_email)?$results->sender_email:'';
      $sender_name = isset($results->sender_name)?$results->sender_name:'';
      //getConfigurationValue('SITE_FULL_NAME');

      if(empty($sender_name)){
        $defaultsender = \App\NotificationSenderDetail::where('is_default', '1')->first();
        $sender_email = isset($defaultsender->sender_email)?$defaultsender->sender_email:'';
        $sender_name = isset($defaultsender->sender_name)?$defaultsender->sender_name:getConfigurationValue('SITE_FULL_NAME');
      }
          
          $data['sender_email'] = $sender_email;
      $data['sender_name'] = $sender_name;

          //$data['reply'] = $reply;
          $data['attachment'] = $file_with_path;
      if(Config::get('constants.localmode') == true){
              //echo '<pre>';print_r($data);die;
          }
          $error_msg = '';
          if($results->noti_type){
              $is_send = 2;
              if($is_cron == 2){

                   $mail_response = Mail::send(['html' =>'emails.mail'], $data, function($message) use ($data) {
                          $message->to($data['to_email']);
                          if(!empty($data['cc'])){
                              $message->cc($data['cc'], $name = null);
                          }  
                          if(!empty($data['bcc'])){
                              $message->bcc($data['bcc'], $name = null);
                          }
                          if(!empty($data['sender_email'])){
                              //getConfigurationValue('SITE_FULL_NAME')
                              $message->from($data['sender_email'], $name = $data['sender_name']);
                              //$message->replyTo($data['reply'], $name = 'Reply');
                          } 
                          if(!empty($data['attachment'])){
                              $message->attach($data['attachment']);
                          }
                          $message->subject($data['subject']);
                      });

      //             if(count(Mail::failures()) > 0){
          //     $error_msg = 'Error: mail was not accepted for delivery.';
          //     dd($error_msg);
          // }else{
          //  dd('here ');
          // }
                  
                  // if(!$mail_response){
                  //  $error_msg = 'Error: mail was not accepted for delivery.';
                  // }else{
                  //  $is_send = 1;
                  // }    
                  $is_send = 1; 
              }
          }

          $saveNotiQueue = new NotificationQueue;
          $saveNotiQueue->subject = addslashes($subject);
          $saveNotiQueue->mail_content = addslashes(str_replace('↵', '', $mail_content));
          $saveNotiQueue->to_email = implode(',',$to_email);
          $saveNotiQueue->cc = implode(',', $cc);
          $saveNotiQueue->bcc = implode(',', $bcc);
          $saveNotiQueue->reply = $sender_email;
          $saveNotiQueue->reply_name = $sender_name;
          $saveNotiQueue->is_cron = $is_cron;
          $saveNotiQueue->is_send = $is_send;
          $saveNotiQueue->notification_type = 'email';
          $saveNotiQueue->to_user = $user_type;
          $saveNotiQueue->attachment = $file_with_path;
          $saveNotiQueue->error_msg = $error_msg;
          $saveNotiQueue->save();

          $return  = ['status'=>'success','message'=>$error_msg];

      }
      catch(Exception $e){
        $return  = ['status'=>'error','message'=>$e->getMessage()];
      }

      return $return;
        
    }


    public static function SendSMS($event_slug, $relevantdata, $smstype = '') {

        //dd($event_id, $lang_id, $relevantdata, $user);
       
        /*fetch email template and revent data*/
        //slug in email template table
        //DB::raw(1) is used for fetch email data
        extract($relevantdata);
        $user_type = isset($user_type)?$user_type:'user';
        $results = DB::table(with(new NotificationEvent)->getTable().' as m')
                      ->join(with(new NotificationEventTemplate)->getTable().' as md', 'm.id', '=', 'md.noti_event_id')
                      ->join(with(new NotificationEventTemplateDetail)->getTable().' as ms', 'm.id', '=', 'ms.noti_event_id')
                      ->leftjoin(with(new MailTemplateMaster)->getTable().' as lm', 'lm.id', '=', 'ms.master_template_id')
                      ->where('m.slug', $event_slug)
                      ->where('md.noti_type_id', '2')
                      ->where('ms.noti_type_id', '2')
                      ->where('ms.lang_id', $lang_id)
                      ->first();

        //dd($results,$user_type);
        $subject='';
        $mail_content ='';
        if ($results) {
            $subject = stripslashes($results->mail_subject);
            $mail_content = stripslashes($results->mail_containt);
        }
        
        /*Merge both layout and content*///template
        /*if(!empty($results->template)){
          $layout = stripslashes($results->template); 
          $mail_content = str_replace( '[CONTENT]' , $mail_content, $layout);
        }*/
        
        $emailReplaceData = [ 'SITE_NAME' => getConfigurationValue('SITE_SHORT_NAME'),
                              'SITE_FULL_NAME' => getConfigurationValue('SITE_FULL_NAME'),
                              'SITE_EMAIL' => getConfigurationValue('SITE_EMAIL'),
                              'SITE_CONTACT' => getConfigurationValue('SITE_CONTACT'),  
                              'SITE_URL'  => url('/') ];

         /*Merge data  comman and relevant data*/              
        $emailReplaceData = array_merge($emailReplaceData, $relevantdata);
        //dd($emailReplaceData);             
        /*replace value in the subject and email content*/
        foreach($emailReplaceData as $key => $value){
              $replaceKey = '['.$key.']';
              $subject = str_replace( $replaceKey ,$value,$subject);
              $mail_content = str_replace( $replaceKey ,$value, $mail_content);
        }
        //dd($mail_content);
        //$mail_content = addslashes($mail_content);
            $sms = '';
            /*find out to buyer SMS*/
            $user_id = Auth::id();
            if(isset($sms_to) && !empty($sms_to)){
               $sms = $sms_to;
            }

            if($results->to_buyer == '1'){
              $ph_number = User::where('id', $user_id)->value('ph_number');
              if(empty($sms)){
                if(!empty($ph_number)){
                  $sms = $ph_number;
                }
              }else{
                if(!empty($ph_number)){
                  $sms = $sms.','.$ph_number;
                }
              }
            }

            if($results->buyer_phone_login == '1'){
              $login_phone = User::where('id', $user_id)->value('ph_number');
              if(empty($sms)){
                if(!empty($login_phone)){
                  $sms = $login_phone;
                }
              }else{
                if(!empty($login_phone)){
                  $sms = $sms.','.$login_phone;
                }
              }
            }

            if($results->buyer_shipping_phone == '1'){
                $formatted_order_id = isset($emailReplaceData['ORDER_NO'])?$emailReplaceData['ORDER_NO']:'';
                if(!empty($formatted_order_id)){
                  $orderData = \App\Order::select('order_json')->where('formatted_id', $formatted_order_id)->first();
                  if($orderData){
                    $orderData = json_decode($orderData->order_json,true);
                    $phone = isset($orderData['shipping_address']['ph_number'])?$orderData['shipping_address']['ph_number']:''; 
                    if(empty($sms)){
                        if(!empty($phone)){
                          $sms = $phone;
                        }
                      }else{
                        if(!empty($phone)){
                          $sms = $sms.','.$phone;
                        }
                    }
                  }
                }  
            }

            if(!empty($sms)){
              $formatted_order_id = isset($emailReplaceData['ORDER_NO'])?$emailReplaceData['ORDER_NO']:'';
              if(!empty($formatted_order_id)){
                $orderData = \App\Order::select('order_json')->where('formatted_id', $formatted_order_id)->first();
                if($orderData){
                  $orderData = json_decode($orderData->order_json,true);
                  $phone = isset($orderData['shipping_address']['ph_number'])?$orderData['shipping_address']['ph_number']:''; 
                  if(empty($sms)){
                      if(!empty($phone)){
                        $sms = $phone;
                      }
                    }else{
                      if(!empty($phone)){
                        $sms = $sms.','.$phone;
                      }
                  }
                }
              }
            }
            //dd($sms);


            $admin_sms = $admin_role_id = [];
            if(!empty($results->to_admin)){
                //$admin_id =  array_filter(explode('-', $results->to_admin));
                $admin_role_id = explode('-', $results->to_admin);
                $admin_sms = AdminUser::select('contact_no')->whereIn('role_id', $admin_role_id)->pluck('contact_no')->toArray();
                $admin_sms = array_filter($admin_sms);
                if(empty($sms)){
                  if($admin_sms){
                    $sms = implode(',', $admin_sms);
                  }
                }else{
                  if($admin_sms){
                    $sms = $sms.','.implode(',', $admin_sms);
                  }
                  
                }
            }
            $mail_content = strip_tags($mail_content);
            if($smstype == 'testing'){
               $sms = $relevantdata['msisdn'];
               /*$url = $relevantdata['api_url'].'?username='.$relevantdata['username'].'&password='.$relevantdata['password'].'&msisdn='.$sms.'&message='.$mail_content; */
              $key = $relevantdata['username'];
              $secret = $relevantdata['password'];
              $url = $relevantdata['api_url'];
              $post_arr = ['msisdn'=>$sms,'message'=>$mail_content,'sender'=>$relevantdata['sender']];
            }else{
              $default_sms_server = \App\SmsTransmissionMethod::where(['is_default'=>'1','status'=>'1','type'=>'sms'])->first();
              $key = $default_sms_server->username;
              $secret = $default_sms_server->password;
              $url = $default_sms_server->api_url;
              $post_arr = ['msisdn'=>$sms,'message'=>$mail_content,'sender'=>$default_sms_server->sender];
              //$url = $default_sms_server->api_url.'?username='.$default_sms_server->username.'&password='.$default_sms_server->password.'&msisdn='.$sms.'&message='.$mail_content;
            }
            //dd($post_arr);
            $header = array(
                "Accept: application/json",
                "Authorization: Basic ".base64_encode("$key:$secret"),
                "content-type:  multipart/form-data"
                );
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 1,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $post_arr,
                CURLOPT_HTTPHEADER => $header,
            ));
            $response = curl_exec($curl);
            
            $json = json_decode($response,TRUE);
            
            if(isset($json['remaining_credit'])){
                $return  = ['status'=>'success','message'=>'send'];
            }else{
                 $return  = ['status'=>'unsuccess','message'=>'not send']; 
            }

            //dd($sms);
            /*find out to seller message*/
            if ($user_type=="seller") {
                $seller_ph_number ='';
                if(!empty($results->to_seller)){
                    if (isset($relevantdata) && $relevantdata['ORDER_NO']) {
                        $data =\App\Order::select('id')->where('formatted_id',$relevantdata['ORDER_NO'])->first();
                        if (isset($data) && $data) {
                            $datas =\App\OrderShop::select('shop_json')->where('order_id',$data->id)->get();
                            //dd($datas);
                            if (isset($datas) && $datas) {
                                foreach ($datas as $key => $values) {
                                    $shop_data = json_decode($values->shop_json);
                                    $sms_mail_content = $mail_content;
                                    if ($shop_data) {
                                        $seller_ph_number = $shop_data->seller_ph_number;
                                        if ($seller_ph_number) {
                                            $sms = $seller_ph_number;
                                        }
                                        //dd($sms);
                                        if ($shop_data->shop_name) {
                                            $seller_shop_name = $shop_data->shop_name[0];
                                            $sms_mail_content = str_replace('[SHOP_NAME]',$seller_shop_name, $sms_mail_content); 
                                            $sms_mail_content = strip_tags($sms_mail_content);
                                            //dd($mail_content);
                                            if($smstype == 'testing'){
                                               $sms = $relevantdata['msisdn'];
                                               /*$url = $relevantdata['api_url'].'?username='.$relevantdata['username'].'&password='.$relevantdata['password'].'&msisdn='.$sms.'&message='.$mail_content; */
                                              $key = $relevantdata['username'];
                                              $secret = $relevantdata['password'];
                                              $url = $relevantdata['api_url'];
                                              $post_arr = ['msisdn'=>$sms,'message'=>$sms_mail_content,'sender'=>$relevantdata['sender']];
                                            }else{
                                              $default_sms_server = \App\SmsTransmissionMethod::where(['is_default'=>'1','status'=>'1','type'=>'sms'])->first();
                                              $key = $default_sms_server->username;
                                              $secret = $default_sms_server->password;
                                              $url = $default_sms_server->api_url;
                                              $post_arr = ['msisdn'=>$sms,'message'=>$sms_mail_content,'sender'=>$default_sms_server->sender];
                                              //$url = $default_sms_server->api_url.'?username='.$default_sms_server->username.'&password='.$default_sms_server->password.'&msisdn='.$sms.'&message='.$mail_content;
                                            }
                                            
                                            $header = array(
                                                "Accept: application/json",
                                                "Authorization: Basic ".base64_encode("$key:$secret"),
                                                "content-type:  multipart/form-data"
                                                );
                                            $curl = curl_init();
                                            curl_setopt_array($curl, array(
                                                CURLOPT_URL => $url,
                                                CURLOPT_RETURNTRANSFER => true,
                                                CURLOPT_ENCODING => "",
                                                CURLOPT_MAXREDIRS => 1,
                                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                                CURLOPT_CUSTOMREQUEST => "POST",
                                                CURLOPT_POSTFIELDS => $post_arr,
                                                CURLOPT_HTTPHEADER => $header,
                                            ));
                                            $response = curl_exec($curl);
                                            
                                            $json = json_decode($response,TRUE);
                                            
                                            if(isset($json['remaining_credit'])){
                                                $return  = ['status'=>'success','message'=>'send'];
                                            }else{
                                                 $return  = ['status'=>'unsuccess','message'=>'not send']; 
                                            }
                                        }
                                           
                                    }
                                }
                                
                            }            
                        }
                        //dd($sms);
                    }         
                }
            }
            
            
            $saveNotiQueue = new NotificationQueue;
            $saveNotiQueue->subject = addslashes($subject);
            $saveNotiQueue->mail_content = addslashes(str_replace('↵', '', $mail_content));
            $saveNotiQueue->to_email = $sms;
            $saveNotiQueue->is_cron = '2';
            $saveNotiQueue->is_send = '1';
            $saveNotiQueue->notification_type = 'sms';
            $saveNotiQueue->to_user = $user_type;
            $saveNotiQueue->error_msg = json_encode($json);
            $saveNotiQueue->save();
            return $return;
    }

    public static function LineSend($event_slug, $emailData) {
    
        try{
            extract($emailData);
            $user_email = isset($user_email)?$user_email:'';
            $is_cron = isset($is_cron)?$is_cron:2;
            $user_type = isset($user_type)?$user_type:'user';        
            $file_with_path = isset($attachment)?$attachment:'';
            $noti_type_name = isset($noti_type_name)?$noti_type_name:'';

            /*fetch email template and revent data*/
            $notifications = DB::table(with(new NotificationEvent)->getTable().' as m')
                      ->join(with(new NotificationEventTemplate)->getTable().' as md', 'm.id', '=', 'md.noti_event_id')
                      ->join(with(new NotificationEventTemplateDetail)->getTable().' as ms', 'm.id', '=', 'ms.noti_event_id')
            ->leftjoin(with(new MailTemplateMaster)->getTable().' as lm', 'lm.id', '=', 'ms.master_template_id')
            ->leftjoin(with(new NotificationSenderDetail)->getTable().' as nsd', 'nsd.id', '=', 'md.token')
                      ->where('m.slug', $event_slug)
            ->where('md.noti_type_id', '6')
            ->where('ms.noti_type_id', '6')
            ->where('ms.lang_id', $lang_id);

            $results = $notifications->first();
      
            //dd($results,unserialize($results->noti_type),$event_slug, $emailData);
            
            if(!$results){
                return false;
            }
            $subject = stripslashes($results->mail_subject ?? '');
            $mail_content = stripslashes($results->mail_containt);

            /*Merge both layout and content*///template
            if(!empty($results->template)){
                $layout = stripslashes($results->template); 
                $mail_content = str_replace( '[CONTENT]' , $mail_content, $layout);
            }
          
            $emailReplaceData = [ 'SITE_NAME' => getConfigurationValue('SITE_SHORT_NAME'),
                            'SITE_FULL_NAME' => getConfigurationValue('SITE_FULL_NAME'),
                            'SITE_EMAIL' => getConfigurationValue('SITE_EMAIL'),
                            'SITE_CONTACT' => getConfigurationValue('SITE_CONTACT'),  
                            'SITE_URL'  => url('/')  
                ];

            /*Merge data  comman and relevant data*/              
            $emailReplaceData = array_merge($emailReplaceData, $relevantdata);

            /*replace value in the subject and email content*/
            foreach($emailReplaceData as $key => $value){
                $replaceKey = '['.$key.']';
                $subject = str_replace( $replaceKey, $value, $subject);
                $mail_content = str_replace( $replaceKey ,$value, $mail_content);
            }
            //dd($mail_content, $emailData,$results); 
            $error_msg = '';
            if(!empty($results->noti_type) && !empty($results->token) && $results->noti_type_id=="6"){
                $token_data = explode(",",$results->token);
                foreach($token_data as $key=>$tokenid){
                  //Get token from table
                  $token = \App\LineTransmissionMethod::where(['id'=>$tokenid])->value('token');
                  $message1 = strip_tags($mail_content);
                  $message = str_replace("<br>", "\n", $message1);
                  
                  //send message in line group   
                  if(!empty($token) && !empty($message)) {
                                
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

                  } 
                }
            }
      
            //Insert queue table
            $saveNotiQueue = new NotificationQueue;
            $saveNotiQueue->subject = addslashes($subject);
            $saveNotiQueue->mail_content = addslashes(str_replace('↵', '', $mail_content));
            $saveNotiQueue->to_email = $results->token;
            $saveNotiQueue->is_cron = '2';
            $saveNotiQueue->is_send = '1';
            $saveNotiQueue->notification_type = 'line';
            $saveNotiQueue->to_user = $user_type;
            $saveNotiQueue->error_msg = $error_msg;
            $saveNotiQueue->save();

            $return  = ['status'=>'success','message'=>$error_msg];

        }
        catch(Exception $e){
            $return  = ['status'=>'error','message'=>$e->getMessage()];
        }

        return $return;
        
    }

    public static function AddNotificationEventLog($event_id, $receiverData=null, $entity_id=null, $entity_type_id=null, $replaceData=array()) {
         
        /*Add Notification EventLog in the tables*/
          /*login User id*/
         $user_id = Auth::id();
          /*Actor type*/
         $user_type = Auth::user()->user_type;
         /*if(!isset($replaceData)){
            $replaceData = array();   
         }*/
        // $replaceData['USER_ID'] = $user_id;

        
         $replaceData['USER_NAME'] = Auth::user()->name;
         $replaceData['USER_IMAGE'] = Auth::user()->image;

         $replace_data = serialize($replaceData);

         $datasave = [];
        
         if(isset($entity_type_id) && ($entity_type_id == '1')){

             $productData= Product::select('user_id')->where('id', $entity_id)->first()->toArray();

             $user_data = User::where('id', $productData['user_id'])->first();
             $lang_id = $user_data->default_language;
             $receiver_type = $user_data->user_type;
             $receiver_id = $user_data->id;

             $datasave[] = ['event_id'=> $event_id, 'actor_id'=> $user_id, 'actor_type'=> $user_type, 'entity_id'=> $entity_id, 'lang_id'=>$lang_id, 'receiver_id'=> $receiver_id, 'receiver_type'=>$receiver_type,
             'entity_type_id' => $entity_type_id, 'replace_data' => $replace_data,'is_read'=> '0', 'created_at' =>date('Y-m-d H:i:s')

           ];

          if(isset($receiverData) && !empty($receiverData)){
              if(isset($receiverData['receiver_id']) && !empty($receiverData['receiver_id'])){
                  $user_data = User::where('id', $receiverData['receiver_id'])->first();

                  $datasave[] = ['event_id'=> $event_id, 'actor_id'=> $user_id, 
                           'actor_type'=> $user_type, 'entity_id'=> $entity_id, 
                           'lang_id'=>$user_data->default_language, 'receiver_id'=> $user_data->id, 
                           'receiver_type'=>$user_data->user_type,
                           'entity_type_id' => $entity_type_id, 'replace_data' => $replace_data, 'is_read'=> '0', 
                           'created_at' => date('Y-m-d H:i:s')

                  ];
              }
           }



         }else if(isset($entity_type_id) && ($entity_type_id == '3' || $entity_type_id == '4')){

          $user_data = User::where('id', $receiverData['receiver_id'])->first();
          $lang_id = $user_data->default_language;
          $receiver_type = $user_data->user_type;
          $receiver_id = $user_data->id;

          $datasave[] = ['event_id'=> $event_id, 'actor_id'=> $user_id, 'actor_type'=> $user_type,'lang_id'=>$lang_id, 'receiver_id'=> $receiver_id, 'receiver_type'=>$receiver_type,
             'entity_type_id' => $entity_type_id, 'replace_data' => $replace_data,'is_read'=> '0', 'created_at' =>date('Y-m-d H:i:s')

           ];



        }else{

          if(isset($receiverData) && !empty($receiverData)){
              if(isset($receiverData['receiver_id']) && !empty($receiverData['receiver_id'])){
                  $user_data = User::where('id', $receiverData['receiver_id'])->first();

                  $datasave[] = ['event_id'=> $event_id, 'actor_id'=> $user_id, 
                           'actor_type'=> $user_type, 'entity_id'=> $entity_id, 
                           'lang_id'=>$user_data->default_language, 'receiver_id'=> $user_data->id, 
                           'receiver_type'=>$user_data->user_type,
                           'entity_type_id' => $entity_type_id, 
                           'replace_data' => $replace_data,
                           'is_read'=> '0', 
                           'created_at' => date('Y-m-d H:i:s')

                  ];
              }
           }else{


              $datasave[] = ['event_id'=> $event_id, 'actor_id'=> $user_id, 
                           'actor_type'=> $user_type, 'entity_id'=> $entity_id, 
                           'lang_id'=>'', 'receiver_id'=> '', 
                           'receiver_type'=>'',
                           'entity_type_id' => $entity_type_id, 
                           'replace_data' => $replace_data,
                           'is_read'=> '1', 
                           'created_at' => date('Y-m-d H:i:s')
                      ];     


           }

        } 

         NotificationEventLog::insert($datasave);

     
    }
    public static function sendAllEnableNotification($event_slug, $emailData=null, $smsData=null, $notificationData = null){
        //dd($event_slug, $emailData, $smsData,$notificationData);
        $notidatas = NotificationEvent::select('noti_type','id')->where('slug', $event_slug)->first();
        $notificationTypes = isset($notidatas->noti_type)?unserialize($notidatas->noti_type):0;
        $event_id = $notidatas->id;
        $lang_id = session('default_lang');
        $lang_id = $lang_id?$lang_id:0;
        $returnData = [];
        /*1=>Email, 2=>SMS, 3=> WEB,  4=> PUSH, 5=>TOASTR, 6=> LINE    Notification Type*/
        //dd($notificationTypes);
        if(isset($notificationTypes) && !empty($notificationTypes)){
            foreach($notificationTypes as $id){
                switch ($id) {
                  case '1':
                    if(!empty($emailData)){
                       $response = self::MailSend($event_slug, $emailData);
                       $returnData[] =  $response;
                    }   
                  break;
                  case '2':
                    if(!empty($emailData)){
                      $response = self::SendSMS($event_slug, $emailData);
                      $returnData[] =  $response;
                    }
                  break;  
                  case '6':
                    if(!empty($emailData)){
                       $response = self::LineSend($event_slug, $emailData);
                       $returnData[] =  $response;
                    }     
                  break;
                }
            }
        }

        return $returnData;
    }

    public static function sendOrderNotificationEmail($formatted_order_id){

        $lang_id = session('default_lang');
        $lang_id = $lang_id?$lang_id:0;
        $orderInfo = \App\Order::where(['formatted_id'=>$formatted_order_id])->with('getOrderDetail')->with('getCurrency')->first();
        //$shippingProfile = json_decode($orderInfo->order_json);
        //$currencyData = \App\Currency::where('id',$orderInfo->currency_id)->first();
        //dd($orderInfo);
        $orderInfoJson = '';
        if(!empty($orderInfo)){
            $orderInfoJson = json_decode($orderInfo->order_json,true);
        }

        $offline_content = "";
        $offline_content_text = "";
        if($orderInfo->payment_type == '2') {

            $event_slug = 'new_order_create_offline_for_buyer'; // offline order notification template
            $admin_event_slug = 'new_order_create_offline_for_admin';
            $seller_event_slug = 'new_order_for_seller';
            $paymentBanks = \App\PaymentBank::where('status','1')->with('paymentBankName')->get();
            $payment_slip_link = action('Checkout\OrderController@confirmPayment',['orderId'=>$orderInfo->formatted_order_id]);
            $offline_content .= '<tr>
             <td style="border: none;font-family:Arial, Helvetica, sans-serif; font-size:12px; text-align: left; color:#000; padding: 0px 0 10px 0">'.Lang::get('checkout.bank_transfer_msg').'
             </td></tr>';
       
        $offline_content_text .= Lang::get('checkout.bank_transfer_msg'); 
      
            foreach($paymentBanks as $key => $bankData){
              $offline_content .= '<tr><td style="border: none;font-family:Arial, Helvetica, sans-serif; font-size:12px; text-align: left; color:#000; padding: 10px 0 0px 0"><p style="padding: 0; margin: 0; font-size: 12px; font-family:Arial, Helvetica, sans-serif;">'.($key+1).'. '.$bankData->paymentBankName->bank_name.'</p><p style="padding: 5px 0; margin: 0; font-size: 12px; font-family:Arial, Helvetica, sans-serif;">'.Lang::get('checkout.account_name').':'.$bankData->account_name.'</p><p style="padding: 0 0 15px 0; margin: 0; font-size: 12px; font-family:Arial, Helvetica, sans-serif;">'.Lang::get('checkout.account_no').":".$bankData->account_no."</p></td></tr>";
              $offline_content_text .= ($key+1).'. '.$bankData->paymentBankName->bank_name."\n".Lang::get('checkout.account_name').':'.$bankData->account_name."\n".Lang::get('checkout.account_no').":".$bankData->account_no."\n";
            }
            $offline_content .= '<tr><td style="border: none;font-family:Arial, Helvetica, sans-serif; font-size:12px; text-align: left; color:#000; padding: 0px 0 10px 0"><a href='.$payment_slip_link.'>'.Lang::get('checkout.upload_payment_slip').'</a></td></tr>';
            $offline_content_text .= '<a href='.$payment_slip_link.'>'.Lang::get('checkout.upload_payment_slip').'</a>';

        }else{

            $event_slug = 'new_order_create_online_for_buyer';  // Online order notification template
            $admin_event_slug = 'new_order_create_online_for_admin';
            $seller_event_slug = 'new_order_for_seller';
        }
        //dd($orderInfo->getOrderDetail);

        $ttl_unit = 0;
		$orderDetailJson=[];
        foreach ($orderInfo->getOrderDetail as $key => $value) {
            //dd($value);
            $orderDetailJson[$value->id] = json_decode($value->order_detail_json, true);
            $ttl_unit += $value->quantity;
        }
        $orderInfo->ttl_unit = $ttl_unit;

        $emailReplaceData = [];
        $emailReplaceData['ORDER_NO'] =$orderInfo->formatted_id;
        $emailReplaceData['ORDER_ID'] =$orderInfo->formatted_id;
        $emailReplaceData['USER_NAME'] = $orderInfo->user_name;
        $emailReplaceData['USER_EMAIL'] = $orderInfo->user_email;
        $admin_url = action('Admin\AdminHomeController@index');
        $emailReplaceData['LOGIN'] = '<a href="'.$admin_url.'" class="btn">login</a>';
        $emailReplaceData['ADMIN_URL'] = '<a href="'.$admin_url.'" class="btn"> Admin</a>';
        $ordertableData = CustomHelpers::orderProductDetailinTable($orderInfo,$orderDetailJson);
        $ordertextData = CustomHelpers::orderProductDetailinText($orderInfo,$orderDetailJson);
        $buyerInfoData = CustomHelpers::getOrderUserInfo($orderInfo, 'Y');
        $buyerInfoTextData = CustomHelpers::getOrderUserInfoText($orderInfo, 'Y');
        $buyerShipToData = CustomHelpers::buyerShipTo($orderInfoJson,'Y');
        $buyerBillToData = CustomHelpers::buyerBillTo($orderInfoJson, 'Y');
        $paymentInfoData = GeneralFunctions::getPaymentInfo($orderInfo, 'Y');
        $paymentInfoTextData = GeneralFunctions::getPaymentInfoText($orderInfo, 'Y');

        $buyerInfoHtml = '<table width="100%" style="border-collapse: collapse;"><tr>
         <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;font-family:Arial, Helvetica, sans-serif; font-size:12px; text-align: left; color:#000; padding: 0px 0 10px 0; font-weight:bold;">'.Lang::get('order.buyer_information').'</td></tr><tr>
         <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;font-family:Arial, Helvetica, sans-serif; font-size:12px; text-align: left; color: #000; padding: 0px 0 15px 0">'.$buyerInfoData.'</td></tr>';

        $buyerInfoHtml.= '<tr style="border-top:solid 1px #cccccc; border-bottom:solid 1px #cccccc">
          <td style="padding:15px 0 0 0; vertical-align:top; border-right:solid 1px #cccccc; width:50%;">
            <table width="100%">
              <tr><td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;font-family:Arial, Helvetica, sans-serif; font-size:12px; text-align: left; color:#000; padding: 0px 0 10px 0; font-weight:bold;">'.Lang::get('checkout.ship_to').'</td>
              </tr>
              <tr><td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;font-family:Arial, Helvetica, sans-serif; font-size:12px; text-align: left; color: #000; padding: 0px 0 0px 0">'.$buyerShipToData.'</td>
              </tr>
            </table>
          </td>
          <td style="padding:15px 0 0 10px; vertical-align:top; width:50%;vertical-align: top;">
            <table width="100%">
              <tr><td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;font-family:Arial, Helvetica, sans-serif; font-size:12px; text-align: left; color:#000; padding: 0px 0 10px 0; font-weight:bold;">'.Lang::get('checkout.bill_to').'</td></tr>
              <tr><td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;font-family:Arial, Helvetica, sans-serif; font-size:12px; text-align: left; color: #000; padding: 0px 0 15px 0; ">'.$buyerBillToData.'</td></tr>
            </table>
          </td>
        </tr>';
        //dd($orderInfoJson);
        $buyerInfoHtml .= '<tr style="border-top:solid 1px #cccccc; border-bottom:solid 1px #cccccc; ">
        <td style="border-right:solid 1px #cccccc; width:50%;vertical-align: top;">
          <table width="100%">
            <tr><td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;font-family:Arial, Helvetica, sans-serif; font-size:12px; text-align: left; color:#000; padding: 10px 0 10px 0; font-weight:bold; ">'.Lang::get('checkout.payment_method').'</td></tr>
            <tr><td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;font-family:Arial, Helvetica, sans-serif; font-size:12px; text-align: left; color: #000; padding: 0px 0 10px 0px">'.$paymentInfoData.'</td></tr>
          </table>
        </td>
        <td style="padding:0 0 0 10px; width:50%;vertical-align: top;">';

        $buyerInfoHtml .= '<table width="100%">
        <tr><td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;font-family:Arial, Helvetica, sans-serif; font-size:12px; text-align: left; color:#000; padding: 10px 0 10px 0; font-weight:bold;">'.Lang::get('order.shipping').'</td>
        </tr>
        </table>';

        $buyerInfoHtml .= '</td></tr></table>';
    
    $buyerInfoText = Lang::get('order.buyer_information').':'.$buyerInfoTextData."\n";
    $buyerInfoText .= Lang::get('checkout.ship_to').':'.$buyerShipToData."\n";
    $buyerInfoText .= Lang::get('checkout.bill_to').':'.$buyerBillToData."\n";
    $buyerInfoText .= Lang::get('checkout.payment_method').':'.$paymentInfoTextData."\n";
    //$buyerInfoText .= Lang::get('order.shipping').':'.$orderInfoJson['shipping_profile'][session('lang_code')]."\n";
    
    
        $emailReplaceDataAdmin = $emailReplaceData;

        $html_start_part = '<table width="100%" style="border-collapse: collapse;"><tbody>
            <tr>
            <td style="font-family: Helvetica,Arial,sans-serif; box-sizing: border-box;border: none;font-family:Arial, Helvetica, sans-serif; font-size:18px; text-align: left; color:#000; padding: 15px 0 15px 0;" valign="top">';

        $html_end_part = '</td></tr></tbody></table>';

        $emailReplaceData['ORDER_DETAILS_TABLE'] = $html_start_part.$buyerInfoHtml.$ordertableData.$offline_content.$html_end_part;
        $emailReplaceDataAdmin['ORDER_DETAILS_TABLE'] = $html_start_part.$buyerInfoHtml.$ordertableData.$html_end_part;

  $emailReplaceData['ORDER_DETAILS_TABLE_TEXTVERSION'] = $buyerInfoText.$ordertextData.$offline_content_text;
        $emailReplaceDataAdmin['ORDER_DETAILS_TABLE_TEXTVERSION'] = $buyerInfoText.$ordertextData;
        //buyer
        $emailData = ['lang_id'=>$lang_id, 'relevantdata'=>$emailReplaceData, 'user_email'=>$orderInfo->user_email,'is_cron' => 2, 'user_type' => 'user' ];
        
        Self::sendAllEnableNotification($event_slug, $emailData);

        //admin
        $emailData = ['lang_id'=>$lang_id, 'relevantdata'=>$emailReplaceDataAdmin, 'user_email'=>'','is_cron' => 2 , 'user_type' => 'admin' ];
        Self::sendAllEnableNotification($admin_event_slug, $emailData);

        //seller
        $emailData = ['lang_id'=>$lang_id, 'relevantdata'=>$emailReplaceDataAdmin, 'user_email'=>'','is_cron' => 2 , 'user_type' => 'seller' ];
        Self::sendAllEnableNotification($seller_event_slug, $emailData);
    }
}