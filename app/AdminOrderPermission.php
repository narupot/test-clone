<?php

namespace App;

class AdminOrderPermission extends User
{
    protected $table = 'admin_order_permission';
    
    public $timestamps = false;
    
	public static function getOrderPermission($admin_id) {
		return self::select('id', 'permission_type')->where('admin_id', $admin_id)->first();
	}    
}
