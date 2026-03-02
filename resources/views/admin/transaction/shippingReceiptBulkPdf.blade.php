@php use Illuminate\Support\Arr; 
    use App\Helpers\ThaiBahtTextHelper;
    
    use Carbon\Carbon;
    $currentDate = Carbon::now()->format('d/m/Y');

@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ใบเสร็จค่าขนส่ง ({{ $deliveryDate }})</title>
    <style>
        @page {
            margin: 5mm;
            @top-right {
                content: "Page " counter(page) " of " counter(pages);
            }
        }

        .page-header {
            position: fixed;
            top: -20px;
            right: 0;
            text-align: right;
            font-size: 12px;
        }

        body { 
            /* font-family: "thsarabun", DejaVu Sans, sans-serif;  */
            font-size: 24px; }
        .header { text-align: center; margin-bottom: 4px; }
        .page-header { text-align: right; font-size: 10px; margin-bottom: 1px; }
        .company { font-weight: bold; font-size: 16px; }
        .taxid, .address { font-size: 14px; }
        .title { font-size: 16px; font-weight: bold; margin: 1px 0 0 0; }
        .section { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th, td { border: 1px solid #333; padding: 2px 4px; }
        th { background: #e0e0e0; }
        .right { text-align: right; }
        .center { text-align: center; }
        .page-break { page-break-after: always; }
        .remark { font-size: 14px; margin-top: 20px; }
         table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }
            th, td {
                border: 1px solid #333;
                padding: 2px 4px; /* ลดจาก 4px 6px */
                line-height: 1.01;  /* ลดระยะห่างระหว่างบรรทัด */
                font-size: 14px;   /* ลดขนาดตัวอักษรลงเล็กน้อย */
            }
            th {
                background: #e0e0e0;
            }

    </style>
</head>
<body>
@foreach($orders as $order)
    @php
        $shipping = $order->total_shipping_cost ?? 0;
        $discount = $order->dcc_shipping_discount ?? 0;
        $net = $shipping - $discount + ($order->transaction_fee ?? 0);
        
        // ใช้ข้อมูลที่ได้ถูก set แล้วใน Controller แทนการ parse JSON ใหม่
        $shippingAddress = implode(' ', array_filter([
            $order->shipping_address ?? '',
            $order->shipping_road ?? '',
            $order->shipping_sub_district ?? '',
            $order->shipping_district ?? '',
            $order->shipping_province ?? '',
            $order->shipping_zip_code ?? ''
        ])) ?: '-';
        
        $phone = $order->shipping_phone ?? '-';
        $numberOfItems = 1; // เนื่องจากใบเสร็จค่าขนส่งมีแค่รายการเดียว
    @endphp
    <div class="page-header">
        
    </div>
    <div class="header">
        <div class="company">บริษัท ดอนเมืองพัฒนา จำกัด (สำนักงานใหญ่)</div>
        <div class="taxid">เลขประจำตัวผู้เสียภาษีอากร / Tax Identification No. 0105522010630</div>
        <div class="address">355/115-116 หมู่ 15 ถนนพหลโยธิน ตำบลคูคต อำเภอลำลูกกา จังหวัดปทุมธานี 12130</div>
        <div class="address">Donmuang Pattana Co.,Ltd. 335/115-116 Moo 15, Phaholyothin Rd. Kookot, Lamlukka, PathumThani, 12130</div>
        <div class="title">ใบเสร็จรับเงิน / ใบกำกับภาษี<br>Receipt / Tax Invoice</div>
    </div>

    
        <table style="width: 100%; border: none; border-collapse: collapse; margin-bottom: 0px;">
            <tr>
                <td style="text-align: left; border: none;">
                     @php
                        // ตรวจสอบข้อมูลจาก properties ที่ set ใน Controller ก่อน
                        $receivedFrom = '';
                        if (!empty($order->billing_company_name)) {
                            $receivedFrom = $order->billing_company_name;
                        } else {
                            $receivedFrom = trim(($order->billing_first_name ?? '') . ' ' . ($order->billing_last_name ?? ''));
                        }
                        
                        // ถ้าไม่มีข้อมูลจาก Controller ให้ลองดึงจาก JSON โดยตรง
                        if (empty($receivedFrom) && !empty($order->order_json)) {
                            $orderJson = json_decode($order->order_json, true);
                            if (isset($orderJson['billing_address'])) {
                                $billing = $orderJson['billing_address'];
                                if (!empty($billing['company_name'])) {
                                    $receivedFrom = $billing['company_name'];
                                } else {
                                    $receivedFrom = trim(($billing['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? ''));
                                }
                            }
                        }
                        
                        // ถ้าไม่มีข้อมูล billing ให้ลองใช้ shipping
                        if (empty($receivedFrom) && !empty($order->order_json)) {
                            $orderJson = json_decode($order->order_json, true);
                            if (isset($orderJson['shipping_address'])) {
                                $shipping = $orderJson['shipping_address'];
                                if (!empty($shipping['company_name'])) {
                                    $receivedFrom = $shipping['company_name'];
                                } else {
                                    $receivedFrom = trim(($shipping['first_name'] ?? '') . ' ' . ($shipping['last_name'] ?? ''));
                                }
                            }
                        }
                    @endphp
                    <b>ลูกค้า / Customer:</b> {{ $receivedFrom ?: 'ไม่ระบุ' }}
                </td>
                <!-- <td style="text-align: left; border: none;"><b>วันที่:</b>{{ $currentDate }}</td> -->
                 @php
                    // ใช้วันที่จาก end_shopping_date ถ้ามี ไม่งั้นใช้วันที่ปัจจุบัน
                    $orderDate = !empty($order->end_shopping_date) ? Carbon::parse($order->end_shopping_date)->format('d/m/Y') : $currentDate;  
                @endphp
                 <td style="text-align: left; border: none;"><b>วันที่:</b>{{ $orderDate }}</td>
                
            </tr>
            
            <!-- <tr><td colspan="2" style="text-align: left; border: none;"><b>ลูกค้า / Customer:</b> {{ $order->user_name ?? '-' }}</td></tr> -->
            @php
                if (!empty($order->billing_company_address)) {
                    $billingAddress = implode(' ', array_filter([
                        $order->billing_company_address,
                        $order->branch,
                        !empty($order->billing_tax_id) ? 'Tax ID: ' . $order->billing_tax_id : null
                    ]));
                } else {
                    $billingAddress = implode(' ', array_filter([
                        $order->billing_address,
                        $order->billing_road,
                        $order->billing_sub_district,
                        $order->billing_district,
                        $order->billing_province,
                        $order->billing_zip_code
                    ]));
                }
                
            @endphp
            <tr> 
                <td style="text-align: left; border: none;">
                    <b>ที่อยู่ / Address:</b> {{ $billingAddress ?: '-' }}
                </td>
                <td style="text-align: left; border: none;"><b>เลขที่ใบสั่งซื้อ / Order No.:</b> {{ $order->formatted_id ?? $order->id }}</td>
                
            </tr>
            <tr><td style="text-align: left; border: none;"><b>หมายเลขโทรศัพท์ / Tel No.:</b> {{ $order->billing_phone }}</td>
            <td style="text-align: left; border: none;">
                    <b>เลขที่ใบเสร็จรับเงิน / Receipt No.:</b> {{ $order->shipping_rept_no ?? '-' }}
                </td>
        </tr>
            
        </table>
    

    <table>
        <thead>
            <tr>
                <th class="center" style="width: 30%;">ลำดับที่ / No.</th>
                <th class="center" style="width: 30%;">รายการ / Description</th>
                <th colspan="2" class="center" style="width: 40%;">จำนวนเงิน / Amount</th>
            </tr>
        </thead>
        <tbody>
            @if($order->shipping_method == 3)
            <tr>
                <td class="center">{{$numberOfItems}}</td>
                <td class="left">ค่าขนส่ง</td>
                <td colspan="2" class="right">{{ number_format($order->total_shipping_cost, 2) }}</td>
            </tr>
                @php $numberOfItems++; @endphp
            @endif
            {{-- แทรกค่าธรรมเนียมการโอนถ้ามีค่า --}}
            @if(!empty($order->transaction_fee) && $order->transaction_fee > 0)
            <tr>
                <td class="center">{{$numberOfItems}}</td>
                <td class="left">ค่าธรรมเนียมการโอน</td>
                <td colspan="2" class="right">{{ number_format($order->transaction_fee, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td class="right">หมายเหตุ : Remarks</td>
                <td class="right"></td>
                <td class="right">ส่วนลดค่าขนส่ง : Discount</td>
                <td class="right">{{ number_format($order->dcc_shipping_discount, 2) }}</td>
            </tr>
            <tr>
                <td class="right" colspan="1">เงื่อนไขการชำระเงิน : Payment Conditions</td>
                <td></td>
                <td class="right"  style="width: 31%;">จำนวนเงินรวม : Sub-total</td>
                <td class="right">{{ number_format($net, 2) }}</td>
            </tr>   
            <tr>
                <td class="right" colspan="1"></td>
                <td></td>
                <td class="right"  >สินค้า/บริการไม่เสียภาษีมูลค่าเพิ่ม : Non VAT Sales</td>
                @php
                    $nonVatSales = $order->total_shipping_cost - $order->dcc_shipping_discount;
                @endphp
                <td class="right">{{ number_format($nonVatSales, 2) }}</td>
            </tr>
            <tr>
                <td class="right"></td>
                <td class="right"></td>
                <td class="right">สินค้า/บริการไม่เสียภาษีมูลค่าเพิ่ม : VAT Sales</td>
                @php
                    if(!empty($order->transaction_fee) && $order->transaction_fee > 0){
                        $vatSales = $order->transaction_fee * 100 / 107; // กรณีมีค่าธรรมเนียมการโอน
                        }
                    else{
                        $vatSales = 0; // เนื่องจากค่าขนส่งไม่เสีย VAT
                    }
                @endphp
                <td class="right">{{number_format($vatSales,2)}}</td>
            </tr>
            <tr>
                <td class="right"></td>
                <td class="right"></td>
                <td class="right">มูลค่าเพิ่ม 7% : VAT 7%</td>
                @php
                    if(!empty($order->transaction_fee) && $order->transaction_fee > 0){
                        $vat7 = $order->transaction_fee -$vatSales; // กรณีมีค่าธรรมเนียมการโอน
                        }
                    else{
                        $vat7 = 0; // เนื่องจากค่าขนส่งไม่เสีย VAT
                    }
                @endphp
                <td class="right">{{number_format($vat7,2)}}</td>
            </tr>
            <tr>
                <td class="right">บาท : Baht</td>
                <td class="center">{{ ThaiBahtTextHelper::convert($net) }}</span></td>
                <td class="right">จำนวนเงินทั้งสิ้น :</td>
                <td class="right"><b>{{ number_format($net, 2) }}</b></td>
            </tr>
            <tr>
                <td colspan ="2" class="left"><div class="remark">
        <b>ระเบียบการเปลี่ยนสินค้าหรือขอคืนสินค้า:</b><br>
            1. กรณีสินค้ามีปัญหาหลังจากรับสินค้แล้ว ผู้ซื้อสามารถแจ้งต่อผู้ขายได้ภายใน 24 ชั่วโมง
               เพื่อให้ทางผู้ขายทำการเปลี่ยนสินค้าหรือคืนเงินภายใน 7-14 วันทำการ<br>
            2. หากผู้ซื้อต้องการคืนสินค้าหรือเปลี่ยนสินค้า ต้องทำการคืนหรือเปลี่ยนสินค้าตามบรรจุภัณฑ์ที่ระบุ
               ในใบส่งสินค้าหรือหน้าเว็บไซต์ ณ ขณะหลังซื้อเท่านั้น<br>
        หากมีข้อสงสัยหรือต้องการสอบถามข้อมูลเพิ่มเติม สามารถติดต่อสอบถามได้ที่ฝ่ายลูกค้าสัมพันธ์ในเวลาทำการ<br>
        8.00 -17.00 น. เบอร์โทร 02 023 9903 หรือ Line ID : @smmonline
    </div></td>
                <td colspan = "2" class="center">
                    <img src="{{ public_path('images/whmanager-signature.png') }}" alt="Authorized Signature" style="height: 55px;"><br>
                    ผู้รับเงิน / Collector<br>
                </td>
                
            </tr>
        </tbody>
    </table>   

    @if (!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach



</body>
</html>
