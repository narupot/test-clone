@extends('layouts/admin/default')

@section('title')
    @lang('country.add_city_district')
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
        <form action="{{ action('Admin\Country\CityController@store') }}" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}        
            <div class="header-title clearfix">
                <h1 class="title">@lang('country.add_city_district')</h1>
                <span class="float-right">
                    <a class="btn btn-back" href="{{ action('Admin\Country\CityController@index') }}">@lang('common.back')</a>
                    <button type="submit" name="submit_type" value="save_and_continue" class="btn">@lang('common.save_and_continue')</button>
                    <button type="submit" name="submit_type" value="save" class="btn btn-save">@lang('common.save')</button>
                </span>
            </div>
            <div class="content-wrap">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="form-group @if($errors->has('country')) error @endif">
                            <label>@lang('country.select_country') <i class="strick">*</i></label>
                            <select name="country" id="country">
                                <option value="">--@lang('common.select')--</option>
                                @foreach($country_list as $key => $country_details)
                                    <option value="{{ $country_details->id }}" @if($country_details->id == $country_id || $country_details->id == old('country')) selected="selected" @endif>{{ $country_details->countryName->country_name }}</option>
                                @endforeach 
                            </select> 
                            @if ($errors->has('country'))
                                <p class="error error-msg">{{ $errors->first('country') }}</p>
                            @endif                                       
                        </div> 
                        <div class="form-group @if($errors->has('province')) error @endif">
                            <label>@lang('country.select_province_state') <i class="strick">*</i></label>
                            <select name="province"  id="province">
                                @if(!empty($province_list)) 
                                    <option value="">--@lang('common.select')--</option>
                                    @foreach($province_list as $key => $province_details)
                                        <option value="{{ $province_details->id }}" @if($province_details->id == $province_id || $province_details->id == old('province')) selected="selected" @endif>{{ $province_details->provinceName->province_state_name }}</option>
                                    @endforeach
                                @elseif(!empty(old('country')))
                                    <option value="">--@lang('common.select')--</option>
                                    {!! CustomHelpers::getProvinceStateDD(old('country'), old('province')) !!}
                                @else 
                                    <option value="">--@lang('common.select')--</option>
                                @endif 
                            </select> 
                            @if ($errors->has('province'))
                                <p class="error error-msg">{{ $errors->first('province') }}</p>
                            @endif                                        
                        </div>
                        <div class="form-group" id="city_div_radio" 
                            @if(($country_id=='1' || old('country') == '1') && (!empty($province_id) || !empty(old('province')))) 
                            style="display: block;" 
                            @else 
                            style="display: none;" 
                            @endif >
                            <label></label>
                            <label class="col-md-2 radio-wrap">
                                <input type="radio" class="radio" name="district_type" id="district_type_1" value="1" checked="checked">
                                <span class="radio-label">@lang('country.create_district')</span>
                            </label>
                            <label class="mt-10 radio-wrap">
                                <input type="radio" class="radio" name="district_type" id="district_type_2" value="2" @if($district_type == '2' || old('district_type') == '2') checked="checked" @endif>
                                <span class="radio-label">@lang('country.create_sub_district')</span>                           
                            </label>
                        </div>                
                        <div class="form-group @if($errors->has('city')) error @endif" id="city_div" 
                            @if(($country_id=='1' || old('country') == '1') && ($district_type == '2' || old('district_type') == '2')) 
                            style="display: block;" 
                            @else 
                            style="display: none;" 
                            @endif >
                            <label>@lang('country.select_city_district')</label>
                            <select name="city"  id="city">
                                @if(!empty($city_list)) 
                                    <option value="">--@lang('common.select')--</option>
                                    @foreach($city_list as $key => $city_details)
                                        <option value="{{ $city_details->id }}" @if($city_details->id == $city_id || $city_details->id == old('city')) selected="selected" @endif>{{ $city_details->cityName->city_district_name }}</option>
                                    @endforeach
                                @elseif(old('district_type') == '2')
                                    <option value="">--@lang('common.select')--</option>
                                    {!! CustomHelpers::getCityDistrictDD(old('province'), old('city')) !!}
                                @else 
                                    <option value="">--@lang('common.select')--</option>
                                @endif 
                            </select>
                            @if ($errors->has('city'))
                                <p class="error error-msg">{{ $errors->first('city') }}</p>
                            @endif                                                              
                        </div>             
                        <div class="form-group @if($errors->has('city_dn')) error @endif">
                            {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'city_district_name','label'=>Lang::get('country.city_district_name').' <i class="strick">*</i>', 'cssClass'=>'city_dn', 'errorkey'=>'city_dn']], '1', $errors) !!}                       
                        </div>
                        {{--<div id="zip_div" class="form-group @if($errors->has('zip')) error @endif"
                            @if(($country_id=='1' || old('country') == '1') && ($district_type == '1' || old('district_type') == '1')) 
                            style="display: block;" 
                            @else 
                            style="display: none;" 
                            @endif >
                            <label>@lang('country.zip_code') <i class="strick">*</i></label>                            
                            <input name="zip" value="{{ old('zip') }}" class="form-control" type="text">
                            @if ($errors->has('zip'))
                                <p class="error error-msg">{{ $errors->first('zip') }}</p>
                            @endif                              
                               
                        </div>  --}} 
                        <div class="form-group" id="zip_div" class="form-group @if($errors->has('zip')) error @endif"
                            @if(($country_id=='1' || old('country') == '1') && ($district_type == '1' || old('district_type') == '1')) 
                            style="display: block;" 
                            @else 
                            style="display: none;" 
                            @endif >
                            <div class="row">
                                <div class="col-md-8 col-lg-6 input_fields_wrap">
                                    <label>@lang('admin_country.zip_code')</label>
                                    <div class="col"><a href="javascript:;" class="btn btn-primary add_field_button" style="margin-bottom: 5px;"><i class="fa fa-plus align-baseline"></i></a></div>
                                    <ui class="css-board"></ui>
                                </div>
                                @if ($errors->has('zip'))
                                    <p class="error error-msg">{{ $errors->first('zip') }}</p>
                                @endif 
                            </div>
                        </div>             
                        <div class="form-group">
                            <label>@lang('common.status')</label>
                            <select name="status">
                                <option value="1" selected="selected">@lang('common.active')</option>
                                <option value="0">@lang('common.inactive')</option>
                            </select>    
                        </div>
                    </div>
                </div>                                                    
            </div>
        </form>
    </div>
      
@stop

@section('footer_scripts') 
    <script>
    var select_opt = '<option value="">--{{ Lang::get('common.select') }}--</option>';
    var sub_dist_level_txt = "{{ Lang::get('country.sub_district_name') }}";
    var city_dist_level_txt = "{{ Lang::get('country.city_district_name') }}";
    var ajax_url_province_list = "{{ action('Admin\Country\CityController@getProvinceList') }}";
    var ajax_url_city_list = "{{ action('Admin\Country\CityController@getCityList') }}";             
    </script>
    <script type="text/javascript" src="{{ Config('constants.admin_js_url') }}cityAddEdit.js"></script>
    <script>
        $(document).ready(function() {
            var max_fields      = 10; //maximum input boxes allowed
            var wrapper         = $(".input_fields_wrap .css-board"); //Fields wrapper
            var add_button      = $(".add_field_button"); //Add button ID
            
            var x = 1; //initlal text box count
            $(add_button).click(function(e){ //on add input button click
                e.preventDefault();
                if(x < max_fields){ //max input box allowed
                    x++; //text box increment
                    $(wrapper).append('<div class="row"><div class="col-sm-1"><span class="ui-icon ui-icon-arrowthick-2-n-s mt10"></span></div><div class="col-sm-10"><input type="text" name="zip[]" style="margin-bottom: 10px;"/></div><div class="col-sm-1"><span class="ui-icon ui-icon-minusthick removeCss cursor-pointer mt10"></span></div></div>'); //add input box
                }
            });
            
            $(wrapper).on("click",".removeCss", function(e){ //user click on remove text
                e.preventDefault(); 
                $(this).parent().parent().remove();
            })
        });   
    </script>      
@stop
