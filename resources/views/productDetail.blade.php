@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/magicscroll','css/magiczoomplus','css/toastr.min'],'css') !!}
    <style type="text/css">
        .product-info .action-btn { display: none; }
    </style>

@endsection

@section('header_script')
    var productData = {!! json_encode($product_data) !!};
    var addProductToCart = "{{action('ProductDetailController@addProductToCart')}}"; 
    var cartUrl = "{{ action('Checkout\CartController@shoppingCart') }}";
    var error_msg = {
        'quantity_error' : "@lang('product.please_select_at_least_one_quantity')",
        'server_error': "@lang('product.server_not_responsed')",
    };
    var review_data = {'page':'product_detail'};
    var getAllReviews = "{{action('ProductDetailController@getAllReviews')}}";
    var addProductToBargain = "{{action('PopUpController@getCheckBargainPopUp')}}";
    var addIntoWishlist = "{{action('ProductsController@addIntoWishlist')}}";
    var removeFromWishlist = "{{action('ProductsController@removeFromWishlist')}}";
    var getRelatedProducts = "{{ action('ProductDetailController@getRelatedProducts') }}";
    var getOrderHistory = "{{ action('ProductDetailController@getBuyerOrderHistory') }}";
@endsection

@section('content')

@if($productDetail->getShop)
    <div class="shop-header clearfix">
        <div class="shop-img">
            <a href="{{ action('ShopController@index',$productDetail->getShop->shop_url)}}"><img src="{{getImgUrl($productDetail->getShop->logo,'logo')}}" alt="" width="100"></a>
        </div>
        <div class="shop-column">
            <a href="{{ action('ShopController@index',$productDetail->getShop->shop_url)}}" class="visit-shop">@lang('shop.visit_shop')</a>
            <h2><a href="{{ action('ShopController@index',$productDetail->getShop->shop_url)}}">{{ $productDetail->getShopDesc->shop_name??'' }}</a></h2>
            <div class="count-item-prod">@lang('product.product') <a class="product-item-num" href="#">{{ $productDetail->tot_shop_prd }} @lang('product.items')</a></div>
            <!-- <div class="shop-col-row">Respon : 67% | Last Updated : 9/9/2018 12.13 </div>
            <div class="shop-col-row">Chat Respon in : 15 Min | Cancelrd Order : 10%</div> -->
        </div>
    </div>
@endif
<div class="product-detail-wrap" data-ng-controller="productDetailCtrl as rvCtrl" ng-cloak>
    <div class="row">               
        <div class="product-media col-sm-3">
            
            <div class="imgzoom-box thumbnail-bottom" data-thumbnail="type_bottom">
                <div class="zoom-gallery">
                    <div class="product-main-img">
                        <div data-slide-id="zoom" class="zoom-gallery-slide active">
                            <a href="@if(isset($productImage[0])){{ $productImage[0]->large }}@endif" class="MagicZoom" id="zoom-v">
                                <img src="@if(isset($productImage[0])){{ $productImage[0]->large }}@endif"/>
                            </a>

                        </div>
                        @if(Auth::check())
                            <div class="product-wish" ng-if="!rvCtrl.productInfo.in_wishlist"  ng-click="rvCtrl.addToWishlist($event, rvCtrl.productInfo)">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="product-wish active" ng-if="rvCtrl.productInfo.in_wishlist"  ng-click="rvCtrl.removeFromWishlist($event, rvCtrl.productInfo)">
                                <i class="fas fa-heart active"></i>
                            </div>
                        @endif
                    </div>
                      
                    <div class="selectors vertical-thumb MagicScroll magic-slider MagicScroll-horizontal" data-options="items: 4; step: 1;arrows: inside">
                        @foreach($productImage as $imgkey => $imgVal)  
                            <a data-slide-id="zoom" @if($imgkey == 0) class="active" @endif href="{{ $imgVal->original }}" data-image="{{ $imgVal->large }}" data-zoom-id="zoom-v">
                                <img src="{{ $imgVal->thumb }}" width="60" height="50" />
                            </a>
                        @endforeach                           
                    </div>
                 
                </div>
            </div>

            <div class="share-lists">
                <a href="#" data-toggle="tooltip" data-placement="top" title="Facebook"><i class="fab fa-facebook"></i></a>
                <a href="#" data-toggle="tooltip" data-placement="top" title="Twitter" class="twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" data-toggle="tooltip" data-placement="top" title="Instagram" class="instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" data-toggle="tooltip" data-placement="top" title="Pinterest" class="pintrest"><i class="fab fa-pinterest"></i></a>                      
            </div>
                
        </div>
        <div class="product-information col-sm-9">
            <div class="row">
                <div class="col-sm-5">
                    <h1>{{ $productDetail->categorydesc->category_name??'' }}</h1>
                    @if($productDetail->badge_name)
                        <div class="grade">
                            <span class="la"><img src="{{ $productDetail->badge_image }}" width="30"></span>
                        </div>
                    @endif
                    <div class="product-review">                            
                        <div class="review-star">
                            <div class="grey-stars"></div>
                            <div class="filled-stars" style="width: {!! $productDetail->avg_rating*20 !!}%">
                            </div>
                        </div>
                    </div>
                    <div class="price-box">
                        @if(!$productDetail->show_price)
                            <span class="price">@lang('product.ask_the_price_from_the_store')</span>
                        @else
                            <span class="price">{{ $productDetail->weight_per_unit }} {{ $productDetail->unit_name }}/{{ $productDetail->package_name }} <br>
                            {{ convert_string($productDetail->unit_price) }} @lang('common.baht')</span>
                            
                        @endif
                        <span class="remark">@lang('product.remark') : 1 {{ $productDetail->package_name }} = {{ $productDetail->weight_per_unit }} {{ $productDetail->unit_name }}</span>
                    </div>
                    @if($productDetail->tierPrices)
                        <div class="price-list">
                            <table>
                                <tbody>
                                    @foreach($productDetail->tierPrices as $tkey => $tval)
                                        <tr>
                                            <td>{{ $tval->start_qty }} - {{ $tval->end_qty }} {{ $productDetail->package_name }}</td>
                                            <td>{{ convert_string($tval->unit_price) }} @lang('common.baht')/{{ $productDetail->package_name }}</td>
                                        </tr>
                                    @endforeach
                                    
                                </tbody>
                            </table>
                            @if($productDetail->order_qty_limit == 0)
                                <div class="min-order">@lang('product.minimun_order') {{ $productDetail->min_order_qty }} {{ $productDetail->package_name }}</div>
                            @endif
                            @if($productDetail->stock == 1)
                                <div class="stock"><span>@lang('product.stock')</span>:  @lang('product.unlimited')</div>
                            @else
                                <div class="stock"><span>@lang('product.stock')</span>:  
                                @if($productDetail->quantity)
                                    {{$productDetail->quantity.' '. $productDetail->package_name }}
                                @else
                                    <span class="red outstock">@lang('product.out_of_stock')</span>
                                @endif
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="col-sm-7">
                    <div class="prod-orderDetail-box">
                        <span class="order-detail-label">@lang('product.order_details')</span>
                        <div class="form-group" ng-if="!rvCtrl.productInfo.sold_out">
                            <label>@lang('product.qty') :</label>
                            <span class="spiner">
                                <span class="decrease fas fa-minus" ng-click="rvCtrl.incDcrQuntity($event,rvCtrl.productInfo.total_quantity,'down',rvCtrl.productInfo,$index)"></span>
                                @if($productDetail->order_qty_limit == 0) 
                                    <input id="orderQty" type="number" class="spinNum" ng-value="rvCtrl.productInfo.min_order_qty" ng-init="rvCtrl.productInfo.quantity = rvCtrl.productInfo.min_order_qty" ng-model="rvCtrl.productInfo.quantity" ng-blur="rvCtrl.incDcrQuntity($event, rvCtrl.productInfo.total_quantity, 'tqchange',rvCtrl.productInfo,$index)" onkeypress="return isNumberKey(event)" />
                                @else 
                                    <input id="orderQty" type="number" class="spinNum" ng-model="rvCtrl.productInfo.quantity" ng-blur="rvCtrl.incDcrQuntity($event, rvCtrl.productInfo.total_quantity, 'tqchange',rvCtrl.productInfo,$index)" onkeypress="return isNumberKey(event)" />
                                @endif
                                <span class="increase fas fa-plus" ng-click="rvCtrl.incDcrQuntity($event,rvCtrl.productInfo.total_quantity,'up',rvCtrl.productInfo,$index)"></span>
                            </span>
                            <span class="qty-label">{{ $productDetail->package_name }}</span>
                        </div>
                        
                        @if(Auth::check())
                            <a href="javascript://" class="addshop-link d-none"   ng-click="rvCtrl.addToShoppinglistHandler($event, rvCtrl.productInfo)"><i class="fas fa-pencil-alt"></i> <span>+ @lang('product.add_to_shopping_list')</span></a>
                            <div class="btn-group">
                                <a href="#" class="btn-dark-grey chat-link d-none"><i class="fas fa-comments"></i></a>
                                @if($productDetail->show_price && $productDetail->quantity)
                                    {{--<a href="{{action('PopUpController@getBargainPopUp', $productDetail->id)}}" qty="{{ $productDetail->order_qty_limit>0?1:$productDetail->min_order_qty }}" rel="{{$productDetail->id}}" class="btn-dark-grey btn-buyer-chat"><i class="fas fa-comments"></i> @lang('product.bargain')</a>--}}
                                    <a href="javascript:;" data-val="{{$productDetail->id}}" class="btn-dark-grey btn-buyer-chat"><i class="fas fa-comments"></i> @lang('product.talk_to_shop')</a>
                                    <a href="javascript:void(0)" class="btn-blue" ng-click="rvCtrl.addToCartHandler($event,'addtocart')" ng-disabled="rvCtrl.loading.disableBtn">@lang('product.add_to_cart')</a>
                                    <a href="javascript:void(0)" class="btn d-none" ng-click="rvCtrl.addToCartHandler($event,'buynow')" ng-disabled="rvCtrl.loading.disableBtn">@lang('product.buy_now')</a>
                                    
                                @endif
                            </div>
                        @else
                            <a href="#" data-toggle="modal" data-target="#loginModal" class="addshop-link d-none"><i class="fas fa-pencil-alt"></i> <span>+ @lang('product.add_to_shopping_list')</span></a>
                            <div class="btn-group">
                                <a href="#" class="btn-dark-grey chat-link d-none" data-toggle="modal" data-target="#loginModal" class="btn-default chat"><i class="fas fa-comments"></i></a>
                                @if($productDetail->show_price && $productDetail->quantity)
                                    <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn-dark-grey"><i class="fas fa-comments"></i> @lang('product.bargain')</a>
                                    <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn-blue">@lang('product.add_to_cart')</a>
                                    <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn d-none">@lang('product.buy_now')</a>

                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- add to cart modal -->
    <div id="addToCartdiv" class="modal modal-Cartdiv modal-address fade in formone-size" role="dialog">
         <div class="modal-dialog modal-dialog-centered model-md">
             <!-- Modal content-->
             <div class="modal-content text-center">
                 <div class="modal-header line-default">
                   <h2 class="modal-title"><% rvCtrl.productInfo.prd_name %> <span>@lang('checkout.added_successfully').</span></h2>
                   <span class="close fas fa-times" data-dismiss="modal"></span>
                 </div>
                 <div class="modal-body">
                     <div class="">
                       <div class="mt-10">
                        <a href="{{ action('HomeController@index') }}" class="btn-blue">@lang('checkout.continue_shopping')</a>
                        </div>
                       <div class="mt-3 or mb-3">@lang('checkout.or')</div>
                       <div class="mt-10">
                        <a class="btn-default" href="{{ action('Checkout\CartController@shoppingCart') }}" target="_self">@lang('checkout.view_cart_checkout')</a>
                       </div>
                     </div>
               </div>
             </div>
         </div>        
    </div>
</div>

<!-- Product Detail tab Start -->
<div class="product-info-detail" data-ng-controller="reviewController as rv"> 
    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#desc">@lang('common.description')</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#review">@lang('product.review')</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#ord-history">@lang('product.order_history')</a>
        </li>
    </ul>
    <!-- Tab content -->
    <div class="tab-content">
        <div id="desc" class="tab-pane active">                         
            <p>{!! $productDetail->productDesc->description??'' !!}</p>
        </div>
        <div id="review" class="tab-pane fade">
            <!-- for list user review -->
            <div class="review-list-container" id="review_list_container">
                <div class="review-grid-row" ng-repeat="item in rv.review_data track by $index">
                    <div class="product-review">                            
                        <div class="review-star">
                          <div class="grey-stars"></div>
                          <div class="filled-stars" style="width:<% item.rating%>%"></div>
                        </div>
                    </div>
                    <div class="review-feedback" ng-bind="item.review"></div>
                    <time ng-bind="item.time"></time>
                </div>
                <div class="loading-more" ng-if="rv.pagination.no_more">
                    <a class="btn-blue" href="javascript:void(0)" ng-click="rv.loadMore($event)">@lang('product.load_more_review')</a>
                </div>
            </div>
            @if($show_review_form)
                <label class="order_label">@lang('product.order_id') </label> 
                <select name="orderId" id="product_order_review"> 
                    <option value="">@lang('common.select')</option>
                    @foreach($rev_data as $unit_rev_data)
                    <option value="{{$unit_rev_data->order_id}}" data-pid="{{$unit_rev_data->product_id}}" data-spid="{{$unit_rev_data->shop_id}}">
                        {{$unit_rev_data->formatted_id}}
                    </option>
                    @endforeach
                </select>
                @include('includes.review_form')
            @endif                
        </div>
        <div id="ord-history" class="tab-pane fade">
            <div class="order-table">
                <div class="table">
                    <div class="table-header">
                        <ul>
                            <li>@lang('checkout.order_no')</li>
                            <li>@lang('checkout.unit_price')</li>
                            <li>@lang('checkout.qty')</li>
                            <li>@lang('checkout.price')</li>
                            <li>@lang('checkout.date')</li>
                        </ul>
                    </div>
                    <div class="table-content">
                        <ul ng-repeat="rows in rv.order_history track by $index">
                            <li class="ord-no">
                                <a href="<%rows.ord_url%>" class="bod skyblue" ng-bind="rows.formatted_id"></a>
                            </li>
                            <li class="uprice">
                                <span class="bod" ng-bind="rows.unit_price"></span>
                            </li>
                            <li class="qty">
                                <span class="bod" ng-bind="rows.quantity"></span>
                            </li>
                            <li class="price">
                                <span class="bod" ng-bind="rows.total_price"></span>
                            </li>
                            <li class="date">
                                <span class="bod" ng-bind="rows.end_shopping_date"></span>
                            </li>                                           
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Related Product Start -->    
    <div class="related-product" ng-if="rv.related_product_config.data">
        <h2 class="title-bg-grey"><span>@lang('product.related_product')</span></h2>
        @include('includes.related-product-listing')
    </div>
</div>
@endsection 



@section('footer_scripts') 

{!! CustomHelpers::combineCssJs(['js/magicscroll','js/magiczoomplus'],'js') !!}
<script type="text/javascript" src="{{ Config::get('constants.angular_libs_url').'angular.js' }}"></script>
<script type="text/javascript" src="{{ Config::get('constants.angular_libs_url').'ng-droplet.min.js' }}"></script> 
<script type="text/javascript" src="{{ Config::get('constants.js_url').'lodash.min.js' }}"></script>
<script src="{{ Config::get('constants.angular_front_url').'services/service.js' }}"></script>  
<script src="{{ Config::get('constants.angular_front_url').'model/productDetailApp.js' }}"></script>
<!-- <script src="{{ Config::get('constants.angular_front_url').'directive/sliderDir.js' }}"></script>
<script src="{{ Config::get('constants.angular_front_url').'directive/product-detail-dir.js' }}"></script> -->
<script src="{{ Config::get('constants.angular_front_url').'controller/frontend/productDetailCtrl.js' }}"></script> 

@stop