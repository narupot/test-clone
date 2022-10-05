<div id="thanks_form_div" style="display: none;">
    <div class="login-wrap">                
        <div class="login-content text-center">
            <h2 class="title-gred2">@lang('auth.congratulation_you_have_already_registered')</h2>                                      
            <div class="form-group">
                <a href="{{ action('Auth\RegisterController@login') }}" class="btn-grey mb-3">@lang('auth.please_sign_in_to_access')</a>

            </div>                      
        </div>
    </div>
</div>