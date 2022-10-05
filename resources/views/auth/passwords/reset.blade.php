@extends('layouts.app')
@section('content')
<div id="content">
    <div class="container">
        <div class="row">
            <h2 class="panel-heading"></h2>
            <div class="col-sm-12">
                @if (session('status'))
                <div class="alert alert-success alert-dismissable margin5">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{session('status')}}
                </div>
                @endif
            </div>
        </div>
        <div class="login-create-shop row">
            <div class="col-sm-6">
            <h1>@lang('auth.reset_password')</h1>
                <form class="form-horizontal formone-size" id="resetPassForm" role="form" method="POST" action="{{ action('Auth\ResetPasswordController@reset', $token) }}">
                    {{ csrf_field() }}
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="form-group">
                          <label for="email">@lang('auth.email')</label>
                          <input id="email" type="email" class="form-control" name="email" value="" aria-required="true" aria-describedby="email-error" aria-invalid="true">
                          @if ($errors->has('email'))
                            <p id="email-error" class="error error-msg">{{ $errors->first('email') }}</p>
                          @endif
                   </div>
                    <div class="form-group">
                       <label for="password"> @lang('auth.password') </label>
                        <input id="password" type="password" class="form-control" name="password" aria-required="true" aria-describedby="password-error" aria-invalid="true">
                         @if ($errors->has('password'))
                          <p id="password-error" class="error error-msg">{{ $errors->first('password') }}</p>
                         @endif 
                    </div>
                    <div class="form-group">
                        <label for="password"> @lang('auth.password_confirmation') </label>
                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" aria-required="true" aria-describedby="password_confirmation-error" aria-invalid="true">
                         @if ($errors->has('password'))
                          <p id="password_confirmation-error" class="error error-msg">{{ $errors->first('password_confirmation') }}</p>
                         @endif
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-primary">@lang('auth.reset_password')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
