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
                <a class="btn btn-back" href="{{ action('Admin\User\AdminController@index') }}">@lang('common.back')</a>
                <button type="submit" name="submit_type" value="save_and_continue" class="btn btn-save btn-success">@lang('common.save')</button>
            </div>
        </div>
        <div class="content-wrap clearfix">
            <div class="team-title">
                <h3>@lang('admin.manage_buyer')</h3>
                <p>@lang('admin.add_buyer_and_manage_thier_details').</p>
            </div>
            <div class="content-left">
                <div class="tablist">
                    <ul class="nav nav-tabs">
                        <li class="nav-item"><a class="nav-link show active" data-toggle="tab" data-target="#buyerInfo">@lang('admin.buyer_information')</a></li>
                    </ul>
                </div>
            </div>
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{$buyer->id}}">
            <div class="content-right container">
                <div class="tab-content">
                    <div id="buyerInfo" class="tab-pane fade show active">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>@lang('auth.name')<i class="red">*</i></label>
                                    <input type="text" name="first_name" value="{{ $buyer->first_name }}"  placeholder="First name">
                                    <p id="first_name" class="error">{{ $errors->first('first_name') }}</p>
                                </div>
                                 <div class="form-group">
                                    <label>@lang('auth.last_name')<i class="red">*</i></label>
                                    <input type="text" name="last_name" value="{{ $buyer->last_name }}" placeholder="Last name">
                                    <p id="last_name" class="error">{{ $errors->first('last_name') }}</p>
                                </div>
                                 <div class="form-group">
                                    <label>@lang('auth.birthday')<i class="red">*</i></label>
                                    <input type="text" class="date-select" name="dob" value="{{ $buyer->dob }}" placeholder="{{ date('d-m-Y') }}">
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
                                            <input type="radio" name="loginuse" value="ph_no" @if(!empty($errors->first('ph_number')) || (isset($buyer->login_use) && $buyer->login_use=='ph_no')) checked="checked" @endif >
                                            <span class="radio-label">@lang('auth.phone_no').</span>
                                        </label>                                    
                                    </div>                         
                                </div> 
                                <div class="form-group" id="emaildiv">
                                    <label>@lang('auth.email')<i class="red">*</i></label>
                                    <input type="text" name="email" value="{{ $buyer->email }}" placeholder="example@xyz.com">
                                    <p id="email" class="error">{{ $errors->first('email') }}</p>
                                </div>
                                <div class="form-group" id="ph_numberdiv">
                                    <label>@lang('auth.phone_no').<i class="red">*</i></label>
                                    <input type="text" name="ph_number" value="{{ $buyer->ph_number }}" placeholder="6932890004">
                                    <p id="ph_number" class="error">{{ $errors->first('ph_number') }}</p>
                                </div>
                                {{--@if($permission_arr['edit'] === true)--}}
                                    <div class="form-group">
                                        <span class="change-pwd btn btn-secondary" data-toggle="modal" data-target="#changerolepwd">@lang('admin.change_password')</span>
                                    </div> 
                               {{-- @endif --}}
                            </div>                      
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form> 
<!-- Modal -->
<div id="changerolepwd" class="modal fade" role="dialog">
    <div class="modal-dialog" id="content_div">
    <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"></h2>
                <span class="fas fa-times" data-dismiss="modal"></span> 
            </div>
            <div class="modal-body">
                <form id="change_password_form" method="post">
                   {{ csrf_field() }}
                    <div class="role-form-right">  
                        <input type="hidden" name="id" value="{{$buyer->id}}">
                        <div class="form-group form-group">
                            <label>@lang('common.new_password') <i class="strick">*</i></label>
                            <input name="password" type="password" class="form-control">
                            <p class="red" id="error_password"></p>
                        </div>                             
                        <div class="form-group form-group">
                            <label>@lang('common.confirm_password') <i class="strick">*</i></label>
                            <input name="password_confirm" type="password" class="form-control">
                            <p class="red" id="error_password_confirm"></p>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="change-pwd btn btn-save">@lang('common.submit')</button>
                            <span id="loader_span"></span>
                        </div>                              
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>       
@stop

@section('footer_scripts')
 
    <!-- begining of page level js -->
    <!-- for file upload --> 
    <script type="text/javascript" src="{{ Config::get('constants.js_url') }}ajaxupload.3.5.js" ></script> 
    <script type="text/javascript">
        var loader_url = "{{ Config::get('constants.loader_url') }}Loading_icon.gif";
        var change_password_url = "{{ action('Admin\Customer\BuyerController@changePassword') }}";     
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
