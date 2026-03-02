<?php namespace App\Modules\BasicModule\Controllers\Frontend;

use App\Http\Controllers\Controller;
//use App\Modules\InvoiceModule\Models\ModelName;
/**
 * IndexController
 *
 * Controller to house all the functionality directly
 * related to the BasicModule.
 */
class HomeController extends Controller
{
	// function __construct( ModelName $ModelName )
	// {
	// 	$this->ModelName = $ModelName;
	// }

	public function index()
	{
		// BasicModule is the module name and dummy is the blade file
		// you can specify BasicModule::someFolder.file if your file exists
		// inside a folder. Also the blade will use the same syntax i.e.
		// BasicModule::viewName
		
		return view('BasicModule::frontend.home');
	}

	public function testModel()
	{
		// Added just to demonstrate that models work
		// return $this->ModelName->getAny();
	}
}
