@extends('layouts.app') 
@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/bootstrap-select','css/ui-grid-unstable','css/flatpickr.min','css/flickity'],'css') !!}

@endsection
@section('header_script')

var data_loading_in_chart_url = '{{action('Seller\SellerReportController@loadChartData')}}';

@endsection

@section('content')
<h1 class="page-title">@lang('seller_report.sale_report')</h1>
  
<ul class="sale-bal-block">
    <li class="bal-opening">
        <span>@lang('seller_report.total_sale_since_opening')</span>
        <span class="balance green">{{numberFormat($total_sale_scince_opening)}} @lang('common.baht')</span>
    </li>
    {{-- <li class="bal-outstanding">
        <span>@lang('seller_report.total_outstanding_balance_from_smm')</span>
        <span class="balance red">{{numberFormat($remaining_balance_from_smm)}} @lang('common.baht')</span>
    </li>
    <li class="bal-recieved">
        <span>@lang('seller_report.total_recieved_amount_from_smm')</span>
        <span class="balance">{{numberFormat($recieved_balance_from_smm)}} @lang('common.baht')</span>
    </li> --}}
</ul>
<ul class="sale-info-report">
    <li>
        <div class="page-title">@lang('seller_report.last_sale_report')</div>
        <ul class="info-schedule">
            <li class="daily-report">
                <span>@lang('seller_report.daily')</span>
                <span class="float-right">{{numberFormat($today_ord_anount)}} @lang('common.baht')</span>
            </li>
            <li>
                <span>@lang('seller_report.weekly')</span>
                <span class="float-right">{{numberFormat($last7days_ord_anount)}} @lang('common.baht')</span>
            </li>
            <li>
                <span>@lang('seller_report.monthly')</span>
                <span class="float-right">{{numberFormat($last30days_ord_anount)}} @lang('common.baht')</span>
            </li>
        </ul>
    </li>
    <li>
        <div class="page-title">@lang('seller_report.all_orders')<span class="details"><a href="{{action('Seller\OrderController@orderHistory')}}">@lang('seller_report.view_details')</a></span></div>
        <div class="all-order">
            <div class="count-order">
                <i class="fas fa-clipboard-list"></i>
                <span>{{$total_orders}}<span class="list">@lang('seller_report.list')</span> </span>
            </div>
        </div>
    </li>
    <li>
        <div class="page-title">@lang('seller_report.all_users')<span class="details"><a href="{{action('Seller\CustomerController@index')}}">@lang('seller_report.view_details')</a></span></div>
        <div class="all-order">
            <div class="count-order">
                <i class="fas fa-users"></i>
                <span>{{$total_users}}<span class="list">@lang('seller_report.list')</span> </span>
            </div>
        </div>
    </li>
</ul>
  
<div class="page-title">@lang('seller_report.sale_overview')</div>
<div class="sales-overview">
    @lang('seller_report.from')
    <input type="text" name="date_from" value="{{$from_date}}" placeholder="date" class="date-select line_chart_rep" id="date_from" >
    @lang('seller_report.to')
    <input type="text" name="date_to" value="{{$to_date}}" placeholder="date" class="date-select line_chart_rep" id="date_to">        
</div>  

<div class="graph-block" id="graph_block"></div>  
  

<div class="page-title">@lang('seller_report.best_seller_report')</div>
<div class="best-seller-table">             
    <div class="table">
        <div class="table-header">
            <ul>
                <li>@lang('seller_report.product')</li>
                <li>@lang('seller_report.standered')</li>
                <li>@lang('seller_report.revenue')</li>                
            </ul>
        </div>
        <div class="table-content">
        @if(count($best_perfor_products))
            @foreach($best_perfor_products as $key =>$product)
                <ul>              
                    <li class="product">                  
                        <span class="product-wrap">
                          <span class="product-img">
                            <img src="{{ getProductImageUrl($product->thumbnail_image,'thumb_135x100') }}" width="100" height="75">
                          </span>
                          <span class="product-name">{{$product->category_name}}</span>
                        </span>                 
                    </li>
                    <li class="qty">                  
                        <span>Size: {{$badgeSize[$product->size]}}</span>
                        <span>Grade: {{$badgeGrade[$product->grade]}}</span>                 
                    </li>
                    <li class="price">                  
                        <div class="product-price">{{numberFormat($product->total_sale)}} @lang('common.baht')</div>
                        <!-- <span>Last Modified 10/09/2018 18:30</span>    -->              
                    </li>                                     
                </ul>
            @endforeach
        @endif
        </div>
    </div>
</div>
@endsection 

@section('footer_scripts')
    {!! CustomHelpers::combineCssJs(['js/price_formatter','js/jquery.touchSwipe.min','js/TweenMax.min','js/seller/product','js/seller/sale_report'],'js') !!}
@stop