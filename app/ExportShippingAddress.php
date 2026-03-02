<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

class ExportShippingAddress extends Model
{
    protected $table = 'shipping_address';

    protected $fillable = [
        'title', 'first_name', 'last_name', 'address', 'road', 'sub_district', 'city_district',
        'province_state', 'zip_code', 'lat', 'long', 'ph_number', 'email',
        'status', 'is_default', 'created_at', 'updated_at'
    ];

    /**
     * ✅ ฟังก์ชัน export หลัก
     */
    public static function exportExcel($data_val, $search_type, $fileName = null)
    {
        $fileName = $fileName ?? 'shipping_address' . date('Y-m-d') . '.xlsx';

        return Excel::download(new ShippingAddressMultiSheetExport($data_val, $search_type), $fileName);
    }

}

/**
 * คลาสหลัก (หลายแท็บ)
 */
class ShippingAddressMultiSheetExport implements WithMultipleSheets
{
    protected $data_val;
    protected $search_type;

    public function __construct($data_val, $search_type)
    {
        $this->data_val = $data_val;
        $this->search_type = $search_type;
    }

    public function sheets(): array
    {
        return [
            new ShippingAddressSheet($this->data_val, $this->search_type), // 1 sheet
        ];
    }
}

/**
 * คลาสชีตเดียว (ตั้งชื่อแท็บ + จัดหัวตาราง)
 */
class ShippingAddressSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $data_val;
    protected $search_type;

    public function __construct($data_val, $search_type)
    {
        $this->data_val = $data_val;
        $this->search_type = $search_type;
    }

    public function collection()
    {   
        $query = DB::table('shipping_address as sa')
        ->join('users as s', 's.id', '=', 'sa.user_id')
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
            CONCAT(smm_s.first_name, ' ', smm_s.last_name) AS full_name_th,
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
                WHEN '{$this->search_type}' = 'buyer_name'
                    THEN 
                        CASE 
                            WHEN smm_sa.created_at != smm_sa.updated_at THEN 'อัปเดต'
                            ELSE 'ใหม่'
                        END
                ELSE 
                    CASE 
                        WHEN smm_sa.created_at != smm_sa.updated_at 
                             AND DATE(smm_sa.updated_at) = '{$this->data_val}'
                        THEN 'อัปเดต'
                        ELSE 'ใหม่'
                    END
            END AS check_update
        ")
        ->where('sa.status', '1');
        // ->where('sa.is_default', '1');

        if ($this->search_type === 'buyer_name' && !empty($this->data_val)) {
            $query->whereRaw("CONCAT(smm_s.first_name, ' ', smm_s.last_name) LIKE ?", ["%{$this->data_val}%"]);
        } elseif ($this->search_type === 'create_date' && !empty($this->data_val)) {
            $query->whereDate('sa.updated_at', $this->data_val);
        }
    
    return $query->orderBy('check_update', 'ASC')->get();
 
    }


    public function map($row): array
    {
        static $i = 1;

        $chk_address = str_replace('/', '\\/', $row->address);
        $title_sub_district = ($row->province_state == "กรุงเทพมหานคร") ? "แขวง" : "ต.";
        $title_city_district = ($row->province_state == "กรุงเทพมหานคร") ? "เขต" : "อ.";
        $title_province_state = ($row->province_state == "กรุงเทพมหานคร") ? "" : "จ.";
        $chk_sub_district = ($row->sub_district != "" && $row->sub_district != "-") ? $title_sub_district. $row->sub_district." " : "";
        $chk_city_district = ($row->city_district != "") ? $title_city_district. $row->city_district." " : "";
        $chk_province_state = ($row->province_state != "") ? $title_province_state. $row->province_state." " : "";

        $row_address = ($row->title.' ' ?? '') . ($chk_address.' ' ?? '') . ($row->road.' ' ?? '') . ($row->sub_district.', ' ?? '') . ($row->city_district.', ' ?? '') . ($row->province_state.', ' ?? '') . ($row->zip_code ?? '');
        $row_address2 = $chk_sub_district . $chk_city_district . $chk_province_state . ($row->zip_code ?? '');

        return [
            $i++,
            str_pad($row->id, 3, '0', STR_PAD_LEFT),
            $row->full_name_th ?? '', // ชื่อผู้รับสินค้า (Th)
            '', // ชื่อผู้รับสินค้า (En)
            '', // รายละเอียดผู้รับสินค้า
            $row_address, // ที่อยู่
            $row_address2, // ตำบล / อำเภอ / จังหวัด / รหัสไปรษณีย์
            $row->lat ?? '', // ละติจูด
            $row->longitude ?? '', // ลองจิจูด
            $row->ph_number ?? '', // เบอร์ติดต่อ
            $row->email ?? '', // Email
            $row->check_update ?? '', // สถานะ
            $row->updated_at ?? '', // วันที่อัปเดต
        ];
    }

    public function headings(): array
    {
        // เหลือเฉพาะหัวข้อเดียวตามที่คุณต้องการ
        return [
            ['ข้อมูลผู้รับสินค้า/ร้านค้า (Receiver/Shop)'],
            [
                'ลำดับ',
                'เลขที่ผู้รับสินค้า',
                'ชื่อผู้รับสินค้า (Th)',
                'ชื่อผู้รับสินค้า (En)',
                'รายละเอียดผู้รับสินค้า',
                'ที่อยู่',
                'ตำบล / อำเภอ / จังหวัด / รหัสไปรษณีย์',
                'ละติจูด',
                'ลองจิจูด',
                'เบอร์ติดต่อ',
                'Email',
                'สถานะ',
                'Last Update',
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // รวมเซลล์แถวหัวข้อหลัก
        $sheet->mergeCells('A1:M1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('left');

        // แถวหัวคอลัมน์
        $sheet->getStyle('A2:M2')->getFont()->setBold(true);
        $sheet->getStyle('A2:M2')->getAlignment()->setHorizontal('center');

        // ปรับความกว้างคอลัมน์อัตโนมัติ
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }

    /** ตั้งชื่อแท็บ */
    public function title(): string
    {
        return 'Shop';
    }
    
}