<?php

namespace App\Http\Controllers\ServiceApi;

use App\Http\Controllers\MarketPlace;
use Illuminate\Http\Request;
use Config;
use Auth;
use Lang;
use DB;


class ServiceApiController extends MarketPlace
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {   

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function getBargainPopUp($id=null){
        $productdetail = Product::getProductDetailByID($id); 
        //dd($productdetail);
        if(empty($productdetail)){
            abort(404);
        }
        $productdetail->unit_name = getUnitName($productdetail->base_unit_id);
        $quantity = $productdetail->stock?'unlimited':$productdetail->quantity;
        $productdetail->total_quantity = $quantity;
        return view('productBargin',['productDetail'=>$productdetail]);
    }

}
