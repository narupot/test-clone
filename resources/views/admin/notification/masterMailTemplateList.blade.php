@extends('layouts/admin/default')

@section('title')
    @lang('admin_notification.master_templates')
@stop

@section('header_styles')

    <!--page level css -->
    <link rel="stylesheet" type="text/css" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css" />
    <!-- end of page level css -->
    
@stop

@section('content')

    <!-- Right side column. Contains the navbar and content of the page -->
    <div class="content">
        <div class="header-title">
            <h1 class="title">@lang('admin_notification.master_templates')</h1>
             @if($permission_arr['add'] === true)
            <div class="float-right">
                <a class="btn btn-primary" href="{{ action('Admin\Notification\MailTemplateController@masterTemplateCreate') }}"> @lang('admin_common.create_new')</a> 
            </div>
          
            @endif
        </div>
        @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
        </div>
        @elseif(Session::has('errorMsg'))
        <div class="alert alert-danger alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <strong>@lang('admin_common.error'):</strong> {{ Session::get('errorMsg') }}
        </div>    
        @endif                
        <!-- Main content -->                
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','mastertemplate','list')!!}
                </ul>
            </div>
            <div class="buyer-mail-table">
                <table class="table table-bordered " id="table">
                    <thead>
                        <tr class="filters">
                            <th>@lang('admin_common.sno')</th>
                            <th>@lang('admin_notification.name')</th>
                            <th>@lang('admin_notification.language')</th>
                            <th>@lang('admin_common.created_at')</th>
                            <th>@lang('admin_common.updated_at')</th>
                            <th>@lang('admin_common.action')</th>
                        </tr>
                    </thead>
                    <tbody>

                    @foreach ($master_templates as $key => $master_template)
                    
                        <tr>
                            <td>{{ ++$key }}</td>
                            <td>{{ $master_template->name }}</td>
                            <td>{{ @$master_template->languageName->languageName }}</td>
                            <td>{{getDateFormat($master_template->created_at, '1') }}</td>                                    
                            <td>{{ getDateFormat($master_template->updated_at, '1') }}</td>

                            <td>
                                <a class="link-primarys btn btn-outline-info view_templete" href="javascript:void(0)" data-attr="{{ action('Admin\Notification\MailTemplateController@show', $master_template->id).'?type=viewMaster' }}" data-toggle="modal" data-target="#preview-modal">
                                    @lang('admin_common.preview')
                                </a>
                                @if($permission_arr['edit'] === true)
                                <span class="line"></span>
                                <a class="link-primarys btn btn-dark" href="{{ action('Admin\Notification\MailTemplateController@masterTemplateEdit', $master_template->id) }}">
                                   @lang('admin_common.edit')
                                </a>
                                @endif
                                @if($permission_arr['delete'] === true)
                                <span class="line"></span>
                                <a class="link-primarys btn btn-danger" href="{{ action('Admin\Notification\MailTemplateController@deleteTemplete', $master_template->id) }}" onclick="return confirm('Are you sure to delete this templete.');">
                                   @lang('admin_common.delete')
                                </a> 
                                @endif                                       
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
     <div id="preview-modal" class="modal fade" role="dialog"> 
        <div class="modal-dialog modal-lg">
           <div class="modal-content">
               <span class="loader loader-medium text-center"></span>
            </div>
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
        $('.view_templete').on('click', function(e){
          e.preventDefault();
          $('#preview-modal').modal('show').find('.modal-content').load($(this).attr('data-attr'));
        }); 
    </script>
    <!-- end of page level js -->
    
@stop