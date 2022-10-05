<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
class SyncLog extends Model
{
    protected $table = 'sync_log';
    public $timestamps = false; 

    public static function todayLimit($slug){
    	$date = date('Y-m-d');

    	return Self::where(DB::raw("date(created_at)"),$date)->where('section',$slug)->first();
    }
}