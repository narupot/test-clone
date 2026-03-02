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
use App\ExportShippingAddress;

class ExportShippingController extends MarketPlace
{
    public function __construct(){
       $this->middleware('admin.user');
    }

    public function index(Request $request)
    {

      $shipAddress = DB::table('shipping_address as sa')
    ->join('users as u', 'u.id', '=', 'sa.user_id')
    ->select(
        'sa.id',
        'u.first_name',
        'sa.title',
        'sa.address',
        'sa.province_state',
        'sa.sub_district',
        'sa.road',
        'sa.sub_district',
        'sa.city_district',
        'sa.province_state',
        'sa.zip_code',
        'sa.lat',
        'sa.long',
        'sa.ph_number',
        'sa.email',
        'sa.is_default',
        'sa.created_at',
        'sa.updated_at'
    )
    ->selectRaw("
        CONCAT(smm_u.first_name, ' ', smm_u.last_name) AS full_name,
        CASE 
            WHEN province_state = 'กรุงเทพมหานคร' THEN CONCAT('แขวง', sub_district)
            ELSE CONCAT('ต.', sub_district)
        END AS sub_district_val,
        CASE 
            WHEN province_state = 'กรุงเทพมหานคร' THEN CONCAT('เขต', city_district)
            ELSE CONCAT('อ.', city_district)
        END AS city_district_val,
        CASE 
            WHEN province_state = 'กรุงเทพมหานคร' THEN CONCAT('', province_state)
            ELSE CONCAT('จ.', province_state)
        END AS province_state_val,
        CASE 
            WHEN smm_sa.created_at != smm_sa.updated_at and date(smm_sa.updated_at) = '".date('Y-m-d')."' THEN 'อัปเดต'
            ELSE 'ใหม่'
        END AS check_update
    ")
    ->where('sa.status', '1')
    // ->where('sa.is_default', '1')
    ->whereDate('sa.updated_at', date('Y-m-d'))
    ->orderBy('check_update', 'asc')
    ->get();
     
        // สำหรับการเปิดหน้าแบบปกติ
        return view('admin.transaction.exportShippingAddress', ['data' => $shipAddress]);

    }

public function listdata(Request $request)
{
    $search_type = $request->search_type;
    $data_val = $request->data_val;

    $query = DB::table('shipping_address as sa')
        ->join('users as u', 'u.id', '=', 'sa.user_id')
        ->select(
            'sa.id',
            'sa.title',
            'sa.address',
            'sa.road',
            'sa.sub_district',
            'sa.city_district',
            'sa.province_state',
            'sa.zip_code',
            'sa.lat',
            'sa.long',
            'sa.ph_number',
            'sa.email',
            'sa.is_default',
            'sa.created_at',
            'sa.updated_at'
        )
        ->selectRaw("
            CONCAT(smm_u.first_name, ' ', smm_u.last_name) AS full_name,
            CASE 
                WHEN province_state = 'กรุงเทพมหานคร' 
                    THEN CONCAT('แขวง', sub_district)
                ELSE CONCAT('ต.', sub_district)
            END AS sub_district_val,
            CASE 
                WHEN province_state = 'กรุงเทพมหานคร' 
                    THEN CONCAT('เขต', city_district)
                ELSE CONCAT('อ.', city_district)
            END AS city_district_val,
            CASE 
                WHEN province_state = 'กรุงเทพมหานคร' 
                    THEN province_state
                ELSE CONCAT('จ.', province_state)
            END AS province_state_val,
            CASE 
                WHEN '{$search_type}' = 'buyer_name'
                    THEN 
                        CASE 
                            WHEN smm_sa.created_at != smm_sa.updated_at THEN 'อัปเดต'
                            ELSE 'ใหม่'
                        END
                ELSE 
                    CASE 
                        WHEN smm_sa.created_at != smm_sa.updated_at 
                             AND DATE(smm_sa.updated_at) = '{$data_val}'
                        THEN 'อัปเดต'
                        ELSE 'ใหม่'
                    END
            END AS check_update
        ")
        ->where('sa.status', '1');
        // ->where('sa.is_default', '1');


    // 🔍 ค้นหาตามชื่อผู้รับ
    if ($search_type === 'buyer_name' && $data_val) {
        $query->whereRaw(
            "CONCAT(smm_u.first_name, ' ', smm_u.last_name) LIKE ?",
            ["%{$data_val}%"]
        );
    }

    // 📅 ค้นหาตามวันที่
    if ($search_type === 'create_date' && $data_val) {
        $query->whereDate('sa.updated_at', $data_val);
    }

    $data = $query
        ->orderBy('check_update', 'asc')
        ->get();

    if ($request->ajax()) {
        return view('admin.transaction._tableRowsShippingAddress', compact('data'))->render();
    }

    return view('admin.transaction.exportShippingAddress', compact('data'));
}

    public function export(Request $request)
    {
        $data_val = $request->query('data_val');
        $search_type = $request->query('search_type');

        return ExportShippingAddress::exportExcel($data_val, $search_type);
    }
}