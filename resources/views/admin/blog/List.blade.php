@extends('layouts/admin/default')

@section('title')
    @lang('blog.title')
@stop

@section('header_styles')

    <!--page level css -->
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
    <!-- end of page level css -->
    
@stop

@section('content')
    <div class="content">
        <div class="header-title">
            <h1 class="title">@lang('blog.title')</h1>
             @if($permission_arr['add'] === true)
            <div class="float-right">
                <a class="btn btn-primary" href="{{ action('Admin\Blog\BlogController@create') }}"> @lang('common.create_new')</a> 
            </div>          
            @endif
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
        <!-- Main content -->         
           
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('blog','blog','list')!!}
                </ul>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr class="filters">
                        <th>@lang('common.sno')</th>
                        <th>@lang('cms.title')</th>
                        <th>@lang('cms.url_key')</th>
                        <th>@lang('cms.comment')</th>
                        <th>@lang('cms.features')</th>
                        <th>@lang('cms.publish')</th>
                        <th>@lang('common.created_at')</th>
                        <th>@lang('common.last_updated')</th>
                        <th>@lang('common.status')</th>
                        <th>@lang('common.actions')</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($blog_dtls as $key => $blog_dtl)
                
                    <tr>
                        <td>{{ ++$key }}</td>
                        <td>{{ $blog_dtl['title'] }}</td>
                        <td>{{ $blog_dtl['url'] }}</td>
                        <td>{{ $blog_dtl['comment'] }}</td>
                        <td>{{ $blog_dtl['features'] }}</td>
                        <td>{{ $blog_dtl['publish'] }}</td>
                        <td>{{ $blog_dtl['created_at'] }}</td>
                        <td>{{ $blog_dtl['updated_at'] }}</td>
                        <td>
                                               

                        <a id="status_{{ $blog_dtl['id'] }}" href="javascript:void(0);" onclick="callForAjax('{{ action('Admin\Blog\BlogController@changeStatus', $blog_dtl['id']) }}', 'status_{{ $blog_dtl['id'] }}')" class="{{($blog_dtl['status'] =='Active')?'status active':'status inactive'}}">

                        {{ $blog_dtl['status'] }}
                        </a>
                        </td>
                        <td class="text-nowrap">
                            @if($permission_arr['edit'] === true)
                                <a class="btn btn-dark" href="{{ action('Admin\Blog\BlogController@edit', $blog_dtl['id']) }}">@lang('common.edit')</a>
                            @endif

                            @if($permission_arr['delete'] === true) 
                            <form method="post" action="{{ action('Admin\Blog\BlogController@destroy', $blog_dtl['id']) }}" onsubmit="return confirm('Are you sure to delete this record ?');" class="inblock"> 
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}                             
                                <a class="btn btn-delete btn-danger" onclick="$(this).closest('form').submit();" data-toggle="modal">
                                   @lang('common.delete')
                                </a>
                            </form>
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
    <script src="{{ Config('constants.admin_js_url') }}jquery.dataTables.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
    <script>
        $(document).ready(function() {
            var table =  jQuery('table.table').DataTable();
        });
    </script>
    <!-- end of page level js -->
    
@stop
