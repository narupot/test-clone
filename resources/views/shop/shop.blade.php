@extends('layouts.app') 

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount','css/bootstrap-select', 'css/toastr.min'],'css') !!}
    <link rel="stylesheet" type="text/css" href="{{Config('constants.admin_css_url')}}cropper.min.css">
    <link rel="stylesheet" type="text/css" href="{{Config('constants.css_url')}}jquery.fancybox.min.css">
  
<style>
    .shop-banner-header .shop-ban-img{
        min-height: 300px;
    }
    .shop-banner-header .shop-banner-content{
        margin-top: -200px;
        background-color: rgba(255, 255, 255, 0.9);
        box-shadow: 1px 1px 5px 0px #ccc;
    }
    .shop-banner-content .shop-img-warp{
        margin-left: initial;
    }
    
    .shop-banner-header{
        z-index: 1;
        margin-left: -15px;
        margin-right: -15px;
    }
    .shop-img-warp .shop-img{
        border: 3px solid lightgray;
        margin: 0 0 15px 0;
        width: 150px;
        height: 150px;
    }
    .nav-fill .nav-item a{
        height: 50px;
        line-height: 35px;
        border-bottom: 3px solid transparent; 
        min-width: max-content;
    }
    .nav-fill .nav-item a.active,
    .nav-fill .nav-item a:hover{
        border-color:white;
    }
    .nav-fill .nav-item{
        flex: 1 1;
    }
    .review-star{
        margin-top: -5px
    }
</style>

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
    @if($category)
        var cat_data ={!! $category !!};
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

    <div class="shop-banner-header mb-4" >
        <div class="shop-ban-img" style="background: url({{getImgUrl($shop_details->banner,'banner')}}) center center / cover no-repeat; ">
            {{-- <img src="{{getImgUrl($shop_details->banner,'banner')}}" alt=""> --}}
        </div>

        <div class="col-md-11 mx-auto shop-banner-content py-md-4 rounded">
            <div class="d-flex w-100">
                <div class="flex-shrink-1 mr-4 shop-img-warp">
                    {{-- <img class="rounded-circle mb-2" src="{{getShopLogoImageUrl($shop_details->logo,'153x153')}}" alt=""> --}}
                    <div class="mx-auto rounded-circle shop-img" style="background: url({{getShopLogoImageUrl($shop_details->logo,'153x153')}}) center center / cover no-repeat; "></div>
                    <div class="d-flex justify-content-between align-items-center " >
                        <button type="button" id="{{$shop_details->shop_url}}" class="shop-wish btn btn-sm btn-outline-danger btn-dark-grey w-100 small px-2 mx-2 @if($isFavorite) active @endif" >
                            <i class="fas fa-heart"></i> @if($isFavorite)ถูกใจร้านค้านี้ @else เพิ่มรายการถูกใจ @endif</button>

                        <button type="button" class="btn btn-sm btn-danger px-2 small chat-wrap btn-buyer-chat">
                            <i class="fa fa-comment"></i> @lang('product.talk_to_shop')
                        </button>
                    </div>
                </div>
                
                <div class="flex-grow-1 mb-1">
                    <div class="">
                        <h1>{{{ isset($shop_details->shopDesc->shop_name)? $shop_details->shopDesc->shop_name : ''}}}</h1>
                    </div>
                    

                    <div class="side-content ">
                        <div class="shop-label text-secondary mb-2">
                            @if($shop_details->shop_status=='open')
                                <small class="badge badge-success  ">@lang('shop.open')</small>
                            @else 
                                <small class="badge badge-secondary">@lang('shop.closed')</small>
                            @endif
                            @lang('shop.last_update') {{diffThaiTime($shop_details->updated_at)}}
                        </div>
                        {{-- <p>{{{ isset($shop_details->shopDesc->description)? $shop_details->shopDesc->description :'NA' }}}</p> --}}

                        <div class="row text-secondary small">
                            <div class="col-6 mb-2">
                                <i class="fa fa-box"></i> รายการสินค้า : <span class="text-danger">{{$shop_details->product_count??''}}</span>
                            </div>
                            <div class="col-6 mb-2">
                                <i class="fa fa-users"></i> ผู้ติดตาม : <span class="text-danger">{{$shop_details->favorite_shop_count??''}}</span>
                            </div>
                            <div class="col-6 mb-2">
                                <i class="fa fa-shop"></i> 
                                <a class="text-danger text-link" data-toggle="collapse" href="#shopDesc" role="button" aria-expanded="false" aria-controls="shopDesc">
                                    ข้อมูลร้านค้า
                                </a> 
                            </div>
                            <div class="col-6 mb-2">
                                <i class="fa fa-star"></i> คะแนน : 
                                <div class="review-star">
                                    <div class="grey-stars"></div>
                                    <div class="filled-stars" style="width: {{ $shop_details->avg_rating*20 }}%"></div>
                                </div>
                                 {{-- <span class="text-danger">(การให้คะแนนทั้งหมด คน)</span> --}}
                            </div>
                            {{-- <div class="col-6 mb-2">
                                <i class="fa fa-comment"></i> ประสิทธิภาพการตอบแชท :  <span class="text-danger">(ภายในชั่วโมง)</span>
                            </div> --}}
                        </div>
                    </div>

                    
                    <div class="collapse" id="shopDesc">
                        <div class="card card-body py-3 border-top border-bottom mb-2">
                            <h3><b>ข้อมูลร้านค้า : </b></h3>
                            <div class="text-secondary small mb-4">
                                {{{ isset($shop_details->shopDesc->description)? $shop_details->shopDesc->description :'' }}}
                            </div>
                            <div class="row text-secondary small">
                                {{-- <i class="fa fa-box"></i> รายการสินค้า : <span class="text-danger">{{$shop_details->product_count??''}}</span> --}}
                            
                                <div class="col-6 mb-2">
                                    <strong class="side-label">@lang('shop.shop_open_close_time') : </strong>
                                    <time class="time">{{$shop_details->open_time}} - {{$shop_details->close_time}}</time>
                                </div>
                                <div class="col-6 mb-2">
                                    <strong class="side-label">@lang('shop.phone_no')</strong>
                                    <time class="time">{{$shop_details->ph_number??''}}</time>
                                </div>

                                <div class="col-6 mb-2">
                                    <strong class="side-label">@lang('shop.line_link')</strong>
                                    <time class="time">{{$shop_details->line_link??''}} </time>
                                </div>

                                {{-- <div class="col-6 mb-2">
                                    <strong>@lang('shop.shops_location')</strong>       
                                    <div class="shop-location-row">
                                        @lang('shop.market') : {{$shop_details->seller_description??''}}
                                    </div>
                                </div> --}}
                                <div class="col-6 mb-2">
                                    <div class="shop-location-row">
                                        @lang('shop.panel_no') : {{$shop_details->panel_no??''}}
                                    </div>
                                </div>
                                <div class="col-12 mb-2">
                                    <strong>@lang('shop.shops_images')</strong>
                                    <ul class="shop-img-list justify-content-start">
                                        @if($shop_details->shop_image??false !='')
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
                                
                                <div class="col-12 mb-2">
                                    <div class="shop-location-row">
                                        <strong> @lang('shop.map')</strong>
                                        @if($shop_details->map_image??false !='')
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

                            </div>
                        </div>

                    </div>
                    
                    <!-- Shop credit -->
                    {{-- <div class=" mb-2">
                        <div class="credit-req ">
                            @if($credit_request=='requested')
                                <button class="btn btn-sm bg-success px-3 small border-0">@lang('shop.already_send_credit_request')</button>
                            @elseif($credit_request=='show')
                                <button class="btn btn-sm btn-danger px-3 small credit_request" id="{{$shop_details->id}}" >@lang('shop.credit_request')</button>
                            @else
        
                            @endif
                        </div>
                    </div> --}}

                </div>
                
            </div>

        </div>

            
    </div>

    <div class="shop-content mb-4" >
        <ul class="nav nav-fill mb-4 rounded " id="pills-tab" role="tablist" style="background: url(/images/bg-footer.png);">
            {{-- <li class="nav-item ">
              <a href="{{ action('ShopController@index', $shop_details->shop_url) }}?search={{request('search')}}&tab=home"
              class="nav-link text-white {{
                request('tab')=='home' 
                || request('tab')=='' 
                || (request('tab')!='all' && request('tab')!='bestsell' && request('tab')!='recommend') 
                ? 'active' : ''
                }} ">หน้าแรก</a>
            </li> --}}
            <li class="nav-item ">
              <a href="{{ action('ShopController@index', $shop_details->shop_url) }}?tab=all"
                class="nav-link text-white {{request('tab')=='all' ? 'active' : ''}}" >สินค้าทั้งหมด</a>
            </li>
            {{-- <li class="nav-item ">
              <a href="{{ action('ShopController@index', $shop_details->shop_url) }}?search={{request('search')}}&tab=bestsell" 
                class="nav-link text-white {{request('tab')=='bestsell' ? 'active' : ''}}" >สินค้าขายดี</a>
            </li>
            <li class="nav-item ">
              <a href="{{ action('ShopController@index', $shop_details->shop_url) }}?search={{request('search')}}&tab=recommend" 
                class="nav-link text-white {{request('tab')=='recommend' ? 'active' : ''}}" >สินค้าแนะนำ</a>
            </li> --}}
        </ul>
        <div class="tab-content" id="">
            <div class="show col" role="tabpanel" >
                    {{-- @if(request('tab')=='home' || request('tab')=='' || (request('tab')!='all' && request('tab')!='bestsell' && request('tab')!='recommend') ) --}}
                        @if ( isset($product_list) && count($product_list) > 0 )    
                            <div class="row">
                                @foreach ($product_list ?? [] as $product)
                                    <x-product-card :product="$product" :row="6" />
                                @endforeach
                                

                            </div>
                            
                            <div>
                                @if ($product_list??false)                        
                                {!! $product_list->links('components.pagination') !!}
                                @endif
                            </div>

                        @else
                        <div class="d-flex justify-content-center text-center w-100 mb-5">
                            <div class="col-lg-4 col-md-8 col-sm-10">
                                <h1 class="text-danger"><strong>สินค้าออนไลหมดชั่วคราว</strong></h1>
                                <p>
                                    
                                    <div>กรุณาติดต่อเรา โทร 02-023-9903</div>
                                </p>
                        
                                <button type="button" id="btn-other-search" class="btn btn-danger w-100 py-1">ค้นหาสินค้าอื่นในร้านค้านี้</button>
                                <div class="my-3">
                                    หรือ
                                </div>
                                <a href="{{action('ProductsController@search')}}?search={{request("search")}}" class="btn btn-danger w-100 py-1">ค้นหา {{'"'.request("search").'"'}} จากทุกร้านค้าในระบบ</a>
                        
                            </div>
                            
                        </div>
                        @endif

                    {{-- @endif --}}


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



{{-- <hr>
<div class="shop-wish-vacation">
    <span class="shop-wish @if($isFavorite) active @endif" id="{{$shop_details->shop_url}}"><i class="fas fa-heart"></i> <span class="fav-shop">@lang('shop.favorit_shop')</span></span>
    @if($shop_details->shop_status=='close')
    <h2>@lang('shop.shop_vacation')</h2>
    <!-- <span class="shop-date">@lang('shop.open_on'): 20/12/2018</span> -->
    @endif           
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
                <div class="side-innder-heading">
                    <h3>@lang('shop.shop_information')</h3>
                    <a class="btn-blue" href={{action('ShopController@index',$shop_details->shop_url)}}>ดูสินค้าทั้งหมด</a>
                </div>
                
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
</div> --}}




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
        $currentUrl = window.location.href;
        $('#searchForm').attr('action', $currentUrl);
        resizeSquareImages();

        $('#searchProduct').attr('placeholder', 'ค้นหาสินค้าในร้านค้า');

        $('#btn-other-search').on('click', function() {
            $('#searchProduct').focus().select();
        });
    });
</script>
{!! CustomHelpers::combineCssJs(['js/seller/manage_shop'],'js') !!}
{{-- <script type="text/javascript" src="{{ Config::get('constants.js_url').'jquery.lazy.min.js' }}"></script> --}}
{{-- <script type="text/javascript" src="{{ Config::get('constants.angular_libs_url').'angular.min.js' }}"></script> --}}
{{-- <script type="text/javascript" src="{{ Config::get('constants.angular_libs_url').'ng-droplet.min.js' }}"></script>  --}}
{{-- <script type="text/javascript" src="{{ Config::get('constants.js_url').'lodash.min.js' }}"></script> --}}
{{-- <script type="text/javascript" src="{{ Config::get('constants.angular_libs_url').'angular-ui-router.min.js' }}"></script> --}}
{{-- <script src="{{ Config::get('constants.angular_front_url').'services/service.js' }}"></script>   --}}
{{-- <script src="{{ Config::get('constants.angular_front_url').'directive/frontPrdListPaginationDir.js' }}"></script> --}}
{{-- <script src="{{ Config::get('constants.angular_front_url').'model/product-listing-app.js' }}"></script> --}}
{{-- <script src="{{ Config::get('constants.angular_front_url').'controller/frontend/product-listing-controller.js' }}"></script>  --}}
@stop