@extends('layouts/admin/default')

@section('title')
    @lang('admin_customer.customer_group_list')
@stop

@section('header_styles')

    <!--page level css -->

    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
    <!-- end of page level css -->
    
@stop

@section('content')
    <div class="content">
        <div class="header-title">
            <h1 class="title">@lang('admin_customer.customer_group_list')</h1>
             @if($permission_arr['add'] === true)
            <div class="float-right">
                <a class="btn btn-create" href="{{ action('Admin\Customer\CustGroupController@create') }}"> @lang('admin_common.create_new_group')</a> 
            </div>
          
            @endif
        </div>
             
        <!-- Main content -->         
           
        <div class="content-wrap ">
             @if(Session::has('succMsg'))
            <div class="alert alert-success alert-dismissable margin5">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
            </div>
            @elseif(Session::has('errorMsg'))
            <div class="alert alert-danger alert-dismissable margin5">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>@lang('admin_common.error'):</strong> {{ Session::get('errorMsg') }}
            </div>    
            @endif  
            <table class="table table-bordered " id="table">
                <thead>
                    <tr class="filters">
                        <th>@lang('admin_common.sno')</th>
                        <th>@lang('admin_customer.group_name')</th>
                        <th>@lang('admin_customer.no_of_user')</th>
                        <th>@lang('admin_customer.require_approve')</th>
                        <th>@lang('admin_customer.default')</th>
                        <!-- <th>@lang('admin_customer.payment')</th>
                        <th>@lang('admin_customer.member')</th> -->
                        <th>@lang('admin_common.status')</th>
                        <th>@lang('admin_common.created_at')</th>
                        <th>@lang('admin_common.last_updated')</th>
                        
                        <th>@lang('admin_common.actions')</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($group_dtl as $key => $value)
                
                    <tr @if($value['is_system'] == 1) style="background: #eff0f1;" @endif>
                        <td>{{ ++$key }}</td>
                        <td>{{ $value['customer_group_desc']['group_name'] }}</td>
                        <td>{{ $value['total_customers'] }}</td>
                        <td>{{ ($value['require_approve']) ? Lang::get('common.yes') : Lang::get('common.no')}} </td>
                        <td>{{ ($value['is_default']) ? Lang::get('common.yes') : Lang::get('common.no')}}</td>
                        <!-- <td>{{ ($value['paid']) ? Lang::get('common.yes') : Lang::get('common.no')}}</td>
                        <td>package name</td> -->
                        <td>
                        @if($value['is_system'] == '0')
                        <a id="status_{{ $value['id'] }}" href="javascript:void(0);"  onclick="callForAjax('{{ action('Admin\Customer\CustGroupController@changeStatus', $value['id']) }}', 'status_{{ $value['id'] }}')" class="{{($value->status == 1)?'status active':'status inactive'}}">
                        {{ ($value['status']) ? Lang::get('common.active') : Lang::get('common.inactive')}}
                        </a>
                        @else
                            @lang('common.active')
                        @endif
                        </td>
                        <td>{{ getDateFormat($value['created_at'],1) }}</td>
                        <td>{{ getDateFormat($value['updated_at'],1) }}</td>
                        <td>
                            @if($permission_arr['edit'] === true)
                                <a class="btn-grey" href="{{ action('Admin\Customer\CustGroupController@edit', $value['id']) }}">@lang('admin_common.edit')</a>
                            @endif

                            @if($permission_arr['delete'] === true && $value['is_system'] == 0 && $value['is_default']!='1' && $value['total_customers']==0) 
                            <form method="post" action="{{ action('Admin\Customer\CustGroupController@destroy', $value['id']) }}" onsubmit="return confirm('Are you sure to delete this record ?');" class="inblock"> 
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}                             
                                <a class="btn btn-delete" onclick="$(this).closest('form').submit();" data-toggle="modal">
                                   @lang('admin_common.delete')
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
        $('#table').dataTable();
    });
    </script>
    <!-- end of page level js -->
    
@stop
