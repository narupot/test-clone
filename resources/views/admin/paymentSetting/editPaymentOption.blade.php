@extends('layouts/admin/default')

@section('title')
    @lang('admin_payment.edit_payment_option')
@stop

@section('header_styles')
  
@stop

@section('content')
<div class="content">
    @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
        </div>
    @endif 
    <form id="cmsForm" action="{{ action('Admin\Config\PaymentOptionController@update', $pay_opt_detail->id) }}" enctype="multipart/form-data" method="post" class="form-horizontal form-bordered">
        {{ csrf_field() }}
        {{ method_field('PUT') }}        
        <div class="header-title">
            <h1 class="title">@lang('admin_payment.edit_payment_option') : @if(isset($pay_opt_detail->paymentOptName->payment_option_name)) {{$pay_opt_detail->paymentOptName->payment_option_name}} @else {{'N/A'}} @endif </h1>
            <div class="float-right">
                <a href="{{ action('Admin\Config\PaymentOptionController@index') }}" class="btn btn-back">@lang('admin_common.back')</a>
                <button type="submit" name="submit_type" value="submit" class="btn btn-primary">@lang('admin_common.update')</button>
            </div>
        </div>       
        <div class="content-wrap">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','payment')!!}
                </ul>
            </div> 
            <div class="form-group row">
                <div class="col-md-5">
                    <label>@lang('admin_common.status') <i class="strick">*</i></label>
                    <select name="status">
                        <option value="1" @if($pay_opt_detail->status == '1') selected="selected" @endif>@lang('admin_common.active')</option>
                        <option value="0" @if($pay_opt_detail->status == '0') selected="selected" @endif>@lang('admin_common.inactive')</option>
                    </select>
                </div>
            </div>                
            <div class="form-group row">
                <div class="col-md-5">
                    <label>@lang('admin_payment.payment_type') <i class="strick">*</i></label>                     
                    <select name="payment_type">
                        <option value="">--@lang('admin_payment.select_type')--</option>
                        <option value="1" @if($pay_opt_detail->payment_type == '1' || old(@payment_type) == '1') selected="selected" @endif>@lang('admin_payment.online')</option>
                        <option value="2" @if($pay_opt_detail->payment_type == '2' || old(@payment_type) == '2') selected="selected" @endif>@lang('admin_payment.offline')</option>
                    </select>
                    @if ($errors->has('payment_type'))
                        <p class="error error-msg">{{ $errors->first('payment_type') }}</p>
                    @endif 
                </div>
            </div> 
            <!-- <div class="form-group row">
                <div class="col-md-5">
                    <label>@lang('admin_payment.payment_currency') <i class="strick">*</i></label> 
                    <select name="currency_id[]" multiple="">
                        <option value="">--@lang('admin_payment.select_currency')--</option>
                        <option value="all">@lang('admin_payment.all')</option>
                        {!! CustomHelpers::getCurrencyDorpDown('', explode(',', $pay_opt_detail->currency_id)) !!}
                    </select>
                    @if ($errors->has('currency_id'))
                        <p class="error error-msg">{{ $errors->first('currency_id') }}</p>
                    @endif
                </div>
            </div> -->
            @if($pay_opt_detail->slug != 'paypal')
                <div class="form-group row">
                    <div class="col-md-5">
                        <label>@lang('admin_payment.payment_option_image') <i class="strick">*</i></label> 
                        <img src="{{ Config::get('constants.payment_option_url').$pay_opt_detail->image_name }}"> 
                        <input id="form-file-input" name="image_name" type="file">
                    </div>
                </div>
            @endif
            <div class="form-group row">
                <div class="col-md-5">
                    <label>@lang('admin_common.title') <i class="strick">*</i></label>
                
                     {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'textarea', 'name'=>'payment_option_name','cssClass'=>'froala-editor-apply', 'label'=>' <i class="strick"></i>', 'errorkey'=>'option_name']], '1', 'payment_option_id', $pay_opt_detail->id, $tblPaymentOptionDesc, $errors) !!}
                    
                </div>
            </div>

            @if(count($field_name))
                <div class="form-group row">
                    <div class="col-md-5">
                        <label>@lang('admin_payment.detail') <i class="strick">*</i></label>
                        <ul class="nav nav-tabs lang-nav-tabs">
                            <li class="tablang_11">
                                <a data-toggle="tab" class="active" href="#live">@lang('admin_payment.live')</a>
                            </li>
                            <li class=" tablang_12">
                                <a data-toggle="tab" href="#sandbox">@lang('admin_payment.sandbox')</a>
                            </li>
                        </ul>
                        <div class="tab-content language-tab">
                            <div id="live" class="tab-pane fade show active">
                            @foreach($field_name as $key => $value)
                                <div class="form-group">
                                    <label>{{$value}}<i class="strick">*</i></label> 
                                    <input type="hidden" name="field_name[]" value="{{$value}}">
                                    <input type="text" class="form-control" name="live[{{$value}}]" value="{{isset($live_detail[$value])?$live_detail[$value]:''}}"/>
                                </div>
                            @endforeach
                            </div>
                            <div id="sandbox" class="tab-pane fade in ">
                            @foreach($field_name as $key => $value)
                                <div class="form-group">
                                    <label>{{$value}}<i class="strick">*</i></label>
                                    <input type="text" class="form-control" name="sandbox[{{$value}}]" value="{{isset($sandbox_detail[$value])?$sandbox_detail[$value]:''}}"/>
                                </div>
                            @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </form>
</div>
@stop

@section('footer_scripts')
      
@stop
