@extends('layouts/admin/default')

@section('title')
    @lang('cms.create_block')
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
                <strong>@lang('common.success'):</strong> {{ Session::get('succMsg') }}
            </div>
        @endif 
        <form id="cmsForm" action="{{ action('Admin\Block\StaticBlockController@store') }}" method="post" class="form-horizontal form-bordered">
            {{ csrf_field() }}
            <div class="header-title">
                <h1 class="title">@lang('cms.create_block')</h1>
                <div class="float-right">
                    <a class="btn btn-back" href="{{ action('Admin\Block\StaticBlockController@index') }}"><span><</span>@lang('common.back')</a>

                    <button type="submit" name="submit_type" value="submit_continue" class="btn static-block-save" data-action="submit_continue">@lang('common.save_and_continue')</button>               
                    <button type="submit" name="submit_type" value="submit" class="btn btn-save static-block-save" data-action="submit">@lang('common.save')</button>
                </div>
            </div>       
            <div class="content-wrap">
                <div class="form-group row">
                    <div class="col-md-5">
                        <label>@lang('common.status') <i class="strick">*</i></label>                     
                        <select class="select" name="status">
                            <option value="1">@lang('common.active')</option>
                            <option value="0">@lang('common.inactive')</option>
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-8">
                        <input type="text" class="form-control" id="page_title" name="page_title" placeholder="First name" aria-required="true" aria-describedby="page_title-error" aria-invalid="true">
                        <label for="form-text-input">&nbsp;</label>                   
                        {!! CustomHelpers::fieldstabWithLanuage([['field'=>'text', 'name'=>'page_title', 'label'=>'Title <i class="strick">*</i>', 'errorkey'=>'page_ttl','required'=>'required']], '1', $errors) !!}
                        <p class="error error-msg">{{ $errors->first('page_title[Session::get(admin_default_lang)]', ':messages') }}</p>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-8">
                        <label for="form-text-input">&nbsp;</label>
                        {!! CustomHelpers::fieldstabWithLanuage([['field'=>'textarea', 'name'=>'page_desc', 'label'=>'Description <i class="strick">*</i>', 'errorkey'=>'page_description', 'cssClass'=>'froala-editor-apply']], '2', $errors) !!}
                        <p class="error error-msg"></p>
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
   var lang_id = "{{Session::get('admin_default_lang')}}";
</script>
<script src="{{asset('js/normal_froala_editor_setting.js')}}"></script>      
@stop
