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
        <form id="cmsForm" action="{{ action('Admin\Config\PaymentBankController@update', $bank_detail->id) }}" method="post" enctype="multipart/form-data">
            {{ method_field('PUT') }}
            {{ csrf_field() }}        
            <div class="header-title">
                <h1 class="title">@lang('admin_payment.edit_payment_option') : @if(isset($bank_detail->paymentBankName->bank_name)) {{$bank_detail->paymentBankName->bank_name}} @else {{'N/A'}} @endif</h1>
                <div class="float-right">
                    <a class="btn btn-back" href="{{ action('Admin\Config\PaymentBankController@index') }}">@lang('admin_common.back')</a>
                    <button type="submit" name="submit_type" value="submit" class="btn btn-primary">@lang('admin_common.update')</button>                    
                </div>
            </div>       
            <div class="content-wrap">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('config','bank')!!}
                    </ul>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>@lang('admin_common.status') <i class="strick">*</i></label>
                            <select name="status">
                                <option value="1" @if($bank_detail->status == '1') selected="selected" @endif>@lang('admin_common.active')</option>
                                <option value="0" @if($bank_detail->status == '0') selected="selected" @endif>@lang('admin_common.inactive')</option>
                            </select>
                        </div>
                        {{-- <div class="form-group">
                            <label>@lang('admin_payment.payment_option') <i class="strick">*</i></label>                            
                            <select name="payment_option_id">
                                <option value="">--@lang('admin_payment.select_payment_option')--</option>
                                {!! CustomHelpers::getOfflinePaymentOption($bank_detail->payment_option_id) !!}
                            </select>
                            @if ($errors->has('payment_option_id'))
                                <p class="error error-msg">{{ $errors->first('payment_option_id') }}</p>
                            @endif                            
                        </div>  --}} 
                        <div class="form-group">
                            <label>@lang('admin_payment.bank_image') </label> 
                            <div class="clearfix">
                                @if($bank_detail->bank_image)
                                    <img  width="200px"  src="{{ Config::get('constants.payment_bank_url').$bank_detail->bank_image }}" class="pull-left">
                                @endif
                                <input id="form-file-input" name="bank_image" type="file">
                            </div>
                        
                        </div>
                        <div class="form-group">
                            
                            {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'bank_name', 'label'=>Lang::get('payment.bank_name').' <i class="strick">*</i>', 'errorkey'=>'bnk_name']], '1', 'payment_bank_id', $bank_detail->id, $tblPaymentBankDesc, $errors) !!}
                      
                            @if ($errors->has('bank_name'))
                                <p class="error error-msg">{{ $errors->first('bank_name') }}</p>
                            @endif 
                        </div>  
                        {{--<div class="form-group">
                            <label>@lang('admin_payment.account_no') <i class="strick">*</i></label>                     
                            <input  name="account_no" type="text" value="{{ $bank_detail->account_no }}">
                            @if ($errors->has('account_no'))
                                <p class="error error-msg">{{ $errors->first('account_no') }}</p>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>@lang('admin_payment.account_name') <i class="strick">*</i></label> 
                            <input  name="account_name" type="text" value="{{ $bank_detail->account_name }}">
                            @if ($errors->has('account_name'))
                                <p class="error error-msg">{{ $errors->first('account_name') }}</p>
                            @endif
                        </div>--}}
                        <div class="form-group">
                            <label>@lang('admin_payment.bank_code')</label> 
                            <input name="bank_code" type="text" value="{{ $bank_detail->bank_code }}">
                            @if ($errors->has('bank_code '))
                                <p class="error error-msg">{{ $errors->first('bank_code') }}</p>
                            @endif
                            
                        </div>  
                        <div class="form-group">
                            <label>@lang('admin_payment.account_type') <i class="strick">*</i></label> 
                            <select name="account_type">
                                <option value="">@lang('admin_common.select')</option>
                                <option value="1" @if($bank_detail->account_type == '1') selected="selected" @endif>@lang('admin_payment.saving')</option>
                                <option value="2" @if($bank_detail->account_type == '2') selected="selected" @endif>@lang('admin_payment.current')</option>
                            </select>
                            @if ($errors->has('account_type'))
                            <p class="error error-msg">{{ $errors->first('account_type') }}</p>
                            @endif
                        </div>
                    </div> 
                </div>                                                      
            </div>
        </form>
    </div>
      
@stop

@section('footer_scripts')
     
@stop
