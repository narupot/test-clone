<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class NotificationEventDetail extends Model {

     protected $table = 'notification_event_detail';
     public $timestamps = false;

     protected $fillable = ['noti_event_id', 'lang_id', 'mail_type', 'mail_desc'];
      
}
