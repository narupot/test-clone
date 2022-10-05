<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationEventTemplateDetail extends Model
{  
    protected $table = 'notification_event_template_detail';
    
    function languageName() {
        
        return $this->hasOne('App\Language', 'id', 'lang_id');
    }    
}
