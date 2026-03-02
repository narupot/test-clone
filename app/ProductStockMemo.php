<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Lang;

class ProductStockMemo extends Model {

    protected  $table = 'product_stock_memo';

    public static function updateProductStock($stock_data) {

    	extract($stock_data);

    	//echo "===>".$shop_id.'==='.$channel.'==='.$product_id.'==='.$qty.'==='.$type; die;

    	$product_qty = $import = $sold = $balance = 0;

        $product_data = \App\Product::where(['id'=>$product_id, 'shop_id'=>$shop_id])->first();

        $status = 'fail';
        $msg = Lang::get('common.error');
        if(!empty($product_data)) {

        	$update_qty = false;

            if ($type == 'import') {
                $product_qty = $product_data->quantity + $qty;
                $import = $qty;
                $balance = $product_qty;
                $update_qty = true;
            }            
            elseif($type == 'sold') {
                if($product_data->stock == '0' && $product_data->quantity >= $qty) {
                    $product_qty = $product_data->quantity - $qty;
                    $sold = $qty;
                    $balance = $product_qty;
                    $update_qty = true;
                }
                else {
                    $msg = Lang::get('stock_memo.you_can_deduct_max').': '.$product_data->quantity.' '.Lang::get('product.quantity');
                }
            }
            
            if($update_qty === true) {
                // $product_data->quantity = $product_qty;
                // $product_data->save();
                \App\MongoProduct::updatePrdQunatity($product_id, $product_qty);

                $stock_memo = new \App\ProductStockMemo;
                $stock_memo->product_id = $product_id;
                $stock_memo->import = $import;
                $stock_memo->sold = $sold;
                $stock_memo->balance = $balance;
                $stock_memo->channel = $channel;
                $stock_memo->save();

                $status = 'success';
                $msg = Lang::get('common.records_updated_successfully');
            }
        }

        return json_encode(['status'=>$status, 'message'=>$msg]);
    }
}
