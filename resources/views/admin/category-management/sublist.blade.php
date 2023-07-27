@extends('layouts/admin/default')

@section('title')
    @lang('category.list')
@stop

@section('header_styles')

    <!--page level css -->
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
    <!-- end of page level css -->
    
@stop

@section('content')
    <div class="content">
        <div class="header-title">
            <h1 class="title">@lang('category.list')</h1>
            <div class="float-right">
                <a class="btn btn-primary" href="{{ action('Admin\CategoryManagement\CategoryController@create') }}"> @lang('common.create_new')</a> 
            </div>
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
                {!!getBreadcrumbAdmin('category','category','list')!!}
                </ul>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr class="filters">
                        <th>@lang('common.sno')</th>
                        <th>@lang('cms.image')</th>
                        <th>@lang('cms.category_name')</th>
                        <th>@lang('cms.parent_category')</th>
                        <!-- <th>@lang('cms.allow_base_unit')</th> -->
                        <th>@lang('cms.status')</th>
                        <th>@lang('cms.created_by')</th>
                        <th>@lang('common.created_at')</th>
                        <th>@lang('common.last_updated')</th>
                        <th>@lang('common.actions')</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($categories as $key => $mainCategory)
                
                    <tr>
                        <td>{{ ++$key }}</td>
                        <td><img src="{{ getCategoryImageUrl($mainCategory->img) }}" width="100px" height="100px" ng-show="display_mode.image"></td>
                        <td>{{ $mainCategory->getCatDesc->name }}</td>
                        <td>{{ getParentCategory($mainCategory->parent_id) }}</td>
                        <!-- <td>--</td> -->
                        @if($mainCategory->status=="1")
                            <td>@lang('common.active')</td>
                        @else
                        <td>@lang('common.inactive')</td>
                        @endif
                        <td>{{ getUser($mainCategory->created_by)}}</td>
                        <td>{{ getDateFormat($mainCategory->created_at, '1')}}</td>
                        <td>{{ getDateFormat($mainCategory->updated_at, '1') }}</td>
                        <td class="text-nowrap">
                            
                            <a class="btn btn-dark" href="{{ action('Admin\CategoryManagement\CategoryController@edit', $mainCategory->id) }}">@lang('common.edit')</a> 
                            <form method="post" action="{{ action('Admin\CategoryManagement\CategoryController@destroy', $mainCategory->id) }}" onsubmit="return confirm('Are you sure to delete this record ?');" class="inblock"> 
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}                             
                                <a class="btn btn-delete btn-danger" onclick="$(this).closest('form').submit();" data-toggle="modal">
                                   @lang('common.delete')
                                </a>
                            </form>
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
