@extends('layouts/admin/default')

@section('title')
    @lang('admin.system_configuration')
@stop

@section('header_styles')
@stop
@section('content')

    <div class="content">
        @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                            
        @endif  
        <form action="{{ action('Admin\Config\SystemConfigController@store') }}" method="post"  enctype="multipart/form-data" >
            <div class="header-title">
                <h1 class="title">@lang('admin.general_configuration_setting')</h1>
                <div class="float-right">
                    <button type="submit" class="btn btn-primary">@lang('common.update')</button>
                </div>
            </div>
            <div class="content-wrap clearfix">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('config')!!}
                    </ul>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        {{ csrf_field() }}
                        <div class="form-group">
                            <label>@lang('admin.site_full_name')</label>
                            <input type="text" name="SITE_FULL_NAME" value="{{ $config_arr['SITE_FULL_NAME'] }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>@lang('admin.site_short_name')</label>
                            <input type="text" name="SITE_SHORT_NAME" value="{{ $config_arr['SITE_SHORT_NAME'] }}" class="form-control">
                        </div>                                
                        <div class="form-group">
                            <label>@lang('admin.site_email')</label>
                            <input type="email" name="SITE_EMAIL" value="{{ $config_arr['SITE_EMAIL'] }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>@lang('admin.sender_email')</label>
                            <input type="text" name="EMAIL_SENDER" value="{{ $config_arr['EMAIL_SENDER'] }}" class="form-control">
                        </div>                                 
                        <div class="form-group">
                            <label>@lang('admin.image_extension')</label>
                            <input type="text" name="IMAGE_EXTENSION" value="{{ $config_arr['IMAGE_EXTENSION'] }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>@lang('admin.file_extension')</label>
                            <input type="text" name="FILE_EXTENSION" value="{{ $config_arr['FILE_EXTENSION'] }}" class="form-control">
                        </div>                              
                        <div class="form-group">
                            <label>@lang('admin.time_zone')</label>
                            <select name="TIMEZONE">
                                <option value="">--@lang('common.select')--</option>
                                {!!CustomHelpers::getTimeZoneDorpDown($config_arr['TIMEZONE'])!!}
                            </select>
                        </div>

                        <div class="form-group">
                            <label>@lang('admin.font_family')</label>
                            <input type="text" name="FONT_FAMILY" value="{{ $config_arr['FONT_FAMILY'] }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>@lang('admin.font_size')</label>
                            <input type="text" name="FONT_SIZE" value="{{ $config_arr['FONT_SIZE'] }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>@lang('admin.font_colour')</label>
                            <input type="text" name="FONT_COLOUR" value="{{ $config_arr['FONT_COLOUR'] }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>@lang('admin.bg_colour')</label>
                            <input type="text" name="BG_CLOUR" value="{{ $config_arr['BG_CLOUR'] }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>@lang('admin.clear_cart_time_in_hour')</label>
                            <input type="text" name="CART_CLEAR_TIME" value="{{ $config_arr['CART_CLEAR_TIME'] }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>@lang('admin.client_code_for_order_export_file')</label>
                            <input type="text" name="CLIENT_CODE_FOR_ORDER_EXPORT_FILE" value="{{ $config_arr['CLIENT_CODE_FOR_ORDER_EXPORT_FILE'] }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>@lang('admin.client_acc_no_for_order_export_file')</label>
                            <input type="text" name="CLIENT_ACC_NO_ORDER_EXPORT_FILE" value="{{ $config_arr['CLIENT_ACC_NO_ORDER_EXPORT_FILE'] }}" class="form-control">
                        </div>
                    </div>
                  
                </div>
            </div>
        </form>
    </div>
      
@stop

@section('footer_scripts')  
@stop
