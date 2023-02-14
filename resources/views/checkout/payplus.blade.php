@extends('layouts.app') 
@section('title','Checkout')
@section('header_style')
    <style>
        .list-group-item {
            cursor: pointer;
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
<form action="submit" method="post" id="payment_form">
    <div class="row h-100 justify-content-center align-items-center">
        <div class="card card_wraps text-center col-5">
            <!--https://www.mercular.com/img/footer/kbank.png-->
            <img src="/assets/images/kbank.png" class="card-img-top" alt="Omise">
            <div class="card-body">
                <div class="form-group">
                    <label for="item">@lang('common.phone_number') :</label>
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
@endsection
@section('footer_scripts')

    <script>
        var submit_url = "{{action('Checkout\CartController@createPayPlusOrder',$orderInfo->formatted_id)}}";
        var check_url = "{{action('Checkout\PaymentGatewayController@payplusCheck')}}";
        var waiting_url = "{{action('Checkout\CartController@payplusWaiting')}}";
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
                            console.log(data);
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