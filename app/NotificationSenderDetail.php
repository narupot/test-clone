<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class NotificationSenderDetail extends Model
{  
    protected $table = 'notification_sender_detail';
    protected $guarded = [];

}
