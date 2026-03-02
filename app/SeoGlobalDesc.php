<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SeoGlobalDesc extends Model
{  
    protected $table = 'seo_global_desc';

    protected $fillable = [
        'seo_global_id', 'lang_id', 'meta_title', 'meta_description', 'meta_keyword'
    ];
    
    public $timestamps = false; 
    
  /*  function languageDetail() {
        
        return $this->hasOne('App\Language', 'id', 'lang_id');
    }   */ 
}
