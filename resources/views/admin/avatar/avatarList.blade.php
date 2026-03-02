@extends('layouts/admin/default')

@section('title')
    @lang('admin.avatar_list')
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
        @endif      
        <!-- Main content -->
        <div class="header-title">
            <h1 class="title">@lang('admin.avatar_list')</h1>
            @if($permission_arr['add'] === true)
            <span class="float-right">
                <a class="btn btn-create" href="{{ action('Admin\Config\AvatarController@create') }}">
                    @lang('admin.add_avatar')
                </a>
            </span>
            @endif
        </div>
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('avatar')!!}
                </ul>
            </div>                                             
            <table class="table table-bordered">
                <thead>
                    <tr class="filters">
                        <th>@lang('common.slno')</th>
                        <th>@lang('common.title')</th>
                        <th>@lang('common.image')</th>
                        <th>@lang('common.description')</th>
                        <th>@lang('common.gender')</th>                           
                        <th>@lang('common.status')</th>
                        <th>@lang('common.actions')</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($avtarlist as $key => $avatarunit)
                    <tr>
                        <td>{{ ++$key }}</td>
                        <td>{{ $avatarunit->title }}</td>
                         <td><img src="{!! asset('files/avtar_images/'.$avatarunit->name)  !!}" width="50px" height="50px"></td>
                        <td>{{ $avatarunit->description }}</td>
                       	@if($avatarunit->gender == "M")
                       		<td>@lang('common.male')</td>
                       	@elseif($avatarunit->gender == "F")
                        	<td>@lang('common.female')</td>
                       	@else
                       		<td>@lang('common.undefined')</td>
                       	@endif

                        @if($avatarunit->status == '1')
                            <td>@lang('common.active')</td>
                        @else
                            <td>@lang('common.inactive')</td>
                        @endif

                        <td>
                        @if($permission_arr['edit'] === true)
                            <a class="btn btn-dark" href="{{ action('Admin\Config\AvatarController@edit', $avatarunit->id) }}">@lang('common.edit')</a>
                        @endif                                        
                            <!--<a class="btn default btn-xs black" href="{{ action('Admin\Config\AvatarController@destroy', $avatarunit->id) }}" onclick="return confirm('Confirm to delete this currency?');" data-toggle="modal">
                                <i class="livicon" data-name="trash" data-loop="true" data-color="#000" data-hovercolor="black" data-size="14"></i>Delete
                            </a>-->
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
        var table =  $('table.table').DataTable();
    });
    </script>
    <!-- end of page level js -->
    
@stop
