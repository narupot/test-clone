<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class EmailTransmissionMethod extends Model
{
    protected $table = 'email_transmission_method';
    
    public $timestamps = false;


}
