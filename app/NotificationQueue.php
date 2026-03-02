<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class NotificationQueue extends Model
{
    protected $table = 'notification_queue';  
    
    public static function getMailToSent() {
    	return self::where(['is_cron'=>'1', 'is_send'=>'2'])->orderBy('id', 'Asc')->limit(10)->get();
    }   
}