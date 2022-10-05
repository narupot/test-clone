<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class LineTransmissionMethod extends Model
{
    protected $table = 'line_transmission_method';
    
    public $timestamps = false;


}
