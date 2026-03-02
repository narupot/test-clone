<?php
  $setting_json_path = themeUrl('json', 'header_setting.json');
  $setting_json = json_decode(file_get_contents($setting_json_path), true); 
  $setting_json = $setting_json['header'];
  //echo '<pre>';print_r($setting_json);  
?>
<header id="header" >
   <div class="first_row" id="sticky-row">
      <div class="container">
         <div class="position-group eposition">
            <div class="first_col1">
                <div class="hamburger">
                    <div class="hamburger-box">
                        <div class="hamburger-inner"></div>
                    </div>
                </div>
                <!-- in case theme customization set logo visibility is visible-->
                @if(isset($setting_json['logo']) && $setting_json['logo']['visibility'] == 'visible')
                    <div class="logo">
                        <a href="{{session('lang_code')}}/"><img src="{{getThemeSiteLogo($setting_json['logo']['path'])}}" alt="Logo"></a> 
                    </div>
                @endif
            </div>
            <div class="second_col2">
                @if(isset($setting_json['menu_links']['menu_links']['id']))   
                    {!!CustomHelpers::getSetMenu($setting_json['menu_links']['menu_links']['id'])!!} 
                @endif 
               {{--@if(isset($setting_json['static_menu']['static_menu']['id']))   
                    {!!CustomHelpers::getSetMenu($setting_json['static_menu']['static_menu']['id'])!!} 
                @endif--}} 
            </div>
            <div class="third_col3">
               <ul class="login-link">
                    @php
                     $currency_data = getCurrencySwitcherData();
                     $language_data = getLanguageSwitcherData();
                    @endphp
                    <!-- in case theme customization set language & currency as a dropdown-->
                    @if(isset($setting_json['language_currency']) && $setting_json['language_currency']['language_currency'] == 'language_currency_dropdown')
                        <li class="currency dropdown line">
                           <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown"><span class="icons-left bg">{{session('default_currency_symbol')}}</span> {{session('default_currency_code')}}</a>
                           <div class="dropdown-menu">
                           @foreach ($currency_data['currency'] as $value)
                              <a  href="javascript:void(0);" class="dropdown-item" onClick="switchCurrency({{$value->id.', "'.$currency_data['ajax_url'].'"'}})"><span class="icons-left bg">{{$value->currency_symbol}}</span> {{$value->currency_code}}</a>
                           @endforeach
                           </div>
                        </li>
                        <li class="language dropdown">
                           <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown"><span class="img-left"><img src="{{$language_data['cur_lang_img_url']}}"></span> {{session('lang_code')}}</a>
                           <div class="dropdown-menu">
                            @foreach($language_data['languages'] as $value)
                              @php
                                 $lang_url = Config::get('constants.public_url').$value->languageCode.'/'.$language_data['cur_url'];
                              @endphp
                              <a href="javascript:void(0);" class="dropdown-item" onClick='switchLanguage({{$value->id.', "'.$value->languageCode.'", "'.$lang_url.'", "'.$language_data['ajax_url'].'"'}})'><span class="img-left"><img src="{{Config::get('constants.language_url').$value->languageFlag}}"></span> {{$value->languageCode}}</a>
                            @endforeach
                           </div>
                        </li>                    
                    <!-- in case theme customization set language & currency as a language_currency_inline-->
                    @elseif(isset($setting_json['language_currency']) && $setting_json['language_currency']['language_currency'] == 'language_currency_inline')
                        <li class="language currency">
                            <div class="currency inline">
                                @foreach ($currency_data['currency'] as $value)
                                  <a  href="javascript:void(0);" class="inline-item line" onClick="switchCurrency({{$value->id.', "'.$currency_data['ajax_url'].'"'}})"><span class="icons-left bg">{{$value->currency_symbol}}</span> {{$value->currency_code}}</a>
                                @endforeach
                            </div>
                            <div class="language inline">
                                @foreach($language_data['languages'] as $value)
                                  @php
                                     $lang_url = Config::get('constants.public_url').$value->languageCode.'/'.$language_data['cur_url'];
                                  @endphp
                                  <a href="javascript:void(0);" class="inline-item line" onClick='switchLanguage({{$value->id.', "'.$value->languageCode.'", "'.$lang_url.'", "'.$language_data['ajax_url'].'"'}})'><span class="img-left"><img src="{{Config::get('constants.language_url').$value->languageFlag}}"></span> {{$value->languageCode}}</a>
                                @endforeach
                            </div>
                        </li>
                    @endif 
                    <li class="search"><a href="javascript:void(0);" data-toggle="modal" data-target="#searchModal" class="search-icon"><i class="fas fa-search"></i></a></li>
                    <!-- in case theme customization set checkout_cart as a cart_item-->
                    @if(isset($setting_json['cart']) && $setting_json['cart']['checkout_cart'] == 'cart_item')
                        <li class="cart">
                            <a href="{{action('Checkout\CartController@shoppingCart')}}"><i class="fas fa-shopping-cart"></i></a>
                            <span class="countcart" id="totalCartProduct">{{getCartProduct()}}</span>
                        </li>
                    @elseif(isset($setting_json['cart']) && $setting_json['cart']['checkout_cart'] == 'cart_item_price')
                        <li class="cart">
                            <a href="{{action('Checkout\CartController@shoppingCart')}}">
                                <i class="fas fa-shopping-cart"></i></a>
                            <span class="countcart" id="totalCartProduct">{{getCartProduct()}}</span>
                            <span class="cart-price" id="totalCartPrice">
                            {{session('default_currency_symbol').getCartPrice()}}</span>
                        </li>
                    @endif

                    <li class="dropdown">
                        <a href="javascript:void(0);" class="" data-toggle="dropdown"><i class="fas align-middle fa-user-tie"></i></a>
                        <div class="dropdown-menu">
                        @if(Auth::check())
                            <a class="dropdown-item" href="{{action('Auth\LogoutController@logout')}}">@lang('auth.logout')</a>
                            @if(session('order_by_ref')=='admin')
                               <a class="dropdown-item" href="{{action('User\Order\UserOrderRmaController@index')}}">@lang('order.rma')</a>
                            @else
                               <a class="dropdown-item" href="{{action('User\UserController@index')}}">@lang('customer.my_account')</a>
                               <a class="dropdown-item" href="{{action('User\UserController@show')}}">@lang('customer.address_book')</a>
                               <a class="dropdown-item" href="{{action('User\WishlistController@index')}}">@lang('customer.my_wishlist')</a>                           
                            @endif
                        @elseif(isset($page) && ($page=='login' || $page=='register'))
                            <a class="dropdown-item" href="{{action('Auth\RegisterController@login')}}">@lang('auth.login')</a>
                            <a class="dropdown-item" href="{{action('Auth\RegisterController@index')}}">@lang('auth.signup')</a>
                        @else
                            <a class="dropdown-item" href="javascript:void(0);" data-toggle="modal" data-target="#loginModal">@lang('auth.login')</a>
                            <a class="dropdown-item" href="javascript:void(0);" id="link_reg">@lang('auth.signup')</a>                                        
                        @endif
                            <a class="dropdown-item" href="{{action('CompareProduct@compareProduct')}}">@lang('product.compare_product')</a>
                            <a class="dropdown-item" href="{{action('User\Order\UserOrderController@track')}}">@lang('order.track_order')</a>
                        </div>
                    </li>
                </ul>
            </div>
         </div>
      </div>
   </div>
</header>
<div id="searchModal" class="modal fade" role="dialog">
    <div class="modal-dialog search-header">
      <div class="modal-content no-bg">
         <div class="search-inner-header">
            <span class="close-search fal fa-times" data-dismiss="modal"></span>                 
            <div class="autosearch-wrap autocomplete">
            {!! Form::open(['action' =>'ProductsController@search', 'id'=>'searchForm', 'method'=>'get']) !!}
                <input type="text" placeholder="@lang('common.search')" name="search" id="searchProduct"  value="{{ isset($search)?$search:''}}" autofocus tabindex="1">
                @if ($errors->has('search'))
                    <p class="error">{{ $errors->first('search') }}</p>
                @endif
                <button type="submit" id="search-btn">
                    <i class="fa fa-search" aria-hidden="true"></i>
                </button>
            {!! Form::close() !!}
            </div>
         </div>
      </div>
    </div>
</div>
@if(Auth::guest() && (!isset($page) || ($page!='login' && $page!='register')))

    @include('includes.login_register_popup')
   
@endif
<script type="text/javascript">

</script>