<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class TranslationModule extends Model
{
    protected  $table = 'translation_module';

    public function SourceKey() {
        return $this->hasMany('App\TranslationSource', 'module_id', 'id')->select('module_id', 'source', 'comment');
    } 

    public static function getModuleSourceKey($module_id) {
    	self::select('module_name', 'lang_file_name')->where(['id'=>$module_id])->with('SourceKey')->first();
    }

    public static function getModuleSourceValue($module_id, $lang_id) {
        return DB::table(with(new \App\TranslationModule)->getTable().' as tm')
            ->join(with(new \App\TranslationSource)->getTable().' as ts', 'tm.id', '=', 'ts.module_id')
            ->leftjoin(with(new \App\TranslationSourceDetails)->getTable().' as tsd', [['ts.id', '=', 'tsd.source_id'], ['tsd.lang_id', '=',  DB::raw($lang_id)]])
            ->select('tm.id', 'tm.module_name', 'tm.lang_file_name', 'ts.source', 'tsd.source_value', 'tsd.comment')
            ->where(['tm.id'=>$module_id])
            ->orderBy('ts.id', 'asc')
            ->get();
    }    

    public static function getImportDetail($module_id, $lang_id) {

        return DB::table(with(new TranslationModule)->getTable().' as tm')
            ->join(with(new TranslationImport)->getTable().' as ti', 'tm.id', '=', 'ti.module_id')
            ->select('lang_id', 'csv_import_date')
            ->where(['tm.id'=>$module_id, 'ti.lang_id'=>$lang_id])
            ->first();
    }     
}
