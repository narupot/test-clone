<!-- <div class="end-shopping-wrap form-group">
    <div class="row">
        <div class="col-sm-8">
            <div class="product-purchase">Already paid <span>5</span> List From <span>12</span> List of Shopping List</div>
            <div class="product-amount"> Paid <span>2,000</span> Baht | Qty <span>10</span> Products </div>
        </div>

        <div class="col-sm-4 text-right">                                   
            <button class="btn-blue">End Shopping</button>                                  
        </div>                              
    </div>
</div> -->
@php 
$tot_bargain_count_noti = getBargainForSellerCount();
$total_products = getProductsForSellerCount();
$total_delivery_items = getDeliveryItemsForSellerCount();
@endphp
<div class="seller-panneltab form-group">
    <ul class="nav" id="seller-Tab">
        <li>
            <a href="{{action('Seller\ProductController@sellerProduct')}}" @if(isset($activetab) && $activetab == 'seller_product_panel')class="active"@endif> 
                <span class="icon-list"><i class="fas fa-lemon"></i></span>
                <span class="tab-name">@lang('product.product_list')</span>
                <span class="info-list">{{$total_products}}</span>
            </a>
        </li>
        <li>
            <a href="{{action('Seller\BargainController@index','bytime')}}" @if(isset($activetab) && $activetab == 'bargain')class="active"@endif> 
                <span class="icon-list"><i class="far fa-usd-circle"></i></span>
                <span class="tab-name">@lang('bargain.product_bargain')</span>
                <span class="info-list">{{$tot_bargain_count_noti}}</span>
            </a>
        </li>
        <li>
            <a href="{{action('Seller\OrderController@deliveryList')}}" class="@if(strpos( $_SERVER['REQUEST_URI'], 'order/delivery-list' ) !== false) active @endif"> 
                <span class="icon-list"><i class="fas fa-truck"></i></span>
                <span class="tab-name">@lang('order.delivery_list')</span>                                     
                <span class="info-list" id="totpendingitems">{{$total_delivery_items}}</span>
            </a>
        </li>
        
        <!--li>
            <a href="{{action('Seller\StockMemoController@index')}}" class="@if(strpos( $_SERVER['REQUEST_URI'], 'seller/stock-memo' ) !== false) active @endif"> 
                <span class="icon-list"><i class="fas fa-clipboard-list"></i></span>
                <span class="tab-name">@lang('stock_memo.stock_memo')</span>
                <span class="info-list">3</span>
            </a>
        </li-->                               
    </ul>
</div>