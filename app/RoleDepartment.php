<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoleDepartment extends Model {

	protected $table = 'role_department';       
	
	public $timestamps = false;	

	public static function getDepartmentName($role_id) {

		if($role_id > 0) {
			return self::where(['role_id'=>$role_id, 'lang_id'=>session('default_lang')])->first()->department_name;
		}
		else {
			return 'admin';
		}
	}	
}
