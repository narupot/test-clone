<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Package extends Model {

    protected $table = 'package';  

    public function packagedesc(){
       return $this->hasOne('App\PackageDesc', 'package_id', 'id')->where('lang_id', session('default_lang')); 
    }

    public function packagedescAll(){
       return $this->hasMany('App\PackageDesc', 'package_id', 'id'); 
    }

    public static function getAllPackage(){
        return self::with('packagedesc')->get();
    }

    public static function getPackages(){
        return self::where(['status'=>'1'])->with('packagedesc')->get();
    }

    public static function getPackagesForFilter(){
        $dataPackages = self::where(['status'=>'1'])->with('packagedesc')->get();
        $dataArry = [];
        foreach($dataPackages as $dataPackage){
           $dataArry[$dataPackage->id] = $dataPackage->packagedesc->package_name;
        }
        return $dataArry;
    }

    public static function getPackagebyId($package_id){
    	return self::where('id',$package_id)->first();
    }   

    public static function packageData($package_id){
        return self::where('id',$package_id)->with('packagedescAll')->first(); 
    }
}
