<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SeoSuperAdmin extends Model
{  
    protected $table = 'seo_super_seller_wise';
    
    /*function countryName() {
        
        //return $this->hasOne('App\CountryDesc', 'country_id', 'id')->where('lang_id', '2');
        return $this->hasOne('App\CountryDesc', 'country_id', 'id')->select(['country_id','country_name','lang_id'])->where('lang_id', session('default_lang'));
    } 
    
    function countryDesc() {
        
        return $this->hasMany('App\CountryDesc', 'country_id', 'id');
    }  */  
}
