<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class  TableConfiguration extends Model
{
    protected  $table = 'table_configuration';

    public static function getTableConfig($tableName, $type=''){

    	if($type == 'slug') {
    		return Self::where('slug',$tableName)->first();
    	}
    	else {
    		return Self::where('table_name',$tableName)->first();
    	}
    }   
}
