<html>
<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->
    <meta http-equiv="Content-Type" content="800; charset=utf-8" />
    <title>Shop Order List</title>

<style>
    @font-face {
	    /* font-family: "THSarabun"; */
        font-family: 'examplefont', sans-serif;
	    font-style: normal;
	    font-weight: normal;
	    /* src: url("{{ asset('pdf_fonts/THSarabun.ttf')}}") format("truetype"); */
    }  
    @font-face {
	    /* font-family: "THSarabunbold"; */
        font-family: 'examplefont', sans-serif;
	    font-style: normal;
	    font-weight: bolder;
	    /* src: url("{{ asset('pdf_fonts/THSarabun Bold.ttf')}}") format("truetype"); */
    }      
    body {
        /* font-family: "THSarabun"; */
        font-family: 'examplefont', sans-serif;
        font-size: 16px; color: #343A40; 
        background: #fff !important; line-height: 16px;
        letter-spacing: 0.38px;
        padding-bottom:1.62cm; margin-top: 5px;
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
        /* border: 1px solid #CED4DA; */
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

    /*  */
    @media  print {    
        body, html {
            margin:0;
        }
        .red, .dont-forget { background: #DC3545; }  
        .order_pdf_repeat {page-break-before: always; margin-top: 30px;} 
        .order_pdf_repeat.page0{
            page-break-before: avoid;
        }
    }
    @page {
        /* header: page-header;
        footer: page-footer; */
        margin-top: 4mm ; 
    }
</style>

<body style="font-family: examplefont, sans-serif; -webkit-font-smoothing: antialiased; line-height: 1.3; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;  margin: 0; padding: 0;">
    @foreach($total_order as $key => $order_shop)
        <?php 
            $shop_name ='';
            $json_name = json_decode($order_shop->shop_json,true);
            if($json_name){
                $shop_name = $json_name['shop_name'][0];
            }
            $order_detail = \App\OrderDetail::getShopOrderDetail('',$order_shop->id);
            $order_shop->details = $order_detail;
            $transaction = \App\OrderTransaction::where('order_shop_id',$order_shop->id)->get();
            if(count($transaction) < 2){
                $transaction = \App\OrderTransaction::where('order_id',$order_shop->order_id)->where('order_shop_id',0)->orderBy('id')->get();
            }
            $order_shop->pickup_time = null;
            if($order_shop->order_id>0)
            {
                $order_info = \App\Order::where('id',$order_shop->order_id)->first();
                if($order_info)
                {
                    $order_shop->pickup_time=$order_info->pickup_time;
                }
            }
            /* Start:: If Product Detail Not Available in Order Details */
            if($order_shop->details)
            {
                if(!empty($order_shop->details))
                {
                    foreach($order_shop->details as $key1 => $val)
                    {
                        if($val->description=='' || $val->description==null)
                        {
                            $productDetail = \App\Product::getProductDetail($val->sku);
                            $order_shop->details[$key1]->description=isset($productDetail->productDesc)?$productDetail->productDesc->description:"";
                        }
                    }
                }
            }
        ?>
        <div class="container order_pdf_repeat page{{$key}}">
            <div style="padding:10px 10px 10px 10px;box-shadow: 0px 3px 9px 0px #ccc;">
                <span style="color: #F00;">Main Order ID:</span> {{getMainOrderId($order_shop->order_id)}}
                <div style="text-align:right; float:right; margin-top:-16px;"><span style="color: #F00;">Shop Name :</span>{{$shop_name}}</div>
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
                                    {{ $order_shop->getOrderStatus->status??'' }}
                                </td>
                                <td style="text-align: center; padding: 30px 5px; font-size:34px; width: 50%; max-width: 50%;">
                                    <div style="margin-bottom: 12px;">&nbsp;&nbsp;&nbsp; ยอดรวม &nbsp;&nbsp;&nbsp;</div>
                                    <span style="font-size: 44px;">@lang('common.thb') {{ numberFormat($order_shop->total_final_price) }}</span>
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
                                    ชื่อ : {{$order_shop->user_name}} <br>
                                    อีเมล : {{$order_shop->user_email}} <br>
                                    เบอร์โทรศัพท์ : {{$order_shop->ph_number}}
                                </td>
                                @if($order_shop->shipping_method == 1)
                                    <td style="width:25%;">
                                        <div style="margin-bottom:15px;">ที่อยู่ในการจัดส่ง :</div>
                                        {!! CustomHelpers::centerAddress($order_shop->order_json) !!}

                                    </td>
                                @elseif($order_shop->shipping_method == 2)
                                    <td style="width:25%;">
                                        <div style="margin-bottom:15px;">ที่อยู่ในการออกใบเสร็จ :</div>
                                        {!! CustomHelpers::storeAddress($order_shop->order_json) !!}

                                    </td>
                                @else
                                    <td style="width:25%;">
                                        <div style="margin-bottom:15px;">ที่อยู่ในการจัดส่ง : </div>
                                        {{ CustomHelpers::buyerShipBillTo($order_shop->order_json,'shipping_address') }}
                                    </td>
                                    <td style="width:25%;">
                                        <div style="margin-bottom:15px;">ที่อยู่ในการออกใบเสร็จ : </div>
                                        {{ CustomHelpers::buyerShipBillTo($order_shop->order_json,'billing_address') }}
                                    </td>

                                @endif
                                <td style="width:25%;">
                                    <div style="margin-bottom:15px;">Shipping : <br> {{ GeneralFunctions::getShippingMethod($order_shop->shipping_method) }}</div>
                                    <div style="margin-bottom:15px;">Pickup Date : <br>{{$order_shop->pickup_time}}</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
    
                <!-- Forth row starts -->
                <tr><td style="height:30px;"></td></tr> 
                <tr>
                    <td style="padding-left: 0; padding-right: 0px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td style="width:50%; font-size: 28px; padding-left:0px;">@lang('admin_order.shop_order_id') : {{ $order_shop->shop_formatted_id }}</td>
                                <td style="text-align:right; width:50%; font-size: 28px; padding-right:0px;color:#F00;">@lang('admin_order.shop_order_status') : {{$order_shop->getOrderStatus->status}}</td>
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
                                <!-- <th style="text-align:center; font-weight: normal;">Remark</th> -->
                                <th style="text-align:center; font-weight: normal;">รายละเอียด <br>สินค้า</th>
                                <th style="text-align:center; font-weight: normal;">@lang('common.action')</th>
                            </tr>
                            @foreach($order_shop->details as $key => $val)
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
                                        <div class="la d-block"><img class="border-0" src="{{ getBadgeImageUrl($detail_json['badge']['icon'] ?? '' )}}" height="30"></div> 
                                                
                                    </td>
                                    <td style="text-align:left;">
                                        <div style="margin-bottom:4px;"><img src="{{getImgUrl($detail_json['logo'] ??'','logo')}}" alt="img" width="50"> </div>
                                        {{ $detail_json['shop_name'][session('default_lang')]??'' }}
                                    </td>
                                    <td>{{numberFormat($val->last_price) }} @lang('common.baht') /{{ $detail_json['package'][session('default_lang')] ?? $val->package_name }}</td>
                                    <td>{{ $val->quantity }} {{ $detail_json['package'][session('default_lang')] ?? $val->package_name }}
                                        <br> <span style="color:#F00;">{{convertString($val->total_weight) }} {{$val->base_unit}}<br> / {{$val->package_name}}</span></td>
                                    <td>{{numberFormat($val->total_price) }} @lang('common.baht')</td>
                                    <td>{!! CustomHelpers::formatPaymentMethodName($val->payment_slug, $detail_json['payment_method'] ?? null) !!}</td>
                                    <td><span style="color:#F00;">{{ $val->getOrderStatus->status??'' }}</span></td>
                                    <!-- <td>{{$val->api_remark}}</td> -->
                                    @php 
                                        $str_description = $val->description;
                                        $str_description = strip_tags($str_description);
                                        $str_description = mb_substr($str_description, 0, 30);
                                    @endphp
                                    <td>{!!$str_description!!}</td>
                                    @if(!$order_shop->end_shopping_date || $order_shop->order_status ==3 || $order_shop->order_status ==4)
                                    <td></td>
                                    @else
                                        
                                        <td>@if($val->status!=4) <a href="javascript:;" data-type='cancel' data-val="{{ $val->id }}" class="ord_item_change">@lang('common.cancel')</a> @endif | <a href="javascript:;" data-type='receive' data-val="{{ $val->id }}" class="ord_item_change">@lang('admin_order.center_received')</a></td> 
                                    @endif
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
                                <td style="width:50%">@lang('checkout.shop_remark') <br>
                                {{$order_shop->api_remark}}</td>
                                <td style="width:50%; border:1px solid #9E9E9E; padding:0; border-bottom:none;">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td style="padding: 10px; border-bottom:1px solid #9E9E9E;">@lang('checkout.total')</td>
                                            <td style="padding: 10px; border-bottom:1px solid #9E9E9E; text-align: right;">{{numberFormat($order_shop->total_core_cost)}} @lang('common.baht')</td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 10px; border-bottom:1px solid #9E9E9E;">@lang('checkout.grand_total')</td>
                                            <td style="padding: 10px; border-bottom:1px solid #9E9E9E; text-align: right;">{{numberFormat($order_shop->total_final_price)}} @lang('common.baht')</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr><td style="height:50px;"></td></tr>

                <!-- Footer starts -->
            </table>
        </div>
    @endforeach
</body>

</html>