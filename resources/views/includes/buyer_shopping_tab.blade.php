@php $tot_cart_prd_noti = getCartProduct(); @endphp

{{-- <div class="end-shopping-wrap form-group">
    <div class="row align-items-center">
        <div class="col-sm-8"> --}}
            {{--<div class="product-purchase">@lang('checkout.purchased_products') <span>{{isset($purchased_products)?$purchased_products:''}}</span> @lang('checkout.item')<span> | <span></span> </div>--}}
            <!-- <div class="product-amount"> Paid <span>2000</span> Baht | Qty <span>10</span> Products </div> -->
            {{-- {{isset($list_of_shopping_list)?$list_of_shopping_list:''}}
            @lang('checkout.list_of_shopping_list') --}}
        {{-- </div>
        @if($tot_cart_prd_noti['cart_prd']>0) --}}
            {{-- <div class="col-sm-4 text-right">                                   
                    <a href="{{ route('buy-now-end-shopping') }}" class="btn-blue2">@lang('checkout.end_shopping')</a>                                  
            </div>  --}}
        {{-- @endif                             
    </div>
</div> --}}


<div class="seller-panneltab form-group">
    <ul class="nav" id="seller-Tab">
        <li>
            <a  href="{{ action('Checkout\CartController@shoppingCart') }}" @if(isset($page) && $page=='shopping_cart') class="active" @endif> 
                <span class="icon-list"><i class="fas fa-shopping-basket"></i></span>
                <span class="tab-name">@lang('checkout.shopping_cart')</span> 
                <span class="info-list" id="tot_cart_items" style="display: {{ $tot_cart_prd_noti['cart_prd']>0?'inline-block':'none' }};">{{ $tot_cart_prd_noti['cart_prd'] }}</span>
            </a>
        </li>
       
        <li>
            <a href="{{action('User\BargainController@index','bytime')}}" @if(isset($page) && $page=='bargain') class="active" @endif> 
                <span class="icon-list"><i class="far fa-usd-circle"></i></span>
                <span class="tab-name">@lang('checkout.product_bargain')</span>
                <span class="info-list" style="display: {{ $tot_cart_prd_noti['bargain_prd'] >0?'inline-block':'none' }};">{{ $tot_cart_prd_noti['bargain_prd'] }}</span>
            </a>
        </li>

        <li>
            <a href="{{action('User\ShoppinglistController@index')}}" @if(isset($page) && $page=='shopping_list') class="active" @endif> 
                <span class="icon-list"><i class="fas fa-lemon"></i></span>
                <span class="tab-name">@lang('shopping_list.shopping_list')</span>
                <!-- <span class="info-list"></span> -->
            </a>
        </li>
        
        {{--
        <li>
            <a href="{{ action('Checkout\CartController@alreadyPaid') }}" @if(isset($page) && $page=='already_paid') class="active" @endif> 
                <span class="icon-list"><i class="fas fa-clipboard-list"></i></span>
                <span class="tab-name">@lang('checkout.already_paid')</span>                                            
                <span class="info-list" style="display: {{ $tot_cart_prd_noti['paid_prd']>0?'inline-block':'none' }};">{{ $tot_cart_prd_noti['paid_prd'] }}</span>
            </a>
        </li>       
        --}}                        
    </ul>
</div>