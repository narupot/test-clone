<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <base href="{{ asset('/') }}" />
    <title>@yield('title')</title>
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
    <meta http-equiv="Content-Type" content="1200; charset=utf-8" />
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    <link rel="shortcut icon" href="{{ getSiteLogo('SITE_FEVICON_ICON') }}" type="image/x-icon">

    <!-- global css -->
    <link rel="stylesheet" href="{{ Config('constants.css_url') }}bootstrap.min.css"/>
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}jquery-ui.css" />    
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}customscrollbar.css" />
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}magicscroll.css" />
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}magiczoomplus.css" />
    <link href="https://fonts.googleapis.com/css?family=Kanit:200,300,400,500,600,700" rel="stylesheet">

    <!-- <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}bootstrap-colorpicker.css" /> -->
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}slick.css" />
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}fontawesome-iconpicker.min.css" />
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}allfontawesome.css" />

    <script src="{{ Config('constants.admin_js_url') }}theme-customization-jquery-all-library.min.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}masonry.pkgd.min.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}custom.js"></script>

    @yield('header_styles')
    <script>
        window.Laravel = <?php
        echo json_encode([
            'csrfToken' => csrf_token(),
        ]);
        ?>;       
        var siteUrl ="{{url('/')}}/{{ session('lang_code')}}";
    </script>   
   
</head>

<body data-ng-app="sabinaAdminApp">
    @yield('content')
    <script src="{{ Config('constants.js_url') }}common.js"></script>
    <!-- <script src="{{ Config('constants.admin_js_url') }}custom-admin.js"></script> -->
    <!-- begin page level js -->
    @yield('footer-scripts')
    <!-- end page level js -->
</body>
</html>
