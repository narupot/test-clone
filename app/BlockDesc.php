<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class BlockDesc extends Model {

    protected $table = 'block_desc';

    public $timestamps = false; 

    public static function insertBlockDesc($data_arr, $block_id) {      

        foreach ($data_arr as $key=>$value){

            $Block_desc = new BlockDesc;
            $Block_desc->block_id = $block_id;
            $Block_desc->lang_id = $key;
            $Block_desc->title = $value['title'];
            $Block_desc->tags = $value['tags'];
            $Block_desc->heading = $value['heading'];      
            $Block_desc->desc = $value['desc'];        
            $Block_desc->save();                   
        }
    }

    public static function updateBlockDesc($data_arr, $block_id) {      

        foreach ($data_arr as $key=>$value){

			self::where(['block_id'=>$block_id, 'lang_id'=>$key])
				->update(['title' => $value['title'], 'desc' => $value['desc'],'heading'=>$value['heading']]);                  
        }
    }                    
}
