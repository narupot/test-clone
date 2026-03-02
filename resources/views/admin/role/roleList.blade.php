@extends('layouts/admin/default')

@section('title')
    @lang('admin.roles_list')
@stop

@section('header_styles')
    <!--page level css -->
    <link rel="stylesheet" type="text/css" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css" />
    <!-- end of page level css -->
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
        
        <div class="header-title">
            <h1 class="title">@lang('admin.roles_list')</h1>
            @if($permission_arr['add'] === true)
            <span class="float-right">
                <a class="btn btn-create" href="{{ action('Admin\Role\GroupController@create') }}">
                    @lang('admin.add_role')
                </a>
            </span>
            @endif
        </div>
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','role','list')!!}
                </ul>
            </div>
            <table class="table table-bordered " id="table">
                <thead>
                    <tr class="filters">
                        <th>@lang('common.slno')</th>
                        <th>@lang('admin.role_name')</th>
                        <th>@lang('admin.department')</th>
                        <th>@lang('admin.total_admin_in_role')</th>
                        <th>@lang('common.created_at')</th>
                        <th>@lang('common.updated_at')</th>
                        <th>@lang('common.actions')</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($admin_lists as $key => $admin_list)
                
                    <tr>
                        <td>{{ ++$key }}</td>
                        <td>{{ $admin_list->name }}</td>
                        <td>{{ @$admin_list->departmentName->department_name }}</td>
                        <td>{{ count($admin_list->getRoleAdminCount) }}</td>
                        <td>{{ getDateFormat($admin_list->created_at, '1') }}</td>
                        <td>{{ getDateFormat($admin_list->created_at, '1') }}</td>
                        <td>
                        @if($permission_arr['edit'] === true)
                            <a class="btn btn-dark" href="{{ action('Admin\Role\GroupController@edit', $admin_list->id) }}">
                                @lang('common.edit')
                            </a>
                        @endif
                        @if($permission_arr['delete'] === true)
                            <a class="btn btn-delete btn-danger" href="{{ action('Admin\Role\GroupController@delete', $admin_list->id) }}" onclick="return confirm('@lang('admin.are_you_sure_to_delete_the_role')');" data-toggle="modal">
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
    <script>
    $(document).ready(function() {
        $('#table').dataTable();
    });
    </script>
    <!-- end of page level js -->
    
@stop
