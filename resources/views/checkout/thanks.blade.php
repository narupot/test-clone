@extends('layouts.app')

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/flickity'],'css') !!}
    <style type="text/css">
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
        .pshop-name {
            display: block;
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

/* CSS สำหรับส่วนแสดงผลรวมราคาสินค้าทั้งหมดในหน้า thanks */
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

@section('header_script')

@endsection

@section('content')
@if(!in_array($main_order->order_status, [2, 3]))
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="alert alert-danger text-center">
                    <h4>@lang('checkout.access_denied')</h4>
                    <p>@lang('checkout.order_status_not_allowed')</p>
                    <a href="/" class="btn btn-primary">@lang('checkout.back_to_home')</a>
                </div>
            </div>
        </div>
    </div>
@else
<div >
    <div class="confirmation">
        <h1>@lang('checkout.thank_you_for_using_the_simummuang_market')</h1>
        <div class="confirm-msg">
            <div class="track-msg">@lang('checkout.order_thanks_message')</div>
            <p class="red"> @lang('checkout.thank_you_for_your_trust_in_our_service')</p>
            <div class="text-center mb-2">
                <a href="/" class="btn-grey">@lang('checkout.continue_shopping')</a>
                <a class="btn" href="{{ action('HomeController@index') }}/track-order"><i class="fas fa-truck"></i> @lang('checkout.tracking_order')</a>
            </div>
            <div class="text-center mb-2">
                <button type="button" class="btn btn-outline-danger" onclick="printFromUrl('{{ action('Checkout\OrderConfirmationController@downloadOrderConfirmation', ['order_id' => $main_order->formatted_id??null]) }}')">
                    <i class="fas fa-print"></i> @lang('checkout.order_confirmation_print')
                </button>
            </div>
        </div>
    </div>
    {{-- <div class="text-right mb-3">
        <button class="btn" onclick="printDiv('printsection')">@lang('common.print')</button>
    </div> --}}
    <div class="combine" id="printsection">
        <div class="track-buyer-info">
            <h2>@lang('checkout.order_no'). {{ $main_order->formatted_id }}</h2>
            <div class="title-track-info">
                <h3>@lang('checkout.buyer_infomation')</h3>
            </div>
            <div class="track-info-detail">
                <div class="mb-3">
                    <div class="tInfo-row">
                        <span class="label">@lang('common.name') :</span> {{$main_order->user_name}}
                    </div>
                    <div class="tInfo-row">
                        <span class="label">@lang('common.email') :</span> {{$main_order->user_email}}
                    </div>
                    <div class="tInfo-row">
                        <span class="label">@lang('common.tel') :</span>  {{$main_order->ph_number}}
                    </div>
                </div>

                <div class="row"><h4><strong>@lang('order.shipping_method')</strong></h4> : {{ shippingMethodName($main_order->shipping_method) }}</div>
                @if($main_order->pickup_time)
                <div class="row"><h4><strong>@lang('order.pickup_time')</strong></h4> : {{ getDateFormat($main_order->pickup_time,8) }}</div>
                @endif
                <div class="row address-row">
                    @if($main_order->shipping_method == 1)
                        <div class="col-sm-6">
                            <div class="tInfo-row">
                                <h4><strong class="a">@lang('admin_order.center_address') : </strong></h4>
                                {!! CustomHelpers::centerAddress($main_order->order_json) !!}
                            </div>
                        </div>
                    @elseif($main_order->shipping_method == 2)
                        <div class="col-sm-6">
                            <div class="tInfo-row">
                                <h4 class="b"><strong>@lang('admin_order.store_address') : </strong></h4>
                                {!! CustomHelpers::storeAddress($main_order->order_json) !!}
                            </div>
                        </div>
                    @else
                        <div class="col-sm-6">
                            <div class="tInfo-row">
                                <h4 class="c"><strong>@lang('checkout.shipping_address') : </strong></h4>
                                {{ CustomHelpers::buyerShipBillTo($main_order->order_json,'shipping_address') }}
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="tInfo-row">
                                <h4 class="d"><strong>@lang('checkout.billing_address') : </strong></h4>
                                {{ CustomHelpers::buyerShipBillingTo($main_order->order_json,'billing_address') }}
                            </div>
                        </div>
                    @endif
                </div>
        </div>
        <h2>@lang('checkout.ordered_items')</h2>
        <div class="table-responsive track-order-table track-orderno">
            <div class="table">
                <div class="table-header">
                    <ul>
                        <li>@lang('checkout.seller')</li>
                        <li>@lang('checkout.product')</li>
                        <li>@lang('checkout.unit_price')</li>
                        <li>@lang('checkout.qty')</li>
                        <li>@lang('checkout.price')</li>
                        <li>@lang('checkout.credit_from_shop')</li>
                        <li>@lang('checkout.payment_method')</li>
                    </ul>
                </div>
                <div class="table-content">
                    @php $id_arr=[]; @endphp
                    @foreach($order_detail as $key => $val)
                        @php
                            $detail_json = jsonDecodeArr($val->order_detail_json);
                            $shop_url = action('ShopController@index',$detail_json['shop_url'] ??'');
                            $prd_url = action('ProductDetailController@display',[$detail_json['cat_url']??'',$val->sku]);
                            
                        @endphp
                        @if(!in_array($val->order_shop_id,$id_arr))
                            <div class="order-no"><span class="skyblue">@lang('order.shop_order_no')</span> : {{ $shop_order[$val->order_shop_id]['shop_formatted_id'] }}</div>
                            @php($id_arr[] = $val->order_shop_id)
                        @endif
                        <ul>
                            <li class="product-shop">
                                <a href="{{ $shop_url}}">
                                <span class="prod-img"><img src="{{getImgUrl($detail_json['logo'] ??'','logo')}}" width="50" height="50" alt=""></span>
                                <span class="shopname"><a class="pshop-name" href="{{ $shop_url }}">{{ $detail_json['shop_name'][session('default_lang')]??'' }}</a></span>
                                </a>
                            </li>
                            <li class="product">
                                <div class="dbox-flex">

                                    <div class="dbox-flex">
                                        <a href="{{ $prd_url}}">
                                        <span class="prod-img prod-134"><img src="{{ getProductImageUrlRunTime($detail_json['thumbnail_image']??'','thumb') }}" width="134" height="100" alt=""></span> </a>
                                    </div>

                                    <div class="ml-2">
                                        <span class="prod-name d-block mb-2"><a href="{{ $prd_url}}">{{ $detail_json['name'][session('default_lang')]??$val->category_name }}</a></span>
                                        <span class="la"><img src="{{ getBadgeImageUrl($detail_json['badge']['icon'] ?? '' )}}" width="30" alt="badge"></span>
                                    </div>
                                </div>
                            </li>

                            <li>{{number_format($val->last_price,2) }} @lang('common.baht') /{{ $detail_json['package'][session('default_lang')] ?? $val->package_name }}</li>

                            <li class="add-rem-qty">
                                {{ $val->quantity }} {{ $detail_json['package'][session('default_lang')] ?? $val->package_name }}
                            </li>

                            <li>
                                {{number_format($val->total_price,2) }} @lang('common.baht')
                            </li>

                            <li>{{ $val->payment_type=='credit'? number_format($val->total_price,2):'' }} @lang('common.baht')</li>

                            <li>{{$detail_json['payment_method'][session('default_lang')] ?? str_replace('_',' ',strtoupper($val->payment_slug)) }}</li>

                        </ul>
                    @endforeach
                    
                </div>
            </div>
            <div class="checkout-summary-section">
                <div class="checkout-table-footer clearfix">
                    <div class="col-sm-8 col-md-8 col-lg-8">
                        <div class="checkout-table-footer clearfix">
                            <div class="col float-right">
                                <div class="row">
                                    <div class="d-flex pt-1 w-100">
                                        <span class="col-6"><strong>@lang('checkout.total')</strong></span>
                                        <span class="col-6">{{ number_format($main_order->total_core_cost,2) }} @lang('common.baht')</span>
                                    </div>
                                </div>


                                @if($main_order->dcc_purchase_discount > 0)
                                <div class="row" id="dcc_purchase">
                                    <div class="d-flex justify-content-around p-2 pl-3 text-danger w-100">
                                        <span class="flex-grow-1">@lang('checkout.code_discount')</span>
                                        <span class="">-{{ number_format($main_order->dcc_purchase_discount,2) }} @lang('common.baht')</span>
                                    </div>
                                </div>
                                @endif

                                @if($main_order->total_shipping_cost > 0)
                                <div class="row" id="delvery_fee_div">
                                    <div class="d-flex justify-content-around w-100 border-top pt-1">
                                        <span class="col-6"><strong>@lang('checkout.delivery_fee')</strong></span>
                                        <span class="col-6">{{ number_format($main_order->total_shipping_cost,2) }} @lang('common.baht')</span>
                                    </div>
                                </div>
                                @endif

                                @if(isset($main_order->dcc_shipping_discount) && $main_order->dcc_shipping_discount > 0)
                                <div class="row" id="dcc_shipping">
                                    <div class="d-flex justify-content-around p-2 pl-3 text-danger w-100">
                                        <span class="flex-grow-1">@lang('checkout.discount_delivery_fee')</span>
                                        <span class="">-{{ number_format($main_order->dcc_shipping_discount,2) }} @lang('common.baht')</span>
                                    </div>
                                </div>
                                @endif

                                @if(isset($main_order->transaction_fee) && $main_order->transaction_fee > 0 && isset($main_order->payment_slug) && strpos($main_order->payment_slug, 'beam') === 0)
                                <div class="row" id="transaction_fee_row">
                                    <div class="d-flex border-top pt-1 w-100">
                                        <span class="col-6" id="transaction_fee_label">
                                            @if(isset($transaction_fee_name) && !empty($transaction_fee_name))
                                                <strong>{{ $transaction_fee_name }}</strong>
                                            @else
                                                <strong>@lang('checkout.transaction_fee')</strong>
                                            @endif
                                            <span class="text-danger"> 
                                                @if(isset($current_tf_percentage) && !empty($current_tf_percentage))
                                                    ({{ number_format($current_tf_percentage, 2) }}%)
                                                @else
                                                    (ฟรีค่าธรรมเนียม)
                                                @endif
                                            </span>
                                        </span>
                                        <span class="col-6 text-danger" id="transaction_fee_amount">{{ number_format($main_order->transaction_fee,2) }} @lang('common.baht')</span>
                                    </div>
                                </div>
                                @endif

                                <div class="row bg-light border-top pt-1">
                                    <div class="col-6"><strong>@lang('checkout.grand_total')</strong></div>
                                    <div class="col-6">
                                        <strong id="tot_order_amount">{{ number_format($main_order->total_final_price,2) }} @lang('common.baht')</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
@endif

</div>

<script type="text/javascript">
    function printDiv(id){
            var printContents = document.getElementById(id).innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
    }
    function printFromUrl(url) {
        var printWindow = window.open(url, '_blank');
        printWindow.focus();
        printWindow.onload = function () {
            printWindow.print();
        };
    }
</script>
@endsection