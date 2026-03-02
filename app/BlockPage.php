<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class BlockPage extends Model {

    protected $table = 'block_page';

    public $timestamps = false; 

    public static function insertBlockPage($page_url, $block_id) {      

            $group = new BlockPage;
            $group->block_id = $block_id;
            $group->page_url = $page_url;
            $group->save();                   
    }

    public static function updateBlockPage($page_url, $block_id) {      
            $countblock = Self::where('block_id',$block_id)->count();
            if($countblock > 0){
                self::where(['block_id'=>$block_id])
                ->update(['page_url' => $page_url]);
            }else{
                Self::insertBlockPage($page_url, $block_id);
            }
			
    }  

    public static function checkPage($block_id, $path){
        //return Self::whereRaw('FIND_IN_SET("'.$path.'",page_url)')->where('block_id',$block_id)->count();
        $urlstring = Self::where('block_id',$block_id)->value('page_url');

        $match = 0;
        if($urlstring){
            $url = substr($path,3);
            $url = $path;
            $myurl = ($url=='' or $url=='/') ? 'home' : $url;

            //$urlstring = 'home,admin/block/[0-9]/edit,product/*,users/orders/shipment/[^a-zA-Z0-9],users/*';
            //$myurl = 'admin/block/18/edit';
            $urlarray = explode(',',$urlstring);
            foreach ($urlarray as $key => $value) {
                if(strpos($value,'[0-9]')!== false || strpos($value,'[a-zA-Z0-9]')!== false || strpos($value,'*')!== false){
                    $first = explode('/',$value);
                    $sec = explode('/',$myurl);
                    $one = array_diff($first, $sec); 
                    $two = array_diff($sec, $first); 
                    $clean = array_merge($one, $two);
                    //dd($clean);
                    $reg = $clean[0];

                    array_shift($clean);
               
                    $uristring = implode('/',$clean);
                    
                    if(in_array($reg,['*','[a-zA-Z0-9]','[0-9]'])){
                        switch ($value) {
                            case (strpos($value,'[0-9]')!== false):
                                if(preg_match("/[0-9]/", $uristring) == 1) {
                                    $match = 1;
                                }
                                break;
                            case (strpos($value,'*')!== false):
                                if(preg_match("/./", $uristring) == 1) {
                                    $match = 1;
                                }
                                break;
                            case (strpos($value,'[a-zA-Z0-9]')!== false):
                                if(preg_match("/[a-zA-Z0-9]/", $uristring) == 1) {
                                    $match = 1;
                                }
                                break;
                            default:
                                # code...
                                break;
                        }
                    }
                    if($match == 1){
                        break;
                    }
                    
                }else{
                    if($value == $myurl){
                        $match = 1;
                        break;
                    }
                }
                
            }

            return $match;
        }else{
            return $match;
        }

    }                  
}
