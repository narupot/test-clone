<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentBank extends Model
{
    protected $table = 'payment_bank';

    function paymentBankName() {
        
        return $this->hasOne('App\PaymentBankDesc', 'payment_bank_id', 'id')->select('bank_name','lang_id','payment_bank_id')->where('lang_id', session('default_lang'));
    }

    public static function getBankDetail($bank_id) {
    	return self::where('id',$bank_id)->with('paymentBankName')->first();
    }

    public static function activeBankList(){
    	return self::where('status','1')->with('paymentBankName')->get();
    }
}
