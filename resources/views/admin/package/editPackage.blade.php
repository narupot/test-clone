@extends('layouts/admin/default')

@section('title')
    @lang('package.edit_package')
@stop

@section('header_styles')

@stop

@section('content')
    <div class="content">
        <form id="packageForm" action="{{ action('Admin\Package\PackageController@update',$package_dtls->id) }}" method="post" class="form-horizontal form-bordered" novalidate="novalidate">
            {{ csrf_field() }}
            {{ method_field('PUT') }}        
            <div class="header-title">
            @if(Session::has('succMsg'))
                <div class="alert alert-success alert-dismissable margin5">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <strong>@lang('common.success'):</strong> {{ Session::get('succMsg') }}
                </div>
            @endif 
                <h1 class="title">@lang('admin_product.edit_badge') : {{!empty($package_dtls->packagedesc)?$package_dtls->packagedesc->package_name:''}}</h1> 
                <div class="float-right">                
                    <a class="btn btn-back" href="{{ action('Admin\Package\PackageController@index') }}">@lang('common.back')</a>
                    <button type="submit" name="submit_type" value="submit_continue" class="btn static-block-save btn-secondary" data-action="submit_continue">@lang('common.save_and_continue')</button>
                    <button type="submit" class="btn btn-save btn-success">@lang('common.save')</button>
                    
                </div>               
            </div>
            <div class="content-wrap">
                <div class="breadcrumb">
                <ul class="bredcrumb-menu">
                    {!!getBreadcrumbAdmin('package')!!}
                </ul>
            </div>
                <div class="form-group row">
                    <div class="col-md-6">
                        <label>@lang('package.title') <i class="strick">*</i></label>
                        <input type="text" name="title" value="{{ $package_dtls->title }}">
                        <p class="error" id="title"></p>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-6">
                        {!! CustomHelpers::fieldstabWithLanuageEdit([['field'=>'text', 'name'=>'package_name', 'label'=>Lang::get('package.package_name').' <i class="strick">*</i>', 'errorkey'=>'package_name']], '1', 'package_id', $package_dtls->id, $tblPackageDesc, $errors) !!}
                        <p class="error" id="package_name"></p>
                        
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <label>@lang('package.dimension') <i class="strick">*</i></label>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-sm-2 form-group">
                        <label>@lang('package.height') <i class="strick">*</i></label>
                        <input type="number" name="height" value="{{$package_dtls->height}}" step=".01">
                    </div>
                    <div class="col-sm-2 form-group">
                        <label>@lang('package.width') <i class="strick">*</i></label>
                        <input type="number" name="width" value="{{$package_dtls->width}}" step=".01">
                    </div>
                    <div class="col-sm-2 form-group">
                        <label>@lang('package.depth') <i class="strick">*</i></label>
                        <input type="number" name="depth" value="{{$package_dtls->depth}}" step=".01">
                    </div>
                </div>

                <div class="form-group">
                    <label>@lang('package.status')</label>
                    <label class="button-switch mt-2">
                        <input type="checkbox" name="status" value="1" class="switch switch-orange ng-valid ng-dirty ng-valid-parse ng-touched ng-not-empty" id="autoRelated" @if($package_dtls->status) checked="checked" @endif>                        
                          <span for="autoRelated" class="lbl-off">@lang('package.off')</span>
                          <span for="autoRelated" class="lbl-on">@lang('package.on')</span>
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
            rules['package_name['+admin_default_lang+']'] = 'required';
            rules['title'] = 'required';
                      
        var messages = {};
            messages['package_name['+admin_default_lang+']'] = "@lang('package.package_name_is_required')";
            messages['title'] = "@lang('common.please_enter_title')";       

        validateForm('packageForm',rules,messages);

    })(jQuery);
</script>       
@stop
