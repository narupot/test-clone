<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CountryDesc extends Model
{  
    protected $table = 'country_desc';
    
    public $timestamps = false; 
    
    function languageDetail() {
        
        return $this->hasOne('App\Language', 'id', 'lang_id');
    }    
}
