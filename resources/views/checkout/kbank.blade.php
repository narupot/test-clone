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
            max-width: 150px;
            margin-bottom: 1rem;
        }

    </style>
@endsection
@section('content')
<div class="row h-100 justify-content-center align-items-center">
    <div class="card card_wraps text-center col-5">
        <img src="/assets/images/kbank.png" class="card-img-top" alt="Omise">
        <div class="card-body">

            <!-- <div class="form-group">
                <label for="item">Item Name :</label>
                <input type="text" class="form-control" name="item" id="item" value="Item name" disabled>
            </div> -->

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
@endsection
@section('footer_scripts') 
    <script id="qr_js" type="text/javascript"
            src="{{$kbank_details['js_url']}}"
            data-apikey="{{$kbank_details['public_key']}}"
            data-amount ="{{$orderInfo->total_final_price}}"
            data-payment-methods="qr"
            data-order-id="{{$order_id}}"
    >
    </script>

    <script>
    var check_ord_url = "{{ action('Checkout\PaymentGatewayController@Check',$order_id) }}";
        function CheckPayment() {
            $.get(check_ord_url,function (data,status) {
                if(data.status == "success"){
                    window.location.href = data.url;
                }else{
                    setTimeout(function () {
                        CheckPayment();
                    },3000);
                }
            });
        }
        $(document).ready(function () {
                if($(".pay-button").length > 0){
                    $(".pay-button").appendTo(".card-body");
                    $(".pay-button").click(function () {
                        CheckPayment();
                    });
                }
        });

    </script>

@endsection