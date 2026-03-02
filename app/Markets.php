<?php
namespace App;

use Illuminate\Database\Eloquent\Model;


class Markets extends Model
{
    protected $table = "markets";
    public static function getAllMarkets(){
    	return self::select('*')->get();
    }
    
    protected $fillable = ['market_name','market_code', 'percent_markup_product_price', 'effective_date', 'updated_by','updated_by','status'];
    
    public $timestamps = false;

    const UPDATED_AT = 'updated_date';
}