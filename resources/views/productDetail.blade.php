@extends('layouts.app')

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/magicscroll','css/magiczoomplus','css/toastr.min'],'css') !!}
    <style type="text/css">
        .product-info .action-btn { display: none; }

        .product-info-detail .nav-link{
            border-width: 2px;
            margin-bottom: -1px;
        }
        .product-info-detail .nav-link.active, .product-info-detail .nav-link:hover{
            border-color: red;
            border-width: 2px;
        }
        .product-info-detail .nav-item{
            margin: 0px;
            min-width: 200px;
            text-align: center
        }
        .product-info-detail .nav-tabs{
            border-bottom: 1px solid lightgray;

        }
        .product-detail-wrap{
            border:0px;
        }
        .product-info-detail{
            margin: 0px;
        }
        .MagicScroll-horizontal .mcs-item{
            width: max-content !important;
            margin-right: 1rem;
        }
        
        .product-information h1{
            font-size: xx-large;
            /* font-weight: 600; */
        }

        @media (max-width: 575.98px) {
            .product-info-detail .nav-item{
                min-width: 100px;
            }
            .product-info-detail .nav-link{
                font-size: 12px;
            }
            .tab-content,.tab-content p{
                font-size: 12px;
            }
        }
    
    </style>
@endsection

@section('header_script')
    var productData = {!! json_encode($product_data) !!};
    var addProductToCart = "{{action('ProductDetailController@addProductToCart')}}";
    var cartUrl = "{{ action('Checkout\CartController@shoppingCart') }}";
    var error_msg = {
        'quantity_error' : "@lang('product.please_select_at_least_one_quantity')",
        'quantity_null' : "จำนวนสินค้าไม่สามารถว่างได้",
        'max_quantity' : "@lang('checkout.please_enter_quantity_less_or_equal')",
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


<div class="alert alert-success position-absolute" style="top:0;right:0; z-index:9" role="alert">
    A simple success alert—check it out!
</div>

<div class="product-detail-wrap mb-4 pt-4" ng-controller="productDetailCtrl as rvCtrl" ng-cloak>
    <div class="row">
        <div class="product-media col-sm-12 col-md-6 col-lg-5">
            
            <div class="imgzoom-box thumbnail-bottom" data-thumbnail="type_bottom">
                <div class="zoom-gallery">
                    <div class="product-main-img">
                        <div data-slide-id="zoom" class="zoom-gallery-slide active">
                            <a href="@if(isset($productImage[0])){{ $productImage[0]->original }}@endif" class="MagicZoom" id="zoom-v">
                                <img src="@if(isset($productImage[0])){{ $productImage[0]->large }}@endif" alt="" />
                            </a>
                        </div>
                    </div>
                      
                    <div class="selectors vertical-thumb MagicScroll magic-slider MagicScroll-horizontal" data-options="items: 4; step: 1;arrows: inside">
                        @foreach($productImage as $imgkey => $imgVal)
                            <a data-slide-id="zoom" @if($imgkey == 0) class="active" @endif href="{{ $imgVal->original }}" data-image="{{ $imgVal->original }}" data-zoom-id="zoom-v">
                                <img src="{{ $imgVal->thumb }}" width="60" height="50" alt="" />
                            </a>
                        @endforeach
                    </div>
                 
                </div>
            </div>

            {{-- <div class="share-lists">
                <a href="#" data-toggle="tooltip" data-placement="top" title="Facebook"><i class="fab fa-facebook"></i></a>
                <a href="#" data-toggle="tooltip" data-placement="top" title="Twitter" class="twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" data-toggle="tooltip" data-placement="top" title="Instagram" class="instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" data-toggle="tooltip" data-placement="top" title="Pinterest" class="pintrest"><i class="fab fa-pinterest"></i></a>
            </div> --}}
                
        </div>
        <div class="product-information col-sm-12 col-md-6 col-lg-7">
            <div class="row">
                <div class="col-md-12 col-lg-10 col-xl-8 mb-4">
                    <h1 class="mb-2"><strong>{{ $productDetail->categorydesc->category_name??'' }}</strong></h1>
                    @if($productDetail->badge_name)
                        <div class="grade">
                            @php
                                $badge = explode(" ", $productDetail->badge_name);
                            @endphp
                            <table class="table table-bordered">
                                <tr>
                                    <td>
                                        <span>ขนาด : </span>
                                        <strong>{{$badge[0]}}</strong>
                                    </td>
                                    <td>
                                        <span>คุณภาพ : </span>
                                        <strong>{{$badge[1]}}</strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    @endif

                    {{-- <span>ราคาปัจจุบัน</span>
                    <div class="price-box">
                        @if(!$productDetail->show_price)
                            <span class="price">@lang('product.ask_the_price_from_the_store')</span>
                        @else
                            <span class="price">{{ $productDetail->weight_per_unit }} {{ $productDetail->unit_name }}/{{ $productDetail->package_name }} <br>
                           <span style="color: red;">{{ number_format($productDetail->unit_price??0,2) }}</span> @lang('common.baht')</span>
                            
                        @endif
                        <!-- <span class="remark">@lang('product.remark') : 1 {{ $productDetail->package_name }} = {{ $productDetail->weight_per_unit }} {{ $productDetail->unit_name }}</span> -->
                    </div> --}}

                    <div>
                        <table class="table table-bordered">
                            <tr>
                                <td class="align-middle">
                                    <h1 class="text-danger font-weight-bold">฿{{ number_format($productDetail->unit_price??0,2) }}</h1>
                                </td>
                                <td class="align-middle">
                                    <div><strong>{{ $productDetail->weight_per_unit??0 }} </strong></div>
                                    <small class="text-secondary">{{ $productDetail->unit_name }} @if($productDetail->unit_name && $productDetail->package_name )/ @endif {{ $productDetail->package_name }}</small>
                                </td>
                                <td class="align-middle">
                                    <div><strong>฿{{ number_format((float)$productDetail->unit_price/(float)$productDetail->weight_per_unit,2) }} </strong></div>
                                    <small class="text-secondary">ต่อ{{ $productDetail->unit_name }}</small>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="col-md-12 col-lg-10 col-xl-8 mb-4">
                 
                    <div class="form-group mb-1 d-flex align-items-center" ng-if="!rvCtrl.productInfo.sold_out">
                        <span class="spiner rounded">
                            <span class="decrease fas fa-minus" ng-click="rvCtrl.incDcrQuntity($event,rvCtrl.productInfo.total_quantity,'down',rvCtrl.productInfo,$index)"></span>
                            @if($productDetail->order_qty_limit == 0 )
                                <input id="orderQty" type="number" class="spinNum" value="" min="0" ng-value="rvCtrl.productInfo.min_order_qty" ng-init="rvCtrl.productInfo.quantity = rvCtrl.productInfo.min_order_qty" ng-model="rvCtrl.productInfo.quantity" ng-blur="rvCtrl.incDcrQuntity($event, rvCtrl.productInfo.total_quantity, 'tqchange',rvCtrl.productInfo,$index)" onkeypress="return isNumberKey(event)" />
                            @else
                                <input id="orderQty" type="number" class="spinNum" value="" min="0" ng-model="rvCtrl.productInfo.quantity" ng-blur="rvCtrl.incDcrQuntity($event, rvCtrl.productInfo.total_quantity, 'tqchange',rvCtrl.productInfo,$index)" onkeypress="return isNumberKey(event)" />
                            @endif
                            <span class="increase fas fa-plus" ng-click="rvCtrl.incDcrQuntity($event,rvCtrl.productInfo.total_quantity,'up',rvCtrl.productInfo,$index)"></span>
                        </span>
                        <span class="qty-label pl-3">{{ $productDetail->package_name }}</span>
                        <span class="qty-label pl-3">
                            <span>ราคารวม</span>
                            <h1 class="text-danger font-weight-bold">฿<span id="product_total_price" ng-bind="rvCtrl.productInfo.unit_price * rvCtrl.productInfo.quantity | number:2">0</span></h1>
                        </span>
                    </div>
                        
                    <div class="product-review mb-4">
                        <div class="review-star">
                            <div class="grey-stars"></div>
                            <div class="filled-stars" style="width: {!! $productDetail->avg_rating*20 !!}%"></div>
                            {{-- <small class="ml-2">ขายแล้ว &nbsp;&nbsp; รายการ</small> --}}
                        </div>
                    </div>

                    
                    {{-- @if(Auth::check())
                        <div class="product-wish" ng-if="!rvCtrl.productInfo.in_wishlist"  ng-click="rvCtrl.addToWishlist($event, rvCtrl.productInfo)">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="product-wish active" ng-if="rvCtrl.productInfo.in_wishlist"  ng-click="rvCtrl.removeFromWishlist($event, rvCtrl.productInfo)">
                            <i class="fas fa-heart active"></i>
                        </div>
                    @endif --}}
                    @if($productDetail->tierPrices)
                        <div class="price-list">
                            <table>
                                @foreach($productDetail->tierPrices as $tkey => $tval)
                                    <tr>
                                        <td>{{ $tval->start_qty }} - {{ $tval->end_qty }} {{ $productDetail->package_name }}</td>
                                        <td>{{ convert_string($tval->unit_price) }} @lang('common.baht')/{{ $productDetail->package_name }}</td>
                                    </tr>
                                @endforeach
                                    
                            </table>
                            <div class="min-order">@lang('product.minimun_order') :
                                @if ($productDetail->order_qty_limit === '0')
                                    {{$productDetail->min_order_qty}} {{$productDetail->package_name??''}}
                                @else
                                    @lang('product.unlimited')
                                @endif
                            </div>
                            
                            @if($productDetail->stock === '1')
                                <div class="stock"><span>@lang('product.stock')</span> :  @lang('product.unlimited')</div>
                            @else
                                <div class="stock"><span>@lang('product.stock')</span> :
                                @if($productDetail->quantity > 0)
                                    <span id="instock">{{ $productDetail->quantity}}</span> {{$productDetail->package_name }}
                                @else
                                    <span class="red outstock">@lang('product.out_of_stock')</span>
                                @endif
                                </div>
                            @endif
                        </div>
                    @endif
                        
                    @if(Auth::check())
                        <a href="javascript://" class="addshop-link d-none"   ng-click="rvCtrl.addToShoppinglistHandler($event, rvCtrl.productInfo)"><i class="fas fa-pencil-alt"></i> <span>+ @lang('product.add_to_shopping_list')</span></a>
                        <div class="row">
                            @if($productDetail->show_price && $productDetail->status == 1)
                            <div class="col-12 mb-2">
                                <a href="javascript:void(0);" id="btnAddToCart" class="btn btn-sm btn-danger w-100"  ng-click="rvCtrl.addToCartHandler($event,'addtocart')" ng-disabled="rvCtrl.loading.disableBtn">@lang('product.add_to_cart')</a>
                            </div>
                            <div class="col-6 mb-2">
                                <a href="javascript:void(0);" class="btn btn-sm btn-outline-danger btn-dark-grey btn-buyer-chat w-100 small" ng-if="!rvCtrl.productInfo.in_wishlist"  ng-click="rvCtrl.addToWishlist($event, rvCtrl.productInfo)">
                                    <i class="fas fa-heart"></i> เพิ่มรายการถูกใจ</a>
                                <a href="javascript:void(0);" class="btn btn-sm btn-outline-danger btn-dark-grey btn-buyer-chat w-100 small" ng-if="rvCtrl.productInfo.in_wishlist"  ng-click="rvCtrl.removeFromWishlist($event, rvCtrl.productInfo)">
                                    <i class="fas fa-heart"></i> @lang('product.product_added_into_wishlist')</a>
                            </div>
                            <div class="col-6 mb-2 ">
                                <a href="javascript:void(0);" data-val="{{$productDetail->id}}" class="btn btn-sm btn-outline-danger btn-dark-grey btn-buyer-chat w-100 small"><i class="fas fa-comments"></i> @lang('product.talk_to_shop') </a>
                                {{-- <a href="javascript:void(0)" class="btn d-none mb-3" ng-click="rvCtrl.addToCartHandler($event,'buynow')" ng-disabled="rvCtrl.loading.disableBtn">@lang('product.buy_now')</a> --}}
                            </div>
                                
                            @endif
                            <a href="javascript:void(0);" class="btn-dark-grey chat-link d-none"><i class="fas fa-comments"></i></a>
                        </div>
                    @else
                        <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="addshop-link d-none"><i class="fas fa-pencil-alt"></i> <span>+ @lang('product.add_to_shopping_list')</span></a>
                        <div class="row">
                            <a href="javascript:void(0);" class="btn-dark-grey chat-link d-none" data-toggle="modal" data-target="#loginModal" class="btn-default chat"><i class="fas fa-comments"></i></a>
                            @if($productDetail->show_price && $productDetail->stock == 1)
                            {{-- <div class="col-6 mb-2">
                                <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn btn-sm w-100 btn-danger"><i class="fas fa-comments"></i> @lang('product.talk_to_shop')</a>
                            </div>
                            <div class="col-6 mb-2">
                                <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn btn-sm w-100 btn-danger"><i class="f fa-basket-shopping"></i> @lang('product.add_to_cart')</a>
                            </div>
                            <div class="col-6 mb-2">
                                <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn btn-sm w-100 btn d-none">@lang('product.buy_now')</a>
                            </div> --}}
                            <div class="col-12 mb-2">
                                <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn btn-sm btn-danger w-100"  >@lang('product.add_to_cart')</a>
                            </div>
                            <div class="col-6 mb-2">
                                <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn btn-sm btn-outline-danger btn-dark-grey btn-buyer-chat w-100 small ">
                                    <i class="fas fa-heart"></i> @lang('product.talk_to_shop')</a>
                            </div>
                            <div class="col-6 mb-2 ">
                                <a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal" class="btn btn-sm btn-outline-danger btn-dark-grey btn-buyer-chat w-100 small"><i class="fas fa-comments"></i> @lang('product.talk_to_shop') </a>
                            </div>
                            @endif
                        </div>
                    @endif
                    {{-- </div> --}}
                </div>

                <div class="col-lg-8 col-lg-10 col-xl-8 mb-4">
                    @if($productDetail->getShop)
                        {{-- <div class="shop-header clearfix">
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
                        </div> --}}
                        {{-- <pre>
                            {{$productDetail->getShop}}
                        </pre> --}}
                        <table class="table table-borderless text-secondary">
                            <tr>
                                <td>ชื่อร้าน : </td>
                                <td><a class="text-danger text-link" href="{{ action('ShopController@index',$productDetail->getShop->shop_url)}}">{{ $productDetail->getShopDesc->shop_name??'' }}</a></td>
                            </tr>
                            <tr>
                                <td>ติดต่อร้านค้า : </td>
                                <td>{{ $productDetail->getShop->ph_number??'' }}</td>
                            </tr>
                            <tr>
                                <td>เลขที่แผง : </td>
                                <td>{{ $productDetail->getShop->panel_no??'' }}</td>
                            </tr>
                        </table>
                        <hr/>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- add to cart modal -->
    {{-- <div id="addToCartdiv" class="modal modal-Cartdiv modal-address fade in formone-size" role="dialog">
         <div class="modal-dialog modal-dialog-centered model-md">
             <!-- Modal content-->
             <div class="modal-content text-center">
                 <div class="modal-header line-default ">
                   <h3 class="modal-title text-center w-100"><% rvCtrl.productInfo.prd_name %> <span>@lang('checkout.added_successfully').</span></h2>
                   <span class="close fas fa-times" data-dismiss="modal"></span>
                </div>
                <div class="modal-body">
                     <div class="">
                       <div class="mt-10">
                        <a href="{{ action('HomeController@index') }}" class="btn-blue">@lang('checkout.continue_shopping')</a>
                        <a href="#" class="btn-blue" data-dismiss="modal">@lang('checkout.continue_shopping')</a>
                        </div>
                       <div class="mt-3 or mb-3">@lang('checkout.or')</div>
                       <div class="mt-10">
                        <a class="btn-default" href="{{ action('Checkout\CartController@shoppingCart') }}" target="_self">@lang('checkout.view_cart_checkout')</a>
                       </div>
                    </div>
                </div>
             </div>
         </div>
    </div> --}}
</div>

<!-- Product Detail tab Start -->
<div class="product-info-detail" data-ng-controller="reviewController as rv">
    <div class="bg-white col-12 py-3 mb-4">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item ">
                <a class="nav-link py-3 active" data-toggle="tab" href="#desc">@lang('common.description')</a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3" data-toggle="tab" href="#review">@lang('product.review')</a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3" data-toggle="tab" href="#ord-history">@lang('product.order_history')</a>
            </li>
        </ul>
        <!-- Tab content -->
        <div class="tab-content mb-4">
            <div id="desc" class="tab-pane active pb-4">
                <p>{!! $productDetail->productDesc->description??'' !!}</p>
            </div>
            <div id="review" class="tab-pane fade pb-4">
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
                        <a class="btn- btn" href="javascript:void(0)" ng-click="rv.loadMore($event)">@lang('product.load_more_review')</a>
                    </div>
                </div>
                @if($show_review_form)
                    <label for="" class="order_label">@lang('product.order_id') </label>
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
            <div id="ord-history" class="tab-pane fade pb-4">
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
    </div>

    <div class="col-12 p-0 py-3">
        
        <!-- Related Product Start -->
        <div class="related-product" ng-if="rv.related_product_config.data">
            {{-- <h2 class="title-bg-grey"><span>@lang('product.related_product')</span></h2> --}}
            <h1 class="slide-title mb-md-3">
                @lang('product.related_product')
            </h1>
            @include('includes.related-product-listing')
        </div>
    </div>
</div>


@endsection



@section('footer_scripts')

{!! CustomHelpers::combineCssJs(['js/magicscroll','js/magiczoomplus'],'js') !!}
<script type="text/javascript" src="{{ Config::get('constants.angular_libs_url').'angular.js' }}{{$version}}"></script>
<script type="text/javascript" src="{{ Config::get('constants.angular_libs_url').'ng-droplet.min.js' }}{{$version}}"></script>
<script type="text/javascript" src="{{ Config::get('constants.js_url').'lodash.min.js' }}{{$version}}"></script>
<script src="{{ Config::get('constants.angular_front_url').'services/service.js' }}{{$version}}"></script>
<script src="{{ Config::get('constants.angular_front_url').'model/productDetailApp.js' }}{{$version}}"></script>
{{-- <script src="{{ Config::get('constants.angular_front_url').'directive/sliderDir.js' }}"></script> --}}
{{-- <script src="{{ Config::get('constants.angular_front_url').'directive/product-detail-dir.js' }}"></script> --}}
<script src="{{ Config::get('constants.angular_front_url').'controller/frontend/productDetailCtrl.js' }}{{$version}}"></script>

<script>
</script>
@stop