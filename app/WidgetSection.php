<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WidgetSection extends Model
{
    protected  $table = 'widget_section';

    public static function getAll(){
		return Self::get();
    }
}
