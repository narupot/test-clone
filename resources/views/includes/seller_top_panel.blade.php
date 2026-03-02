<div class="seller-header clearfix">
    <div class="container">
        <ul class="seller-menu seller-carousel">
            <!-- <li>
                <a href="{{action('Seller\ProductController@sellerProduct')}}" class="sel-paneltxt @if(isset($activetab) && $activetab == 'seller_product_panel') active @endif">
                    <span class="menu-txt">@lang('shop.seller_panel')</span>
                </a>
            </li> -->
            <!-- อ๊อฟปิด Star 09-06-2568 <li>
                <a href="{{action('Seller\ProductController@sellerProduct')}}" class="sel-paneltxt @if(strpos( $_SERVER['REQUEST_URI'], 'seller-product') !== false or strpos( $_SERVER['REQUEST_URI'], 'bargain/bytime') !== false or strpos( $_SERVER['REQUEST_URI'], 'order/delivery-list') !== false) active @endif">
                    <span class="menu-txt"><i class="fa-duotone fas fa-wallet"></i> @lang('shop.seller_panel')</span>
                </a>
            </li> End --> 

            
            <li>
                
                <a href="{{ action('Seller\OrderController@orderHistory') }}" class="@if(strpos( $_SERVER['REQUEST_URI'], 'seller/order/history' ) !== false) active @endif">
                    <i class="fas fa-history"></i> @lang('product.order_history_menu')
                </a>
            </li>
            <li>
                <a href="{{action('Seller\ProductController@index')}}" class="@if(strpos( $_SERVER['REQUEST_URI'], '/product' ) !== false)  active @endif">
                    <i class="fas fa-plus"></i> @lang('product.manage_product')
                </a>
            </li>
            <li>
                <a href="{{action('Seller\CustomerController@index')}}" class="@if(strpos( $_SERVER['REQUEST_URI'], 'customer' ) !== false) active @endif">
                    <i class="fas fa-users"></i> @lang('customer.customer')
                </a>
            </li>
            {{-- <li>
                <a href="{{action('Seller\CreditController@index')}}" class="@if(strpos( $_SERVER['REQUEST_URI'], 'manage-credit' ) !== false) active @endif">
                    <i class="fas fa-credit-card"></i> @lang('shop.manage_credit')
                </a>
            </li> --}}
            <li>
                <a href="{{action('Seller\ReviewController@index')}}" class="@if(strpos( $_SERVER['REQUEST_URI'], 'rating' ) !== false) active @endif">
                    <i class="fas fa-star"></i> @lang('shop.rating')
                </a>
            </li>
            <li>
                <a href="{{action('Seller\ShopController@index')}}" class="@if(strpos( $_SERVER['REQUEST_URI'], 'manage-shop' ) !== false) active @endif">
                    <i class="far fa-home"></i> @lang('shop.manage_store')
                </a>
            </li>
            <li>
                <a href="{{action('Seller\SellerReportController@index')}}" class="@if(strpos( $_SERVER['REQUEST_URI'], 'report' ) !== false) active @endif">
                    <i class="far fa-chart-bar"></i> @lang('seller_report.report')
                </a>
            </li>
            <li class="d-none">
                <a href="{{action('Seller\OrderController@orderOutstandingBalance')}}" class="@if(strpos( $_SERVER['REQUEST_URI'], 'bill' ) !== false) active @endif">
                    <i class="fas fa-dollar-sign"></i> @lang('seller_report.bills')
                </a>
            </li>
        </ul>           
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function(){
	
	    if (jQuery(window).width() < 1023) {
	        jQuery('.seller-carousel').flickity({             
	          resize: true,
	          wrapAround: false,
	          cellAlign: 'left',      
	          pageDots: false,
	          contain: true
	        });
	    }
	
});
</script>