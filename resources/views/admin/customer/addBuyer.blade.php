@extends('layouts/admin/default')

@section('title')
    @lang('admin.add_new_member')
@stop

@section('header_styles')    
    <link rel="stylesheet" type="text/css" href="{{Config('constants.css_url') }}flatpickr.min.css">
@stop

@section('content')
<form id="add_buyer_form" method="post" action="{{ action('Admin\Customer\BuyerController@saveBuyer') }}" enctype="multipart/form-data" autocomplete="off">
    {{ csrf_field() }}

    <div class="content">
        @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                                   
        @endif      
        <div class="header-title">
            <h1 class="title">@lang('admin.add_new_buyer')</h1>
            <div class="float-right">
                <a class="btn btn-back" href="{{ action('Admin\Customer\UserController@index') }}">@lang('common.back')</a>
                <button type="submit" name="submit_type" value="save_and_continue" class="btn btn-secondary">@lang('common.save_and_continue')</button>
                <button type="submit" name="submit_type" value="save" class="btn btn-save btn-success">@lang('common.save')</button>
            </div>
        </div>
        <div class="content-wrap clearfix">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('buyer','buyer', 'list')!!}
                </ul>
            </div>
            <div class="team-title">
                <h3>@lang('admin.manage_buyer')</h3>
                <p>@lang('admin.add_buyer_and_manage_thier_details').</p>
            </div>
           <!--  <div class="content-left">
                <div class="tablist">
                    <h3>@lang('admin.buyer_information')</h3>
                </div>
            </div> -->
            <div class="content-right container">
                    {{ csrf_field() }}
                    <h3 class="mb-3">@lang('admin.buyer_information')</h3>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>@lang('auth.name')<i class="red">*</i></label>
                                <input type="text" name="first_name" value="{{ old('first_name') }}"  placeholder="First name">
                                <p id="first_name" class="error">{{ $errors->first('first_name') }}</p>
                            </div>
                             <div class="form-group">
                                <label>@lang('auth.last_name')<i class="red">*</i></label>
                                <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Last name">
                                <p id="last_name" class="error">{{ $errors->first('last_name') }}</p>
                            </div>
                             <div class="form-group">
                                <label>@lang('auth.birthday')<i class="red">*</i></label>
                                <input type="text" class="date-select" name="dob" value="{{ old('dob') }}" placeholder="{{ date('d-m-Y') }}">
                                <p id="dob" class="error">{{ $errors->first('dob') }}</p>
                            </div>
                            
                             <div class="form-group">
                                <label>@lang('auth.login_information_as_well')<i class="red">*</i></label>
                                <div class="radio-group">
                                    <label class="radio-wrap">
                                        <input type="radio" name="loginuse" value="email" checked="checked">
                                        <span class="radio-label">@lang('auth.email')</span>
                                    </label>
                                    <label class="radio-wrap">
                                        <input type="radio" name="loginuse" value="ph_no" @if(!empty($errors->first('ph_number'))) checked="checked" @endif >
                                        <span class="radio-label">@lang('auth.phone_no').</span>
                                    </label>                                    
                                </div>                         
                            </div> 
                            <div class="form-group" id="emaildiv">
                                <label>@lang('auth.email')<i class="red">*</i></label>
                                <input type="text" name="email" value="{{ old('email') }}" placeholder="example@xyz.com">
                                <p id="email" class="error">{{ $errors->first('email') }}</p>
                            </div>
                            <div class="form-group" id="ph_numberdiv">
                                <label>@lang('auth.phone_no').<i class="red">*</i></label>
                                <input type="text" name="ph_number" value="{{ old('ph_number') }}" placeholder="6932890004">
                                <p id="ph_number" class="error">{{ $errors->first('ph_number') }}</p>
                            </div>
                            <div class="form-group">
                                <label>@lang('auth.password')<i class="red">*</i></label>
                                <input type="password" name="password" value="{{ old('password') }}" placeholder="**********">
                                <p id="password" class="error">{{ $errors->first('password') }}</p>
                            </div>
                            <div class="form-group">
                                <label>@lang('auth.confirm_password')<i class="red">*</i></label>
                                <input type="password" name="password_confirm" value="{{ old('password_confirm') }}" placeholder="**********">
                                <p id="password_confirm" class="error">{{ $errors->first('password_confirm') }}</p>
                            </div>
                        </div>                      
                    </div>
            </div>
        </div>
    </div>
</form>        
@stop

@section('footer_scripts')
 
    <!-- begining of page level js -->
    <!-- for file upload --> 
    <script type="text/javascript" src="{{ Config::get('constants.js_url') }}ajaxupload.3.5.js" ></script> 
    <script type="text/javascript">
        var loader_url = "{{ Config::get('constants.loader_url') }}Loading_icon.gif";
        var upload_path = "{{ Config::get('constants.user_path') }}";
        var upload_url = "{{ Config::get('constants.user_url') }}";
        var ajax_url = "{{ action('AjaxController@uploadImageAjax') }}";
        //alert(loader_url+'==='+upload_path+'==='+upload_url+'==='+ajax_url);                
    </script>    
    <!-- for file upload ended -->    
    <script src="{{ Config('constants.admin_js_url') }}bootstrapValidator.min.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}lang/{{ session('lang_code') }}.lang.js"></script>    
    <script src="{{ Config('constants.admin_js_url') }}add_buyer.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}flatpickr.min.js"></script>  
    <script type="text/javascript">
        $(document).ready(function() {
            // Date time Pickers
            $(".date-select").flatpickr();   
        });
    </script>    
    <!-- end of page level js -->    
    
@stop
