@extends('layouts.app') 

@section('header_style')

@endsection

@section('breadcrumbs')
<!-- Breadcrum section start -->
@stop

@section('content')

@if(Session::has('verify_msg'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    {{Session::get('verify_msg')}}
</div>
@endif

@if(Session::has('not_verify_msg')) 
    <div class="alert alert-danger alert-dismissable margin5"> 
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button> 
        {{Session::get('not_verify_msg')}} 
    </div> 
@endif

<div class="" id="forget_form_div">
    <h1>@lang('auth.verify_account')</h1>
    <div class="forget-help">
        @lang('auth.enter_your_email_or_phone_no_into_the_space_the_system_will_send_you_a_code_to_verify_your_account')
    </div>
    <div class="row">
        <form id="pageforgetForm" name="forgetForm" method="POST" action="{{ action('Auth\ForgotPasswordController@sendResetLinkEmail') }}"class="col-sm-6">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="mb-3">@lang('auth.login_information_as_well') <i class="red">*</i></label>
                <label class="radio-wrap" @if($use=='ph_no') style="display: none;" @endif>
                    <input type="radio" value="email" name="find_by_use" id="email-chk" @if(!$use or $use=='email') checked="checked" @endif>
                    <span class="radio-mark">@lang('auth.email')</span>
                </label>
                <label class="radio-wrap" @if($use=='email') style="display: none;" @endif>
                    <input type="radio" value="ph_no" name="find_by_use" id="phone-chk" @if($use=='ph_no') checked="checked" @endif>
                    <span class="radio-mark">@lang('auth.phone_no').</span>
                </label>
            </div>
            <div class="form-group" id="find_by_email" @if($use=='ph_no') style="display: none;" @endif>
                <label>@lang('auth.email') <i class="red">*</i></label>
                <input  type="text" name="email" id="emailForget" value="{{$useval}}" placeholder="">
            </div>
            <div class="form-group" id="find_by_ph_no" @if($use !='ph_no') style="display: none;" @endif>
                <label>@lang('auth.phone_no')<i class="red">*</i></label>
                <input type="text" name="phone_no" value="{{$useval}}" id="phoneForget" placeholder="">
            </div>
            <input type="hidden" name="post_from" value="page">
            <div class="text-right">
                <button type="button" id="btn_forget" class="btn">@lang('common.send')</button>
            </div>
        </form>
    </div>
</div>

<div id="otp_form_div" style="display: none;">
    <div class="login-wrap">                
        <div class="login-content text-center">
            <div class="form-group register-otp">
                <label class="skyblue">@lang('auth.please_enter_the_4_digit_code_received_from_otp_email').</label>
                <input type="text" id="confirm_otp" value="">
                <input type="hidden" name="" id="request_from" value="page">
            </div>   
            <div class="form-group">
                <button type="button" id="btn_otp_request" class="btn-grey mb-3">@lang('auth.request_otp')</button> 
                <button type="button" id="btn_otp_confirm" class="btn">@lang('common.confirm')</button>
            </div>                   
                
        </div>
    </div>
</div>

@include('auth.thanks')

@endsection 

@section('footer_scripts') 

@stop