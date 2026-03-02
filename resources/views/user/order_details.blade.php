@extends('layouts.app')

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/dataTables.bootstrap', 'css/myaccount', 'css/cropper.min'],'css') !!}
@endsection

@section('header_script')      
@stop
@section('breadcrumbs')
@stop
@section('content')
<h1 class="page-title">@lang('shop.order_details')</h1>                   
<div class="track-order-num-wrap border-bottom pb-2">
    <div class="cust-ord-num">
        <div class="float-right btn-group">
            <a href="javascript://" class="btn-grey"><i>&lt;</i> Back</a>
            <!-- <button class="btn-default">Print</button> -->
        </div>
        <h2>@lang('order.shop_order_no'). {{$orderShopData->shop_formatter_id}}</h2>
    </div>
    <div class="track-status">
        <button class="btn- btn">@lang('shop.status') : {{($orderShopData->getOrderStatus->status) ? $orderShopData->getOrderStatus->status : "NA" }}</button>                                 
        <span class="ship-track-time">{{getDateFormat($orderShopData->updated_at,7)}}</span>
    </div>
</div>          
<div class="order-step-wrap mb-4">
    <ul>
        <li class="completed">
            <span class="step-icon">
                <i class="fas fa-store"></i>
            </span>
            <span class="step-name">@lang('order.prepare_products_at_the_store')</span>
        </li>
        <li class="active">
            <div class="current-status">
                <span class="d-block">@lang('order.current_status_is_here')</span>
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <span class="step-icon">
                <i class="far fa-warehouse-alt"></i>
            </span>
            <span class="step-name">@lang('order.products_at_the_center')</span>
        </li>
        <li>
            <span class="step-icon">
                <i class="fas fa-truck"></i>
            </span>
            <span class="step-name">@lang('order.shipping_now')</span>
        </li>
        <li>
            <span class="step-icon">
                <i class="far fa-thumbs-up"></i>
            </span>
            <span class="step-name">@lang('order.recieved_the_product')</span>
        </li>
    </ul>
</div>

<h2>@lang('order.order_items')</h2>
<div class="table-responsive track-order-table">
    <div class="table">
        <div class="table-header">
            <ul>
                <li>@lang('product.product_name')</li>
                <li>@lang('product.standered')</li>
                <li>@lang('product.qty')</li>
                <li>@lang('product.price_per_item')</li>
                <li>@lang('product.price')</li>
                <li>@lang('product.payment_by')</li>                         
            </ul>
        </div>
        <div class="table-content">
            @if(count($orderItems))
                @foreach($orderItems as $key =>$item)

                @php($detail_json = jsonDecodeArr($item->order_detail_json))
                <ul>
                    <li class="product">
                        <a href="{{ action('ProductDetailController@display',[$item->getCat->url,$item->sku])}}"><span class="prod-img"><img src="{{ getProductImageUrlRunTime($detail_json['thumbnail_image'],'thumb') }}" width="80" height="80" alt=""></span>
                        <span class="prod-name">{{ $detail_json['name'][session('default_lang')]??$item->category_name }}</span></a>
                    </li>
                    <li><span class="mr"><img src="{{ getBadgeImageUrl($detail_json['badge']['icon'] ?? '' )}}" width="30"></span></li>
                    <li> {{ $item->quantity }} {{ $detail_json['unit'][session('default_lang')] ?? $item->unit_name }}</li>
                    <li>{{numberFormat($item->last_price) }} @lang('common.baht')</li>
                    <li>{{numberFormat($item->total_price) }} @lang('common.baht')</li>
                    <li>{{$detail_json['payment_method'][session('default_lang')]}}</li>
                </ul>
                @endforeach
            @endif
        </div>
    </div>
    <div class="table-footer clearfix">
        <div class="col-sm-10 float-right">                     
            <div class="row">
                <span class="col-6">@lang('order.total_shipping_cast')</span>
                <span class="col-6">---</span>
            </div>
            <div class="bg">
                <div class="row">
                    <span class="col-6">@lang('order.grand_total')</span>
                    <span class="col-6">{{$orderShopData->total_final_price}} @lang('common.baht')</span>
                </div>
            </div>
        </div>
    </div>
</div>  

<div class="track-buyer-info border-top-0 mt-3">
    <div class="title-track-info">
        <h3 class="skyblue">@lang('customer.buyer_information')</h3>
    </div>
    <div class="track-info-detail">
        <div class="tInfo-row">
            <span class="label">@lang('customer.name') :</span> {{$mainOrderData->user_name}}
        </div>
        <div class="tInfo-row">
            <span class="label">@lang('customer.email') :</span> {{$mainOrderData->user_email}}
        </div>
        <div class="tInfo-row">
            <span class="label">@lang('customer.telephone') :</span>  {{$mainOrderData->ph_number}}
        </div>
        <div class="tInfo-row">
            <span class="label">@lang('customer.customer_group') :</span> ---
        </div>
        <div class="tInfo-row">
            <span class="label">@lang('customer.billing_address') :</span> ---
        </div>
        <div class="tInfo-row">
            <span class="label">@lang('customer.shipping_address') :</span> ---
        </div>
    </div>
    <div class="title-track-info">
        <h3 class="skyblue">@lang('order.shipping_method')</h3>
        <div class="track-ship-name">{{ shippingMethodName($mainOrderData->shipping_method) }}</div>
    </div>
</div>  
                
@endsection

@section('footer_scripts')
    {!! CustomHelpers::combineCssJs(['js/jquery.dataTables','js/dataTables.bootstrap', 'js/user/myaccount'],'js') !!}   
    <!-- begining of page level js -->
    <!-- end of page level js -->
@endsection