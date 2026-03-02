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
    .sortBy ,.sortBy option{
        font-size: 12px;
        height: auto;
    }
</style>
{{-- <input type="hidden" name="searchUrl" value="{{$page =='category' ? action('ProductController@category') : ($page =='categorys'?action('ProductController@categorys') : '') }}"> --}}
    
    <div class="">
        <div class="row">

            @if (isset($product_type) && count($product_type) >= 1)
            <div class="col-12 mb-3">
                <x-product-type-card :producttype="$product_type" />
            </div>
            @endif

            @if (isset($shop_list) && count($shop_list) > 0)
            
            <div class="col-12 mb-3">
                <x-shop-card :shoplist="$shop_list" />
            </div>
    
            @endif

        </div>


        <div class="row">
            @if (isset($product_list) && $product_list->total()>0)
            
            <div class="col-12 mb-3 d-flex justify-content-between align-items-end">
                <div>
                    @if (request('search')) <h1>ผลการค้นหา <span class="text-danger">"{{request('search')}}"</span></h1> @endif
                    @if (request('productCate')) <h1>สินค้า <span class="text-danger">"{{$p_cate->category_name??''}}"</span></h1> @endif
                    @if (request('productType')) <h1>สินค้า <span class="text-danger">"{{$p_type->category_name??''}}"</span></h1> @endif
                    <span>{{$product_list->total()??0}} รายการ</span>
                </div>
                <div class=" mb-3 pt-2">
                    <div>
                        <label for="">เรียงตาม </label>
                        <select name="" id="" class="sortBy form-control form-control-sm">
                            <option value="">ค่าเริ่มต้น</option>
                            <option value="1" {{(request('sortBy') && request('sortBy') == '1') ?'selected':'' }} >ราคา น้อย-มาก</option>
                            <option value="2" {{(request('sortBy') && request('sortBy') == '2') ?'selected':'' }}>ราคา มาก-น้อย</option>
                            <option value="3" {{(request('sortBy') && request('sortBy') == '3') ?'selected':'' }}>ชื่อ ก-ฮ</option>
                            <option value="4" {{(request('sortBy') && request('sortBy') == '4') ?'selected':'' }}>ชื่อ ฮ-ก</option>
                        </select>
                    </div>
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
            <x-not-found />

            {{-- <div class="text-center p-5">
                {!!getStaticBlock('no-item')!!}
            </div> --}}
        @endif
        </div>
    
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

        // initProductCateSlider(resizeSquareImages);
        resizeSquareImages(resizeSquareImages);
        setTimeout(() => {
            resizeSquareImages();
        }, 300);

        $('.sortBy').change(function() {
            let sortBy = $(this).val();
            $('[name="sortBy"]').val(sortBy);
            $('#filterForm').submit();
        });


    });

    // function initProductCateSlider(callback) {
        // if (!$('.product-cate-slider').hasClass('slick-initialized')) {
        //     $('.product-cate-slider').slick({
        //         variableWidth: false,
        //         slidesToShow: 12,
        //         slidesToScroll: 12,
        //         centerMode: false,
        //         infinite: false,
        //         adaptiveHeight: true,
        //         responsive: [
        //             {
        //                 breakpoint: 1024,
        //                 settings: {
        //                     slidesToShow: 8,
        //                     slidesToScroll: 8
        //                 }
        //             },
        //             {
        //                 breakpoint: 768,
        //                 settings: {
        //                     slidesToShow: 6,
        //                     slidesToScroll: 6
        //                 }
        //             }
        //         ]
        //     });
            
        //     if(callback){
        //         setTimeout(() => {
        //         callback();
        //         }, 300);
        //     }
        // }
    // }
    
</script>
@stop
