@extends('layouts.app') 
@section('title','Checkout')
@section('header_style')
<style>
    .list-group-item {
        cursor: pointer;
    }
    .track-orderno {
        position: relative;
    }
    .order-no {
        position: absolute;
        padding: 10px;
        left: 0;
        right: 0;
        z-index: 9;
    }
    .order-no + ul li {
        padding-top: 50px;
    }
    .order-no + ul li:after {
        top: 70% !important;height: 50% !important;
    }
    .order-no:before {
        left: 0;
        top: 0;
        bottom: 0;
        position: absolute;
        content: " ";
        background: #E6E6E6;
        right: 0;
        z-index: -1;
    }
    .btn-small {
        padding: 5px;
        line-height: normal;
        font-size: 12px;
        display: inline-block;
    }
    .pshop-name {
        display: block;
        max-width: 300px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .card_wraps {
        background: #FFF;
        padding: 30px;
        box-shadow: 0 0 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 1rem;
        border-radius: 15px;
    }
    .card_wraps img {
        width: 75px;
        height: 75px;
        object-fit: contain;
        display: block;
        margin: 0 auto 1rem;
        border-radius: 6px;
    }

    /* สำหรับ mobile ให้ภาพไม่เกิน card */
    @media (max-width: 767.98px) {
        .card_wraps img {
            width: 50px;
            height: 50px;
            object-fit: contain;
            display: block;
            margin: 0 auto 1rem;
            border-radius: 5px;
        }
    }

    /* CSS สำหรับส่วนแสดงผลรวมราคาสินค้าทั้งหมดในหน้า kbank  */
    .checkout-summary-section {
        max-width: 100%; 
        margin: 20px auto; 
        padding: 0 15px; 
    }

    .checkout-summary-section .checkout-table-footer {
        display: flex;
        justify-content: flex-end; 
        width: 100%; 
    }

    .checkout-summary-section .col-sm-5 {
        flex: 0 0 auto;
        width: 100%; 
        max-width: 41.66666667%; 
    }


    @media (max-width: 767.98px) { 
        .checkout-summary-section .col-sm-5 {
            max-width: 100%; 
        }
    }

    .checkout-summary-section .float-right {
        float: right !important; 
    }

    .checkout-summary-section .row {
        display: flex !important;
        flex-wrap: wrap !important;
        margin-left: -15px !important; 
        margin-right: -15px !important;
    }

    .checkout-summary-section .row > span.col-6 {
        flex: 0 0 auto !important;
        width: 50% !important;
        padding-left: 15px !important;
        padding-right: 15px !important;
    }

    .checkout-summary-section .mb-2 {
        margin-bottom: 0.5rem !important;
    }

    .checkout-summary-section .text-danger {
        color: #dc3545 !important;
    }

    .checkout-summary-section .text-end {
        text-align: right !important;
    }

    .checkout-summary-section .text {
        color: #212529 !important; 
    }

    .checkout-summary-section hr.my-2 {
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
        border: 0 !important;
        border-top: 1px solid rgba(0,0,0,.1) !important;
    }

    .checkout-summary-section .grand-total-section {
        margin-top: 10px !important; 

    }

    .checkout-summary-section .grand-total-section .bg-light {
        background-color: #f8f9fa !important;
    }

    .checkout-summary-section .grand-total-section .p-2 {
        padding: 0.5rem !important;
    }

    .checkout-summary-section .grand-total-section .rounded {
        border-radius: 0.25rem !important;
    }

    .checkout-summary-section .grand-total-section .fw-bold {
        font-weight: 700 !important;
    }

    .checkout-summary-section .grand-total-section span {
        color: #333 !important;
        font-size: 1.1rem !important;
    }

</style>
@endsection
@section('content')
<form action="submit" method="post" id="payment_form">
    <div class="row h-100 justify-content-center align-items-center">
        <div class="card card_wraps text-center col-11 col-lg-3 col-sm-6">
            <!--https://www.mercular.com/img/footer/kbank.png-->
            <img src="/assets/images/kbank.png" class="card-img-top" alt="Omise">
            <div class="card-body">
                <div class="form-group">
                    <label for="item">@lang('common.phone_number')</label>
                    <input type="text" class="form-control" name="phone" id="phone">
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-success" value="@lang('checkout.pay')">
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive track-order-table track-orderno">
        <div class="table">
            <div class="table-header">
                <ul>
                    <li>@lang('product.product_name')</li>
                    <li>@lang('product.standered')</li>
                    <li>@lang('product.qty')</li>
                    <li>@lang('product.price_per_item')</li>
                    <li>@lang('product.price')</li>
                               
                </ul>
            </div>
            <div class="table-content">
                @if(count($order_detail))
                    @php($id_arr=[])
                    @foreach($order_detail as $key =>$item)

                    @php($detail_json = jsonDecodeArr($item->order_detail_json))
                    @php($prd_url =action('ProductDetailController@display',[$detail_json['cat_url']??'',$item->sku]))
                    @if(!in_array($item->order_shop_id,$id_arr))
                        <div class="order-no d-flex align-items-center"><span class="skyblue pshop-name">{{ $detail_json['shop_name'][session('default_lang')]??'' }}</span> : {{ $shop_order[$item->order_shop_id]['shop_formatted_id'] }} &nbsp;<span class="red">(@lang('order.order_status') : <span class="shop_status" id="shop_status_{{ $item->order_shop_id }}">{{ $shop_order[$item->order_shop_id]['status'] }}</span>)</span> 
                            @if($shop_order[$item->order_shop_id]['order_status'] ==3 || $shop_order[$item->order_shop_id]['order_status'] ==4)
                            @elseif($orderInfo->shipping_method == 2)
                            <a class="btn-light-red btn-small ml-auto receive_items" data-val="{{ $item->order_shop_id }}" href="javascript:void(0);">@lang('order.receive')</a>
                            @endif
                        </div>
                        @php($id_arr[] = $item->order_shop_id)
                    @endif
                    <ul>

                        <li class="product">
                            <a href="{{ $prd_url }}"><span class="prod-img"><img src="{{ getProductImageUrlRunTime($detail_json['thumbnail_image'],'thumb') }}" width="80" height="80" alt=""></span>
                            <span class="prod-name">{{ $detail_json['name'][session('default_lang')]??$item->category_name }}</span></a>
                        </li>
                        <li><span class="mr"><img src="{{ getBadgeImageUrl($detail_json['badge']['icon'] ?? '' )}}" width="30"></span></li>
                        <li> {{ $item->quantity }} {{ $detail_json['package'][session('default_lang')] ?? $item->package_name }}</li>
                        <li>{{numberFormat($item->last_price) }} @lang('common.baht')</li>
                        <li>{{numberFormat($item->total_price) }} @lang('common.baht')</li>
                        
                    </ul>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</form>

    <div class="checkout-summary-section">
        <div class="checkout-table-footer clearfix">
            <div class="col-sm-6 float-right">
                <div class="row ">
                    <span class="col-6 fw-bold">@lang('checkout.total')</span>
                    <span class="col-6 text-end">{{ number_format($orderInfo->total_core_cost??0,2) }} @lang('common.baht')</span>
                </div>

                @if($orderInfo->dcc_purchase_discount > 0)
                <div class="row justify-content-around pl-3 text-danger">
                    <span class="col-6">@lang('checkout.code_discount')</span>
                    <span class="col-6 text-end">-{{ number_format($orderInfo->dcc_purchase_discount??0,2) }} @lang('common.baht')</span>
                </div>
                @endif

                @if($orderInfo->total_shipping_cost > 0)
                <hr class="my-2">

                <div class="row text">
                    <span class="col-6 fw-bold">@lang('checkout.delivery_fee')</span>
                    <span class="col-6 text-end">{{ number_format($orderInfo->total_shipping_cost??0,2) }} @lang('common.baht')</span>
                </div>
                @endif

                {{-- ตรวจสอบว่ามีส่วนลดค่าจัดส่งหรือไม่ ถ้ามีและค่าจัดส่งมากกว่า 0 --}}
                @if(isset($orderInfo->dcc_shipping_discount) && $orderInfo->dcc_shipping_discount > 0)
                <div class="row justify-content-around pl-3 text-danger">
                    <span class="col-6">@lang('checkout.discount_delivery_fee')</span>
                    <span class="col-6 text-end">-{{ number_format($orderInfo->dcc_shipping_discount??0,2) }} @lang('common.baht')</span>
                </div>
                @endif

                <hr class="my-2">

                <div class="grand-total-section">
                    <div class="bg-light py-2 rounded fw-bold">
                        <div class="row">
                            <span class="col-6">@lang('checkout.grand_total')</span>
                            <span class="col-6 text-end">{{ number_format($orderInfo->total_final_price,2) }} @lang('common.baht')</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('footer_scripts')

    <script>
        var submit_url = "{{action('Checkout\CartController@createPayPlusOrder',$orderInfo->formatted_id)}}";
        var check_url = "{{action('Checkout\PaymentGatewayController@payplusCheck')}}";
        var waiting_url = "{{action('Checkout\CartController@payplusWaiting',$orderInfo->formatted_id)}}";
        function PopupCenter(url, title, w, h) {

            var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : window.screenX;
            var dualScreenTop = window.screenTop != undefined ? window.screenTop : window.screenY;

            var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
            var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

            var systemZoom = width / window.screen.availWidth;
            var left = (width - w) / 2 / systemZoom + dualScreenLeft;
            var top = (height - h) / 2 / systemZoom + dualScreenTop + 50;
            var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w / systemZoom + ', height=' + h / systemZoom + ', top=' + top + ', left=' + left);

            if (window.focus) newWindow.focus();
            return newWindow;
        }

        function CheckPayment(invoice) {
            $.get(check_url+'/'+invoice,function (data,status) {
                if(data.status == "success"){
                    showHideLoader('hideLoader');
                    window.location.href = data.url;
                }else{
                    setTimeout(function () {
                        CheckPayment(invoice);
                    },3000);
                }
            });
        }

        $(document).ready(function () {

            $("#payment_form").submit(function (e) {
                e.preventDefault();
                number = $("#phone").val();
                    if(number.length != 10){
                        $("#phone").css("border-color","red");
                        alert("Phone number must be 10 digits");
                    }else{
                        $.post(submit_url,{
                            _token : '{{ csrf_token() }}',
                            phone : number
                        },function (data, status) {
                            window.location.href = waiting_url;
                            /*url = window.location.href.replace("checkout","");
                            newWindow = PopupCenter(waiting_url+'/'+data,'Payment',400,500);*/
                            object = JSON.parse(atob(data));
                            CheckPayment(object.invoice);
                            showHideLoader('showLoader');
                        });

                    }
            });

        });

    </script>

@endsection