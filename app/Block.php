<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
use Auth;
use DB;
class Block extends Model {

    protected $table = 'block';  

    public function blockDesc() {       
        return $this->hasOne('App\BlockDesc', 'block_id', 'id')->where('lang_id', session('default_lang'));
    } 

    public function blockPage(){
        return $this->hasOne('App\BlockPage','block_id','id');
    }

    public function blockCustGroup(){
        return $this->hasOne('App\BlockCustomerGroup','block_id','id');
    }

    public function staticBlockDesc() {       
        return $this->hasOne('App\StaticBlockDesc', 'static_block_id', 'type_id')->where('lang_id', session('default_lang'));
    }

    public function bannerGroup() {       
        return $this->hasOne('App\BannerGroup', 'id', 'type_id');
    }

    public function getBanner() {       
        return $this->hasOne('App\Banner', 'id', 'type_id');
    }

    public function getBannerDesc() {       
        return $this->hasOne('App\BannerDesc', 'banner_id', 'type_id')->where('lang_id', session('default_lang'));
    }

    public function staticBlock() {       
        return $this->hasOne('App\StaticBlock', 'id');
    }

  

    public static function insertBlock($dataArr){ 
       
            $cms = new Block;

            $cms->type = $dataArr['type'];
            $cms->type_id = $dataArr['type_id'];
            $cms->updated_by = $dataArr['updated_by'];
            $cms->save();
                    
    }

    public static function getBlockByIdArr(){
        return Self::whereIn('section_id',[1,2,3,4,5])->where('status','1')->orderBy('section_id','ASC')->orderBy('order_by','ASC')->get();
    }

    public static function checkBlockExist($sec_id,$page='',$block_key,$type){
        $groupCon = $pageCon = 0;
        $user_group_id = (Auth::check()) ? session('user_group_id') : 1;
        switch($type){
            case 'static-block' : 
            $section = DB::table(with(new \App\Block)->getTable().' as block')  
                ->join(with(new \App\StaticBlock)->getTable().' as static', 'block.type_id', '=', 'static.id')
                ->where(['block.type'=>'static-block','static.url'=>$block_key,'block.section_id'=>$sec_id])
                ->select('block.id','block.customer_group','block.pages')
                ->first();
            //dd($section);
                break;
        }
        

        if(count($section)){
                if($section->customer_group == 1){
                    $groupCon = 1;
                }elseif ($section->customer_group == 2) {
                   $checkGroup = \App\BlockCustomerGroup::checkGroup($section->id,$user_group_id);
                   $groupCon = ($checkGroup) ? 1 : 0;
                }else{
                    $checkGroup = \App\BlockCustomerGroup::checkGroup($section->id,$user_group_id);
                    $groupCon = ($checkGroup) ? 0 : 1;
                }

                if($groupCon){
                    /***checking pages****/
                    if($section->pages == 1){
                        $pageCon = 1;
                    }elseif ($section->pages == 2) {
                       $checkPage = \App\BlockPage::checkPage($section->id,$page);
                       $pageCon = ($checkPage) ? 1 : 0;
                    }else{
                        $checkPage = \App\BlockPage::checkPage($section->id,$page);
                        $pageCon = ($checkPage) ? 0 : 1;
                    }
                }
                //dd($pageCon);
                return ($pageCon) ? $section : '';
        }else{
            return '';
        }
        
    }
    
    public static function checkPage($block_id, $path,$section_data){
        //return Self::whereRaw('FIND_IN_SET("'.$path.'",page_url)')->where('block_id',$block_id)->count();
        $urlstring =$section_data->page_url;

        $match = 0;
        if($urlstring){

            if(getConfigValue('LANG_CODE_IN_URL')=='Y'){
                $sublen = strlen(session('lang_code'))+1;
                $url = substr($path,$sublen);
                $trim = trim($path,'/');
                if(strlen($trim)<=3 && $trim != session('lang_code')){
                    $url = 'xxxxxxxx';
                }
            }else{
                $url = $path;
            }
           $myurl = ($url=='' || $url=='/' ) ? 'home' : $url;
            //$urlstring = 'home,admin/block/[0-9]/edit,product/*,users/orders/shipment/[^a-zA-Z0-9],users/*';
            //$myurl = 'admin/block/18/edit';
            $urlarray = explode(',',$urlstring);
            foreach ($urlarray as $key => $value) {

                if($value == urldecode($myurl)){
                    $match = 1;
                    break;
                }

                if(strpos($value,'[0-9]')!== false || strpos($value,'[a-zA-Z0-9]')!== false || strpos($value,'*')!== false){

                    $first = explode('/',$value);
                    $sec = explode('/',$myurl);
                    $one = array_diff($first, $sec); 
                    $two = array_diff($sec, $first); 
                    $clean = array_merge($one, $two);
                    
                    $reg = $clean[0];

                    array_shift($clean);
               
                    $uristring = implode('/',$clean);
                    
                    if(in_array($reg,['*','[a-zA-Z0-9]','[0-9]'])){
                        switch ($value) {
                            case (strpos($value,'[0-9]')!== false):
                                if(preg_match("/[0-9]/", $uristring) == 1 && count($first) == count($sec)) {
                                    $match = 1;
                                }
                                break;
                            case (strpos($value,'*')!== false):
                                if(preg_match("/./", $uristring) == 1) {
                                    $match = 1;
                                }
                                break;
                            case (strpos($value,'[a-zA-Z0-9]')!== false):
                                if(preg_match("/[a-zA-Z0-9]/", $uristring) == 1 && count($first) == count($sec)) {
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
                    
                }
                
            }

            return $match;
        }else{
            return $match;
        }

    }
  
}
