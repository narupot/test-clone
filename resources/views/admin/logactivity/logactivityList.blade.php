@extends('layouts/admin/default')

@section('title')
    @lang('admin.log_activity')
@stop

@section('header_styles')

    <!--page level css -->
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
    <!-- end of page level css -->
    
@stop

@section('content')
    <div class="content">
        <div class="header-title">
            <h1 class="title">@lang('admin.log_activity')</h1>             
        </div>
             
        <!-- Main content -->         
           
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config')!!}
                </ul>
            </div>
            @if(Session::has('succMsg'))    
                <script type="text/javascript">               
                    _toastrMessage('success', "{{ Session::get('succMsg') }}");    
                </script>                            
            @elseif(Session::has('errorMsg'))
                <script type="text/javascript"> 
                    _toastrMessage('error', "{{ Session::get('errorMsg') }}");
                </script>  
            @endif 
            <table class="table table-bordered" id="table">
                <thead>
                    <tr class="filters">
                        <th>@lang('common.sno')</th>
                        <th>@lang('admin.action_by')</th>
                        <th>@lang('admin.action_by_email')</th>
                        <th>@lang('admin.action_type')</th>
                        <th>@lang('admin.module_name')</th>
                        <th>@lang('admin.action_detail')</th>                            
                        <th>@lang('admin.action_date')</th>
                        <th>@lang('common.ip_address')</th>
                        <th>@lang('admin.log_action')</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($activity_logs as $key => $value)
                
                    <tr @if($value['type'] == 0) style="background: #eff0f1;" @endif>
                        <td>{{ ++$key }}</td>
                        <td>{{ $value['action_by'] }}</td>
                        <td>{{ $value['action_by_email'] }}</td>
                        <td>{{ $value['action_type'] }}</td>
                        <td>{{ $value['module_name'] }}</td>
                        <td>{{ $value['action_detail'] }} </td>                            
                        <td>{{ getDateFormat($value['created_at'],1) }}</td> 
                        <td>{{ $value['ip_address'] }}</td>                           
                        <td>
                            @if(!empty($value['old_data'] || $value['new_data'] ))
                            <a href="{{ action('Admin\Logactivity\LogactivityController@logDetails',$value->id) }}" class="btn btn-info">@lang('admin.log_details')</a>
                            @endif                             

                            <!-- @if($value['module_name']=="product")
                            <a href="{{ action('Admin\Logactivity\LogactivityController@productView',$value->id) }}" class="btn-default">@lang('admin.logproduct_view')</a>
                            @endif 
                            
                            @if($value['module_name']=="order")
                            <a href="{{ action('Admin\Logactivity\LogactivityController@orderView',$value->id) }}" class="btn-default">@lang('admin.logorder_view')</a>
                            @endif  -->
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
        var table =  $('#table').DataTable();
    });
    </script>
    <!-- end of page level js -->
    
@stop
