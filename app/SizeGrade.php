<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class SizeGrade extends Model {

    protected $table = 'size_grade';  

    public function sizegradedesc(){
       return $this->hasOne('App\SizeGradeDesc', 'size_grade_id', 'id')->where('lang_id', session('default_lang')); 
    }

    public function sizegradedescAll(){
       return $this->hasMany('App\SizeGradeDesc', 'size_grade_id', 'id'); 
    }

    public static function getAllSizeGrade(){
        return self::with('sizegradedesc')->get();
    }

    public static function SizeGradeData($id){
        return self::where('id',$id)->with('sizegradedesc')->first(); 
    }

    public static function getSizeGradebyId($id){
        return self::where('id',$id)->first();
    }

    public static function sizeGrade(){
        $data = Self::where('status','1')->with('sizegradedesc')->get();
        $size_arr = $grade_arr = [];
        foreach ($data as $key => $value) {
            if($value->type=='size'){
                $size_arr[] = ['slug'=>$value->slug,'name'=>$value->sizegradedesc->name];
            }else{
                $grade_arr[] = ['slug'=>$value->slug,'name'=>$value->sizegradedesc->name];
            }
        }
        return ['size'=>$size_arr,'grade'=>$grade_arr];
    }
}
