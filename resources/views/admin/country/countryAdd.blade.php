@extends('layouts/admin/default')

@section('title')
    @lang('country.add_country')
@stop

@section('header_styles')
    
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
        <form id="addCountryForm" action="{{ action('Admin\Country\CountryController@store') }}" method="post" enctype="multipart/form-data" class="form-horizontal form-bordered">
            {{ csrf_field() }}        
            <div class="header-title clearfix">
                <h1 class="title">@lang('country.add_country')</h1>
                <span class="float-right">
                    <a href="{{ action('Admin\Country\CountryController@index') }}" class="btn btn-back">@lang('common.back')</a>
                    <button type="submit" class="btn btn-save">@lang('common.submit')</button>
                </span>
            </div>
            <div class="content-wrap">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group @if($errors->has('country_flag')) error @endif">
                            <label>@lang('country.country_flag') <i class="strick">*</i></label>                                           
                            <input id="form-file-input" name="country_flag" type="file">
                            @if($errors->has('country_flag'))
                                <p class="error error-msg">{{ $errors->first('country_flag') }}</p>
                            @endif                         
                        </div>                                
                        <div class="form-group @if($errors->has('country_nm')) error @endif">
                            {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'country_name','label'=>Lang::get('country.country_name').' <i class="strick">*</i>', 'cssClass'=>'country_nm', 'errorkey'=>'country_nm']], '1', $errors) !!}
                        </div>
                        <div class="form-group @if($errors->has('ps_header')) error @endif">
                            {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'province_state_header','label'=>Lang::get('country.province_state_header').' <i class="strick">*</i>', 'cssClass'=>'ps_header', 'errorkey'=>'ps_header']], '1', $errors) !!}       
                        </div>                
                        <div class="form-group @if($errors->has('cd_header')) error @endif">
                            {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'city_district_header', 'label'=>Lang::get('country.city_district_header').' <i class="strick">*</i>', 'cssClass'=>'cd_header', 'errorkey'=>'cd_header']], '1', $errors) !!}                     
                        </div>
                        <div class="form-group  @if($errors->has('sd_header')) error @endif">
                            {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'sub_district_header', 'label'=>Lang::get('country.sub_district_header').' <i class="strick">*</i>', 'cssClass'=>'sd_header', 'errorkey'=>'sd_header']], '1', $errors) !!}                         
                        </div>                                
                        <div class="form-group @if($errors->has('country_code')) error @endif">
                            <label>@lang('country.country_code') <i class="strick">*</i></label>
                            <input type="text" name="country_code" value="{{ old('country_code') }}" class="form-control" placeholder="@lang('country.eg_ndia_country_code_is_ind')">
                            @if($errors->has('country_code'))
                                <p class="error error-msg">{{ $errors->first('country_code') }}</p>
                            @endif   
                        </div>
                        <div class="form-group @if($errors->has('short_code')) error @endif">
                            <label>@lang('country.country_short_code') <i class="strick">*</i></label> 
                            <input type="text" name="short_code" value="{{ old('short_code') }}" class="form-control" placeholder="@lang('country.eg_india_country_short_code_is_in')">
                            @if($errors->has('short_code'))
                                <p class="error error-msg">{{ $errors->first('short_code') }}</p>
                            @endif                
                        </div>                                
                        <div class="form-group @if($errors->has('country_isd')) error @endif">
                            <label>@lang('country.isd_code') <i class="strick">*</i></label>                             
                            <input type="text" name="country_isd" value="{{ old('country_isd') }}" class="form-control" placeholder="@lang('country.eg_india_isd_code_is') 91">
                            @if($errors->has('country_isd'))
                                <p class="error error-msg">{{ $errors->first('country_isd') }}</p>
                            @endif   
                        </div>
                        <div class="form-group">
                            <label>@lang('common.status')</label>
                            <select name="status">
                                <option value="1" selected="selected">@lang('common.active')</option>
                                <option value="0">@lang('common.inactive')</option>
                            </select>
                        </div>                
                        <div class="form-group">
                            <label>@lang('common.is_default')</label>
                            <div class="radio-group">
                                <label class="radio-wrap">
                                    <input type="radio" id="form-inline-radio1" class="radio-blue" name="is_default" value="1">
                                    <span class="radio-label "> @lang('common.yes')</span>
                                </label>
                                <label class="radio-wrap">
                                     <input type="radio" id="form-inline-radio2" class="radio-blue" name="is_default" value="0" checked>
                                    <span class="radio-label ">@lang('common.no')</span>
                                </label>
                            </div>
                        </div>                                 
                        <div class="form-group btns-group">  
                            <a class="btn btn-back" href="{{ action('Admin\Country\CountryController@index') }}">@lang('common.back')</a>                          
                            <button type="submit" class="btn btn-save">@lang('common.submit')</button>                                                        
                        </div>
                    </div>
                </div>    
            </div>
        </form>
    </div>
      
@stop

@section('footer_scripts')
 
    <!-- for file upload ended -->    
    <script src="{{ Config('constants.admin_js_url') }}bootstrapValidator.min.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}lang/{{ session('lang_code') }}.lang.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}validateCountry.js"></script>      
    <!-- end of page level js -->  
    
@stop
