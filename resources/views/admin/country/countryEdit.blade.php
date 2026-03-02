@extends('layouts/admin/default')

@section('title')
    @lang('country.edit_country')
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
        <form id="editCountryForm" action="{{ action('Admin\Country\CountryController@update', $country_detail->id) }}" method="post" enctype="multipart/form-data" class="form-horizontal">
            {{ csrf_field() }}
            {{ method_field('PUT') }}       
            <div class="header-title clearfix">
                <h1 class="title">@lang('country.edit_country') :  @if(isset($country_detail->countryName->country_name)) {{$country_detail->countryName->country_name}} @else {{'N/A'}} @endif </h1>
                <span class="float-right btns-group">
                    <a href="{{ action('Admin\Country\CountryController@index') }}" class="btn btn-back">@lang('common.back')</a>
                    <button type="submit" class="btn">@lang('common.update')</button>
                </span>
            </div>
            <div class="content-wrap"> 
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group @if($errors->has('country_flag')) error @endif">
                            <label>@lang('country.country_flag') <i class="strick">*</i></label>                                               
                            <img src="{{ Config::get('constants.country_flag_url').$country_detail->country_flag }}">
                            <input id="form-file-input" name="country_flag" type="file">
                            @if($errors->has('country_flag'))
                                <p class="error error-msg">{{ $errors->first('country_flag') }}</p>
                            @endif
                        </div>                                
                        <div class="form-group @if($errors->has('country_nm')) error @endif">                           
                            {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'country_name','label'=>Lang::get('country.country_name').' <i class="strick">*</i>', 'cssClass'=>'country_nm', 'errorkey'=>'country_nm']], '1', 'country_id', $country_detail->id, $tblCountryDesc, $errors) !!}                            
                        </div>
                        <div class="form-group @if($errors->has('ps_header')) error @endif">                            
                            {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'province_state_header', 'label'=>Lang::get('country.province_state_header').' <i class="strick">*</i>', 'cssClass'=>'ps_header', 'errorkey'=>'ps_header']], '1', 'country_id', $country_detail->id, $tblCountryDesc, $errors) !!} 
                        </div>
                        <div class="form-group @if($errors->has('cd_header')) error @endif">
                            {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'city_district_header','label'=>Lang::get('country.city_district_header').' <i class="strick">*</i>', 'cssClass'=>'cd_header', 'errorkey'=>'cd_header']], '1', 'country_id', $country_detail->id, $tblCountryDesc, $errors) !!}
                        </div>
                        <div class="form-group @if($errors->has('sd_header')) error @endif">                           
                            {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'sub_district_header', 'label'=>Lang::get('country.sub_district_header').' <i class="strick">*</i>', 'cssClass'=>'sd_header', 'errorkey'=>'sd_header']], '1', 'country_id', $country_detail->id, $tblCountryDesc, $errors) !!}     
                        </div>
                        <div class="form-group @if($errors->has('country_code')) error @endif">
                            <label>@lang('country.country_code') <i class="strick">*</i></label>                            
                            <input type="text" name="country_code" value="{{ $country_detail->country_code }}" class="form-control" placeholder="@lang('country.eg_ndia_country_code_is_ind')">
                            @if($errors->has('country_code'))
                                <p class="error error-msg">{{ $errors->first('country_code') }}</p>
                            @endif   
                        </div>
                        <div class="form-group @if($errors->has('short_code')) error @endif">
                            <label>@lang('country.country_short_code') <i class="strick">*</i></label>                         
                            <input type="text" name="short_code" value="{{ $country_detail->short_code }}" class="form-control" placeholder="@lang('country.eg_india_country_short_code_is_in')">
                            @if($errors->has('short_code'))
                                <p class="error error-msg">{{ $errors->first('short_code') }}</p>
                            @endif  
                        </div>                                
                        <div class="form-group @if($errors->has('country_isd')) error @endif">
                            <label>@lang('country.isd_code') <i class="strick">*</i></label> 
                            <input type="text" name="country_isd" value="{{ $country_detail->country_isd }}" class="form-control" placeholder="@lang('country.eg_india_isd_code_is') 91">
                            @if($errors->has('country_isd'))
                                <p class="error error-msg">{{ $errors->first('country_isd') }}</p>
                            @endif   
                        </div>
                        <div class="form-group">
                            <label>@lang('common.is_default')</label>
                            <div class="radio-inline">
                                <label class="radio-wrap">
                                    <input type="radio" id="form-inline-radio1" class="radio-blue" name="is_default" value="1" @if($country_detail->is_default == '1') checked @endif> <span class="radio-label">@lang('common.yes')</span></label>
                                <label class="radio-wrap mt-10">
                                    <input type="radio" id="form-inline-radio2" class="radio-blue" name="is_default" value="0" @if($country_detail->is_default == '0') checked @endif> <span class="radio-label">@lang('common.no')</span></label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('common.status')</label>                            
                            <select name="status">
                                <option value="1" @if($country_detail->status == '1') selected="selected" @endif>@lang('common.active')</option>
                                <option value="0" @if($country_detail->status == '0') selected="selected" @endif>@lang('common.inactive')</option>
                            </select>                                 
                        </div>                                                  
                        <div class="form-group btns-group">                    
                            <a class="btn btn-back" href="{{ action('Admin\Country\CountryController@index') }}">@lang('common.back')</a>
                            <button type="submit" class="btn">@lang('common.update')</button>                        
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
      
@stop

@section('footer_scripts')
 
    <!-- begining of page level js -->
    <script src="{{ Config('constants.admin_js_url') }}bootstrapValidator.min.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}lang/{{ session('lang_code') }}.lang.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}validateCountry.js"></script>
    <!-- end of page level js -->   
@stop
