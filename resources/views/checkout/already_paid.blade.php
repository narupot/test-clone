@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/flickity'],'css') !!}

@endsection

@section('header_script')

@endsection

@section('content')
    
<div class="container">         
    <div class="row">
        <div class="col-sm-12"> 
            @include('includes.buyer_shopping_tab',['purchased_products'=>$pur_prds_in_shop_list,'list_of_shopping_list'=>$total_prds_in_shop_list])  

            <div class="tab-content">
                <div class="tab-pane" id="tab-seler1">
                </div>
                <div class="tab-pane" id="tab-seler2">
                
                </div>

                <div class="tab-pane active" id="tab-seler4">                                
                        
                    @if(!empty($main_order) && count($paid_product))                         
                        <div class="table-responsive checkout-order-table tblseller-pannel">
                            <div class="table">
                                <div class="table-header">
                                    <ul>                                                    
                                        <li class="item-product">@lang('checkout.product')</li>                                        
                                        <li>@lang('checkout.shop')</li>
                                        <li>@lang('checkout.unit_price')</li>
                                        <li>@lang('checkout.qty')</li>
                                        <li>@lang('checkout.price')</li>
                                        <li>@lang('checkout.payment_method')</li>
                                        <li>@lang('checkout.transaction_id')</li>
                                    </ul>
                                </div>
                                <div class="table-content">                     
                                    
                                    @php($totqty = 0)
                                    @foreach($paid_product as $key => $val)

                                        @php($totqty = $totqty + $val->quantity)   
                                        @php($detail_json = jsonDecodeArr($val->order_detail_json))
                                        @php($shop_url = action('ShopController@index',$detail_json['shop_url'] ??''))
                                        @php($prd_url = action('ProductDetailController@display',[$detail_json['cat_url']??'',$val->sku]))

                                        <ul>                                      
                                            <li class="product">
                                                <div class="dbox-flex">
                                                    <div class="dbox-flex">
                                                        
                                                        <a href="{{ $prd_url}}">
                                                        <span class="prod-img prod-134"><img src="{{ getProductImageUrlRunTime($detail_json['thumbnail_image']??'','thumb') }}" width="134" height="100" alt=""></span> </a>
                                                    </div>

                                                    <div class="ml-2">
                                                        <span class="prod-name d-block mb-2"><a href="{{ $prd_url }}">{{ $detail_json['name'][session('default_lang')]??$val->category_name }}</a></span>
                                                        <span class="la"><img src="{{ getBadgeImageUrl($detail_json['badge']['icon'] ?? '' )}}" width="30"></span>
                                                    </div>
                                                </div>                                                
                                            </li>                           
                                            
                                            <li>
                                                <a href="{{ $shop_url }}" class="link-skyblue">{{ $detail_json['shop_name'][session('default_lang')]??'' }}</a>
                                            </li>
                                            <li>{{numberFormat($val->last_price) }} @lang('common.baht') /{{ $detail_json['package'][session('default_lang')] ?? $val->package_name }}</li>
                                            <li class="add-rem-qty">
                                                {{ $val->quantity }} {{ $detail_json['package'][session('default_lang')] ?? $val->package_name }}
                                            </li>
                                            <li>
                                                {{numberFormat($val->total_price) }} @lang('common.baht')
                                            </li>  
                                            <li>{{$detail_json['payment_method'][session('default_lang')] ?? str_replace('_',' ',strtoupper($val->payment_slug)) }}</li>
                                            <li></li>                                                 
                                        </ul>
                                    @endforeach
                                    
                                </div>
                            </div>
                           
                        </div>
                        <div class="checkout-table-footer clearfix">
                            <div class="col-sm-4 float-right">
                                <div class="row">
                                    <span class="col-6">@lang('checkout.total_products')</span>
                                    <span class="col-6">{{ $totqty??"0" }} @lang('checkout.unit')</span>
                                </div>                                                                                      
                                <div class="bg-grey">
                                    <div class="row">
                                        <span class="col-6">@lang('checkout.total_paid_price')</span>
                                        <span class="col-6">{{ numberFormat($main_order->total_final_price) }} @lang('common.baht')</span>
                                    </div>
                                </div>                                
                                <div class="row">
                                    <a href="{{ route('end-shopping') }}" class="btn-blue2 w-100"><span class="col-12">@lang('checkout.end_shopping')</span></a>
                                </div>
                                                                             
                            </div>
                        </div>
                    @else
                        <div>No record found</div>
                    @endif
                </div>
            </div>     
        </div>
    </div>  
</div>
@endsection 

@section('footer_scripts') 


@stop