@extends('layouts/admin/default') 

@section('title')
    @lang('admin_shipping.shipping_change_log')
@stop

@section('header_styles')
    <link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css" />
@stop

@section('content')
    <div class="content"> 

        @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close far fa-times" data-dismiss="alert" aria-hidden="true"></button>
            <strong>@lang('admin.success'):</strong> {{Session::get('succMsg')}}
        </div>
        @endif

        <div class="header-title">
            <h1 class="title">@lang('admin_shipping.shipping_change_log') : {{$table_rate->id}} </h1>
            <a href="{{action('Admin\ShippingProfile\ShippingRateTableController@changeLog',$id)}}" class="btn btn-back">&lt;@lang('admin_common.back')</a>
            </div>            
        </div>  

        <!-- Main content -->
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('warehouse','product', 'log')!!}
                </ul>
            </div>

            <table class="table table-bordered" id="table">
                <thead>
                    <tr class="filters">
                        <th>@lang('admin_common.slno')</th>
                        <th>@lang('admin_common.activity')</th>
                        <th>@lang('admin_shipping.change_from')</th>
                        <th>@lang('admin_shipping.change_to')</th>
                        <th>@lang('admin_common.updated_by')</th>
                        <th>@lang('admin_common.updated_at')</th>
                    </tr>
                </thead>
                <tbody>
                @php
                $i = 0;
                foreach($prod_log_list as $log_key=>$log_detail) {

                    $update_detail = json_decode($log_detail->update_detail);

                    foreach($update_detail as $key=>$value) {

                        $value_arr = explode('=>', $value);

                        @endphp                                   
                        <tr>
                            <td>{{ ++$i }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                            <td>{{ $value_arr['0'] }}</td>
                            <td>{{ $value_arr['1'] }}</td>
                            <td>{{ $log_detail->updated_by }}</td>
                            <td>{{ getDateFormat($log_detail->updated_at) }}</td>
                        </tr> 
                    @php
                    }
                }
                @endphp
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
