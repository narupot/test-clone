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
    </style>
@endsection

@section('header_script')

@endsection

@section('content')
<div >
    <div class="confirmation">
        <h1>@lang('checkout.thank_you_for_using_the_simummuang_market')</h1>
        <div class="confirm-msg">
            <div class="track-msg">@lang('checkout.order_thanks_message')
            </div>
            <p class="red"> @lang('checkout.thank_you_for_your_trust_in_our_service')</p>
            <div class="text-center">
                <a href="/" class="btn-grey">@lang('checkout.continue_shopping')</a>
                <a class="btn" href="{{ action('HomeController@index') }}/track-order"><i class="fas fa-truck"></i> @lang('checkout.tracking_order')</a>
            </div>
        </div>
    </div>
    <div class="text-right mb-3">
        <button class="btn" onclick="printDiv('printsection')">@lang('common.print')</button>
    </div>
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
                                {{ CustomHelpers::buyerShipBillTo($main_order->order_json,'billing_address') }}
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
                                        <span class="la"><img src="{{ getBadgeImageUrl($detail_json['badge']['icon'] ?? '' )}}" width="30"></span>
                                    </div>
                                </div>                                                
                            </li>

                            <li>{{numberFormat($val->last_price) }} @lang('common.baht') /{{ $detail_json['package'][session('default_lang')] ?? $val->package_name }}</li>

                            <li class="add-rem-qty">
                                {{ $val->quantity }} {{ $detail_json['package'][session('default_lang')] ?? $val->package_name }}
                            </li>

                            <li>
                                {{numberFormat($val->total_price) }} @lang('common.baht')
                            </li>  

                            <li>{{ $val->payment_type=='credit'? numberFormat($val->total_price):'' }} @lang('common.baht')</li>     

                            <li>{{$detail_json['payment_method'][session('default_lang')] ?? str_replace('_',' ',strtoupper($val->payment_slug)) }}</li>     

                        </ul>
                    @endforeach                     
                    
                </div>
            </div>
            <div class="checkout-table-footer clearfix">
                <div class="col-sm-5 float-right">
                    <div class="row">
                        <span class="col-6">@lang('checkout.total')</span>
                        <span class="col-6">{{numberFormat($main_order->total_core_cost)}} @lang('common.baht')</span>
                    </div>
                    <!-- ///////////////////// -->
                    @if($main_order->total_shipping_cost>0)
                    <div class="row">
                        <span class="col-6">@lang('checkout.shipping_charge')</span>
                        <span class="col-6">{{numberFormat($main_order->total_shipping_cost)}} @lang('common.baht')</span>
                    </div>
                    @else
                        @if($total_logistic_cost>0)
                        <div class="row">
                            <span class="col-6">@lang('checkout.delivery_fee')</span>
                            <span class="col-6">{{numberFormat($total_logistic_cost)}} @lang('common.baht')</span>
                        </div>
                        <div class="row">
                            <span class="col-6">@lang('checkout.discount_delivery_fee')</span>
                            <span class="col-6"> - {{numberFormat($total_logistic_cost)}} @lang('common.baht')</span>
                        </div>
                        @endif

                    @endif
                    <!-- ///////////////// -->
                    <div class="bg-grey">
                        <div class="row">
                            <span class="col-6">@lang('checkout.grand_total')</span>
                            <span class="col-6">{{numberFormat($main_order->total_final_price)}} @lang('common.baht')</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

</div>

<script type="text/javascript">
    function printDiv(id){
            var printContents = document.getElementById(id).innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
    }
</script>
@endsection