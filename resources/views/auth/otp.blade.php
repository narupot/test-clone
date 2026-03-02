
<div class="container">
    <div class="register-step">
        @if($register_by == 'seller')
            @include('auth.register_step')
        @endif
        <div id="otp_form_div">
            <div class="login-wrap">                
                <div class="login-content text-center">
                    @if($register_by == 'buyer')
                        <h2 class="title-gred2">@lang('customer.please_confirm_the_registration')</h2>
                    @endif
                        <div class="form-group register-otp">
                            <label class="skyblue">
                                @if($user_info->login_use == 'ph_no')
                                    @lang('customer.please_enter_4_digit_code_recieved_on_your_mobile')
                                @else
                                    @lang('customer.please_enter_4_digit_code_recieved_on_your_email')
                                @endif
                            </label>
                            <input type="text" id="confirm_otp" value="">
                            <input type="hidden" name="" id="request_from" value="page">
                        </div>   
                        <div class="form-group">
                            <button type="button" id="btn_otp_request" class="btn-grey mb-3">@lang('customer.resend_code')</button> 
                            <button type="button" id="btn_otp_confirm" class="btn">@lang('customer.confirm')</button>
                        </div>                   
                        
                </div>
            </div>
        </div>
    </div>
    
    @include('auth.thanks')
    
</div>      
