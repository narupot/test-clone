@extends('layouts/admin/default')
@section('title')
    @lang('setting.setting')
@stop
@section('header_styles')
<link rel="stylesheet" type="text/css" href="{{Config('constants.admin_css_url') }}order.css">
@stop
@section('content')
    <div class="content">
        @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                            
        @elseif(Session::has('errorMsg'))
            <script type="text/javascript"> 
                _toastrMessage('error', "{{ Session::get('errorMsg') }}");
            </script>  
        @endif 
        <!-- Setting section start here -->
        <div class="header-title">
            <h1 class="title">@lang('setting.system_config')</h1>           
        </div>
        <div class="content-wrap clearfix">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','config','list')!!}
                </ul>
            </div> 
            <div class="content-left">
                <div class="tablist">                    
                    <ul class="nav nav-tabs">
                        <li class="nav-item"><a class="nav-link show active" data-toggle="tab" data-target="#config-admin"><i class="fa fa-cog"></i> @lang('setting.admin_setting')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#config-general"><i class="fa fa-cog"></i> @lang('setting.general_setting')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#config-seo"><i class="fa fa-cog"></i> @lang('setting.seo_and_analytic_tools')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#config-advance"><i class="fa fa-cog"></i> @lang('setting.advance')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#config-shipping"><i class="fa fa-truck"></i> @lang('setting.shipping')</a></li>
                    </ul>
                </div>
            </div>
            <div class="content-right">
                <div class="tab-content">
                    <div id="config-admin" class="tab-pane fade show active">
                        <h2 class="title-prod">@lang('setting.admin_setting')</h2>
                        <div class="setting-box-container flex-cols">
                        @if($permission_arr['team_member'] === true)
                            <div class="setting-box bg-blue1">
                                <div class="icons"><i class="fa fa-users"></i></div>
                                <h3>@lang('setting.team_member')</h3>
                                <div class="setting-box-content">
                                    <ul>
                                        <li><a href="{{ action('Admin\User\AdminController@index') }}">- @lang('setting.team_member')</a></li>
                                    </ul>
                                </div>
                            </div>
                        @endif
                        @if($permission_arr['role_setting'] === true)
                            <div class="setting-box bg-perano">
                                <div class="icons"><i class="fa fa-key"></i></div>
                                <h3>@lang('setting.role_setting')</h3>
                                <div class="setting-box-content">
                                    <ul>
                                        <li><a href="{{ action('Admin\Role\GroupController@index') }}">- @lang('setting.role_setting')</a></li>
                                    </ul>
                                </div>
                            </div>
                        @endif
                        @if($permission_arr['team_member'] === true)
                            <div class="setting-box bg-blue1">
                                <a href="{{ action('Admin\WebsiteMaintenance\WebsiteMaintenanceController@index') }}"><div class="icons"><i class="fa fa-cog"></i></div></a>
                                <h3>@lang('setting.website_maintenance')</h3>
                                <div class="setting-box-content">
                                    <ul>
                                        <li><a href="{{ action('Admin\WebsiteMaintenance\WebsiteMaintenanceController@index') }}">-@lang('setting.website_maintenance')</a></li>
                                    </ul>
                                </div>
                            </div>
                        @endif
                        @if($permission_arr['logactivity'] === true)
                            <div class="setting-box bg-perano">
                                <div class="icons"><i class="fa fa-key"></i></div>
                                <h3>@lang('setting.logs')</h3>
                                <div class="setting-box-content">
                                    <ul>
                                        <li><a href="{{ action('Admin\Logactivity\LogactivityController@index') }}">- @lang('setting.log_activity')</a></li>

                                        <li><a href="{{ action('Admin\LoginLog\LoginLogController@admin') }}">- @lang('setting.admin_log_activity')</a></li>

                                        <li><a href="{{ action('Admin\LoginLog\LoginLogController@user') }}">- @lang('setting.user_log_activity')</a></li>
                                    </ul>
                                </div>
                            </div>
                        @endif

                        @if($permission_arr['payment_method'] === true)
                            <div class="setting-box bg-perano">
                                <div class="icons"><i class="fa fa-credit-card"></i></div>
                                <h3>@lang('admin_setting.payment_method')</h3>
                                <div class="setting-box-content">
                                    <ul>
                                        @if($permission_arr['payment_option'] === true)
                                            <li><a href="{{ action('Admin\Config\PaymentOptionController@index') }}">- @lang('admin_setting.payment_option')</a></li>
                                        @endif
                                        @if($permission_arr['payment_bank'] === true)
                                            <li><a href="{{ action('Admin\Config\PaymentBankController@index') }}">- @lang('admin_setting.payment_bank')</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        @endif
                        </div>                        
                    </div>
                    <div id="config-general" class="tab-pane fade">
                        <h2 class="title-prod">@lang('setting.general_setting') </h2>
                        @if($permission_arr['placeholder'] === true)
                        <div class="setting-box-container flex-cols">
                            <div class="setting-box bg-sham-green">
                                <div class="icons"><i class="far fa-image"></i></div>
                                <h3>@lang('setting.placeholders')</h3>
                                <div class="setting-box-content">
                                    <ul>
                                    @if($permission_arr['gender_list'] === true)
                                        <li><a href="{{ action('Admin\Gender\GenderController@index') }}">- @lang('setting.gender_list')</a></li>
                                    @endif
                                    @if($permission_arr['avtar_images'] === true)
                                        <li><a href="{{ action('Admin\Config\AvatarController@index') }}">- @lang('setting.avtar_images')</a></li>
                                    @endif
                                    @if($permission_arr['image_placeholder'] === true)
                                        <li><a href="{{ action('Admin\Config\SystemConfigController@placeholderImage') }}">- @lang('setting.image_placeholders')</a></li>
                                    @endif
                                    </ul>                            
                                </div>
                            </div>
                            @endif                
                            @if($permission_arr['site_setting'] === true)
                            <div class="setting-box bg-pig-green">
                                <div class="icons"><i class="fa fa-key"></i></div>
                                <h3>@lang('setting.site_configuration')</h3>
                                <div class="setting-box-content">
                                    <ul>
                                        <li><a href="{{ action('Admin\Config\SystemConfigController@index') }}">- @lang('setting.site_setting')</a></li>
                                        <li>
                                          <a href="{{ action('Admin\Config\SystemConfigController@siteLoaderEdit') }}">- @lang('setting.site_loader')</a>
                                        </li>
                                    </ul>
                                </div>
                                
                            </div>
                            @endif
                            @if($permission_arr['site_logo'] === true)
                            <div class="setting-box bg-b-turquoise">
                                <div class="icons"><i class="shopinfIcon icon-icon-shop"></i></div>
                                <h3>@lang('setting.site_logo')</h3>
                                <div class="setting-box-content">
                                    <ul>
                                        <li><a href="{{ action('Admin\Config\SystemConfigController@siteLogoEdit') }}">- @lang('setting.site_logo')</a></li>
                                    </ul>
                                </div>
                            </div>
                            @endif                
                        </div>
                    </div>
                    <div id="config-seo" class="tab-pane fade">
                    <h2 class="title-prod">@lang('setting.seo_and_analytic_tools')</h2>
                    @if($permission_arr['seo'] === true)
                        <div class="setting-box-container flex-cols">
                            <div class="setting-box bg-chinook-green">
                                <div class="icons"><i class="fa fa-search"></i></div>
                                <h3>@lang('setting.seo')</h3>
                                <div class="setting-box-content">
                                    <ul>
                                    @if($permission_arr['templete_seo'] === true)
                                        <li><a href="{{ action('Admin\SEO\SeoGlobalController@index') }}">- @lang('setting.seo_template')</a></li>
                                    @endif
                                    @if($permission_arr['global_seo'] === true)
                                        <li><a href="{{ action('Admin\SEO\SeoController@pages') }}">- @lang('setting.global_seo_management')</a></li>
                                    @endif
                                    @if($permission_arr['store_location_seo'] === true)
                                        {{--<li><a href="{{ action('Admin\storeLocation\StoreLocationController@show', 'seo') }}">- @lang('setting.store_location_seo_setting')</a></li>--}}
                                    @endif                            
                                    </ul>
                              </div>
                            </div>
                        @endif
                        @if($permission_arr['default_seo'] === true)
                            <div class="setting-box bg-l-blue">
                                <div class="icons"><i class="analyIcon icon-icon-report"></i></div>
                                <h3>@lang('setting.analytic')</h3>
                                <div class="setting-box-content">
                                    <ul>
                                        <li><a href="{{ action('Admin\Config\SystemConfigController@SEOConfig') }}">- @lang('setting.analytic')</a></li>
                                    </ul>
                                </div>
                            </div>
                        @endif
                        </div>
                    </div>
                    <div id="config-advance" class="tab-pane fade">
                        <h2 class="title-prod">@lang('setting.advance')</h2>
                        <div class="setting-box-container flex-cols">
                        @if($permission_arr['manage_language'] === true)
                            <div class="setting-box bg-green">
                                <div class="icons"><i class="analyIcon icon-icon-report"></i></div>
                                <h3>@lang('setting.base_language')</h3>
                                <div class="setting-box-content">
                                    <ul>
                                        <li><a href="{{ action('Admin\Config\LanguageController@index') }}">- @lang('setting.base_language')</a></li>
                                    </ul>
                                </div>
                            </div>
                        @endif                
                        @if($permission_arr['mail_template'] === true)
                            <div class="setting-box bg-purple">
                                <div class="icons"><i class="mediaIcon icon-icon-media"></i></div>
                                <h3>@lang('setting.email_template')</h3>
                                <div class="setting-box-content">
                                    <ul>
                                    @if($permission_arr['manage_mail_template'] === true)
                                        <li><a href="{{ action('Admin\Notification\MailTemplateController@index') }}">- @lang('setting.manage_mail_template')</a></li>
                                    @endif
                                    @if($permission_arr['manage_master_template'] === true)
                                        <li><a href="{{ action('Admin\Notification\MailTemplateController@masterTempateList') }}">- @lang('setting.manage_master_template')</a></li>
                                    @endif
                                    @if($permission_arr['email_transmission_setting'] === true)
                                        <li><a href="{{ action('Admin\Notification\MailTemplateController@manageEmailTransmission') }}">- @lang('setting.email_transmission_setting')</a></li>
                                    @endif
                                    </ul>                             
                              </div>
                            </div>
                        @endif
                        @if($permission_arr['translation'] === true)
                            <div class="setting-box bg-orange">
                                <div class="icons"><i class="menuIcon icon-icon-menuMgt"></i></div>
                                <h3>@lang('setting.translation')</h3>
                                <div class="setting-box-content">
                                    <ul>
                                    @if($permission_arr['translation_module'] === true)
                                        <li><a href="{{ action('Admin\Translation\TranslationModuleController@index') }}">- @lang('setting.translation_module')</a></li>
                                    @endif
                                    @if($permission_arr['translate_module_key'] === true)
                                        <li><a href="{{ action('Admin\Translation\TranslationController@index') }}">- @lang('setting.translate_module_key')</a></li>
                                    @endif
                                    @if($permission_arr['translate_by_search'] === true)
                                        <li><a href="{{ action('Admin\Translation\TranslationController@searchTranslation') }}">- @lang('setting.translate_by_search')</a></li>
                                    @endif
                                    @if($permission_arr['translate_menu'] === true)
                                        <li><a href="{{ action('Admin\Translation\MenuController@index') }}">- @lang('setting.translate_menu')</a></li>
                                    @endif
                                        <li><a href="{{ action('Admin\Translation\OrderStatusController@index') }}">- @lang('setting.order_status')</a></li>
                                    </ul>
                                </div>
                            </div>
                        @endif                
                        
                        </div>
                    </div>
                    <div id="config-shipping" class="tab-pane fade">
                        <h2 class="title-prod">@lang('setting.shipping')</h2>
                        <div class="setting-box-container flex-cols">
                        @if($permission_arr['pickup-at-center'] === true)
                            <div class="setting-box bg-green">
                                <div class="icons"><i class="fas fa-cubes"></i></div>
                                <h3>@lang('setting.pickup_at_center')</h3>
                                <div class="setting-box-content">
                                    <ul>
                                        <li><a href="{{ action('Admin\Config\SystemConfigController@pickupCenter') }}"> - @lang('setting.pickup_at_center')</a></li>
                                    </ul>
                                </div>
                            </div>
                        @endif                
                        @if($permission_arr['pickup-at-store'] === true)
                            <!-- <div class="setting-box bg-green">
                                <div class="icons"><i class="fas fa-warehouse"></i></div>
                                <h3>@lang('setting.pickup_at_store')</h3>
                                <div class="setting-box-content">
                                    <ul>
                                        <li><a href="{{ action('Admin\Config\SystemConfigController@pickupAtStore') }}"> - @lang('setting.pickup_at_store')</a></li>
                                    </ul>
                                </div>
                            </div> -->
                        @endif
                        @if($permission_arr['delivery-at-address'] === true)
                            <div class="setting-box bg-green">
                                <div class="icons"><i class="fas fa-truck"></i></div>
                                <h3>@lang('setting.delivery_at_address')</h3>
                                <div class="setting-box-content">
                                    <ul>
                                        <li><a href="{{ action('Admin\ShippingProfile\ShippingRateTableController@deliveryAtAddress') }}"> - @lang('setting.delivery_at_address')</a></li>
                                    </ul>
                                </div>
                            </div>
                        @endif                
                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Setting section end here -->
        </div>
    </div>
@stop

@section('footer_scripts')
    
@stop
