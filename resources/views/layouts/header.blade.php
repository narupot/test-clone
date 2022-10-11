<header id="header">
    <div class="container">
        <div class="header-inner">
            <div class="logo">
                <a href="{{ action('HomeController@index') }}" title="Smmarket"><img src="{{ getSiteLogo('SITE_LOGO_HEADER') }}" width="85" alt=""></a>
            </div>
            <div class="header-content">
                <div class="login-link">
                    <ul>
                        @if(Auth::check())
                            <li class="user dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <img src="{{ getUserImageUrl(Auth::user()->image) }}" alt="">{{ Auth::user()->display_name }}</a>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{action('User\UserController@index')}}"><i class="fas fa-user"></i> @lang('customer.my_account')</a>
                                    <a class="dropdown-item" href="user/order/history"><!-- <i class="fas fa-user"></i> <--><img src="images/order.png" alt="img" height="18" class="rounded-0"></--> @lang('customer.order_history')</a>
                                    <!-- <a class="dropdown-item" href="#"><i class="fas fa-credit-card"></i> @lang('customer.my_credit')</a> -->
                                    <a class="dropdown-item d-none" href="{{action('Checkout\TrackOrderController@trackOrderDetail')}}"><i class="fas fa-truck"></i> @lang('customer.product_tracking')</a>

                                    @if(Auth::user()->user_type == 'seller')
                                    <a href="{{ action('Seller\ProductController@sellerProduct') }}/" class="dropdown-item @if(strpos( $_SERVER['REQUEST_URI'], 'seller' ) !== false) active @endif" ><i class="far fa-home"></i> @lang('customer.for_seller') </a>
                                    @endif
                                    

                                    @if(Auth::user()->user_type != 'seller')
                                    <a href="{{ action('Auth\SellerRegisterController@index',Auth::user()->id) }}" class="dropdown-item @if(isset($page)&&$page=='buyer' || strpos( $_SERVER['REQUEST_URI'], 'seller-register/' ) !== false) active @endif"><i class="fas fa-user"></i> @lang('customer.seller_register') </a>
                                    @endif

                                </div>
                            </li>
                            <li><a href="{{ action('Auth\LogoutController@logout') }}"> <i class="fas fa-sign-in-alt"></i> @lang('common.sign_out')</a></li>
                            <!-- <li><a href="#"> <i class="fas fa-comments"></i> @lang('common.chat')</a></li> -->
                        @else
                            <li><a href="{{ action('Auth\RegisterController@index') }}">@lang('auth.signup')</a></li>

                            @if(isset($page) && ($page=='login' || $page=='register'))
                                <li><a href="{{action('Auth\RegisterController@login')}}"> <i class="fas fa-sign-in-alt"></i> @lang('auth.login')</a></li>
                            @else
                                <li><a href="javascript:void(0);" data-toggle="modal" data-target="#loginModal"> <i class="fas fa-sign-in-alt"></i> @lang('auth.login')</a></li>
                            @endif
                        @endif                       
                        
                    </ul>
                </div>
                <div class="search-cart-wrap">

                    @if(Auth::check())
                    <div class="head-col buyer-seller-col">
                        <div class="btn-buyer-header">
                            @if(Auth::user()->user_type == 'seller')
                                <!-- <a href="{{ action('Seller\ProductController@sellerProduct') }}/" class="btn-grey @if(strpos( $_SERVER['REQUEST_URI'], 'seller' ) !== false) active @endif" ><i class="far fa-home"></i> @lang('customer.for_seller') </a> -->

                                {{-- <a href="{{ action('HomeController@index') }}" class="btn-grey @if(strpos( $_SERVER['REQUEST_URI'], 'seller' ) == false) active @endif" style="border-radius: 10px;"><i class="fas fa-user"></i> @lang('customer.for_buyer') <i class="badge d-none">3</i></a> --}}
                            @else
                                {{-- <a href="{{action('User\UserController@index')}}" class="btn-grey @if(isset($page)&&$page=='buyer' || strpos( $_SERVER['REQUEST_URI'], 'user/' ) !== false) active @endif" style="border-radius: 10px;"><i class="fas fa-user"></i> @lang('customer.for_buyer') </a> --}}
                                <!-- <a href="{{ action('Auth\SellerRegisterController@index',Auth::user()->id) }}" class="btn-grey @if(isset($page)&&$page=='buyer' || strpos( $_SERVER['REQUEST_URI'], 'seller-register/' ) !== false) active @endif"><i class="fas fa-user"></i> @lang('customer.seller_register') </a> -->
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="head-col search-form">
                        <form action="{{action('ProductsController@search')}}" method="GET" id="searchForm" accept-charset="UTF-8">
                            <div class="search-group">
                                <input type="hidden" name="searchtype" value="all" class="searchtype">
                                <!--div class="nav-search-select">
                                    <div class="nav-search-selected">
                                        <span class="nav-search-text">@lang('product.product')</span> 
                                        <i class="fas fa-angle-down"></i>
                                    </div>
                                    <select name="searchtype" id="searchtype">
                                        <option value="product" selected="selected"> @lang('product.product')</option>
                                        <option value="shop"> @lang('shop.shop')</option>
                                    </select>
                                </div-->
                                <div class="nav-search-input">
                                    <input type="text" placeholder="Search" id="searchProduct" name="search">
                                    <i class="fas fa-search"></i>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- <div class="autosearch-wrap autocomplete">
                        <form method="GET" action="{{action('ProductsController@autosearch')}}" accept-charset="UTF-8" id="searchForm">
                            <input type="text" placeholder="Search" name="search" id="searchProduct" value="" autofocus="" tabindex="1" class="ui-autocomplete-input" autocomplete="off">
                                            <button type="submit" id="search-btn">
                                <i class="fa fa-search" aria-hidden="true"></i>
                            </button>
                        </form>
                    </div> -->


                    @if(Auth::check())
                        @php $tot_cart_prd_noti = getCartProduct(); @endphp
                        <div class="head-col buyer-col chatWrap @if(strpos( $_SERVER['REQUEST_URI'], 'seller' ) !== false) mobhide @endif">
                            <div class="buyer-pannel">
                                <a href="#" class="btn-grey dropdown-toggle" data-flip="false" data-toggle="dropdown"><i class="badge" style="display: {{ $tot_cart_prd_noti['tot']>0?'inline-block':'none' }};" id="tot_cart_noti">{{ $tot_cart_prd_noti['tot']}}</i> <!-- <i class="fas fa-shopping-basket"></i> --> <img src="images/acoount-icon.png" height="22" alt="img"> @lang('checkout.buyer_panel')</a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="{{ action('Checkout\CartController@shoppingCart') }}"><i class="fas fa-shopping-basket"></i> @lang('checkout.product_wait_for_payment') <i class="badge" id="tot_prd_noti" style="display: {{ $tot_cart_prd_noti['cart_prd']>0?'inline-block':'none' }};">{{ $tot_cart_prd_noti['cart_prd'] }}</i></a>

                                    {{-- <a class="dropdown-item" href="{{action('User\ShoppinglistController@index')}}"><i class="fas fa-pen-square"></i> +@lang('checkout.create_shopping_list') </a> --}}

                                    <a class="dropdown-item" href="{{action('User\BargainController@index','bytime')}}"><i class="fas fa-credit-card"></i> @lang('checkout.product_bargaining') <i class="badge" style="display: {{ $tot_cart_prd_noti['bargain_prd']>0?'inline-block':'none' }};" id="tot_bar_noti">{{ $tot_cart_prd_noti['bargain_prd'] }}</i></a>
                                    
                                    {{-- <a class="dropdown-item" href="{{ action('Checkout\CartController@alreadyPaid') }}"><i class="fas fa-truck"></i> @lang('checkout.product_wait_for_end_shopping') <i class="badge" id="tot_paid_noti" style="display: {{ $tot_cart_prd_noti['paid_prd']>0?'inline-block':'none' }};">{{ $tot_cart_prd_noti['paid_prd'] }}</i></a> --}}
                                </div>
                            </div>
                            
                            <span class="chat-wrap btn-buyer-chat" data-val="">
                                <a href="javascript:void(0);"><i class="fas fa-comments mr-2"></i>@lang('product.talk_to_shop_home')</a>
                            </span>
                            
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
    </div>
    
    @if(checkPageSection() != 'seller')

    @php 
        $parent_cat_data = \App\MongoCategory::getParentCategories(); 
        
    @endphp
    <!-- Menu start -->
    <div class="menu-wrap">
        <div class="container">
            <div class="header-menu">
                <span class="menu-icon"><i class="fas fa-bars"></i> <span>@lang('product.product_group')</span></span>
                {!!CustomHelpers::getSetMenu(2, 'static-menu m-menu')!!}          
            </div>
            <!-- <div class="header-menu">
                <span class="menu-icon"><i class="fas fa-bars"></i></span>
                <ul class="main-menu">
                    @foreach($parent_cat_data as $unit_cat_data)
                        <li><a href="/category/{{$unit_cat_data->url}}" title="Import Fruit">{{ $unit_cat_data->category_name ?? ''}}</a></li>
                    @endforeach
                </ul>
                <div class="menu-link">
                    <ul>
                        <li><a href="#">Price Hostory</a></li>
                        <li><a href="#">Price Hostory</a></li>
                    </ul>
                </div>
            </div> -->
        </div>
    </div>
    <!-- Menu start -->
    @endif

    <script src="{{Config::get('constants.js_url').'jquery-ui.min.js'}}"></script>
    
    <!-- <script src="http://code.jquery.com/ui/1.10.2/jquery-ui.js" ></script> -->
    <script type="text/javascript">
    $(document).ready(function() {
       var availableTags = "{{action('ProductsController@autosearch')}}";
       var searchUrl = "{{action('ProductsController@search')}}?search=";   
       var stype = 'product';
       //Listen on hightliter
       function highlighter (item) {
           var word = $('#searchProduct').val();
           var html = item;
            if ($.trim(word)) {
                   word = word.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&');
                   if ("strong".indexOf(word.toLowerCase()) == -1) {
                       html = html.replace(new RegExp('(' + word + '(?![^<>]*>))', 'ig'), function ($1, match) {
                           return '<strong>' + match + '</strong>';
                       });
                   }
            }
            return html;                    
       };
        
        $('#searchtype').on('change', function() {
            $('#searchtype option')
              .removeAttr('selected')
              .filter('[value='+this.value+']')
              .attr('selected', true);
             //alert($('#searchtype').val());

            try{
              $("form#searchForm #searchProduct").autocomplete("destroy");
            }catch(errr){}
        });

        function resetAutocomplete(){   
          /*if($('#searchtype').val()=='product')
            var searchUrl = "{{action('ProductsController@search')}}?search=";  
          else
            var searchUrl = "{{url('/')}}/shop?search="; */

          var searchUrl = "{{action('ProductsController@search')}}?search="; 
          
          $("form#searchForm #searchProduct").autocomplete({
            source: availableTags+'?searchtype='+$('.searchtype').val(),
            autoFocus: false,
            delay: 100,
            select: function( event, ui ) {
              var searchtext = $('#searchProduct').val();
              $("form#searchForm #searchProduct").val(searchtext);
              $('form#searchForm').submit();
            }, 
            classes: {
              "ui-autocomplete": "search-dropdown"
            },
            open: function(event, ui) {
                var searchtext =  $('#searchProduct').val();
                $('.ui-autocomplete').append('<div class="search-result"><a href="'+searchUrl+searchtext+'">All search results <i>→</i></a></div>');
                $('.product_0').before('<li class="head-wap"><h3 class="search-head">Products</h3></li>');
                $('.shop_0').before('<li class="head-wap-shop"><h3 class="search-head">Shop</h3></li>');
            }
          }).autocomplete().data("uiAutocomplete")._renderItem = function(ul, item) {          
               console.log(item);

              //var names = highlighter(item.value);
              //var item = results.shop;
              var names = item.value;
              var minus = item.minus;
              var specialhtml = '';

              if(minus > 0){
                specialhtml='<span class="strike-price">'+item.price+'</span><span class="normal-price"> '+item.special_price+'</span>';
              }else{
                specialhtml='<span class="normal-price">'+item.price+'</span>';
              }
              if(typeof item.type != "undefined" && item.type == "shop"){
                var beforeHtml = '';
                if(item.i > 0 && item.j==0){
                   //beforeHtml = '</ul><ul><li class="head-wap"><h3 class="search-head">Shop</h3></li>';
                }
                var html = $(beforeHtml+"<li class='product-wrap "+item.type+"_"+item.j+"'>").append('<a href="'+item.url+'"><div class="search-img"><img src="'+item.image+'" width="60"></div><div class="search-prod-desc clearfix"><span class="name link-product-name">'+names+'</span> <div class="price-wrap">'+specialhtml+'</div></div></a>').appendTo(ul);
              }else{
                var html = $("<li class='shop-wrap "+item.type+"_"+item.i+"'>").append('<a href="'+item.url+'"><div class="search-img"><img src="'+item.image+'" width="60"></div><div class="search-prod-desc clearfix"><span class="name link-product-name">'+names+'</span> <div class="price-wrap">'+specialhtml+'</div><div class="inner-info">'+item.shop_name+'</div></div></a>').appendTo(ul);  
              }
              
              //var html = $("<li>").append('<a href="'+item.url+'"><div class="search-img"><img src="'+item.image+'" width="60"></div><div class="search-prod-desc clearfix"><span class="name d-block link-product-name">'+names+'</span> <div class="price-wrap">'+specialhtml+'</div></div></a>').appendTo(ul);  
              return html;
          };
          
        }

        // $('#searchProduct').keypress(function(){
        //     resetAutocomplete();
        // }); 
        $("#searchProduct").keypress(function(){  
            resetAutocomplete();
        });   

    });

        $(document).ready(function(){
            /*  Header select */
            jQuery('.nav-search-select select').on('change' , function(){
                //var optionSelected = jQuery(".search-selectitem option:selected").text();
                var optionSelected = jQuery(this).find("option:selected").text();

                
                jQuery('.nav-search-selected .nav-search-text').text(optionSelected);
            });


            
            jQuery("#searchForm" ).submit(function( event ) {
                /*if($('#searchtype').val()=='product')
                  var searchUrl = "{{action('ProductsController@search')}}?search=";  
                else
                  var searchUrl = "{{url('/')}}/shop?search=";  */

                var searchUrl = "{{action('ProductsController@search')}}?search=";

                $('#searchForm').attr('action', searchUrl);
            }); 
        })

        jQuery(document).ready(function(){
            jQuery('#searchProduct').on('keyup',function(e) {
                this.value = this.value.replace(/[0-9]/g, "");                
            });
        })

        

    </script>
</header>

@if(Auth::guest() && (!isset($page) || ($page!='login' && $page!='register')))
    @include('includes.login_register_popup')
@endif

