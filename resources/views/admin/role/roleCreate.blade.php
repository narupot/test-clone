@extends('layouts/admin/default')

@section('title')
  @lang('admin.new_role') - {{getSiteName()}}
@stop

@section('header_styles')
    
@stop

@section('content')

    <!-- Right side column. Contains the navbar and content of the page -->
    <div class="content">
        @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                             
        @endif   
        <form name="addGroupFrm" id="addGroupFrm" method="post" action="{{ action('Admin\Role\GroupController@store') }}">
            <!-- CSRF Token -->
            {{ csrf_field() }}    
            <div class="header-title">
                <h1 class="title">@lang('admin.new_role')</h1>
                <div class="float-right">
                    <a href="{{ action('Admin\Role\GroupController@index') }}" class="btn btn-back">@lang('common.back')</a>
                    <button type="submit" name="submit_type" value="save_and_continue" class="btn btn-secondary">@lang('common.save_and_continue')</button>
                    <button type="submit" name="submit_type" value="save" class="btn btn-save btn-success">@lang('common.save')</button>
                </div>
            </div>
            <div class="content-wrap clearfix">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('config','role')!!}
                    </ul>
                </div>
                <div class="content-left">
                    <div class="tablist">
                        <h3>@lang('admin.role_information')</h3>
                        <ul class="nav nav-tabs">
                            <li class="nav-item"><a class="nav-link show active" data-toggle="tab" data-target="#roleInfo">@lang('admin.role_info')</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#roleResources">@lang('admin.role_resources')</a></li>
                        </ul>
                    </div>
                </div>
                <div class="content-right container"> 
                    <div class="tab-content">
                        <div id="roleInfo" class="tab-pane fade show active">
                            <div class="row">
                                <div class="col-sm-10">
                                    <div class="form-group">
                                        <label>@lang('admin.role_name') <i class="strick">*</i></label>
                                        <input id="role" name="name" class="input" value="{{ old('name') }}" type="text">
                                        <small class="red" id="role_error"></small>
                                        @if($errors->has('name') || $errors->has('slug'))
                                            <p class="red">{{ $errors->first('name')?$errors->first('name'):$errors->first('slug') }}</p>
                                        @endif 
                                    </div>
                                    <div class="form-group">
                                        {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'department_name', 'label'=>'Department Name <i class="strick">*</i>', 'errorkey'=>'depart_name', 'cssClass'=>'dpt-name']], '1', $errors) !!}
                                        <small class="red" id="department_name_error"></small>
                                    </div>
                                    <!--
                                    <div class="form-group check-group">
                                        <label class="check-wrap"><input type="checkbox" name="show_on_checkout" value="1"> <span class="chk-label">@lang('admin.show_in_checkout_page_salesman')</span></label>
                                    </div>
                                    -->
                                </div>
                            </div>
                        </div>
                        <div id="roleResources" class="tab-pane">
                            <h3>@lang('admin.role_resources')</h3>

                            <small class="red" id="role_menu_error"></small>
                            @if($errors->has('menu_check'))
                              <p class="red">{{ $errors->first('menu_check') }}</p>
                            @endif
                            {!! CustomHelpers::getRoleMenu() !!}                                
                            
                        </div>
                    </div>
                </div>
            </div>
        </form>    
    </div>

    <div class="error-msg-container" id="error_div_main">
        <div class="sucess-msg">
            <div class="clearfix">
                <span class="close icon-remove close-msg"></span>
            </div>                    
            <div class="ok">
                <span class="fas fa-check error-icon"></span>
            </div>
            <div id="error_div">                    
            </div>
            <button class="ok-msg btn-primary">@lang('common.ok')</button>
        </div>
    </div>       
        
@stop

@section('footer_scripts')

    <!-- begining of page level js -->
    <script src="{{ Config('constants.admin_js_url') }}lang/{{ session('lang_code') }}.lang.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}roles.js"></script>
    <!-- end of page level js -->    
@stop