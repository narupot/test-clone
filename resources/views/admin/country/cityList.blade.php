@extends('layouts/admin/default')

@section('title')
    @lang('country.city_district_list')
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
            <h1 class="title">@lang('country.city_district_list')</h1>
            @if($permission_arr['add'] === true)
            <span class="float-right">
                <a class="btn" href="{{ action('Admin\Country\CityController@create') }}">
                    @lang('country.add_city_district')
                </a>
            </span>
            @endif
        </div>
        <div class="content-wrap">
            <div class="form-group row">
                <label class="col-sm-2" for="form-text-input">@lang('country.country'):</label>
                <div class="col-sm-3">
                    <select name="country" id="country">
                        <option value="">--@lang('common.select')--</option>
                        @foreach($country_list as $key => $country_details)
                            <option value="{{ $country_details->id }}" @if($country_details->id == $country_id) selected="selected" @endif>{{ $country_details->countryName->country_name }}</option>
                        @endforeach 
                    </select>
                </div>                
            </div>
            <div class="form-group row">
                <label class="col-sm-2" for="form-text-input">@lang('country.province_state'):</label>
                <div class="col-sm-3">
                    <select name="province" id="province">
                        <option value="">--@lang('common.select')--</option>
                        @foreach($province_list as $key => $province_details)
                            <option value="{{ $province_details->id }}" @if($province_details->id == $province_id) selected="selected" @endif>{{ $province_details->provinceName->province_state_name }}</option>
                        @endforeach 
                    </select>
                </div>                
            </div>
            <div class="form-group row" @if($country_id!='1') style="display: none;" @endif>
                <label class="col-sm-2" for="form-text-input">@lang('country.select_city_district'):</label>
                <div class="col-sm-3">
                    <select name="city"  id="city">
                        <option value="">--@lang('common.select')--</option>
                        @if(!empty($province_city_list)) 
                            @foreach($province_city_list as $key => $province_city_details)
                                <option value="{{ $province_city_details->id }}" @if($province_city_details->id == $city_id) selected="selected" @endif>{{ $province_city_details->cityName->city_district_name }}</option>
                            @endforeach
                        @endif 
                    </select> 
                </div>                
            </div>
            <div class="form-group row">
                @if(!empty($city_id))
                    <label class="col-sm-2" for="form-text-input"><b>@lang('country.sub_district_listing')</b></label>
                @else
                    <label class="col-sm-2" for="form-text-input"><b>@lang('country.city_district_listing')</b></label>                    
                @endif
            </div>                
            <table class="table table-bordered " id="table">
                <thead>
                    <tr class="filters">
                        <th>@lang('common.slno')</th>
                        @if(!empty($city_id))
                            <th>@lang('country.sub_district_name')</th>
                        @else
                            <th>@lang('country.city_district')</th>
                            <th>@lang('country.zip_code')</th>
                        @endif
                        <th>@lang('common.status')</th>
                        <th>@lang('common.actions')</th>
                    </tr>
                </thead>
                <tbody id="table_detail">
                @if(!empty($city_list))
                    @foreach ($city_list as $key => $city_detail)
                        <tr>
                            <td>{{ ++$key }}</td>
                            @if(!empty($city_id))
                                <td>{{ $city_detail->subDistrictName->sub_district_name }}</td>
                            @else
                                <td>{{ $city_detail->cityName->city_district_name }}</td>
                                <td>{{ $city_detail->zip }}</td>
                            @endif                            
                            @if($city_detail->status == '1')
                                <td>@lang('common.active')</td>
                            @else
                                <td>@lang('common.inactive')</td>
                            @endif                        
                            <td>
                            @if($permission_arr['edit'] === true)
                                <a class="btn" href="{{ action('Admin\Country\CityController@edit', $city_detail->id) }}?country={{ $country_id }}&province={{ $province_id }}&city={{ $city_id }}">
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

    var redirect_url = "{{ action('Admin\Country\CityController@index') }}";
    var ajax_url = "{{ action('Admin\Country\CityController@getProvinceList') }}";                
    </script>
    <script type="text/javascript" src="{{ Config('constants.admin_js_url') }}cityList.js"></script>
    <!-- end of page level js -->
    
@stop
