@extends('layouts/admin/default')

@section('title')
    @lang('admin.image_placeholder') 
@stop

@section('header_styles') 
    <!--page level css -->
    <link rel="stylesheet" type="text/css" href="{{ Config('constants.admin_css_url') }}jasny-bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="{{Config('constants.admin_css_url') }}cropper.min.css">
    <!-- end of page level css -->  
    <script type="text/javascript"> 
        var cropper_setting = {
            //width : 305,
            height : 125,
        };
    </script>
@stop

@section('content')

    <!-- Right side column. Contains the navbar and content of the page -->
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
        <!-- Main content -->
        <form action="{{ action('Admin\Config\SystemConfigController@siteLogoUpdate') }}" method="post" enctype="multipart/form-data" class="form-horizontal form-bordered">
            {{ csrf_field() }}
            <div class="header-title">
                <h1 class="title">@lang('common.site_logo')</h1>
                <span class="float-right">
                    <button type="submit" class="btn btn-secondary">@lang('common.update')</button>
                </span>                
            </div>
            <div class="content-wrap">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('config')!!}
                    </ul>
                </div>

                <!--code for file upload -->
                <div class="form-group">
                    <h2 class="title-prod">@lang('common.logo') 1</h2>
                    <div class="fileinput fileinput-exists" data-provides="fileinput">
                        <div class="fileinput-new thumbnail" style="">
                        </div>
                        <div class="fileinput-preview fileinput-exists thumbnail" style="">
                            <img src="{{ Config::get('constants.site_logo_url').$config_arr['SITE_LOGO_HEADER'].'?header-'.rand(10, 1000) }}">
                        </div> 
                        <!--<span class="btn btn-file">
                            <span class="fileinput-exists">@lang('common.change')</span>
                            <input type="file" name="images[SITE_LOGO_HEADER]" accept="image/*">
                        </span>-->
                    </div>
                    <div class="form-group">
                            {{-- <label for="form-text-input">@lang('cms.select_image') <i class="strick">*</i></label> --}}
                            <input type="hidden" name="images[SITE_LOGO_HEADER]" value="" id="avatar_image_input">
                            @include('admin.includes.sitelogo_image_upload')
                            <div>                      
                             {{-- {!! Form::file('avatar_image') !!} --}}
                             
                             @if ($errors->has('avatarimage'))
                               <p id="avatar_image-error" class="error error-msg">{{ $errors->first('avatarimage') }}</p>
                             @endif
                            </div>
                    </div>
                    <div class=""> 
                        =>@lang('admin.used_on_header_mobile_desktop')
                    </div>                    
                </div>

                <div class="form-group">
                    <label>@lang('common.fevicon_icon') :</label>
                    <div class="fileinput fileinput-exists" data-provides="fileinput">
                        <div class="fileinput-new thumbnail" style="">
                            </div>
                        <div class="fileinput-preview fileinput-exists thumbnail" style="">
                            <img src="{{ Config::get('constants.site_logo_url').$config_arr['SITE_FEVICON_ICON'].'?fevicon-'.rand(10, 1000) }}" height="100">
                        </div> 
                        <span class="btn btn-file btn-primary ml-2">
                            <span class="fileinput-exists">@lang('common.change')</span>
                            <input type="file" name="images[SITE_FEVICON_ICON]" accept="image/*" />
                        </span>
                    </div>
                    <div> 
                        =>@lang('admin.used_for_favicon_icon')
                    </div>                    
                </div>                                                  
                <!--code for file upload ended-->
<!--                 <div class="form-group">
                    <h3 class="col-md-2 control-label"></h3>
                    <div class="col-md-5">
                        <button type="submit" class="btn">@lang('common.update')</button>
                    </div>
                </div> -->
            </div>
        </form>
    </div>
      
@stop

@section('footer_scripts')
    <script type="text/javascript" src="{{ Config('constants.admin_js_url') }}jasny-bootstrap.js"></script>
    <script src="{{ Config('constants.js_url') }}jquery-cropper.min.js" type="text/javascript"></script>
    <script src="{{ Config('constants.js_url') }}sitelogo_cropper_setting.js" type="text/javascript"></script>
@stop
