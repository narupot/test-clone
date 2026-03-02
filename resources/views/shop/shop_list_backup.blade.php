@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/dataTables.bootstrap', 'css/myaccount', 'css/bootstrap-select'],'css') !!}

@endsection

@section('header_script')
 
@endsection

@section('content')

@if(Session::has('verify_msg'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    {{Session::get('verify_msg')}}
</div>
@endif

@if(Session::has('not_verify_msg')) 
    <div class="alert alert-danger alert-dismissable margin5"> 
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button> 
        {{Session::get('not_verify_msg')}} 
    </div> 
@endif
<div class="container">
    <!-- Breadcrumb -->
                <ul class="breadcrumb">
                    <li><a href="/">Home</a></li>
                    <li class="active">Shops</li>
                </ul>

                <div class="filter-wrap">
                    <div class="row">
                        <div class="col-md-8 col-lg-9">
                            
                        </div>
                        <div class="col-md-4 col-lg-3">
                            <div class="view-product-shop">
                                <span>View :</span>
                                <a href="javascript:;">Product</a>
                                <a href="{{action('ShopController@shopList')}}" class="active">Shop</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="category-products">
                    <div class="toolbar border-bottom-0">
                        <div class="title-bg-red"><span>Orange A</span></div>
                        <div class="view-mode">
                        </div>
                    </div>
                    <div class="product-shoplist">
                        <table class="table table-bordered " id="table">
                            <thead>
                                <tr class="filters">
                                    <th class="text-center">@lang('shop.favorit_shop_store')</th>
                                    <th class="text-center">@lang('shop.favorit_shop_market')</th>
                                    <th class="text-center">@lang('shop.favorit_shop_product_type')</th>
                                    <th class="text-center">@lang('shop.favorit_shop_rating')</th>
                                    <th class="text-center">@lang('shop.favorit_shop_update_price')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(count($shop_list)>0)
                                    @foreach($shop_list as $s_key => $shop)
                                        <tr>
                                            <td>
                                                <div class="product-wrap">
                                                    <div class="prod-img">
                                                        <img src="{{getImgUrl($shop->logo,'logo')}}" width="50" height="50">                         
                                                    </div>
                                                    <div class="product-info">
                                                        <div class="shop-name">
                                                            <a href="{{action('ShopController@index',['shop'=>$shop->shop_url])}}">
                                                            {{{ isset($shop->shopDesc->shop_name)? $shop->shopDesc->shop_name : 'NA'}}}</a>
                                                        </div>  
                                                        <div class="review-star">
                                                          <div class="grey-stars"></div>
                                                          <div class="filled-stars" style="width: 60%"></div>
                                                        </div>                                                           
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="marketname text-center">{{{ isset($shop->market)? $shop->market : 'NA'}}}</td>
                                            <td class="product-name text-center">Orange, Mango, Apple</td>
                                            <td class="chat-wrap">
                                                <span class="progress-perc">80%</span>
                                                <div class="resp-progress">
                                                    <span class="grey-bar"></span>
                                                    <span class="prog-bar" style="width: 80%"></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="last-updatebox">                             
                                                    <span class="date">20/11/2018 </span>
                                                    <span class="divider"> | </span>
                                                    <span class="time"> 12:20</span>
                                                    <span class="update-price"><i class="fas fa-long-arrow-alt-right"></i> Update Mango's Price</span>
                                                </div>
                                                <div class="view-detail">                         
                                                    <a href="#">
                                                        <span class="notification">3</span>
                                                        <span>See more details</span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
</div>
@endsection 

@section('footer_scripts') 
{!! CustomHelpers::combineCssJs(['js/jquery.dataTables','js/dataTables.bootstrap'],'js') !!}   
    <!-- begining of page level js -->
    <script>
    $(document).ready(function() {
        $('#table').dataTable({
            ordering: false,
            bLengthChange: false,
            searching: false,
        });
    });
    </script>
@stop