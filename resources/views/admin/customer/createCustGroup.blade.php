@extends('layouts/admin/default')

@section('title')
    @lang('admin_customer.create_customer_roup')
@stop

@section('header_styles')
<!--page level css -->

<!-- end of page level css -->
@stop

@section('content')
    <div class="content">
        @if(Session::has('succMsg'))
        <div class="alert alert-success alert-dismissable margin5">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
        </div>
        @endif 
        <form id="cmsForm" action="{{ action('Admin\Customer\CustGroupController@store') }}" method="post" class="form-horizontal form-bordered">
            {{ csrf_field() }}
            <div class="header-title">
                <h1 class="title">@lang('admin_customer.add_group')</h1>
                <div class="float-right">
                    <a href="{{ action('Admin\Customer\CustGroupController@index') }}" class="btn btn-back"><span><</span>@lang('admin_common.back')</a>
                    <button type="submit" name="submit_type" value="submit_continue" class="btn">@lang('admin_common.save_and_continue')</button>
                     <button type="submit" name="submit_type" value="submit" class="btn btn-save">@lang('admin_common.save')</button>
                </div>
            </div>       
            <div class="content-wrap">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>@lang('admin_customer.group_name') <i class="strick">*</i></label>
                            
                            {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'group_name', 'label'=>Lang::get('customer.group_name').' <i class="strick">*</i>', 'errorkey'=>'group_name']], '1', $errors) !!}                            
                        </div>
                        <div class="form-group">
                            <label>@lang('admin_customer.require_approve') <i class="strick">*</i></label>                           
                            <label class="check-wrap mt-2">
                             <input type="checkbox" name="require_approve" value="1"> 
                             <span class="chk-label">@lang('admin_customer.after_register_change_to_this_group_need_to_get_approve_from_admin_first')</span>
                             </label>                            
                        </div>
                        <div class="form-group">
                            <label>@lang('admin_customer.default') <i class="strick">*</i></label>
                            <label class="check-wrap mt-2">
                                <input type="checkbox" name="is_default" value="1" > 
                                <span class="chk-label">@lang('admin_customer.default_group')</span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label>@lang('admin_cms.description') <i class="strick">*</i></label>
                            
                            {!! CustomHelpers::fieldstabWithLanuage([['field'=>'textarea', 'name'=>'group_desc', 'label'=>'Description <i class="strick">*</i>', 'errorkey'=>'group_desc', 'cssClass'=>'froala-editor-apply']], '2', $errors) !!}
                            
                        </div>  
                    </div>
                </div>                                                        
            </div>
        </form>
    </div>
@stop

@section('footer_scripts')
 
<!-- begining of page level js -->
@include('includes.froalaeditor_dependencies')
<script type="text/javascript">
   var csrftoken = window.Laravel.csrfToken;
</script>
<script src="{{asset('js/normal_froala_editor_setting.js')}}"></script> 
<!-- end of page level js -->        
@stop
