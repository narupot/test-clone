<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class NotificationEvent extends Model
{
    use SoftDeletes;
    
    protected $table = 'notification_event';
    
    protected $dates = ['deleted_at'];

    public $timestamps = false;


    
    
    function mailSetting() {
        
        return $this->hasMany('App\NotificationEventTemplateDetail', 'noti_event_id', 'id');
    }

    public static function getNotificationEventDetail($id, $mail_type) {
    	//return self::where('id', '=', $id)->first();
   

       return DB::table(with(new NotificationEvent)->getTable().' as mt')
                ->leftjoin(with(new NotificationEventTemplate)->getTable().' as mtd', [['mt.id', '=', 'mtd.noti_event_id'], ['mtd.noti_type_id', '=' , DB::raw("'".$mail_type."'")]]
             )->select('mt.id', 'mt.mail_type', 'mt.mail_desc', 'mtd.noti_type_id', 'mtd.noti_type_id', 'mtd.sender', 'mtd.to_buyer', 'mtd.to_seller', 'mtd.to_admin','mtd.token','mtd.buyer_phone_login','mtd.buyer_shipping_phone', 'mtd.cc', 'mtd.bcc', 'mtd.type', 'mt.icon')->where('mt.id', '=', $id)->first();

    }


    public static function getNotificationEvent($id, $mail_type) {
       //return self::where('id', '=', $id)->first();

            return DB::table(with(new NotificationEvent)->getTable().' as mt')
                ->join(with(new NotificationEventTemplateDetail)->getTable().' as ms', [['mt.id', '=', 'ms.noti_event_id'], ['ms.noti_type_id', '=' , DB::raw("'".$mail_type."'")]]
             )
             ->join(with(new Language)->getTable().' as l', 'l.id', '=', 'ms.lang_id')   
             
             ->select('ms.id', 'ms.mail_subject', 'l.languageName', 'ms.created_at', 'ms.updated_at')


             ->where('mt.id', '=', $id)->get();
    }

    public function GetNotificationEventDetails(){

       // echo session('default_lang');
        

       return $this->hasOne('App\NotificationEventDetail','noti_event_id','id')
              ->select('noti_event_id','id', 'mail_desc')
              ->where('lang_id', session('default_lang'));
    }


    /*function mailSettingwithlangid() {
        return $this->hasOne('App\MailSetting', 'noti_event_id', 'id');
        //$lang_id ->where('lang_id',$lang_id);
    }*/


}
