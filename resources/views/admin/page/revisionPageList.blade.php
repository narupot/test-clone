@extends('layouts/admin/default')

@section('title')
    @lang('cms.page_list')
@stop

@section('header_styles')

    <!--page level css -->

    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
    <!-- end of page level css -->
    
@stop

@section('content')
    <div class="content">
        <div class="header-title">
            <h1 class="title">@lang('common.revision')</h1>
        </div>      
        <!-- Main content -->           
        <div class="content-wrap ">
            <div class="tab-content listing-tab">
                <div class="tab-pane fade active show" id="staticPage">
                    <table class="table table-bordered " id="table">
                        <thead>
                            <tr class="filters">
                                <th>@lang('common.sno')</th>
                                <th>@lang('cms.title')</th>
                                <th>@lang('common.description')</th>
                                <th>@lang('common.last_updated')</th>
                                <th>@lang('common.actions')</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($revisions as $key => $page_dtl)
                        
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>{{ $page_dtl['static_page_title'] }}</td>
                                <td>{{ $page_dtl['static_page_desc'] }}</td>
                                <td>{{ $page_dtl['updated_at'] }}</td>
                                <td>
                                    <a class="btn-grey" href="{{ action('Admin\Page\StaticPageController@restorepagerevision',[$page_id,$page_dtl['revision']]) }}">@lang('common.restore_this_revision')</a>
                                </td>
                            </tr>
                         @endforeach  
                         </tbody>
                    </table>
                </div>
                
            </div>

            
        </div>
    </div>
@stop

@section('footer_scripts')

    <!-- begining of page level js -->

    <script src="{{ Config('constants.admin_js_url') }}jquery.dataTables.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
    <script>
    $(document).ready(function() {
        $('#table').dataTable();
        $('#table1').dataTable();
    });
    </script>
    <!-- end of page level js -->
    
@stop
