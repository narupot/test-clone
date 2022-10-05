@extends('layouts/admin/default')

@section('title')
    @lang('admin_common.edit_block')
@stop

@section('header_styles')

<!--page level css -->

<!-- end of page level css -->
    
@stop

@section('content')
    <div class="content">
        <form id="BlockForm" action="{{ action('Admin\Customer\CustGroupController@update', $group_dtls->id) }}" method="post" class="form-horizontal form-bordered">
            <div class="header-title clearfix">
                @if(Session::has('sucBlockg'))
                <div class="alert alert-success alert-dismissable margin5">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <strong>@lang('admin_common.success'):</strong> {{ Session::get('succMsg') }}
                </div>
                @endif 
                <h1 class="title">@lang('admin_customer.edit_customer_group') : {{$group_dtls->customerGroupDesc->group_name}}</h1> 
              
                <div class="float-right">                
                    <a href="{{ action('Admin\Customer\CustGroupController@index') }}" class="btn btn-back">@lang('admin_common.back')</a>
                    <button type="submit" class="btn">@lang('admin_common.update')</button>
                </div>
                                
            </div>
            <div class="content-wrap">
                <div class="row">
                    <div class="col-sm-6">
                        
                        {{ csrf_field() }}
                        {{ method_field('PUT') }}

                        <div class="form-group">
                            <label>@lang('admin_customer.group_name') <i class="strick">*</i></label>
                                {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'group_name', 'label'=>Lang::get('customer.group_name').' <i class="strick">*</i>', 'errorkey'=>'group_name']], '1', 'group_id', $group_dtls->id, $tblCustomerGroupDesc, $errors) !!}
                        </div>
                        @if($group_dtls->id != '1')
                            <div class="form-group">
                                <label>@lang('admin_customer.require_approve') <i class="strick">*</i></label>                               
                                <label class="check-wrap mt-2">
                                    <input type="checkbox" name="require_approve" value="1" @if($group_dtls->require_approve == '1') checked="checked" @endif> 
                                    <span class="chk-label">@lang('admin_customer.after_register_change_to_this_group_need_to_get_approve_from_admin_first')</span>
                                </label>                            
                            </div>
                        @endif

                        @if($group_dtls->is_default != '1' && $group_dtls->id != '1')
                            <div class="form-group">
                                <label>@lang('admin_customer.default') <i class="strick">*</i></label>
                                <label class="check-wrap mt-2">
                                    <input type="checkbox" name="is_default" value="1" @if($group_dtls->is_default == '1') checked="checked" @endif> 
                                    <span class="chk-label">@lang('admin_customer.default_group')</span>
                                </label>
                            </div>
                        @endif
                        <div class="form-group">
                            <label>@lang('admin_cms.description') <i class="strick">*</i></label>
                           
                            {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'textarea', 'name'=>'group_desc', 'label'=>Lang::get('cms.description').' <i class="strick">*</i>', 'errorkey'=>'group_desc']], '2', 'group_id', $group_dtls->id, $tblCustomerGroupDesc, $errors) !!}
                            
                        </div>
                    </div>
                </div>
            </div>
        </form>      
    </div>

@stop

@section('footer_scripts')
 
<!-- begining of page level js -->
<script src="{{ Config('constants.page_js_url') }}validateCMS.js" type="text/javascript"></script>
<!-- end of page level js --> 

@include('includes.froalaeditor_dependencies')
<script type="text/javascript">
   var csrftoken = window.Laravel.csrfToken;
</script>
<script src="{{asset('js/normal_froala_editor_setting.js')}}"></script>        
@stop
