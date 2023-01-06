@extends('layouts/admin/default')

@section('title')
    @lang('admin_common.create_block')
@stop

@section('header_styles')
<link rel="stylesheet" type="text/css" href="{{Config('constants.public_url')}}/angular-froala/bower_components/font-awesome/css/font-awesome.min.css">

@stop

@section('content')
    <div class="content">
        @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
        </div>
        @endif 
        <form id="cmsForm" action="{{ action('Admin\Config\PaymentOptionController@store') }}" method="post" class="" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="header-title">
                <h1 class="title">@lang('admin_payment.add_payment_option')</h1>
                <div class="float-right">                    
                    <a href="{{ action('Admin\Config\PaymentOptionController@index') }}" class="btn btn-back">@lang('admin_common.back')</a>
                    <button type="submit" name="submit_type" value="submit_continue" class="btn">@lang('admin_common.save_and_continue')</button>
                    <button type="submit" name="submit_type" value="submit" class="btn btn-save">@lang('admin_common.save')</button>
                </div>
            </div>       
            <div class="content-wrap">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('config','payment')!!}
                    </ul>
                </div> 
                <div class="row">
                    <div class="col-sm-5">                       
                    
                        <div class="form-group">
                            <label>@lang('admin_common.status') <i class="strick">*</i></label>
                            <select name="status">
                                <option value="1" @if(old('status') == '1') selected="selected" @endif>@lang('admin_common.active')</option>
                                <option value="0" @if(old('status') == '0') selected="selected" @endif>@lang('admin_common.inactive')</option>
                            </select>
                        </div>            
                        <div class="form-group">
                            <label>@lang('admin_payment.payment_type') <i class="strick">*</i></label> 
                          
                            <select name="payment_type">
                                <option value="">--@lang('admin_common.type')--</option>
                                <option value="1" @if(old(@payment_type) == '1') selected="selected" @endif>@lang('admin_payment.online')</option>
                                <option value="2" @if(old(@payment_type) == '2') selected="selected" @endif>@lang('admin_payment.offline')</option>
                            </select>
                            @if ($errors->has('payment_type'))
                                <p class="error error-msg">{{ $errors->first('payment_type') }}</p>
                            @endif 
                           
                        </div> 
                        <div class="form-group">
                            <label>@lang('admin_payment.payment_currency') <i class="strick">*</i></label>
                            <select name="currency_id[]" multiple="">
                                <option value="">--@lang('admin_payment.select_currency')--</option>
                                <option value="all">@lang('admin_payment.all')</option>
                                {!! CustomHelpers::getCurrencyDorpDown() !!}
                            </select>
                            @if ($errors->has('currency_id'))
                                <p class="error error-msg">{{ $errors->first('currency_id') }}</p>
                            @endif
                        </div>

                        <div class="form-group">
                            <label>@lang('admin_payment.payment_option_image') <i class="strick">*</i></label>                            
                            <input id="form-file-input" name="image_name" type="file">                            
                        </div>

                        <div class="form-group">                            
                            {!! CustomHelpers::fieldstabWithLanuage([['field'=>'textarea', 'name'=>'payment_option_name','cssClass'=>'froala-editor-apply', 'label'=>Lang::get('common.title').' <i class="strick">*</i>', 'errorkey'=>'option_name']], '1', $errors) !!}                         
                        </div>   
                    </div>
                </div>                                                      
            </div>
        </form>
    </div>
      
@stop

@section('footer_scripts')
@include('includes.froalaeditor_dependencies')
<script src="{{asset('js/normal_froala_editor_setting.js')}}"></script>  
@stop
