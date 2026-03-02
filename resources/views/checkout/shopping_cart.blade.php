@extends('layouts.app')

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/flickity'],'css') !!}
<style>
    .card-header{ background-color: #EFF2F4}
    .checkout-table-footer{ background-color: #EFF2F4}
    input[type="checkbox"] {  transform: scale(1.5); }

    /* .fixed-top {
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 999;
        left: inherit;
    } */

    .fixed-top {
        left: inherit;
        /* box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); */
        border: 1px solid #dee2e6;
        padding: 10px 15px;
        z-index: 10;
        width: auto;
        /* margin-left: -30px;
        margin-right: -30px; */
    }
    
    .dbox-flex img{
        width: auto;
        max-height: 110px
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
        opacity: 0.6; /* ทำให้ดูจางลง */
        user-select: none; /* กันการ select ข้อความ */
    }

    /* .disabled-cart h3,
    .disabled-cart h4,
    .disabled-cart h5,
    .disabled-cart span,
    .disabled-cart td,
    .disabled-cart strong,
    .disabled-cart a {
        color: #6c757d !important;
    } */

    .disabled-cart input,
    .disabled-cart button,
    .disabled-cart select {
        background-color: #e9ecef !important;
        color: #6c757d !important;
        pointer-events: none;
    }
    .disabled-cart .spiner {
        pointer-events: none; /* ปิดการคลิก interaction */
    }
    .disabled-cart a.cart-remove {
        opacity: 1;
        color: inherit!important;
    }
    #checkout-summary{
        z-index: 0 !important;
    }
    @media (max-width: 575.98px) {
        .prod-image {
            /* width: 70px;
            height: 50px; */
        }
        .px-3{
            padding-left: 0.8rem !important;
            padding-right: 0.8rem !important;
        }
        .p-3{
            padding: 0.8rem !important;
        }
        h3{ font-size: 1rem; }
        h4{ font-size: 0.8rem; }
        
        .tab-content , .tab-content h3 , .tab-content button,.tab-content .dropdown-menu{
            font-size: 90%;
        }
        .spiner{
            height: 30px;
            border-radius: 7px;
        }
        .spiner input[type="number"], .spiner .spinNum{
            width: 50px;
            line-height: 25px
        }
        .spiner .decrease, .spiner .increase{
            font-size: 10px;
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
    var error_msg ={
        txt_delete_confirm : "@lang('common.are_you_sure_to_delete_this_record')",
        yes_delete_it : "@lang('common.yes_delete_it')",
        txt_no : "@lang('common.no')",
        server_error : "@lang('common.something_went_wrong')",
        buynow_ckeck : "@lang('checkout.please_select_product')",
        max_quantity : "@lang('checkout.please_enter_quantity_less_or_equal')",
        //quantity_blank_zero : "จำนวนสินค้าต้องไม่น้อยกว่า 1 หรือ ไม่เท่า 0",
        buynow_title : "@lang('checkout.do_you_want_to_end_shopping_or_pay_only_for_the_products')",
        end_shopping : "@lang('checkout.end_shopping')",
        buynow : "@lang('checkout.buy_now')",
        update_price : "@lang('checkout.update_price')",
        pay_cerdit : "@lang('checkout.are_you_sure_want_to_pay')",
    };
    var removeCart = "{{action('Checkout\CartController@removeCart')}}";
    var removeOrder = "{{action('Checkout\CartController@removeOrder')}}";
    var updateCart = "{{action('Checkout\CartController@updateCart')}}";
    var payProduct = "{{action('Checkout\CartController@payProduct')}}";
    var addProductToBargain = "{{action('PopUpController@getCheckBargainPopUp')}}";
    var updateCartPrice = "{{ action('Checkout\CartController@updateCartPrice') }}";
    var change_ship_address = "{{action('Checkout\CartController@changeShipAddress')}}";
    var selectedCartItemUrl = "{{ route('checkout.selectedCartItem') }}";
    var selectedMultiCartItemUrl = "{{ route('checkout.selectedMultiCartItem') }}";
    var validateCartItemsUrl = "{{ route('checkout.validateProductCartItem') }}";
    var removeMultiCartUrl = "{{route('checkout.removeMultiCart')}}";
    

@endsection

@section('content')
    
<div class=" bg-white">
    <div class="row">
        <div class="col-sm-12">
            {{-- @include('includes.buyer_shopping_tab',['purchased_products'=>$pur_prds_in_shop_list,'list_of_shopping_list'=>$total_prds_in_shop_list]) --}}

            <div class="tab-content px-3">
                <div class="tab-pane p-3 active" id="tab-seler5">
                    <form action="{{ action('Checkout\CartController@index') }}" id="end_shopping_form" class="row" method="get">
                        @csrf
                        @if(!empty($orderInfo))
                            <div class="col-lg-8">
                                <div class="row mb-2 justify-content-between align-items-center bg-white sticky-top">
                                    <div class="form-check pt-2">
                                        <input class="form-check-input" type="checkbox" name="allProductItem" id="allProductItem" {{ !empty($orderDetails) && $orderDetails->every(fn($item) => $item->is_selected) ? 'checked' : '' }} >
                                        <label class="form-check-label" for="allProductItem">&nbsp; เลือกทั้งหมด</label>

                                        <span> (<span id="selectedCount">{{ $orderDetails->where('is_selected', true)->count() }}</span>/<span id="totalCount">{{ $orderDetails->count() }}</span>) </span>
                                    </div>
                                    <button class="btn btn-secondary dropdown-toggle btn-sm px-2 pr-5" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        @switch(request('sort'))
                                            @case('asc') {{ __('checkout.sort_by_latest') }} @break
                                            @case('desc') {{ __('checkout.sort_by_oldest') }} @break
                                            @default {{ __('checkout.sort_by_default') }}
                                        @endswitch
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'asc']) }}">@lang('checkout.sort_by_latest')</a>
                                        <a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'desc']) }}">@lang('checkout.sort_by_oldest')</a>
                                    </div>
                                </div>
                                @php
                                    $itemShortStock = $orderDetails->where('is_short_stock', true);
                                @endphp
                                @if ($itemShortStock->isNotEmpty())
                                    
                                <div class="text-danger mb-3">
                                @foreach ($itemShortStock??[] as $item)
                                    <span class="text-danger small">
                                        *{{ $item->getCatDesc->category_name??'' }} : จำนวนสั่งซื้อขั้นต่ำ {{ number_format(($item->getPrd->min_order_qty ?? 0)) }} {{ $item->getPrd->package_id?getPackageName($item->getPrd->package_id):null }}
                                        สามารถซื้อได้ {{ number_format(($item->getPrd->quantity ?? 0)) }} {{ $item->getPrd->package_id?getPackageName($item->getPrd->package_id):null }}
                                    </span>
                                                        
                                @endforeach
                                </div>
                                @endif

                                @if (!empty($orderDetails) && count($orderDetails) > 0)
                                    
                                @php
                                    $totPrice = 0;
                                @endphp
                                @foreach($orderDetails as $cartKey => $cartVal)
                                    @php
                                        $hasChangePrice = $cartVal->cart_price != $cartVal->getPrd->unit_price;
                                        $totPrice += $hasChangePrice ? $cartVal->getPrd->unit_price*$cartVal->quantity : $cartVal->total_price;
                                        $package_name = optional($cartVal->getPrd->package)->title??'สินค้าไม่พบ';
                                        // $stockNotEnough = $cartVal->getPrd->quantity < $cartVal->getPrd->min_order_qty;
                                        // if ($hasChangePrice || $stockNotEnough) {
                                        //     $changeProduct[] = $cartVal;
                                        // }
                                    @endphp

                                    <div id="cart_{{ $cartVal->id }}" class="cart_shop_item cart_item row {{$loop->last?'':' '}} border p-3 mb-3" >
                                        <div class="col-12 p-0 d-flex justify-content-between align-items-center mb-3">
                                            <h3>{{ $cartVal->getShopDesc->shop_name??'' }}</h3>
                                            @if ($hasChangePrice)
                                                <h5 class="text-danger bold mb-0">@lang('checkout.product_price_changed')</h5>
                                            @endif
                                        </div>
                                        <div class=" col-sm-12 col-md-8 col mb-2 d-sm-flex">
                                            <div class="mr-3 mb-2 d-flex justify-content-start align-items-start">
                                                <input type="checkbox" name="cartItem[]" value="{{ $cartVal->id }}" class="{{ isset($cartVal->is_short_stock) && $cartVal->is_short_stock == true? 'unselectable' :'' }} mr-3 float-left cartItem" {{ $cartVal->is_selected?'checked="checked"':'' }}>
                                                <a class="d-flex align-items-center " href="{{ action('ProductDetailController@display',[$cartVal->getCat->url,$cartVal->getPrd->sku])}}">
                                                    <div class="prod-image" style="
                                                        background-image: url('{{ getProductImageUrlRunTime($cartVal->getPrd->thumbnail_image,'thumb') }}');
                                                    "></div>
                                                </a>
                                            </div>
                                            <div class="flex-sm-grow-1">
                                                <div>
                                                    <h3 class="prod-name mb-1">
                                                        <strong><a href="{{ action('ProductDetailController@display',[$cartVal->getCat->url,$cartVal->getPrd->sku])}}">{{ $cartVal->getCatDesc->category_name??'' }}</a></strong>
                                                    </h3>
                                                    <div class="mb-1">
                                                        <img src="{{ getBadgeImage($cartVal->getPrd->badge_id) }}" height="40" alt="Badge" class="prod-badge" />
                                                    </div>
                                                </div>
                                                <div class="">
                                                    <h4 class="mb-1 ">
                                                        {{ number_format($cartVal->getPrd->weight_per_unit??null, 2) }}
                                                        {{ $cartVal->getPrd->base_unit_id?getUnitName($cartVal->getPrd->base_unit_id):null}} /
                                                        {{ $cartVal->getPrd->package_id?getPackageName($cartVal->getPrd->package_id):null }}
                                                    </h4>
                                                        
                                                    <h4 class="mb-1">
                                                        {{-- @if ($hasChangePrice)
                                                            <del class="text-muted">{{ number_format($cartVal->cart_price ?? 0, 2) }}</del>
                                                        @endif --}}

                                                        <span class="prd-unit-price">
                                                            {{ $hasChangePrice ?
                                                            number_format($cartVal->getPrd->unit_price  ?? 0, 2) :
                                                            number_format($cartVal->cart_price  ?? 0, 2) }}
                                                        </span>
                                                        <span>&nbsp;@lang('common.baht') / {{ $cartVal->getPrd->package_id?getPackageName($cartVal->getPrd->package_id):null }} </span>
                                                    </h4>

                                                        @if(isset($cartVal->is_short_stock) && $cartVal->is_short_stock == true)
                                                            <div class="text-danger small">* จำนวนสั่งซื้อขั้นต่ำ {{ number_format(($cartVal->getPrd->min_order_qty ?? 0)) }} {{ $cartVal->getPrd->package_id?getPackageName($cartVal->getPrd->package_id):null }}
                                                                สามารถซื้อได้ {{ number_format(($cartVal->getPrd->quantity ?? 0)) }} {{ $cartVal->getPrd->package_id?getPackageName($cartVal->getPrd->package_id):null }}</div>
                                                        @endif
                                                        @if ($hasChangePrice)
                                                            <div class="text-danger small">* สินค้ามีการเปลี่ยนแปลงจาก {{ number_format($cartVal->cart_price ?? 0, 2) }} @lang('common.baht') / {{ $cartVal->getPrd->package_id?getPackageName($cartVal->getPrd->package_id):null }}</div>
                                                        @endif

                                                </div>
                                                @if ($cartVal->is_bargain)
                                                    <small class="text-danger">สินค้าต่อรองราคา</small>
                                                @endif

                                            </div>
                                        </div>
                                        <div class=" text-center col-sm-12 col-md-4 text-right px-0 mt-auto mb-auto ">
                                            <table class="table table-borderless align-middle text-right m-0 mb-1 h-100">
                                                <tbody>
                                                    <tr>
                                                        <td class="p-0 small ">@lang('product.minimun_order') : </td>
                                                        <td class="p-0 small text-left">&nbsp;
                                                            @if ($cartVal->getPrd->order_qty_limit === '0')
                                                                {{$cartVal->getPrd->min_order_qty}} {{$package_name}}
                                                            @else
                                                                @lang('product.unlimited')
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="p-0 small ">@lang('product.stock') : </td>
                                                        <td class="p-0 small text-left">&nbsp;
                                                            @if($cartVal->getPrd->stock === '1')
                                                                @lang('product.unlimited')
                                                            @else
                                                                @if($cartVal->getPrd->quantity > 0)
                                                                    <span class="product_quantity">{{ $cartVal->getPrd->quantity }}</span> {{$package_name}}
                                                                @else
                                                                    <span class="red outstock">@lang('product.out_of_stock')</span>
                                                                @endif
                                                                
                                                            @endif
                                                        </td>

                                                    </tr>
                                                </tbody>
                                            </table>

                                            <table class="table table-borderless align-middle text-right mb-0" >
                                                <tbody>
                                                    <tr>
                                                        <td class="text-right align-middle p-0 ">
                                                            <div class="spiner d-inline-flex align-items-center" data-cartid="{{ $cartVal->id }}">
                                                                <span class="decrease fas fa-minus"></span>
                                                                <input type="number" class="spinNum form-control mx-1"
                                                                    value="{{ $cartVal->quantity }}"
                                                                    min="0"
                                                                    {{-- max="{{ $cartVal->getPrd->quantity }}" --}}
                                                                    data-haschange="{{$hasChangePrice}}"
                                                                    @if($cartVal->product_from=='bargain') readonly="readonly" @endif>
                                                                <span class="increase fas fa-plus"></span>
                                                            </div>
                                                        </td>
                                                        <td class="text-center align-middle p-0" >
                                                            {{ $cartVal->getPrd->package_id ?getPackageName($cartVal->getPrd->package_id) : null }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-right align-middle p-0 pt-2">
                                                            <h2 class="font-weight-bold prd-total-price text-danger text-right mr-3">
                                                                ฿{{ $hasChangePrice
                                                                ? number_format(($cartVal->getPrd->unit_price ?? 0) * $cartVal->quantity, 2)
                                                                : number_format($cartVal->total_price ?? 0, 2) }}
                                                            </h2>
                                                        </td>
                                                        <td class="text-center">
                                                            <a href="javascript:;" class="text-secondary del-action cart-remove">
                                                                <i class="fa fa-trash fa-lg" alt="Delete"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    @if($cartVal->product_from == 'bargain')
                                                        <tr>
                                                            <td colspan="3" class="text-muted small text-right">
                                                                @lang('checkout.price_has_already_bargained')
                                                            </td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>

                                        </div>
                                    </div>
                                @endforeach
                                @endif

                                <div class="mb-5"></div>
                                @if (!empty($inactiveItems) && count($inactiveItems) > 0)
                                    <div class="d-flex mb-3" id="inactive_cart_item_header">
                                        <h3>รายการสินค้าหมดชั่วคราว</h3>
                                        <button type="button" class="btn btn-sm btn-danger ml-auto" id="delete_close_product">ลบทั้งหมด</button>
                                    </div>
                                    
                                    @php
                                        $totPrice = 0;
                                    @endphp
                                    @foreach ($inactiveItems as $cartVal)
                                        @php
                                            $hasChangePrice = $cartVal->cart_price != $cartVal->getPrd->unit_price;
                                            $totPrice += $hasChangePrice ? $cartVal->getPrd->unit_price*$cartVal->quantity : $cartVal->total_price;
                                            $package_name = optional($cartVal->getPrd->package)->title;
                                        @endphp
                                        <div id="cart_{{ $cartVal->id }}" class="inactive_cart_item disabled-cart cart_shop_item cart_item row {{$loop->last?'':' '}} border p-3 mb-3"
                                            data-id="{{ $cartVal->id }}">
                                            <div class="col-12 p-0">
                                                <h3>{{ $cartVal->getShopDesc->shop_name??'' }}</h3>
                                            </div>
                                            <div class=" col-sm-12 col-md-8 col mb-2 d-sm-flex">
                                                <div class="mr-3 mb-2 d-flex justify-content-start align-items-start">
                                                    <input type="checkbox" name="" value="{{ $cartVal->id }}" class="mr-3 float-left "  >
                                                    @if($cartVal->is_short_stock == true)
                                                    <a class="d-flex align-items-center " href="{{ action('ProductDetailController@display',[$cartVal->getCat->url,$cartVal->getPrd->sku])}}">
                                                    @else
                                                    <a class="d-flex align-items-center " href="javascript:void(0)">
                                                    @endif
                                                        <div class="prod-image" style="
                                                            background-image: url('{{ getProductImageUrlRunTime($cartVal->getPrd->thumbnail_image,'thumb') }}');
                                                        "></div>
                                                    </a>
                                                </div>
                                                <div class="flex-sm-grow-1">
                                                    <div>
                                                        <h3 class="prod-name mb-1">
                                                            @if($cartVal->is_short_stock == true)
                                                            <strong><a href="{{ action('ProductDetailController@display',[$cartVal->getCat->url,$cartVal->getPrd->sku])}}">{{ $cartVal->getCatDesc->category_name??'' }}</a></strong>
                                                            @else
                                                            <strong>{{ $cartVal->getCatDesc->category_name??'' }}</strong>
                                                            @endif
                                                            
                                                        </h3>
                                                        <div class="mb-1">
                                                            <img src="{{ getBadgeImage($cartVal->getPrd->badge_id) }}" height="40" alt="Badge" class="prod-badge" />
                                                        </div>
                                                    </div>
                                                    <div class="">
                                                        <h4 class="mb-1 ">
                                                            {{ number_format($cartVal->getPrd->weight_per_unit??null, 2) }}
                                                            {{ $cartVal->getPrd->base_unit_id?getUnitName($cartVal->getPrd->base_unit_id):null}} /
                                                            {{ $cartVal->getPrd->package_id?getPackageName($cartVal->getPrd->package_id):null }}
                                                        </h4>
                                                            
                                                        <h4 class="mb-1">
                                                            
                                                            {{-- @if ($hasChangePrice)
                                                                <del class="text-muted">{{ number_format($cartVal->cart_price ?? 0, 2) }}</del>
                                                            @endif --}}
                                                            <span class="prd-unit-price">
                                                                {{ $hasChangePrice ?
                                                                number_format($cartVal->getPrd->unit_price  ?? 0, 2) :
                                                                number_format($cartVal->cart_price  ?? 0, 2) }}
                                                            </span>
                                                            <span>&nbsp;@lang('common.baht') / {{ $cartVal->getPrd->package_id?getPackageName($cartVal->getPrd->package_id):null }} </span>
                                                        </h4>
                                                        @if(isset($cartVal->is_short_stock) && $cartVal->is_short_stock == true)
                                                            <div class="text-danger small">* จำนวนสั่งซื้อขั้นต่ำ {{ number_format(($cartVal->getPrd->min_order_qty ?? 0)) }} {{ $cartVal->getPrd->package_id?getPackageName($cartVal->getPrd->package_id):null }}
                                                                สามารถซื้อได้ {{ number_format(($cartVal->getPrd->quantity ?? 0)) }} {{ $cartVal->getPrd->package_id?getPackageName($cartVal->getPrd->package_id):null }}</div>
                                                        @endif
                                                        @if(isset($cartVal->is_product_close) && $cartVal->is_product_close == true)
                                                            <div class="text-danger small">* สินค้าปิดการขายชั่วคราว</div>
                                                        @endif
                                                        @if(isset($cartVal->is_shop_close) && $cartVal->is_shop_close == true)
                                                            <div class="text-danger small">* ร้านปิดการขายชั่วคราว</div>
                                                        @endif
                                                        @if(isset($cartVal->is_out_of_stock) && $cartVal->is_out_of_stock == true)
                                                            <div class="text-danger small">* สินค้าหมดชั่วคราว</div>
                                                        @endif
                                                        @if ($hasChangePrice)
                                                            <b class="text-danger small">* สินค้ามีการเปลี่ยนแปลงจาก {{ number_format($cartVal->cart_price ?? 0, 2) }} @lang('common.baht') / {{ $cartVal->getPrd->package_id?getPackageName($cartVal->getPrd->package_id):null }}</b>
                                                        @endif
                                                    </div>
                                                    @if ($cartVal->is_bargain)
                                                        <small class="text-danger">สินค้าต่อรองราคา</small>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class=" text-center col-sm-12 col-md-4 text-right px-0 mt-auto mb-auto ">
                                                <table class="table table-borderless align-middle text-right m-0 mb-1 h-100" role="presentation">
                                                    <tbody>
                                                        <tr>
                                                            <td class="p-0 small ">@lang('product.minimun_order') : </td>
                                                            <td class="p-0 small text-left">&nbsp;
                                                                @if ($cartVal->getPrd->order_qty_limit === '0')
                                                                    {{$cartVal->getPrd->min_order_qty}} {{$package_name}}
                                                                @else
                                                                    @lang('product.unlimited')
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="p-0 small ">@lang('product.stock') : </td>
                                                            <td class="p-0 small text-left">&nbsp;
                                                                @if($cartVal->getPrd->stock === '1')
                                                                    @lang('product.unlimited')
                                                                @else
                                                                    @if($cartVal->getPrd->quantity > 0)
                                                                        {{ $cartVal->getPrd->quantity}} {{$package_name}}
                                                                    @else
                                                                        <span class="red outstock">@lang('product.out_of_stock')</span>
                                                                    @endif
                                                                    
                                                                @endif
                                                            </td>

                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <table class="table table-borderless align-middle text-right mb-0" role="presentation" >
                                                    <tbody>
                                                        <tr>
                                                            <td class="text-right align-middle p-0 ">
                                                                <div class="spiner d-inline-flex align-items-center" data-cartid="{{ $cartVal->id }}">
                                                                    <span class="decrease fas fa-minus"></span>
                                                                    <input type="number" class=" form-control mx-1"
                                                                        value="{{ $cartVal->quantity }}"
                                                                        min="0"
                                                                        {{-- max="{{ $cartVal->getPrd->quantity }}" --}}
                                                                        style="width:70px;"
                                                                        data-haschange="{{$hasChangePrice}}"
                                                                        @if($cartVal->product_from=='bargain') readonly="readonly" @endif>
                                                                    <span class="increase fas fa-plus"></span>
                                                                </div>
                                                            </td>
                                                            <td class="text-center align-middle p-0" >
                                                                {{ $cartVal->getPrd->package_id ?getPackageName($cartVal->getPrd->package_id) : null }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-right align-middle p-0 pt-2">
                                                                <h2 class="font-weight-bold prd-total-price text-danger text-right mr-3">
                                                                    ฿{{ $hasChangePrice
                                                                    ? number_format(($cartVal->getPrd->unit_price ?? 0) * $cartVal->quantity, 2)
                                                                    : number_format($cartVal->total_price ?? 0, 2) }}
                                                                </h2>
                                                            </td>
                                                            <td class="text-center">
                                                                <a href="javascript:;" class="text-secondary del-action cart-remove">
                                                                    <i class="fa fa-trash fa-lg" alt="Delete"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        @if($cartVal->product_from == 'bargain')
                                                            <tr>
                                                                <td colspan="3" class="text-muted small text-right">
                                                                    @lang('checkout.price_has_already_bargained')
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>

                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>

                            <div class="col-lg-4 p-0 pl-lg-3 ">
                                <div class="checkout-table-footer clearfix p-3 pr-0 sticky-top" id="checkout-summary">
                                    <h2 class="mb-3"><strong>@lang('checkout.summary')</strong></h2>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="">@lang('checkout.grand_total') </span>
                                        <span class="">
                                            <strong id="tot_order_amount">
                                                {{-- @if ($hasChangePrice)
                                                    ฿{{ number_format($totPrice, 2) }}
                                                @else --}}
                                                    ฿{{ isset($orderInfo) && isset($orderInfo->total_final_price) && is_numeric($orderInfo->total_final_price)
                                                        ? number_format($orderInfo->total_final_price, 2)
                                                        : '0.00' }}
                                                {{-- @endif --}}
                                            </strong>
                                        </span>
                                    </div>
                                    <div class="dbox-flex1 text-right">
                                        @if(count($user_credits) && $show_credit)
                                            <button class="btn-blue2 mr-3 all_pay_credit d-none" id="all_pay_credit">@lang('checkout.pay_credit_term')</button>
                                        @endif
                                        <button type="submit" id="buy_now" class="btn btn-sm px-3">@lang('checkout.buy_now')</button>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div>@lang('common.no_record_found')</div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('footer_scripts')

{!! CustomHelpers::combineCssJs(['js/cart/cart'],'js') !!}

<script>
    
    let $header = $('#header');
    function updateOffsets($el) {
        let headerHeight = $header.outerHeight() || 0;
        let targetTop = $el.offset().top;

        return {
            headerHeight: headerHeight,
            targetOffset: targetTop - headerHeight
        };
    }

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
    
    function selectedCount() {
        const selectedCount = $('.cartItem:checked').length;
        const totalCount = $('.cartItem').length;

        // อัปเดตเฉพาะถ้ามี element เหล่านี้
        $('#selectedCount').text(selectedCount || 0);
        $('#totalCount').text(totalCount || 0);
    }
    
    function handleCartItemSelection() {
        let allChecked = $('.cartItem').length === $('.cartItem:checked').length;
        $('#allProductItem').prop('checked', allChecked);
    }

    function selectedCartItem(data,callback = null, triggerEl = null ) {
        $.ajax({
            url: selectedCartItemUrl,
            method: 'POST',
            data: {
                ...data,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() { showHideLoader('showLoader'); },
            success: function(response) {
                if(response.status === 'success') {
                    $('#tot_order_amount').text('฿' + response.totalAmount);
                    if (triggerEl) {
                        $(triggerEl).removeClass('unselectable');
                    }
                    callback ? callback() : null;
                } else {
                    // showSweetAlertError(response.message??'รายการสินค้ามีการเปลี่ยนแปลง กรุณาตรวจสอบอีกครั้ง');
                    let candidateChecked = $(triggerEl).prop('checked');
                    
                    // window.location.reload();
                    let msg = response.message ?? 'สินค้าในตะกร้ามีการเปลี่ยนแปลง กรุณาตรวจสอบอีกครั้ง';
                    const sections = [];
                    if (response.priceChange?.length) {
                        sections.push(`สินค้าราคามีการเปลี่ยนแปลง (${response.priceChange.length} รายการ) :<br>` +
                            response.priceChange.map(i => '- ' + i.get_cat_desc?.category_name ?? 'ไม่ทราบชื่อ').join('<br>')
                        );
                    }
                    if (response.productClose?.length) {
                        sections.push(`สินค้าถูกปิดการขาย (${response.productClose.length}) :<br>` +
                            response.productClose.map(i => '- ' + i.get_cat_desc?.category_name ?? 'ไม่ทราบชื่อ').join('<br>')
                        );
                    }
                    if (response.shopClose?.length) {
                        sections.push(`ร้านค้าปิดทำการ (${response.shopClose.length}) :<br>` +
                            response.shopClose.map(i => '- ' + i.get_cat_desc?.category_name ?? 'ไม่ทราบชื่อร้าน').join('<br>')
                        );
                    }
                    if (response.outOfStock?.length) {
                        sections.push(`สินค้าหมดสต็อก (${response.outOfStock.length}) :<br>` +
                            response.outOfStock.map(i => '- ' + i.get_cat_desc?.category_name ?? 'ไม่ทราบชื่อ').join('<br>')
                        );
                    }
                    if (response.shortStock?.length) {
                        sections.push(`สินค้ามีจำนวนไม่พอ (${response.shortStock.length}) :<br>` +
                            response.shortStock.map(i => '- ' + i.get_cat_desc?.category_name ?? 'ไม่ทราบชื่อ').join('<br>')
                        );
                    }

                    if (sections.length > 0) {
                        msg += '<br><br>' + sections.join('<br><br>');
                    }

                    if (triggerEl) {
                        $(triggerEl).prop('checked', false);
                        $(triggerEl).addClass('unselectable');
                    }
                    
                    if (candidateChecked) {
                        showSweetAlertError(msg??'รายการสินค้ามีการเปลี่ยนแปลง กรุณาตรวจสอบอีกครั้ง');
                        swal({
                            title: 'รายการสินค้ามีการเปลี่ยนแปลง',
                            html: msg??'กรุณาตรวจสอบอีกครั้ง<br><br>',
                            type: 'error',
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#CE232A',
                        }).then(() => {
                            // showHideLoader('showLoader');
                            // window.location.reload();
                        })
                    }
                }
            },
            error: function(xhr, status, error) {
                showSweetAlertError(xhr.responseJSON.message??"ไม่สามารถดำเนินการได้ กรุณาทำรายการใหม่อีกครั้ง");
                window.location.reload();
            },
            complete: function () {
                showHideLoader('hideLoader');
            }
        });
    }

    function handleCloseProductHeader() {
        const inactiveItemsCount = $('.inactive_cart_item').length;
        if (inactiveItemsCount === 0) {
            $('#inactive_cart_item_header').remove();
        }
    }
    
    function selectedMultiCartItem(data,callback = null) {
        $.ajax({
            url: selectedMultiCartItemUrl,
            method: 'POST',
            data: {
                ...data,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() { showHideLoader('showLoader'); },
            success: function(response) {
                if(response.status === 'success') {
                    $('#tot_order_amount').text('฿' + response.totalAmount);
                    callback ? callback() : null;
                } else {
                    showSweetAlertError(response.message??'รายการสินค้ามีการเปลี่ยนแปลง กรุณาตรวจสอบอีกครั้ง');
                    window.location.reload();
                }
            },
            error: function(xhr, status, error) {
                showSweetAlertError(xhr.responseJSON.message??"ไม่สามารถดำเนินการได้ กรุณาทำรายการใหม่อีกครั้ง");
                window.location.reload();
            },
            complete: function () {
                showHideLoader('hideLoader');
            }
        });
    }

    function removeMultiCartItem(data) {
        swal({
            title: error_msg.txt_delete_confirm,
            type: "warning",
            showCancelButton: true,
            confirmButtonText: error_msg.yes_delete_it,
            cancelButtonText: error_msg.txt_no,
            closeOnConfirm: true,
            closeOnCancel: true,
        }).then((rep) => {
            if (rep) {
                $.ajax({
                    url: removeMultiCartUrl,
                    method: 'POST',
                    data: {
                        ...data,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() { showHideLoader('showLoader'); },
                    success: function(result) {
                        if(result.status=='success'){
                            swal(lang_success, result.msg, "success").then(function(){
                                $('.inactive_cart_item,#inactive_cart_item_header').remove();
                            });
                        }else{
                            showSweetAlertError(result.msg);
                            window.location.reload();
                        }
                    },
                    error: function(xhr, status, error) {
                        showSweetAlertError(xhr.responseJSON.message??"ไม่สามารถลบสินค้าได้");
                        window.location.reload();
                    },
                    complete: function () { showHideLoader('hideLoader'); }
                });
            }
        });
    }
    
    
    $(document).on('change', '.cartItem', function () {
        const $checkbox = $(this);
        const data = {
            cartItemId: $checkbox.val(),
            isSelected: $checkbox.is(':checked')
        };

        selectedCartItem(data, () => {
            selectedCount();
            handleCartItemSelection();
        }, $checkbox);

    });

    $(function () {
        
        $('form').on('keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                return false;
            }
        });
        
        // Select / Deselect all checkboxes
        $('#allProductItem').on('change', function (e) {
            let isSelected = $(this).is(':checked');
            $('.cartItem:not(.unselectable)').prop('checked', isSelected);
            
            let selectedCartIds = $('.cartItem:checked').map(function () {
                return $(this).val();
            }).get();
            let data = {
                selectedCartIds : selectedCartIds,
                isSelected: isSelected ? true : false
            }
            selectedMultiCartItem(data ,() => {
                selectedCount();
                handleCartItemSelection();
            });
        });

        $('#delete_close_product').on('click', function (e) {
            let data = {
                cartIds: $('.inactive_cart_item').data('id')
            }
            removeMultiCartItem(data,()=>{});

        })

        // Buy Now (Submit Form)
        $('#end_shopping_form').on('submit', function (e) {
            e.preventDefault();
            let form = this;
            let selectedCartIds = $('.cartItem:checked').map(function () {
                return $(this).val();
            }).get();

            if(selectedCartIds.length === 0) {
                showSweetAlertError("กรุณาเลือกสินค้าขั้นต่ำ 1 รายการ");
                return;
            }

            $.ajax({
                url: validateCartItemsUrl,
                method: 'POST',
                data: {
                    cartItems: selectedCartIds,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() { showHideLoader('showLoader'); },
                success: function(response) {
                    
                    if (response.status === 'success' && response.selectedItems.length > 0 && response.passItems.length === selectedCartIds.length) {
                        form.submit();
                    }else{
                        let errorMsg = 'กรุณาตรวจสอบใหม่อีกครั้ง';
                        if (response.msg && typeof response.msg === 'object') {
                            errorMsg = Object.values(response.msg).flat().join('\n');
                        } else if (typeof response.msg === 'string') {
                            errorMsg = response.msg;
                        }

                        swal({
                            title: 'ไม่สามารถทำรายการได้',
                            html: errorMsg ?? `กรุณากรอกข้อมูลให้ครบถ้วน และตรวจสอบการทำรายการอีกครั้ง<br><br>`,
                            type: 'error',
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#CE232A',
                        }).then(() => {
                            showHideLoader('showLoader');
                            window.location.reload();
                        });
                        
                    }
                },
                error: function(xhr, status, error) {
                    swal({
                        title: 'รายการสินค้ามีการเปลี่ยนแปลง',
                        html: error ?? `กรุณาตรวจสอบใหม่อีกครั้ง`,
                        type: 'error',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#CE232A',
                    }).then(() => {
                        showHideLoader('showLoader');
                        window.location.reload();
                    });
                },
                complete: function () { showHideLoader('hideLoader'); }
            });
            
        });


    });
</script>

@stop