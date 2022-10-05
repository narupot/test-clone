<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class SmsTransmissionMethod extends Model
{
    protected $table = 'sms_transmission_method';
    
    public $timestamps = false;


}
