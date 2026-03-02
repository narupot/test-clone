@extends('layouts/admin/default')

@section('title')
    @lang('admin.add_block') - {{getSiteName()}} 
@stop

@section('header_styles')

<link href="{{ asset('assets/css/pages/form_layouts.css') }}" rel="stylesheet" type="text/css" />
    <style type="text/css">
        .hide-div{
            display: none;
        }
    </style>
@stop

@section('content')
<div class="content"> 
     @if(Session::has('succMsg'))
        <script type="text/javascript">               
            _toastrMessage('success', "{{ Session::get('succMsg') }}");    
        </script> 
    @endif       
 
    <div class="header-title clearfix">
                       
        <h1 class="title">@lang('admin.add_block')</h1>
     </div>
    <div class="content-wrap">
        <div class="breadcrumb">
            <ul class="bredcrumb-menu">
                {!!getBreadcrumbAdmin('blog','widget')!!}
            </ul>
        </div>
        <form id="cmsForm" action="{{ action('Admin\Widget\WidgetController@store') }}" method="post" class="form-horizontal form-bordered">
            {{ csrf_field() }}
            <div class="form-group">
                <label class="col-md-3 control-label" for="form-text-input">@lang('admin.url_key') <i class="strick">*</i></label> 
                <div class="col-md-5">
                    <input type="text" name="url" value="{{ old('url') }}">
                    @if ($errors->has('url'))
                        <p class="error error-msg">{{ $errors->first('url') }}</p>
                    @endif
                </div>
            </div> 
            <div class="form-group">
                <label class="col-md-3 control-label" for="form-text-input">@lang('admin.status') <i class="strick">*</i></label> 
                <div class="col-md-5">
                    <select class="select" name="status">
                        <option value="1">@lang('admin.enable')</option>
                        <option value="0">@lang('admin.disable')</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label" for="form-text-input">@lang('admin.section') </label> 
                <div class="col-md-5">
                    <select class="select" name="section">
                    <option value="">@lang('admin.select')</option>
                    @if(count($section))
                    @foreach($section as $key => $value)
                        <option value="{{ $value->id }}" @if(old('section') == $value->id) selected="selected" @endif>{{ ucfirst(str_replace('-',' ', $value->section_name)) }}</option>
                    @endforeach
                    @endif
                        
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label" for="form-text-input">&nbsp; </label> 
                <div class="col-md-5">
                    <input type="checkbox" name="fixed" value="1"> @lang('admin.is_fixed')
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label" for="form-text-input">@lang('admin.type') </label> 
                <div class="col-md-5">
                    <select class="select" name="type">
                    {{-- <option value="">@lang('admin.select')</option> --}}
                        <option value="static-block">@lang('admin.static-block')</option>
                        <option value="banner">@lang('admin.banner')</option>
                        <option value="product-slider">@lang('admin.product_slider')</option>
                        <option value="part-finder">@lang('admin.part_finder')</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label" for="form-text-input">@lang('admin.pages') </label> 
                <div class="col-md-5">
                    <input type="radio" name="pages" checked="checked" value="1"> @lang('admin.show_all_pages')
                    <input type="radio" name="pages" value="2"> @lang('admin.show_checked_pages')
                    <input type="radio" name="pages" value="3"> @lang('admin.hide_checked_pages')
                </div>
            </div>

            <div class="form-group hide-div" id="page_value">
                <label class="col-md-3 control-label" for="form-text-input">@lang('admin.url') </label> 
                <div class="col-md-5">
                    <input type="text" name="page_url" value=""> 
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label" for="form-text-input">@lang('admin.customer_group') </label> 
                <div class="col-md-5">
                    <input type="radio" name="group" checked="checked" value="1"> @lang('admin.show_all_group')
                    <input type="radio" name="group" value="2"> @lang('admin.show_checked_group')
                    <input type="radio" name="group" value="3"> @lang('admin.hide_checked_group')
                </div>
            </div>

            <div class="form-group hide-div" id="group_value">
                <label class="col-md-3 control-label" for="form-text-input">&nbsp; </label> 
                <div class="col-md-5">
                    <select name="group_id[]" multiple="multiple" class="multiple-selectw">
                        <option value="">@lang('admin.select')</option>
                        @if(count($customer_group))
                        @foreach($customer_group as $key => $group)
                            <option value="{{ $group->id}}">{{ $group->customerGroupDesc->group_name  }}</option>
                        @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-3 control-label" for="form-text-input">@lang('admin.title') <i class="strick">*</i></label>
                <div class="col-md-8">
                    
                    {!! CustomHelpers::textWithLanuage('text','title', '', 'page-title', $errors, 'page_ttl') !!}
                    
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label" for="form-text-input">@lang('admin.heading') </label>
                <div class="col-md-8">
                    
                    {!! CustomHelpers::textWithLanuage('text','heading', '', 'heading', $errors, 'page_ttl') !!}
                    
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-3 control-label" for="form-text-input">@lang('admin.description') <i class="strick">*</i></label>
                <div class="col-md-8">
                    
                    {!! CustomHelpers::textWithLanuage('textarea','desc', 'froala-editor-apply', 'page-desc', $errors, 'page_description') !!}
                    
                </div>
            </div>
                                             
            <div class="form-group">
                <div class="col-md-9 col-md-offset-3">
                    <button type="submit" name="submit_type" value="submit" class="btn btn-effect-ripple btn-primary">@lang('admin.save')</button>

                    <button type="submit" name="submit_type" value="submit_continue" class="btn btn-effect-ripple btn-primary">@lang('admin.save_and_continue')</button>

                    <a href="{{ action('AdminBlockController@index') }}"><button type="button" class="btn btn-effect-ripple btn-default">@lang('admin.back')</button></a>
                </div>
            </div>
        </form>
    </div>
      
</div>
      
@stop

@section('footer_scripts')
 
<!-- begining of page level js -->
<script src="{{ Config('constants.page_js_url') }}validateCMS.js" type="text/javascript"></script>
<script type="text/javascript">
    jQuery('input[name=pages]').click(function(){
        if($(this).val() != '1'){
            $('#page_value').show();
        }else{
            $('#page_value').hide();
        }
    })

    jQuery('input[name=group]').click(function(){
        if($(this).val() != '1'){
            $('#group_value').show();
        }else{
            $('#group_value').hide();
        }
    })
</script>
<!-- end of page level js --> 

@include('includes.froalaeditor_dependencies')
<script type="text/javascript">
   var csrftoken = window.Laravel.csrfToken;
</script>
<script src="{{asset('js/normal_froala_editor_setting.js')}}"></script>        
@stop
