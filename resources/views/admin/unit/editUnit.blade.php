@extends('layouts/admin/default')

@section('title')
    @lang('admin_product.edit_unit')
@stop

@section('header_styles')

@stop

@section('content')
    <div class="content">
        <form id="unitForm" action="{{ action('Admin\Unit\UnitController@update',$unit_dtls->id) }}" method="post" class="form-horizontal form-bordered" novalidate="novalidate">
            {{ csrf_field() }}
            {{ method_field('PUT') }}        
            <div class="header-title">
            @if(Session::has('succMsg'))
                <div class="alert alert-success alert-dismissable margin5">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <strong>@lang('common.success'):</strong> {{ Session::get('succMsg') }}
                </div>
            @endif 
                <h1 class="title">@lang('admin_product.edit_badge') : {{!empty($unit_dtls->unitdesc)?$unit_dtls->unitdesc->unit_name:''}}</h1> 
                <div class="float-right">                
                    <a class="btn btn-back" href="{{ action('Admin\Unit\UnitController@index') }}">@lang('common.back')</a>
                    <button type="submit" name="submit_type" value="submit_continue" class="btn static-block-save btn-secondary" data-action="submit_continue">@lang('common.save_and_continue')</button>
                    <button type="submit" class="btn btn-save btn-success">@lang('common.save')</button>
                    
                </div>               
            </div>
            <div class="content-wrap">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('unit')!!}
                    </ul>
                </div>
                <div class="form-group row">
                    <div class="col-md-5">
                        <label>@lang('admin_common.title') <i class="strick">*</i></label>
                        <input type="text" name="title" value="{{ $unit_dtls->title }}">
                        <p class="error" id="title"></p>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-5">
                    
                        {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'unit_name', 'label'=>Lang::get('admin_product.unit_name').' <i class="strick">*</i>', 'errorkey'=>'un_name']], '1', 'unit_id', $unit_dtls->id, $tblUnitDesc, $errors) !!}
                        <p class="error" id="un_name"></p>
                        
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-5">  
                        <label>@lang('admin_product.unit_weight') <i class="strick">*</i></label>  
                        <input type="number" name="unit_weight" value="{{$unit_dtls->unit_weight}}">
                        <span>@lang('admin_product.weight_unit')</span>
                        <p class="error" id="un_weight"></p>
                    </div>
                </div>

                <div class="form-group">
                    <label>@lang('admin_common.status')</label>
                    <label class="button-switch mt-2">
                        <input type="checkbox" name="status" value="1" class="switch switch-orange ng-valid ng-dirty ng-valid-parse ng-touched ng-not-empty" id="autoRelated" @if($unit_dtls->status) checked="checked" @endif>                        
                          <span for="autoRelated" class="lbl-off">@lang('admin_common.off')</span>
                          <span for="autoRelated" class="lbl-on">@lang('admin_common.on')</span>
                    </label>
                </div>

            </div>
        </form>      
    </div>

@stop

@section('footer_scripts')
 
<!-- begining of page level js -->

<!-- end of page level js --> 

<script src="{{ Config('constants.js_url') }}jquery.validate.min.js" type="text/javascript"></script>
<script type="text/javascript">
    
    (function($){

        var rules= {};            
            rules['unit_name['+admin_default_lang+']'] = 'required';
            rules['title'] = 'required';
                      
        var messages = {};
            messages['unit_name['+admin_default_lang+']'] = "@lang('admin_product.badge_name_is_required')";
            messages['title'] = "@lang('common.please_enter_title')";       

        validateForm('unitForm',rules,messages);

    })(jQuery);
</script>       
@stop
