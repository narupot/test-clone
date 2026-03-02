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


    {{-- <link rel="stylesheet" type="text/css" href="css/slick.min.css" />
    <link rel="stylesheet" type="text/css" href="css/slick-theme.min.css" /> --}}

    {!! SEO::FetchSeoTags() !!}
    @endif
    <link rel="shortcut icon" href="{{ getSiteLogo('SITE_FEVICON_ICON') }}" type="image/x-icon">
    <!-- Styles -->
    {!! CustomHelpers::getCommonCss() !!}

    <link rel="stylesheet" type="text/css" href="{{Config::get('constants.css_url').'jquery-ui.css'}}" />
    {{-- <link rel="stylesheet" type="text/css" href="css/global.css" /> --}}
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">

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





    <style>
        .lazy-bg {
            background-color: #f7f8fa;
            /* สีพื้นหลังก่อนโหลดรูป */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        /* ตัวบ่งชี้การโหลด (optional) */
        .lazy-bg::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            border: 3px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top-color: #ED1B24;
            animation: spin 1s ease-in-out infinite;
            display: none;
            /* สามารถเปิดแสดงเมื่อต้องการ */
        }


        .fixed-div {
            position: fixed !important;
            bottom: 0 !important;
            right: 1%;
            z-index: 3;
            display: block;
            /* Hidden by default */
        }

        .fixed-div img {
            display: block;
            vertical-align: top;
            margin: 5px auto;
            text-align: center;
            width: 120px;
        }

        .toggle-button {
            position: fixed;
            bottom: 100px;
            right: 10px;
            z-index: 4;
            padding: 5px;
            background-color: rgba(251, 249, 249, 0.93);
            border: none;
            cursor: pointer;
            color: red;
        }

        @keyframes spin {
            to {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }

        .small {
            font-size: 80%
        }

        .slick-arrow.slick-prev:before,
        .slick-arrow.slick-next:before,
        .MagicScroll-horizontal .mcs-button-arrow-prev,
        .MagicScroll-horizontal .mcs-button-arrow-next {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            padding-top: 10px;
            background-color: white;
            color: #555;
            font-weight: 100;
            box-shadow: 0px 0px 1px 1px #ccc;
            font-size: small
        }

        .slick-arrow.slick-prev:before {
            margin-left: -10px;
        }

        .slick-arrow.slick-next:before {
            margin-right: -10px;
        }

        .MagicScroll-horizontal .mcs-button-arrow-next:before,
        .MagicScroll-horizontal .mcs-button-arrow-prev:before {
            color: gray;
            border-color: gray;
        }

        .banner-home {
            margin-top: 0px;
        }

        .content-wrap {
            background-color: #F7F8FA;
            /* background-image: url(/images/bg-1.png);
                background-repeat: no-repeat;
                background-size: contain; */
        }

        .menu-wrap {
            margin-bottom: 0px
        }

        .banner-home .banner-item img {
            border-radius: 0px;
            width: 100%
        }

        .slick-dots li button {
            width: 10px;
            height: 10px;
        }

        .slick-track {
            padding: 0px;
        }

        .product-item-info {
            background: white;
            border-radius: 5px;
            padding: 5px;
            /* margin: 0px !important; */

        }

        .product-item-info .product-info {
            padding: 10px;
        }

        .product-slider .prod-img {
            position: relative;
            width: 100%;
            padding-top: 100%;
            /* 1:1 Ratio */
            overflow: hidden;
            border-radius: 5px;
            margin-bottom: 0px;
        }

        .product-slider .prod-img img,
        .product-slider .prod-img .prod-img-display {
            width: 100% !important;
        }


        .product-item-info .prod-desc {
            float: right;
            height: 30px;
            margin-top: -29px;
            margin-right: -2px;
            position: relative;
        }

        .product-item-info .prod-desc img {
            height: 100%;
            width: auto%;
        }

        .product-item-info .prod-img {
            padding-bottom: 0% !important;
            padding: 0 !important;
            position: inherit;
            margin-bottom: 0px
        }

        .product-item-info .link-product-name {
            font-weight: bold;
            width: 100% !important;
        }

        .product-item-info .price-wrap .price-label,
        .sold-label {
            color: gray !important;
            font-size: 12px !important;
            padding: 0px !important;
        }

        .product-item-info .shop-name a {
            color: gray;
            font-size: 10px
        }

        .slick-slide img {
            border-radius: 0px
        }

        .slick-slide .normal-price {
            font-size: 16px;
        }

        .slick-slide .slide-title {
            font-size: 32px;
            font-weight: 800;
        }

        .mcs-wrapper {
            width: 100%;
            left: 0px !important;
            right: 0px !important;
        }

        .content-wrap {
            min-height: 400px;
        }

        .link-product-name {
            display: -webkit-box !important;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: normal !important;
            line-height: 1.4em;
            max-height: calc(1.4em * 2);
            height: auto !important;
            /* <== ปรับตรงนี้ ถ้า fix 45px อาจตัดข้อความผิดจังหวะ */
            min-height: calc(1.4em * 2);
        }

        .pagination .page-link {
            min-width: 50px;
        }

        .pagination li {
            margin: inherit;
        }

        .pagination .page-item:last-child .page-link,
        .pagination .page-item:first-child .page-link,
        .pagination .page-link {
            font-size: inherit;
            border-radius: inherit;
            border: 0.5px solid #dee2e6;
            padding: 0.5rem 0.75rem;
            color: #ED1B24;
            font-weight: 100;
            text-decoration: none;
        }

        .pagination .page-item.active .page-link,
        .pagination .page-item:hover .page-link {
            background-color: #FFE1E3;
            border: 0.5px solid #dee2e6;
            text-decoration: none;
        }

        .grid-bdr li:after {
            background-color: none;
        }

        .product-slider .item-box,
        .product-cate-slider .item-box {
            display: none;
        }

        .product-item-info .product-info {
            padding: inherit
        }

        .slick-slider .slick-track,
        .slick-slider .slick-list {
            margin-left: inherit;
        }

        .product-cate-slider.shop .product-item-info .prod-img-display {
            border-radius: 50%;
            width: 80%;
            height: 80%;

        }

        .product-cate-slider .product-item-info .link-product-name {
            font-size: 10px;
        }

        .product-cate-slider .product-info {
            padding-left: 0px;
            padding-right: 0px;
        }

        .spiner .decrease:hover,
        .spiner .increase:hover {
            color: #ED1B24;
        }

        .text-link {
            text-decoration: underline;
        }

        /* .dropdown-menu{ padding-left: 10px} */

        .seller-panneltab li a:hover,
        .seller-panneltab li a.active {
            background-color: #ED1B24
        }

        .seller-panneltab ul {
            border-color: #ED1B24
        }

        .seller-panneltab li a {
            background-color: #ed1b2414;
        }

        .ship-method-list:hover,
        .ship-method-list.active {
            background-color: #ED1B24
        }

        .checkout-order-table .table .product-shop .shopname {
            color: #ED1B24;
        }

        .button-switch-sm .switch:checked:before,
        .button-switch .switch:checked:before {
            background-color: green;
        }

        .button-switch-sm .switch:before,
        .button-switch .switch:before {
            background-color: #ED1B24;
        }

        .addto-link {
            top: 0%;
            right: 0%;
            z-index: 1;
            visibility: visible;
            opacity: 1;
        }

        @media (max-width: 1305px) {

            #header .static-menu li {
                font-size: small;
            }
        }

        @media (max-width: 1199.98px) {

            .slick-arrow.slick-next:before {
                right: 20px;
            }
            .banner-home .banner-item img{
                border-radius: 0px;
            }
            .slick-arrow.slick-prev:before {
                left: 20px;
            }
        }

        @media (max-width: 768px) {
            .shop-banner-content .shop-img-warp {
                min-width: max-content;
            }

            .shop-banner-content .shop-img {
                max-width: 100%;

            }

        }

        @media (max-width: 767.98px) {
            .header-menu .static-menu li.open>span {
                background: #faa6aa;
            }

            .slide-title {
                font-size: x-large;
            }

            .slick-arrow.slick-prev:before,
            .slick-arrow.slick-next:before {
                width: 30px;
                height: 30px;
                font-weight: 600;
                font-size: x-small;
                padding-top: 29%;
            }

            .slick-dots li button {
                width: 10px;
                height: 10px;
            }

            .slick-track{
                padding: 0px;
            }
            .product-item-info{
                background: white;
                border-radius: 5px;
                padding: 5px; 
                /* margin: 0px !important; */

            }
            .product-item-info .product-info{
                padding: 10px;
            }
            .product-slider .prod-img {
                position: relative;
                width: 100%;
                padding-top: 100%; /* 1:1 Ratio */
                overflow: hidden;
                border-radius: 5px;
                margin-bottom: 0px;
            }
            .product-slider .prod-img img,
            .product-slider .prod-img .prod-img-display {
                width: 100% !important;
            }
            

            .product-item-info .prod-desc{ 
                float: right;
                height: 30px;
                margin-top: -29px;
                margin-right: -2px;
                position: relative;
            }
            .product-item-info .prod-desc img{
                height: 100%;
                width: auto%;
            }

            .product-item-info .prod-img{
                padding-bottom: 0% !important;
                padding: 0 !important;
                position: inherit;
                margin-bottom:0px
            }
            .product-item-info .link-product-name{
                font-weight: bold ;
                width: 100% !important;
            }
            .product-item-info .price-wrap .price-label,.sold-label{
                color: gray !important;
                font-size: 12px !important;
                padding: 0px !important;
            }
            .product-item-info .shop-name a{
                color: gray;
                font-size: 10px
            }
            .slick-slide img{
                border-radius: 0px
            }
            .slick-slide .normal-price{
                font-size: 16px;
            }
            .slick-slide .slide-title{
                font-size: 32px;
                font-weight: 800;
            }
            .mcs-wrapper{
                width: 100%;
                left: 0px !important;
                right: 0px !important;
            }
            .content-wrap {
                min-height: 400px;
            }

            .link-product-name {
                display: -webkit-box !important;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 2;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                white-space: normal !important;
                line-height: 1.4em;
                max-height: calc(1.4em * 2);
                height: auto !important; /* <== ปรับตรงนี้ ถ้า fix 45px อาจตัดข้อความผิดจังหวะ */
                min-height: calc(1.4em * 2);
            }

            .pagination .page-link{
                min-width: 50px;
            }
            .pagination li{
                margin: inherit;
                background: unset;
                width: fit-content;
            }
            .pagination .page-item:last-child .page-link,
            .pagination .page-item:first-child .page-link,
            .pagination .page-link{
                font-size: inherit;
                border-radius: inherit;
                border: 0.5px solid #dee2e6;
                padding: 0.5rem 0.75rem;
                color: #ED1B24;
                font-weight:100;
                text-decoration: none;
            }
            .pagination .page-item.active .page-link ,
            .pagination .page-item:hover .page-link{
                background-color: #FFE1E3;
                border: 0.5px solid #dee2e6;
                text-decoration: none;
            }

            .grid-bdr li:after{
                background-color: none;
            }
            .product-slider .item-box,
            .product-cate-slider .item-box{
                display: none;
            }
            .product-item-info .product-info{
                padding: inherit
            }
            .slick-slider .slick-track, .slick-slider .slick-list{
                margin-left: inherit;
            }
            .product-cate-slider.shop .product-item-info .prod-img-display{
                border-radius: 50%;
                width: 80%;
                height: 80%;

            }
            .product-cate-slider .product-item-info .link-product-name{
                font-size: 10px;
            }

            h1 {
                font-size: 24px;
            }

            .seller-panneltab li a{
                background-color: #ed1b2414;
            }
            .ship-method-list:hover, .ship-method-list.active{
                background-color: #ED1B24
            }
            .checkout-order-table .table .product-shop .shopname{
                color: #ED1B24;
            }
            .button-switch-sm .switch:checked:before,
            .button-switch .switch:checked:before{
                background-color: green;
            }
            .button-switch-sm .switch:before,
            .button-switch .switch:before{
                background-color: #ED1B24;
            }
            
            .addto-link{
                top: 0%;
                right: 0%;
                z-index: 1;
                visibility: visible;
                opacity: 1;
            }
      
            /* .swal2-container:not(.swal2-in) {
                pointer-events: inherit !important;
            } */

            @media (max-width: 1305px) {

                #header .static-menu li{
                    font-size: smaller;
                }
            }
            @media (max-width: 1199.98px) {
                
                .slick-arrow.slick-next:before{
                    right: 20px;
                }
                .slick-arrow.slick-prev:before{
                    left: 20px;
                }
            }
            @media (max-width: 768px) {
                .shop-banner-content .shop-img-warp {
                    min-width: max-content ;
                }
                .shop-banner-content .shop-img{
                    max-width: 100%;

                }
                
            }
            @media (max-width: 767.98px) {
                .header-menu .static-menu li.open>span{
                    background:#faa6aa;
                }
                .slide-title{
                    font-size:x-large;
                }
                .slick-arrow.slick-prev:before,.slick-arrow.slick-next:before{
                    width: 30px;
                    height: 30px;
                    font-weight: 600;
                    font-size: x-small;
                    padding-top: 29%;
                }
                .slick-dots li button {
                    width: 10px;
                    height: 10px;
                }
                
                .product-item-info.link-product-name{
                    font-size: 10px;    
                }
                h1{
                    font-size: 24px;
                }
                h2{ font-size: 20px; }

                .shop-banner-content .shop-img {
                    max-width: inherit;
                    min-width: inherit;
                }
                .nav-fill .nav-item a{
                    font-size: 12px;
                }
                
                .shop-banner-content > div.d-flex {
                    flex-direction: column;
                }
                .shop-banner-content .shop-img-warp{
                    display: flex;
                }
                .shop-img-warp .shop-img{
                    width: 100px;
                    height: 100px;
                }

            }

            .shop-banner-content .shop-img {
                max-width: inherit;
                min-width: inherit;
            }

            .nav-fill .nav-item a {
                font-size: 12px;
            }

            .shop-banner-content>div.d-flex {
                flex-direction: column;
            }

            .shop-banner-content .shop-img-warp {
                display: flex;
            }

            .shop-img-warp .shop-img {
                width: 100px;
                height: 100px;
            }

        }

        @media (max-width: 575.98px) {

            .product-cate-slider .slick-next,
            .product-cate-slider .slick-prev {
                top: 50px
            }

            .product-cate-slider .slick-arrow.slick-next:before {
                right: 0;
            }

            .product-cate-slider .slick-arrow.slick-prev:before {
                left: 0;
            }

            .filter-field * {
                font-size: small;
            }
        }
    </style>    
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

    <script>
        // document.addEventListener("DOMContentLoaded", function () {
        //     var userAgent = navigator.userAgent.toLowerCase();
        //     var isAndroid = /android/.test(userAgent);
        //     var isIOS = /iphone|ipad|ipod/.test(userAgent);
        //     var isFromLINE = userAgent.includes("line");
        //     var isFromFacebook = userAgent.includes("fbav") || userAgent.includes("facebook");
        //     var isFromInstagram = userAgent.includes("instagram");

        //     // เอา path หลัง domain มาใช้
        //     var path = window.location.pathname; // เช่น /product/26144/268
        //     var deeplinkPath = path.replace(/^\/+/, ""); // เอา path ที่ไม่มี "/" หน้า

        //     if (isFromLINE || isFromFacebook || isFromInstagram) {
        //     // ถ้าเปิดจาก in-app browser ให้ redirect ไปหน้าบอกให้เปิดผ่าน Chrome
        //     //window.location.href = "https://www.simummuangonline.com/open-in-browser?path=" + encodeURIComponent(deeplinkPath);
        //     window.location.href = "https://www.simummuangonline.com/open-in-browser?path=" + path;
        //     return;
        //     }

        //     if (isAndroid) {
        //     window.location = "intent://" + deeplinkPath + "#Intent;scheme=simummuang;package=com.smm.buyer.smm_buyer;end";
        //     } else if (isIOS) {
        //     setTimeout(function () {
        //         window.location = "https://apps.apple.com/th/app/id1607337228";
        //     }, 1500);
        //     window.location = "simummuang://" + deeplinkPath;
        //     } else {
        //     // Desktop
        //     return; // ให้แสดงหน้าเว็บตามปกติ
        //     }
        // });
    </script>


</head>

<body>

    {!! SEO::googleTagManagerBody() !!}
    <div class="wrapper " ng-app="smm-app">
        <div class="loader-wrapper" id="showHideLoader" style="display: none">
            <span class="loader">
                <img src="{{getSiteLoader('SITE_LOADER_IMAGE')}}" alt="Loader">
            </span>
        </div>

        @if(count($header_content))
        @include('layouts.header')
        @endif


        <div class="content-wrap w-100 py-3 {{ $class['sideClass'] }} @if(isset($page_class)) {{ $page_class }} @endif">

            @yield('breadcrumbs')
            @if(checkPageSection() == 'seller')
            @include('includes.seller_top_panel')
            @endif
            @if(count($left_content) || count($right_content)) <div class="container">
                <div class="row"> @endif

                    @if(count($left_content))
                    <aside class="left-sidebar col-md-3" aria-label="sidebar">
                        <div class="sidebar-inner ">
                            @foreach($left_content as $key => $value)

                            @if($value->block_url_key == 'my-account-left-menu')

                            @include('includes.myAccountLeftMenu')

                            @elseif(isset($value->banner_type) && ($value->banner_type == 'banner') || ($value->banner_type == 'slider'))

                            <div class="banner {{ ($value->banner_type == 'slider')?'slider':'' }}">
                                @foreach($value->slider as $skey => $slider_val)
                                <div class="banner-img" data-index="{{$skey}}">
                                    <a href="{{ $slider_val->banner_url }}" target="{{ $slider_val->url_target }}">
                                        <img src="{{ Config::get('constants.banner_url').$slider_val->banner_image }}" alt="{{ !empty($slider_val->bannerdesc)?$slider_val->bannerdesc->banner_title:'' }}" loading="lazy">
                                    </a>
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

                    <div class="{{ $class['main'] }} pt-3 bg-white">
                        {{-- {{json_encode($main_content[2])}}; --}}

                        @if(count($main_content))
                        @foreach($main_content as $key => $value)
                        @if($value->block_url_key == 'content')
                        @if(empty($left_content) && empty($right_content))
                        <div class="container">
                            @endif
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

                        <div class="container home-container full-banner p-0 pb-md-3 mb-3">
                            <div class="banner banner-home {{ ($value->banner_type == 'slider')?'slider':'' }} {{ ($value->group_loop=='true')?'slider_autoloop':'' }}" data-slick='{"speed":{{$value->speed}}}'>
                                @foreach($value->slider as $skey => $slider_val)
                                <div class="banner-img banner-item">
                                    <a href="@if($slider_val->banner_url){{ $slider_val->banner_url }}@else javascript:;@endif" target="{{ $slider_val->url_target }}"><img src="{{ Config::get('constants.banner_url').$slider_val->banner_image }}" alt="{{ !empty($slider_val->bannerdesc)?$slider_val->bannerdesc->banner_title:'' }}" loading="lazy"></a>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @elseif(isset($value->cms_slider))
                        <div class="container">
                            <div class="banner-slider " style="margin-top: {{ $value->cms_slider->slider_option['cont_space_top']??'' }}px; margin-bottom: {{ $value->cms_slider->slider_option['cont_space_bottom']??'' }}px; padding-left: {{ $value->cms_slider->slider_option['thumb_space']??'' }}px; padding-right: {{ $value->cms_slider->slider_option['thumb_space']??'' }}px;">

                                @if(isset($value->cms_slider->sliderdesc) && count($value->cms_slider->slider))
                                <div class="d-flex justify-content-between align-items-center mb-md-3">
                                    <h1 class="slide-title mb-0">
                                        {!! $value->cms_slider->sliderdesc->title ?? '' !!}
                                    </h1>
                                    @if(in_array($value->cms_slider->name , ['product-for-you', 'flash-sales', 'ส-นค-าแนะนำ', 'fresh-fruits']))
                                    <!-- <a class="text-danger text-link" href="{{ url('view-all-products') }}?slider={{ $value->cms_slider->id }}">ดูทั้งหมด</a> -->
                                    @endif
                                </div>
                                @endif

                                <div class="popular-products-contain mb-5">
                                    @if($value->cms_slider->design !='1' && $value->cms_slider->banner)
                                    <div class="bannerimg col-sm-{{ $value->cms_slider->design_val[2] }} @if($value->cms_slider->design_val[1]=='right') order-last @endif">
                                        <div class="banner-innerImg">
                                            <a href="{{ $value->cms_slider->banner_url ?? 'javascript:;' }}">
                                                {{--<img data-original="{{ $value->cms_slider->banner }}" src="{{ Config('constants.image_url')}}/loading.gif" height="" alt="">
                                                --}}<img data-original="{{ $value->cms_slider->banner }}" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ3POnkNx9OsSZdaSodD4MO_oJ5dZZvyLDEoA&s" height="" alt="" loading="lazy">

                                            </a>
                                        </div>
                                    </div>
                                    @endif
                                    {{--
                                    <div class="slider-sg @if($value->cms_slider->design == 1 && $value->cms_slider->container_width>0) col-sm-{{ $value->cms_slider->container_width }} @else col-sm-{{ $value->cms_slider->design_val[3] ?? ''}} @endif">
                                    --}}
                                    <div class="slider-sg row">
                                        @if($value->cms_slider->type == 'product' && !empty($value->cms_slider->slider))
                                        <div id="scroll-1" class=" products product-grid-view w-100  @if($value->cms_slider->show_slider=='yes')  @if($value->cms_slider->design_val[0]=='one') banner-onerow-slider  @else banner-tworow-slider @endif @else product-item-wrappers @endif" data-options="{{ $value->cms_slider->slider_option['item_per_slider'] }};{{ $value->cms_slider->slider_option['setting_slider'] }};height:auto; arrows:true; mode:animation;arrows:outside;lazyLoad: true;">
                                            <div class="product-slider w-100">
                                                @foreach($value->cms_slider->slider as $skey => $result)
                                                <div class="item-box p-1" style=" padding-left: {{ $value->cms_slider->slider_option['thumb_space']??'' }}px; padding-right: {{ $value->cms_slider->slider_option['thumb_space']??'' }}px;">
                                                    <a href="{{ $result['cat_url'] }}">
                                                        <div class="product-item-info">
                                                            <div class="prod-img">

                                                                <div class="prod-img-display shop mx-auto" style="background:url('{{$result['cat_img']??'' }}') center center / cover no-repeat;" loading="lazy"></div>

                                                            </div>
                                                            @if (!isset($result['cat_id']))
                                                            <div class="prod-desc"><img src="{{ $result['badge_img'] }}" loading="lazy" /></div>
                                                            @endif

                                                            <div class="product-info">

                                                                <div class="d-block link-product-name ">
                                                                    {{ $result['cat_name'] }}
                                                                </div>
                                                                @if (isset($result['cat_id']))

                                                                <div class="price-wrap">
                                                                    <div class="price-label mb-1">{{format_number($result['weight_per_unit'])}} {{$result['base_unit']}} / {{$result['package_name']}}</div>
                                                                    <div class="price-label pb-1"> ราคาล่าสุด </div>
                                                                    <h2 class="text-danger mb-0">
                                                                        <strong>฿{{ format_number($result['unit_price'])}}</strong>
                                                                    </h2>
                                                                </div>
                                                                @else
                                                                <div class="d-block shop-name">
                                                                    <small class=""> </small>
                                                                </div>
                                                                <div class="price-wrap">
                                                                    <div class="price-label mb-1">{{format_number($result['weight_per_unit'])}} {{$result['base_unit']}} / {{$result['package_name']}}</div>
                                                                    <div class="price-label mb-1"> ราคาปัจจุบัน </div>

                                                                    <div class="normal-price text-white px-3 py-1 mb-0 bg-danger rounded d-flex align-items-center justify-content-between">
                                                                        <strong>฿{{ format_number($result['unit_price'])}}</strong>
                                                                        <i class="fa-solid fa-cart-shopping float-right"></i>
                                                                    </div>
                                                                    {{--<div class="sold-label">ขายแล้ว <span></span> รายการ</div>--}}
                                                                </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>

                                                @endforeach
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{--
                                    @if ($key ===2) 
                                    <div class="container" style="margin-top: {{ $value->cms_slider->slider_option['cont_space_top']??'' }}px; margin-bottom: {{ $value->cms_slider->slider_option['cont_space_bottom']??'' }}px; padding-left: {{ $value->cms_slider->slider_option['thumb_space']??'' }}px; padding-right: {{ $value->cms_slider->slider_option['thumb_space']??'' }}px;">
                        <div class="col banner-meesuk">

                            <img src="/images/banner-meesuk.png" alt="">
                        </div>
                    </div>
                    @endif
                    --}}

                    @else
                    <div class="fr-element home-container ">
                        {!! $value->static_desc !!}
                    </div>
                    @endif
                    @endforeach

                    @endif

                </div>


                @if(isset($right_content) && count($right_content))
                <aside class="right-sidebar col-md-3">
                    @foreach($right_content as $key => $value)
                    @if($value->block_url_key == 'static-right-side')

                    @elseif(isset($value->banner_type) && ($value->banner_type == 'banner') || ($value->banner_type == 'slider'))
                    <div class="banner {{ ($value->banner_type == 'slider')?'slider':'' }}">
                        @foreach($value->slider as $skey => $slider_val)
                        <div class="banner-img">
                            <a href="{{ $slider_val->banner_url }}" target="{{ $slider_val->url_target }}"><img src="{{ Config::get('constants.banner_url').$slider_val->banner_image }}" alt="{{ !empty($slider_val->bannerdesc)?$slider_val->bannerdesc->banner_title:'' }}" loading="lazy"></a>
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

                @if((isset($left_content) && count($left_content)) || (isset($right_content) && count($right_content)) ) </div></div> @endif
            </div>
            {{--
            <div class="fixed-div" id="fixedDiv">
                <button class="toggle-button" onclick="toggleDiv()">X</button>
                <a href="tel:020239903" rel="noopener noreferrer" target="_blank"><img src="/files/media_manager/12075f8459499dd19b4bc0eed7ccbf39/383-0.11592100%201725548916-froalaeditor.png"></a>
                <a href="https://lin.ee/sTVgeNi" rel="noopener noreferrer" target="_blank"><img src="/files/media_manager/12075f8459499dd19b4bc0eed7ccbf39/142-0.36363300%201725548973-froalaeditor.png"></a>
                <a href="https://www.facebook.com/taladsimummuang" rel="noopener noreferrer" target="_blank"><img src="/files/media_manager/12075f8459499dd19b4bc0eed7ccbf39/244-0.95616600%201725549000-froalaeditor.png"></a>
            </div>
            --}}
            @if(isset($footer_content) && count($footer_content))
                @foreach($footer_content as $fkey => $fval)
                    @if($fval->is_fix !=1 || count($footer_content)==1)
                            @include('layouts.footer',['footer_block_desc'=>$fval->static_desc])
                        @break
                    @endif
                @endforeach
                
            @endif

    {{-- DEV --}}
    {{----}}
        <script type="text/javascript" src="https://cookiecdn.com/cwc.js"></script>
        <script id="cookieWow" type="text/javascript" src="https://cookiecdn.com/configs/fNhtN7Gz8nyeDLKKFCuV8WZK" data-cwcid="fNhtN7Gz8nyeDLKKFCuV8WZK"></script>

    {{-- PROD --}}
    {{--
        <script type="text/javascript" src="https://cookiecdn.com/cwc.js"></script>
        <script id="cookieWow" type="text/javascript" src="https://cookiecdn.com/configs/vUKvRjwKoew5hEdLGhYcYpf4" data-cwcid="vUKvRjwKoew5hEdLGhYcYpf4"></script>
    --}}

    <script type="text/javascript">
        //hide page loader after page load
        /*jQuery(window).on("load", function(){
            showHideLoader('hideLoader');
        }); */
    </script>


    <!-- begin page level js -->
    @yield('footer_scripts')



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


    <script>
        let resizeTimer;
        (function() {
            if ($('.slider div').length > 1) {
                $('.slider').slick({
                    autoplay: true,
                    arrows: true,
                    dots: true,
                    //speed:500,
                });
            }
            initProductSlider(resizeSquareImages);
            initProductCateSlider(resizeSquareImages);

        })(jQuery);

        $(document).ready(function() {
            $(".toggle-btn").click(function() {
                var icon = $(this).find("i");
                icon.toggleClass("fa-angle-up fa-angle-down ");
            });
            resizeSquareImages();

            $('ul#menu1 > li.nav-item li').hover(function () {
                const $submenu = $(this).children('ul');

                if ($submenu.length) {
                    $submenu.css({ display: 'block', visibility: 'hidden' });

                    const submenuOffset = $submenu.offset();
                    const submenuWidth = $submenu.outerWidth();
                    const viewportWidth = $(window).width();

                    if (submenuOffset.left + submenuWidth > viewportWidth) {
                        const newLeft = viewportWidth - submenuWidth - $submenu.offsetParent().offset().left;

                        $submenu.css('left', newLeft + 'px');
                    } else {
                        $submenu.css('left', ''); 
                    }

                    $submenu.css({ display: '', visibility: '' });
                }
            });

        });

        $(window).on('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                resizeSquareImages();
            }, 250);
        });

        function initProductSlider(callback) {
            let slider = $('.product-slider');
            if (slider.length > 0) {
                setTimeout(function() {
                    slider.slick({
                        variableWidth: false,
                        slidesToShow: 6,
                        slidesToScroll: 6,
                        centerMode: false,
                        infinite: false,
                        adaptiveHeight: true,
                        responsive: [{
                                breakpoint: 1024,
                                settings: {
                                    slidesToShow: 4,
                                    slidesToScroll: 4
                                }
                            },
                            {
                                breakpoint: 768,
                                settings: {
                                    slidesToShow: 2,
                                    slidesToScroll: 2
                                }
                            }
                        ]
                    });
                    if (callback) callback();
                }, 200);
            }
        }

        function initProductCateSlider(callback) {
            let slider = $('.product-cate-slider');
            if (slider.length > 0) {
                setTimeout(function() {
                    slider.slick({
                        variableWidth: false,
                        slidesToShow: 10,
                        slidesToScroll: 10,
                        centerMode: false,
                        infinite: false,
                        adaptiveHeight: true,
                        responsive: [{
                                breakpoint: 1024,
                                settings: {
                                    slidesToShow: 6,
                                    slidesToScroll: 6
                                }
                            },
                            {
                                breakpoint: 768,
                                settings: {
                                    slidesToShow: 4,
                                    slidesToScroll: 4
                                }
                            }
                        ]
                    });
                }, 200);
                if (callback) callback();
            }
        }

        function resizeSquareImages(callback) {
            let images = $('.prod-img-display');
            images.each(function() {
                const width = $(this).outerWidth();
                $(this).stop().animate({
                    height: width + 'px'
                }, 300);
            });
            if (callback) callback();

        }

        function toggleDiv() {
            var fixedDiv = document.getElementById('fixedDiv');
            if (fixedDiv.style.display === "none") {
                fixedDiv.style.display = "block";
            } else {
                fixedDiv.style.display = "none";
            }
        }
    </script>

</body>

</html>