@extends('layouts/admin/default')

@section('title')
    @lang('common.log_details') - {{getSiteName()}}
@stop

@section('header_styles')

    <!--page level css -->
    <link href="{{ asset('assets/css/pages/tables.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css" />
    <!-- end of page level css -->
    
@stop

@section('content')
     <div class="content">
       @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                            
        @endif 
       <div class="header-title">
            <h1 class="title">@lang('common.log_details')
            </h1>
            @if($permission_arr['delete'] === true)
                    <div class="float-right">
                        <a id="userclearLog" class="pull-right btn clearLog btn-danger" href="javascript://"> @lang('common.clear_all')</a>
                    </div>
             @endif           
            
        </div>
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config')!!}
                </ul>
            </div>
            <table class="table table-bordered" id="table">
                <thead>
                     <tr class="filters">
                        <th>@lang('common.full_name')</th>
                        <th>@lang('common.email')</th>
                        <th>@lang('common.password')</th>
                        <th>@lang('common.ip_address')</th>
                        <th>@lang('common.created_at')</th>
                        <th>@lang('common.login_status')</th>
                        <th>@lang('common.action')</th>
                     </tr>
                </thead>
                <tbody>

                @foreach ($log_lists as $admin_log)
                    <tr>
                    <td>{{ $admin_log->full_name }}</td>
                    <td>{{ $admin_log->email }}</td>
                    <td>{{ $admin_log->password }}</td>
                    <td>{{ $admin_log->ip_address }}</td>
                    <td>{{ $admin_log->created_at }}</td>             
                    @if($admin_log->status == '1')
                    <td><span style="color:green">@lang('common.success')</span></td>
                    @elseif($admin_log->status == '2')
                                <td><span style="color:red">@lang('common.failed')</span></td>
                    @elseif($admin_log->status == '3')
                                <td><span style="color:blue">@lang('common.logout')</span></td>
                    @endif         
                    <td>
                       @if($permission_arr['delete'] === true)
                       <a class="btn default btn-xs black userDeleteLog" id="{{$admin_log->id}}" href="javascript://" data-toggle="modal">
                                    @lang('common.delete')
                       </a>
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
    <script type="text/javascript" src="{{ asset('js/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/logs.js') }}"></script>
    <script>
    var userclearLog_url = "{{ action('Admin\LoginLog\LoginLogController@userclearLog') }}";
    var userDeleteLog_url = "{{ action('Admin\LoginLog\LoginLogController@userdeleteLog') }}";
    $(document).ready(function() {
        $('#table').dataTable({
            "orderFixed": [ 4, 'desc' ]
        });
    });
    </script>
    <!-- end of page level js -->
    
@stop