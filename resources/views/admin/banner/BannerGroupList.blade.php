@extends('layouts/admin/default')

@section('title')
    @lang('cms.banner_group_list') - {{getSiteName()}} 
@stop

@section('header_styles')

    <!--page level css -->

    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
    <!-- end of page level css -->
    
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
            <h1 class="title">@lang('cms.banner_group_list')</h1>
            @if($permission_arr['add'] === true)
            <div class="float-right">
                <a class="btn btn-create" href="{{ action('Admin\Banner\BannerGroupController@create') }}"> @lang('common.create_new')</a> 
            </div>
            @endif
        </div>
             
        <!-- Main content -->         
           
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('block','bannergroup','list')!!}
                </ul>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr class="filters">
                        <th>@lang('common.sno')</th>
                        <th>@lang('cms.group_name')</th>
                        <th>@lang('common.status')</th>
                        <th>@lang('common.created_at')</th>                            
                        <th>@lang('common.actions')</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($results as $key => $result)
                
                    <tr>
                        <td>{{ ++$key }}</td>
                        <td>{{ $result->group_name }}</td>


                       @if($result->status == '1')
                          <td><span class="active-btn">@lang('common.active')</span></td>
                       @else
                           <td><span class=" inactive-btn">@lang('common.inactive')</span></td> 
                       @endif

                        <td>{{ getDateFormat($result->created_at, '1') }}</td>

                        <td>
                        @if($permission_arr['edit'] === true)
                            <a class="btn btn-dark" href="{{ action('Admin\Banner\BannerGroupController@edit', $result->id) }}">@lang('common.edit')</a>
                        @endif
                        @if($permission_arr['delete'] === true)
                           {!! Form::open(['style' => 'display: inline-block;', 'method' => 'DELETE', 'onsubmit' => 'return confirm("Are sure delete this data.");',  'url' => action('Admin\Banner\BannerGroupController@update', $result->id)]) !!}
                               <button class="btn-grey btn-danger" type="submit">
                                @lang('common.delete')
                            </button>
                        {!! Form::close() !!}
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
        var table =  $('table.table').DataTable();
    });
    </script>
    <!-- end of page level js -->
    
@stop
