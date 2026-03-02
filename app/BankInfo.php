<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;


class BankInfo extends Model
{
    protected $table = 'bank_info';


    public static function dropdown()
    {
        return self::where('status', 1)->pluck('bank_name', 'bank_code');
    }

}