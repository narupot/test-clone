<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use SoftDeletes;
    
    protected $table = 'currency';
    
    protected $dates = ['deleted_at'];

    public static function getCurrencyDetails() {
    	return self::select('id', 'code', 'image', 'symbol')->where('status', '1')->get();
    }

    public static function getCurrencyDetailsById($currency_id) {
    	return self::select('id', 'value', 'symbol', 'code')->where('id', $currency_id)->first();
    }

    public static function getDefaultCurrency() {

    	return self::where(['is_default'=>'1','status'=>'1'])->first();

    }    
}
