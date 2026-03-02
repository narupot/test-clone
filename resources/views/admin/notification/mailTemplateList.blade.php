@extends('layouts/admin/default')

@section('title')
    {{$template_data->mail_type}}
@stop

@section('header_styles')

<link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
@stop

@section('content')

    <!-- Right side column. Contains the navbar and content of the page -->
    <div class="content">
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

        <div class="header-title">
            <h1 class="title">{{ $template_data->mail_type }}</h1>
            <div class="float-right">
                <a class="btn btn-back" href="{{ action('Admin\Notification\MailTemplateController@index') }}">&lt;@lang('admin_common.back')</a>
                <a class="btn btn-primary" href="{{ action('Admin\Notification\MailTemplateController@create').'?tempId='.$template_data->id.'&template_type='.$template_type }}">@lang('admin_notification.add_message')</a>
            </div>
        </div>

        <section class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','mail')!!}
                </ul>
            </div>           
        
            <ul class="nav nav-tabs chtabs" role="tablist">
				<li><a class="nav-link" href="{{ action('Admin\Notification\MailTemplateController@editTemplateType', [$template_data->id, $template_type]) }}">{{ ucfirst($notificationName) }} @lang('admin_notification.event')</a></li>
                 <li><a class="active nav-link" href="{{ action('Admin\Notification\MailTemplateController@showdetails', [$template_data->id, $template_type]) }}">@lang('admin_notification.add_message')</a></li>
             </ul>
            <div class="tab-content buyer-mail-table">
                <div class="bg-white p-3">
                    <table class="table table-bordered" id="table">
                        <thead>
                            <tr class="filters">
                                <th>@lang('admin_common.sno')</th>
                                <th>@lang('admin_notification.subject')</th>
                                <th>@lang('admin_notification.language')</th>
                                <th>@lang('admin_notification.created_at')</th>
                                <th>@lang('admin_common.updated_at')</th>
                                <th>@lang('admin_common.action')</th>
                            </tr>
                        </thead>
                        <tbody>

                        @foreach ($templete_list as $key => $mail_detail)
                        
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>{{ $mail_detail->mail_subject }}</td>
                                <td>{{ @$mail_detail->languageName }}</td>
                                <td>{{ getDateFormat($mail_detail->created_at, '1') }}</td>                                    
                                <td>{{ getDateFormat($mail_detail->updated_at, '1') }}</td>

                                <td>
                                    <a class="link-primarys btn btn-outline-info view_templete" href="javascript:void(0)" data-attr="{{ action('Admin\Notification\MailTemplateController@show', $mail_detail->id).'?type=viewMail' }}" data-target="#preview-modal">@lang('admin_common.preview')</a>
                                    &nbsp;
                                    @if($permission_arr['edit'] === true)
                                    <span class="line"></span> 
                                    <a class="link-primarys btn btn-dark" href="{{ action('Admin\Notification\MailTemplateController@edit', $mail_detail->id) }}">
                                       <i class="livicon" data-name="pen" data-loop="true" data-color="#000" data-hovercolor="black" data-size="14"></i>@lang('admin_common.edit')
                                    </a>
                                    @endif                                        
                                </td>
                            </tr>
                        @endforeach

                         </tbody>
                    </table>
                </div>
            </div>
        </section>
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

    <script src="{{ Config('constants.admin_js_url') }}jquery.dataTables.js"></script>
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