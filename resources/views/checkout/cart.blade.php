@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/flickity', 'css/jquery-editable-select.min'],'css') !!}
    <style>
        .sel-pay-method ul{ display: inherit }
        .ship-method-list i { font-size: 30px} 
        #shipTab li{
            width: 50%;
        }
        #shipTab li a.ship-method-list{
            width: 90%;
            padding: 0px
        }
        .bg-panel{
            background-color: #EFF2F4;
        }
        .d-flex > .form-control {
            min-width: 0;
        }
        .sel-pay-method.active, .payment-option-card.active{
            border-color: #007bff !important;
            background-color: #f8f9ff !important;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.2);
        }
        
        .payment-option-card {
            transition: all 0.3s ease;
            cursor: pointer;
            min-height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .payment-option-card:hover {
            border-color: #007bff !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
        }
        
        .payment-method-info {
            margin-top: 8px;
        }
        
        .payment-option-card .bank-name {
            font-size: 14px;
            margin-bottom: 4px;
        }
        
        .payment-option-card small {
            font-size: 11px;
        }
        
        .dbox-flex img{
            width: auto;
            max-height: 110px
        }
        img.prod-badge{
            height: 30px;
        }
        
        /* Transaction Fee Styling */

        #transaction_fee_label .fee-percentage {
            color: #dc3545; /* สีแดงสำหรับเปอร์เซ็นต์ */
        }
        
        /* Discount Item Styling */
        .discount-item {
            padding-left: 20px;
        }
        
        .discount-item .col-6:first-child {
            padding-left: 20px;
        }
        
        /* Ensure consistent spacing for discount items */
        .discount-item.row {
            margin-bottom: 0.5rem;
        }
        
        /* Styling for zero-value shipping discount */
        .discount-item .text-muted {
            color: #6c757d !important;
            opacity: 0.8;
        }

    </style>
    
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
@endsection

@section('header_script')
    var error_msg ={
        select_shipping : "@lang('checkout.select_shipping_method')",
        select_payment : "@lang('checkout.select_payment_method')",
        select_shipping_address : "@lang('checkout.select_shipping_address')",
        select_billing_address : "@lang('checkout.select_billing_address')",
        select_shipping_error: "@lang('checkout.select_shipping_error')",
        select_pickup_time:"@lang('checkout.select_pickup_time')",
        ok : "@lang('common.ok')",
        txt_no : "@lang('common.no')",
        update_price : "@lang('checkout.update_price')",
        server_error : "@lang('common.something_went_wrong')",
        shipping_fee : "@lang('checkout.delivery_fee')",
        discount_shipping_fee : "@lang('checkout.discount_delivery_fee')",
        currency : "@lang('common.currency')",
        enter_phone_no : "@lang('checkout.enter_phone_no')"
    };
    var baht_currency = "@lang('common.baht')";
    var delivery_time_arr = {!! json_encode($delivery_time_arr) !!};
    var checkout_type = "{{ $checkout_type }}";
    var address_form_url = "{{ action('Checkout\CartController@cartAddress') }}";
    var address_dd_url = "{{action('AjaxController@getStateCityDD')}}";
    var save_address_url = "{{action('Checkout\CartController@saveAddress')}}";
    var change_ship_address = "{{action('Checkout\CartController@changeShipAddress')}}";
    var change_bill_address = "{{action('Checkout\CartController@changeBillAddress')}}";
    var pickup_time_url = "{{ action('Checkout\CartController@pickupTime') }}" ;
    var tot_delivery_time = "{{ $delivery_details['item_pickup_time'] }}";
    var updateCartPrice = "{{ action('Checkout\CartController@updateCartPrice') }}";
    var checkCartUrl = "{{action('Checkout\CartController@checkCartExist')}}";
    var deletetemporder = "{{action('Checkout\CartController@deleteTempOrder')}}";
    let isCheckout = false;
    var activeDiscountCode = '';
    let validateCartItemsUrl = "{{ route('checkout.validateProductCartItem') }}";
    let shoppingCartUrl = "{{ action('Checkout\CartController@shoppingCart') }}";

    
@endsection

@section('content')

@php($tot_amount = collect($orderDetails)->sum('total_price'))
{{-- @php($tot_amount = 0) --}}
<div class="checkout-wrap  ">
    @if(!empty($orderInfo))

        <h1 class="page-title d-flex mb-3"><strong>ชำระเงิน</strong></h1>


        {{-- <h1 class="page-title d-flex">@lang('checkout.end_shopping_step') <a href="{{ action('Checkout\CartController@shoppingCart') }}" class="btn-grey back">@lang('common.back')</a> </h1> --}}
        
        <form id="checkout_form" method="post" action="{{ action('Checkout\\CartController@store') }}">
            <input type="hidden" name="checkout_type" value="{{ $checkout_type }}">
            <div class="row">
                <div class="panel col-12 mb-3">
                    <div class="bg-panel p-3">
                        @if($checkout_type == 'end-shopping' || $checkout_type == 'buy-now-end-shopping')
                        {{-- <div class="step-title">
                            <span class="step-num">1</span>
                            <h3>@lang('checkout.select_shipping_method')</h3>
                        </div> --}}
                        <h2><strong>1. เลือกการจัดส่ง</strong></h2>

                        <div class="ship-method ">
                            
                            {{ csrf_field() }}
                            <input type="hidden" name="order_id" value="{{ $orderInfo->formatted_order_id }}">
                            <div class="col-sm-8 col-md-6">
                                <ul class="nav d-flex" id="shipTab">
                                    <li>
                                        <a class="ship-method-list py-4 active" data-toggle="tab" href="#select-address" id="delivery_at_the_address">
                                            <input type="radio" value="3" name="ship_method" id="ship-address" class="d-none" checked>
                                            <i class="fas fa-truck"></i> <span>@lang('checkout.delivery_at_the_address')</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="ship-method-list py-4" data-toggle="tab" href="#pick_up_center" id="pick_up_at_center">
                                            <input type="radio" value="1" name="ship_method" id="ship-center" class="d-none">
                                            <i class="fas fa-cubes"></i> <span>@lang('checkout.pick_up_at_center')</span>
                                        </a>
                                    </li>
                                    {{--
                                    <li>
                                        <a class="ship-method-list" data-toggle="tab" href="#shop_address" id="pick_up_at_the_store">
                                            <input type="radio" value="2" name="ship_method" id="ship-store">
                                            <i class="fas fa-warehouse"></i> <span>@lang('checkout.pick_up_at_the_store')</span>
                                        </a>
                                    </li>
                                    --}}

                                </ul>
                                <p id="e_ship_method" class="error"></p>
                            </div>
                            <div class="tab-content">
                                <div class="tab-pane active" id="select-address">
                                    {{-- <div class="step-title">
                                        <span class="step-num">2</span>
                                        <h3>@lang('checkout.select_address')</h3>
                                    </div> --}}
                                    <div class="row">
                                        <div class="col-md-6 mb-3 ">
                                            <div class="form-group">
                                                <label for="">@lang('checkout.select_shipping_address')<i class="red">*</i></label>
                                                <div class="block-add-address">
                                                    <select class="selectpicker" name="ship_address" id="dd_shipping">
                                                        <option value="">@lang('checkout.select_address')</option>
                                                        @if(count($user_address))
                                                            @foreach($user_address as $skey => $sval)
                                                                <option value="{{ $sval->id}}" @if($shipping_address && $shipping_address->id == $sval->id) selected="selected" @endif>{{ $sval->title }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    <a href="javascript:void(0);" class="btn-grey add_address">+@lang('checkout.add_new_address')</a>
                                                </div>
                                                <p class="error" id="e_ship_address"></p>
                                            </div>
                                            <address class="post-address" id="shipping_address">
                                            @if($shipping_address)
                                                <table>
                                                    <tr>
                                                        <td class="d-flex pt-2"><ion-icon name="location-outline" class="mr-2"></ion-icon></td>
                                                        <td>
                                                            @if(!empty($billing_address->company_name))
                                                                <p>{{ ($billing_address->company_name ?? '') . ' ' . ($billing_address->branch ?? '') }}</p>
                                                                <p>{{ $billing_address->company_address ?? '' }}</p>
                                                                <p>TAX ID : {{ $billing_address->tax_id ?? '' }}</p>
                                                            @else
                                                                <p>{{ ($billing_address->first_name ?? '') . ' ' . ($billing_address->last_name ?? '') }}</p>
                                                                <p>{{ ($billing_address->address ?? '') . ', ' . ($billing_address->road ?? '') }}</p>
                                                                <p>{{ ($billing_address->city_district ?? '') . ', ' . ($billing_address->province_state ?? '') . ', ' . ($billing_address->zip_code ?? '') }}</p>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><ion-icon name="call-outline" class="mr-2"></ion-icon></td>
                                                        <td><p>{{$billing_address->ph_number??''}}</p></td>
                                                    </tr>
                                                </table>
                                            @endif
                                            </address>
                                        </div>
                                        <div class="col-md-6 mb-3" id="block_bill_address">
                                            <div class="form-group">
                                                <label for="">@lang('checkout.select_billing_address')<i class="red">*</i></label>
                                                <div class="block-add-address">
                                                    <select class="selectpicker" name="bill_address" id="dd_billing">
                                                        <option value="">@lang('checkout.select_billing_address')</option>
                                                        @if(count($user_address))
                                                            @foreach($user_address as $bkey => $bval)
                                                                <option value="{{ $bval->id}}"  @if($billing_address && $billing_address->id == $bval->id) selected="selected" @endif>{{ $bval->title }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                    <a href="javascript:void(0);" class="btn-grey add_address">+@lang('checkout.add_new_address')</a>
                                                </div>
                                                <p class="error" id="e_ship_address"></p>
                                            </div>
                                            <address class="post-address" id="billing_address">
                                            @if($billing_address)
                                                
                                                <table>
                                                    <tr>
                                                        <td class="d-flex pt-2"><ion-icon name="location-outline" class="mr-2"></ion-icon> </td>
                                                        <td>
                                                            @if(!empty($billing_address->company_name))
                                                                <p>{{ ($billing_address->company_name ?? '') . ' ' . ($billing_address->branch ?? '') }}</p>
                                                                <p>{{ $billing_address->company_address ?? '' }}</p>
                                                                <p>TAX ID : {{ $billing_address->tax_id ?? '' }}</p>
                                                            @else
                                                                <p>{{ ($billing_address->first_name ?? '') . ' ' . ($billing_address->last_name ?? '') }}</p>
                                                                <p>{{ ($billing_address->address ?? '') . ', ' . ($billing_address->road ?? '') }}</p>
                                                                <p>{{ ($billing_address->city_district ?? '') . ', ' . ($billing_address->province_state ?? '') . ', ' . ($billing_address->zip_code ?? '') }}</p>
                                                            @endif

                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td><ion-icon name="call-outline" class="mr-2"></ion-icon></td>
                                                        <td><p>{{$billing_address->ph_number??''}}</p></td>
                                                    </tr>
                                                </table>
                                            @endif
                                            </address>
                                        </div>
                                        
                                    </div>
                                </div>
                                <div class="tab-pane col" id="pick_up_center">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="form-group">
                                                <div class="ship-info">
                                                @if($pickup_center_address)
                                                    {{-- @if($delivery_details['item_pickup_time'])
                                                         <p >@lang('checkout.can_pick_up_the_product_within') {{ $delivery_details['item_pickup_time'] }} @lang('checkout.hours')</p>
                                                    @endif --}}
                                                    <p>{{ $pickup_center_address['name']??'' }}</p>
                                                    <address>
                                                        <ion-icon name="location-outline" class="mr-2"></ion-icon>
                                                        {{ $pickup_center_address['location']??'' }} <br/>
                                                        <a href="tel:{{ $pickup_center_address['contact']??'' }}"><ion-icon name="call-outline" class="mr-2"></ion-icon>{{ $pickup_center_address['contact']??'' }}</a>
                                                    </address>
                                                @endif
                                                </div>
                                             </div>
                                        </div>
                                    </div>
                                
                                </div>
                            </div>
                            <div class="row" id="user_phone_no_div" style="display: none;">
                                <div class="col-sm-4">
                                    <label for="">@lang('checkout.phone_no')</label>
                                    <input type="text" name="phone_no" id="phone_no">
                                    <p class="error" id="e_phone_no"></p>
                                    
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <label for="">@lang('checkout.pickup_time')<i class="red">*</i></label>
                                    <select class="" name="pickup_time" id="pickup_time">
                                        <option value="">@lang('checkout.select_pickup_time')</option>
                                        @foreach($time_arr as $val)
                                            <option value="{{ $val }}">{{ (strrpos($val,'_n')!==false) ? str_replace('_n','',$val).':00 '. (date('d')+1).' '.date('M'):$val.':00' }} </option>
                                        @endforeach
                                    </select>
                                    <p class="error" id="e_pickup_time"></p>
                                    <input type="hidden" name="nexday" value="{{ $delivery_details['tomorrow'] }}">
                                </div>
                                
                            </div>
                        </div>
                        @endif

                    </div>
                </div>

                <div class="panel col-12 mb-3">
                    
                    <div class="bg-panel p-3">
                        <div class="table-responsive checkout-order-table cartpage-tbl">
                            @if(!empty($orderInfo) && count($orderDetails))
                                <h2><strong>รายการสั่งซื้อแล้ว</strong></h2>
                                @php($totqty = 0)
                                @php($shopId = null)
                                
                                @foreach($orderDetails as $cartKey => $cartVal)
                                    @php($totqty = $totqty + $cartVal->quantity)

                                    @if ($cartVal->shop_id != $shopId || $loop->first)
                                    <div class="bg-white p-2 border-bottom border-light-2 mt-3">
                                        <h3>{{ $cartVal->getShopDesc->shop_name??'' }}</h3>
                                    </div>
                                    @endif
                                    
                                    <div id="cart_{{ $cartVal->id }}" data-item-id="{{ $cartVal->id }}" class="cartItem row m-0 bg-white p-3 border-bottom border-light " >
                                        <div class="col-sm-12 col-md-8 col mb-2 d-sm-flex">
                                            <div class="dbox-flex mr-3 mb-2">
                                                <a class="d-flex align-items-center" href="{{ action('ProductDetailController@display',[$cartVal->getCat->url,$cartVal->getPrd->sku])}}">
                                                    <img src="{{ getProductImageUrlRunTime($cartVal->getPrd->thumbnail_image,'thumb') }}"   alt="" class=" h-100">
                                                </a>
                                            </div>
                                            
                                            <div class="flex-sm-grow-1">
                                                <h3 class="prod-name mb-2">
                                                    <strong><a href="{{ action('ProductDetailController@display',[$cartVal->getCat->url,$cartVal->getPrd->sku])}}">{{ $cartVal->getCatDesc->category_name??'' }}</a></strong>
                                                </h3>
                                                <div class="mb-2">
                                                    <img src="{{ getBadgeImage($cartVal->getPrd->badge_id) }}" alt="Badge" class="prod-badge" />
                                                </div>
                                                <h4 class="d-flex">
                                                    {{  ($cartVal->getPrd->weight_per_unit??null) }}
                                                    {{ $cartVal->getPrd->base_unit_id?getUnitName($cartVal->getPrd->base_unit_id):null}} /
                                                    {{ $cartVal->getPrd->package_id?getPackageName($cartVal->getPrd->package_id):null }}
                                                </h4>
                                                <h4 class="d-flex justify-content-start align-items-center">
                                                    <span class="prd-unit-price">{{ number_format($cartVal->cart_price  ?? 0,2) }} </span>
                                                    <span>&nbsp;@lang('common.baht') / {{ $cartVal->getPrd->package_id?getPackageName($cartVal->getPrd->package_id):null }} </span>
                                                </h4>
                                            </div>
                                        </div>
                                        <div class=" col-sm-12 col-md-4 text-right px-0 mt-auto mb-auto ">
                                            <div class="">
                                                <h3 class="">จำนวน {{ $cartVal->quantity }} {{ getPackageName($cartVal->getPrd->package_id) }}</h3>
                                            </div>
                                            <div class="">
                                                <h3 class="font-weight-bold prd-total-price text-danger mr-1">{{number_format($cartVal->total_price,2) }} @lang('common.baht')</h3>
                                            </div>
                                        </div>
                                    </div>

                                    @php( $shopId = $cartVal->shop_id != $shopId ?$cartVal->shop_id : $shopId )

                                @endforeach
                                        
                            @endif
                            
                            {{-- @if(!empty($main_order) && count($paid_product))
                                <h2>@lang('checkout.already_paid')</h2>
                                <div class="table">
                                    <div class="table-header">
                                        <ul>
                                            <li class="sel-tbl">@lang('checkout.seller')</li>
                                            <li class="goods-tbl">@lang('checkout.product')</li>
                                            <li class="unit-tbl">@lang('checkout.unit_price')</li>
                                            <li class="num-tbl">@lang('checkout.qty')</li>
                                            <li class="total-tbl">@lang('checkout.price')</li>
                                            <li class="paymethod-tbl">@lang('checkout.payment_method')</li>
                                        </ul>
                                    </div>
                                    <div class="table-content">
                                        @php($totqty = 0)
                                        @foreach($paid_product as $key => $val)
                                            @php($totqty = $totqty + $val->quantity)
                                            @php($detail_json = jsonDecodeArr($val->order_detail_json))
                                            @php($shop_url = action('ShopController@index',$detail_json['shop_url'] ??''))
                                            @php($prd_url = action('ProductDetailController@display',[$detail_json['cat_url']??'',$val->sku]))      

                                            <ul>
                                                <li class="product-shop sel-tbl">
                                                    <a href="{{ $shop_url }}">
                                                    <span class="prod-img"><img src="{{getImgUrl($detail_json['logo']??'','logo')}}" width="50" height="50" alt=""></span>
                                                    <span class="shopname"><a href="{{ $shop_url }}">{{ $detail_json['shop_name'][session('default_lang')]??'' }}</a></span>
                                                    </a>
                                                </li>
                                                <li class="product goods-tbl">
                                                    <div class="dbox-flex">
                                                        <div class="dbox-flex">
                                                            
                                                            <a href="{{ $prd_url }}">
                                                            <span class="prod-img prod-134"><img src="{{ getProductImageUrlRunTime($detail_json['thumbnail_image']??'','thumb') }}" width="134" height="100" alt=""></span> </a>
                                                        </div>

                                                        <div class="ml-2">
                                                            <span class="prod-name d-block mb-2"><a href="{{ $prd_url }}">{{ $detail_json['name'][session('default_lang')]??$val->category_name }}</a></span>
                                                            <span class="la"><img src="{{ getBadgeImageUrl($detail_json['badge']['icon'] ?? '' )}}" width="30" alt=""></span>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="unit-tbl">{{convert_string($val->last_price) }} @lang('common.baht') /{{ $detail_json['package'][session('default_lang')] ?? $val->package_name }}</li>
                                                <li class="add-rem-qty num-tbl">
                                                    {{ $val->quantity }} {{ $detail_json['package'][session('default_lang')] ?? $val->package_name }}
                                                </li>
                                                <li class="total-tbl">
                                                    {{convert_string($val->total_price) }} @lang('common.baht')
                                                </li>
                                                <li class="paymethod-tbl">{{$detail_json['payment_method'][session('default_lang')] ?? str_replace('_',' ',strtoupper($val->payment_slug)) }}</li>
                                            </ul>
                                        @endforeach
                                        
                                    </div>
                                </div>
                            @endif --}}
                            
                        </div>
                    </div>

                </div>

                <div class="panel col-12 mb-3">
                    <div class="bg-panel p-3">
                        <input type="hidden" name="check_pay_method" id="check_pay_method" value="{{ ($tot_amount > 0) ? 1 : 0}}">
                        <input type="hidden" name="transaction_fee" id="transaction_fee" value="0">
                        <div id="payment_method_div" @if($tot_amount <= 0) style="display: none;" @endif>
                            <h2><strong>2. วิธีการชำระเงิน</strong></h2>
                            <div class="row">
                                {{-- <ul> --}}
                                    @if(count($payment_option))
                                        @foreach($payment_option as $pkey => $pval)
                                            
                                            <div class="col-6 col-sm-6 col-md-6 col-lg-3 mb-3">
                                                <div class="sel-pay-method p-3 bg-white border payment-option-card h-100 d-flex flex-column" data-slug="{{ $pval->slug }}" style="border-radius: 10px;">
                                                    <a href="javascript:void(0)" class="h-100 d-flex flex-column">
                                                        <label for="bank_{{$pkey}}" class="m-0 w-100 h-100 d-flex flex-column">
                                                            @if($pval->slug!='odd' ||($pval->slug=='odd' && !empty($user_odd_info) && $user_odd_info->espa_id!=''))
                                                                <input type="radio" name="payment_method" id="" value="{{ $pval->id }}" class="d-none" >
                                                            @else
                                                                <input type="radio" name="" id="odd_radio" value="{{ $pval->id }}" class="d-none" >
                                                            @endif
                                                            
                                                            <div class="text-center flex-grow-1 d-flex flex-column justify-content-center">
                                                                {{-- แสดงไอคอนตาม payment method --}}
                                                                <div class="payment-method-icon flex-grow-1 d-flex align-items-center justify-content-center">
                                                                    @if(count(getMultiplePayImgUrls($pval->image_name)) == 1)
                                                                        <img src="{{ getMultiplePayImgUrls($pval->image_name)[0] }}" alt="" width="56px" style="border-radius: 10px;">
                                                                    @elseif(count(getMultiplePayImgUrls($pval->image_name)) >= 2)
                                                                        <div class="d-flex justify-content-center align-items-center" style="gap: 8px;">
                                                                            @foreach(getMultiplePayImgUrls($pval->image_name) as $img_url)
                                                                                <img src="{{ $img_url }}" alt="" width="48px" style="max-height: 48px; object-fit: contain; border-radius: 10px;">
                                                                            @endforeach
                                                                        </div>
                                                                    @else
                                                                        <div class="d-flex justify-content-center align-items-center" style="gap: 6px;">
                                                                            @foreach(getMultiplePayImgUrls($pval->image_name) as $img_url)
                                                                                <img src="{{ $img_url }}" alt="" width="40px" style="max-height: 40px; object-fit: contain; border-radius: 10px;">
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                
                                                                <div class="payment-method-info">
                                                                    <div class="bank-name font-weight-bold">
                                                                        {{ $pval->paymentOptionDesc->payment_option_name??'' }}
                                                                    </div>
                                                                    
                                                                    {{-- แสดงข้อมูลค่าธรรมเนียมจาก relation --}}
                                                                    @if($pval->transactionFeeConfig)
                                                                        <div class="mt-1">
                                                                            @if($pval->transactionFeeConfig->current_tf > 0)
                                                                                <small class="text-danger">{{ $pval->transactionFeeConfig->message ?? 'ค่าธรรมเนียม' }} </small>
                                                                                <small class="text-danger">{{ number_format($pval->transactionFeeConfig->current_tf, 2) }}%</small>
                                                                            @else
                                                                                <small class="text-success">ฟรีค่าธรรมเนียม</small>
                                                                            @endif
                                                                        </div>
                                                                    @else
                                                                        {{-- แสดงข้อความฟรีค่าธรรมเนียมสำหรับวิธีการชำระที่ไม่มีค่าธรรมเนียม --}}
                                                                        <div class="mt-1">
                                                                            <small class="text-success">ฟรีค่าธรรมเนียม</small>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </a>
                                                </div>
                                            </div>
                                            
                                        @endforeach
                                    @endif
                                {{-- </ul> --}}
                                <p id="e_payment_method" class="error"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel col-12 mb-3">
                    @if($checkout_type == 'buy-now' || $checkout_type == 'buy-now-end-shopping')
                    <div class="bg-panel p-3  mb-3 d-flex justify-content-end">
                        <div class="col-sm-12 col-md-12 col-lg-6">
                            <h2><strong>โค้ด@lang('checkout.code_discount')</strong></h2>
                            <div class=" card py-2" >
                                <div id="discount_code_input">
                                    <div class="d-flex justify-content-between align-items-center" >
                                        <input type="text" class="form-control rounded-right flex-grow-1" name="discount_code" id="discount_code"
                                        placeholder="กรอกโค้ดส่วนลด" aria-label="โค้ดส่วนลด" aria-describedby="btn_discount_code" value="">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary rounded-left" type="button" id="btn_discount_code">ตกลง</button>
                                        </div>
                                    </div>
                                </div>
                                <div id="discount_code_show" class="d-none">
                                    <div class="d-flex justify-content-between align-items-center " >
                                        <span id="discount_code_txt"></span>
                                        <button type="button" class="btn btn-danger btn-sm" id="btn_discount_code_cancel">ยกเลิกโค้ด</button>
                                    </div>
                                </div>
                            </div>
                                
                            <div class="checkout-table-footer clearfix">
                                    
                                <div class="col float-right">
                                    
                                    <div class="row">
                                        <span class="col-6"><strong>@lang('checkout.total')</strong></span>
                                        <span class="col-6">{{ number_format($tot_amount, 2) }} @lang('common.baht')</span>
                                    </div>
                                    <div class="row " id="dcc_purchase"> </div>
                                    <div class="row " id="delvery_fee_div"></div>
                                    <div class="row " id="dcc_shipping"></div>
                                    {{-- แสดงค่าธรรมเนียมเมื่อเลือก Beam payment method --}}
                                    <div class="row " id="transaction_fee_row"></div>
                                    <div class="row border-top pt-1">
                                        <div class="col-12">
                                            <div class="bg-light rounded py-1" style="margin-top: 2px; margin-left: -6px; margin-right: -6px;">
                                                <div class="row" style="margin-left: 0; margin-right: 0;">
                                                    <span class="col-6" style="padding-left: 6px;"><strong>@lang('checkout.grand_total')</strong></span>
                                                    <span class="col-6" style="padding-right: 6px; text-align: right;"><strong id="tot_order_amount">{{number_format($tot_amount, 2) }}</strong></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class=" notification_text">
                                        {!! getStaticBlock('before-checkout-notifiction') !!}
                                    </div>
                                    <div class=" notification_text pt-0">
                                        <span>โดยการคลิก "สั่งสินค้า" ฉันได้อ่านและยอมรับเงื่อนไขการให้บริการ </span><span class="red">สี่มุมเมืองออนไลน์</span><span class="blue"> นโยบายการคืนเงินและคืนสินค้า</span>
                                    </div>
                                    
                                    <div class="row">
                                        <button type="button" class="col-12 btn" id="btn_checkout">@lang('checkout.confirm_order_to_end_shopping')</button>
                                    </div>
                                    
                                </div>
                            </div>

                        </div>
                    </div>
                    @endif
                </div>
                            
            </div>
        </form>
    @else
        <div> No record found </div>
    @endif
</div>
@endsection

@section('footer_scripts')

{!! CustomHelpers::combineCssJs(['js/jquery-ui.min', 'js/jquery-editable-select.min', 'js/cart/cart', 'js/user/user_address'],'js') !!}
<script type="text/javascript">
    $('#odd_radio').click(function(e){
        swal({
            title : "@lang('checkout.are_you_sure_want_to_register_odd')",
            text : "@lang('checkout.you_have_not_register_odd')",
            type : 'warning',
            confirmButtonText:lang_yes,
            cancelButtonText:lang_cancel,
            showCloseButton : true,
            showConfirmButton : true,
            showCancelButton: true,
        }).then(res=>{
            window.location.href = "{{action('User\ODDController@oddCondition')}}";
            
        }, rej=>{
            // console.log;
        });
    });

    let purchase = {{$tot_amount}};

    const submitDiscountCode = () => {
        let code = ($('#discount_code').val() || '').trim();
        let shippingCost = $('[name="shipping_fee_val"]').val() || 0;

        if(code.length >= 6){
            checkDiscountCode(code,purchase,shippingCost);
        }else{
            // Store current shipping method state before showing error
            let currentShippingMethod = $('[name="ship_method"]:checked').val();
            let currentActiveTab = $('.ship-method-list.active').attr('id');
            
            swal({
                title: "จำกัดการใช้โค้ดส่วนลด 6 ตัวอักษร",
                type: 'error',
                confirmButtonText: 'ตกลง',
                showCloseButton : true,
                showConfirmButton : true,
                showCancelButton: false,
            }).then(() => {
                let shipId = $('#dd_shipping').val();
                getDeliveryFee(shipId);
                showHideLoader('showLoader');
                // window.location.href = window.location.pathname;
            });
        }
    };
            
    $(document).ready(function(){

        $('#btn_discount_code').on('click',function(){
           submitDiscountCode();
        });

        $('[name="discount_code"]').on('keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                submitDiscountCode();
            }
        });

        $('#btn_discount_code_cancel').on('click',function(){
            $('#discount_code').val('');
            activeDiscountCode = '';
            checkDiscountCode(null,purchase,0);
        });

        $(document).on('click', '.payment-option-card', function(e) {
            let isMobile = isMobileOrTablet();
            let paymentSlug = $(this).data('slug') || '';
            let shipId = $('#dd_shipping').val();
          
            if (!isMobile && paymentSlug.indexOf('beam-banking') !== -1) {
                e.preventDefault();
                e.stopPropagation();
                
                $('input[name="payment_method"]').prop('checked', false);
                $('.payment-option-card').removeClass('active');
               showSweetAlertError('Mobile Banking สามารถชำระได้ผ่านโทรศัพท์ หรือแท็บเล็ต เท่านั้น');
            }
            getDeliveryFee(shipId);
        });

    });

    function checkDiscountCode(code,purchase,shippingCost){
        
        let shipMethod = $('[name="ship_method"]:checked').val();
        let shipId = $('#dd_shipping').val();
        if(shipMethod==='1'){
            shipId = undefined;
        }
        
        // Store current shipping method state
        let currentShippingMethod = shipMethod;
        let currentActiveTab = $('.ship-method-list.active').attr('id');
        
        if(code){
            showHideLoader('showLoader');
            $.ajax({
                url: '{{ route('discount_code.check-usable') }}',
                type:'POST',
                headers : {'X-CSRF-TOKEN' : window.Laravel.csrfToken, '_token' : window.Laravel.csrfToken},
                data: {
                    code:code,
                    purchase:purchase,
                    shippingCost:shippingCost
                },
                success:function(result){
                    
                    if(result.status == 'success'){
                        const data = result.data;
                        let message = "คุณได้รับส่วนลด" +
                            (data?.discountPurchase > 0 ? ` ${data.discountPurchase} บาท` : '') +
                            (data?.discountShipping > 0 ? ` ค่าจัดส่ง ${data.discountShipping} บาท` : '');
                        swal({
                            title: message,
                            type: 'success',
                            confirmButtonText: error_msg.ok? error_msg.ok : 'ตกลง',
                            showCloseButton: true,
                            showConfirmButton: true,
                            showCancelButton: false,
                        }).then(() => {
                            // window.location.href = window.location.pathname + '?code=' + encodeURIComponent(code);
                            activeDiscountCode = code;
                            getDeliveryFee(shipId);
                            $('#discount_code_show').removeClass('d-none');
                            $('#discount_code_txt').text(code);
                            $('#discount_code_input').addClass('d-none');
                            showHideLoader('showLoader');
                        });
                    }else{
                        $('#discount_code').val('');
                        swal({
                            title: result?.message,
                            type: 'error',
                            confirmButtonText: error_msg.ok? error_msg.ok : 'ตกลง',
                            showCloseButton : true,
                            showConfirmButton : true,
                            showCancelButton: false,
                        }).then(() => {
                            getDeliveryFee(shipId);
                            showHideLoader('showLoader');
                            window.location.href = window.location.pathname;
                        });
                    }
                },
                error:function(xhr, status, error){
                    $('#discount_code').val('');
                    if(xhr.status == 422){
                    }else{
                        $('#discount_code').val('');
                        swal({
                            title: 'โค้ดส่วนลดไม่ถูกต้อง',
                            type: 'error',
                            confirmButtonText: error_msg.ok? error_msg.ok : 'ตกลง',
                            showCloseButton : true,
                            showConfirmButton : true,
                            showCancelButton: false,
                        });
                    }
                },
                complete: function(xhr, status) {
                    showHideLoader('hideLoader');
                }
            });
        }else{
            getDeliveryFee(shipId);
            $('#discount_code_input').removeClass('d-none');
            $('#discount_code').val('');
            $('#discount_code_show').addClass('d-none');
            $('#dcc_purchase').html('');
            $('#dcc_shipping').html('');
        }
    }

    // ฟังก์ชัน helper สำหรับตรวจสอบ device type ที่ครอบคลุม iPad ทุกประเภท
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
    
    // ฟังก์ชันตรวจสอบ device type และซ่อน/แสดง Mobile Banking
    function checkDeviceAndHideMobileBanking() {
        var isMobile = isMobileOrTablet();
        
        if (!isMobile) {
            // ถ้าเป็น desktop ให้ซ่อน Mobile Banking
            $('.mobile-banking-only').hide();
        } else {
            // ถ้าเป็น mobile หรือ iPad ให้แสดง Mobile Banking
            $('.mobile-banking-only').show();
        }
    }

</script>


@stop