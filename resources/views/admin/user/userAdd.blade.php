@extends('layouts/admin/default')

@section('title')
    @lang('admin.add_new_member')
@stop

@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{ Config::get('constants.css_url') }}bootstrap-datepicker.css" />
@stop

@section('content')
<form id="add_admin_form" method="post" action="{{ action('Admin\User\AdminController@store') }}" enctype="multipart/form-data" autocomplete="off">
    {{ csrf_field() }}

    <div class="content">
        @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                                   
        @endif      
        <div class="header-title">
            <h1 class="title">@lang('admin.add_new_member')</h1>
            <div class="float-right">
                <a class="btn btn-back" href="{{ action('Admin\User\AdminController@index') }}">@lang('common.back')</a>
                <button type="submit" name="submit_type" value="save_and_continue" class="btn btn-secondary">@lang('common.save_and_continue')</button>
                <button type="submit" name="submit_type" value="save" class="btn btn-save btn-primary">@lang('common.save')</button>
            </div>
        </div>
        <div class="content-wrap clearfix">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','user')!!}
                </ul>
            </div>
            <div class="team-title">
                <h3>@lang('admin.manage_users')</h3>
                <p>@lang('admin.add_your_team_members_and_manage_thier_details_and_user_permission').</p>
            </div>
            <div class="content-left">
                <div class="tablist">
                    <h3>@lang('admin.role_information')</h3>
                    <ul class="nav nav-tabs">
                        <li class="nav-item"><a class="nav-link show active" data-toggle="tab" data-target="#accountInfo">@lang('admin.account_info')</a></li>
                        {{--
                        @if($permission_arr['manage_product'] === true)
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#rProduct">@lang('admin.role_products')</a></li>
                        @endif
                        @if($permission_arr['manage_customer'] === true)
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#rUser">@lang('admin.role_user')</a></li>
                        @endif
                        @if($permission_arr['manage_order'] === true)
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#rOrder">@lang('admin.role_order')</a></li>
                        @endif
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#rActivities">@lang('admin.log_activities')</a></li>
                        --}}
                    </ul>
                </div>
            </div>
            <div class="content-right container">
                <div class="tab-content">
                    <div id="accountInfo" class="tab-pane fade show active">
                        <div class="form-group">
                            <div class="select-profile">
                                <span class="profile-img" id="image_display">
                                    <img src="{{ Config('constants.image_url').'blank-profile-img.jpg' }}" width="150" height="150">
                                </span>
                                <div class="file-wrapper" id="image_upload">
                                    <input type="file" accept="image/*" name="entity[image]">
                                    <span class="btn-grey btn-primary">+ @lang('admin.choose_profile_image')</span>
                                </div>
                                <span id="image_upload_status"></span>                          
                            </div>                            
                        </div>
                        <div class="form-group form-group role-select">
                            <label>@lang('admin.role') <i class="strick">*</i></label>
                            <select name="role" class="form-control">
                                <option disabled selected>@lang('admin.select_role')</option>
                                @foreach($role_lists as $role)
                                    <option value="{{ $role->id }}" @if($role->id == old('role')) selected @endif>{{ $role->name }}</option> 
                                @endforeach
                            </select>
                            @if($errors->has('role'))
                              <p class="red">{{ $errors->first('role') }}</p>
                            @endif 
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group form-group">
                                    <label>@lang('common.nick_name') <i class="strick">*</i></label>
                                    <input name="nick_name" value="{{ old('nick_name') }}" type="text" class="form-control">
                                    @if($errors->has('nick_name'))
                                      <p class="red">{{ $errors->first('nick_name') }}</p>
                                    @endif
                                </div>
                                <div class="form-group form-group">
                                    <label>@lang('common.first_name') <i class="strick">*</i></label>
                                    <input type="text" name="first_name" value="{{ old('first_name') }}" class="form-control">
                                    @if($errors->has('first_name'))
                                      <p class="red">{{ $errors->first('first_name') }}</p>
                                    @endif
                                </div>
                                <div class="form-group form-group">
                                    <label>@lang('common.last_name') <i class="strick">*</i></label>
                                    <input name="last_name" value="{{ old('last_name') }}" type="text" class="form-control">
                                    @if($errors->has('last_name'))
                                      <p class="red">{{ $errors->first('last_name') }}</p>
                                    @endif
                                </div>
                                <div class="form-group form-group">
                                    <label>@lang('admin.contact_no').</label>
                                    <input name="entity[contact_no]" value="{{ old('contact_no') }}" type="text" class="form-control" maxlength="14"> 
                                    @if($errors->has('entity[contact_no]'))
                                      <p class="red">{{ $errors->first('entity[contact_no]') }}</p>
                                    @endif                                  
                                </div>
                                <div class="form-group">
                                    <label>@lang('admin.address')</label>
                                    <textarea name="entity[address]" class="form-control">{{ old('entity[gender]') }}</textarea>
                                </div>
                                <div class="form-group form-group">
                                    <label>@lang('common.sex')</label>
                                    <div class="radio-group">
                                        <label class="radio-wrap"><input name="entity[gender]" value="M" type="radio"> <span class="radio-label ">@lang('common.male')</span></label>
                                        <label class="radio-wrap"><input name="entity[gender]" value="F" type="radio"> <span class="radio-label ">@lang('common.female')</span></label>
                                    </div>
                                    @if($errors->has('entity[gender]'))
                                      <p class="red">{{ $errors->first('entity[gender]') }}</p>
                                    @endif                                     
                                </div>
                                <div class="form-group form-group">
                                    <label>@lang('admin.date_of_birth')</label>
                                    <input type="text" name="entity[dob]" value="{{ old('dob') }}" class="date-select form-control">
                                    @if($errors->has('dob'))
                                      <p class="red">{{ $errors->first('dob') }}</p>
                                    @endif 
                                </div>
                            </div>
                            <div class="col-sm-6 role-form-right">
                                <div class="form-group form-group">
                                    <label>@lang('common.email') <i class="strick">*</i></label>
                                    <input name="email" value="{{ old('email') }}" type="text" class="form-control">
                                    @if($errors->has('email'))
                                    <p class="red">{{ $errors->first('email') }}</p>
                                    @endif 
                                </div>                               
                                <div class="form-group form-group">
                                    <label>@lang('common.password') <i class="strick">*</i></label>
                                    <input name="password" type="password" class="form-control">
                                    @if($errors->has('password'))
                                      <p class="red">{{ $errors->first('password') }}</p>
                                    @endif 
                                </div>                            
                                <div class="form-group form-group">
                                    <label>@lang('common.confirm_password') <i class="strick">*</i></label>
                                    <input name="password_confirm" type="password" class="form-control">
                                    @if($errors->has('password_confirm'))
                                      <p class="red">{{ $errors->first('password_confirm') }}</p>
                                    @endif    
                                </div>
                                <div class="form-group">
                                    <label>@lang('common.status')</label>
                                    <div class="radio-group">
                                        <label class="radio-wrap"><input name="status" type="radio" value="1" checked="checked"> <span class="radio-label ">@lang('common.active')</span></label>
                                        <label class="radio-wrap"><input name="status" type="radio" value="0"> <span class="radio-label ">@lang('common.inactive')</span></label>
                                    </div>
                                </div>                             
                            </div>
                        </div>
                    </div>
                    @if($permission_arr['manage_product'] === true)
                        <div id="rProduct" class="tab-pane fade">
                            <h2 class="tab-heading">@lang('admin.role_products')</h2>
                            <div class="form-group">                        
                                <label class="radio-wrap"><input type="radio" name="product_permission_type" value="1" checked="checked"> <span class="radio-label ">@lang('admin.see_all_product')</span></label>
                            </div>                        
                            <div class="form-group">                        
                                <label class="radio-wrap"><input type="radio" name="product_permission_type" value="2"> <span class="radio-label ">@lang('admin.see_product_belong_to_this_user_created')</span></label>
                            </div>
                            <div class="form-group">                        
                                <label class="radio-wrap"><input type="radio" name="product_permission_type" value="3"> <span class="radio-label ">@lang('admin.see_product_belong_to_this_role_created')</span></label>
                            </div>
                            <div class="form-group">                        
                                <label class="radio-wrap"><input type="radio" name="product_permission_type" value="4" id="roleproduct-list"> <span class="radio-label">@lang('admin.see_product_belong_to_select_below'):</span></label>
                            </div>
                            <div class="filter-table-container" >
                                Product Table Will be  here....
                            </div>
                        </div>
                    @endif
                    @if($permission_arr['manage_customer'] === true)
                        <div id="rUser" class="tab-pane fade">
                        <h2 class="tab-heading">@lang('admin.role_user')</h2>
                        <div class="form-group">                        
                            <label class="radio-wrap"><input type="radio" name="customer_permission_type" value="1" checked="checked"> <span class="radio-label ">@lang('admin.all_customer')</span></label>
                        </div>
                        <div class="form-group">                        
                            <label class="radio-wrap"><input type="radio" name="customer_permission_type" value="2" id="customer-list"> <span class="radio-label ">@lang('admin.limit_access')</span></label>
                        </div>
                        <div class="form-group inlclude">
                            <div class="pull-left">@lang('admin.inclduing_with') : </div>
                            <div class="pull-left"> 
                                <ul>
                                    <li> - @lang('admin.customer_belong_to_orders_assigned_by_admin') </li>
                                    <li> - @lang('admin.some_of_customers_assigned')</li>
                                </ul>
                           
                            </div>                            
                        </div>
                        <div class="customer-table-container" >
                            Customers Table Will be  here....
                        </div>                                                
                        </div>
                    @endif
                    @if($permission_arr['manage_order'] === true)
                        <div id="rOrder" class="tab-pane fade">
                        <h2 class="tab-heading">@lang('admin.role_order')</h2>
                        <div class="form-group">                        
                            <label class="radio-wrap"><input type="radio" name="order_permission_type" value="1" checked="checked"> <span class="radio-label ">@lang('admin.all_orders')</span></label>
                        </div>
                        <div class="form-group">                        
                            <label class="radio-wrap"><input type="radio" name="order_permission_type" value="2" id="order-list"> <span class="radio-label ">@lang('admin.limit_access')</span></label>
                        </div>
                        <div class="form-group inlclude">
                            <div class="pull-left">@lang('admin.inclduing_with') : </div>
                            <div class="pull-left"> 
                                <ul>
                                    <li> - @lang('admin.order_assigned_by_customer_while_checkout') </li>
                                    <li> - @lang('admin.some_of_orders_assigned')</li>
                                </ul>
                           
                            </div>
                        </div>
                        <div class="order-table-container" >
                            Order Table Will be  here....
                        </div>                         
                        </div>
                    @endif
                    <div id="rActivities" class="tab-pane fade">
                        <h3>Log activities cotent goes here </h3>
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
    <script src="{{ Config::get('constants.js_url') }}bootstrap-datepicker.min.js"></script>  
    <script src="{{ Config('constants.admin_js_url') }}adduser.js"></script>      
    <!-- end of page level js -->    
    
@stop
