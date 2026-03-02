@extends('layouts.app')

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/dataTables.bootstrap', 'css/myaccount', 'css/cropper.min'],'css') !!}
    <style>
        .text-danger {
            color: #dc3545 !important;
        }
        .hr {
            border-top: 1px solid #dee2e6;
            margin: 0.5rem 0;
        }
        
        /* Payment Method Grid Layout - Desktop */
        .payment-methods-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin: 20px 0;
        }
        
        .payment-method-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            min-height: 160px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .payment-method-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: #007bff;
        }
        
        .payment-method-card.selected {
            border-color: #007bff;
            background: #f8f9ff;
            box-shadow: 0 4px 15px rgba(0,123,255,0.2);
        }
        
        .payment-method-icon {
            width: 48px;
            height: 48px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .payment-method-icon img {
            max-width: 100%;
            max-height: 100%;
        }
        
        .payment-method-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .payment-method-subtitle {
            font-size: 12px;
        }
        /* Payment Total Container Styling */

        .grand-total-container {
            background: #fff;
            border-radius: 3px;
            padding: 10px 6px;
            margin-left: -6px;
            margin-right: -6px;
            margin-top: -5px;
        }


        .payment-method-fee {
            font-size: 11px;
            color: #28a745;
            font-weight: 500;
        }
        
        .payment-method-fee.charged {
            color: #dc3545;
        }
        
        /* Mobile Layout - Vertical List */
        @media (max-width: 768px) {
            .payment-methods-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .payment-method-card {
                padding: 20px 15px;
                min-height: 120px;
                flex-direction: column;
                text-align: center;
                justify-content: center;
                align-items: center;
            }
            
            .payment-method-icon {
                width: 48px;
                height: 48px;
                margin-bottom: 15px;
                margin-right: 0;
                flex-shrink: 0;
            }
            
            .payment-method-content {
                flex: 1;
                text-align: center;
            }
            
            .payment-method-title {
                font-size: 16px;
                margin-bottom: 8px;
                font-weight: 700;
            }
            
            .payment-method-subtitle {
                font-size: 12px;
                margin-bottom: 8px;
            }
            
            .payment-method-fee {
                font-size: 12px;
                font-weight: 500;
            }
        }
        
        /* Small Mobile */
        @media (max-width: 480px) {
            .payment-method-card {
                padding: 18px 12px;
                min-height: 110px;
                border-radius: 10px;
                margin-bottom: 10px;
            }
            
            .payment-method-icon {
                width: 44px;
                height: 44px;
                margin-bottom: 12px;
            }
            
            .payment-method-title {
                font-size: 15px;
                font-weight: 700;
                color: #333;
            }
            
            .payment-method-subtitle {
                font-size: 11px;
            }
            
            .payment-method-fee {
                font-size: 11px;
                font-weight: 500;
            }
            
            .payment-method-fee.charged {
                color: #dc3545;
            }
        }
        
        /* Extra Small Mobile */
        @media (max-width: 360px) {
            .payment-method-card {
                padding: 15px 10px;
                min-height: 100px;
            }
            
            .payment-method-icon {
                width: 40px;
                height: 40px;
                margin-bottom: 10px;
            }
            
            .payment-method-title {
                font-size: 14px;
            }
            
            .payment-method-fee {
                font-size: 10px;
            }
        }
        
        /* Checkout Button Styling */
        .btn-checkout-mobile {
            font-size: 16px;
            padding: 15px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
            transition: all 0.3s ease;
        }
        
        .btn-checkout-mobile:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(220, 53, 69, 0.4);
        }
        
        .btn-checkout-mobile:active {
            transform: translateY(0);
        }
        
        @media (max-width: 768px) {
            .btn-checkout-mobile {
                font-size: 18px;
                padding: 18px 25px;
                margin: 20px 0;
            }
        }
        
        @media (max-width: 480px) {
            .btn-checkout-mobile {
                font-size: 20px;
                padding: 20px 30px;
                margin: 25px 0;
            }
        }
        
        /* Transaction Fee Info Styling */
        .transaction-fee-info {
            margin-top: 8px;
            text-align: center;
        }
        
        .fee-amount {
            font-size: 12px;
            font-weight: bold;
            color: #dc3545;
            padding: 2px 6px;
            border-radius: 4px;
            margin-bottom: 2px;
        }
        
        .fee-message {
            font-size: 10px;
            color: #6c757d;
            line-height: 1.2;
        }
        
        /* Order tracking styles */
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
        
        /* Payment Summary Spacing */
        .payment-summary-row {
            margin-bottom: 0.5rem;
        }
        
        .payment-summary-row:last-child {
            margin-bottom: 0;
        }
        
        /* Discount Items Styling */
        .discount-item {
            padding-left: 20px;
            margin-left: 10px;
        }
        
        /* Grand Total Styling */
        .grand-total {
            font-weight: bold;
            font-size: 16px;
            color: #333;
        }
        
        /* Divider Line */
        .summary-divider {
            border-top: 1px solid #dee2e6;
            margin: 10px 0;
        }
        
        /* Mobile Banking Card on Desktop */
        .payment-method-card.desktop-mobile-banking {
            position: relative;
        }
        
        .payment-method-card.desktop-mobile-banking::after {
            position: absolute;
            top: 8px;
            right: 8px;
            font-size: 14px;
            opacity: 0.7;
        }
        
        .prod-image {
            background-repeat: no-repeat;
            background-position: center center;
            /* background-size: auto 100%; */
            background-size:cover;
            width: 135px;
            height: 100px;
        }
    </style>
@endsection

@section('header_script')
@stop

@section('breadcrumbs')
@stop

@section('content')
        
<div class="track-order-num-wrap border-bottom pb-2">
    <div class="cust-ord-num d-flex align-items-center">
        <h2>@lang('order.order_no') {{$main_order->formatted_id}}</h2>
        <div class="btn-group ml-auto">
            <a href="{{ url()->previous() }}" class="btn-grey"><i>&lt;</i> @lang('common.back')</a>
        </div>
    </div>
</div>

<div class="title-track-info">
    <h3 class="skyblue">@lang('order.shipping_method')</h3>
    <div class="track-ship-name">{{ GeneralFunctions::getShippingMethod($main_order->shipping_method) }}</div>
</div>

<form id="checkout_form" method="post" action="{{ action('Checkout\CartController@submitPayment') }}">
    {{ csrf_field() }}
    <input type="hidden" name="formatted_id" value="{{ $main_order->formatted_id }}">
    <div class="form-row">
        <div class="col-sm-4">
            <label for="">@lang('checkout.pickup_time') <i class="red">*</i></label>
            <select class="" name="pickup_time" id="pickup_time">
                <option value="">Select</option>
                @foreach($pickup_time_arr as $key => $val)
                    <option value="{{$val['key']}}" >{{$val['val']}}</option>
                @endforeach
            </select>
            <p class="error" id="e_pickup_time"></p>
            <input type="hidden" name="nexday" value="">
        </div>
    </div>
    <div class=" mb-3">
        <div id="payment_method_div">
            <div class="step-title">
                <span class="step-num">2</span>
                <h3>@lang('checkout.select_payment_method')<i class="red">*</i></h3>
            </div>
          
            <div class="row">
                @if(count($payment_option))
                    @foreach($payment_option as $pkey => $pval)
                        @if($pval->slug!='odd' ||($pval->slug=='odd' && !empty($user_odd_info) && $user_odd_info->espa_id!=''))
                            @php
                                // ใช้ relation แทนการ hard code
                                $feeConfig = $pval->transactionFeeConfig;
                                $paymentName = $pval->paymentOptName->payment_option_name ?? '';
                            @endphp
                            <div class="col-6 col-sm-6 col-md-6 col-lg-3 mb-3">
                                <div class="payment-method-card h-100 {{ ($main_order->payment_slug ?? null) == $pval->slug ? 'selected' : '' }}" data-payment-id="{{ $pval->id }} ">
                                    <div class="payment-method-icon">
                                        @php
                                            $payment_images = getMultiplePayImgUrls($pval->image_name);
                                        @endphp
                                        @if(count($payment_images) == 1)
                                            <img src="{{ $payment_images[0] }}" alt="{{ $paymentName }}" style="border-radius: 5px;">
                                        @elseif(count($payment_images) >= 2)
                                            <div class="d-flex justify-content-center align-items-center" style="gap: 6px;">
                                                @foreach($payment_images as $img_url)
                                                    <img src="{{ $img_url }}" alt="" style="width: 48px; height: 48px; object-fit: contain; border-radius: 5px;">
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="d-flex justify-content-center align-items-center" style="gap: 3px;">
                                                @foreach($payment_images as $img_url)
                                                    <img src="{{ $img_url }}" alt="" style="max-width: 30px; max-height: 30px; object-fit: contain; border-radius: 5px;">
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="payment-method-content">
                                        <div class="payment-method-title">{{ $paymentName }}</div>
                                        @if($feeConfig)
                                            {{-- แสดงค่าธรรมเนียมจาก relation --}}
                                            <div class="mt-1">
                                                @if($feeConfig->current_tf > 0)
                                                    <small class="text-danger">{{ $feeConfig->message ?? 'ค่าธรรมเนียม' }} </small>
                                                    <small class="text-danger">{{ number_format($feeConfig->current_tf, 2) }}%</small>
                                                @else
                                                    <small class="text-success">ฟรีค่าธรรมเนียม</small>
                                                @endif
                                            </div>
                                        @else
                                            {{-- แสดงฟรีค่าธรรมเนียมสำหรับวิธีการชำระที่ไม่มีค่าธรรมเนียม --}}
                                            <div class="payment-method-fee">ฟรีค่ารรรมเนียม</div>
                                        @endif
                                    </div>
                                    <input type="radio" name="payment_method" value="{{ $pval->id }}" style="display: none;" {{ ($main_order->payment_slug ?? null) == $pval->slug ? 'checked' : '' }}>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
            <p id="e_payment_method" class="error"></p>
        </div>
    </div>

    <div class="mb-3">
        <h2><strong>รายการสั่งซื้อแล้ว</strong></h2>
        @if(!empty($order_detail) && count($order_detail))

            <div class="col">
                @php
                    $changeProduct = null;
                    $closeProduct = null;
                @endphp
                @foreach($order_detail as $i => $item)
                    @php
                        $hasChangePrice = 0;
                        $package_name = '';
                    @endphp

                    @if ( $item->getShop->status === '1' && $item->getPrd->status === '1' )
                        @php
                            $hasChangePrice = $item->last_price != $item->getPrd->unit_price;
                            $package_name = $item->getPrd->package->title;
                            $stockNotEnough = $item->getPrd->quantity < $item->getPrd->min_order_qty;
                            if ($hasChangePrice || $stockNotEnough) {
                                $changeProduct[] = $item;
                            }
                        @endphp

                        <div id="cart_{{ $item->id }}" class="cart_shop_item cart_item row {{$loop->last?'':' '}} border p-3 mb-3" >
                            <div class="col-12 p-0 d-flex justify-content-between align-items-center mb-3">
                                <h3>
                                    {{ $item->getShopDesc->shop_name??'' }}
                                    <span>{{ $shop_order[$item->order_shop_id]['shop_formatted_id'] }}</span>
                                </h3>
                                {{-- <h5><span class="red">(<span class="shop_status" id="shop_status_{{ $cartVal->order_shop_id }}">{{ $shop_order[$cartVal->order_shop_id]['status'] }}</span>)</h5> --}}
                                @if ($hasChangePrice)
                                    <h5 class="text-danger bold mb-0">@lang('checkout.product_price_changed')</h5>
                                @endif
                            </div>
                            <div class=" col-sm-12 col-md-8 col mb-2 d-sm-flex">
                                <div class="mr-3 mb-2">
                                    <a class="d-flex align-items-center " href="{{ action('ProductDetailController@display',[$item->getCat->url,$item->getPrd->sku])}}">
                                        {{-- <img src="{{ getProductImageUrlRunTime($item->getPrd->thumbnail_image,'thumb') }}"   alt="" class=""> --}}
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
                                    {{ $item->quantity }} {{ $item->getPrd->package_id ?getPackageName($item->getPrd->package_id) : null }}
                                </h3>
                                <h2 class="font-weight-bold prd-total-price text-danger text-right mr-3">
                                    ฿{{ $hasChangePrice
                                    ? number_format(($item->getPrd->unit_price ?? 0) * $item->quantity, 2)
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
                    @else
                        @php
                            $item['hasChangePrice'] = true;
                            $closeProduct[] = $item;
                        @endphp
                    @endif
            
                @endforeach
                
                <div class="mb-5"></div>
                
                @if (!empty($closeProduct))
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
                                    <a class="d-flex align-items-center " href="{{ action('ProductDetailController@display',[$item->getCat->url,$item->getPrd->sku])}}">
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
                @endif
                    
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

            @php
                $paymentOption = $main_order->paymentOption ?? null;
                $transactionFeeConfig = $paymentOption->transactionFeeConfig ?? null;
                $current_tf_rate = $transactionFeeConfig->current_tf ?? 0;
                $isHasTF = $transactionFeeConfig && ($main_order->transaction_fee > 0);
            @endphp
            <div class="row" id="transaction_fee_row" style="{{ $isHasTF ? '' : 'display:none;' }}">
                <div class="d-flex border-top w-100 justify-content-between py-3">
                    <span class="" id="transaction_fee_label">
                        <strong>@lang('checkout.transaction_fee')</strong>
                        <strong id="transaction_fee_name">
                            {{ $isHasTF ? ($paymentOption->paymentOptionDesc->payment_option_name ?? '') : '' }}
                        </strong>
                        <span class="text-danger" id="transaction_fee_percentage">
                            @if($current_tf_rate > 0)
                                ({{ number_format($current_tf_rate, 2) }}%)
                            @else
                                (ฟรีค่าธรรมเนียม)
                            @endif
                        </span>
                    </span>
                    <span class="text-danger" id="transaction_fee_amount">{{ number_format($main_order->transaction_fee,2) }} @lang('common.baht')</span>
                </div>
            </div>

            <div class="row py-2 bg-light border-top mb-3">
                <div class="flex-grow-1"><strong>@lang('checkout.grand_total')</strong></div>
                <div class="">
                    <strong id="tot_order_amount">{{ number_format($main_order->total_final_price,2) }} @lang('common.baht')</strong>
                </div>
            </div>
            <div class="">
                <button type="button" class="col-12 btn btn-danger btn-checkout-mobile" id="btn_checkout">ดำเนินการชำระเงิน</button>
            </div>
        </div>
    </div>
        
</form>
@endsection

@section('footer_scripts')
    {!! CustomHelpers::combineCssJs(['js/jquery.dataTables','js/dataTables.bootstrap', 'js/user/myaccount'],'js') !!}

    <script type="text/javascript">
        // JavaScript function to format numbers with commas (similar to PHP number_format)
        
        var receive_item_url = "{{ action('User\OrderController@receiveOrdItems') }}";
        var lang_receive_item = "@lang('order.are_you_sure_want_to_receive_this_items')";
        var lang_yes = "@lang('common.yes')";
        var lang_no = "@lang('common.no')";
        var error_msg ={
            select_payment : "@lang('checkout.select_payment_method')",
            select_pickup_time:"@lang('checkout.select_pickup_time')",
            ok : "@lang('common.ok')",
            txt_no : "@lang('common.no')"
        };
        
        // Function to detect device type
        function isMobileOrTablet() {
            var userAgent = navigator.userAgent;
            
            // ตรวจสอบ device type ที่ครอบคลุม iPad ทุกประเภท
            var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Macintosh.*Mac OS X.*Mobile|Macintosh.*Mac OS X.*iPad/i.test(userAgent);
            
            // เพิ่มการตรวจสอบ iPad แบบพิเศษสำหรับ iOS เวอร์ชันใหม่
            var isIPad = /iPad|Macintosh.*Mac OS X.*Mobile|Macintosh.*Mac OS X.*iPad/i.test(userAgent);
            
            // ตรวจสอบ iPad ที่ใช้ Safari บน macOS (iPadOS)
            var isIPadSafari = userAgent.includes('Macintosh') && userAgent.includes('Safari') &&
                            (userAgent.includes('Version/14') || userAgent.includes('Version/15') ||
                                userAgent.includes('Version/16') || userAgent.includes('Version/17') ||
                                userAgent.includes('Version/18'));
            
            // รวมผลลัพธ์
            var finalResult = isMobile || isIPadSafari;
            
            return finalResult;
        }

        function calculatePayment(){
            let paymentMethod = $('input[name="payment_method"]:checked').val();
            
            if(!paymentMethod){
                showSweetAlertError('กรุณาเลือกวิธีการชำระเงิน');
                return;
            }
            let data={
                'paymentMethod':paymentMethod,
                'formattedId':"{{ $main_order->formatted_id }}"
            };
            // data = {...data,discountCode:discountCode,paymentOptionId:paymentOptionId};
            let url ="{{ action('Checkout\CartController@calculatePaymentMethod') }}";
            callAjaxRequest(url, "post", data, function(result) {
                if (result.status === 'success') {
                    const data = result.data ?? {};

                    $('#tot_order_amount').text(data.totalFinalPrice ?? 0.00);

                    // ตรวจว่ามีค่าธรรมเนียมหรือไม่
                    if (data.transactionFeeLabel && parseFloat(data.transactionFee) > 0) {

                        $('#transaction_fee_row').show();
                        $('#transaction_fee_name').text(data.transactionFeeLabel);
                        $('#transaction_fee_amount').text(`${data.transactionFee} บาท`);
                        
                        // แสดงเปอร์เซ็นต์หรือข้อความฟรี
                        if (parseFloat(data.transactionFeeRate) > 0) {
                            $('#transaction_fee_percentage').text(`(${data.transactionFeeRate}%)`);
                            $('#transaction_fee_rate').text(`(${data.transactionFeeRate}%)`);
                        } else {
                            $('#transaction_fee_percentage').text('');
                            $('#transaction_fee_rate').text('ฟรีค่าธรรมเนียม');
                        }

                    } else {
                        // ถ้าไม่มีค่าธรรมเนียม — ซ่อนทั้งหมด
                        $('#transaction_fee_row').hide();
                        $('#transaction_fee_name, #transaction_fee_percentage, #transaction_fee_amount').text('');
                    }

                } else {
                    showSweetAlertError(result.msg);
                }
            });
        }
        
        
        // Initialize on page load
        $(document).ready(function() {
            if (!isMobileOrTablet()) {
                var paymentOption = @json($payment_option);
                $('.payment-method-card').each(function() {
                    var card = $(this);
                    var paymentId = card.data('payment-id');
                    
                    // หา payment option
                    for (var i = 0; i < paymentOption.length; i++) {
                        if (paymentOption[i].id == paymentId) {
                            var payment = paymentOption[i];
                            // ถ้าเป็น Mobile Banking ให้เพิ่ม class และ tooltip
                            if (payment.slug && (payment.slug.includes('banking') || payment.slug.includes('mobile'))) {
                                card.addClass('desktop-mobile-banking');
                                card.attr('title', 'Mobile Banking สามารถชำระได้ผ่านโทรศัพท์ หรือแท็บเล็ต เท่านั้น');
                            }
                            break;
                        }
                    }
                });
            }

            // Payment method card click handler
            $(document).on('click', '.payment-method-card', function(e) {
                let card = $(this);
                let radioInput = card.find('input[type="radio"]');
                let paymentId = card.data('payment-id');
                
                let paymentOption = @json($payment_option);
                let selectedPayment = null;
                
                for (let i = 0; i < paymentOption.length; i++) {
                    if (paymentOption[i].id == paymentId) {
                        selectedPayment = paymentOption[i];
                        break;
                    }
                }
                
                if (selectedPayment && selectedPayment.slug &&
                    (selectedPayment.slug.includes('banking') || selectedPayment.slug.includes('mobile')) &&
                    !isMobileOrTablet()) {
                    
                    e.preventDefault();
                    e.stopPropagation();
                    showSweetAlertError('Mobile Banking สามารถชำระได้ผ่านโทรศัพท์ หรือแท็บเล็ต เท่านั้น');
                    return false;
                }
                $('.payment-method-card').removeClass('selected');
                card.addClass('selected');
                radioInput.prop('checked', true);
                calculatePayment();
                
            });
            
            // calculateTransactionFee();
        });

        jQuery('body').on('click', '#btn_checkout', function(e){
            var error_str = '';
            var pickup_time = $('select[name=pickup_time]').val();
            
            if(pickup_time == '' || typeof pickup_time == 'undefined'){
                $('#e_pickup_time').html(error_msg.select_pickup_time);
                error_str += '<p class="error">'+error_msg.select_pickup_time+'</p>';
            }else{
                $('#e_pickup_time').html('');
            }
            
            var payment_method = $('input[name=payment_method]:checked').val();
            if(payment_method == '' || typeof payment_method == 'undefined'){
                
                $('#e_payment_method').html(error_msg.select_payment);
                error_str += '<p class="error">'+error_msg.select_payment+'</p>';
            }else{
                $('#e_payment_method').html('');
                // Check if Mobile Banking is selected on desktop
                var selectedPaymentOption = $('input[name=payment_method]:checked');
                if (selectedPaymentOption.length > 0) {
                    var paymentOption = @json($payment_option);
                    var selectedPayment = null;
                    // Find selected payment option
                    for (var i = 0; i < paymentOption.length; i++) {
                        if (paymentOption[i].id == selectedPaymentOption.val()) {
                            selectedPayment = paymentOption[i];
                            break;
                        }
                    }
                    // Check if Mobile Banking is selected on desktop
                    if (selectedPayment && selectedPayment.slug &&
                        (selectedPayment.slug.includes('banking') || selectedPayment.slug.includes('mobile')) &&
                        !isMobileOrTablet()) {
                        
                        showSweetAlertError('Mobile Banking สามารถชำระได้ผ่านโทรศัพท์ หรือแท็บเล็ต เท่านั้น');
                        selectedPaymentOption.prop('checked', false);
                        $('.payment-method-card').removeClass('selected');
                        // hideTransactionFee();
                        return false;
                    }
                }
            }
            
            
            if(error_str != ''){
                showSweetAlertError(error_str);
                return false;
            }else{
                var formAction = $('#checkout_form').attr('action');
                var form = $('#checkout_form').serialize();

                callAjaxRequest(formAction,'post',form,function(response){
                    if(response.status == "success"){
                        window.location.href=response.url;
                    }
                    else if(response.status == "fail"){
                        if(response.validation == true){
                            var error = '';
                            $.each(response.msg, function(key,val){
                                error +='<p class="error">'+val+'</p>'
                                $('#e_'+key).html(val);
                            });
                            showSweetAlertError(error);
                        }else if(response.type=='price'){
                            $('#cart_'+response.cart_id).css("background-color","yellow");
                            $('#cart_'+response.cart_id+' li.price_li').append('<br><a href="javascript:;" class="update_cart_price text-primary">'+error_msg.update_price+'</a>')
                            showSweetAlertError(response.msg);
                        }else{
                            showSweetAlertError(response.msg);
                        }
                    }else{
                        showSweetAlertError(response.msg);
                    }
                });
            }
        });
    </script>

@endsection
