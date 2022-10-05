<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model {

    protected $table = 'order_payment';

    public $timestamps = false; 
    protected $guarded = [];
                    
}
