@extends('layouts.app')

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/dataTables.bootstrap', 'css/myaccount', 'css/cropper.min'],'css') !!}

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
        .table-footer{
            width: 100% !important;
        }

        /* CSS สำหรับส่วนแสดงผลรวมราคาสินค้าทั้งหมดในหน้า main_order */
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


        img.prod-badge{
            height: 30px;
        }
        .prod-image {
            background-repeat: no-repeat;
            background-position: center center;
            /* background-size: auto 100%; */
            background-size:cover;
            width: 135px;
            height: 100px;
        }

        .disabled-cart {
            opacity: 0.6;
            pointer-events: none;
            user-select: none;
            background-color: #ccc
        }
        
        input[type="checkbox"] {  transform: scale(1.5); }

        .fixed-top {
            left: inherit;
            /* box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); */
            border: 1px solid #dee2e6;
            padding: 10px 15px;
            z-index: 10;
            width: auto;
            margin-left: -30px;
            margin-right: -30px;
        }

        .strike-text {
            text-decoration: line-through !important;
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
        
        /* Mobile responsive for status section only */
        @media (max-width: 767.98px) {
            .track-status {
                width: 100%;
                margin-bottom: 15px;
            }
            
            .track-status .btn-sm {
                width: 100%;
                text-align: center;
                margin-bottom: 10px;
            }
            
            .ship-track-time {
                text-align: center;
                font-size: 0.9rem;
                color: #666;
            }
            
            .track-status + .d-flex.flex-column {
                width: 100%;
            }
            
            .track-status + .d-flex.flex-column .btn {
                width: 100%;
                text-align: center;
                margin-bottom: 10px;
            }
            
            .track-status + .d-flex.flex-column .btn:last-child {
                margin-bottom: 0;
            }
            
            .prod-image {
                background-repeat: no-repeat;
                background-position: center center;
                /* background-size: auto 100%; */
                background-size:cover;
                width: 135px;
                height: 100px;
            }
        }
        
        @supports (position: sticky) {
            .sticky-top {
                z-index: 10 !important;
            }
        }
    </style>
@endsection

@section('header_script')
@stop

@section('breadcrumbs')
@stop

@section('content')
        
<div class="track-order-num-wrap border-bottom pb-2">
    <h2 class="title-track-info text-center bg-danger text-white fw-bold">ประวัติการสั่งซื้อ</h2>
    <div class="cust-ord-num d-flex align-items-center">
        <h2>@lang('order.order_no') {{$main_order->formatted_id}}</h2>
        <div class=" ml-auto">
            <a href="{{ url()->previous() }}" class="btn btn-sm px-2"> @lang('common.back')</a>
        </div>
    </div>
    <div class="d-flex mb-3 mt-3 justify-content-between flex-wrap">
        <div class="track-status form-group mb-2">
            <button class="btn-sm btn px-2 col">@lang('common.status') : <span id="order_status">{{$main_order->getOrderStatus->status ?? "NA" }}</span></button>
            <span class="ship-track-time d-block mt-2 col">{{getDateFormat($main_order->updated_at,7)}}</span>
        </div>
        <div class="d-flex justify-content-end mb-2">
            @if($main_order->payment_status==0 && $main_order->order_status==1)
                <a class="btn btn-danger mb-2" href="{{action('User\OrderController@orderPayment',$main_order->formatted_id)}}"> @lang('admin_order.pay_now')</a>
            @endif
            @if(in_array($main_order->order_status, [2, 3]))
            <button type="button" class="btn btn-outline-danger btn-sm px-2" onclick="printFromUrl('{{ action('Checkout\OrderConfirmationController@downloadOrderConfirmation', ['order_id' => $main_order->formatted_id??null]) }}')">
                <i class="fas fa-print"></i> @lang('checkout.order_confirmation_print')
            </button>
            @endif
        </div>
    </div>
    <div class="text-right mb-2">
    </div>
    @if($main_order->payment_status==0 && $main_order->order_status==1)
    <div>
        <div class="notification_text re_pay pl-3">{!! getStaticBlock('before-checkout-notifiction') !!}</div>
    </div>
    @endif
</div>

<div class="track-buyer-info mt-3 border-top-0">
    <div class="title-track-info bg-danger">
        <h3 class="text-white">@lang('customer.buyer_information')</h3>
    </div>
    <div class="track-info-detail">
        <div class="tInfo-row">
            <span class="label">@lang('customer.name') :</span> {{$main_order->user_name}}
        </div>
        <div class="tInfo-row">
            <span class="label">@lang('customer.email') :</span> {{$main_order->user_email}}
        </div>
        <div class="tInfo-row">
            <span class="label">@lang('customer.telephone') :</span>  {{$main_order->ph_number}}
        </div>
        <div class="row">
        @if($main_order->shipping_method == 1)
            <div class="tInfo-row col">
                <h4><strong>@lang('admin_order.center_address') : </strong></h4>
                {!! CustomHelpers::centerAddress($main_order->order_json) !!}
            </div>
        @elseif($main_order->shipping_method == 2)
            <div class="tInfo-row col">
                <h4><strong>@lang('admin_order.store_address') : </strong></h4>
                {!! CustomHelpers::storeAddress($main_order->order_json) !!}
            </div>
        @else
            <div class="tInfo-row col-sm-6">
                <h4><strong>@lang('checkout.shipping_address') : </strong></h4>
                {{ CustomHelpers::buyerShipBillTo($main_order->order_json,'shipping_address') }}
            </div>
            <div class="tInfo-row col-sm-6">
                <h4><strong>@lang('checkout.billing_address') : </strong></h4>
                {{ CustomHelpers::buyerShipBillingTo($main_order->order_json,'billing_address') }}
            </div>
        @endif
        </div>
    </div>
    
</div>
<div class="title-track-info">
    <h3 class="skyblue">@lang('order.shipping_method')</h3>
    <div class="track-ship-name">{{ GeneralFunctions::getShippingMethod($main_order->shipping_method) }} @if(strtotime($main_order->pickup_time)) <span class="red">(@lang('order.expected_time_to_receive') : {{ getDateFormat($main_order->pickup_time,8) }} @if(isset($main_order->plus_two_hr)) - {{$main_order->plus_two_hr}}:00 @endif)</span> @endif</div>
</div>

<h2 class="d-flex">@lang('order.order_items')
    
    @if($main_order->order_status == 3 || $main_order->order_status == 4)
    @elseif($main_order->shipping_method == 2)
    <a class="btn-light-red btn-small ml-auto receive_all" data-val="{{ $main_order->formatted_id }}" href="javascript:void(0);">@lang('order.receive_all')</a>
    @endif
</h2>


{{-- new --}}
<div class="mb-3">
    @if(!empty($order_detail) && count($order_detail))

        <div class="col">
            <div class="row mb-2 justify-content-between align-items-center bg-white sticky-top">
                <div class="form-check pt-2">
                    <input class="form-check-input" type="checkbox" name="allProductItem" id="allProductItem">
                    <label class="form-check-label" for="allProductItem">&nbsp; เลือกทั้งหมด</label>
                </div>
                <button class="btn btn-sm px-2" type="button" id="btnAddToCart" >
                    <i class="fa fa-cart-plus"></i> เพิ่มสินค้าลงตะกร้า
                </button>
            </div>

            @php
                $changeProduct = null;
                $closeProduct = null;
            @endphp
            @foreach($order_detail as $i => $item)
                @php
                    $hasChangePrice = 0;
                    $package_name = '';
                @endphp
                @if($item->status == 9 || $item->status == 10 || $item->status == 11 || $item->status == 12)
                    <div id="cart_{{ $item->id }}" class="disabled-cart cart_shop_item cart_item row {{$loop->last?'':' '}} border p-3 mb-3 strike-text" >
                        <div class="col-12 p-0 d-flex justify-content-between align-items-center mb-3">
                            <h3 class="">{{ $item->getShopDesc->shop_name??'' }}</h3>
                            @if ($item->hasChangePrice)
                                <h5 class="text-danger bold mb-0">@lang('checkout.product_price_changed')</h5>
                            @endif
                        </div>
                        <div class=" col-sm-12 col-md-8 col mb-2 d-sm-flex">
                            <div class="mr-3 mb-2">
                                <input type="checkbox" name="" value="{{ $item->id }}" class="mr-3 float-left "  >
                                <a class="d-flex align-items-center " href="{{ action('ProductDetailController@display',[$item->getCat->url,$item->sku])}}">
                                    <div class="prod-image" style="
                                        background-image: url('{{ getProductImageUrlRunTime($item->getPrd->thumbnail_image,'thumb') }}');
                                    "></div>
                                </a>
                            </div>
                            <div class="flex-sm-grow-1">
                                <div>
                                    <h3 class="prod-name mb-1">
                                        <strong><a href="{{ action('ProductDetailController@display',[$item->getCat->url,$item->getPrd->sku])}}">{{ $item->getCatDesc->category_name??'' }}</a></strong>
                                    </h3>
                                    <div class="mb-1">
                                        <img src="{{ getBadgeImage($item->getPrd->badge_id) }}" height="40" alt="Badge" class="prod-badge" />
                                    </div>
                                </div>
                                <div class="">
                                    <h4 class="mb-1 ">
                                        {{ number_format($item->total_weight??null, 2) }}
                                        {{ $item->base_unit??null}} / {{ $item->package_name??null }}
                                    </h4>
                                    <h4 class="mb-1">
                                        <span class="prd-unit-price">{{ number_format($item->last_price  ?? 0, 2) }} </span>
                                        <span>&nbsp;@lang('common.baht') / {{ $item->package_name??null }} </span>
                                    </h4>
                                    @if ($item->last_price != $item->original_price)
                                        <small class="text-danger">สินค้าต่อรองราคา</small>
                                    @endif
                                </div>

                            </div>
                        </div>
                        <div class=" text-center col-sm-12 col-md-4 text-right px-0 mt-auto mb-auto ">
                            <h3 class="font-weight-bold prd-total-price text-right mr-3">
                                {{ $item->quantity }} {{ $item->getPrd->package_id ?getPackageName($item->getPrd->package_id) : null }}
                            </h3>
                            <h2 class="font-weight-bold prd-total-price text-danger text-right mr-3">
                                ฿{{ $hasChangePrice
                                ? number_format(($item->getPrd->unit_price ?? 0) * $item->quantity, 2)
                                : number_format($item->total_price ?? 0, 2) }}
                            </h2>
                        </div>
                    </div>
                @else
                    <div id="cart_{{ $item->id }}" class="cart_shop_item cart_item row {{$loop->last?'':' '}} border p-3 mb-3" >
                        <div class="col-12 p-0 d-flex justify-content-between align-items-center mb-3">
                            <h3>
                                {{ $item->getShopDesc->shop_name??'' }}
                                <span>{{ $shop_order[$item->order_shop_id]['shop_formatted_id'] }}</span>
                            </h3>
                            @if ($hasChangePrice)
                                <h5 class="text-danger bold mb-0">@lang('checkout.product_price_changed')</h5>
                            @endif
                        </div>
                        <div class=" col-sm-12 col-md-8 col mb-2 d-sm-flex">
                            <div class="mr-3 mb-2">
                                <input type="checkbox" name="itemOrder[]" value="{{ $item->id }}" class="mr-3 float-left itemOrder" data-quantity="{{ $item->quantity }}" data-product-id="{{ $item->product_id }}" checked >
                                <a class="d-flex align-items-center " href="{{ action('ProductDetailController@display',[$item->getCat->url,$item->sku])}}">
                                    <div class="prod-image" style="
                                        background-image: url('{{ $item->order_detail_json['thumbnail_image']?getProductImageUrlRunTime($item->order_detail_json['thumbnail_image'],'thumb'):'' }}');
                                    "></div>
                                </a>
                            </div>
                            <div class="flex-sm-grow-1">
                                <div>
                                    <h3 class="prod-name mb-1">
                                        <strong><a href="{{ action('ProductDetailController@display',[$item->getCat->url,$item->sku])}}">{{ $item->getCatDesc->category_name??'' }}</a></strong>
                                    </h3>
                                    <div class="mb-1">
                                        <img src="{{ $item->order_detail_json['badge']['icon']?getBadgeImageUrl($item->order_detail_json['badge']['icon']):'' }}" height="40" alt="Badge" class="prod-badge" />
                                    </div>
                                </div>
                                <div class="">
                                    <h4 class="mb-1 ">
                                        {{ number_format($item->total_weight??null, 2) }}
                                        {{ $item->base_unit??null}} / {{ $item->package_name??null }}
                                    </h4>
                                    <h4 class="mb-1">
                                        <span class="prd-unit-price">{{ number_format($item->last_price  ?? 0, 2) }}</span>
                                        <span>&nbsp;@lang('common.baht') / {{ $item->package_name??null }} </span>
                                    </h4>
                                    @if ($item->last_price != $item->original_price)
                                        <small class="text-danger">สินค้าต่อรองราคา</small>
                                    @endif
                                </div>

                            </div>
                        </div>
                        
                        <div class=" text-center col-sm-12 col-md-4 text-right px-0 mt-auto mb-auto ">
                            <h3 class="font-weight-bold prd-total-price text-right mr-3">
                                {{ $item->quantity }} {{ $item->package_name??null }}
                            </h3>
                            <h2 class="font-weight-bold prd-total-price text-danger text-right mr-3">
                                ฿{{ $hasChangePrice
                                ? number_format(($item->order_detail_json->unit_price ?? 0) * $item->quantity, 2)
                                : number_format($item->total_price ?? 0, 2) }}
                            </h2>
                            @if($item->product_from == 'bargain')
                                <tr>
                                    <td colspan="3" class="text-muted small text-right">
                                        @lang('checkout.price_has_already_bargained')
                                    </td>
                                </tr>
                            @endif
                        </div>
                    </div>
                @endif
                {{-- @else
                    @php
                        $item['hasChangePrice'] = true;
                        $closeProduct[] = $item;
                    @endphp
                @endif --}}
        
            @endforeach
            
            <div class="mb-5"></div>
            
            {{-- @if (!empty($closeProduct))
                <h2 class="row">รายการสินค้าปิดการขาย</h2>
                @foreach ($closeProduct as $item)
                    <div id="cart_{{ $item->id }}" class="disabled-cart cart_shop_item cart_item row {{$loop->last?'':' '}} border p-3 mb-3" >
                        <div class="col-12 p-0 d-flex justify-content-between align-items-center mb-3">
                            <h3 class="">{{ $item->getShopDesc->shop_name??'' }}</h3>
                            @if ($item->hasChangePrice)
                                <h5 class="text-danger bold mb-0">@lang('checkout.product_price_changed')</h5>
                            @endif
                        </div>
                        <div class=" col-sm-12 col-md-8 col mb-2 d-sm-flex">
                            <div class="mr-3 mb-2">
                                <input type="checkbox" name="" value="{{ $item->id }}" class="mr-3 float-left "  >
                                <a class="d-flex align-items-center " href="{{ action('ProductDetailController@display',[$item->getCat->url,$item->sku])}}">
                                    <div class="prod-image" style="
                                        background-image: url('{{ getProductImageUrlRunTime($item->getPrd->thumbnail_image,'thumb') }}');
                                    "></div>
                                </a>
                            </div>
                            <div class="flex-sm-grow-1">
                                <div>
                                    <h3 class="prod-name mb-1">
                                        <strong><a href="{{ action('ProductDetailController@display',[$item->getCat->url,$item->getPrd->sku])}}">{{ $item->getCatDesc->category_name??'' }}</a></strong>
                                    </h3>
                                    <div class="mb-1">
                                        <img src="{{ getBadgeImage($item->getPrd->badge_id) }}" height="40" alt="Badge" class="prod-badge" />
                                    </div>
                                </div>
                                <div class="">
                                    <h4 class="mb-1 ">
                                        {{ number_format($item->total_weight??null, 2) }}
                                        {{ $item->base_unit??null}} / {{ $item->package_name??null }}
                                    </h4>
                                    <h4 class="mb-1">
                                        <span class="prd-unit-price">{{ number_format($item->last_price  ?? 0, 2) }} </span>
                                        <span>&nbsp;@lang('common.baht') / {{ $item->package_name??null }} </span>
                                    </h4>
                                    @if ($item->last_price != $item->original_price)
                                        <small class="text-danger">สินค้าต่อรองราคา</small>
                                    @endif
                                </div>

                            </div>
                        </div>
                        <div class=" text-center col-sm-12 col-md-4 text-right px-0 mt-auto mb-auto ">
                            <h3 class="font-weight-bold prd-total-price text-right mr-3">
                                {{ $item->quantity }} {{ $item->getPrd->package_id ?getPackageName($item->getPrd->package_id) : null }}
                            </h3>
                            <h2 class="font-weight-bold prd-total-price text-danger text-right mr-3">
                                ฿{{ $hasChangePrice
                                ? number_format(($item->getPrd->unit_price ?? 0) * $item->quantity, 2)
                                : number_format($item->total_price ?? 0, 2) }}
                            </h2>
                        </div>
                    </div>
                @endforeach
            @endif --}}
                
        </div>

    @else
        <div>@lang('common.no_record_found')</div>
    @endif
    
</div>

<div class="d-flex justify-content-end">
    <div class="col-sm-12 col-md-10 col-lg-8 col-xl-6 ">
        
        @if(isset($order_discount_code) && $order_discount_code && !empty($order_discount_code->discount_code))
        <div class="row my-2">
            <div class="d-flex w-100 justify-content-around">
                    <div class="flex-grow-1 "><strong>โค้ด@lang('checkout.code_discount')</strong></div>
                    <div class="mt-2">{{ $order_discount_code->discount_code }}</div>
                
            </div>
        </div>
        @endif

        <div class="row my-2">
            <div class="d-flex w-100 justify-content-around">
                <span class="flex-grow-1"><strong>@lang('checkout.total')</strong></span>
                <span class="">{{ number_format($main_order->total_core_cost,2) }} @lang('common.baht')</span>
            </div>
        </div>
        
        @if($main_order->dcc_purchase_discount > 0)
        <div class="row my-2" id="dcc_purchase">
            <div class="d-flex justify-content-around pl-3 text-danger w-100">
                <span class="flex-grow-1">@lang('checkout.code_discount')</span>
                <span class="">-{{ number_format($main_order->dcc_purchase_discount,2) }} @lang('common.baht')</span>
            </div>
        </div>
        @endif

        @if($main_order->total_shipping_cost > 0)
        <div class="row my-2 border-top " id="delvery_fee_div">
            <div class="d-flex justify-content-around w-100 pt-1">
                <span class="flex-grow-1"><strong>@lang('checkout.delivery_fee')</strong></span>
                <span class="">{{ number_format($main_order->total_shipping_cost,2) }} @lang('common.baht')</span>
            </div>
        </div>
        @endif

        @if(isset($main_order->dcc_shipping_discount) && $main_order->dcc_shipping_discount > 0)
        <div class="row my-2" id="dcc_shipping">
            <div class="d-flex justify-content-around pl-3 text-danger w-100">
                <span class="flex-grow-1">@lang('checkout.discount_delivery_fee')</span>
                <span class="">-{{ number_format($main_order->dcc_shipping_discount,2) }} @lang('common.baht')</span>
            </div>
        </div>
        @endif

        @if(isset($main_order->transaction_fee) && $main_order->transaction_fee > 0 && isset($main_order->payment_slug) && strpos($main_order->payment_slug, 'beam') === 0)
        <div class="row my-2 border-top " id="transaction_fee_row">
            <div class="d-flex justify-content-around w-100 align-items-end">
                <span class="flex-grow-1" id="transaction_fee_label">
                    @if(isset($transaction_fee_name) && !empty($transaction_fee_name))
                        <strong>@lang('checkout.transaction_fee') {{ $transaction_fee_name }}</strong>
                    @else
                        <strong>@lang('checkout.transaction_fee')</strong>
                    @endif
                    <span class="text-danger">
                        @if(isset($current_tf_percentage) && !empty($current_tf_percentage))
                            ({{ number_format($current_tf_percentage, 2) }}%)
                        @endif
                    </span>
                </span>
                <span class="text-danger" id="transaction_fee_amount">{{ number_format($main_order->transaction_fee,2) }} @lang('common.baht')</span>
            </div>
        </div>
        @endif

        <div class="row py-2 bg-light border-top mb-3">
            <div class="flex-grow-1"><strong>@lang('checkout.grand_total')</strong></div>
            <div class="">
                <strong id="tot_order_amount">{{ number_format($main_order->total_final_price,2) }} @lang('common.baht')</strong>
            </div>
        </div>
    </div>
</div>

                
@endsection

@section('footer_scripts')
    <script type="text/javascript">
        // var receive_item_url = "{{ action('User\OrderController@receiveOrdItems') }}";
        // var lang_receive_item = "@lang('order.are_you_sure_want_to_receive_this_items')";
        // var lang_yes = "@lang('common.yes')";
        // var lang_no = "@lang('common.no')";

        function printFromUrl(url) {
            var printWindow = window.open(url, '_blank');
            printWindow.focus();
            printWindow.onload = function () {
                printWindow.print();
            };
        }

        let $header = $('#header');
        function updateOffsets($el) {
            let headerHeight = $header.outerHeight() || 0;
            let targetTop = $el.offset().top;
            return {
                headerHeight: headerHeight,
                targetOffset: targetTop - headerHeight
            };
        }
        // ทำงานกับ sticky-top ทุกตัว
        $('.sticky-top').each(function () {
            let $target = $(this);
            let offsets = updateOffsets($target);

            $(window).on('load scroll resize', function () {
                offsets = updateOffsets($target);
                let scrollTop = $(window).scrollTop();

                if (scrollTop >= offsets.targetOffset) {
                    if (!$target.hasClass('fixed-top')) {
                        let targetLeft = $target.offset().left;

                        $target.addClass('fixed-top').css({
                            'top': offsets.headerHeight + 'px',
                            'left': targetLeft + 'px',
                            // 'width': $target.outerWidth() + 'px' // กัน layout กระโดด
                        });
                    }
                } else {
                    if ($target.hasClass('fixed-top')) {
                        $target.removeClass('fixed-top').css({
                            'top': '',
                            'left': '',
                            // 'width': ''
                        });
                    }
                }
            });
        });


        // Select / Deselect all checkboxes
        $('#allProductItem').prop('checked', true);
        $('.itemOrder').prop('checked', true);

        $('#allProductItem').on('change', function () {
            let isChecked = $(this).is(':checked');
            $('.itemOrder').prop('checked', isChecked);
        });
        $('.itemOrder').on('change', function () {
            let allChecked = $('.itemOrder').length === $('.itemOrder:checked').length;
            $('#allProductItem').prop('checked', allChecked);
        });

        $("#btnAddToCart").click(function(){
            let products = [];
            $(".itemOrder:checked").each(function(){
                products.push({
                    productId: $(this).data("product-id"),
                    quantity: $(this).data("quantity")
                });
            });
            
            if(products.length === 0){
                showSweetAlertError("กรุณาเลือกสินค้าที่ต้องการเพิ่มลงตะกร้า");
                return;
            }
            $.ajax({
                url: "{{ route('user.order.reOrderToCart',['order_id'=>$main_order->formatted_id??null]) }}",
                type: "POST",
                data: {
                    _token: window.Laravel.csrfToken,
                    products: products
                },
                beforeSend: function() { showHideLoader('showLoader'); },
                success: function(resp){
                    if(resp.status === 'success'){
                        let { addToCartItems = [],itemNotfound = []} =resp.data;
                        if(itemNotfound.length > 0){
                            swal("คำเตือน", `เพิ่มสินค้าลงตะกร้า ${addToCartItems.length} รายการ เรียบร้อย\n ไม่สามารถเพิ่มสินค้า ${itemNotfound.length} รายการ  \nกรุณาตรวจสอบในตะกร้าสินค้า`, "warning")
                            .then(function(isConfirm){ window.location.href = "{{ action('Checkout\CartController@shoppingCart') }}";});
                        } else{
                            swal("สำเร็จ", `เพิ่มสินค้าลงตะกร้า ${addToCartItems.length} รายการ เรียบร้อย\n กรุณาตรวจสอบในตะกร้าสินค้า`, "success")
                            .then(function(isConfirm){ window.location.href = "{{ action('Checkout\CartController@shoppingCart') }}";});
                        }
                    } else {
                        showSweetAlertError("เกิดข้อผิดพลาด");
                    }
                },
                error: function(xhr, status, error) {
                    showSweetAlertError("ไม่สามารถดำเนินการได้ กรุณาทำรายการใหม่อีกครั้ง");
                },
                complete: function () { showHideLoader('hideLoader'); }

            });

        });

    </script>
    {!! CustomHelpers::combineCssJs(['js/jquery.dataTables','js/dataTables.bootstrap', 'js/user/myaccount'],'js') !!}
    <!-- begining of page level js -->
    <!-- end of page level js -->
@endsection