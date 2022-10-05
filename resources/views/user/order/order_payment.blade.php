@extends('layouts.app')

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/dataTables.bootstrap', 'css/myaccount', 'css/cropper.min'],'css') !!}
<style type="text/css">
    .sel-pay-method .radio-wrap .radio-mark:before {
        opacity: 0;display: none;
    }
    .sel-pay-method .radio-wrap .radio-mark {
        display: inline-block;
        padding: 0;
    }
    .sel-pay-method input[type="radio"]:checked + .radio-mark:after {
        opacity: 0;
        display: none;
    }
    .sel-pay-method ul input[type="radio"]:checked + .radio-mark {
        color: #CE232A;
        font-weight: bold;
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
</style>
@endsection

@section('header_script')    
@stop

@section('breadcrumbs')
@stop

@section('content')
        
<div class="track-order-num-wrap border-bottom pb-2">
    <div class="cust-ord-num d-flex align-items-center">
        <h2>@lang('order.order_no'). {{$main_order->formatted_id}}</h2>
        <div class="btn-group ml-auto">
            <a href="{{ url()->previous() }}" class="btn-grey"><i>&lt;</i> @lang('common.back')</a>
            <!-- <button class="btn-default">Print</button> -->
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
            <label>@lang('checkout.pickup_time')</label>
            <select class="" name="pickup_time" id="pickup_time">
                <option value="">Select</option>
                @foreach($pickup_time_arr as $key => $val)
                    <option value="{{$val['key']}}">{{$val['val']}}</option>
                @endforeach
            </select>
            <p class="error" id="e_pickup_time"></p>
            <input type="hidden" name="nexday" value="">
        </div>
        
    </div>
    <div class="table-responsive checkout-order-table cartpage-tbl">        
        <div id="payment_method_div">
            <div class="step-title">
                    <span class="step-num">2</span>
                    <h3>@lang('checkout.select_payment_method')<i class="red">*</i></h3>
            </div>
          
            <div class="sel-pay-method">
                <ul>
                    @if(count($payment_option))
                        @foreach($payment_option as $pkey => $pval)
                            @if($pval->slug!='odd' ||($pval->slug=='odd' && !empty($user_odd_info) && $user_odd_info->espa_id!=''))
                            <li>
                                <a href="javascript:void(0)">
                                    <label class="radio-wrap">
                                        <input type="radio" name="payment_method" value="{{ $pval->id }}">
                                        <span class="radio-mark"><div class="bank-img-block">
                                            <img src="{{ getPayImgUrl($pval->image_name) }}" alt="">
                                        </div>
                                        <div class="bank-name">{{ $pval->paymentOptName->payment_option_name??'' }}</div>
                                        </span>
                                    </label>
                                </a>
                            </li>
                            @endif
                        @endforeach
                    @endif
                </ul>
                <p id="e_payment_method" class="error"></p>
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
                            @elseif($main_order->shipping_method == 2)
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
        <div class="table-footer clearfix">
            <div class="col-sm-10 float-right">
                <div class="row form-group">
                    <span class="col-6">@lang('checkout.total')</span>
                    <span class="col-6">{{numberFormat($main_order->total_core_cost)}} @lang('common.baht')</span>
                </div>  
                <!-- ///////////////////// -->
                @if($main_order->total_shipping_cost>0)
                <div class="row form-group">
                    <span class="col-6">@lang('checkout.shipping_charge')</span>
                    <span class="col-6">{{numberFormat($main_order->total_shipping_cost)}} @lang('common.baht')</span>
                </div>
                @else
                    @if($total_logistic_cost>0)
                    <div class="row form-group">
                        <span class="col-6">@lang('checkout.delivery_fee')</span>
                        <span class="col-6">{{numberFormat($total_logistic_cost)}} @lang('common.baht')</span>
                    </div>
                    <div class="row form-group">
                        <span class="col-6">@lang('checkout.discount_delivery_fee')</span>
                        <span class="col-6"> - {{numberFormat($total_logistic_cost)}} @lang('common.baht')</span>
                    </div>
                    @endif

                @endif
                <!-- ///////////////// -->
                <div class="bg form-group">
                    <div class="row">
                        <span class="col-6">@lang('order.grand_total')</span>
                        <span class="col-6">{{numberFormat($main_order->total_final_price)}} @lang('common.baht')</span>
                    </div>
                </div>

                <div class="row form-group">                               
                    <button type="button" class="col-12 btn-blue2" id="btn_checkout">@lang('checkout.confirm_order_to_end_shopping')</button>
                </div>
            </div>
        </div>
    </div> 
</form> 
                
@endsection

@section('footer_scripts')
    <script type="text/javascript">
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
    </script>
    {!! CustomHelpers::combineCssJs(['js/jquery.dataTables','js/dataTables.bootstrap', 'js/user/myaccount'],'js') !!}   
    <script type="text/javascript">
        jQuery('body').on('click', '#btn_checkout', function(e){
            //check payment method selection 
            /*if($('input[name=payment_method]:checked').length && !$('input[name=payment_method]:checked').parents('li').hasClass('active')){
                $('input[name=payment_method]:checked').prop('checked', false);
            }*/
            var error_str = '';
            
            var pickup_time = $('select[name=pickup_time]').val();
            
            if(pickup_time == '' || typeof pickup_time == 'undefined'){
                $('#e_pickup_time').html(error_msg.select_pickup_time);
                error_str += '<p class="error">'+error_msg.select_pickup_time+'</p>';
            }else{
                $('#e_pickup_time').html('');
            }
            
            var payment_method = $('input[name=payment_method]:checked').val();
            //console.log(payment_method);
            if(payment_method == '' || typeof payment_method == 'undefined'){
                
                $('#e_payment_method').html(error_msg.select_payment);
                error_str += '<p class="error">'+error_msg.select_payment+'</p>';
            }else{
                $('#e_payment_method').html('');
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