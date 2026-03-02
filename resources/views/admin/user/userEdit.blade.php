@extends('layouts/admin/default')

@section('title')
    @lang('admin.edit_member')
@stop

@section('header_styles')
    <link rel="stylesheet" type="text/css" href="{{ Config::get('constants.css_url') }}bootstrap-datepicker.css" />
@stop

@section('content') 
<form id="edit_admin_form" method="post" action="{{ action('Admin\User\AdminController@update', $admin_details->id) }}" enctype="multipart/form-data" autocomplete="off">
    {{ csrf_field() }}
    {{ method_field('PUT') }}

    <input type="hidden" name="admin_id" value="{{ $admin_details->id }}">
    @if($page_type=='my_account')
        <input type="hidden" name="page_type" value="my_account">
    @endif
    <div class="content">
        @if(Session::has('succMsg'))    
            <script type="text/javascript">               
                _toastrMessage('success', "{{ Session::get('succMsg') }}");    
            </script>                            
        @elseif(Session::has('errorMsg'))
            <script type="text/javascript"> 
                _toastrMessage('error', "{{ Session::get('errorMsg') }}");
            </script>  
        @endif    
        <div class="header-title">
            <h1 class="title">
            @if($page_type=='my_account')
                @lang('customer.my_account')
            @else
                @lang('admin.edit_member')
            @endif
            - {{$admin_details->nick_name}}
            </h1> 
            <div class="float-right">
                @if($page_type=='user_account')
                    <a class="btn btn-back" href="{{ action('Admin\User\AdminController@index') }}">@lang('common.back')</a>
                @endif
                @if($permission_arr['edit'] === true)
                    <input type="submit" name="update" value="@lang('common.update')" class="btn btn-primary">
                @endif
            </div>                       
        </div>        
        <div class="content-wrap clearfix">
            <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('config','user')!!}
                </ul>
            </div>
            @if($page_type=='user_account')
                <div class="team-title">
                    <h3>@lang('admin.manage_users')</h3>
                    <p>@lang('admin.edit_your_team_members_and_manage_thier_details_and_user_permission').</p>
                </div>
            @endif
            <div class="content-left">
                <div class="tablist">
                    <h3>@lang('admin.role_information')</h3>
                    <ul class="nav nav-tabs">
                        <li class="nav-item"><a class="nav-link show active" data-toggle="tab" data-target="#accountInfo">@lang('admin.account_info')</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" data-target="#rResources">@lang('admin.role_resources')</a></li>
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
                        --}}
                        <li class="nav-item""><a class="nav-link" data-toggle="tab" data-target="#rActivities">@lang('admin.log_activities')</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="content-right container">
               
                <div class="tab-content">
                    <div id="accountInfo" class="tab-pane fade show active">
                        <div class="form-group">
                            <div class="select-profile">
                                <span class="profile-img" id="image_display">
                                    <img src="{{ getUserImageUrl(isset($entity_arr['profile_image'])?$entity_arr['profile_image']:'') }}" alt="{{ $admin_details->nick_name }}" title="{{ $admin_details->nick_name }}" width="150" height="150">
                                </span>
                                <div class="file-wrapper" id="image_upload">
                                    <input type="file" accept="image/*">
                                    <span class="btn-grey">+ @lang('admin.choose_profile_image')</span>
                                </div>
                                <span id="image_upload_status"></span>                          
                            </div>
                        </div>
                        @if($admin_details->admin_level != -1)
                            @if($permission_arr['edit_role'] === true && $admin_details->id != Auth::guard('admin_user')->user()->id) 
                                <div class="form-group form-group role-select">
                                    <label>@lang('admin.role') <i class="strick">*</i></label>
                                    <select name="role" class="form-control">
                                        <option disabled selected>@lang('admin.select_role')</option>
                                        @foreach($role_lists as $role)
                                            <option value="{{ $role->id }}" @if($role->id == $admin_details->role_id) selected @endif>{{ $role->name }}</option> 
                                        @endforeach
                                    </select>
                                    @if($errors->has('role'))
                                      <p class="red">{{ $errors->first('role') }}</p>
                                    @endif 
                                </div>
                            @else
                                <input type="hidden" name="role" value="{{$admin_details->role_id}}">
                            @endif                                
                        @else
                            <input type="hidden" name="role" value="0">
                        @endif                         
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group form-group">
                                    <label>@lang('common.nick_name') <i class="strick">*</i></label>
                                    <input name="nick_name" value="{{ $admin_details->nick_name }}" type="text" class="form-control">
                                    @if($errors->has('nick_name'))
                                      <p class="red">{{ $errors->first('nick_name') }}</p>
                                    @endif
                                </div>
                                <div class="form-group form-group">
                                    <label>@lang('common.first_name') <i class="strick">*</i></label>
                                    <input type="text" name="first_name" value="{{ $admin_details->first_name }}" class="form-control">
                                    @if($errors->has('first_name'))
                                      <p class="red">{{ $errors->first('first_name') }}</p>
                                    @endif
                                </div>
                                <div class="form-group form-group">
                                    <label>@lang('common.last_name') <i class="strick">*</i></label>
                                    <input name="last_name" value="{{ $admin_details->last_name }}" type="text" class="form-control">
                                    @if($errors->has('last_name'))
                                      <p class="red">{{ $errors->first('last_name') }}</p>
                                    @endif
                                </div>
                                <div class="form-group form-group">
                                    <label>@lang('admin.contact_no').</label>
                                    <input name="entity[contact_no]" value="{{ isset($entity_arr['contact_no'])?$entity_arr['contact_no']:'' }}" type="text" class="form-control" maxlength="14"> 
                                    @if($errors->has('contact_no'))
                                      <p class="red">{{ $errors->first('contact_no') }}</p>
                                    @endif                                  
                                </div>
                                <div class="form-group">
                                    <label>@lang('admin.address')</label>
                                    <textarea name="entity[address]" class="form-control">{{ isset($entity_arr['address'])?$entity_arr['address']:'' }}</textarea>
                                </div>
                                <div class="form-group form-group">
                                    <label>@lang('common.sex')</label>
                                    <div class="radio-group">
                                        <label class="radio-wrap"><input name="entity[gender]" value="M" type="radio" @if(isset($entity_arr['gender']) && $entity_arr['gender'] == 'M') checked @endif > <span class="radio-label ">@lang('common.male')</span></label>
                                        <label class="radio-wrap"><input name="entity[gender]" value="F" type="radio" @if(isset($entity_arr['gender']) && $entity_arr['gender'] == 'F') checked @endif> <span class="radio-label ">@lang('common.female')</span></label>
                                    </div>
                                    @if($errors->has('gender'))
                                      <p class="red">{{ $errors->first('gender') }}</p>
                                    @endif                                     
                                </div>
                                <div class="form-group form-group">
                                    <label>@lang('admin.date_of_birth')</label>
                                    <input type="text" name="entity[dob]" value="{{ isset($entity_arr['dob'])?$entity_arr['dob']:'' }}" id="" class="date-select form-control">
                                    @if($errors->has('dob'))
                                      <p class="red">{{ $errors->first('dob') }}</p>
                                    @endif 
                                </div>
                            </div>
                            <div class="col-sm-6 role-form-right">
                            @if($admin_details->id == Auth::guard('admin_user')->user()->id)
                                <div class="form-group form-group">
                                    <label>@lang('common.email') <i class="strick">*</i></label>
                                    <input name="email" value="{{ $admin_details->email }}" type="text" class="form-control">
                                    @if($errors->has('email'))
                                    <p class="red">{{ $errors->first('email') }}</p>
                                    @endif 
                                </div>
                            @else
                                <input type="hidden" name="email" value="{{$admin_details->email}}">
                                <div class="form-group">
                                    <label>@lang('common.email')</label>
                                    <span class="change-pwd form-control">{{$admin_details->email}}</span>
                                </div>
                            @endif                            
                                <div class="form-group">
                                    <label>@lang('common.status')</label>
                                    <div class="radio-group">
                                        <label class="radio-wrap"><input name="status" type="radio" value="1" @if($admin_details->status=='1') checked @endif> <span class="radio-label ">@lang('common.active')</span></label>
                                        <label class="radio-wrap"><input name="status" type="radio" value="0" @if($admin_details->status=='0') checked @endif> <span class="radio-label ">@lang('common.inactive')</span></label>
                                    </div>
                                </div>
                                @if($permission_arr['edit'] === true)
                                    <div class="form-group">
                                        <span class="change-pwd btn btn-primary" data-toggle="modal" data-target="#changerolepwd">@lang('admin.change_password')</span>
                                    </div> 
                                @endif                            
                            </div>
                        </div>
                   
                    </div>
                    <div id="rResources" class="tab-pane fade">
                        <div class="resources-wrap">
                            <div class="resources-img">
                                <div class="resource-profile-img">
                                    <img src="{{ getUserImageUrl($admin_details->image) }}" width="150" height="150">
                                </div>
                                {{$admin_details->role->name}}
                            </div>
                            <div class="role-menulist">
                                {!! CustomHelpers::getRoleMenuDisplay($admin_details->role_id) !!}                             
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
                                <label class="radio-wrap"><input type="radio" name="product_permission_type" value="2" @if($admin_permission['product_permission_type']=='2') checked @endif> <span class="radio-label ">@lang('admin.see_product_belong_to_this_user_created')</span></label>
                            </div>
                            <div class="form-group">                        
                                <label class="radio-wrap"><input type="radio" name="product_permission_type" value="3" @if($admin_permission['product_permission_type']=='3') checked @endif> <span class="radio-label ">@lang('admin.see_product_belong_to_this_role_created')</span></label>
                            </div>
                            <div class="form-group">                        
                                <label class="radio-wrap"><input type="radio" name="product_permission_type" value="4" @if($admin_permission['product_permission_type']=='4') checked @endif id="roleproduct-list"> <span class="radio-label">@lang('admin.see_product_belong_to_select_below'):</span></label>
                            </div>
                            <div class="filter-table-container"  @if($admin_permission['product_permission_type']=='4') style="display:block" @endif>
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
                                <label class="radio-wrap"><input type="radio" name="customer_permission_type" value="2" @if($admin_permission['customer_permission_type']=='2') checked @endif id="customer-list"> <span class="radio-label ">@lang('admin.limit_access')</span></label>
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
                            <div class="customer-table-container" @if($admin_permission['product_permission_type']=='2') style="display:block" @endif>
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
                                <label class="radio-wrap"><input type="radio" name="order_permission_type" value="2" @if($admin_permission['order_permission_type']=='2') checked @endif id="order-list"> <span class="radio-label ">@lang('admin.limit_access')</span></label>
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
                            <div class="order-table-container" @if($admin_permission['order_permission_type']=='2') style="display:block" @endif>
                                Order Table Will be  here....
                            </div>                         
                        </div>
                    @endif
                    <div id="rActivities" class="tab-pane fade">
                        @include('admin.user.userActivityLogs')
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
                        <input type="hidden" name="admin_id" value="{{ $admin_details->id }}">
                        <div class="form-group form-group">
                            <label>@lang('common.old_password') <i class="strick">*</i></label>
                            <input name="old_password" type="password" class="form-control">
                            <p class="red" id="error_old_password"></p>
                        </div> 
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

<div id="confirm_pwd" class="modal fade" role="dialog">
    <div class="modal-dialog">
    <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">&nbsp;</h3>
                <span class="fas fa-times" data-dismiss="modal"></span> 
            </div>
            <div class="modal-body">
                <form id="confirm_password_form" method="post">
                    {{ csrf_field() }}                       
                        <div class="form-group form-group">
                            <label>@lang('common.confirm_your_password') <i class="strick">*</i></label>
                            <input name="confirm_password" type="password" class="form-control">
                            <p class="red" id="error_confirm_password"></p>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="change-pwd btn">@lang('common.submit')</button>
                            <span id="confirm_loader_span"></span>
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
        var loader_url = "{{ Config::get('constants.loader_url') }}loading_small.gif";
        var upload_path = "{{ Config::get('constants.user_path') }}";
        var upload_url = "{{ Config::get('constants.user_url') }}";
        var ajax_url = "{{ action('AjaxController@uploadImageAjax') }}";   
    </script>    
    <!-- for file upload ended --> 
    <script type="text/javascript">
        var change_password_url = "{{ action('Admin\User\AdminController@changePassword') }}";
        var confirm_password_url = "{{ action('Admin\User\AdminController@confirmPassword') }}";
    </script>   
    <script src="{{ Config('constants.admin_js_url') }}bootstrapValidator.min.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}lang/{{ session('lang_code') }}.lang.js"></script>
    <script src="{{ Config::get('constants.js_url') }}bootstrap-datepicker.min.js"></script>
    <script src="{{ Config('constants.admin_js_url') }}adduser.js"></script>      
    <!-- end of page level js -->
@stop
