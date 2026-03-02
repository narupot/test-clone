<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class NotificationEventLog extends Model {

     protected $table = 'notification_event_log';
    // public $timestamps = false;

    // protected $fillable = ['noti_event_id', 'lang_id', 'mail_type', 'mail_desc'];
    public function actorUsers(){

        return $this->hasOne('App\User', 'id', 'actor_id')->select('id','name', 'image');
    }

    public function notificationEntity(){
        return $this->hasOne('App\NotificationEntity', 'id', 'entity_type_id')->select('id','entity_name', 'module');
    }
      
}
