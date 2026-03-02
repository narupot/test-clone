<!--  Login register popup -->
<div id="loginModal" class="modal fade" role="dialog" >
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="mb-0">@lang('auth.login')</h1>                    
                <span class="close fas fa-times" data-dismiss="modal"></span>
            </div>
            <div class="modal-body">
            <form class="userlogin formone-size" id="popuploginForm" name="loginForm" method="POST" action="{{ route('userlogin') }}">
                {{ csrf_field() }}
                <span id="login-error"></span>
                <span class="error" id="verify_div"></span>
                <div class="form-group">
                    <label>@lang('auth.email_phone_no')</label>
                    <input type="text" name="login_email_phone" id="login_email_phone" value="">
                </div>

                <div class="form-group">
                    <label>@lang('auth.password')</label>
                    <input type="password" name="login_password" id="login_password" autocomplete="off">
                </div>

                <div class="form-group">
                    <p>
                        <a href="javascript:;" data-toggle="modal" data-target="#forgotModal" data-dismiss="modal" class="forget-link">@lang('auth.forget_password')</a>
                    <br>
                        <a href="{{ action('Auth\RegisterController@index') }}" class="forget-link">@lang('auth.signup')</a>
                    </p>
                </div>
                
                <div class="form-group">
                    <button type="button" class="btn btn_login float-right">@lang('auth.signin')</button>
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
</div>

<div id="forgotModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <!-- Modal content-->
        <div class="modal-content clearfix">
             <div class="modal-header border-bottom">
                <h1 class="mb-0">@lang('auth.forget_password')</h1>                    
                <span class="close fas fa-times" data-dismiss="modal"></span>
            </div>

        <div class="modal-body">
            <form id="pageforgetForm" name="forgetForm" method="POST" action="{{ action('Auth\ForgotPasswordController@sendResetLinkEmail') }}">
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
                <div class="text-right">
                    <a class="btn-grey" onclick="goBack()">@lang('common.back')</a>
                    <button type="button" id="btn_forget" class="btn">@lang('common.send')</button>
                </div>
            </form>
        </div>
        </div>
    </div>
</div>

<div id="otpModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header border-bottom">
            <!-- <h1 class="mb-0">@lang('auth.set_an_otp')</h1>  -->                   
            <span class="close fas fa-times" data-dismiss="modal"></span>
        </div>
        <div class="modal-body">        
            <div id="otp_form_div">
                <div class="login-wrap">                
                    <div class="login-content text-center">

                        <div class="form-group register-otp">
                            <label class="skyblue" id="otp_msg">@lang('auth.please_enter_the_4_digit_code_received_from_otp_email')</label>
                            <input type="text" id="confirm_otp" value="">
                            <input type="hidden" name="" id="request_from" value="popup">
                        </div>   
                        <div class="form-group">
                            <button type="button" id="btn_otp_request" class="btn-grey mb-3">@lang('customer.resend_code')</button> 
                            <button type="button" id="btn_otp_confirm" class="btn">@lang('common.confirm')</button>
                        </div>                   
                            
                    </div>
                </div>
            </div>        
        </div>
        </div>
    </div>
</div>

<div id="resetPwdModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h1 class="mb-0">@lang('auth.set_a_new_password')</h1>                    
                <span class="close fas fa-times" data-dismiss="modal"></span>
            </div>
            <div class="modal-body">
                    <!-- <h1 class="login-title">Set a new password</h1>  -->        
                    <div class="row">
                        <form method="post" action="{{ action('Auth\ResetPasswordController@resetPasswordPhone') }}" class="col-sm-12" id="resetPwdForm">
                            {{ csrf_field() }}
                            <div class="form-group">
                                <label>@lang('auth.new_password')<i class="red">*</i></label>
                                <input type="password" name="password" autocomplete="off">
                                <p class="error"></p>
                            </div>
                            <div class="form-group">
                                <label>@lang('auth.confirm_new_password')<i class="red">*</i></label>
                                <input type="password" name="password_confirm" autocomplete="off">
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
</div>