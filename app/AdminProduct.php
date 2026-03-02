<?php

namespace App;

class AdminProduct extends User
{
    protected $table = 'admin_product';
    
    public $timestamps = false;
    
    // public function role(){
    //     return $this->hasOne('App\Role', 'id', 'role_id');
    // }    
}
