<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SeoSuperAdminDesc extends Model
{  
    protected $table = 'seo_super_seller_wise_desc';

    protected $fillable = [
        'seo_super_id', 'lang_id', 'meta_title', 'meta_description', 'meta_keyword'
    ];
    
    public $timestamps = false; 
    
   /* function languageDetail() {
        
        return $this->hasOne('App\Language', 'id', 'lang_id');
    }   */ 
}
