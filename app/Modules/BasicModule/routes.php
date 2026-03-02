<?php 
$siteprefix = Request::segment(1); //'en';
/*
|--------------------------------------------------------------------------
| AdminModule1 Module Routes
|--------------------------------------------------------------------------
| All the routes related to the ModuleOne module have to go in here. Make sure
| to change the namespace in case you decide to change the 
| namespace/structure of controllers.
|
*/
Route::group(array('prefix' =>$siteprefix.'/'), function () {
	// Admin Section module routes | Start
	Route::group(array('prefix' =>'admin'), function () {
		Route::group(array('prefix' =>'modules'), function () {

		// There will be Added middleware to maintain admin user Auth 
			Route::group(array('prefix'=>'BasicModule', 'namespace'=>'App\Modules\BasicModule\Controllers','middleware'=>['web']), function (){ 
				
					Route::get('/','Admin\HomeController@index');
			});
		});

	});
	// Admin Section module routes | End

	// Routes to handle front requests of payment process | Start
	Route::group(array('prefix'=>'modules', 'namespace'=>'App\Modules\BasicModule\Controllers','middleware'=>['web']), function (){ 
			Route::group(array('prefix'=>'BasicModule'), function (){
				Route::get('/','Frontend\HomeController@index');
			});	
	});
	// Routes to handle front requests of payment process | End
});

