<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class MenuItemsDesc extends Model {

    protected $table = 'megamenu_items_desc';
    public $timestamps = false; 
    protected $guarded = []; 

    public function getLangData() {       
        return $this->hasOne('App\Language', 'id', 'lang_id');
    }                   
}
