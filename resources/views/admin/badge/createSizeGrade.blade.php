@extends('layouts/admin/default')

@section('title')
    @lang('admin_product.create_new')
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
        <form id="sizegradeForm"  method="post" action="{{ action('Admin\Badge\SizeGradeController@store') }}" class="form-horizontal form-bordered" novalidate="novalidate" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="header-title">
                <h1 class="title">@lang('admin_product.create_new')</h1>
                <div class="float-right">
                    <a class="btn btn-back" href="{{ action('Admin\Badge\SizeGradeController@index') }}"><span><</span>@lang('admin_common.back')</a>
                    <button type="submit" name="submit_type" value="submit_continue" class="btn static-block-save btn-secondary" data-action="submit_continue">@lang('admin_common.save_and_continue')</button>
                    
                    <!-- <button type="button" name="submit_type" value="preview" class="btn static-block-save" style="background: #38c1ff;" data-action="preview">@lang('admin_common.priview')</button> -->                    
                    <button type="submit" name="submit_type" value="submit" class="btn btn-save static-block-save btn-success" data-action="submit">@lang('common.save')</button>
                </div>
            </div>       
            <div class="content-wrap">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('size_grade')!!}
                    </ul>
                </div>
                <div class="form-group row">
                    <div class="col-md-5">
                        <label>@lang('admin_common.slug') <i class="strick">*</i></label>
                        <input type="text" name="slug" value="">
                        <p class="error" id="slug"></p>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-5">                                          
                        {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'name', 'label'=>Lang::get('admin_product.name').'<i class="strick">*</i>', 'errorkey'=>'sg_name']], '1', $errors) !!}
                        <p class="error" id="un_name"></p>
                        
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-5">  
                        <label>@lang('admin_product.type') <i class="strick">*</i></label>  
                        <select name="type">
                            <option value="size">@lang('admin_product.size')</option>
                            <option value="grade">@lang('admin_product.grade')</option>
                        </select>                                      
                        
                        <p class="error" id="type"></p>
                    </div>
                </div>

                <div class="form-group">
                    <label>@lang('admin_common.status')</label>
                    <label class="button-switch mt-2">
                        <input type="checkbox" name="status" value="1" class="switch switch-orange ng-valid ng-dirty ng-valid-parse ng-touched ng-not-empty" id="autoRelated" checked="checked">                        
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
            rules['name['+admin_default_lang+']'] = 'required';
            rules['slug'] = 'required';
                      
        var messages = {};
            messages['name['+admin_default_lang+']'] = "@lang('admin_product.name_is_required')";
            messages['slug'] = "@lang('common.please_enter_slug')";       

        validateForm('sizegradeForm',rules,messages);

    })(jQuery);
</script>  





@stop
