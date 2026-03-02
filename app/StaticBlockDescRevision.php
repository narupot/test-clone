<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class StaticBlockDescRevision extends Model {

    protected $table = 'static_block_desc_revision';

    public $timestamps = false; 

    public static function insertStaticBloclDescRevision($data_arr, $blok_id) {
        $def_lang_id = session('admin_default_lang');
        $revisiondata = StaticBlockDescRevision::where(['static_block_id'=>$blok_id,'lang_id'=>$def_lang_id])->get()->last();
        if(!empty($revisiondata->revision)){
            $rev_id = $revisiondata->revision;
            $rev_ids = $rev_id+1;                
        }else{
            $rev_ids = 1;
        }      
        //dd($data_arr);
        foreach ($data_arr as $key=>$value){
            
            $staticblockdescrevision = new StaticBlockDescRevision;
            $staticblockdescrevision->revision = $rev_ids;
            $staticblockdescrevision->static_block_id = $blok_id;
            $staticblockdescrevision->lang_id = $key;
            $staticblockdescrevision->static_block_title = $value['page_title']; 
            $staticblockdescrevision->static_block_desc = $value['page_desc'];
            //dd($value['lang_id']);
            $staticblockdescrevision->save();                   
        }
    }

    public static function updatestatickblockDescRevision($data_arr, $blok_id) {      
        
        self::insertStaticBloclDescRevision($data_arr,$blok_id);
    }                    
}
