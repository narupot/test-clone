<?php

namespace App;

class AdminCustomer extends User
{
    protected $table = 'admin_customer';
    
    public $timestamps = false;
    
    // public function role(){
    //     return $this->hasOne('App\Role', 'id', 'role_id');
    // }    
}
