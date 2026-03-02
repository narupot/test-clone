<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TranslationSource extends Model
{
    protected  $table = 'translation_source';

    protected $fillable = [
        'source', 'module_id', 'comment', 'created_by'
    ];
  
}
