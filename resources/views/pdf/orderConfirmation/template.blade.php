<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ใบสรุปรายการสั่งซื้อ</title>
    <style>
        @page {
            header: page-header;
            margin: 120px 40px 40px 40px;
        }

        body {
            /* font-family: "thsarabun", sans-serif; */
            font-size: 16px;
        }

        #header {
            text-align: center;
            width: 100%;
        }

        .logo {
            float: left;
            position: absolute;
            top: 0;
            left: 0;
            width: 100px;
            margin-top: 25px;
        }

        .company-info {
            margin-left: -100px;
            height: 120px;
            padding-top: 30px;
        }

        .company-info .title {
            width: 100%;
            font-size: 24px;
            /* text-align: center; */
        }

        .section {
            page-break-inside: avoid;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            vertical-align: top
        }

        th,
        td {
            border-collapse: collapse;
            border: 1px solid #000;
        }

        .table-item th,
        .table-item td {
            padding: 5px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .bold {
            font-weight: bold !important;
        }

        .w-50-p {
            width: 50%;
        }

        .w-40-p {
            width: 40%;
        }

        .w-30-p {
            width: 30%;
        }

        .w-100-p {
            width: 100%;
        }

        .w-80-px {
            width: 80px;
        }

        .w-100-px {
            width: 100px;
        }

        .w-200-px {
            width: 200px;
        }

        .w-250-px {
            width: 250px;
        }

        .w-300-px {
            width: 300px;
        }

        .w-400-px {
            width: 400px;
        }

        .border-none {
            border: none;
        }

        .mb-5 {
            margin-bottom: 5px;
        }

        .mb-10 {
            margin-bottom: 10px;
        }

        .ml-auto {
            margin-left: auto;
        }

        .border-collapse {
            border-collapse: collapse;
        }

        .vertical-align-bottom {
            vertical-align: bottom;
        }

        .pt-20 {
            padding-top: 20px;
        }

        .border {
            border: 1px solid #000;
        }

        .border-bottom {
            border: 0px;
            border-bottom: 1px solid #000;
            !important;
        }

        .border-top {
            border: 0px;
            border-top: 1px solid #000;
            !important;
        }
    </style>
</head>

<body>
    <htmlpageheader name="page-header">
        <div id="header">
            <img src="{{ public_path('images/logo_order-comfirmation.png') }}" class="logo" alt="SMM Online Logo">
            <div class="company-info">
                <strong class="title">ใบสรุปรายการสั่งซื้อ</strong><br>
                เว็บไซต์: www.simummuangonline.com โทร: 02-023-9903 | Line Official: @smmonline
            </div>
        </div>
    </htmlpageheader>

    <main>
        <div class="section">
            <table class="border-none mb-10">
                <thead>
                    <tr>
                        <th class="border-none w-50-p text-left"><strong>ข้อมูลผู้ซื้อ</strong></th>
                        <th class="border-none w-50-p text-left"><strong>หมายเลขคำสั่งซื้อ : </strong>
                            {{$order_info['formatted_id'] ?? ''}}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border-none w-50-p">
                            <div><strong>ชื่อผู้ซื้อ : </strong>{{$user_info['name'] ?? ''}}</div>
                            <div><strong>อีเมล์ : </strong>{{$user_info['email'] ?? ''}}</div>
                            <div><strong>เบอร์โทรศัพท์ : </strong>{{$user_info['phone'] ?? ''}}</div>
                        </td>
                        <td class="border-none w-50-p">
                            <div><strong>วันที่สั่งซื้อ :
                                </strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$order_info['created_at'] ? formatThaiDateTime($order_info['created_at']) : ''}}
                            </div>
                            <div><strong>รอบการจัดส่ง :
                                </strong>&nbsp;{{$order_info['pickup_time'] ? formatThaiDateTime($order_info['pickup_time']) : ''}}
                            </div>
                            <div><strong>วิธีชำระเงิน : </strong>&nbsp;&nbsp;&nbsp; {{ $payment_name ?? '' }}</div>
                            <div><strong>วิธีการจัดส่ง :</strong>&nbsp;&nbsp;&nbsp;
                                {{ !empty($shipping_info['shipping_address']) ? 'จัดส่งตามที่อยู่' : ($shipping_info['name'] ?? '') }}
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="border-none mb-10">
                <thead>
                    <tr>
                        @if (!empty($shipping_info['billing_address']))
                            <th class="border-none w-50-p text-left"><strong>ที่อยู่ในการออกใบเสร็จ</strong></th>
                        @endif
                        @if (!empty($shipping_info['shipping_address']))
                            <th class="border-none w-50-p text-left"><strong>ที่อยู่การจัดส่ง:</strong></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @if (!empty($shipping_info['billing_address']))
                            <td class="border-none w-50-p">
                                @if (!empty($shipping_info['billing_address']['company_name']) && $shipping_info['billing_address']['company_name'] != '')
                                    @if(!empty($shipping_info['billing_address']['branch']) && $shipping_info['billing_address']['branch'] != '')
                                        <div>
                                            {{ ($shipping_info['billing_address']['company_name'] ?? '') . ' สาขา ' . ($shipping_info['billing_address']['branch'] ?? '') }}
                                        </div>
                                    @else
                                        <div>{{ ($shipping_info['billing_address']['company_name'] ?? '') }}</div>
                                    @endif
                                    {{-- show company address if not empty --}}
                                    @if (!empty($shipping_info['billing_address']['company_address']) && $shipping_info['billing_address']['company_address'] != '')
                                        <div>{{ ($shipping_info['billing_address']['company_address'] ?? '') }}</div>
                                    @endif
                                @else
                                    <div>
                                        {{ trim(($shipping_info['billing_address']['first_name'] ?? '') . ' ' . ($shipping_info['billing_address']['last_name'] ?? '')) }}
                                    </div>
                                    <div>{{ trim(($shipping_info['billing_address']['address'] ?? '')) }}</div>
                                    <div>
                                        {{ $shipping_info['billing_address']['road'] ?? '' ? $shipping_info['billing_address']['road'] . ', ' : '' }}
                                        {{ $shipping_info['billing_address']['sub_district'] ? $shipping_info['billing_address']['sub_district'] . ', ' : '' }}
                                        {{ $shipping_info['billing_address']['district'] ? $shipping_info['billing_address']['district'] . ', ' : '-' }}
                                        {{ $shipping_info['billing_address']['provice'] ? $shipping_info['billing_address']['provice'] . ', ' : '-' }}
                                        {{ $shipping_info['billing_address']['zip_code'] ?? '' }}
                                    </div>
                                    <div>{{ $shipping_info['billing_address']['ph_number'] ?? '-' }}</div>
                                @endif

                                {{-- {{ $shipping_info['billing_address']['title'] ?? '' }}<br> --}}

                                @if(!empty($shipping_info['billing_address']['tax_id']))
                                    <div>TAX ID : {{ $shipping_info['billing_address']['tax_id'] }}</div>
                                @endif
                            </td>
                        @endif
                        @if (!empty($shipping_info['shipping_address']))
                            <td class="border-none w-50-p">
                                {{-- {{ $shipping_info['shipping_address']['title'] ?? '' }}<br> --}}
                                <div>
                                    {{ trim(($shipping_info['shipping_address']['first_name'] ?? '') . ' ' . ($shipping_info['shipping_address']['last_name'] ?? '')) }}
                                </div>
                                <div>
                                    {{ $shipping_info['shipping_address']['address'] ? $shipping_info['shipping_address']['address'] . ', ' : '' }}
                                </div>
                                <div>
                                    {{ $shipping_info['shipping_address']['road'] ?? '' ? $shipping_info['shipping_address']['road'] . ', ' : '' }}
                                    {{ $shipping_info['shipping_address']['sub_district'] ? $shipping_info['shipping_address']['sub_district'] . ', ' : '' }}
                                    {{ $shipping_info['shipping_address']['district'] ? $shipping_info['shipping_address']['district'] . ', ' : '-' }}
                                    {{ $shipping_info['shipping_address']['provice'] ? $shipping_info['shipping_address']['provice'] . ', ' : '-' }}
                                    {{ $shipping_info['shipping_address']['zip_code'] ?? '' }}
                                </div>
                                <div>{{ $shipping_info['shipping_address']['ph_number'] ?? '-' }}</div>
                            </td>
                        @endif
                    </tr>
                </tbody>
            </table>
        </div>

        @foreach ($order_details ?? [] as $shop)
            <div class="section">
                <strong>{{ $shop['shop_name'] ?? '' }} ( เลขแผง : {{ $shop['panel_no'] ?? '' }} )</strong>
                <table class="table-item">
                    <thead>
                        <tr>
                            <th>ชื่อสินค้า</th>
                            <th>จำนวนในบรรจุภัณฑ์</th>
                            <th>ราคาต่อบรรจุภัณฑ์</th>
                            <th>จำนวน</th>
                            <th>ราคารวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($shop['items'] ?? [] as $item)
                            @if(!in_array($item['status'], [9, 10, 11, 12]))
                                <tr>
                                    <td class=" w-40-p"> {{ $item['product_name'] ?? '' }} ({{ $item['badge'] ?? '' }}) </td>
                                    <td class="text-center">
                                        {{ format_number($item['weight_per_unit'] ?? 0) }}
                                        {{ $item['unit_name'] ?? '' }}{{ $item['package_name'] ? ' / ' . $item['package_name'] : '' }}
                                    </td>
                                    <td class="text-center "> {{ format_number($item['last_price'] ?? 0) }} บาท /
                                        {{ $item['unit_name'] ?? '' }}
                                    </td>
                                    <td class="text-center w-80-px"> {{ $item['quantity'] ?? '' }} {{$item['package_name'] ?? ''}}
                                    </td>
                                    <td class="text-center w-80-px"> {{ number_format($item['total_price'] ?? 0, 2) }} บาท </td>
                                </tr>
                            @endif
                        @endforeach
                        <tr>
                            <td colspan="3" class="border-none"></td>
                            <td class="border"><strong>ยอดรวมร้านค้า</strong></td>
                            <td class="text-center"><strong>{{ number_format($shop['total_final_price'] ?? 0, 2) }}
                                    บาท</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        @endforeach

        <br>
        <div class="section">
            <table class="w-50-p ml-auto border-collapse">
                <thead>
                    <tr>
                        <th class="border-none"></th>
                        <th class="text-right border-none"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border-none w-200-px"><strong>ค่าสินค้ารวม</strong></td>
                        <td class="text-right border-none">{{ number_format($order_info['total_core_cost'] ?? 0, 2) }}
                            บาท</td>
                    </tr>
                    @if (!empty($order_info['dcc_purchase_discount']) && $order_info['dcc_purchase_discount'] > 0)
                        <tr>
                            <td class="border-none w-200-px">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; @lang('checkout.code_discount')
                            </td>
                            <td class="border-none text-right ">
                                -{{ number_format($order_info['dcc_purchase_discount'] ?? 0, 2) }} บาท
                            </td>
                        </tr>
                    @endif

                    @if (!empty($shipping_info['shipping_address']))
                        @if (!empty($order_info['total_shipping_cost']) && $order_info['total_shipping_cost'] > 0)
                            <tr>
                                <td class="border-top w-250-px"><strong>ค่าจัดส่ง</strong></td>
                                <td class="text-right border-top ">
                                    {{ number_format(($order_info['total_shipping_cost'] ?? 0), 2) }} บาท
                                </td>
                            </tr>
                            @if (!empty($order_info['dcc_shipping_discount']) && $order_info['dcc_shipping_discount'] > 0)
                                <tr>
                                    <td class="border-none w-250-px">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        @lang('checkout.code_discount_shipping')
                                    </td>
                                    <td class="border-none text-right ">
                                        -{{ number_format($order_info['dcc_shipping_discount'] ?? 0, 2) }} บาท
                                    </td>
                                </tr>

                            @endif
                        @endif
                    @endif

                    @if (!empty($payment_info->transactionFeeConfig) && $order_info['transaction_fee'] > 0)
                        <tr>
                            <td class="border-top w-250-px">
                                <span class="nowrap"><strong>ค่าธรรมเนียมการโอน
                                        {{$payment_info->transactionFeeConfig->name ?? ''}}</strong>
                                </span>
                                <span
                                    class="text-danger">({{ ($payment_info->transactionFeeConfig->current_tf ?? 0) . '%'}})</span>
                            </td>
                            <td class="text-right border-top ">{{ number_format(($order_info['transaction_fee'] ?? 0), 2) }}
                                บาท</td>
                        </tr>
                    @endif

                    <tr>
                        <td class="border-top w-250-px "><strong>ยอดรวมสุทธิ</strong></td>
                        <td class="text-right border-top ">
                            <strong>{{ number_format($order_info['total_final_price'] ?? 0, 2) }} บาท</strong>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>
        <br>
        <br>


        <table class="w-100-p ml-auto border-collapse">
            <tr>
                <td class="border-none w-50-p text-left vertical-align-bottom">
                    <strong>ขอขอบคุณที่ใช้บริการสี่มุมเมืองออนไลน์</strong><br>
                    และไว้วางใจเลือกซื้อสินค้าผ่านระบบของเรา<br>
                    สินค้าของเราถูกส่งตรงจากตลาดสี่มุมเมือง สด สะอาด ปลอดภัย<br>
                    หากคุณพึงพอใจในบริการ อย่าลืมรีวิวให้กำลังใจผู้ขายนะครับ<br><br>
                </td>
                <td rowspan="2" class="border-none w-50-p vertical-align-bottom text-center">
                    <img src="{{ public_path('images/ceo-signature.png') }}" class="logo mb-5" alt="Ceo Signature">
                    <div class="bold">
                        คุณ อนล ภัทรประสิทธิ์<br>
                        ผู้ช่วยกรรมการผู้จัดการสายงานสี่มุมเมืองออนไลน์
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="border-none text-left pt-20">
                    <u>หมายเหตุ / คำแนะนำ</u><br />
                    โปรดตรวจสอบสินค้าเมื่อได้รับ และแจ้งปัญหาภายใน 24 ชม.
                </td>
            </tr>
        </table>


    </main>

</body>

</html>