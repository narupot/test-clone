<html>
<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->
    <meta http-equiv="Content-Type" content="800; charset=utf-8" />
    <title>Order Pdf</title>

<style>
    @font-face {
	    / font-family: "THSarabun"; /
        font-family: 'examplefont', sans-serif;
	    font-style: normal;
	    font-weight: normal;
	    / src: url("{{ asset('pdf_fonts/THSarabun.ttf')}}") format("truetype"); /
    }  
    @font-face {
	    / font-family: "THSarabunbold"; /
        font-family: 'examplefont', sans-serif;
	    font-style: normal;
	    font-weight: bolder;
	    / src: url("{{ asset('pdf_fonts/THSarabun Bold.ttf')}}") format("truetype"); /
    }      
    body {
        / font-family: "THSarabun"; /
        font-family: 'examplefont', sans-serif;
        font-size: 16px; color: #343A40; 
        background: #fff !important; line-height: 16px;
        letter-spacing: 0.38px;
        padding-bottom:1.62cm; 
    }
    html { -webkit-print-color-adjust: exact; }
    table {
        border-collapse: collapse;
        border: 0; 
    }
    th,
    td {
        border-collapse: collapse;
        padding: 3px 8px;
        / border: 1px solid #CED4DA; /
        border:none; font-size: 20px;
    }
    th {
        font-weight: bold;
    }
    td {
        vertical-align: top;
    }
    img {
        line-height: 1; max-width: 100%;
        vertical-align: top;
    }
    a {
        text-decoration: none; color: #000;
    }
    .data-tables td, .data-tables th {
        padding-top: 15px; padding-bottom: 15px;
        text-align: center; color: #000;
    }
    .data-tables td {
        border-bottom: 1px solid #9E9E9E;        
    }
    .border-0 {
        border: 0;
    }
    .border-0 td,
    .border-0 th {
        border: 0
    }
    .red {
        color: #F00;
    }

    /  /
    @media  print {    
        body, html {
            margin:0;
        }
        .red, .dont-forget { background: #DC3545; }  
        .voucher-page {page-break-after: always; margin-top: 30px;}
    }
    @page {
        header: page-header;
        footer: page-footer;
    }
</style>

<body style="font-family: examplefont, sans-serif; -webkit-font-smoothing: antialiased; line-height: 1.3; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;  margin: 0; padding: 0;">
    @foreach($total_order as $key => $main_order)
    
    <?php 
        $order_shop = \App\OrderShop::where('order_id',$main_order->id)->with(['getOrderStatus'])->get();
        if(count($order_shop)){
            foreach ($order_shop as $key => $value) {
                $order_detail = \App\OrderDetail::getShopOrderDetail('',$value->id);
                $order_shop[$key]->details = $order_detail;
            }
        }
        $main_order->tot_shop = count($order_shop);
        
        //dd($order_shop);
        $transaction = \App\OrderTransaction::where('order_id',$main_order->id)->orderBy('id')->get();
        
        $main_order->pickup_time = null;
        if($main_order->id>0)
        {
            $order_info = \App\Order::where('id',$main_order->id)->first();
            if($order_info)
            {
                $main_order->pickup_time=$order_info->pickup_time;
            }
        }
        if(count($order_shop))
        {
            foreach($order_shop as $skey => $shop_ord_val)
                {
                    foreach($shop_ord_val->details as $key => $val)
                    {
                        if($val->description=='' || $val->description==null)
                        {
                            $productDetail = \App\Product::getProductDetail($val->sku);
                            $order_shop[$skey]->details[$key]->description=isset($productDetail->productDesc)?$productDetail->productDesc->description:"";
                        }
                    }
                }
        }
    ?>
    <div class="container">

        <div style="padding:15px 10px 10px 10px;box-shadow: 0px 3px 9px 0px #ccc;">
            <span style="color: #F00;">Main Order ID:</span> {{$main_order->formatted_id}}
        </div>
        
        <table border="0" cellpadding="0" cellspacing="0" align="center" style="font-family: examplefont, sans-serif; width: 1000px; color:#000; line-height:1.3;">
            <!-- First row starts -->
                
            <tr><td style="height:40px;"></td></tr> 
            <!-- Second row starts -->
            <tr>
                <td>
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td style="text-align: center; border-right: 1px solid #9E9E9E; width: 50%; max-width: 50%; padding: 30px 5px; font-size:34px;">
                                <div style="margin-bottom: 12px;">@lang('admin_order.main_order_status') :</div>
                                {{ $main_order->getOrderStatus->status??'' }}
                            </td>
                            <td style="text-align: center; padding: 30px 5px; font-size:34px; width: 50%; max-width: 50%;">
                                <div style="margin-bottom: 12px;">&nbsp;&nbsp;&nbsp; ยอดรวม &nbsp;&nbsp;&nbsp;</div>
                                <span style="font-size: 44px;">@lang('common.thb') {{ numberFormat($main_order->total_final_price) }}</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
         
            <tr><td style="height:40px;"></td></tr> 
            <!-- Third row starts -->
            <tr>
                <td style="border:1px solid #9E9E9E; padding:12px 10px;">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td colspan="4" style="font-size:28px;">@lang('admin_order.buyer_information')</td>
                        </tr>
                        <tr><td style="height:22px;"></td></tr> 
                        <tr>
                            <td style="width:25%;">
                                ชื่อ : {{$main_order->user_name}} <br>
                                อีเมล : {{$main_order->user_email}} <br>
                                เบอร์โทรศัพท์ : {{$main_order->ph_number}}
                            </td>
                            @if($main_order->shipping_method == 1)
                                <td style="width:25%;">
                                    <div style="margin-bottom:15px;">ที่อยู่ในการจัดส่ง :</div>
                                    {!! CustomHelpers::centerAddress($main_order->order_json) !!}
                                    <!-- <div style="margin-bottom:15px;">name</div>
                                    <div style="margin-bottom:15px;">123 <br> 
                                        location <br> 12002</div>
                                    <div style="margin-bottom:15px;">0990990999</div> -->

                                </td>
                            @elseif($main_order->shipping_method == 2)
                                <td style="width:25%;">
                                    <div style="margin-bottom:15px;">ที่อยู่ในการออกใบเสร็จ :</div>
                                    {!! CustomHelpers::storeAddress($main_order->order_json) !!}
                                    <!-- <div style="margin-bottom:15px;">name</div>
                                    <div style="margin-bottom:15px;">123 <br> 
                                        location <br> 12002</div>
                                    <div style="margin-bottom:15px;">0990990999</div> -->

                                </td>
                            @else
                                <td style="width:25%;">
                                    <div style="margin-bottom:15px;">ที่อยู่ในการจัดส่ง : </div>
                                    {{ CustomHelpers::buyerShipBillTo($main_order->order_json,'shipping_address') }}
                                </td>
                                <td style="width:25%;">
                                    <div style="margin-bottom:15px;">ที่อยู่ในการออกใบเสร็จ : </div>
                                    {{ CustomHelpers::buyerShipBillTo($main_order->order_json,'billing_address') }}
                                </td>

                            @endif
                            <td style="width:25%;">
                                <div style="margin-bottom:15px;">Shipping : <br> {{ GeneralFunctions::getShippingMethod($main_order->shipping_method) }}</div>
                                <div style="margin-bottom:15px;">Pickup Date : <br>{{$main_order->pickup_time}}</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
 
            <tr><td style="height:30px;"></td></tr> 
            <tr>
                <td style="border-bottom:none; padding:0;" align="center">
                    <table border="0" cellpadding="0" cellspacing="0" width="53%" align="center;" class="total-table" style="border:1px solid #9E9E9E;">
                        <tr>
                            <td style="padding:12px; border-bottom: 1px solid #9E9E9E;text-align:left;">@lang('checkout.total_seller')</td>
                            <td style="text-align:right; padding:12px;border-bottom: 1px solid #9E9E9E;">{{ $main_order->tot_shop }}</td>
                        </tr>
                        <tr>
                            <td style="padding:12px;border-bottom: 1px solid #9E9E9E;text-align:left;">@lang('checkout.total')</td>
                            <td style="text-align:right; padding:12px;border-bottom: 1px solid #9E9E9E;">{{numberFormat($main_order->total_core_cost)}} @lang('common.baht')</td>
                        </tr>
                        <tr>
                            <td style="padding:12px;border-bottom: 1px solid #9E9E9E;text-align:left;">@lang('checkout.shipping_charge')</td>
                            <td style="text-align:right; padding:12px;border-bottom: 1px solid #9E9E9E;">{{numberFormat($main_order->total_shipping_cost)}} @lang('common.baht')</td>
                        </tr>
                        <tr>
                            <td style="padding:12px;border-bottom: 1px solid #9E9E9E;text-align:left;">@lang('checkout.grand_total')</td>
                            <td style="text-align:right; padding:12px;border-bottom: 1px solid #9E9E9E;">{{numberFormat($main_order->total_final_price)}} @lang('common.baht')</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <!-- Forth row starts -->
            @if(count($order_shop))
                @foreach($order_shop as $skey => $shop_ord_val)
                    <tr><td style="height:30px;"></td></tr> 
                    <tr>
                        <td style="padding-left: 0; padding-right: 0px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="width:50%; font-size: 28px; padding-left:0px;">@lang('admin_order.shop_order_id') : {{ $shop_ord_val->shop_formatted_id }}</td>
                                    <td style="text-align:right; width:50%; font-size: 28px; padding-right:0px;color:#F00;">@lang('admin_order.shop_order_status') : {{$shop_ord_val->getOrderStatus->status}}</td>
                                </tr>
                                <tr><td style="height:5px;"></td></tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="border:1px solid #9E9E9E; border-bottom:none; padding:0;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" class="data-tables">
                                <tr>
                                    <th style="text-align:center; font-weight: normal;">สินค้า</th>
                                    <th style="text-align:left; font-weight: normal;">ร้านค้า</th>
                                    <th style="text-align:center; font-weight: normal;">ราคา <br> ต่อหน่วย</th>
                                    <th style="text-align:center; font-weight: normal;">จำนวน</th>
                                    <th style="text-align:center; font-weight: normal;">ราคารวม <br></th>
                                    <th style="text-align:center; font-weight: normal;">ช่องทางการ <br>ชำระเงิน</th>
                                    <th style="text-align:center; font-weight: normal;">สถานะ </th>
                                    <th style="text-align:center; font-weight: normal;">Remark</th>
                                    <th style="text-align:center; font-weight: normal;">รายละเอียด <br>สินค้า</th>
                                </tr>
                                @foreach($shop_ord_val->details as $key => $val)
                                    @php 
                                        $detail_json = jsonDecodeArr($val->order_detail_json);
                                        $shop_url = action('ShopController@index',$detail_json['shop_url'] ??'');
                                        $prd_url = action('ProductDetailController@display',[$detail_json['cat_url']??'',$val->sku]);
                                    @endphp
                                    <tr>
                                        <td style="text-align:left;">
                                            <div style="margin-bottom:4px;"><img src="{{ getProductImageUrlRunTime($detail_json['thumbnail_image']??'','thumb') }}" alt="img" width="50"> </div>
                                            <div style="margin-bottom: 2px;">{{ $detail_json['name'][session('default_lang')]??$val->category_name }}</div>
                                            <!-- <div>
                                                <span style="border:1px solid green; font-size: 8px;
                                                padding:2px; display: inline-block; border-radius: 50%; width:20px; height:20px; line-height: 20px; text-align: center;">XLA</span>
                                                จัมโบ้ | สวย
                                            </div> -->
                                        </td>
                                        <td style="text-align:left;">
                                            <div style="margin-bottom:4px;"><img src="{{getImgUrl($detail_json['logo'] ??'','logo')}}" alt="img" width="50"> </div>
                                            {{ $detail_json['shop_name'][session('default_lang')]??'' }}
                                        </td>
                                        <td>{{numberFormat($val->last_price) }} @lang('common.baht') /{{ $detail_json['package'][session('default_lang')] ?? $val->package_name }}</td>
                                        <td>{{ $val->quantity }} {{ $detail_json['package'][session('default_lang')] ?? $val->package_name }}
                                            <br> <span style="color:#F00;">{{convertString($val->total_weight) }} {{$val->base_unit}}<br> / {{$val->package_name}}</span></td>
                                        <td>{{numberFormat($val->total_price) }} @lang('common.baht')</td>
                                        <td>{!!$detail_json['payment_method'][session('default_lang')] ?? str_replace('_',' ',strtoupper($val->payment_slug)) !!}</td>
                                        <td><span style="color:#F00;">{{ $val->getOrderStatus->status??'' }}</span></td>
                                        <td>{{$val->api_remark}}</td>
                                        @php 
                                            $str_description = $val->description;
                                            $str_description = strip_tags($str_description);
                                            $str_description = mb_substr($str_description, 0, 30);
                                        @endphp
                                        <td>{!!$str_description!!}</td>
                                    </tr>
                                @endforeach 
                            </table>
                        </td>
                    </tr>
                    <tr><td style="height:40px;"></td></tr> 
                    <!-- Table footer starts -->
                    <tr>
                        <td style="padding-left:0px; padding-right:0px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="width:50%">checkout.shop_remark <br>
                                    {{$shop_ord_val->api_remark}}</td>
                                    <td style="width:50%; border:1px solid #9E9E9E; padding:0; border-bottom:none;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td style="padding: 10px; border-bottom:1px solid #9E9E9E;">@lang('checkout.total')</td>
                                                <td style="padding: 10px; border-bottom:1px solid #9E9E9E; text-align: right;">{{numberFormat($shop_ord_val->total_core_cost)}} @lang('common.baht')</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 10px; border-bottom:1px solid #9E9E9E;">@lang('checkout.grand_total')</td>
                                                <td style="padding: 10px; border-bottom:1px solid #9E9E9E; text-align: right;">{{numberFormat($shop_ord_val->total_final_price)}} @lang('common.baht')</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr><td style="height:50px;"></td></tr>

                    <!-- Footer starts -->
                @endforeach
            @endif
        </table>
    </div>
    @endforeach
</body>

</html>