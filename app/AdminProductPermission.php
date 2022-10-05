<?php

namespace App;

class AdminProductPermission extends User
{
    protected $table = 'admin_product_permission';
    
    public $timestamps = false;
    
	public static function getProductPermission($admin_id) {
		return self::select('id', 'permission_type')->where('admin_id', $admin_id)->first();
	} 


}
