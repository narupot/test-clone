<?php

namespace App;

class AdminUser extends User
{
    protected $table = 'admin_users';
    
    public function role(){
        return $this->hasOne('App\Role', 'id', 'role_id');
    }

    public function getAdminDepartment() {
        return $this->hasOne('App\RoleDepartment','role_id','role_id')->where('lang_id', session('default_lang'));
    }

    public static function getAdminDetail($admin_id) {
    	return self::where('id', '=', $admin_id)->with('getAdminDepartment')->first();
    }

	public static function getCheckoutAdminsDetail() {
		return self::where(['status'=>'1', 'show_on_checkout'=>'1'])->get();
	}
}
