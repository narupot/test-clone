<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model {

    protected $table = 'standard_badge';         

    public function badgedesc() {
       return $this->hasOne('App\BadgeDesc', 'badge_id', 'id')->where('lang_id', session('default_lang')); 
    }

    public function descAll(){
       return $this->hasMany('App\BadgeDesc', 'badge_id', 'id'); 
    }

    public static function getBadge() {
        return self::where('status', '1')->with('badgedesc')->get();
    } 

    public static function getBadgeForFilter() {
        $dataBadges = self::where('status', '1')->with('badgedesc')->get();
        $dataArry = [];
        foreach($dataBadges as $dataBadge){
           $dataArry[$dataBadge->id] = $dataBadge->badgedesc->badge_name;
        }
        return $dataArry;
    }     

    public static function getAllBadge() {
        return self::with('badgedesc')->get();
    }

    public static function getBadgebyId($badge_id) {
    	return self::where('id',$badge_id)->first();
    }

    public static function badgeData($badge_id){
        return self::where('id',$badge_id)->with('descAll')->first(); 
    }
}
