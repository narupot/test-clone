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
    </style>
@endsection

@section('header_script')      
@stop

@section('breadcrumbs')
@stop

@section('content')

<h1 class="page-title">@lang('customer.tracking_order')</h1>
<div class="form-group">
    <label>@lang('customer.order_no.')</label>
    <div class="order-track-group">
        <input type="text" id="track_no" placeholder="Track number" value="{{(isset($main_order) && !empty($main_order)) ? $main_order->formatted_id : ''}}">
        <button id="btn_track" class="btn-dark-grey">Follow</button>
        <a href="#" class="btn-grey back"><i>&lt;</i> Back</a>
    </div>
</div>
@if(isset($main_order) && !empty($main_order))        
    <div class="track-order-num-wrap border-bottom mt-5 pb-2">
        <div class="cust-ord-num">
            <h2>@lang('order.order_no'). {{$main_order->formatted_id}}</h2>
        </div>
        <div class="track-status">
            <button class="btn-blue">@lang('common.status') : <span id="order_status">{{$main_order->getOrderStatus->status ?? "NA" }}</span></button>                                 
            <span class="ship-track-time">{{getDateFormat($main_order->updated_at,7)}}</span>
        </div>
    </div>          

    <div class="track-buyer-info border-top-0 mt-3">
        <div class="title-track-info">
            <h3 class="skyblue">@lang('customer.buyer_information')</h3>
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
            @if($main_order->shipping_method == 1)
                <div class="tInfo-row">
                    <h4><strong>@lang('admin_order.center_address') : </strong></h4>
                    {!! CustomHelpers::centerAddress($main_order->order_json) !!}
                </div>
            @elseif($main_order->shipping_method == 2)
                <div class="tInfo-row">
                    <h4><strong>@lang('admin_order.store_address') : </strong></h4>
                    {!! CustomHelpers::storeAddress($main_order->order_json) !!}
                </div>
            @else
                <div class="tInfo-row">
                    <h4><strong>@lang('checkout.shipping_address') : </strong></h4>
                    {{ CustomHelpers::buyerShipBillTo($main_order->order_json,'shipping_address') }}
                </div>
                <div class="tInfo-row">
                    <h4><strong>@lang('checkout.billing_address') : </strong></h4>
                    {{ CustomHelpers::buyerShipBillTo($main_order->order_json,'billing_address') }}
                </div>
            @endif

        </div>
        <div class="title-track-info">
            <h3 class="skyblue">@lang('order.shipping_method')</h3>
            <div class="track-ship-name">{{ GeneralFunctions::getShippingMethod($main_order->shipping_method) }} @if(strtotime($main_order->pickup_time)) <span class="red">(@lang('order.expected_time_to_receive') : {{ getDateFormat($main_order->pickup_time,8) }})</span> @endif</div>
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
                    <li>@lang('product.payment_by')</li>      
                    <li>@lang('order.item_status')</li> 
                               
                </ul>
            </div>
            <div class="table-content">
                @if(count($order_detail))
                    
                    @foreach($order_detail as $key =>$item)

                    @php($detail_json = jsonDecodeArr($item->order_detail_json))
                    @php($prd_url =action('ProductDetailController@display',[$detail_json['cat_url']??'',$item->sku]))
                    
                    <ul>

                        <li class="product">
                            <a href="{{ $prd_url }}"><span class="prod-img"><img src="{{ getProductImageUrlRunTime($detail_json['thumbnail_image'],'thumb') }}" width="80" height="80" alt=""></span>
                            <span class="prod-name">{{ $detail_json['name'][session('default_lang')]??$item->category_name }}</span></a>
                        </li>
                        <li><span class="mr"><img src="{{ getBadgeImageUrl($detail_json['badge']['icon'] ?? '' )}}" width="30"></span></li>
                        <li> {{ $item->quantity }} {{ $detail_json['package'][session('default_lang')] ?? $item->package_name }}</li>
                        <li>{{numberFormat($item->last_price) }} @lang('common.baht')</li>
                        <li>{{numberFormat($item->total_price) }} @lang('common.baht')</li>
                        <li>{{$detail_json['payment_method'][session('default_lang')]}}</li>
                        <li class="detail_status item_status_{{ $item->order_shop_id }}">{{$shop_order[$item->order_shop_id]['status']??''}}</li>
                        
                    </ul>
                    @endforeach
                @endif
            </div>
        </div>
        <div class="table-footer clearfix">
            <div class="col-sm-10 float-right">                     
                <div class="row">
                    <span class="col-6">@lang('order.total_shipping_cast')</span>
                    <span class="col-6">{{ numberFormat($main_order->total_shipping_cost)}}</span>
                </div>
                <div class="bg">
                    <div class="row">
                        <span class="col-6">@lang('order.grand_total')</span>
                        <span class="col-6">{{numberFormat($main_order->total_final_price)}} @lang('common.baht')</span>
                    </div>
                </div>
            </div>
        </div>
    </div>  
@endif  
@endsection

@section('footer_scripts')
    <script type="text/javascript">
        var track_url = "{{action('Checkout\TrackOrderController@trackOrderDetail')}}";
        var receive_item_url = "{{ action('User\OrderController@receiveOrdItems') }}";
        var lang_receive_item = "@lang('order.are_you_sure_want_to_receive_this_items')";
        var lang_yes = "@lang('common.yes')";
        var lang_no = "@lang('common.no')";
        $('#btn_track').click(function(e){
            var val = $('#track_no').val();
            if(val){
                window.location.href=track_url+'/'+val;
            }else{
                $('#track_no').focus();
            }
        })
    </script>
    {!! CustomHelpers::combineCssJs(['js/jquery.dataTables','js/dataTables.bootstrap'],'js') !!}   
    <!-- begining of page level js -->
    <!-- end of page level js -->
@endsection