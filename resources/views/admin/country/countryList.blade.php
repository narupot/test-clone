@extends('layouts/admin/default')

@section('title')
    @lang('country.country_list')
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
            <h1 class="title">@lang('country.country_list')</h1>
            @if($permission_arr['add'] === true)
            <span class="float-right">
                <a class="btn" href="{{ action('Admin\Country\CountryController@create') }}">
                    @lang('country.add_country')
                </a>
            </span>
            @endif
        </div>
        <div class="content-wrap">                                                
            <table class="table table-bordered " id="table">
                <thead>
                    <tr class="filters">
                        <th>@lang('common.slno')</th>
                        <th>@lang('country.flag')</th>
                        <th>@lang('country.country')</th>
                        <th>@lang('country.code')</th>
                        <th>@lang('country.short_code')</th>
                        <th>@lang('country.isd_code')</th>
                        <th>@lang('common.default')</th>
                        <th>@lang('common.status')</th>
                        <th>@lang('common.actions')</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($country_list as $key => $country_details)
                    <tr>
                        <td>{{ ++$key }}</td>
                        <td><img src="{{ Config::get('constants.country_flag_url').$country_details->country_flag }}"></td>
                        <td>{{ $country_details->countryName->country_name }}</td>
                        <td>{{ $country_details->country_code }}</td>
                        <td>{{ $country_details->short_code }}</td>
                        <td>{{ $country_details->country_isd }}</td>
                        @if($country_details->is_default == '1')
                            <td>@lang('common.yes')</td>
                        @else
                            <td>@lang('common.no')</td>
                        @endif

                        @if($country_details->status == '1')
                            <td>@lang('common.active')</td>
                        @else
                            <td>@lang('common.inactive')</td>
                        @endif                        
                        <td>
                        @if($permission_arr['edit'] === true)
                            <a class="btn" href="{{ action('Admin\Country\CountryController@edit', $country_details->id) }}">
                               @lang('common.edit')
                            </a>
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
    <script type="text/javascript" src="{{ Config('constants.admin_js_url') }}jquery.dataTables.js"></script>
    <script type="text/javascript" src="{{ Config('constants.admin_js_url') }}dataTables.bootstrap.js"></script>
    <script>
    $(document).ready(function() {
        $('#table').dataTable();
    });
    </script>
    <!-- end of page level js -->
    
@stop
