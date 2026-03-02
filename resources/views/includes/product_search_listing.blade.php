@extends('layouts.app')

{{-- @section('header_style')
    {!! CustomHelpers::combineCssJs(['css/magicscroll','css/magiczoomplus', 'css/toastr.min'],'css') !!}
    <style type="text/css">
        .product-info .action-btn { display: none; }
    </style>

@endsection --}}
{{--
@section('header_script')   
    //for routing url (query string)
    var browser_url =  window.location.pathname;
    var cate_id = null;
    var name = "{{isset($_GET['search']) ? $_GET['search']: '' }}";    
    var getproductURL = "{{action('ProductsController@getProductsShopBysearch')}}";
    var getshopURL = "{{action('ProductsController@getShopBysearch')}}";
    
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
--}}

@section('content')
    {{-- <div ng-controller="ProductListController" ng-cloak>
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
    

    <!-- product listing section -->
    @include('includes.product_main_listing')

    <div class="category-products" ng-if="varModel.no_result_found && varModel.no_result_found1">
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
    </div> --}}

<style>
    
    .filter-field{
        height: max-content;
    }
    
    .product-item-info .link-product-name.fw-100{
        font-weight: 100 !important;
    }
</style>
    
    <div class="pt-4">
        {{-- <h1>{{$page == 'category'?'ชนิดสินค้า':($page == 'categorys'?'ประเภทสินค้า':'')}} <span class="text-danger">"{{$product_cate->category_name??''}}"</span></h1> --}}
        <div class="row pt-4">

            @if (isset($product_type) )
            <div class="col-12 mb-5">
                <x-product-type-card :producttype="$product_type" />
            </div>
            @endif

            @if (isset($shop_list) && count($shop_list) > 0)
            
            <div class="col-12 mb-5">
                <x-shop-card :shoplist="$shop_list" />
            </div>
    
            @endif

        </div>


        <div class="row">
            @if (isset($product_list) && $product_list->total()>0)
            
            <div class="col-12 mb-3">
                @if (request('search'))
                <h2>ผลการค้นหา <span class="text-danger">"{{request('search')}}"</span></h2>
                @endif

                <div>
                    <span>{{$product_list->total()??0}} รายการ</span>
                    {{-- <div>
                        <label for=""></label>
                        <select name="" id="">
                            <option value="">รายการแนะนำ</option>
                            <option value=""></option>
                            <option value=""></option>
                        </select>
                    </div> --}}
                </div>
            </div>

            <div class="col-lg-3 filter-field  mb-4">
                <x-product-filter :productsize="$product_size" :productgrade="$product_grade" />
            </div>
            <div class="col-lg-9">
                <div class="product_list_warpper row ml-lg-1">

                        @foreach ($product_list ?? [] as $product)
                        <x-product-card :product="$product" :row="4" />
                        @endforeach

                    
                </div>
                <div>
                    
                    @if ($product_list??false)                        
                    {!! $product_list->links('components.pagination') !!}
                    @endif
                    
                </div>
            </div>
            
            @else
            {{-- <div class="text-center p-5">
                {!!getStaticBlock('no-item')!!}
            </div> --}}
            <x-not-found />
        </div>
    
    @endif


    </div>



@endsection

@section('footer_scripts') 

{{-- {!! CustomHelpers::combineCssJs(['js/magicscroll','js/magiczoomplus'],'js') !!}
<script src="{{ Config::get('constants.js_url').'jquery.lazy.min.js' }}"></script>
<script src="{{ Config::get('constants.angular_libs_url').'angular.min.js' }}"></script>
<script src="{{ Config::get('constants.angular_libs_url').'ng-droplet.min.js' }}"></script> 
<script src="{{ Config::get('constants.js_url').'lodash.min.js' }}"></script>
<script src="{{ Config::get('constants.angular_libs_url').'angular-ui-router.min.js' }}"></script>
<script src="{{ Config::get('constants.angular_front_url').'services/service.js' }}"></script>  
<script src="{{ Config::get('constants.angular_front_url').'directive/frontPrdListPaginationDir.js' }}"></script>
<script src="{{ Config::get('constants.angular_front_url').'model/product-listing-app.js' }}"></script>
<script src="{{ Config::get('constants.angular_front_url').'controller/frontend/product-main-listing-controller.js' }}"></script>  --}}


{{-- // var totalShopCount = {{ $total_shop_count }};
// $('.total_shop').text('Total Shops: ' + totalShopCount);

// $('.other_shop').click(function() {
//     window.location.href = "{{ action('ProductsController@shopList') }}";
// }); --}}

{!! CustomHelpers::combineCssJs(['js/bootstrap.bundle'],'js') !!}

<script>
    $(document).ready(function() {

        $('.btn_clean_filter').click(function() {
            $('.filter-field input[type="checkbox"]').prop('checked', false);
        });


    });

    
    
</script>
@stop
