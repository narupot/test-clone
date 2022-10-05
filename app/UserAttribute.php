<?php

namespace App;
use Illuminate\Database\Eloquent\Model;
class UserAttribute extends Model
{
    protected $table = 'user_attribute';
    
    public $timestamps = false;
    
    public function userAttrDesc(){
       return $this->hasOne('App\CustomerAttributeDesc', 'cust_attr_id', 'attribute_id')->select('id', 'cust_attr_id', 'name')->where('lang_id', session('default_lang')); 
    }

    public function userAttrValDesc(){
       return $this->hasMany('App\CustomerAttrValueDesc', 'cust_attr_id', 'attribute_id')->where('lang_id', session('default_lang')); 
    }

    public function userAttrVal(){
       return $this->hasMany('App\CustomerAttrValue', 'cust_attr_id', 'attribute_id'); 
    }

    public function userAttr(){
       return $this->hasOne('App\CustomerAttribute', 'id', 'attribute_id'); 
    }

    public static function attributeUserValue($attribute_id, $user_id) {
      return self::select('attribute_value_id', 'attribute_value')->where(['user_id'=>$user_id, 'attribute_id'=>$attribute_id])->first();
    }
}
