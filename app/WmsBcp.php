<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WmsBcp extends Model
{
    /**
     * Export Excel (แท็บเดียว)
     */
    public static function exportExcel($pickup_date, $pickup_time, $shipping_method, $fileName = null)
    {
        $fileName = $fileName ?? 'wms_bcp_' . $pickup_date .'_'. $pickup_time . '.xlsx';
        return Excel::download(new WmsBcpSheet($pickup_date,$pickup_time,$shipping_method), $fileName);
    }
}

class WmsBcpSheet implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $pickup_date;
    protected $pickup_time;
    protected $shipping_method;

    public function __construct($pickup_date, $pickup_time, $shipping_method)
    {
        $this->pickup_date = $pickup_date;
        $this->pickup_time = $pickup_time;
        $this->shipping_method = $shipping_method;
    }

    public function collection()
    {
        $pickupDateTime = $this->pickup_date . ' ' . $this->pickup_time . ':00.000';
        $shipping_method = $this->shipping_method;

        $sql = "
            SELECT 
                o.formatted_id AS order_no,
                o.user_name AS buyer_name,
                CASE
                    WHEN o.shipping_method = 3 THEN 
                        SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(o.order_json, '\"title\":\"', -1), '\"', 1), '\"', 1)
                    ELSE 'มารับสินค้าที่ศูนย์กระจายสินค้า ตลาดสี่มุมเมือง'
                END AS shipping_title,  
                CASE
                    WHEN o.shipping_method = 3 THEN 
                        SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(o.order_json, '\"shipping_address_id\":', -1),',', 1),'}', 1)
                    ELSE ''
                END AS shipping_address_id,
                CASE
                    WHEN o.shipping_method = 3 THEN 
                        CONCAT(
                    REPLACE(TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(o.order_json, '\"address\":\"', -1), '\"', 1), '\"', 1)), '\\/', '/'), ' ',
                    TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(o.order_json, '\"road\":\"', -1), '\"', 1), '\"', 1)), ' ',
                    TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(o.order_json, '\"sub_district\":\"', -1), '\"', 1), '\"', 1)), ', ',
                    TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(o.order_json, '\"district\":\"', -1), '\"', 1), '\"', 1)), ', ',
                    TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(o.order_json, '\"provice\":\"', -1), '\"', 1), '\"', 1)), ', ',
                    SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(o.order_json, '\"zip_code\":', -1), ',', 1), '}', 1)
                    )
                    ELSE SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(o.order_json, '\"location\":\"', -1), '\"', 1), '\"', 1)
                END AS shipping_full_address,
                od.sku AS product_code,
                od.category_name,
                SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(od.order_detail_json, '\"grade\":\"', -1), '(', -1), ')', 1) AS grade_th,
                LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(od.order_detail_json, '\"grade\":\"', -1), '\"', 1), '\"', 1), 1) AS grade_en,
                SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(od.order_detail_json, '\"size\":\"', -1), '(', -1), ')', 1) AS size_th,
                LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(od.order_detail_json, '\"size\":\"', -1), '\"', 1), '\"', 1), 2) AS size_en,
                SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(od.order_detail_json, '\"title\":\"', -1), '\"', 1), '\"', 1) AS grade_size_th,
                CONCAT(
                    LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(od.order_detail_json, '\"grade\":\"', -1), '\"', 1), '\"', 1), 1), ' ',
                    LEFT(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(od.order_detail_json, '\"size\":\"', -1), '\"', 1), '\"', 1), 2)
                ) AS grade_size_en,
                o.pickup_time AS delivery_date,
                DATE_ADD(o.pickup_time, INTERVAL 3 HOUR) AS delivery_finish_date,
                od.quantity,
                od.package_name,
                CONCAT(
                    TRIM(TRAILING '.' FROM TRIM(TRAILING '0' FROM od.total_weight)),
                    ' ', od.base_unit, ' / ', od.package_name
                ) AS amount_text,
                od.total_weight * od.quantity AS total_weight,
                (SELECT SUM(total_weight * quantity) FROM smm_order_detail WHERE order_id = o.id) AS total_weight_in_kg_per_main_order,
                'SMM' AS sender,
                REPLACE(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(o.order_json, '\"ph_number\":', -1), ',', 1), '}', 1),'\"','') AS shipping_ph_no,
                od.shop_id,
                sd.shop_name,
                CASE WHEN o.shipping_method = '1' THEN 'มารับที่ศูนย์' WHEN o.shipping_method = '3' THEN 'จัดส่งตามที่อยู่' END AS delivery_type,
                od.original_price AS unit_price,
                od.total_price AS total_product_price,
                o.total_shipping_cost - o.shipping_discount - o.dcc_shipping_discount AS shipping_cost,
                CONCAT(
                    TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(o.order_json, '\"billing_address\":{', -1), '\"address\":\"', -1), '\"', 1)), ' ',
                    TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(o.order_json, '\"billing_address\":{', -1), '\"road\":\"', -1), '\"', 1)), ' ',
                    TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(o.order_json, '\"billing_address\":{', -1), '\"sub_district\":\"', -1), '\"', 1)), ', ',
                    TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(o.order_json, '\"billing_address\":{', -1), '\"district\":\"', -1), '\"', 1)), ', ',
                    TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(o.order_json, '\"billing_address\":{', -1), '\"provice\":\"', -1), '\"', 1)), ', ',
                    SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(o.order_json, '\"billing_address\":{', -1), '\"zip_code\":', -1), ',', 1)
                ) AS billing_full_address,
                s.ph_number AS shop_ph_no
            FROM smm_order o
            JOIN smm_order_detail od ON o.id = od.order_id
            JOIN smm_shop_desc sd ON od.shop_id = sd.shop_id
            JOIN smm_shop s ON od.shop_id = s.id
            WHERE o.order_status = 2
            AND o.pickup_time = ?
            AND o.shipping_method = ?
        ";

        return collect(DB::select($sql, [$pickupDateTime, $shipping_method]));
    }

    public function map($row): array
    {
        
        return [
            $row->order_no,
            $row->buyer_name,
            $row->shipping_address_id,
            str_replace('\/', '/', $row->shipping_title),
            str_replace('\r\n','',str_replace('\/', '/', $row->shipping_full_address)),
            $row->product_code,
            $row->category_name,
            $row->grade_th,
            $row->grade_en,
            $row->size_th,
            $row->size_en,
            $row->grade_size_th,
            $row->grade_size_en,
            $row->delivery_date,
            $row->delivery_finish_date,
            $row->quantity,
            $row->package_name,
            $row->amount_text,
            $row->total_weight,
            $row->total_weight_in_kg_per_main_order,
            $row->sender,
            $row->shipping_ph_no,
            $row->shop_id,
            $row->shop_name,
            $row->delivery_type,
            $row->unit_price,
            $row->total_product_price,
            $row->shipping_cost,
            str_replace('\/', '/', $row->billing_full_address),
            $row->shop_ph_no,
            $row->quantity
        ];
    }

    public function headings(): array
    {
        return [
            'order_no','buyer_name','shipping_address_id','shipping_title','shipping_address','product code (SKU)',
            'product_name','grade_th','grade_en','size_th','size_en','grade_size_th_text','grade_size_en_text',
            'delivery_date','deliver_finish_date','quantity','package_name','amount_text','total_weight',
            'total_weight_in_kg_per_main_order','ผู้ส่ง','shipping_ph_no','shop_id','shop_name','Delivery Type',
            'ราคาต่อหน่วยของสินค้า','ราคารวมของสินค้า','ราคาค่าขนส่งสินค้าภายใน order เดียวกัน','ที่อยู่ในการออกใบเสร็จรับเงิน','shop_ph_number','QR'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:AE1')->getFont()->setBold(true);
        $sheet->getStyle('A1:AE1')->getAlignment()->setHorizontal('center');

        foreach (range('A', 'AE') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }

}
