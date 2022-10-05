<?php

namespace App;

class AdminOrder extends User
{
    protected $table = 'admin_order';
    
    public $timestamps = false;
    
    // public function role(){
    //     return $this->hasOne('App\Role', 'id', 'role_id');
    // }    
}
