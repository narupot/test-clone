@extends('layouts/admin/default')

@section('title')
    @lang('admin.logorder_view')
@stop


@section('header_styles')

<!--page level css -->

<!-- end of page level css -->
    
@stop

@section('content')
    <div class="content">
        <div class="form-horizontal form-bordered">
            <div class="header-title clearfix">                
                <h1 class="title">@lang('admin.logorder_view')</h1> 
                <a href="{{ URL::previous() }}" class="btn btn-default" style="float: right;">Back</a>             
            </div>
            <div class="content-wrap">
                <div class="table-responsive">

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>@lang('admin.log_name')</th>
                                <th>@lang('admin.log_oldvalue')</th>
                                <th>@lang('admin.log_newvalue')</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            @foreach(json_decode($order_view['new_data'],true) as $newkey => $newdata)                            
                            <tr>
                            <td>{{ $newkey }}</td>                            
                            <td>@if($old_data) {{ $old_data[$newkey] }}@endif</td>
                            <td>{{ $newdata }}</td>   
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
@stop
