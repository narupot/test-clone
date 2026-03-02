<?php namespace App\Modules\BasicModule\Config;

use App\Http\Controllers\Controller;
use App;
/**
 * Settings Controller
 *
 * Controller to house all the functionality directly
 * 
 */
class Configuration
{
   /*
	* Set the base path of this module
	*/
	const MODULES = "Modules";

	/*
	* Set the base url of this module
	*/
	const MODULE_NAME = "BasicModule"; // This will change module to module

	public static function getModuleBasePath()
	{
		return base_path()."/app/".self::MODULES."/".self::MODULE_NAME;
	}

	public static function getModuleBaseUrl()
	{
		return env('APP_URL_SERVER'). App::getLocale() . '/admin/modules/BasicModule/'; // This will change module to module
	}

	
}
