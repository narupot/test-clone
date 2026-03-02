@extends('layouts/admin/default')

@section('title')
    @lang('admin_payment.add_payment_bank')
@stop

@section('header_styles')

@stop

@section('content')
    <div class="content">
        @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('common.success'):</strong> {{ Session::get('succMsg') }}
        </div>
        @endif 
        <form id="cmsForm" action="{{ action('Admin\Config\PaymentBankController@store') }}" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="header-title">
                <h1 class="title">@lang('admin_payment.add_payment_bank')</h1>
                <div class="float-right">
                    <a href="{{ action('Admin\Config\PaymentBankController@index') }}" class="btn btn-back">@lang('common.back')</a>
                    <button type="submit" name="submit_type" value="submit_continue" class="btn btn-secondary">@lang('common.save_and_continue')</button>
                    <button type="submit" name="submit_type" value="submit" class="btn btn-save">@lang('common.save')</button>                    
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
                            <label>@lang('common.status') <i class="strick">*</i></label> 
                            <select name="status">
                                <option value="1" @if(old('status') == '1') selected="selected" @endif>@lang('common.active')</option>
                                <option value="0" @if(old('status') == '0') selected="selected" @endif>@lang('common.inactive')</option>
                            </select>
                        </div>

                        {{-- <div class="form-group">
                            <label>@lang('admin_payment.payment_option') <i class="strick">*</i></label>
                            <select name="payment_option_id">
                                <option value="">--@lang('admin_payment.select_payment_option')--</option>
                                {!! CustomHelpers::getOfflinePaymentOption() !!}
                            </select>
                            @if ($errors->has('payment_option_id'))
                                <p class="error error-msg">{{ $errors->first('payment_option_id') }}</p>
                            @endif 
                        </div>  --}}

                        <div class="form-group">
                            <label>@lang('admin_payment.bank_image') 
                            </label> 
                            <input id="form-file-input" name="bank_image" type="file">
                        </div>

                        <div class="form-group">
                            <label>@lang('admin_payment.bank_name') <i class="strick">*</i></label>
                            {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'bank_name', 'label'=>'', 'errorkey'=>'bnk_name']], '1', $errors) !!}
                        </div>    

                        {{--<div class="form-group">
                            <label>@lang('admin_payment.account_no') <i class="strick">*</i></label> 
                            <input  name="account_no" type="text">
                            @if ($errors->has('account_no'))
                                <p class="error error-msg">{{ $errors->first('account_no') }} </p>
                            @endif
                        </div>

                        <div class="form-group">
                            <label>@lang('admin_payment.account_name') <i class="strick">*</i></label>                            
                            <input  name="account_name" type="text">
                            @if ($errors->has('account_name'))
                                <p class="error error-msg">{{ $errors->first('account_name') }} </p>
                            @endif                           
                        </div>--}}

                        <div class="form-group">
                            <label>@lang('admin_payment.bank_code') </label>
                            <input name="bank_code" type="text">
                            @if ($errors->has('bank_code'))
                                <p class="error error-msg">{{ $errors->first('bank_code') }}</p>
                            @endif
                        </div>     

                        {{--<div class="form-group">
                            <label>@lang('admin_payment.account_type') <i class="strick">*</i></label>
                            <select name="account_type">
                                <option value="">@lang('common.select')</option>
                                <option value="1">@lang('admin_payment.saving')</option>
                                <option value="2">@lang('admin_payment.current')</option>
                            </select>
                            @if ($errors->has('account_type'))
                                <p class="error error-msg">{{ $errors->first('account_type') }} </p>
                            @endif
                        </div>--}}
                    </div>
                </div>                                                 
            </div>
        </form>
    </div>   
@stop

@section('footer_scripts')
       
@stop
