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
            <h1 class="title">@lang('blog.revision')</h1>
             
        </div>      
        <!-- Main content -->         
           
        <div class="content-wrap ">
            <table class="table table-bordered">
                <thead>
                    <tr class="filters">
                        <th>@lang('common.sno')</th>
                        <th>@lang('cms.title')</th>
                        <th>@lang('cms.short_desc')</th>
                        <th>@lang('cms.description')</th>
                        <th>@lang('common.last_updated')</th>
                        <th>@lang('common.actions')</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($revisions as $key => $blog_dtl)
                
                    <tr>
                        <td>{{ ++$key }}</td>
                        <td>{{ $blog_dtl['blog_title'] }}</td>
                        <td>{{ $blog_dtl['blog_short_desc'] }}</td>
                        <td>{{ $blog_dtl['blog_desc'] }}</td>
                        <td>{{ $blog_dtl['updated_at'] }}</td>
                        
                        <td>
                            <a class="btn-grey" href="{{ action('Admin\Blog\BlogController@restoreblogrevision',[$blog_id,$blog_dtl['revision']]) }}">@lang('common.restore_this_revision')</a>
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
