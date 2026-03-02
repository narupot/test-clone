<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected  $table = 'banners';

    public function bannerdesc(){
       return $this->hasOne('App\BannerDesc', 'banner_id', 'id')->where('lang_id', session('default_lang')); 
    }

    public function bannergroup(){
       return $this->hasOne('App\BannerGroup', 'id', 'group_id'); 
    }

    public static function getBannerDetail($group_id){
        $cur_date = date('Y-m-d');
        $cur_sec = strtotime($cur_date);
        $banner_arr = [];
    	$banner = Self::where(['group_id'=>$group_id,'status'=>'1'])->select('id','banner_image','banner_url','url_target','start_date','end_date', 'admin_title')->orderBy('sort_order')->with('bannerdesc')->get();
        if(count($banner)){
            foreach ($banner as $key => $value) {
                $start_sec = strtotime($value->start_date);
                $end_sec   = strtotime($value->end_date);
                $datecon = 0;
                
                if($start_sec>0 && $end_sec>0){
                    if($cur_sec >= $start_sec && $cur_sec <= $end_sec){
                        $datecon = 1;
                    }
                }elseif($start_sec>0 && $cur_sec >= $start_sec){
                        $datecon = 1;
                }elseif($end_sec>0 && $cur_sec <= $end_sec){
                    $datecon = 1;
                }
                if($datecon){
                    $banner_arr[] = $value;
                }
            }
        }
       
    	return $banner_arr;
    }
   
}
