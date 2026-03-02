@extends('layouts/admin/default')

@section('title')
    @lang('country.add_province_state')
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
        <form id="addCountryForm" action="{{ action('Admin\Country\ProvinceController@store') }}" method="post" enctype="multipart/form-data" class="form-horizontal form-bordered">
            {{ csrf_field() }}        
            <div class="header-title clearfix">
                <h1 class="title">@lang('country.add_province_state')</h1>
                <span class="float-right">
                    <a class="btn btn-back" href="{{ action('Admin\Country\ProvinceController@index') }}">@lang('common.back')</a>
                    <button type="submit" name="submit_type" value="save_and_continue" class="btn">@lang('common.save_and_continue')</button>
                    <button type="submit" name="submit_type" value="save" class="btn btn-save">@lang('common.save')</button>
                </span>
            </div>
            <div class="content-wrap">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="form-group @if($errors->has('country')) error @endif">
                            <label>@lang('country.select_country') <i class="strick">*</i></label>
                            <select name="country">
                                <option value="">--@lang('common.select')--</option>
                                @foreach($country_list as $key => $country_details)
                                    <option value="{{ $country_details->id }}" @if($country_details->id == $country_id) selected="selected" @endif>{{ $country_details->countryName->country_name }}</option>
                                @endforeach 
                            </select> 
                            @if ($errors->has('country'))
                                <p class="error error-msg">{{ $errors->first('country') }}</p>
                            @endif      
                        </div>                                            
                        <div class="form-group @if($errors->has('province_sn')) error @endif">
                            {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'province_state_name','label'=>Lang::get('country.province_state_name').' <i class="strick">*</i>',  'cssClass'=>'province_sn', 'errorkey'=>'province_sn']], '1', $errors) !!}                       
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
    
@stop
