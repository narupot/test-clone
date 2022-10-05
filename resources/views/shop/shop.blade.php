@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/bootstrap-select', 'css/toastr.min'],'css') !!}
    <link rel="stylesheet" type="text/css" href="{{Config('constants.admin_css_url')}}cropper.min.css">
    <link rel="stylesheet" type="text/css" href="{{Config('constants.css_url')}}jquery.fancybox.min.css">
  

@endsection


@section('header_script')
    var url_checkStoreName = "{{ action('Auth\SellerRegisterController@checkStoreName') }}";
    var url_checkStoreUrl = "{{ action('Auth\SellerRegisterController@checkStoreUrl') }}";
    var url_deleteshopimage = "{{ action('Seller\ShopController@deleteShopImg') }}";
    var txt_delete_confirm = "@lang('common.are_you_sure_to_delete_this_record')";
    var yes_delete_it = "@lang('common.yes_delete_it')";
    var txt_no = "@lang('common.no')";
    var yes_send_it = "@lang('common.yes_send_it')";
    var txt_cancel = "@lang('common.txt_cancel')";
    var text_ok_btn = "@lang('common.ok_btn')";
    var text_success = "@lang('common.text_success')";
    var text_error = "@lang('common.text_error')";
    var text_yes_remove_it = "@lang('common.yes_remove_it')";
    var text_create_shopping_list = "@lang('shop.text_create_shopping_list')";
    var text_shopping_list_name = "@lang('shop.text_shopping_list_name')";
    var text_save_btn = "@lang('common.text_save_btn')";
    var text_confirm_btn = "@lang('common.text_confirm_btn')";
    var text_want_to_remove_product_from_wishlist = "@lang('shop.text_want_to_remove_product_from_wishlist')";
    var text_you_nned_to_write_shopping_list_name = "@lang('shop.text_you_nned_to_write_shopping_list_name')";
    var are_you_sure = "@lang('common.are_you_sure')";
    var lang_baht = "@lang('common.baht')";

    var url_manageFavoriteShop = "{{ action('ShopController@manageFavoriteShop') }}"; 
    var credit_request_url = "{{ action('ShopController@sendCreditRequest') }}";
    var checkLogin_url = "{{ action('ShopController@checkLogin') }}";
    var not_login_msg = "@lang('shop.credit_request_to_seller_confirmation_msg')";
    var getproductURL = "{{action('ProductsController@getProductsByShop')}}";
    //for routing url (query string)
    var browser_url =  window.location.pathname;
    var cate_id = null;  
    var paginations = {!! $show_per_page !!};  
    var rating = null;
    var addIntoWishlist = "{{action('ProductsController@addIntoWishlist')}}";
    var removeFromWishlist = "{{action('ProductsController@removeFromWishlist')}}";
    var addProductToCart = "{{action('ProductDetailController@addProductToCart')}}"; 
    var cartUrl = "{{ action('Checkout\CartController@shoppingCart') }}";
    var shopFilter = "{{action('ShopController@shopFilter')}}";
    var error_msg = {
        'quantity_error' : "@lang('product.please_select_at_least_one_quantity')",
        'server_error': "@lang('product.server_not_responsed')",
    };
    var shop_id = {!! $shop_details->id !!};
    
    var addProductToBargain = "{{action('PopUpController@getCheckBargainPopUp')}}"; 
    @if($cat_data)
        var cat_data ={!! $cat_data !!};
    @else
        var cat_data = null;
    @endif
    
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

    <div class="shop-banner-header">
        <div class="shop-ban-img">
            <img src="{{getImgUrl($shop_details->banner,'banner')}}" alt="">
        </div>
        <div class="shop-banner-content">
            <div class="shop-img">
                <img src="{{getShopLogoImageUrl($shop_details->logo,'153x153')}}" alt="">
            </div>
            <div class="shop-wish-vacation">
                <span class="shop-wish @if($isFavorite) active @endif" id="{{$shop_details->shop_url}}"><i class="fas fa-heart"></i> <span class="fav-shop">@lang('shop.favorit_shop')</span></span>
                @if($shop_details->shop_status=='close')
                <h2>@lang('shop.shop_vacation')</h2>
                <!-- <span class="shop-date">@lang('shop.open_on'): 20/12/2018</span> -->
                @endif           
            </div>
        </div>
    </div>
    <div class="shop-content" ng-controller="ProductListController" ng-cloak>
        <div class="row">
            <aside class="col-md-3 left-sidebar">
                <div class="side-heading">
                    <h2>{{{ isset($shop_details->shopDesc->shop_name)? $shop_details->shopDesc->shop_name : 'NA'}}}</h2>
                    <div class="text-center">
                        <div class="review-star">
                          <div class="grey-stars"></div>
                          <div class="filled-stars" style="width: {{ $shop_details->avg_rating*20 }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="side-content">
                    <p>{{{ isset($shop_details->shopDesc->description)? $shop_details->shopDesc->description :'NA' }}}</p>
                </div>
                <div class="credit-req">
                    @if($credit_request=='requested')
                        <button class="btn-dark-grey w-100">@lang('shop.already_send_credit_request')</button>
                    @elseif($credit_request=='show')
                        <a href="javascript:://" class="credit_request" id="{{$shop_details->id}}" style="display: none;">@lang('shop.credit_request')</a>
                    @else

                    @endif
                </div>
                <div class="side-heading">
                    <h3>@lang('shop.shop_information')</h3>
                </div>
                <div class="side-content">
                    <span class="side-label">@lang('shop.shop_open_close_time')</span>
                    <time class="time">{{$shop_details->open_time}} - {{$shop_details->close_time}}</time>
                    @if($shop_details->ph_number)
                        <span class="side-label">@lang('shop.phone_no')</span>
                        <time class="time">{{$shop_details->ph_number}}</time>
                    @endif
                    @if($shop_details->line_link)
                        <span class="side-label">@lang('shop.line_link')</span>
                        <time class="time">{{$shop_details->line_link}} </time>
                    @endif
                    <div class="shop-status">
                        <span class="side-label">@lang('shop.shop_status')</span>
                        <div class="shop-status-name">
                            
                            @if($shop_details->shop_status=='open')
                            <button class="btn-blue ">@lang('shop.open')</button>
                            <div class="shop-status-txt">
                                <span class="last-update">
                                    <span class="d-block">@lang('shop.last_update')</span>
                                    {{$shop_details->updated_at}}
                                </span>
                            </div>
                            @else 
                            <button class="btn">@lang('shop.closed')</button>
                            <div class="shop-status-txt">
                                <span class="vacation-label">@lang('shop.vacation')</span>
                            </div>  
                            @endif                               
                        </div>
                    </div>
                </div>

                <div class="side-heading">
                    <h3>@lang('shop.shops_location')</h3>                         
                </div>
                <div class="side-content">
                    <div class="shop-location-row">
                        @lang('shop.market') : {{$shop_details->seller_description}}
                    </div>
                    <div class="shop-location-row">
                        @lang('shop.panel_no') : {{$shop_details->panel_no}}
                    </div>
                    <div class="shop-location-row">
                        @lang('shop.map')
                        @if($shop_details->map_image!='')
                            @php($map_img_list = explode(',',$shop_details->map_image))
                            @foreach($map_img_list as $key => $image)
                            <div class="map-img">
                                <a href="{{getImgUrl($image,'map')}}" class="fancybox01" data-fancybox="map">
                                    <img src="{{getShopImageUrl($image,'100x100')}}" alt="">
                                </a>
                            </div>
                            @endforeach
                        @endif

                    </div>
                </div>
                <div class="side-heading">
                    <h3>@lang('shop.shops_images')</h3>                            
                </div>
                <div class="side-content">
                    <ul class="shop-img-list justify-content-start">
                        @if($shop_details->shop_image!='')
                            @php($shop_img_list = explode(',',$shop_details->shop_image))
                            @foreach($shop_img_list as $key => $image)
                            <li class="mr-2">
                                <a href="{{getImgUrl($image,'shop')}}" class="fancybox0" data-fancybox="gallery">
                                    <img src="{{getShopImageUrl($image,'80x80')}}" alt="">
                                </a>
                            </li>
                            @endforeach
                        @endif
                    </ul>                           
                </div>

            </aside>
            <div class="col-md-9">
                <div class="shop-content-header">
                    <div class="respons-shop-list">
                        <ul class="respon-update">
                            <!-- <li>
                                <span class="shop-label">@lang('shop.shops_chat_response')</span>
                                <span class="res-num">-</span>
                            </li> -->
                            <li class="w-100" >
                                <span class="shop-label">@lang('shop.last_update')</span>
                                <span class="res-num" style="border-radius: 10px;">{{$shop_details->updated_at}}</span> 
                            </li>
                            <!-- <li>
                                <span class="shop-label">@lang('shop.response_speed')</span>
                                <span class="res-num">-</span>
                            </li>
                            <li>
                                <span class="shop-label">@lang('shop.order_cancilation')</span>
                                <span class="res-num">-</span>
                            </li> -->
                        </ul>
                    </div>
                    <!-- hide filter by turk task -->
                    {{--<div class="filter-wrap">
                        <h3>@lang('shop.badges')</h3>
                        <div class="chk-group">
                            <label class="chk-wrap" ng-repeat="item in shop_filter_data.badges">
                                <input type="checkbox" name="<%item.badge_name%>" ng-model="item.checked" ng-change="filter_action.badgeHandler(item)" />
                                <span class="chk-mark"><%item.badge_name%> <img src="<%item.icon%>" alt="<%item.badge_name%>"></span>
                            </label>
                        </div>
                        <h3>@lang('shop.category')</h3>
                        <div class="chk-group">
                            <label class="chk-wrap" ng-repeat="item in shop_filter_data.category"> 
                                <input type="checkbox" name="<%item.category_name%>" ng-model="item.checked" ng-change="filter_action.categoryHandler(item)" />
                                <span class="chk-mark"><%item.category_name%> <img src="<%item.img%>" alt="<%item.category_name%>"></span>
                            </label>
                        </div>
                    </div>--}}


                    {{-- @if(Auth::check() && $shop_details->user_id != Auth::id())
                    <div class="chat">
                        <button class="btn-default">
                            <i class="fas fa-comments"></i>
                            @lang('shop.chat')                                    
                            <span class="available"></span>
                        </button>
                    </div> 
                    @endif  --}}                       
                </div>
                
                <!-- product listing section -->
                @include('includes.product_listing')
                
                <div class="category-products" ng-if="varModel.no_result_found">      
                    {!! getStaticBlock('search-not-found') !!}
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
                                    <button class="btn-blue" class="close" data-dismiss="modal" aria-label="Close">@lang('checkout.continue_shopping')</button>
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
        </div>
    </div>

@endsection 

@section('footer_scripts') 

<script src="{{ Config('constants.js_url') }}jquery.fancybox.min.js" type="text/javascript"></script>

<script type="text/javascript">       
    $().fancybox({
        selector : '.mapfancy'
    }); 
    $().fancybox({
        selector : '.shopfancy'
    });    
    
</script>
<script>
    $(document).ready(function() {
        $('[data-fancybox="gallery"]').fancybox({           
            thumbs : {              
                autoStart : true,
                axis      : 'x'
            }
        });
    });
</script>
{!! CustomHelpers::combineCssJs(['js/seller/manage_shop'],'js') !!}
<script type="text/javascript" src="{{ Config::get('constants.js_url').'jquery.lazy.min.js' }}"></script>
<script type="text/javascript" src="{{ Config::get('constants.angular_libs_url').'angular.min.js' }}"></script>
<script type="text/javascript" src="{{ Config::get('constants.angular_libs_url').'ng-droplet.min.js' }}"></script> 
<script type="text/javascript" src="{{ Config::get('constants.js_url').'lodash.min.js' }}"></script>
<script type="text/javascript" src="{{ Config::get('constants.angular_libs_url').'angular-ui-router.min.js' }}"></script>
<script src="{{ Config::get('constants.angular_front_url').'services/service.js' }}"></script>  
<script src="{{ Config::get('constants.angular_front_url').'directive/frontPrdListPaginationDir.js' }}"></script>
<script src="{{ Config::get('constants.angular_front_url').'model/product-listing-app.js' }}"></script>
<script src="{{ Config::get('constants.angular_front_url').'controller/frontend/product-listing-controller.js' }}"></script> 
@stop