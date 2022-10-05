<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class StaticBlockDesc extends Model {

    protected $table = 'static_block_desc';

    public $timestamps = false; 

    public static function insertBlockDesc($data_arr, $block_id) {      

        foreach ($data_arr as $key=>$value){

            $Block_desc = new StaticBlockDesc;
            $Block_desc->static_block_id = $block_id;
            $Block_desc->lang_id = $key;
            $Block_desc->page_title = $value['page_title'];
            $Block_desc->page_desc = $value['page_desc'];              
            $Block_desc->save();                   
        }
    }

    public static function updateBlockDesc($data_arr, $block_id) {      

        foreach ($data_arr as $key=>$value){

			self::where(['static_block_id'=>$block_id, 'lang_id'=>$key])
				->update(['page_title' => $value['page_title'], 'page_desc' => $value['page_desc']]);                  
        }
    }                    
}
