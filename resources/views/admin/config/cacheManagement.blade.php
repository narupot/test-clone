        
@extends('layouts/admin/default')

@section('title')
    @lang('admin_common.cache_management')
@stop

@section('header_styles')
<link rel="stylesheet" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css"/>
@stop

@section('content')
<div class="content">
    <div class="header-title">
        <h1 class="title">@lang('admin_common.cache_storage_management')</h1>
        
        <div class="float-right">
            <a class="btn btn-primary" href="{{ action('Admin\Config\CacheController@clearCloudeCache') }}"> @lang('admin_common.clear_cloude_cache')</a>

            <a class="btn btn-warning" href="{{ action('Admin\Config\CacheController@updateVersion') }}"> @lang('admin_common.css_js_update')</a>
			@if($permission_arr['view'] === true)
				<a class="btn btn-info" href="{{ action('Admin\Config\CacheController@clearWebsiteView') }}"> @lang('admin_common.view_clear')</a>
            @endif
			@if($permission_arr['config'] === true)
				<a class="btn btn-primary" href="{{ action('Admin\Config\CacheController@clearWebsiteConfig') }}"> @lang('admin_common.config_clear')</a>
            @endif
			@if($permission_arr['route'] === true)
				<a class="btn btn-primary" href="{{ action('Admin\Config\CacheController@clearWebsiteRoute') }}"> @lang('admin_common.route_clear')</a>
            @endif
			@if($permission_arr['all'] === true)
				<a class="btn btn-danger" href="{{ action('Admin\Config\CacheController@clearWebsiteCache') }}"> @lang('admin_common.clear_all')</a> 
			@endif
		</div>
        
    </div>
        
    <!-- Main content -->       
       
    <div class="content-wrap ">
        <div class="breadcrumb">
            <ul class="bredcrumb-menu">
                {!!getBreadcrumbAdmin('config','cache','list')!!}
            </ul>
        </div> 
        <!-- @if(Session::has('succMsg'))
            <div class="alert alert-success alert-dismissable margin5">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
            </div>
        @elseif(Session::has('errorMsg'))
            <div class="alert alert-danger alert-dismissable margin5">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <strong>@lang('admin_common.error'):</strong> {{ Session::get('errorMsg') }}
            </div>    
        @endif  -->  
        <table class="table table-bordered " id="table">
            <thead>
                <tr class="filters">
                    
                    <th>@lang('admin_common.sno')</th>
                    <th>@lang('admin_common.name')</th>
                    <th>@lang('admin_common.clear_by')</th>
                    <th>@lang('admin_common.date')</th>
                    <th>@lang('admin_common.clear_time_in_min')</th>
                    <th>@lang('admin_common.actions')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cache_data as $key => $val)
                    <tr>
                        <td>{{$val->id}}</td>
                        <td>{{ucfirst(str_replace('_',' ',$val->module))}}</td>
                        <td>{{$val->updated_by}}</td>
                        <td>{{getDateFormat($val->updated_at,4)}}</td>
                        <td>
						@if($permission_arr['clear'] === true)
							<input type="text" class="txt_clear_time" id="txt_clear_time_{{$val->id}}" name="" value="{{$val->clear_time}}">
						@endif
						</td>
                        <td class="text-nowrap">
						@if($permission_arr['clear'] === true)
							<button class="btn btn-clear btn-danger" data-val="{{$val->module}}">@lang('admin_common.clear')</button>
						@endif	
						@if($permission_arr['update'] === true)		
							<button class="btn btn-primary clear_time" data-val="{{$val->id}}">@lang('admin_common.update')</button>
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
<script src="{{ Config('constants.admin_js_url') }}jquery.dataTables.js"></script>
<script src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
<script>
var clear_cache_url = "{{action('Admin\Config\CacheController@clearCache')}}";
var clear_time_url = "{{action('Admin\Config\CacheController@cacheTimeUpdate')}}";
$(document).ready(function() {
    $('#table').dataTable();
});
$('.btn-clear').click(function(e){
    var type = $(this).data('val');
    var data = {type:type};
    callAjaxRequest(clear_cache_url,"post",data,function(result){
        swal('success', result.msg, 'success');
    });
});

$('.clear_time').click(function(e){
    var id = $(this).data('val');
    var time = $('#txt_clear_time_'+id).val();
    var data = {id:id,time:time};
    callAjaxRequest(clear_time_url,"post",data,function(result){
        if(result.status=='success'){
            swal('success', result.msg, 'success');
        }else{
            swal('error', result.msg, 'error');
        }
        
    });
});
</script>
@stop
