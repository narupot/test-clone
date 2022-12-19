
<div class="account-nav">
    <h3>{{ Auth::user()->display_name ?? '' }}</h3> 
    <ul class="box-grey">
        <li><a href="{{ action('User\OrderController@orderHistory') }}" class="@if(strpos( $_SERVER['REQUEST_URI'], 'user/order' ) !== false && strpos( $_SERVER['REQUEST_URI'], 'delivery-list' )== false && strpos( $_SERVER['REQUEST_URI'], 'user/order/pending-order' )== false) active @endif">@lang('order.order_history')</a></li>
        <li><a href="{{ action('User\OrderController@pendingOrder') }}" class="@if(strpos( $_SERVER['REQUEST_URI'], 'user/order/pending-order' ) !== false && strpos( $_SERVER['REQUEST_URI'], 'delivery-list' )== false) active @endif">@lang('order.pending_order')</a></li>
        <li class="d-none"><a href="{{ action('User\OrderController@deliveryList') }}" class="@if(strpos( $_SERVER['REQUEST_URI'], 'delivery-list' ) !== false) active @endif">@lang('order.order_waiting_for_delivery')</a></li>
        <li><a href="{{action('User\WishlistController@index')}}" class="@if(strpos( $_SERVER['REQUEST_URI'], 'wishlist' ) !== false) active @endif">@lang('user.wishlist')</a></li>
        <li><a href="{{action('User\UserController@favoriteShop')}}" class="@if(strpos( $_SERVER['REQUEST_URI'], 'favorite-shopes' ) !== false) active @endif">@lang('shop.favorit_shop')</a></li>
        <li><a href="{{action('User\UserController@index')}}" class="@if(strpos( $_SERVER['REQUEST_URI'], 'profile' ) !== false) active @endif">@lang('customer.profile_setting')</a></li>
        <li><a href="{{action('User\UserController@show')}}" class="@if(strpos( $_SERVER['REQUEST_URI'], 'address' ) !== false) active @endif">@lang('customer.address')</a></li>
        <li><a href="{{action('User\ReviewController@index')}}" class="@if(strpos( $_SERVER['REQUEST_URI'], 'review' ) !== false) active @endif">@lang('customer.user_review')</a></li>
        <li><a href="{{action('User\ODDController@oddCondition')}}" class="@if(strpos( $_SERVER['REQUEST_URI'], 'register-odd' ) !== false) active @endif">@lang('customer.register_odd')</a></li>
    </ul>
    <div class="title-bg-red d-none"><span>@lang('shop.credit')</span></div>
    <ul class="box-grey d-none">
        <li><a href="{{action('User\CreditController@creditBalance')}}" class="@if(strpos( $_SERVER['REQUEST_URI'], 'credit-balence' ) !== false) active @endif">@lang('shop.credit_balance')</a></li>
        <li><a href="{{action('User\CreditController@creditUsage')}}" class="@if(strpos( $_SERVER['REQUEST_URI'], 'credit-usage' ) !== false) active @endif">@lang('shop.credit_usage')</a></li>
        <li><a href="{{action('User\CreditController@index')}}" class="@if(strpos( $_SERVER['REQUEST_URI'], 'credit-requets' ) !== false) active @endif">@lang('shop.credit_requests')</a></li>
    </ul>                   
</div>
