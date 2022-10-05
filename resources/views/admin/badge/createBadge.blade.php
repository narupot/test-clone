@extends('layouts/admin/default')

@section('title')
    @lang('admin_product.create_new_badge')
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
        <form id="badgeForm"  method="post" action="{{ action('Admin\Badge\BadgeController@store') }}" class="form-horizontal form-bordered" novalidate="novalidate" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="header-title">
                <h1 class="title">@lang('admin_product.create_new_badge')</h1>
                <div class="float-right">
                    <a class="btn btn-back" href="{{ action('Admin\Badge\BadgeController@index') }}"><span><</span>@lang('admin_common.back')</a>
                    <button type="submit" name="submit_type" value="submit_continue" class="btn static-block-save btn-secondary" data-action="submit_continue">@lang('admin_common.save_and_continue')</button>
                    
                    <!-- <button type="button" name="submit_type" value="preview" class="btn static-block-save" style="background: #38c1ff;" data-action="preview">@lang('admin_common.priview')</button> -->                    
                    <button type="submit" name="submit_type" value="submit" class="btn btn-save static-block-save btn-success" data-action="submit">@lang('common.save')</button>
                </div>
            </div>       
            <div class="content-wrap">
                <div class="breadcrumb">
                    <ul class="bredcrumb-menu">
                        {!!getBreadcrumbAdmin('product','badge')!!}
                    </ul>
                </div>
                <div class="form-group row">
                    <div class="col-md-5">
                        <label>@lang('admin_common.title') <i class="strick">*</i></label>
                        <input type="text" name="title" value="">
                        <p class="error" id="title"></p>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-5">                                        
                        {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'badge_name', 'label'=>Lang::get('admin_product.badge_name').'<i class="strick">*</i>', 'errorkey'=>'bg_name']], '1', $errors) !!}
                        <p class="error" id="bg_name"></p>                        
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-5">
                        <label>@lang('admin_product.size') <i class="strick">*</i></label>
                        <select name="size">
                            @foreach(CustomHelpers::getBadgeSize() as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                        <p class="error" id="size"></p>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-5">
                        <label>@lang('admin_product.grade') <i class="strick">*</i></label>
                        <select name="grade">
                            @foreach(CustomHelpers::getBadgeGrade() as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                        
                        <p class="error" id="grade"></p>
                    </div>
                </div>
                
                <div class="form-group">                    
                    <label>@lang('admin_common.icon')</label>

                    <div class="file-wrapper">
                        <div class="custom-img-file" style="position: relative; width: auto; display: inline-block;">
                            <input type="file" name="icon" class="file-upload">
                            <span class="file-img btn-default"><img src="images/file-upload.png"></span>
                            <img class="upload-img" src=""/ style="height: 50px; display: none;">
                        </div>                                                  
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
            rules['badge_name['+admin_default_lang+']'] = 'required';
            rules['title'] = 'required';
                      
        var messages = {};
            messages['badge_name['+admin_default_lang+']'] = "@lang('admin_product.badge_name_is_required')";
            messages['title'] = "@lang('common.please_enter_title')";       

        validateForm('badgeForm',rules,messages);

    })(jQuery);
</script>  


@stop
