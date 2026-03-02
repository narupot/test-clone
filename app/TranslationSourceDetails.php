<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TranslationSourceDetails extends Model
{
    protected  $table = 'translation_source_details';

    public function sourceName() {
        
        return $this->hasOne('App\TranslationSource', 'id', 'source_id');
    }
}
