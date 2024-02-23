<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <base href="{{ asset('/') }}" />
    <title>@yield('title')</title>
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
    <meta http-equiv="Content-Type" content="1200; charset=utf-8" />    
    <!--[if lt IE 9]>    
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    <link rel="shortcut icon" href="{{ getSiteLogo('SITE_FEVICON_ICON').'?fevicon-'.rand(10, 1000) }}" type="image/x-icon">

    <!-- global css -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}allfontawesome.css" />
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}bootstrap.min.css"/>
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}global.css" />
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}style.css" />
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}jquery-ui.min.css" />
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}customscrollbar.css" />
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}broadcast_notification.css" />
    <link rel="stylesheet" type="text/css" href="{{ Config('constants.admin_css_url') }}sweetalert2.min.css" />
    <link rel="stylesheet" type="text/css" href="{{ Config('constants.admin_css_url') }}toastr.min.css" />

    <script src="{{ Config('constants.admin_js_url') }}jquery.min.js"></script> 
    <script src="{{ Config('constants.admin_js_url') }}jquery-ui.min.js"></script>    
    <script src="{{ Config('constants.admin_js_url') }}bootstrap.bundle.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}bootstrap.min.js"></script>  
    <script>
        $.fn.bootstrapBtn = $.fn.button.noConflict();
        $.fn.bootstrapTooltip = $.fn.tooltip.noConflict();
    </script>           
    <script src="{{ Config('constants.admin_js_url') }}jquery.mousewheel.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}customscrollbar.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}bootstrap-typeahead.js"></script>    
    <script src="{{ Config('constants.admin_js_url').'sweetalert2.min.js' }}"></script>
    <script src="{{ Config('constants.admin_js_url').'toastr.min.js' }}"></script>
    <!-- end of global css -->
    
    <!--page level css-->
    @yield('header_styles')
    <!--end of page level css-->
    <script>
        window.Laravel = <?php
        echo json_encode([
            'csrfToken' => csrf_token(),
        ]);
        ?>;       
        var siteUrl ="{{url('/')}}/{{ session('lang_code')}}";
        var autoUrl = "{{ action('Admin\Search\AdminSearchController@adminAutoSearch') }}";
        var website_maintenance_url = "{{ Action('Admin\WebsiteMaintenance\WebsiteMaintenanceController@updateMaintenance') }}";
        var mobile_maintenance_url = "{{ Action('Admin\WebsiteMaintenance\WebsiteMaintenanceController@updateMobileMaintenance') }}";
        var admin_default_lang = "{{Session::get('admin_default_lang')}}";
        var records_updated_successfully = "@lang('common.records_updated_successfully')";
        var lang_success = "@lang('common.success')";
        var lang_oops = "@lang('common.oops')";
        var lang_ok = "@lang('common.ok')";
        var lang_yes = "@lang('admin_common.yes')";
        var lang_no = "@lang('admin_common.no')";
        var table_message = {
            "page" : "@lang('admin_common.pagination_page')",
            "record" : "@lang('admin_common.pagination_records_per_page')",
            "display" : "@lang('admin_common.pagination_displaying')",
            "s_no" : "@lang('admin_common.sno')",
            "reset_filter" : "@lang('admin_common.reset_filter')",
            'export' :  "@lang('admin_common.export')",
            "format":  "@lang('admin_common.format')",
        };
    </script>   
   
</head>

<div class="loader-wrapper d-none" id="showHideLoader">
    <span class="loader">
        <img src="{{getSiteLoader('SITE_LOADER_IMAGE')}}" alt="Loader" width="30" height="30"> 
        <div>@lang('common.please_wait')...</div>
    </span>
</div>

<body data-ng-app="smm-app">
    <header class="hidden-xs">
        <div class="header-top">
            <div class="logo">
                <a href="{{ action('Admin\AdminHomeController@index') }}"><img src="{{ getSiteLogo('SITE_LOGO_HEADER') }}" alt="Logo"></a>
            </div>

            <div class="header-search-block">
                <div class="on-off-block d-flex pr-2">
                        @if(checkPermission('web_status_button'))
                        <div class="switch switch-vertical">
                            @if(empty(websiteMaintenanceMode()))
                                <input id="switchonoff" type="checkbox" value="1" name="second-switch" checked="checked" />
                                <div class="web-status">Web status</div>                              
                                <label class="cng-text">Open</label>
                                <span class="toggle-outside">
                                    <span class="toggle-inside"></span>
                                </span>
                            @else
                                <input id="switchonoff" type="checkbox" value="0" name="second-switch" />
                                <div class="web-status">Web status</div>                              
                                <label class="cng-text" style="color: #ff0000">Close</label>
                                <span class="toggle-outside switch-close">
                                    <span class="toggle-inside trv-bottom"></span>
                                </span>
                            @endif
                        </div> 
                        @endif
                        @if(checkPermission('mobile_status_button')) 
                            <div class="switch switch-vertical">
                                @if(empty(mobileMaintenanceMode()))
                                    <input id="mobileswitchonoff" type="checkbox" value="1" name="second-switch" checked="checked" />
                                    <div class="web-status">Mobile status</div>                              
                                    <label class="cng-text-1">Open</label>
                                    <span class="toggle-outside">
                                        <span class="toggle-inside"></span>
                                    </span>
                                @else
                                    <input id="mobileswitchonoff" type="checkbox" value="0" name="second-switch" />
                                    <div class="web-status">Mobile status</div>                              
                                    <label class="cng-text-1" style="color: #ff0000">Close</label>
                                    <span class="toggle-outside switch-close">
                                        <span class="toggle-inside trv-bottom"></span>
                                    </span>
                                @endif
                                                
                            </div>
                        @endif
                </div>
                <div class="search-block">
                    <!--<form method="get" action="{{ action('Admin\Search\AdminSearchController@index') }}">-->
                        <form method="get" >
                        <div class="search-select">
                            <div class="nav-search-selected">
                                <span class="nav-search-text">@lang('admin_order.order')</span>
                                <i class="fas fa-chevron-down"></i>    
                            </div>
                            <select class="search-selectitem" id="autoSearchItem" name="search_type">
                                <option value="order">@lang('admin_order.order')</option>
                                <option value="shipment">@lang('admin_order.shipment')</option>
                                <option value="invoice">@lang('admin_order.invoice')</option>
                                <option value="user">@lang('admin_seo.user')</option>
                                <option value="product">@lang('admin_order.product')</option>                            
                            </select>
                        </div>
                        <div class="searchinput">                        
                            <input id="header-search" type="text" name="header_search" class="ip-search" placeholder="Search your order" autocomplete="off" />
                        </div>
                        <button class="search-btn" type="submit"><i class="fas fa-search"></i></button>  
                    </form>                  
                </div>
            </div>

            <div class="float-right header-right">
                <div class="header-col time-zone d-none">   
                    <div class="time-label">@lang('admin.time_zone') :</div>
                    <span class="user-nm">{{session('default_time_zone_label')}}</span>
                </div>
                <div class="header-col border-left-0 pl-0">
                    <div class="dropdown d-inline-block">
                        <a herf="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">
                            <span class="user-name">
                                <img src="{{getUserImageUrl(Auth::guard('admin_user')->user()->image)}}"> 
                                <span class="user-nm">{{Auth::guard('admin_user')->user()->nick_name}}</span>
                                <i class="fas fa-chevron-down pl-1"></i> 
                            </span>                            
                        </a>
                        <div class="dropdown-menu small_font center dropdown-menu-right">
                            <ul>
                                <li class="dd-language">
                                    @php($language_data = getLanguageSwitcherData()) <a href="javascript:void(0);">@lang('admin.language') <i class="fas fa-chevron-down"></i></a>
                                    <ul class="lang-flag">
                                    @foreach($language_data['languages'] as $value)
                                        @php($lang_url = Config::get('constants.public_url').$value->languageCode.'/'.$language_data['cur_url'])
                                        <li><a href="{{$lang_url}}"><span><img src="{{Config::get('constants.language_url').$value->languageFlag}}" alt=""></span>{{strtoupper($value->languageCode)}}</a></li>
                                    @endforeach
                                    </ul>
                                </li>
                                <li><a href="{{ action('Admin\User\AdminController@accountDetail')}}"><i class="fas fa-edit"></i> Edit Profile</a></li>
                                <li>
                                    <form id="admin-logout-form" action="{{action('AdminAuth\LoginController@logout')}}" method="POST">
                                        {{ csrf_field() }}
                                        <button class="section-icon-wrapper"><i class="fas fa-sign-out-alt"></i> Logout</button>   
                                    </form>                                 
                                </li>
                                <li>
                                    <div class="time-zone">
                                        <span class="time-head"><i class="fas fa-clock"></i> @lang('admin.time_zone') : </span>
                                        <span class="user-nm">{{session('default_time_zone_label')}}</span>
                                    </div>
                                </li>
                                <li class="spaces_consumed">
                                    @lang('common.spaces_consumed') : <span class="font-weight-bold">{{getConsumedSpace()}} MB</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="wrapper">
        <!-- Left side column. contains the logo and sidebar -->
        <div class="green_clkmenu">
            <a href="javascript:;" class="mbSlide"><span></span></a>        
        </div>
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
    @php($popup_noti = CustomHelpers::getLatestPopupNotification())
    @if(!empty($popup_noti))
    <div id="popupdivbrodcast" class="modal modal-payment fade in" role="dialog">
        <div class="modal-dialog modal-md complain-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>@lang('admin_notification.broadcast_message')</h2>
                    <span class="close icon-remove" data-dismiss="modal">                                  
                    </span>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-3">
                            <div class="notify-img">
                                <img src="{{$popup_noti->notifi_type_img}}" alt="">
                            </div>
                        </div>
                        <div class="col-sm-9">
                             <p><?php echo $popup_noti->message;?></p>
                             <p><a href="{{action('Admin\Broadcast\BroadcastController@index',['message'=> $popup_noti->id])}}">@lang('admin_notification.read')</a></p>
                        </div>
                    </div>
                  
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">   
        $(window).on('load',function(){
            var is_set_popup = 
            $('#popupdivbrodcast').modal('show');
        });
    </script>    
    @endif
    

    <!-- <a id="back-to-top" href="#" class="btn btn-primary btn-lg back-to-top" role="button" title="Return to top" data-toggle="tooltip" data-placement="left"> 
        <i class="livicon" data-name="plane-up" data-size="18" data-loop="true" data-c="#fff" data-hc="white"></i>
    </a>-->

    <!-- global js -->
    <script type="text/javascript">
        var order_mode_status = "{{GeneralFunctions::systemConfig('ORDER_MODE')}}";
        var no_record_found = "@lang('admin_common.no_record_found')";
        var all = "@lang('admin_common.all')";
        var search_result = "@lang('admin_common.search_result')";
        var txt_order = "@lang('admin_order.order')";
        var txt_invoice = "@lang('admin_order.invoice')";
        var txt_shipment = "@lang('admin_order.shipment')";
    </script>
    <script src="{{ Config('constants.js_url') }}common.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}custom-admin.js"></script>
    <!-- end of global js -->
    @if(Session::has('succMsg'))    
        <script type="text/javascript">               
            _toastrMessage('success', "{{ Session::get('succMsg') }}");    
        </script>                              
    @endif
    @if(Session::has('errorMsg'))
        <script type="text/javascript">               
            _toastrMessage('error', "{{ Session::get('errorMsg') }}");    
        </script>    
    @endif
    <!-- begin page level js -->
    @yield('footer_scripts_include')
    @yield('footer_scripts')
    <!-- end page level js -->

</body>
</html>
