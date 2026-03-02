@extends('layouts/admin/default')

@section('title')
    @lang('admin_product.product_list')
@stop

@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{Config('constants.admin_css_url') }}order.css">
    
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
@stop

@section('content')


<div class="content">
    <div class="header-title">
        <h1 class="title">@lang('checkout.order_no'). {{ $main_order->formatted_id }}</h1>       
        <div class="float-right">
            @if($main_order->logistic_status != '1' && $main_order->end_shopping_date!=null && $main_order->shipping_method !=2)
                <button id="btn-resend" class="btn-primary">Resend to logistic</button>
            @endif
            <a href="{{ action('Admin\Transaction\OrderController@index') }}" class="btn-back">@lang('admin_common.back')</a>
            <a href="{{ action('Admin\Transaction\OrderController@orderDetailExport',$main_order->formatted_id) }}" class="btn-back">@lang('admin_common.export_order_pdf')</a>
        
        </div>
    </div>
    <div class="content-wrap">
        <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('order')!!}
                </ul>
            </div>
        <div class="order-create pt-0">
            <div class="col-sm-9 aaa"> 
                <div class="shadow-box">
                    <div class="border-box order-status-wrap clearfix border-none">
                        <div class="row">
                            <div class="col-sm-7">
                                <div class="box-status">
                                    <div class="row">
                                        <div class="col-md-5 pr-0">
                                            <h3 class="status-heading">@lang('admin_order.main_order_status') : </h3>
                                        </div>
                                        <div class="col-md-7">
                                            <div class="d-flex align-items-center">
                                                <div class="order-status-content m-0 p-0" style="min-height: auto;">
                                                    <span class="processing mr-3">{{ $main_order->getOrderStatus->status??'' }} </span>
                                                    @if($main_order->order_status == '1' || (explode(' ',$main_order->pickup_time)[0].' 17:58' > now()->format('Y-m-d H:i') && $main_order->order_status == '4'))
                                                        <a href="javascript::void(0);" onclick="$('#change_status_option').show();$(this).hide();">@lang('admin_common.change')</a>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="order-status-content m-0 px-0" id="change_status_option" style="display:none;">
                                                <div class="d-flex align-items-center">
                                                    <select id="order_status_id" name="order_status_id" style="max-width: 170px;" class="mr-2">
                                                        <option value="">--@lang('admin_order.select_new_status')--</option>
                                                        <option value="2">@lang('admin_order.preparing_goods')</option>
                                                        <!--<option value="4">@lang('admin_common.cancel')</option>-->
                                                    </select>
                                                    <a href="javascript::void(0);" id="update_order_status" class="btn btn-primary">@lang('admin_common.update')</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>                              
                                </div>
                            </div>
                            <div class="col-sm-5">
                               <div class="box-status border-none">
                                  <h3 class="status-heading">@lang('checkout.total')</h3>
                                  <div class="order-status-content">
                                     <h3><strong>@lang('common.thb') {{ numberFormat($main_order->total_final_price) }}</strong></h3>
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
                                                <span class="label">@lang('common.name') :</span> {{$main_order->user_name}}
                                            </div>
                                            <div class="tInfo-row">
                                                <span class="label">@lang('common.email') :</span> {{$main_order->user_email}}
                                            </div>
                                            <div class="tInfo-row">
                                                <span class="label">@lang('common.tel') :</span>  {{$main_order->ph_number}}
                                            </div>
                                        </div>
                                        @if($main_order->shipping_method == 1)
                                            <div class="col-sm-3">
                                                <h4><strong>@lang('admin_order.center_address') : </strong></h4>
                                                {!! CustomHelpers::centerAddress($main_order->order_json) !!}
                                            </div>
                                        @elseif($main_order->shipping_method == 2)
                                            <div class="col-sm-3">
                                                <h4><strong>@lang('admin_order.store_address') : </strong></h4>
                                                {!! CustomHelpers::storeAddress($main_order->order_json) !!}
                                            </div>
                                        @else
                                        
                                            <div class="col-sm-3">
                                                <h4><strong>@lang('checkout.shipping_address') : </strong></h4>
                                                {{ CustomHelpers::buyerShipBillTo($main_order->order_json,'shipping_address') }}
                                                @if($main_order->order_status == '2' && ($main_order->pickup_time > now()->format('Y-m-d H:i')))
                                                <a href="javascript::void(0);" onclick="$('#change_Shipping_address').show();$(this).hide();">เปลี่ยนที่อยู่การจัดส่ง</a>
                                                @endif
                                                <div class="order-status-content m-0 px-0" id="change_Shipping_address" style="display:none;">
                                                    <div class="block-add-address">
                                                        <select class="selectpicker" name="ship_address" id="dd_shipping">
                                                            <option value="">@lang('checkout.select_address')</option>
                                                            @if(count($user_address))
                                                                @foreach($user_address as $skey => $sval)
                                                                    <option value="{{ $sval->id}}" @if($shipping_address && $shipping_address->id == $sval->id) selected="selected" @endif>{{ $sval->title }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                         
                                                    </div>
                                                    <a href="javascript::void(0);" id="update_shipping_address" class="btn btn-primary">@lang('admin_common.update')</a>
                                                    <address class="post-address" id="shipping_address">
                                                        @if($shipping_address)
                                                            
                                                            {{-- <p>{{$shipping_address->address.', '.$shipping_address->road}}</p>
                                                            <p>{{$shipping_address->city_district.', '.$shipping_address->province_state.', '.$shipping_address->zip_code}}</p>
                                                            <p>@lang('customer.tel'). {{$shipping_address->ph_number}}</p> --}}
                                                            @if($shipping_address)
                                                            <table>
                                                                <tr>
                                                                    <td class="d-flex pt-2"><ion-icon name="location-outline" class="mr-2"></ion-icon></td>
                                                                    <td>
                                                                        <p>{{$billing_address->first_name.' '.$billing_address->last_name}}</p>
                                                                        <p>{{$billing_address->address.', '.$billing_address->road}}</p>
                                                                        <p>{{$billing_address->city_district.', '.$billing_address->province_state.', '.$billing_address->zip_code}}</p>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><ion-icon name="call-outline" class="mr-2"></ion-icon></td>
                                                                    <td><p>{{$billing_address->ph_number}}</p></td>
                                                                </tr>
                                                            </table>
                                                        @endif
                                                    </address> 
                                                </div>
                                                
                                            </div>
                                        
                                            <div class="col-sm-3">
                                                <h4><strong>@lang('checkout.billing_address') : </strong></h4>
                                                {{ CustomHelpers::buyerShipBillTo($main_order->order_json,'billing_address') }}
                                            </div>
                                        @endif
                                        <div class="col-sm-3">
                                            <h4><strong>@lang('admin_order.shipping') : </strong></h4>
                                            <div class="form-group">
                                               <span>{{ GeneralFunctions::getShippingMethod($main_order->shipping_method) }}</span>
                                               <br>

                                               @if($main_order->shipping_method!=3 && $main_order->user_phone_no !='')
                                               <span>@lang('admin_order.phone_no') : {{$main_order->user_phone_no}}</span>
                                               @endif
                                            </div>
											
											<h4><strong>@lang('admin_order.pickup_date') : </strong></h4>
                                            <div class="form-group">
                                               <span>{{$main_order->pickup_time}}</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <div class="order-status-content m-0 p-0" style="min-height: auto;">                                                    
                                                    @if($main_order->order_status == '2' && ($main_order->pickup_time > now()->format('Y-m-d H:i')))
                                                        <a href="javascript::void(0);" onclick="$('#change_pickup_time').show();$(this).hide();">เปลี่ยนรอบการจัดส่ง</a>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="order-status-content m-0 px-0" id="change_pickup_time" style="display:none;">
                                                <div class="col-sm-4">                                                        
                                                    <select style="width: 170px;" class="mr-2" name="pickup_time" id="pickup_time_id">                                                            
                                                            @foreach($pickup_time_arr as $key => $val)
                                                                <option value="{{$val['key']}}">{{$val['val']}}</option>
                                                            @endforeach
                                                    </select>
                                                    <p class="error" id="e_pickup_time"></p>
                                                    <input type="hidden" name="nexday" value="">
                                                </div>
                                                <a href="javascript::void(0);" id="update_pickup_time" class="btn btn-primary">@lang('admin_common.update')</a>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                               
                            </div>
                            <div class="border-box order-table border-none mb-5">
                                <div class="table-wrapper cart-col text-center">
                                    <div class="table-footer mid-align">
                                        <div class="footer-row row m-0">
                                            <span class="col-6">@lang('checkout.total_seller')</span>
                                            <span class="col-6">{{ $main_order->tot_shop }}</span>
                                        </div>
                                        <div class="footer-row row m-0">
                                            <span class="col-6">@lang('checkout.total')</span>
                                            <span class="col-6">{{numberFormat($main_order->total_core_cost)}} @lang('common.baht')</span>
                                        </div>
                                        <!-- <div class="footer-row row m-0">
                                            <span class="col-6">@lang('checkout.item_total')</span>
                                            <span class="col-6">9,0000 THB</span>
                                        </div> -->
                                        <div class="footer-row row m-0">
                                            <span class="col-6">@lang('checkout.shipping_charge')</span>
                                            <span class="col-6">{{numberFormat($main_order->total_shipping_cost)}} @lang('common.baht')</span>
                                        </div>
                                        <div class="footer-row total row m-0">
                                            <span class="col-6">@lang('checkout.grand_total')</span>
                                            <strong class="col-6">{{numberFormat($main_order->total_final_price)}} @lang('common.baht')</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(count($order_shop))
                        @foreach($order_shop as $skey => $shop_ord_val)
                            <div class="d-flex align-items-center">
                                <h2 class="mb-2">@lang('admin_order.shop_order_id') : {{ $shop_ord_val->shop_formatted_id }}</h2>
                                <span class="text-failed ml-auto">@lang('admin_order.shop_order_status') : {{$shop_ord_val->getOrderStatus->status}}</span>
                            </div>
                            <div class="border-box order-table border-none mb-3">
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
                                                <li>@lang('checkout.remark')</li>
												<li>@lang('checkout.details')</li>
                                            </ul>
                                        </div>
                                        <div class="table-content">
                                            @foreach($shop_ord_val->details as $key => $val)
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
                                                    <li>
                                                        {{numberFormat($val->last_price) }} @lang('common.baht') /{{ $detail_json['package'][session('default_lang')] ?? $val->package_name }}
                                                       

                                                    </li>
                                                    <li class="add-rem-qty">
                                                        {{ $val->quantity }} {{ $detail_json['package'][session('default_lang')] ?? $val->package_name }}
                                                         <br/>
                                                        <span class="red">{{convertString($val->total_weight) }}
                                                        {{$val->base_unit}} / 
                                                        {{$val->package_name}}</span>
                                                    </li>

                                                    <li>
                                                        {{numberFormat($val->total_price) }} @lang('common.baht')
                                                    </li>
													
                                                    <!-- <li>{{ $val->payment_type=='credit'? numberFormat($val->total_price):'' }} @lang('common.baht')</li> -->

                                                    <li>{!! CustomHelpers::formatPaymentMethodName($val->payment_slug, $detail_json['payment_method'] ?? null) !!}</li>
                                                    <li class="red" id="item_status_{{ $val->id }}">{{ $val->getOrderStatus->status??'' }}</li>
                                                    {{-- <li><a href="javascript:;" data-type='cancel' data-val="{{ $val->id }}" class="ord_item_change">@lang('common.cancel')</a> | <a href="javascript:;" data-type='receive' data-val="{{ $val->id }}" class="ord_item_change">@lang('admin_order.center_received')</a></li>      --}}
                                                    <li>
                                                        {{$val->api_remark}}
                                                    </li>
													@php 
														$str_description = $val->description;
														$str_description = strip_tags($str_description);
														$str_description = mb_substr($str_description, 0, 30);
													@endphp
													<li>{!!$str_description!!}</li> 
                                                </ul>
                                            @endforeach                     
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>@lang('checkout.shop_remark')</label>
                                            <label class="font-weight-bold">{{$shop_ord_val->api_remark}}</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label>&nbsp;</label>
                                        <div class="table-footer w-100">
                                            <div class="footer-row row m-0">
                                                <span class="col-6">@lang('checkout.total')</span>
                                                <span class="col-6">{{numberFormat($shop_ord_val->total_core_cost)}} @lang('common.baht')</span>
                                            </div>
                                            
                                            <div class="footer-row total row m-0">
                                                <span class="col-6">@lang('checkout.grand_total')</span>
                                                <strong class="col-6">{{numberFormat($shop_ord_val->total_final_price)}} @lang('common.baht')</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>@lang('checkout.main_order_remark')</label>
                                <label class="font-weight-bold">{{$main_order->api_remark}}</label>
                            </div>
                        </div>
                    </div>
                    <form name="remark-form" method="post" id="remark-form" action="{{action('Admin\Transaction\OrderController@updateRemark')}}">
                        <input type="hidden" name="order_id" value="{{$main_order->id}}">
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <label>@lang('admin_order.remark')</label>
                                <textarea name="remark" required="required" id="txt_remark" placeholder="Remark text ...">{{$main_order->admin_remark}}</textarea>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-primary" id="btn-remark">@lang('admin_common.save')</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>          
            </div>
            <div class="col-sm-3 right-sidebar">
                <div class="order-title clearfix">
                    <h4><i class="fas fa-chevron-left skyblue"></i>@lang('admin_order.order') # {{ $main_order->formatted_id }}</h4>
                </div>
                <div class="sidebox history">
                    <h2 class="mb-2">@lang('admin_order.history')</h2>
                    <div class="historyBox sidebox">
                        <div class="order-list-row history-list border-none">
                            @if(count($transaction))
                                @foreach($transaction as $key => $value)
                                    <div class="">
                                        <span class="ord-txt">{{$value->comment}}<label class="ml-1" style="color:lightslategray;">-by {{$value->updated_by}}</label></span>
                                        <span class="time"><b>{{getDateFormat($value->created_at,1)}}</b>
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
    var lang_cancel = "@lang('admin_order.are_you_sure_want_to_cancel_this_items')";
    var lang_receive = "@lang('admin_order.are_you_sure_want_to_receive_this_items')";
    var update_status_confirm = "@lang('admin_order.are_you_sure_to_update_status')";
    var update_status_error = "@lang('admin_order.select_order_status_to_update')";
    var update_pickup_time_error = "เกิดความผิดพลาด กรุณาติดต่อ admin"
    var update_pickup_time_confirm = "ยืนยันการปรับรอบการจัดส่งสินค้า";
    var update_shipping_address = "ยืนยันการเปลี่ยนที่อยู่การจัดส่ง";
    var change_url = "{{ action('Admin\Transaction\OrderController@ordChangeItemStatus') }}";
    var resend_url = "{{ action('Admin\Transaction\OrderController@resendLogistic') }}";

    jQuery('#btn-remark').click(function(evt) {
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

    //for re send order to logistic
    jQuery('#btn-resend').click(function(evt) {
        evt.preventDefault();
        
        var order_id = {{$main_order->id}};
        var data = {'order_id':order_id};
        callAjax(resend_url, 'post', data, function(result) {

                if(result.status=='fail'){
                    showSweetAlertError(result.msg);
                   
                    return false;

                }else if(result.status=='success'){
                    swal('success', result.msg, "success");       
                }
        });
    });


    $('body').on('click','#update_order_status',function() {

        swal({
            text: update_status_confirm,
            type: 'warning',
            showCancelButton: true,
        }).then(function () {
            
            var order_status_id = $('#order_status_id').val();
            if(order_status_id == '') {
                swal('', update_status_error, 'error');
            }
            else {
                var order_id = {{$main_order->id}};
                var ajax_url = "{{action('Admin\Transaction\OrderController@updateOrderStatus')}}";
                var data = {'order_id':order_id, 'order_status_id':order_status_id};

                showHideLoaderAdmin('showLoader');
                callAjax(ajax_url, 'post', data, function(result) {
                    showHideLoaderAdmin('hideLoader');
                    swal('', result.msg, result.status).then(function() {
                        location.reload();
                    });                    
                });
            }
        });
    });
    
    $('body').on('click','#update_pickup_time',function() {

        var pickup_time_id = $('#pickup_time_id').val();
        var order_id = {{$main_order->id}};
        var pickup_time_text = $('#pickup_time_id option:selected').text(); 
        swal({
            text: update_pickup_time_confirm + ' ' + pickup_time_id + ' ' + pickup_time_text + ' ' + order_id,
            type: 'warning',
            showCancelButton: true,
        }).then(function () {
    
            //var order_status_id = $('#pickup_time_id').val();
            if(pickup_time_id == '') {
                swal('', update_pickup_time_error, 'error');
            }
            else {
                
                var ajax_url = "{{action('Admin\Transaction\OrderController@updatePickupTime')}}";
                var data = {'order_id':order_id, 'pickup_time_id':pickup_time_text};
                
                //showHideLoaderAdmin('showLoader');
                callAjax(ajax_url, 'post', data, function(result) {
                //howHideLoaderAdmin('hideLoader');
                swal('', result.msg, result.status).then(function() {
                    location.reload();
                    });                    
                });
                
            }
        });
    });

    $('body').on('change','#dd_shipping',function() {
        var shipId = $('#dd_shipping').val();
        var userid = {{$main_order->user_id}};
        var data = {'shipping_address_id':shipId,'userid':userid};
        
        var ajax_url = "{{action('Admin\Transaction\OrderController@getShippingAddress')}}";
        
        if(shipId){
            
            callAjaxRequest(ajax_url,"post",data,function(result) {
            var response = jQuery.parseJSON(result); 
            if(response.status=='success'){
                
                $('#shipping_address').html(response.shipVal);
            }
            });

        }else{
            
            $('#shipping_address').html('');
        }        
    });

    $('body').on('click','#update_shipping_address',function() {

        var ship_address_id = $('#dd_shipping').val();
        var order_id = {{$main_order->id}};
        
        swal({
            text: update_shipping_address + ' ' + ship_address_id + ' ' + order_id,
            type: 'warning',
            showCancelButton: true,
        }).then(function () {

            //var order_status_id = $('#pickup_time_id').val();
            if(pickup_time_id == '') {
                swal('', update_shipping_address, 'error');
            }
            else {
        
                var ajax_url = "{{action('Admin\Transaction\OrderController@changeShippingAddress')}}";
                var data = {'order_id':order_id, 'shipId':ship_address_id};
        
                //showHideLoaderAdmin('showLoader');
                callAjax(ajax_url, 'post', data, function(result) {
                //howHideLoaderAdmin('hideLoader');
                swal('', result.msg, result.status).then(function() {
                    location.reload();
                    });                    
                });
        
            }
        });
    });

</script>
<script src="{{ Config('constants.admin_js_url') }}order/order.js"></script>
@stop
