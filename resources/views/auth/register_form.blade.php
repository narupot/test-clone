<form method="post" id="userRegisterForm" class="register formone-size" action="{{ action('Auth\RegisterController@insert') }}">
    {{ csrf_field() }}
    <div class="row">
        <div class="col-sm-6 mb-2">
            <div class="form-group">
                <label for="">@lang('auth.name')<i class="red">*</i></label>
                <input type="text" name="first_name" value="{{(!empty($facebook_arr) && isset($facebook_arr->user->first_name))?$facebook_arr->user->first_name:old('first_name')}}" class="alphabetsOnly">
                <p id="first_name" class="error">{{ $errors->first('first_name') }}</p>
            </div>
        </div>
        <div class="col-sm-6 mb-2">
            <div class="form-group">
                <label for="">@lang('auth.last_name')<i class="red">*</i></label>
                <input type="text" name="last_name" value="{{(!empty($facebook_arr) && isset($facebook_arr->user->last_name))?$facebook_arr->user->last_name:old('last_name')}}" class="alphabetsOnly">
                <p id="last_name" class="error">{{ $errors->first('last_name') }}</p>
            </div>
        </div>
    </div>
    <div class="form-group">
         <label for="">@lang('auth.birthday')<i class="red">*</i></label>
    </div>
 	<div class="row">
    	<div class="col-md-4 form-group">
            <select class="w-100" name="date">
                @for($i=1; $i<=31; $i++)
                    <option value="{{sprintf('%02s', $i)}}">{{sprintf('%02s', $i)}}</option>
                @endfor
            </select>
    	</div>
    	<div class="col-md-4 form-group">
            <select class="w-100" name="month">
                @for($i=1; $i<=12; $i++)
                    <option value="{{sprintf('%02s', $i)}}">{{getThaiMonth($i)}}</option>
                @endfor
            </select>
    	</div>
    	<div class="col-md-4 form-group mb-2">
            <select class="w-100" name="year">
                @for($i=date('Y')-15; $i>1949; $i--)
                    <option value="{{$i}}">{{$i}}</option>
                @endfor
            </select>
    	</div>
    </div>
    <p id="dob" class="error">{{ $errors->first('dob') }}</p>
    <div class="form-group">
        <label for="">@lang('auth.login_information_as_well')<i class="red">*</i></label>
        <label class="radio-wrap">
            <input type="radio" name="loginuse" value="email" checked="checked">
            <span class="radio-mark">@lang('auth.email')</span>
        </label>
        <label class="radio-wrap">
            <input type="radio" name="loginuse" value="ph_no">
            <span class="radio-mark">@lang('auth.phone_no')</span>
        </label>
        <p id="loginuse" class="error">{{ $errors->first('email') }}</p>
    </div>
    <div class="form-group" id="emaildiv">
        <input type="text" name="email" value="">
        <p id="email" class="error">{{ $errors->first('email') }}</p>
    </div>
    <div class="form-group" id="ph_numberdiv">
        <label for="">@lang('auth.phone_no')<i class="red">*</i></label>
        <input type="text" name="ph_number">
        <p id="ph_number" class="error">{{ $errors->first('ph_number') }}</p>
    </div>
    <div class="form-group">
        <label for="">@lang('auth.password')<i class="red">*</i></label>
        <input type="password" name="password" autocomplete="off">
        <p id="password" class="error">{{ $errors->first('password') }}</p>
    </div>
    <div class="form-group">
        <label for="">@lang('auth.confirm_password')<i class="red">*</i></label>
        <input type="password" name="password_confirm" autocomplete="off">
        <p id="password_confirm" class="error">{{ $errors->first('password_confirm') }}</p>
    </div>
    <div class="form-group">
        <label class="chk-wrap">
            <input type="checkbox" name="terms_condition">
            <span class="chk-mark">
                @lang('common.shinup_terms_and_condition') 
                <a class="red" href="{{ action('StaticPageController@pagedata',['url'=>'terms-and-condition'])}}" target="_blank">@lang('common.shinup_terms_and_condition_text')</a>
                และ
                <a class="red" href="https://www.simummuangonline.com/page/pdpa-v06062025" target="_blank">นโยบายความเป็นส่วนตัว</a>
                ทั้งหมด
            </span>
        </label>
        <p class="error error-msg" id="terms_condition">{{ $errors->first('terms_condition') }}</p>
    </div>
    <div class="form-group text-right">
        <button id="register" type="button" class="btn">@lang('auth.register_button')</button>
    </div>
</form>
<script>
    $(".flatpickr").flatpickr({
       maxDate: "{{ date('d-m-Y',strtotime(date('Y-m-d').' -15 year')) }}"
    });
</script>