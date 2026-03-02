<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationEventTemplate extends Model
{
    use SoftDeletes;
    
    protected $table = 'notification_event_template';
    
    protected $dates = ['deleted_at'];

    public $timestamps = false;

    protected $fillable = ['noti_event_id','noti_type_id', 'sender', 'to_buyer', 'to_seller', 'to_admin','token', 'cc', 'bcc', 'type'];


}
