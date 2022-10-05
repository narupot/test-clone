@extends('layouts.app')

@section('header_style')

@endsection

@section('header_script')
var register_by = 'seller';
@endsection

@section('breadcrumbs')

@stop

@section('content')
    @if(Session::has('verify_msg'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        {{Session::get('verify_msg')}}
    </div>
    @else  

            <div class="register-step adj-mrgin">
            @include('auth.register_step')

                <div id="register_form_div" class="row justify-content-md-center">
                    <div class="col-sm-12 col-md-8 login-content">
                    @include('auth.register_form')
                    </div>
                </div>

            </div>

            <div  id="thanks_form_div" style="display: none;">
                <div class="login-wrap">                
                    <div class="login-content text-center">
                        <h2 class="title-gred2">Congratulation ! You have already registered.</h2>                                      
                        <div class="form-group">
                            <a href="{{ action('Auth\RegisterController@login') }}" class="btn-grey mb-3">Please sign in to access.</a>

                            <a id="btn_resend"  href="javascript:;" class="btn">@lang('auth.resend_verify_email')</a>

                        </div>                      
                    </div>
                </div>
            </div>

            <div  id="email_form_div" style="display: none;">
                <div class="login-wrap">
                    <h1 class="login-title">@lang('auth.resend_verify_email')</h1>
                        <div class="row">
                            <div class="col-sm-6">
                            <div class="form-group">
                                <label>@lang('common.email')<i class="red">*</i></label>
                                <input type="text" name="email" id="txt_resend_email" value="{{ old('email') }}">
                                <p class="error error-msg" id="info-msg">{{ $errors->first('email') }}</p>
                            </div>
                             <div class="text-right">
                                <input type="button" id="btn_resend_mail" class="btn" value="Send">
                            </div>
                            </div>
                        </div>  
                </div>
            </div>
    
    
    @endif
@endsection
@section('footer_scripts')
@stop