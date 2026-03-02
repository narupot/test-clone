<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentOption extends Model
{
    protected $table = 'payment_option';

    public function paymentOptName() {
        return $this->hasOne('App\PaymentOptionDesc', 'payment_option_id', 'id')->where('lang_id', session('default_lang'));
    }

    function paymentOptField() {
        return $this->hasMany('App\PaymentOptionField', 'payment_option_id', 'id')->select('id', 'field_slug')->where(['status'=>'1']);
    }

    public static function getPaymentOptions(){
    	return Self::where(['status'=>'1','payment_type'=>'1'])->where('slug','!=','credit')->with('paymentOptName')->get();
    }

    public static function getPaymentOptionBySlug($slug){
        return Self::where('slug',$slug)->first();
    }

    public function transactionFeeConfig(){
        return $this->hasOne('App\TransactionFeeConfig', 'payment_option_id', 'id');
    }

    public function paymentOptionDesc(){
        return $this->hasOne('App\PaymentOptionDesc', 'payment_option_id', 'id');
    }
}
