<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the products from the database.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $products = Product::all();
        return view('products.index', compact('products'));
    }

    /**
     * Display a mockup listing of products without hitting the database.
     *
     * @return \Illuminate\View\View
     */
    public function mockup()
    {
        // hardcoded product array; cast each entry to object so blade can use -> syntax
        $products = collect([
            ['id' => 1, 'name' => 'สินค้าเทส 1', 'description' => 'คำอธิบายสินค้าเทส 1', 'price' => 120.00],
            ['id' => 2, 'name' => 'สินค้าเทส 2', 'description' => 'คำอธิบายสินค้าเทส 2', 'price' => 250.50],
            ['id' => 3, 'name' => 'สินค้าเทส 3', 'description' => 'คำอธิบายสินค้าเทส 3', 'price' => 75.75],
        ])->map(function($item) {
            return (object) $item;
        });

        return view('products.index', compact('products'));
    }
}
