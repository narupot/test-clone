<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Session;

class TableColumnConfiguration extends Model
{
    protected  $table = 'table_column_configuration';
    public $timestamps = false;

    public static function columnConfigValue($columnName){
    	return Self::where('column_name',$columnName)->first();
    }

    public function description(){
       return $this->hasOne('App\TableColumnConfigurationDesc', 'column_id', 'id')->where('lang_id',Session::get('default_lang'))->select('column_id','display_name'); 
    }
}
