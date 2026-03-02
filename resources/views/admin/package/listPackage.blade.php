@extends('layouts/admin/default')

@section('title')
    @lang('package.list_package')
@stop

@section('header_styles')

    <!--page level css -->

    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
    <!-- end of page level css -->
    
@stop

@section('content')
    <div class="content">

        <div class="header-title">
            <h1 class="title">@lang('package.list_package')</h1>
            @if($permission_arr['add'] === true)
                <div class="float-right">
                    <a class="btn btn-create" href="{{ action('Admin\Package\PackageController@create') }}">@lang('package.create_new')</a> 
                </div>
            @endif
        </div> 
               
        <!-- Main content -->         
           
        <div class="content-wrap ">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('package')!!}
                </ul>
            </div>
            <div class="tab-content listing-tab">
                
                <table class="table table-bordered " id="table">
                    <thead>
                        <tr class="filters">
                            <th>@lang('package.sno')</th>
                            <th>@lang('package.package_name')</th>
                            <th>@lang('package.created_at')</th>
                            <th>@lang('package.last_updated')</th>
                            <th>@lang('package.status')</th>
                            <th>@lang('package.actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($package_dtl)
                            @foreach ($package_dtl as $key => $res)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td>{{ $res->packagedesc->package_name }}</td>
                                    <td>{{ $res->created_at }}</td>
                                    <td>{{ $res->updated_at }}</td>
                                    <td>
                                    <a id="status_{{ $res->id }}" href="javascript:void(0);" onclick="callForAjax('{{ action('Admin\Package\PackageController@changeStatus', $res->id) }}', 'status_{{ $res->id }}')" class="{{($res->status == 1)?'status active':'status inactive'}}">
                                    @if($res->status)
                                        @lang('package.active')
                                    @else
                                        @lang('package.inactive')
                                    @endif
                                    </a>
                                    </td>
                                    <td>
                                        @if($permission_arr['edit'] === true)
                                            <a class="btn btn-dark" href="{{ action('Admin\Package\PackageController@edit', $res->id) }}">@lang('package.edit')</a>
                                        @endif

                                        @if($permission_arr['delete'] === true) 
                                        <form method="post" action="{{ action('Admin\Package\PackageController@destroy', $res->id) }}" onsubmit="return confirm('@lang("package.are_you_sure_to_delete_this_record")');" class="inblock"> 
                                            {{ csrf_field() }}
                                            {{ method_field('DELETE') }}   
                                            
                                            <a class="btn btn-delete btn-danger" onclick="$(this).closest('form').submit();" data-toggle="modal">@lang('package.delete')
                                            </a>
                                           
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif  
                    </tbody>
                </table>

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
    });
    </script>
    <!-- end of page level js -->

@stop
