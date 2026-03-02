<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;
class Widget extends Model {

    protected $table = 'widget';  

    public function blockDesc() {       
        return $this->hasOne('App\BlockDesc', 'block_id', 'id')->where('lang_id', session('default_lang'));
    } 

    public function blockPage(){
        return $this->hasOne('App\WidgetPage','widget_id','id');
    }

    public function blockCustGroup(){
        return $this->hasOne('App\WidgetCustomerGroup','widget_id','id');
    }

    public function staticBlockDesc() {       
        return $this->hasOne('App\StaticBlockDesc', 'static_block_id', 'type_name')->where('lang_id', session('default_lang'));
    }

    public function bannerGroup() {       
        return $this->hasOne('App\BannerGroup', 'id', 'type_name');
    }

    public function getBanner() {       
        return $this->hasOne('App\Banner', 'id', 'type_name');
    }

    public function getBannerDesc() {       
        return $this->hasOne('App\BannerDesc', 'banner_id', 'type_name')->where('lang_id', session('default_lang'));
    }

    public function staticBlock() {       
        return $this->hasOne('App\StaticBlock', 'id');
    }

    public static function insertBlock($dataArr){ 
       
            $cms = new Widget;
            $cms->type = $dataArr['type'];
            $cms->type_name = $dataArr['type_name'];
            $cms->updated_by = $dataArr['updated_by'];
            $cms->save();
                    
    }

    public static function getBlockByIdArr(){
        return Self::whereIn('section_id',[1,2,3,4,5])->orderBy('section_id','ASC')->orderBy('order_by','ASC')->get();
    }

    public static function checkBlockExist($sec_id,$page='',$block_key,$type){
        $groupCon = $pageCon = 0;
        $user_group_id = (Auth::check()) ? session('user_group_id') : 1;
        switch($type){
            case 'widget' : 
            $section = DB::table(with(new \App\Widget)->getTable().' as widget')  
                ->join(with(new \App\StaticBlock)->getTable().' as static', 'widget.type_name', '=', 'static.id')
                ->where(['widget.type'=>'widget','static.url'=>$block_key,'widget.section_id'=>$sec_id])
                ->select('widget.id','widget.customer_group','widget.pages')
                ->first();        
                break;
        }
        

        if(count($section)){
                if($section->customer_group == 1){
                    $groupCon = 1;
                }elseif ($section->customer_group == 2) {
                   $checkGroup = \App\WidgetCustomerGroup::checkGroup($section->id,$user_group_id);
                   $groupCon = ($checkGroup) ? 1 : 0;
                }else{
                    $checkGroup = \App\WidgetCustomerGroup::checkGroup($section->id,$user_group_id);
                    $groupCon = ($checkGroup) ? 0 : 1;
                }

                if($groupCon){
                    
                    if($section->pages == 1){
                        $pageCon = 1;
                    }elseif ($section->pages == 2) {
                       $checkPage = \App\WidgetPage::checkPage($section->id,$page);
                       $pageCon = ($checkPage) ? 1 : 0;
                    }else{
                        $checkPage = \App\WidgetPage::checkPage($section->id,$page);
                        $pageCon = ($checkPage) ? 0 : 1;
                    }
                }

                return ($pageCon) ? $section : '';
        }else{
            return '';
        }
        
    }
            
}
