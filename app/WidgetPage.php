<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class WidgetPage extends Model {

    protected $table = 'widget_page';

    public $timestamps = false; 

    public static function insertBlockPage($page_url, $widget_id) {      

            $group = new WidgetPage;
            $group->widget_id = $widget_id;
            $group->page_url = $page_url;
            $group->save();                   
    }

    public static function updateBlockPage($page_url, $widget_id) {      
            $countblock = Self::where('widget_id',$widget_id)->count();
            if($countblock > 0){
                self::where(['widget_id'=>$widget_id])
                ->update(['page_url' => $page_url]);
            }else{
                Self::insertBlockPage($page_url, $widget_id);
            }
			
    }  

    public static function checkPage($widget_id, $path){     

        $urlstring = Self::where('widget_id',$widget_id)->value('page_url');
      
        $match = 0;
        if($urlstring){

            $url = substr($path,3);
            $myurl = ($url=='') ? 'home' : $url;

            $urlarray = explode(',',$urlstring);
            //dd($urlstring,$myurl);
            foreach ($urlarray as $key => $value) {
                if(strpos($value,'[0-9]')!== false || strpos($value,'[a-zA-Z0-9]')!== false || strpos($value,'*')!== false){
                    $first = explode('/',$value);
                    $sec = explode('/',$myurl);
                    $one = array_diff($first, $sec); 
                    $two = array_diff($sec, $first); 
                    $clean = array_merge($one, $two);                    
                    $reg = $clean[0];

                    array_shift($clean);
               
                    $uristring = implode('/',$clean);
                    if(count($first) == count($sec)){
                        
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
                                    break;
                            }
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
