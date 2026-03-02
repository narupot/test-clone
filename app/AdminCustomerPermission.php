<?php

namespace App;

class AdminCustomerPermission extends User
{
    protected $table = 'admin_customer_permission';
    
    public $timestamps = false;
    
	public static function getCustomerPermission($admin_id) {
		return self::select('id', 'permission_type')->where('admin_id', $admin_id)->first();
	}     
}
