@extends('layouts.app') 

@section('header_style')

@endsection

@section('breadcrumbs')
<!-- Breadcrum section start -->
<div class="container">
    <div class="breadcrumb">
        <ul class="bredcrumb-menu">
            {!! $breadcrumb !!}
            <li>{{'Login'}}</li>
        </ul>
    </div>
</div>
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

<div class="login-wrapper" id="login_form_div">
    <div class="row login-wrap">
        <div class="col-sm-6">
            <div class="login-banner">
                <img src="images/login/login-banner.jpg" alt="">
            </div>
        </div>
        <div class="col-sm-6 login-content">
            <h1 class="login-title">@lang('auth.signin')</h1>
            <form class="userlogin formone-size" id="pageloginForm" name="loginForm" method="POST" action="{{ route('userlogin') }}">
                {{ csrf_field() }}
                <span id="login-error"></span>
                <span class="error" id="verify_div"></span>
                <div class="form-group">
                    <label>@lang('auth.email_phone_no')</label>
                    <input type="text" name="login_email_phone" id="login_email_phone" value="">
                </div>

                <div class="form-group">
                    <label>@lang('auth.password')</label>
                    <input type="password" name="login_password" id="login_password">
                </div>

                <div class="form-group">
                    <a href="javascript:;" id="link_forget_password" class="forget-link">@lang('auth.forget_password')</a>
                </div>
                
                <div class="form-group">
                    <button type="button" class="btn btn_login">@lang('auth.signin')</button>
                </div>
                @if(!empty(getConfigValue('FACEBOOK_CLIENT_ID')) && !empty(getConfigValue('FACEBOOK_CLIENT_SECRET')))
                <div class="form-group">
                    <div class="or-wrap">
                        <span class="or">
                            <span> @lang('auth.or') </span>
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    <a href="javascript:;" class="fb-btn btn_login"> <i class="fab fa-facebook"></i> @lang('auth.login_with_facebook')</a>
                </div>
                @endif
            </form>
        </div>
    </div>

</div>

<div class="" id="forget_form_div" style="display: none;">
    <h1>@lang('auth.forget_password')?</h1>
    <div class="forget-help">
        @lang('auth.enter_your_email_or_phone_no_into_the_space_the_system_will_send_you_a_code_to_change_your_password')
    </div>
    <div class="row">
        <form id="pageforgetForm" name="forgetForm" method="POST" action="{{ action('Auth\ForgotPasswordController@sendResetLinkEmail') }}" class="col-sm-6">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="mb-3">@lang('auth.login_information_as_well') <i class="red">*</i></label>
                <label class="radio-wrap" >
                    <input type="radio" value="email" name="find_by_use" id="email-chk" checked="checked">
                    <span class="radio-mark">@lang('auth.email')</span>
                </label>
                <label class="radio-wrap">
                    <input type="radio" value="ph_no" name="find_by_use" id="phone-chk">
                    <span class="radio-mark">@lang('auth.phone_no').</span>
                </label>
            </div>
            <div class="form-group" id="find_by_email">
                <label>@lang('auth.email') <i class="red">*</i></label>
                <input  type="text" name="email" id="emailForget" value="" placeholder="">
            </div>
            <div class="form-group" id="find_by_ph_no" style="display: none;">
                <label>@lang('auth.phone_no')<i class="red">*</i></label>
                <input type="text" name="phone_no" id="phoneForget" placeholder="">
            </div>
            <input type="hidden" name="post_from" value="page">
            <div class="text-right">
                <a class="btn-grey" onclick="goBack()">@lang('common.back')</a>
                <button type="button" id="btn_forget" class="btn">@lang('common.send')</button>
            </div>
        </form>
    </div>
</div>

<div id="otp_form_div" style="display: none;">
    <div class="login-wrap">                
        <div class="login-content text-center">
            <div class="form-group register-otp">
                <label class="skyblue" id="otp_msg">@lang('auth.please_enter_the_4_digit_code_received_from_otp_email').</label>
                <input type="text" id="confirm_otp" value="">
                <input type="hidden" name="" id="request_from" value="page">
            </div>   
            <div class="form-group">
                <button type="button" id="btn_otp_request" class="btn-grey mb-3">@lang('customer.resend_code')</button> 
                <button type="button" id="btn_otp_confirm" class="btn">@lang('common.confirm')</button>
            </div>                   
                
        </div>
    </div>
</div>

<div id="pwd_form_div" style="display: none;">
    <div class="login-wrap">                
        <div class="login-content">
            <h1 class="login-title">@lang('auth.set_a_new_password')</h1>
            <div class="row">
                <form method="post" action="{{ action('Auth\ResetPasswordController@resetPasswordPhone') }}" class="col-sm-6" id="resetPwdForm">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label>@lang('auth.new_password')<i class="red">*</i></label>
                        <input type="password" name="password">
                        <p class="error"></p>
                    </div>
                    <div class="form-group">
                        <label>@lang('auth.confirm_new_password')<i class="red">*</i></label>
                        <input type="password" name="password_confirm">
                        <p class="error"></p>
                    </div>
                    <div class="text-right">
                        <button type="button" id="btn_smt_pws" class="btn">@lang('common.confirm')</button>
                    </div>
                </form>
            </div>                   
                
        </div>
    </div>
</div>

@endsection 