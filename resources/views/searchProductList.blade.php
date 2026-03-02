@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/magicscroll','css/magiczoomplus', 'css/toastr.min'],'css') !!}
    <style type="text/css">
        .product-info .action-btn { display: none; }
    </style>

@endsection

@section('header_script')   
    //for routing url (query string)
    var browser_url =  window.location.pathname;
    var cate_id = null;
    var name = "{{isset($_GET['search']) ? $_GET['search']: '' }}";    
    var getproductURL = "{{action('ProductsController@getProductsBysearch')}}";
    //for routing url (query string)
    var browser_url =  window.location.pathname;    
    var paginations = {!! $show_per_page !!};
    var short_data = {!! $order_by_item !!};
    var rating = {!! $rating_star_item !!};
        for(p of rating){
            p['type'] = 'rating';
            p['checked'] = false;
        };
    var addIntoWishlist = "{{action('ProductsController@addIntoWishlist')}}";
    var removeFromWishlist = "{{action('ProductsController@removeFromWishlist')}}";
    var addProductToCart = "{{action('ProductDetailController@addProductToCart')}}"; 
    var addProductToBargain = "{{action('PopUpController@getCheckBargainPopUp')}}"; 
    var cartUrl = "{{ action('Checkout\CartController@shoppingCart') }}";
    var error_msg = {
        'quantity_error' : "@lang('product.please_select_at_least_one_quantity')",
        'server_error': "@lang('product.server_not_responsed')",
    };
    var badges = {!! $badges!!};
    var price_flag = "{{ $price_flag }}"; 
    
    var cat_data = {!! json_encode($cat_data)!!};
@endsection

@section('content')
<div ng-controller="ProductListController" ng-cloak>
    <!-- Breadcrumb -->         
    <!-- <ul class="breadcrumb">
        <li><a href="#">หน้าแรก</a></li>
        <li><a href="#">ผลไม้ตามฤดูกาล</a></li>
        <li><a href="#">ส้ม</a></li>
        <li class="active">ส้มเขียวหวาน</li>
    </ul> -->
    <div class="breadcrumb">
        <ul class="bredcrumb-menu container">
            {!! $breadcrumb !!}
            <li>@lang('product.search'): {{$search}}</li>
        </ul>
    </div>
    <div class="filter-wrap">
        <div class="row">
        <div class="col-md-8 col-lg-9">
            <div class="filter-tool">                   
                <a href="javascript:void(0)" class="showFilter">
                    @lang('product.filter') <i class="fas fa-angle-down"></i>
                </a>
                <div class="dropdown-wrap">
                    <span class="sortby dropdown">
                        <label>@lang('product.filter_sort') :</label>
                        <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown"><%orderLabel%></a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="javascript:void(0)" class="dropdown-item" ng-repeat="item in shortData" data-name="<%item.name%>" data-order="<%item.name%>" ng-click="changeOrder($event, item)" ng-bind="item.value"></a>
                        </div>
                    </span>
                    <span class="showby dropdown">
                        <label>@lang('product.filter_show') :</label>
                        <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown"><span class="selectdd" ng-bind="pagination.label"></span></a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="javascript:void(0)" class="dropdown-item" ng-repeat="item in pagination.item_option_arr" data-name="per_page_<%item%>" data-page="<%item%>" ng-click="changeItemPerPage($event, item)" ng-bind="item"></a>
                        </div>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-3">
            {{--<div class="view-product-shop">
                <span>@lang('product.view') :</span>
                <a href="#" class="active">@lang('order.product')</a>
                <a href="{{action('ShopController@shopList')}}">@lang('product.shop')</a>
            </div>--}}
        </div>              
        </div>
    </div>

    <div class="filer-box">
        <div class="box-grey">
        <!-- filter by category -->
        <h4>@lang('product.search_category_label')</h4>
        <ul class="filter-select">           
            <li ng-repeat="attribute_result in cate_data">
                <label class="chk-wrap">
                    <input type="checkbox" ng-model="attribute_result['checked']" name="<%attribute_result.category_name%>" ng-change="filter_action.filter_list_handler(attribute_result)"> 
                    <span class="chk-mark">
                        <span class="xla"><img ng-src="{{Config::get('constants.category_img_url')}}<%attribute_result.img%>" onerror="this.onerror=null; this.src = '{{getCategoryImageUrl('')}}'"></span>
                        <span class="chk-text" ng-bind="attribute_result.category_name"></span>
                    </span>                                                     
                </label>                        
            </li>
        </ul>
        <!-- search by badges -->
        <h4>มาตรฐานสินค้า</h4>
        <ul class="filter-select">           
            <li ng-repeat="attribute_result in filter_action.attrbute_results">
                <label class="chk-wrap">
                    <input type="checkbox" ng-model="attribute_result['checked']" name="<%attribute_result.badge_name%>" ng-change="filter_action.filter_list_handler(attribute_result)"> 
                    <span class="chk-mark">
                        <span class="xla"><img ng-src="{{Config::get('constants.standard_badge_url')}}<%attribute_result.icon%>" onerror="this.onerror=null; this.src = '{{getBadgeImageUrl('')}}'"></span>
                        <span class="chk-text" ng-bind="attribute_result.badge_name"></span>
                    </span>                                                     
                </label>                        
            </li>
        </ul>

        <div class="row">
            <div class="col-sm-6 col-lg-3">
                <div class="rating-review">
                    <h4>เรทติ้ง</h4>
                    <ul>
                        <li ng-repeat="rating in filter_action.review_rating">
                            <label class="chk-wrap">
                                <input type="checkbox" ng-model="rating['checked']" name="reviw-rating" ng-change="filter_action.filter_list_handler(rating)">
                                <span class="chk-mark">
                                    <div class="review-star">
                                      <div class="grey-stars"></div>
                                      <div class="filled-stars" style="width: <%rating.rating*20%>%"></div>
                                    </div>                                      
                                </span>                         
                            </label>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3 price-range" ng-show="filter_action.price_flag">                     
                <h4>ช่วงราคา</h4>
                <div class="min-max-price">                             
                    <div class="form-group">
                        <input type="text" placeholder="เริ่มต้น" ng-model="filter_action.filter_price_range.min" ng-blur="filter_action.filter_by_price(filter_action.filter_price_range)" onkeypress="return isNumberKey(event)">
                    </div>
                    <span class="divider">-</span>
                    <div class="form-group">
                        <input type="text" placeholder="สูงสุด" ng-model="filter_action.filter_price_range.max" ng-blur="filter_action.filter_by_price(filter_action.filter_price_range)" onkeypress="return isNumberKey(event)">
                    </div>                              
                </div>
            </div>  
        </div>

        <div class="action-filter form-group col-sm-12 text-center">
            <!-- <button type="button" class="btn btn-light-red" ng-click="filter_action.clearAllFilter('reset_all')" ng-disabled="!filter_action.filter_list.length">@lang('product.reset_filter')</button>
            <button type="button" class="btn btn-dark-grey" ng-click="filter_action.apply_filter($event)" ng-disabled="!filter_action.filter_list.length">@lang('product.apply')</button> -->
        </div>
        </div>
        
        <div class="filter-result" ng-if="filter_action.filter_list.length>0">
            <ul class="filter-select">               
                <li data-ng-repeat="item in filter_action.filter_list track by $index">
                    <!-- for badge -->
                    <span class="btn btn-" ng-if="item.badge_name">
                        <%item.badge_name%> 
                        <a href="javascript:void(0)" ng-click="filter_action.removeFilterHandler(item);">
                            <i class="fas fa-times"></i>
                        </a>
                    </span> 
                     <!-- for badge -->
                    <span class="btn btn-" ng-if="item.f_type=='cate'">
                        <%item.category_name%> 
                        <a href="javascript:void(0)" ng-click="filter_action.removeFilterHandler(item);">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                    <!-- for price -->
                    <span class="btn btn-" ng-if="item.price_type">
                        @lang('product.price')<%item.value%>
                        <a href="javascript:void(0)" ng-click="filter_action.removeFilterHandler(item);">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                    <!-- for review -->
                    <span class="btn btn-" ng-if="item.type && item.type ==='rating'">
                        <div class="review-star">
                          <div class="grey-stars"></div>
                          <div class="filled-stars" style="width: <%item.rating*20%>%"></div>
                        </div>
                        <a href="javascript:void(0)" ng-click="filter_action.removeFilterHandler(item);">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                </li>
                <li ng-if="filter_action.filter_list.length>0">
                    <button type="button" class="btn btn-light-red" ng-click="filter_action.clearAllFilter('reset_all')" ng-disabled="!filter_action.filter_list.length">@lang('product.reset_filter')</button>
                </li>
            </ul>
            <!-- <span class="btn btn-blue"> 
                <i class="fas fa-star"></i> <i class="fas fa-star"></i> <i class="fas fa-star"></i> <i class="fas fa-star"></i> <i class="fas fa-star"></i> 
                <a href="#"><i class="fas fa-times"></i></a>
            </span>
            <span class="btn btn-blue">20-100 บาท/ถุง <a href="#"><i class="fas fa-times"></i></a></span> -->
        </div>
    </div>
    <div  ng-if="!varModel.no_result_found">
        <!-- product listing section -->
        @include('includes.product_listing')
    </div>
    <div class="category-products" ng-if="varModel.no_result_found">
        {{-- {!! getStaticBlock('search-not-found') !!} --}}
        <x-not-found />
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
                        <button class="btn-primary" class="close" data-dismiss="modal" aria-label="Close">@lang('checkout.continue_shopping')</button>
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

    
@endsection 

@section('footer_scripts') 

{!! CustomHelpers::combineCssJs(['js/magicscroll','js/magiczoomplus'],'js') !!}
<script src="{{ Config::get('constants.js_url').'jquery.lazy.min.js' }}"></script>
<script src="{{ Config::get('constants.angular_libs_url').'angular.min.js' }}"></script>
<script src="{{ Config::get('constants.angular_libs_url').'ng-droplet.min.js' }}"></script> 
<script src="{{ Config::get('constants.js_url').'lodash.min.js' }}"></script>
<script src="{{ Config::get('constants.angular_libs_url').'angular-ui-router.min.js' }}"></script>
<script src="{{ Config::get('constants.angular_front_url').'services/service.js' }}"></script>  
<script src="{{ Config::get('constants.angular_front_url').'directive/frontPrdListPaginationDir.js' }}"></script>
<script src="{{ Config::get('constants.angular_front_url').'model/product-listing-app.js' }}"></script>
<script src="{{ Config::get('constants.angular_front_url').'controller/frontend/product-listing-controller.js' }}"></script> 

@stop