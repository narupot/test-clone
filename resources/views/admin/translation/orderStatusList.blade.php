@extends('layouts/admin/default')

@section('title')
    @lang('admin.order_status_list')
@stop

@section('header_styles')
    <!--page level css -->
    <link rel="stylesheet" type="text/css" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css" />
    <!-- end of page level css -->
@stop

@section('content')

    <div class="content">
        @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
        </div>   
        @endif      
        <div class="header-title">
            <h1 class="title">@lang('admin.order_status_list')</h1>            
        </div>
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','translation-menu','list')!!}
                </ul>
            </div>
            <table class="table table-bordered " id="table">
                <thead>
                    <tr class="filters">
                        <th>@lang('admin_common.slno')</th>
                        <th>@lang('admin.order_status_name')</th>
                        <th>@lang('admin_common.action')</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($menu_lists as $key => $menu_list)
                    <tr>
                        <td>{{ ++$key }}</td>
                        <td>{{ @$menu_list->orderStatusName->status }}</td>
                        <td>
                        @if($permission_arr['edit'] === true)
                            <a class="btn btn-dark" href="{{ action('Admin\Translation\OrderStatusController@edit', $menu_list->id) }}">@lang('admin_common.edit')</a>
                        @endif
                        </td>
                    </tr>                            
                @endforeach  
                </tbody>
            </table>
        </div>
    </div>
        
@stop

@section('footer_scripts')

    <!-- begining of page level js -->
    <script type="text/javascript" src="{{ Config('constants.admin_js_url') }}jquery.dataTables.js"></script>
    <script type="text/javascript" src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
    <script>
    $(document).ready(function() {
        $('#table').dataTable();
    });
    </script>
    <!-- end of page level js -->
    
@stop
