<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model {

    protected $table = 'unit';  

    public function unitdesc(){
       return $this->hasOne('App\UnitDesc', 'unit_id', 'id')->where('lang_id', session('default_lang')); 
    }

    public function unitdescAll(){
       return $this->hasMany('App\UnitDesc', 'unit_id', 'id'); 
    }

    public static function getAllUnit(){
        return self::with('unitdesc')->get();
    }

    public static function getUnits(){
        return self::where(['status'=>'1'])->with('unitdesc')->get();
    }

    public static function getUnitsForFilter(){
        $dataUnits = self::where(['status'=>'1'])->with('unitdesc')->get();
        $dataArry = [];
        foreach($dataUnits as $dataUnit){
           $dataArry[$dataUnit->id] = $dataUnit->unitdesc->unit_name;
        }
        return $dataArry;
    }



    public static function getUnitbyId($unit_id){
    	return self::where('id',$unit_id)->first();
    }   

    public static function unitData($unit_id){
        return self::where('id',$unit_id)->with('unitdescAll')->first(); 
    }
}
