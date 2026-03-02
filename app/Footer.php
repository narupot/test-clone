<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Footer extends Model {

    protected $table = 'footer';  

    public function footerDesc() {
        return $this->hasOne('App\FooterDesc', 'footer_id', 'id')->where('lang_id', session('default_lang'));
    }

    public static function getFrontFooter(){
    	return FooterDesc::where(['lang_id'=>session('default_lang'),'footer_id'=>'1'])->value('description');
    }
}
