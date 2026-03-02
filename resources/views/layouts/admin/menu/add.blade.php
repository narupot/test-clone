<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <base href="{{ asset('/') }}" />
    <title>        
    @yield('title')     
    </title>
    <!-- <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'> -->
    <meta http-equiv="Content-Type" content="1200; charset=utf-8" />
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

    <!-- global css -->
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}bootstrap.min.css"/>
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}global.css" />
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}style.css" />
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}jquery-ui.min.css" />
    <script src="{{ Config('constants.admin_js_url') }}jquery.min.js"></script> 
    <script src="{{ Config('constants.admin_js_url') }}jquery-ui.min.js"></script>    
    <!-- end of global css -->    
    <!--page level css-->
    @yield('header_styles')
    <!--end of page level css-->
    <script>
        window.Laravel = <?php
        echo json_encode([
            'csrfToken' => csrf_token(),
        ]);
        ?>        
        var siteUrl ="{{url('/')}}/{{ session('lang_code')}}";
    </script>   
</head>
<body data-ng-app="megaMenuApp">
    <header class="hidden-xs">
        <div class="header-top">
            <div class="logo">
            <a href="#"><img src="images/logo.png" alt="logo" title="Life Compass Shoping"></a>
            </div>
            <div class="header-search">
                <form name="searchform" class="form-inline" action="#" method="post" novalidate>
                {{ csrf_field() }}
                    <div class="input-group"> 

                        <div class="nav-search-wrapper">
                        <div class="nav-search-select">
                            {{-- <div class="nav-search-selected">
                                <span class="nav-search-text" id="autoSearchItemSelected">Select</span> 
                                <i class="glyphicon glyphicon-triangle-bottom"></i>
                            </div> --}}
                            <select class="search-selectitem" id="autoSearchItem" name="search_type">
                                <optgroup label="Order">
                                    <option value="ord_order_id" @if(isset($_GET['section']) && $_GET['section'] == 'ord_order_id') selected="selected" @endif>Order Id</option>
                                    <option value="ord_user_name" @if(isset($_GET['section']) && $_GET['section'] == 'ord_user_name') selected="selected" @endif>User name</option>
                                    <option value="ord_user_email" @if(isset($_GET['section']) && $_GET['section'] == 'ord_user_email') selected="selected" @endif>User email</option>
                                </optgroup> 

                                <optgroup label="Shipment">
                                    <option value="ship_order_id" @if(isset($_GET['section']) && $_GET['section'] == 'ship_order_id') selected="selected" @endif>Order Id</option>
                                    <option value="ship_shipment_id" @if(isset($_GET['section']) && $_GET['section'] == 'ship_shipment_id') selected="selected" @endif>Shipment Id</option>
                                </optgroup> 

                                <optgroup label="User">
                                    <option value="usr_name" @if(isset($_GET['section']) && $_GET['section'] == 'usr_name') selected="selected" @endif>Name</option>
                                    <option value="usr_email" @if(isset($_GET['section']) && $_GET['section'] == 'usr_name') selected="selected" @endif>Email</option>
                                </optgroup>
                                <optgroup label="Blogger">
                                    <option value="blgr_name" @if(isset($_GET['section']) && $_GET['section'] == 'blgr_name') selected="selected" @endif>Name</option>
                                    <option value="blgr_email" @if(isset($_GET['section']) && $_GET['section'] == 'blgr_email') selected="selected" @endif>Email</option>
                                </optgroup>
                                <optgroup label="Seller">
                                    <option value="seller_name" @if(isset($_GET['section']) && $_GET['section'] == 'seller_name') selected="selected" @endif>Name</option>
                                    <option value="seller_email" @if(isset($_GET['section']) && $_GET['section'] == 'blgr_email') selected="selected" @endif>Email</option>
                                    <option value="seller_shop" @if(isset($_GET['section']) && $_GET['section'] == 'blgr_email') selected="selected" @endif>Shop name</option>
                                </optgroup>
                                <optgroup label="Product">
                                    <option value="prd_name" @if(isset($_GET['section']) && $_GET['section'] == 'seller_name') selected="selected" @endif>Product Name</option>
                                    <option value="prd_shop" @if(isset($_GET['section']) && $_GET['section'] == 'blgr_email') selected="selected" @endif>Shop name</option>
                                </optgroup>
                                <optgroup label="Blog">
                                    <option value="blog_name" @if(isset($_GET['section']) && $_GET['section'] == 'blog_name') selected="selected" @endif>Blog Name</option>
                                    <option value="blog_blogger" @if(isset($_GET['section']) && $_GET['section'] == 'blog_blogger') selected="selected" @endif>Blogger name</option>
                                </optgroup>

                            </select>
                        </div>
                        <div class="nav-search-submit">
                            {{-- <button class="input-group-addon" type="submit" ><span class="glyphicon glyphicon-search"></span></button>  --}}
                        </div>
                        <div class="nav-search-input">
                            <input type="text" required class="form-control" id="header-search" placeholder="Search" name="search" @if(isset($_GET['search'])) value="{{$_GET['search']}}" @endif  autocomplete="off"/> 
                        </div>
                     </div>
                    </div>
                </form>
            </div>

                <!-- Profile  and message section  -->     

            <div class="header-setting">            
                <div class="dropdown">
                    <span class="section-icon-wrapper icon-head-more dropdown-toggle icon-General" data-toggle="dropdown"></span>
                    <ul class="dropdown-menu right-dd">
                        <li>
                            <form id="admin-logout-form" action="{{action('AdminAuth\LoginController@logout')}}" method="POST">
                                {{ csrf_field() }}
                                <button class="btn">Logout</button>   
                            </form>                
                        </li>
                    </ul>
              </div>
            </div>

        </div>
    </header>

    <section class="wrapper">
        <!-- Left side column. contains the logo and sidebar -->
        
        <!-- BEGIN LEFT SIDEBAR MENU -->
        <aside class="admin-sidebar">
            <section class="sidebar ">
                <nav class="admin-menu">        
                    <ul id="navmenu">               
                        {!! CustomHelpers::getUserMenu() !!}
                    </ul>
                </nav>
            </section>   
        </aside>
        <!-- END LEFT SIDEBAR MENU -->
        
        <!-- BEGIN MAIN CONTENT -->
        @yield('content')
        <!-- END MAIN CONTENT -->
        
    </section>

    <!-- <a id="back-to-top" href="#" class="btn btn-primary btn-lg back-to-top" role="button" title="Return to top" data-toggle="tooltip" data-placement="left"> 
        <i class="livicon" data-name="plane-up" data-size="18" data-loop="true" data-c="#fff" data-hc="white"></i>
    </a>-->

    <!-- global js -->
    <script src="{{ Config('constants.admin_js_url') }}bootstrap.min.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}custom-admin.js"></script>
    <script src="{{ Config('constants.js_url') }}common.js"></script>
    <!-- end of global js -->
    
    <!-- begin page level js -->
    @yield('footer_scripts')
    <!-- end page level js -->

</body>
</html>
