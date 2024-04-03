<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class StaticPage extends Model {

    protected $table = 'static_page';  

    public function staticPageDesc($lang_id=null) { 

        //get the page url
        return $this->hasOne('App\StaticPageDesc', 'static_page_id', 'id')->where('lang_id', session('default_lang'));
        $url = explode('/', url()->current());
        $urlkey = end($url);
        $pageId = self::where('url',$urlkey)->value('id');        
        $data = \DB::table(with(new StaticPageDesc)->getTable())->where(['lang_id'=> session('admin_default_lang'),'static_page_id'=>$pageId])->get();          
        $default_lang_id = '6';     
        if(!count($data)){
            return $this->hasOne('App\StaticPageDesc', 'static_page_id', 'id')->where('lang_id', $default_lang_id);
        }else{
            return $this->hasOne('App\StaticPageDesc', 'static_page_id', 'id')->where('lang_id', session('default_lang'));    
        }
        
    }  

    public static function getStaticPage(){
    	return self::select('id', 'url', 'status', 'is_system', 'created_at', 'updated_at')->with('staticPageDesc')->orderBy('is_system','DESC')->latest()->get();
    }

    public static function getStaticCustomeBlock(){
        return self::select('id', 'url', 'status','is_system', 'created_at', 'updated_at')->with('staticPageDesc')->where(['is_system'=>'0'])->orderBy('is_system','DESC')->get();
    }
    public static function getStaticSystemBlock(){
        return self::select('id', 'url', 'status','is_system', 'created_at', 'updated_at')->with('staticPageDesc')->where(['is_system'=>'1'])->orderBy('is_system','DESC')->get();
    }

    public static function getStaticPagebyId($id){
    	return self::select('id', 'url', 'status', 'created_at', 'updated_at','metaimage','fbimage','twimage','insimage','header_footer')->with('staticPageDesc')->where(['id'=>$id])->first();
    }   

    public static function pageBuilderData(){
        return \DB::table(with(new StaticPage)->getTable().' as p')
                ->join(with(new StaticPageDesc)->getTable().' as pd', 'p.id', '=', 'pd.static_page_id')
                ->select('p.id', 'p.url','p.image', 'p.status', 'p.created_at', 'p.updated_at', 'pd.meta_title','pd.meta_keyword','pd.meta_desc','pd.page_title','pd.page_desc','pd.page_heading')
                ->where(['p.is_system'=>'0','pd.lang_id'=> session('default_lang')])
                ->get();
    }

    public static function singlePageDate($page_id,$lang_id){
        return \DB::table(with(new StaticPage)->getTable().' as p')
                ->join(with(new StaticPageDesc)->getTable().' as pd', 'p.id', '=', 'pd.static_page_id')
                ->select('p.id', 'p.url','p.image', 'p.status', 'p.created_at', 'p.updated_at', 'pd.meta_title','pd.meta_keyword','pd.meta_desc','pd.page_title','pd.page_desc','pd.page_heading')
                ->where(['pd.lang_id'=> $lang_id,'p.id'=>$page_id])
                ->first();
    }          
}
