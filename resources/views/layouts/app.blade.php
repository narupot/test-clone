@php($section_data = GeneralFunctions::sectionData(basename(request()->path())))  
@php($embeded_data = GeneralFunctions::embededCssJs(basename(request()->path())))
@php(extract($section_data))

@php(extract($embeded_data))
@php($class = pageClass($left,$right))
@php($version_cssjs = getConfigValue('CSS_JS_VERSION'))

<!DOCTYPE html>
<html lang="{{session('lang_code')}}">
    <head>       
        <meta name="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <base href="{{ asset('/') }}" />
        @if(isset($page) && ($page == 'products'))
           {!! SEO::FetchSeoTags($page, $productDetail) !!}
        @elseif(isset($page) && $page == 'blogDetails')
           {!! SEO::FetchSeoTags($page, $page_dtls) !!} 
        @elseif(isset($page) && $page == 'category')
           {!! SEO::FetchSeoTags($page, $result) !!} 
        @elseif(isset($pageName) && $pageName == 'categoryBlogList')
           {!! SEO::FetchSeoTags($pageName, $cateData) !!}      
        @else

          {!! SEO::FetchSeoTags() !!}
        @endif
        <link rel="shortcut icon" href="{{ getSiteLogo('SITE_FEVICON_ICON') }}" type="image/x-icon">        
        <!-- Styles -->
        {!! CustomHelpers::getCommonCss() !!}
        <link href="https://fonts.googleapis.com/css?family=Kanit:200,300,400,500,600,700" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">
        
        <link rel="stylesheet" type="text/css" href="{{Config::get('constants.css_url').'jquery-ui.css'}}" />       
        @if(Auth::guest())
            {!! CustomHelpers::combineCssJs(['css/login'],'css') !!}
        @endif
        @yield('header_style')
        <!--styles ended-->
        <!----Start Getting embeded css--->
        @if(isset($embeded_css) && count($embeded_css))
            @foreach($embeded_css as $cssval)
                <link rel="stylesheet" type="text/css" href="{{$cssval}}?ver={{ $version_cssjs}}">
            @endforeach
        @endif
        <link rel="stylesheet" type="text/css" href="{{ Config('constants.css_url') }}admin_custom_style.css?ver={{ $version_cssjs}}">
        <!----End Getting embeded css--->

        <!--header js-->
        {!! CustomHelpers::getCommonJs() !!}        
        <script>
            window.Laravel = <?php echo json_encode(['csrfToken' => csrf_token()]);?>

            var siteUrl = "{{url('/')}}/{{ session('lang_code')}}";
            var home = "{{ action('HomeController@index') }}";
            @if(Auth::guest())
                var request_otp_url = "{{ action('Auth\RegisterController@requestOtp') }}";
                var confirm_otp_url = "{{ action('Auth\RegisterController@confirmOtp') }}";
                var resend_email_url = "{{ action('Auth\RegisterController@resendverificationlink') }}";
                var txt_verify = "@lang('auth.click_here_to_verify_account')";
                var txt_verify_success = "@lang('auth.account_verification')";
            @endif
            var lang_success = "@lang('common.success')";
            var lang_oops = "@lang('common.oops')";
            var lang_ok = "@lang('common.ok')";
            var lang_cancel = "@lang('common.cancel')";
            var lang_yes = "@lang('common.yes')";
            var mobile_login = "{{action('HomeController@mobileLogin')}}";
            var chat_token_url = "{{action('HomeController@mobileLogin')}}";
            @yield('header_script')
        </script> 
        <!--header js ended-->
        {!! SEO::googleTagManagerHead() !!}
    </head>
    <body>
        {!! SEO::googleTagManagerBody() !!}
        <div class="wrapper" ng-app="smm-app">
            <div class="loader-wrapper" id="showHideLoader" style="display: none">
                <span class="loader">
                    <img src="{{getSiteLoader('SITE_LOADER_IMAGE')}}" alt="Loader"> 
                </span>
            </div>
           
            @if(count($header_content))
                @include('layouts.header')
            @endif

            <div class="content-wrap {{ $class['sideClass'] }} @if(isset($page_class)) {{ $page_class }} @endif">

                @yield('breadcrumbs')
                @if(checkPageSection() == 'seller')
                    @include('includes.seller_top_panel')
                @endif
                @if(count($left_content) || count($right_content)) <div class="container"> <div class="row"> @endif

                @if(count($left_content))
                    <aside class="left-sidebar col-md-3">
                        <div class="sidebar-inner">
                    @foreach($left_content as $key => $value)

                        @if($value->block_url_key == 'my-account-left-menu') 

                            @include('includes.myAccountLeftMenu')

                        @elseif(isset($value->banner_type) && ($value->banner_type == 'banner') || ($value->banner_type == 'slider'))

                            <div class="banner {{ ($value->banner_type == 'slider')?'slider':'' }}">
                                @foreach($value->slider as $skey => $slider_val)
                                    <div class="banner-img">
                                        <a href="{{ $slider_val->banner_url }}" target="{{ $slider_val->url_target }}"><img src="{{ Config::get('constants.banner_url').$slider_val->banner_image }}" alt="{{ !empty($slider_val->bannerdesc)?$slider_val->bannerdesc->banner_title:'' }}"></a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="fr-element">{!! $value->static_desc !!}</div>
                        @endif
                    @endforeach

                    @yield('left')
                        </div>
                    </aside>
                @endif

                <div class="{{ $class['main'] }}" >
                @if(count($main_content))
                    @foreach($main_content as $key => $value)
                        @if($value->block_url_key == 'content')
                            @if(empty($left_content) && empty($right_content))<div class="container">@endif
                                @yield('content')
                            @if(empty($left_content) && empty($right_content))</div>@endif
                        @elseif(isset($value->banner_type) && ($value->banner_type == 'banner') || ($value->banner_type == 'slider'))
                            @if($value->banner_type == 'slider' && $value->full_width=='true' )
                                <div class="banner slider full-banner {{ ($value->group_loop=='true')?'slider_autoloop':'' }}">
                                    @foreach($value->slider as $skey => $slider_val)
                                        
                                        <div class="slide slide--{{ ++$skey }}" style="background-image: url({{ Config::get('constants.banner_url').$slider_val->banner_image }});">
                                            @if($slider_val->banner_url)<a href="{{ $slider_val->banner_url }}" target="{{ $slider_val->url_target }}"></a>@endif
                                        </div>
                                        
                                    @endforeach
                                </div>
                            @else

                            <div class="container home-container full-banner">
                                <div class="banner banner-home {{ ($value->banner_type == 'slider')?'slider':'' }} {{ ($value->group_loop=='true')?'slider_autoloop':'' }}" data-slick='{"speed":{{$value->speed}}}'>
                                    @foreach($value->slider as $skey => $slider_val)
                                        <div class="banner-img banner-item">
                                            <a href="@if($slider_val->banner_url){{ $slider_val->banner_url }}@else javascript:;@endif" target="{{ $slider_val->url_target }}"><img src="{{ Config::get('constants.banner_url').$slider_val->banner_image }}" alt="{{ !empty($slider_val->bannerdesc)?$slider_val->bannerdesc->banner_title:'' }}"></a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        @elseif(isset($value->cms_slider))
                            <div class="container">
                                <div class="banner-slider"  style="margin-top: {{ $value->cms_slider->slider_option['cont_space_top']??'' }}px; margin-bottom: {{ $value->cms_slider->slider_option['cont_space_bottom']??'' }}px; padding-left: {{ $value->cms_slider->slider_option['thumb_space']??'' }}px; padding-right: {{ $value->cms_slider->slider_option['thumb_space']??'' }}px;">

                                  @if(isset($value->cms_slider->sliderdesc) && count($value->cms_slider->slider))
                                    <h3 class="title-bg">
                                        <span>
                                            {!! $value->cms_slider->sliderdesc->title ?? '' !!}
                                        </span>
                                    </h3>
                                  @endif
                                  <div class="row popular-products-contain">
                                    @if($value->cms_slider->design !='1' && $value->cms_slider->banner)
                                      <div class="bannerimg col-sm-{{ $value->cms_slider->design_val[2] }} @if($value->cms_slider->design_val[1]=='right') order-last @endif">
                                        <div class="banner-innerImg">

                                            <a href="{{ $value->cms_slider->banner_url ?? 'javascript:;' }}"><img data-original="{{ $value->cms_slider->banner }}" src="{{ Config('constants.image_url')}}/loading.gif" height="" alt=""></a>

                                        </div>                                            
                                      </div>
                                    @endif

                                    <div class="slider-sg @if($value->cms_slider->design == 1 && $value->cms_slider->container_width>0) col-sm-{{ $value->cms_slider->container_width }} @else col-sm-{{ $value->cms_slider->design_val[3] ?? ''}} @endif">
                                    @if($value->cms_slider->type == 'product' && !empty($value->cms_slider->slider))
                                        
                                    <ul id="scroll-1" class="products product-grid-view  @if($value->cms_slider->show_slider=='yes')  @if($value->cms_slider->design_val[0]=='one') banner-onerow-slider MagicScroll @else banner-tworow-slider @endif @else product-item-wrappers @endif" data-options="{{ $value->cms_slider->slider_option['item_per_slider'] }};{{ $value->cms_slider->slider_option['setting_slider'] }};height:auto; arrows:true; mode:animation;arrows:outside;lazyLoad: true;">
                                        
                                      @foreach($value->cms_slider->slider as $skey => $result)
                                        <li class="item-box" style="padding-left: {{ $value->cms_slider->slider_option['thumb_space']??'' }}px; padding-right: {{ $value->cms_slider->slider_option['thumb_space']??'' }}px;">
                                            <div class="product-item-info">
                                                <div class="prod-img">
                                                  <a href="{{ $result['cat_url'] }}">
                                                    <img src="{{ $result['cat_img'] }}" />
                                                  </a>  
                                                 
                                                </div>                                                    
                                                <div class="product-info">
                                                    
                                                    <div class="d-block"><a href="{{ $result['cat_url']}}" class="link-product-name">{{ $result['cat_name'] }}</a></div> 
                                                    <div class="prod-desc"><img src="{{ $result['badge_img'] }}" /></div>  
                                                    
                                                    <div class="price-wrap">
                                                    	<span class="price-label"> ราคาปัจจุบัน </span>
                                                        <span class="normal-price">
                                                        {{$result['weight_per_unit']}} {{$result['base_unit']}} / {{$result['package_name']}}<br> {{ round($result['unit_price'],2)}} บาท  
                                                        </span>  
                                                                                                                              
                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </li>
                                      @endforeach
                                    </ul>

                                    @endif

                                    </div>
                                  </div>
                                </div>
                            </div>
                        @else
                            <div class="fr-element home-container">{!! $value->static_desc !!}</div>
                        @endif
                    @endforeach
                @endif   
                </div>

                @if(count($right_content))
                    <aside class="right-sidebar col-md-3">
                        @foreach($right_content as $key => $value)
                            @if($value->block_url_key == 'static-right-side')         

                            @elseif(isset($value->banner_type) && ($value->banner_type == 'banner') || ($value->banner_type == 'slider'))
                                <div class="banner {{ ($value->banner_type == 'slider')?'slider':'' }}">
                                    @foreach($value->slider as $skey => $slider_val)
                                        <div class="banner-img">
                                            <a href="{{ $slider_val->banner_url }}" target="{{ $slider_val->url_target }}"><img src="{{ Config::get('constants.banner_url').$slider_val->banner_image }}" alt="{{ !empty($slider_val->bannerdesc)?$slider_val->bannerdesc->banner_title:'' }}"></a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="fr-element">{!! $value->static_desc !!}</div>
                            @endif
                        @endforeach

                        @yield('right')
                    </aside>
                @endif

                @if(count($left_content) || count($right_content)) </div></div> @endif
            </div>

            @if(count($footer_content))
                @foreach($footer_content as $fkey => $fval)
                    @if($fval->is_fix !=1 || count($footer_content)==1)
                            @include('layouts.footer',['footer_block_desc'=>$fval->static_desc])
                        @break
                    @endif
                @endforeach
                
            @endif
            
            <script type="text/javascript">
                //hide page loader after page load
                /*jQuery(window).on("load", function(){
                    showHideLoader('hideLoader');
                }); */                             
            </script> 

                      
            <!-- begin page level js -->
            @yield('footer_scripts')

            <script>
                (function(){
                    if(jQuery('.slider div').length > 1){
                        jQuery('.slider').slick({
                             autoplay:true,
                             arrows:true,
                             dots : true,
                             //speed:500,
                        });
                    }   
                })(jQuery);
            </script>
            
            @if(Auth::guest())
                {!! CustomHelpers::combineCssJs(['js/login_register'],'js') !!}
            @endif


            @yield('footer_scripts_include')
            <script type="text/javascript" src="{{ Config::get('constants.js_url').'sgCustom.js' }}"></script> 
            <!----Getting embeded js--->
            @if(isset($embeded_js) && count($embeded_js))
                @foreach($embeded_js as $jsval)
                    <script type="text/javascript" src="{{$jsval}}?ver={{ $version_cssjs}}"></script>
                @endforeach
            @endif
            <!-- end page level js -->  
        </div>
        <div id="popupdiv"></div><!-- used for popup don`t delete it -->

        {!!LayoutHtml::getBargainPop()!!}

    </body>
</html>
