<?php

use Illuminate\Database\Seeder;

class ModulesSeeder extends Seeder
{
   /**
    * Run the module seeds.
    *
    * @return void
    */
	
	public function run()
    	{
		// Change the path of class by script | Start

		$this->call(DineshTableSeeder::class);
		
		// Change the path of class by script | end

	}
}
