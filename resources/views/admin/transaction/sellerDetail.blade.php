@extends('layouts/admin/default')

@section('title')
    @lang('admin_shop.shop_detail')
@stop

@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{Config('constants.admin_css_url') }}order.css">
    <link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}flatpickr.min.css">
@stop

@section('content')
<div class="content">
    <div class="header-title">
        <h1 class="title">@lang('admin_shop.shop_detail')</h1>       
        <div class="float-right">
            <a href="{{ action('Admin\Transaction\ShopOrderController@sellerOrder') }}?filter_date={{$order_date}}&refresh=refresh" class="btn-back">@lang('admin_common.back')</a>

        </div>
    </div>
    <div class="content-wrap">
        <div class="breadcrumb">
            <ul class="bredcrumb-menu">
                {!!getBreadcrumbAdmin('shoporder')!!}
            </ul>
        </div>
        <div class="order-create pt-0">
            <div class="col-sm-12"> 
                <div class="shadow-box">
                    <form action="{{action('Admin\Transaction\ShopOrderController@sellerDetail')}}" method="GET" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-sm-4 form-group">
                                <label>@lang('admin_report.date') <i class="red">*</i></label>
                                <input type="hidden" name="shop_id" value="{{$shop_details->id}}">
                                <input type="text" class="date-select-new date-picker" name="order_date" id="reservationtime" value="{{$order_date}}">
                                
                            </div>
                            <div class="col-sm-2 text-right">
                               <button class="btn btn-primary" name="refresh">@lang('admin_report.submit')</button>
                            </div>
                        </div>
                        
                    </form>
                    <div class="buy-pay-info row clearfix">
                        <div class="col-sm-12">
                            <div class="border-box mb-5">
                                <div class="clearfix buy-pay-add">
                                    <h3 class="buy-title">@lang('admin_shop.shop_detail')</h3>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <div class="tInfo-row">
                                                <img src="{{getImgUrl($shop_details->logo,'logo')}}" alt="" id="logo_image_thumb">
                                                
                                            </div>
                                            <div class="tInfo-row">
                                                {{$shop_details->shopDesc->shop_name}}
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="tInfo-row">
                                                <label>@lang('shop.market') : </label>
                                                {{$shop_details->market}}
                                            </div>
                                            <div class="tInfo-row">
                                                <label>@lang('shop.panel') : </label>
                                                {{$shop_details->panel_no}}
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="tInfo-row">
                                                <label>@lang('shop.store_url') : </label>
                                                {{$shop_details->shop_url}}
                                            </div>
                                            <div class="tInfo-row">
                                                <label>@lang('common.phone_number') : </label>
                                                {{$shop_details->ph_number}}
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="tInfo-row">
                                                <label>@lang('admin_customer.seller_description') : </label>
                                                {{$shop_details->shopDesc->description}}
                                            </div>
                                            <div class="tInfo-row">
                                                <label>@lang('shop.shop_status') : </label>
                                                {{$shop_details->shop_status}}
                                            </div>
                                        </div>
                                        
                                        
                                    </div>
                                </div>
                               
                            </div>
                        </div>
                    </div>

                    <h2 class="mb-2">@lang('admin_order.shop_order') </h2>
                    <div class="border-box order-table border-none">
                        <div class="table-wrapper cart-col col-sm-6">
                            <div class="table">
                                <div class="table-header">
                                    <ul>
                                        <li>@lang('admin_order.shop_order_id')</li> <li>@lang('checkout.grand_total')</li>
                                    </ul>
                                </div>
                                <div class="table-content">
                                    @foreach($order_shop as $key => $val)
                                        
                                        <ul>
                                            <li>{{$val->shop_formatted_id}}</li> 
                                            <li>
                                                {{numberFormat($val->total_final_price) }} @lang('common.baht')
                                            </li> 
                                            
                                                   
                                        </ul>
                                    @endforeach                     
                                    
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    
                </div>
            </div>
            
        </div>
    </div>
    
</div>
@stop

@section('footer_scripts')
<script src="{{ Config('constants.admin_js_url') }}flatpickr.min.js"></script>
    <!-- begining of page level js -->
    <script>
        var csrftoken = window.Laravel.csrfToken;
           $(document).ready(function() {
            $(".date-picker").flatpickr({});
        });
    </script>
@stop