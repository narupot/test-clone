@extends('layouts/admin/default')

@section('title')
    @lang('admin_website_management.api_configuration')
@stop

@section('header_styles')
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
        <div class="header-title">
            <h3 class="title">@lang('admin_website_management.api_configuration')</h3>
        </div>
        <div class="content-wrap clearfix">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config')!!}
                </ul>
            </div>
            <div class="row">
                <form action="{{ action('Admin\WebsiteMaintenance\WebsiteMaintenanceController@apistore') }}" method="post"  enctype="multipart/form-data" class="col-sm-5">
                    {{ csrf_field() }}

                    <div class="form-group">
                        <label>@lang('admin_website_management.site_maintenance')</label>
                        <select name="MOBILE_MAINTENANCE" required>
                            <option value="1" @if($config_arr['MOBILE_MAINTENANCE'] == '1') selected="selected" @endif>@lang('common.yes')</option>
                            <option value="0" @if($config_arr['MOBILE_MAINTENANCE'] == '0') selected="selected" @endif>@lang('common.no')</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>@lang('admin_website_management.page_title')</label>
                        <input type="text" name="M_PAGE_TITLE" value="{{ $config_arr['M_PAGE_TITLE'] }}" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>@lang('admin_website_management.allowed_ip') 
                            <a href="https://ipinfo.info/html/my_ip_address.php" target="_blank" style="float: right">How to find IP</a></label>
                        <input type="text" name="M_ALLOWED_IP" value="{{ $config_arr['M_ALLOWED_IP'] }}" class="form-control">

                    </div>

                    <div class="form-group">
                        <label>@lang('admin_website_management.by_pass_url')</label>
                         <input type="text" name="M_BY_PASS_URL" value="{{ $config_arr['M_BY_PASS_URL'] }}" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>@lang('admin_website_management.api_page_discription')</label>
                         <textarea name="M_MAINTENANCE_PAGE_DIS" class="form-control froala-editor-apply" required>{{ $config_arr['M_MAINTENANCE_PAGE_DIS'] }}</textarea>
                    </div>
                    
                    </div>    
                    <div class="form-group form-actions">
                        <div class="">
                            <button type="submit" class="btn btn-primary">@lang('admin_common.update')</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
      
@stop
@section('footer_scripts')  
@include('includes.froalaeditor_dependencies')
<script type="text/javascript">
   var csrftoken = window.Laravel.csrfToken;
</script>      
<script src="{{asset('js/normal_froala_editor_setting.js')}}"></script>
@stop
