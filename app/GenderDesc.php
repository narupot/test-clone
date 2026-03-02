<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class GenderDesc extends Model {

    protected $table = 'gender_desc';

    public $timestamps = false; 

    public static function insertGenderDesc($data_arr, $gender_id) {      
        
        foreach ($data_arr as $key=>$value){
            
            $genderdesc = new GenderDesc;
            $genderdesc->gender_id = $gender_id;
            $genderdesc->lang_id = $key;
            $genderdesc->gender_name = $value['gender_name'];            
            $genderdesc->save();                   
        }
    }

    public static function updateGenderDesc($data_arr, $gender_id) {      
        
        self::where(['gender_id'=>$gender_id])->delete();   
        self::insertGenderDesc($data_arr,$gender_id);
    }                    
}
