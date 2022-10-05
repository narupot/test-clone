<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SeoPageDesc extends Model
{  
    protected $table = 'seo_page_desc';

    protected $fillable = [
        'seo_page_id', 'lang_id', 'meta_title', 'meta_description', 'meta_keyword'
    ];
    
    public $timestamps = false; 
    
  /*  function languageDetail() {
        
        return $this->hasOne('App\Language', 'id', 'lang_id');
    }   */ 
}
