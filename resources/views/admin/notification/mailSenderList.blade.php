@extends('layouts/admin/default')

@section('title')
    @lang('admin_notification.email_sender_management')
@stop

@section('header_styles')

    <!--page level css -->

    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
    <!-- end of page level css -->
    
@stop

@section('content')
    <div class="content">
        <div class="header-title">
            <h1 class="title">@lang('admin_notification.email_sender_management')</h1>
             @if($permission_arr['create'] === true)
                <div class="float-right">
                    <a class="btn btn-primary" href="{{ action('Admin\Notification\MailTemplateController@senderCreate') }}">@lang('common.create_new')</a> 
                </div>          
            @endif
        </div>
               
        <!-- Main content -->         
           
        <div class="content-wrap ">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','mail','list')!!}
                </ul>
            </div>
            @if(Session::has('succMsg'))
            <div class="alert alert-success alert-dismissable margin5">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
            </div>
            @endif  
            <table class="table table-bordered mail-type-table" id="table">
                <thead>
                    <tr class="filters">
                        <th>@lang('admin_common.slno')</th>
                        <th style="display:none">@lang('admin_common.id')</th>
                        <th>@lang('admin_notification.sender_name')</th>
                        <th>@lang('admin_notification.sender_email')</th>
                        <th>@lang('admin_notification.default')</th>
                        <th>@lang('admin_notification.status')</th>
                        <th>@lang('admin_notification.created_at')</th>
                        <th>@lang('admin_notification.updated_at')</th>
                        <th>@lang('admin_common.action')</th>
                    </tr>
                </thead>
                <tbody>

                @foreach ($results as $key => $result)
                
                    <tr>
                        <td>{{ ++$key }}</td>
                        <td style="display:none">{{ $result->id }}</td>

                        <td class="primary-color">{{ $result->sender_name }}</td>
                        <td class="primary-color">{{ $result->sender_email }}</td>
                        <td class="primary-color">{{ $result->is_default }}</td>
                        <td class="primary-color">
                          @if($result->status == '1')
                            <span class="active-btn">@lang('admin_product.active')</span>
                          @else
                            <span class="inactive-btn">@lang('admin_product.inactive')</span>
                          @endif

                        </td>
                        <td>{{getDateFormat($result->created_at, '1')}}</td>
                        <td>{{getDateFormat($result->updated_at, '1')}}</td>
                        <td class="text-nowrap">
                            @if($permission_arr['edit'] === true)
                              <a class="link-primarys btn btn-dark" href="{{ action('Admin\Notification\MailTemplateController@editSender',$result->id) }}">@lang('admin_product.edit')</a>
                            @endif
                            @if($permission_arr['delete'] === true)
                            <span class="line"></span>
                              <a class="link-primarys btn btn-danger delete-action" href="{{ action('Admin\Notification\MailTemplateController@deleteSender',$result->id) }}">@lang('admin_product.delete')</a>
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

    <script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
    <script>
    $(document).ready(function() {
        $('#table').dataTable();
    });
    </script>
    
@stop
