@extends('layouts.app')

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/dataTables.bootstrap', 'css/myaccount', 'css/toastr.min'],'css') !!}
@endsection

@section('header_script')
    var getproductURL = "{{action('ProductsController@getProductByWishlist')}}";
    //for routing url (query string)
    var browser_url =  window.location.pathname;
    var cate_id = null;  
    var paginations = {!! $show_per_page !!};  
    var rating = null;
    var addIntoWishlist = "{{action('ProductsController@addIntoWishlist')}}";
    var removeFromWishlist = "{{action('ProductsController@removeFromWishlist')}}";
    var addProductToCart = "{{action('ProductDetailController@addProductToCart')}}"; 
    var addProductToBargain = "{{action('PopUpController@getCheckBargainPopUp')}}"; 
    var cartUrl = "{{ action('Checkout\CartController@shoppingCart') }}";
    var error_msg = {
        'quantity_error' : "@lang('product.please_select_at_least_one_quantity')",
        'server_error': "@lang('product.server_not_responsed')",
    }; 
    var page_type = 'user_wishlist';  
    var txt_no = "@lang('common.no')";
    var text_ok_btn = "@lang('common.ok_btn')";
    var text_success = "@lang('common.text_success')";
    var text_error = "@lang('common.text_error')";
    var text_yes_remove_it = "@lang('common.yes_remove_it')";
    var yes_complete_it = "@lang('shopping_list.yes_complete_it')";
    var are_you_sure = "@lang('shopping_list.are_you_sure')";
    var text_want_to_remove_product_from_wishlist = "@lang('product.text_want_to_remove_product_from_wishlist')";
    var text_confirm_btn = "@lang('common.text_confirm_btn')";

    var text_create_shopping_list = "@lang('shopping_list.create_shopping_list')";
    var text_shopping_list_name = "@lang('shopping_list.create_shopping_list_name')";

    var text_save_btn = "@lang('common.save_btn')";
    
    
    var text_you_need_to_write_shopping_list_name = "@lang('shopping_list.text_you_need_to_write_shopping_list_name')";

    var lang_baht = "@lang('common.baht')";
@stop

@section('breadcrumbs')

@stop

@section('content')
    <h1 class="page-title">@lang('user.wishlist_products')</h1>                   
    <div class="user-wishlist" ng-controller="ProductListController">
        <!-- product listing section -->
        @include('includes.product_listing')
        
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
                            <button class="btn- btn" class="close" data-dismiss="modal" aria-label="Close">@lang('checkout.continue_shopping')</button>
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
    {!! CustomHelpers::combineCssJs(['js/jquery.dataTables','js/dataTables.bootstrap', 'js/user/myaccount'],'js') !!} 
    <script type="text/javascript" src="{{ Config::get('constants.js_url').'jquery.lazy.min.js' }}"></script>
    <script type="text/javascript" src="{{ Config::get('constants.angular_libs_url').'angular.min.js' }}"></script>
    <script type="text/javascript" src="{{ Config::get('constants.angular_libs_url').'ng-droplet.min.js' }}"></script> 
    <script type="text/javascript" src="{{ Config::get('constants.js_url').'lodash.min.js' }}"></script>
    <script type="text/javascript" src="{{ Config::get('constants.angular_libs_url').'angular-ui-router.min.js' }}"></script>
    <script src="{{ Config::get('constants.angular_front_url').'services/service.js' }}"></script>  
    <script src="{{ Config::get('constants.angular_front_url').'directive/frontPrdListPaginationDir.js' }}"></script>
    <script src="{{ Config::get('constants.angular_front_url').'model/product-listing-app.js' }}"></script>
    <script src="{{ Config::get('constants.angular_front_url').'controller/frontend/product-listing-controller.js' }}"></script>   
    <!-- begining of page level js -->
    <script type="text/javascript">
        var lang = {
            zeroRecords : "@lang('data_table.zeroRecords')",
            emptyTable : "@lang('data_table.emptyTable')",
            search : "@lang('data_table.search')",
            display : "@lang('data_table.display')",
            entries : "@lang('data_table.entries')", 
            filtered_from : "@lang('data_table.filtered_from')",
            total_entries : "@lang('data_table.total_entries')",
            loadingRecords : "@lang('data_table.loadingRecords')",
            paginate_first : "@lang('data_table.paginate_first')",
            paginate_last : "@lang('data_table.paginate_last')",
            paginate_next : "@lang('data_table.paginate_next')",
            paginate_previous : "@lang('data_table.paginate_previous')",
            processing : "@lang('data_table.processing')",
            showing : "@lang('data_table.showing')",
            to : "@lang('data_table.to')",
            of : "@lang('data_table.of')",
        };
    </script>
    <script>
    $(document).ready(function() {
        $('#table').dataTable({
            ordering: false,
            //bLengthChange: false,
            //searching: false,
            "language": {
                "processing": lang.processing,
                "zeroRecords": lang.zeroRecords,
                "emptyTable": lang.emptyTable,
                "info": lang.showing + "_START_"+ lang.to +"_END_"+ lang.of +"_TOTAL_"+ lang.entries,
                "infoEmpty" : lang.showing+" 0 "+ lang.to + " 0 " + lang.of + " 0 "+ lang.entries,
                "search" : lang.search,
                "lengthMenu" : lang.display+" _MENU_ "+lang.entries,
                "infoFiltered" :   "("+lang.filtered_from+" _MAX_ "+lang.total_entries+")",
                "loadingRecords" : lang.loadingRecords,
                "paginate" : {
                    "first" : lang.paginate_first,
                    "last" : lang.paginate_last,
                    "next" : lang.paginate_next,
                    "previous" : lang.paginate_previous
                },
            },
        });
    });
    </script>
    <!-- end of page level js -->
@endsection