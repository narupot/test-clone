@extends('layouts/admin/default')

@section('title')
    @lang('admin_product.badge_list')
@stop

@section('header_styles')

    <!--page level css -->

    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
    <!-- end of page level css -->
    
@stop

@section('content')
    <div class="content">

        <div class="header-title">
            <h1 class="title">@lang('admin_product.badge_list')</h1>
            @if($permission_arr['add'] === true)
                <div class="float-right">
                    <a class="btn btn-create btn-success" href="{{ action('Admin\Badge\BadgeController@create') }}">@lang('common.create_new')</a> 
                </div>
            @endif
        </div> 
               
        <!-- Main content -->         
           
        <div class="content-wrap ">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('product')!!}
                </ul>
            </div>
            <div class="tab-content listing-tab">
                <table class="table table-bordered " id="table">
                    <thead>
                        <tr class="filters">
                            <th>@lang('common.sno')</th>
                            <th>@lang('common.title')</th>
                            <th>@lang('common.created_at')</th>
                            <th>@lang('common.last_updated')</th>
                            <th>@lang('common.status')</th>
                            <th>@lang('common.actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($badge_dtl)
                            @foreach ($badge_dtl as $key => $res)
                                <tr>
                                    <td>{{ ++$key }}</td>
                                    <td>{{ $res->title }}</td>
                                    <td>{{ $res->created_at }}</td>
                                    <td>{{ $res->updated_at }}</td>
                                    <td>
                                    <a id="status_{{ $res->id }}" href="javascript:void(0);" onclick="callForAjax('{{ action('Admin\Badge\BadgeController@changeStatus', $res->id) }}', 'status_{{ $res->id }}')"  class="{{($res->status == 1)?'status active':'status inactive'}}">
                                    @if($res->status)
                                        @lang('admin_common.active')
                                    @else
                                        @lang('admin_common.inactive')
                                    @endif
                                    </a>
                                    </td>
                                    <td>
                                        @if($permission_arr['edit'] === true)
                                            <a class="btn btn-dark" href="{{ action('Admin\Badge\BadgeController@edit', $res->id) }}">@lang('common.edit')</a>
                                        @endif

                                        @if($permission_arr['delete'] === true) 
                                        <form method="post" action="{{ action('Admin\Badge\BadgeController@destroy', $res->id) }}" onsubmit="return confirm('@lang("admin_common.are_you_sure_to_delete_this_record")');" class="inblock"> 
                                            {{ csrf_field() }}
                                            {{ method_field('DELETE') }}   
                                            
                                            <a class="btn btn-delete btn-danger" onclick="$(this).closest('form').submit();" data-toggle="modal">@lang('common.delete')
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

    <script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
    <script>
    $(document).ready(function() {
        $('#table').dataTable();
    });
    </script>
    <!-- end of page level js -->

@stop
