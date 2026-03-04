<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Product::truncate();

        Product::create([
            'name' => 'Example Product 1',
            'description' => 'รายละเอียดสินค้าตัวอย่าง 1',
            'price' => 99.99,
        ]);

        Product::create([
            'name' => 'Example Product 2',
            'description' => 'รายละเอียดสินค้าตัวอย่าง 2',
            'price' => 149.50,
        ]);
    }
}
