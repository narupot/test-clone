<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionFeeConfig extends Model
{
    protected $table = 'transaction_fee_config';

    protected $fillable = [
        'name',
        'message',
        'tf',
        'effective_date',
        'current_tf'
    ];

    protected $dates = [
        'effective_date',
        'created_at',
        'updated_at'
    ];
} 