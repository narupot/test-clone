@extends('layouts/admin/default')

@section('title')
    @lang('country.province_state_list')
@stop

@section('header_styles')
    <!--page level css -->
    <link rel="stylesheet" type="text/css" href="{{ Config('constants.admin_css_url') }}dataTables.bootstrap.css" />
    <!-- end of page level css -->    
@stop

@section('content')

    <!-- Right side column. Contains the navbar and content of the page -->
    <div class="content">
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
        <div class="header-title clearfix">
            <h1 class="title">@lang('country.province_state_list')</h1>
            @if($permission_arr['add'] === true)
            <span class="float-right">
                <a class="btn" href="{{ action('Admin\Country\ProvinceController@create') }}">
                    @lang('country.add_province_state')
                </a>
            </span>
            @endif
        </div>
        <div class="content-wrap">
            <div class="form-group row">
                <div class="col-sm-3">
                    <select name="country" id="country">
                        <option value="">--@lang('common.select')--</option>
                        @foreach($country_list as $key => $country_details)
                            <option value="{{ $country_details->id }}" @if($country_details->id == $country_id) selected="selected" @endif>{{ $country_details->countryName->country_name }}</option>
                        @endforeach 
                    </select>
                </div>                
            </div>                                                
            <table class="table table-bordered " id="table">
                <thead>
                    <tr class="filters">
                        <th>@lang('common.slno')</th>
                        <th>@lang('country.province_state')</th>
                        <th>@lang('common.status')</th>
                        <th>@lang('common.actions')</th>
                    </tr>
                </thead>
                <tbody id="table_detail">
                @if(count($province_list) > 0)
                    @foreach ($province_list as $key => $province_detail)
                        <tr>
                            <td>{{ ++$key }}</td>
                            <td>{{ $province_detail->provinceName->province_state_name }}</td>
                            @if($province_detail->status == '1')
                                <td>@lang('common.active')</td>
                            @else
                                <td>@lang('common.inactive')</td>
                            @endif                        
                            <td>
                            @if($permission_arr['edit'] === true)
                                <a class="btn" href="{{ action('Admin\Country\ProvinceController@edit', $province_detail->id) }}?country={{ $country_id }}">
                                   @lang('common.edit')
                                </a>
                            @endif                                        
                            </td>
                        </tr>
                     @endforeach 
                @endif
                </tbody>
            </table>
        </div>
    </div>    
@stop

@section('footer_scripts')

    <!-- begining of page level js -->
    <script type="text/javascript" src="{{ Config('constants.admin_js_url') }}jquery.dataTables.js"></script>
    <script type="text/javascript" src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
    <script>
    $(document).ready(function() {
        $('#table').dataTable();
    });

    $('body').on('change','#country',function(){

        var country_id = $(this).val();
        if(country_id > 0) {
            var redirect_url = '{{ action('Admin\Country\ProvinceController@index') }}?country='+country_id;
            window.location = redirect_url;
        }
    });         
    </script>
    <!-- end of page level js -->
    
@stop
