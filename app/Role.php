<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class Role extends Model {

	protected $table = 'roles';       

	protected $fillable = ['slug', 'name'];
	protected $guarded = ['id'];

	public function getRoleAdminCount() {
		return $this->hasMany('App\AdminUser', 'role_id', 'id')->select('role_id');
	}

	public function departmentName() {
		return $this->hasOne('App\RoleDepartment', 'role_id', 'id')->select('department_name');
	}		

	public static function getAdminRole() {
		return self::where('status', '=', '1')->with('getRoleAdminCount')->get();
	}

	// public static function getAllAdminRole() {
	// 	return self::where('status', '=', '1')->get();
	// }

	public static function getRoles() {
		return self::where('status', '=', '1')->get();
	}

	public static function getAllRoles() {
		return self::all();
	}			
	
}
