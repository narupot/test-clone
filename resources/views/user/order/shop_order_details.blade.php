@extends('layouts.app')

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/dataTables.bootstrap', 'css/myaccount', 'css/cropper.min'],'css') !!}
@endsection

@section('header_script')
@stop
@section('breadcrumbs')
@stop
@section('content')
           
<div class="track-order-num-wrap border-bottom pb-2">
    <div class="cust-ord-num">
        <div class="float-right btn-group">
            @if($orderShopData->order_status ==3 || $orderShopData->order_status ==4)
            @elseif($orderShopData->shipping_method == 2)
                <a class="btn-light-red btn-small ml-auto receive_items" data-val="{{ $orderShopData->id }}" href="javascript:void(0);">@lang('order.receive')</a>
            @endif
            <a href="{{ url()->previous() }}" class="btn-grey"><i>&lt;</i> @lang('common.back')</a>
        </div>
        <h2>@lang('order.seller_order_no'). {{$orderShopData->shop_formatted_id}}</h2>
        <p>@lang('order.main_order_no'). {{$mainOrderData->formatted_id}}</p>
    </div>
    <div class="track-status">
        <button class="btn- btn">@lang('shop.status') : <span id="shop_status_{{ $orderShopData->id }}">{{($orderShopData->getOrderStatus->status) ? $orderShopData->getOrderStatus->status : "NA" }}</span></button>&nbsp;
        @if($orderShopData->shipping_method ==1 && $orderShopData->order_status !=3 && $orderShopData->seller_status == 'ready')
            <button class="btn- btn">@lang('order.ready_to_receive')</button>
        @endif
        <span class="ship-track-time">{{getDateFormat($orderShopData->updated_at,7)}}</span>
    </div>
</div>
<div class="order-step-wrap mb-4">
    <ul>
        
        @if($orderShopData->order_status ==1 || $orderShopData->order_status ==2)
            <li class="active">
            <div class="current-status">
                <div class="d-block">@lang('order.current_status_is_here')</div>
                <i class="fas fa-map-marker-alt"></i>
            </div>
        @else
            <li class="completed">
        @endif
            <span class="step-icon">
                <i class="fas fa-store"></i>
            </span>
            <span class="step-name">@lang('order.prepare_products_at_the_store')</span>
        </li>

        @if($orderShopData->shipping_method != 2)
            <li @if($orderShopData->order_status > 2) @if($orderShopData->order_status ==5)class="active" @else class="completed" @endif @endif>
                @if($orderShopData->order_status ==5)
                    <div class="current-status">
                        <div class="d-block">@lang('order.current_status_is_here')</div>
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                @endif
                <span class="step-icon">
                    <i class="far fa-warehouse-alt"></i>
                </span>
                <span class="step-name">@lang('order.products_at_the_center')</span>
            </li>
        @endif

        @if($orderShopData->shipping_method == 3)
            <li @if($orderShopData->order_status != 5 && $orderShopData->order_status > 2) class="completed" @endif>
                @if($orderShopData->order_status ==6)
                    <div class="current-status">
                        <div class="d-block">@lang('order.current_status_is_here')</div>
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                @endif
                <span class="step-icon">
                    <i class="fas fa-truck"></i>
                </span>
                <span class="step-name">@lang('order.shipping_now')</span>
            </li>
        @endif

        @if($orderShopData->order_status !=4)
            <li @if($orderShopData->order_status ==3) class="active" @endif>
                @if($orderShopData->order_status ==3)
                    <div class="current-status">
                        <div class="d-block">@lang('order.current_status_is_here')</div>
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                @endif
                <span class="step-icon">
                    <i class="far fa-thumbs-up"></i>
                </span>
                <span class="step-name">@lang('order.recieved_the_product')</span>
            </li>
        @endif

        @if($orderShopData->order_status ==4)
            <li class="active cancel">
                <div class="current-status">
                    <div class="d-block">@lang('order.current_status_is_here')</div>
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <span class="step-icon">
                    <i class="far fa-times"></i>
                </span>
                <span class="step-name">@lang('common.cancel')</span>
            </li>
        @endif
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
                <li>@lang('order.item_status')</li>
            </ul>
        </div>
        <div class="table-content">
            @if(count($orderItems))
                @foreach($orderItems as $key =>$item)

                @php($detail_json = jsonDecodeArr($item->order_detail_json))
                <ul>
                    <li class="product">
                        <a href="{{ action('ProductDetailController@display',[$item->getCat->url,$item->sku])}}"><span class="prod-img float-left"><img src="{{ getProductImageUrlRunTime($detail_json['thumbnail_image'],'thumb') }}" width="80" height="80" alt=""></span>
                        <span class="prod-name">{{ $detail_json['name'][session('default_lang')]??$item->category_name }}</span></a>
                    </li>
                    <li><span class="mr"><img src="{{ getBadgeImageUrl($detail_json['badge']['icon'] ?? '' )}}" width="" class="h-atuo" alt=""></span></li>
                    <li> {{ $item->quantity }} {{ $detail_json['unit'][session('default_lang')] ?? $item->unit_name }}</li>
                    <li>{{number_format($item->last_price, 2) }} @lang('common.baht')</li>
                    <li>{{number_format($item->total_price, 2) }} @lang('common.baht')</li>
                    <li>{{$detail_json['payment_method'][session('default_lang')]}}</li>
                    <li class="detail_status item_status_{{ $item->order_shop_id }}">{{$item->getOrderStatus->status??''}}</li>
                </ul>
                @endforeach
            @endif
        </div>
    </div>
    <div class="table-footer clearfix">
        <div class="col-sm-10 float-right">
            @if($orderShopData->total_credit_amount > 0)
                <div class="row">
                    <span class="col-6">@lang('order.total_amount_by_credit_term')</span>
                    <span class="col-6">{{ number_format($orderShopData->total_credit_amount, 2) }} @lang('common.baht')</span>
                </div>
            @endif
            @if(($orderShopData->total_final_price - $orderShopData->total_credit_amount) > 0)
                <div class="row">
                    <span class="col-6">@lang('order.total_amount_by_cash')</span>
                    <span class="col-6">{{ number_format($orderShopData->total_final_price - $orderShopData->total_credit_amount, 2) }} @lang('common.baht')</span>
                </div>
            @endif
            
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
        @if($mainOrderData->shipping_method == 1)
            <div class="tInfo-row">
                <h4><strong>@lang('admin_order.center_address') : </strong></h4>
                {!! CustomHelpers::centerAddress($mainOrderData->order_json) !!}
            </div>
        @elseif($mainOrderData->shipping_method == 2)
            <div class="tInfo-row">
                <h4><strong>@lang('admin_order.store_address') : </strong></h4>
                {!! CustomHelpers::storeAddress($orderShopData->order_json) !!}
            </div>
        @else
            <div class="tInfo-row">
                <h4><strong>@lang('checkout.shipping_address') : </strong></h4>
                {{ CustomHelpers::buyerShipBillTo($mainOrderData->order_json,'shipping_address') }}
            </div>
            <div class="tInfo-row">
                <h4><strong>@lang('checkout.billing_address') : </strong></h4>
                {{ CustomHelpers::buyerShipBillTo($mainOrderData->order_json,'billing_address') }}
            </div>
        @endif
    </div>
    <div class="title-track-info">
        <h3 class="skyblue">@lang('order.shipping_method')</h3>
        <div class="track-ship-name">{{ GeneralFunctions::getShippingMethod($mainOrderData->shipping_method) }} @if(strtotime($mainOrderData->pickup_time)) <span class="red">(@lang('order.expected_time_to_receive') : {{ getDateFormat($mainOrderData->pickup_time,8) }})</span> @endif</div>
    </div>
</div>
                
@endsection

@section('footer_scripts')
    <script type="text/javascript">
        var receive_item_url = "{{ action('User\OrderController@receiveOrdItems') }}";
        var receive_ord_url = "{{ action('User\OrderController@receiveOrd') }}";
        var lang_receive_item = "@lang('order.are_you_sure_want_to_receive_this_items')";
        var lang_yes = "@lang('common.yes')";
        var lang_no = "@lang('common.no')";
    </script>
    {!! CustomHelpers::combineCssJs(['js/jquery.dataTables','js/dataTables.bootstrap', 'js/user/myaccount'],'js') !!}
    <!-- begining of page level js -->
    <!-- end of page level js -->
@endsection