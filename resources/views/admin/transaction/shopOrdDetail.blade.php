@extends('layouts/admin/default')

@section('title')
    @lang('admin_product.product_list')
@stop

@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{Config('constants.admin_css_url') }}order.css">

@stop

@section('content')
<div class="content">
    <div class="header-title">
        <h1 class="title">@lang('checkout.order_no'). {{ $order_shop->shop_formatted_id }}</h1>       
        <div class="float-right">
            <a href="{{ action('Admin\Transaction\ShopOrderController@index') }}" class="btn-back">@lang('admin_common.back')</a>

            @if($order_shop->end_shopping_date && $order_shop->order_status !=3 && $order_shop->order_status !=4)
                <a href="javascript:;" data-val="{{ $order_shop->shop_formatted_id }}" data-type="complete" class="btn-primary ord_status_change">@lang('admin_order.complete_order')</a>

                <a href="javascript:;" data-val="{{ $order_shop->shop_formatted_id }}" data-type="cancel" class="btn-danger ord_status_change">@lang('admin_order.cancel_order')</a>
            @endif
        </div>
    </div>
    <div class="content-wrap">
        <div class="breadcrumb">
            <ul class="bredcrumb-menu">
                {!!getBreadcrumbAdmin('shoporder')!!}
            </ul>
        </div>
        <div class="order-create pt-0">
            <div class="col-sm-9"> 
                <div class="shadow-box">
                    <div class="border-box order-status-wrap clearfix border-none">
                        <div class="row">
                            <div class="col-sm-6">
                               <div class="box-status">
                                  <h3 class="status-heading">@lang('admin_order.order_status')</h3>
                                  <div class="order-status-content">
                                     <span class="processing" id="shop_status">{{ $order_shop->getOrderStatus->status??'' }}</span>
                                  </div>
                               </div>
                            </div>
                            <div class="col-sm-6">
                               <div class="box-status border-none">
                                  <h3 class="status-heading">@lang('checkout.total')</h3>
                                  <div class="order-status-content">
                                     <h3><strong>@lang('common.thb') {{ numberFormat($order_shop->total_final_price) }}</strong></h3>
                                  </div>
                               </div>
                            </div>
                        </div>
                    </div>
                    <div class="buy-pay-info row clearfix">
                        <div class="col-sm-12">
                            <div class="border-box mb-5">
                                <div class="clearfix buy-pay-add">
                                    <h3 class="buy-title">@lang('admin_order.buyer_information')</h3>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <div class="tInfo-row">
                                                <span class="label">@lang('common.name') :</span> {{$order_shop->user_name}}
                                            </div>
                                            <div class="tInfo-row">
                                                <span class="label">@lang('common.email') :</span> {{$order_shop->user_email}}
                                            </div>
                                            <div class="tInfo-row">
                                                <span class="label">@lang('common.tel') :</span>  {{$order_shop->ph_number}}
                                            </div>
                                        </div>
                                        @if($order_shop->shipping_method == 1)
                                            <div class="col-sm-3">
                                                <h4><strong>@lang('admin_order.center_address') : </strong></h4>
                                                {!! CustomHelpers::centerAddress($order_shop->order_json) !!}
                                            </div>
                                        @elseif($order_shop->shipping_method == 2)
                                            <div class="col-sm-3">
                                                <h4><strong>@lang('admin_order.store_address') : </strong></h4>
                                                {!! CustomHelpers::storeAddress($order_shop->order_json) !!}
                                            </div>
                                        @else
                                            <div class="col-sm-3">
                                                <h4><strong>@lang('checkout.shipping_address') : </strong></h4>
                                                {{ CustomHelpers::buyerShipBillTo($order_shop->order_json,'shipping_address') }}
                                            </div>
                                            <div class="col-sm-3">
                                                <h4><strong>@lang('checkout.billing_address') : </strong></h4>
                                                {{ CustomHelpers::buyerShipBillTo($order_shop->order_json,'billing_address') }}
                                            </div>
                                        @endif
                                        <div class="col-sm-3">
                                            <h4><strong>@lang('admin_order.shipping') : </strong></h4>
                                            <div class="form-group">
                                               <span>{{ GeneralFunctions::getShippingMethod($order_shop->shipping_method) }}</span>
                                            </div>
											
											<h4><strong>@lang('admin_order.pickup_date') : </strong></h4>
                                            <div class="form-group">
                                               <span>{{$order_shop->pickup_time}}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                               
                            </div>
                        </div>
                    </div>

                    <h2 class="mb-2">@lang('admin_order.shop_order_id') : {{ $order_shop->shop_formatted_id }}</h2>
                    <div class="border-box order-table border-none">
                        <div class="table-wrapper cart-col">
                            <div class="table">
                                <div class="table-header">
                                    <ul>
                                        <li>@lang('checkout.product')</li>                              
                                        <li>@lang('checkout.seller')</li>
                                        <li>@lang('checkout.unit_price')</li>
                                        <li>@lang('checkout.qty')</li>
                                        <li>@lang('checkout.price')</li>
                                        <!-- <li>@lang('checkout.credit_from_shop')</li> -->
                                        <li>@lang('checkout.payment_method')</li>
                                        <li>@lang('common.status')</li>
                                        <!-- <li>@lang('common.action')</li> -->
										<li>@lang('checkout.details')</li>
                                    </ul>
                                </div>
                                <div class="table-content">
                                    @foreach($order_shop->details as $key => $val)
                                        @php 
                                            $detail_json = jsonDecodeArr($val->order_detail_json);
                                            $shop_url = action('ShopController@index',$detail_json['shop_url'] ??'');
                                            $prd_url = action('ProductDetailController@display',[$detail_json['cat_url']??'',$val->sku]);
                                        @endphp
                                        <ul>
                                            <li class="product">
                                                <div class="flexwrap-box">
                                                <a href="{{ $prd_url }}">
                                                <span class="prod-img prod-134"><img src="{{ getProductImageUrlRunTime($detail_json['thumbnail_image']??'','thumb') }}" alt=""></span> </a>
                                                <span class="prod-name">
                                                <a href="{{ $prd_url }}" class="mb-2 d-block">{{ $detail_json['name'][session('default_lang')]??$val->category_name }}</a>

                                                <div class="la d-block"><img class="border-0" src="{{ getBadgeImageUrl($detail_json['badge']['icon'] ?? '' )}}" height="30"></div> 
                                                </span>
                                                
                                                </div>
                                            </li>
                                            <li class="product-shop">
                                                <a href="{{ $shop_url }}">
                                                <div class="flexwrap-box justify-content-center">
                                                <span class="prod-img seller-shop-img"><img src="{{getImgUrl($detail_json['logo'] ??'','logo')}}" width="50" height="50" alt=""></span>
                                                <span class="shopname"><a href="{{ $shop_url }}">{{ $detail_json['shop_name'][session('default_lang')]??'' }}</a></span>
                                                </div>
                                                </a>
                                            </li>                                      
                                            <li>{{numberFormat($val->last_price) }} @lang('common.baht') /{{ $detail_json['package'][session('default_lang')] ?? $val->package_name }}
                                                             
                                            </li>
                                            <li class="add-rem-qty">
                                                {{ $val->quantity }} {{ $detail_json['package'][session('default_lang')] ?? $val->package_name }}
                                                <br/>
                                            <span class="red">    
                                            {{convertString($val->total_weight) }} {{$val->base_unit}} / 
                                                        {{$val->package_name}}
                                             </span>           
                                            </li>

                                            <li>
                                                {{numberFormat($val->total_price) }} @lang('common.baht')
                                            </li>   

                                            <!-- <li>{{ $val->payment_type=='credit'? numberFormat($val->total_price):'' }} @lang('common.baht')</li> -->    

                                            <li>{!! $detail_json['payment_method'][session('default_lang')] ?? str_replace('_',' ',strtoupper($val->payment_slug)) !!}</li>   
                                            <li class="red" id="item_status_{{ $val->id }}">{{ $val->getOrderStatus->status??'' }}</li> 
											<!--
                                            @if(!$order_shop->end_shopping_date || $order_shop->order_status ==3 || $order_shop->order_status ==4)
                                            <li></li>
                                            @else
                                                
                                                <li>@if($val->status!=4) <a href="javascript:;" data-type='cancel' data-val="{{ $val->id }}" class="ord_item_change">@lang('common.cancel')</a> @endif | <a href="javascript:;" data-type='receive' data-val="{{ $val->id }}" class="ord_item_change">@lang('admin_order.center_received')</a></li> 
                                            @endif 
											-->
											@php 
												$str_description = $val->description;
												$str_description = strip_tags($str_description);
												$pattern_str = '/^[A-Za-z0-9]+$/';
												if (preg_match($pattern_str, $str_description)) {
													$strdesclen=30;
												} else {
													$strdesclen=90;
												} 
												if (strlen($str_description) > $strdesclen) {
													$stringCut = substr($str_description, 0, $strdesclen);
													$strEndPoint = strrpos($stringCut, ' ');
													$str_description = $strEndPoint? substr($stringCut, 0, $strEndPoint) : substr($stringCut, 0);
												}
											@endphp
											<li>{!!$str_description!!}</li>
                                        </ul>
                                    @endforeach                     
                                    
                                </div>
                            </div>
                            <div class="table-footer">
                                <div class="footer-row row m-0">
                                    <span class="col-6">@lang('checkout.total')</span>
                                    <span class="col-6">{{numberFormat($order_shop->total_core_cost)}} @lang('common.baht')</span>
                                </div>
                                
                                <div class="footer-row total row m-0">
                                    <span class="col-6">@lang('checkout.grand_total')</span>
                                    <strong class="col-6">{{numberFormat($order_shop->total_final_price)}} @lang('common.baht')</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form method="post" id="remark-form" action="{{action('Admin\Transaction\ShopOrderController@updateRemark')}}">
                        <input type="hidden" name="order_shop_id" value="{{$order_shop->id}}">
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label>@lang('admin_order.remark')</label>
                                <textarea name="remark" required="required" id="txt_remark">{{$order_shop->admin_remark}}</textarea>
                                <div class="mt-2">
                                    <button type="button" class="button button-primary" id="btn-remark">@lang('admin_common.save')</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-sm-3 right-sidebar">
                <div class="order-title clearfix">
                    <h4><i class="fas fa-chevron-left skyblue"></i>@lang('admin_order.order') #{{ $order_shop->shop_formatted_id }}</h4>
                </div>
                <div class="sidebox history">
                    <h2 class="mb-2">@lang('admin_order.history')</h2>
                    <div class="historyBox sidebox">
                        <div class="order-list-row history-list border-none">
                            @if(count($transaction))
                                @foreach($transaction as $key => $value)
                                    <div class="">
                                        <span class="ord-txt">{{ $value->comment }}</span>
                                        <span class="time"><b>{{ getDateFormat($value->created_at,1) }}</b>
                                        </span>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>
@stop

@section('footer_scripts')
<script type="text/javascript">
    var lang_ord_cancel = "@lang('admin_order.are_you_sure_want_to_cancel_this_items')";
    var lang_ord_complete = "@lang('admin_order.are_you_sure_want_to_complete_this_order')";
    var lang_cancel = "@lang('admin_order.are_you_sure_want_to_cancel_this_order')";
    var lang_receive = "@lang('admin_order.are_you_sure_want_to_receive_this_items')";
    var change_url = "{{ action('Admin\Transaction\OrderController@ordChangeItemStatus') }}";
    var ord_status_url = "{{ action('Admin\Transaction\ShopOrderController@changeShopOrderStatus') }}";
    var order_shop_id = "{{ $order_shop->id }}";

    jQuery('#btn-remark').click(function(evt){
        evt.preventDefault();
        if($('#txt_remark').val()==''){
            $('#txt_remark').focus();
            return false;
        }

        var formAction = $(this).closest('form').attr('action');
        var formId = $(this).closest('form').attr('id');
        var _this = $(formId);
        var form_data = new FormData($("#"+formId)[0]);

        _this.prop('disabled',false);
        callAjaxFormRequest(formAction,'post',form_data,function(result){

                if(result.status=='fail'){
                    showSweetAlertError(result.msg);
                    _this.prop('disabled',false);
                    return false;

                }else if(result.status=='success'){
                    swal('success', result.msg, "success");       
                }
        });
    });
</script>
<script src="{{ Config('constants.admin_js_url') }}order/order.js"></script>
@stop