<?php

namespace App\Http\Controllers\Admin\Transaction;

use App\Http\Controllers\MarketPlace;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use App\Helpers\CustomHelpers;
use Auth;
use App\Order;
use App\OrderDetail;
use App\OrderShop;
use App\OrderExportLog;
use App\Product;
use Lang;
use Config;
use Excel;
use App\WmsBcp;


class WmsBcpController extends MarketPlace
{
    public function __construct()
    {
        $this->middleware('admin.user');
    }

    public function index()
    {

    //     $pickup_time = DB::table('delivery_time')
    //         ->where('delivery_type', 'buyer_address')
    //         ->pluck('time_slot') // ได้ "4,6,9,13"
    //         ->map(function($time) {
    //         // แยกเป็น array
    //         $times = explode(',', $time);
    //         // แปลงแต่ละค่าเป็น HH:00
    //         return array_map(function($hour){
    //             return str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
    //         }, $times);
    //    })
    //    ->flatten() // รวมหลาย array เป็น array เดียว
    //    ->toArray();


        $pickup_time = DB::table('delivery_time')
            ->whereIn('delivery_type', ['buyer_address', 'pickup_center'])
            ->pluck('time_slot')
            ->flatMap(function ($time) {
                return array_map(function ($hour) {
                return str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                }, array_filter(explode(',', $time)));
         })
        ->unique()
        ->sort()
        ->values()
        ->toArray();

        return view('admin.transaction.WmsBcp', compact('pickup_time'));
    }

    public function export(Request $request)
    {
        $pickup_date = $request->query('pickup_date');
        $pickup_time = $request->query('pickup_time'); 
        $shipping_method = $request->query('shipping_method'); 

        if (!$pickup_date || !$pickup_time || !$shipping_method) {
            return redirect()->back()->with('error', 'กรุณาเลือกวันที่ เวลา และวิธีการจัดส่งให้ครบถ้วน');
        }

        return WmsBcp::exportExcel($pickup_date, $pickup_time, $shipping_method);
    }
}
