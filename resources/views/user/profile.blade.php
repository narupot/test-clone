@extends('layouts.app')

@section('header_style')
    {!! CustomHelpers::combineCssJs(['css/myaccount', 'css/cropper.min'],'css') !!}
@endsection

@section('header_script')
    var lang_json = {"ok":"@lang('common.ok')", "otp_resent_successfully":"@lang('customer.otp_resent_successfully')"};

    var confirm_password_url = "{{action('User\UserController@confirmPassword')}}";
    var confirm_otp_url = "{{action('User\UserController@confirmOtp')}}";

    <?php  
        $cropper_setting = [
            [
                'section' => 'user_thumb', 'dimension' => ['width' => 115, 'height' => 115], 'file_field_selector' => '#userThumbImage', 'section_id'=>'user-thumb', 'image_type' => 'jpg,png'
            ],
        ];
    ?>
    var CROPPER_SETTING = {!! json_encode($cropper_setting) !!};    
@stop

@section('breadcrumbs')

@stop

@section('content')
<div class="profile-setting">
    <h1 class="page-title title-border">@lang('customer.profile_setting')</h1>
    <form method="post" id="update_profile_frm" action="{{action('User\UserController@update', $userDetail->id)}}" enctype="multipart/form-data">

        {{ csrf_field() }}
        {{ method_field('PUT') }}
        <input type="hidden" name="facebook_login" value="{{$userDetail->facebook_login}}">    
        <div class="row">
            <div class="col-sm-12">
                <div class="profile-image">
                    <input type="hidden" name="image" class="file-upload" id="userThumbImage">
                    <div id="user-thumb">   
                        <div class="avatar-view">
                            <img src="{{getUserImageUrl($userDetail->image)}}" alt="profile image" id="user_thumb">
                            <span class="fas fa-camera"></span>
                        </div>                                
                        @include('includes.common_cropper_upload') 
                    </div>               
                </div>

                <div class="profile-summery">
                    <div>@lang('customer.join_date')</div>
                    <div class="skyblue">{{getDateFormat($userDetail->created_at)}}</div>
                </div>
            </div>
        </div>
        <div class="form-profile-setting">
            <div class="row">
                <div class="col-sm-4 mb-3 pr-0">
                    <div class="form-group">
                        <label>@lang('customer.username_for_login')</label>
                        <input type="text" name="login_detail" value="@if($userDetail->login_use=='email'){{$userDetail->email}}@else{{$userDetail->ph_number}}@endif" disabled="disabled">
                    </div>
                </div>
                <div class="col-sm-2 mb-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <a href="javascript:;" data-toggle="modal" data-target="#confirmModal" data-dismiss="modal" class="btn btn-blue">@lang('common.change')</a>
                    </div>
                </div>                                
            </div>

            <div class="row">
                <div class="col-sm-6 mb-3">
                    <div class="form-group">
                        <label>@lang('common.name')<span class="red">*</span></label>
                        <input type="text" name="first_name" value="{{$userDetail->first_name}}">
                        <p class="error" id="error_first_name"></p>
                    </div>                                  
                </div>
                <div class="col-sm-6 mb-3">
                    <div class="form-group">
                        <label>@lang('common.last_name')<span class="red">*</span></label>
                        <input type="text" name="last_name" value="{{$userDetail->last_name}}">
                        <p class="error" id="error_last_name"></p>
                    </div>
                </div>  
            </div>
            <div class="form-group">
                <label>@lang('common.date_of_birth')<span class="red">*</span></label>
                <input type="text" class="date-select col-sm-4" name="dob" value="{{$userDetail->dob}}">
                <p class="error" id="error_dob"></p>
            </div>

            <div class="form-group">
                <label class="chk-wrap" style="display: inline-block;">
                    <input type="checkbox" id="change_password" name="change_password" value="1">
                    <span class="chk-mark">@lang('customer.change_password')</span>
                </label>                                
            </div>

            <div class="row form-change-password" id="password_div" style="display: none;">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>@lang('common.current_password')<span class="red">*</span></label>
                        <input type="Password" name="current_password" placeholder="**********">
                        <p class="error" id="error_current_password"></p>
                    </div>                
                    <div class="form-group">
                        <label>@lang('common.new_password')<span class="red">*</span></label>
                        <input type="Password" name="new_password" placeholder="**********">
                        <p class="error" id="error_new_password"></p>
                    </div>
                    <div class="form-group mb-3">
                        <label>@lang('common.confirm_password')<span class="red">*</span></label>
                        <input type="Password" name="confirm_password" placeholder="**********">
                        <p class="error" id="error_confirm_password"></p>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="button" id="update_profile" class="btn">@lang('common.update')</button>
            </div>
        </div>
    </form>
</div>

<div id="confirmModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h2 class="mb-0">@lang('customer.confirm_password')</h2>                    
                <span class="close fas fa-times" data-dismiss="modal"></span>
            </div>
            <div class="modal-body">        
                <div class="login-wrap">                
                    <div class="login-content text-center">
                        <div class="form-group register-otp">
                            <label class="skyblue">@lang('customer.enter_current_password')</label>
                            <input type="password" id="confirm_cur_pass" name="confirm_cur_pass" value="" placeholder="**********">
                            <p class="error" id="error_confirm_cur_pass"></p>
                        </div>   
                        <div class="form-group">
                            <button type="button" id="confirm_pass_btn" class="btn">Confirm</button>
                        </div>    
                    </div>
                </div>       
            </div>
        </div>
    </div>
</div>

<div id="updateModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content clearfix">
             <div class="modal-header border-bottom">
                <h1 class="mb-0">@lang('customer.update_login_information')</h1>
                <span class="close fas fa-times" data-dismiss="modal"></span>
            </div>

            <div class="modal-body">
                <form id="login_info_frm" method="POST" action="{{ action('User\UserController@sendUpdateOtp') }}">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label class="radio-wrap" >
                            <input type="radio" value="email" name="login_type" @if($userDetail->login_use=='email') checked="checked" @endif>
                            <span class="radio-mark">@lang('common.email')</span>
                        </label>
                        <label class="radio-wrap">
                            <input type="radio" value="phone" name="login_type" @if($userDetail->login_use=='ph_no') checked="checked" @endif>
                            <span class="radio-mark">@lang('customer.phone_no').</span>
                        </label>
                        <p class="error" id="error_login_type"></p>
                    </div>
                    <div class="form-group" id="email_div" @if($userDetail->login_use=='ph_no') style="display: none;" @endif>
                        <label>@lang('common.email') <i class="red">*</i></label>
                        <input  type="text" name="email" id="login_type_email" value="{{$userDetail->email}}">
                        <p class="error" id="error_email"></p>
                    </div>
                    <div class="form-group" id="phone_div" @if($userDetail->login_use=='email') style="display: none;" @endif>
                        <label>@lang('customer.phone_no')<i class="red">*</i></label>
                        <input type="text" name="phone_no" id="login_type_phone" value="{{$userDetail->ph_number}}">
                        <p class="error" id="error_phone_no"></p>
                    </div>
                    <div class="form-group">
                        <p class="error" id="error_login_type_fail"></p>
                    </div>                   
                    <div class="text-right">
                        <button type="button" id="update_login" class="btn">@lang('common.update')</button>
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
                <h1 class="mb-0">@lang('customer.otp_detail')</h1>                    
                <span class="close fas fa-times" data-dismiss="modal"></span>
            </div>
            <div class="modal-body">        
                <div class="login-wrap">                
                    <div class="login-content text-center">
                        <div class="form-group register-otp">
                            <label class="skyblue" id="otp_msg_label"></label>
                            <input type="text" id="confirm_otp" value="">
                            <p class="error" id="error_confirm_otp"></p>
                        </div>   
                        <div class="form-group">
                            <button type="button" id="resend_otp_btn" class="btn-grey mb-3">@lang('customer.resend_otp')</button> 
                            <button type="button" id="confirm_otp_btn" class="btn">@lang('common.confirm')</button>
                        </div>  
                    </div>
                </div>        
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="https://npmcdn.com/flatpickr@4.6.9/dist/l10n/th.js"></script>
@endsection

@section('footer_scripts')
    {!! CustomHelpers::combineCssJs(['js/user/myaccount', 'js/jquery-cropper.min', 'js/common_cropper_upload_setting'],'js') !!}   
@endsection