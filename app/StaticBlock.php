<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class StaticBlock extends Model {

    protected $table = 'static_block';  

    public function staticBlockDesc() {       
        return $this->hasOne('App\StaticBlockDesc', 'static_block_id', 'id')->where('lang_id', session('admin_default_lang'));
    } 

    public function blockDesc() {       
        return $this->hasOne('App\StaticBlockDesc', 'static_block_id', 'id')->where('lang_id', session('default_lang'));
    }

    public static function getStaticBlock(){
    	return self::select('id', 'url', 'status','is_system', 'created_at', 'updated_at')->with('staticBlockDesc')->orderBy('is_system','DESC')->get();
    }

    public static function getStaticCustomeBlock(){
        return self::select('id', 'url', 'status','is_system', 'created_at', 'updated_at')->with('staticBlockDesc')->where(['is_system'=>'0'])->orderBy('is_system','DESC')->get();
    }
    public static function getStaticSystemBlock(){
        return self::select('id', 'url', 'status','is_system', 'created_at', 'updated_at')->with('staticBlockDesc')->where(['is_system'=>'1'])->orderBy('is_system','DESC')->get();
    }

    public static function getStaticBlockbyId($id){
    	return self::select('id', 'url', 'status', 'created_at','is_system', 'updated_at')->with('staticBlockDesc')->where(['id'=>$id])->first();
    }             
}
