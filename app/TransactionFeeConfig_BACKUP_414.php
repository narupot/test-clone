<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionFeeConfig extends Model
{
    protected $table = 'smm_transaction_fee_config';
    
    protected $fillable = [
        'name',
        'message',
        'tf',
        'effective_date',
        'current_tf'
    ];

    protected $casts = [
        'tf' => 'decimal:2',
        'current_tf' => 'decimal:2',
        'effective_date' => 'date'
    ];
} 