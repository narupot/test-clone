@extends('layouts/admin/default')
@section('title')
    @lang('admin_menu.menu_management')
@stop
@section('header_styles')
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/> 
@stop

@section('content')
    <div class="content">

        <div class="header-title">
            <h1 class="title">@lang('admin_menu.my_menu')</h1>
<!--             <div class="float-right">
                <a class="btn-secondary" href="{{ action('Admin\Menu\MenuController@createMenu') }}">@lang('common.create_new')</a> 
            </div> -->
        </div> 

        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('menu','menu','list')!!}
                </ul>
            </div>
            <div class="container-fluid">
                @if(count($menu_data))             
                    <table class="table table-bordered " id="table">
                        <thead>
                            <tr class="filters">
                                <th>@lang('admin_common.id')</th> 
                                <th>@lang('admin_common.title')</th>
                                <th>@lang('admin_common.status')</th>
                                <th>@lang('admin_common.default')</th>
                                <th>@lang('admin_common.created_at')</th>
                                <th>@lang('admin_common.updated_at')</th> 
                                @if($permission_arr['edit'] === true)                   
                                    <th>@lang('admin_common.action')</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($menu_data as $key => $menu)
                                <tr>
                                    <td>{{ $menu->id }}</td>
                                    <td>{{ $menu->name}}</td>
                                    <td>{{ $menu->status }}</td>
                                    <td>{{ $menu->is_default }}</td>
                                    <td>{{ getDateFormat($menu->created_at, '1') }}</td>
                                    <td>{{ getDateFormat($menu->updated_at, '1') }}</td>
                                    @if($permission_arr['edit'] === true)
                                        <td>                   
                                            <a class="btn btn-dark" href="{{ $menu->edit_url }}">
                                               @lang('admin_common.edit')
                                            </a>                   
                                        </td>
                                    @endif
                                </tr>                
                             @endforeach                 
                        </tbody>
                    </table>
                @endif 
            </div>
        </div>
    </div>
@stop
@section('footer_scripts')
    <script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script> 
    <script type="text/javascript">
        (function($){
            $('#table').dataTable();
        })(jQuery);
    </script>
@stop