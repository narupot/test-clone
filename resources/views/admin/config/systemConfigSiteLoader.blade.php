@extends('layouts/admin/default')

@section('title')
    @lang('admin.site_loader') 
@stop

@section('header_styles') 
    <!--page level css -->
    <link rel="stylesheet" type="text/css" href="{{ Config('constants.admin_css_url') }}jasny-bootstrap.css" />
    <!-- end of page level css -->  
@stop

@section('content')

    <!-- Right side column. Contains the navbar and content of the page -->
    <div class="content">
        @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                            
        @endif 

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissable">

                    @foreach ($errors->all() as $error)
                        <div class="d-inline-block">{{ $error }}</div>                      
                    @endforeach
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            </div>
        @endif


        <!-- Main content -->
        <form action="{{ action('Admin\Config\SystemConfigController@siteLoaderUpdate') }}" method="post" enctype="multipart/form-data" class="form-horizontal form-bordered">
            {{ csrf_field() }}
            <div class="header-title">
                <h1 class="title">@lang('common.site_loader')</h1>
                <span class="float-right">
                    <button type="submit" class="btn btn-primary">@lang('common.update')</button>
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
                    <h3 class="col-md-2 control-label">@lang('common.loader')</h3>
                    <div class="fileinput fileinput-exists col-md-3" data-provides="fileinput">
                        <div class="fileinput-new thumbnail" style=""> 
                            </div>
                        <div class="fileinput-preview fileinput-exists thumbnail" style="">
                            <img src="{{ Config::get('constants.site_loader_url').$config_arr['SITE_LOADER_IMAGE'] }}">
                        </div> 
                        <span class="btn btn-file btn-default">
                            <span class="fileinput-exists">@lang('common.change')</span>
                            <input type="file" name="SITE_LOADER_IMAGE" accept="image/*">
                        </span>
                    </div>
                               
                </div>

                
            </div>
        </form>
    </div>
      
@stop

@section('footer_scripts')
    <script type="text/javascript" src="{{ Config('constants.admin_js_url') }}jasny-bootstrap.js"></script>
@stop
