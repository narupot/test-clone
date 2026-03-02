<?php

use Illuminate\Database\Seeder;

class {MODULES_SEEDER_CLASS_NAME} extends Seeder
{
   /**
    * Run the module seeds.
    *
    * @return void
    */
	
	public function run()
    	{
		// Change the path of class by script | Start

		$this->call({SEEDER_CLASS_PATH}::class);
		
		// Change the path of class by script | End
 
	}
}
